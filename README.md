# Conseil de classe — plugin WordPress

Plugin de gestion des **conseils de classe** pour WordPress : paramètres établissement / association, années et trimestres, classes, planning, inscriptions parents, comptes-rendus et exports (CSV / PDF).

**Dépôt source :** [github.com/elhajjaji/wp-conseil-classe](https://github.com/elhajjaji/wp-conseil-classe)

Le code du plugin se trouve dans le dossier **`conseil-classe-plugin/`** à la racine de ce dépôt.

---

## Prérequis

- WordPress récent (testé en usage courant avec les versions stables actuelles).
- PHP **7.4** minimum (recommandé : PHP 8.x).

---

## Installation

1. Copiez le dossier **`conseil-classe-plugin`** depuis ce dépôt.
2. Déposez-le dans le répertoire des extensions de votre site :  
   `wp-content/plugins/conseil-classe-plugin/`
3. Dans l’administration WordPress, menu **Extensions**, activez **Conseil de classe**.

> **Mise à jour :** remplacez tout le dossier `conseil-classe-plugin` par la nouvelle version, ou utilisez un ZIP téléversé (Extensions → Ajouter).

---

## Première configuration (admin)

Après activation, ouvrez le menu **Conseil de classe** et configurez dans l’ordre :

| Écran | Action |
|--------|--------|
| **Paramètres** | Établissement, association parents, quota d’inscriptions par conseil, pages du site (shortcodes — voir ci‑dessous). |
| **Années scolaires** | Créer au moins une année et la définir **active**. |
| **Trimestres** | Choisir le **trimestre actif**. |
| **Classes** | Renseigner les classes pour l’année active. |
| **Conseils** | Saisir le planning (dates, salles, etc.). |
| **Parents** | Création / import CSV parents et équipe conseil, export. |

Sans **année** et **trimestre actifs**, le planning et les écrans parents ne peuvent pas fonctionner correctement.

---

## Pages publiques (shortcodes)

Créez des **pages WordPress** et associez-les sous **Conseil de classe → Paramètres → Pages (front)**.

| Shortcode | Rôle |
|-----------|------|
| `[cc_parent_login]` | Espace parents (connexion WordPress, liens vers le reste de l’espace). |
| `[cc_plannings]` | Planning des conseils (inscription / désinscription pour les comptes autorisés). |
| `[cc_my_councils]` | Conseils du parent (actifs + historique), lien vers la rédaction du compte-rendu. |
| `[cc_report_form]` | Formulaire de compte-rendu (souvent ouvert avec `?council_id=…` depuis « Mes conseils »). |

---

## Imports / exports CSV (aperçu)

- **Classes** : export / import `nom`, `niveau` (mise à jour si la classe existe pour l’année active).
- **Conseils** : export / import du planning pour l’année et le trimestre actifs.
- **Parents** : deux modèles possibles (parents seuls / admins conseil) — voir l’écran **Parents** dans l’admin.
- **Comptes-rendus** : export CSV depuis le menu dédié.
- **Paramètres** : export / import d’une ligne CSV (sauvegarde / restauration des champs paramétrables).

---

## Structure du dépôt

```
wp-conseil-classe/
├── README.md                 ← ce fichier
└── conseil-classe-plugin/    ← dossier à copier dans wp-content/plugins/
    ├── conseil-classe-plugin.php
    ├── assets/
    ├── admin/
    ├── includes/
    └── public/
```

---

## Licence

Le plugin est publié sous licence **GPL v2 ou ultérieure**, conformément à l’écosystème WordPress.

---

## Contribution

Les rapports de bug et les propositions d’évolution sont les bienvenues via les [issues GitHub](https://github.com/elhajjaji/wp-conseil-classe/issues) du dépôt.
