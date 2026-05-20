=== Gestion Conseils de Classe ===
Contributors: aelhajjaji
Tags: school, education, parents, csv, pdf
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.4.33
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

1. Upload the plugin folder to `wp-content/plugins/` (or install it from the Plugins screen in your WordPress admin).
2. Activate **Gestion Conseils de Classe** from the Plugins menu.
3. Open the **Conseil de classe** menu and follow the setup order: settings, active school year, active term, classes, councils, parents.

== Frequently Asked Questions ==

= Do I need to create pages on the site? =

Yes. Create WordPress pages containing the shortcodes listed under **Conseil de classe → Settings → Pages (front)**, then select those pages in the dropdown menus.

= What WordPress roles does the plugin add? =

The plugin adds dedicated roles (parent, council administrator, council super-administrator) with appropriate capabilities. WordPress site administrators retain full access to plugin management.

= Does PDF generation work without extra plugins? =

The plugin can generate a PDF via the browser (bundled script). If the **Dompdf** library is available on the server, server-side PDF generation is also supported.

== Screenshots ==

1. Dashboard: shortcuts and active context (school, year, term).
2. Settings: school, association, rules and public pages (shortcodes).
3. Council schedule in the admin area.
4. Parent portal: schedule and registration or deregistration.
5. Dashboard with the year-scoped statistics strip (key figures: parents, classes, councils, reports).
6. Statistics page: assessments by class, parent involvement, coverage rate and term-over-term trends.

== Changelog ==

= 0.4.32 =
* WordPress.org review fixes: plugin renamed to Gestion Conseils de Classe, readme translated to English, inline scripts replaced with wp_print_inline_script_tag(), pdfobject.min.js hosted locally (no CDN calls).

= 0.4.31 =
* Dashboard hero strip scoped to the active year: key stats (parents, classes, councils, reports) for the current year displayed prominently; other years shown in a compact secondary line.
* Added screenshots 5 and 6 (dashboard stats strip and Statistics page).

= 0.4.1 =
* WordPress.org compliance fixes (Plugin Check): input validation/escaping, SQL query hardening and readme adjustments.

= 0.4.0 =
* WordPress-compliant structure: plugin folder `conseil-classe/` with main file `conseil-classe/conseil-classe.php` (old paths removed after uninstall).

= 0.3.2 =
* Repository: removed duplicated `conseil-classe-plugin/` subfolder inside the plugin folder. WordPress sometimes detected two plugin paths ("file not found" after update when the old entry pointed to the duplicate).

= 0.3.1 =
* Main file header: compliant format per the Plugin Handbook (spaced fields, LF line endings, licence "GPL v2 or later") to avoid false positives from Plugin Check.

= 0.3.0 =
* WordPress.org directory preparation: readme.txt and main file headers (URI, licence, PHP and WordPress requirements).
* Artwork (banners, icons, screenshots) organised in the Git repository under `wordpress-org/assets/` with the names expected by Subversion — not included in the plugin ZIP.

= 0.2.9 =
* Previous version (see Git history for details).

== Upgrade Notice ==

= 0.4.32 =
Recommended update: WordPress.org review fixes (plugin name, English readme, inline scripts, no CDN calls).

= 0.4.31 =
Recommended update: dashboard statistics scoped to active year, new screenshots.

= 0.4.1 =
Recommended update for WordPress.org compliance fixes (Plugin Check) and general robustness.

= 0.4.0 =
IMPORTANT: uninstall/remove any old folder (`conseil-classe-plugin` or duplicates) before installing this ZIP. Expected path after install: `wp-content/plugins/conseil-classe/conseil-classe.php`.
