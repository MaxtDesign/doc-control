<?php

class Doc_Control_Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->check_tables();
        
        // Add AJAX handlers
        add_action('wp_ajax_doc_control_validate_path', array($this, 'handle_path_validation'));
        
        // Add admin post handler for settings
        add_action('admin_post_save_doc_control_settings', array($this, 'save_settings'));
    }

    private function check_tables() {
        global $wpdb;
        
        // Check if tables exist
        $documents_table = $wpdb->prefix . 'doc_control_documents';
        $history_table = $wpdb->prefix . 'doc_control_history';
        $logs_table = $wpdb->prefix . 'doc_control_verification_logs';
        
        $tables_exist = $wpdb->get_results("SHOW TABLES LIKE '$documents_table'");
        
        if (empty($tables_exist)) {
            // Tables don't exist, run activation
            require_once DOC_CONTROL_PLUGIN_DIR . 'includes/class-doc-control-activator.php';
            Doc_Control_Activator::activate();
        }
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            DOC_CONTROL_PLUGIN_URL . 'admin/css/doc-control-admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            DOC_CONTROL_PLUGIN_URL . 'admin/js/doc-control-admin.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'docControlAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('doc_control_admin_nonce')
        ));
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            'Document Control System',
            'Doc Control',
            'manage_doc_control',
            $this->plugin_name,
            array($this, 'display_plugin_admin_dashboard'),
            'dashicons-media-document',
            30
        );

        add_submenu_page(
            $this->plugin_name,
            'Document Management',
            'Manage Documents',
            'manage_doc_control',
            $this->plugin_name . '-documents',
            array($this, 'display_document_management_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Verification Logs',
            'Verification Logs',
            'manage_doc_control',
            $this->plugin_name . '-logs',
            array($this, 'display_verification_logs_page')
        );

        add_submenu_page(
            $this->plugin_name,
            'Settings',
            'Settings',
            'manage_doc_control',
            $this->plugin_name . '-settings',
            array($this, 'display_settings_page')
        );
    }

    public function display_plugin_admin_dashboard() {
        include_once 'partials/doc-control-admin-dashboard.php';
    }

    public function display_document_management_page() {
        include_once 'partials/doc-control-admin-documents.php';
    }

    public function display_verification_logs_page() {
        include_once 'partials/doc-control-admin-logs.php';
    }

    public function display_settings_page() {
        include_once 'partials/doc-control-admin-settings.php';
    }

    public function handle_document_upload() {
        check_ajax_referer('doc_control_nonce', 'nonce');

        if (!current_user_can('upload_documents')) {
            wp_send_json_error('Permission denied');
        }

        // Handle file upload logic here
        // This will be implemented in the next step

        wp_send_json_success('Document uploaded successfully');
    }

    public function handle_document_update() {
        check_ajax_referer('doc_control_nonce', 'nonce');

        if (!current_user_can('edit_documents')) {
            wp_send_json_error('Permission denied');
        }

        // Handle document update logic here
        // This will be implemented in the next step

        wp_send_json_success('Document updated successfully');
    }

    public function handle_document_delete() {
        check_ajax_referer('doc_control_nonce', 'nonce');

        if (!current_user_can('delete_documents')) {
            wp_send_json_error('Permission denied');
        }

        // Handle document deletion logic here
        // This will be implemented in the next step

        wp_send_json_success('Document deleted successfully');
    }

    public function handle_document_search() {
        check_ajax_referer('doc_control_nonce', 'nonce');

        if (!current_user_can('manage_doc_control')) {
            wp_send_json_error('Permission denied');
        }

        // Handle document search logic here
        // This will be implemented in the next step

        wp_send_json_success(array(
            'documents' => array()
        ));
    }

    public function get_documents() {
        global $wpdb;
        
        // Get pagination parameters
        $items_per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $items_per_page;
        
        // Build the query
        $query = "SELECT * FROM {$wpdb->prefix}doc_control_documents";
        
        // Add search if provided
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $search = '%' . $wpdb->esc_like($_GET['s']) . '%';
            $query .= $wpdb->prepare(
                " WHERE doc_number LIKE %s 
                OR description LIKE %s 
                OR created_by LIKE %s 
                OR revised_by LIKE %s",
                $search, $search, $search, $search
            );
        }
        
        // Add ordering
        $query .= " ORDER BY created_date DESC";
        
        // Add pagination
        $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $items_per_page, $offset);
        
        return $wpdb->get_results($query);
    }

    public function get_total_documents() {
        global $wpdb;
        
        $query = "SELECT COUNT(*) FROM {$wpdb->prefix}doc_control_documents";
        
        // Add search if provided
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $search = '%' . $wpdb->esc_like($_GET['s']) . '%';
            $query .= $wpdb->prepare(
                " WHERE doc_number LIKE %s 
                OR description LIKE %s 
                OR created_by LIKE %s 
                OR revised_by LIKE %s",
                $search, $search, $search, $search
            );
        }
        
        return $wpdb->get_var($query);
    }

    public function get_pending_documents() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}doc_control_documents WHERE status = 'pending'");
    }

    public function get_missing_files() {
        global $wpdb;
        $documents = $wpdb->get_results("SELECT file_location FROM {$wpdb->prefix}doc_control_documents");
        $missing = 0;
        foreach ($documents as $doc) {
            if (!file_exists($doc->file_location)) {
                $missing++;
            }
        }
        return $missing;
    }

    public function get_last_verification() {
        global $wpdb;
        $last_verification = $wpdb->get_var("SELECT MAX(verification_date) FROM {$wpdb->prefix}doc_control_verification_logs");
        return $last_verification ? date('Y-m-d', strtotime($last_verification)) : 'Never';
    }

    public function get_recent_activity() {
        global $wpdb;
        return $wpdb->get_results("
            SELECT h.*, d.doc_number 
            FROM {$wpdb->prefix}doc_control_history h
            JOIN {$wpdb->prefix}doc_control_documents d ON h.doc_id = d.id
            ORDER BY h.action_date DESC
            LIMIT 10
        ");
    }

    public function get_backup_status() {
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/doc-control-backups';
        return file_exists($backup_dir) ? 'Configured' : 'Not Configured';
    }

    public function get_storage_status() {
        $upload_dir = wp_upload_dir();
        $total_space = disk_total_space($upload_dir['basedir']);
        $free_space = disk_free_space($upload_dir['basedir']);
        $used_space = $total_space - $free_space;
        $used_percentage = round(($used_space / $total_space) * 100, 2);
        return "{$used_percentage}% used";
    }

    public function get_last_backup() {
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/doc-control-backups';
        if (!file_exists($backup_dir)) {
            return 'Never';
        }
        $files = glob($backup_dir . '/*.zip');
        if (empty($files)) {
            return 'Never';
        }
        $latest = max($files);
        return date('Y-m-d H:i:s', filemtime($latest));
    }

    public function get_verification_logs() {
        global $wpdb;
        
        // Get pagination parameters
        $items_per_page = 20;
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $items_per_page;
        
        // Build the query with JOIN to get document information
        $query = "SELECT vl.*, d.doc_number, d.description 
                FROM {$wpdb->prefix}doc_control_verification_logs vl
                LEFT JOIN {$wpdb->prefix}doc_control_documents d ON vl.doc_id = d.id";
        
        // Add search if provided
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $search = '%' . $wpdb->esc_like($_GET['s']) . '%';
            $query .= $wpdb->prepare(
                " WHERE d.doc_number LIKE %s 
                OR d.description LIKE %s 
                OR vl.status LIKE %s",
                $search, $search, $search
            );
        }
        
        // Add ordering
        $query .= " ORDER BY vl.verification_date DESC";
        
        // Add pagination
        $query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $items_per_page, $offset);
        
        return $wpdb->get_results($query);
    }

    public function get_total_verification_logs() {
        global $wpdb;
        
        $query = "SELECT COUNT(*) 
                FROM {$wpdb->prefix}doc_control_verification_logs vl
                LEFT JOIN {$wpdb->prefix}doc_control_documents d ON vl.doc_id = d.id";
        
        // Add search if provided
        if (isset($_GET['s']) && !empty($_GET['s'])) {
            $search = '%' . $wpdb->esc_like($_GET['s']) . '%';
            $query .= $wpdb->prepare(
                " WHERE d.doc_number LIKE %s 
                OR d.description LIKE %s 
                OR vl.status LIKE %s",
                $search, $search, $search
            );
        }
        
        return $wpdb->get_var($query);
    }

    public function get_permission_issues_count() {
        global $wpdb;
        return $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->prefix}doc_control_verification_logs 
            WHERE status = 'permission_denied'
        ");
    }

    public function get_settings() {
        $default_settings = array(
            // File Storage Settings
            'master_doc_path' => wp_upload_dir()['basedir'] . '/doc-control/master',
            'pdf_doc_path' => wp_upload_dir()['basedir'] . '/doc-control/pdf',
            'backup_path' => wp_upload_dir()['basedir'] . '/doc-control/backups',
            
            // Verification Settings
            'verification_frequency' => 'daily',
            'excluded_paths' => '',
            
            // Backup Settings
            'backup_frequency' => 'weekly',
            'backup_retention' => '30',
            
            // Notification Settings
            'notify_missing_files' => '1',
            'notify_permission_issues' => '1',
            'notification_email' => get_option('admin_email')
        );

        $settings = get_option('doc_control_settings', array());
        return wp_parse_args($settings, $default_settings);
    }

    public function handle_path_validation() {
        check_ajax_referer('doc_control_admin_nonce', 'nonce');

        if (!current_user_can('manage_doc_control')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        $path = isset($_POST['path']) ? sanitize_text_field($_POST['path']) : '';
        
        if (empty($path)) {
            wp_send_json_error(array('message' => 'Path cannot be empty'));
        }

        // Check if path exists and is writable
        if (!file_exists($path)) {
            // Try to create the directory
            if (!wp_mkdir_p($path)) {
                wp_send_json_error(array('message' => 'Directory could not be created'));
            }
        }

        if (!is_writable($path)) {
            wp_send_json_error(array('message' => 'Directory is not writable'));
        }

        wp_send_json_success(array('message' => 'Path is valid'));
    }

    public function save_settings() {
        if (!current_user_can('manage_doc_control')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        check_admin_referer('doc_control_settings_nonce', 'doc_control_settings_nonce');

        $settings = array(
            // File Storage Settings
            'master_doc_path' => sanitize_text_field($_POST['master_doc_path']),
            'pdf_doc_path' => sanitize_text_field($_POST['pdf_doc_path']),
            'backup_path' => sanitize_text_field($_POST['backup_path']),
            
            // Verification Settings
            'verification_frequency' => sanitize_text_field($_POST['verification_frequency']),
            'excluded_paths' => sanitize_textarea_field($_POST['excluded_paths']),
            
            // Backup Settings
            'backup_frequency' => sanitize_text_field($_POST['backup_frequency']),
            'backup_retention' => absint($_POST['backup_retention']),
            
            // Notification Settings
            'notify_missing_files' => isset($_POST['notify_missing_files']) ? '1' : '0',
            'notify_permission_issues' => isset($_POST['notify_permission_issues']) ? '1' : '0',
            'notification_email' => sanitize_email($_POST['notification_email'])
        );

        // Validate settings
        if (!in_array($settings['verification_frequency'], array('hourly', 'daily', 'weekly'))) {
            $settings['verification_frequency'] = 'daily';
        }

        if (!in_array($settings['backup_frequency'], array('daily', 'weekly', 'monthly'))) {
            $settings['backup_frequency'] = 'weekly';
        }

        if ($settings['backup_retention'] < 1 || $settings['backup_retention'] > 365) {
            $settings['backup_retention'] = 30;
        }

        if (!is_email($settings['notification_email'])) {
            $settings['notification_email'] = get_option('admin_email');
        }

        // Create storage directories if they don't exist
        $paths_to_create = array(
            $settings['master_doc_path'],
            $settings['pdf_doc_path'],
            $settings['backup_path']
        );

        foreach ($paths_to_create as $path) {
            if (!empty($path) && !file_exists($path)) {
                wp_mkdir_p($path);
            }
        }

        // Save settings
        update_option('doc_control_settings', $settings);

        // Add settings updated message
        add_settings_error(
            'doc_control_messages',
            'doc_control_message',
            __('Settings Saved', 'doc-control'),
            'updated'
        );

        // Redirect back to the settings page
        wp_safe_redirect(
            add_query_arg(
                array(
                    'page' => $this->plugin_name . '-settings',
                    'settings-updated' => 'true'
                ),
                admin_url('admin.php')
            )
        );
        exit;
    }
} 