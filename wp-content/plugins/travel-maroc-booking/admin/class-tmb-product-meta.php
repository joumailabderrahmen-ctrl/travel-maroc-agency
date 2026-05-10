<?php
defined( 'ABSPATH' ) || exit;

class TMB_Product_Meta {

    public static function init() {
        add_action( 'add_meta_boxes',  [ __CLASS__, 'register_meta_box' ] );
        add_action( 'save_post_product', [ __CLASS__, 'save_meta_box' ], 10, 2 );
    }

    public static function register_meta_box(): void {
        add_meta_box(
            'tma-product-info',
            'Infos voyage Travel Maroc',
            [ __CLASS__, 'render_meta_box' ],
            'product',
            'normal',
            'high'
        );
    }

    public static function render_meta_box( WP_Post $post ): void {
        global $wpdb;
        wp_nonce_field( 'tma_product_meta_save', 'tma_product_meta_nonce' );

        $destination = get_post_meta( $post->ID, '_tma_destination', true );
        $duration    = get_post_meta( $post->ID, '_tma_duration',    true );
        $points      = get_post_meta( $post->ID, '_tma_points',      true );
        $guide_id    = (int) get_post_meta( $post->ID, '_tma_guide_id',     true );
        $hotel_id    = (int) get_post_meta( $post->ID, '_tma_hotel_id',     true );
        $transport_id= (int) get_post_meta( $post->ID, '_tma_transport_id', true );

        $guides     = $wpdb->get_results( "SELECT id, CONCAT(prenom,' ',nom) AS label FROM {$wpdb->prefix}tma_guide     WHERE statut='ACTIF' ORDER BY nom, prenom" ) ?: [];
        $hotels     = $wpdb->get_results( "SELECT id, nom AS label FROM {$wpdb->prefix}tma_hotel     WHERE statut='ACTIF' ORDER BY nom" ) ?: [];
        $transports = $wpdb->get_results( "SELECT id, CONCAT(type,' : ', (SELECT nom FROM {$wpdb->prefix}tma_localisation WHERE id=localisation_depart_id), ' → ', (SELECT nom FROM {$wpdb->prefix}tma_localisation WHERE id=localisation_arrivee_id)) AS label FROM {$wpdb->prefix}tma_transport WHERE statut='ACTIF'" ) ?: [];
        ?>
        <style>
        .tma-meta-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; padding:8px 0; }
        .tma-meta-field label { display:block; font-weight:600; color:#374151; margin-bottom:5px; font-size:13px; }
        .tma-meta-field input, .tma-meta-field select { width:100%; }
        .tma-meta-field .description { color:#6b7280; font-size:12px; margin-top:4px; }
        .tma-meta-sep { grid-column:1/-1; border:0; border-top:1px solid #e5e7eb; margin:4px 0; }
        </style>

        <div class="tma-meta-grid">
            <div class="tma-meta-field">
                <label for="tma_destination">Destination</label>
                <input type="text" id="tma_destination" name="tma_destination"
                       value="<?php echo esc_attr( $destination ); ?>"
                       placeholder="ex: Marrakech, Paris…" class="regular-text">
                <p class="description">Ville ou région affichée sur les cartes</p>
            </div>
            <div class="tma-meta-field">
                <label for="tma_duration">Durée</label>
                <input type="text" id="tma_duration" name="tma_duration"
                       value="<?php echo esc_attr( $duration ); ?>"
                       placeholder="ex: 7 jours / 6 nuits" class="regular-text">
                <p class="description">Durée affichée sur les cartes produit</p>
            </div>
            <div class="tma-meta-field">
                <label for="tma_points">Points fidélité bonus</label>
                <input type="number" id="tma_points" name="tma_points"
                       value="<?php echo esc_attr( $points ); ?>"
                       min="0" step="1" class="regular-text">
                <p class="description">En plus du calcul automatique (10 MAD = 1 pt)</p>
            </div>

            <hr class="tma-meta-sep">

            <div class="tma-meta-field">
                <label for="tma_guide_id">Guide assigné</label>
                <select id="tma_guide_id" name="tma_guide_id">
                    <option value="">— Aucun —</option>
                    <?php foreach ( $guides as $g ) : ?>
                        <option value="<?php echo (int)$g->id; ?>" <?php selected($guide_id, (int)$g->id); ?>>
                            <?php echo esc_html($g->label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Guide TMA qui accompagne ce voyage</p>
            </div>
            <div class="tma-meta-field">
                <label for="tma_hotel_id">Hôtel inclus</label>
                <select id="tma_hotel_id" name="tma_hotel_id">
                    <option value="">— Aucun —</option>
                    <?php foreach ( $hotels as $h ) : ?>
                        <option value="<?php echo (int)$h->id; ?>" <?php selected($hotel_id, (int)$h->id); ?>>
                            <?php echo esc_html($h->label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Hôtel TMA lié à cette offre</p>
            </div>
            <div class="tma-meta-field">
                <label for="tma_transport_id">Transport inclus</label>
                <select id="tma_transport_id" name="tma_transport_id">
                    <option value="">— Aucun —</option>
                    <?php foreach ( $transports as $t ) : ?>
                        <option value="<?php echo (int)$t->id; ?>" <?php selected($transport_id, (int)$t->id); ?>>
                            <?php echo esc_html($t->label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Liaison transport TMA liée</p>
            </div>
        </div>
        <?php
    }

    public static function save_meta_box( int $post_id, WP_Post $post ): void {
        if ( ! isset( $_POST['tma_product_meta_nonce'] )
             || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tma_product_meta_nonce'] ) ), 'tma_product_meta_save' )
        ) return;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( $post->post_type !== 'product' ) return;

        $fields = [
            '_tma_destination'  => 'tma_destination',
            '_tma_duration'     => 'tma_duration',
            '_tma_points'       => 'tma_points',
            '_tma_guide_id'     => 'tma_guide_id',
            '_tma_hotel_id'     => 'tma_hotel_id',
            '_tma_transport_id' => 'tma_transport_id',
        ];
        $int_keys = [ '_tma_points', '_tma_guide_id', '_tma_hotel_id', '_tma_transport_id' ];
        foreach ( $fields as $meta_key => $post_key ) {
            if ( isset( $_POST[ $post_key ] ) ) {
                $value = in_array( $meta_key, $int_keys, true )
                    ? (int) $_POST[ $post_key ]
                    : sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
                update_post_meta( $post_id, $meta_key, $value );
            }
        }
    }
}
