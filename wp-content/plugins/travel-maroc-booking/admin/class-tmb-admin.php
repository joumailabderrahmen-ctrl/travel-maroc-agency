<?php
defined( 'ABSPATH' ) || exit;

class TMB_Admin {

    public static function init() {
        add_action( 'admin_menu',            [ __CLASS__, 'register_menu'  ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
        add_action( 'admin_post_tma_destination',    [ __CLASS__, 'handle_destination'    ] );
        add_action( 'admin_post_tma_export_reservations', [ __CLASS__, 'export_reservations' ] );
    }

    public static function register_menu() {
        add_menu_page(
            'Travel Maroc Agency',
            'Travel Maroc',
            'manage_woocommerce',
            'tma-dashboard',
            [ __CLASS__, 'page_dashboard' ],
            'dashicons-location-alt',
            30
        );
        add_submenu_page( 'tma-dashboard', 'Dashboard',       'Dashboard',       'manage_woocommerce', 'tma-dashboard',      [ __CLASS__, 'page_dashboard'   ] );
        add_submenu_page( 'tma-dashboard', 'Clients',         'Clients',         'manage_woocommerce', 'tma-clients',        [ __CLASS__, 'page_clients'     ] );
        add_submenu_page( 'tma-dashboard', 'Fidélité',        'Fidélité',        'manage_woocommerce', 'tma-fidelite',       [ __CLASS__, 'page_fidelite'    ] );
        add_submenu_page( 'tma-dashboard', 'Notifications',   'Notifications',   'manage_woocommerce', 'tma-notifications',  [ __CLASS__, 'page_notifs'      ] );
        add_submenu_page( 'tma-dashboard', 'Journal d\'audit','Journal d\'audit','manage_options',     'tma-logs',           [ __CLASS__, 'page_logs'        ] );
        add_submenu_page( 'tma-dashboard', 'Destinations',    'Destinations',    'manage_woocommerce', 'tma-destinations',   [ __CLASS__, 'page_destinations' ] );
    }

    public static function enqueue_assets( string $hook ) {
        if ( strpos( $hook, 'tma-' ) === false ) return;
        wp_enqueue_style( 'tma-admin', TMB_URL . 'admin/admin.css', [], TMB_VERSION );
    }

    // ── DASHBOARD ───────────────────────────────────────────
    public static function page_dashboard() {
        global $wpdb;

        // KPIs — requêtes directes
        $rev_total    = (float)($wpdb->get_var("SELECT SUM(meta_value) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE pm.meta_key='_order_total' AND p.post_status='wc-completed'") ?? 0);
        $rev_mois     = (float)($wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE pm.meta_key='_order_total' AND p.post_status='wc-completed' AND DATE_FORMAT(p.post_date,'%%Y-%%m')=%s", gmdate('Y-m'))) ?? 0);
        $nb_clients   = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tma_client_profile");
        $nb_commandes = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='shop_order' AND post_status NOT IN('wc-cancelled','trash')");
        $pts_total    = (int)($wpdb->get_var("SELECT SUM(points) FROM {$wpdb->prefix}tma_historique_points WHERE operation='GAIN'") ?? 0);
        $notifs_att   = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}tma_notification WHERE statut='EN_ATTENTE'");

        $clients_par_type = $wpdb->get_results("SELECT t.libelle, t.couleur_badge, COUNT(p.id) AS nb FROM {$wpdb->prefix}tma_type_client t LEFT JOIN {$wpdb->prefix}tma_client_profile p ON p.type_client_id=t.id GROUP BY t.id ORDER BY t.ordre_affichage");
        $logs_recents     = TMB_Log_Admin::get_recent(10);
        $offres_top       = $wpdb->get_results("SELECT p.post_title, COUNT(*) AS nb FROM {$wpdb->prefix}woocommerce_order_items oi JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id=oi.order_item_id JOIN {$wpdb->posts} p ON p.ID=oim.meta_value WHERE oi.order_item_type='line_item' AND oim.meta_key='_product_id' GROUP BY p.ID ORDER BY nb DESC LIMIT 5");
        ?>
        <div class="wrap tma-admin">
        <h1 style="display:flex;align-items:center;justify-content:space-between">
            <span>🌍 Travel Maroc Agency — Dashboard</span>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0">
                <?php wp_nonce_field('tma_export_reservations'); ?>
                <input type="hidden" name="action" value="tma_export_reservations">
                <button type="submit" class="button button-primary">⬇ Exporter CSV réservations</button>
            </form>
        </h1>

        <div class="tma-kpis">
            <div class="tma-kpi">
                <div class="tma-kpi-value"><?php echo number_format($rev_total,0,',',' '); ?> MAD</div>
                <div class="tma-kpi-label">Revenus totaux</div>
            </div>
            <div class="tma-kpi tma-kpi-orange">
                <div class="tma-kpi-value"><?php echo number_format($rev_mois,0,',',' '); ?> MAD</div>
                <div class="tma-kpi-label">Revenus ce mois</div>
            </div>
            <div class="tma-kpi">
                <div class="tma-kpi-value"><?php echo $nb_commandes; ?></div>
                <div class="tma-kpi-label">Réservations</div>
            </div>
            <div class="tma-kpi">
                <div class="tma-kpi-value"><?php echo $nb_clients; ?></div>
                <div class="tma-kpi-label">Clients inscrits</div>
            </div>
            <div class="tma-kpi tma-kpi-purple">
                <div class="tma-kpi-value"><?php echo number_format($pts_total,0,',',' '); ?></div>
                <div class="tma-kpi-label">Points distribués</div>
            </div>
            <div class="tma-kpi tma-kpi-red">
                <div class="tma-kpi-value"><?php echo $notifs_att; ?></div>
                <div class="tma-kpi-label">Notifs en attente</div>
            </div>
        </div>

        <div class="tma-grid-2">
            <div class="tma-card">
                <h3>Clients par type</h3>
                <table class="tma-table">
                    <tr><th>Type</th><th>Clients</th></tr>
                    <?php foreach ($clients_par_type as $ct) : ?>
                    <tr>
                        <td><span class="tma-badge" style="background:<?php echo esc_attr($ct->couleur_badge); ?>22;color:<?php echo esc_attr($ct->couleur_badge); ?>"><?php echo esc_html($ct->libelle); ?></span></td>
                        <td><strong><?php echo (int)$ct->nb; ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div class="tma-card">
                <h3>Top 5 offres réservées</h3>
                <table class="tma-table">
                    <tr><th>Offre</th><th>Réservations</th></tr>
                    <?php foreach ($offres_top as $o) : ?>
                    <tr><td><?php echo esc_html($o->post_title); ?></td><td><strong><?php echo (int)$o->nb; ?></strong></td></tr>
                    <?php endforeach;
                    if (empty($offres_top)) echo '<tr><td colspan="2" style="text-align:center;color:#999">Aucune réservation</td></tr>';
                    ?>
                </table>
            </div>
        </div>

        <div class="tma-card">
            <h3>Dernières actions admin</h3>
            <table class="tma-table">
                <tr><th>Date</th><th>Admin</th><th>Action</th><th>Entité</th><th>ID</th><th>IP</th></tr>
                <?php foreach ($logs_recents as $log) : ?>
                <tr>
                    <td><?php echo esc_html($log['date_action']); ?></td>
                    <td><?php echo esc_html($log['admin_name'] ?? '—'); ?></td>
                    <td><code><?php echo esc_html($log['action']); ?></code></td>
                    <td><?php echo esc_html($log['entite_cible']); ?></td>
                    <td><?php echo $log['entite_id'] ? '#'.(int)$log['entite_id'] : '—'; ?></td>
                    <td><?php echo esc_html($log['ip_address']); ?></td>
                </tr>
                <?php endforeach;
                if (empty($logs_recents)) echo '<tr><td colspan="6" style="text-align:center;color:#999">Aucun log</td></tr>';
                ?>
            </table>
        </div>
        </div>
        <?php
    }

    // ── CLIENTS ─────────────────────────────────────────────
    public static function page_clients() {
        global $wpdb;
        $clients = $wpdb->get_results("
            SELECT p.*, u.display_name, u.user_email, t.libelle AS type_libelle, t.couleur_badge
            FROM {$wpdb->prefix}tma_client_profile p
            JOIN {$wpdb->users} u ON u.ID = p.wp_user_id
            JOIN {$wpdb->prefix}tma_type_client t ON t.id = p.type_client_id
            ORDER BY p.date_inscription DESC
        ");
        ?>
        <div class="wrap tma-admin">
        <h1>Clients (<?php echo count($clients); ?>)</h1>
        <table class="tma-table tma-table-full">
            <tr><th>Client</th><th>Email</th><th>Type</th><th>Points</th><th>Statut</th><th>Inscrit le</th></tr>
            <?php foreach ($clients as $c) : ?>
            <tr>
                <td><strong><?php echo esc_html($c->display_name); ?></strong></td>
                <td><?php echo esc_html($c->user_email); ?></td>
                <td><span class="tma-badge" style="background:<?php echo esc_attr($c->couleur_badge); ?>22;color:<?php echo esc_attr($c->couleur_badge); ?>"><?php echo esc_html($c->type_libelle); ?></span></td>
                <td><strong><?php echo (int)$c->points_fidelite_solde; ?></strong> pts</td>
                <td><?php echo esc_html($c->statut); ?></td>
                <td><?php echo esc_html(substr($c->date_inscription,0,10)); ?></td>
            </tr>
            <?php endforeach;
            if (empty($clients)) echo '<tr><td colspan="6" style="text-align:center;color:#999">Aucun client</td></tr>';
            ?>
        </table>
        </div>
        <?php
    }

    // ── FIDÉLITÉ ────────────────────────────────────────────
    public static function page_fidelite() {
        global $wpdb;
        $types = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}tma_type_client ORDER BY ordre_affichage");
        $hist  = $wpdb->get_results("SELECT h.*, u.display_name FROM {$wpdb->prefix}tma_historique_points h JOIN {$wpdb->prefix}tma_client_profile p ON p.id=h.client_id JOIN {$wpdb->users} u ON u.ID=p.wp_user_id ORDER BY h.date_operation DESC LIMIT 30");
        ?>
        <div class="wrap tma-admin">
        <h1>Programme de Fidélité</h1>
        <div class="tma-grid-2">
            <div class="tma-card">
                <h3>Types clients & remises</h3>
                <table class="tma-table">
                    <tr><th>Type</th><th>Remise</th><th>Seuil points</th><th>Pts n'expirent pas</th></tr>
                    <?php foreach ($types as $t) : ?>
                    <tr>
                        <td><span class="tma-badge" style="background:<?php echo esc_attr($t->couleur_badge); ?>22;color:<?php echo esc_attr($t->couleur_badge); ?>"><?php echo esc_html($t->libelle); ?></span></td>
                        <td><strong><?php echo $t->taux_remise; ?>%</strong></td>
                        <td><?php echo number_format($t->seuil_points,0,',',' '); ?> pts</td>
                        <td><?php echo $t->points_jamais_expirent ? '✅' : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="tma-card">
                <h3>Dernières opérations points</h3>
                <table class="tma-table">
                    <tr><th>Client</th><th>Op.</th><th>Points</th><th>Solde</th><th>Date</th></tr>
                    <?php foreach ($hist as $h) : ?>
                    <tr>
                        <td><?php echo esc_html($h->display_name); ?></td>
                        <td><code><?php echo esc_html($h->operation); ?></code></td>
                        <td style="color:<?php echo (int)$h->points>0?'#10b981':'#ef4444'; ?>"><?php echo ((int)$h->points>0?'+':'').((int)$h->points); ?></td>
                        <td><?php echo (int)$h->solde_apres; ?> pts</td>
                        <td><?php echo esc_html(substr($h->date_operation,0,10)); ?></td>
                    </tr>
                    <?php endforeach;
                    if(empty($hist)) echo '<tr><td colspan="5" style="text-align:center;color:#999">Aucune opération</td></tr>';
                    ?>
                </table>
            </div>
        </div>
        </div>
        <?php
    }

    // ── NOTIFICATIONS ───────────────────────────────────────
    public static function page_notifs() {
        global $wpdb;
        $notifs = $wpdb->get_results("SELECT n.*, COALESCE(u.display_name, ua.display_name) AS dest_name FROM {$wpdb->prefix}tma_notification n LEFT JOIN {$wpdb->prefix}tma_client_profile p ON p.id=n.client_id LEFT JOIN {$wpdb->users} u ON u.ID=p.wp_user_id LEFT JOIN {$wpdb->users} ua ON ua.ID=n.wp_admin_user_id ORDER BY n.date_creation DESC LIMIT 50");
        ?>
        <div class="wrap tma-admin">
        <h1>Notifications</h1>
        <table class="tma-table tma-table-full">
            <tr><th>Date</th><th>Destinataire</th><th>Type</th><th>Canal</th><th>Statut</th><th>Tentatives</th></tr>
            <?php foreach ($notifs as $n) :
                $colors = ['EN_ATTENTE'=>'#f59e0b','ENVOYEE'=>'#10b981','LUE'=>'#6b7280','ECHEC'=>'#ef4444'];
                $c = $colors[$n->statut] ?? '#6b7280';
            ?>
            <tr>
                <td><?php echo esc_html(substr($n->date_creation,0,16)); ?></td>
                <td><?php echo esc_html($n->dest_name ?? $n->destinataire_type); ?></td>
                <td style="font-size:.8rem"><?php echo esc_html($n->type); ?></td>
                <td><?php echo esc_html($n->canal); ?></td>
                <td><span class="tma-badge" style="background:<?php echo esc_attr($c); ?>22;color:<?php echo esc_attr($c); ?>"><?php echo esc_html($n->statut); ?></span></td>
                <td><?php echo (int)$n->tentatives; ?>/3</td>
            </tr>
            <?php endforeach;
            if(empty($notifs)) echo '<tr><td colspan="6" style="text-align:center;color:#999">Aucune notification</td></tr>';
            ?>
        </table>
        </div>
        <?php
    }

    // ── JOURNAL AUDIT ───────────────────────────────────────
    public static function page_logs() {
        $logs = TMB_Log_Admin::get_recent(100);
        ?>
        <div class="wrap tma-admin">
        <h1>Journal d'audit (100 dernières actions)</h1>
        <p style="color:#666;margin-bottom:1rem">Les logs sont immuables — lecture seule.</p>
        <table class="tma-table tma-table-full">
            <tr><th>Date</th><th>Admin</th><th>Action</th><th>Entité</th><th>ID</th><th>IP</th><th>Notes</th></tr>
            <?php foreach ($logs as $l) : ?>
            <tr>
                <td><?php echo esc_html($l['date_action']); ?></td>
                <td><?php echo esc_html($l['admin_name'] ?? '—'); ?></td>
                <td><code><?php echo esc_html($l['action']); ?></code></td>
                <td><?php echo esc_html($l['entite_cible']); ?></td>
                <td><?php echo $l['entite_id'] ? '#'.(int)$l['entite_id'] : '—'; ?></td>
                <td><?php echo esc_html($l['ip_address']); ?></td>
                <td style="font-size:.8rem;color:#666"><?php echo esc_html($l['notes'] ?? ''); ?></td>
            </tr>
            <?php endforeach;
            if(empty($logs)) echo '<tr><td colspan="7" style="text-align:center;color:#999">Aucun log</td></tr>';
            ?>
        </table>
        </div>
        <?php
    }

    // ── DESTINATIONS ────────────────────────────────────────
    public static function page_destinations() {
        global $wpdb;
        $action  = sanitize_text_field( $_GET['action'] ?? 'list' );
        $edit_id = (int) ( $_GET['id'] ?? 0 );
        $locs    = $wpdb->get_results( "SELECT id, nom, type, code_pays FROM {$wpdb->prefix}tma_localisation ORDER BY nom" ) ?: [];
        $loc_opts = [ '' => '— Choisir —' ];
        foreach ( $locs as $l ) {
            $flag = $l->code_pays ? '[' . strtoupper($l->code_pays) . '] ' : '';
            $loc_opts[ $l->id ] = $flag . $l->nom . ' (' . $l->type . ')';
        }

        if ( in_array( $action, ['new','edit'], true ) ) {
            $row = $edit_id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tma_destination WHERE id=%d", $edit_id ) ) : null;
            echo '<div class="wrap tma-admin">';
            echo '<h1>' . esc_html( $edit_id ? 'Modifier la destination' : 'Nouvelle destination' ) . ' <a href="' . esc_url(admin_url('admin.php?page=tma-destinations')) . '" class="page-title-action">← Liste</a></h1>';
            // flash notice
            if ( ! empty( $_GET['msg'] ) ) {
                $type = sanitize_text_field( $_GET['msgtype'] ?? 'updated' );
                echo '<div class="notice ' . esc_attr($type === 'error' ? 'notice-error' : 'notice-success') . ' is-dismissible"><p>' . esc_html( sanitize_text_field( wp_unslash( $_GET['msg'] ) ) ) . '</p></div>';
            }
            ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:1rem">
                <?php wp_nonce_field('tma_destination_save'); ?>
                <input type="hidden" name="action" value="tma_destination">
                <input type="hidden" name="id"     value="<?php echo esc_attr($edit_id); ?>">
                <table class="form-table">
                    <tr><th style="width:200px">Localisation <span style="color:#ef4444">*</span></th>
                        <td><select name="localisation_id" style="max-width:420px;width:100%">
                            <?php foreach ($loc_opts as $v => $lbl) : ?>
                                <option value="<?php echo esc_attr($v); ?>" <?php selected($row->localisation_id ?? '', $v); ?>><?php echo esc_html($lbl); ?></option>
                            <?php endforeach; ?>
                        </select></td></tr>
                    <tr><th>Nom <span style="color:#ef4444">*</span></th>
                        <td><input type="text" name="nom" value="<?php echo esc_attr($row->nom ?? ''); ?>" style="width:100%;max-width:420px" class="regular-text" required></td></tr>
                    <tr><th>Pays <span style="color:#ef4444">*</span></th>
                        <td><input type="text" name="pays" value="<?php echo esc_attr($row->pays ?? ''); ?>" style="width:100%;max-width:420px" class="regular-text" placeholder="Maroc" required></td></tr>
                    <tr><th>Région</th>
                        <td><input type="text" name="region" value="<?php echo esc_attr($row->region ?? ''); ?>" style="width:100%;max-width:420px" class="regular-text"></td></tr>
                    <tr><th>Description</th>
                        <td><textarea name="description" rows="3" style="width:100%;max-width:520px" class="large-text"><?php echo esc_textarea($row->description ?? ''); ?></textarea></td></tr>
                    <tr><th>Image URL</th>
                        <td><input type="url" name="image_url" value="<?php echo esc_attr($row->image_url ?? ''); ?>" style="width:100%;max-width:420px" class="regular-text"></td></tr>
                    <tr><th>Destination populaire</th>
                        <td><label><input type="checkbox" name="est_populaire" value="1" <?php checked(!empty($row->est_populaire)); ?>> Afficher dans les mises en avant</label></td></tr>
                    <tr><th>Statut</th>
                        <td><select name="statut" style="max-width:200px">
                            <option value="ACTIF"   <?php selected($row->statut ?? 'ACTIF', 'ACTIF'); ?>>Actif</option>
                            <option value="INACTIF" <?php selected($row->statut ?? '', 'INACTIF'); ?>>Inactif</option>
                        </select></td></tr>
                </table>
                <p><button type="submit" class="button button-primary button-large"><?php echo $edit_id ? 'Mettre à jour' : 'Créer la destination'; ?></button></p>
            </form>
            <?php
            echo '</div>';
            return;
        }

        $dests = $wpdb->get_results("SELECT d.*, l.latitude, l.longitude, l.code_pays FROM {$wpdb->prefix}tma_destination d JOIN {$wpdb->prefix}tma_localisation l ON l.id=d.localisation_id ORDER BY d.est_populaire DESC, d.nom ASC");
        ?>
        <div class="wrap tma-admin">
        <h1>Destinations (<?php echo count($dests); ?>) <a href="<?php echo esc_url(admin_url('admin.php?page=tma-destinations&action=new')); ?>" class="page-title-action">+ Nouvelle</a></h1>
        <?php if ( ! empty($_GET['msg']) ) : ?>
        <div class="notice notice-success is-dismissible"><p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['msg'] ) ) ); ?></p></div>
        <?php endif; ?>
        <table class="tma-table tma-table-full wp-list-table widefat fixed striped" style="margin-top:1rem">
            <thead><tr><th>Destination</th><th>Pays</th><th>Région</th><th>GPS</th><th>Populaire</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if ( $dests ) : foreach ($dests as $d) : $badge = $d->statut === 'ACTIF' ? '#10b981' : '#6b7280'; ?>
            <tr>
                <td><strong><?php echo esc_html($d->nom); ?></strong></td>
                <td><?php echo esc_html($d->pays); ?></td>
                <td><?php echo esc_html($d->region ?? '—'); ?></td>
                <td style="font-size:.8rem;color:#666"><?php echo esc_html($d->latitude) . ', ' . esc_html($d->longitude); ?></td>
                <td><?php echo $d->est_populaire ? '⭐' : '—'; ?></td>
                <td><span class="tma-badge" style="background:<?php echo esc_attr($badge); ?>20;color:<?php echo esc_attr($badge); ?>"><?php echo esc_html($d->statut); ?></span></td>
                <td>
                    <a href="<?php echo esc_url(admin_url("admin.php?page=tma-destinations&action=edit&id={$d->id}")); ?>" class="button button-small">Modifier</a>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url("admin-post.php?action=tma_destination&delete=1&id={$d->id}"), 'tma_destination_save')); ?>"
                       class="button button-small" style="color:#ef4444"
                       onclick="return confirm('Supprimer cette destination ?')">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; else : ?>
                <tr><td colspan="7" style="text-align:center;padding:2rem;color:#6b7280">Aucune destination.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
        <?php
    }

    public static function handle_destination(): void {
        check_admin_referer('tma_destination_save');
        if ( ! current_user_can('manage_woocommerce') ) wp_die('Accès refusé');

        global $wpdb;
        $table = $wpdb->prefix . 'tma_destination';
        $id    = (int) ( $_REQUEST['id'] ?? 0 );

        if ( isset( $_GET['delete'] ) ) {
            $wpdb->delete( $table, ['id' => $id] );
            TMB_Log_Admin::write([ 'action' => 'DELETE', 'entite_cible' => 'tma_destination', 'entite_id' => $id ]);
            wp_safe_redirect( add_query_arg(['page'=>'tma-destinations','msg'=>urlencode('Destination supprimée.')], admin_url('admin.php') ) );
            exit;
        }

        if ( empty($_POST['nom']) || empty($_POST['pays']) || empty($_POST['localisation_id']) ) {
            wp_safe_redirect( add_query_arg(['page'=>'tma-destinations','action'=>$id?'edit':'new','id'=>$id,'msg'=>urlencode('Champs obligatoires manquants.'),'msgtype'=>'error'], admin_url('admin.php') ) );
            exit;
        }

        $data = [
            'localisation_id' => (int) $_POST['localisation_id'],
            'nom'             => sanitize_text_field( $_POST['nom'] ),
            'pays'            => sanitize_text_field( $_POST['pays'] ),
            'region'          => sanitize_text_field( $_POST['region'] ?? '' ),
            'description'     => sanitize_textarea_field( $_POST['description'] ?? '' ),
            'image_url'       => esc_url_raw( $_POST['image_url'] ?? '' ),
            'est_populaire'   => isset($_POST['est_populaire']) ? 1 : 0,
            'statut'          => in_array($_POST['statut'], ['ACTIF','INACTIF'], true) ? $_POST['statut'] : 'ACTIF',
        ];

        if ( $id ) {
            $wpdb->update( $table, $data, ['id' => $id] );
            TMB_Log_Admin::write([ 'action' => 'UPDATE', 'entite_cible' => 'tma_destination', 'entite_id' => $id, 'notes' => 'MàJ destination ' . $data['nom'] ]);
            wp_safe_redirect( add_query_arg(['page'=>'tma-destinations','msg'=>urlencode('Destination mise à jour.')], admin_url('admin.php') ) );
        } else {
            $wpdb->insert( $table, $data );
            $new_id = $wpdb->insert_id;
            TMB_Log_Admin::write([ 'action' => 'CREATE', 'entite_cible' => 'tma_destination', 'entite_id' => $new_id, 'notes' => 'Nouvelle destination ' . $data['nom'] ]);
            wp_safe_redirect( add_query_arg(['page'=>'tma-destinations','msg'=>urlencode('Destination créée.')], admin_url('admin.php') ) );
        }
        exit;
    }

    // ── EXPORT CSV RÉSERVATIONS ──────────────────────────────
    public static function export_reservations(): void {
        check_admin_referer('tma_export_reservations');
        if ( ! current_user_can('manage_woocommerce') ) wp_die('Accès refusé');

        global $wpdb;

        $orders = wc_get_orders([
            'limit'  => -1,
            'status' => ['wc-processing','wc-completed','wc-on-hold','wc-pending'],
            'orderby'=> 'date',
            'order'  => 'DESC',
        ]);

        $filename = 'reservations-tma-' . gmdate('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM UTF-8 pour Excel
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'N° Commande', 'Date commande', 'Statut',
            'Client', 'Email', 'Téléphone',
            'Offre', 'Prix HT', 'Total TTC',
            'Date départ', 'Adultes', 'Enfants',
            'Demandes spéciales',
        ], ';');

        foreach ( $orders as $order ) {
            $items      = $order->get_items();
            $first      = reset($items);
            $offre_nom  = $first ? $first->get_name() : '';

            fputcsv($out, [
                $order->get_order_number(),
                $order->get_date_created()->date('d/m/Y H:i'),
                wc_get_order_status_name($order->get_status()),
                $order->get_formatted_billing_full_name(),
                $order->get_billing_email(),
                $order->get_billing_phone(),
                $offre_nom,
                number_format((float)$order->get_subtotal(), 2, ',', ' '),
                number_format((float)$order->get_total(),    2, ',', ' '),
                $order->get_meta('_tma_date_depart')       ?: '',
                $order->get_meta('_tma_nb_adultes')        ?: '',
                $order->get_meta('_tma_nb_enfants')        ?: '',
                $order->get_meta('_tma_demandes_speciales') ?: '',
            ], ';');
        }

        fclose($out);
        exit;
    }
}
