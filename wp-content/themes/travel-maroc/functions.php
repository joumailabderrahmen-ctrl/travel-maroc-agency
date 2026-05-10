<?php
defined( 'ABSPATH' ) || exit;

// ── Internationalisation (i18n) ────────────────────────────────
add_action( 'after_setup_theme', function() {
    load_theme_textdomain( 'travel-maroc', get_template_directory() . '/languages' );
} );

// ── Support thème ──────────────────────────────────────────────
function tma_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form','comment-form','comment-list','gallery','caption' ] );
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
    add_theme_support( 'custom-logo', [
        'height' => 60, 'width' => 200, 'flex-height' => true, 'flex-width' => true,
    ]);

    register_nav_menus([
        'primary' => __( 'Menu Principal', 'travel-maroc' ),
        'footer'  => __( 'Menu Footer',    'travel-maroc' ),
    ]);

    add_image_size( 'tma-card',   600, 400, true );
    add_image_size( 'tma-hero',  1400, 600, true );
    add_image_size( 'tma-thumb',  300, 200, true );
}
add_action( 'after_setup_theme', 'tma_setup' );

// ── Enqueue assets ─────────────────────────────────────────────
function tma_enqueue() {
    $dir = get_template_directory();
    wp_enqueue_style(  'tma-style', get_stylesheet_uri(), [], filemtime( $dir . '/style.css' ) );
    wp_enqueue_style(  'tma-logo',  get_template_directory_uri() . '/assets/css/logo-animation.css', [ 'tma-style' ], filemtime( $dir . '/assets/css/logo-animation.css' ) );
    wp_enqueue_style(  'tma-main',  get_template_directory_uri() . '/assets/css/main.css', [ 'tma-style', 'tma-logo' ], filemtime( $dir . '/assets/css/main.css' ) );
    // GSAP (CDN) — standard industrie pour animations SVG
    wp_enqueue_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js', [], '3.12.5', true );
    wp_enqueue_script( 'tma-logo-anim', get_template_directory_uri() . '/assets/js/logo-animation.js', [ 'gsap' ], filemtime( $dir . '/assets/js/logo-animation.js' ), true );
    wp_enqueue_script( 'tma-main',  get_template_directory_uri() . '/assets/js/main.js', [ 'jquery' ], filemtime( $dir . '/assets/js/main.js' ), true );

    wp_localize_script( 'tma-main', 'tmaData', [
        'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'tma_nonce' ),
        'cartUrl'  => wc_get_cart_url(),
        'currency' => get_woocommerce_currency_symbol(),
    ]);

    if ( is_singular() ) wp_enqueue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'tma_enqueue' );

// ── Contenu du menu — icône panier ─────────────────────────────
function tma_cart_count_fragment( $fragments ) {
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    $fragments['.cart-count'] = '<span class="cart-count">' . $count . '</span>';
    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'tma_cart_count_fragment' );

// ── Supprimer la sidebar WooCommerce par défaut ─────────────────
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// ── Personnaliser le nombre de produits par page ────────────────
add_filter( 'loop_shop_per_page', fn() => 12 );

// ── Colonnes grille produits ────────────────────────────────────
add_filter( 'loop_shop_columns', fn() => 3 );

// ── Métadonnées TMA dans la fiche produit ──────────────────────
function tma_product_meta() {
    global $post;
    $duration = get_post_meta( $post->ID, '_tma_duration',    true );
    $dest     = get_post_meta( $post->ID, '_tma_destination', true );
    $points   = get_post_meta( $post->ID, '_tma_points',      true );
    if ( ! $duration && ! $dest ) return;
    echo '<div class="tma-product-meta">';
    if ( $dest )     echo '<span class="tma-meta-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg> ' . esc_html( $dest ) . '</span>';
    if ( $duration ) echo '<span class="tma-meta-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> ' . esc_html( $duration ) . '</span>';
    if ( $points )   echo '<span class="tma-meta-item tma-points"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> +' . esc_html( $points ) . ' points</span>';
    echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'tma_product_meta', 25 );
add_action( 'woocommerce_after_shop_loop_item_title', 'tma_product_meta', 5 );

// ── Symbole monnaie MAD en texte lisible ───────────────────────
add_filter( 'woocommerce_currency_symbol', function( $symbol, $currency ) {
    return $currency === 'MAD' ? 'MAD' : $symbol;
}, 10, 2 );

// ── Format prix : 1 200 MAD (sans décimales inutiles) ──────────
add_filter( 'woocommerce_price_trim_zeros', '__return_true' );

