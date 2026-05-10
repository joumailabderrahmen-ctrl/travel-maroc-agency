<?php
defined( 'ABSPATH' ) || exit;

class TMB_Rest_Api {

    const NS = 'tma/v1';

    public static function init() {
        add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
    }

    public static function register_routes() {

        // GET /tma/v1/offres — liste des produits avec meta TMA
        register_rest_route( self::NS, '/offres', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_offres' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'cat'   => [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ],
                'limit' => [ 'type' => 'integer','default' => 12, 'minimum' => 1, 'maximum' => 50 ],
                'page'  => [ 'type' => 'integer','default' => 1,  'minimum' => 1 ],
            ],
        ] );

        // GET /tma/v1/offres/{id}
        register_rest_route( self::NS, '/offres/(?P<id>\d+)', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_offre' ],
            'permission_callback' => '__return_true',
            'args'                => [ 'id' => [ 'type' => 'integer', 'validate_callback' => fn($v) => is_numeric($v) ] ],
        ] );

        // GET /tma/v1/destinations — liste des destinations
        register_rest_route( self::NS, '/destinations', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_destinations' ],
            'permission_callback' => '__return_true',
        ] );

        // GET /tma/v1/profil — profil fidélité du client connecté
        register_rest_route( self::NS, '/profil', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_profil' ],
            'permission_callback' => [ __CLASS__, 'is_logged_in' ],
        ] );

        // GET /tma/v1/profil/points — historique points
        register_rest_route( self::NS, '/profil/points', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_points_historique' ],
            'permission_callback' => [ __CLASS__, 'is_logged_in' ],
        ] );

        // GET /tma/v1/profil/notifications
        register_rest_route( self::NS, '/profil/notifications', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_notifications' ],
            'permission_callback' => [ __CLASS__, 'is_logged_in' ],
        ] );

        // POST /tma/v1/profil/notifications/{id}/lue
        register_rest_route( self::NS, '/profil/notifications/(?P<id>\d+)/lue', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ __CLASS__, 'marquer_notification_lue' ],
            'permission_callback' => [ __CLASS__, 'is_logged_in' ],
        ] );

        // GET /tma/v1/dashboard — stats admin (admin uniquement)
        register_rest_route( self::NS, '/dashboard', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_dashboard_stats' ],
            'permission_callback' => [ __CLASS__, 'is_admin_user' ],
        ] );

        // GET /tma/v1/logs — journal admin
        register_rest_route( self::NS, '/logs', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_logs' ],
            'permission_callback' => [ __CLASS__, 'is_admin_user' ],
        ] );

        // GET /tma/v1/geo/proches — destinations à proximité (Haversine)
        register_rest_route( self::NS, '/geo/proches', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ __CLASS__, 'get_destinations_proches' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'lat'    => [ 'type' => 'number', 'required' => true ],
                'lng'    => [ 'type' => 'number', 'required' => true ],
                'rayon'  => [ 'type' => 'number', 'default'  => 500 ],
            ],
        ] );
    }

    // ── Endpoints ───────────────────────────────────────────

    public static function get_offres( WP_REST_Request $req ): WP_REST_Response {
        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $req->get_param('limit'),
            'paged'          => $req->get_param('page'),
        ];
        $cat = $req->get_param('cat');
        if ( $cat ) {
            $args['tax_query'] = [[ 'taxonomy'=>'product_cat','field'=>'slug','terms'=>$cat ]];
        }
        $query    = new WP_Query( $args );
        $products = [];
        foreach ( $query->posts as $post ) {
            $products[] = self::format_offre( $post->ID );
        }
        wp_reset_postdata();

        return new WP_REST_Response([
            'data'  => $products,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
        ], 200);
    }

    public static function get_offre( WP_REST_Request $req ): WP_REST_Response {
        $id = (int) $req->get_param('id');
        if ( get_post_type($id) !== 'product' ) {
            return new WP_REST_Response([ 'message' => 'Offre introuvable' ], 404);
        }
        return new WP_REST_Response( self::format_offre($id), 200 );
    }

    public static function get_destinations( WP_REST_Request $req ): WP_REST_Response {
        global $wpdb;
        $dests = $wpdb->get_results(
            "SELECT d.*, l.latitude, l.longitude, l.code_pays
             FROM {$wpdb->prefix}tma_destination d
             JOIN {$wpdb->prefix}tma_localisation l ON l.id = d.localisation_id
             WHERE d.statut = 'ACTIF'
             ORDER BY d.est_populaire DESC, d.nom ASC"
        );
        return new WP_REST_Response( $dests, 200 );
    }

    public static function get_profil( WP_REST_Request $req ): WP_REST_Response {
        $profile = TMB_Fidelite::get_profile( get_current_user_id() );
        if ( ! $profile ) {
            return new WP_REST_Response([ 'message' => 'Profil non trouvé' ], 404);
        }
        return new WP_REST_Response([
            'points_solde'   => (int) $profile->points_fidelite_solde,
            'points_cumules' => (int) $profile->points_cumules_total,
            'type_client'    => $profile->type_libelle,
            'taux_remise'    => (float) $profile->taux_remise,
            'couleur_badge'  => $profile->couleur_badge,
        ], 200);
    }

    public static function get_points_historique( WP_REST_Request $req ): WP_REST_Response {
        $data = TMB_Fidelite::get_historique( get_current_user_id(), 20 );
        return new WP_REST_Response( $data, 200 );
    }

    public static function get_notifications( WP_REST_Request $req ): WP_REST_Response {
        $profile = TMB_Fidelite::get_profile( get_current_user_id() );
        if ( ! $profile ) return new WP_REST_Response( [], 200 );
        $data = TMB_Notifications::get_for_client( (int) $profile->id );
        return new WP_REST_Response( $data, 200 );
    }

    public static function marquer_notification_lue( WP_REST_Request $req ): WP_REST_Response {
        $profile = TMB_Fidelite::get_profile( get_current_user_id() );
        if ( ! $profile ) return new WP_REST_Response([ 'message' => 'Non autorisé' ], 403);
        $ok = TMB_Notifications::marquer_lue( (int) $req->get_param('id'), (int) $profile->id );
        return new WP_REST_Response([ 'success' => $ok ], $ok ? 200 : 404);
    }

    public static function get_dashboard_stats( WP_REST_Request $req ): WP_REST_Response {
        global $wpdb;

        $today   = gmdate('Y-m-d');
        $month   = gmdate('Y-m');

        // Revenus & commandes (requêtes directes — pas de STATS_CACHE)
        $rev_total = $wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON p.ID = pm.post_id
            WHERE pm.meta_key='_order_total' AND p.post_status='wc-completed'");

        $rev_mois  = $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(meta_value) FROM {$wpdb->postmeta} pm
             JOIN {$wpdb->posts} p ON p.ID = pm.post_id
             WHERE pm.meta_key='_order_total' AND p.post_status='wc-completed'
             AND DATE_FORMAT(p.post_date, '%%Y-%%m') = %s", $month
        ) );

        $nb_commandes = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='shop_order' AND post_status NOT IN ('wc-cancelled','trash')");
        $nb_clients   = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tma_client_profile");
        $pts_distribues = $wpdb->get_var("SELECT SUM(points) FROM {$wpdb->prefix}tma_historique_points WHERE operation='GAIN'");

        $offres_top = $wpdb->get_results("
            SELECT p.post_title, COUNT(*) AS nb_reservations
            FROM {$wpdb->prefix}woocommerce_order_items oi
            JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
            JOIN {$wpdb->posts} p ON p.ID = oim.meta_value
            WHERE oi.order_item_type = 'line_item' AND oim.meta_key = '_product_id'
            GROUP BY p.ID ORDER BY nb_reservations DESC LIMIT 5
        ", ARRAY_A);

        $notifs_attente = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tma_notification WHERE statut='EN_ATTENTE'");

        $clients_par_type = $wpdb->get_results("
            SELECT t.libelle, t.couleur_badge, COUNT(p.id) AS nb
            FROM {$wpdb->prefix}tma_type_client t
            LEFT JOIN {$wpdb->prefix}tma_client_profile p ON p.type_client_id = t.id
            GROUP BY t.id ORDER BY t.ordre_affichage
        ", ARRAY_A);

        return new WP_REST_Response([
            'revenus'   => [ 'total' => (float)($rev_total ?? 0), 'mois' => (float)($rev_mois ?? 0) ],
            'commandes' => (int) $nb_commandes,
            'clients'   => [ 'total' => (int) $nb_clients, 'par_type' => $clients_par_type ],
            'points_distribues' => (int)($pts_distribues ?? 0),
            'offres_top'        => $offres_top,
            'notifications_en_attente' => (int) $notifs_attente,
            'generated_at'      => current_time('mysql'),
        ], 200);
    }

    public static function get_logs( WP_REST_Request $req ): WP_REST_Response {
        $logs = TMB_Log_Admin::get_recent( 50 );
        return new WP_REST_Response( $logs, 200 );
    }

    public static function get_destinations_proches( WP_REST_Request $req ): WP_REST_Response {
        global $wpdb;
        $lat   = (float) $req->get_param('lat');
        $lng   = (float) $req->get_param('lng');
        $rayon = (float) $req->get_param('rayon');

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT d.nom, d.pays, l.latitude, l.longitude,
             ROUND(6371 * ACOS(
                 COS(RADIANS(%f)) * COS(RADIANS(l.latitude)) *
                 COS(RADIANS(l.longitude) - RADIANS(%f)) +
                 SIN(RADIANS(%f)) * SIN(RADIANS(l.latitude))
             ), 0) AS distance_km
             FROM {$wpdb->prefix}tma_destination d
             JOIN {$wpdb->prefix}tma_localisation l ON l.id = d.localisation_id
             HAVING distance_km <= %f
             ORDER BY distance_km ASC",
            $lat, $lng, $lat, $rayon
        ), ARRAY_A);

        return new WP_REST_Response( $results, 200 );
    }

    // ── Helpers permission ──────────────────────────────────
    public static function is_logged_in(): bool   { return is_user_logged_in(); }
    public static function is_admin_user(): bool  { return current_user_can( 'manage_woocommerce' ); }

    // ── Format offre ────────────────────────────────────────
    private static function format_offre( int $id ): array {
        $product = wc_get_product( $id );
        if ( ! $product ) return [];
        $cats = get_the_terms( $id, 'product_cat' );
        return [
            'id'          => $id,
            'nom'         => $product->get_name(),
            'slug'        => $product->get_slug(),
            'prix'        => (float) $product->get_price(),
            'prix_promo'  => $product->is_on_sale() ? (float)$product->get_sale_price() : null,
            'description' => wp_strip_all_tags( $product->get_description() ),
            'categorie'   => $cats ? $cats[0]->name : null,
            'destination' => get_post_meta( $id, '_tma_destination', true ),
            'duree'       => get_post_meta( $id, '_tma_duration',    true ),
            'points'      => (int) get_post_meta( $id, '_tma_points', true ),
            'image'       => get_the_post_thumbnail_url( $id, 'tma-card' ) ?: null,
            'url'         => get_permalink( $id ),
        ];
    }
}
