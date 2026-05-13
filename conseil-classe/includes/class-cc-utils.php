<?php

if (!defined('ABSPATH')) {
    exit;
}

final class CC_Utils {
    public static function ip_address(): string {
        // Best-effort: ne pas faire confiance pour la sécurité, juste pour log.
        $server = wp_unslash($_SERVER);
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($server[$key])) {
                $val = sanitize_text_field((string) $server[$key]);
                if ($key === 'HTTP_X_FORWARDED_FOR') {
                    $parts = explode(',', $val);
                    $val = trim((string) ($parts[0] ?? ''));
                }
                return substr($val, 0, 45);
            }
        }
        return '';
    }

    public static function normalize_email(string $email): string {
        return strtolower(trim($email));
    }

    public static function sanitize_textarea(?string $value): string {
        return sanitize_textarea_field((string) ($value ?? ''));
    }

    public static function generate_access_code(callable $existsCallback): string {
        // Format: 2 lettres + 4 chiffres + 2 lettres
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $digits = '0123456789';

        while (true) {
            $code =
                $letters[random_int(0, 25)] . $letters[random_int(0, 25)] .
                $digits[random_int(0, 9)] . $digits[random_int(0, 9)] . $digits[random_int(0, 9)] . $digits[random_int(0, 9)] .
                $letters[random_int(0, 25)] . $letters[random_int(0, 25)];

            if (!$existsCallback($code)) {
                return $code;
            }
        }
    }

    public static function mysql_now(): string {
        return current_time('mysql');
    }
}

