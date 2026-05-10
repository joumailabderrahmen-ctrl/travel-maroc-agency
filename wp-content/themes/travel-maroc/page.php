<?php get_header(); ?>
<div class="section">
    <div class="container" style="max-width:860px">
        <?php while ( have_posts() ) : the_post(); ?>
        <h1 style="font-size:2rem;color:var(--bleu);margin-bottom:2rem"><?php the_title(); ?></h1>
        <div style="line-height:1.8;color:var(--gris-fonce)"><?php the_content(); ?></div>
        <?php endwhile; ?>
    </div>
</div>
<?php get_footer(); ?>
