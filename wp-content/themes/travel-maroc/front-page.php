<?php get_header(); ?>

<!-- ═══════════════════════════════════════════════
     HERO
════════════════════════════════════════════════ -->
<?php
$hero_bg_url    = get_option('tma_hero_bg_url');
$hero_video_url = get_option('tma_video_hero_url');
?>
<section class="hero"<?php if ( $hero_bg_url && ! $hero_video_url ) : ?> style="background:linear-gradient(135deg,rgba(13,43,85,.88) 0%,rgba(37,99,235,.7) 100%),url(<?php echo esc_url($hero_bg_url); ?>) center/cover no-repeat"<?php endif; ?>>
    <?php if ( $hero_video_url ) : ?>
    <video class="hero-video-bg" autoplay muted loop playsinline>
        <source src="<?php echo esc_url($hero_video_url); ?>" type="video/mp4">
    </video>
    <div class="hero-video-overlay"></div>
    <?php endif; ?>
    <div class="container">
        <div class="hero-content">
            <div class="hero-badge">
                <span class="badge badge-orange">🌍 Agence de voyage certifiée</span>
            </div>
            <h1 class="hero-title">
                Explorez le monde<br><span>avec confiance</span>
            </h1>
            <p class="hero-desc">
                Séjours, circuits, excursions — des expériences uniques au Maroc et à l'international.
                Réservez en ligne en 3 clics, profitez de nos offres exclusives.
            </p>
            <div class="hero-actions">
                <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="btn btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Voir toutes les offres
                </a>
                <a href="<?php echo esc_url( home_url('/contact') ); ?>" class="btn btn-white">
                    Nous contacter
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat"><div class="hero-stat-num">10+</div><div class="hero-stat-lbl">Destinations</div></div>
                <div class="hero-stat"><div class="hero-stat-num">500+</div><div class="hero-stat-lbl">Clients satisfaits</div></div>
                <div class="hero-stat"><div class="hero-stat-num">3</div><div class="hero-stat-lbl">Types de séjours</div></div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     CATÉGORIES RAPIDES
════════════════════════════════════════════════ -->
<section class="section" style="padding:2.5rem 0">
    <div class="container">
        <div style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center">
            <?php
            $cats = get_terms([ 'taxonomy' => 'product_cat', 'hide_empty' => true ]);
            $icons = [ 'Voyages Nationaux' => '🇲🇦', 'Voyages Internationaux' => '✈️', 'Excursions' => '🏕️' ];
            foreach ( $cats as $cat ) :
                $icon = $icons[ $cat->name ] ?? '🌍';
            ?>
            <a href="<?php echo esc_url( get_term_link($cat) ); ?>" class="btn btn-outline" style="font-size:1rem">
                <?php echo $icon; ?> <?php echo esc_html( $cat->name ); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     OFFRES POPULAIRES
