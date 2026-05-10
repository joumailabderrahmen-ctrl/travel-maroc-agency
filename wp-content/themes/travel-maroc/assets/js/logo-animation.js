/* ================================================================
   TRAVEL MAROC — Logo Animation (GSAP 3)
   Timeline : 18s loop + 3s pause
   ================================================================ */
(function () {
    'use strict';

    function boot() {
        if (typeof gsap === 'undefined') return;

        const svg      = document.querySelector('.tma-logo-svg');
        if (!svg) return;

        const q = (sel) => svg.querySelector(sel);

        const wordmark = q('.lgs-wordmark');
        const scRoad   = q('.lgs-road');
        const scSky    = q('.lgs-sky');
        const car      = q('.lgv-car');
        const plane    = q('.lgv-plane');
        const star     = q('.lgi-star');
        const underline = q('.lgl-underline');
        const tagline  = q('.lgt-tagline');

        /* ── États initiaux ─────────────────────────────────────── */
        gsap.set(scRoad,  { autoAlpha: 0 });
        gsap.set(scSky,   { autoAlpha: 0 });
        gsap.set(car,     { x: -100, autoAlpha: 0 });
        gsap.set(plane,   { x: 0,   y: 0, rotation: 0, autoAlpha: 0 });
        gsap.set(tagline, { autoAlpha: 0 });

        /* ── Timeline maître ─────────────────────────────────────── */
        const tl = gsap.timeline({
            repeat: -1,
            repeatDelay: 3,
            defaults: { ease: 'power2.inOut' }
        });

        /* ────────────────────────────────────────────────────────────
           PHASE 1 — Logo au repos (0 → 5s)
           Texte apparaît lettre par lettre via clip, underline se dessine
        ──────────────────────────────────────────────────────────── */
        // Icône étoile tourne lentement
        tl.to(star, {
            rotation:        360,
            duration:        5,
            ease:            'none',
            transformOrigin: '50% 50%'
        }, 0);

        // Underline se dessine
        tl.to(underline, {
            strokeDashoffset: 0,
            duration:         1.8,
            ease:             'power3.out'
        }, 0.6);

        // Tagline apparaît
        tl.to(tagline, {
            autoAlpha: 1,
            duration:  1,
            ease:      'power2.out'
        }, 1.2);

        /* ────────────────────────────────────────────────────────────
           PHASE 2 — Fondu vers la route (5 → 6.5s)
        ──────────────────────────────────────────────────────────── */
        tl.to([wordmark], {
            autoAlpha: 0,
            y:        -6,
            duration: 1.2,
            ease:     'power3.in'
        }, 5);

        /* ────────────────────────────────────────────────────────────
           PHASE 3 — Route apparaît (6 → 7s)
        ──────────────────────────────────────────────────────────── */
        tl.to(scRoad, {
            autoAlpha: 1,
            duration:  1,
            ease:      'power2.out'
        }, 6);

        /* ────────────────────────────────────────────────────────────
           PHASE 4 — La voiture traverse lentement (7 → 14s)
           Entrée douce depuis la gauche, sortie fondue à droite
        ──────────────────────────────────────────────────────────── */
        tl.to(car, {
            autoAlpha: 1,
            x:         -100,
            duration:  0.6,
            ease:      'power2.out'
        }, 6.8);

        tl.to(car, {
            x:        380,
            duration: 6.5,
            ease:     'power1.inOut'
        }, 7);

        // Fondu en sortie côté droit
        tl.to(car, {
            autoAlpha: 0,
            duration:  0.8,
            ease:      'power2.in'
        }, 12.8);

        /* ────────────────────────────────────────────────────────────
           PHASE 5 — Transition route → ciel (13.5 → 14.5s)
        ──────────────────────────────────────────────────────────── */
        tl.to(scRoad, {
            autoAlpha: 0,
            duration:  0.9,
            ease:      'power2.in'
        }, 13.5);

        tl.to(scSky, {
            autoAlpha: 1,
            duration:  0.9,
            ease:      'power2.out'
        }, 13.8);

        /* ────────────────────────────────────────────────────────────
           PHASE 6 — L'avion entre depuis bas-droite et vole en arc (14.5 → 18s)
           Trajectoire : bas-droite → montée → virage → sortie bas-gauche
        ──────────────────────────────────────────────────────────── */
        // Position départ : bas droite
        gsap.set(plane, { x: 270, y: 52, rotation: -28 });

        tl.to(plane, { autoAlpha: 1, duration: 0.4, ease: 'power2.out' }, 14.5);

        // Segment 1 : montée initiale (angle -28°)
        tl.to(plane, {
            x:        190,
            y:        28,
            rotation: -18,
            duration: 1.1,
            ease:     'power1.in'
        }, 14.5);

        // Segment 2 : sommet de l'arc (aile légèrement à plat)
        tl.to(plane, {
            x:        110,
            y:        10,
            rotation: -4,
            duration: 1.0,
            ease:     'none'
        }, 15.6);

        // Segment 3 : descente et virage sur l'aile gauche
        tl.to(plane, {
            x:        30,
            y:        26,
            rotation: 12,
            duration: 1.0,
            ease:     'power1.out'
        }, 16.6);

        // Fondu sortie
        tl.to(plane, {
            autoAlpha: 0,
            x:        -30,
            y:         38,
            rotation:  20,
            duration:  0.8,
            ease:      'power2.in'
        }, 17.6);

        /* ────────────────────────────────────────────────────────────
           PHASE 7 — Ciel s'efface, logo revient (18 → 20s)
        ──────────────────────────────────────────────────────────── */
        tl.to(scSky, {
            autoAlpha: 0,
            duration:  0.9,
            ease:      'power2.in'
        }, 18);

        // Reset du wordmark
        tl.set(wordmark, { y: 8 }, 18);

        tl.to(wordmark, {
            autoAlpha: 1,
            y:         0,
            duration:  1.6,
            ease:      'power3.out'
        }, 18.2);

        tl.to(tagline, {
            autoAlpha: 1,
            duration:  1,
            ease:      'power2.out'
        }, 18.8);

        // Reset underline pour le prochain cycle
        tl.set(underline, { strokeDashoffset: 190 }, 18);
        tl.to(underline, {
            strokeDashoffset: 0,
            duration:         1.4,
            ease:             'power3.out'
        }, 18.5);

        // Icône : retour avec ressort
        tl.fromTo(star, {
            rotation: 0,
            scale:    0.4,
            autoAlpha: 0
        }, {
            rotation:  360,
            scale:     1,
            autoAlpha: 1,
            duration:  1.2,
            ease:      'back.out(2)',
            transformOrigin: '50% 50%'
        }, 18.4);
    }

    /* Attendre que GSAP soit chargé */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
