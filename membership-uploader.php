<?php
/**
 * Plugin Name: WPProAtoZ ACF Membership Uploader
 * Description: Tiered frontend uploader for membership sites (PE Tracker). One entry per user + dynamic PMPro tier limits. Built on ACF Pro.
 * Author: WPProAtoZ
 * Author URI: https://wpproatoz.com
 * Version: 2.2.7
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Text Domain: wpproatoz-acf-membership-uploader
 * Update URI: https://github.com/Ahkonsu/wpproatoz-acf-membership-uploader/releases
 * GitHub Plugin URI: https://github.com/Ahkonsu/wpproatoz-acf-membership-uploader
 * GitHub Branch: main
 * Requires Plugins: advanced-custom-fields-pro, paid-memberships-pro
 */

if (!defined('ABSPATH')) exit;

// Define plugin constants
define('IV_PLUGIN_VERSION', '2.2.6');
define('IV_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IV_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load plugin update checker
require_once IV_PLUGIN_DIR . 'plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/Ahkonsu/wpproatoz-acf-membership-uploader/',
    __FILE__,
    'wpproatoz-acf-membership-uploader'
);
$myUpdateChecker->setBranch('main');

// Load all modules
require_once IV_PLUGIN_DIR . 'includes/functions.php';
require_once IV_PLUGIN_DIR . 'includes/class-frontend.php';
require_once IV_PLUGIN_DIR . 'includes/class-validation.php';
require_once IV_PLUGIN_DIR . 'includes/class-pmpro-integration.php';

// Admin files
if (is_admin()) {
    require_once IV_PLUGIN_DIR . 'admin/settings-page.php';
}

// Initialize plugin
add_action('plugins_loaded', 'iv_init_plugin');
function iv_init_plugin() {
    // Future initialization hooks can go here
}

// ====================== PLUGIN ACTIVATION / DEACTIVATION ======================
register_activation_hook(__FILE__, 'iv_activate_plugin');
function iv_activate_plugin() {
    // Flush rewrite rules if using custom CPT
    flush_rewrite_rules();
}