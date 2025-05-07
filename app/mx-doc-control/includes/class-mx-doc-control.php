<?php

class MX_Doc_Control {
    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct() {
        $this->version = MX_DOC_CONTROL_VERSION;
        $this->plugin_name = 'mx-doc-control';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once MX_DOC_CONTROL_PLUGIN_DIR . 'includes/class-mx-doc-control-loader.php';
        require_once MX_DOC_CONTROL_PLUGIN_DIR . 'admin/class-mx-doc-control-admin.php';
        require_once MX_DOC_CONTROL_PLUGIN_DIR . 'public/class-mx-doc-control-public.php';
        
        $this->loader = new MX_Doc_Control_Loader();
    }

    private function define_admin_hooks() {
        $plugin_admin = new MX_Doc_Control_Admin($this->get_plugin_name(), $this->get_version());

        // Add menu items
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        
        // Add Settings link to the plugin
        $plugin_basename = plugin_basename(plugin_dir_path(__FILE__) . 'mx-doc-control.php');
        $this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links');

        // Register admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    private function define_public_hooks() {
        $plugin_public = new MX_Doc_Control_Public($this->get_plugin_name(), $this->get_version());

        // Register public scripts and styles
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        
        // Register shortcode for the submission form
        $this->loader->add_shortcode('mx_doc_control_form', $plugin_public, 'display_submission_form');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_loader() {
        return $this->loader;
    }

    public function get_version() {
        return $this->version;
    }
} 