// ── Texte "Ajouter au panier" personnalisé ──────────────────────
add_filter( 'woocommerce_product_add_to_cart_text',   fn() => 'Réserver maintenant' );
add_filter( 'woocommerce_product_single_add_to_cart_text', fn() => 'Réserver maintenant' );

// ── Breadcrumb WooCommerce personnalisé ─────────────────────────
add_filter( 'woocommerce_breadcrumb_defaults', function( $args ) {
    $args['delimiter']   = ' &rsaquo; ';
    $args['home']        = 'Accueil';
    $args['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="Breadcrumb">';
    $args['wrap_after']  = '</nav>';
    return $args;
});

// ── Widgets ────────────────────────────────────────────────────
function tma_register_widgets() {
    register_sidebar([
        'name'          => 'Sidebar Boutique',
        'id'            => 'shop-sidebar',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ]);
    register_sidebar([
        'name'          => 'Footer Col 1',
        'id'            => 'footer-1',
        'before_widget' => '<div class="footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ]);
}
add_action( 'widgets_init', 'tma_register_widgets' );

// ── Shortcodes utiles ──────────────────────────────────────────
add_shortcode( 'tma_offers', function( $atts ) {
    $a = shortcode_atts([ 'cat' => '', 'limit' => 6 ], $atts );
    ob_start();
    $args = [
        'post_type'      => 'product',
        'posts_per_page' => (int) $a['limit'],
        'post_status'    => 'publish',
    ];
    if ( $a['cat'] ) {
        $args['tax_query'] = [[ 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $a['cat'] ]];
    }
    $products = new WP_Query( $args );
    if ( $products->have_posts() ) :
        echo '<div class="grid-3">';
        while ( $products->have_posts() ) : $products->the_post();
            get_template_part( 'template-parts/offer-card' );
        endwhile;
        echo '</div>';
    endif;
    wp_reset_postdata();
    return ob_get_clean();
});

// ── Données structurées JSON-LD produit voyage ─────────────────
function tma_product_schema() {
    if ( ! is_product() ) return;
    global $post;
    $product = wc_get_product( $post->ID );
    if ( ! $product ) return;
    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'TouristTrip',
        'name'        => $product->get_name(),
        'description' => wp_strip_all_tags( $product->get_description() ),
        'offers'      => [
            '@type'         => 'Offer',
            'price'         => $product->get_price(),
            'priceCurrency' => 'MAD',
            'availability'  => 'https://schema.org/InStock',
        ],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE ) . '</script>';
}
add_action( 'wp_head', 'tma_product_schema' );

// About + Contact sont gérés par page-about.php et page-contact.php

// ── Traitement formulaire contact ──────────────────────────────
add_action( 'wp_ajax_tma_contact',        'tma_handle_contact' );
add_action( 'wp_ajax_nopriv_tma_contact', 'tma_handle_contact' );
function tma_handle_contact() {
    check_ajax_referer( 'tma_nonce', 'nonce' );
    $name    = sanitize_text_field( $_POST['name']    ?? '' );
    $email   = sanitize_email(      $_POST['email']   ?? '' );
    $phone   = sanitize_text_field( $_POST['phone']   ?? '' );
    $message = sanitize_textarea_field( $_POST['message'] ?? '' );
    if ( ! $name || ! is_email( $email ) || ! $message ) {
        wp_send_json_error( 'Champs invalides' );
    }
    $to      = get_option( 'admin_email' );
    $subject = "Contact TMA — $name";
    $phone_line = $phone ? "\nTéléphone : $phone" : '';
    $body    = "Nom : $name\nEmail : $email$phone_line\n\nMessage :\n$message";
    $sent    = wp_mail( $to, $subject, $body );
    $sent ? wp_send_json_success( 'Message envoyé' ) : wp_send_json_error( 'Erreur envoi' );
}

// Note : fidélité, profil client et montée de palier sont gérés
// exclusivement par TMB_Fidelite (plugin travel-maroc-booking).

// ── Menu Mon Compte WooCommerce ───────────────────────────────
add_filter( 'woocommerce_account_menu_items', function( $items ) {
    $custom = [];
    $custom['dashboard']       = 'Tableau de bord';
    $custom['orders']          = 'Mes réservations';
    $custom['edit-address']    = 'Mes adresses';
    $custom['edit-account']    = 'Mon profil';
    $custom['customer-logout'] = 'Déconnexion';
    return $custom;
} );

// ── Réécriture label "Produit" → "Offre" dans les commandes ──
add_filter( 'woocommerce_order_item_name', function( $name ) {
    return $name;
} );

// ── Image taille pour les pages Mon Compte ────────────────────
add_image_size( 'tma-account', 80, 80, true );

// ── Couleurs emails WooCommerce — palette TMA ─────────────────
add_filter( 'woocommerce_email_background_color',      fn() => '#f0f4f8', 20 );
add_filter( 'woocommerce_email_body_background_color', fn() => '#ffffff', 20 );
add_filter( 'woocommerce_email_base_color',            fn() => '#0d2b55', 20 );
add_filter( 'woocommerce_email_text_color',            fn() => '#374151', 20 );
add_filter( 'woocommerce_email_footer_text_color',     fn() => '#94a3b8', 20 );

// ── SEO — Open Graph & Twitter Cards ──────────────────────────
function tma_og_meta(): void {
    global $post;
    $site_name  = get_bloginfo('name');
    $site_url   = home_url('/');
    $default_desc = get_bloginfo('description');
    $default_img  = get_option('tma_hero_bg_url', '');

    if ( is_singular('product') && $post ) {
        $product  = wc_get_product( $post->ID );
        $title    = $product ? $product->get_name() : get_the_title();
        $desc     = $product ? wp_trim_words( wp_strip_all_tags( $product->get_description() ), 25 ) : $default_desc;
        $img      = get_the_post_thumbnail_url( $post->ID, 'large' ) ?: $default_img;
        $url      = get_permalink( $post->ID );
        $price    = $product ? $product->get_price() : '';
        $dest     = get_post_meta( $post->ID, '_tma_destination', true );
        if ( $dest ) $desc = $dest . ' — ' . $desc;
    } elseif ( is_product_category() ) {
        $cat   = get_queried_object();
        $title = $cat->name . ' — ' . $site_name;
        $desc  = $cat->description ?: $default_desc;
        $img   = $default_img;
        $url   = get_term_link( $cat );
    } else {
        $title = is_front_page() ? $site_name : get_the_title() . ' — ' . $site_name;
        $desc  = $default_desc;
        $img   = $default_img;
        $url   = $site_url;
    }

    $desc = esc_attr( wp_strip_all_tags( $desc ) );
    $title = esc_attr( $title );
    $url   = esc_url( $url );
    $img   = esc_url( $img );

    echo "\n<!-- Open Graph -->\n";
    echo '<meta property="og:type"        content="website">' . "\n";
    echo '<meta property="og:site_name"   content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta property="og:title"       content="' . $title . '">' . "\n";
    echo '<meta property="og:description" content="' . $desc  . '">' . "\n";
    echo '<meta property="og:url"         content="' . $url   . '">' . "\n";
    if ( $img ) echo '<meta property="og:image" content="' . $img . '">' . "\n";
    echo '<meta name="twitter:card"        content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title"       content="' . $title . '">' . "\n";
    echo '<meta name="twitter:description" content="' . $desc  . '">' . "\n";
    if ( $img ) echo '<meta name="twitter:image" content="' . $img . '">' . "\n";
    echo "<!-- /Open Graph -->\n";
}

// ── Google Analytics GA4 ───────────────────────────────────────
add_action('wp_head', function() {
    $ga_id = get_option('tma_analytics_id', '');
    if ( ! $ga_id ) return;
    $ga_id = esc_js( sanitize_text_field($ga_id) );
    echo "\n<!-- Google Analytics GA4 -->\n";
    echo '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $ga_id . '"></script>' . "\n";
    echo '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config","' . $ga_id . '");</script>' . "\n";
    echo "<!-- /Google Analytics -->\n";
}, 1);

// ── Avis clients WooCommerce ───────────────────────────────────
add_filter( 'woocommerce_product_tabs', function( $tabs ) {
    if ( isset( $tabs['reviews'] ) ) {
        $tabs['reviews']['title']    = 'Avis voyageurs';
        $tabs['reviews']['priority'] = 20;
    }
    return $tabs;
} );
// Forcer l'activation des avis sur les produits (si désactivé globalement)
add_filter( 'woocommerce_reviews_enabled',           '__return_true' );
add_filter( 'pre_option_woocommerce_enable_reviews', '__return_true' );

// ── Sitemap WordPress natif — déclaration des types ───────────
add_filter( 'wp_sitemaps_post_types', function( $post_types ) {
    // S'assurer que les produits sont dans le sitemap
    if ( ! isset( $post_types['product'] ) ) {
        $post_types['product'] = get_post_type_object('product');
    }
    return $post_types;
} );
add_filter( 'wp_sitemaps_taxonomies', function( $taxonomies ) {
    $taxonomies['product_cat'] = get_taxonomy('product_cat');
    return $taxonomies;
} );

// ── RTL : support arabe via option langue ─────────────────────
add_filter( 'body_class', function( $classes ) {
    if ( get_bloginfo('text_direction') === 'rtl' ) {
        $classes[] = 'tma-rtl';
    }
    return $classes;
} );

// ── Sélecteur de langue (Polylang + WPML) ────────────────────
function tma_language_switcher(): void {
    // ── Polylang ───────────────────────────────────────────────
    if ( function_exists('pll_the_languages') ) {
        $langs = pll_the_languages(['raw' => 1]);
        if ( empty($langs) || count($langs) < 2 ) return;
        echo '<nav class="tma-lang-switcher" aria-label="Langue">';
        foreach ( $langs as $lang ) {
            $active = $lang['current_lang'] ? ' tma-lang--active' : '';
            echo '<a href="' . esc_url($lang['url']) . '"'
               . ' class="tma-lang-btn' . $active . '"'
               . ' hreflang="' . esc_attr($lang['slug']) . '"'
               . ' title="' . esc_attr($lang['name']) . '">';
            if ( ! empty($lang['flag']) ) {
                echo $lang['flag']; // flag est déjà un <img> sanitisé par Polylang
            }
            echo ' <span>' . esc_html(strtoupper($lang['slug'])) . '</span>';
            echo '</a>';
        }
        echo '</nav>';
        return;
    }

    // ── WPML (fallback) ────────────────────────────────────────
    if ( has_filter('wpml_active_languages') ) {
        $langs = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);
        if ( empty($langs) || count($langs) < 2 ) return;
        echo '<nav class="tma-lang-switcher" aria-label="Langue">';
        foreach ( $langs as $lang ) {
            $active = $lang['active'] ? ' tma-lang--active' : '';
            echo '<a href="' . esc_url($lang['url']) . '" class="tma-lang-btn' . $active . '" hreflang="' . esc_attr($lang['language_code']) . '">';
            echo '<img src="' . esc_url($lang['country_flag_url']) . '" alt="" width="18" height="12">';
            echo ' <span>' . esc_html(strtoupper($lang['language_code'])) . '</span>';
            echo '</a>';
        }
        echo '</nav>';
    }
}

// ── Polylang — enregistrement des strings traduisibles ────────
add_action( 'init', function() {
    $strings = [
        'Réserver maintenant'    => 'Réserver maintenant',
        'Connexion'              => 'Connexion',
        'Mon compte'             => 'Mon compte',
        'Déconnexion'            => 'Déconnexion',
        'Voir toutes les offres' => 'Voir toutes les offres',
        'Tout voir'              => 'Tout voir',
        'Nos offres'             => 'Nos offres',
        'Contactez-nous'         => 'Contactez-nous',
        'Points disponibles'     => 'Points disponibles',
        'Tableau de bord'        => 'Tableau de bord',
        'Mes réservations'       => 'Mes réservations',
    ];

    // Polylang
    if ( function_exists('pll_register_string') ) {
        foreach ( $strings as $name => $value ) {
            pll_register_string( $name, $value, 'Travel Maroc Thème' );
        }
        // Options traduisibles via Polylang String Translation
        $opts = [
            'tma_about_text'      => get_option('tma_about_text', ''),
            'tma_contact_address' => get_option('tma_contact_address', ''),
        ];
        foreach ( $opts as $name => $value ) {
            if ( $value ) pll_register_string( $name, $value, 'Travel Maroc Options', true );
        }
        return;
    }

    // WPML fallback
    if ( function_exists('icl_register_string') ) {
        foreach ( $strings as $name => $value ) {
            icl_register_string( 'travel-maroc-theme', $name, $value );
        }
    }
} );

// ── Lire une option dans la langue courante ───────────────────
function tma_get_option_translated( string $key, string $default = '' ): string {
    $value = get_option( $key, $default );
    if ( function_exists('pll__') ) {
        return pll__( $value ) ?: $value;
    }
    if ( function_exists('icl_t') ) {
        return icl_t( 'travel-maroc-options', $key, $value );
    }
    return $value;
}

// ── Polylang — synchroniser les meta TMA entre traductions ────
add_filter( 'pll_copy_post_metas', function( array $keys, bool $sync ): array {
    // copy = même valeur dans toutes les langues (IDs, nombres)
    $copy_keys = [ '_tma_points', '_tma_guide_id', '_tma_hotel_id', '_tma_transport_id' ];
    return array_merge( $keys, $copy_keys );
}, 10, 2 );

// ── Polylang — déclaration des post types WooCommerce ─────────
add_filter( 'pll_get_post_types', function( array $types ): array {
    $types['product']           = 'product';
    $types['product_variation'] = 'product_variation';
    return $types;
} );
add_filter( 'pll_get_taxonomies', function( array $taxonomies ): array {
    $taxonomies['product_cat'] = 'product_cat';
    $taxonomies['product_tag'] = 'product_tag';
    return $taxonomies;
} );
