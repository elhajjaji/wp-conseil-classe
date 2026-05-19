<?php
/**
 * Plugin Name:       Conseil de classe
 * Plugin URI:        https://github.com/elhajjaji/wp-conseil-classe
 * Description:       Gestion conseils de classe : établissement, années, trimestres, classes, planning, parents, CR, CSV/PDF.
 * Version:           0.4.28
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tested up to:       6.9
 * Author:            aelhajjaji
 * Author URI:        https://profiles.wordpress.org/aelhajjaji/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       conseil-classe
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CC_PLUGIN_VERSION', '0.4.28');
define('CC_PLUGIN_FILE', __FILE__);
define('CC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CC_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once CC_PLUGIN_DIR . 'includes/class-cc-plugin.php';

function cc_plugin_boot(): void {
    $plugin = new CC_Plugin();
    $plugin->init();
}
add_action('plugins_loaded', 'cc_plugin_boot');

register_activation_hook(__FILE__, ['CC_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['CC_Plugin', 'deactivate']);
