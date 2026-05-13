# Rôles et droits

Le plugin enregistre des **rôles WordPress** dédiés et des **capacités** (permissions fines).

## Rôles

| Rôle (identifiant technique) | Usage typique |
|------------------------------|---------------|
| **Super admin (conseil de classe)** (`cc_conseil_super_admin`) | Gestion complète du plugin + fonctions « parent » ; peut attribuer le rôle super admin à d’autres comptes (selon l’interface). |
| **Admin (conseil de classe)** (`cc_conseil_admin`) | Équipe conseil : menus d’administration du plugin + accès portail parent. |
| **Parent (conseil de classe)** (`cc_parent`) | Accès **lecture / action** sur l’espace parents (planning, inscriptions, compte-rendu) **uniquement**. |

Les **administrateurs WordPress** (`administrator`) reçoivent automatiquement les capacités équivalentes au super admin du plugin (gestion totale).

## Capacités principales

- **`cc_conseil_super`** : pouvoirs étendus (ex. attribution de rôles sensibles).
- **`cc_conseil_manage`** : accès aux écrans d’administration **Conseil de classe** (paramètres, années, classes, exports, etc.).
- **`cc_conseil_parent`** : droit d’utiliser le **portail parent** (pages protégées du plugin).

Un utilisateur doit avoir **`cc_conseil_parent`** (directement ou via un rôle) pour ouvrir les pages associées au planning, « Mes conseils » et au formulaire de compte-rendu (hors simple connexion WordPress sur la page dédiée).

## Création des comptes

Lors de la création d’une fiche parent, le menu **Rôle du compte WordPress** propose typiquement :

- Parent uniquement ;
- **Admin conseil** (+ parent) — si l’utilisateur connecté a le droit de gestion ;
- **Super admin conseil** — réservé aux comptes qui ont déjà la capacité super admin du plugin.

Les imports CSV « admins conseil » permettent de préciser une colonne **Rôle** (`admin` ou `super`). Voir [Imports et exports CSV](imports-exports-csv.md).
