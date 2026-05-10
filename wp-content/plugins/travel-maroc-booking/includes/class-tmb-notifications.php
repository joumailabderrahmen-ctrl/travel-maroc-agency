<?php
defined( 'ABSPATH' ) || exit;

class TMB_Notifications {

    public static function init() {
        // Déclencher notifications sur événements WooCommerce
        add_action( 'woocommerce_new_order',              [ __CLASS__, 'on_new_order'       ] );
        add_action( 'woocommerce_order_status_completed', [ __CLASS__, 'on_order_completed' ] );
        add_action( 'woocommerce_order_status_cancelled', [ __CLASS__, 'on_order_cancelled' ] );

        // Envoi différé des notifications EMAIL (cron)
        add_action( 'tmb_send_notifications_cron',        [ __CLASS__, 'process_queue'      ] );
        if ( ! wp_next_scheduled( 'tmb_send_notifications_cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'tmb_send_notifications_cron' );
        }
    }

    /**
     * Crée une notification en base (statut EN_ATTENTE).
     */
    public static function creer( array $data ): ?int {
        global $wpdb;
        $defaults = [
            'destinataire_type' => 'CLIENT',
            'client_id'         => null,
            'wp_admin_user_id'  => null,
            'wc_order_id'       => null,
            'offre_post_id'     => null,
            'type'              => 'SYSTEME',
            'titre'             => '',
            'message'           => '',
            'canal'             => 'EMAIL',
            'statut'            => 'EN_ATTENTE',
            'date_creation'     => current_time( 'mysql' ),
            'tentatives'        => 0,
        ];
        $row = array_intersect_key( array_merge( $defaults, $data ), $defaults );

        $inserted = $wpdb->insert( $wpdb->prefix . 'tma_notification', $row );
        return $inserted ? $wpdb->insert_id : null;
    }

    // ── Événements automatiques ─────────────────────────────

    public static function on_new_order( int $order_id ): void {
        $order   = wc_get_order( $order_id );
        $user_id = $order ? $order->get_user_id() : 0;
        if ( ! $user_id ) return;

        $profile = TMB_Fidelite::get_profile( $user_id );
        if ( ! $profile ) return;

        $code = 'TMA-' . gmdate('Y') . '-' . str_pad( $order_id, 4, '0', STR_PAD_LEFT );
        self::creer( [
            'client_id'   => $profile->id,
            'type'        => 'CONFIRMATION_RESERVATION',
            'titre'       => "Réservation confirmée — $code",
            'message'     => "Votre réservation {$code} a bien été reçue. Total : " . wc_price( $order->get_total() ) . ". Nous vous contacterons pour confirmer les détails.",
            'canal'       => 'EMAIL',
            'wc_order_id' => $order_id,
        ] );
    }

    public static function on_order_completed( int $order_id ): void {
        $order   = wc_get_order( $order_id );
        $user_id = $order ? $order->get_user_id() : 0;
        if ( ! $user_id ) return;
        $profile = TMB_Fidelite::get_profile( $user_id );
        if ( ! $profile ) return;

        self::creer( [
            'client_id'   => $profile->id,
            'type'        => 'PAIEMENT_VALIDE',
            'titre'       => "Paiement validé — Commande #$order_id",
            'message'     => "Votre paiement a été validé. Bon voyage ! L'équipe Travel Maroc Agency.",
            'canal'       => 'EMAIL',
            'wc_order_id' => $order_id,
        ] );
    }

    public static function on_order_cancelled( int $order_id ): void {
        $order   = wc_get_order( $order_id );
        $user_id = $order ? $order->get_user_id() : 0;
        if ( ! $user_id ) return;
        $profile = TMB_Fidelite::get_profile( $user_id );
        if ( ! $profile ) return;

        self::creer( [
            'client_id'   => $profile->id,
            'type'        => 'ANNULATION',
            'titre'       => "Annulation — Commande #$order_id",
            'message'     => "Votre commande #$order_id a été annulée. Contactez-nous pour toute question.",
            'canal'       => 'EMAIL',
            'wc_order_id' => $order_id,
        ] );
    }

    // ── Traitement de la file d'envoi (cron) ────────────────
    public static function process_queue(): void {
        global $wpdb;
        $table  = $wpdb->prefix . 'tma_notification';
        $notifs = $wpdb->get_results(
            "SELECT n.*, p.wp_user_id
             FROM $table n
             LEFT JOIN {$wpdb->prefix}tma_client_profile p ON p.id = n.client_id
             WHERE n.statut = 'EN_ATTENTE' AND n.canal = 'EMAIL' AND n.tentatives < 3
             ORDER BY n.date_creation ASC LIMIT 20"
        );

        foreach ( $notifs as $n ) {
            $sent = self::envoyer_email( $n );
            if ( $sent ) {
                $wpdb->update( $table, [
                    'statut'     => 'ENVOYEE',
                    'date_envoi' => current_time( 'mysql' ),
                ], [ 'id' => $n->id ] );
            } else {
                $wpdb->update( $table, [
                    'tentatives'    => (int)$n->tentatives + 1,
                    'statut'        => ( (int)$n->tentatives >= 2 ) ? 'ECHEC' : 'EN_ATTENTE',
                    'erreur_details'=> 'Échec wp_mail()',
                ], [ 'id' => $n->id ] );
            }
        }
    }

    private static function envoyer_email( object $notif ): bool {
        if ( ! $notif->wp_user_id ) return false;
        $user = get_user_by( 'ID', $notif->wp_user_id );
        if ( ! $user ) return false;
        return wp_mail( $user->user_email, $notif->titre, $notif->message );
    }

    // ── Marquer comme lue (IN_APP) ──────────────────────────
    public static function marquer_lue( int $notif_id, int $client_id ): bool {
        global $wpdb;
        return (bool) $wpdb->update(
            $wpdb->prefix . 'tma_notification',
            [ 'statut' => 'LUE', 'date_lecture' => current_time( 'mysql' ) ],
            [ 'id' => $notif_id, 'client_id' => $client_id ]
        );
    }

    // ── Lister les notifications d'un client ────────────────
    public static function get_for_client( int $profile_id, int $limit = 20 ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tma_notification
             WHERE client_id = %d ORDER BY date_creation DESC LIMIT %d",
            $profile_id, $limit
        ), ARRAY_A );
    }
}
