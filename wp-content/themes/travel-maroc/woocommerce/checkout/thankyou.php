<?php
defined( 'ABSPATH' ) || exit;

// Points gagnés sur cette commande
$points_gagnes = 0;
if ( $order && class_exists('TMB_Fidelite') ) {
    $profile = TMB_Fidelite::get_profile( $order->get_user_id() );
    if ( $profile ) {
        global $wpdb;
        $points_gagnes = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT points FROM {$wpdb->prefix}tma_historique_points
             WHERE client_id = %d AND wc_order_id = %d AND operation = 'GAIN' LIMIT 1",
            $profile->id, $order->get_id()
        ));
    }
}
?>

<div class="tma-thankyou-wrap">
    <div class="container">

    <?php if ( $order && $order->has_status('failed') ) : ?>

        <!-- ── Commande échouée ────────────────────────────────── -->
        <div class="tma-thankyou-failed">
            <div class="tma-ty-icon tma-ty-icon--fail">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
            <h1>Paiement non abouti</h1>
            <p>Votre transaction a été refusée. Veuillez réessayer ou contacter votre banque.</p>
            <div class="tma-ty-actions">
                <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="btn btn-primary">Réessayer le paiement</a>
                <a href="<?php echo esc_url( home_url('/contact') ); ?>" class="btn btn-outline">Contacter le support</a>
            </div>
        </div>

    <?php elseif ( $order ) : ?>

        <?php do_action( 'woocommerce_before_thankyou', $order->get_id() ); ?>

        <!-- ── Succès ──────────────────────────────────────────── -->
        <div class="tma-thankyou-hero">
            <div class="tma-ty-icon tma-ty-icon--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <h1 class="tma-ty-title">Réservation confirmée !</h1>
            <p class="tma-ty-sub">
                Merci <strong><?php echo esc_html( $order->get_billing_first_name() ); ?></strong> !
                Votre commande <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong> a bien été reçue.
                Un email de confirmation a été envoyé à <strong><?php echo esc_html( $order->get_billing_email() ); ?></strong>.
            </p>

            <?php if ( $points_gagnes > 0 ) : ?>
            <div class="tma-ty-points">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                Vous avez gagné <strong><?php echo esc_html( $points_gagnes ); ?> points fidélité</strong> grâce à cette réservation !
            </div>
            <?php endif; ?>
        </div>

        <!-- ── Étapes visuelles ────────────────────────────────── -->
        <div class="tma-ty-steps">
            <div class="tma-ty-step tma-ty-step--done">
                <div class="tma-ty-step__num">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="tma-ty-step__label">Réservation reçue</div>
            </div>
            <div class="tma-ty-step__line"></div>
            <div class="tma-ty-step tma-ty-step--active">
                <div class="tma-ty-step__num">2</div>
                <div class="tma-ty-step__label">En traitement</div>
            </div>
            <div class="tma-ty-step__line"></div>
            <div class="tma-ty-step">
                <div class="tma-ty-step__num">3</div>
                <div class="tma-ty-step__label">Confirmation finale</div>
            </div>
            <div class="tma-ty-step__line"></div>
            <div class="tma-ty-step">
                <div class="tma-ty-step__num">4</div>
                <div class="tma-ty-step__label">Départ</div>
            </div>
        </div>

        <!-- ── Détails commande + résumé articles ──────────────── -->
        <div class="tma-ty-grid">

            <!-- Infos commande -->
            <div class="tma-ty-card">
                <h2 class="tma-ty-card__title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Détails de la commande
                </h2>
                <ul class="tma-ty-details">
                    <li>
                        <span>Numéro</span>
                        <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>
                    </li>
                    <li>
                        <span>Date</span>
                        <strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
                    </li>
                    <li>
                        <span>Statut</span>
                        <strong><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></strong>
                    </li>
                    <li>
                        <span>Moyen de paiement</span>
                        <strong><?php echo esc_html( $order->get_payment_method_title() ?: '—' ); ?></strong>
                    </li>
                    <li class="tma-ty-details__total">
                        <span>Total</span>
                        <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                    </li>
                </ul>
            </div>

            <!-- Articles réservés -->
            <div class="tma-ty-card">
                <h2 class="tma-ty-card__title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Offres réservées
                </h2>
                <div class="tma-ty-items">
                    <?php foreach ( $order->get_items() as $item ) :
                        $product     = $item->get_product();
                        $img_url     = $product ? get_the_post_thumbnail_url( $product->get_id(), 'thumbnail' ) : '';
                        $destination = $product ? get_post_meta( $product->get_id(), '_tma_destination', true ) : '';
                        $duree       = $product ? get_post_meta( $product->get_id(), '_tma_duration',    true ) : '';
                        $item_pts    = $product ? (int) get_post_meta( $product->get_id(), '_tma_points', true ) : 0;
                    ?>
                    <div class="tma-ty-item">
                        <div class="tma-ty-item__img">
                            <?php if ( $img_url ) : ?>
                                <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($item->get_name()); ?>" loading="lazy">
                            <?php else : ?>
                                <div class="tma-ty-item__placeholder">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="tma-ty-item__info">
                            <strong><?php echo esc_html( $item->get_name() ); ?></strong>
                            <?php if ( $destination || $duree ) : ?>
                            <div class="tma-ty-item__meta">
                                <?php if ($destination) : ?><span>📍 <?php echo esc_html($destination); ?></span><?php endif; ?>
                                <?php if ($duree) : ?><span>🕐 <?php echo esc_html($duree); ?></span><?php endif; ?>
                            </div>
                            <?php endif; ?>
                            <div class="tma-ty-item__qty">Quantité : <?php echo esc_html( $item->get_quantity() ); ?></div>
                        </div>
                        <div class="tma-ty-item__price">
                            <?php echo wc_price( $order->get_line_total( $item, true ) ); ?>
                            <?php if ( $item_pts ) : ?>
                                <span class="tma-ty-item__pts">+<?php echo esc_html($item_pts * $item->get_quantity()); ?> pts</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- .tma-ty-grid -->

        <!-- ── Coordonnées client ──────────────────────────────── -->
        <div class="tma-ty-contact-card">
            <h2 class="tma-ty-card__title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Vos coordonnées
            </h2>
            <div class="tma-ty-contact-grid">
                <div>
                    <h4>Informations de facturation</h4>
                    <address>
                        <?php echo esc_html( $order->get_formatted_billing_full_name() ); ?><br>
                        <?php if ( $order->get_billing_company() ) echo esc_html( $order->get_billing_company() ) . '<br>'; ?>
                        <?php echo esc_html( $order->get_billing_email() ); ?><br>
                        <?php echo esc_html( $order->get_billing_phone() ); ?>
                    </address>
                </div>
                <div>
                    <h4>Notes de commande</h4>
                    <?php
                    $notes = $order->get_customer_note();
                    echo $notes ? '<p>' . nl2br( esc_html($notes) ) . '</p>' : '<p style="color:var(--gris)">—</p>';
                    ?>
                </div>
            </div>
        </div>

        <!-- ── Actions finales ─────────────────────────────────── -->
        <div class="tma-ty-actions">
            <?php if ( is_user_logged_in() ) : ?>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url('orders') ); ?>" class="btn btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                Mes réservations
            </a>
            <?php endif; ?>
            <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="btn btn-outline">
                Continuer mes achats
            </a>
            <a href="https://wa.me/212500000000?text=<?php echo rawurlencode('Bonjour, j\'ai une question sur ma réservation #' . $order->get_order_number()); ?>"
               target="_blank" rel="noopener" class="btn btn-ghost tma-ty-whatsapp">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Support WhatsApp
            </a>
        </div>

        <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

    <?php else : ?>

        <!-- ── Pas de commande (accès direct) ─────────────────── -->
        <div class="tma-thankyou-hero" style="text-align:center">
            <div class="tma-ty-icon tma-ty-icon--success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <h1 class="tma-ty-title">Commande enregistrée !</h1>
            <p class="tma-ty-sub">Merci pour votre réservation. Notre équipe vous contactera prochainement.</p>
            <div class="tma-ty-actions" style="justify-content:center">
                <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="btn btn-primary">Voir nos offres</a>
            </div>
        </div>

    <?php endif; ?>

    </div><!-- .container -->
</div><!-- .tma-thankyou-wrap -->
