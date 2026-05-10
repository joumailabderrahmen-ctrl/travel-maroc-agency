<?php
defined( 'ABSPATH' ) || exit;

/**
 * Gestion des disponibilités par offre (produit WC).
 * Les dates bloquées sont stockées en post_meta _tma_dates_bloquees (JSON array de "YYYY-MM-DD").
 * La capacité maximale par départ est stockée en _tma_capacite_max (int).
 */
class TMB_Availability {

    public static function init(): void {
        // Meta box dans l'édition produit
        add_action( 'add_meta_boxes',        [ __CLASS__, 'add_meta_box'      ] );
        add_action( 'save_post_product',     [ __CLASS__, 'save_meta'         ], 10, 1 );
        // Endpoint AJAX pour le calendrier front
        add_action( 'wp_ajax_tma_get_blocked_dates',        [ __CLASS__, 'ajax_blocked_dates' ] );
        add_action( 'wp_ajax_nopriv_tma_get_blocked_dates', [ __CLASS__, 'ajax_blocked_dates' ] );
    }

    // ── META BOX ADMIN ─────────────────────────────────────────────
    public static function add_meta_box(): void {
        add_meta_box(
            'tma-availability',
            '📅 Disponibilités & Capacité',
            [ __CLASS__, 'render_meta_box' ],
            'product',
            'normal',
            'default'
        );
    }

