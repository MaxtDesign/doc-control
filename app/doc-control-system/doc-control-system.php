<?php
/**
 * Plugin Name: Document Control System
 * Plugin URI: https://github.com/MaxtDesign/doc-control
 * Description: A document control system for managing department procedures and marketing workflows
 * Version: 1.0.0
 * Author: MaxtDesign
 * Author URI: https://maxtdesign.com
 * Text Domain: doc-control-system
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('DOC_CONTROL_VERSION', '1.0.0');
define('DOC_CONTROL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOC_CONTROL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-document-post-type.php';
require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-document-metabox.php';
require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-document-list-table.php';
require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-document-processor.php';
require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-form-handler.php';

// Activation Hook
register_activation_hook(__FILE__, 'doc_control_activate');
function doc_control_activate() {
    // Create necessary database tables
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    $table_name = $wpdb->prefix . 'doc_control_documents';
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        doc_number varchar(10) NOT NULL,
        file_name varchar(255) NOT NULL,
        department varchar(50) NOT NULL,
        originator varchar(100) NOT NULL,
        created_date datetime DEFAULT CURRENT_TIMESTAMP,
        created_by varchar(100),
        revised_by varchar(10),
        revision_number int DEFAULT 0,
        master_file_location text,
        pdf_file_location text,
        status varchar(20) DEFAULT 'pending',
        PRIMARY KEY  (id),
        UNIQUE KEY doc_number (doc_number)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Initialize the plugin
function doc_control_init() {
    // Initialize post type
    new Document_Post_Type();
    
    // Initialize metabox
    new Document_Metabox();
    
    // Initialize list table
    new Document_List_Table();
    
    // Initialize document processor
    new Document_Processor();
    
    // Initialize form handler
    new Form_Handler();
}
add_action('plugins_loaded', 'doc_control_init');

// Add admin menu
function doc_control_admin_menu() {
    add_menu_page(
        'Document Control',
        'Doc Control',
        'manage_options',
        'doc-control',
        'doc_control_admin_page',
        'dashicons-media-document',
        30
    );
}
add_action('admin_menu', 'doc_control_admin_menu');

// Admin page callback
function doc_control_admin_page() {
    require_once DOC_CONTROL_PLUGIN_DIR . 'templates/admin-process-view.php';
}

// Add shortcode for frontend form
function doc_control_form_shortcode() {
    ob_start();
    require_once DOC_CONTROL_PLUGIN_DIR . 'templates/submission-form.php';
    return ob_get_clean();
}
add_shortcode('doc_control_form', 'doc_control_form_shortcode');

// Enqueue admin scripts and styles
function doc_control_admin_enqueue_scripts($hook) {
    if ('toplevel_page_doc-control' !== $hook) {
        return;
    }
    
    wp_enqueue_style(
        'doc-control-admin',
        DOC_CONTROL_PLUGIN_URL . 'admin/css/admin.css',
        array(),
        DOC_CONTROL_VERSION
    );
    
    wp_enqueue_script(
        'doc-control-admin',
        DOC_CONTROL_PLUGIN_URL . 'admin/js/admin.js',
        array('jquery'),
        DOC_CONTROL_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'doc_control_admin_enqueue_scripts');

// Enqueue frontend scripts and styles
function doc_control_enqueue_scripts() {
    wp_enqueue_style(
        'doc-control-public',
        DOC_CONTROL_PLUGIN_URL . 'public/css/public.css',
        array(),
        DOC_CONTROL_VERSION
    );
    
    wp_enqueue_script(
        'doc-control-public',
        DOC_CONTROL_PLUGIN_URL . 'public/js/public.js',
        array('jquery'),
        DOC_CONTROL_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'doc_control_enqueue_scripts'); 
