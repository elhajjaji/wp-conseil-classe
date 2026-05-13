<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CC_Repo {
    public static function get_settings(): array {
        global $wpdb;
        $table = CC_DB::table('settings');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row("SELECT * FROM " . esc_sql($table) . " ORDER BY id ASC LIMIT 1", ARRAY_A);
        return is_array($row) ? $row : [];
    }

    public static function update_settings(array $data): void {
        global $wpdb;
        $table = CC_DB::table('settings');
        $current = self::get_settings();
        if (!$current) {
            return;
        }

        $update = $data;
        $update['updated_at'] = CC_Utils::mysql_now();

        // Log champ par champ (à la manière de LogParametres)
        foreach ($data as $k => $v) {
            if (array_key_exists($k, $current) && (string) $current[$k] !== (string) $v) {
                $section = (strpos($k, 'association') !== false) ? 'ASSOCIATION' : 'ETABLISSEMENT';
                CC_Logs::add('UPDATE', $section, $k, $current[$k], $v, "Modification du champ '$k'");
            }
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
        $wpdb->update($table, $update, ['id' => (int) $current['id']]);
    }

    public static function get_active_year(): ?array {
        global $wpdb;
        $table = CC_DB::table('years');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row("SELECT * FROM " . esc_sql($table) . " WHERE active = 1 ORDER BY id DESC LIMIT 1", ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public static function set_active_year(int $yearId): void {
        global $wpdb;
        $table = CC_DB::table('years');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $wpdb->query("UPDATE " . esc_sql($table) . " SET active = 0");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
        $wpdb->update($table, ['active' => 1], ['id' => $yearId], ['%d'], ['%d']);
    }

    public static function list_years(): array {
        global $wpdb;
        $table = CC_DB::table('years');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        return (array) $wpdb->get_results("SELECT * FROM " . esc_sql($table) . " ORDER BY nom DESC", ARRAY_A);
    }

    public static function create_year(string $nom, bool $active = false): int {
        global $wpdb;
        $table = CC_DB::table('years');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
        $wpdb->insert($table, [
            'nom' => $nom,
            'active' => $active ? 1 : 0,
            'created_at' => CC_Utils::mysql_now(),
        ], ['%s', '%d', '%s']);
        $id = (int) $wpdb->insert_id;
        if ($active) {
            self::set_active_year($id);
        }
        return $id;
    }

    public static function delete_year(int $id): void {
        global $wpdb;
        $table = CC_DB::table('years');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional delete.
        $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    public static function list_terms(): array {
        global $wpdb;
        $table = CC_DB::table('terms');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        return (array) $wpdb->get_results("SELECT * FROM " . esc_sql($table) . " ORDER BY nom ASC", ARRAY_A);
    }

    public static function get_active_term(): ?array {
        global $wpdb;
        $table = CC_DB::table('terms');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row("SELECT * FROM " . esc_sql($table) . " WHERE actif = 1 ORDER BY id DESC LIMIT 1", ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public static function set_active_term(int $termId): void {
        global $wpdb;
        $table = CC_DB::table('terms');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $wpdb->query("UPDATE " . esc_sql($table) . " SET actif = 0");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
        $wpdb->update($table, ['actif' => 1], ['id' => $termId], ['%d'], ['%d']);
    }

    public static function list_classes_for_year(int $yearId): array {
        global $wpdb;
        $table = CC_DB::table('classes');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        return (array) $wpdb->get_results($wpdb->prepare("SELECT * FROM " . esc_sql($table) . " WHERE year_id = %d ORDER BY niveau ASC, nom ASC", $yearId), ARRAY_A);
    }

    public static function get_class_by_nom_for_year(int $yearId, string $nom): ?array {
        global $wpdb;
        $table = CC_DB::table('classes');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . esc_sql($table) . " WHERE year_id = %d AND nom = %s LIMIT 1", $yearId, $nom),
            ARRAY_A
        );
        return is_array($row) ? $row : null;
    }

    public static function upsert_class(int $yearId, string $nom, string $niveau): int {
        global $wpdb;
        $existing = self::get_class_by_nom_for_year($yearId, $nom);
        if ($existing) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
            $wpdb->update(CC_DB::table('classes'), ['niveau' => $niveau], ['id' => (int) $existing['id']], ['%s'], ['%d']);
            return (int) $existing['id'];
        }
        return self::create_class($yearId, $nom, $niveau);
    }

    public static function create_class(int $yearId, string $nom, string $niveau): int {
        global $wpdb;
        $table = CC_DB::table('classes');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
        $wpdb->insert($table, [
            'nom' => $nom,
            'niveau' => $niveau,
            'year_id' => $yearId,
        ], ['%s', '%s', '%d']);
        return (int) $wpdb->insert_id;
    }

    public static function delete_class(int $id): void {
        global $wpdb;
        $table = CC_DB::table('classes');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional delete.
        $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    public static function list_councils(int $yearId, int $termId): array {
        global $wpdb;
        $councils = CC_DB::table('councils');
        $classes = CC_DB::table('classes');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*, cl.nom AS classe_nom, cl.niveau AS classe_niveau
                 FROM " . esc_sql($councils) . " c
                 JOIN " . esc_sql($classes) . " cl ON cl.id = c.class_id
                 WHERE c.year_id = %d AND c.term_id = %d
                 ORDER BY c.date_conseil ASC, c.heure_debut ASC",
                $yearId,
                $termId
            ),
            ARRAY_A
        );
    }

    public static function get_council(int $id): ?array {
        global $wpdb;
        $councils = CC_DB::table('councils');
        $classes = CC_DB::table('classes');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT c.*, cl.nom AS classe_nom, cl.niveau AS classe_niveau
                 FROM " . esc_sql($councils) . " c
                 JOIN " . esc_sql($classes) . " cl ON cl.id = c.class_id
                 WHERE c.id = %d
                 LIMIT 1",
                $id
            ),
            ARRAY_A
        );
        return is_array($row) ? $row : null;
    }

    public static function create_council(array $data): int {
        global $wpdb;
        $table = CC_DB::table('councils');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
        $wpdb->insert($table, $data);
        return (int) $wpdb->insert_id;
    }

    public static function delete_council(int $id): void {
        global $wpdb;
        $table = CC_DB::table('councils');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional delete.
        $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    public static function get_parent_by_wp_user(int $wpUserId): ?array {
        global $wpdb;
        $table = CC_DB::table('parents');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . esc_sql($table) . " WHERE wp_user_id = %d LIMIT 1", $wpUserId), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public static function get_parent_by_email(string $email): ?array {
        global $wpdb;
        $table = CC_DB::table('parents');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . esc_sql($table) . " WHERE email = %s LIMIT 1", CC_Utils::normalize_email($email)), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public static function get_parent(int $id): ?array {
        global $wpdb;
        $table = CC_DB::table('parents');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . esc_sql($table) . " WHERE id = %d LIMIT 1", $id), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public static function list_parents(string $search = '', string $profile = 'all'): array {
        global $wpdb;
        $table = CC_DB::table('parents');
        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
            $rows = (array) $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM " . esc_sql($table) . " WHERE nom LIKE %s OR prenom LIKE %s OR email LIKE %s ORDER BY nom ASC, prenom ASC",
                    $like,
                    $like,
                    $like
                ),
                ARRAY_A
            );
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
            $rows = (array) $wpdb->get_results("SELECT * FROM " . esc_sql($table) . " ORDER BY nom ASC, prenom ASC", ARRAY_A);
        }

        return self::filter_parents_by_wp_profile($rows, $profile);
    }

    /**
     * @param string $profile all|parent|admin
     *
     * @return array<int, array<string,mixed>>
     */
    private static function filter_parents_by_wp_profile(array $rows, string $profile): array {
        if ($profile !== 'parent' && $profile !== 'admin') {
            return $rows;
        }
        $out = [];
        foreach ($rows as $p) {
            $wid = (int) ($p['wp_user_id'] ?? 0);
            $isMgr = $wid > 0 && CC_Roles::user_can_manage_plugin($wid);
            if ($profile === 'admin' && $isMgr) {
                $out[] = $p;
            }
            if ($profile === 'parent' && !$isMgr) {
                $out[] = $p;
            }
        }

        return $out;
    }

    public static function access_code_exists(string $code, ?int $exceptParentId = null): bool {
        global $wpdb;
        $table = CC_DB::table('parents');
        if ($exceptParentId !== null && $exceptParentId > 0) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
            $count = (int) $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM " . esc_sql($table) . " WHERE code_acces = %s AND id != %d",
                $code,
                $exceptParentId
            ));
        } else {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
            $count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . esc_sql($table) . " WHERE code_acces = %s", $code));
        }
        return $count > 0;
    }

    public static function create_parent(array $data): int {
        global $wpdb;
        $table = CC_DB::table('parents');
        $data['email'] = CC_Utils::normalize_email((string) ($data['email'] ?? ''));
        $data['created_at'] = CC_Utils::mysql_now();
        if (empty($data['code_acces'])) {
            $data['code_acces'] = CC_Utils::generate_access_code([self::class, 'access_code_exists']);
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
        $wpdb->insert($table, $data);
        return (int) $wpdb->insert_id;
    }

    public static function update_parent(int $id, array $data): void {
        global $wpdb;
        $table = CC_DB::table('parents');
        if (isset($data['email'])) {
            $data['email'] = CC_Utils::normalize_email((string) $data['email']);
        }
        if (array_key_exists('code_acces', $data) && $data['code_acces'] !== null) {
            $data['code_acces'] = (string) $data['code_acces'];
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
        $wpdb->update($table, $data, ['id' => $id], null, ['%d']);
    }

    public static function delete_parent(int $id): void {
        global $wpdb;
        $table = CC_DB::table('parents');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional delete.
        $wpdb->delete($table, ['id' => $id], ['%d']);
    }

    public static function count_registrations_for_council(int $councilId): int {
        global $wpdb;
        $table = CC_DB::table('registrations');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . esc_sql($table) . " WHERE council_id = %d", $councilId));
    }

    public static function is_parent_registered(int $councilId, int $parentId): bool {
        global $wpdb;
        $table = CC_DB::table('registrations');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . esc_sql($table) . " WHERE council_id = %d AND parent_id = %d", $councilId, $parentId));
        return $count > 0;
    }

    public static function register_parent(int $councilId, int $parentId): void {
        global $wpdb;
        $table = CC_DB::table('registrations');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
        $wpdb->insert($table, [
            'parent_id' => $parentId,
            'council_id' => $councilId,
            'date_inscription' => CC_Utils::mysql_now(),
            'presente' => 0,
            'commentaire' => null,
        ], ['%d', '%d', '%s', '%d', '%s']);
    }

    public static function unregister_parent(int $councilId, int $parentId): void {
        global $wpdb;
        $table = CC_DB::table('registrations');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional delete.
        $wpdb->delete($table, ['council_id' => $councilId, 'parent_id' => $parentId], ['%d', '%d']);
    }

    public static function list_registrations_for_council(int $councilId): array {
        global $wpdb;
        $reg = CC_DB::table('registrations');
        $parents = CC_DB::table('parents');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, p.nom, p.prenom, p.email, p.telephone, p.code_acces
                 FROM " . esc_sql($reg) . " r
                 JOIN " . esc_sql($parents) . " p ON p.id = r.parent_id
                 WHERE r.council_id = %d
                 ORDER BY r.date_inscription ASC",
                $councilId
            ),
            ARRAY_A
        );
    }

    public static function list_parent_councils(int $parentId, int $activeYearId, int $activeTermId): array {
        global $wpdb;
        $reg = CC_DB::table('registrations');
        $councils = CC_DB::table('councils');
        $classes = CC_DB::table('classes');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $actifs = (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*, cl.nom AS classe_nom, cl.niveau AS classe_niveau
                 FROM " . esc_sql($reg) . " r
                 JOIN " . esc_sql($councils) . " c ON c.id = r.council_id
                 JOIN " . esc_sql($classes) . " cl ON cl.id = c.class_id
                 WHERE r.parent_id = %d AND c.year_id = %d AND c.term_id = %d
                 ORDER BY c.date_conseil DESC, c.heure_debut DESC",
                $parentId,
                $activeYearId,
                $activeTermId
            ),
            ARRAY_A
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $hist = (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT c.*, cl.nom AS classe_nom, cl.niveau AS classe_niveau
                 FROM " . esc_sql($reg) . " r
                 JOIN " . esc_sql($councils) . " c ON c.id = r.council_id
                 JOIN " . esc_sql($classes) . " cl ON cl.id = c.class_id
                 WHERE r.parent_id = %d AND (c.year_id != %d OR c.term_id != %d)
                 ORDER BY c.date_conseil DESC, c.heure_debut DESC",
                $parentId,
                $activeYearId,
                $activeTermId
            ),
            ARRAY_A
        );

        return ['actifs' => $actifs, 'historique' => $hist];
    }

    public static function get_report_for_council(int $councilId): ?array {
        global $wpdb;
        $table = CC_DB::table('reports');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . esc_sql($table) . " WHERE council_id = %d LIMIT 1", $councilId), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public static function create_report(array $data): int {
        global $wpdb;
        $table = CC_DB::table('reports');
        $now = CC_Utils::mysql_now();
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
        $wpdb->insert($table, $data);
        return (int) $wpdb->insert_id;
    }

    /** Mise à jour du contenu éditable d’un compte-rendu (admin). Ne modifie pas valide / council_id. */
    public static function update_report_content(int $reportId, array $data): void {
        global $wpdb;
        $table = CC_DB::table('reports');
        $now = CC_Utils::mysql_now();

        $row = [
            'nom_parent' => $data['nom_parent'],
            'prenom_parent' => $data['prenom_parent'],
            'email_parent' => $data['email_parent'],
            'profs_participants' => $data['profs_participants'],
            'delegue_eleve_1_nom' => $data['delegue_eleve_1_nom'],
            'delegue_eleve_1_prenom' => $data['delegue_eleve_1_prenom'],
            'delegue_eleve_2_nom' => $data['delegue_eleve_2_nom'],
            'delegue_eleve_2_prenom' => $data['delegue_eleve_2_prenom'],
            'delegue_parent_1_nom' => $data['delegue_parent_1_nom'],
            'delegue_parent_1_prenom' => $data['delegue_parent_1_prenom'],
            'delegue_parent_2_nom' => $data['delegue_parent_2_nom'],
            'delegue_parent_2_prenom' => $data['delegue_parent_2_prenom'],
            'remarque_principal' => $data['remarque_principal'],
            'remarque_prof_principal' => $data['remarque_prof_principal'],
            'remarques_autres_profs' => $data['remarques_autres_profs'],
            'remarques_eleves_delegues' => $data['remarques_eleves_delegues'],
            'remarques_parents' => $data['remarques_parents'],
            'nb_felicitations' => $data['nb_felicitations'],
            'nb_encouragements' => $data['nb_encouragements'],
            'nb_compliments' => $data['nb_compliments'],
            'nb_mise_en_garde_travail' => $data['nb_mise_en_garde_travail'],
            'nb_mise_en_garde_comportement' => $data['nb_mise_en_garde_comportement'],
            'updated_at' => $now,
        ];

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
        $wpdb->update(
            $table,
            $row,
            ['id' => $reportId],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d', '%d', '%s'],
            ['%d']
        );
    }

    public static function set_report_validation(int $reportId, bool $valide): void {
        global $wpdb;
        $table = CC_DB::table('reports');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
        $wpdb->update($table, [
            'valide' => $valide ? 1 : 0,
            'date_validation' => $valide ? CC_Utils::mysql_now() : null,
            'updated_at' => CC_Utils::mysql_now(),
        ], ['id' => $reportId], ['%d', '%s', '%s'], ['%d']);
    }

    public static function list_reports(int $yearId, int $termId): array {
        global $wpdb;
        $reports = CC_DB::table('reports');
        $councils = CC_DB::table('councils');
        $classes = CC_DB::table('classes');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, c.date_conseil, c.heure_debut, c.president_conseil, cl.nom AS classe_nom, cl.niveau AS classe_niveau
                 FROM " . esc_sql($reports) . " r
                 JOIN " . esc_sql($councils) . " c ON c.id = r.council_id
                 JOIN " . esc_sql($classes) . " cl ON cl.id = c.class_id
                 WHERE c.year_id = %d AND c.term_id = %d
                 ORDER BY c.date_conseil DESC, c.heure_debut DESC",
                $yearId,
                $termId
            ),
            ARRAY_A
        );
    }

    public static function get_report(int $reportId): ?array {
        global $wpdb;
        $reports = CC_DB::table('reports');
        $councils = CC_DB::table('councils');
        $classes = CC_DB::table('classes');
        $years = CC_DB::table('years');
        $terms = CC_DB::table('terms');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT r.*,
                        c.date_conseil, c.heure_debut, c.heure_fin, c.salle_conseil, c.president_conseil,
                        c.year_id, c.term_id,
                        cl.nom AS classe_nom, cl.niveau AS classe_niveau,
                        y.nom AS annee_nom,
                        t.nom AS trimestre_nom
                 FROM " . esc_sql($reports) . " r
                 JOIN " . esc_sql($councils) . " c ON c.id = r.council_id
                 JOIN " . esc_sql($classes) . " cl ON cl.id = c.class_id
                 LEFT JOIN " . esc_sql($years) . " y ON y.id = c.year_id
                 LEFT JOIN " . esc_sql($terms) . " t ON t.id = c.term_id
                 WHERE r.id = %d
                 LIMIT 1",
                $reportId
            ),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }

    // =========================
    // Templates PDF
    // =========================
    public static function list_pdf_templates(): array {
        global $wpdb;
        $table = CC_DB::table('pdf_templates');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        return (array) $wpdb->get_results("SELECT * FROM " . esc_sql($table) . " ORDER BY actif DESC, updated_at DESC", ARRAY_A);
    }

    public static function get_pdf_template(int $id): ?array {
        global $wpdb;
        $table = CC_DB::table('pdf_templates');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . esc_sql($table) . " WHERE id = %d LIMIT 1", $id), ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public static function get_active_pdf_template(): ?array {
        global $wpdb;
        $table = CC_DB::table('pdf_templates');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row("SELECT * FROM " . esc_sql($table) . " WHERE actif = 1 ORDER BY id DESC LIMIT 1", ARRAY_A);
        return is_array($row) ? $row : null;
    }

    public static function create_pdf_template(string $nom, string $description, string $html, string $css, bool $actif): int {
        global $wpdb;
        $table = CC_DB::table('pdf_templates');
        $now = CC_Utils::mysql_now();
        if ($actif) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
            $wpdb->query("UPDATE " . esc_sql($table) . " SET actif = 0");
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
        $wpdb->insert($table, [
            'nom' => $nom,
            'description' => $description,
            'html_template' => $html,
            'css_style' => $css,
            'actif' => $actif ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ], ['%s', '%s', '%s', '%s', '%d', '%s', '%s']);
        return (int) $wpdb->insert_id;
    }

    public static function update_pdf_template(int $id, string $nom, string $description, string $html, string $css, bool $actif): void {
        global $wpdb;
        $table = CC_DB::table('pdf_templates');
        if ($actif) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
            $wpdb->query("UPDATE " . esc_sql($table) . " SET actif = 0");
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
        $wpdb->update($table, [
            'nom' => $nom,
            'description' => $description,
            'html_template' => $html,
            'css_style' => $css,
            'actif' => $actif ? 1 : 0,
            'updated_at' => CC_Utils::mysql_now(),
        ], ['id' => $id], ['%s', '%s', '%s', '%s', '%d', '%s'], ['%d']);
    }

    public static function activate_pdf_template(int $id): void {
        global $wpdb;
        $table = CC_DB::table('pdf_templates');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $wpdb->query("UPDATE " . esc_sql($table) . " SET actif = 0");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
        $wpdb->update($table, ['actif' => 1, 'updated_at' => CC_Utils::mysql_now()], ['id' => $id], ['%d', '%s'], ['%d']);
    }

    public static function delete_pdf_template(int $id): void {
        global $wpdb;
        $table = CC_DB::table('pdf_templates');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional delete.
        $wpdb->delete($table, ['id' => $id], ['%d']);
    }
}

