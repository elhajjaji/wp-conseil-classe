<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CC_Logs {
    public static function add(string $action, string $section, ?string $champ, $ancienneValeur, $nouvelleValeur, ?string $description = null): void {
        global $wpdb;

        $user = wp_get_current_user();
        $username = $user && $user->exists() ? $user->user_login : null;

        $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            CC_DB::table('logs'),
            [
                'timestamp' => CC_Utils::mysql_now(),
                'action' => $action,
                'section' => $section,
                'champ' => $champ,
                'ancienne_valeur' => is_null($ancienneValeur) ? null : wp_json_encode($ancienneValeur, JSON_UNESCAPED_UNICODE),
                'nouvelle_valeur' => is_null($nouvelleValeur) ? null : wp_json_encode($nouvelleValeur, JSON_UNESCAPED_UNICODE),
                'utilisateur' => $username,
                'adresse_ip' => CC_Utils::ip_address(),
                'description' => $description,
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
    }
}

