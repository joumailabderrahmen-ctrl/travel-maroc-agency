<?php
defined( 'ABSPATH' ) || exit;

class TMB_Public {

    public static function init() {
        add_shortcode( 'tma_points_solde',    [ __CLASS__, 'sc_points_solde'    ] );
        add_shortcode( 'tma_points_historique', [ __CLASS__, 'sc_points_historique' ] );
        add_shortcode( 'tma_destinations_map', [ __CLASS__, 'sc_destinations_map'  ] );

        // Affichage remise dans le panier WooCommerce
        add_action( 'woocommerce_before_cart_totals', [ __CLASS__, 'afficher_remise_fidelite' ] );
    }

    // ── Shortcode : solde points ────────────────────────────
    public static function sc_points_solde(): string {
        if ( ! is_user_logged_in() ) return '';
        $profile = TMB_Fidelite::get_profile( get_current_user_id() );
        if ( ! $profile ) return '';
        ob_start();
        ?>
        <div class="tma-points-widget">
            <div class="tma-points-badge" style="background:<?php echo esc_attr($profile->couleur_badge); ?>22;border:1px solid <?php echo esc_attr($profile->couleur_badge); ?>;border-radius:12px;padding:1rem 1.5rem;display:inline-flex;align-items:center;gap:1rem">
                <div>
                    <div style="font-size:1.8rem;font-weight:800;color:<?php echo esc_attr($profile->couleur_badge); ?>"><?php echo esc_html( number_format((int)$profile->points_fidelite_solde, 0, ',', ' ') ); ?></div>
                    <div style="font-size:.8rem;color:#6b7280">points disponibles</div>
                </div>
                <div style="border-left:1px solid <?php echo esc_attr($profile->couleur_badge); ?>;padding-left:1rem">
                    <div style="font-weight:700;color:<?php echo esc_attr($profile->couleur_badge); ?>"><?php echo esc_html($profile->type_libelle); ?></div>
                    <div style="font-size:.8rem;color:#6b7280">remise <?php echo esc_html($profile->taux_remise); ?>%</div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ── Shortcode : historique points ───────────────────────
    public static function sc_points_historique(): string {
        if ( ! is_user_logged_in() ) return '<p>Connectez-vous pour voir votre historique.</p>';
        $hist = TMB_Fidelite::get_historique( get_current_user_id(), 10 );
        if ( empty($hist) ) return '<p>Aucune opération de points pour le moment.</p>';

        ob_start();
        echo '<table style="width:100%;border-collapse:collapse;font-size:.9rem">';
        echo '<tr style="background:#f5f7fa"><th style="padding:.5rem;text-align:left">Date</th><th>Opération</th><th>Points</th><th>Solde</th></tr>';
        foreach ( $hist as $h ) :
            $color = (int)$h['points'] > 0 ? '#10b981' : '#ef4444';
            $sign  = (int)$h['points'] > 0 ? '+' : '';
            echo "<tr style='border-bottom:1px solid #f3f4f6'>";
            echo "<td style='padding:.5rem'>" . esc_html(substr($h['date_operation'],0,10)) . "</td>";
            echo "<td>" . esc_html($h['operation']) . "</td>";
            echo "<td style='color:$color;font-weight:700'>$sign{$h['points']}</td>";
            echo "<td>" . esc_html($h['solde_apres']) . " pts</td>";
            echo "</tr>";
        endforeach;
        echo '</table>';
        return ob_get_clean();
    }

    // ── Shortcode : carte destinations (Leaflet.js) ─────────
    public static function sc_destinations_map(): string {
        global $wpdb;
        $dests = $wpdb->get_results(
            "SELECT d.nom, d.pays, d.description, l.latitude, l.longitude
             FROM {$wpdb->prefix}tma_destination d
             JOIN {$wpdb->prefix}tma_localisation l ON l.id = d.localisation_id
             WHERE d.statut = 'ACTIF'
               AND l.latitude IS NOT NULL AND l.longitude IS NOT NULL"
        );

        $map_id = 'tma-map-' . wp_unique_id();
        $dests_json = wp_json_encode( array_map( fn($d) => [
            'nom'  => $d->nom,
            'pays' => $d->pays,
            'desc' => $d->description ? wp_strip_all_tags($d->description) : '',
            'lat'  => (float) $d->latitude,
            'lng'  => (float) $d->longitude,
        ], $dests ), JSON_UNESCAPED_UNICODE );

        ob_start();
        ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
        <div id="<?php echo esc_attr($map_id); ?>" style="width:100%;height:450px;border-radius:12px;border:1px solid #e5e7eb;z-index:0"></div>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WPtI=" crossorigin=""></script>
        <script>
        (function() {
            var dests = <?php echo $dests_json; ?>;
            var map = L.map(<?php echo wp_json_encode($map_id); ?>).setView([28.0, 10.0], 3);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 18
            }).addTo(map);

            var icon = L.divIcon({
                className: '',
                html: '<div style="background:#f97316;width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4)"></div>',
                iconSize: [14, 14], iconAnchor: [7, 7]
            });

            dests.forEach(function(d) {
                if (!d.lat || !d.lng) return;
                L.marker([d.lat, d.lng], {icon: icon})
                 .addTo(map)
                 .bindPopup('<strong>' + d.nom + '</strong><br><em style="color:#6b7280">' + d.pays + '</em>' + (d.desc ? '<br><span style="font-size:.85rem">' + d.desc.slice(0,80) + '…</span>' : ''));
            });
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    // ── Info fidélité dans le panier ────────────────────────
    public static function afficher_remise_fidelite(): void {
        if ( ! is_user_logged_in() ) return;
        $profile = TMB_Fidelite::get_profile( get_current_user_id() );
        if ( ! $profile || ! $profile->points_fidelite_solde ) return;
        $remise_dispo = TMB_Fidelite::calculer_remise_points( (int)$profile->points_fidelite_solde );
        ?>
        <div class="tma-fidelite-notice">
            ⭐ <strong><?php echo esc_html((int)$profile->points_fidelite_solde); ?> points disponibles</strong>
            — Vous pouvez obtenir jusqu'à <strong><?php echo number_format($remise_dispo,2,',',' '); ?> MAD</strong> de remise.
            Statut : <strong><?php echo esc_html($profile->type_libelle); ?></strong>
            (remise automatique de <?php echo esc_html($profile->taux_remise); ?>% appliquée).
        </div>
        <?php
    }
}
