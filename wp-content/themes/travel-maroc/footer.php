<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">

            <div class="footer-brand">
                <div class="brand-name">Travel <span>Maroc</span></div>
                <p>Votre partenaire de confiance pour découvrir le Maroc et le monde. Des voyages sur mesure, des expériences inoubliables.</p>
                <div style="display:flex;gap:.75rem;margin-top:1.25rem">
                    <a href="#" aria-label="Facebook" style="color:rgba(255,255,255,.6)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                    </a>
                    <a href="#" aria-label="Instagram" style="color:rgba(255,255,255,.6)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a href="#" aria-label="WhatsApp" style="color:rgba(255,255,255,.6)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Navigation</h4>
                <ul>
                    <li><a href="<?php echo esc_url( home_url('/') ); ?>">Accueil</a></li>
                    <?php if ( class_exists('WooCommerce') ) : ?>
                    <li><a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>">Toutes les offres</a></li>
                    <?php
                    $cats = get_terms([ 'taxonomy' => 'product_cat', 'hide_empty' => true ]);
                    foreach ( $cats as $cat ) :
                    ?>
                    <li><a href="<?php echo esc_url( get_term_link($cat) ); ?>"><?php echo esc_html($cat->name); ?></a></li>
                    <?php endforeach; endif; ?>
                    <li><a href="<?php echo esc_url( home_url('/contact') ); ?>">Contact</a></li>
                    <li><a href="<?php echo esc_url( home_url('/a-propos') ); ?>">À propos</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Mon compte</h4>
                <ul>
                    <?php if ( class_exists('WooCommerce') ) : ?>
                    <li><a href="<?php echo esc_url( wc_get_page_permalink('myaccount') ); ?>">Connexion / Inscription</a></li>
                    <li><a href="<?php echo esc_url( wc_get_account_endpoint_url('orders') ); ?>">Mes réservations</a></li>
                    <li><a href="<?php echo esc_url( wc_get_account_endpoint_url('edit-account') ); ?>">Mon profil</a></li>
                    <li><a href="<?php echo esc_url( wc_get_cart_url() ); ?>">Mon panier</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-col footer-contact">
                <h4>Contact</h4>
                <ul>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        EST Dakhla, Route de l'Aéroport, Dakhla 73000
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.09 19.79 19.79 0 010 .45 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91a16 16 0 006.16 6.16l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                        +212 5XX-XX-XX-XX
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        contact@travelmaroc.ma
                    </li>
                    <li>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Lun–Sam : 9h – 18h
                    </li>
                </ul>
            </div>

        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>© <?php echo gmdate('Y'); ?> Travel Maroc Agency — Tous droits réservés |
               <a href="<?php echo esc_url( home_url('/') ); ?>" style="color:rgba(255,255,255,.5)">Politique de confidentialité</a>
            </p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
