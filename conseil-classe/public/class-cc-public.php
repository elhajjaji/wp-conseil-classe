<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CC_Public {
    /** Handlers pour wp-admin/admin-post.php (formulaires du front). Toujours enregistrés. */
    public function register_action_handlers(): void {
        add_action('admin_post_cc_parent_register', [$this, 'handle_parent_register']);
        add_action('admin_post_cc_parent_unregister', [$this, 'handle_parent_unregister']);
        add_action('admin_post_cc_report_create', [$this, 'handle_report_create']);

        add_action('admin_post_nopriv_cc_parent_register', [$this, 'handle_parent_register']);
        add_action('admin_post_nopriv_cc_parent_unregister', [$this, 'handle_parent_unregister']);
        add_action('admin_post_nopriv_cc_report_create', [$this, 'handle_report_create']);
    }

    /** Shortcodes et garde-front : uniquement hors admin. */
    public function register_frontend(): void {
        add_shortcode('cc_plannings', [$this, 'shortcode_plannings']);
        add_shortcode('cc_my_councils', [$this, 'shortcode_my_councils']);
        add_shortcode('cc_report_form', [$this, 'shortcode_report_form']);
        add_shortcode('cc_parent_login', [$this, 'shortcode_parent_login']);

        add_action('template_redirect', [$this, 'gate_conseil_pages'], 4);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets(): void {
        wp_register_style('cc-public', CC_PLUGIN_URL . 'assets/public.css', [], CC_PLUGIN_VERSION);
        wp_enqueue_style('cc-public');
    }

    /** Pages conseil (pas la page connexion/info) réservées aux comptes avec capacité conseil-parent. */
    public function gate_conseil_pages(): void {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return;
        }
        if (!is_singular('page')) {
            return;
        }
        $post = get_queried_object();
        if (!$post || empty($post->ID)) {
            return;
        }
        $id = (int) $post->ID;

        $protected = array_unique(array_filter(array_map('intval', [
            get_option('cc_plannings_page_id', 0),
            get_option('cc_my_councils_page_id', 0),
            get_option('cc_report_form_page_id', 0),
        ])));

        if (!in_array($id, $protected, true)) {
            return;
        }

        if (!is_user_logged_in()) {
            auth_redirect();
            exit;
        }
        if (!CC_Roles::user_can_parent_portal()) {
            wp_die(
                wp_kses_post(__('Ce compte n’a pas le profil conseil de classe nécessaire (parent, admin conseil ou super admin conseil).', 'conseil-classe')),
                esc_html__('Accès refusé', 'conseil-classe'),
                ['response' => 403]
            );
        }
    }

    /** Redirection après action POST (évite referrer admin-post.php). */
    private function redirect_after_frontend_action(): void {
        $ref = wp_get_referer() ?: '';
        if ($ref !== '' && strpos((string) $ref, 'admin-post.php') !== false) {
            $fallback = '';
            foreach (['cc_plannings_page_id', 'cc_my_councils_page_id'] as $opt) {
                $pid = (int) get_option($opt, 0);
                if ($pid > 0 && ($u = get_permalink($pid))) {
                    $fallback = $u;
                    break;
                }
            }
            $target = $fallback !== '' ? $fallback : home_url('/');
            wp_safe_redirect((string) wp_validate_redirect($target, home_url('/')));
            exit;
        }
        $target = $this->parent_safe_redirect_target($ref !== '' ? $ref : home_url('/'));
        wp_safe_redirect($target);
        exit;
    }

    /**
     * Cible redirect_to depuis les liens (wp-login, etc.).
     */
    private function parent_safe_redirect_target(string $candidate): string {
        $fallback = home_url('/');
        $pid = (int) get_option('cc_parent_login_page_id', 0);
        if ($pid > 0) {
            $permalink = get_permalink($pid);
            if ($permalink) {
                $fallback = $permalink;
            }
        }

        return (string) wp_validate_redirect(wp_unslash(trim($candidate)), $fallback);
    }

    private function wp_login_url_for_conseil(): string {
        global $post;
        $here = ($post && !empty($post->ID)) ? get_permalink((int) $post->ID) : '';
        $back = $here !== '' ? $here : (wp_get_referer() ?: home_url('/'));
        $back = $this->parent_safe_redirect_target($back);

        return wp_login_url($back);
    }

    /**
     * Fiche parent liée au compte WP (wp_user_id ou même email non lié).
     */
    private function get_frontend_parent_record(): ?array {
        if (!is_user_logged_in() || !CC_Roles::user_can_parent_portal()) {
            return null;
        }
        $uid = get_current_user_id();
        $row = CC_Repo::get_parent_by_wp_user($uid);
        if ($row) {
            return $row;
        }

        $email = CC_Utils::normalize_email((string) wp_get_current_user()->user_email);
        if ($email === '') {
            return null;
        }
        $candidate = CC_Repo::get_parent_by_email($email);
        if (!$candidate) {
            return null;
        }
        if (!empty($candidate['wp_user_id']) && (int) $candidate['wp_user_id'] !== $uid) {
            return null;
        }
        if (empty($candidate['wp_user_id'])) {
            CC_Repo::update_parent((int) $candidate['id'], ['wp_user_id' => $uid]);

            return CC_Repo::get_parent_by_wp_user($uid);
        }

        return $candidate;
    }

    private function require_parent_row_for_action(): array {
        $row = $this->get_frontend_parent_record();
        if (!$row) {
            wp_safe_redirect(wp_login_url());
            exit;
        }

        return $row;
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
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- nonce is verified in each POST action handler before use.
        return $this->request_scalar($_POST, $key, $default);
    }

    private function get_scalar(string $key, string $default = ''): string {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only query params for shortcode rendering.
        return $this->request_scalar($_GET, $key, $default);
    }

    /**
     * @param string|null $highlight login|planning|my_councils|report
     */
    private function render_parent_area_nav(?string $highlight = null): string {
        $items = [];

        $loginId = (int) get_option('cc_parent_login_page_id', 0);
        if ($loginId > 0 && ($u = get_permalink($loginId))) {
            $items[] = ['key' => 'login', 'label' => __('Espace compte', 'conseil-classe'), 'url' => $u];
        }
        $planId = (int) get_option('cc_plannings_page_id', 0);
        if ($planId > 0 && ($u = get_permalink($planId))) {
            $items[] = ['key' => 'planning', 'label' => __('Planning', 'conseil-classe'), 'url' => $u];
        }
        $mcId = (int) get_option('cc_my_councils_page_id', 0);
        if ($mcId > 0 && ($u = get_permalink($mcId))) {
            $items[] = ['key' => 'my_councils', 'label' => __('Mes conseils', 'conseil-classe'), 'url' => $u];
        }
        $rpId = (int) get_option('cc_report_form_page_id', 0);
        if ($rpId > 0 && ($u = get_permalink($rpId))) {
            $items[] = ['key' => 'report', 'label' => __('Compte rendu', 'conseil-classe'), 'url' => $u];
        }

        if (!$items) {
            return '<div class="cc-notice cc-notice-warning">' . esc_html__('Aucune page parent n’est configurée. Allez dans Conseil de classe → Paramètres → Pages (front).', 'conseil-classe') . '</div>';
        }

        ob_start();
        echo '<nav class="cc-parent-nav" aria-label="' . esc_attr__('Espace parents — conseils de classe', 'conseil-classe') . '">';
        echo '<p class="cc-parent-nav-title">' . esc_html__('Conseils de classe — Parents', 'conseil-classe') . '</p>';
        echo '<ul class="cc-parent-nav-list">';
        foreach ($items as $it) {
            $cls = ['cc-parent-nav-link'];
            if ($highlight !== null && $it['key'] === $highlight) {
                $cls[] = 'is-current';
            }
            echo '<li><a class="' . esc_attr(implode(' ', $cls)) . '" href="' . esc_url($it['url']) . '">' . esc_html($it['label']) . '</a></li>';
        }
        echo '</ul></nav>';

        return (string) ob_get_clean();
    }

    private function wrap_parent_shortcode_output(string $inner, ?string $navHighlight = null): string {
        return '<div class="cc-portal">'
            . $this->render_parent_area_nav($navHighlight)
            . '<div class="cc-portal-main">' . $inner . '</div></div>';
    }

    public function shortcode_parent_login($atts): string {
        ob_start();
        echo '<div class="cc-parent-login">';
        echo '<h3>' . esc_html__('Espace parents (compte du site)', 'conseil-classe') . '</h3>';

        if (is_user_logged_in()) {
            if (!CC_Roles::user_can_parent_portal()) {
                echo '<div class="cc-notice cc-notice-warning">' . esc_html__('Vous êtes connecté, mais ce compte n’a pas le rôle « parent conseil » demandé. Contactez l’administration.', 'conseil-classe') . '</div>';
            } else {
                $row = $this->get_frontend_parent_record();
                $u = wp_get_current_user();
                echo '<div class="cc-notice cc-notice-info">' . esc_html__('Vous êtes connecté.', 'conseil-classe') . '</div>';
                echo '<p><strong>' . esc_html($u->display_name) . '</strong> — ' . esc_html((string) $u->user_email) . '</p>';
                if ($row) {
                    echo '<p>' . esc_html__('Fiche parent :', 'conseil-classe') . ' <strong>' . esc_html($row['prenom'] . ' ' . $row['nom']) . '</strong></p>';
                } else {
                    echo '<div class="cc-notice cc-notice-warning">' . esc_html__('Aucune fiche parent n’est liée à ce compte (identifiant ou email). Un administrateur doit associer cet utilisateur à la liste des parents.', 'conseil-classe') . '</div>';
                }
                echo '<p><a class="cc-btn cc-btn-secondary" href="' . esc_url(wp_logout_url((string) wp_validate_redirect(self::current_page_url_or_home(), home_url('/')))) . '">' . esc_html__('Se déconnecter', 'conseil-classe') . '</a></p>';
            }
        } else {
            echo '<p class="cc-muted">' . esc_html__('Accédez au planning, à vos inscriptions et aux comptes rendus avec votre compte fourni par l’établissement ou l’association.', 'conseil-classe') . '</p>';
            echo '<p><a class="cc-btn cc-btn-primary" href="' . esc_url($this->wp_login_url_for_conseil()) . '">' . esc_html__('Se connecter', 'conseil-classe') . '</a></p>';
        }

        echo '</div>';
        $inner = (string) ob_get_clean();

        return $this->wrap_parent_shortcode_output($inner, 'login');
    }

    private static function current_page_url_or_home(): string {
        global $post;
        if ($post && !empty($post->ID)) {
            $p = get_permalink((int) $post->ID);

            return $p ? $p : home_url('/');
        }

        return home_url('/');
    }

    public function shortcode_plannings($atts): string {
        if (!is_user_logged_in() || !CC_Roles::user_can_parent_portal()) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-plannings"><div class="cc-notice cc-notice-warning">' . esc_html__('Connectez-vous avec un compte autorisé pour voir le planning.', 'conseil-classe') . '</div></div>',
                'planning'
            );
        }

        $parentRow = $this->get_frontend_parent_record();
        if (!$parentRow) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-plannings"><div class="cc-notice cc-notice-warning">' . esc_html__('Votre compte n’est pas encore lié à une fiche parent. Contactez l’administration.', 'conseil-classe') . '</div></div>',
                'planning'
            );
        }

        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-plannings"><div class="cc-notice cc-notice-warning">' . esc_html__('Aucune année/trimestre actif configuré.', 'conseil-classe') . '</div></div>',
                'planning'
            );
        }

        $councils = CC_Repo::list_councils((int) $year['id'], (int) $term['id']);
        $settings = CC_Repo::get_settings();
        $maxParents = (int) ($settings['max_parents_per_conseil'] ?? 2);

        $parent = $parentRow;

        ob_start();
        echo '<div class="cc-plannings">';
        /* translators: 1: school year name, 2: term name */
        echo '<h3>' . esc_html(sprintf(__('Planning des conseils (%1$s - %2$s)', 'conseil-classe'), $year['nom'], $term['nom'])) . '</h3>';

        if (!$councils) {
            echo '<p>' . esc_html__('Aucun conseil planifié.', 'conseil-classe') . '</p>';
            echo '</div>';

            return $this->wrap_parent_shortcode_output((string) ob_get_clean(), 'planning');
        }

        echo '<div class="cc-table-scroll" role="region" aria-label="' . esc_attr__('Planning des conseils', 'conseil-classe') . '">';
        echo '<table class="cc-table"><thead><tr>';
        echo '<th>' . esc_html__('Classe', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Date', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Heure', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Salle', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Places', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Action', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($councils as $co) {
            $count = CC_Repo::count_registrations_for_council((int) $co['id']);
            $heure = substr((string) $co['heure_debut'], 0, 5);
            if (!empty($co['heure_fin'])) {
                $heure .= ' - ' . substr((string) $co['heure_fin'], 0, 5);
            }
            $remaining = max(0, $maxParents - $count);

            $isRegistered = CC_Repo::is_parent_registered((int) $co['id'], (int) $parent['id']);

            echo '<tr>';
            echo '<td>' . esc_html($co['classe_nom'] . ' (' . $co['classe_niveau'] . ')') . '</td>';
            echo '<td>' . esc_html(mysql2date('d/m/Y', $co['date_conseil'])) . '</td>';
            echo '<td>' . esc_html($heure) . '</td>';
            echo '<td>' . esc_html($co['salle_conseil']) . '</td>';
            echo '<td>' . esc_html($remaining . ' / ' . $maxParents) . '</td>';
            echo '<td>';

            if ($isRegistered) {
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
                echo '<input type="hidden" name="action" value="cc_parent_unregister" />';
                wp_nonce_field('cc_parent_unregister');
                echo '<input type="hidden" name="council_id" value="' . esc_attr((string) $co['id']) . '" />';
                echo '<button class="cc-btn cc-btn-secondary" type="submit">' . esc_html__('Se désinscrire', 'conseil-classe') . '</button>';
                echo '</form>';
            } elseif ($remaining <= 0) {
                echo '<span class="cc-badge cc-badge-full">' . esc_html__('Complet', 'conseil-classe') . '</span>';
            } else {
                echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
                echo '<input type="hidden" name="action" value="cc_parent_register" />';
                wp_nonce_field('cc_parent_register');
                echo '<input type="hidden" name="council_id" value="' . esc_attr((string) $co['id']) . '" />';
                echo '<button class="cc-btn cc-btn-primary" type="submit">' . esc_html__('S’inscrire', 'conseil-classe') . '</button>';
                echo '</form>';
            }

            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '</div>';

        return $this->wrap_parent_shortcode_output((string) ob_get_clean(), 'planning');
    }

    public function handle_parent_register(): void {
        $parent = $this->require_parent_row_for_action();
        check_admin_referer('cc_parent_register');
        $councilId = (int) $this->post_scalar('council_id', '0');
        if ($councilId <= 0) {
            wp_die(esc_html__('Conseil invalide.', 'conseil-classe'));
        }

        $settings = CC_Repo::get_settings();
        $maxParents = (int) ($settings['max_parents_per_conseil'] ?? 2);

        if (CC_Repo::is_parent_registered($councilId, (int) $parent['id'])) {
            $this->redirect_after_frontend_action();
        }

        $count = CC_Repo::count_registrations_for_council($councilId);
        if ($count >= $maxParents) {
            /* translators: %d: max number of parents allowed */
            wp_die(esc_html(sprintf(__('Limite atteinte : maximum %d parent(s) autorisé(s) par conseil.', 'conseil-classe'), $maxParents)));
        }

        CC_Repo::register_parent($councilId, (int) $parent['id']);
        $this->redirect_after_frontend_action();
    }

    public function handle_parent_unregister(): void {
        $parent = $this->require_parent_row_for_action();
        check_admin_referer('cc_parent_unregister');
        $councilId = (int) $this->post_scalar('council_id', '0');
        if ($councilId > 0) {
            CC_Repo::unregister_parent($councilId, (int) $parent['id']);
        }
        $this->redirect_after_frontend_action();
    }

    public function shortcode_my_councils($atts): string {
        if (!is_user_logged_in() || !CC_Roles::user_can_parent_portal()) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-my-councils"><div class="cc-notice cc-notice-warning">' . esc_html__('Connectez-vous pour accéder à vos conseils.', 'conseil-classe') . '</div></div>',
                'my_councils'
            );
        }

        $parent = $this->get_frontend_parent_record();
        if (!$parent) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-my-councils"><div class="cc-notice cc-notice-warning">' . esc_html__('Votre compte n’est pas lié à une fiche parent.', 'conseil-classe') . '</div></div>',
                'my_councils'
            );
        }

        $year = CC_Repo::get_active_year();
        $term = CC_Repo::get_active_term();
        if (!$year || !$term) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-my-councils"><div class="cc-notice cc-notice-warning">' . esc_html__('Aucune année/trimestre actif configuré.', 'conseil-classe') . '</div></div>',
                'my_councils'
            );
        }

        $data = CC_Repo::list_parent_councils((int) $parent['id'], (int) $year['id'], (int) $term['id']);
        $actifs = (array) ($data['actifs'] ?? []);
        $hist = (array) ($data['historique'] ?? []);

        $reportFormUrl = '';
        $pageId = (int) get_option('cc_report_form_page_id', 0);
        if ($pageId > 0) {
            $reportFormUrl = get_permalink($pageId) ?: '';
        }

        ob_start();
        echo '<div class="cc-my-councils">';
        echo '<h3>' . esc_html__('Mes conseils (actifs)', 'conseil-classe') . '</h3>';
        echo $this->render_parent_councils_table($actifs, $reportFormUrl); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        echo '<h3 style="margin-top:24px">' . esc_html__('Historique', 'conseil-classe') . '</h3>';
        echo $this->render_parent_councils_table($hist, $reportFormUrl); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '</div>';

        return $this->wrap_parent_shortcode_output((string) ob_get_clean(), 'my_councils');
    }

    private function render_parent_councils_table(array $councils, string $reportFormUrl): string {
        if (!$councils) {
            return '<p>' . esc_html__('Aucun conseil.', 'conseil-classe') . '</p>';
        }

        ob_start();
        echo '<div class="cc-table-scroll" role="region" aria-label="' . esc_attr__('Liste des conseils', 'conseil-classe') . '">';
        echo '<table class="cc-table"><thead><tr>';
        echo '<th>' . esc_html__('Classe', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Date', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Heure', 'conseil-classe') . '</th>';
        echo '<th>' . esc_html__('Compte-rendu', 'conseil-classe') . '</th>';
        echo '</tr></thead><tbody>';

        foreach ($councils as $co) {
            $heure = substr((string) $co['heure_debut'], 0, 5);
            if (!empty($co['heure_fin'])) {
                $heure .= ' - ' . substr((string) $co['heure_fin'], 0, 5);
            }

            $existing = CC_Repo::get_report_for_council((int) $co['id']);

            echo '<tr>';
            echo '<td>' . esc_html($co['classe_nom'] . ' (' . $co['classe_niveau'] . ')') . '</td>';
            echo '<td>' . esc_html(mysql2date('d/m/Y', $co['date_conseil'])) . '</td>';
            echo '<td>' . esc_html($heure) . '</td>';
            echo '<td>';
            if ($existing) {
                echo '<span class="cc-badge cc-badge-done">' . esc_html__('Déjà rédigé', 'conseil-classe') . '</span>';
            } elseif ($reportFormUrl) {
                $url = add_query_arg(['council_id' => (int) $co['id']], $reportFormUrl);
                echo '<a class="cc-btn cc-btn-primary" href="' . esc_url($url) . '">' . esc_html__('Rédiger', 'conseil-classe') . '</a>';
            } else {
                echo '<em>' . esc_html__('Configurez la page du formulaire de compte-rendu.', 'conseil-classe') . '</em>';
            }
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';

        return (string) ob_get_clean();
    }

    public function shortcode_report_form($atts): string {
        if (!is_user_logged_in() || !CC_Roles::user_can_parent_portal()) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-report-form"><div class="cc-notice cc-notice-warning">' . esc_html__('Connexion obligatoire pour rédiger un compte rendu.', 'conseil-classe') . '</div></div>',
                'report'
            );
        }

        $parent = $this->get_frontend_parent_record();
        if (!$parent) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-report-form"><div class="cc-notice cc-notice-warning">' . esc_html__('Fiche parent introuvable pour ce compte.', 'conseil-classe') . '</div></div>',
                'report'
            );
        }

        $councilId = (int) $this->get_scalar('council_id', '0');
        if ($councilId <= 0) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-report-form"><div class="cc-notice cc-notice-warning">' . esc_html__('Conseil manquant : utilisez « Rédiger » depuis « Mes conseils ».', 'conseil-classe') . '</div></div>',
                'report'
            );
        }

        if (!CC_Repo::is_parent_registered($councilId, (int) $parent['id'])) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-report-form"><div class="cc-notice cc-notice-error">' . esc_html__('Vous n’êtes pas inscrit(e) à ce conseil.', 'conseil-classe') . '</div></div>',
                'report'
            );
        }

        $council = CC_Repo::get_council($councilId);
        if (!$council) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-report-form"><div class="cc-notice cc-notice-warning">' . esc_html__('Conseil introuvable.', 'conseil-classe') . '</div></div>',
                'report'
            );
        }

        $existing = CC_Repo::get_report_for_council($councilId);
        if ($existing) {
            return $this->wrap_parent_shortcode_output(
                '<div class="cc-report-form"><div class="cc-notice cc-notice-info">' . esc_html__('Un compte-rendu existe déjà pour ce conseil.', 'conseil-classe') . '</div></div>',
                'report'
            );
        }

        $heure = substr((string) $council['heure_debut'], 0, 5);
        if (!empty($council['heure_fin'])) {
            $heure .= ' - ' . substr((string) $council['heure_fin'], 0, 5);
        }

        ob_start();
        echo '<div class="cc-report-form">';
        echo '<h3>' . esc_html__('Nouveau compte-rendu', 'conseil-classe') . '</h3>';
        echo '<p><strong>' . esc_html($council['classe_nom'] . ' (' . $council['classe_niveau'] . ')') . '</strong> — ' . esc_html(mysql2date('d/m/Y', $council['date_conseil'])) . ' ' . esc_html($heure) . '</p>';

        echo '<div class="cc-notice cc-notice-info">';
        echo esc_html__('Une fois créé, vous ne pouvez plus modifier ce compte-rendu depuis cet espace ; l’équipe conseil peut le corriger depuis l’administration du site si besoin.', 'conseil-classe');
        echo '</div>';

        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        echo '<input type="hidden" name="action" value="cc_report_create" />';
        wp_nonce_field('cc_report_create');
        echo '<input type="hidden" name="council_id" value="' . esc_attr((string) $councilId) . '" />';

        echo '<fieldset class="cc-fieldset"><legend>' . esc_html__('Participants', 'conseil-classe') . '</legend>';
        echo '<label>' . esc_html__('Professeurs participants', 'conseil-classe') . '<br />';
        echo '<textarea name="profs_participants" rows="3" class="cc-textarea"></textarea></label>';

        echo '<div class="cc-grid">';
        echo '<div><h4>' . esc_html__('Délégués élèves', 'conseil-classe') . '</h4>';
        $this->two_name_fields('delegue_eleve_1_prenom', 'delegue_eleve_1_nom', __('Prénom', 'conseil-classe'), __('Nom', 'conseil-classe'));
        $this->two_name_fields('delegue_eleve_2_prenom', 'delegue_eleve_2_nom', __('Prénom', 'conseil-classe'), __('Nom', 'conseil-classe'));
        echo '</div>';

        echo '<div><h4>' . esc_html__('Délégués parents', 'conseil-classe') . '</h4>';
        $this->two_name_fields('delegue_parent_1_prenom', 'delegue_parent_1_nom', __('Prénom', 'conseil-classe'), __('Nom', 'conseil-classe'));
        $this->two_name_fields('delegue_parent_2_prenom', 'delegue_parent_2_nom', __('Prénom', 'conseil-classe'), __('Nom', 'conseil-classe'));
        echo '</div>';
        echo '</div>';
        echo '</fieldset>';

        echo '<fieldset class="cc-fieldset"><legend>' . esc_html__('Décisions et récompenses', 'conseil-classe') . '</legend>';
        $this->number_field('nb_felicitations', __('Félicitations', 'conseil-classe'));
        $this->number_field('nb_encouragements', __('Encouragements', 'conseil-classe'));
        $this->number_field('nb_compliments', __('Compliments', 'conseil-classe'));
        $this->number_field('nb_mise_en_garde_travail', __('Mise en garde travail', 'conseil-classe'));
        $this->number_field('nb_mise_en_garde_comportement', __('Mise en garde comportement', 'conseil-classe'));
        echo '</fieldset>';

        echo '<fieldset class="cc-fieldset"><legend>' . esc_html__('Remarques', 'conseil-classe') . '</legend>';
        $this->textarea_field('remarque_principal', __('Remarque principal', 'conseil-classe'));
        $this->textarea_field('remarque_prof_principal', __('Remarque prof principal', 'conseil-classe'));
        $this->textarea_field('remarques_autres_profs', __('Remarques autres profs', 'conseil-classe'));
        $this->textarea_field('remarques_eleves_delegues', __('Remarques délégués élèves', 'conseil-classe'));
        $this->textarea_field('remarques_parents', __('Remarques parents', 'conseil-classe'));
        echo '</fieldset>';

        echo '<input type="hidden" name="nom_parent" value="' . esc_attr((string) ($parent['nom'] ?? '')) . '" />';
        echo '<input type="hidden" name="prenom_parent" value="' . esc_attr((string) ($parent['prenom'] ?? '')) . '" />';
        echo '<input type="hidden" name="email_parent" value="' . esc_attr((string) ($parent['email'] ?? '')) . '" />';

        echo '<button class="cc-btn cc-btn-primary" type="submit">' . esc_html__('Créer le compte-rendu', 'conseil-classe') . '</button>';
        echo '</form>';
        echo '</div>';

        return $this->wrap_parent_shortcode_output((string) ob_get_clean(), 'report');
    }

    public function handle_report_create(): void {
        $parent = $this->require_parent_row_for_action();
        check_admin_referer('cc_report_create');
        $councilId = (int) $this->post_scalar('council_id', '0');
        if ($councilId <= 0) {
            wp_die(esc_html__('Conseil invalide.', 'conseil-classe'));
        }

        if (!CC_Repo::is_parent_registered($councilId, (int) $parent['id'])) {
            wp_die(esc_html__('Vous n’êtes pas inscrit(e) à ce conseil.', 'conseil-classe'));
        }

        if (CC_Repo::get_report_for_council($councilId)) {
            wp_die(esc_html__('Un compte-rendu existe déjà.', 'conseil-classe'));
        }

        $data = [
            'council_id' => $councilId,
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
            'valide' => 0,
            'date_validation' => null,
        ];

        CC_Repo::create_report($data);
        $this->redirect_after_frontend_action();
    }

    private function two_name_fields(string $prenomName, string $nomName, string $prenomLabel, string $nomLabel): void {
        echo '<div class="cc-row">';
        echo '<label>' . esc_html($prenomLabel) . '<br /><input class="cc-input" type="text" name="' . esc_attr($prenomName) . '" /></label>';
        echo '<label>' . esc_html($nomLabel) . '<br /><input class="cc-input" type="text" name="' . esc_attr($nomName) . '" /></label>';
        echo '</div>';
    }

    private function number_field(string $name, string $label): void {
        echo '<label class="cc-inline-field">' . esc_html($label) . '<br />';
        echo '<input class="cc-input" type="number" name="' . esc_attr($name) . '" min="0" value="0" /></label> ';
    }

    private function textarea_field(string $name, string $label): void {
        echo '<label>' . esc_html($label) . '<br />';
        echo '<textarea class="cc-textarea" name="' . esc_attr($name) . '" rows="4"></textarea></label>';
    }
}
