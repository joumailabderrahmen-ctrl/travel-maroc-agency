<?php
defined( 'ABSPATH' ) || exit;

class TMB_Roles {

    // Caps TMA partagées
    const CAPS = [
        'tma_view_dashboard',
        'tma_view_clients',
        'tma_view_orders',
        'tma_manage_bookings',
    ];

    public static function init() {
        // Rien à faire à chaque requête — les rôles persistent en DB
    }

    public static function add_roles(): void {
        if ( ! get_role('tma_agent') ) {
            add_role( 'tma_agent', 'Agent TMA', array_merge(
                [ 'read' => true ],
                array_fill_keys( self::CAPS, true )
            ));
        }

        // Donner les caps TMA au shop_manager
        $shop_mgr = get_role('shop_manager');
        if ( $shop_mgr ) {
            foreach ( self::CAPS as $cap ) {
                $shop_mgr->add_cap( $cap );
            }
        }
    }

    public static function remove_roles(): void {
        remove_role('tma_agent');

        $shop_mgr = get_role('shop_manager');
        if ( $shop_mgr ) {
            foreach ( self::CAPS as $cap ) {
                $shop_mgr->remove_cap( $cap );
            }
        }
    }

    /**
     * Créer le compte shop_manager s'il n'existe pas encore.
     * Retourne ['created' => bool, 'user_id' => int, 'password' => string|null]
     */
    public static function ensure_shop_manager(): array {
        $login = 'shop_manager_tma';
        $existing = get_user_by('login', $login);
        if ( $existing ) {
            return [ 'created' => false, 'user_id' => $existing->ID, 'password' => null ];
        }
        $password = wp_generate_password( 16, true, false );
        $user_id  = wp_create_user( $login, $password, 'manager@travelmaroc.local' );
        if ( is_wp_error($user_id) ) {
            return [ 'created' => false, 'user_id' => 0, 'password' => null ];
        }
        $user = new WP_User( $user_id );
        $user->set_role('shop_manager');
        return [ 'created' => true, 'user_id' => $user_id, 'password' => $password ];
    }
}