    public static function render_meta_box( WP_Post $post ): void {
        $dates_bloquees = json_decode( get_post_meta( $post->ID, '_tma_dates_bloquees', true ) ?: '[]', true );
        $capacite       = (int) get_post_meta( $post->ID, '_tma_capacite_max', true );
        wp_nonce_field( 'tma_availability_' . $post->ID, 'tma_availability_nonce' );
        ?>
        <style>
        .tma-avail-wrap { display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; }
        .tma-cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; margin-top:.5rem; }
        .tma-cal-nav { display:flex; align-items:center; justify-content:space-between; margin-bottom:.5rem; }
        .tma-cal-day { text-align:center; padding:5px 2px; border:1px solid #e5e7eb; border-radius:4px; cursor:pointer; font-size:.8rem; user-select:none; }
        .tma-cal-day.tma-past { background:#f9fafb; color:#d1d5db; cursor:default; }
        .tma-cal-day.tma-blocked { background:#fee2e2; color:#dc2626; border-color:#fca5a5; }
        .tma-cal-day.tma-header { background:transparent; border:none; font-weight:700; color:#6b7280; cursor:default; font-size:.75rem; }
        .tma-cal-day.tma-empty { border:none; background:transparent; cursor:default; }
        #tma-blocked-list { margin-top:.75rem; display:flex; flex-wrap:wrap; gap:.3rem; }
        .tma-blocked-tag { background:#fee2e2; color:#dc2626; padding:.2rem .5rem; border-radius:4px; font-size:.8rem; cursor:pointer; }
        .tma-blocked-tag:hover { background:#fca5a5; }
        </style>
        <div class="tma-avail-wrap">
            <div>
                <label><strong>Capacité maximale par départ (0 = illimitée)</strong></label>
                <input type="number" name="tma_capacite_max" value="<?php echo esc_attr($capacite); ?>"
                       min="0" max="500" style="width:100px;margin-top:.3rem">
                <p style="color:#6b7280;font-size:.8rem;margin-top:.3rem">
                    Nombre max de voyageurs acceptés par date de départ.
                </p>
                <hr style="margin:1rem 0">
                <label><strong>Dates bloquées</strong> <span style="color:#6b7280;font-size:.8rem">(cliquer pour bloquer/débloquer)</span></label>
                <div class="tma-cal-nav">
                    <button type="button" class="button" id="tma-cal-prev">‹</button>
                    <strong id="tma-cal-month-label"></strong>
                    <button type="button" class="button" id="tma-cal-next">›</button>
                </div>
                <div class="tma-cal-grid" id="tma-cal-grid"></div>
                <input type="hidden" name="tma_dates_bloquees" id="tma-dates-input"
                       value="<?php echo esc_attr( json_encode($dates_bloquees) ); ?>">
            </div>
            <div>
                <label><strong>Dates bloquées enregistrées</strong></label>
                <div id="tma-blocked-list"></div>
                <p style="color:#6b7280;font-size:.8rem;margin-top:.75rem">Cliquer sur une date pour la débloquer.</p>
                <hr style="margin:1rem 0">
                <div style="background:#f0f9ff;border-left:3px solid #0d2b55;padding:.75rem;border-radius:4px;font-size:.85rem">
                    <strong>Comment ça marche :</strong><br>
                    Les dates bloquées sont masquées dans le sélecteur de date du checkout.<br>
                    Réservations existantes sur ces dates ne sont pas annulées.
                </div>
            </div>
        </div>
        <script>
        (function(){
            var blocked = <?php echo json_encode($dates_bloquees); ?>;
            var today   = new Date(); today.setHours(0,0,0,0);
            var cur     = new Date(today.getFullYear(), today.getMonth(), 1);
            var days    = ['Dim','Lun','Mar','Mer','Jeu','Ven','Sam'];
            var months  = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

            function fmt(d){ return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'); }

            function renderList(){
                var el = document.getElementById('tma-blocked-list');
                if(!blocked.length){ el.innerHTML='<em style="color:#9ca3af;font-size:.85rem">Aucune date bloquée</em>'; return; }
                el.innerHTML = blocked.sort().map(function(d){
                    return '<span class="tma-blocked-tag" data-date="'+d+'" title="Cliquer pour débloquer">'+d+' ✕</span>';
                }).join('');
                el.querySelectorAll('.tma-blocked-tag').forEach(function(tag){
                    tag.addEventListener('click', function(){
                        var date = this.dataset.date;
                        blocked = blocked.filter(function(b){ return b !== date; });
                        document.getElementById('tma-dates-input').value = JSON.stringify(blocked);
                        renderCal(); renderList();
                    });
                });
            }

            function renderCal(){
                var grid  = document.getElementById('tma-cal-grid');
                var label = document.getElementById('tma-cal-month-label');
                label.textContent = months[cur.getMonth()] + ' ' + cur.getFullYear();
                grid.innerHTML = '';
                days.forEach(function(d){ var el=document.createElement('div'); el.className='tma-cal-day tma-header'; el.textContent=d; grid.appendChild(el); });
                var first = new Date(cur.getFullYear(), cur.getMonth(), 1).getDay();
                for(var i=0;i<first;i++){ var el=document.createElement('div'); el.className='tma-cal-day tma-empty'; grid.appendChild(el); }
                var days_in = new Date(cur.getFullYear(), cur.getMonth()+1, 0).getDate();
                for(var d=1;d<=days_in;d++){
                    var date = new Date(cur.getFullYear(), cur.getMonth(), d);
                    var ds   = fmt(date);
                    var el   = document.createElement('div');
                    el.className = 'tma-cal-day';
                    el.textContent = d;
                    if(date < today){ el.classList.add('tma-past'); }
                    else {
                        if(blocked.indexOf(ds)>=0) el.classList.add('tma-blocked');
                        (function(ds,el){
                            el.addEventListener('click', function(){
                                var idx = blocked.indexOf(ds);
                                if(idx>=0) blocked.splice(idx,1);
                                else blocked.push(ds);
                                document.getElementById('tma-dates-input').value = JSON.stringify(blocked);
                                renderCal(); renderList();
                            });
                        })(ds,el);
                    }
                    grid.appendChild(el);
                }
                document.getElementById('tma-dates-input').value = JSON.stringify(blocked);
                renderList();
            }

            document.getElementById('tma-cal-prev').addEventListener('click',function(){
                cur = new Date(cur.getFullYear(), cur.getMonth()-1, 1); renderCal();
            });
            document.getElementById('tma-cal-next').addEventListener('click',function(){
                cur = new Date(cur.getFullYear(), cur.getMonth()+1, 1); renderCal();
            });
            renderCal();
        })();
        </script>
        <?php
    }

    public static function save_meta( int $post_id ): void {
        if ( ! isset($_POST['tma_availability_nonce']) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['tma_availability_nonce'])), 'tma_availability_' . $post_id ) ) return;
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( ! current_user_can('edit_post', $post_id) ) return;

        $capacite = max(0, (int)($_POST['tma_capacite_max'] ?? 0));
        update_post_meta( $post_id, '_tma_capacite_max', $capacite );

        $raw    = sanitize_text_field(wp_unslash($_POST['tma_dates_bloquees'] ?? '[]'));
        $dates  = json_decode($raw, true);
        if ( ! is_array($dates) ) $dates = [];
        // Valider le format YYYY-MM-DD
        $dates = array_values(array_unique(array_filter($dates, function($d){
            return (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
        })));
        update_post_meta( $post_id, '_tma_dates_bloquees', wp_json_encode($dates) );
    }

    // ── AJAX : liste des dates bloquées pour un produit ───────────
    public static function ajax_blocked_dates(): void {
        $product_id = (int)($_GET['product_id'] ?? 0);
        if ( ! $product_id ) wp_send_json_error('missing product_id');
        $raw   = get_post_meta($product_id, '_tma_dates_bloquees', true) ?: '[]';
        $dates = json_decode($raw, true) ?: [];
        wp_send_json_success(['blocked' => $dates]);
    }

    // ── HELPER : une date est-elle disponible ? ────────────────────
    public static function is_date_available( int $product_id, string $date_ymd ): bool {
        $raw   = get_post_meta($product_id, '_tma_dates_bloquees', true) ?: '[]';
        $dates = json_decode($raw, true) ?: [];
        return ! in_array($date_ymd, $dates, true);
    }
}
