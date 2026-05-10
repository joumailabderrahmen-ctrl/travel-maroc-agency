/* Travel Maroc Agency — Main JS */
(function () {
    'use strict';

    // ── Menu hamburger mobile ──────────────────────────────
    const toggle = document.getElementById('nav-toggle');
    const nav    = document.getElementById('primary-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            const open = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open);
        });
        document.addEventListener('click', (e) => {
            if (!nav.contains(e.target) && !toggle.contains(e.target)) {
                nav.classList.remove('open');
                toggle.setAttribute('aria-expanded', false);
            }
        });
    }

    // ── Animation apparition au scroll ────────────────────
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity    = '1';
                entry.target.style.transform  = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.offer-card, .why-card, .testimonial-card, .dest-card').forEach(el => {
        el.style.opacity   = '0';
        el.style.transform = 'translateY(24px)';
        el.style.transition = 'opacity .4s ease, transform .4s ease';
        observer.observe(el);
    });

    // ── Formulaire contact AJAX ────────────────────────────
    const form = document.getElementById('tma-contact-form');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const status = document.getElementById('contact-status');
            const btn    = form.querySelector('[type="submit"]');
            btn.disabled   = true;
            btn.textContent = 'Envoi en cours…';
            status.textContent = '';

            const data = new FormData(form);
            data.append('action', 'tma_contact');
            data.append('nonce',  tmaData.nonce);

            try {
                const res  = await fetch(tmaData.ajaxUrl, { method: 'POST', body: data });
                const json = await res.json();
                if (json.success) {
                    status.style.color = '#10b981';
                    status.textContent  = '✅ Message envoyé ! Nous vous répondrons dans les 24h.';
                    form.reset();
                } else {
                    status.style.color = '#ef4444';
                    status.textContent  = '❌ Erreur : ' + (json.data || 'Réessayez plus tard.');
                }
            } catch {
                status.style.color = '#ef4444';
                status.textContent  = '❌ Erreur réseau.';
            } finally {
                btn.disabled    = false;
                btn.textContent = 'Envoyer le message';
            }
        });
    }

    // ── Mise à jour compteur panier ────────────────────────
    document.body.addEventListener('added_to_cart', () => {
        const count = document.querySelector('.cart-count');
        if (count) {
            const n = parseInt(count.textContent || '0', 10);
            count.textContent = n + 1;
            count.style.transform = 'scale(1.4)';
            setTimeout(() => { count.style.transform = 'scale(1)'; }, 200);
        }
    });

    // ── Smooth scroll pour ancres ──────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(a => {
        a.addEventListener('click', e => {
            const target = document.querySelector(a.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

})();
