<?php
defined( 'ABSPATH' ) || exit;

class TMB_Log_Admin {

    private static function table(): string {
        global $wpdb;
        return $wpdb->prefix . 'tma_log_admin';
    }

    public static function init() {
        add_action( 'wp_login',  [ __CLASS__, 'log_login'  ], 10, 2 );
        add_action( 'wp_logout', [ __CLASS__, 'log_logout' ] );
        add_action( 'woocommerce_order_status_changed', [ __CLASS__, 'log_order_status' ], 10, 4 );
        add_action( 'save_post_product',               [ __CLASS__, 'log_product_save' ], 10, 3 );
    }

    /**
     * Enregistre une action dans le journal.
     * Accepte deux formes :
     *   - write( 'ACTION', 'entite', $id, $avant, $apres, $notes )
     *   - write([ 'action' => '...', 'entite_cible' => '...', 'entite_id' => ..., 'notes' => '...' ])
     */
    public static function write(
        string|array $action_or_data,
        string $entite     = '',
        ?int   $entite_id  = null,
        ?array $avant      = null,
        ?array $apres      = null,
        string $notes      = ''
    ): void {
        global $wpdb;
        $user_id = get_current_user_id();
        if ( ! $user_id ) return;

        if ( is_array( $action_or_data ) ) {
            $action    = strtoupper( $action_or_data['action']       ?? 'UPDATE' );
            $entite    = $action_or_data['entite_cible'] ?? $entite;
            $entite_id = isset( $action_or_data['entite_id'] ) ? (int) $action_or_data['entite_id'] : $entite_id;
            $avant     = $action_or_data['avant']  ?? $avant;
            $apres     = $action_or_data['apres']  ?? $apres;
            $notes     = $action_or_data['notes']  ?? $notes;
        } else {
            $action = strtoupper( $action_or_data );
        }

        $wpdb->insert( self::table(), [
            'wp_user_id'   => $user_id,
            'action'       => strtoupper( $action ),
            'entite_cible' => $entite,
            'entite_id'    => $entite_id,
            'valeurs_avant'=> $avant ? wp_json_encode( $avant, JSON_UNESCAPED_UNICODE ) : null,
            'valeurs_apres'=> $apres ? wp_json_encode( $apres, JSON_UNESCAPED_UNICODE ) : null,
            'ip_address'   => self::get_ip(),
            'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
            'notes'        => sanitize_text_field( $notes ),
            'date_action'  => current_time( 'mysql' ),
        ] );
    }

    public static function log_login( string $login, WP_User $user ): void {
        global $wpdb;
        $wpdb->insert( self::table(), [
            'wp_user_id'   => $user->ID,
            'action'       => 'LOGIN',
            'entite_cible' => 'wp_users',
            'entite_id'    => $user->ID,
            'ip_address'   => self::get_ip(),
            'user_agent'   => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
            'date_action'  => current_time( 'mysql' ),
        ] );
    }

    public static function log_logout(): void {
        $user_id = get_current_user_id();
        if ( ! $user_id ) return;
        global $wpdb;
        $wpdb->insert( self::table(), [
            'wp_user_id'   => $user_id,
            'action'       => 'LOGOUT',
            'entite_cible' => 'wp_users',
            'entite_id'    => $user_id,
            'ip_address'   => self::get_ip(),
            'date_action'  => current_time( 'mysql' ),
        ] );
    }

    public static function log_order_status( int $order_id, string $old, string $new, WC_Order $order ): void {
        if ( ! is_admin() ) return;
        self::write( 'UPDATE', 'wc_orders', $order_id,
            [ 'statut' => $old ],
            [ 'statut' => $new ],
            "Changement statut commande #$order_id"
        );
    }

    public static function log_product_save( int $post_id, WP_Post $post, bool $update ): void {
        if ( ! is_admin() || wp_is_post_revision( $post_id ) ) return;
        self::write(
            $update ? 'UPDATE' : 'CREATE',
            'wp_posts',
            $post_id,
            null,
            [ 'title' => $post->post_title, 'status' => $post->post_status ],
            "Produit : {$post->post_title}"
        );
    }

    /**
     * Récupère les derniers logs pour le dashboard.
     */
    public static function get_recent( int $limit = 20 ): array {
        global $wpdb;
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT l.*, u.display_name AS admin_name
             FROM {$wpdb->prefix}tma_log_admin l
             LEFT JOIN {$wpdb->users} u ON u.ID = l.wp_user_id
             ORDER BY l.date_action DESC
             LIMIT %d",
            $limit
        ), ARRAY_A );
    }

    private static function get_ip(): string {
        // REMOTE_ADDR is authoritative — X-Forwarded-For is client-controlled and can be spoofed
        if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }
        return '0.0.0.0';
    }
}
