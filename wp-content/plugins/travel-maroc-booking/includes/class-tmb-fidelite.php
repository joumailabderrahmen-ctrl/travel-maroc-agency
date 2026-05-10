<?php
defined( 'ABSPATH' ) || exit;

class TMB_Fidelite {

    // 1 point pour 10 MAD dépensés
    const POINTS_PAR_MAD   = 0.1;
    // 100 points = 10 MAD de remise
    const MAD_PAR_100_PTS  = 10.0;
    // Expiration : 24 mois
    const EXPIRATION_MOIS  = 24;
    // Minimum total_net (plancher)
    const MONTANT_MIN_MAD  = 50.0;

    // Méta clé panier pour activer la remise points
    const CART_META_USE_POINTS = 'tma_use_points';
    // Nom de la frais WooCommerce (valeur négative = remise)
    const FEE_LABEL = 'Remise fidélité';

    public static function init() {
        add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'attribuer_points'       ] );
        add_action( 'woocommerce_order_status_cancelled', [ __CLASS__, 'annuler_points'         ] );
        add_action( 'user_register',                      [ __CLASS__, 'creer_profil_client'    ] );

        // Remise fidélité (taux type client) appliquée automatiquement au panier
        add_action( 'woocommerce_cart_calculate_fees',    [ __CLASS__, 'appliquer_remise_panier' ] );
        // Déduire les points utilisés en remise lors de la commande
        add_action( 'woocommerce_checkout_order_created', [ __CLASS__, 'deduire_points_commande' ] );
        // Cron expiration points
        add_action( 'tma_cron_expirer_points',            [ __CLASS__, 'expirer_points'          ] );
    }

    // ── Création automatique du profil à l'inscription ─────
    public static function creer_profil_client( int $user_id ): void {
        global $wpdb;
        $table = $wpdb->prefix . 'tma_client_profile';
        if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE wp_user_id=%d", $user_id ) ) ) return;

        $wpdb->insert( $table, [
            'wp_user_id'     => $user_id,
            'type_client_id' => 1,
            'date_inscription'=> current_time( 'mysql' ),
        ] );
    }

    // ── Attribuer des points après commande complétée ───────
    public static function attribuer_points( int $order_id ): void {
        $order   = wc_get_order( $order_id );
        if ( ! $order ) return;
        $user_id = $order->get_user_id();
        if ( ! $user_id ) return;

        $profile = self::get_profile( $user_id );
        if ( ! $profile ) {
            self::creer_profil_client( $user_id );
            $profile = self::get_profile( $user_id );
        }
        if ( ! $profile ) return;

        $total  = (float) $order->get_total();
        $points = (int) floor( $total * self::POINTS_PAR_MAD );
        if ( $points < 1 ) return;

        $exp    = ( (int) $profile->points_jamais_expirent ?? 0 )
                    ? null
                    : gmdate( 'Y-m-d', strtotime( '+' . self::EXPIRATION_MOIS . ' months' ) );

        self::crediter( $profile->id, $points, $order_id, "Commande #$order_id", $exp );
        self::verifier_montee_palier( $profile->id );

        // Notification client
        TMB_Notifications::creer( [
            'destinataire_type' => 'CLIENT',
            'client_id'         => $profile->id,
            'type'              => 'POINTS_GAGNES',
            'titre'             => "Vous avez gagné $points points !",
            'message'           => "Félicitations ! Vous avez gagné $points points de fidélité grâce à votre commande #$order_id. Solde actuel : " . ( $profile->points_fidelite_solde + $points ) . ' pts.',
            'canal'             => 'EMAIL',
            'wc_order_id'       => $order_id,
        ] );
    }

    // ── Annuler les points si commande annulée ──────────────
    public static function annuler_points( int $order_id ): void {
        global $wpdb;
        $table   = $wpdb->prefix . 'tma_historique_points';
        $gain    = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table WHERE wc_order_id=%d AND operation='GAIN' LIMIT 1", $order_id
        ) );
        if ( ! $gain ) return;

        $profile = self::get_profile_by_id( (int) $gain->client_id );
        if ( ! $profile ) return;

        $points = (int) $gain->points;
        $avant  = (int) $profile->points_fidelite_solde;
        $apres  = max( 0, $avant - $points );

        global $wpdb;
        $wpdb->update( $wpdb->prefix . 'tma_client_profile', [
            'points_fidelite_solde' => $apres,
            'points_cumules_total'  => max( 0, (int)$profile->points_cumules_total - $points ),
        ], [ 'id' => $profile->id ] );

        $wpdb->insert( $table, [
            'client_id'    => $profile->id,
            'wc_order_id'  => $order_id,
            'operation'    => 'EXPIRATION',
            'points'       => -$points,
            'solde_avant'  => $avant,
            'solde_apres'  => $apres,
            'description'  => "Annulation commande #$order_id — points repris",
        ] );
    }

    // ── Remise fidélité automatique dans le panier ─────────
    public static function appliquer_remise_panier( WC_Cart $cart ): void {
        if ( ! is_user_logged_in() ) return;
        $profile = self::get_profile( get_current_user_id() );
        if ( ! $profile || ! (float) $profile->taux_remise ) return;

        $taux   = (float) $profile->taux_remise / 100;
        $total  = $cart->get_subtotal();
        if ( $total < self::MONTANT_MIN_MAD ) return;

        $remise = round( $total * $taux, 2 );
        $cart->add_fee( self::FEE_LABEL . ' (' . $profile->type_libelle . ')', -$remise, false );
    }

    // ── Déduire les points utilisés en remise ───────────────
    public static function deduire_points_commande( WC_Order $order ): void {
        $user_id = $order->get_user_id();
        if ( ! $user_id ) return;
        $profile = self::get_profile( $user_id );
        if ( ! $profile ) return;

        // Chercher une fee "Remise fidélité" dans la commande
        $remise_appliquee = 0.0;
        foreach ( $order->get_fees() as $fee ) {
            if ( strpos( $fee->get_name(), self::FEE_LABEL ) !== false && (float) $fee->get_total() < 0 ) {
                $remise_appliquee += abs( (float) $fee->get_total() );
            }
        }
        if ( $remise_appliquee < 0.01 ) return;

        // Convertir la remise en points débités (inverse de calculer_remise_points)
        $points_a_deduire = (int) round( $remise_appliquee / self::MAD_PAR_100_PTS * 100 );
        if ( $points_a_deduire < 1 ) return;

        global $wpdb;
        $pt          = $wpdb->prefix . 'tma_client_profile';
        $ht          = $wpdb->prefix . 'tma_historique_points';
        $solde_avant = (int) $profile->points_fidelite_solde;
        $solde_apres = max( 0, $solde_avant - $points_a_deduire );

        $wpdb->update( $pt,
            [ 'points_fidelite_solde' => $solde_apres ],
            [ 'id' => $profile->id ]
        );
        $wpdb->insert( $ht, [
            'client_id'   => $profile->id,
            'wc_order_id' => $order->get_id(),
            'operation'   => 'UTILISATION',
            'points'      => -$points_a_deduire,
            'solde_avant' => $solde_avant,
            'solde_apres' => $solde_apres,
            'description' => 'Remise appliquée commande #' . $order->get_id() . ' (-' . number_format($remise_appliquee,2,',','') . ' MAD)',
        ] );
    }

    // ── Expiration automatique des points (cron) ────────────
    public static function expirer_points(): void {
        global $wpdb;
        $today   = gmdate('Y-m-d');
        $expired = $wpdb->get_results( $wpdb->prepare(
            "SELECT h.*, p.id AS profile_id, p.points_fidelite_solde
             FROM {$wpdb->prefix}tma_historique_points h
             JOIN {$wpdb->prefix}tma_client_profile p ON p.id = h.client_id
             WHERE h.operation = 'GAIN'
               AND h.date_expiration IS NOT NULL
               AND h.date_expiration <= %s
               AND h.points > 0
               AND NOT EXISTS (
                   SELECT 1 FROM {$wpdb->prefix}tma_historique_points e
                   WHERE e.client_id = h.client_id
                     AND e.wc_order_id = h.wc_order_id
                     AND e.operation = 'EXPIRATION'
               )",
            $today
        ) );

        foreach ( $expired as $row ) {
            $pts_expire  = (int) $row->points;
            $solde_avant = (int) $row->points_fidelite_solde;
            $solde_apres = max( 0, $solde_avant - $pts_expire );

            $wpdb->update( $wpdb->prefix . 'tma_client_profile',
                [ 'points_fidelite_solde' => $solde_apres ],
                [ 'id' => $row->profile_id ]
            );
            $wpdb->insert( $wpdb->prefix . 'tma_historique_points', [
                'client_id'   => $row->profile_id,
                'wc_order_id' => $row->wc_order_id,
                'operation'   => 'EXPIRATION',
                'points'      => -$pts_expire,
                'solde_avant' => $solde_avant,
                'solde_apres' => $solde_apres,
                'description' => 'Expiration automatique (éch. ' . $row->date_expiration . ')',
            ] );
        }
    }

    // ── Calcul de la remise selon type client ───────────────
    public static function calculer_remise_type( float $total, int $type_client_id ): float {
        global $wpdb;
        $type = $wpdb->get_row( $wpdb->prepare(
            "SELECT taux_remise FROM {$wpdb->prefix}tma_type_client WHERE id=%d", $type_client_id
        ) );
        if ( ! $type ) return 0.0;
        return round( $total * ( (float) $type->taux_remise / 100 ), 2 );
    }

    // ── Montant de remise points ────────────────────────────
    public static function calculer_remise_points( int $points_utilises ): float {
        return round( ( $points_utilises / 100 ) * self::MAD_PAR_100_PTS, 2 );
    }

    // ── Créditer des points (opération interne) ─────────────
    public static function crediter( int $profile_id, int $points, ?int $order_id, string $desc, ?string $exp = null ): void {
        global $wpdb;
        $pt  = $wpdb->prefix . 'tma_client_profile';
        $ht  = $wpdb->prefix . 'tma_historique_points';

        $profile    = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $pt WHERE id=%d", $profile_id ) );
        $solde_avant = (int) $profile->points_fidelite_solde;
        $solde_apres = $solde_avant + $points;

        $wpdb->update( $pt, [
            'points_fidelite_solde' => $solde_apres,
            'points_cumules_total'  => (int)$profile->points_cumules_total + $points,
        ], [ 'id' => $profile_id ] );

        $wpdb->insert( $ht, [
            'client_id'      => $profile_id,
            'wc_order_id'    => $order_id,
            'operation'      => 'GAIN',
            'points'         => $points,
            'solde_avant'    => $solde_avant,
            'solde_apres'    => $solde_apres,
            'description'    => $desc,
            'date_expiration'=> $exp,
        ] );
    }

    // ── Vérifier montée de palier ───────────────────────────
    public static function verifier_montee_palier( int $profile_id ): void {
        global $wpdb;
        $profile = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tma_client_profile WHERE id=%d", $profile_id
        ) );
        if ( ! $profile ) return;

        $type = $wpdb->get_row( $wpdb->prepare(
            "SELECT id, libelle FROM {$wpdb->prefix}tma_type_client
             WHERE seuil_points <= %d AND libelle != 'Entreprise'
             ORDER BY seuil_points DESC LIMIT 1",
            (int) $profile->points_cumules_total
        ) );
        if ( $type && (int)$type->id !== (int)$profile->type_client_id ) {
            $wpdb->update( $wpdb->prefix . 'tma_client_profile',
                [ 'type_client_id' => $type->id ],
                [ 'id' => $profile_id ]
            );
        }
    }

    // ── Getters profil ──────────────────────────────────────
    public static function get_profile( int $wp_user_id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT p.*, t.taux_remise, t.libelle AS type_libelle, t.couleur_badge, t.points_jamais_expirent
             FROM {$wpdb->prefix}tma_client_profile p
             JOIN {$wpdb->prefix}tma_type_client t ON t.id = p.type_client_id
             WHERE p.wp_user_id = %d", $wp_user_id
        ) );
    }

    public static function get_profile_by_id( int $profile_id ): ?object {
        global $wpdb;
        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tma_client_profile WHERE id = %d", $profile_id
        ) );
    }

    // ── Historique pour le compte client ───────────────────
    public static function get_historique( int $wp_user_id, int $limit = 10 ): array {
        global $wpdb;
        $profile = self::get_profile( $wp_user_id );
        if ( ! $profile ) return [];
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tma_historique_points
             WHERE client_id = %d ORDER BY date_operation DESC LIMIT %d",
            $profile->id, $limit
        ), ARRAY_A );
    }
}
