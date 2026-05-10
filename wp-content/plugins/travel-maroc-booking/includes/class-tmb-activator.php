<?php
defined( 'ABSPATH' ) || exit;

class TMB_Activator {

    public static function activate() {
        self::create_tables();
        self::seed_default_data();
        TMB_Roles::add_roles();
        if ( ! wp_next_scheduled('tma_cron_expirer_points') ) {
            wp_schedule_event( time(), 'daily', 'tma_cron_expirer_points' );
        }
        flush_rewrite_rules();
    }

    public static function deactivate() {
        TMB_Roles::remove_roles();
        wp_clear_scheduled_hook('tma_cron_expirer_points');
        wp_clear_scheduled_hook('tmb_send_notifications_cron');
        flush_rewrite_rules();
    }

    public static function uninstall() {
        global $wpdb;
        $tables = [
            'tma_log_admin', 'tma_notification', 'tma_historique_points',
            'tma_client_profile', 'tma_transport', 'tma_hotel', 'tma_guide',
            'tma_type_client', 'tma_destination', 'tma_localisation',
        ];
        foreach ( $tables as $t ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$t}" );
        }
        delete_option('tma_video_hero_url');
        wp_clear_scheduled_hook('tma_cron_expirer_points');
        wp_clear_scheduled_hook('tmb_send_notifications_cron');
    }

    private static function create_tables() {
        global $wpdb;
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Toutes les tables utilisent dbDelta pour une migration propre
        $sql = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_localisation (
            id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            nom         VARCHAR(100)  NOT NULL,
            type        VARCHAR(20)   NOT NULL DEFAULT 'VILLE',
            code_pays   VARCHAR(3)    NULL,
            latitude    DECIMAL(9,6)  NULL,
            longitude   DECIMAL(9,6)  NULL,
            actif       TINYINT(1)    NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            KEY idx_type (type)
        ) $charset;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_destination (
            id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
            localisation_id INT UNSIGNED  NOT NULL,
            nom             VARCHAR(100)  NOT NULL,
            pays            VARCHAR(100)  NOT NULL DEFAULT 'Maroc',
            region          VARCHAR(100)  NULL,
            description     TEXT          NULL,
            image_url       VARCHAR(255)  NULL,
            est_populaire   TINYINT(1)    NOT NULL DEFAULT 0,
            statut          VARCHAR(10)   NOT NULL DEFAULT 'ACTIF',
            PRIMARY KEY (id),
            KEY idx_localisation (localisation_id)
        ) $charset;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_guide (
            id                  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
            localisation_id     INT UNSIGNED   NOT NULL,
            prenom              VARCHAR(60)    NOT NULL,
            nom                 VARCHAR(60)    NOT NULL,
            email               VARCHAR(100)   NULL,
            telephone           VARCHAR(20)    NOT NULL,
            langues             VARCHAR(200)   NULL,
            specialites         VARCHAR(200)   NULL,
            tarif_journalier    DECIMAL(8,2)   NOT NULL DEFAULT 0.00,
            experience_annees   TINYINT UNSIGNED NOT NULL DEFAULT 0,
            photo_url           VARCHAR(255)   NULL,
            note_moyenne        DECIMAL(3,2)   NOT NULL DEFAULT 0.00,
            statut              VARCHAR(10)    NOT NULL DEFAULT 'ACTIF',
            PRIMARY KEY (id),
            KEY idx_localisation (localisation_id)
        ) $charset;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_hotel (
            id                 INT UNSIGNED   NOT NULL AUTO_INCREMENT,
            localisation_id    INT UNSIGNED   NOT NULL,
            nom                VARCHAR(150)   NOT NULL,
            categorie_etoiles  TINYINT UNSIGNED NULL,
            adresse_complete   TEXT           NULL,
            email              VARCHAR(100)   NULL,
            telephone          VARCHAR(20)    NULL,
            site_web           VARCHAR(255)   NULL,
            description        TEXT           NULL,
            prix_chambre_nuit  DECIMAL(8,2)   NOT NULL DEFAULT 0.00,
            capacite_chambres  SMALLINT UNSIGNED NULL,
            image_url          VARCHAR(255)   NULL,
            note_moyenne       DECIMAL(3,2)   NOT NULL DEFAULT 0.00,
            statut             VARCHAR(10)    NOT NULL DEFAULT 'ACTIF',
            PRIMARY KEY (id),
            KEY idx_localisation (localisation_id)
        ) $charset;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_transport (
            id                       INT UNSIGNED   NOT NULL AUTO_INCREMENT,
            localisation_depart_id   INT UNSIGNED   NOT NULL,
            localisation_arrivee_id  INT UNSIGNED   NOT NULL,
            type                     VARCHAR(20)    NOT NULL,
            compagnie                VARCHAR(100)   NULL,
            numero_liaison           VARCHAR(30)    NULL,
            duree_trajet_minutes     SMALLINT UNSIGNED NULL,
            prix_par_personne        DECIMAL(8,2)   NOT NULL DEFAULT 0.00,
            capacite                 SMALLINT UNSIGNED NULL,
            statut                   VARCHAR(10)    NOT NULL DEFAULT 'ACTIF',
            PRIMARY KEY (id),
            KEY idx_depart  (localisation_depart_id),
            KEY idx_arrivee (localisation_arrivee_id)
        ) $charset;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_type_client (
            id                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
            libelle                VARCHAR(50)  NOT NULL,
            taux_remise            DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            seuil_points           INT UNSIGNED NOT NULL DEFAULT 0,
            points_jamais_expirent TINYINT(1)   NOT NULL DEFAULT 0,
            couleur_badge          VARCHAR(7)   NOT NULL DEFAULT '#6c757d',
            avantages              TEXT         NULL,
            ordre_affichage        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            actif                  TINYINT(1)   NOT NULL DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY uq_libelle (libelle)
        ) $charset;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_client_profile (
            id                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
            wp_user_id            BIGINT UNSIGNED NOT NULL,
            type_client_id        INT UNSIGNED    NOT NULL DEFAULT 1,
            telephone             VARCHAR(20)     NULL,
            date_naissance        DATE            NULL,
            points_fidelite_solde INT UNSIGNED    NOT NULL DEFAULT 0,
            points_cumules_total  INT UNSIGNED    NOT NULL DEFAULT 0,
            statut                VARCHAR(10)     NOT NULL DEFAULT 'ACTIF',
            avatar_url            VARCHAR(255)    NULL,
            ville                 VARCHAR(100)    NULL,
            pays                  VARCHAR(100)    NOT NULL DEFAULT 'Maroc',
            date_inscription      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_wp_user (wp_user_id),
            KEY idx_type_client (type_client_id)
        ) $charset;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_historique_points (
            id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
            client_id       INT UNSIGNED    NOT NULL,
            wc_order_id     BIGINT UNSIGNED NULL,
            operation       VARCHAR(20)     NOT NULL,
            points          INT             NOT NULL,
            solde_avant     INT UNSIGNED    NOT NULL,
            solde_apres     INT UNSIGNED    NOT NULL,
            date_operation  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_expiration DATE            NULL,
            description     VARCHAR(255)    NULL,
            PRIMARY KEY (id),
            KEY idx_client (client_id),
            KEY idx_date   (date_operation)
        ) $charset;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_log_admin (
            id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            wp_user_id   BIGINT UNSIGNED  NOT NULL,
            action       VARCHAR(20)      NOT NULL,
            entite_cible VARCHAR(50)      NOT NULL,
            entite_id    BIGINT UNSIGNED  NULL,
            valeurs_avant LONGTEXT        NULL,
            valeurs_apres LONGTEXT        NULL,
            ip_address   VARCHAR(45)      NOT NULL,
            user_agent   VARCHAR(255)     NULL,
            date_action  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            notes        TEXT             NULL,
            PRIMARY KEY (id),
            KEY idx_user   (wp_user_id),
            KEY idx_action (action),
            KEY idx_date   (date_action)
        ) $charset;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}tma_notification (
            id                BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            destinataire_type VARCHAR(10)      NOT NULL,
            client_id         INT UNSIGNED     NULL,
            wp_admin_user_id  BIGINT UNSIGNED  NULL,
            wc_order_id       BIGINT UNSIGNED  NULL,
            offre_post_id     BIGINT UNSIGNED  NULL,
            type              VARCHAR(40)      NOT NULL,
            titre             VARCHAR(200)     NOT NULL,
            message           TEXT             NOT NULL,
            canal             VARCHAR(10)      NOT NULL DEFAULT 'EMAIL',
            statut            VARCHAR(15)      NOT NULL DEFAULT 'EN_ATTENTE',
            date_creation     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            date_envoi        DATETIME         NULL,
            date_lecture      DATETIME         NULL,
            tentatives        TINYINT UNSIGNED NOT NULL DEFAULT 0,
            erreur_details    TEXT             NULL,
            PRIMARY KEY (id),
            KEY idx_client  (client_id),
            KEY idx_statut  (statut),
            KEY idx_date    (date_creation)
        ) $charset;
        ";

        // dbDelta exécute chaque CREATE TABLE séparément
        foreach ( explode( ';', $sql ) as $q ) {
            $q = trim( $q );
            if ( $q ) dbDelta( $q . ';' );
        }
    }

    public static function seed_resources_demo(): void {
        self::seed_default_data();
    }

    private static function seed_default_data() {
        global $wpdb;

        // Types clients
        $table_tc = $wpdb->prefix . 'tma_type_client';
        if ( (int)$wpdb->get_var( "SELECT COUNT(*) FROM $table_tc" ) === 0 ) {
            $wpdb->insert( $table_tc, [ 'libelle' => 'Standard',   'taux_remise' => 0,  'seuil_points' => 0,    'couleur_badge' => '#6c757d', 'ordre_affichage' => 1 ] );
            $wpdb->insert( $table_tc, [ 'libelle' => 'Silver',     'taux_remise' => 5,  'seuil_points' => 500,  'couleur_badge' => '#adb5bd', 'ordre_affichage' => 2 ] );
            $wpdb->insert( $table_tc, [ 'libelle' => 'Gold',       'taux_remise' => 10, 'seuil_points' => 1500, 'couleur_badge' => '#ffc107', 'ordre_affichage' => 3 ] );
            $wpdb->insert( $table_tc, [ 'libelle' => 'VIP',        'taux_remise' => 20, 'seuil_points' => 5000, 'points_jamais_expirent' => 1, 'couleur_badge' => '#6f42c1', 'ordre_affichage' => 4 ] );
            $wpdb->insert( $table_tc, [ 'libelle' => 'Entreprise', 'taux_remise' => 0,  'seuil_points' => 0,    'couleur_badge' => '#0d6efd', 'ordre_affichage' => 5 ] );
        }

        // Localisations marocaines de base (doit être seedé EN PREMIER — guides/hôtels/transport en dépendent)
        $table_loc = $wpdb->prefix . 'tma_localisation';
        if ( (int)$wpdb->get_var( "SELECT COUNT(*) FROM $table_loc" ) === 0 ) {
            $villes = [
                [ 'nom' => 'Casablanca',   'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 33.589886, 'longitude' => -7.603869  ],
                [ 'nom' => 'Marrakech',    'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 31.628674, 'longitude' => -7.992047  ],
                [ 'nom' => 'Fès',          'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 34.037310, 'longitude' => -4.999892  ],
                [ 'nom' => 'Rabat',        'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 33.991520, 'longitude' => -6.849813  ],
                [ 'nom' => 'Agadir',       'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 30.421533, 'longitude' => -9.589760  ],
                [ 'nom' => 'Meknès',       'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 33.895000, 'longitude' => -5.554722  ],
                [ 'nom' => 'Tanger',       'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 35.769141, 'longitude' => -5.795433  ],
                [ 'nom' => 'Ouarzazate',   'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 30.916561, 'longitude' => -6.893617  ],
                [ 'nom' => 'Merzouga',     'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 31.080000, 'longitude' => -4.014000  ],
                [ 'nom' => 'Chefchaouen',  'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 35.168860, 'longitude' => -5.269350  ],
                [ 'nom' => 'Essaouira',    'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 31.508380, 'longitude' => -9.759030  ],
                [ 'nom' => 'Dakhla',       'type' => 'VILLE',    'code_pays' => 'MA', 'latitude' => 23.714100, 'longitude' => -15.944800 ],
                [ 'nom' => 'Aéroport Mohammed V',       'type' => 'AEROPORT', 'code_pays' => 'MA', 'latitude' => 33.367535, 'longitude' => -7.589970  ],
                [ 'nom' => 'Aéroport Marrakech-Menara', 'type' => 'AEROPORT', 'code_pays' => 'MA', 'latitude' => 31.606900, 'longitude' => -8.036300  ],
                [ 'nom' => 'Sahara marocain', 'type' => 'REGION', 'code_pays' => 'MA', 'latitude' => 28.000000, 'longitude' => -7.000000 ],
                [ 'nom' => 'Haut Atlas',      'type' => 'REGION', 'code_pays' => 'MA', 'latitude' => 31.500000, 'longitude' => -7.500000 ],
            ];
            foreach ( $villes as $v ) {
                $wpdb->insert( $table_loc, $v );
            }
        }

        // Guides démo (id localisation: 1=Casa 2=Marrakech 3=Fès 8=Ouarzazate 9=Merzouga)
        $table_g = $wpdb->prefix . 'tma_guide';
        if ( (int)$wpdb->get_var( "SELECT COUNT(*) FROM $table_g" ) === 0 ) {
            $guides_demo = [
                [ 'loc' => 2, 'prenom' => 'Hassan',  'nom' => 'Ouazani',  'tel' => '+212 661-000-001', 'langues' => 'Arabe, Français, Anglais', 'spec' => 'Sahara, Désert, Randonnée', 'tarif' => 600, 'exp' => 12 ],
                [ 'loc' => 2, 'prenom' => 'Youssef', 'nom' => 'Amrani',   'tel' => '+212 661-000-002', 'langues' => 'Arabe, Français, Espagnol', 'spec' => 'Villes impériales, Culture', 'tarif' => 700, 'exp' => 15 ],
                [ 'loc' => 3, 'prenom' => 'Fatima',  'nom' => 'El Idrissi','tel' => '+212 661-000-003', 'langues' => 'Arabe, Français',           'spec' => 'Fès médiévale, Artisanat',  'tarif' => 500, 'exp' => 8  ],
                [ 'loc' => 8, 'prenom' => 'Driss',   'nom' => 'Aït Baha', 'tel' => '+212 661-000-004', 'langues' => 'Arabe, Français, Anglais', 'spec' => 'Ouarzazate, Cinéma, Kasbahs','tarif'=> 550, 'exp' => 10 ],
            ];
            foreach ( $guides_demo as $g ) {
                $wpdb->insert( $table_g, [
                    'localisation_id'   => $g['loc'],
                    'prenom'            => $g['prenom'],
                    'nom'               => $g['nom'],
                    'telephone'         => $g['tel'],
                    'langues'           => $g['langues'],
                    'specialites'       => $g['spec'],
                    'tarif_journalier'  => $g['tarif'],
                    'experience_annees' => $g['exp'],
                    'statut'            => 'ACTIF',
                ]);
            }
        }

        // Hôtels démo
        $table_h = $wpdb->prefix . 'tma_hotel';
        if ( (int)$wpdb->get_var( "SELECT COUNT(*) FROM $table_h" ) === 0 ) {
            $hotels_demo = [
                [ 'loc' => 2, 'nom' => 'Riad La Palmeraie', 'etoiles' => 4, 'prix' => 850,  'cap' => 12, 'desc' => 'Riad traditionnel au cœur de la médina de Marrakech.' ],
                [ 'loc' => 2, 'nom' => 'Hôtel Atlas Asni',  'etoiles' => 3, 'prix' => 450,  'cap' => 80, 'desc' => 'Hôtel moderne avec vue sur l\'Atlas.' ],
                [ 'loc' => 3, 'nom' => 'Riad Bab Boujloud', 'etoiles' => 4, 'prix' => 700,  'cap' => 8,  'desc' => 'Riad historique proche de Bab Boujloud à Fès.' ],
                [ 'loc' => 9, 'nom' => 'Auberge du Désert',  'etoiles' => 3, 'prix' => 600,  'cap' => 20, 'desc' => 'Hébergement berbère aux portes du Sahara à Merzouga.' ],
                [ 'loc' => 5, 'nom' => 'Sofitel Agadir',    'etoiles' => 5, 'prix' => 1500, 'cap' => 200,'desc' => 'Resort 5 étoiles face à l\'océan Atlantique.' ],
                [ 'loc' => 1, 'nom' => 'Hôtel Four Seasons Casablanca', 'etoiles' => 5, 'prix' => 2000, 'cap' => 150, 'desc' => 'Adresse de prestige en bord de mer à Casablanca.' ],
            ];
            foreach ( $hotels_demo as $h ) {
                $wpdb->insert( $table_h, [
                    'localisation_id'   => $h['loc'],
                    'nom'               => $h['nom'],
                    'categorie_etoiles' => $h['etoiles'],
                    'prix_chambre_nuit' => $h['prix'],
                    'capacite_chambres' => $h['cap'],
                    'description'       => $h['desc'],
                    'statut'            => 'ACTIF',
                ]);
            }
        }

        // Transports démo
        $table_t = $wpdb->prefix . 'tma_transport';
        if ( (int)$wpdb->get_var( "SELECT COUNT(*) FROM $table_t" ) === 0 ) {
            $transports_demo = [
                [ 'dep' => 13, 'arr' => 14, 'type' => 'AVION',          'compagnie' => 'Royal Air Maroc', 'num' => 'AT 600', 'duree' => 60,  'prix' => 800  ],
                [ 'dep' => 1,  'arr' => 2,  'type' => 'BUS',            'compagnie' => 'CTM',             'num' => 'C-01',   'duree' => 210, 'prix' => 120  ],
                [ 'dep' => 4,  'arr' => 1,  'type' => 'TRAIN',          'compagnie' => 'ONCF',            'num' => 'TR 5',   'duree' => 90,  'prix' => 95   ],
                [ 'dep' => 2,  'arr' => 9,  'type' => 'VOITURE_PRIVEE', 'compagnie' => 'Travel Maroc',    'num' => '',       'duree' => 480, 'prix' => 350  ],
                [ 'dep' => 13, 'arr' => 2,  'type' => 'AVION',          'compagnie' => 'Royal Air Maroc', 'num' => 'AT 201', 'duree' => 60,  'prix' => 700  ],
            ];
            foreach ( $transports_demo as $t ) {
                $wpdb->insert( $table_t, [
                    'localisation_depart_id'  => $t['dep'],
                    'localisation_arrivee_id' => $t['arr'],
                    'type'                    => $t['type'],
                    'compagnie'               => $t['compagnie'],
                    'numero_liaison'          => $t['num'],
                    'duree_trajet_minutes'    => $t['duree'],
                    'prix_par_personne'       => $t['prix'],
                    'statut'                  => 'ACTIF',
                ]);
            }
        }

    }
}
