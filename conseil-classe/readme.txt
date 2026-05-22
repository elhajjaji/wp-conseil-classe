=== Gestion Conseils de Classe ===
Contributors: aelhajjaji
Tags: school, education, parents, csv, pdf
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage class councils in WordPress with dashboards, planning, parent participation, reports, and CSV/PDF exports.

== Description ==

Gestion Conseils de Classe helps schools and parent associations run class councils directly in WordPress, from setup and planning to parent participation, report writing, and follow-up.

It combines day-to-day management screens with a statistics dashboard that gives a quick view of assessments by class, parent involvement, coverage, and pending actions for the active period.

Core features:

* School and parent-association settings
* School years and terms (active period)
* Statistics dashboard for assessments, parent involvement, coverage, and operational follow-up
* Classes and council planning (dates, rooms, chairperson)
* Parent records, linked WordPress accounts, and registration management
* Council reports with admin validation and CSV/HTML/PDF exports

The public area uses WordPress pages with shortcodes. These pages are selected in the plugin settings.

The plugin is intended for schools, PTAs, and teams that want one WordPress site for preparation, participation, reporting, and follow-up.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins/` (or install it from the Plugins screen in your WordPress admin).
2. Activate **Gestion Conseils de Classe** from the Plugins menu.
3. Open the **Conseil de classe** menu and configure school settings, the active school year, and the active term.
4. Create the required WordPress pages with the plugin shortcodes, then select those pages in the plugin settings.
5. Add classes, councils, parents, and administrators, then start using the dashboard and exports.

== Frequently Asked Questions ==

= Do I need to create pages on the site? =

Yes. Create WordPress pages containing the shortcodes listed under **Conseil de classe → Settings → Pages (front)**, then assign those pages in the plugin settings.

= What WordPress roles does the plugin add? =

The plugin adds dedicated roles (parent, council administrator, council super-administrator) with appropriate capabilities. WordPress site administrators retain full access to plugin management.

= Does PDF generation work without extra plugins? =

Yes. The plugin can generate a PDF in the browser with the bundled script. If the **Dompdf** library is available on the server, server-side PDF generation is also supported.

== Screenshots ==

1. Statistics dashboard: assessments by class, parent involvement, coverage rate, and operational follow-up for the active period.
2. Main dashboard: shortcuts and active context for the school year and term.
3. Council schedule in the admin area.
4. Parent portal: schedule and registration or deregistration.
5. Settings: school, parent association, and public pages linked to shortcodes.
6. Admin overview dashboard: key figures, setup status, quick actions, and summary charts.

== Changelog ==

= 1.0.0 =
* First public package prepared for WordPress.org review.
* Readme, screenshots, metadata, and release assets aligned for the initial directory release.

== Upgrade Notice ==

= 1.0.0 =
First public version for WordPress.org review.
