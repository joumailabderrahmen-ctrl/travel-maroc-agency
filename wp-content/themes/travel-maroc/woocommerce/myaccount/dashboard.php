<?php
defined( 'ABSPATH' ) || exit;

$user    = wp_get_current_user();
// Crée le profil fidélité si l'utilisateur n'en a pas encore (ex: admin créé avant le plugin)
if ( class_exists('TMB_Fidelite') ) {
    TMB_Fidelite::creer_profil_client( $user->ID );
}
$profile = class_exists('TMB_Fidelite') ? TMB_Fidelite::get_profile( $user->ID ) : null;

// Données fidélité
$points_solde  = $profile ? (int) $profile->points_fidelite_solde  : 0;
$points_cumul  = $profile ? (int) $profile->points_cumules_total   : 0;
$tier_libelle  = $profile ? $profile->type_libelle  : 'Standard';
$tier_color    = $profile ? $profile->couleur_badge : '#6c757d';
$taux_remise   = $profile ? (float) $profile->taux_remise : 0;

// Prochain palier
$next_tier = null;
if ( $profile ) {
    global $wpdb;
    $next_tier = $wpdb->get_row( $wpdb->prepare(
        "SELECT libelle, seuil_points, couleur_badge
         FROM {$wpdb->prefix}tma_type_client
         WHERE seuil_points > %d AND libelle != 'Entreprise'
         ORDER BY seuil_points ASC LIMIT 1",
        $points_cumul
    ));
    $current_seuil = $wpdb->get_var( $wpdb->prepare(
        "SELECT seuil_points FROM {$wpdb->prefix}tma_type_client WHERE id = %d",
        $profile->type_client_id
    ));
}

$progress_pct = 0;
if ( $next_tier && isset($current_seuil) ) {
    $range        = $next_tier->seuil_points - (int)$current_seuil;
    $earned       = $points_cumul - (int)$current_seuil;
    $progress_pct = $range > 0 ? min( 100, round( $earned / $range * 100 ) ) : 100;
}

// Stats commandes
$orders      = wc_get_orders([ 'customer' => $user->ID, 'limit' => -1, 'status' => ['completed','processing'] ]);
$nb_orders   = count( $orders );
$total_spent = array_sum( array_map( fn($o) => (float)$o->get_total(), $orders ) );

// Historique points (5 derniers)
$historique = class_exists('TMB_Fidelite') ? TMB_Fidelite::get_historique( $user->ID, 5 ) : [];

// Notifications non lues
$notifications = [];
if ( class_exists('TMB_Notifications') && $profile ) {
    $notifications = TMB_Notifications::get_for_client( (int) $profile->id, 5 );
}

// 3 dernières commandes
$recent_orders = wc_get_orders([ 'customer' => $user->ID, 'limit' => 3, 'orderby' => 'date', 'order' => 'DESC' ]);
?>

