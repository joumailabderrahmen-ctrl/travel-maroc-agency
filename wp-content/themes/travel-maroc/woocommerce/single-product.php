<?php
defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
    the_post();
    global $product;

    $destination  = get_post_meta( $product->get_id(), '_tma_destination',  true );
    $duree        = get_post_meta( $product->get_id(), '_tma_duration',     true );
    $points       = (int) get_post_meta( $product->get_id(), '_tma_points', true );
    $guide_id     = (int) get_post_meta( $product->get_id(), '_tma_guide_id',     true );
    $hotel_id     = (int) get_post_meta( $product->get_id(), '_tma_hotel_id',     true );
    $transport_id = (int) get_post_meta( $product->get_id(), '_tma_transport_id', true );

    global $wpdb;
    $guide     = $guide_id     ? $wpdb->get_row( $wpdb->prepare( "SELECT CONCAT(prenom,' ',nom) AS label, telephone FROM {$wpdb->prefix}tma_guide     WHERE id=%d", $guide_id ) )     : null;
    $hotel     = $hotel_id     ? $wpdb->get_row( $wpdb->prepare( "SELECT nom AS label, categorie_etoiles FROM {$wpdb->prefix}tma_hotel     WHERE id=%d", $hotel_id ) )     : null;
    $transport = $transport_id ? $wpdb->get_row( $wpdb->prepare(
        "SELECT CONCAT(type,' — ',(SELECT nom FROM {$wpdb->prefix}tma_localisation WHERE id=localisation_depart_id),' → ',(SELECT nom FROM {$wpdb->prefix}tma_localisation WHERE id=localisation_arrivee_id)) AS label, compagnie
         FROM {$wpdb->prefix}tma_transport WHERE id=%d", $transport_id ) ) : null;
    $image_url   = get_the_post_thumbnail_url( $product->get_id(), 'tma-hero' )
                   ?: get_the_post_thumbnail_url( $product->get_id(), 'large' );
    $cats        = get_the_terms( $product->get_id(), 'product_cat' );
    $cat_name    = ( $cats && ! is_wp_error($cats) ) ? $cats[0]->name : '';
    $cat_url     = ( $cats && ! is_wp_error($cats) ) ? get_term_link( $cats[0] ) : '';
    ?>

    <!-- ── Breadcrumb ──────────────────────────────────────── -->
    <div class="tma-single-breadcrumb">
        <div class="container">
            <?php woocommerce_breadcrumb(); ?>
        </div>
    </div>

    <!-- ── Hero image ─────────────────────────────────────── -->
    <div class="tma-single-hero <?php echo $image_url ? '' : 'tma-single-hero--placeholder'; ?>"
         <?php if ( $image_url ) : ?>
         style="background-image:url('<?php echo esc_url( $image_url ); ?>')"
         <?php endif; ?>>
        <div class="tma-single-hero__overlay">
            <div class="container">
                <?php if ( $cat_name ) : ?>
                    <a href="<?php echo esc_url( $cat_url ); ?>" class="tma-single-cat">
                        <?php echo esc_html( $cat_name ); ?>
                    </a>
                <?php endif; ?>
                <h1 class="tma-single-title"><?php echo esc_html( $product->get_name() ); ?></h1>
                <div class="tma-single-meta">
                    <?php if ( $destination ) : ?>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                            <circle cx="12" cy="9" r="2.5"/>
                        </svg>
                        <?php echo esc_html( $destination ); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ( $duree ) : ?>
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        <?php echo esc_html( $duree ); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ( $points ) : ?>
                    <span class="tma-meta-pts">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                        +<?php echo esc_html( $points ); ?> points fidélité
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Contenu principal ───────────────────────────────── -->
    <div class="tma-single-wrap">
        <div class="container">
            <div class="tma-single-grid">

                <!-- Colonne gauche : description -->
                <div class="tma-single-content">

                    <?php do_action( 'woocommerce_before_single_product' ); ?>

                    <!-- Onglets : Description / Infos / Avis -->
                    <?php woocommerce_output_product_data_tabs(); ?>

                    <!-- Produits similaires -->
                    <div class="tma-related">
                        <?php
                        $related_args = [
                            'posts_per_page' => 3,
                            'columns'        => 3,
                            'orderby'        => 'rand',
                        ];
                        woocommerce_related_products( $related_args );
                        ?>
                    </div>

                </div>

                <!-- Colonne droite : CTA réservation -->
                <aside class="tma-single-sidebar">
                    <div class="tma-booking-card">

                        <!-- Prix -->
                        <div class="tma-booking-price">
                            <?php if ( $product->is_on_sale() ) : ?>
                                <div class="tma-booking-price__old">
                                    <?php echo wc_price( $product->get_regular_price() ); ?>
                                </div>
                            <?php endif; ?>
                            <div class="tma-booking-price__current">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                            <div class="tma-booking-price__label">par personne</div>
                        </div>

                        <?php if ( $points ) : ?>
                        <div class="tma-booking-points">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            Gagnez <strong><?php echo esc_html( $points ); ?> points</strong> fidélité
                        </div>
                        <?php endif; ?>

                        <!-- Bouton panier -->
                        <?php woocommerce_template_single_add_to_cart(); ?>

                        <!-- Informations pratiques -->
                        <ul class="tma-booking-info">
                            <?php if ( $duree ) : ?>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <span>Durée : <strong><?php echo esc_html( $duree ); ?></strong></span>
                            </li>
                            <?php endif; ?>
                            <?php if ( $destination ) : ?>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                    <circle cx="12" cy="9" r="2.5"/>
                                </svg>
                                <span>Destination : <strong><?php echo esc_html( $destination ); ?></strong></span>
                            </li>
                            <?php endif; ?>
                            <?php if ( $guide ) : ?>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                                </svg>
                                <span>Guide : <strong><?php echo esc_html( $guide->label ); ?></strong></span>
                            </li>
                            <?php endif; ?>
                            <?php if ( $hotel ) : ?>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                                <span>Hôtel : <strong><?php echo esc_html( $hotel->label ); ?></strong>
                                    <?php if ($hotel->categorie_etoiles) echo ' ' . str_repeat('★', (int)$hotel->categorie_etoiles); ?></span>
                            </li>
                            <?php endif; ?>
                            <?php if ( $transport ) : ?>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>
                                </svg>
                                <span>Transport : <strong><?php echo esc_html( $transport->label ); ?></strong>
                                    <?php if ($transport->compagnie) echo ' (' . esc_html($transport->compagnie) . ')'; ?></span>
                            </li>
                            <?php endif; ?>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span>Paiement sécurisé</span>
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91A16 16 0 0016 17l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                                </svg>
                                <span>Assistance 7j/7</span>
                            </li>
                        </ul>

                        <!-- Partage -->
                        <div class="tma-booking-share">
                            <span>Partager :</span>
                            <a href="https://wa.me/?text=<?php echo rawurlencode( get_the_title() . ' — ' . get_permalink() ); ?>"
                               target="_blank" rel="noopener" aria-label="WhatsApp">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                </svg>
                            </a>
                        </div>

                    </div>
                </aside>

            </div>
        </div>
    </div>

    <?php do_action( 'woocommerce_after_single_product' ); ?>

<?php endwhile; ?>

<?php get_footer(); ?>
