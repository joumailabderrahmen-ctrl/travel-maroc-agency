<?php
defined( 'ABSPATH' ) || exit;

class TMB_Resources_Admin {

    public static function init() {
        add_action( 'admin_menu',              [ __CLASS__, 'register_menus'    ], 31 );
        add_action( 'admin_post_tma_guide',    [ __CLASS__, 'handle_guide'      ] );
        add_action( 'admin_post_tma_hotel',    [ __CLASS__, 'handle_hotel'      ] );
        add_action( 'admin_post_tma_transport',[ __CLASS__, 'handle_transport'  ] );
    }

    public static function register_menus() {
        add_submenu_page( 'tma-dashboard', 'Guides',    'Guides',    'manage_woocommerce', 'tma-guides',    [ __CLASS__, 'page_guides'    ] );
        add_submenu_page( 'tma-dashboard', 'Hôtels',    'Hôtels',    'manage_woocommerce', 'tma-hotels',    [ __CLASS__, 'handle_hotels_page'   ] );
        add_submenu_page( 'tma-dashboard', 'Transport', 'Transport', 'manage_woocommerce', 'tma-transport', [ __CLASS__, 'page_transport' ] );
    }

    /* ════════════════════════════════════════════════════════════
       HELPERS COMMUNS
    ════════════════════════════════════════════════════════════ */

