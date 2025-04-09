<?php

class Doc_Control {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->plugin_name = 'doc-control-system';
        $this->version = DOC_CONTROL_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-doc-control-loader.php';
        require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-doc-control-i18n.php';
        require_once DOC_CONTROL_PLUGIN_DIR . 'admin/class-doc-control-admin.php';
        require_once DOC_CONTROL_PLUGIN_DIR . 'public/class-doc-control-public.php';

        $this->loader = new Doc_Control_Loader();
    }

    private function set_locale() {
        $plugin_i18n = new Doc_Control_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {
        $plugin_admin = new Doc_Control_Admin($this->get_plugin_name(), $this->get_version());

        // Add menu items
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Add admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Add AJAX handlers
        $this->loader->add_action('wp_ajax_upload_document', $plugin_admin, 'handle_document_upload');
        $this->loader->add_action('wp_ajax_update_document', $plugin_admin, 'handle_document_update');
        $this->loader->add_action('wp_ajax_delete_document', $plugin_admin, 'handle_document_delete');
        $this->loader->add_action('wp_ajax_search_documents', $plugin_admin, 'handle_document_search');
    }

    private function define_public_hooks() {
        $plugin_public = new Doc_Control_Public($this->get_plugin_name(), $this->get_version());

        // Add public scripts and styles
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Add shortcode for document upload form
        $this->loader->add_action('init', $plugin_public, 'register_shortcodes');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }
} 