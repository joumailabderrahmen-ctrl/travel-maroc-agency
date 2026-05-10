<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );
$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
?>

<!-- ── Bannière ──────────────────────────────────────────── -->
<div class="tma-login-hero">
    <div class="container">
        <h1 class="tma-login-hero__title">Mon Compte</h1>
        <p class="tma-login-hero__sub">Connectez-vous pour gérer vos réservations et points fidélité</p>
    </div>
</div>

<!-- ── Formulaires ──────────────────────────────────────── -->
<div class="tma-login-wrap">
    <div class="container">

        <?php if ( $registration_enabled ) : ?>
        <div class="tma-login-grid" id="customer_login">
        <?php else : ?>
        <div class="tma-login-center">
        <?php endif; ?>

            <!-- ── Connexion ─────────────────────────────── -->
            <div class="tma-login-card">

                <div class="tma-login-card__header">
                    <div class="tma-login-card__icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                    </div>
                    <h2>Connexion</h2>
                    <p>Accédez à votre espace client</p>
                </div>

                <form class="woocommerce-form woocommerce-form-login login tma-auth-form" method="post" novalidate>

                    <?php do_action( 'woocommerce_login_form_start' ); ?>

                    <div class="tma-field-group">
                        <label for="username">Nom d'utilisateur ou e-mail <span class="tma-required">*</span></label>
                        <input type="text"
                               class="woocommerce-Input woocommerce-Input--text input-text tma-input"
                               name="username" id="username"
                               autocomplete="username"
                               placeholder="admin ou votre@email.com"
                               value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
                               required aria-required="true" />
                    </div>

                    <div class="tma-field-group">
                        <label for="password">Mot de passe <span class="tma-required">*</span></label>
                        <input class="woocommerce-Input woocommerce-Input--text input-text tma-input"
                               type="password" name="password" id="password"
                               autocomplete="current-password"
                               placeholder="••••••••"
                               required aria-required="true" />
                    </div>

                    <?php do_action( 'woocommerce_login_form' ); ?>

                    <div class="tma-login-row">
                        <label class="tma-checkbox-label">
                            <input type="checkbox" name="rememberme" id="rememberme" value="forever" />
                            <span>Se souvenir de moi</span>
                        </label>
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="tma-lost-pwd">
                            Mot de passe oublié ?
                        </a>
                    </div>

                    <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

                    <button type="submit" class="btn btn-primary tma-auth-btn" name="login" value="Se connecter">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        Se connecter
                    </button>

                    <?php do_action( 'woocommerce_login_form_end' ); ?>

                </form>
            </div>

            <?php if ( $registration_enabled ) : ?>
            <!-- ── Inscription ───────────────────────────── -->
            <div class="tma-login-card">

                <div class="tma-login-card__header">
                    <div class="tma-login-card__icon tma-login-card__icon--orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <h2>Créer un compte</h2>
                    <p>Rejoignez Travel Maroc Agency</p>
                </div>

                <form method="post" class="woocommerce-form woocommerce-form-register register tma-auth-form"
                      <?php do_action( 'woocommerce_register_form_tag' ); ?>>

                    <?php do_action( 'woocommerce_register_form_start' ); ?>

                    <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                    <div class="tma-field-group">
                        <label for="reg_username">Nom d'utilisateur <span class="tma-required">*</span></label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text tma-input"
                               name="username" id="reg_username"
                               autocomplete="username"
                               placeholder="votre_pseudo"
                               value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
                               required aria-required="true" />
                    </div>
                    <?php endif; ?>

                    <div class="tma-field-group">
                        <label for="reg_email">Adresse e-mail <span class="tma-required">*</span></label>
                        <input type="email" class="woocommerce-Input woocommerce-Input--text input-text tma-input"
                               name="email" id="reg_email"
                               autocomplete="email"
                               placeholder="votre@email.com"
                               value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>"
                               required aria-required="true" />
                    </div>

                    <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                    <div class="tma-field-group">
                        <label for="reg_password">Mot de passe <span class="tma-required">*</span></label>
                        <input type="password" class="woocommerce-Input woocommerce-Input--text input-text tma-input"
                               name="password" id="reg_password"
                               autocomplete="new-password"
                               placeholder="••••••••"
                               required aria-required="true" />
                    </div>
                    <?php else : ?>
                    <p class="tma-info-msg">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Un lien pour définir votre mot de passe sera envoyé à votre adresse e-mail.
                    </p>
                    <?php endif; ?>

                    <?php do_action( 'woocommerce_register_form' ); ?>

                    <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

                    <button type="submit"
                            class="btn btn-outline tma-auth-btn"
                            name="register" value="Créer mon compte">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/>
                            <line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
                        </svg>
                        Créer mon compte
                    </button>

                    <?php do_action( 'woocommerce_register_form_end' ); ?>

                </form>
            </div>
            <?php endif; ?>

        </div><!-- .tma-login-grid / .tma-login-center -->
    </div>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
