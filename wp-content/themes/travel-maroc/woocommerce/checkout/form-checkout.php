<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
    echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
    return;
}
?>

<div class="tma-checkout-wrap">
    <div class="container">

        <!-- Étapes visuelles -->
        <div class="tma-checkout-steps">
            <div class="tma-step tma-step--done">
                <span class="tma-step__num">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </span>
                <span class="tma-step__label">Panier</span>
            </div>
            <div class="tma-step tma-step--active">
                <span class="tma-step__num">2</span>
                <span class="tma-step__label">Réservation</span>
            </div>
            <div class="tma-step">
                <span class="tma-step__num">3</span>
                <span class="tma-step__label">Confirmation</span>
            </div>
        </div>

        <form name="checkout" method="post"
              class="checkout woocommerce-checkout tma-checkout-form"
              action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
              enctype="multipart/form-data"
              aria-label="<?php esc_attr_e( 'Checkout', 'woocommerce' ); ?>">

            <div class="tma-checkout-grid">

                <!-- ── Colonne gauche : coordonnées ────────────── -->
                <div class="tma-checkout-fields">

                    <?php if ( $checkout->get_checkout_fields() ) : ?>
                        <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

                        <div class="tma-checkout-section">
                            <h2 class="tma-checkout-section__title">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                Vos coordonnées
                            </h2>
                            <?php do_action( 'woocommerce_checkout_billing' ); ?>
                        </div>

                        <?php
                        $shipping_fields = $checkout->get_checkout_fields( 'shipping' );
                        if ( $shipping_fields ) : ?>
                        <div class="tma-checkout-section">
                            <h2 class="tma-checkout-section__title">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                    <circle cx="12" cy="9" r="2.5"/>
                                </svg>
                                Adresse de livraison
                            </h2>
                            <?php do_action( 'woocommerce_checkout_shipping' ); ?>
                        </div>
                        <?php endif; ?>

                        <?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
                    <?php endif; ?>

                    <!-- Notes commande -->
                    <?php
                    $order_notes = $checkout->get_checkout_fields( 'order' );
                    if ( $order_notes ) : ?>
                    <div class="tma-checkout-section">
                        <h2 class="tma-checkout-section__title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            Remarques / Demandes spéciales
                        </h2>
                        <?php
                        foreach ( $order_notes as $key => $field ) {
                            woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
                        }
                        ?>
                    </div>
                    <?php endif; ?>

                </div><!-- .tma-checkout-fields -->

                <!-- ── Colonne droite : résumé + paiement ──────── -->
                <aside class="tma-checkout-sidebar">

                    <div class="tma-checkout-summary">
                        <h2 class="tma-checkout-section__title" id="order_review_heading">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                                <line x1="3" y1="6" x2="21" y2="6"/>
                                <path d="M16 10a4 4 0 01-8 0"/>
                            </svg>
                            Votre commande
                        </h2>

                        <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
                        <div id="order_review" class="woocommerce-checkout-review-order">
                            <?php do_action( 'woocommerce_checkout_order_review' ); ?>
                        </div>
                        <?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
                    </div>

                    <!-- Garanties -->
                    <ul class="tma-checkout-guarantees">
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/>
                                <path d="M9 12l2 2 4-4"/>
                            </svg>
                            Paiement 100% sécurisé
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.22 1.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.91A16 16 0 0016 17l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                            </svg>
                            Assistance 7j/7
                        </li>
                        <li>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                            Points fidélité crédités automatiquement
                        </li>
                    </ul>

                </aside><!-- .tma-checkout-sidebar -->

            </div><!-- .tma-checkout-grid -->

        </form>

    </div><!-- .container -->
</div><!-- .tma-checkout-wrap -->

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
