# Pages et shortcodes

## Shortcodes disponibles

| Shortcode | Rôle |
|-----------|------|
| `[cc_parent_login]` | Page d’**information / liens** vers la connexion WordPress et le reste de l’espace parents. |
| `[cc_plannings]` | Affiche le **planning** des conseils et les actions d’**inscription** / **désinscription**. |
| `[cc_my_councils]` | **Mes conseils** : conseils en cours et historique, liens vers le formulaire de compte-rendu. |
| `[cc_report_form]` | **Formulaire de compte-rendu** ; en pratique souvent ouvert avec `?council_id=…` depuis « Mes conseils ». |

## Associer les pages (admin)

**Conseil de classe → Paramètres → Pages (front)**

Pour chaque shortcode, sélectionnez la **page WordPress** correspondante (liste déroulante). Les pages doivent **contenir** le shortcode indiqué dans l’intitulé du réglage.

## Accès au site public

- **Page « Connexion / compte »** : en général accessible sans être connecté (liens vers `wp-login.php`).
- **Planning**, **Mes conseils**, **Formulaire compte-rendu** : pages **protégées**. Les visiteurs non connectés sont **redirigés vers la connexion** ; les utilisateurs connectés sans capacité `cc_conseil_parent` reçoivent un **accès refusé (403)**.

Il faut donc attribuer aux parents (et à l’équipe conseil qui utilise le portail) un rôle comportant la capacité parent du plugin. Détails : [Rôles et droits](roles-et-droits.md).
