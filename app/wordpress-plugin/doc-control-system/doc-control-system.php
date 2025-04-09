<?php
/**
 * Plugin Name: Document Control System
 * Plugin URI: 
 * Description: A comprehensive document control system for managing company procedures and documents
 * Version: 1.0.0
 * Author: Your Company
 * Text Domain: doc-control-system
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('DOC_CONTROL_VERSION', '1.0.0');
define('DOC_CONTROL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOC_CONTROL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-doc-control.php';
require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-doc-control-activator.php';
require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-doc-control-deactivator.php';

// Activation Hook
register_activation_hook(__FILE__, array('Doc_Control_Activator', 'activate'));

// Deactivation Hook
register_deactivation_hook(__FILE__, array('Doc_Control_Deactivator', 'deactivate'));

// Initialize the plugin
function run_doc_control_system() {
    $plugin = new Doc_Control();
    $plugin->run();
}

// Hook into WordPress init
add_action('plugins_loaded', 'run_doc_control_system');

// Ensure tables exist on plugin load
add_action('init', function() {
    if (!get_option('doc_control_version')) {
        Doc_Control_Activator::activate();
    }
}); 