<?php get_header(); ?>

<div class="section">
    <div class="container">
        <?php while ( have_posts() ) : the_post(); ?>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:3rem;align-items:start">
            <div>
                <?php if ( has_post_thumbnail() ) : ?>
                <div style="border-radius:var(--radius-lg);overflow:hidden;margin-bottom:2rem">
                    <?php the_post_thumbnail('tma-hero', ['style'=>'width:100%;height:420px;object-fit:cover']); ?>
                </div>
                <?php endif; ?>
                <h1 style="font-size:2rem;color:var(--bleu);margin-bottom:1rem"><?php the_title(); ?></h1>
                <div class="offer-card-meta" style="margin-bottom:1.5rem">
                    <?php
                    $dest     = get_post_meta( get_the_ID(), '_tma_destination', true );
                    $duration = get_post_meta( get_the_ID(), '_tma_duration',    true );
                    if ( $dest )     echo '<span class="offer-meta-item">📍 ' . esc_html($dest) . '</span>';
                    if ( $duration ) echo '<span class="offer-meta-item">⏱ ' . esc_html($duration) . '</span>';
                    ?>
                </div>
                <div style="color:var(--gris-fonce);line-height:1.8;font-size:1rem"><?php the_content(); ?></div>
            </div>
            <div style="position:sticky;top:100px">
                <div style="background:var(--gris-clair);border-radius:var(--radius-lg);padding:2rem;border:1px solid var(--border)">
                    <div style="font-size:2.5rem;font-weight:800;color:var(--bleu);margin-bottom:1.5rem">
                        <?php
                        $price = get_post_meta( get_the_ID(), '_price', true );
                        echo esc_html( number_format((float)$price, 0, ',', '.') ) . ' <span style="font-size:1rem;font-weight:400;color:var(--gris)">MAD / personne</span>';
                        ?>
                    </div>
                    <?php
                    $points = get_post_meta( get_the_ID(), '_tma_points', true );
                    if ( $points ) :
                    ?>
                    <div style="background:rgba(249,115,22,.1);border-radius:var(--radius);padding:.75rem 1rem;margin-bottom:1.5rem;font-size:.9rem;color:var(--orange-fonce)">
                        ⭐ Gagnez <strong>+<?php echo esc_html($points); ?> points</strong> avec cette réservation
                    </div>
                    <?php endif; ?>
                    <?php
                    if ( function_exists('woocommerce_template_single_add_to_cart') ) {
                        global $product;
                        $product = wc_get_product( get_the_ID() );
                        woocommerce_template_single_add_to_cart();
                    }
                    ?>
                    <div style="margin-top:1.25rem;font-size:.85rem;color:var(--gris);border-top:1px solid var(--border);padding-top:1.25rem">
                        <p>✅ Paiement à l'agence ou par virement</p>
                        <p>✅ Confirmation immédiate par email</p>
                        <p>✅ Annulation flexible (conditions applicables)</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php get_footer(); ?>
