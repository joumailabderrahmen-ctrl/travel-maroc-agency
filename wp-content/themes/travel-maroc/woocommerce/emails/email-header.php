<?php
defined( 'ABSPATH' ) || exit;

$store_name = get_bloginfo( 'name', 'display' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo esc_html( $store_name ); ?></title>
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0"
      style="background-color:#f0f4f8;padding:0;margin:0;font-family:Helvetica,Arial,sans-serif;text-align:center;">

<table id="outer_wrapper" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation"
       style="background-color:#f0f4f8;">
    <tr>
        <td></td>
        <td width="600">
            <div id="wrapper" style="margin:0 auto;padding:40px 0;width:100%;max-width:600px;">

                <table id="inner_wrapper" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" role="presentation">
                    <tr>
                        <td align="center" valign="top">

                            <!-- ── Marque TMA ───────────────────────────────────── -->
                            <div id="template_header_image" style="margin-bottom:0;">
                                <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                    <tr>
                                        <td style="background-color:#0d2b55;border-radius:12px 12px 0 0;padding:24px 32px;text-align:left;">
                                            <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:0.5px;">
                                                &#9992;&#65039; <?php echo esc_html( $store_name ); ?>
                                            </p>
                                            <p style="margin:4px 0 0;font-size:11px;color:#f97316;letter-spacing:2px;text-transform:uppercase;">
                                                Voyages &amp; R&eacute;servations au Maroc
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- ── Conteneur principal ─────────────────────────── -->
                            <table id="template_container" border="0" cellpadding="0" cellspacing="0" width="100%"
                                   role="presentation"
                                   style="background-color:#ffffff;box-shadow:0 4px 20px rgba(0,0,0,.08);">
                                <tr>
                                    <td align="center" valign="top">
                                        <!-- En-tête email -->
                                        <table id="template_header" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"
                                               style="background-color:#0d2b55;border-bottom:4px solid #f97316;">
                                            <tr>
                                                <td id="header_wrapper" style="padding:20px 32px;text-align:left;">
                                                    <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">
                                                        <?php echo esc_html( $email_heading ); ?>
                                                    </h1>
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- /En-tête -->
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" valign="top">
                                        <!-- Corps -->
                                        <table id="template_body" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                            <tr>
                                                <td valign="top" id="body_content" style="background-color:#ffffff;">
                                                    <!-- Contenu -->
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                                        <tr>
                                                            <td valign="top" id="body_content_inner_cell" style="padding:32px 40px 24px;">
                                                                <div id="body_content_inner"
                                                                     style="color:#374151;font-family:Helvetica,Arial,sans-serif;font-size:15px;line-height:1.65;text-align:left;">
