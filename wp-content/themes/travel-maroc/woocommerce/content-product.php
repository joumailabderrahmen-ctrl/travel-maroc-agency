<?php
defined( 'ABSPATH' ) || exit;
global $product;
if ( ! $product || ! $product->is_visible() ) return;

$destination = get_post_meta( $product->get_id(), '_tma_destination', true );
$duree       = get_post_meta( $product->get_id(), '_tma_duration',    true );
$points      = (int) get_post_meta( $product->get_id(), '_tma_points', true );
$image_url   = get_the_post_thumbnail_url( $product->get_id(), 'tma-card' );
$cats        = get_the_terms( $product->get_id(), 'product_cat' );
$cat_name    = ( $cats && ! is_wp_error($cats) ) ? $cats[0]->name : '';
?>
<article <?php wc_product_class( 'tma-offer-card', $product ); ?>>

    <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="tma-card-link" tabindex="-1" aria-hidden="true">
        <div class="tma-card-image">
            <?php if ( $image_url ) : ?>
                <img src="<?php echo esc_url( $image_url ); ?>"
                     alt="<?php echo esc_attr( $product->get_name() ); ?>"
                     loading="lazy" width="600" height="400">
            <?php else : ?>
                <div class="tma-card-placeholder">
                    <svg viewBox="0 0 80 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M40 10 L55 35 L70 20 L70 50 L10 50 L10 30 L25 45 Z" fill="rgba(255,255,255,.12)"/>
                        <circle cx="25" cy="22" r="8" fill="rgba(255,255,255,.15)"/>
                    </svg>
                </div>
            <?php endif; ?>

            <?php if ( $cat_name ) : ?>
                <span class="tma-card-badge"><?php echo esc_html( $cat_name ); ?></span>
            <?php endif; ?>

            <?php if ( $product->is_on_sale() ) : ?>
                <span class="tma-card-promo">Promo</span>
            <?php endif; ?>
        </div>
    </a>

    <div class="tma-card-body">
        <h3 class="tma-card-title">
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>">
                <?php echo esc_html( $product->get_name() ); ?>
            </a>
        </h3>

        <?php if ( $destination || $duree ) : ?>
        <div class="tma-card-meta">
            <?php if ( $destination ) : ?>
            <span class="tma-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                    <circle cx="12" cy="9" r="2.5"/>
                </svg>
                <?php echo esc_html( $destination ); ?>
            </span>
            <?php endif; ?>
            <?php if ( $duree ) : ?>
            <span class="tma-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <?php echo esc_html( $duree ); ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <p class="tma-card-excerpt">
            <?php echo wp_trim_words( wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ), 16, '…' ); ?>
        </p>

        <div class="tma-card-footer">
            <div class="tma-card-price">
                <?php if ( $product->is_on_sale() ) : ?>
                    <span class="tma-price-old"><?php echo wc_price( $product->get_regular_price() ); ?></span>
                <?php endif; ?>
                <span class="tma-price-current"><?php echo $product->get_price_html(); ?></span>
                <?php if ( $points ) : ?>
                    <span class="tma-price-points">+<?php echo esc_html( $points ); ?> pts</span>
                <?php endif; ?>
            </div>
            <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="btn btn-primary tma-card-btn">
                Réserver
            </a>
        </div>
    </div>

</article>
