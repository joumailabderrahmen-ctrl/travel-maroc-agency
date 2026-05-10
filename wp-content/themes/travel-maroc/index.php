<?php get_header(); ?>
<div class="section"><div class="container">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
    <article style="margin-bottom:2rem;padding-bottom:2rem;border-bottom:1px solid var(--border)">
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
        <?php the_excerpt(); ?>
    </article>
<?php endwhile; the_posts_pagination(); else : ?>
    <p>Aucun contenu.</p>
<?php endif; ?>
</div></div>
<?php get_footer(); ?>
