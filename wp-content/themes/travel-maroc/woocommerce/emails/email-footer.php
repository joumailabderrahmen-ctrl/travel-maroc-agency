<?php
defined( 'ABSPATH' ) || exit;

$email       = $email ?? null;
$store_name  = get_bloginfo( 'name', 'display' );
$admin_email = get_option( 'admin_email' );
?>
                                                                </div><!-- /#body_content_inner -->
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <!-- /Contenu -->
                                                </td>
                                            </tr>
                                        </table>
                                        <!-- /Corps -->
                                    </td>
                                </tr>
                            </table><!-- /#template_container -->

                        </td>
                    </tr>

                    <!-- ── Pied de page ─────────────────────────────────────── -->
                    <tr>
                        <td align="center" valign="top">
                            <table id="template_footer" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                <tr>
                                    <td style="padding:0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
                                            <tr>
                                                <td id="credit" style="border-top:3px solid #f97316;background-color:#0d2b55;border-radius:0 0 12px 12px;padding:24px 32px;text-align:center;">

                                                    <!-- Coordonnées -->
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" style="margin-bottom:16px;">
                                                        <tr>
                                                            <td style="text-align:center;">
                                                                <p style="margin:0 0 6px;font-size:14px;font-weight:700;color:#ffffff;">
                                                                    <?php echo esc_html( $store_name ); ?>
                                                                </p>
                                                                <p style="margin:0 0 4px;font-size:12px;color:#94a3b8;">
                                                                    &#128205; Maroc &nbsp;|&nbsp;
                                                                    &#128222; <a href="tel:+212500000000" style="color:#f97316;text-decoration:none;">+212 5 00 00 00 00</a> &nbsp;|&nbsp;
                                                                    &#9993; <a href="mailto:<?php echo esc_attr( $admin_email ); ?>" style="color:#f97316;text-decoration:none;"><?php echo esc_html( $admin_email ); ?></a>
                                                                </p>
                                                                <p style="margin:4px 0 0;font-size:12px;color:#94a3b8;">
                                                                    Support WhatsApp : <a href="https://wa.me/212500000000" style="color:#25d366;text-decoration:none;font-weight:600;">+212 5 00 00 00 00</a>
                                                                </p>
                                                            </td>
                                                        </tr>
                                                    </table>

                                                    <!-- Texte footer WC + mentions légales -->
                                                    <p style="margin:0;font-size:11px;color:#64748b;line-height:1.5;">
                                                        <?php
                                                        $footer_text = get_option( 'woocommerce_email_footer_text' );
                                                        if ( $footer_text ) {
                                                            echo wp_kses_post( wpautop( wptexturize( apply_filters( 'woocommerce_email_footer_text', $footer_text, $email ) ) ) );
                                                        } else {
                                                            echo '<span style="color:#64748b;">Cet e-mail a &eacute;t&eacute; envoy&eacute; automatiquement suite &agrave; votre r&eacute;servation.</span>';
                                                        }
                                                        ?>
                                                    </p>
                                                    <p style="margin:8px 0 0;font-size:11px;color:#475569;font-style:italic;">
                                                        &#10024; R&eacute;serv&eacute; avec passion &mdash; <?php echo esc_html( $store_name ); ?>
                                                    </p>

                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            <!-- /Pied de page -->
                        </td>
                    </tr>

                </table><!-- /#inner_wrapper -->

            </div><!-- /#wrapper -->
        </td>
        <td></td>
    </tr>
</table><!-- /#outer_wrapper -->

</body>
</html>
