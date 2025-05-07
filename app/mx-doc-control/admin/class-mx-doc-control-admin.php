<?php

class MX_Doc_Control_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            MX_DOC_CONTROL_PLUGIN_URL . 'admin/css/mx-doc-control-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            MX_DOC_CONTROL_PLUGIN_URL . 'admin/js/mx-doc-control-admin.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'mx_doc_control', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mx_doc_control_nonce')
        ));
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            'MX Doc Control',
            'MX Doc Control',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_admin_page'),
            'dashicons-media-document',
            20
        );

        add_submenu_page(
            $this->plugin_name,
            'Document Requests',
            'Document Requests',
            'manage_options',
            $this->plugin_name . '-requests',
            array($this, 'display_plugin_requests_page')
        );
    }

    public function add_action_links($links) {
        $settings_link = array(
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name) . '">' . __('Settings', 'mx-doc-control') . '</a>',
        );
        return array_merge($settings_link, $links);
    }

    public function display_plugin_admin_page() {
        include_once MX_DOC_CONTROL_PLUGIN_DIR . 'admin/partials/mx-doc-control-admin-display.php';
    }

    public function display_plugin_requests_page() {
        include_once MX_DOC_CONTROL_PLUGIN_DIR . 'admin/partials/mx-doc-control-requests-display.php';
    }

    public function get_next_doc_id() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mx_documents';
        
        $last_doc = $wpdb->get_var("SELECT doc_id FROM $table_name ORDER BY id DESC LIMIT 1");
        
        if (!$last_doc) {
            return 'DOC0001';
        }
        
        $number = intval(substr($last_doc, 3)) + 1;
        return 'DOC' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function process_document_request() {
        check_ajax_referer('mx_doc_control_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $request_id = intval($_POST['request_id']);
        $doc_id = sanitize_text_field($_POST['doc_id']);
        $master_path = sanitize_text_field($_POST['master_path']);
        $document_path = sanitize_text_field($_POST['document_path']);
        
        global $wpdb;
        $requests_table = $wpdb->prefix . 'mx_document_requests';
        $documents_table = $wpdb->prefix . 'mx_documents';
        
        $request = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $requests_table WHERE id = %d",
            $request_id
        ));
        
        if (!$request) {
            wp_send_json_error('Request not found');
        }
        
        $wpdb->insert(
            $documents_table,
            array(
                'doc_id' => $doc_id,
                'description' => $request->file_name,
                'created_by' => get_user_by('id', $request->user_id)->display_name,
                'created_date' => current_time('mysql'),
                'master_file_path' => $master_path,
                'document_path' => $document_path,
                'department' => $request->department,
                'status' => 'active'
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        $wpdb->update(
            $requests_table,
            array('status' => 'completed'),
            array('id' => $request_id),
            array('%s'),
            array('%d')
        );
        
        wp_send_json_success();
    }
} 