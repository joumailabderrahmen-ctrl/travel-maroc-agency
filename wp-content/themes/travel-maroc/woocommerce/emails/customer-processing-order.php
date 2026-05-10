<?php
/**
 * Email : commande reçue (en cours de traitement)
 * Override du template WooCommerce customer-processing-order.php
 */
defined( 'ABSPATH' ) || exit;

// Points gagnés sur cette commande
$points_gagnes = 0;
if ( $order && class_exists( 'TMB_Fidelite' ) && $order->get_user_id() ) {
    $profile = TMB_Fidelite::get_profile( $order->get_user_id() );
    if ( $profile ) {
        global $wpdb;
        $points_gagnes = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT points FROM {$wpdb->prefix}tma_historique_points
             WHERE client_id = %d AND wc_order_id = %d AND operation = 'GAIN' LIMIT 1",
            $profile->id,
            $order->get_id()
        ) );
    }
}

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<!-- ── Message d'accueil ─────────────────────────────────────────── -->
<p style="margin:0 0 16px;font-size:16px;color:#374151;">
    Bonjour <strong><?php echo esc_html( $order->get_billing_first_name() ); ?></strong>,
</p>
<p style="margin:0 0 16px;color:#374151;">
    Nous avons bien re&ccedil;u votre r&eacute;servation&nbsp;<strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>.
    Notre &eacute;quipe la traite actuellement et vous contactera dans les plus brefs d&eacute;lais pour confirmer tous les d&eacute;tails de votre voyage.
</p>

<!-- ── Badge points fidélité ─────────────────────────────────────── -->
<?php if ( $points_gagnes > 0 ) : ?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin:0 0 24px;">
    <tr>
        <td style="background-color:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:14px 20px;text-align:left;">
            <p style="margin:0;font-size:14px;color:#713f12;">
                &#11088; Vous avez gagn&eacute; <strong><?php echo esc_html( $points_gagnes ); ?> points fid&eacute;lit&eacute;</strong> gr&acirc;ce &agrave; cette r&eacute;servation.
                Consultez votre solde dans votre
                <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" style="color:#f97316;font-weight:600;">espace client</a>.
            </p>
        </td>
    </tr>
</table>
<?php endif; ?>

<!-- ── Étapes de traitement ──────────────────────────────────────── -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin:0 0 24px;">
    <tr>
        <td style="background-color:#f0f9ff;border-left:4px solid #0d2b55;border-radius:0 8px 8px 0;padding:16px 20px;text-align:left;">
            <p style="margin:0 0 8px;font-size:13px;font-weight:700;color:#0d2b55;text-transform:uppercase;letter-spacing:0.5px;">
                Prochaines &eacute;tapes
            </p>
            <p style="margin:0;font-size:13px;color:#374151;line-height:1.7;">
                &#9989; R&eacute;servation re&ccedil;ue<br>
                &#128336; En cours de traitement par notre &eacute;quipe<br>
                &#128203; Confirmation finale par email<br>
                &#9992;&#65039; Bon voyage !
            </p>
        </td>
    </tr>
</table>

<!-- ── Détails de la commande (table WC standard) ───────────────── -->
<?php
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta',    $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
?>

<!-- ── Bouton WhatsApp ───────────────────────────────────────────── -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin:24px 0 8px;">
    <tr>
        <td style="text-align:left;">
            <a href="<?php echo esc_url( 'https://wa.me/' . get_option('tma_whatsapp_number','212500000000') . '?text=' . rawurlencode( 'Bonjour, j\'ai une question sur ma réservation #' . $order->get_order_number() ) ); ?>"
               style="display:inline-block;background-color:#25d366;color:#ffffff;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;padding:10px 20px;">
                &#128172; Contacter le support WhatsApp
            </a>
        </td>
    </tr>
</table>

<p style="margin:16px 0 0;font-size:13px;color:#6b7280;">
    Merci de nous avoir fait confiance pour votre voyage. &agrave; bient&ocirc;t !<br>
    <strong>L'&eacute;quipe <?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong>
</p>

<?php if ( $additional_content ) : ?>
    <?php echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) ); ?>
<?php endif; ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
