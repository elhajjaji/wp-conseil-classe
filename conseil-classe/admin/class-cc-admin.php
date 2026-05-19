<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CC_Admin {
    // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_operations_fclose
    public function init(): void {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('admin_body_class', [$this, 'filter_admin_body_class']);

        add_action('admin_post_cc_save_settings', [$this, 'handle_save_settings']);
        add_action('admin_post_cc_year_create', [$this, 'handle_year_create']);
        add_action('admin_post_cc_year_delete', [$this, 'handle_year_delete']);
        add_action('admin_post_cc_year_set_active', [$this, 'handle_year_set_active']);

        add_action('admin_post_cc_term_set_active', [$this, 'handle_term_set_active']);

        add_action('admin_post_cc_class_create', [$this, 'handle_class_create']);
        add_action('admin_post_cc_class_delete', [$this, 'handle_class_delete']);
        add_action('admin_post_cc_classes_bulk_delete', [$this, 'handle_classes_bulk_delete']);

        add_action('admin_post_cc_council_create', [$this, 'handle_council_create']);
        add_action('admin_post_cc_council_delete', [$this, 'handle_council_delete']);
        add_action('admin_post_cc_councils_bulk_delete', [$this, 'handle_councils_bulk_delete']);

        add_action('admin_post_cc_parent_create', [$this, 'handle_parent_create']);
        add_action('admin_post_cc_parent_delete', [$this, 'handle_parent_delete']);
        add_action('admin_post_cc_parents_bulk_delete', [$this, 'handle_parents_bulk_delete']);
        add_action('admin_post_cc_parent_regenerate_code', [$this, 'handle_parent_regenerate_code']);
        add_action('admin_post_cc_parents_export_csv', [$this, 'handle_parents_export_csv']);
        add_action('admin_post_cc_parents_import_csv', [$this, 'handle_parents_import_csv']);
        add_action('admin_post_cc_parents_import_admins_csv', [$this, 'handle_parents_import_admins_csv']);
        add_action('admin_post_cc_parents_download_template', [$this, 'handle_parents_download_template']);
        add_action('admin_post_cc_parents_download_template_admins', [$this, 'handle_parents_download_template_admins']);
        add_action('admin_post_cc_reports_export_csv', [$this, 'handle_reports_export_csv']);
        add_action('admin_post_cc_reports_import_csv', [$this, 'handle_reports_import_csv']);
        add_action('admin_post_cc_reports_download_template', [$this, 'handle_reports_download_template']);

        add_action('admin_post_cc_registration_unregister', [$this, 'handle_registration_unregister']);
    add_action('admin_post_cc_registrations_bulk_delete', [$this, 'handle_registrations_bulk_delete']);
        add_action('admin_post_cc_registrations_export_csv', [$this, 'handle_registrations_export_csv']);
        add_action('admin_post_cc_registrations_import_csv', [$this, 'handle_registrations_import_csv']);
        add_action('admin_post_cc_registrations_download_template', [$this, 'handle_registrations_download_template']);

        add_action('admin_post_cc_report_toggle_validation', [$this, 'handle_report_toggle_validation']);
        add_action('admin_post_cc_report_update', [$this, 'handle_report_update']);
        add_action('admin_post_cc_report_export', [$this, 'handle_report_export']);
        add_action('admin_post_cc_reports_bulk_delete', [$this, 'handle_reports_bulk_delete']);

        add_action('admin_post_cc_pdf_template_save', [$this, 'handle_pdf_template_save']);
        add_action('admin_post_cc_pdf_template_activate', [$this, 'handle_pdf_template_activate']);
        add_action('admin_post_cc_pdf_template_delete', [$this, 'handle_pdf_template_delete']);

        // Imports/exports en masse
        add_action('admin_post_cc_classes_export_csv', [$this, 'handle_classes_export_csv']);
        add_action('admin_post_cc_classes_import_csv', [$this, 'handle_classes_import_csv']);
        add_action('admin_post_cc_classes_download_template', [$this, 'handle_classes_download_template']);

        add_action('admin_post_cc_councils_export_csv', [$this, 'handle_councils_export_csv']);
        add_action('admin_post_cc_councils_import_csv', [$this, 'handle_councils_import_csv']);
        add_action('admin_post_cc_councils_download_template', [$this, 'handle_councils_download_template']);

        add_action('admin_post_cc_settings_export_csv', [$this, 'handle_settings_export_csv']);
        add_action('admin_post_cc_settings_import_csv', [$this, 'handle_settings_import_csv']);
        add_action('admin_post_cc_settings_download_template', [$this, 'handle_settings_download_template']);
        add_action('admin_post_cc_term_export_excel', [$this, 'handle_term_export_excel']);
        add_action('admin_post_cc_term_import_excel', [$this, 'handle_term_import_excel']);
        add_action('admin_post_cc_setup_front_pages', [$this, 'handle_setup_front_pages']);
    }

    public function enqueue_admin_assets(string $hook): void {
        $hook = (string) $hook;
        $our = ($hook === 'toplevel_page_cc_dashboard') || (strpos($hook, '_page_cc_') !== false);
        if (!$our) {
            return;
        }
        wp_enqueue_style(
            'cc-admin',
            CC_PLUGIN_URL . 'assets/admin.css',
            [],
            CC_PLUGIN_VERSION
        );
        // Chart.js bundlé localement (pas de CDN tiers)
        $needsCharts = in_array($hook, ['toplevel_page_cc_dashboard', 'conseil-de-classe_page_cc_statistics'], true);
        if ($needsCharts) {
            wp_enqueue_script(
                'cc-chartjs',
                CC_PLUGIN_URL . 'assets/chart.min.js',
                [],
                '4.4.4',
                true
            );
        }
        if ($hook === 'toplevel_page_cc_dashboard') {
            wp_enqueue_script(
                'cc-admin-charts',
                CC_PLUGIN_URL . 'assets/admin-charts.js',
                ['cc-chartjs'],
                CC_PLUGIN_VERSION,
                true
            );
        }
        if ($hook === 'conseil-de-classe_page_cc_statistics') {
            wp_enqueue_script(
                'cc-admin-stats',
                CC_PLUGIN_URL . 'assets/admin-stats.js',
                ['cc-chartjs'],
                CC_PLUGIN_VERSION,
                true
            );
        }
    }

    public function filter_admin_body_class(string $classes): string {
        if (!function_exists('get_current_screen')) {
            return $classes;
        }
        $s = get_current_screen();
        if (!$s || !isset($s->id)) {
            return $classes;
        }
        $id = (string) $s->id;
        if (($id === 'toplevel_page_cc_dashboard') || (strpos($id, 'cc_dashboard') !== false) || (strpos($id, 'page_cc_') !== false)) {
            return $classes . ' cc-admin-conseil';
        }

        return $classes;
    }

    public function register_menu(): void {
        add_menu_page(
            __('Conseil de classe', 'conseil-classe'),
            __('Conseil de classe', 'conseil-classe'),
            CC_Roles::CAP_MANAGE,
            'cc_dashboard',
            [$this, 'render_dashboard'],
            'dashicons-welcome-learn-more',
            26
        );

        add_submenu_page('cc_dashboard', __('Paramètres', 'conseil-classe'), __('Paramètres', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_settings', [$this, 'render_settings']);
        add_submenu_page('cc_dashboard', __('Années scolaires', 'conseil-classe'), __('Années scolaires', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_years', [$this, 'render_years']);
        add_submenu_page('cc_dashboard', __('Trimestres', 'conseil-classe'), __('Trimestres', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_terms', [$this, 'render_terms']);
        add_submenu_page('cc_dashboard', __('Classes', 'conseil-classe'), __('Classes', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_classes', [$this, 'render_classes']);
        add_submenu_page('cc_dashboard', __('Conseils', 'conseil-classe'), __('Conseils', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_councils', [$this, 'render_councils']);
        add_submenu_page('cc_dashboard', __('Parents', 'conseil-classe'), __('Parents', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_parents', [$this, 'render_parents']);
        add_submenu_page('cc_dashboard', __('Inscriptions', 'conseil-classe'), __('Inscriptions', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_registrations', [$this, 'render_registrations']);
        add_submenu_page('cc_dashboard', __('Comptes-rendus', 'conseil-classe'), __('Comptes-rendus', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_reports', [$this, 'render_reports']);
        add_submenu_page('cc_dashboard', __('Statistiques', 'conseil-classe'), __('Statistiques', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_statistics', [$this, 'render_statistics']);
        add_submenu_page('cc_dashboard', __('Templates PDF', 'conseil-classe'), __('Templates PDF', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_pdf_templates', [$this, 'render_pdf_templates']);
        add_submenu_page('cc_dashboard', __('Logs', 'conseil-classe'), __('Logs', 'conseil-classe'), CC_Roles::CAP_MANAGE, 'cc_logs', [$this, 'render_logs']);
    }

    private function require_manage(): void {
        if (!CC_Roles::user_can_manage_plugin()) {
            wp_die(esc_html__('Accès refusé.', 'conseil-classe'));
        }
    }

    private function request_scalar(array $source, string $key, string $default = ''): string {
        if (!isset($source[$key])) {
            return $default;
        }
        $value = wp_unslash($source[$key]);
        if (!is_scalar($value)) {
            return $default;
        }

        return (string) $value;
    }

    private function post_scalar(string $key, string $default = ''): string {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified in each action handler before use.
        return $this->request_scalar($_POST, $key, $default);
    }

    private function get_scalar(string $key, string $default = ''): string {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only query params for admin views.
        return $this->request_scalar($_GET, $key, $default);
    }

    private function request_scalar_value(string $key, string $default = ''): string {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- source-specific nonce checks are performed in handlers.
        return $this->request_scalar($_REQUEST, $key, $default);
    }

    private function uploaded_tmp_path(string $field): string {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- handlers verify nonce before calling this helper.
        if (empty($_FILES[$field]) || !is_array($_FILES[$field]) || empty($_FILES[$field]['tmp_name'])) {
            return '';
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- handlers verify nonce before calling this helper; value is unslashed and sanitized before use.
        return sanitize_text_field((string) wp_unslash($_FILES[$field]['tmp_name']));
    }

    /**
     * @param string|null $roleSlugApply null : ne pas modifier le rôle (import fichier « parents »).
     * @param string|null $passwordPlain  null ou '' : ne pas modifier le mot de passe (compte existant).
     *
     * @return int|\WP_Error
     */
    private function create_or_attach_wp_parent_user(
        string $email,
        string $prenom,
        string $nom,
        ?string $roleSlugApply,
        ?string $passwordPlain,
        bool $sendWelcomeEmail = false
    ) {
        $existId = email_exists($email);
        if ($existId) {
            $userId = (int) $existId;
            wp_update_user([
                'ID' => $userId,
                'first_name' => $prenom,
                'last_name' => $nom,
                'display_name' => trim($prenom . ' ' . $nom),
            ]);
            $this->sync_wp_existing_user_conseil($userId, $passwordPlain, $roleSlugApply);

            return $userId;
        }

        $pass = ($passwordPlain !== null && $passwordPlain !== '')
            ? $passwordPlain
            : CC_Utils::generate_access_code([CC_Repo::class, 'access_code_exists']);
        $roleForNew = ($roleSlugApply !== null && $roleSlugApply !== '') ? $roleSlugApply : CC_Roles::ROLE_PARENT;

        $parts = explode('@', $email, 2);
        $localPart = strtolower(sanitize_user((string) ($parts[0] ?? 'parent'), true));
        if ($localPart === '') {
            $localPart = 'parent';
        }
        $loginBase = substr($localPart, 0, 40);
        $login = $loginBase;
        $suffix = 1;
        while (username_exists($login)) {
            $login = $loginBase . $suffix++;
        }

        $userId = wp_insert_user([
            'user_login' => $login,
            'user_pass' => $pass,
            'user_email' => $email,
            'first_name' => $prenom,
            'last_name' => $nom,
            'display_name' => trim($prenom . ' ' . $nom),
            'role' => $roleForNew,
        ]);
        if (is_wp_error($userId)) {
            return $userId;
        }
        if ($sendWelcomeEmail) {
            if (function_exists('wp_send_new_user_notifications')) {
                wp_send_new_user_notifications((int) $userId, 'both');
            } elseif (function_exists('wp_new_user_notification')) {
                wp_new_user_notification((int) $userId, null, 'both');
            }
        }

        return (int) $userId;
    }

    /** Compte WP existant : mot de passe et/ou rôle conseil (sécurités sur admin WP / super conseil). */
    private function sync_wp_existing_user_conseil(int $userId, ?string $passwordPlain, ?string $newRoleSlug): void {
        if ($passwordPlain !== null && $passwordPlain !== '' && !user_can($userId, 'manage_options')) {
            wp_set_password($passwordPlain, $userId);
        }
        if ($newRoleSlug === null || $newRoleSlug === '') {
            return;
        }
        if (user_can($userId, 'manage_options')) {
            return;
        }
        if (user_can($userId, CC_Roles::CAP_SUPER) && $newRoleSlug !== CC_Roles::ROLE_SUPER) {
            return;
        }
        $user = get_userdata($userId);
        if (!$user) {
            return;
        }
        $user->set_role($newRoleSlug);
    }

    /** @return string|false rôle slug ou false si valeur interdite / droits insuffisants pour super */
    private function parse_admin_import_role_slug(string $cell) {
        $t = strtolower(trim($cell));
        if ($t === '' || $t === 'admin' || $t === 'admin_conseil' || $t === CC_Roles::ROLE_ADMIN || $t === 'gestion') {
            return CC_Roles::ROLE_ADMIN;
        }
        $isSuperMarker = (
            strpos($t, 'super') !== false
            || $t === CC_Roles::ROLE_SUPER
            || strpos($t, 'cc_conseil_super') !== false
        );
        if ($isSuperMarker) {
            if (!CC_Roles::user_can_super()) {
                return false;
            }

            return CC_Roles::ROLE_SUPER;
        }

        return false;
    }

    /**
     * @return 'created'|'updated'|'error'
     */
    private function upsert_parent_from_csv(array $row, string $mode): string {
        $nom = sanitize_text_field((string) ($row[0] ?? ''));
        $prenom = sanitize_text_field((string) ($row[1] ?? ''));
        $email = CC_Utils::normalize_email((string) ($row[2] ?? ''));
        $telephone = sanitize_text_field((string) ($row[3] ?? ''));
        $codeRaw = trim(sanitize_text_field((string) ($row[4] ?? '')));

        if ($nom === '' || $prenom === '' || $email === '') {
            return 'error';
        }

        $roleSlugApply = null;
        if ($mode === 'admin') {
            $parsed = $this->parse_admin_import_role_slug((string) ($row[5] ?? ''));
            if ($parsed === false) {
                return 'error';
            }
            $roleSlugApply = $parsed;
        }

        $existing = CC_Repo::get_parent_by_email($email);

        $pwdApply = null;
        if ($codeRaw !== '') {
            if (strlen($codeRaw) < 6) {
                return 'error';
            }
            $exceptId = $existing ? (int) $existing['id'] : null;
            if (CC_Repo::access_code_exists($codeRaw, $exceptId)) {
                return 'error';
            }
            $pwdApply = $codeRaw;
        }

        $roleForAttach = ($mode === 'parent') ? null : $roleSlugApply;
        $telephoneVal = $telephone !== '' ? $telephone : null;

        if (!$existing) {
            $finalCode = ($pwdApply !== null) ? $pwdApply : CC_Utils::generate_access_code([CC_Repo::class, 'access_code_exists']);
            $wpRes = $this->create_or_attach_wp_parent_user($email, $prenom, $nom, $roleForAttach, $finalCode, false);
            if (is_wp_error($wpRes)) {
                return 'error';
            }
            CC_Repo::create_parent([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephoneVal,
                'code_acces' => $finalCode,
                'wp_user_id' => (int) $wpRes,
            ]);

            return 'created';
        }

        $year = CC_Repo::get_active_year();
        if ($year && !CC_Repo::is_parent_assigned_to_year((int) $existing['id'], (int) $year['id'])) {
            CC_Repo::assign_parent_to_year((int) $existing['id'], (int) $year['id']);
        }

        $widRow = (int) ($existing['wp_user_id'] ?? 0);
        $upd = [
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephoneVal,
        ];

        $wpPassForAttach = null;
        if ($widRow > 0) {
            $wpPassForAttach = $pwdApply;
            if ($pwdApply !== null && $pwdApply !== '') {
                $upd['code_acces'] = $pwdApply;
            }
        } else {
            $wpPassForAttach = ($pwdApply !== null && $pwdApply !== '')
                ? $pwdApply
                : (trim((string) ($existing['code_acces'] ?? '')) ?: CC_Utils::generate_access_code([CC_Repo::class, 'access_code_exists']));
            $upd['code_acces'] = $wpPassForAttach;
        }

        $passArg = (($widRow > 0 && $pwdApply === null)) ? null : $wpPassForAttach;

        $wpRes = $this->create_or_attach_wp_parent_user($email, $prenom, $nom, $roleForAttach, $passArg, false);
        if (is_wp_error($wpRes)) {
            return 'error';
        }
        $upd['wp_user_id'] = (int) $wpRes;
        CC_Repo::update_parent((int) $existing['id'], $upd);

        return 'updated';
    }

    /**
     * Code d’accès = mot de passe WP (généré si vide). Longueur min. 6 caractères si saisi.
     *
     * @return string|\WP_Error
     */
    private function resolve_parent_password_plain(string $codeInput) {
        $raw = trim(sanitize_text_field($codeInput));
        if ($raw === '') {
            return CC_Utils::generate_access_code([CC_Repo::class, 'access_code_exists']);
        }
        if (strlen($raw) < 6) {
            return new WP_Error('cc_pw_short', __('Le mot de passe / code doit contenir au moins 6 caractères.', 'conseil-classe'));
        }
        if (strlen($raw) > 160) {
            return new WP_Error('cc_pw_long', __('Mot de passe trop long.', 'conseil-classe'));
        }

        return $raw;
    }

    private function format_wp_user_roles(int $userId): string {
        $user = get_userdata($userId);
        if (!$user || empty($user->roles)) {
            return '—';
        }
        $glob = wp_roles();
        $labels = [];
        foreach ((array) $user->roles as $slug) {
            $name = isset($glob->roles[$slug]['name']) ? translate_user_role($glob->roles[$slug]['name']) : $slug;
            $labels[] = $name;
        }

        return implode(', ', $labels);
    }

    /** Administrateurs WP ou comptes avec droit de gestion du plugin conseil : ne pas exposer le mot de passe dans la liste. */
    private function is_parent_wp_account_privileged(int $wpUserId): bool {
        if ($wpUserId <= 0) {
            return false;
        }
        if (user_can($wpUserId, 'manage_options')) {
            return true;
        }

        return CC_Roles::user_can_manage_plugin($wpUserId);
    }

    private function redirect_admin(string $page, array $args = []): void {
        $url = add_query_arg(array_merge(['page' => $page], $args), admin_url('admin.php'));
        wp_safe_redirect($url);
        exit;
    }

    /**
     * @return array<string,array{title:string,slug:string,shortcode:string,label:string}>
     */
    private function get_front_page_definitions(): array {
        return [
            'cc_parent_login_page_id' => [
                'title' => __('Connexion / compte parent', 'conseil-classe'),
                'slug' => 'connexion-compte-parent',
                'shortcode' => '[cc_parent_login]',
                'label' => __('Connexion / compte', 'conseil-classe'),
            ],
            'cc_plannings_page_id' => [
                'title' => __('Planning des conseils', 'conseil-classe'),
                'slug' => 'planning-conseils-classe',
                'shortcode' => '[cc_plannings]',
                'label' => __('Planning', 'conseil-classe'),
            ],
            'cc_my_councils_page_id' => [
                'title' => __('Mes conseils', 'conseil-classe'),
                'slug' => 'mes-conseils',
                'shortcode' => '[cc_my_councils]',
                'label' => __('Mes conseils', 'conseil-classe'),
            ],
            'cc_report_form_page_id' => [
                'title' => __('Formulaire compte-rendu', 'conseil-classe'),
                'slug' => 'formulaire-compte-rendu',
                'shortcode' => '[cc_report_form]',
                'label' => __('Formulaire compte-rendu', 'conseil-classe'),
            ],
        ];
    }

    private function is_valid_front_page_id(int $pageId): bool {
        if ($pageId <= 0) {
            return false;
        }

        $page = get_post($pageId);
        if (!$page instanceof WP_Post) {
            return false;
        }

        return $page->post_type === 'page' && $page->post_status !== 'trash';
    }

    /**
     * @return array<string,array{title:string,slug:string,shortcode:string,label:string,current_id:int}>
     */
    private function get_missing_front_pages(): array {
        $missing = [];
        foreach ($this->get_front_page_definitions() as $option => $definition) {
            $pageId = (int) get_option($option, 0);
            if ($this->is_valid_front_page_id($pageId)) {
                continue;
            }

            $definition['current_id'] = $pageId;
            $missing[$option] = $definition;
        }

        return $missing;
    }

    private function render_front_pages_setup_notice(bool $compact = false): void {
        $missing = $this->get_missing_front_pages();
        $setupDone = $this->get_scalar('front_pages_setup') === '1';
        $confirmSetup = $this->get_scalar('front_pages_confirm') === '1';
        $created = max(0, (int) $this->get_scalar('front_pages_created', '0'));
        $attached = max(0, (int) $this->get_scalar('front_pages_attached', '0'));

        if ($setupDone) {
            echo '<div class="notice notice-success"><p>' . esc_html(sprintf(
                __('Pages front configurées: %1$d créée(s), %2$d associée(s).', 'conseil-classe'),
                $created,
                $attached
            )) . '</p></div>';
        }

        if (!$missing) {
            return;
        }

        echo '<div class="notice notice-warning"><p><strong>' . esc_html__('Pages front manquantes', 'conseil-classe') . '</strong><br />';
        echo esc_html__('Certaines pages publiques ne sont pas encore créées ou associées. Le plugin peut proposer et rattacher automatiquement les 4 pages standard.', 'conseil-classe');
        echo '</p><ul style="list-style:disc; padding-left:18px; margin-top:8px;">';
        foreach ($missing as $definition) {
            echo '<li>' . esc_html($definition['label'] . ' -> ' . $definition['title'] . ' (' . $definition['shortcode'] . ')') . '</li>';
        }
        echo '</ul><p>';
        if ($compact) {
            echo '<a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=cc_settings&front_pages_confirm=1#cc-settings-pages')) . '">' . esc_html__('Proposer la création des pages', 'conseil-classe') . '</a>';
            echo ' <a class="button" href="' . esc_url(admin_url('admin.php?page=cc_settings#cc-settings-pages')) . '">' . esc_html__('Vérifier dans Paramètres', 'conseil-classe') . '</a>';
            echo '</p></div>';

            return;
        }

        if ($confirmSetup) {
            echo esc_html__('Confirmez la création automatique des pages ci-dessus. Les pages déjà existantes avec le bon slug seront simplement rattachées.', 'conseil-classe');
            echo '</p><form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-top:12px;">';
            wp_nonce_field('cc_setup_front_pages');
            echo '<input type="hidden" name="action" value="cc_setup_front_pages" />';
            submit_button(__('Oui, créer et associer ces pages', 'conseil-classe'), 'primary', 'submit', false);
            echo ' <a class="button" href="' . esc_url(admin_url('admin.php?page=cc_settings#cc-settings-pages')) . '">' . esc_html__('Annuler', 'conseil-classe') . '</a>';
            echo '</form></div>';

            return;
        }

        echo '<a class="button button-primary" href="' . esc_url(admin_url('admin.php?page=cc_settings&front_pages_confirm=1#cc-settings-pages')) . '">' . esc_html__('Proposer la création de ces pages', 'conseil-classe') . '</a>';
        echo '</p></div>';
    }

    public function handle_setup_front_pages(): void {
        $this->require_manage();
        check_admin_referer('cc_setup_front_pages');

        $created = 0;
        $attached = 0;

        foreach ($this->get_missing_front_pages() as $option => $definition) {
            $pageId = 0;
            $existing = get_page_by_path($definition['slug'], OBJECT, 'page');
            if ($existing instanceof WP_Post) {
                $pageId = (int) $existing->ID;
                $attached++;
            } else {
                $inserted = wp_insert_post([
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'post_title' => $definition['title'],
                    'post_name' => $definition['slug'],
                    'post_content' => $definition['shortcode'],
                ], true);

                if (is_wp_error($inserted)) {
                    continue;
                }

                $pageId = (int) $inserted;
                $created++;
            }

            if ($pageId > 0) {
                update_option($option, $pageId);
            }
        }

        $this->redirect_admin('cc_settings', [
            'front_pages_setup' => 1,
            'front_pages_created' => $created,
            'front_pages_attached' => $attached,
        ]);
    }

    // =========================
    // Helpers CSV (Excel-friendly)
    // - Export: UTF-8 BOM + séparateur ';'
    // - Import: détecte ',' ou ';' depuis la première ligne
    // =========================
    private function csv_send_headers(string $filename): void {
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        // BOM UTF-8 pour Excel
        echo "\xEF\xBB\xBF";
    }

    private function csv_fputcsv($handle, array $fields, string $delimiter = ';'): void {
        fputcsv($handle, $fields, $delimiter);
    }

    private function csv_detect_delimiter(string $line): string {
        $comma = substr_count($line, ',');
        $semi = substr_count($line, ';');
        return ($semi > $comma) ? ';' : ',';
    }

    private function csv_open_import_handle(string $tmpPath, ?string &$delimiterOut = null) {
        $handle = fopen($tmpPath, 'r');
        if (!$handle) {
            return false;
        }
        $pos = ftell($handle);
        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fseek($handle, $pos);
            $delimiterOut = ',';
            return $handle;
        }
        $delimiterOut = $this->csv_detect_delimiter($firstLine);
        // reset to beginning
        rewind($handle);
        return $handle;
    }

    private function excel_send_headers(string $filename): void {
        nocache_headers();
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
    }

    private function excel_xml_escape(string $value): string {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    /**
     * @param array<int,string> $headers
     * @param array<int,array<int,string|int|null>> $rows
     */
    private function excel_render_worksheet(string $sheetName, array $headers, array $rows): string {
        $xml = '<Worksheet ss:Name="' . $this->excel_xml_escape($sheetName) . '"><Table>';
        $xml .= '<Row>';
        foreach ($headers as $header) {
            $xml .= '<Cell><Data ss:Type="String">' . $this->excel_xml_escape((string) $header) . '</Data></Cell>';
        }
        $xml .= '</Row>';
        foreach ($rows as $row) {
            $xml .= '<Row>';
            foreach ($row as $cell) {
                $xml .= '<Cell><Data ss:Type="String">' . $this->excel_xml_escape((string) ($cell ?? '')) . '</Data></Cell>';
            }
            $xml .= '</Row>';
        }
        $xml .= '</Table></Worksheet>';

        return $xml;
    }

    /**
     * @param array<string,array{headers:array<int,string>,rows:array<int,array<int,string|int|null>>}> $worksheets
     */
    private function excel_render_workbook(array $worksheets): string {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?mso-application progid="Excel.Sheet"?>';
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
        $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"';
        $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
        $xml .= '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">';
        $xml .= '<Author>Conseil de classe</Author>';
        $xml .= '<Created>' . gmdate('Y-m-d\TH:i:s\Z') . '</Created>';
        $xml .= '</DocumentProperties>';
        $xml .= '<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel"><ProtectStructure>False</ProtectStructure><ProtectWindows>False</ProtectWindows></ExcelWorkbook>';
        foreach ($worksheets as $sheetName => $sheet) {
            $xml .= $this->excel_render_worksheet($sheetName, $sheet['headers'], $sheet['rows']);
        }
        $xml .= '</Workbook>';

        return $xml;
    }

    /**
     * @return array<string,array{headers:array<int,string>,rows:array<int,array<string,string>>}>|null
     */
    private function excel_parse_workbook(string $tmpPath): ?array {
        if (!function_exists('simplexml_load_file')) {
            return null;
        }
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($tmpPath);
        if (!$xml) {
            libxml_clear_errors();
            return null;
        }
        $ns = 'urn:schemas-microsoft-com:office:spreadsheet';
        $sheets = [];
        $workbookChildren = $xml->children($ns);
        foreach ($workbookChildren->Worksheet as $worksheet) {
            $attrs = $worksheet->attributes($ns);
            $sheetName = isset($attrs['Name']) ? (string) $attrs['Name'] : '';
            if ($sheetName === '') {
                continue;
            }
            $rows = [];
            $headers = [];
            $table = $worksheet->children($ns)->Table;
            if (!$table) {
                continue;
            }
            foreach ($table->children($ns)->Row as $row) {
                $values = [];
                $index = 1;
                foreach ($row->children($ns)->Cell as $cell) {
                    $cellAttrs = $cell->attributes($ns);
                    if (isset($cellAttrs['Index'])) {
                        $index = (int) $cellAttrs['Index'];
                    }
                    $data = '';
                    $dataNode = $cell->children($ns)->Data;
                    if ($dataNode) {
                        $data = (string) $dataNode;
                    }
                    $values[$index - 1] = $data;
                    $index++;
                }
                if (!$headers) {
                    ksort($values);
                    $headers = array_values($values);
                    continue;
                }
                $assoc = [];
                foreach ($headers as $i => $header) {
                    $assoc[(string) $header] = (string) ($values[$i] ?? '');
                }
                $rows[] = $assoc;
            }
            $sheets[$sheetName] = [
                'headers' => $headers,
                'rows' => $rows,
            ];
        }

        return $sheets;
    }

    /**
     * @param array<int,array<string,mixed>> $councils
     * @param array<int,array<string,mixed>> $registrations
     * @param array<int,array<string,mixed>> $reports
     *
     * @return array<int,array<string,mixed>>
     */
    private function collect_term_parents(array $councils, array $registrations, array $reports): array {
        $parentsByEmail = [];
        $allParents = CC_Repo::list_parents('', 'parent');
        foreach ($allParents as $parent) {
            $email = CC_Utils::normalize_email((string) ($parent['email'] ?? ''));
            if ($email === '') {
                continue;
            }
            $wid = (int) ($parent['wp_user_id'] ?? 0);
            $pwdOut = '';
            if ($wid <= 0 || !$this->is_parent_wp_account_privileged($wid)) {
                $pwdOut = (string) ($parent['code_acces'] ?? '');
            }
            $parentsByEmail[$email] = [
                'nom' => (string) ($parent['nom'] ?? ''),
                'prenom' => (string) ($parent['prenom'] ?? ''),
                'email' => $email,
                'telephone' => (string) ($parent['telephone'] ?? ''),
                'code_acces' => $pwdOut,
            ];
        }
        foreach ($registrations as $registration) {
            $email = CC_Utils::normalize_email((string) ($registration['parent_email'] ?? ''));
            if ($email === '') {
                continue;
            }
            if (!isset($parentsByEmail[$email])) {
                $parentsByEmail[$email] = [
                    'nom' => (string) ($registration['parent_nom'] ?? ''),
                    'prenom' => (string) ($registration['parent_prenom'] ?? ''),
                    'email' => $email,
                    'telephone' => '',
                    'code_acces' => '',
                ];
            }
            $parent = CC_Repo::get_parent_by_email($email);
            if ($parent) {
                $parentsByEmail[$email]['telephone'] = (string) ($parent['telephone'] ?? '');
                $wid = (int) ($parent['wp_user_id'] ?? 0);
                if ($wid <= 0 || !$this->is_parent_wp_account_privileged($wid)) {
                    $parentsByEmail[$email]['code_acces'] = (string) ($parent['code_acces'] ?? '');
                }
            }
        }
        foreach ($reports as $report) {
            $email = CC_Utils::normalize_email((string) ($report['email_parent'] ?? ''));
            if ($email === '') {
                continue;
            }
            if (!isset($parentsByEmail[$email])) {
                $parentsByEmail[$email] = [
                    'nom' => (string) ($report['nom_parent'] ?? ''),
                    'prenom' => (string) ($report['prenom_parent'] ?? ''),
                    'email' => $email,
                    'telephone' => '',
                    'code_acces' => '',
                ];
            }
            $parent = CC_Repo::get_parent_by_email($email);
            if ($parent) {
                $parentsByEmail[$email]['telephone'] = (string) ($parent['telephone'] ?? '');
                $parentsByEmail[$email]['code_acces'] = (string) ($parent['code_acces'] ?? '');
            }
        }
        ksort($parentsByEmail);

        return array_values($parentsByEmail);
    }

    /**
     * @param array<int,array<string,mixed>> $councils
     *
     * @return array<int,array<string,string>>
     */
    private function collect_term_classes(array $councils): array {
        $classes = [];
        foreach ($councils as $council) {
            $key = (string) ($council['classe_nom'] ?? '');
            if ($key === '') {
                continue;
            }
            $classes[$key] = [
                'nom' => (string) ($council['classe_nom'] ?? ''),
                'niveau' => (string) ($council['classe_niveau'] ?? ''),
            ];
        }
        ksort($classes);

        return array_values($classes);
    }

    /**
     * @param array<int,array<string,string>> $rows
     *
     * @return array<string,string>
     */
    private function term_meta_to_assoc(array $rows): array {
        $meta = [];
        foreach ($rows as $row) {
            $key = sanitize_key((string) ($row['key'] ?? ''));
            if ($key === '') {
                continue;
            }
            $meta[$key] = (string) ($row['value'] ?? '');
        }

        return $meta;
    }

    private function normalize_term_bundle_label(string $value): string {
        $value = strtolower(trim($value));
        $value = preg_replace('/[\s_\/\\\\]+/', '-', $value);
        $value = preg_replace('/-+/', '-', (string) $value);

        return trim((string) $value, '-');
    }

        /**
         * @return array<int,int>
         */
        private function post_ids_array(string $key): array {
                $raw = $_POST[$key] ?? [];
                if (!is_array($raw)) {
                        return [];
                }

                $ids = array_map('intval', $raw);
                $ids = array_filter($ids, static function ($id): bool {
                        return $id > 0;
                });

                return array_values(array_unique($ids));
        }

        private function render_bulk_toggle_script(string $scope): void {
                echo '<script>
document.addEventListener("DOMContentLoaded", function () {
    var master = document.querySelector("[data-cc-toggle-all=\"' . esc_js($scope) . '\"]");
    if (!master) return;
    master.addEventListener("change", function () {
        var items = document.querySelectorAll("input[data-cc-bulk-item=\"' . esc_js($scope) . '\"]");
        items.forEach(function (item) {
            item.checked = master.checked;
        });
    });
});
</script>';
        }

    public function render_dashboard(): void {
        $this->require_manage();
        $settings = CC_Repo::get_settings();
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        $activeYearId = $year ? (int) $year['id'] : null;
        $activeTermId = $term ? (int) $term['id'] : null;
        $dashboardStats = CC_Repo::get_dashboard_stats($activeYearId, $activeTermId);
        $classStats = CC_Repo::list_dashboard_class_stats($activeYearId, $activeTermId);

        echo '<div class="wrap cc-admin-page">';

        // ── Hero header ──────────────────────────────────────────────────────
        echo '<header class="cc-admin-page-head cc-admin-page-head--dashboard">';
        echo '<div class="cc-admin-dash-hero-main">';
        echo '<h1>' . esc_html__('Conseil de classe', 'conseil-classe') . '</h1>';
        if (!empty($settings['nom_etablissement'])) {
            echo '<p class="cc-admin-page-lede">' . esc_html($settings['nom_etablissement']) . '</p>';
        } else {
            echo '<p class="cc-admin-page-lede">';
            echo '<a class="cc-admin-dash-setup-link" href="' . esc_url(admin_url('admin.php?page=cc_settings')) . '">';
            echo esc_html__('⚠ Configurer l\'établissement', 'conseil-classe');
            echo '</a>';
            echo '</p>';
        }
        echo '</div>';
        echo '<div class="cc-admin-dash-hero-aside">';
        echo '<div class="cc-admin-dash-badges">';
        if ($year) {
            echo '<span class="cc-admin-dash-badge cc-admin-dash-badge--year">' . esc_html($year['nom']) . '</span>';
        } else {
            echo '<a class="cc-admin-dash-badge cc-admin-dash-badge--missing" href="' . esc_url(admin_url('admin.php?page=cc_years')) . '">' . esc_html__('Définir une année', 'conseil-classe') . '</a>';
        }
        if ($term) {
            echo '<span class="cc-admin-dash-badge cc-admin-dash-badge--term">' . esc_html($term['nom']) . '</span>';
        } else {
            echo '<a class="cc-admin-dash-badge cc-admin-dash-badge--missing" href="' . esc_url(admin_url('admin.php?page=cc_years')) . '">' . esc_html__('Définir un trimestre', 'conseil-classe') . '</a>';
        }
        echo '</div>';
        echo '</div>';
        // ── Stat strip (chiffres globaux) ────────────────────────────────────
        echo '<div class="cc-admin-dash-hero-strip">';
        echo '<div class="cc-admin-dash-hero-stat"><strong>' . esc_html((string) $dashboardStats['global']['parents']) . '</strong><span>' . esc_html__('Parents', 'conseil-classe') . '</span></div>';
        echo '<div class="cc-admin-dash-hero-stat"><strong>' . esc_html((string) $dashboardStats['global']['classes']) . '</strong><span>' . esc_html__('Classes', 'conseil-classe') . '</span></div>';
        echo '<div class="cc-admin-dash-hero-stat"><strong>' . esc_html((string) $dashboardStats['global']['councils']) . '</strong><span>' . esc_html__('Conseils', 'conseil-classe') . '</span></div>';
        echo '<div class="cc-admin-dash-hero-stat"><strong>' . esc_html((string) $dashboardStats['global']['reports']) . '</strong><span>' . esc_html__('Comptes-rendus', 'conseil-classe') . '</span></div>';
        echo '<div class="cc-admin-dash-hero-stat"><strong>' . esc_html((string) ($settings['max_parents_per_conseil'] ?? '2')) . '</strong><span>' . esc_html__('Quota/conseil', 'conseil-classe') . '</span></div>';
        echo '</div>';
        echo '</header>';

        $this->render_front_pages_setup_notice(true);

        // ── Tuiles de navigation ─────────────────────────────────────────────
        $tiles = [
            [
                'url'   => admin_url('admin.php?page=cc_settings'),
                'title' => __('Paramètres', 'conseil-classe'),
                'desc'  => __('Établissement, association, quotas, pages publiques.', 'conseil-classe'),
                'icon'  => 'dashicons-admin-settings',
                'count' => null,
            ],
            [
                'url'   => admin_url('admin.php?page=cc_years'),
                'title' => __('Années & trimestres', 'conseil-classe'),
                'desc'  => __('Cycles scolaires et périodes actives.', 'conseil-classe'),
                'icon'  => 'dashicons-calendar-alt',
                'count' => null,
            ],
            [
                'url'   => admin_url('admin.php?page=cc_classes'),
                'title' => __('Classes', 'conseil-classe'),
                'desc'  => __('Classes pour l’année active.', 'conseil-classe'),
                'icon'  => 'dashicons-welcome-learn-more',
                'count' => $year ? (int) $dashboardStats['active']['classes'] : null,
            ],
            [
                'url'   => admin_url('admin.php?page=cc_councils'),
                'title' => __('Planning des conseils', 'conseil-classe'),
                'desc'  => __('Créneaux, lieux et présidences.', 'conseil-classe'),
                'icon'  => 'dashicons-list-view',
                'count' => ($year && $term) ? (int) $dashboardStats['active']['councils'] : null,
            ],
            [
                'url'   => admin_url('admin.php?page=cc_parents'),
                'title' => __('Parents', 'conseil-classe'),
                'desc'  => __('Fiches, import CSV et comptes utilisateurs.', 'conseil-classe'),
                'icon'  => 'dashicons-groups',
                'count' => (int) $dashboardStats['global']['parents'],
            ],
            [
                'url'   => admin_url('admin.php?page=cc_reports'),
                'title' => __('Comptes-rendus', 'conseil-classe'),
                'desc'  => __('Lecture, modification et export PDF.', 'conseil-classe'),
                'icon'  => 'dashicons-media-document',
                'count' => ($year && $term) ? (int) $dashboardStats['active']['reports'] : null,
            ],
        ];

        echo '<div class="cc-admin-dash-grid" role="navigation" aria-label="' . esc_attr__('Raccourcis Conseil de classe', 'conseil-classe') . '">';
        foreach ($tiles as $t) {
            echo '<a class="cc-admin-dash-tile" href="' . esc_url($t['url']) . '">';
            echo '<span class="cc-admin-dash-tile-top">';
            echo '<span class="dashicons ' . esc_attr((string) $t['icon']) . ' cc-admin-dash-tile-icon"></span>';
            if ($t['count'] !== null) {
                echo '<span class="cc-admin-dash-tile-count">' . esc_html((string) $t['count']) . '</span>';
            }
            echo '</span>';
            echo '<span class="cc-admin-dash-tile-title">' . esc_html($t['title']) . '</span>';
            echo '<span class="cc-admin-dash-tile-desc">' . esc_html($t['desc']) . '</span>';
            echo '</a>';
        }
        echo '</div>';

        // ── Checklist de mise en service ─────────────────────────────────────
        $missingFrontPages = $this->get_missing_front_pages();
        $checkItems = [
            [
                'ok'    => !empty($settings['nom_etablissement']),
                'label' => __('Établissement', 'conseil-classe'),
                'url'   => admin_url('admin.php?page=cc_settings'),
            ],
            [
                'ok'    => $year !== null,
                'label' => __('Année active', 'conseil-classe'),
                'url'   => admin_url('admin.php?page=cc_years'),
            ],
            [
                'ok'    => $term !== null,
                'label' => __('Trimestre actif', 'conseil-classe'),
                'url'   => admin_url('admin.php?page=cc_years'),
            ],
            [
                'ok'    => empty($missingFrontPages),
                'label' => __('Pages publiques', 'conseil-classe'),
                'url'   => admin_url('admin.php?page=cc_settings'),
            ],
            [
                'ok'    => $year && (int) $dashboardStats['active']['classes'] > 0,
                'label' => __('Classes', 'conseil-classe'),
                'url'   => admin_url('admin.php?page=cc_classes'),
            ],
            [
                'ok'    => $year && $term && (int) $dashboardStats['active']['councils'] > 0,
                'label' => __('Conseils planifiés', 'conseil-classe'),
                'url'   => admin_url('admin.php?page=cc_councils'),
            ],
        ];
        $hasSetupIssues = false;
        foreach ($checkItems as $chk) {
            if (!$chk['ok']) {
                $hasSetupIssues = true;
                break;
            }
        }
        if ($hasSetupIssues) {
            echo '<section class="cc-admin-section cc-admin-dash-checklist">';
            echo '<h2>' . esc_html__('Mise en service', 'conseil-classe') . '</h2>';
            echo '<div class="cc-admin-checklist-items">';
            foreach ($checkItems as $chk) {
                $cls = $chk['ok'] ? 'ok' : 'missing';
                echo '<div class="cc-admin-checklist-item cc-admin-checklist-item--' . esc_attr($cls) . '">';
                if (!$chk['ok']) {
                    echo '<a href="' . esc_url($chk['url']) . '">' . esc_html($chk['label']) . '</a>';
                } else {
                    echo '<span>' . esc_html($chk['label']) . '</span>';
                }
                echo '</div>';
            }
            echo '</div>';
            echo '</section>';
        }

        // ── Actions rapides ──────────────────────────────────────────────────
        echo '<section class="cc-admin-section cc-admin-dash-quickactions">';
        echo '<h2>' . esc_html__('Actions rapides', 'conseil-classe') . '</h2>';
        echo '<div class="cc-admin-qa-grid">';
        echo '<a class="cc-admin-qa-btn" href="' . esc_url(admin_url('admin.php?page=cc_classes')) . '"><span class="dashicons dashicons-plus-alt2"></span>' . esc_html__('Ajouter une classe', 'conseil-classe') . '</a>';
        echo '<a class="cc-admin-qa-btn" href="' . esc_url(admin_url('admin.php?page=cc_councils')) . '"><span class="dashicons dashicons-plus-alt2"></span>' . esc_html__('Planifier un conseil', 'conseil-classe') . '</a>';
        echo '<a class="cc-admin-qa-btn" href="' . esc_url(admin_url('admin.php?page=cc_parents')) . '"><span class="dashicons dashicons-plus-alt2"></span>' . esc_html__('Ajouter un parent', 'conseil-classe') . '</a>';
        echo '<a class="cc-admin-qa-btn" href="' . esc_url(admin_url('admin.php?page=cc_reports')) . '"><span class="dashicons dashicons-media-document"></span>' . esc_html__('Comptes-rendus', 'conseil-classe') . '</a>';
        echo '<a class="cc-admin-qa-btn" href="' . esc_url(admin_url('admin.php?page=cc_settings')) . '"><span class="dashicons dashicons-admin-settings"></span>' . esc_html__('Paramètres', 'conseil-classe') . '</a>';
        echo '</div>';
        echo '</section>';

        if ($year && $term) {
            $maxParentsPerCouncil = max(1, (int) ($settings['max_parents_per_conseil'] ?? 2));
            $totalCouncilCapacity = max(0, (int) $dashboardStats['active']['councils'] * $maxParentsPerCouncil);
            $usedCapacity = min($totalCouncilCapacity, (int) $dashboardStats['active']['registrations']);
            $freeCapacity = max(0, $totalCouncilCapacity - $usedCapacity);
            $plannedClasses = max(0, (int) $dashboardStats['active']['classes'] - (int) $dashboardStats['active']['classes_without_council']);
            $draftReports = max(0, (int) $dashboardStats['active']['reports'] - (int) $dashboardStats['active']['validated_reports']);

            $activeCards = [
                [
                    'label' => __('Parents inscrits', 'conseil-classe'),
                    'value' => (string) $dashboardStats['active']['registered_parents'],
                    'meta' => __('Parents distincts inscrits sur le trimestre actif.', 'conseil-classe'),
                ],
                [
                    'label' => __('Inscriptions', 'conseil-classe'),
                    'value' => (string) $dashboardStats['active']['registrations'],
                    'meta' => __('Total des inscriptions enregistrées sur les conseils actifs.', 'conseil-classe'),
                ],
                [
                    'label' => __('Conseils planifiés', 'conseil-classe'),
                    'value' => (string) $dashboardStats['active']['councils'],
                    'meta' => __('Conseils rattachés à l’année et au trimestre actifs.', 'conseil-classe'),
                ],
                [
                    'label' => __('Conseils orphelins', 'conseil-classe'),
                    'value' => (string) $dashboardStats['active']['orphan_councils'],
                    'meta' => __('Conseils sans parent inscrit pour le moment.', 'conseil-classe'),
                    'tone' => ((int) $dashboardStats['active']['orphan_councils'] > 0) ? 'warning' : 'ok',
                ],
                [
                    'label' => __('Comptes-rendus saisis', 'conseil-classe'),
                    'value' => (string) $dashboardStats['active']['reports'],
                    'meta' => __('Comptes-rendus présents pour les conseils du trimestre actif.', 'conseil-classe'),
                ],
                [
                    'label' => __('Comptes-rendus validés', 'conseil-classe'),
                    'value' => (string) $dashboardStats['active']['validated_reports'],
                    'meta' => __('Comptes-rendus validés administrativement.', 'conseil-classe'),
                    'tone' => 'ok',
                ],
                [
                    'label' => __('Comptes-rendus manquants', 'conseil-classe'),
                    'value' => (string) $dashboardStats['active']['pending_reports'],
                    'meta' => __('Conseils planifiés sans compte-rendu saisi.', 'conseil-classe'),
                    'tone' => ((int) $dashboardStats['active']['pending_reports'] > 0) ? 'warning' : 'ok',
                ],
                [
                    'label' => __('Classes sans conseil', 'conseil-classe'),
                    'value' => (string) $dashboardStats['active']['classes_without_council'],
                    'meta' => __('Classes de l’année active encore sans planification.', 'conseil-classe'),
                    'tone' => ((int) $dashboardStats['active']['classes_without_council'] > 0) ? 'warning' : 'ok',
                ],
            ];

            // Données pour le graphique horizontal (inscrits par classe)
            $classLabels = [];
            $classData = [];
            $classColors = [];
            $classBorderColors = [];
            foreach ($classStats as $row) {
                $classLabels[] = (string) $row['nom'];
                $classData[] = (int) ($row['registrations_count'] ?? 0);
                $hasCouncilRow = !empty($row['council_id']);
                $isValidatedRow = (int) ($row['report_validated'] ?? 0) === 1;
                if (!$hasCouncilRow) {
                    $classColors[] = 'rgba(199,206,212,0.80)';
                    $classBorderColors[] = '#8a9aa8';
                } elseif ($isValidatedRow) {
                    $classColors[] = 'rgba(34,169,92,0.82)';
                    $classBorderColors[] = '#17883e';
                } else {
                    $classColors[] = 'rgba(34,113,177,0.82)';
                    $classBorderColors[] = '#135e96';
                }
            }

            $maxRegistrationsByClass = 0;
            foreach ($classStats as $row) {
                $maxRegistrationsByClass = max($maxRegistrationsByClass, (int) ($row['registrations_count'] ?? 0));
            }

            $generalCards = [
                [
                    'label' => __('Parents enregistrés', 'conseil-classe'),
                    'value' => (string) $dashboardStats['global']['parents'],
                    'meta' => __('Fiches parents présentes dans le plugin.', 'conseil-classe'),
                ],
                [
                    'label' => __('Classes', 'conseil-classe'),
                    'value' => (string) $dashboardStats['global']['classes'],
                    'meta' => __('Total cumulé des classes stockées.', 'conseil-classe'),
                ],
                [
                    'label' => __('Conseils', 'conseil-classe'),
                    'value' => (string) $dashboardStats['global']['councils'],
                    'meta' => __('Tous trimestres et années confondus.', 'conseil-classe'),
                ],
                [
                    'label' => __('Inscriptions cumulées', 'conseil-classe'),
                    'value' => (string) $dashboardStats['global']['registrations'],
                    'meta' => __('Historique complet des inscriptions.', 'conseil-classe'),
                ],
                [
                    'label' => __('Comptes-rendus cumulés', 'conseil-classe'),
                    'value' => (string) $dashboardStats['global']['reports'],
                    'meta' => __('Tous les comptes-rendus enregistrés.', 'conseil-classe'),
                ],
                [
                    'label' => __('Templates PDF', 'conseil-classe'),
                    'value' => (string) $dashboardStats['global']['pdf_templates'],
                    'meta' => sprintf(
                        /* translators: %d: active templates count */
                        __('%d actif(s) actuellement.', 'conseil-classe'),
                        (int) $dashboardStats['global']['active_pdf_templates']
                    ),
                ],
            ];

            // ── JSON pour Chart.js ───────────────────────────────────────────
            $chartData = [
                'councilStatus' => [
                    'labels' => [
                        __('CR validé', 'conseil-classe'),
                        __('CR saisi (non validé)', 'conseil-classe'),
                        __('Planifié sans CR', 'conseil-classe'),
                        __('Orphelin (sans inscrit)', 'conseil-classe'),
                    ],
                    'data' => [
                        (int) $dashboardStats['active']['validated_reports'],
                        $draftReports,
                        max(0, (int) $dashboardStats['active']['councils'] - (int) $dashboardStats['active']['reports'] - (int) $dashboardStats['active']['orphan_councils']),
                        (int) $dashboardStats['active']['orphan_councils'],
                    ],
                ],
                'occupation' => [
                    'labels' => [
                        __('Places occupées', 'conseil-classe'),
                        __('Places libres', 'conseil-classe'),
                        __('Conseils orphelins', 'conseil-classe'),
                    ],
                    'data' => [$usedCapacity, $freeCapacity, (int) $dashboardStats['active']['orphan_councils']],
                ],
                'reports' => [
                    'labels' => [
                        __('Validés', 'conseil-classe'),
                        __('Saisis', 'conseil-classe'),
                        __('Manquants', 'conseil-classe'),
                    ],
                    'data' => [
                        (int) $dashboardStats['active']['validated_reports'],
                        $draftReports,
                        (int) $dashboardStats['active']['pending_reports'],
                    ],
                ],
                'coverage' => [
                    'labels' => [
                        __('Classes planifiées', 'conseil-classe'),
                        __('Sans conseil', 'conseil-classe'),
                    ],
                    'data' => [
                        $plannedClasses,
                        (int) $dashboardStats['active']['classes_without_council'],
                    ],
                ],
                'classInscrits' => [
                    'labels'       => $classLabels,
                    'data'         => $classData,
                    'colors'       => $classColors,
                    'borderColors' => $classBorderColors,
                    'legend'       => __('Inscrits', 'conseil-classe'),
                ],
            ];
            echo '<script id="cc-chart-data">var ccDashCharts = ' . wp_json_encode($chartData) . ';</script>';

            // ── Section graphiques (4 canvases) ─────────────────────────────
            echo '<section class="cc-admin-section cc-admin-section--dashboard">';
            echo '<h2>' . esc_html__('Graphiques de synthèse', 'conseil-classe') . '</h2>';
            echo '<div class="cc-admin-canvas-grid">';
            echo '<div class="cc-admin-canvas-card"><h3>' . esc_html__('État des conseils', 'conseil-classe') . '</h3>';
            echo '<div class="cc-admin-canvas-wrap"><canvas id="cc-chart-councils" role="img" aria-label="' . esc_attr__('État des conseils', 'conseil-classe') . '"></canvas></div></div>';
            echo '<div class="cc-admin-canvas-card"><h3>' . esc_html__('Occupation des places', 'conseil-classe') . '</h3>';
            echo '<div class="cc-admin-canvas-wrap"><canvas id="cc-chart-occupation" role="img" aria-label="' . esc_attr__('Occupation des places', 'conseil-classe') . '"></canvas></div></div>';
            echo '<div class="cc-admin-canvas-card"><h3>' . esc_html__('Comptes-rendus', 'conseil-classe') . '</h3>';
            echo '<div class="cc-admin-canvas-wrap"><canvas id="cc-chart-reports" role="img" aria-label="' . esc_attr__('Comptes-rendus', 'conseil-classe') . '"></canvas></div></div>';
            echo '<div class="cc-admin-canvas-card"><h3>' . esc_html__('Couverture des classes', 'conseil-classe') . '</h3>';
            echo '<div class="cc-admin-canvas-wrap"><canvas id="cc-chart-coverage" role="img" aria-label="' . esc_attr__('Couverture des classes', 'conseil-classe') . '"></canvas></div></div>';
            echo '</div>';
            echo '</section>';

            echo '<section class="cc-admin-section cc-admin-section--dashboard">';
            echo '<h2>' . esc_html__('Pilotage du trimestre actif', 'conseil-classe') . '</h2>';
            echo '<div class="cc-admin-metric-grid">';
            foreach ($activeCards as $card) {
                echo '<article class="cc-admin-metric-card' . (!empty($card['tone']) ? ' cc-admin-metric-card--' . esc_attr((string) $card['tone']) : '') . '">';
                echo '<span class="cc-admin-metric-label">' . esc_html((string) $card['label']) . '</span>';
                echo '<strong class="cc-admin-metric-value">' . esc_html((string) $card['value']) . '</strong>';
                echo '<p class="cc-admin-metric-meta">' . esc_html((string) $card['meta']) . '</p>';
                echo '</article>';
            }
            echo '</div>';
            echo '</section>';

            echo '<section class="cc-admin-section cc-admin-section--dashboard">';
            echo '<h2>' . esc_html__('Vue générale', 'conseil-classe') . '</h2>';
            echo '<div class="cc-admin-metric-grid cc-admin-metric-grid--compact">';
            foreach ($generalCards as $card) {
                echo '<article class="cc-admin-metric-card">';
                echo '<span class="cc-admin-metric-label">' . esc_html((string) $card['label']) . '</span>';
                echo '<strong class="cc-admin-metric-value">' . esc_html((string) $card['value']) . '</strong>';
                echo '<p class="cc-admin-metric-meta">' . esc_html((string) $card['meta']) . '</p>';
                echo '</article>';
            }
            echo '</div>';
            echo '</section>';

            echo '<section class="cc-admin-section cc-admin-section--dashboard">';
            echo '<h2>' . esc_html__('Statistiques par classe', 'conseil-classe') . '</h2>';
            if ($classStats) {
                $canvasHeight = max(200, count($classStats) * 38) . 'px';
                echo '<div class="cc-admin-canvas-wrap cc-admin-canvas-wrap--class" style="height:' . esc_attr($canvasHeight) . '">';
                echo '<canvas id="cc-chart-classes" role="img" aria-label="' . esc_attr__('Inscriptions par classe', 'conseil-classe') . '"></canvas>';
                echo '</div>';
            } else {
                echo '<p>' . esc_html__('Aucune classe à afficher pour le contexte actif.', 'conseil-classe') . '</p>';
            }
            echo '<table class="widefat striped cc-admin-dashboard-table"><thead><tr>';
            echo '<th>' . esc_html__('Classe', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Conseil', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Inscrits', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Compte-rendu', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('État', 'conseil-classe') . '</th>';
            echo '</tr></thead><tbody>';

            foreach ($classStats as $row) {
                $hasCouncil = !empty($row['council_id']);
                $hasReport = !empty($row['report_id']);
                $isValidated = (int) ($row['report_validated'] ?? 0) === 1;
                $schedule = __('Non planifié', 'conseil-classe');
                if ($hasCouncil) {
                    $schedule = mysql2date('d/m/Y', (string) $row['date_conseil']);
                    if (!empty($row['heure_debut'])) {
                        $schedule .= ' · ' . substr((string) $row['heure_debut'], 0, 5);
                    }
                }

                $statusLabel = __('À planifier', 'conseil-classe');
                $statusClass = 'pending';
                if ($hasCouncil && !$hasReport) {
                    $statusLabel = __('Conseil planifié', 'conseil-classe');
                    $statusClass = 'scheduled';
                }
                if ($hasReport) {
                    $statusLabel = $isValidated ? __('Compte-rendu validé', 'conseil-classe') : __('Compte-rendu en attente', 'conseil-classe');
                    $statusClass = $isValidated ? 'ok' : 'warning';
                }

                echo '<tr>';
                echo '<td><strong>' . esc_html((string) $row['nom']) . '</strong><br /><span class="cc-admin-dashboard-muted">' . esc_html((string) $row['niveau']) . '</span></td>';
                echo '<td>' . esc_html($schedule) . '</td>';
                echo '<td>' . esc_html((string) ((int) ($row['registrations_count'] ?? 0))) . '</td>';
                echo '<td>' . esc_html($hasReport ? ($isValidated ? __('Validé', 'conseil-classe') : __('Saisi', 'conseil-classe')) : __('Absent', 'conseil-classe')) . '</td>';
                echo '<td><span class="cc-admin-status-badge cc-admin-status-badge--' . esc_attr($statusClass) . '">' . esc_html($statusLabel) . '</span></td>';
                echo '</tr>';
            }

            if (!$classStats) {
                echo '<tr><td colspan="5">' . esc_html__('Aucune classe à afficher pour le contexte actif.', 'conseil-classe') . '</td></tr>';
            }

            echo '</tbody></table>';
            echo '</section>';
        } else {
            echo '<section class="cc-admin-section">';
            echo '<h2>' . esc_html__('Tableau de bord', 'conseil-classe') . '</h2>';
            echo '<p>' . esc_html__('Définissez une année scolaire active et un trimestre actif pour afficher les statistiques détaillées du tableau de bord.', 'conseil-classe') . '</p>';
            echo '</section>';
        }

        echo '</div>';
    }

    private function dashboard_percent(int $value, int $total): int {
        if ($total <= 0) {
            return 0;
        }

        $percent = (int) round(($value / $total) * 100);
        if ($value > 0 && $percent === 0) {
            return 1;
        }

        return max(0, min(100, $percent));
    }

    public function render_settings(): void {
        $this->require_manage();
        $s = CC_Repo::get_settings();
        $year = CC_Repo::get_active_year();
        $nonce = wp_create_nonce('cc_save_settings');
        $dashboardUrl = admin_url('admin.php?page=cc_dashboard');
        $logsUrl = admin_url('admin.php?page=cc_logs');

        $schoolAddressLines = preg_split('/\r\n|\r|\n/', (string) ($s['adresse_etablissement'] ?? '')) ?: [];
        $schoolAddressLines = array_values(array_filter(array_map('trim', $schoolAddressLines), static function (string $line): bool {
            return $line !== '';
        }));
        $associationAddressLines = preg_split('/\r\n|\r|\n/', (string) ($s['adresse_association_parents'] ?? '')) ?: [];
        $associationAddressLines = array_values(array_filter(array_map('trim', $associationAddressLines), static function (string $line): bool {
            return $line !== '';
        }));

        $schoolContactLines = [];
        if (($s['telephone_etablissement'] ?? '') !== '') {
            $schoolContactLines[] = sprintf(__('Tél: %s', 'conseil-classe'), $s['telephone_etablissement']);
        }
        if (($s['email_etablissement'] ?? '') !== '') {
            $schoolContactLines[] = sprintf(__('Email: %s', 'conseil-classe'), $s['email_etablissement']);
        }
        if (($s['site_web_etablissement'] ?? '') !== '') {
            $schoolContactLines[] = sprintf(__('Web: %s', 'conseil-classe'), $s['site_web_etablissement']);
        }

        $associationContactLines = [];
        if (($s['telephone_association_parents'] ?? '') !== '') {
            $associationContactLines[] = sprintf(__('Tél: %s', 'conseil-classe'), $s['telephone_association_parents']);
        }
        if (($s['email_association_parents'] ?? '') !== '') {
            $associationContactLines[] = sprintf(__('Email: %s', 'conseil-classe'), $s['email_association_parents']);
        }
        if (($s['site_web_association_parents'] ?? '') !== '') {
            $associationContactLines[] = sprintf(__('Web: %s', 'conseil-classe'), $s['site_web_association_parents']);
        }

        echo '<div class="wrap cc-admin-page cc-settings-page">';
        echo '<header class="cc-settings-hero">';
        echo '<h1><span class="dashicons dashicons-admin-generic" aria-hidden="true"></span> ' . esc_html__('Paramètres et Coordonnées', 'conseil-classe') . '</h1>';
        echo '<p class="cc-settings-hero-lede">' . esc_html__('Gestion des coordonnées de l’établissement et de l’association des parents d’élèves.', 'conseil-classe') . '</p>';
        if ($year) {
            echo '<p class="cc-settings-hero-lede"><strong>' . esc_html__('Année active :', 'conseil-classe') . '</strong> ' . esc_html($year['nom']) . '</p>';
        }
        echo '<p class="cc-settings-breadcrumb"><a href="' . esc_url($dashboardUrl) . '">' . esc_html__('Accueil', 'conseil-classe') . '</a> <span>/</span> <span>' . esc_html__('Paramètres', 'conseil-classe') . '</span></p>';
        echo '</header>';

        if ($this->get_scalar('updated') !== '') {
            echo '<div class="notice notice-success"><p>' . esc_html__('Paramètres enregistrés.', 'conseil-classe') . '</p></div>';
        }

        $this->render_front_pages_setup_notice();

        echo '<div class="cc-settings-layout">';
        echo '<aside class="cc-settings-sidebar">';
        echo '<section class="cc-settings-panel cc-settings-panel--actions">';
        echo '<h2><span class="dashicons dashicons-admin-links" aria-hidden="true"></span> ' . esc_html__('Actions', 'conseil-classe') . '</h2>';
        echo '<div class="cc-settings-action-list">';
        echo '<a class="cc-settings-action cc-settings-action--primary" href="#cc-settings-establishment"><span class="dashicons dashicons-building" aria-hidden="true"></span>' . esc_html__('Configurer l’établissement', 'conseil-classe') . '</a>';
        echo '<a class="cc-settings-action cc-settings-action--success" href="#cc-settings-association"><span class="dashicons dashicons-groups" aria-hidden="true"></span>' . esc_html__('Configurer l’association', 'conseil-classe') . '</a>';
        echo '<a class="cc-settings-action cc-settings-action--info" href="#cc-settings-pages"><span class="dashicons dashicons-visibility" aria-hidden="true"></span>' . esc_html__('Voir toutes les coordonnées', 'conseil-classe') . '</a>';
        echo '<a class="cc-settings-action cc-settings-action--warning" href="' . esc_url($logsUrl) . '"><span class="dashicons dashicons-backup" aria-hidden="true"></span>' . esc_html__('Historique des modifications', 'conseil-classe') . '</a>';
        echo '</div>';
        echo '</section>';
        echo '</aside>';

        echo '<div class="cc-settings-main">';
        echo '<div class="cc-settings-summary-grid">';
        $this->render_settings_summary_card(
            (string) ($s['nom_etablissement'] ?? __('Établissement', 'conseil-classe')),
            'dashicons-building',
            [
                [
                    [
                        'label' => __('Adresse', 'conseil-classe'),
                        'lines' => $schoolAddressLines,
                    ],
                    [
                        'label' => __('Contact', 'conseil-classe'),
                        'lines' => $schoolContactLines,
                    ],
                ],
                [
                    [
                        'label' => __('Principal/Principale', 'conseil-classe'),
                        'lines' => array_values(array_filter([(string) ($s['nom_principal'] ?? '')])),
                    ],
                    [
                        'label' => __('Direction', 'conseil-classe'),
                        'lines' => array_values(array_filter([(string) ($s['nom_directeur'] ?? '')])),
                    ],
                ],
            ]
        );
        $this->render_settings_summary_card(
            (string) ($s['nom_association_parents'] ?? __('Association des parents', 'conseil-classe')),
            'dashicons-groups',
            [
                [
                    [
                        'label' => __('Adresse', 'conseil-classe'),
                        'lines' => $associationAddressLines,
                    ],
                    [
                        'label' => __('Contact', 'conseil-classe'),
                        'lines' => $associationContactLines,
                    ],
                ],
                [
                    [
                        'label' => __('Président(e)', 'conseil-classe'),
                        'lines' => array_values(array_filter([(string) ($s['president_association'] ?? '')])),
                    ],
                    [
                        'label' => __('Bureau', 'conseil-classe'),
                        'lines' => array_values(array_filter([
                            (string) ($s['vice_president_association'] ?? ''),
                            (string) ($s['secretaire_association'] ?? ''),
                            (string) ($s['tresorier_association'] ?? ''),
                        ])),
                    ],
                ],
            ]
        );
        echo '</div>';

        echo '<form class="cc-settings-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="cc_save_settings" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';

        echo '<section id="cc-settings-establishment" class="cc-settings-panel cc-settings-panel--form">';
        echo '<div class="cc-settings-panel-head">';
        echo '<h2>' . esc_html__('Établissement', 'conseil-classe') . '</h2>';
        echo '<p>' . esc_html__('Coordonnées et contacts principaux du collège.', 'conseil-classe') . '</p>';
        echo '</div>';
        echo '<div class="cc-settings-fields">';
        $this->text('nom_etablissement', __('Nom', 'conseil-classe'), $s['nom_etablissement'] ?? '');
        $this->textarea('adresse_etablissement', __('Adresse', 'conseil-classe'), $s['adresse_etablissement'] ?? '');
        $this->text('telephone_etablissement', __('Téléphone', 'conseil-classe'), $s['telephone_etablissement'] ?? '');
        $this->text('email_etablissement', __('Email', 'conseil-classe'), $s['email_etablissement'] ?? '');
        $this->text('site_web_etablissement', __('Site web', 'conseil-classe'), $s['site_web_etablissement'] ?? '');
        $this->text('nom_directeur', __('Nom du directeur', 'conseil-classe'), $s['nom_directeur'] ?? '');
        $this->text('nom_principal', __('Nom du principal', 'conseil-classe'), $s['nom_principal'] ?? '');
        echo '</div>';
        echo '</section>';

        echo '<section id="cc-settings-association" class="cc-settings-panel cc-settings-panel--form">';
        echo '<div class="cc-settings-panel-head">';
        echo '<h2>' . esc_html__('Association des parents', 'conseil-classe') . '</h2>';
        echo '<p>' . esc_html__('Informations de contact et composition du bureau.', 'conseil-classe') . '</p>';
        echo '</div>';
        echo '<div class="cc-settings-fields">';
        $this->text('nom_association_parents', __('Nom', 'conseil-classe'), $s['nom_association_parents'] ?? '');
        $this->textarea('adresse_association_parents', __('Adresse', 'conseil-classe'), $s['adresse_association_parents'] ?? '');
        $this->text('telephone_association_parents', __('Téléphone', 'conseil-classe'), $s['telephone_association_parents'] ?? '');
        $this->text('email_association_parents', __('Email', 'conseil-classe'), $s['email_association_parents'] ?? '');
        $this->text('site_web_association_parents', __('Site web', 'conseil-classe'), $s['site_web_association_parents'] ?? '');
        $this->text('president_association', __('Président', 'conseil-classe'), $s['president_association'] ?? '');
        $this->text('vice_president_association', __('Vice-président', 'conseil-classe'), $s['vice_president_association'] ?? '');
        $this->text('secretaire_association', __('Secrétaire', 'conseil-classe'), $s['secretaire_association'] ?? '');
        $this->text('tresorier_association', __('Trésorier', 'conseil-classe'), $s['tresorier_association'] ?? '');
        echo '</div>';
        echo '</section>';

        echo '<section id="cc-settings-rules" class="cc-settings-panel cc-settings-panel--form">';
        echo '<div class="cc-settings-panel-head">';
        echo '<h2>' . esc_html__('Règles', 'conseil-classe') . '</h2>';
        echo '<p>' . esc_html__('Réglages de fonctionnement appliqués aux inscriptions.', 'conseil-classe') . '</p>';
        echo '</div>';
        echo '<div class="cc-settings-fields cc-settings-fields--compact">';
        $this->number('max_parents_per_conseil', __('Nombre max d’inscriptions par conseil', 'conseil-classe'), (string) ($s['max_parents_per_conseil'] ?? 2), 1, 50);
        echo '</div>';
        echo '</section>';

        echo '<section id="cc-settings-pages" class="cc-settings-panel cc-settings-panel--form">';
        echo '<div class="cc-settings-panel-head">';
        echo '<h2>' . esc_html__('Pages (front)', 'conseil-classe') . '</h2>';
        echo '<p>' . esc_html__('Associez les pages du site qui exposent les shortcodes publics.', 'conseil-classe') . '</p>';
        echo '</div>';
        echo '<div class="cc-settings-fields">';
        $loginPageId = (int) get_option('cc_parent_login_page_id', 0);
        $this->render_settings_page_selector(
            'cc_parent_login_page_id',
            __('Page « Connexion / compte » (shortcode [cc_parent_login] — liens vers la connexion du site)', 'conseil-classe'),
            absint($loginPageId)
        );

        $planningPageId = (int) get_option('cc_plannings_page_id', 0);
        $this->render_settings_page_selector(
            'cc_plannings_page_id',
            __('Page “Planning” (doit contenir le shortcode [cc_plannings])', 'conseil-classe'),
            absint($planningPageId)
        );

        $myCouncilsPageId = (int) get_option('cc_my_councils_page_id', 0);
        $this->render_settings_page_selector(
            'cc_my_councils_page_id',
            __('Page “Mes conseils” (doit contenir le shortcode [cc_my_councils])', 'conseil-classe'),
            absint($myCouncilsPageId)
        );

        $reportPageId = (int) get_option('cc_report_form_page_id', 0);
        $this->render_settings_page_selector(
            'cc_report_form_page_id',
            __('Page “Formulaire compte-rendu” (doit contenir le shortcode [cc_report_form])', 'conseil-classe'),
            absint($reportPageId)
        );

        echo '</div>';
        echo '</section>';

        echo '<div class="cc-settings-submit">';
        submit_button(__('Enregistrer', 'conseil-classe'));
        echo '</div>';
        echo '</form>';

        echo '<section id="cc-settings-import" class="cc-settings-panel cc-settings-panel--form cc-settings-panel--import">';
        echo '<div class="cc-settings-panel-head">';
        echo '<h2>' . esc_html__('Import / Export (CSV)', 'conseil-classe') . '</h2>';
        echo '<p>' . esc_html__('Exportez la configuration actuelle ou réinjectez un fichier CSV d’une seule ligne.', 'conseil-classe') . '</p>';
        echo '</div>';
        echo '<div class="cc-settings-import-actions">';
        echo '<a class="button button-primary" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_settings_export_csv'), 'cc_settings_export_csv')) . '">' . esc_html__('Export établissement + association (CSV)', 'conseil-classe') . '</a> ';
        echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_settings_download_template'), 'cc_settings_download_template')) . '">' . esc_html__('Modèle import (CSV)', 'conseil-classe') . '</a>';
        echo '</div>';
        echo '<form class="cc-settings-import-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="cc_settings_import_csv" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p><input type="file" name="csv_file" accept=".csv,text/csv" required /> ';
        submit_button(__('Import (1 ligne)', 'conseil-classe'), 'secondary', 'submit', false);
        echo ' <a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_settings_download_template'), 'cc_settings_download_template')) . '">' . esc_html__('Télécharger le modèle', 'conseil-classe') . '</a>';
        echo '</p></form>';
        echo '</section>';

        echo '<section id="cc-settings-term-workbook" class="cc-settings-panel cc-settings-panel--form cc-settings-panel--import">';
        echo '<div class="cc-settings-panel-head">';
        echo '<h2>' . esc_html__('Export / Import trimestre (Excel)', 'conseil-classe') . '</h2>';
        echo '<p>' . esc_html__('Un seul fichier Excel multi-onglets avec les parents, classes, plannings, inscriptions et comptes-rendus du trimestre actif.', 'conseil-classe') . '</p>';
        echo '</div>';
        if ($this->get_scalar('term_bundle_context_error') === '1') {
            echo '<div class="notice notice-error"><p>' . esc_html__('Le fichier ne correspond pas à l’année/trimestre actifs. Activez le bon contexte avant l’import.', 'conseil-classe') . '</p></div>';
        }
        if ($this->get_scalar('term_bundle_imported') !== '' || $this->get_scalar('term_bundle_ignored') !== '' || $this->get_scalar('term_bundle_errors') !== '') {
            echo '<div class="notice notice-success"><p>' . esc_html(sprintf(
                /* translators: 1: imported rows, 2: ignored rows, 3: error rows */
                __('Import trimestre terminé : %1$d importé(s), %2$d ignoré(s), %3$d erreur(s).', 'conseil-classe'),
                (int) $this->get_scalar('term_bundle_imported', '0'),
                (int) $this->get_scalar('term_bundle_ignored', '0'),
                (int) $this->get_scalar('term_bundle_errors', '0')
            )) . '</p></div>';
        }
        echo '<div class="cc-settings-import-actions">';
        echo '<a class="button button-primary" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_term_export_excel'), 'cc_term_export_excel')) . '">' . esc_html__('Export trimestre (Excel)', 'conseil-classe') . '</a>';
        echo '</div>';
        echo '<form class="cc-settings-import-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="cc_term_import_excel" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p><input type="file" name="excel_file" accept=".xml,.xls,application/vnd.ms-excel,text/xml,application/xml" required /> ';
        submit_button(__('Importer le trimestre', 'conseil-classe'), 'secondary', 'submit', false);
        echo '</p></form>';
        echo '</section>';

        echo '</div>';
        echo '</div>';

        echo '</div>';
    }

    private function render_settings_summary_card(string $title, string $iconClass, array $columns): void {
        echo '<section class="cc-settings-panel cc-settings-summary-card">';
        echo '<header class="cc-settings-summary-head"><h2><span class="dashicons ' . esc_attr($iconClass) . '" aria-hidden="true"></span> ' . esc_html($title) . '</h2></header>';
        echo '<div class="cc-settings-summary-columns">';
        foreach ($columns as $column) {
            echo '<div class="cc-settings-summary-column">';
            foreach ($column as $block) {
                $label = isset($block['label']) ? (string) $block['label'] : '';
                $lines = [];
                if (isset($block['lines']) && is_array($block['lines'])) {
                    foreach ($block['lines'] as $line) {
                        if (!is_scalar($line)) {
                            continue;
                        }
                        $line = trim((string) $line);
                        if ($line !== '') {
                            $lines[] = $line;
                        }
                    }
                }

                echo '<div class="cc-settings-summary-item">';
                echo '<h3>' . esc_html($label) . '</h3>';
                if ($lines) {
                    echo '<p>' . implode('<br />', array_map('esc_html', $lines)) . '</p>';
                } else {
                    echo '<p class="cc-settings-summary-empty">' . esc_html__('Non renseigné', 'conseil-classe') . '</p>';
                }
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>';
        echo '</section>';
    }

    private function render_settings_page_selector(string $name, string $label, int $selected): void {
        echo '<p class="cc-settings-field cc-settings-field--wide"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label><br />';
        wp_dropdown_pages([
            'name' => $name,
            'selected' => $selected,
            'show_option_none' => esc_html__('— Non défini —', 'conseil-classe'),
        ]);
        echo '</p>';
    }

    public function handle_save_settings(): void {
        $this->require_manage();
        check_admin_referer('cc_save_settings');
        $year = CC_Repo::get_active_year();
        if (!$year) {
            $this->redirect_admin('cc_settings');
        }

        $data = [
            'nom_etablissement' => sanitize_text_field($this->post_scalar('nom_etablissement')),
            'adresse_etablissement' => CC_Utils::sanitize_textarea($this->post_scalar('adresse_etablissement')),
            'telephone_etablissement' => sanitize_text_field($this->post_scalar('telephone_etablissement')),
            'email_etablissement' => sanitize_email($this->post_scalar('email_etablissement')),
            'site_web_etablissement' => esc_url_raw($this->post_scalar('site_web_etablissement')),
            'nom_directeur' => sanitize_text_field($this->post_scalar('nom_directeur')),
            'nom_principal' => sanitize_text_field($this->post_scalar('nom_principal')),
            'nom_association_parents' => sanitize_text_field($this->post_scalar('nom_association_parents')),
            'adresse_association_parents' => CC_Utils::sanitize_textarea($this->post_scalar('adresse_association_parents')),
            'telephone_association_parents' => sanitize_text_field($this->post_scalar('telephone_association_parents')),
            'email_association_parents' => sanitize_email($this->post_scalar('email_association_parents')),
            'site_web_association_parents' => esc_url_raw($this->post_scalar('site_web_association_parents')),
            'president_association' => sanitize_text_field($this->post_scalar('president_association')),
            'vice_president_association' => sanitize_text_field($this->post_scalar('vice_president_association')),
            'secretaire_association' => sanitize_text_field($this->post_scalar('secretaire_association')),
            'tresorier_association' => sanitize_text_field($this->post_scalar('tresorier_association')),
            'max_parents_per_conseil' => max(1, (int) $this->post_scalar('max_parents_per_conseil', '2')),
        ];

        CC_Repo::update_settings($data, (int) $year['id']);

        update_option('cc_parent_login_page_id', (int) $this->post_scalar('cc_parent_login_page_id', '0'));
        update_option('cc_plannings_page_id', (int) $this->post_scalar('cc_plannings_page_id', '0'));
        update_option('cc_my_councils_page_id', (int) $this->post_scalar('cc_my_councils_page_id', '0'));
        update_option('cc_report_form_page_id', (int) $this->post_scalar('cc_report_form_page_id', '0'));
        $this->redirect_admin('cc_settings', ['updated' => 1]);
    }

    public function render_years(): void {
        $this->require_manage();
        $years = CC_Repo::list_years();
        $nonce = wp_create_nonce('cc_years');

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Années scolaires', 'conseil-classe') . '</h1>';

        echo '<h2>' . esc_html__('Ajouter', 'conseil-classe') . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="cc_year_create" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        $this->text('nom', __('Nom (ex: 2025-2026)', 'conseil-classe'), '');
        echo '<label><input type="checkbox" name="active" value="1" /> ' . esc_html__('Définir active', 'conseil-classe') . '</label>';
        submit_button(__('Créer', 'conseil-classe'), 'primary', 'submit', false);
        echo '</form>';

        echo '<hr />';
        echo '<h2>' . esc_html__('Liste', 'conseil-classe') . '</h2>';

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . esc_html__('Nom', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Active', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Actions', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($years as $y) {
            $active = ((int) $y['active'] === 1);
            echo '<tr>';
            echo '<td>' . esc_html($y['nom']) . '</td>';
            echo '<td>' . ($active ? '<strong>' . esc_html__('Oui', 'conseil-classe') . '</strong>' : esc_html__('Non', 'conseil-classe')) . '</td>';
            echo '<td>';

            echo '<form style="display:inline" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            echo '<input type="hidden" name="action" value="cc_year_set_active" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $y['id']) . '" />';
            submit_button(__('Activer', 'conseil-classe'), 'secondary', 'submit', false);
            echo '</form> ';

            echo '<form style="display:inline" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Supprimer cette année ?\');">';
            echo '<input type="hidden" name="action" value="cc_year_delete" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $y['id']) . '" />';
            submit_button(__('Supprimer', 'conseil-classe'), 'delete', 'submit', false);
            echo '</form>';

            echo '</td>';
            echo '</tr>';
        }

        if (!$years) {
            echo '<tr><td colspan="3">' . esc_html__('Aucune année.', 'conseil-classe') . '</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
    }

    public function handle_year_create(): void {
        $this->require_manage();
        check_admin_referer('cc_years');
        $nom = sanitize_text_field($this->post_scalar('nom'));
        if ($nom !== '') {
            CC_Repo::create_year($nom, $this->post_scalar('active') !== '');
        }
        $this->redirect_admin('cc_years');
    }

    public function handle_year_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_years');
        $id = (int) $this->post_scalar('id', '0');
        if ($id > 0) {
            CC_Repo::delete_year($id);
        }
        $this->redirect_admin('cc_years');
    }

    public function handle_year_set_active(): void {
        $this->require_manage();
        check_admin_referer('cc_years');
        $id = (int) $this->post_scalar('id', '0');
        if ($id > 0) {
            CC_Repo::set_active_year($id);
        }
        $this->redirect_admin('cc_years');
    }

    public function render_terms(): void {
        $this->require_manage();
        $terms = CC_Repo::list_terms();
        $active = CC_Repo::get_active_term();
        $nonce = wp_create_nonce('cc_terms');

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Trimestres', 'conseil-classe') . '</h1>';
        echo '<section class="cc-admin-section">';
        echo '<h2>' . esc_html__('Trimestre actif', 'conseil-classe') . '</h2>';
        echo '<p>' . esc_html__('Le plugin crée par défaut T1/T2/T3. Ici, vous choisissez le trimestre actif.', 'conseil-classe') . '</p>';

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . esc_html__('Nom', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Actif', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Action', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($terms as $t) {
            $isActive = $active && (int) $active['id'] === (int) $t['id'];
            echo '<tr>';
            echo '<td>' . esc_html($t['nom']) . '</td>';
            echo '<td>' . ($isActive ? '<strong>' . esc_html__('Oui', 'conseil-classe') . '</strong>' : esc_html__('Non', 'conseil-classe')) . '</td>';
            echo '<td>';
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            echo '<input type="hidden" name="action" value="cc_term_set_active" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $t['id']) . '" />';
            submit_button(__('Activer', 'conseil-classe'), 'secondary', 'submit', false);
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</section>';
        echo '</div>';
    }

    public function handle_term_set_active(): void {
        $this->require_manage();
        check_admin_referer('cc_terms');
        $id = (int) $this->post_scalar('id', '0');
        if ($id > 0) {
            CC_Repo::set_active_term($id);
        }
        $this->redirect_admin('cc_terms');
    }

    public function render_classes(): void {
        $this->require_manage();
        $year = CC_Repo::get_active_year();
        $nonce = wp_create_nonce('cc_classes');

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Classes', 'conseil-classe') . '</h1>';

        if (!$year) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Définissez d’abord une année active.', 'conseil-classe') . '</p></div>';
            echo '</div>';
            return;
        }

        echo '<p><strong>' . esc_html__('Année active:', 'conseil-classe') . '</strong> ' . esc_html($year['nom']) . '</p>';
        if ($this->get_scalar('bulk_deleted') !== '') {
            echo '<div class="notice notice-success"><p>' . esc_html(sprintf(
                /* translators: %d: number of deleted classes */
                __('%d classe(s) supprimée(s) pour l’année active.', 'conseil-classe'),
                (int) $this->get_scalar('bulk_deleted', '0')
            )) . '</p></div>';
        }

        echo '<h2>' . esc_html__('Import / Export (CSV)', 'conseil-classe') . '</h2>';
        echo '<p>';
        echo '<a class="button button-primary" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_classes_export_csv'), 'cc_classes_export_csv')) . '">' . esc_html__('Export classes (CSV)', 'conseil-classe') . '</a> ';
        echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_classes_download_template'), 'cc_classes_download_template')) . '">' . esc_html__('Modèle import (CSV)', 'conseil-classe') . '</a>';
        echo '</p>';
        if ($this->get_scalar('imported') !== '' || $this->get_scalar('updated') !== '' || $this->get_scalar('import_errors') !== '') {
            echo '<div class="notice notice-success"><p>' . esc_html(sprintf(
                /* translators: 1: created classes, 2: updated classes, 3: import errors */
                __('Import classes terminé: %1$d créé(s), %2$d mis à jour, %3$d erreur(s).', 'conseil-classe'),
                (int) $this->get_scalar('imported', '0'),
                (int) $this->get_scalar('updated', '0'),
                (int) $this->get_scalar('import_errors', '0')
            )) . '</p></div>';
        }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="cc_classes_import_csv" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p><input type="file" name="csv_file" accept=".csv,text/csv" required /> ';
        submit_button(__('Import', 'conseil-classe'), 'secondary', 'submit', false);
        echo ' <a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_classes_download_template'), 'cc_classes_download_template')) . '">' . esc_html__('Télécharger le modèle', 'conseil-classe') . '</a>';
        echo '</p></form>';

        echo '<h2>' . esc_html__('Ajouter une classe', 'conseil-classe') . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="cc_class_create" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        $this->text('nom', __('Nom (ex: 6A)', 'conseil-classe'), '');
        $this->text('niveau', __('Niveau (ex: 6eme)', 'conseil-classe'), '');
        submit_button(__('Créer', 'conseil-classe'), 'primary', 'submit', false);
        echo '</form>';

        $classes = CC_Repo::list_classes_for_year((int) $year['id']);

        echo '<hr />';
        echo '<h2>' . esc_html__('Liste', 'conseil-classe') . '</h2>';
        echo '<table class="widefat striped"><thead><tr>';
        echo '<th><input type="checkbox" data-cc-toggle-all="classes" /></th>';
        echo '<th>' . esc_html__('Nom', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Niveau', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Action', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($classes as $c) {
            echo '<tr>';
            echo '<td><input type="checkbox" name="ids[]" value="' . esc_attr((string) $c['id']) . '" data-cc-bulk-item="classes" form="cc-classes-bulk-form" /></td>';
            echo '<td>' . esc_html($c['nom']) . '</td>';
            echo '<td>' . esc_html($c['niveau']) . '</td>';
            echo '<td>';
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Supprimer cette classe ?\');">';
            echo '<input type="hidden" name="action" value="cc_class_delete" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $c['id']) . '" />';
            submit_button(__('Supprimer', 'conseil-classe'), 'delete', 'submit', false);
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        if (!$classes) {
            echo '<tr><td colspan="4">' . esc_html__('Aucune classe.', 'conseil-classe') . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '<form id="cc-classes-bulk-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Supprimer les classes sélectionnées ?\');">';
        echo '<input type="hidden" name="action" value="cc_classes_bulk_delete" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p>';
        submit_button(__('Supprimer la sélection', 'conseil-classe'), 'delete', 'submit', false);
        echo '</p></form>';
        $this->render_bulk_toggle_script('classes');
        echo '</div>';
    }

    public function handle_class_create(): void {
        $this->require_manage();
        check_admin_referer('cc_classes');
        $year = CC_Repo::get_active_year();
        if (!$year) {
            $this->redirect_admin('cc_classes');
        }
        $nom = sanitize_text_field($this->post_scalar('nom'));
        $niveau = sanitize_text_field($this->post_scalar('niveau'));
        if ($nom !== '' && $niveau !== '') {
            CC_Repo::create_class((int) $year['id'], $nom, $niveau);
        }
        $this->redirect_admin('cc_classes');
    }

    public function handle_class_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_classes');
        $id = (int) $this->post_scalar('id', '0');
        if ($id > 0) {
            CC_Repo::delete_class($id);
        }
        $this->redirect_admin('cc_classes');
    }

    public function handle_classes_bulk_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_classes');
        $ids = $this->post_ids_array('ids');
        foreach ($ids as $id) {
            CC_Repo::delete_class($id);
        }
        $this->redirect_admin('cc_classes', ['bulk_deleted' => count($ids)]);
    }

    public function render_councils(): void {
        $this->require_manage();
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        $nonce = wp_create_nonce('cc_councils');

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Conseils', 'conseil-classe') . '</h1>';

        if (!$year || !$term) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Définissez une année active et un trimestre actif.', 'conseil-classe') . '</p></div>';
            echo '</div>';
            return;
        }

        echo '<p><strong>' . esc_html__('Contexte:', 'conseil-classe') . '</strong> ' . esc_html($year['nom']) . ' - ' . esc_html($term['nom']) . '</p>';
        if ($this->get_scalar('bulk_deleted') !== '') {
            echo '<div class="notice notice-success"><p>' . esc_html(sprintf(
                /* translators: %d: number of deleted councils */
                __('%d conseil(s) supprimé(s) pour le trimestre actif.', 'conseil-classe'),
                (int) $this->get_scalar('bulk_deleted', '0')
            )) . '</p></div>';
        }

        echo '<h2>' . esc_html__('Import / Export (CSV)', 'conseil-classe') . '</h2>';
        echo '<p>';
        echo '<a class="button button-primary" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_councils_export_csv'), 'cc_councils_export_csv')) . '">' . esc_html__('Export planning (CSV)', 'conseil-classe') . '</a> ';
        echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_councils_download_template'), 'cc_councils_download_template')) . '">' . esc_html__('Modèle import (CSV)', 'conseil-classe') . '</a>';
        echo '</p>';
        if ($this->get_scalar('imported') !== '' || $this->get_scalar('ignored') !== '' || $this->get_scalar('import_errors') !== '') {
            echo '<div class="notice notice-success"><p>' . esc_html(sprintf(
                /* translators: 1: created councils, 2: ignored rows, 3: import errors */
                __('Import conseils terminé: %1$d créé(s), %2$d ignoré(s), %3$d erreur(s).', 'conseil-classe'),
                (int) $this->get_scalar('imported', '0'),
                (int) $this->get_scalar('ignored', '0'),
                (int) $this->get_scalar('import_errors', '0')
            )) . '</p></div>';
        }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="cc_councils_import_csv" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p><input type="file" name="csv_file" accept=".csv,text/csv" required /> ';
        submit_button(__('Import planning', 'conseil-classe'), 'secondary', 'submit', false);
        echo ' <a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_councils_download_template'), 'cc_councils_download_template')) . '">' . esc_html__('Télécharger le modèle', 'conseil-classe') . '</a>';
        echo '</p></form>';

        $classes = CC_Repo::list_classes_for_year((int) $year['id']);
        if (!$classes) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Ajoutez d’abord des classes pour cette année.', 'conseil-classe') . '</p></div>';
        } else {
            echo '<h2>' . esc_html__('Planifier un conseil', 'conseil-classe') . '</h2>';
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            echo '<input type="hidden" name="action" value="cc_council_create" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';

            echo '<p><label>' . esc_html__('Classe', 'conseil-classe') . '</label><br />';
            echo '<select name="class_id" required>';
            foreach ($classes as $c) {
                $label = $c['nom'] . ' (' . $c['niveau'] . ')';
                echo '<option value="' . esc_attr((string) $c['id']) . '">' . esc_html($label) . '</option>';
            }
            echo '</select></p>';

            echo '<p><label>' . esc_html__('Date', 'conseil-classe') . '</label><br />';
            echo '<input type="date" name="date_conseil" required /></p>';

            echo '<p><label>' . esc_html__('Heure début', 'conseil-classe') . '</label><br />';
            echo '<input type="time" name="heure_debut" required /></p>';

            echo '<p><label>' . esc_html__('Heure fin (optionnel)', 'conseil-classe') . '</label><br />';
            echo '<input type="time" name="heure_fin" /></p>';

            $this->text('salle_conseil', __('Salle', 'conseil-classe'), '');
            $this->text('president_conseil', __('Président du conseil', 'conseil-classe'), '');

            submit_button(__('Créer', 'conseil-classe'), 'primary', 'submit', false);
            echo '</form>';
        }

        $councils = CC_Repo::list_councils((int) $year['id'], (int) $term['id']);
        $settings = CC_Repo::get_settings();
        $maxParents = (int) ($settings['max_parents_per_conseil'] ?? 2);

        echo '<hr />';
        echo '<h2>' . esc_html__('Liste', 'conseil-classe') . '</h2>';
        echo '<table class="widefat striped"><thead><tr>';
        echo '<th><input type="checkbox" data-cc-toggle-all="councils" /></th>';
        echo '<th>' . esc_html__('Classe', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Date', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Heure', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Salle', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Président', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Inscrits', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Actions', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($councils as $co) {
            $count = CC_Repo::count_registrations_for_council((int) $co['id']);
            $heure = substr((string) $co['heure_debut'], 0, 5);
            if (!empty($co['heure_fin'])) {
                $heure .= ' - ' . substr((string) $co['heure_fin'], 0, 5);
            }
            echo '<tr>';
            echo '<td><input type="checkbox" name="ids[]" value="' . esc_attr((string) $co['id']) . '" data-cc-bulk-item="councils" form="cc-councils-bulk-form" /></td>';
            echo '<td>' . esc_html($co['classe_nom'] . ' (' . $co['classe_niveau'] . ')') . '</td>';
            echo '<td>' . esc_html(mysql2date('d/m/Y', $co['date_conseil'])) . '</td>';
            echo '<td>' . esc_html($heure) . '</td>';
            echo '<td>' . esc_html($co['salle_conseil']) . '</td>';
            echo '<td>' . esc_html($co['president_conseil']) . '</td>';
            echo '<td>' . esc_html($count . ' / ' . $maxParents) . '</td>';
            echo '<td>';
            echo '<a class="button" href="' . esc_url(admin_url('admin.php?page=cc_registrations&council_id=' . (int) $co['id'])) . '">' . esc_html__('Inscriptions', 'conseil-classe') . '</a> ';
            echo '<form style="display:inline" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Supprimer ce conseil ?\');">';
            echo '<input type="hidden" name="action" value="cc_council_delete" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $co['id']) . '" />';
            submit_button(__('Supprimer', 'conseil-classe'), 'delete', 'submit', false);
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        if (!$councils) {
            echo '<tr><td colspan="8">' . esc_html__('Aucun conseil planifié.', 'conseil-classe') . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '<form id="cc-councils-bulk-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Supprimer les conseils sélectionnés ?\');">';
        echo '<input type="hidden" name="action" value="cc_councils_bulk_delete" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p>';
        submit_button(__('Supprimer la sélection', 'conseil-classe'), 'delete', 'submit', false);
        echo '</p></form>';
        $this->render_bulk_toggle_script('councils');

        echo '</div>';
    }

    // =========================
    // CSV: settings (etablissement + association)
    // =========================
    public function handle_term_export_excel(): void {
        $this->require_manage();
        check_admin_referer('cc_term_export_excel');
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            wp_die(esc_html__('Année/trimestre actif manquant.', 'conseil-classe'));
        }

        $yearId = (int) $year['id'];
        $termId = (int) $term['id'];
        $councils = CC_Repo::list_councils($yearId, $termId);
        $registrations = CC_Repo::list_registrations_for_year_term($yearId, $termId);
        $reports = CC_Repo::list_reports($yearId, $termId);
        $parents = $this->collect_term_parents($councils, $registrations, $reports);
        $classes = $this->collect_term_classes($councils);

        $worksheets = [
            'meta' => [
                'headers' => ['key', 'value'],
                'rows' => [
                    ['format', 'cc_term_bundle_v1'],
                    ['year_nom', (string) $year['nom']],
                    ['term_nom', (string) $term['nom']],
                    ['generated_at', gmdate('Y-m-d H:i:s')],
                ],
            ],
            'parents' => [
                'headers' => ['nom', 'prenom', 'email', 'telephone', 'code_acces'],
                'rows' => array_map(static function (array $parent): array {
                    return [
                        $parent['nom'] ?? '',
                        $parent['prenom'] ?? '',
                        $parent['email'] ?? '',
                        $parent['telephone'] ?? '',
                        $parent['code_acces'] ?? '',
                    ];
                }, $parents),
            ],
            'classes' => [
                'headers' => ['nom', 'niveau'],
                'rows' => array_map(static function (array $class): array {
                    return [
                        $class['nom'] ?? '',
                        $class['niveau'] ?? '',
                    ];
                }, $classes),
            ],
            'plannings' => [
                'headers' => ['classe_nom', 'classe_niveau', 'date_conseil', 'heure_debut', 'heure_fin', 'salle_conseil', 'president_conseil'],
                'rows' => array_map(static function (array $council): array {
                    return [
                        $council['classe_nom'] ?? '',
                        $council['classe_niveau'] ?? '',
                        $council['date_conseil'] ?? '',
                        substr((string) ($council['heure_debut'] ?? ''), 0, 5),
                        !empty($council['heure_fin']) ? substr((string) $council['heure_fin'], 0, 5) : '',
                        $council['salle_conseil'] ?? '',
                        $council['president_conseil'] ?? '',
                    ];
                }, $councils),
            ],
            'inscrits' => [
                'headers' => ['parent_email', 'parent_nom', 'parent_prenom', 'classe_nom', 'date_conseil', 'presente', 'commentaire'],
                'rows' => array_map(static function (array $registration): array {
                    return [
                        $registration['parent_email'] ?? '',
                        $registration['parent_nom'] ?? '',
                        $registration['parent_prenom'] ?? '',
                        $registration['classe_nom'] ?? '',
                        $registration['date_conseil'] ?? '',
                        (string) (int) ($registration['presente'] ?? 0),
                        $registration['commentaire'] ?? '',
                    ];
                }, $registrations),
            ],
            'cr' => [
                'headers' => [
                    'classe_nom', 'date_conseil', 'nom_parent', 'prenom_parent', 'email_parent',
                    'profs_participants', 'delegue_eleve_1_nom', 'delegue_eleve_1_prenom', 'delegue_eleve_2_nom', 'delegue_eleve_2_prenom',
                    'delegue_parent_1_nom', 'delegue_parent_1_prenom', 'delegue_parent_2_nom', 'delegue_parent_2_prenom',
                    'remarque_principal', 'remarque_prof_principal', 'remarques_autres_profs', 'remarques_eleves_delegues', 'remarques_parents',
                    'nb_felicitations', 'nb_encouragements', 'nb_compliments', 'nb_mise_en_garde_travail', 'nb_mise_en_garde_comportement', 'valide',
                ],
                'rows' => array_map(static function (array $report): array {
                    return [
                        $report['classe_nom'] ?? '',
                        $report['date_conseil'] ?? '',
                        $report['nom_parent'] ?? '',
                        $report['prenom_parent'] ?? '',
                        $report['email_parent'] ?? '',
                        $report['profs_participants'] ?? '',
                        $report['delegue_eleve_1_nom'] ?? '',
                        $report['delegue_eleve_1_prenom'] ?? '',
                        $report['delegue_eleve_2_nom'] ?? '',
                        $report['delegue_eleve_2_prenom'] ?? '',
                        $report['delegue_parent_1_nom'] ?? '',
                        $report['delegue_parent_1_prenom'] ?? '',
                        $report['delegue_parent_2_nom'] ?? '',
                        $report['delegue_parent_2_prenom'] ?? '',
                        $report['remarque_principal'] ?? '',
                        $report['remarque_prof_principal'] ?? '',
                        $report['remarques_autres_profs'] ?? '',
                        $report['remarques_eleves_delegues'] ?? '',
                        $report['remarques_parents'] ?? '',
                        (string) (int) ($report['nb_felicitations'] ?? 0),
                        (string) (int) ($report['nb_encouragements'] ?? 0),
                        (string) (int) ($report['nb_compliments'] ?? 0),
                        (string) (int) ($report['nb_mise_en_garde_travail'] ?? 0),
                        (string) (int) ($report['nb_mise_en_garde_comportement'] ?? 0),
                        (string) (int) ($report['valide'] ?? 0),
                    ];
                }, $reports),
            ],
        ];

        $filename = 'trimestre_' . sanitize_file_name((string) $year['nom'] . '_' . (string) $term['nom']) . '_' . gmdate('Ymd') . '.xls';
        $this->excel_send_headers($filename);
        echo $this->excel_render_workbook($worksheets);
        exit;
    }

    public function handle_term_import_excel(): void {
        $this->require_manage();
        check_admin_referer('cc_save_settings');
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            $this->redirect_admin('cc_settings');
        }
        $tmpPath = $this->uploaded_tmp_path('excel_file');
        if ($tmpPath === '') {
            $this->redirect_admin('cc_settings');
        }

        $workbook = $this->excel_parse_workbook($tmpPath);
        if (!$workbook || empty($workbook['meta']['rows'])) {
            $this->redirect_admin('cc_settings', ['term_bundle_errors' => 1]);
        }

        $meta = $this->term_meta_to_assoc($workbook['meta']['rows']);
        $metaYear = $this->normalize_term_bundle_label((string) ($meta['year_nom'] ?? ''));
        $activeYear = $this->normalize_term_bundle_label((string) $year['nom']);
        $metaTerm = $this->normalize_term_bundle_label((string) ($meta['term_nom'] ?? ''));
        $activeTerm = $this->normalize_term_bundle_label((string) $term['nom']);
        if (($meta['format'] ?? '') !== 'cc_term_bundle_v1'
            || $metaYear !== $activeYear
            || $metaTerm !== $activeTerm) {
            $this->redirect_admin('cc_settings', ['term_bundle_context_error' => 1]);
        }

        $yearId = (int) $year['id'];
        $termId = (int) $term['id'];
        $imported = 0;
        $ignored = 0;
        $errors = 0;

        foreach (($workbook['parents']['rows'] ?? []) as $row) {
            $email = CC_Utils::normalize_email((string) ($row['email'] ?? ''));
            if ($email === '' || CC_Repo::get_parent_by_email($email)) {
                $ignored++;
                continue;
            }
            $nom = sanitize_text_field((string) ($row['nom'] ?? ''));
            $prenom = sanitize_text_field((string) ($row['prenom'] ?? ''));
            if ($nom === '' || $prenom === '') {
                $errors++;
                continue;
            }
            $plainPw = $this->resolve_parent_password_plain((string) ($row['code_acces'] ?? ''));
            if (is_wp_error($plainPw)) {
                $errors++;
                continue;
            }
            if (CC_Repo::access_code_exists((string) $plainPw)) {
                $errors++;
                continue;
            }
            $wpAttached = $this->create_or_attach_wp_parent_user($email, $prenom, $nom, null, (string) $plainPw, false);
            if (is_wp_error($wpAttached)) {
                $errors++;
                continue;
            }
            CC_Repo::create_parent([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => sanitize_text_field((string) ($row['telephone'] ?? '')),
                'code_acces' => (string) $plainPw,
                'wp_user_id' => (int) $wpAttached,
            ]);
            $imported++;
        }

        foreach (($workbook['classes']['rows'] ?? []) as $row) {
            $nom = sanitize_text_field((string) ($row['nom'] ?? ''));
            $niveau = sanitize_text_field((string) ($row['niveau'] ?? ''));
            if ($nom === '' || $niveau === '') {
                $errors++;
                continue;
            }
            if (CC_Repo::get_class_by_nom_for_year($yearId, $nom)) {
                $ignored++;
                continue;
            }
            CC_Repo::create_class($yearId, $nom, $niveau);
            $imported++;
        }

        foreach (($workbook['plannings']['rows'] ?? []) as $row) {
            $classeNom = sanitize_text_field((string) ($row['classe_nom'] ?? ''));
            $classeNiveau = sanitize_text_field((string) ($row['classe_niveau'] ?? ''));
            $date = sanitize_text_field((string) ($row['date_conseil'] ?? ''));
            $heureDebut = sanitize_text_field((string) ($row['heure_debut'] ?? ''));
            $heureFin = sanitize_text_field((string) ($row['heure_fin'] ?? ''));
            if ($classeNom === '' || $classeNiveau === '' || $date === '' || $heureDebut === '') {
                $errors++;
                continue;
            }
            $classe = CC_Repo::get_class_by_nom_for_year($yearId, $classeNom);
            $classId = $classe ? (int) $classe['id'] : CC_Repo::create_class($yearId, $classeNom, $classeNiveau);
            if ($classe) {
                $existingCouncil = CC_Repo::get_council_by_class_date($yearId, $termId, $classeNom, $date);
                if ($existingCouncil) {
                    $ignored++;
                    continue;
                }
            }
            CC_Repo::create_council([
                'term_id' => $termId,
                'year_id' => $yearId,
                'class_id' => $classId,
                'date_conseil' => $date,
                'heure_debut' => strlen($heureDebut) === 5 ? $heureDebut . ':00' : $heureDebut,
                'heure_fin' => $heureFin !== '' ? (strlen($heureFin) === 5 ? $heureFin . ':00' : $heureFin) : null,
                'salle_conseil' => sanitize_text_field((string) ($row['salle_conseil'] ?? '')),
                'president_conseil' => sanitize_text_field((string) ($row['president_conseil'] ?? '')),
            ]);
            $imported++;
        }

        foreach (($workbook['inscrits']['rows'] ?? []) as $row) {
            $email = CC_Utils::normalize_email((string) ($row['parent_email'] ?? ''));
            $classeNom = sanitize_text_field((string) ($row['classe_nom'] ?? ''));
            $date = sanitize_text_field((string) ($row['date_conseil'] ?? ''));
            if ($email === '' || $classeNom === '' || $date === '') {
                $errors++;
                continue;
            }
            $parent = CC_Repo::get_parent_by_email($email);
            $council = CC_Repo::get_council_by_class_date($yearId, $termId, $classeNom, $date);
            if (!$parent || !$council) {
                $errors++;
                continue;
            }
            CC_Repo::upsert_registration(
                (int) $council['id'],
                (int) $parent['id'],
                max(0, (int) ($row['presente'] ?? 0)) > 0 ? 1 : 0,
                sanitize_textarea_field((string) ($row['commentaire'] ?? ''))
            );
            $imported++;
        }

        foreach (($workbook['cr']['rows'] ?? []) as $row) {
            $classeNom = sanitize_text_field((string) ($row['classe_nom'] ?? ''));
            $date = sanitize_text_field((string) ($row['date_conseil'] ?? ''));
            if ($classeNom === '' || $date === '') {
                $errors++;
                continue;
            }
            $council = CC_Repo::get_council_by_class_date($yearId, $termId, $classeNom, $date);
            if (!$council) {
                $errors++;
                continue;
            }
            $data = [
                'nom_parent' => sanitize_text_field((string) ($row['nom_parent'] ?? '')),
                'prenom_parent' => sanitize_text_field((string) ($row['prenom_parent'] ?? '')),
                'email_parent' => sanitize_email((string) ($row['email_parent'] ?? '')),
                'profs_participants' => sanitize_textarea_field((string) ($row['profs_participants'] ?? '')),
                'delegue_eleve_1_nom' => sanitize_text_field((string) ($row['delegue_eleve_1_nom'] ?? '')),
                'delegue_eleve_1_prenom' => sanitize_text_field((string) ($row['delegue_eleve_1_prenom'] ?? '')),
                'delegue_eleve_2_nom' => sanitize_text_field((string) ($row['delegue_eleve_2_nom'] ?? '')),
                'delegue_eleve_2_prenom' => sanitize_text_field((string) ($row['delegue_eleve_2_prenom'] ?? '')),
                'delegue_parent_1_nom' => sanitize_text_field((string) ($row['delegue_parent_1_nom'] ?? '')),
                'delegue_parent_1_prenom' => sanitize_text_field((string) ($row['delegue_parent_1_prenom'] ?? '')),
                'delegue_parent_2_nom' => sanitize_text_field((string) ($row['delegue_parent_2_nom'] ?? '')),
                'delegue_parent_2_prenom' => sanitize_text_field((string) ($row['delegue_parent_2_prenom'] ?? '')),
                'remarque_principal' => sanitize_textarea_field((string) ($row['remarque_principal'] ?? '')),
                'remarque_prof_principal' => sanitize_textarea_field((string) ($row['remarque_prof_principal'] ?? '')),
                'remarques_autres_profs' => sanitize_textarea_field((string) ($row['remarques_autres_profs'] ?? '')),
                'remarques_eleves_delegues' => sanitize_textarea_field((string) ($row['remarques_eleves_delegues'] ?? '')),
                'remarques_parents' => sanitize_textarea_field((string) ($row['remarques_parents'] ?? '')),
                'nb_felicitations' => max(0, (int) ($row['nb_felicitations'] ?? 0)),
                'nb_encouragements' => max(0, (int) ($row['nb_encouragements'] ?? 0)),
                'nb_compliments' => max(0, (int) ($row['nb_compliments'] ?? 0)),
                'nb_mise_en_garde_travail' => max(0, (int) ($row['nb_mise_en_garde_travail'] ?? 0)),
                'nb_mise_en_garde_comportement' => max(0, (int) ($row['nb_mise_en_garde_comportement'] ?? 0)),
            ];
            $existing = CC_Repo::get_report_by_council((int) $council['id']);
            if ($existing) {
                CC_Repo::update_report_content((int) $existing['id'], $data);
            } else {
                $data['council_id'] = (int) $council['id'];
                $data['valide'] = max(0, (int) ($row['valide'] ?? 0)) > 0 ? 1 : 0;
                CC_Repo::create_report($data);
            }
            $imported++;
        }

        $this->redirect_admin('cc_settings', [
            'term_bundle_imported' => $imported,
            'term_bundle_ignored' => $ignored,
            'term_bundle_errors' => $errors,
        ]);
    }

    public function handle_settings_export_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_settings_export_csv');
        $s = CC_Repo::get_settings();

        $filename = 'parametres_etablissement_association_' . gmdate('Ymd') . '.csv';
        $this->csv_send_headers($filename);

        $out = fopen('php://output', 'w');
        $headers = [
            'nom_etablissement', 'adresse_etablissement', 'telephone_etablissement', 'email_etablissement', 'site_web_etablissement',
            'nom_directeur', 'nom_principal',
            'nom_association_parents', 'adresse_association_parents', 'telephone_association_parents', 'email_association_parents', 'site_web_association_parents',
            'president_association', 'vice_president_association', 'secretaire_association', 'tresorier_association',
            'max_parents_per_conseil',
        ];
        $this->csv_fputcsv($out, $headers);
        $row = [];
        foreach ($headers as $h) {
            $row[] = $s[$h] ?? '';
        }
        $this->csv_fputcsv($out, $row);
        fclose($out);
        exit;
    }

    public function handle_settings_download_template(): void {
        $this->require_manage();
        check_admin_referer('cc_settings_download_template');
        $filename = 'modele_parametres_etablissement_association.csv';
        $this->csv_send_headers($filename);
        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, [
            'nom_etablissement', 'adresse_etablissement', 'telephone_etablissement', 'email_etablissement', 'site_web_etablissement',
            'nom_directeur', 'nom_principal',
            'nom_association_parents', 'adresse_association_parents', 'telephone_association_parents', 'email_association_parents', 'site_web_association_parents',
            'president_association', 'vice_president_association', 'secretaire_association', 'tresorier_association',
            'max_parents_per_conseil',
        ]);
        $this->csv_fputcsv($out, ['Mon établissement', "1 rue de l'école", '0123456789', 'contact@exemple.fr', '', 'M. Directeur', 'Mme Principale', 'APE', '', '', 'ape@exemple.fr', '', 'Président', '', '', '', '2']);
        fclose($out);
        exit;
    }

    public function handle_settings_import_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_save_settings');
        $tmpPath = $this->uploaded_tmp_path('csv_file');
        if ($tmpPath === '') {
            $this->redirect_admin('cc_settings');
        }
        $delimiter = ',';
        $handle = $this->csv_open_import_handle($tmpPath, $delimiter);
        if (!$handle) {
            $this->redirect_admin('cc_settings');
        }
        $headers = fgetcsv($handle, 0, $delimiter);
        $values = fgetcsv($handle, 0, $delimiter);
        fclose($handle);
        if (!is_array($headers) || !is_array($values)) {
            $this->redirect_admin('cc_settings');
        }
        $data = [];
        foreach ($headers as $i => $h) {
            $key = sanitize_key((string) $h);
            $data[$key] = (string) ($values[$i] ?? '');
        }
        // Sanitization proche de handle_save_settings
        $update = [
            'nom_etablissement' => sanitize_text_field($data['nom_etablissement'] ?? ''),
            'adresse_etablissement' => CC_Utils::sanitize_textarea($data['adresse_etablissement'] ?? ''),
            'telephone_etablissement' => sanitize_text_field($data['telephone_etablissement'] ?? ''),
            'email_etablissement' => sanitize_email($data['email_etablissement'] ?? ''),
            'site_web_etablissement' => esc_url_raw($data['site_web_etablissement'] ?? ''),
            'nom_directeur' => sanitize_text_field($data['nom_directeur'] ?? ''),
            'nom_principal' => sanitize_text_field($data['nom_principal'] ?? ''),
            'nom_association_parents' => sanitize_text_field($data['nom_association_parents'] ?? ''),
            'adresse_association_parents' => CC_Utils::sanitize_textarea($data['adresse_association_parents'] ?? ''),
            'telephone_association_parents' => sanitize_text_field($data['telephone_association_parents'] ?? ''),
            'email_association_parents' => sanitize_email($data['email_association_parents'] ?? ''),
            'site_web_association_parents' => esc_url_raw($data['site_web_association_parents'] ?? ''),
            'president_association' => sanitize_text_field($data['president_association'] ?? ''),
            'vice_president_association' => sanitize_text_field($data['vice_president_association'] ?? ''),
            'secretaire_association' => sanitize_text_field($data['secretaire_association'] ?? ''),
            'tresorier_association' => sanitize_text_field($data['tresorier_association'] ?? ''),
            'max_parents_per_conseil' => max(1, (int) ($data['max_parents_per_conseil'] ?? 2)),
        ];
        CC_Repo::update_settings($update);
        $this->redirect_admin('cc_settings', ['updated' => 1]);
    }

    // =========================
    // CSV: classes (nom + niveau) pour l'année active
    // =========================
    public function handle_classes_export_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_classes_export_csv');
        $year = CC_Repo::get_active_year();
        if (!$year) {
            wp_die(esc_html__('Aucune année active.', 'conseil-classe'));
        }
        $classes = CC_Repo::list_classes_for_year((int) $year['id']);
        $filename = 'classes_' . sanitize_file_name($year['nom']) . '_' . gmdate('Ymd') . '.csv';
        $this->csv_send_headers($filename);
        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, ['nom', 'niveau']);
        foreach ($classes as $c) {
            $this->csv_fputcsv($out, [$c['nom'], $c['niveau']]);
        }
        fclose($out);
        exit;
    }

    public function handle_classes_download_template(): void {
        $this->require_manage();
        check_admin_referer('cc_classes_download_template');
        $filename = 'modele_classes.csv';
        $this->csv_send_headers($filename);
        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, ['nom', 'niveau']);
        $this->csv_fputcsv($out, ['6A', '6eme']);
        $this->csv_fputcsv($out, ['6B', '6eme']);
        fclose($out);
        exit;
    }

    public function handle_classes_import_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_classes');
        $year = CC_Repo::get_active_year();
        if (!$year) {
            $this->redirect_admin('cc_classes');
        }
        $tmpPath = $this->uploaded_tmp_path('csv_file');
        if ($tmpPath === '') {
            $this->redirect_admin('cc_classes');
        }
        $delimiter = ',';
        $handle = $this->csv_open_import_handle($tmpPath, $delimiter);
        if (!$handle) {
            $this->redirect_admin('cc_classes');
        }
        $headers = fgetcsv($handle, 0, $delimiter);
        $created = 0;
        $updated = 0;
        $errors = 0;
        $yearId = (int) $year['id'];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!$headers || !is_array($headers)) {
                $errors++;
                continue;
            }
            $map = [];
            foreach ($headers as $i => $h) {
                $map[sanitize_key((string) $h)] = (string) ($row[$i] ?? '');
            }
            $nom = sanitize_text_field($map['nom'] ?? '');
            $niveau = sanitize_text_field($map['niveau'] ?? '');
            if ($nom === '' || $niveau === '') {
                $errors++;
                continue;
            }
            $exists = CC_Repo::get_class_by_nom_for_year($yearId, $nom);
            CC_Repo::upsert_class($yearId, $nom, $niveau);
            if ($exists) {
                $updated++;
            } else {
                $created++;
            }
        }
        fclose($handle);
        $this->redirect_admin('cc_classes', ['imported' => $created, 'updated' => $updated, 'import_errors' => $errors]);
    }

    // =========================
    // CSV: planning conseils (pour année+trimestre actifs)
    // =========================
    public function handle_councils_export_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_councils_export_csv');
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            wp_die(esc_html__('Année/trimestre actif manquant.', 'conseil-classe'));
        }
        $councils = CC_Repo::list_councils((int) $year['id'], (int) $term['id']);
        $filename = 'planning_conseils_' . sanitize_file_name($year['nom'] . '_' . $term['nom']) . '_' . gmdate('Ymd') . '.csv';
        $this->csv_send_headers($filename);
        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, ['classe_nom', 'classe_niveau', 'date_conseil', 'heure_debut', 'heure_fin', 'salle_conseil', 'president_conseil']);
        foreach ($councils as $c) {
            $this->csv_fputcsv($out, [
                $c['classe_nom'] ?? '',
                $c['classe_niveau'] ?? '',
                $c['date_conseil'] ?? '',
                substr((string) ($c['heure_debut'] ?? ''), 0, 5),
                !empty($c['heure_fin']) ? substr((string) $c['heure_fin'], 0, 5) : '',
                $c['salle_conseil'] ?? '',
                $c['president_conseil'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function handle_councils_download_template(): void {
        $this->require_manage();
        check_admin_referer('cc_councils_download_template');
        $filename = 'modele_planning_conseils.csv';
        $this->csv_send_headers($filename);
        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, ['classe_nom', 'classe_niveau', 'date_conseil', 'heure_debut', 'heure_fin', 'salle_conseil', 'president_conseil']);
        $this->csv_fputcsv($out, ['6A', '6eme', '2026-05-20', '18:00', '19:00', 'Salle A101', 'M. Dupont']);
        fclose($out);
        exit;
    }

    public function handle_councils_import_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_councils');
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            $this->redirect_admin('cc_councils');
        }
        $tmpPath = $this->uploaded_tmp_path('csv_file');
        if ($tmpPath === '') {
            $this->redirect_admin('cc_councils');
        }

        global $wpdb;
        $delimiter = ',';
        $handle = $this->csv_open_import_handle($tmpPath, $delimiter);
        if (!$handle) {
            $this->redirect_admin('cc_councils');
        }
        $headers = fgetcsv($handle, 0, $delimiter);
        $created = 0;
        $ignored = 0;
        $errors = 0;
        $yearId = (int) $year['id'];
        $termId = (int) $term['id'];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!$headers || !is_array($headers)) {
                $errors++;
                continue;
            }
            $map = [];
            foreach ($headers as $i => $h) {
                $map[sanitize_key((string) $h)] = (string) ($row[$i] ?? '');
            }

            $classeNom = sanitize_text_field($map['classe_nom'] ?? '');
            $classeNiveau = sanitize_text_field($map['classe_niveau'] ?? '');
            $date = sanitize_text_field($map['date_conseil'] ?? '');
            $hd = sanitize_text_field($map['heure_debut'] ?? '');
            $hf = sanitize_text_field($map['heure_fin'] ?? '');
            $salle = sanitize_text_field($map['salle_conseil'] ?? '');
            $president = sanitize_text_field($map['president_conseil'] ?? '');

            if ($classeNom === '' || $date === '' || $hd === '') {
                $errors++;
                continue;
            }

            // Classe: si absent, on la crée (niveau requis dans ce cas)
            $classe = CC_Repo::get_class_by_nom_for_year($yearId, $classeNom);
            if (!$classe) {
                if ($classeNiveau === '') {
                    $errors++;
                    continue;
                }
                $classId = CC_Repo::upsert_class($yearId, $classeNom, $classeNiveau);
            } else {
                $classId = (int) $classe['id'];
            }

            $wpdb->hide_errors();
            CC_Repo::create_council([
                'term_id' => $termId,
                'year_id' => $yearId,
                'class_id' => $classId,
                'date_conseil' => $date,
                'heure_debut' => (strlen($hd) === 5 ? $hd . ':00' : $hd),
                'heure_fin' => ($hf !== '' ? (strlen($hf) === 5 ? $hf . ':00' : $hf) : null),
                'salle_conseil' => $salle,
                'president_conseil' => $president,
            ]);
            if (!empty($wpdb->last_error)) {
                // Très souvent: contrainte unique (déjà existant)
                $ignored++;
                $wpdb->last_error = '';
            } else {
                $created++;
            }
        }
        fclose($handle);
        $this->redirect_admin('cc_councils', ['imported' => $created, 'ignored' => $ignored, 'import_errors' => $errors]);
    }

    public function handle_council_create(): void {
        $this->require_manage();
        check_admin_referer('cc_councils');
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            $this->redirect_admin('cc_councils');
        }

        $classId = (int) $this->post_scalar('class_id', '0');
        $date = sanitize_text_field($this->post_scalar('date_conseil'));
        $hd = sanitize_text_field($this->post_scalar('heure_debut'));
        $hf = sanitize_text_field($this->post_scalar('heure_fin'));
        $salle = sanitize_text_field($this->post_scalar('salle_conseil'));
        $president = sanitize_text_field($this->post_scalar('president_conseil'));

        if ($classId > 0 && $date !== '' && $hd !== '') {
            CC_Repo::create_council([
                'term_id' => (int) $term['id'],
                'year_id' => (int) $year['id'],
                'class_id' => $classId,
                'date_conseil' => $date,
                'heure_debut' => $hd . ':00',
                'heure_fin' => $hf !== '' ? $hf . ':00' : null,
                'salle_conseil' => $salle,
                'president_conseil' => $president,
            ]);
        }

        $this->redirect_admin('cc_councils');
    }

    public function handle_council_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_councils');
        $id = (int) $this->post_scalar('id', '0');
        if ($id > 0) {
            CC_Repo::delete_council($id);
        }
        $this->redirect_admin('cc_councils');
    }

    public function handle_councils_bulk_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_councils');
        $ids = $this->post_ids_array('ids');
        foreach ($ids as $id) {
            CC_Repo::delete_council($id);
        }
        $this->redirect_admin('cc_councils', ['bulk_deleted' => count($ids)]);
    }

    public function render_parents(): void {
        $this->require_manage();
        $nonce = wp_create_nonce('cc_parents');
        $year = CC_Repo::get_active_year();
        $search = sanitize_text_field($this->get_scalar('s'));
        $profile = sanitize_key($this->get_scalar('cc_profile', 'all'));
        if (!in_array($profile, ['all', 'parent', 'admin'], true)) {
            $profile = 'all';
        }

        $parents = CC_Repo::list_parents($search, $profile);

        $exportParentsUrl = wp_nonce_url(
            add_query_arg(
                ['action' => 'cc_parents_export_csv', 'cc_profile' => $profile],
                admin_url('admin-post.php')
            ),
            'cc_parents_export_csv'
        );

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Parents', 'conseil-classe') . '</h1>';
        if ($year) {
            echo '<p><strong>' . esc_html__('Année active:', 'conseil-classe') . '</strong> ' . esc_html($year['nom']) . '</p>';
        }
        if ($this->get_scalar('bulk_deleted') !== '') {
            echo '<div class="notice notice-success"><p>' . esc_html(sprintf(
                /* translators: %d: number of removed parents */
                __('%d parent(s) retiré(s) de l’année active.', 'conseil-classe'),
                (int) $this->get_scalar('bulk_deleted', '0')
            )) . '</p></div>';
        }

        echo '<p>';
        echo '<a class="button button-primary" href="' . esc_url($exportParentsUrl) . '">' . esc_html__('Export (CSV)', 'conseil-classe') . '</a> ';
        echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_parents_download_template'), 'cc_parents_download_template')) . '">' . esc_html__('Modèle CSV parents', 'conseil-classe') . '</a> ';
        echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_parents_download_template_admins'), 'cc_parents_download_template_admins')) . '">' . esc_html__('Modèle CSV admins conseil', 'conseil-classe') . '</a>';
        echo '</p>';

        echo '<h2>' . esc_html__('Importer des parents (CSV)', 'conseil-classe') . '</h2>';
        echo '<p class="description">' . esc_html__('Colonnes : Nom, Prénom, Email, Téléphone, Mot de passe ou code. Les lignes existantes sont mises à jour (email identique).', 'conseil-classe') . '</p>';
        if ($this->get_scalar('imported') !== '' || $this->get_scalar('import_updated') !== '' || $this->get_scalar('import_errors') !== '') {
            $imported = (int) $this->get_scalar('imported', '0');
            $importUpdated = (int) $this->get_scalar('import_updated', '0');
            $errors = (int) $this->get_scalar('import_errors', '0');
            $suffix = ($this->get_scalar('import_mode') === 'admins')
                ? ' ' . __('(import équipe conseil)', 'conseil-classe')
                : '';
            echo '<div class="notice notice-success"><p>' . esc_html(
                sprintf(
                    /* translators: 1: optional suffix, 2: created parents, 3: updated parents, 4: import errors */
                    __('Import terminé%1$s : %2$d créé(s), %3$d mis à jour, %4$d erreur(s).', 'conseil-classe'),
                    $suffix,
                    $imported,
                    $importUpdated,
                    $errors
                )
            ) . '</p></div>';
        }
        if ($this->get_scalar('user_err') !== '') {
            echo '<div class="notice notice-error"><p>' . esc_html__('Création ou liaison du compte utilisateur impossible. Aucune fiche parent n’a été enregistrée.', 'conseil-classe') . '</p></div>';
        }
        if ($this->get_scalar('pw_err') !== '') {
            echo '<div class="notice notice-error"><p>' . esc_html__('Mot de passe / code invalide (minimum 6 caractères si vous le renseignez).', 'conseil-classe') . '</p></div>';
        }
        if ($this->get_scalar('code_dup') !== '') {
            echo '<div class="notice notice-error"><p>' . esc_html__('Ce mot de passe / code est déjà utilisé par un autre parent.', 'conseil-classe') . '</p></div>';
        }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="cc_parents_import_csv" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p><input type="file" name="csv_file" accept=".csv,text/csv" required /> ';
        submit_button(__('Importer les parents', 'conseil-classe'), 'secondary', 'submit', false);
        echo ' <a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_parents_download_template'), 'cc_parents_download_template')) . '">' . esc_html__('Télécharger le modèle parents', 'conseil-classe') . '</a></p>';
        echo '</form>';

        echo '<h2>' . esc_html__('Importer des comptes admins conseil (CSV)', 'conseil-classe') . '</h2>';
        echo '<p class="description">' . esc_html__('Un fichier distinct, avec une colonne « Rôle » en plus : admin ou super (super réservé aux super-admins du plugin). Les comptes sont créés ou mis à jour.', 'conseil-classe') . '</p>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="cc_parents_import_admins_csv" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p><input type="file" name="csv_file_admins" accept=".csv,text/csv" required /> ';
        submit_button(__('Importer les admins', 'conseil-classe'), 'secondary', 'submit', false);
        echo ' <a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_parents_download_template_admins'), 'cc_parents_download_template_admins')) . '">' . esc_html__('Télécharger le modèle admins conseil', 'conseil-classe') . '</a></p>';
        echo '</form>';

        echo '<h2>' . esc_html__('Ajouter un parent', 'conseil-classe') . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="cc_parent_create" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        $this->text('nom', __('Nom', 'conseil-classe'), '');
        $this->text('prenom', __('Prénom', 'conseil-classe'), '');
        $this->text('email', __('Email', 'conseil-classe'), '');
        $this->text('telephone', __('Téléphone', 'conseil-classe'), '');
        $this->text('code_acces', __('Mot de passe / code connexion (= mot de passe du compte, laissé vide = généré)', 'conseil-classe'), '');
        echo '<p class="description">' . esc_html__('Un compte utilisateur est toujours créé ou relié automatiquement (même adresse email).', 'conseil-classe') . '</p>';
        echo '<p><label>' . esc_html__('Rôle du compte', 'conseil-classe') . '<br />';
        echo '<select name="cc_new_wp_role">';
        foreach (CC_Roles::allowed_roles_for_parent_user_dropdown() as $roleSlug => $roleLabel) {
            echo '<option value="' . esc_attr($roleSlug) . '">' . esc_html($roleLabel) . '</option>';
        }
        echo '</select></label></p>';
        submit_button(__('Créer', 'conseil-classe'), 'primary', 'submit', false);
        echo '</form>';

        echo '<hr />';
        echo '<form method="get" action="' . esc_url(admin_url('admin.php')) . '">';
        echo '<input type="hidden" name="page" value="cc_parents" />';
        echo '<p>';
        echo '<label>' . esc_html__('Profil affiché', 'conseil-classe') . '<br />';
        echo '<select name="cc_profile">';
        $profiles = [
            'all' => __('Tous', 'conseil-classe'),
            'parent' => __('Parents (sans gestion conseil)', 'conseil-classe'),
            'admin' => __('Admins conseil', 'conseil-classe'),
        ];
        foreach ($profiles as $slug => $label) {
            echo '<option value="' . esc_attr($slug) . '"' . selected($profile, $slug, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select></label> ';
        echo '<input type="search" name="s" value="' . esc_attr($search) . '" placeholder="' . esc_attr__('Recherche nom/email…', 'conseil-classe') . '" /> ';
        submit_button(__('Filtrer', 'conseil-classe'), 'secondary', 'submit', false);
        echo '</p></form>';

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th><input type="checkbox" data-cc-toggle-all="parents" /></th>';
        echo '<th>' . esc_html__('Nom', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Email', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Téléphone', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Mot de passe / code', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Compte WP', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Rôle WP', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Actions', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';
        foreach ($parents as $p) {
            echo '<tr>';
            echo '<td><input type="checkbox" name="ids[]" value="' . esc_attr((string) $p['id']) . '" data-cc-bulk-item="parents" form="cc-parents-bulk-form" /></td>';
            echo '<td>' . esc_html($p['prenom'] . ' ' . $p['nom']) . '</td>';
            echo '<td>' . esc_html($p['email']) . '</td>';
            echo '<td>' . esc_html($p['telephone'] ?? '') . '</td>';
            $wid = (int) ($p['wp_user_id'] ?? 0);
            if ($wid > 0 && $this->is_parent_wp_account_privileged($wid)) {
                echo '<td><em>' . esc_html__('Masqué (admin / gestion conseil). Utilisez « Régénérer mot de passe / code » pour définir un nouveau mot de passe.', 'conseil-classe') . '</em></td>';
            } else {
                echo '<td><code>' . esc_html($p['code_acces'] ?? '') . '</code></td>';
            }
            echo '<td>';
            if ($wid > 0) {
                $ulink = get_edit_user_link($wid);
                echo $ulink ? '<a href="' . esc_url($ulink) . '">#' . esc_html((string) $wid) . '</a>' : '#' . esc_html((string) $wid);
            } else {
                echo '<em>' . esc_html__('—', 'conseil-classe') . '</em>';
            }
            echo '</td>';
            echo '<td>';
            if ($wid > 0) {
                echo esc_html($this->format_wp_user_roles($wid));
            } else {
                echo '<em>' . esc_html__('—', 'conseil-classe') . '</em>';
            }
            echo '</td>';
            echo '<td>';
            echo '<form style="display:inline" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return ccAskParentPassword(this);">';
            echo '<input type="hidden" name="action" value="cc_parent_regenerate_code" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $p['id']) . '" />';
            echo '<input type="hidden" name="code_acces_custom" value="" />';
            submit_button(__('Régénérer mot de passe / code', 'conseil-classe'), 'secondary', 'submit', false);
            echo '</form> ';

            echo '<form style="display:inline" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Supprimer ce parent ?\');">';
            echo '<input type="hidden" name="action" value="cc_parent_delete" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $p['id']) . '" />';
            submit_button(__('Supprimer', 'conseil-classe'), 'delete', 'submit', false);
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        if (!$parents) {
            echo '<tr><td colspan="8">' . esc_html__('Aucun parent.', 'conseil-classe') . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '<form id="cc-parents-bulk-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Retirer les parents sélectionnés de l\'année active ?\');">';
        echo '<input type="hidden" name="action" value="cc_parents_bulk_delete" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p>';
        submit_button(__('Retirer la sélection de l’année active', 'conseil-classe'), 'delete', 'submit', false);
        echo '</p></form>';
        $this->render_bulk_toggle_script('parents');

        echo '<script>
function ccAskParentPassword(form) {
  var msg = "Saisissez un nouveau mot de passe / code (min 6 caractères).\\n" +
            "Laissez vide pour générer automatiquement.";
  var val = window.prompt(msg, "");
  if (val === null) return false;
  var input = form.querySelector("input[name=\\"code_acces_custom\\"]");
  if (input) input.value = (val || "").trim();
  return true;
}
</script>';

        echo '</div>';
    }

    public function handle_parent_create(): void {
        $this->require_manage();
        check_admin_referer('cc_parents');
        $year = CC_Repo::get_active_year();
        if (!$year) {
            $this->redirect_admin('cc_parents');
        }

        $nom = sanitize_text_field($this->post_scalar('nom'));
        $prenom = sanitize_text_field($this->post_scalar('prenom'));
        $email = CC_Utils::normalize_email($this->post_scalar('email'));
        $telephone = sanitize_text_field($this->post_scalar('telephone'));

        $wpRole = sanitize_key($this->post_scalar('cc_new_wp_role', CC_Roles::ROLE_PARENT));
        $allowedRoles = array_keys(CC_Roles::allowed_roles_for_parent_user_dropdown());
        if (!in_array($wpRole, $allowedRoles, true)) {
            $wpRole = CC_Roles::ROLE_PARENT;
        }

        if ($nom === '' || $prenom === '' || $email === '') {
            $this->redirect_admin('cc_parents');
        }

        $plainPw = $this->resolve_parent_password_plain($this->post_scalar('code_acces'));
        if (is_wp_error($plainPw)) {
            $this->redirect_admin('cc_parents', ['pw_err' => '1']);
        }

        $existing = CC_Repo::get_parent_by_email($email);
        $existingId = $existing ? (int) $existing['id'] : null;

        if (CC_Repo::access_code_exists((string) $plainPw, $existingId)) {
            $this->redirect_admin('cc_parents', ['code_dup' => '1']);

            return;
        }

        if ($existing && CC_Repo::is_parent_assigned_to_year((int) $existing['id'], (int) $year['id'])) {
            $this->redirect_admin('cc_parents', ['user_err' => '1']);

            return;
        }

        $wpAttached = $this->create_or_attach_wp_parent_user($email, $prenom, $nom, $wpRole, (string) $plainPw, false);
        if (is_wp_error($wpAttached)) {
            $this->redirect_admin('cc_parents', ['user_err' => '1']);

            return;
        }

        if ($existing) {
            CC_Repo::update_parent((int) $existing['id'], [
                'nom' => $nom,
                'prenom' => $prenom,
                'telephone' => $telephone !== '' ? $telephone : null,
                'code_acces' => (string) $plainPw,
                'wp_user_id' => (int) $wpAttached,
            ]);
            CC_Repo::assign_parent_to_year((int) $existing['id'], (int) $year['id']);
        } else {
            CC_Repo::create_parent([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephone !== '' ? $telephone : null,
                'code_acces' => (string) $plainPw,
                'wp_user_id' => (int) $wpAttached,
            ]);
        }

        $this->redirect_admin('cc_parents');
    }

    public function handle_parent_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_parents');
        $id = (int) $this->post_scalar('id', '0');
        $year = CC_Repo::get_active_year();
        if ($id > 0 && $year) {
            CC_Repo::unassign_parent_from_year($id, (int) $year['id']);
        }
        $this->redirect_admin('cc_parents');
    }

    public function handle_parents_bulk_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_parents');
        $year = CC_Repo::get_active_year();
        if (!$year) {
            $this->redirect_admin('cc_parents');
        }
        $ids = $this->post_ids_array('ids');
        foreach ($ids as $id) {
            CC_Repo::unassign_parent_from_year($id, (int) $year['id']);
        }
        $this->redirect_admin('cc_parents', ['bulk_deleted' => count($ids)]);
    }

    public function handle_parent_regenerate_code(): void {
        $this->require_manage();
        check_admin_referer('cc_parents');
        $id = (int) $this->post_scalar('id', '0');
        if ($id > 0) {
            $row = CC_Repo::get_parent($id);
            if ($row) {
                $custom = trim(sanitize_text_field($this->post_scalar('code_acces_custom')));
                if ($custom !== '') {
                    if (strlen($custom) < 6) {
                        $this->redirect_admin('cc_parents', ['pw_err' => '1']);
                    }
                    if (CC_Repo::access_code_exists($custom, $id)) {
                        $this->redirect_admin('cc_parents', ['code_dup' => '1']);
                    }
                    $newCode = $custom;
                } else {
                    $newCode = CC_Utils::generate_access_code([CC_Repo::class, 'access_code_exists']);
                }

                CC_Repo::update_parent($id, ['code_acces' => $newCode]);
                $wid = (int) ($row['wp_user_id'] ?? 0);
                if ($wid > 0 && get_userdata($wid)) {
                    wp_set_password($newCode, $wid);
                }
            }
        }
        $this->redirect_admin('cc_parents');
    }

    public function handle_parents_export_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_parents_export_csv');

        $profile = sanitize_key($this->request_scalar_value('cc_profile', 'all'));
        if (!in_array($profile, ['all', 'parent', 'admin'], true)) {
            $profile = 'all';
        }

        $parents = CC_Repo::list_parents('', $profile);
        $filename = 'parents_export_' . sanitize_file_name($profile) . '_' . gmdate('Ymd') . '.csv';

        $this->csv_send_headers($filename);

        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, ['Nom', 'Prénom', 'Email', 'Téléphone', 'Mot de passe / code (min. 6 car.)']);
        foreach ($parents as $p) {
            $widCsv = (int) ($p['wp_user_id'] ?? 0);
            $pwdOut = '';
            if ($widCsv <= 0 || !$this->is_parent_wp_account_privileged($widCsv)) {
                $pwdOut = (string) ($p['code_acces'] ?? '');
            }
            $this->csv_fputcsv($out, [$p['nom'], $p['prenom'], $p['email'], $p['telephone'] ?? '', $pwdOut]);
        }
        fclose($out);
        exit;
    }

    public function handle_parents_download_template(): void {
        $this->require_manage();
        check_admin_referer('cc_parents_download_template');

        $filename = 'modele_import_parents.csv';
        $this->csv_send_headers($filename);

        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, ['Nom', 'Prénom', 'Email', 'Téléphone', 'Mot de passe ou code']);
        $this->csv_fputcsv($out, ['Dupont', 'Jean', 'jean.dupont@email.fr', '0123456789', 'AB1234CD']);
        $this->csv_fputcsv($out, ['Martin', 'Marie', 'marie.martin@email.fr', '', '']);
        fclose($out);
        exit;
    }

    public function handle_parents_download_template_admins(): void {
        $this->require_manage();
        check_admin_referer('cc_parents_download_template_admins');

        $filename = 'modele_import_admins_conseil.csv';
        $this->csv_send_headers($filename);

        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, ['Nom', 'Prénom', 'Email', 'Téléphone', 'Mot de passe ou code', 'Rôle (admin|super)']);
        $this->csv_fputcsv($out, ['Admin', 'Camille', 'camille.admin@email.fr', '0123456789', 'MJ45az99', 'admin']);
        $this->csv_fputcsv($out, ['Chef', 'Projet', 'chef.projet@email.fr', '', '', 'super']);
        fclose($out);
        exit;
    }

    public function handle_parents_import_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_parents');

        $tmpPath = $this->uploaded_tmp_path('csv_file');
        if ($tmpPath === '') {
            $this->redirect_admin('cc_parents');
        }

        [$created, $updated, $errors] = $this->consume_parents_csv_import($tmpPath, 'parent');

        $this->redirect_admin('cc_parents', [
            'imported' => $created,
            'import_updated' => $updated,
            'import_errors' => $errors,
        ]);
    }

    public function handle_parents_import_admins_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_parents');

        $tmpPath = $this->uploaded_tmp_path('csv_file_admins');
        if ($tmpPath === '') {
            $this->redirect_admin('cc_parents');
        }

        [$created, $updated, $errors] = $this->consume_parents_csv_import($tmpPath, 'admin');

        $this->redirect_admin('cc_parents', [
            'imported' => $created,
            'import_updated' => $updated,
            'import_errors' => $errors,
            'import_mode' => 'admins',
        ]);
    }

    /** @return array{0:int,1:int,2:int} created, updated, errors */
    private function consume_parents_csv_import(string $tmpPath, string $mode): array {
        $delimiter = ',';
        $handle = $this->csv_open_import_handle($tmpPath, $delimiter);
        if (!$handle) {
            return [0, 0, 1];
        }

        $created = 0;
        $updated = 0;
        $errors = 0;
        $first = true;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($first) {
                $first = false;
                $firstCell = strtolower(trim((string) ($row[0] ?? '')));
                if ($firstCell === 'nom' || $firstCell === 'id') {
                    continue;
                }
            }

            $status = $this->upsert_parent_from_csv($row, $mode);
            if ($status === 'error') {
                $errors++;
            } elseif ($status === 'created') {
                $created++;
            } else {
                $updated++;
            }
        }

        fclose($handle);

        return [$created, $updated, $errors];
    }

    public function render_registrations(): void {
        $this->require_manage();
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        $councilId = (int) $this->get_scalar('council_id', '0');
        $nonce = wp_create_nonce('cc_registrations');

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Inscriptions', 'conseil-classe') . '</h1>';

        if (!$year || !$term) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Définissez une année active et un trimestre actif.', 'conseil-classe') . '</p></div>';
            echo '</div>';
            return;
        }

        $councils = CC_Repo::list_councils((int) $year['id'], (int) $term['id']);

    echo '<p><strong>' . esc_html__('Contexte:', 'conseil-classe') . '</strong> ' . esc_html($year['nom']) . ' — ' . esc_html($term['nom']) . '</p>';

    echo '<section class="cc-admin-section">';
    echo '<h2>' . esc_html__('Import / Export (CSV)', 'conseil-classe') . '</h2>';
    echo '<p>';
    echo '<a class="button button-primary" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_registrations_export_csv'), 'cc_registrations_export_csv')) . '">' . esc_html__('Export inscriptions (CSV)', 'conseil-classe') . '</a> ';
    echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_registrations_download_template'), 'cc_registrations_download_template')) . '">' . esc_html__('Modèle import (CSV)', 'conseil-classe') . '</a>';
    echo '</p>';
    if ($this->get_scalar('imported') !== '' || $this->get_scalar('import_errors') !== '') {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf(
            /* translators: 1: created/updated registrations, 2: ignored rows, 3: import errors */
            __('Import inscriptions terminé : %1$d créée(s)/mise(s) à jour, %2$d ignorée(s), %3$d erreur(s).', 'conseil-classe'),
            (int) $this->get_scalar('imported', '0'),
            (int) $this->get_scalar('ignored', '0'),
            (int) $this->get_scalar('import_errors', '0')
        )) . '</p></div>';
    }
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
    echo '<input type="hidden" name="action" value="cc_registrations_import_csv" />';
    echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
    echo '<p><input type="file" name="csv_file" accept=".csv,text/csv" required /> ';
    submit_button(__('Importer', 'conseil-classe'), 'secondary', 'submit', false);
    echo '</p></form>';
    echo '</section>';

    echo '<section class="cc-admin-section">';
    echo '<h2>' . esc_html__('Sélection du conseil', 'conseil-classe') . '</h2>';
    echo '<form method="get" action="' . esc_url(admin_url('admin.php')) . '">';
        echo '<input type="hidden" name="page" value="cc_registrations" />';
        echo '<p><label>' . esc_html__('Conseil', 'conseil-classe') . '</label><br />';
        echo '<select name="council_id">';
        echo '<option value="0">' . esc_html__('— Choisir —', 'conseil-classe') . '</option>';
        foreach ($councils as $co) {
            $label = $co['classe_nom'] . ' (' . $co['classe_niveau'] . ') - ' . mysql2date('d/m/Y', $co['date_conseil']) . ' ' . substr((string) $co['heure_debut'], 0, 5);
            echo '<option value="' . esc_attr((string) $co['id']) . '"' . selected($councilId, (int) $co['id'], false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select> ';
        submit_button(__('Afficher', 'conseil-classe'), 'secondary', 'submit', false);
        echo '</p></form>';
        echo '</section>';

        if ($councilId > 0) {
            $council = CC_Repo::get_council($councilId);
            $regs = CC_Repo::list_registrations_for_council($councilId);
            echo '<section class="cc-admin-section">';
            echo '<h2>' . esc_html__('Inscrits', 'conseil-classe') . '</h2>';
            if ($council) {
                echo '<p><strong>' . esc_html($council['classe_nom'] . ' (' . $council['classe_niveau'] . ')') . '</strong> — ' . esc_html(mysql2date('d/m/Y', $council['date_conseil'])) . ' ' . esc_html(substr((string) $council['heure_debut'], 0, 5)) . '</p>';
            }

            if ($this->get_scalar('bulk_deleted') !== '') {
                echo '<div class="notice notice-success"><p>' . esc_html(sprintf(
                    /* translators: %d: number of removed registrations */
                    __('%d inscription(s) supprimée(s) pour ce conseil.', 'conseil-classe'),
                    (int) $this->get_scalar('bulk_deleted', '0')
                )) . '</p></div>';
            }

            echo '<table class="widefat striped"><thead><tr>';
            echo '<th><input type="checkbox" data-cc-toggle-all="registrations" /></th>';
            echo '<th>' . esc_html__('Parent', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Email', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Téléphone', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Date inscription', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Présent', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Commentaire', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Action', 'conseil-classe') . '</th>';
            echo '</tr></thead><tbody>';

            foreach ($regs as $r) {
                echo '<tr>';
                echo '<td><input type="checkbox" name="ids[]" value="' . esc_attr((string) $r['parent_id']) . '" data-cc-bulk-item="registrations" form="cc-registrations-bulk-form" /></td>';
                echo '<td>' . esc_html($r['prenom'] . ' ' . $r['nom']) . '</td>';
                echo '<td>' . esc_html($r['email']) . '</td>';
                echo '<td>' . esc_html($r['telephone'] ?? '') . '</td>';
                echo '<td>' . esc_html(mysql2date('d/m/Y H:i', $r['date_inscription'])) . '</td>';
                echo '<td>' . ((int) $r['presente'] === 1 ? esc_html__('Oui', 'conseil-classe') : esc_html__('Non', 'conseil-classe')) . '</td>';
                echo '<td>' . esc_html($r['commentaire'] ?? '') . '</td>';
                echo '<td>';
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Désinscrire ce parent ?\');">';
                echo '<input type="hidden" name="action" value="cc_registration_unregister" />';
                echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
                echo '<input type="hidden" name="council_id" value="' . esc_attr((string) $councilId) . '" />';
                echo '<input type="hidden" name="parent_id" value="' . esc_attr((string) $r['parent_id']) . '" />';
                submit_button(__('Désinscrire', 'conseil-classe'), 'delete', 'submit', false);
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }

            if (!$regs) {
                echo '<tr><td colspan="8">' . esc_html__('Aucune inscription.', 'conseil-classe') . '</td></tr>';
            }

            echo '</tbody></table>';
            echo '<form id="cc-registrations-bulk-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Désinscrire les parents sélectionnés ?\');">';
            echo '<input type="hidden" name="action" value="cc_registrations_bulk_delete" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="council_id" value="' . esc_attr((string) $councilId) . '" />';
            echo '<p>';
            submit_button(__('Désinscrire la sélection', 'conseil-classe'), 'delete', 'submit', false);
            echo '</p></form>';
            $this->render_bulk_toggle_script('registrations');
            echo '</section>';
        }

        echo '</div>';
    }

    public function handle_registration_unregister(): void {
        $this->require_manage();
        check_admin_referer('cc_registrations');
        $councilId = (int) $this->post_scalar('council_id', '0');
        $parentId = (int) $this->post_scalar('parent_id', '0');
        if ($councilId > 0 && $parentId > 0) {
            CC_Repo::unregister_parent($councilId, $parentId);
        }
        $this->redirect_admin('cc_registrations', ['council_id' => $councilId]);
    }

    public function handle_registrations_bulk_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_registrations');
        $councilId = (int) $this->post_scalar('council_id', '0');
        $ids = $this->post_ids_array('ids');
        foreach ($ids as $parentId) {
            CC_Repo::unregister_parent($councilId, $parentId);
        }
        $this->redirect_admin('cc_registrations', ['council_id' => $councilId, 'bulk_deleted' => count($ids)]);
    }

    public function handle_registrations_export_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_registrations_export_csv');
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            wp_die(esc_html__('Année/trimestre actif manquant.', 'conseil-classe'));
        }
        $regs = CC_Repo::list_registrations_for_year_term((int) $year['id'], (int) $term['id']);
        $filename = 'inscriptions_' . sanitize_file_name($year['nom'] . '_' . $term['nom']) . '_' . gmdate('Ymd') . '.csv';
        $this->csv_send_headers($filename);
        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, ['parent_email', 'parent_nom', 'parent_prenom', 'classe_nom', 'date_conseil', 'presente', 'commentaire']);
        foreach ($regs as $r) {
            $this->csv_fputcsv($out, [
                $r['parent_email'] ?? '',
                $r['parent_nom'] ?? '',
                $r['parent_prenom'] ?? '',
                $r['classe_nom'] ?? '',
                $r['date_conseil'] ?? '',
                (int) ($r['presente'] ?? 0),
                $r['commentaire'] ?? '',
            ]);
        }
        fclose($out);
        exit;
    }

    public function handle_registrations_download_template(): void {
        $this->require_manage();
        check_admin_referer('cc_registrations_download_template');
        $filename = 'modele_inscriptions.csv';
        $this->csv_send_headers($filename);
        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, ['parent_email', 'parent_nom', 'parent_prenom', 'classe_nom', 'date_conseil', 'presente', 'commentaire']);
        $this->csv_fputcsv($out, ['parent@exemple.fr', 'Dupont', 'Marie', '6A', '2026-05-20', '1', 'Arrivée tardive']);
        fclose($out);
        exit;
    }

    public function handle_registrations_import_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_registrations');
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            $this->redirect_admin('cc_registrations');
        }
        $tmpPath = $this->uploaded_tmp_path('csv_file');
        if ($tmpPath === '') {
            $this->redirect_admin('cc_registrations');
        }
        $delimiter = ',';
        $handle = $this->csv_open_import_handle($tmpPath, $delimiter);
        if (!$handle) {
            $this->redirect_admin('cc_registrations');
        }
        $headers  = fgetcsv($handle, 0, $delimiter);
        $imported = 0;
        $ignored  = 0;
        $errors   = 0;
        $yearId   = (int) $year['id'];
        $termId   = (int) $term['id'];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!$headers || !is_array($headers)) {
                $errors++;
                continue;
            }
            $map = [];
            foreach ($headers as $i => $h) {
                $map[sanitize_key((string) $h)] = (string) ($row[$i] ?? '');
            }
            $email     = sanitize_email($map['parent_email'] ?? '');
            $classeNom = sanitize_text_field($map['classe_nom'] ?? '');
            $date      = sanitize_text_field($map['date_conseil'] ?? '');
            $presente  = (int) ($map['presente'] ?? 0);
            $comment   = sanitize_textarea_field($map['commentaire'] ?? '');
            if ($email === '' || $classeNom === '' || $date === '') {
                $errors++;
                continue;
            }
            $parent = CC_Repo::get_parent_by_email($email);
            if (!$parent) {
                $errors++;
                continue;
            }
            $council = CC_Repo::get_council_by_class_date($yearId, $termId, $classeNom, $date);
            if (!$council) {
                $errors++;
                continue;
            }
            CC_Repo::upsert_registration((int) $council['id'], (int) $parent['id'], $presente > 0 ? 1 : 0, $comment);
            $imported++;
        }
        fclose($handle);
        $this->redirect_admin('cc_registrations', ['imported' => $imported, 'ignored' => $ignored, 'import_errors' => $errors]);
    }

    public function render_reports(): void {
        $this->require_manage();
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        $nonce = wp_create_nonce('cc_reports');

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Comptes-rendus', 'conseil-classe') . '</h1>';

        if (!$year || !$term) {
            echo '<div class="notice notice-warning"><p>' . esc_html__('Définissez une année active et un trimestre actif.', 'conseil-classe') . '</p></div>';
            echo '</div>';
            return;
        }

        echo '<section class="cc-admin-section">';
        echo '<h2>' . esc_html__('Actions', 'conseil-classe') . '</h2>';
        echo '<p>';
        echo '<a class="button button-primary" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_reports_export_csv'), 'cc_reports_export_csv')) . '">' . esc_html__('Export (CSV)', 'conseil-classe') . '</a> ';
        echo '<a class="button" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cc_reports_download_template'), 'cc_reports_download_template')) . '">' . esc_html__('Modèle import (CSV)', 'conseil-classe') . '</a>';
        echo '</p>';
        if ($this->get_scalar('rep_imported') !== '' || $this->get_scalar('rep_import_errors') !== '') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf(
                /* translators: 1: created/updated reports, 2: ignored rows, 3: import errors */
                __('Import comptes-rendus terminé : %1$d créé(s)/mis à jour, %2$d ignoré(s), %3$d erreur(s).', 'conseil-classe'),
                (int) $this->get_scalar('rep_imported', '0'),
                (int) $this->get_scalar('rep_ignored', '0'),
                (int) $this->get_scalar('rep_import_errors', '0')
            )) . '</p></div>';
        }
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" enctype="multipart/form-data">';
        echo '<input type="hidden" name="action" value="cc_reports_import_csv" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p><input type="file" name="csv_file" accept=".csv,text/csv" required /> ';
        submit_button(__('Importer', 'conseil-classe'), 'secondary', 'submit', false);
        echo '</p></form>';
        echo '</section>';

        $reports = CC_Repo::list_reports((int) $year['id'], (int) $term['id']);
        $updateNonceToken = wp_create_nonce('cc_report_update');

        if ($this->get_scalar('updated') !== '') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Compte-rendu enregistré.', 'conseil-classe') . '</p></div>';
        }
        if ($this->get_scalar('bulk_deleted') !== '') {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html(sprintf(
                /* translators: %d: number of deleted reports */
                __('%d compte-rendu(s) supprimé(s) pour le trimestre actif.', 'conseil-classe'),
                (int) $this->get_scalar('bulk_deleted', '0')
            )) . '</p></div>';
        }

        echo '<section class="cc-admin-section">';
        echo '<h2>' . esc_html__('Liste', 'conseil-classe') . '</h2>';
        echo '<table class="widefat striped"><thead><tr>';
        echo '<th><input type="checkbox" data-cc-toggle-all="reports" /></th>';
        echo '<th>' . esc_html__('Classe', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Date conseil', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Président', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Rédigé par', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Validé', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Action', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($reports as $r) {
            echo '<tr>';
            echo '<td><input type="checkbox" name="ids[]" value="' . esc_attr((string) $r['id']) . '" data-cc-bulk-item="reports" form="cc-reports-bulk-form" /></td>';
            echo '<td>' . esc_html($r['classe_nom'] . ' (' . $r['classe_niveau'] . ')') . '</td>';
            echo '<td>' . esc_html(mysql2date('d/m/Y', $r['date_conseil']) . ' ' . substr((string) $r['heure_debut'], 0, 5)) . '</td>';
            echo '<td>' . esc_html($r['president_conseil'] ?? '') . '</td>';
            echo '<td>' . esc_html(trim(($r['prenom_parent'] ?? '') . ' ' . ($r['nom_parent'] ?? '')) . ' (' . ($r['email_parent'] ?? '') . ')') . '</td>';
            echo '<td>' . ((int) $r['valide'] === 1 ? '<strong>' . esc_html__('Oui', 'conseil-classe') . '</strong>' : esc_html__('Non', 'conseil-classe')) . '</td>';
            echo '<td>';
            echo '<a class="button" href="' . esc_url(admin_url('admin.php?page=cc_reports&report_id=' . (int) $r['id'])) . '">' . esc_html__('Voir / Modifier', 'conseil-classe') . '</a> ';

            $exportUrl = wp_nonce_url(
                admin_url('admin-post.php?action=cc_report_export&id=' . (int) $r['id'] . '&format=pdf'),
                'cc_report_export'
            );
            $exportHtmlUrl = wp_nonce_url(
                admin_url('admin-post.php?action=cc_report_export&id=' . (int) $r['id'] . '&format=html'),
                'cc_report_export'
            );
            echo '<a class="button" href="' . esc_url($exportUrl) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Export PDF', 'conseil-classe') . '</a> ';
            echo '<a class="button" href="' . esc_url($exportHtmlUrl) . '">' . esc_html__('Export HTML', 'conseil-classe') . '</a> ';

            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            echo '<input type="hidden" name="action" value="cc_report_toggle_validation" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $r['id']) . '" />';
            echo '<input type="hidden" name="valide" value="' . esc_attr((string) ((int) $r['valide'] === 1 ? 0 : 1)) . '" />';
            submit_button(((int) $r['valide'] === 1) ? __('Dévalider', 'conseil-classe') : __('Valider', 'conseil-classe'), 'secondary', 'submit', false);
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }

        if (!$reports) {
            echo '<tr><td colspan="7">' . esc_html__('Aucun compte-rendu.', 'conseil-classe') . '</td></tr>';
        }

        echo '</tbody></table>';
        echo '<form id="cc-reports-bulk-form" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Supprimer les comptes-rendus sélectionnés ?\');">';
        echo '<input type="hidden" name="action" value="cc_reports_bulk_delete" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p>';
        submit_button(__('Supprimer la sélection', 'conseil-classe'), 'delete', 'submit', false);
        echo '</p></form>';
        $this->render_bulk_toggle_script('reports');
        echo '</section>';

        // Détail + édition (administrateur conseil).
        $reportId = (int) $this->get_scalar('report_id', '0');
        if ($reportId > 0) {
            $report = CC_Repo::get_report($reportId);
            if ($report) {
                echo '<section class="cc-admin-section cc-admin-section--report-detail">';
                echo '<h2>' . esc_html__('Modifier le compte-rendu', 'conseil-classe') . '</h2>';
                echo '<p><a href="' . esc_url(admin_url('admin.php?page=cc_reports')) . '">' . esc_html__('← Retour à la liste', 'conseil-classe') . '</a></p>';
                echo '<div class="cc-admin-kv-grid cc-admin-kv-grid--report">';
                echo '<div class="cc-admin-kv"><span class="cc-admin-kv-label">' . esc_html__('Classe', 'conseil-classe') . '</span><span class="cc-admin-kv-value">' . esc_html(($report['classe_nom'] ?? '') . ' (' . ($report['classe_niveau'] ?? '') . ')') . '</span></div>';
                $dateLabel = mysql2date('d/m/Y', $report['date_conseil']) . ' ' . substr((string) $report['heure_debut'], 0, 5);
                if (!empty($report['heure_fin'])) {
                    $dateLabel .= ' — ' . substr((string) $report['heure_fin'], 0, 5);
                }
                echo '<div class="cc-admin-kv"><span class="cc-admin-kv-label">' . esc_html__('Date', 'conseil-classe') . '</span><span class="cc-admin-kv-value">' . esc_html($dateLabel) . '</span></div>';
                echo '<div class="cc-admin-kv"><span class="cc-admin-kv-label">' . esc_html__('Salle', 'conseil-classe') . '</span><span class="cc-admin-kv-value">' . esc_html((string) ($report['salle_conseil'] ?? '')) . '</span></div>';
                echo '<div class="cc-admin-kv"><span class="cc-admin-kv-label">' . esc_html__('Président', 'conseil-classe') . '</span><span class="cc-admin-kv-value">' . esc_html((string) ($report['president_conseil'] ?? '')) . '</span></div>';
                echo '<div class="cc-admin-kv"><span class="cc-admin-kv-label">' . esc_html__('Année / trimestre', 'conseil-classe') . '</span><span class="cc-admin-kv-value">' . esc_html(($report['annee_nom'] ?? '') . ' - ' . ($report['trimestre_nom'] ?? '')) . '</span></div>';
                echo '<div class="cc-admin-kv"><span class="cc-admin-kv-label">' . esc_html__('Validé', 'conseil-classe') . '</span><span class="cc-admin-kv-value">' . esc_html(((int) ($report['valide'] ?? 0) === 1) ? __('Oui', 'conseil-classe') : __('Non', 'conseil-classe')) . '</span></div>';
                echo '</div>';

                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" class="cc-admin-report-edit">';
                echo '<input type="hidden" name="action" value="cc_report_update" />';
                echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($updateNonceToken) . '" />';
                echo '<input type="hidden" name="id" value="' . esc_attr((string) $report['id']) . '" />';

                echo '<h3>' . esc_html__('Auteur (fiche parent)', 'conseil-classe') . '</h3>';
                echo '<table class="form-table"><tbody>';
                $this->admin_text_row('nom_parent', __('Nom', 'conseil-classe'), (string) ($report['nom_parent'] ?? ''));
                $this->admin_text_row('prenom_parent', __('Prénom', 'conseil-classe'), (string) ($report['prenom_parent'] ?? ''));
                $this->admin_text_row('email_parent', __('Email', 'conseil-classe'), (string) ($report['email_parent'] ?? ''), 'email');
                echo '</tbody></table>';

                echo '<h3>' . esc_html__('Participants', 'conseil-classe') . '</h3>';
                echo '<p><label for="cc_profs_participants"><strong>' . esc_html__('Professeurs participants', 'conseil-classe') . '</strong></label></p>';
                echo '<textarea class="large-text" rows="4" id="cc_profs_participants" name="profs_participants">' . esc_textarea((string) ($report['profs_participants'] ?? '')) . '</textarea>';

                echo '<h3>' . esc_html__('Délégués', 'conseil-classe') . '</h3>';
                echo '<table class="form-table"><tbody>';
                $this->admin_text_row('delegue_eleve_1_prenom', __('Élève 1 — prénom', 'conseil-classe'), (string) ($report['delegue_eleve_1_prenom'] ?? ''));
                $this->admin_text_row('delegue_eleve_1_nom', __('Élève 1 — nom', 'conseil-classe'), (string) ($report['delegue_eleve_1_nom'] ?? ''));
                $this->admin_text_row('delegue_eleve_2_prenom', __('Élève 2 — prénom', 'conseil-classe'), (string) ($report['delegue_eleve_2_prenom'] ?? ''));
                $this->admin_text_row('delegue_eleve_2_nom', __('Élève 2 — nom', 'conseil-classe'), (string) ($report['delegue_eleve_2_nom'] ?? ''));
                $this->admin_text_row('delegue_parent_1_prenom', __('Parent 1 — prénom', 'conseil-classe'), (string) ($report['delegue_parent_1_prenom'] ?? ''));
                $this->admin_text_row('delegue_parent_1_nom', __('Parent 1 — nom', 'conseil-classe'), (string) ($report['delegue_parent_1_nom'] ?? ''));
                $this->admin_text_row('delegue_parent_2_prenom', __('Parent 2 — prénom', 'conseil-classe'), (string) ($report['delegue_parent_2_prenom'] ?? ''));
                $this->admin_text_row('delegue_parent_2_nom', __('Parent 2 — nom', 'conseil-classe'), (string) ($report['delegue_parent_2_nom'] ?? ''));
                echo '</tbody></table>';

                echo '<h3>' . esc_html__('Décisions et récompenses', 'conseil-classe') . '</h3>';
                echo '<table class="form-table"><tbody>';
                $this->admin_number_row('nb_felicitations', __('Félicitations', 'conseil-classe'), (int) ($report['nb_felicitations'] ?? 0));
                $this->admin_number_row('nb_encouragements', __('Encouragements', 'conseil-classe'), (int) ($report['nb_encouragements'] ?? 0));
                $this->admin_number_row('nb_compliments', __('Compliments', 'conseil-classe'), (int) ($report['nb_compliments'] ?? 0));
                $this->admin_number_row('nb_mise_en_garde_travail', __('Mise en garde travail', 'conseil-classe'), (int) ($report['nb_mise_en_garde_travail'] ?? 0));
                $this->admin_number_row('nb_mise_en_garde_comportement', __('Mise en garde comportement', 'conseil-classe'), (int) ($report['nb_mise_en_garde_comportement'] ?? 0));
                echo '</tbody></table>';

                echo '<h3>' . esc_html__('Remarques', 'conseil-classe') . '</h3>';
                $remarkFields = [
                    'remarque_principal' => __('Remarque principal', 'conseil-classe'),
                    'remarque_prof_principal' => __('Remarque prof principal', 'conseil-classe'),
                    'remarques_autres_profs' => __('Remarques autres profs', 'conseil-classe'),
                    'remarques_eleves_delegues' => __('Remarques délégués élèves', 'conseil-classe'),
                    'remarques_parents' => __('Remarques parents', 'conseil-classe'),
                ];
                foreach ($remarkFields as $field => $label) {
                    echo '<p><label for="cc_rep_' . esc_attr($field) . '"><strong>' . esc_html($label) . '</strong></label></p>';
                    echo '<textarea class="large-text" rows="4" id="cc_rep_' . esc_attr($field) . '" name="' . esc_attr($field) . '">' . esc_textarea((string) ($report[$field] ?? '')) . '</textarea>';
                }

                submit_button(__('Enregistrer les modifications', 'conseil-classe'));
                echo '</form>';
                echo '</section>';
            }
        }

        echo '</div>';
    }

    public function handle_report_toggle_validation(): void {
        $this->require_manage();
        check_admin_referer('cc_reports');
        $id = (int) $this->post_scalar('id', '0');
        $valide = (int) $this->post_scalar('valide', '0') === 1;
        if ($id > 0) {
            CC_Repo::set_report_validation($id, $valide);
        }
        $this->redirect_admin('cc_reports');
    }

    public function handle_reports_bulk_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_reports');
        $ids = $this->post_ids_array('ids');
        foreach ($ids as $reportId) {
            CC_Repo::delete_report($reportId);
        }
        $this->redirect_admin('cc_reports', ['bulk_deleted' => count($ids)]);
    }

    public function handle_report_update(): void {
        $this->require_manage();
        check_admin_referer('cc_report_update');

        $id = (int) $this->post_scalar('id', '0');
        if ($id <= 0 || !CC_Repo::get_report($id)) {
            $this->redirect_admin('cc_reports');
            return;
        }

        CC_Repo::update_report_content($id, [
            'nom_parent' => sanitize_text_field($this->post_scalar('nom_parent')),
            'prenom_parent' => sanitize_text_field($this->post_scalar('prenom_parent')),
            'email_parent' => sanitize_email($this->post_scalar('email_parent')),
            'profs_participants' => CC_Utils::sanitize_textarea($this->post_scalar('profs_participants')),
            'delegue_eleve_1_nom' => sanitize_text_field($this->post_scalar('delegue_eleve_1_nom')),
            'delegue_eleve_1_prenom' => sanitize_text_field($this->post_scalar('delegue_eleve_1_prenom')),
            'delegue_eleve_2_nom' => sanitize_text_field($this->post_scalar('delegue_eleve_2_nom')),
            'delegue_eleve_2_prenom' => sanitize_text_field($this->post_scalar('delegue_eleve_2_prenom')),
            'delegue_parent_1_nom' => sanitize_text_field($this->post_scalar('delegue_parent_1_nom')),
            'delegue_parent_1_prenom' => sanitize_text_field($this->post_scalar('delegue_parent_1_prenom')),
            'delegue_parent_2_nom' => sanitize_text_field($this->post_scalar('delegue_parent_2_nom')),
            'delegue_parent_2_prenom' => sanitize_text_field($this->post_scalar('delegue_parent_2_prenom')),
            'remarque_principal' => CC_Utils::sanitize_textarea($this->post_scalar('remarque_principal')),
            'remarque_prof_principal' => CC_Utils::sanitize_textarea($this->post_scalar('remarque_prof_principal')),
            'remarques_autres_profs' => CC_Utils::sanitize_textarea($this->post_scalar('remarques_autres_profs')),
            'remarques_eleves_delegues' => CC_Utils::sanitize_textarea($this->post_scalar('remarques_eleves_delegues')),
            'remarques_parents' => CC_Utils::sanitize_textarea($this->post_scalar('remarques_parents')),
            'nb_felicitations' => max(0, (int) $this->post_scalar('nb_felicitations', '0')),
            'nb_encouragements' => max(0, (int) $this->post_scalar('nb_encouragements', '0')),
            'nb_compliments' => max(0, (int) $this->post_scalar('nb_compliments', '0')),
            'nb_mise_en_garde_travail' => max(0, (int) $this->post_scalar('nb_mise_en_garde_travail', '0')),
            'nb_mise_en_garde_comportement' => max(0, (int) $this->post_scalar('nb_mise_en_garde_comportement', '0')),
        ]);

        $this->redirect_admin('cc_reports', ['report_id' => $id, 'updated' => '1']);
    }

    public function handle_reports_download_template(): void {
        $this->require_manage();
        check_admin_referer('cc_reports_download_template');
        $filename = 'modele_comptes_rendus.csv';
        $this->csv_send_headers($filename);
        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, [
            'classe_nom', 'date_conseil', 'nom_parent', 'prenom_parent', 'email_parent',
            'profs_participants',
            'delegue_eleve_1_nom', 'delegue_eleve_1_prenom',
            'delegue_eleve_2_nom', 'delegue_eleve_2_prenom',
            'delegue_parent_1_nom', 'delegue_parent_1_prenom',
            'delegue_parent_2_nom', 'delegue_parent_2_prenom',
            'remarque_principal', 'remarque_prof_principal',
            'remarques_autres_profs', 'remarques_eleves_delegues', 'remarques_parents',
            'nb_felicitations', 'nb_encouragements', 'nb_compliments',
            'nb_mise_en_garde_travail', 'nb_mise_en_garde_comportement', 'valide',
        ]);
        $this->csv_fputcsv($out, [
            '6A', '2026-05-20', 'Dupont', 'Marie', 'parent@exemple.fr',
            'M. Martin, Mme Durand', '', '', '', '', '', '', '', '',
            '', '', '', '', '', '5', '3', '2', '1', '0', '1',
        ]);
        fclose($out);
        exit;
    }

    public function handle_reports_import_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_reports');
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            $this->redirect_admin('cc_reports');
        }
        $tmpPath = $this->uploaded_tmp_path('csv_file');
        if ($tmpPath === '') {
            $this->redirect_admin('cc_reports');
        }
        $delimiter = ',';
        $handle    = $this->csv_open_import_handle($tmpPath, $delimiter);
        if (!$handle) {
            $this->redirect_admin('cc_reports');
        }
        $headers  = fgetcsv($handle, 0, $delimiter);
        $imported = 0;
        $ignored  = 0;
        $errors   = 0;
        $yearId   = (int) $year['id'];
        $termId   = (int) $term['id'];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!$headers || !is_array($headers)) {
                $errors++;
                continue;
            }
            $map = [];
            foreach ($headers as $i => $h) {
                $map[sanitize_key((string) $h)] = (string) ($row[$i] ?? '');
            }
            $classeNom = sanitize_text_field($map['classe_nom'] ?? '');
            $date      = sanitize_text_field($map['date_conseil'] ?? '');
            if ($classeNom === '' || $date === '') {
                $errors++;
                continue;
            }
            $council = CC_Repo::get_council_by_class_date($yearId, $termId, $classeNom, $date);
            if (!$council) {
                $errors++;
                continue;
            }
            $councilId = (int) $council['id'];
            $data = [
                'nom_parent'                    => sanitize_text_field($map['nom_parent'] ?? ''),
                'prenom_parent'                 => sanitize_text_field($map['prenom_parent'] ?? ''),
                'email_parent'                  => sanitize_email($map['email_parent'] ?? ''),
                'profs_participants'             => sanitize_textarea_field($map['profs_participants'] ?? ''),
                'delegue_eleve_1_nom'           => sanitize_text_field($map['delegue_eleve_1_nom'] ?? ''),
                'delegue_eleve_1_prenom'        => sanitize_text_field($map['delegue_eleve_1_prenom'] ?? ''),
                'delegue_eleve_2_nom'           => sanitize_text_field($map['delegue_eleve_2_nom'] ?? ''),
                'delegue_eleve_2_prenom'        => sanitize_text_field($map['delegue_eleve_2_prenom'] ?? ''),
                'delegue_parent_1_nom'          => sanitize_text_field($map['delegue_parent_1_nom'] ?? ''),
                'delegue_parent_1_prenom'       => sanitize_text_field($map['delegue_parent_1_prenom'] ?? ''),
                'delegue_parent_2_nom'          => sanitize_text_field($map['delegue_parent_2_nom'] ?? ''),
                'delegue_parent_2_prenom'       => sanitize_text_field($map['delegue_parent_2_prenom'] ?? ''),
                'remarque_principal'            => sanitize_textarea_field($map['remarque_principal'] ?? ''),
                'remarque_prof_principal'       => sanitize_textarea_field($map['remarque_prof_principal'] ?? ''),
                'remarques_autres_profs'        => sanitize_textarea_field($map['remarques_autres_profs'] ?? ''),
                'remarques_eleves_delegues'     => sanitize_textarea_field($map['remarques_eleves_delegues'] ?? ''),
                'remarques_parents'             => sanitize_textarea_field($map['remarques_parents'] ?? ''),
                'nb_felicitations'              => max(0, (int) ($map['nb_felicitations'] ?? 0)),
                'nb_encouragements'             => max(0, (int) ($map['nb_encouragements'] ?? 0)),
                'nb_compliments'                => max(0, (int) ($map['nb_compliments'] ?? 0)),
                'nb_mise_en_garde_travail'      => max(0, (int) ($map['nb_mise_en_garde_travail'] ?? 0)),
                'nb_mise_en_garde_comportement' => max(0, (int) ($map['nb_mise_en_garde_comportement'] ?? 0)),
            ];
            $existing = CC_Repo::get_report_by_council($councilId);
            if ($existing) {
                CC_Repo::update_report_content((int) $existing['id'], $data);
                $imported++;
            } else {
                $valide = in_array(strtolower($map['valide'] ?? ''), ['1', 'oui', 'yes', 'true'], true) ? 1 : 0;
                $data['council_id'] = $councilId;
                $data['valide']     = $valide;
                CC_Repo::create_report($data);
                $imported++;
            }
        }
        fclose($handle);
        $this->redirect_admin('cc_reports', ['rep_imported' => $imported, 'rep_ignored' => $ignored, 'rep_import_errors' => $errors]);
    }

    public function handle_reports_export_csv(): void {
        $this->require_manage();
        check_admin_referer('cc_reports_export_csv');
        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            wp_die(esc_html__('Année/trimestre actif manquant.', 'conseil-classe'));
        }

        $reports = CC_Repo::list_reports((int) $year['id'], (int) $term['id']);
        $filename = 'comptes_rendus_' . sanitize_file_name($year['nom'] . '_' . $term['nom'] . '_' . gmdate('Ymd')) . '.csv';

        $this->csv_send_headers($filename);

        $out = fopen('php://output', 'w');
        $this->csv_fputcsv($out, [
            'Classe',
            'Date conseil',
            'Trimestre',
            'Président conseil',
            'Professeurs participants',
            'Délégué élève 1',
            'Délégué élève 2',
            'Délégué parent 1',
            'Délégué parent 2',
            'Validé',
            'Date validation',
            'Félicitations',
            'Encouragements',
            'Compliments',
            'Mise en garde travail',
            'Mise en garde comportement',
            'Remarque principal',
            'Remarque prof principal',
            'Remarques autres profs',
            'Remarques délégués élèves',
            'Remarques parents',
        ]);

        foreach ($reports as $r) {
            $this->csv_fputcsv($out, [
                $r['classe_nom'] ?? '',
                mysql2date('d/m/Y', $r['date_conseil']) . ' ' . substr((string) $r['heure_debut'], 0, 5),
                $term['nom'],
                $r['president_conseil'] ?? '',
                $r['profs_participants'] ?? '',
                trim(($r['delegue_eleve_1_prenom'] ?? '') . ' ' . ($r['delegue_eleve_1_nom'] ?? '')),
                trim(($r['delegue_eleve_2_prenom'] ?? '') . ' ' . ($r['delegue_eleve_2_nom'] ?? '')),
                trim(($r['delegue_parent_1_prenom'] ?? '') . ' ' . ($r['delegue_parent_1_nom'] ?? '')),
                trim(($r['delegue_parent_2_prenom'] ?? '') . ' ' . ($r['delegue_parent_2_nom'] ?? '')),
                ((int) ($r['valide'] ?? 0) === 1) ? 'Oui' : 'Non',
                !empty($r['date_validation']) ? mysql2date('d/m/Y H:i', $r['date_validation']) : '',
                (int) ($r['nb_felicitations'] ?? 0),
                (int) ($r['nb_encouragements'] ?? 0),
                (int) ($r['nb_compliments'] ?? 0),
                (int) ($r['nb_mise_en_garde_travail'] ?? 0),
                (int) ($r['nb_mise_en_garde_comportement'] ?? 0),
                $r['remarque_principal'] ?? '',
                $r['remarque_prof_principal'] ?? '',
                $r['remarques_autres_profs'] ?? '',
                $r['remarques_eleves_delegues'] ?? '',
                $r['remarques_parents'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    public function handle_report_export(): void {
        $this->require_manage();
        check_admin_referer('cc_report_export');

        $id = (int) $this->get_scalar('id', '0');
        $format = sanitize_text_field($this->get_scalar('format', 'pdf'));
        $report = $id > 0 ? CC_Repo::get_report($id) : null;
        if (!$report) {
            wp_die(esc_html__('Compte-rendu introuvable.', 'conseil-classe'));
        }

        $settings = CC_Repo::get_settings();
        $titre = sprintf(
            'Compte-rendu — %s (%s) — %s %s',
            (string) ($report['classe_nom'] ?? ''),
            (string) ($report['classe_niveau'] ?? ''),
            mysql2date('d/m/Y', (string) $report['date_conseil']),
            substr((string) $report['heure_debut'], 0, 5)
        );

        $template = CC_Repo::get_active_pdf_template();
        $rendered = $this->render_report_using_template($report, $settings, $titre, $template);
        $html = $rendered['full'];
        $filenameBase = 'CR_' . sanitize_file_name(($report['classe_nom'] ?? 'classe') . '_' . ($report['trimestre_nom'] ?? 'T') . '_' . mysql2date('Ymd', (string) $report['date_conseil']));

        if ($format === 'html') {
            nocache_headers();
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filenameBase . '.html');
            echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            exit;
        }

        // PDF côté serveur si Dompdf est présent sur le site
        if (class_exists('\\Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            nocache_headers();
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename=' . $filenameBase . '.pdf');
            echo $dompdf->output(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            exit;
        }

        // PDF via le navigateur (html2pdf.js embarqué, sans Composer)
        $this->emit_report_pdf_browser($rendered['fragment'], $filenameBase, $titre);
        exit;
    }

    /** Page HTML minimale qui génère le fichier .pdf côté client (voir assets/html2pdf.bundle.min.js). */
    private function emit_report_pdf_browser(string $fragment, string $filenameBase, string $title): void {
        nocache_headers();
        header('Content-Type: text/html; charset=utf-8');

        $pdfFile = CC_PLUGIN_DIR . 'assets/html2pdf.bundle.min.js';
        if (!is_readable($pdfFile)) {
            wp_die(wp_kses_post(
                __('Le générateur PDF intégré est manquant. Réinstallez le plugin ou utilisez « Export HTML ».', 'conseil-classe')
            ));
        }

        $scriptUrl = CC_PLUGIN_URL . 'assets/html2pdf.bundle.min.js';
        $fn = sanitize_file_name($filenameBase . '.pdf');
        $msgWait = __('Génération du PDF… votre navigateur doit proposer un téléchargement.', 'conseil-classe');
        $msgErr = __('Impossible de générer le PDF (script bloqué). Utilisez l’export HTML.', 'conseil-classe');

        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8" />';
        echo '<title>' . esc_html($title) . '</title>';
        echo '<style>html,body{margin:0;padding:14px;background:#eef0f4;font-family:system-ui,sans-serif;font-size:14px;}</style>';
        echo '</head><body>';
        echo '<p class="cc-pdf-wait-msg">' . esc_html($msgWait) . '</p>';
        echo $fragment; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        $scriptHref = add_query_arg('ver', CC_PLUGIN_VERSION, $scriptUrl);
        echo '<script src="' . esc_url($scriptHref) . '"></script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
        echo '<script>'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
        echo '(function(){var el=document.getElementById("cc-pdf-root");var fn=' . wp_json_encode($fn) . ';';
        echo 'var err=' . wp_json_encode($msgErr) . ';';
        echo 'if(!el||typeof html2pdf!=="function"){alert(err);return;}';
        echo 'html2pdf().set({margin:[10,10,12,10],filename:fn,image:{type:"jpeg",quality:0.93},';
        echo 'html2canvas:{scale:2,useCORS:true,letterRendering:true},';
        echo 'jsPDF:{unit:"mm",format:"a4",orientation:"portrait"},';
        echo 'pagebreak:{mode:["avoid-all","css","legacy"]}}).from(el).save();})();';
        echo '</script></body></html>';
    }

    /**
     * @return array{full:string, fragment:string}
     */
    private function render_report_using_template(array $r, array $settings, string $titre, ?array $template): array {
        $tplHtml = (string) (($template['html_template'] ?? '') ?: CC_Defaults::default_pdf_html_template());
        $tplCss = (string) (($template['css_style'] ?? '') ?: CC_Defaults::default_pdf_css());

        $heure = substr((string) ($r['heure_debut'] ?? ''), 0, 5);
        if (!empty($r['heure_fin'])) {
            $heure .= ' - ' . substr((string) $r['heure_fin'], 0, 5);
        }

        $decisions = [];
        $mapDec = [
            'nb_felicitations' => __('Félicitations', 'conseil-classe'),
            'nb_encouragements' => __('Encouragements', 'conseil-classe'),
            'nb_compliments' => __('Compliments', 'conseil-classe'),
            'nb_mise_en_garde_travail' => __('Mise en garde travail', 'conseil-classe'),
            'nb_mise_en_garde_comportement' => __('Mise en garde comportement', 'conseil-classe'),
        ];
        foreach ($mapDec as $k => $label) {
            $v = (int) ($r[$k] ?? 0);
            if ($v > 0) {
                $decisions[] = esc_html($label) . ' : <strong>' . esc_html((string) $v) . '</strong>';
            }
        }
        $decisionsHtml = $decisions ? implode('<br />', $decisions) : esc_html__('Aucune', 'conseil-classe');

        $deleguesEleves = trim(($r['delegue_eleve_1_prenom'] ?? '') . ' ' . ($r['delegue_eleve_1_nom'] ?? ''));
        $deleguesEleves2 = trim(($r['delegue_eleve_2_prenom'] ?? '') . ' ' . ($r['delegue_eleve_2_nom'] ?? ''));
        $deleguesParents = trim(($r['delegue_parent_1_prenom'] ?? '') . ' ' . ($r['delegue_parent_1_nom'] ?? ''));
        $deleguesParents2 = trim(($r['delegue_parent_2_prenom'] ?? '') . ' ' . ($r['delegue_parent_2_nom'] ?? ''));

        $tokens = [
            '{{association_nom}}' => esc_html((string) ($settings['nom_association_parents'] ?? '')),
            '{{association_adresse}}' => nl2br(esc_html((string) ($settings['adresse_association_parents'] ?? ''))),
            '{{association_telephone}}' => esc_html((string) ($settings['telephone_association_parents'] ?? '')),
            '{{association_email}}' => esc_html((string) ($settings['email_association_parents'] ?? '')),
            '{{etablissement_nom}}' => esc_html((string) ($settings['nom_etablissement'] ?? '')),
            '{{annee}}' => esc_html((string) ($r['annee_nom'] ?? '')),
            '{{trimestre}}' => esc_html((string) ($r['trimestre_nom'] ?? '')),
            '{{classe}}' => esc_html((string) ($r['classe_nom'] ?? '')),
            '{{classe_niveau}}' => esc_html((string) ($r['classe_niveau'] ?? '')),
            '{{date_conseil}}' => esc_html(mysql2date('d/m/Y', (string) ($r['date_conseil'] ?? ''))),
            '{{heure_conseil}}' => esc_html($heure),
            '{{salle}}' => esc_html((string) ($r['salle_conseil'] ?? '')),
            '{{president}}' => esc_html((string) ($r['president_conseil'] ?? '')),
            '{{profs_participants}}' => nl2br(esc_html((string) ($r['profs_participants'] ?? ''))),
            '{{delegues_eleves}}' => esc_html(trim($deleguesEleves . ($deleguesEleves2 ? ' - ' . $deleguesEleves2 : ''))),
            '{{delegues_parents}}' => esc_html(trim($deleguesParents . ($deleguesParents2 ? ' - ' . $deleguesParents2 : ''))),
            '{{decisions}}' => $decisionsHtml,
            '{{remarque_principal}}' => nl2br(esc_html((string) ($r['remarque_principal'] ?? ''))),
            '{{remarque_prof_principal}}' => nl2br(esc_html((string) ($r['remarque_prof_principal'] ?? ''))),
            '{{remarques_autres_profs}}' => nl2br(esc_html((string) ($r['remarques_autres_profs'] ?? ''))),
            '{{remarques_eleves_delegues}}' => nl2br(esc_html((string) ($r['remarques_eleves_delegues'] ?? ''))),
            '{{remarques_parents}}' => nl2br(esc_html((string) ($r['remarques_parents'] ?? ''))),
            '{{date_generation}}' => esc_html(date_i18n('d/m/Y H:i')),
        ];

        $body = strtr($tplHtml, $tokens);
        $fragment = '<div id="cc-pdf-root" class="cc-cr-pdf-root" style="max-width:800px;background:#fff;padding:20px;"><style>'
            . $tplCss
            . '</style>'
            . $body
            . '</div>';

        $full = '<!doctype html><html><head><meta charset="utf-8" /><title>'
            . esc_html($titre)
            . '</title><style>'
            . $tplCss
            . '</style></head><body>'
            . $body
            . '</body></html>';

        return ['full' => $full, 'fragment' => $fragment];
    }

    public function render_pdf_templates(): void {
        $this->require_manage();
        $nonce = wp_create_nonce('cc_pdf_templates');

        $editId = (int) $this->get_scalar('edit_id', '0');
        $template = $editId > 0 ? CC_Repo::get_pdf_template($editId) : null;
        $isEdit = (bool) $template;

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Templates PDF', 'conseil-classe') . '</h1>';

        echo '<h2>' . esc_html($isEdit ? __('Modifier un template', 'conseil-classe') : __('Nouveau template', 'conseil-classe')) . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="cc_pdf_template_save" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<input type="hidden" name="id" value="' . esc_attr((string) ($template['id'] ?? 0)) . '" />';

        $this->text('nom', __('Nom', 'conseil-classe'), (string) ($template['nom'] ?? ''));
        $this->textarea('description', __('Description', 'conseil-classe'), (string) ($template['description'] ?? ''));

        echo '<p><label for="html_template">' . esc_html__('HTML (avec tokens {{...}})', 'conseil-classe') . '</label><br />';
        echo '<textarea class="large-text code" rows="16" id="html_template" name="html_template">' . esc_textarea((string) ($template['html_template'] ?? CC_Defaults::default_pdf_html_template())) . '</textarea></p>';

        echo '<p><label for="css_style">' . esc_html__('CSS', 'conseil-classe') . '</label><br />';
        echo '<textarea class="large-text code" rows="12" id="css_style" name="css_style">' . esc_textarea((string) ($template['css_style'] ?? CC_Defaults::default_pdf_css())) . '</textarea></p>';

        echo '<p><label><input type="checkbox" name="actif" value="1"' . checked((int) ($template['actif'] ?? 0), 1, false) . ' /> ' . esc_html__('Définir actif', 'conseil-classe') . '</label></p>';

        submit_button($isEdit ? __('Enregistrer', 'conseil-classe') : __('Créer', 'conseil-classe'));
        echo '</form>';

        echo '<hr />';
        echo '<h2>' . esc_html__('Liste', 'conseil-classe') . '</h2>';

        $templates = CC_Repo::list_pdf_templates();
        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . esc_html__('Nom', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Actif', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Mis à jour', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Actions', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($templates as $t) {
            echo '<tr>';
            echo '<td>' . esc_html($t['nom']) . '</td>';
            echo '<td>' . (((int) $t['actif'] === 1) ? '<strong>' . esc_html__('Oui', 'conseil-classe') . '</strong>' : esc_html__('Non', 'conseil-classe')) . '</td>';
            echo '<td>' . esc_html(!empty($t['updated_at']) ? mysql2date('d/m/Y H:i', $t['updated_at']) : '') . '</td>';
            echo '<td>';
            echo '<a class="button" href="' . esc_url(admin_url('admin.php?page=cc_pdf_templates&edit_id=' . (int) $t['id'])) . '">' . esc_html__('Éditer', 'conseil-classe') . '</a> ';

            echo '<form style="display:inline" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
            echo '<input type="hidden" name="action" value="cc_pdf_template_activate" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $t['id']) . '" />';
            submit_button(__('Activer', 'conseil-classe'), 'secondary', 'submit', false);
            echo '</form> ';

            echo '<form style="display:inline" method="post" action="' . esc_url(admin_url('admin-post.php')) . '" onsubmit="return confirm(\'Supprimer ce template ?\');">';
            echo '<input type="hidden" name="action" value="cc_pdf_template_delete" />';
            echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
            echo '<input type="hidden" name="id" value="' . esc_attr((string) $t['id']) . '" />';
            submit_button(__('Supprimer', 'conseil-classe'), 'delete', 'submit', false);
            echo '</form>';

            echo '</td>';
            echo '</tr>';
        }

        if (!$templates) {
            echo '<tr><td colspan="4">' . esc_html__('Aucun template.', 'conseil-classe') . '</td></tr>';
        }
        echo '</tbody></table>';

        echo '<p style="margin-top:14px;"><strong>' . esc_html__('Tokens disponibles:', 'conseil-classe') . '</strong><br />';
        echo '<code>{{association_nom}}</code> <code>{{association_adresse}}</code> <code>{{association_telephone}}</code> <code>{{association_email}}</code> ';
        echo '<code>{{etablissement_nom}}</code> <code>{{annee}}</code> <code>{{trimestre}}</code> <code>{{classe}}</code> <code>{{classe_niveau}}</code> ';
        echo '<code>{{date_conseil}}</code> <code>{{heure_conseil}}</code> <code>{{salle}}</code> <code>{{president}}</code> ';
        echo '<code>{{profs_participants}}</code> <code>{{delegues_eleves}}</code> <code>{{delegues_parents}}</code> ';
        echo '<code>{{decisions}}</code> ';
        echo '<code>{{remarque_principal}}</code> <code>{{remarque_prof_principal}}</code> <code>{{remarques_autres_profs}}</code> <code>{{remarques_eleves_delegues}}</code> <code>{{remarques_parents}}</code> ';
        echo '<code>{{date_generation}}</code>';
        echo '</p>';

        echo '</div>';
    }

    public function handle_pdf_template_save(): void {
        $this->require_manage();
        check_admin_referer('cc_pdf_templates');

        $id = (int) $this->post_scalar('id', '0');
        $nom = sanitize_text_field($this->post_scalar('nom'));
        $description = sanitize_textarea_field($this->post_scalar('description'));
        $html = $this->post_scalar('html_template');
        $css = $this->post_scalar('css_style');
        $actif = $this->post_scalar('actif') !== '';

        if ($nom === '' || $html === '') {
            $this->redirect_admin('cc_pdf_templates');
        }

        if ($id > 0) {
            CC_Repo::update_pdf_template($id, $nom, $description, $html, $css, $actif);
        } else {
            CC_Repo::create_pdf_template($nom, $description, $html, $css, $actif);
        }
        $this->redirect_admin('cc_pdf_templates');
    }

    public function handle_pdf_template_activate(): void {
        $this->require_manage();
        check_admin_referer('cc_pdf_templates');
        $id = (int) $this->post_scalar('id', '0');
        if ($id > 0) {
            CC_Repo::activate_pdf_template($id);
        }
        $this->redirect_admin('cc_pdf_templates');
    }

    public function handle_pdf_template_delete(): void {
        $this->require_manage();
        check_admin_referer('cc_pdf_templates');
        $id = (int) $this->post_scalar('id', '0');
        if ($id > 0) {
            CC_Repo::delete_pdf_template($id);
        }
        $this->redirect_admin('cc_pdf_templates');
    }

    public function render_logs(): void {
        $this->require_manage();
        global $wpdb;
        $logs = CC_DB::table('logs');
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- table name from safe source.
        $rows = (array) $wpdb->get_results("SELECT * FROM " . esc_sql($logs) . " ORDER BY timestamp DESC LIMIT 100", ARRAY_A);

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Logs', 'conseil-classe') . '</h1>';
        echo '<section class="cc-admin-section">';
        echo '<h2>' . esc_html__('Historique récent', 'conseil-classe') . '</h2>';
        echo '<p>' . esc_html__('Dernières 100 modifications (paramètres).', 'conseil-classe') . '</p>';

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>' . esc_html__('Date', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Action', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Section', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Champ', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Utilisateur', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('IP', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($rows as $r) {
            echo '<tr>';
            echo '<td>' . esc_html(mysql2date('d/m/Y H:i:s', $r['timestamp'])) . '</td>';
            echo '<td>' . esc_html($r['action']) . '</td>';
            echo '<td>' . esc_html($r['section']) . '</td>';
            echo '<td>' . esc_html($r['champ'] ?? '') . '</td>';
            echo '<td>' . esc_html($r['utilisateur'] ?? '') . '</td>';
            echo '<td>' . esc_html($r['adresse_ip'] ?? '') . '</td>';
            echo '</tr>';
        }
        if (!$rows) {
            echo '<tr><td colspan="6">' . esc_html__('Aucun log.', 'conseil-classe') . '</td></tr>';
        }
        echo '</tbody></table>';
        echo '</section>';
        echo '</div>';
    }

    private function admin_text_row(string $name, string $label, string $value, string $inputType = 'text'): void {
        echo '<tr><th scope="row"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label></th>';
        echo '<td><input class="regular-text" type="' . esc_attr($inputType) . '" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" /></td></tr>';
    }

    private function admin_number_row(string $name, string $label, int $value): void {
        echo '<tr><th scope="row"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label></th>';
        echo '<td><input type="number" class="small-text" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr((string) $value) . '" min="0" step="1" /></td></tr>';
    }

    private function text(string $name, string $label, string $value): void {
        echo '<p class="cc-settings-field"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label><br />';
        echo '<input class="regular-text" type="text" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" /></p>';
    }

    private function number(string $name, string $label, string $value, int $min, int $max): void {
        echo '<p class="cc-settings-field"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label><br />';
        echo '<input type="number" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" min="' . esc_attr((string) $min) . '" max="' . esc_attr((string) $max) . '" /></p>';
    }

    private function textarea(string $name, string $label, string $value): void {
        echo '<p class="cc-settings-field cc-settings-field--wide"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label><br />';
        echo '<textarea class="large-text" rows="4" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '">' . esc_textarea($value) . '</textarea></p>';
    }

    // ── Page Statistiques ────────────────────────────────────────────────────

    public function render_statistics(): void {
        $this->require_manage();
        $settings             = CC_Repo::get_settings();
        $year                 = CC_Repo::get_active_year();
        $term                 = CC_Repo::get_active_term();
        $activeYearId         = $year ? (int) $year['id'] : null;
        $activeTermId         = $term ? (int) $term['id'] : null;
        $maxParentsPerCouncil = max(1, (int) ($settings['max_parents_per_conseil'] ?? 2));

        echo '<div class="wrap cc-admin-page">';
        echo '<h1>' . esc_html__('Statistiques', 'conseil-classe') . '</h1>';

        if (!$year || !$term || !$activeYearId || !$activeTermId) {
            echo '<div class="notice notice-warning inline"><p>';
            echo esc_html__('Définissez une année scolaire et un trimestre actifs pour afficher les statistiques.', 'conseil-classe');
            echo '</p></div></div>';
            return;
        }

        $yearNom = (string) $year['nom'];
        $termNom = (string) $term['nom'];

        // ── Données ─────────────────────────────────────────────────────────
        $apprecByClass    = CC_Repo::list_appreciations_by_class($activeYearId, $activeTermId);
        $apprecByTerm     = CC_Repo::list_appreciations_by_term($activeYearId);
        $apprecAllClasses = CC_Repo::list_appreciations_all_classes_all_terms($activeYearId);
        $topParents       = CC_Repo::list_top_parents($activeYearId, $activeTermId, 5);
        $pendingCouncils  = CC_Repo::list_pending_councils($activeYearId, $activeTermId);
        $engagementByTerm = CC_Repo::list_parent_engagement_by_term($activeYearId, $maxParentsPerCouncil);

        // ── Construction JSON ────────────────────────────────────────────────
        $fields = ['fel', 'enc', 'comp', 'mgt', 'mgc'];
        $dbKeys = ['fel' => 'sum_fel', 'enc' => 'sum_enc', 'comp' => 'sum_comp', 'mgt' => 'sum_mgt', 'mgc' => 'sum_mgc'];

        // A) par classe, trimestre actif
        $classNames  = [];
        $nbReports   = [];
        $byClassData = ['fel' => [], 'enc' => [], 'comp' => [], 'mgt' => [], 'mgc' => []];
        foreach ($apprecByClass as $row) {
            $classNames[] = (string) $row['nom'];
            $nbReports[]  = (int) $row['nb_reports'];
            foreach ($fields as $f) {
                $byClassData[$f][] = (int) $row[$dbKeys[$f]];
            }
        }

        // B) moyennes globales camembert (trimestre actif)
        $totalReports = max(1, array_sum($nbReports));
        $avgData = [];
        foreach ($fields as $f) {
            $avgData[$f] = round(array_sum($byClassData[$f]) / $totalReports, 2);
        }

        // C) évolution globale par trimestre
        $termLabels = [];
        $globalEvol = ['fel' => [], 'enc' => [], 'comp' => [], 'mgt' => [], 'mgc' => []];
        foreach ($apprecByTerm as $row) {
            $termLabels[] = (string) $row['term_nom'];
            foreach ($fields as $f) {
                $globalEvol[$f][] = (int) $row[$dbKeys[$f]];
            }
        }

        // C2) par classe × trimestre
        $classByTerm = [];
        foreach ($apprecAllClasses as $row) {
            $cn = (string) $row['class_nom'];
            $tn = (string) $row['term_nom'];
            if (!isset($classByTerm[$cn])) {
                $classByTerm[$cn] = [];
            }
            $classByTerm[$cn][$tn] = [
                'fel'  => (int) $row['sum_fel'],
                'enc'  => (int) $row['sum_enc'],
                'comp' => (int) $row['sum_comp'],
                'mgt'  => (int) $row['sum_mgt'],
                'mgc'  => (int) $row['sum_mgc'],
            ];
        }
        $evolutionByClass = [];
        foreach ($classByTerm as $cn => $terms) {
            $evolutionByClass[$cn] = ['fel' => [], 'enc' => [], 'comp' => [], 'mgt' => [], 'mgc' => []];
            foreach ($termLabels as $tl) {
                foreach ($fields as $f) {
                    $evolutionByClass[$cn][$f][] = isset($terms[$tl]) ? $terms[$tl][$f] : 0;
                }
            }
        }

        // D) implication parents par trimestre
        $engTermLabels = [];
        $engNbCouncils = [];
        $engCapacity   = [];
        $engInscr      = [];
        $engReports    = [];
        $engPending    = [];
        foreach ($engagementByTerm as $row) {
            $engTermLabels[] = (string) $row['term_nom'];
            $engNbCouncils[] = (int) $row['nb_councils'];
            $engCapacity[]   = (int) $row['capacity'];
            $engInscr[]      = (int) $row['nb_inscriptions'];
            $engReports[]    = (int) $row['nb_reports'];
            $engPending[]    = (int) $row['nb_pending'];
        }

        $statsJson = [
            'labels' => [
                'fel'  => __('Félicitations', 'conseil-classe'),
                'enc'  => __('Encouragements', 'conseil-classe'),
                'comp' => __('Compliments', 'conseil-classe'),
                'mgt'  => __('M.G. Travail', 'conseil-classe'),
                'mgc'  => __('M.G. Comportement', 'conseil-classe'),
            ],
            'colors' => [
                'fel'  => ['bg' => 'rgba(34,169,92,0.75)',  'border' => '#17883e'],
                'enc'  => ['bg' => 'rgba(34,113,177,0.75)', 'border' => '#135e96'],
                'comp' => ['bg' => 'rgba(255,193,7,0.75)',  'border' => '#a07800'],
                'mgt'  => ['bg' => 'rgba(255,99,71,0.75)',  'border' => '#c0392b'],
                'mgc'  => ['bg' => 'rgba(148,55,218,0.75)', 'border' => '#6c3483'],
            ],
            'apprecByClass' => array_merge(
                ['classes' => $classNames, 'nb_reports' => $nbReports],
                $byClassData
            ),
            'apprecAvg' => $avgData,
            'evolution' => [
                'labels'  => $termLabels,
                'global'  => $globalEvol,
                'byClass' => $evolutionByClass,
            ],
            'parentsEngagement' => [
                'labels'          => $engTermLabels,
                'nb_councils'     => $engNbCouncils,
                'capacity'        => $engCapacity,
                'nb_inscriptions' => $engInscr,
                'nb_reports'      => $engReports,
                'nb_pending'      => $engPending,
            ],
        ];

        echo '<script id="cc-stat-data">var ccStatCharts = ' . wp_json_encode($statsJson) . ';</script>';

        // ── Bandeau contexte ────────────────────────────────────────────────
        echo '<div class="cc-stat-context-bar">';
        echo '<span class="cc-admin-dash-badge cc-admin-dash-badge--year">' . esc_html($yearNom) . '</span>';
        echo '<span class="cc-admin-dash-badge cc-admin-dash-badge--term">' . esc_html($termNom) . '</span>';
        echo '</div>';

        // ══ SECTION APPRÉCIATIONS ════════════════════════════════════════════
        echo '<section class="cc-admin-section cc-admin-section--dashboard">';
        echo '<h2>' . esc_html__('Appréciations', 'conseil-classe') . '</h2>';

        // Graphique 1 : barres verticales empilées par classe
        echo '<div class="cc-admin-canvas-card" style="margin-bottom:14px">';
        echo '<h3>' . esc_html(
            /* translators: %s: term name */
            sprintf(__('Appréciations par classe — %s', 'conseil-classe'), $termNom)
        ) . '</h3>';
        echo '<div class="cc-admin-canvas-wrap" style="height:340px">';
        echo '<canvas id="cc-stat-apprec-class" role="img" aria-label="' . esc_attr__('Appréciations par classe', 'conseil-classe') . '"></canvas>';
        echo '</div></div>';

        // Graphiques 2 + 3 côte à côte
        echo '<div class="cc-admin-canvas-grid">';

        // Graphique 2 : camembert des moyennes
        echo '<div class="cc-admin-canvas-card">';
        echo '<h3>' . esc_html(
            /* translators: %s: term name */
            sprintf(__('Répartition moyenne — %s', 'conseil-classe'), $termNom)
        ) . '</h3>';
        echo '<div class="cc-admin-canvas-wrap cc-admin-canvas-wrap--donut">';
        echo '<canvas id="cc-stat-apprec-avg" role="img" aria-label="' . esc_attr__('Répartition des appréciations', 'conseil-classe') . '"></canvas>';
        echo '</div></div>';

        // Graphique 3 : évolution par trimestre (courbe) + filtre classe
        echo '<div class="cc-admin-canvas-card">';
        echo '<h3>' . esc_html(
            /* translators: %s: year name */
            sprintf(__('Évolution des appréciations — %s', 'conseil-classe'), $yearNom)
        ) . '</h3>';
        echo '<div class="cc-stat-class-filter">';
        echo '<select id="cc-stat-class-select" class="cc-stat-select">';
        echo '<option value="global">' . esc_html__('Toutes les classes (total)', 'conseil-classe') . '</option>';
        foreach (array_keys($evolutionByClass) as $cn) {
            echo '<option value="' . esc_attr((string) $cn) . '">' . esc_html((string) $cn) . '</option>';
        }
        echo '</select></div>';
        echo '<div class="cc-admin-canvas-wrap" style="height:260px">';
        echo '<canvas id="cc-stat-apprec-evolution" role="img" aria-label="' . esc_attr__('Évolution des appréciations', 'conseil-classe') . '"></canvas>';
        echo '</div></div>';

        echo '</div>'; // grid
        echo '</section>';

        // ══ SECTION IMPLICATION DES PARENTS ══════════════════════════════════
        echo '<section class="cc-admin-section cc-admin-section--dashboard">';
        echo '<h2>' . esc_html__('Implication des parents', 'conseil-classe') . '</h2>';

        // Graphiques 4 + 5
        echo '<div class="cc-admin-canvas-grid" style="margin-bottom:14px">';

        // Graphique 4 : barres (inscriptions / CR / en attente par trimestre)
        echo '<div class="cc-admin-canvas-card">';
        echo '<h3>' . esc_html(
            /* translators: %s: year name */
            sprintf(__('Couverture & comptes-rendus — %s', 'conseil-classe'), $yearNom)
        ) . '</h3>';
        echo '<div class="cc-admin-canvas-wrap" style="height:260px">';
        echo '<canvas id="cc-stat-parents-engagement" role="img" aria-label="' . esc_attr__('Implication des parents par trimestre', 'conseil-classe') . '"></canvas>';
        echo '</div></div>';

        // Graphique 5 : courbe des taux (%)
        echo '<div class="cc-admin-canvas-card">';
        echo '<h3>' . esc_html(
            /* translators: %s: year name */
            sprintf(__('Taux de couverture & CR — %s', 'conseil-classe'), $yearNom)
        ) . '</h3>';
        echo '<div class="cc-admin-canvas-wrap" style="height:260px">';
        echo '<canvas id="cc-stat-parents-rate" role="img" aria-label="' . esc_attr__('Taux de couverture et CR', 'conseil-classe') . '"></canvas>';
        echo '</div></div>';

        echo '</div>'; // grid

        // Tableaux: TOP 5 + conseils en attente
        echo '<div class="cc-admin-canvas-grid">';

        // TOP 5 parents
        echo '<div class="cc-admin-canvas-card">';
        echo '<h3>' . esc_html(
            /* translators: %s: term name */
            sprintf(__('Top 5 parents engagés — %s', 'conseil-classe'), $termNom)
        ) . '</h3>';
        if ($topParents) {
            echo '<ol class="cc-stat-top-list">';
            foreach ($topParents as $i => $p) {
                echo '<li class="cc-stat-top-item">';
                echo '<span class="cc-stat-top-rank">' . esc_html((string) ($i + 1)) . '</span>';
                echo '<span class="cc-stat-top-name">' . esc_html(trim((string) $p['prenom'] . ' ' . (string) $p['nom'])) . '</span>';
                echo '<span class="cc-stat-top-count">' . esc_html((string) $p['nb_inscriptions']) . ' ';
                echo esc_html(_n('conseil', 'conseils', (int) $p['nb_inscriptions'], 'conseil-classe')) . '</span>';
                echo '</li>';
            }
            echo '</ol>';
        } else {
            echo '<p class="cc-admin-dash-empty">' . esc_html__('Aucune inscription pour ce trimestre.', 'conseil-classe') . '</p>';
        }
        echo '</div>';

        // Conseils en attente
        echo '<div class="cc-admin-canvas-card">';
        echo '<h3>' . esc_html(
            /* translators: %s: term name */
            sprintf(__('Conseils sans CR (date passée) — %s', 'conseil-classe'), $termNom)
        ) . '</h3>';
        if ($pendingCouncils) {
            echo '<table class="widefat striped cc-stat-pending-table"><thead><tr>';
            echo '<th>' . esc_html__('Classe', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Date', 'conseil-classe') . '</th>';
            echo '<th>' . esc_html__('Salle', 'conseil-classe') . '</th>';
            echo '</tr></thead><tbody>';
            foreach ($pendingCouncils as $pc) {
                echo '<tr>';
                echo '<td><strong>' . esc_html((string) $pc['class_nom']) . '</strong> <em class="cc-admin-dashboard-muted">' . esc_html((string) $pc['niveau']) . '</em></td>';
                echo '<td>' . esc_html(mysql2date('d/m/Y', (string) $pc['date_conseil'])) . '</td>';
                echo '<td>' . esc_html((string) $pc['salle_conseil']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p class="cc-admin-dash-empty cc-admin-dash-empty--ok">' . esc_html__('Aucun conseil en attente. Tout est à jour.', 'conseil-classe') . '</p>';
        }
        echo '</div>';

        echo '</div>'; // grid
        echo '</section>';
        echo '</div>'; // wrap
    }

    // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_operations_fopen,WordPress.WP.AlternativeFunctions.file_system_operations_fclose
}

