<?php

class MX_Doc_Control_Public {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Register AJAX handlers
        add_action('wp_ajax_mx_doc_control_submit_document', array($this, 'handle_document_submission'));
        add_action('wp_ajax_mx_doc_control_get_documents', array($this, 'get_document_list'));
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            MX_DOC_CONTROL_PLUGIN_URL . 'public/css/mx-doc-control-public.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            MX_DOC_CONTROL_PLUGIN_URL . 'public/js/mx-doc-control-public.js',
            array('jquery'),
            $this->version,
            false
        );

        // Create nonce for AJAX requests
        $nonce = wp_create_nonce('mx_doc_control_nonce');
        error_log('MX Doc Control: Generated nonce - ' . $nonce);

        wp_localize_script($this->plugin_name, 'mx_doc_control', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
            'debug' => array(
                'is_user_logged_in' => is_user_logged_in(),
                'user_id' => get_current_user_id(),
                'ajax_url' => admin_url('admin-ajax.php')
            )
        ));
    }

    public function display_submission_form() {
        ob_start();
        include MX_DOC_CONTROL_PLUGIN_DIR . 'public/partials/mx-doc-control-public-display.php';
        return ob_get_clean();
    }

    public function handle_document_submission() {
        global $wpdb;
        
        // Log the incoming request
        error_log('MX Doc Control: Document submission request received');
        error_log('POST data: ' . print_r($_POST, true));
        error_log('FILES data: ' . print_r($_FILES, true));
        error_log('Nonce from request: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'not set'));

        // Debug: Check table structure
        $table_name = $wpdb->prefix . 'mx_document_requests';
        $table_structure = $wpdb->get_results("DESCRIBE {$table_name}");
        error_log('Table Structure: ' . print_r($table_structure, true));

        // Verify nonce first
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mx_doc_control_nonce')) {
            error_log('MX Doc Control: Nonce verification failed');
            wp_send_json_error('Security check failed');
            return;
        }
        
        if (!is_user_logged_in()) {
            error_log('MX Doc Control: User not logged in');
            wp_send_json_error('User not logged in');
            return;
        }

        // Validate required fields
        $required_fields = array('requestee_name', 'department', 'destination');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                error_log('MX Doc Control: Missing required field - ' . $field);
                wp_send_json_error('Missing required field: ' . $field);
                return;
            }
        }

        $user_id = get_current_user_id();
        $created_by = sanitize_text_field($_POST['requestee_name']);
        $department = sanitize_text_field($_POST['department']);
        $destination = sanitize_text_field($_POST['destination']);
        $is_revision = isset($_POST['is_revision']) ? 1 : 0;
        $parent_doc_id = isset($_POST['parent_doc_id']) ? sanitize_text_field($_POST['parent_doc_id']) : null;

        // Handle file upload
        if (!isset($_FILES['document'])) {
            error_log('MX Doc Control: No file uploaded');
            wp_send_json_error('No file uploaded');
        }

        $file = $_FILES['document'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log('MX Doc Control: File upload error - ' . $file['error']);
            wp_send_json_error('File upload error: ' . $this->get_upload_error_message($file['error']));
        }

        // Validate file type
        $allowed_types = array('pdf', 'docx', 'xlsx', 'pptx');
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed_types)) {
            error_log('MX Doc Control: Invalid file type - ' . $file_ext);
            wp_send_json_error('Invalid file type. Allowed types: ' . implode(', ', $allowed_types));
        }

        $upload_dir = wp_upload_dir();
        $doc_control_dir = $upload_dir['basedir'] . '/mx-doc-control/' . $department;
        
        if (!file_exists($doc_control_dir)) {
            if (!wp_mkdir_p($doc_control_dir)) {
                error_log('MX Doc Control: Failed to create directory - ' . $doc_control_dir);
                wp_send_json_error('Failed to create directory');
            }
        }

        $original_filename = $file['name'];
        $file_ext = pathinfo($original_filename, PATHINFO_EXTENSION);
        $file_name_without_ext = pathinfo($original_filename, PATHINFO_FILENAME);
        $temp_path = $file['tmp_name'];
        
        // Generate document ID
        $doc_id = $this->generate_document_id($department);
        
        // Create filename with original name and document ID
        $unique_filename = $file_name_without_ext . '-' . $doc_id . '.' . $file_ext;
        $target_path = $doc_control_dir . '/' . $unique_filename;

        if (!move_uploaded_file($temp_path, $target_path)) {
            error_log('MX Doc Control: Failed to move uploaded file to - ' . $target_path);
            wp_send_json_error('Failed to move uploaded file');
        }

        // Debug: Log insert data
        $insert_data = array(
            'user_id' => $user_id,
            'department' => $department,
            'file_name' => $original_filename,
            'original_file_path' => $target_path,
            'requested_destination' => $destination,
            'is_revision' => $is_revision,
            'parent_doc_id' => $parent_doc_id,
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );
        error_log('Insert Data: ' . print_r($insert_data, true));

        // Debug: Generate and log the actual SQL query
        $query = $wpdb->prepare(
            "INSERT INTO $table_name (user_id, department, file_name, original_file_path, requested_destination, is_revision, parent_doc_id, status, created_at) 
            VALUES (%d, %s, %s, %s, %s, %d, %s, %s, %s)",
            $user_id,
            $department,
            $original_filename,
            $target_path,
            $destination,
            $is_revision,
            $parent_doc_id,
            'pending',
            current_time('mysql')
        );
        error_log('Generated SQL Query: ' . $query);

        $result = $wpdb->query($query);

        if ($result === false) {
            error_log('MX Doc Control: Database insert failed - ' . $wpdb->last_error);
            wp_send_json_error('Failed to save request: ' . $wpdb->last_error);
        }

        error_log('MX Doc Control: Document submission successful - ' . $doc_id);
        wp_send_json_success(array(
            'message' => 'Document request submitted successfully',
            'request_id' => $wpdb->insert_id,
            'doc_id' => $doc_id
        ));
    }

    /**
     * Get user-friendly upload error message
     */
    private function get_upload_error_message($error_code) {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'A PHP extension stopped the file upload';
            default:
                return 'Unknown upload error';
        }
    }

    /**
     * Generate a unique document ID based on department and timestamp
     * 
     * @param string $department The department code
     * @return string A unique document ID
     */
    private function generate_document_id($department) {
        // Get department code (first 3 letters)
        $dept_code = strtoupper(substr($department, 0, 3));
        
        // Get current year
        $year = date('Y');
        
        // Get current timestamp for uniqueness
        $timestamp = time();
        
        // Get a random 4-digit number
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Format: DEPT-YEAR-TIMESTAMP-RANDOM
        return $dept_code . '-' . $year . '-' . $timestamp . '-' . $random;
    }

    public function get_document_list() {
        check_ajax_referer('mx_doc_control_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('User not logged in');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'mx_documents';
        
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $per_page = 20;
        $offset = ($page - 1) * $per_page;

        $where = '';
        if (!empty($search)) {
            $where = $wpdb->prepare(
                " WHERE doc_id LIKE %s OR description LIKE %s",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name" . $where);
        $total_pages = ceil($total_items / $per_page);

        $documents = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name" . $where . " ORDER BY created_date DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ));

        wp_send_json_success(array(
            'documents' => $documents,
            'total_pages' => $total_pages,
            'current_page' => $page
        ));
    }
} 