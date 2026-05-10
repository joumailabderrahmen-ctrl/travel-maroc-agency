<?php
/**
 * Email : commande complétée / voyage confirmé
 * Override du template WooCommerce customer-completed-order.php
 */
defined( 'ABSPATH' ) || exit;

// Infos voyage (première offre de la commande)
$destination = '';
$duree       = '';
if ( $order ) {
    foreach ( $order->get_items() as $item ) {
        $pid = $item->get_product_id();
        if ( $pid ) {
            $destination = get_post_meta( $pid, '_tma_destination', true );
            $duree       = get_post_meta( $pid, '_tma_duration',    true );
            break;
        }
    }
}

// Solde de points client
$points_solde = 0;
if ( $order && class_exists( 'TMB_Fidelite' ) && $order->get_user_id() ) {
    $profile = TMB_Fidelite::get_profile( $order->get_user_id() );
    if ( $profile ) {
        $points_solde = (int) $profile->points_fidelite_solde;
    }
}

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<!-- ── Bon voyage ────────────────────────────────────────────────── -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin:0 0 24px;">
    <tr>
        <td style="background:linear-gradient(135deg,#0d2b55 0%,#1a4a8c 100%);border-radius:10px;padding:24px 28px;text-align:center;">
            <p style="margin:0 0 8px;font-size:36px;">&#9992;&#65039;</p>
            <p style="margin:0 0 6px;font-size:20px;font-weight:700;color:#ffffff;">
                Votre voyage est confirm&eacute; !
            </p>
            <?php if ( $destination ) : ?>
            <p style="margin:0;font-size:14px;color:#f97316;font-weight:600;">
                &#128205; <?php echo esc_html( $destination ); ?>
                <?php if ( $duree ) : ?>&nbsp;&mdash;&nbsp;&#128336; <?php echo esc_html( $duree ); ?><?php endif; ?>
            </p>
            <?php endif; ?>
        </td>
    </tr>
</table>

<!-- ── Message ───────────────────────────────────────────────────── -->
<p style="margin:0 0 16px;font-size:16px;color:#374151;">
    Bonjour <strong><?php echo esc_html( $order->get_billing_first_name() ); ?></strong>,
</p>
<p style="margin:0 0 20px;color:#374151;line-height:1.7;">
    Excellente nouvelle ! Votre r&eacute;servation <strong>#<?php echo esc_html( $order->get_order_number() ); ?></strong>
    est d&eacute;sormais <strong style="color:#16a34a;">confirm&eacute;e et valid&eacute;e</strong>.
    Notre &eacute;quipe a finalis&eacute; tous les d&eacute;tails de votre voyage.
    Vous n'avez plus qu'&agrave; faire vos valises !
</p>

<!-- ── Points fidélité ───────────────────────────────────────────── -->
<?php if ( $points_solde > 0 ) : ?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin:0 0 24px;">
    <tr>
        <td style="background-color:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:14px 20px;text-align:left;">
            <p style="margin:0;font-size:14px;color:#713f12;">
                &#11088; Votre solde de points fid&eacute;lit&eacute; : <strong><?php echo esc_html( $points_solde ); ?> points</strong>.
                Utilisez-les pour b&eacute;n&eacute;ficier de r&eacute;ductions sur vos prochaines r&eacute;servations.
            </p>
        </td>
    </tr>
</table>
<?php endif; ?>

<!-- ── Checklist voyage ───────────────────────────────────────────── -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin:0 0 24px;">
    <tr>
        <td style="background-color:#f0fdf4;border-left:4px solid #16a34a;border-radius:0 8px 8px 0;padding:16px 20px;text-align:left;">
            <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:#15803d;text-transform:uppercase;letter-spacing:0.5px;">
                Checklist avant le d&eacute;part
            </p>
            <p style="margin:0;font-size:13px;color:#374151;line-height:1.8;">
                &#9989; V&eacute;rifiez la validit&eacute; de votre passeport / CIN<br>
                &#9989; Pr&eacute;parez vos documents de r&eacute;servation<br>
                &#9989; V&eacute;rifiez les informations de d&eacute;part communiqu&eacute;es par email<br>
                &#128222; En cas de question, contactez-nous imm&eacute;diatement
            </p>
        </td>
    </tr>
</table>

<!-- ── Détails de la commande ────────────────────────────────────── -->
<?php
do_action( 'woocommerce_email_order_details',   $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_order_meta',      $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
?>

<!-- ── Actions ──────────────────────────────────────────────────── -->
<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin:24px 0 8px;">
    <tr>
        <td style="text-align:left;">
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"
               style="display:inline-block;background-color:#0d2b55;color:#ffffff;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;padding:10px 20px;margin-right:10px;">
                &#128196; Mes r&eacute;servations
            </a>
            <a href="<?php echo esc_url( 'https://wa.me/' . get_option('tma_whatsapp_number','212500000000') . '?text=' . rawurlencode( 'Bonjour, j\'ai une question sur ma réservation #' . $order->get_order_number() ) ); ?>"
               style="display:inline-block;background-color:#25d366;color:#ffffff;font-family:Helvetica,Arial,sans-serif;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;padding:10px 20px;">
                &#128172; WhatsApp
            </a>
        </td>
    </tr>
</table>

<p style="margin:20px 0 0;font-size:14px;color:#374151;line-height:1.7;">
    Nous vous souhaitons un <strong>excellent voyage</strong> et esp&eacute;rons vous revoir tr&egrave;s bient&ocirc;t.<br>
    <strong>L'&eacute;quipe <?php echo esc_html( get_bloginfo( 'name' ) ); ?></strong> &#10084;&#65039;
</p>

<?php if ( $additional_content ) : ?>
    <?php echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) ); ?>
<?php endif; ?>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
