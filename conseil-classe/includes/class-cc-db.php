<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CC_DB {
    public static function table(string $suffix): string {
        global $wpdb;
        return $wpdb->prefix . 'cc_' . $suffix;
    }

    public static function install(): void {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charsetCollate = $wpdb->get_charset_collate();

        $tables = [];

        $tables[] = "CREATE TABLE " . self::table('settings') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            year_id BIGINT UNSIGNED NULL,
            nom_etablissement VARCHAR(200) NOT NULL DEFAULT '',
            adresse_etablissement TEXT NULL,
            telephone_etablissement VARCHAR(20) NULL,
            email_etablissement VARCHAR(120) NULL,
            site_web_etablissement VARCHAR(200) NULL,
            nom_directeur VARCHAR(100) NULL,
            nom_principal VARCHAR(100) NULL,
            nom_association_parents VARCHAR(200) NULL,
            adresse_association_parents TEXT NULL,
            telephone_association_parents VARCHAR(20) NULL,
            email_association_parents VARCHAR(120) NULL,
            site_web_association_parents VARCHAR(200) NULL,
            president_association VARCHAR(100) NULL,
            vice_president_association VARCHAR(100) NULL,
            secretaire_association VARCHAR(100) NULL,
            tresorier_association VARCHAR(100) NULL,
            max_parents_per_conseil INT NOT NULL DEFAULT 2,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY year_id (year_id)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('logs') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            timestamp DATETIME NOT NULL,
            action VARCHAR(50) NOT NULL,
            section VARCHAR(50) NOT NULL,
            champ VARCHAR(100) NULL,
            ancienne_valeur LONGTEXT NULL,
            nouvelle_valeur LONGTEXT NULL,
            utilisateur VARCHAR(100) NULL,
            adresse_ip VARCHAR(45) NULL,
            description LONGTEXT NULL,
            PRIMARY KEY (id),
            KEY section (section),
            KEY action (action),
            KEY timestamp (timestamp)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('years') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            nom VARCHAR(20) NOT NULL,
            active TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY nom (nom),
            KEY active (active)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('terms') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            nom VARCHAR(10) NOT NULL,
            actif TINYINT(1) NOT NULL DEFAULT 0,
            year_id BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY nom (nom),
            KEY actif (actif),
            KEY year_id (year_id)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('classes') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            nom VARCHAR(10) NOT NULL,
            niveau VARCHAR(20) NOT NULL,
            year_id BIGINT UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_classe_annee (nom, year_id),
            KEY year_id (year_id)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('councils') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            term_id BIGINT UNSIGNED NOT NULL,
            year_id BIGINT UNSIGNED NOT NULL,
            class_id BIGINT UNSIGNED NOT NULL,
            date_conseil DATE NOT NULL,
            heure_debut TIME NOT NULL,
            heure_fin TIME NULL,
            salle_conseil VARCHAR(50) NOT NULL DEFAULT '',
            president_conseil VARCHAR(100) NOT NULL DEFAULT '',
            PRIMARY KEY (id),
            UNIQUE KEY unique_conseil (term_id, year_id, class_id),
            KEY year_id (year_id),
            KEY term_id (term_id),
            KEY class_id (class_id),
            KEY date_conseil (date_conseil)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('parents') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wp_user_id BIGINT UNSIGNED NULL,
            nom VARCHAR(100) NOT NULL,
            prenom VARCHAR(100) NOT NULL,
            email VARCHAR(120) NOT NULL,
            telephone VARCHAR(20) NULL,
            code_acces VARCHAR(8) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            UNIQUE KEY code_acces (code_acces),
            KEY wp_user_id (wp_user_id)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('parent_years') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            parent_id BIGINT UNSIGNED NOT NULL,
            year_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_parent_year (parent_id, year_id),
            KEY parent_id (parent_id),
            KEY year_id (year_id)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('registrations') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            parent_id BIGINT UNSIGNED NOT NULL,
            council_id BIGINT UNSIGNED NOT NULL,
            date_inscription DATETIME NOT NULL,
            presente TINYINT(1) NOT NULL DEFAULT 0,
            commentaire LONGTEXT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_inscription_parent (parent_id, council_id),
            KEY council_id (council_id),
            KEY parent_id (parent_id)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('reports') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            council_id BIGINT UNSIGNED NOT NULL,
            nom_parent VARCHAR(100) NULL,
            prenom_parent VARCHAR(100) NULL,
            email_parent VARCHAR(200) NULL,
            profs_participants LONGTEXT NULL,
            delegue_eleve_1_nom VARCHAR(100) NULL,
            delegue_eleve_1_prenom VARCHAR(100) NULL,
            delegue_eleve_2_nom VARCHAR(100) NULL,
            delegue_eleve_2_prenom VARCHAR(100) NULL,
            delegue_parent_1_nom VARCHAR(100) NULL,
            delegue_parent_1_prenom VARCHAR(100) NULL,
            delegue_parent_2_nom VARCHAR(100) NULL,
            delegue_parent_2_prenom VARCHAR(100) NULL,
            remarque_principal LONGTEXT NULL,
            remarque_prof_principal LONGTEXT NULL,
            remarques_autres_profs LONGTEXT NULL,
            remarques_eleves_delegues LONGTEXT NULL,
            remarques_parents LONGTEXT NULL,
            nb_felicitations INT NOT NULL DEFAULT 0,
            nb_encouragements INT NOT NULL DEFAULT 0,
            nb_compliments INT NOT NULL DEFAULT 0,
            nb_mise_en_garde_travail INT NOT NULL DEFAULT 0,
            nb_mise_en_garde_comportement INT NOT NULL DEFAULT 0,
            valide TINYINT(1) NOT NULL DEFAULT 0,
            date_validation DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY council_id (council_id),
            KEY valide (valide)
        ) $charsetCollate;";

        $tables[] = "CREATE TABLE " . self::table('pdf_templates') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            nom VARCHAR(100) NOT NULL,
            description LONGTEXT NULL,
            html_template LONGTEXT NOT NULL,
            css_style LONGTEXT NULL,
            actif TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY nom (nom),
            KEY actif (actif)
        ) $charsetCollate;";

        foreach ($tables as $sql) {
            dbDelta($sql);
        }

        self::ensure_seed_data();
    }

    private static function ensure_seed_data(): void {
        global $wpdb;

        $settingsTable = self::table('settings');
        $yearsTable = self::table('years');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $count = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . esc_sql($settingsTable));
        if ($count === 0) {
            $now = current_time('mysql');
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- intentional insert.
            $wpdb->insert($settingsTable, [
                'year_id' => null,
                'nom_etablissement' => __('Mon établissement', 'conseil-de-classe'),
                'max_parents_per_conseil' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ], ['%d', '%s', '%d', '%s', '%s']);
        }

        $termsTable = self::table('terms');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $termCount = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . esc_sql($termsTable));
        if ($termCount === 0) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- intentional insert.
            $wpdb->insert($termsTable, ['nom' => 'T1', 'actif' => 1, 'year_id' => null]);
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- intentional insert.
            $wpdb->insert($termsTable, ['nom' => 'T2', 'actif' => 0, 'year_id' => null]);
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- intentional insert.
            $wpdb->insert($termsTable, ['nom' => 'T3', 'actif' => 0, 'year_id' => null]);
        }

        // Migre l'ancienne configuration globale vers l'année active si nécessaire.
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $activeYearId = (int) $wpdb->get_var('SELECT id FROM ' . esc_sql($yearsTable) . ' WHERE active = 1 ORDER BY id DESC LIMIT 1');
        if ($activeYearId > 0) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
            $settingsAssigned = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM ' . esc_sql($settingsTable) . ' WHERE year_id = %d', $activeYearId));
            if ($settingsAssigned === 0) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
                $legacySettingsId = (int) $wpdb->get_var('SELECT id FROM ' . esc_sql($settingsTable) . ' WHERE year_id IS NULL ORDER BY id ASC LIMIT 1');
                if ($legacySettingsId > 0) {
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update on activation/migration path.
                    $wpdb->update($settingsTable, ['year_id' => $activeYearId], ['id' => $legacySettingsId], ['%d'], ['%d']);
                }
            }
        }

        $parentYearsTable = self::table('parent_years');
        // Associe les parents existants à l'année active pour préserver le comportement historique.
        if ($activeYearId > 0) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
            $assignCount = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . esc_sql($parentYearsTable));
            if ($assignCount === 0) {
                $parentsTable = self::table('parents');
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
                $parents = (array) $wpdb->get_results('SELECT id FROM ' . esc_sql($parentsTable), ARRAY_A);
                $now = current_time('mysql');
                foreach ($parents as $parent) {
                    $parentId = (int) ($parent['id'] ?? 0);
                    if ($parentId <= 0) {
                        continue;
                    }
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- intentional insert.
                    $wpdb->insert($parentYearsTable, [
                        'parent_id' => $parentId,
                        'year_id' => $activeYearId,
                        'created_at' => $now,
                    ], ['%d', '%d', '%s']);
                }
            }
        }

        $tplTable = self::table('pdf_templates');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $tplCount = (int) $wpdb->get_var('SELECT COUNT(*) FROM ' . esc_sql($tplTable));
        if ($tplCount === 0) {
            $now = current_time('mysql');
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- intentional insert.
            $wpdb->insert($tplTable, [
                'nom' => __('Template par défaut', 'conseil-de-classe'),
                'description' => __('Template de base pour l’export PDF des comptes-rendus', 'conseil-de-classe'),
                'html_template' => CC_Defaults::default_pdf_html_template(),
                'css_style' => CC_Defaults::default_pdf_css(),
                'actif' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

