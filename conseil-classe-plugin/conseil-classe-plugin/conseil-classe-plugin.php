<?php
/**
 * Plugin Name: Conseil de classe
 * Description: Gestion des conseils de classe (paramètres établissement, années/trimestres/classes, planning, inscriptions parents, comptes-rendus, exports).
 * Version: 0.2.9
 * Author: Conseil Classe
 * Text Domain: conseil-classe
 */

if (!defined('ABSPATH')) {
    exit;
}

define('CC_PLUGIN_VERSION', '0.2.9');
define('CC_PLUGIN_FILE', __FILE__);
define('CC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CC_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once CC_PLUGIN_DIR . 'includes/class-cc-plugin.php';
require_once CC_PLUGIN_DIR . 'conseil-classe.php';

function cc_plugin_boot(): void {
    $plugin = new CC_Plugin();
    $plugin->init();
}
add_action('plugins_loaded', 'cc_plugin_boot');

register_activation_hook(__FILE__, ['CC_Plugin', 'activate']);
register_deactivation_hook(__FILE__, ['CC_Plugin', 'deactivate']);