<div class="tma-account-dashboard">

    <!-- ── Bienvenue ───────────────────────────────────────────── -->
    <div class="tma-dash-welcome">
        <div class="tma-dash-avatar"><?php echo esc_html( mb_strtoupper( mb_substr( $user->display_name, 0, 1 ) ) ); ?></div>
        <div>
            <h2 class="tma-dash-hello">Bonjour, <strong><?php echo esc_html( $user->display_name ); ?></strong> !</h2>
            <p class="tma-dash-email"><?php echo esc_html( $user->user_email ); ?></p>
        </div>
        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="tma-dash-logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            Déconnexion
        </a>
    </div>

    <!-- ── Carte fidélité ──────────────────────────────────────── -->
    <div class="tma-fidelite-card" style="--tier-color:<?php echo esc_attr($tier_color); ?>">
        <div class="tma-fidelite-card__left">
            <div class="tma-fidelite-tier">
                <span class="tma-fidelite-tier__badge" style="background:<?php echo esc_attr($tier_color); ?>">
                    <?php echo esc_html( $tier_libelle ); ?>
                </span>
                <?php if ( $taux_remise > 0 ) : ?>
                    <span class="tma-fidelite-tier__discount">-<?php echo esc_html($taux_remise); ?>% sur chaque réservation</span>
                <?php endif; ?>
            </div>
            <div class="tma-fidelite-points">
                <span class="tma-fidelite-points__num"><?php echo number_format( $points_solde, 0, ',', ' ' ); ?></span>
                <span class="tma-fidelite-points__label">points disponibles</span>
            </div>
            <div class="tma-fidelite-sub">
                Valeur : <strong><?php echo number_format( $points_solde / 10, 0, ',', ' ' ); ?> MAD</strong>
                &nbsp;|&nbsp;
                Cumulés : <strong><?php echo number_format( $points_cumul, 0, ',', ' ' ); ?> pts</strong>
            </div>
        </div>

        <div class="tma-fidelite-card__right">
            <?php if ( $next_tier ) : ?>
            <div class="tma-fidelite-progress">
                <div class="tma-fidelite-progress__labels">
                    <span><?php echo esc_html( $tier_libelle ); ?></span>
                    <span style="color:<?php echo esc_attr($next_tier->couleur_badge); ?>"><?php echo esc_html( $next_tier->libelle ); ?></span>
                </div>
                <div class="tma-fidelite-progress__bar">
                    <div class="tma-fidelite-progress__fill" style="width:<?php echo esc_attr($progress_pct); ?>%;background:<?php echo esc_attr($tier_color); ?>"></div>
                </div>
                <p class="tma-fidelite-progress__hint">
                    Encore <strong><?php echo number_format( $next_tier->seuil_points - $points_cumul, 0, ',', ' ' ); ?> pts</strong>
                    pour atteindre le niveau <strong><?php echo esc_html( $next_tier->libelle ); ?></strong>
                </p>
            </div>
            <?php else : ?>
            <div class="tma-fidelite-progress__vip">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                Vous êtes au niveau maximum — Merci pour votre fidélité !
            </div>
            <?php endif; ?>

            <div class="tma-fidelite-equivalence">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
                100 pts = 10 MAD de remise
            </div>
        </div>
    </div>

    <!-- ── Stats ───────────────────────────────────────────────── -->
    <div class="tma-dash-stats">
        <div class="tma-dash-stat">
            <div class="tma-dash-stat__icon" style="background:#eff6ff">
                <svg viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" aria-hidden="true">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <div class="tma-dash-stat__val"><?php echo esc_html( $nb_orders ); ?></div>
            <div class="tma-dash-stat__lbl">Réservations</div>
        </div>
        <div class="tma-dash-stat">
            <div class="tma-dash-stat__icon" style="background:#fef3c7">
                <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" aria-hidden="true">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </div>
            <div class="tma-dash-stat__val"><?php echo number_format( $points_solde, 0, ',', ' ' ); ?></div>
            <div class="tma-dash-stat__lbl">Points disponibles</div>
        </div>
        <div class="tma-dash-stat">
            <div class="tma-dash-stat__icon" style="background:#f0fdf4">
                <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" aria-hidden="true">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
                </svg>
            </div>
            <div class="tma-dash-stat__val"><?php echo number_format( $total_spent, 0, ',', ' ' ); ?> <small>MAD</small></div>
            <div class="tma-dash-stat__lbl">Total dépensé</div>
        </div>
        <div class="tma-dash-stat">
            <div class="tma-dash-stat__icon" style="background:#fdf4ff">
                <svg viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/>
                </svg>
            </div>
            <div class="tma-dash-stat__val"><?php echo number_format( $points_cumul, 0, ',', ' ' ); ?></div>
            <div class="tma-dash-stat__lbl">Points cumulés (vie)</div>
        </div>
    </div>

    <!-- ── Grille : commandes + historique points ──────────────── -->
    <div class="tma-dash-grid">

        <!-- Dernières réservations -->
        <div class="tma-dash-section">
            <div class="tma-dash-section__head">
                <h3>Dernières réservations</h3>
                <a href="<?php echo esc_url( wc_get_account_endpoint_url('orders') ); ?>">Tout voir →</a>
            </div>
            <?php if ( $recent_orders ) : ?>
            <div class="tma-dash-orders">
                <?php foreach ( $recent_orders as $order ) :
                    $status      = $order->get_status();
                    $status_data = [
                        'completed'  => ['Complétée',   '#10b981'],
                        'processing' => ['En cours',    '#f97316'],
                        'pending'    => ['En attente',  '#f59e0b'],
                        'cancelled'  => ['Annulée',     '#ef4444'],
                        'on-hold'    => ['En suspens',  '#6b7280'],
                    ];
                    [$status_label, $status_color] = $status_data[$status] ?? ['Inconnue', '#6b7280'];
                    $items = $order->get_items();
                    $first = reset( $items );
                ?>
                <div class="tma-dash-order">
                    <div class="tma-dash-order__info">
                        <span class="tma-dash-order__ref">#<?php echo esc_html( $order->get_order_number() ); ?></span>
                        <span class="tma-dash-order__name"><?php echo esc_html( $first ? $first->get_name() : '—' ); ?></span>
                        <span class="tma-dash-order__date"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
                    </div>
                    <div class="tma-dash-order__right">
                        <span class="tma-dash-order__status" style="color:<?php echo esc_attr($status_color); ?>;background:<?php echo esc_attr($status_color); ?>18">
                            <?php echo esc_html($status_label); ?>
                        </span>
                        <span class="tma-dash-order__total"><?php echo wc_price( $order->get_total() ); ?></span>
                        <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="tma-dash-order__link">Voir</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <p class="tma-dash-empty">Aucune réservation pour le moment.
                <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>">Découvrir nos offres →</a>
            </p>
            <?php endif; ?>
        </div>

        <!-- Historique points -->
        <div class="tma-dash-section">
            <div class="tma-dash-section__head">
                <h3>Historique des points</h3>
            </div>
            <?php if ( $historique ) : ?>
            <div class="tma-dash-points-history">
                <?php foreach ( $historique as $h ) :
                    $gain  = (int)$h['points'] > 0;
                    $color = $gain ? '#10b981' : '#ef4444';
                    $sign  = $gain ? '+' : '';
                ?>
                <div class="tma-dash-pts-row">
                    <div class="tma-dash-pts-icon" style="background:<?php echo $gain ? '#f0fdf4' : '#fef2f2'; ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr($color); ?>" stroke-width="2" aria-hidden="true">
                            <?php if ( $gain ) : ?>
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
                            <?php else : ?>
                                <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>
                            <?php endif; ?>
                        </svg>
                    </div>
                    <div class="tma-dash-pts-info">
                        <span class="tma-dash-pts-desc"><?php echo esc_html( $h['description'] ?: $h['operation'] ); ?></span>
                        <span class="tma-dash-pts-date"><?php echo esc_html( substr($h['date_operation'], 0, 10) ); ?></span>
                    </div>
                    <div class="tma-dash-pts-amount" style="color:<?php echo esc_attr($color); ?>">
                        <?php echo $sign . esc_html( abs((int)$h['points']) ); ?> pts
                    </div>
                    <div class="tma-dash-pts-balance"><?php echo esc_html($h['solde_apres']); ?> pts</div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <p class="tma-dash-empty">Aucun point encore. Faites votre première réservation !<br>
                <small style="color:var(--gris)">1 point gagné pour 10 MAD dépensés</small>
            </p>
            <?php endif; ?>
        </div>

    </div><!-- .tma-dash-grid -->

    <!-- ── Notifications ─────────────────────────────────────────── -->
    <?php if ( $notifications ) :
        $unread = array_filter( $notifications, fn($n) => $n['statut'] !== 'LUE' );
    ?>
    <div class="tma-dash-section tma-dash-notifs" style="margin-bottom:2rem">
        <div class="tma-dash-section__head">
            <h3>Notifications
                <?php if ( $unread ) : ?>
                    <span style="background:#ef4444;color:#fff;border-radius:999px;padding:1px 7px;font-size:.7rem;margin-left:6px;vertical-align:middle"><?php echo count($unread); ?></span>
                <?php endif; ?>
            </h3>
        </div>
        <div class="tma-dash-notif-list">
            <?php foreach ( $notifications as $n ) :
                $is_unread = $n['statut'] !== 'LUE';
                $type_icons = [
                    'CONFIRMATION_RESERVATION' => '📋',
                    'POINTS_GAGNES'            => '⭐',
                    'MONTEE_PALIER'            => '🏆',
                    'EXPIRATION_POINTS'        => '⏳',
                    'OFFRE_SPECIALE'           => '🎁',
                    'SYSTEME'                  => '🔔',
                ];
                $icon = $type_icons[ $n['type'] ] ?? '🔔';
            ?>
            <div class="tma-dash-notif<?php echo $is_unread ? ' tma-dash-notif--unread' : ''; ?>"
                 style="display:flex;gap:.75rem;align-items:flex-start;padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;<?php echo $is_unread ? 'background:#eff6ff;' : ''; ?>">
                <span style="font-size:1.25rem;line-height:1"><?php echo $icon; ?></span>
                <div style="flex:1;min-width:0">
                    <div style="font-weight:<?php echo $is_unread ? '700' : '500'; ?>;font-size:.875rem;color:#111827"><?php echo esc_html($n['titre']); ?></div>
                    <div style="font-size:.8rem;color:#6b7280;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo esc_html($n['message']); ?></div>
                    <div style="font-size:.75rem;color:#9ca3af;margin-top:3px"><?php echo esc_html(substr($n['date_creation'],0,16)); ?></div>
                </div>
                <?php if ( $is_unread ) : ?>
                <span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;flex-shrink:0;margin-top:4px"></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Liens rapides ───────────────────────────────────────── -->
    <div class="tma-dash-quicklinks">
        <a href="<?php echo esc_url( wc_get_page_permalink('shop') ); ?>" class="tma-dash-quicklink">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Nos offres
        </a>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url('orders') ); ?>" class="tma-dash-quicklink">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            Réservations
        </a>
        <a href="<?php echo esc_url( wc_get_account_endpoint_url('edit-account') ); ?>" class="tma-dash-quicklink">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Mon profil
        </a>
        <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="tma-dash-quicklink">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            Mon panier
        </a>
        <a href="<?php echo esc_url( home_url('/contact') ); ?>" class="tma-dash-quicklink">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            Nous contacter
        </a>
    </div>

</div><!-- .tma-account-dashboard -->
