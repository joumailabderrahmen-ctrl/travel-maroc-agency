<?php
/**
 * Plugin Name:  Travel Maroc Booking
 * Plugin URI:   http://localhost/boutique
 * Description:  Plugin métier Travel Maroc Agency — Fidélité, Notifications, Audit (LOG_ADMIN), REST API, Dashboard admin.
 * Version:      1.0.0
 * Author:       EST Dakhla CDL 2025/2026
 * Text Domain:  travel-maroc-booking
 * Requires WP:  6.0
 * Requires PHP: 8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'TMB_VERSION',  '1.0.0' );
define( 'TMB_DIR',      plugin_dir_path( __FILE__ ) );
define( 'TMB_URL',      plugin_dir_url( __FILE__ ) );
define( 'TMB_PREFIX',   'wp_tma_' );

// Chargement des classes
require_once TMB_DIR . 'includes/class-tmb-activator.php';
require_once TMB_DIR . 'includes/class-tmb-fidelite.php';
require_once TMB_DIR . 'includes/class-tmb-log-admin.php';
require_once TMB_DIR . 'includes/class-tmb-notifications.php';
require_once TMB_DIR . 'includes/class-tmb-rest-api.php';
require_once TMB_DIR . 'includes/class-tmb-checkout.php';
require_once TMB_DIR . 'includes/class-tmb-availability.php';
require_once TMB_DIR . 'includes/class-tmb-roles.php';
require_once TMB_DIR . 'admin/class-tmb-admin.php';
require_once TMB_DIR . 'admin/class-tmb-resources-admin.php';
require_once TMB_DIR . 'admin/class-tmb-product-meta.php';
require_once TMB_DIR . 'admin/class-tmb-settings.php';
require_once TMB_DIR . 'public/class-tmb-public.php';

register_activation_hook(   __FILE__, [ 'TMB_Activator', 'activate'   ] );
register_deactivation_hook( __FILE__, [ 'TMB_Activator', 'deactivate' ] );
register_uninstall_hook(    __FILE__, [ 'TMB_Activator', 'uninstall'  ] );

function tmb_init() {
    // S'assurer que le cron est planifié même sans désactivation/réactivation
    if ( ! wp_next_scheduled('tma_cron_expirer_points') ) {
        wp_schedule_event( time(), 'daily', 'tma_cron_expirer_points' );
    }
    TMB_Fidelite::init();
    TMB_Log_Admin::init();
    TMB_Notifications::init();
    TMB_Rest_Api::init();
    TMB_Admin::init();
    TMB_Resources_Admin::init();
    TMB_Product_Meta::init();
    TMB_Settings::init();
    TMB_Checkout::init();
    TMB_Availability::init();
    TMB_Roles::init();
    TMB_Public::init();
}
add_action( 'plugins_loaded', 'tmb_init' );