    private static function get_localisations(): array {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT id, nom, type, code_pays FROM {$wpdb->prefix}tma_localisation ORDER BY nom"
        ) ?: [];
    }

    private static function redirect_back( string $page, string $msg, string $type = 'updated' ): void {
        wp_safe_redirect( add_query_arg([
            'page' => $page, 'msg' => urlencode($msg), 'msgtype' => $type,
        ], admin_url('admin.php') ) );
        exit;
    }

    private static function show_notice(): void {
        $msg  = sanitize_text_field( $_GET['msg']     ?? '' );
        $type = sanitize_text_field( $_GET['msgtype'] ?? 'updated' );
        if ( $msg ) {
            $class = $type === 'error' ? 'notice-error' : 'notice-success';
            echo '<div class="notice ' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($msg) . '</p></div>';
        }
    }

    private static function form_head( string $title, string $back_page ): void {
        echo '<div class="wrap tma-admin">';
        echo '<h1>' . esc_html($title) . ' <a href="' . esc_url(admin_url("admin.php?page=$back_page")) . '" class="page-title-action">← Liste</a></h1>';
    }

    private static function field( string $label, string $html, bool $required = false ): void {
        echo '<tr>';
        echo '<th style="width:200px;text-align:left;padding:.6rem .75rem;font-weight:600;color:#374151">';
        echo esc_html($label);
        echo $required ? ' <span style="color:#ef4444">*</span>' : '';
        echo '</th>';
        echo '<td style="padding:.6rem .75rem">' . $html . '</td>';
        echo '</tr>';
    }

    private static function input( string $name, $value = '', string $type = 'text', array $attrs = [] ): string {
        $attr_str = '';
        foreach ( $attrs as $k => $v ) $attr_str .= ' ' . esc_attr($k) . '="' . esc_attr($v) . '"';
        return '<input type="' . esc_attr($type) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" style="width:100%;max-width:420px" class="regular-text"' . $attr_str . '>';
    }

    private static function select( string $name, array $options, $selected ): string {
        $html = '<select name="' . esc_attr($name) . '" style="max-width:420px;width:100%">';
        foreach ( $options as $val => $label ) {
            $html .= '<option value="' . esc_attr($val) . '" ' . selected($selected, $val, false) . '>' . esc_html($label) . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

    private static function textarea( string $name, string $value, int $rows = 3 ): string {
        return '<textarea name="' . esc_attr($name) . '" rows="' . $rows . '" style="width:100%;max-width:520px" class="large-text">' . esc_textarea($value) . '</textarea>';
    }

    private static function loc_options( array $locs ): array {
        $opts = [ '' => '— Choisir —' ];
        foreach ( $locs as $l ) {
            $flag = $l->code_pays ? '[' . strtoupper($l->code_pays) . '] ' : '';
            $opts[$l->id] = $flag . $l->nom . ' (' . $l->type . ')';
        }
        return $opts;
    }

    private static function etoiles( int $n ): string {
        return str_repeat('★', $n) . str_repeat('☆', 5 - $n);
    }

    /* ════════════════════════════════════════════════════════════
       GUIDES
    ════════════════════════════════════════════════════════════ */

    public static function page_guides(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'tma_guide';
        $locs  = self::get_localisations();
        $loc_map = array_column( $locs, 'nom', 'id' );

        // ── Formulaire create/edit ──────────────────────────────
        $action = sanitize_text_field( $_GET['action'] ?? 'list' );
        $edit_id = (int) ( $_GET['id'] ?? 0 );

        if ( in_array( $action, ['new','edit'], true ) ) {
            $row = $edit_id ? $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE id=%d", $edit_id) ) : null;
            self::form_head( $edit_id ? 'Modifier le guide' : 'Nouveau guide', 'tma-guides' );
            self::show_notice();
            ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:1rem">
                <?php wp_nonce_field('tma_guide_save'); ?>
                <input type="hidden" name="action" value="tma_guide">
                <input type="hidden" name="id"     value="<?php echo esc_attr($edit_id); ?>">
                <table class="form-table">
                    <?php
                    self::field('Localisation', self::select('localisation_id', self::loc_options($locs), $row->localisation_id ?? ''), true);
                    self::field('Prénom',        self::input('prenom', $row->prenom ?? ''), true);
                    self::field('Nom',           self::input('nom',    $row->nom    ?? ''), true);
                    self::field('Email',         self::input('email',  $row->email  ?? '', 'email'));
                    self::field('Téléphone',     self::input('telephone', $row->telephone ?? ''), true);
                    self::field('Langues',       self::input('langues', $row->langues ?? '', 'text', ['placeholder' => 'Arabe, Français, Anglais']));
                    self::field('Spécialités',   self::input('specialites', $row->specialites ?? '', 'text', ['placeholder' => 'Sahara, Villes impériales']));
                    self::field('Tarif/jour (MAD)', self::input('tarif_journalier', $row->tarif_journalier ?? '0', 'number', ['min'=>'0','step'=>'50']), true);
                    self::field('Expérience (ans)', self::input('experience_annees', $row->experience_annees ?? '1', 'number', ['min'=>'0','max'=>'50']));
                    self::field('Photo URL',     self::input('photo_url', $row->photo_url ?? '', 'url'));
                    self::field('Statut',        self::select('statut', ['ACTIF'=>'Actif','INACTIF'=>'Inactif'], $row->statut ?? 'ACTIF'));
                    ?>
                </table>
                <p><button type="submit" class="button button-primary button-large"><?php echo $edit_id ? 'Mettre à jour' : 'Créer le guide'; ?></button></p>
            </form>
            <?php
            echo '</div>';
            return;
        }

        // ── Liste ───────────────────────────────────────────────
        $guides = $wpdb->get_results("SELECT g.*, l.nom AS ville FROM $table g LEFT JOIN {$wpdb->prefix}tma_localisation l ON l.id=g.localisation_id ORDER BY g.nom, g.prenom");
        echo '<div class="wrap tma-admin">';
        echo '<h1>Guides touristiques <a href="' . esc_url(admin_url('admin.php?page=tma-guides&action=new')) . '" class="page-title-action">+ Nouveau</a></h1>';
        self::show_notice();
        ?>
        <table class="tma-table tma-table-full wp-list-table widefat fixed striped" style="margin-top:1rem">
            <thead><tr>
                <th>Nom complet</th>
                <th>Ville</th>
                <th>Langues</th>
                <th>Tarif/j</th>
                <th>Exp.</th>
                <th>Note</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $guides ) : foreach ( $guides as $g ) :
                $badge_color = $g->statut === 'ACTIF' ? '#10b981' : '#6b7280';
                ?>
                <tr>
                    <td><strong><?php echo esc_html("$g->prenom $g->nom"); ?></strong>
                        <?php if ($g->email) echo '<br><small style="color:#6b7280">' . esc_html($g->email) . '</small>'; ?>
                    </td>
                    <td><?php echo esc_html($g->ville ?: '—'); ?></td>
                    <td><?php echo esc_html($g->langues ?: '—'); ?></td>
                    <td><?php echo number_format((float)$g->tarif_journalier, 0, ',', ' ') . ' MAD'; ?></td>
                    <td><?php echo esc_html($g->experience_annees ?? '—'); ?> ans</td>
                    <td><?php echo $g->note_moyenne > 0 ? esc_html(number_format($g->note_moyenne,1)) . '/5' : '—'; ?></td>
                    <td><span class="tma-badge" style="background:<?php echo esc_attr($badge_color); ?>20;color:<?php echo esc_attr($badge_color); ?>"><?php echo esc_html($g->statut); ?></span></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url("admin.php?page=tma-guides&action=edit&id={$g->id}")); ?>" class="button button-small">Modifier</a>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url("admin-post.php?action=tma_guide&delete=1&id={$g->id}"), 'tma_guide_save')); ?>"
                           class="button button-small" style="color:#ef4444"
                           onclick="return confirm('Supprimer ce guide ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:#6b7280">Aucun guide. <a href="<?php echo esc_url(admin_url('admin.php?page=tma-guides&action=new')); ?>">Créer le premier →</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php
        echo '</div>';
    }

    public static function handle_guide(): void {
        check_admin_referer('tma_guide_save');
        if ( ! current_user_can('manage_woocommerce') ) wp_die('Accès refusé');

        global $wpdb;
        $table = $wpdb->prefix . 'tma_guide';
        $id    = (int) ( $_REQUEST['id'] ?? 0 );

        // Suppression
        if ( isset($_GET['delete']) ) {
            $wpdb->delete( $table, ['id' => $id] );
            TMB_Log_Admin::write([ 'action' => 'DELETE', 'entite_cible' => 'tma_guide', 'entite_id' => $id, 'notes' => 'Suppression guide' ]);
            self::redirect_back('tma-guides', 'Guide supprimé.');
        }

        // Validation
        $required = ['localisation_id','prenom','nom','telephone','tarif_journalier'];
        foreach ( $required as $field ) {
            if ( empty($_POST[$field]) ) {
                self::redirect_back('tma-guides', "Le champ '$field' est obligatoire.", 'error');
            }
        }

        $data = [
            'localisation_id'   => (int) $_POST['localisation_id'],
            'prenom'            => sanitize_text_field($_POST['prenom']),
            'nom'               => sanitize_text_field($_POST['nom']),
            'email'             => sanitize_email($_POST['email'] ?? ''),
            'telephone'         => sanitize_text_field($_POST['telephone']),
            'langues'           => sanitize_text_field($_POST['langues'] ?? ''),
            'specialites'       => sanitize_text_field($_POST['specialites'] ?? ''),
            'tarif_journalier'  => (float) $_POST['tarif_journalier'],
            'experience_annees' => (int) ($_POST['experience_annees'] ?? 0),
            'photo_url'         => esc_url_raw($_POST['photo_url'] ?? ''),
            'statut'            => in_array($_POST['statut'], ['ACTIF','INACTIF']) ? $_POST['statut'] : 'ACTIF',
        ];

        if ( $id ) {
            $wpdb->update( $table, $data, ['id' => $id] );
            TMB_Log_Admin::write([ 'action' => 'UPDATE', 'entite_cible' => 'tma_guide', 'entite_id' => $id, 'notes' => 'MàJ guide ' . $data['prenom'] . ' ' . $data['nom'] ]);
            self::redirect_back('tma-guides', 'Guide mis à jour.');
        } else {
            $wpdb->insert( $table, $data );
            $new_id = $wpdb->insert_id;
            TMB_Log_Admin::write([ 'action' => 'CREATE', 'entite_cible' => 'tma_guide', 'entite_id' => $new_id, 'notes' => 'Nouveau guide ' . $data['prenom'] . ' ' . $data['nom'] ]);
            self::redirect_back('tma-guides', 'Guide créé avec succès.');
        }
    }

    /* ════════════════════════════════════════════════════════════
       HÔTELS
    ════════════════════════════════════════════════════════════ */

    public static function handle_hotels_page(): void {
        self::page_hotels();
    }

    public static function page_hotels(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'tma_hotel';
        $locs  = self::get_localisations();

        $action  = sanitize_text_field( $_GET['action'] ?? 'list' );
        $edit_id = (int) ( $_GET['id'] ?? 0 );

        if ( in_array( $action, ['new','edit'], true ) ) {
            $row = $edit_id ? $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE id=%d", $edit_id) ) : null;
            self::form_head( $edit_id ? 'Modifier l\'hôtel' : 'Nouvel hôtel', 'tma-hotels' );
            self::show_notice();
            $etoiles_opts = [ '' => '— Non classé —', '1'=>'★ (1 étoile)','2'=>'★★','3'=>'★★★','4'=>'★★★★','5'=>'★★★★★' ];
            ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:1rem">
                <?php wp_nonce_field('tma_hotel_save'); ?>
                <input type="hidden" name="action" value="tma_hotel">
                <input type="hidden" name="id"     value="<?php echo esc_attr($edit_id); ?>">
                <table class="form-table">
                    <?php
                    self::field('Localisation',    self::select('localisation_id', self::loc_options($locs), $row->localisation_id ?? ''), true);
                    self::field('Nom de l\'hôtel', self::input('nom', $row->nom ?? ''), true);
                    self::field('Classement',      self::select('categorie_etoiles', $etoiles_opts, $row->categorie_etoiles ?? ''));
                    self::field('Adresse',         self::textarea('adresse_complete', $row->adresse_complete ?? '', 2));
                    self::field('Email',           self::input('email', $row->email ?? '', 'email'));
                    self::field('Téléphone',       self::input('telephone', $row->telephone ?? ''));
                    self::field('Site web',        self::input('site_web', $row->site_web ?? '', 'url'));
                    self::field('Description',     self::textarea('description', $row->description ?? '', 4));
                    self::field('Prix/nuit (MAD)', self::input('prix_chambre_nuit', $row->prix_chambre_nuit ?? '0', 'number', ['min'=>'0','step'=>'50']), true);
                    self::field('Nb. chambres',    self::input('capacite_chambres', $row->capacite_chambres ?? '', 'number', ['min'=>'1']));
                    self::field('Image URL',       self::input('image_url', $row->image_url ?? '', 'url'));
                    self::field('Statut',          self::select('statut', ['ACTIF'=>'Actif','INACTIF'=>'Inactif'], $row->statut ?? 'ACTIF'));
                    ?>
                </table>
                <p><button type="submit" class="button button-primary button-large"><?php echo $edit_id ? 'Mettre à jour' : 'Créer l\'hôtel'; ?></button></p>
            </form>
            <?php
            echo '</div>';
            return;
        }

        // Liste
        $hotels = $wpdb->get_results("SELECT h.*, l.nom AS ville FROM $table h LEFT JOIN {$wpdb->prefix}tma_localisation l ON l.id=h.localisation_id ORDER BY h.categorie_etoiles DESC, h.nom");
        echo '<div class="wrap tma-admin">';
        echo '<h1>Hôtels <a href="' . esc_url(admin_url('admin.php?page=tma-hotels&action=new')) . '" class="page-title-action">+ Nouveau</a></h1>';
        self::show_notice();
        ?>
        <table class="tma-table tma-table-full wp-list-table widefat fixed striped" style="margin-top:1rem">
            <thead><tr>
                <th>Nom</th><th>Ville</th><th>Étoiles</th>
                <th>Prix/nuit</th><th>Chambres</th><th>Note</th><th>Statut</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $hotels ) : foreach ( $hotels as $h ) :
                $badge = $h->statut === 'ACTIF' ? '#10b981' : '#6b7280';
                $etoiles_n = (int) $h->categorie_etoiles;
                ?>
                <tr>
                    <td><strong><?php echo esc_html($h->nom); ?></strong>
                        <?php if ($h->email) echo '<br><small style="color:#6b7280">' . esc_html($h->email) . '</small>'; ?>
                    </td>
                    <td><?php echo esc_html($h->ville ?: '—'); ?></td>
                    <td style="color:#f97316"><?php echo $etoiles_n ? str_repeat('★', $etoiles_n) : '—'; ?></td>
                    <td><?php echo number_format((float)$h->prix_chambre_nuit, 0, ',', ' ') . ' MAD'; ?></td>
                    <td><?php echo $h->capacite_chambres ? esc_html($h->capacite_chambres) . ' ch.' : '—'; ?></td>
                    <td><?php echo $h->note_moyenne > 0 ? number_format((float)$h->note_moyenne,1) . '/5' : '—'; ?></td>
                    <td><span class="tma-badge" style="background:<?php echo esc_attr($badge); ?>20;color:<?php echo esc_attr($badge); ?>"><?php echo esc_html($h->statut); ?></span></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url("admin.php?page=tma-hotels&action=edit&id={$h->id}")); ?>" class="button button-small">Modifier</a>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url("admin-post.php?action=tma_hotel&delete=1&id={$h->id}"), 'tma_hotel_save')); ?>"
                           class="button button-small" style="color:#ef4444"
                           onclick="return confirm('Supprimer cet hôtel ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:#6b7280">Aucun hôtel. <a href="<?php echo esc_url(admin_url('admin.php?page=tma-hotels&action=new')); ?>">Créer le premier →</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php echo '</div>';
    }

    public static function handle_hotel(): void {
        check_admin_referer('tma_hotel_save');
        if ( ! current_user_can('manage_woocommerce') ) wp_die('Accès refusé');

        global $wpdb;
        $table = $wpdb->prefix . 'tma_hotel';
        $id    = (int) ( $_REQUEST['id'] ?? 0 );

        if ( isset($_GET['delete']) ) {
            $wpdb->delete( $table, ['id' => $id] );
            TMB_Log_Admin::write([ 'action' => 'DELETE', 'entite_cible' => 'tma_hotel', 'entite_id' => $id ]);
            self::redirect_back('tma-hotels', 'Hôtel supprimé.');
        }

        if ( empty($_POST['nom']) || empty($_POST['localisation_id']) || empty($_POST['prix_chambre_nuit']) ) {
            self::redirect_back('tma-hotels', 'Champs obligatoires manquants.', 'error');
        }

        $etoiles = (int) ($_POST['categorie_etoiles'] ?? 0);
        $data = [
            'localisation_id'   => (int) $_POST['localisation_id'],
            'nom'               => sanitize_text_field($_POST['nom']),
            'categorie_etoiles' => $etoiles >= 1 && $etoiles <= 5 ? $etoiles : null,
            'adresse_complete'  => sanitize_textarea_field($_POST['adresse_complete'] ?? ''),
            'email'             => sanitize_email($_POST['email'] ?? ''),
            'telephone'         => sanitize_text_field($_POST['telephone'] ?? ''),
            'site_web'          => esc_url_raw($_POST['site_web'] ?? ''),
            'description'       => sanitize_textarea_field($_POST['description'] ?? ''),
            'prix_chambre_nuit' => (float) $_POST['prix_chambre_nuit'],
            'capacite_chambres' => $_POST['capacite_chambres'] ? (int) $_POST['capacite_chambres'] : null,
            'image_url'         => esc_url_raw($_POST['image_url'] ?? ''),
            'statut'            => in_array($_POST['statut'], ['ACTIF','INACTIF']) ? $_POST['statut'] : 'ACTIF',
        ];

        if ( $id ) {
            $wpdb->update( $table, $data, ['id' => $id] );
            TMB_Log_Admin::write([ 'action' => 'UPDATE', 'entite_cible' => 'tma_hotel', 'entite_id' => $id, 'notes' => 'MàJ hôtel ' . $data['nom'] ]);
            self::redirect_back('tma-hotels', 'Hôtel mis à jour.');
        } else {
            $wpdb->insert( $table, $data );
            TMB_Log_Admin::write([ 'action' => 'CREATE', 'entite_cible' => 'tma_hotel', 'entite_id' => $wpdb->insert_id, 'notes' => 'Nouvel hôtel ' . $data['nom'] ]);
            self::redirect_back('tma-hotels', 'Hôtel créé avec succès.');
        }
    }

    /* ════════════════════════════════════════════════════════════
       TRANSPORT
    ════════════════════════════════════════════════════════════ */

    public static function page_transport(): void {
        global $wpdb;
        $table = $wpdb->prefix . 'tma_transport';
        $locs  = self::get_localisations();

        $action  = sanitize_text_field( $_GET['action'] ?? 'list' );
        $edit_id = (int) ( $_GET['id'] ?? 0 );

        $type_labels = [
            'AVION'         => '✈ Avion',
            'BUS'           => '🚌 Bus',
            'TRAIN'         => '🚆 Train',
            'VOITURE_PRIVEE'=> '🚗 Voiture privée',
            'BATEAU'        => '⛴ Bateau',
        ];

        if ( in_array( $action, ['new','edit'], true ) ) {
            $row = $edit_id ? $wpdb->get_row( $wpdb->prepare("SELECT * FROM $table WHERE id=%d", $edit_id) ) : null;
            self::form_head( $edit_id ? 'Modifier la liaison' : 'Nouvelle liaison transport', 'tma-transport' );
            self::show_notice();
            ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:1rem">
                <?php wp_nonce_field('tma_transport_save'); ?>
                <input type="hidden" name="action" value="tma_transport">
                <input type="hidden" name="id"     value="<?php echo esc_attr($edit_id); ?>">
                <table class="form-table">
                    <?php
                    self::field('Départ',           self::select('localisation_depart_id',  self::loc_options($locs), $row->localisation_depart_id  ?? ''), true);
                    self::field('Arrivée',           self::select('localisation_arrivee_id', self::loc_options($locs), $row->localisation_arrivee_id ?? ''), true);
                    self::field('Type',              self::select('type', ['' => '— Choisir —'] + $type_labels, $row->type ?? ''), true);
                    self::field('Compagnie',         self::input('compagnie', $row->compagnie ?? '', 'text', ['placeholder'=>'Royal Air Maroc, ONCF…']));
                    self::field('N° liaison',        self::input('numero_liaison', $row->numero_liaison ?? '', 'text', ['placeholder'=>'AT 600']));
                    self::field('Durée (minutes)',   self::input('duree_trajet_minutes', $row->duree_trajet_minutes ?? '', 'number', ['min'=>'1']));
                    self::field('Prix/pers. (MAD)',  self::input('prix_par_personne', $row->prix_par_personne ?? '0', 'number', ['min'=>'0','step'=>'10']), true);
                    self::field('Capacité (places)', self::input('capacite', $row->capacite ?? '', 'number', ['min'=>'1']));
                    self::field('Statut',            self::select('statut', ['ACTIF'=>'Actif','INACTIF'=>'Inactif'], $row->statut ?? 'ACTIF'));
                    ?>
                </table>
                <p><button type="submit" class="button button-primary button-large"><?php echo $edit_id ? 'Mettre à jour' : 'Créer la liaison'; ?></button></p>
            </form>
            <?php
            echo '</div>';
            return;
        }

        // Liste
        $liaisons = $wpdb->get_results(
            "SELECT t.*,
                    ld.nom AS ville_depart,
                    la.nom AS ville_arrivee
             FROM $table t
             LEFT JOIN {$wpdb->prefix}tma_localisation ld ON ld.id = t.localisation_depart_id
             LEFT JOIN {$wpdb->prefix}tma_localisation la ON la.id = t.localisation_arrivee_id
             ORDER BY t.type, ld.nom"
        );

        echo '<div class="wrap tma-admin">';
        echo '<h1>Transport <a href="' . esc_url(admin_url('admin.php?page=tma-transport&action=new')) . '" class="page-title-action">+ Nouvelle liaison</a></h1>';
        self::show_notice();
        ?>
        <table class="tma-table tma-table-full wp-list-table widefat fixed striped" style="margin-top:1rem">
            <thead><tr>
                <th>Liaison</th><th>Type</th><th>Compagnie</th>
                <th>Durée</th><th>Prix/pers.</th><th>Capacité</th><th>Statut</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if ( $liaisons ) : foreach ( $liaisons as $t ) :
                $badge = $t->statut === 'ACTIF' ? '#10b981' : '#6b7280';
                $duree = $t->duree_trajet_minutes ? self::format_duree((int)$t->duree_trajet_minutes) : '—';
                $label = $type_labels[$t->type] ?? $t->type;
                ?>
                <tr>
                    <td><strong><?php echo esc_html($t->ville_depart . ' → ' . $t->ville_arrivee); ?></strong>
                        <?php if ($t->numero_liaison) echo '<br><small style="color:#6b7280">' . esc_html($t->numero_liaison) . '</small>'; ?>
                    </td>
                    <td><?php echo esc_html($label); ?></td>
                    <td><?php echo esc_html($t->compagnie ?: '—'); ?></td>
                    <td><?php echo esc_html($duree); ?></td>
                    <td><?php echo number_format((float)$t->prix_par_personne, 0, ',', ' ') . ' MAD'; ?></td>
                    <td><?php echo $t->capacite ? esc_html($t->capacite) . ' places' : '—'; ?></td>
                    <td><span class="tma-badge" style="background:<?php echo esc_attr($badge); ?>20;color:<?php echo esc_attr($badge); ?>"><?php echo esc_html($t->statut); ?></span></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url("admin.php?page=tma-transport&action=edit&id={$t->id}")); ?>" class="button button-small">Modifier</a>
                        <a href="<?php echo esc_url(wp_nonce_url(admin_url("admin-post.php?action=tma_transport&delete=1&id={$t->id}"), 'tma_transport_save')); ?>"
                           class="button button-small" style="color:#ef4444"
                           onclick="return confirm('Supprimer cette liaison ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:#6b7280">Aucune liaison. <a href="<?php echo esc_url(admin_url('admin.php?page=tma-transport&action=new')); ?>">Créer la première →</a></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <?php echo '</div>';
    }

    public static function handle_transport(): void {
        check_admin_referer('tma_transport_save');
        if ( ! current_user_can('manage_woocommerce') ) wp_die('Accès refusé');

        global $wpdb;
        $table = $wpdb->prefix . 'tma_transport';
        $id    = (int) ( $_REQUEST['id'] ?? 0 );

        if ( isset($_GET['delete']) ) {
            $wpdb->delete( $table, ['id' => $id] );
            TMB_Log_Admin::write([ 'action' => 'DELETE', 'entite_cible' => 'tma_transport', 'entite_id' => $id ]);
            self::redirect_back('tma-transport', 'Liaison supprimée.');
        }

        $types_valides = ['AVION','BUS','TRAIN','VOITURE_PRIVEE','BATEAU'];
        if ( empty($_POST['localisation_depart_id']) || empty($_POST['localisation_arrivee_id'])
             || empty($_POST['type']) || ! in_array($_POST['type'], $types_valides)
             || empty($_POST['prix_par_personne']) ) {
            self::redirect_back('tma-transport', 'Champs obligatoires manquants.', 'error');
        }

        $data = [
            'localisation_depart_id'  => (int) $_POST['localisation_depart_id'],
            'localisation_arrivee_id' => (int) $_POST['localisation_arrivee_id'],
            'type'                    => $_POST['type'],
            'compagnie'               => sanitize_text_field($_POST['compagnie'] ?? ''),
            'numero_liaison'          => sanitize_text_field($_POST['numero_liaison'] ?? ''),
            'duree_trajet_minutes'    => $_POST['duree_trajet_minutes'] ? (int) $_POST['duree_trajet_minutes'] : null,
            'prix_par_personne'       => (float) $_POST['prix_par_personne'],
            'capacite'                => $_POST['capacite'] ? (int) $_POST['capacite'] : null,
            'statut'                  => in_array($_POST['statut'], ['ACTIF','INACTIF']) ? $_POST['statut'] : 'ACTIF',
        ];

        if ( $id ) {
            $wpdb->update( $table, $data, ['id' => $id] );
            TMB_Log_Admin::write([ 'action' => 'UPDATE', 'entite_cible' => 'tma_transport', 'entite_id' => $id ]);
            self::redirect_back('tma-transport', 'Liaison mise à jour.');
        } else {
            $wpdb->insert( $table, $data );
            TMB_Log_Admin::write([ 'action' => 'CREATE', 'entite_cible' => 'tma_transport', 'entite_id' => $wpdb->insert_id ]);
            self::redirect_back('tma-transport', 'Liaison créée avec succès.');
        }
    }

    private static function format_duree( int $minutes ): string {
        if ( $minutes < 60 ) return "{$minutes} min";
        $h = (int) floor( $minutes / 60 );
        $m = $minutes % 60;
        return $m > 0 ? "{$h}h{$m}" : "{$h}h";
    }
}
