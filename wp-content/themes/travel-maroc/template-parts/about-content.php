<div class="about-hero">
    <div class="container">
        <h1 class="about-hero h1">À Propos de Travel Maroc Agency</h1>
        <p>Votre partenaire de confiance pour des voyages inoubliables depuis 2020</p>
    </div>
</div>
<?php
$about_maroc_url = get_option('tma_about_maroc_url');
$about_team_url  = get_option('tma_about_team_url');
?>
<div class="section"><div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;margin-bottom:5rem">
        <div>
            <h2 class="section-title">Notre histoire</h2>
            <?php
            $about_text = tma_get_option_translated('tma_about_text', '');
            if ( $about_text ) :
                echo '<div style="color:var(--gris-fonce);line-height:1.8">' . wp_kses_post( wpautop($about_text) ) . '</div>';
            else :
            ?>
            <p style="color:var(--gris-fonce);line-height:1.8;margin-bottom:1rem">
                Travel Maroc Agency est née d'une passion commune pour le voyage et la découverte culturelle. Fondée à Dakhla, au cœur du Maroc profond, notre agence s'est donnée pour mission de vous faire vivre des expériences authentiques, que ce soit dans les dunes de Merzouga, les ruelles de Chefchaouen ou les capitales du monde entier.
            </p>
            <p style="color:var(--gris-fonce);line-height:1.8">
                Chaque itinéraire est soigneusement conçu par notre équipe de spécialistes. Nous collaborons avec des guides locaux certifiés, des hôtels sélectionnés pour leur qualité, et des partenaires de transport fiables pour vous garantir une expérience sans stress.
            </p>
            <?php endif; ?>
        </div>
        <?php if ( $about_maroc_url ) : ?>
        <div style="border-radius:var(--radius-lg);overflow:hidden;height:380px">
            <img src="<?php echo esc_url($about_maroc_url); ?>" alt="Maroc authentique" style="width:100%;height:100%;object-fit:cover">
        </div>
        <?php else : ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <?php
            $stats = [ ['500+','Clients satisfaits'], ['10+','Destinations'], ['5★','Note moyenne'], ['3','Guides certifiés'] ];
            foreach ( $stats as $s ) :
            ?>
            <div style="background:var(--gris-clair);border-radius:var(--radius-lg);padding:2rem;text-align:center">
                <div style="font-size:2.5rem;font-weight:900;color:var(--bleu)"><?php echo $s[0]; ?></div>
                <div style="color:var(--gris);font-size:.9rem"><?php echo $s[1]; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="text-center" style="margin-bottom:3rem">
        <h2 class="section-title">Notre équipe</h2>
        <p class="section-sub">Des professionnels passionnés à votre service</p>
    </div>
    <?php if ( $about_team_url ) : ?>
    <div style="border-radius:var(--radius-lg);overflow:hidden;margin-bottom:5rem;max-height:420px">
        <img src="<?php echo esc_url($about_team_url); ?>" alt="Notre équipe Travel Maroc" style="width:100%;height:100%;object-fit:cover">
    </div>
    <?php else : ?>
    <div class="grid-3" style="margin-bottom:5rem">
        <?php
        $team = [
            [ '👨‍💼', 'Youssef Amrani',  'Directeur & Fondateur',    'Expert en tourisme avec 15 ans d\'expérience au Maroc et à l\'international.' ],
            [ '👩‍💻', 'Salma Benali',    'Responsable Réservations', 'Coordinatrice des voyages, polyglotte FR/AR/EN/ES.' ],
            [ '🧭', 'Hassan Ouazani',   'Guide Senior',             'Spécialiste du désert marocain et des circuits culturels.' ],
        ];
        foreach ( $team as $m ) :
        ?>
        <div class="team-card">
            <div class="team-avatar"><?php echo $m[0]; ?></div>
            <div class="team-name"><?php echo esc_html($m[1]); ?></div>
            <div class="team-role" style="margin-bottom:.75rem"><?php echo esc_html($m[2]); ?></div>
            <p style="font-size:.88rem;color:var(--gris)"><?php echo esc_html($m[3]); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="cta-section" style="border-radius:var(--radius-lg)">
        <h2>Prêt à voyager avec nous ?</h2>
        <p>Découvrez nos offres et réservez votre prochain voyage en quelques clics.</p>
        <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="btn btn-white">Voir les offres</a>
    </div>
</div></div>
