<?php
defined( 'ABSPATH' ) || exit;

class TMB_Checkout {

    public static function init() {
        add_action( 'woocommerce_after_order_notes',                    [ __CLASS__, 'add_fields'           ] );
        add_action( 'woocommerce_checkout_process',                     [ __CLASS__, 'validate_fields'      ] );
        add_action( 'woocommerce_checkout_order_created',               [ __CLASS__, 'save_fields'          ] );
        add_action( 'woocommerce_admin_order_data_after_billing_address', [ __CLASS__, 'display_in_admin'   ], 10, 1 );
        add_filter( 'woocommerce_email_order_meta_fields',              [ __CLASS__, 'add_to_email'         ], 10, 3 );
        add_action( 'woocommerce_order_details_after_order_table',      [ __CLASS__, 'display_on_order_page'] );
    }

    public static function add_fields( WC_Checkout $checkout ): void {
        ?>
        <div class="tma-checkout-voyage" style="margin-top:1.5rem;padding:1.25rem;background:#f0f4f8;border-radius:10px;border-left:4px solid #0d2b55">
            <h3 style="margin:0 0 1rem;color:#0d2b55;font-size:1rem">🧳 Détails du voyage</h3>

            <?php woocommerce_form_field( 'tma_date_depart', [
                'type'              => 'text',
                'class'             => ['form-row-wide'],
                'label'             => 'Date de départ souhaitée',
                'required'          => true,
                'custom_attributes' => [ 'autocomplete' => 'off', 'min' => gmdate('Y-m-d', strtotime('+1 day')) ],
            ], $checkout->get_value('tma_date_depart') ); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <?php woocommerce_form_field( 'tma_nb_adultes', [
                    'type'              => 'number',
                    'class'             => ['form-row-wide'],
                    'label'             => 'Adultes (12 ans et +)',
                    'required'          => true,
                    'default'           => 1,
                    'custom_attributes' => [ 'min' => '1', 'max' => '20' ],
                ], $checkout->get_value('tma_nb_adultes') ?: 1 ); ?>

                <?php woocommerce_form_field( 'tma_nb_enfants', [
                    'type'              => 'number',
                    'class'             => ['form-row-wide'],
                    'label'             => 'Enfants (moins de 12 ans)',
                    'required'          => false,
                    'default'           => 0,
                    'custom_attributes' => [ 'min' => '0', 'max' => '10' ],
                ], $checkout->get_value('tma_nb_enfants') ?: 0 ); ?>
            </div>

            <?php woocommerce_form_field( 'tma_demandes_speciales', [
                'type'        => 'textarea',
                'class'       => ['form-row-wide'],
                'label'       => 'Demandes spéciales (optionnel)',
                'placeholder' => 'Régime alimentaire, accessibilité, chambre avec vue…',
                'required'    => false,
            ], $checkout->get_value('tma_demandes_speciales') ); ?>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var d = document.querySelector('#tma_date_depart');
            if (!d) return;
            d.type   = 'date';
            d.min    = new Date(Date.now() + 86400000).toISOString().slice(0, 10);
            d.style.cursor = 'pointer';

            // Récupérer les dates bloquées via AJAX et griser les dates invalides
            var productId = typeof tmaData !== 'undefined' ? (tmaData.productId || 0) : 0;
            if (!productId) {
                // Essayer de déduire depuis l'URL du produit dans les items du cart
                var cartItems = document.querySelectorAll('.cart_item');
                if (cartItems.length) {
                    var link = cartItems[0].querySelector('a[href*="product"]');
                    if (link) {
                        var m = link.href.match(/\?p=(\d+)|\/([^\/]+)\/?$/);
                    }
                }
            }

            if (typeof tmaData !== 'undefined' && tmaData.blockedDates && tmaData.blockedDates.length) {
                d.addEventListener('change', function(){
                    if (tmaData.blockedDates.indexOf(this.value) >= 0) {
                        this.setCustomValidity('Cette date est indisponible. Veuillez choisir une autre date.');
                        this.reportValidity();
                        this.value = '';
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });
        </script>
        <?php
    }

    public static function validate_fields(): void {
        $date = sanitize_text_field( wp_unslash( $_POST['tma_date_depart'] ?? '' ) );

        if ( ! $date ) {
            wc_add_notice( 'Veuillez indiquer une date de départ.', 'error' );
        } else {
            // Vérifier que la date n'est pas dans le passé
            if ( $date < gmdate('Y-m-d', strtotime('+1 day')) ) {
                wc_add_notice( 'La date de départ doit être au minimum demain.', 'error' );
            }
            // Vérifier les dates bloquées pour chaque produit du panier
            if ( class_exists('TMB_Availability') && WC()->cart ) {
                foreach ( WC()->cart->get_cart() as $item ) {
                    $product_id = (int)($item['product_id'] ?? 0);
                    if ( $product_id && ! TMB_Availability::is_date_available($product_id, $date) ) {
                        $name = get_the_title($product_id);
                        wc_add_notice( sprintf('La date %s est indisponible pour "%s". Choisissez une autre date.', esc_html($date), esc_html($name)), 'error' );
                    }
                }
            }
        }

        if ( empty( $_POST['tma_nb_adultes'] ) || (int) $_POST['tma_nb_adultes'] < 1 ) {
            wc_add_notice( 'Au moins 1 adulte est requis.', 'error' );
        }
    }

    public static function save_fields( WC_Order $order ): void {
        $order->update_meta_data( '_tma_date_depart',        sanitize_text_field( wp_unslash( $_POST['tma_date_depart']        ?? '' ) ) );
        $order->update_meta_data( '_tma_nb_adultes',         max( 1, (int) ( $_POST['tma_nb_adultes'] ?? 1 ) ) );
        $order->update_meta_data( '_tma_nb_enfants',         max( 0, (int) ( $_POST['tma_nb_enfants'] ?? 0 ) ) );
        $order->update_meta_data( '_tma_demandes_speciales', sanitize_textarea_field( wp_unslash( $_POST['tma_demandes_speciales'] ?? '' ) ) );
        $order->save();
    }

    public static function display_in_admin( WC_Order $order ): void {
        $date     = $order->get_meta('_tma_date_depart');
        $adultes  = $order->get_meta('_tma_nb_adultes');
        $enfants  = $order->get_meta('_tma_nb_enfants');
        $demandes = $order->get_meta('_tma_demandes_speciales');
        if ( ! $date ) return;
        echo '<div style="margin-top:1rem;padding:.75rem 1rem;background:#f0f4f8;border-radius:6px;border-left:3px solid #0d2b55">';
        echo '<strong style="display:block;margin-bottom:.4rem;color:#0d2b55">🧳 Détails du voyage</strong>';
        echo '<p style="margin:.2rem 0"><strong>Date départ :</strong> ' . esc_html($date) . '</p>';
        echo '<p style="margin:.2rem 0"><strong>Voyageurs :</strong> ' . (int)$adultes . ' adulte(s) — ' . (int)$enfants . ' enfant(s)</p>';
        if ( $demandes ) echo '<p style="margin:.2rem 0"><strong>Demandes :</strong> ' . esc_html($demandes) . '</p>';
        echo '</div>';
    }

    public static function add_to_email( array $fields, bool $sent_to_admin, WC_Order $order ): array {
        $date     = $order->get_meta('_tma_date_depart');
        $adultes  = $order->get_meta('_tma_nb_adultes');
        $enfants  = $order->get_meta('_tma_nb_enfants');
        $demandes = $order->get_meta('_tma_demandes_speciales');
        if ( $date ) {
            $fields['tma_date_depart'] = [ 'label' => 'Date de départ',  'value' => esc_html($date) ];
            $fields['tma_voyageurs']   = [ 'label' => 'Voyageurs',        'value' => (int)$adultes . ' adulte(s) — ' . (int)$enfants . ' enfant(s)' ];
        }
        if ( $demandes ) {
            $fields['tma_demandes'] = [ 'label' => 'Demandes spéciales', 'value' => esc_html($demandes) ];
        }
        return $fields;
    }

    public static function display_on_order_page( WC_Order $order ): void {
        $date    = $order->get_meta('_tma_date_depart');
        $adultes = $order->get_meta('_tma_nb_adultes');
        $enfants = $order->get_meta('_tma_nb_enfants');
        if ( ! $date ) return;
        echo '<section style="margin-top:1.5rem;padding:1rem 1.25rem;background:#f0f4f8;border-radius:8px">';
        echo '<h2 style="font-size:.95rem;margin:0 0 .75rem;color:#0d2b55">🧳 Détails du voyage</h2>';
        echo '<p style="margin:.3rem 0"><strong>Date de départ :</strong> ' . esc_html($date) . '</p>';
        echo '<p style="margin:.3rem 0"><strong>Voyageurs :</strong> ' . (int)$adultes . ' adulte(s) — ' . (int)$enfants . ' enfant(s)</p>';
        echo '</section>';
    }
}
