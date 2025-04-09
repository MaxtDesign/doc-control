<?php

class Doc_Control_Public {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            DOC_CONTROL_PLUGIN_URL . 'public/css/doc-control-public.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            DOC_CONTROL_PLUGIN_URL . 'public/js/doc-control-public.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'docControlPublic', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('doc_control_public_nonce')
        ));
    }

    public function register_shortcodes() {
        add_shortcode('doc_control_upload_form', array($this, 'render_upload_form'));
    }

    public function render_upload_form($atts) {
        // Only show the form to logged-in users
        if (!is_user_logged_in()) {
            return '<p>Please log in to upload documents.</p>';
        }

        ob_start();
        include_once 'partials/doc-control-public-upload-form.php';
        return ob_get_clean();
    }

    public function handle_public_upload() {
        check_ajax_referer('doc_control_public_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Please log in to upload documents.');
        }

        // Handle file upload logic here
        // This will be implemented in the next step

        wp_send_json_success('Document submitted successfully');
    }
} 