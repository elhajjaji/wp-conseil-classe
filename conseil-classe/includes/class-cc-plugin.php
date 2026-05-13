<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-cc-db.php';
require_once __DIR__ . '/class-cc-roles.php';
require_once __DIR__ . '/class-cc-utils.php';
require_once __DIR__ . '/class-cc-logs.php';
require_once __DIR__ . '/class-cc-defaults.php';
require_once __DIR__ . '/class-cc-repo.php';

require_once CC_PLUGIN_DIR . 'admin/class-cc-admin.php';
require_once CC_PLUGIN_DIR . 'public/class-cc-public.php';

final class CC_Plugin {
    public function init(): void {
        CC_Roles::register();

        $public = new CC_Public();
        // admin-post.php est considéré comme « admin » : il faut quand même enregistrer les handlers
        // utilisés par les formulaires front (inscription / désinscription / compte-rendu).
        $public->register_action_handlers();

        if (is_admin()) {
            (new CC_Admin())->init();
        } else {
            $public->register_frontend();
        }
    }

    public static function activate(): void {
        CC_DB::install();
        CC_Roles::register();
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        flush_rewrite_rules();
    }
}