════════════════════════════════════════════════ -->
<section class="section section-alt">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Offres Populaires</h2>
            <p class="section-sub">Nos voyages les plus réservés du moment</p>
        </div>

        <?php
        $featured = new WP_Query([
            'post_type'      => 'product',
            'posts_per_page' => 6,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);
        if ( $featured->have_posts() ) :
        ?>
        <div class="grid-3">
        <?php while ( $featured->have_posts() ) : $featured->the_post(); ?>
            <?php get_template_part( 'template-parts/offer-card' ); ?>
        <?php endwhile; wp_reset_postdata(); ?>
        </div>

        <div class="text-center" style="margin-top:2.5rem">
            <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="btn btn-primary">
                Voir toutes les offres →
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     POURQUOI NOUS
════════════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Pourquoi choisir Travel Maroc ?</h2>
            <p class="section-sub">Des avantages pensés pour vous</p>
        </div>
        <div class="grid-3">
            <div class="why-card">
                <div class="why-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="why-title">Paiement sécurisé</div>
                <div class="why-desc">Paiement à l'agence ou par virement bancaire. Aucune mauvaise surprise.</div>
            </div>
            <div class="why-card">
                <div class="why-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div class="why-title">Programme fidélité</div>
                <div class="why-desc">Gagnez des points à chaque réservation et bénéficiez de remises exclusives.</div>
            </div>
            <div class="why-card">
                <div class="why-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
                <div class="why-title">Guides certifiés</div>
                <div class="why-desc">Des guides locaux expérimentés, multilingues, passionnés par leur région.</div>
            </div>
            <div class="why-card">
                <div class="why-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="why-title">Réservation rapide</div>
                <div class="why-desc">Réservez en 3 clics depuis n'importe quel appareil, 24h/24.</div>
            </div>
            <div class="why-card">
                <div class="why-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div class="why-title">10+ destinations</div>
                <div class="why-desc">Du désert de Merzouga aux capitales européennes — partez où vous voulez.</div>
            </div>
            <div class="why-card">
                <div class="why-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.09 19.79 19.79 0 010 .45 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.16 6.16l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                </div>
                <div class="why-title">Support 6j/7</div>
                <div class="why-desc">Notre équipe répond à vos questions du lundi au samedi, de 9h à 18h.</div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     VOYAGES NATIONAUX (mise en avant)
════════════════════════════════════════════════ -->
<section class="section section-alt">
    <div class="container">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem">
            <div>
                <h2 class="section-title" style="margin-bottom:.25rem">🇲🇦 Voyages Nationaux</h2>
                <p style="color:var(--gris)">Découvrez la richesse du Royaume</p>
            </div>
            <?php $cat_nat = get_term_by('slug','voyages-nationaux','product_cat'); ?>
            <?php if ( $cat_nat ) : ?>
            <a href="<?php echo esc_url( get_term_link($cat_nat) ); ?>" class="btn btn-outline">Voir tout →</a>
            <?php endif; ?>
        </div>
        <?php echo do_shortcode('[tma_offers cat="voyages-nationaux" limit="3"]'); ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     CTA
════════════════════════════════════════════════ -->
<section class="cta-section">
    <div class="container">
        <h2>Prêt pour votre prochaine aventure ?</h2>
        <p>Rejoignez des centaines de voyageurs qui nous font confiance. Réservez dès maintenant et bénéficiez de nos tarifs exclusifs.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="btn btn-white">
                Réserver maintenant
            </a>
            <a href="<?php echo esc_url( home_url('/contact') ); ?>" class="btn btn-outline" style="border-color:#fff;color:#fff">
                Demander un devis
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     TÉMOIGNAGES
════════════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <div class="text-center">
            <h2 class="section-title">Ce que disent nos clients</h2>
            <p class="section-sub">Des expériences authentiques, des souvenirs inoubliables</p>
        </div>
        <div class="grid-3">
            <?php
            $testi_photos = [
                get_option('tma_testi_1_url'),
                get_option('tma_testi_2_url'),
                get_option('tma_testi_3_url'),
            ];
            $testimonials = [
                [ 'text' => 'Voyage au désert de Merzouga absolument magique. Le guide était exceptionnel, le bivouac sous les étoiles inoubliable. Je recommande vivement !', 'name' => 'Fatima B.', 'loc' => 'Casablanca', 'note' => 5 ],
                [ 'text' => 'Istanbul séjour parfait — hôtel 4★ au cœur de la ville, transfers inclus. L\'agence a tout géré, aucun stress. Merci Travel Maroc !', 'name' => 'Mohammed A.', 'loc' => 'Rabat', 'note' => 5 ],
                [ 'text' => 'Les cascades d\'Ouzoud en excursion d\'une journée : paysage époustouflant, repas délicieux, guide sympa. Rapport qualité-prix excellent.', 'name' => 'Khadija M.', 'loc' => 'Marrakech', 'note' => 4 ],
            ];
            foreach ( $testimonials as $i => $t ) :
                $photo = $testi_photos[$i] ?? '';
            ?>
            <div class="testimonial-card">
                <div class="stars"><?php echo str_repeat('★', $t['note']) . str_repeat('☆', 5 - $t['note']); ?></div>
                <p class="testimonial-text">"<?php echo esc_html($t['text']); ?>"</p>
                <div class="testimonial-author">
                    <div class="author-avatar" style="<?php echo $photo ? 'padding:0;overflow:hidden' : ''; ?>">
                        <?php if ( $photo ) : ?>
                            <img src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr($t['name']); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                        <?php else : ?>
                            <?php echo esc_html( mb_substr($t['name'], 0, 1) ); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="author-name"><?php echo esc_html($t['name']); ?></div>
                        <div class="author-loc"><?php echo esc_html($t['loc']); ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
