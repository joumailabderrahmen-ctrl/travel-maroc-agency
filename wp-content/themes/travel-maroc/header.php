<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php bloginfo('description'); ?>">
    <?php tma_og_meta(); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Bandeau fidélité (clients connectés)
if ( is_user_logged_in() ) {
    global $wpdb;
    $uid     = get_current_user_id();
    $profile = $wpdb->get_row( $wpdb->prepare(
        "SELECT p.points_fidelite_solde, t.libelle, t.couleur_badge
         FROM {$wpdb->prefix}tma_client_profile p
         JOIN {$wpdb->prefix}tma_type_client t ON t.id = p.type_client_id
         WHERE p.wp_user_id = %d", $uid
    ));
    if ( $profile ) :
?>
<div class="fidelite-banner">
    <span>⭐ Statut : <strong><?php echo esc_html( $profile->libelle ); ?></strong></span>
    <span>|</span>
    <span>Points disponibles : <strong><?php echo esc_html( $profile->points_fidelite_solde ); ?> pts</strong></span>
</div>
<?php endif; } ?>

<header class="site-header">
    <div class="header-inner">

        <div class="site-branding">
            <a href="<?php echo esc_url( home_url('/') ); ?>" class="tma-logo-wrap" aria-label="Travel Maroc Agency — Accueil">
                <svg class="tma-logo-svg" viewBox="0 0 260 65" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="false">
                    <title>Travel Maroc Agency</title>
                    <defs>
                        <!-- Dégradé ciel nuit -->
                        <linearGradient id="lgGradSky" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%"   stop-color="#061428"/>
                            <stop offset="100%" stop-color="#0d2b55"/>
                        </linearGradient>
                        <!-- Dégradé route -->
                        <linearGradient id="lgGradRoad" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%"   stop-color="#1f2937"/>
                            <stop offset="50%"  stop-color="#374151"/>
                            <stop offset="100%" stop-color="#1f2937"/>
                        </linearGradient>
                        <!-- Reflet vitre voiture -->
                        <linearGradient id="lgGradWindow" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%"   stop-color="rgba(100,160,255,0.35)"/>
                            <stop offset="100%" stop-color="rgba(13,43,85,0.55)"/>
                        </linearGradient>
                        <!-- Carrosserie voiture -->
                        <linearGradient id="lgGradCar" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%"   stop-color="#1a4a8a"/>
                            <stop offset="60%"  stop-color="#0d2b55"/>
                            <stop offset="100%" stop-color="#071a36"/>
                        </linearGradient>
                    </defs>

                    <!-- ═══════════════════════════════════════════════
                         SCÈNE 1 — WORDMARK (visible au repos)
                    ═══════════════════════════════════════════════ -->
                    <g class="lgs-wordmark">

                        <!-- TRAVEL (light weight) -->
                        <text class="lgt-travel" x="8" y="36">TRAVEL</text>

                        <!-- MAROC (bold, orange) -->
                        <text class="lgt-maroc" x="114" y="36">MAROC</text>

                        <!-- Ligne orange sous le texte — se dessine via GSAP -->
                        <line class="lgl-underline" x1="8" y1="42" x2="198" y2="42"/>

                        <!-- Icône : croissant + étoile marocaine -->
                        <g class="lgi-star" transform="translate(218,20)">
                            <!-- Cercle fond subtil -->
                            <circle cx="0" cy="0" r="16" fill="none" stroke="#f97316" stroke-width="0.5" opacity="0.25"/>
                            <!-- Croissant -->
                            <path d="M-5,-11 A12,12 0 1,1 -5,11 A8.5,8.5 0 1,0 -5,-11 Z"
                                  fill="#f97316" transform="rotate(-20)"/>
                            <!-- Étoile 5 branches -->
                            <polygon points="8,-1.5 9.5,3 14,3 10.5,5.8 11.5,10.2 8,7.5 4.5,10.2 5.5,5.8 2,3 6.5,3"
                                     fill="#f97316"
                                     transform="translate(-4.5,-6) scale(0.68)"/>
                        </g>

                        <!-- Tagline -->
                        <text class="lgt-tagline" x="8" y="55">AGENCE DE VOYAGE</text>

                    </g>

                    <!-- ═══════════════════════════════════════════════
                         SCÈNE 2 — ROUTE (cachée, GSAP la révèle)
                    ═══════════════════════════════════════════════ -->
                    <g class="lgs-road">
                        <!-- Ciel du soir -->
                        <rect x="-10" y="-5" width="280" height="45" fill="url(#lgGradRoad)" opacity="0.18"/>
                        <!-- Silhouette montagnes Atlas -->
                        <path d="M-10,42 L20,22 L38,32 L62,14 L88,26 L118,10 L148,22 L178,15 L208,26 L235,18 L260,28 L270,42 Z"
                              fill="#0a1e3d" opacity="0.55"/>
                        <!-- Bande route -->
                        <rect x="-10" y="42" width="280" height="28" fill="url(#lgGradRoad)"/>
                        <!-- Bord gauche (ligne blanche) -->
                        <line x1="-10" y1="44" x2="270" y2="44" stroke="rgba(255,255,255,.22)" stroke-width="1.2"/>
                        <!-- Ligne médiane pointillée -->
                        <line x1="-10" y1="56" x2="270" y2="56"
                              stroke="#fef9c3" stroke-width="1.2"
                              stroke-dasharray="14 10" opacity="0.55"/>
                        <!-- Bord droit -->
                        <line x1="-10" y1="68" x2="270" y2="68" stroke="rgba(255,255,255,.15)" stroke-width="1.2"/>

                        <!-- ── VOITURE ─────────────────────────────── -->
                        <!-- Positionnée à transform="translate(x, 46)" par GSAP -->
                        <g class="lgv-car" transform="translate(-100,46)">
                            <!-- Ombre portée sol -->
                            <ellipse cx="34" cy="4" rx="38" ry="3.5" fill="rgba(0,0,0,0.25)"/>
                            <!-- Carrosserie principale — berline élégante -->
                            <path d="M4,0 C4,-9 9,-12 16,-12 L55,-12 C62,-12 66,-9 66,0 Z"
                                  fill="url(#lgGradCar)"/>
                            <!-- Pavillon / toit — ligne fuyante -->
                            <path d="M16,-12 C17,-22 21,-27 27,-29 L45,-29 C51,-27 55,-22 56,-12 Z"
                                  fill="url(#lgGradCar)"/>
                            <!-- Vitre latérale (reflet bleuté) -->
                            <path d="M20,-12 C20,-25 24,-28 28,-29 L44,-29 C48,-28 52,-25 52,-12 Z"
                                  fill="url(#lgGradWindow)"/>
                            <!-- Montant B (séparation vitre) -->
                            <line x1="34" y1="-12" x2="34" y2="-29"
                                  stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
                            <!-- Bande de caisse orange -->
                            <rect x="4" y="-14" width="62" height="2.5" rx="1.2" fill="#f97316"/>
                            <!-- Jante avant — 3 anneaux -->
                            <circle cx="52" cy="3" r="9.5" fill="#111827"/>
                            <circle cx="52" cy="3" r="6"   fill="#1f2937"/>
                            <circle cx="52" cy="3" r="3"   fill="#374151"/>
                            <circle cx="52" cy="3" r="1"   fill="#6b7280"/>
                            <!-- Pneu avant (demi-arc) -->
                            <path d="M43,0 A9.5,9.5 0 0,0 61,0" fill="#0a0f1a"/>
                            <!-- Jante arrière -->
                            <circle cx="18" cy="3" r="9.5" fill="#111827"/>
                            <circle cx="18" cy="3" r="6"   fill="#1f2937"/>
                            <circle cx="18" cy="3" r="3"   fill="#374151"/>
                            <circle cx="18" cy="3" r="1"   fill="#6b7280"/>
                            <path d="M9,0 A9.5,9.5 0 0,0 27,0" fill="#0a0f1a"/>
                            <!-- Phare avant (halo) -->
                            <ellipse cx="66" cy="-7" rx="3.5" ry="2.5" fill="#fef9c3" opacity="0.92"/>
                            <ellipse cx="66" cy="-7" rx="7"   ry="4.5" fill="#fef9c3" opacity="0.08"/>
                            <!-- Feu arrière -->
                            <rect x="2" y="-9" width="3" height="5" rx="1.5" fill="#ef4444" opacity="0.9"/>
                            <!-- Pare-choc avant chromé -->
                            <rect x="63" y="-5" width="4" height="3" rx="1" fill="#9ca3af" opacity="0.6"/>
                            <!-- Rétroviseur -->
                            <rect x="57" y="-17" width="5" height="3" rx="1" fill="#0d2b55"/>
                            <!-- Ligne de toit (reflet) -->
                            <path d="M20,-26 Q35,-30 50,-27"
                                  stroke="rgba(255,255,255,0.18)" stroke-width="1" fill="none"/>
                        </g>
                    </g>

                    <!-- ═══════════════════════════════════════════════
                         SCÈNE 3 — CIEL NOCTURNE (cachée, GSAP la révèle)
                    ═══════════════════════════════════════════════ -->
                    <g class="lgs-sky">
                        <!-- Fond nuit gradient -->
                        <rect x="-10" y="-5" width="280" height="80" fill="url(#lgGradSky)"/>
                        <!-- Étoiles (fixes) -->
                        <circle cx="18"  cy="8"  r="0.9" fill="white" opacity="0.7"/>
                        <circle cx="45"  cy="4"  r="0.7" fill="white" opacity="0.55"/>
                        <circle cx="80"  cy="12" r="1.1" fill="white" opacity="0.8"/>
                        <circle cx="120" cy="6"  r="0.7" fill="white" opacity="0.5"/>
                        <circle cx="160" cy="14" r="1"   fill="white" opacity="0.7"/>
                        <circle cx="195" cy="5"  r="0.8" fill="white" opacity="0.6"/>
                        <circle cx="235" cy="10" r="0.9" fill="white" opacity="0.65"/>
                        <circle cx="32"  cy="22" r="0.6" fill="white" opacity="0.4"/>
                        <circle cx="145" cy="20" r="0.7" fill="white" opacity="0.45"/>
                        <circle cx="210" cy="22" r="0.6" fill="white" opacity="0.4"/>
                        <!-- Croissant de lune -->
                        <path d="M228,8 A11,11 0 1,1 228,30 A8,8 0 1,0 228,8 Z"
                              fill="#f97316" opacity="0.85" transform="scale(0.75) translate(77,-4)"/>
                        <!-- Nuages discrets -->
                        <ellipse cx="70"  cy="30" rx="22" ry="6"  fill="white" opacity="0.04"/>
                        <ellipse cx="190" cy="25" rx="18" ry="5"  fill="white" opacity="0.03"/>

                        <!-- ── AVION ───────────────────────────────── -->
                        <!-- Positionné et animé par GSAP -->
                        <g class="lgv-plane" transform="translate(260,55)">
                            <!-- Fuselage (corps principal) -->
                            <ellipse cx="0" cy="0" rx="28" ry="7" fill="#f0f4ff"/>
                            <!-- Nez profilé -->
                            <path d="M22,-4 Q35,0 22,4 Z" fill="#e2e8f0"/>
                            <!-- Aile principale (balayée en arrière) -->
                            <path d="M-3,-6 L-3,6 Q-18,18 -32,22 L-28,6 Z"
                                  fill="#e8edf5" opacity="0.93"/>
                            <!-- Petite aile (opposée — effet 3D) -->
                            <path d="M2,-5 L2,4 Q14,2 24,-5 Z"
                                  fill="#d8e0ef" opacity="0.75"/>
                            <!-- Dérive verticale (empennage) -->
                            <path d="M-22,-7 L-20,0 L-28,0 Z" fill="#e8edf5" opacity="0.9"/>
                            <!-- Stabilisateurs horizontaux -->
                            <path d="M-20,0 L-20,5 Q-28,10 -32,12 L-30,5 Z"
                                  fill="#dce5f0" opacity="0.8"/>
                            <!-- Réacteur sous l'aile -->
                            <ellipse cx="-16" cy="10" rx="7" ry="2.8" fill="#c0cce0" opacity="0.85"/>
                            <ellipse cx="-16" cy="10" rx="4"  ry="1.8" fill="#a0b0c8" opacity="0.7"/>
                            <!-- Bande livrée orange -->
                            <path d="M-22,1.5 L20,1.5 L20,4.5 L-22,4.5 Z" rx="0"
                                  fill="#f97316" opacity="0.8"/>
                            <!-- Hublots -->
                            <circle cx="-14" cy="-1.5" r="1.5" fill="rgba(13,43,85,0.35)"/>
                            <circle cx="-8"  cy="-1.5" r="1.5" fill="rgba(13,43,85,0.35)"/>
                            <circle cx="-2"  cy="-1.5" r="1.5" fill="rgba(13,43,85,0.35)"/>
                            <circle cx="4"   cy="-1.5" r="1.5" fill="rgba(13,43,85,0.35)"/>
                            <circle cx="10"  cy="-1.5" r="1.5" fill="rgba(13,43,85,0.35)"/>
                            <!-- Reflet nacelle -->
                            <path d="M-8,-5 Q2,-7 14,-4"
                                  stroke="rgba(255,255,255,0.3)" stroke-width="1" fill="none"/>
                        </g>
                    </g>

                </svg>
            </a>
        </div>

        <nav class="primary-nav" id="primary-nav" aria-label="Navigation principale">
            <?php wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class'     => '',
                'container'      => false,
                'fallback_cb'    => false,
                'items_wrap'     => '<ul>%3$s</ul>',
            ]); ?>
        </nav>

        <div class="header-actions">
            <?php if ( class_exists('WooCommerce') ) : ?>
            <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart-icon" aria-label="Panier">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
                <span class="cart-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
            </a>
            <?php endif; ?>

            <?php if ( is_user_logged_in() ) : ?>
                <a href="<?php echo esc_url( wc_get_account_endpoint_url('dashboard') ); ?>" class="btn btn-outline" style="padding:.5rem 1rem;font-size:.88rem;color:#fff;border-color:rgba(255,255,255,.4)">Mon compte</a>
                <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="btn btn-primary" style="padding:.5rem 1rem;font-size:.88rem;">Déconnexion</a>
            <?php else : ?>
                <a href="<?php echo esc_url( wc_get_page_permalink('myaccount') ); ?>" class="btn btn-primary" style="padding:.5rem 1rem;font-size:.88rem;">Connexion</a>
            <?php endif; ?>

            <?php tma_language_switcher(); ?>

            <button class="nav-toggle" id="nav-toggle" aria-label="Menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>

    </div>
</header>
