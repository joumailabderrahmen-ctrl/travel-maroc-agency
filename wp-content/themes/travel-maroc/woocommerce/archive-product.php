<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<?php
// Titre de section dynamique
$queried = get_queried_object();
$is_cat  = is_product_category();
$title   = $is_cat ? $queried->name : 'Toutes nos offres';
$desc    = $is_cat ? category_description() : 'Circuits, séjours et excursions au Maroc et à l\'international.';
?>

<!-- ── Bannière section ─────────────────────────────────────── -->
<section class="tma-shop-hero">
    <div class="container">
        <h1 class="tma-shop-hero__title"><?php echo esc_html( $title ); ?></h1>
        <?php if ( $desc ) : ?>
            <p class="tma-shop-hero__desc"><?php echo wp_kses_post( $desc ); ?></p>
        <?php endif; ?>
    </div>
</section>

<!-- ── Filtres catégories (toutes les pages archive) ────────── -->
<div class="tma-shop-filters">
    <div class="container">
        <?php
        $shop_url    = get_permalink( wc_get_page_id('shop') );
        $current_slug = $is_cat ? $queried->slug : get_query_var('product_cat');
        $cats = get_terms([ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ]);
        $icons = [ 'voyages-nationaux' => '🇲🇦', 'voyages-internationaux' => '✈️', 'excursions' => '🏕️' ];
        ?>
        <a href="<?php echo esc_url( $shop_url ); ?>"
           class="tma-filter-btn <?php echo ! $current_slug ? 'active' : ''; ?>">
            🌍 Tout voir
        </a>
        <?php foreach ( $cats as $cat ) :
            $icon = $icons[ $cat->slug ] ?? '📍';
        ?>
            <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
               class="tma-filter-btn <?php echo $current_slug === $cat->slug ? 'active' : ''; ?>">
                <?php echo $icon; ?> <?php echo esc_html( $cat->name ); ?>
                <span class="tma-filter-count"><?php echo (int) $cat->count; ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── Résultats + tri ──────────────────────────────────────── -->
<div class="tma-shop-wrap">
    <div class="container">

        <?php if ( woocommerce_product_loop() ) : ?>

            <div class="tma-shop-bar">
                <?php woocommerce_result_count(); ?>
                <?php woocommerce_catalog_ordering(); ?>
            </div>

            <?php woocommerce_product_loop_start(); ?>
                <?php woocommerce_product_subcategories(); ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php wc_get_template_part( 'content', 'product' ); ?>
                <?php endwhile; ?>
            <?php woocommerce_product_loop_end(); ?>

            <?php
            woocommerce_pagination();
            wc_reset_loop();
            ?>

        <?php else : ?>
            <div class="tma-shop-empty">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <circle cx="32" cy="32" r="30" stroke="#e5e7eb" stroke-width="2"/>
                    <path d="M22 32 Q32 20 42 32 Q32 44 22 32Z" fill="#f3f4f6"/>
                </svg>
                <p>Aucune offre disponible pour le moment.</p>
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id('shop') ) ); ?>" class="btn btn-primary">
                    Voir toutes les offres
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php get_footer(); ?>
