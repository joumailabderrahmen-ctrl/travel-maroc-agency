<?php
$product  = wc_get_product( get_the_ID() );
if ( ! $product ) return;

$cat_terms = get_the_terms( get_the_ID(), 'product_cat' );
$cat_name  = $cat_terms ? $cat_terms[0]->name : '';
$price     = $product->get_price();
$duration  = get_post_meta( get_the_ID(), '_tma_duration', true );
$dest      = get_post_meta( get_the_ID(), '_tma_destination', true );
$points    = get_post_meta( get_the_ID(), '_tma_points', true );
$img_url   = get_the_post_thumbnail_url( get_the_ID(), 'tma-card' );
?>
<article class="offer-card">
    <div class="offer-card-img">
        <?php if ( $img_url ) : ?>
            <img src="<?php echo esc_url($img_url); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
        <?php else : ?>
            <div style="width:100%;height:100%;background:linear-gradient(135deg,#0d2b55,#1a4a8a);display:flex;align-items:center;justify-content:center">
                <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.3)" stroke-width="1"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
            </div>
        <?php endif; ?>
        <?php if ( $cat_name ) : ?>
        <div class="offer-cat-badge">
            <span class="badge badge-orange"><?php echo esc_html($cat_name); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="offer-card-body">
        <h3 class="offer-card-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h3>

        <div class="offer-card-meta">
            <?php if ( $dest ) : ?>
            <span class="offer-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                <?php echo esc_html($dest); ?>
            </span>
            <?php endif; ?>
            <?php if ( $duration ) : ?>
            <span class="offer-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php echo esc_html($duration); ?>
            </span>
            <?php endif; ?>
            <?php if ( $points ) : ?>
            <span class="offer-meta-item" style="color:var(--orange)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                +<?php echo esc_html($points); ?> pts
            </span>
            <?php endif; ?>
        </div>

        <p class="offer-card-desc"><?php the_excerpt(); ?></p>

        <div class="offer-card-footer">
            <div class="offer-price">
                <?php echo esc_html( number_format($price, 0, ',', '.') ); ?> <small>MAD</small>
            </div>
            <a href="<?php the_permalink(); ?>" class="btn btn-primary" style="font-size:.9rem;padding:.6rem 1.25rem">
                Réserver
            </a>
        </div>
    </div>
</article>
