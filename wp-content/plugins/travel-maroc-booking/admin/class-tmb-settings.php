<?php
defined( 'ABSPATH' ) || exit;

class TMB_Settings {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_page'     ], 32 );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ]     );
        add_action( 'admin_post_tma_create_shop_manager', [ __CLASS__, 'handle_create_shop_manager' ] );
        add_action( 'admin_post_tma_seed_demo_data',      [ __CLASS__, 'handle_seed_demo_data'      ] );
    }

    public static function register_page(): void {
        add_submenu_page(
            'tma-dashboard', 'Réglages TMA', 'Réglages',
            'manage_options', 'tma-settings', [ __CLASS__, 'render_page' ]
        );
    }

    public static function register_settings(): void {
        $group = 'tma_settings_group';

        $text_opts = [
            'tma_video_hero_url', 'tma_hero_bg_url', 'tma_analytics_id',
            'tma_contact_phone', 'tma_contact_email', 'tma_whatsapp_number',
            'tma_about_image_url', 'tma_facebook_url', 'tma_instagram_url',
        ];
        foreach ( $text_opts as $opt ) {
            register_setting( $group, $opt, [ 'sanitize_callback' => 'sanitize_text_field' ] );
        }
        register_setting( $group, 'tma_contact_address', [ 'sanitize_callback' => 'sanitize_textarea_field' ] );
        register_setting( $group, 'tma_about_text',      [ 'sanitize_callback' => 'wp_kses_post' ] );
    }

    public static function render_page(): void {
        if ( ! current_user_can('manage_options') ) wp_die('Accès refusé');

        $saved = isset($_GET['settings-updated']) && $_GET['settings-updated'];
        ?>
        <div class="wrap tma-admin">
        <h1>⚙️ Réglages Travel Maroc Agency</h1>

        <?php if ( $saved ) : ?>
        <div class="notice notice-success is-dismissible"><p>Réglages enregistrés avec succès.</p></div>
        <?php endif; ?>

        <?php if ( ! empty($_GET['smsg']) ) : ?>
        <div class="notice notice-<?php echo sanitize_text_field($_GET['smsgtype'] ?? 'info'); ?> is-dismissible">
            <p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['smsg'] ) ) ); ?></p>
        </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields('tma_settings_group'); ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-top:1.5rem">

                <!-- Héro & Médias -->
                <div class="tma-card">
                    <h3>🎬 Héro & Médias</h3>
                    <table class="form-table">
                        <tr><th>Vidéo hero (URL .mp4)</th>
                            <td><input type="url" name="tma_video_hero_url" class="regular-text"
                                value="<?php echo esc_attr(get_option('tma_video_hero_url')); ?>"
                                placeholder="https://…/video.mp4"></td></tr>
                        <tr><th>Image hero (fallback)</th>
                            <td><input type="url" name="tma_hero_bg_url" class="regular-text"
                                value="<?php echo esc_attr(get_option('tma_hero_bg_url')); ?>"></td></tr>
                        <tr><th>Image À-propos</th>
                            <td><input type="url" name="tma_about_image_url" class="regular-text"
                                value="<?php echo esc_attr(get_option('tma_about_image_url')); ?>"></td></tr>
                    </table>
                </div>

                <!-- Analytics -->
                <div class="tma-card">
                    <h3>📊 Analytics Google</h3>
                    <table class="form-table">
                        <tr><th>ID Measurement (GA4)</th>
                            <td>
                                <input type="text" name="tma_analytics_id" class="regular-text"
                                    value="<?php echo esc_attr(get_option('tma_analytics_id')); ?>"
                                    placeholder="G-XXXXXXXXXX">
                                <p class="description">Laisser vide pour désactiver le tracking.</p>
                            </td></tr>
                    </table>
                </div>

                <!-- Contact -->
                <div class="tma-card">
                    <h3>📞 Coordonnées</h3>
                    <table class="form-table">
                        <tr><th>Téléphone</th>
                            <td><input type="text" name="tma_contact_phone" class="regular-text"
                                value="<?php echo esc_attr(get_option('tma_contact_phone')); ?>"
                                placeholder="+212 5XX-XXXXXX"></td></tr>
                        <tr><th>WhatsApp (numéro intl.)</th>
                            <td><input type="text" name="tma_whatsapp_number" class="regular-text"
                                value="<?php echo esc_attr(get_option('tma_whatsapp_number', '212500000000')); ?>"
                                placeholder="212600000000">
                                <p class="description">Sans + ni espaces (ex: 212661234567)</p></td></tr>
                        <tr><th>Email contact</th>
                            <td><input type="email" name="tma_contact_email" class="regular-text"
                                value="<?php echo esc_attr(get_option('tma_contact_email')); ?>"
                                placeholder="contact@travelmaroc.ma"></td></tr>
                        <tr><th>Adresse</th>
                            <td><textarea name="tma_contact_address" rows="3" class="large-text"><?php echo esc_textarea(get_option('tma_contact_address')); ?></textarea></td></tr>
                    </table>
                </div>

                <!-- Réseaux sociaux -->
                <div class="tma-card">
                    <h3>🌐 Réseaux sociaux</h3>
                    <table class="form-table">
                        <tr><th>Facebook</th>
                            <td><input type="url" name="tma_facebook_url" class="regular-text"
                                value="<?php echo esc_attr(get_option('tma_facebook_url')); ?>"
                                placeholder="https://facebook.com/travelmaroc"></td></tr>
                        <tr><th>Instagram</th>
                            <td><input type="url" name="tma_instagram_url" class="regular-text"
                                value="<?php echo esc_attr(get_option('tma_instagram_url')); ?>"
                                placeholder="https://instagram.com/travelmaroc"></td></tr>
                    </table>
                </div>

                <!-- À propos -->
                <div class="tma-card" style="grid-column:1/-1">
                    <h3>🏢 Page À-propos — Texte de présentation</h3>
                    <table class="form-table">
                        <tr><th style="width:180px">Texte</th>
                            <td><textarea name="tma_about_text" rows="6" class="large-text" style="max-width:700px"><?php echo esc_textarea(get_option('tma_about_text')); ?></textarea></td></tr>
                    </table>
                </div>

            </div><!-- grid -->

            <?php submit_button('Enregistrer les réglages', 'primary large', 'submit', true, ['style'=>'margin-top:1rem']); ?>
        </form>

        <!-- Gestion des comptes -->
        <hr style="margin:2rem 0">
        <div class="tma-card" style="max-width:600px">
            <h3>👥 Comptes & Rôles</h3>
            <?php
            $sm = get_user_by('login', 'shop_manager_tma');
            if ( $sm ) :
            ?>
            <p>✅ Compte <strong>shop_manager_tma</strong> existe (ID #<?php echo $sm->ID; ?> — <a href="<?php echo esc_url(admin_url("user-edit.php?user_id={$sm->ID}")); ?>">Modifier</a>)</p>
            <?php else : ?>
            <p>Le compte <code>shop_manager_tma</code> n'existe pas encore.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('tma_create_shop_manager'); ?>
                <input type="hidden" name="action" value="tma_create_shop_manager">
                <button type="submit" class="button button-primary">Créer le compte Shop Manager</button>
            </form>
            <?php endif; ?>

            <p style="margin-top:1rem">
                Rôle <strong>tma_agent</strong> :
                <?php echo get_role('tma_agent') ? '✅ Existe' : '❌ Absent — désactiver/réactiver le plugin pour le créer'; ?>
            </p>
        </div>

        <!-- Données démo -->
        <hr style="margin:2rem 0">
        <div class="tma-card" style="max-width:600px">
            <h3>🗄️ Données de démonstration</h3>
            <p style="color:#6b7280;font-size:.9rem">Insère des guides, hôtels et transports d'exemple si les tables sont vides. Sans effet si des données existent déjà.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('tma_seed_demo_data'); ?>
                <input type="hidden" name="action" value="tma_seed_demo_data">
                <button type="submit" class="button button-secondary">Insérer les données démo</button>
            </form>
        </div>

        </div><!-- .wrap -->
        <?php
    }

    public static function handle_seed_demo_data(): void {
        check_admin_referer('tma_seed_demo_data');
        if ( ! current_user_can('manage_options') ) wp_die('Accès refusé');
        TMB_Activator::seed_resources_demo();
        wp_safe_redirect( add_query_arg([
            'page' => 'tma-settings',
            'smsg' => urlencode('Données démo insérées (guides, hôtels, transports).'),
            'smsgtype' => 'notice-success',
        ], admin_url('admin.php') ) );
        exit;
    }

    public static function handle_create_shop_manager(): void {
        check_admin_referer('tma_create_shop_manager');
        if ( ! current_user_can('manage_options') ) wp_die('Accès refusé');

        $result = TMB_Roles::ensure_shop_manager();
        if ( $result['created'] ) {
            $msg     = 'Compte créé — login: shop_manager_tma — mot de passe: ' . $result['password'] . ' (notez-le, il ne sera plus affiché)';
            $msgtype = 'success';
        } else {
            $msg     = 'Le compte shop_manager_tma existe déjà.';
            $msgtype = 'info';
        }
        wp_safe_redirect( add_query_arg([
            'page'    => 'tma-settings',
            'smsg'    => urlencode($msg),
            'smsgtype'=> 'notice-' . $msgtype,
        ], admin_url('admin.php') ) );
        exit;
    }
}
