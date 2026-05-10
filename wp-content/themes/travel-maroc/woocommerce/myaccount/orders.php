<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<?php if ( $has_orders ) : ?>

<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
    <thead>
        <tr>
            <th class="woocommerce-orders-table__header"><span class="nobr">Réservation</span></th>
            <th class="woocommerce-orders-table__header"><span class="nobr">Date commande</span></th>
            <th class="woocommerce-orders-table__header"><span class="nobr">Départ prévu</span></th>
            <th class="woocommerce-orders-table__header"><span class="nobr">Voyageurs</span></th>
            <th class="woocommerce-orders-table__header"><span class="nobr">Statut</span></th>
            <th class="woocommerce-orders-table__header"><span class="nobr">Total</span></th>
            <th class="woocommerce-orders-table__header"><span class="nobr">Actions</span></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ( $customer_orders->orders as $customer_order ) :
        $order      = wc_get_order( $customer_order );
        $item_count = $order->get_item_count() - $order->get_item_count_refunded();
        $date_dep   = $order->get_meta('_tma_date_depart');
        $adultes    = (int) $order->get_meta('_tma_nb_adultes');
        $enfants    = (int) $order->get_meta('_tma_nb_enfants');

        // Nom de la première offre réservée
        $items      = $order->get_items();
        $first_item = reset( $items );
        $offre_nom  = $first_item ? $first_item->get_name() : '';

        $status_labels = [
            'pending'    => ['label' => 'En attente',    'color' => '#f59e0b'],
            'processing' => ['label' => 'En traitement', 'color' => '#3b82f6'],
            'on-hold'    => ['label' => 'En attente',    'color' => '#f59e0b'],
            'completed'  => ['label' => 'Confirmée',     'color' => '#10b981'],
            'cancelled'  => ['label' => 'Annulée',       'color' => '#ef4444'],
            'refunded'   => ['label' => 'Remboursée',    'color' => '#6b7280'],
            'failed'     => ['label' => 'Échouée',       'color' => '#ef4444'],
        ];
        $st    = $order->get_status();
        $badge = $status_labels[ $st ] ?? ['label' => ucfirst($st), 'color' => '#6b7280'];
    ?>
        <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr($st); ?> order">

            <th class="woocommerce-orders-table__cell" data-title="Réservation" scope="row">
                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" style="font-weight:700;color:var(--bleu,#0d2b55)">
                    #<?php echo esc_html( $order->get_order_number() ); ?>
                </a>
                <?php if ( $offre_nom ) : ?>
                <br><span style="font-size:.8rem;color:#6b7280;font-weight:400"><?php echo esc_html( $offre_nom ); ?></span>
                <?php endif; ?>
            </th>

            <td class="woocommerce-orders-table__cell" data-title="Date commande">
                <time datetime="<?php echo esc_attr( $order->get_date_created()->date('c') ); ?>">
                    <?php echo esc_html( $order->get_date_created()->date_i18n('d/m/Y') ); ?>
                </time>
            </td>

            <td class="woocommerce-orders-table__cell" data-title="Départ prévu">
                <?php if ( $date_dep ) : ?>
                    <strong style="color:var(--bleu,#0d2b55)">
                        <?php echo esc_html( date_i18n('d/m/Y', strtotime($date_dep)) ); ?>
                    </strong>
                <?php else : ?>
                    <span style="color:#9ca3af">—</span>
                <?php endif; ?>
            </td>

            <td class="woocommerce-orders-table__cell" data-title="Voyageurs">
                <?php if ( $adultes ) : ?>
                    <span title="<?php echo esc_attr($adultes); ?> adulte(s), <?php echo esc_attr($enfants); ?> enfant(s)">
                        👤 <?php echo $adultes; ?>
                        <?php if ( $enfants ) : ?>
                            &nbsp;🧒 <?php echo $enfants; ?>
                        <?php endif; ?>
                    </span>
                <?php else : ?>
                    <span style="color:#9ca3af">—</span>
                <?php endif; ?>
            </td>

            <td class="woocommerce-orders-table__cell" data-title="Statut">
                <span style="display:inline-block;padding:.2rem .7rem;border-radius:999px;background:<?php echo esc_attr($badge['color']); ?>22;color:<?php echo esc_attr($badge['color']); ?>;font-size:.8rem;font-weight:700">
                    <?php echo esc_html($badge['label']); ?>
                </span>
            </td>

            <td class="woocommerce-orders-table__cell" data-title="Total">
                <?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
                <?php if ( $item_count > 1 ) : ?>
                <br><span style="font-size:.8rem;color:#9ca3af"><?php echo $item_count; ?> offres</span>
                <?php endif; ?>
            </td>

            <td class="woocommerce-orders-table__cell" data-title="Actions">
                <?php
                $actions = wc_get_account_orders_actions( $order );
                if ( ! empty( $actions ) ) {
                    foreach ( $actions as $key => $action ) {
                        echo '<a href="' . esc_url($action['url']) . '" class="woocommerce-button button ' . sanitize_html_class($key) . '">' . esc_html($action['name']) . '</a> ';
                    }
                }
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
<div class="woocommerce-pagination woocommerce-Pagination" style="margin-top:1.5rem;display:flex;gap:.5rem">
    <?php if ( 1 !== $current_page ) : ?>
        <a class="woocommerce-button button" href="<?php echo esc_url( wc_get_endpoint_url('orders', $current_page - 1) ); ?>">← Précédent</a>
    <?php endif; ?>
    <?php if ( intval($customer_orders->max_num_pages) !== $current_page ) : ?>
        <a class="woocommerce-button button" href="<?php echo esc_url( wc_get_endpoint_url('orders', $current_page + 1) ); ?>">Suivant →</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php else : ?>

<div style="text-align:center;padding:3rem 1rem">
    <p style="font-size:1.1rem;color:#6b7280;margin-bottom:1.5rem">Vous n'avez pas encore de réservation.</p>
    <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="btn btn-primary">Découvrir nos offres</a>
</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
