<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

// Calcul des points que l'achat rapporterait
$cart_total  = WC()->cart->get_cart_contents_total();
$points_earn = floor( $cart_total / 10 );
?>

<div class="tma-cart-wrap">
    <div class="container">

        <div class="tma-cart-grid">

            <!-- ── Colonne gauche : articles ───────────────────── -->
            <div class="tma-cart-main">

                <?php if ( $points_earn > 0 ) : ?>
                <div class="tma-cart-points-notice">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    Cette commande vous rapportera <strong><?php echo esc_html( $points_earn ); ?> points fidélité</strong>
                </div>
                <?php endif; ?>

                <form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
                    <?php do_action( 'woocommerce_before_cart_table' ); ?>

                    <div class="tma-cart-items">
                        <?php do_action( 'woocommerce_before_cart_contents' ); ?>

                        <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                            $_product        = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                            $product_id      = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
                            $product_name    = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );

                            if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) continue;
                            if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) continue;

                            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                            $thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'woocommerce_thumbnail' ), $cart_item, $cart_item_key );

                            $destination = get_post_meta( $product_id, '_tma_destination', true );
                            $duree       = get_post_meta( $product_id, '_tma_duration', true );
                            $item_pts    = (int) get_post_meta( $product_id, '_tma_points', true );
                        ?>
                        <div class="tma-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                            <div class="tma-cart-item__img">
                                <?php if ( $product_permalink ) : ?>
                                    <a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo $thumbnail; ?></a>
                                <?php else : ?>
                                    <?php echo $thumbnail; ?>
                                <?php endif; ?>
                            </div>

                            <div class="tma-cart-item__info">
                                <div class="tma-cart-item__name">
                                    <?php if ( $product_permalink ) : ?>
                                        <a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo esc_html( $_product->get_name() ); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html( $_product->get_name() ); ?>
                                    <?php endif; ?>
                                </div>

                                <?php if ( $destination || $duree ) : ?>
                                <div class="tma-cart-item__meta">
                                    <?php if ( $destination ) : ?>
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                            <circle cx="12" cy="9" r="2.5"/>
                                        </svg>
                                        <?php echo esc_html( $destination ); ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if ( $duree ) : ?>
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <circle cx="12" cy="12" r="10"/>
                                            <polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                        <?php echo esc_html( $duree ); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>

                                <?php if ( $item_pts ) : ?>
                                <div class="tma-cart-item__pts">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                    </svg>
                                    +<?php echo esc_html( $item_pts * $cart_item['quantity'] ); ?> pts fidélité
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="tma-cart-item__qty">
                                <?php
                                $min_qty = $_product->is_sold_individually() ? 1 : 0;
                                $max_qty = $_product->is_sold_individually() ? 1 : $_product->get_max_purchase_quantity();
                                echo apply_filters( 'woocommerce_cart_item_quantity',
                                    woocommerce_quantity_input([
                                        'input_name'   => "cart[{$cart_item_key}][qty]",
                                        'input_value'  => $cart_item['quantity'],
                                        'max_value'    => $max_qty,
                                        'min_value'    => $min_qty,
                                        'product_name' => $product_name,
                                    ], $_product, false),
                                    $cart_item_key, $cart_item
                                );
                                ?>
                            </div>

                            <div class="tma-cart-item__price">
                                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                            </div>

                            <div class="tma-cart-item__remove">
                                <?php echo apply_filters( 'woocommerce_cart_item_remove_link',
                                    sprintf(
                                        '<a role="button" href="%s" class="tma-cart-remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </a>',
                                        esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                                        esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
                                        esc_attr( $product_id ),
                                        esc_attr( $_product->get_sku() )
                                    ),
                                    $cart_item_key
                                ); ?>
                            </div>

                        </div>
                        <?php endforeach; ?>

                        <?php do_action( 'woocommerce_cart_contents' ); ?>
                        <?php do_action( 'woocommerce_after_cart_contents' ); ?>
                    </div>

                    <?php do_action( 'woocommerce_after_cart_table' ); ?>

                    <!-- Coupon + Mise à jour -->
                    <div class="tma-cart-actions">
                        <?php if ( wc_coupons_enabled() ) : ?>
                        <div class="tma-coupon">
                            <label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon :', 'woocommerce' ); ?></label>
                            <input type="text" name="coupon_code" id="coupon_code" class="input-text"
                                   placeholder="Code promo" value="">
                            <button type="submit" name="apply_coupon" value="Appliquer" class="btn btn-outline">
                                Appliquer
                            </button>
                            <?php do_action( 'woocommerce_cart_coupon' ); ?>
                        </div>
                        <?php endif; ?>

                        <button type="submit" name="update_cart" value="Mettre à jour" class="btn btn-ghost">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="23 4 23 10 17 10"/>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                            </svg>
                            Mettre à jour
                        </button>

                        <?php do_action( 'woocommerce_cart_actions' ); ?>
                        <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
                    </div>

                </form>

            </div><!-- .tma-cart-main -->

            <!-- ── Colonne droite : récapitulatif ──────────────── -->
            <aside class="tma-cart-sidebar">
                <?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
                <div class="tma-cart-totals-wrap">
                    <?php do_action( 'woocommerce_cart_collaterals' ); ?>
                </div>
            </aside>

        </div><!-- .tma-cart-grid -->

    </div><!-- .container -->
</div><!-- .tma-cart-wrap -->

<?php do_action( 'woocommerce_after_cart' ); ?>
