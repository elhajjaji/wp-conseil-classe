<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CC_Repo {
    public static function get_settings(?int $yearId = null): array {
        global $wpdb;
        $table = CC_DB::table('settings');
        if ($yearId === null || $yearId <= 0) {
            $year = self::get_active_year();
            $yearId = $year ? (int) $year['id'] : 0;
        }
        if ($yearId > 0) {
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . esc_sql($table) . " WHERE year_id = %d LIMIT 1", $yearId), ARRAY_A);
            if (is_array($row)) {
                return $row;
            }
        }
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- fallback legacy row.
        $row = $wpdb->get_row("SELECT * FROM " . esc_sql($table) . " WHERE year_id IS NULL ORDER BY id ASC LIMIT 1", ARRAY_A);
        return is_array($row) ? $row : [];
    }

    public static function ensure_settings_for_year(int $yearId): array {
        global $wpdb;
        $table = CC_DB::table('settings');
        $current = self::get_settings($yearId);
        if ($current && (int) ($current['year_id'] ?? 0) === $yearId) {
            return $current;
        }
        $now = CC_Utils::mysql_now();
        $defaults = [
            'year_id' => $yearId,
            'nom_etablissement' => '',
            'adresse_etablissement' => '',
            'telephone_etablissement' => '',
            'email_etablissement' => '',
            'site_web_etablissement' => '',
            'nom_directeur' => '',
            'nom_principal' => '',
            'nom_association_parents' => '',
            'adresse_association_parents' => '',
            'telephone_association_parents' => '',
            'email_association_parents' => '',
            'site_web_association_parents' => '',
            'president_association' => '',
            'vice_president_association' => '',
            'secretaire_association' => '',
            'tresorier_association' => '',
            'max_parents_per_conseil' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
        $wpdb->insert($table, $defaults, ['%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s']);
        return self::get_settings($yearId);
    }

    public static function update_settings(array $data, ?int $yearId = null): void {
        global $wpdb;
        $table = CC_DB::table('settings');
        if ($yearId === null || $yearId <= 0) {
            $year = self::get_active_year();
            $yearId = $year ? (int) $year['id'] : 0;
        }
        if ($yearId <= 0) {
            return;
        }
        $current = self::ensure_settings_for_year($yearId);

        $update = $data;
        $update['year_id'] = $yearId;
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
        self::ensure_settings_for_year($id);
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

    public static function list_parents(string $search = '', string $profile = 'all', ?int $yearId = null): array {
        global $wpdb;
        $table = CC_DB::table('parents');
        $parentYears = CC_DB::table('parent_years');
        if ($yearId === null || $yearId <= 0) {
            $year = self::get_active_year();
            $yearId = $year ? (int) $year['id'] : 0;
        }
        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';
            if ($yearId > 0) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
                $rows = (array) $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT p.* FROM " . esc_sql($table) . " p
                         INNER JOIN " . esc_sql($parentYears) . " py ON py.parent_id = p.id
                         WHERE py.year_id = %d AND (p.nom LIKE %s OR p.prenom LIKE %s OR p.email LIKE %s)
                         ORDER BY p.nom ASC, p.prenom ASC",
                        $yearId,
                        $like,
                        $like,
                        $like
                    ),
                    ARRAY_A
                );
            } else {
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
            }
        } else {
            if ($yearId > 0) {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
                $rows = (array) $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT p.* FROM " . esc_sql($table) . " p
                         INNER JOIN " . esc_sql($parentYears) . " py ON py.parent_id = p.id
                         WHERE py.year_id = %d
                         ORDER BY p.nom ASC, p.prenom ASC",
                        $yearId
                    ),
                    ARRAY_A
                );
            } else {
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
                $rows = (array) $wpdb->get_results("SELECT * FROM " . esc_sql($table) . " ORDER BY nom ASC, prenom ASC", ARRAY_A);
            }
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
        $parentId = (int) $wpdb->insert_id;
        $year = self::get_active_year();
        if ($parentId > 0 && $year) {
            self::assign_parent_to_year($parentId, (int) $year['id']);
        }
        return $parentId;
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

    public static function assign_parent_to_year(int $parentId, int $yearId): void {
        global $wpdb;
        $table = CC_DB::table('parent_years');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $exists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . esc_sql($table) . " WHERE parent_id = %d AND year_id = %d", $parentId, $yearId));
        if ($exists > 0) {
            return;
        }
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
        $wpdb->insert($table, [
            'parent_id' => $parentId,
            'year_id' => $yearId,
            'created_at' => CC_Utils::mysql_now(),
        ], ['%d', '%d', '%s']);
    }

    public static function unassign_parent_from_year(int $parentId, int $yearId): void {
        global $wpdb;
        $table = CC_DB::table('parent_years');
        $reg = CC_DB::table('registrations');
        $councils = CC_DB::table('councils');
        // Supprime d'abord les inscriptions de cette année active, puis l'affectation.
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $wpdb->query($wpdb->prepare(
            "DELETE r FROM " . esc_sql($reg) . " r
             INNER JOIN " . esc_sql($councils) . " c ON c.id = r.council_id
             WHERE r.parent_id = %d AND c.year_id = %d",
            $parentId,
            $yearId
        ));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional delete.
        $wpdb->delete($table, ['parent_id' => $parentId, 'year_id' => $yearId], ['%d', '%d']);
    }

    public static function is_parent_assigned_to_year(int $parentId, int $yearId): bool {
        global $wpdb;
        $table = CC_DB::table('parent_years');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . esc_sql($table) . " WHERE parent_id = %d AND year_id = %d", $parentId, $yearId));
        return $count > 0;
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

    public static function delete_report(int $reportId): void {
        global $wpdb;
        $table = CC_DB::table('reports');
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional delete.
        $wpdb->delete($table, ['id' => $reportId], ['%d']);
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

    public static function get_dashboard_stats(?int $yearId = null, ?int $termId = null): array {
        global $wpdb;

        $parents = CC_DB::table('parents');
        $classes = CC_DB::table('classes');
        $councils = CC_DB::table('councils');
        $registrations = CC_DB::table('registrations');
        $reports = CC_DB::table('reports');
        $templates = CC_DB::table('pdf_templates');

        $stats = [
            'global' => [
                'parents' => 0,
                'classes' => 0,
                'councils' => 0,
                'registrations' => 0,
                'reports' => 0,
                'validated_reports' => 0,
                'pdf_templates' => 0,
                'active_pdf_templates' => 0,
            ],
            'active' => [
                'classes' => 0,
                'classes_without_council' => 0,
                'councils' => 0,
                'orphan_councils' => 0,
                'registrations' => 0,
                'registered_parents' => 0,
                'reports' => 0,
                'validated_reports' => 0,
                'pending_reports' => 0,
            ],
        ];

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['global']['parents'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($parents));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['global']['classes'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($classes));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['global']['councils'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($councils));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['global']['registrations'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($registrations));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['global']['reports'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($reports));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['global']['validated_reports'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($reports) . " WHERE valide = 1");
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['global']['pdf_templates'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($templates));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['global']['active_pdf_templates'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM " . esc_sql($templates) . " WHERE actif = 1");

        // ── Per-year breakdown (for dashboard header strip) ──────────────────
        $stats['year']    = ['parents' => 0, 'classes' => 0, 'councils' => 0, 'reports' => 0];
        $stats['by_year'] = []; // [ year_nom => ['parents'=>n, 'classes'=>n, 'councils'=>n, 'reports'=>n] ]
        $years_table      = CC_DB::table('years');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $pa_by_yr = (array) $wpdb->get_results("SELECT y.id, y.nom, COUNT(DISTINCT r.parent_id) AS cnt FROM " . esc_sql($registrations) . " r JOIN " . esc_sql($councils) . " c ON c.id = r.council_id JOIN " . esc_sql($years_table) . " y ON y.id = c.year_id GROUP BY y.id, y.nom ORDER BY y.nom DESC", ARRAY_A);
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $cl_by_yr = (array) $wpdb->get_results("SELECT y.id, y.nom, COUNT(*) AS cnt FROM " . esc_sql($classes) . " cl JOIN " . esc_sql($years_table) . " y ON y.id = cl.year_id GROUP BY y.id, y.nom ORDER BY y.nom DESC", ARRAY_A);
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $co_by_yr = (array) $wpdb->get_results("SELECT y.id, y.nom, COUNT(*) AS cnt FROM " . esc_sql($councils) . " c JOIN " . esc_sql($years_table) . " y ON y.id = c.year_id GROUP BY y.id, y.nom ORDER BY y.nom DESC", ARRAY_A);
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $re_by_yr = (array) $wpdb->get_results("SELECT y.id, y.nom, COUNT(*) AS cnt FROM " . esc_sql($reports) . " r JOIN " . esc_sql($councils) . " c ON c.id = r.council_id JOIN " . esc_sql($years_table) . " y ON y.id = c.year_id GROUP BY y.id, y.nom ORDER BY y.nom DESC", ARRAY_A);
        $tmp = [];
        foreach ($pa_by_yr as $row) { $tmp[(int) $row['id']]['nom'] = $row['nom']; $tmp[(int) $row['id']]['parents']  = (int) $row['cnt']; }
        foreach ($cl_by_yr as $row) { $tmp[(int) $row['id']]['nom'] = $row['nom']; $tmp[(int) $row['id']]['classes']  = (int) $row['cnt']; }
        foreach ($co_by_yr as $row) { $tmp[(int) $row['id']]['nom'] = $row['nom']; $tmp[(int) $row['id']]['councils'] = (int) $row['cnt']; }
        foreach ($re_by_yr as $row) { $tmp[(int) $row['id']]['nom'] = $row['nom']; $tmp[(int) $row['id']]['reports']  = (int) $row['cnt']; }
        foreach ($tmp as $yrId => $data) {
            $nom                  = $data['nom'];
            $stats['by_year'][$nom] = [
                'parents'  => $data['parents']  ?? 0,
                'classes'  => $data['classes']  ?? 0,
                'councils' => $data['councils'] ?? 0,
                'reports'  => $data['reports']  ?? 0,
            ];
            if ($yearId && $yrId === $yearId) {
                $stats['year'] = $stats['by_year'][$nom];
            }
        }

        if (!$yearId || !$termId) {
            return $stats;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['active']['classes'] = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . esc_sql($classes) . " WHERE year_id = %d", $yearId));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['active']['councils'] = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . esc_sql($councils) . " WHERE year_id = %d AND term_id = %d", $yearId, $termId));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['active']['registrations'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
             FROM " . esc_sql($registrations) . " r
             JOIN " . esc_sql($councils) . " c ON c.id = r.council_id
             WHERE c.year_id = %d AND c.term_id = %d",
            $yearId,
            $termId
        ));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['active']['registered_parents'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT r.parent_id)
             FROM " . esc_sql($registrations) . " r
             JOIN " . esc_sql($councils) . " c ON c.id = r.council_id
             WHERE c.year_id = %d AND c.term_id = %d",
            $yearId,
            $termId
        ));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['active']['orphan_councils'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
             FROM " . esc_sql($councils) . " c
             LEFT JOIN " . esc_sql($registrations) . " r ON r.council_id = c.id
             WHERE c.year_id = %d AND c.term_id = %d AND r.id IS NULL",
            $yearId,
            $termId
        ));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['active']['reports'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
             FROM " . esc_sql($reports) . " r
             JOIN " . esc_sql($councils) . " c ON c.id = r.council_id
             WHERE c.year_id = %d AND c.term_id = %d",
            $yearId,
            $termId
        ));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['active']['validated_reports'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
             FROM " . esc_sql($reports) . " r
             JOIN " . esc_sql($councils) . " c ON c.id = r.council_id
             WHERE c.year_id = %d AND c.term_id = %d AND r.valide = 1",
            $yearId,
            $termId
        ));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $stats['active']['classes_without_council'] = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
             FROM " . esc_sql($classes) . " cl
             LEFT JOIN " . esc_sql($councils) . " c ON c.class_id = cl.id AND c.year_id = %d AND c.term_id = %d
             WHERE cl.year_id = %d AND c.id IS NULL",
            $yearId,
            $termId,
            $yearId
        ));
        $stats['active']['pending_reports'] = max(0, $stats['active']['councils'] - $stats['active']['reports']);

        return $stats;
    }

    public static function list_dashboard_class_stats(?int $yearId = null, ?int $termId = null): array {
        global $wpdb;

        if (!$yearId || !$termId) {
            return [];
        }

        $classes = CC_DB::table('classes');
        $councils = CC_DB::table('councils');
        $registrations = CC_DB::table('registrations');
        $reports = CC_DB::table('reports');

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT cl.id,
                        cl.nom,
                        cl.niveau,
                        c.id AS council_id,
                        c.date_conseil,
                        c.heure_debut,
                        c.heure_fin,
                        c.salle_conseil,
                        c.president_conseil,
                        COALESCE(reg.registrations_count, 0) AS registrations_count,
                        COALESCE(rep.report_id, 0) AS report_id,
                        COALESCE(rep.report_validated, 0) AS report_validated
                 FROM " . esc_sql($classes) . " cl
                 LEFT JOIN " . esc_sql($councils) . " c
                        ON c.class_id = cl.id AND c.year_id = %d AND c.term_id = %d
                 LEFT JOIN (
                        SELECT council_id, COUNT(*) AS registrations_count
                        FROM " . esc_sql($registrations) . "
                        GROUP BY council_id
                 ) reg ON reg.council_id = c.id
                 LEFT JOIN (
                        SELECT id AS report_id, council_id, valide AS report_validated
                        FROM " . esc_sql($reports) . "
                 ) rep ON rep.council_id = c.id
                 WHERE cl.year_id = %d
                 ORDER BY cl.niveau ASC, cl.nom ASC",
                $yearId,
                $termId,
                $yearId
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

    // ── Statistiques : appréciations par classe (trimestre actif) ─────────────

    public static function list_appreciations_by_class(int $yearId, int $termId): array {
        global $wpdb;
        $classes  = CC_DB::table('classes');
        $councils = CC_DB::table('councils');
        $reports  = CC_DB::table('reports');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT cl.nom,
                        cl.niveau,
                        COUNT(r.id)                                      AS nb_reports,
                        COALESCE(SUM(r.nb_felicitations),0)              AS sum_fel,
                        COALESCE(SUM(r.nb_encouragements),0)             AS sum_enc,
                        COALESCE(SUM(r.nb_compliments),0)                AS sum_comp,
                        COALESCE(SUM(r.nb_mise_en_garde_travail),0)      AS sum_mgt,
                        COALESCE(SUM(r.nb_mise_en_garde_comportement),0) AS sum_mgc
                 FROM " . esc_sql($classes) . " cl
                 LEFT JOIN " . esc_sql($councils) . " co
                        ON co.class_id = cl.id AND co.year_id = %d AND co.term_id = %d
                 LEFT JOIN " . esc_sql($reports) . " r ON r.council_id = co.id
                 WHERE cl.year_id = %d
                 GROUP BY cl.id, cl.nom, cl.niveau
                 ORDER BY cl.niveau ASC, cl.nom ASC",
                $yearId, $termId, $yearId
            ),
            ARRAY_A
        );
    }

    // ── Statistiques : appréciations par trimestre (toute l'année) ────────────

    public static function list_appreciations_by_term(int $yearId): array {
        global $wpdb;
        $councils = CC_DB::table('councils');
        $reports  = CC_DB::table('reports');
        $terms    = CC_DB::table('terms');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT t.id AS term_id,
                        t.nom AS term_nom,
                        COUNT(r.id)                                      AS nb_reports,
                        COALESCE(SUM(r.nb_felicitations),0)              AS sum_fel,
                        COALESCE(SUM(r.nb_encouragements),0)             AS sum_enc,
                        COALESCE(SUM(r.nb_compliments),0)                AS sum_comp,
                        COALESCE(SUM(r.nb_mise_en_garde_travail),0)      AS sum_mgt,
                        COALESCE(SUM(r.nb_mise_en_garde_comportement),0) AS sum_mgc
                 FROM " . esc_sql($terms) . " t
                 INNER JOIN " . esc_sql($councils) . " co
                        ON co.term_id = t.id AND co.year_id = %d
                 LEFT JOIN " . esc_sql($reports) . " r ON r.council_id = co.id
                 GROUP BY t.id, t.nom
                 ORDER BY t.id ASC",
                $yearId
            ),
            ARRAY_A
        );
    }

    // ── Statistiques : appréciations par classe × trimestre (évolution) ───────

    public static function list_appreciations_all_classes_all_terms(int $yearId): array {
        global $wpdb;
        $classes  = CC_DB::table('classes');
        $councils = CC_DB::table('councils');
        $reports  = CC_DB::table('reports');
        $terms    = CC_DB::table('terms');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT cl.id AS class_id,
                        cl.nom AS class_nom,
                        t.nom AS term_nom,
                        COUNT(r.id)                                      AS nb_reports,
                        COALESCE(SUM(r.nb_felicitations),0)              AS sum_fel,
                        COALESCE(SUM(r.nb_encouragements),0)             AS sum_enc,
                        COALESCE(SUM(r.nb_compliments),0)                AS sum_comp,
                        COALESCE(SUM(r.nb_mise_en_garde_travail),0)      AS sum_mgt,
                        COALESCE(SUM(r.nb_mise_en_garde_comportement),0) AS sum_mgc
                 FROM " . esc_sql($classes) . " cl
                 INNER JOIN " . esc_sql($councils) . " co
                        ON co.class_id = cl.id AND co.year_id = %d
                 INNER JOIN " . esc_sql($terms) . " t ON t.id = co.term_id
                 LEFT JOIN " . esc_sql($reports) . " r ON r.council_id = co.id
                 WHERE cl.year_id = %d
                 GROUP BY cl.id, cl.nom, t.id, t.nom
                 ORDER BY cl.niveau ASC, cl.nom ASC, t.id ASC",
                $yearId, $yearId
            ),
            ARRAY_A
        );
    }

    // ── Statistiques : top parents (nb inscriptions, trimestre actif) ─────────

    public static function list_top_parents(int $yearId, int $termId, int $limit = 5): array {
        global $wpdb;
        $parents       = CC_DB::table('parents');
        $registrations = CC_DB::table('registrations');
        $councils      = CC_DB::table('councils');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.nom, p.prenom, COUNT(reg.id) AS nb_inscriptions
                 FROM " . esc_sql($parents) . " p
                 INNER JOIN " . esc_sql($registrations) . " reg ON reg.parent_id = p.id
                 INNER JOIN " . esc_sql($councils) . " co
                        ON co.id = reg.council_id AND co.year_id = %d AND co.term_id = %d
                 GROUP BY p.id, p.nom, p.prenom
                 ORDER BY nb_inscriptions DESC
                 LIMIT %d",
                $yearId, $termId, $limit
            ),
            ARRAY_A
        );
    }

    // ── Statistiques : conseils sans CR (date passée) ─────────────────────────

    public static function list_pending_councils(int $yearId, int $termId): array {
        global $wpdb;
        $classes  = CC_DB::table('classes');
        $councils = CC_DB::table('councils');
        $reports  = CC_DB::table('reports');
        $today    = current_time('Y-m-d');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT cl.nom AS class_nom, cl.niveau, co.date_conseil, co.salle_conseil
                 FROM " . esc_sql($councils) . " co
                 INNER JOIN " . esc_sql($classes) . " cl ON cl.id = co.class_id
                 LEFT JOIN " . esc_sql($reports) . " r ON r.council_id = co.id
                 WHERE co.year_id = %d AND co.term_id = %d
                   AND co.date_conseil < %s
                   AND r.id IS NULL
                 ORDER BY co.date_conseil ASC",
                $yearId, $termId, $today
            ),
            ARRAY_A
        );
    }

    // ── Statistiques : implication parents par trimestre (toute l'année) ──────

    public static function list_parent_engagement_by_term(int $yearId, int $maxParentsPerCouncil): array {
        global $wpdb;
        $councils      = CC_DB::table('councils');
        $registrations = CC_DB::table('registrations');
        $reports       = CC_DB::table('reports');
        $terms         = CC_DB::table('terms');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $rows = (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT t.id AS term_id,
                        t.nom AS term_nom,
                        COUNT(DISTINCT co.id)  AS nb_councils,
                        COUNT(DISTINCT reg.id) AS nb_inscriptions,
                        COUNT(DISTINCT r.id)   AS nb_reports,
                        SUM(CASE WHEN co.date_conseil < CURDATE() AND r.id IS NULL THEN 1 ELSE 0 END) AS nb_pending
                 FROM " . esc_sql($terms) . " t
                 INNER JOIN " . esc_sql($councils) . " co
                        ON co.term_id = t.id AND co.year_id = %d
                 LEFT JOIN " . esc_sql($registrations) . " reg ON reg.council_id = co.id
                 LEFT JOIN " . esc_sql($reports) . " r ON r.council_id = co.id
                 GROUP BY t.id, t.nom
                 ORDER BY t.id ASC",
                $yearId
            ),
            ARRAY_A
        );
        foreach ($rows as &$row) {
            $row['capacity'] = (int) $row['nb_councils'] * $maxParentsPerCouncil;
        }
        unset($row);
        return $rows;
    }

    /** Liste toutes les inscriptions d'une année + trimestre (pour export CSV). */
    public static function list_registrations_for_year_term(int $yearId, int $termId): array {
        global $wpdb;
        $reg      = CC_DB::table('registrations');
        $parents  = CC_DB::table('parents');
        $councils = CC_DB::table('councils');
        $classes  = CC_DB::table('classes');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        return (array) $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, p.nom AS parent_nom, p.prenom AS parent_prenom, p.email AS parent_email,
                        co.date_conseil, cl.nom AS classe_nom
                 FROM " . esc_sql($reg) . " r
                 JOIN " . esc_sql($parents) . " p ON p.id = r.parent_id
                 JOIN " . esc_sql($councils) . " co ON co.id = r.council_id
                 JOIN " . esc_sql($classes) . " cl ON cl.id = co.class_id
                 WHERE co.year_id = %d AND co.term_id = %d
                 ORDER BY cl.nom ASC, co.date_conseil ASC, p.nom ASC",
                $yearId,
                $termId
            ),
            ARRAY_A
        );
    }

    /** Trouve un conseil par nom de classe et date (pour import). */
    public static function get_council_by_class_date(int $yearId, int $termId, string $classeNom, string $dateConseil): ?array {
        global $wpdb;
        $councils = CC_DB::table('councils');
        $classes  = CC_DB::table('classes');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table names from safe source.
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT co.*
                 FROM " . esc_sql($councils) . " co
                 JOIN " . esc_sql($classes) . " cl ON cl.id = co.class_id
                 WHERE co.year_id = %d AND co.term_id = %d
                   AND cl.nom = %s AND co.date_conseil = %s
                 LIMIT 1",
                $yearId,
                $termId,
                $classeNom,
                $dateConseil
            ),
            ARRAY_A
        );
        return is_array($row) ? $row : null;
    }

    /** Récupère le compte-rendu d'un conseil (un seul CR par conseil). */
    public static function get_report_by_council(int $councilId): ?array {
        global $wpdb;
        $table = CC_DB::table('reports');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM " . esc_sql($table) . " WHERE council_id = %d LIMIT 1", $councilId),
            ARRAY_A
        );
        return is_array($row) ? $row : null;
    }

    /** Insère ou met à jour une inscription (pour import CSV). */
    public static function upsert_registration(int $councilId, int $parentId, int $presente, string $commentaire): void {
        global $wpdb;
        $table = CC_DB::table('registrations');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $existing = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM " . esc_sql($table) . " WHERE council_id = %d AND parent_id = %d LIMIT 1", $councilId, $parentId)
        );
        if ($existing) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional update.
            $wpdb->update(
                $table,
                ['presente' => $presente, 'commentaire' => $commentaire],
                ['council_id' => $councilId, 'parent_id' => $parentId],
                ['%d', '%s'],
                ['%d', '%d']
            );
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- intentional insert.
            $wpdb->insert($table, [
                'parent_id'        => $parentId,
                'council_id'       => $councilId,
                'date_inscription' => CC_Utils::mysql_now(),
                'presente'         => $presente,
                'commentaire'      => $commentaire !== '' ? $commentaire : null,
            ], ['%d', '%d', '%s', '%d', '%s']);
        }
    }
}

