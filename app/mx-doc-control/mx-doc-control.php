<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://yourcompany.com/mx-doc-control
 * @since             1.0.0
 * @package           MX_Doc_Control
 *
 * @wordpress-plugin
 * Plugin Name:       MX Document Control
 * Plugin URI:        https://yourcompany.com/mx-doc-control
 * Description:       A comprehensive document management system for WordPress.
 * Version:           1.0.0
 * Author:            Your Company
 * Author URI:        https://yourcompany.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mx-doc-control
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('MX_DOC_CONTROL_VERSION', '1.0.1');

/**
 * Plugin directory path.
 */
define('MX_DOC_CONTROL_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Plugin directory URL.
 */
define('MX_DOC_CONTROL_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_mx_doc_control() {
    require_once MX_DOC_CONTROL_PLUGIN_DIR . 'includes/class-mx-doc-control-activator.php';
    MX_Doc_Control_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_mx_doc_control() {
    require_once MX_DOC_CONTROL_PLUGIN_DIR . 'includes/class-mx-doc-control-deactivator.php';
    MX_Doc_Control_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_mx_doc_control');
register_deactivation_hook(__FILE__, 'deactivate_mx_doc_control');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require MX_DOC_CONTROL_PLUGIN_DIR . 'includes/class-mx-doc-control.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_mx_doc_control() {
    $plugin = new MX_Doc_Control();
    $plugin->run();
}
run_mx_doc_control(); 