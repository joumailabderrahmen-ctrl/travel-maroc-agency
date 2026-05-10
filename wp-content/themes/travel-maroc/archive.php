<?php get_header(); ?>

<div class="shop-header">
    <div class="container">
        <h1><?php post_type_archive_title(); ?></h1>
        <p>Découvrez toutes nos offres de voyage</p>
    </div>
</div>

<div class="section">
    <div class="container">
        <?php if ( have_posts() ) : ?>
            <div class="grid-3">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/offer-card' ); ?>
                <?php endwhile; ?>
            </div>
            <div style="margin-top:2.5rem;text-align:center">
                <?php the_posts_pagination([ 'mid_size' => 2 ]); ?>
            </div>
        <?php else : ?>
            <p style="text-align:center;color:var(--gris);padding:3rem">Aucune offre disponible pour le moment.</p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
