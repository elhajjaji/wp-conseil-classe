<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Droits conseil de classe (héritage via capacités WP) :
 * - Super admin (cc_conseil_super_admin) : cc_conseil_super + gestion plugin + parent
 * - Admin (cc_conseil_admin) : cc_conseil_manage + cc_conseil_parent
 * - Parent (cc_parent) : cc_conseil_parent uniquement.
 *
 * Les administrateurs WordPress reçoivent les trois capacités au chargement pour rester équivalents au super-admin du plugin.
 */
final class CC_Roles {
    public const ROLE_SUPER = 'cc_conseil_super_admin';
    public const ROLE_ADMIN = 'cc_conseil_admin';
    public const ROLE_PARENT = 'cc_parent';

    public const CAP_SUPER = 'cc_conseil_super';
    public const CAP_MANAGE = 'cc_conseil_manage';
    public const CAP_PARENT = 'cc_conseil_parent';

    private static function ensure_role(string $slug, string $display, array $caps): void {
        $caps = array_merge(['read' => true], $caps);
        $role = get_role($slug);
        if (!$role) {
            add_role($slug, $display, $caps);
            return;
        }
        foreach ($caps as $cap => $grant) {
            if ($grant) {
                $role->add_cap((string) $cap);
            }
        }
    }

    public static function register(): void {
        self::ensure_role(self::ROLE_SUPER, __('Super admin (conseil de classe)', 'conseil-classe'), [
            self::CAP_SUPER => true,
            self::CAP_MANAGE => true,
            self::CAP_PARENT => true,
        ]);
        self::ensure_role(self::ROLE_ADMIN, __('Admin (conseil de classe)', 'conseil-classe'), [
            self::CAP_MANAGE => true,
            self::CAP_PARENT => true,
        ]);
        self::ensure_role(self::ROLE_PARENT, __('Parent (conseil de classe)', 'conseil-classe'), [
            self::CAP_PARENT => true,
        ]);

        $adminRole = get_role('administrator');
        if ($adminRole) {
            $adminRole->add_cap(self::CAP_SUPER);
            $adminRole->add_cap(self::CAP_MANAGE);
            $adminRole->add_cap(self::CAP_PARENT);
        }
    }

    public static function user_can_manage_plugin(?int $userId = null): bool {
        $userId = $userId ?: get_current_user_id();
        if ($userId <= 0) {
            return false;
        }
        return user_can($userId, self::CAP_MANAGE);
    }

    public static function user_can_super(?int $userId = null): bool {
        $userId = $userId ?: get_current_user_id();
        if ($userId <= 0) {
            return false;
        }
        return user_can($userId, self::CAP_SUPER);
    }

    /** Accès frontend (planning, inscriptions CR) : parent + tous les niveaux gestionnaires. */
    public static function user_can_parent_portal(?int $userId = null): bool {
        $userId = $userId ?: get_current_user_id();
        if ($userId <= 0) {
            return false;
        }
        return user_can($userId, self::CAP_PARENT);
    }

    /** @deprecated */
    public static function current_user_is_parent(): bool {
        return self::user_can_parent_portal();
    }

    /** @deprecated Utiliser user_can_manage_plugin */
    public static function current_user_can_manage(): bool {
        return self::user_can_manage_plugin();
    }

    /** Rôle attribuable lors de la création d’un compte WP depuis la fiche parent. */
    public static function allowed_roles_for_parent_user_dropdown(): array {
        $choices = [];
        $choices[self::ROLE_PARENT] = __('Parent uniquement', 'conseil-classe');

        $uid = get_current_user_id();
        if ($uid > 0 && self::user_can_manage_plugin($uid)) {
            $choices[self::ROLE_ADMIN] = __('Admin conseil (+ parent)', 'conseil-classe');
        }
        if ($uid > 0 && self::user_can_super($uid)) {
            $choices[self::ROLE_SUPER] = __('Super admin conseil (+ admin + parent)', 'conseil-classe');
        }

        return $choices;
    }
}
