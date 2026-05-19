=== Conseil de classe ===
Contributors: aelhajjaji
Tags: school, education, parents, csv, pdf
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.4.31
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage class councils: school settings, school years/terms, classes, planning, parent registrations, reports, CSV and PDF exports.

== Description ==

**Conseil de classe** helps schools and parent associations manage class council workflows directly in WordPress.

Main features:

* School and parent-association settings
* School years and terms (active period)
* Classes and council planning (dates, rooms, chairperson)
* Parent records, linked WordPress accounts, and slot registrations
* Council reports with admin validation and CSV/HTML/PDF exports (customizable templates)

The parent area is based on WordPress pages that contain shortcodes. These pages are mapped in the plugin settings.

== Installation ==

1. Téléversez le dossier du plugin dans `wp-content/plugins/` (ou installez-le depuis le répertoire des extensions).
2. Activez **Conseil de classe** dans le menu Extensions.
3. Ouvrez le menu **Conseil de classe** et suivez l'ordre de configuration : paramètres, année active, trimestre actif, classes, conseils, parents.

== Frequently Asked Questions ==

= Faut-il créer des pages sur le site ? =

Oui. Créez des pages WordPress contenant les shortcodes indiqués dans **Conseil de classe → Paramètres → Pages (front)**, puis sélectionnez ces pages dans les listes déroulantes.

= Quels rôles WordPress le plugin ajoute-t-il ? =

Des rôles dédiés (parent, administrateur conseil, super administrateur conseil) avec les capacités adaptées. Les administrateurs WordPress du site conservent l'accès complet à la gestion du plugin.

= Le PDF fonctionne-t-il sans extension supplémentaire ? =

Le plugin peut générer un PDF via le navigateur (script intégré). Si la bibliothèque **Dompdf** est disponible sur le site, un PDF peut aussi être généré côté serveur.

== Screenshots ==

1. Tableau de bord : raccourcis et contexte actif (établissement, année, trimestre).
2. Paramètres : établissement, association, règles et pages publiques (shortcodes).
3. Planning des conseils dans l'administration.
4. Espace public : planning et inscription ou désinscription d'un parent.
5. Tableau de bord avec le bandeau de statistiques par année (chiffres clés : parents, classes, conseils, comptes-rendus).
6. Page Statistiques : appréciations par classe, implication des parents, taux de couverture et évolution trimestrielle.

== Changelog ==

= 0.4.1 =
* Correctifs de conformité WordPress.org (Plugin Check) : validation/échappement des entrées, durcissement des requêtes SQL et ajustements readme.

= 0.4.0 =
* Structure conforme aux usages WordPress : dossier du plugin `conseil-classe/` et fichier principal `conseil-classe/conseil-classe.php` (anciens chemins `conseil-classe-plugin/conseil-classe-plugin.php` supprimés après désinstallation).

= 0.3.2 =
* Dépot : suppression du sous-dossier dupliqué `conseil-classe-plugin/` à l’intérieur du dossier du plugin. WordPress détectait parfois deux chemins d’extension (« fichier inexistant » après mise à jour si l’ancienne entrée était celle du doublon).

= 0.3.1 =
* En-tête du fichier principal : format conforme au Plugin Handbook (champs espacés, fins de ligne LF, licence « GPL v2 or later ») pour éviter les faux positifs de Plugin Check et assurer une lecture correcte par WordPress.

= 0.3.0 =
* Préparation au répertoire WordPress.org : fichier readme.txt et en-têtes du fichier principal (URI, licence, prérequis PHP et WordPress).
* Visuels de fiche (bannières, icônes, captures) regroupés dans le dépôt Git sous `wordpress-org/assets/` avec les noms attendus par Subversion — ne pas les inclure dans le ZIP du plugin.

= 0.2.9 =
* Version précédente (voir l'historique Git pour le détail des changements).

== Upgrade Notice ==

= 0.4.1 =
Mise à jour recommandée pour les correctifs de conformité WordPress.org (Plugin Check) et de robustesse générale.

= 0.4.0 =
IMPORTANT : désinstallez / supprimez tout ancien dossier (`conseil-classe-plugin` ou doublons), puis installez ce ZIP — chemin attendu après installation : `wp-content/plugins/conseil-classe/conseil-classe.php`.

= 0.3.2 =
Correction du chemin d’installation du plugin et des métadonnées d’en-tête. Si nécessaire, supprimez l’ancien dossier puis réinstallez le ZIP.

= 0.3.0 =
Mise à jour recommandée si vous préparez une distribution sur WordPress.org ; aucun changement de schéma de base de données spécifique à cette version.
