<?php get_header(); ?>
<div class="section" style="text-align:center;padding:8rem 0">
    <div class="container">
        <div style="font-size:6rem;font-weight:900;color:var(--bleu-light)">404</div>
        <h1 style="color:var(--bleu);margin-bottom:1rem">Page introuvable</h1>
        <p style="color:var(--gris);margin-bottom:2rem">Cette destination n'existe pas encore dans notre catalogue.</p>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary">Retour à l'accueil</a>
    </div>
</div>
<?php get_footer(); ?>
