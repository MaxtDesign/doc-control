<?php

class Form_Handler {
    private $processor;

    public function __construct() {
        $this->processor = new Document_Processor();
        
        // Frontend form submission
        add_action('wp_ajax_submit_document', array($this, 'handle_frontend_submission'));
        add_action('wp_ajax_nopriv_submit_document', array($this, 'handle_frontend_submission'));
        
        // Admin form submissions
        add_action('admin_post_process_document', array($this, 'handle_admin_processing'));
        add_action('admin_post_complete_document', array($this, 'handle_admin_completion'));
        add_action('admin_post_submit_revision', array($this, 'handle_revision_submission'));
    }

    public function handle_frontend_submission() {
        // Verify nonce
        if (!isset($_POST['doc_control_nonce']) || !wp_verify_nonce($_POST['doc_control_nonce'], 'submit_document')) {
            wp_send_json_error(__('Invalid security token.', 'doc-control-system'));
        }

        // Validate required fields
        if (empty($_POST['department']) || empty($_POST['originator']) || empty($_POST['pdf_destination'])) {
            wp_send_json_error(__('All fields are required.', 'doc-control-system'));
        }

        // Validate file
        if (empty($_FILES['document'])) {
            wp_send_json_error(__('No file uploaded.', 'doc-control-system'));
        }

        // Process document
        $result = $this->processor->process_document(
            $_FILES['document'],
            sanitize_text_field($_POST['department']),
            sanitize_text_field($_POST['originator']),
            sanitize_text_field($_POST['pdf_destination'])
        );

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        // Send success response
        wp_send_json_success(array(
            'message' => __('Document submitted successfully.', 'doc-control-system'),
            'doc_number' => $result['doc_number']
        ));
    }

    public function handle_admin_processing() {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'process_document_' . $_POST['document_id'])) {
            wp_die(__('Invalid security token.', 'doc-control-system'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'doc-control-system'));
        }

        // Validate document ID
        if (empty($_POST['document_id'])) {
            wp_die(__('Invalid document ID.', 'doc-control-system'));
        }

        // Redirect back to admin page
        wp_redirect(add_query_arg(
            array(
                'page' => 'doc-control',
                'message' => 'processing'
            ),
            admin_url('admin.php')
        ));
        exit;
    }

    public function handle_admin_completion() {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'complete_document_' . $_POST['document_id'])) {
            wp_die(__('Invalid security token.', 'doc-control-system'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'doc-control-system'));
        }

        // Validate required fields
        if (empty($_POST['document_id']) || empty($_POST['created_by'])) {
            wp_die(__('All fields are required.', 'doc-control-system'));
        }

        // Complete document
        $result = $this->processor->complete_document(
            intval($_POST['document_id']),
            sanitize_text_field($_POST['created_by'])
        );

        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }

        // Redirect back to admin page
        wp_redirect(add_query_arg(
            array(
                'page' => 'doc-control',
                'message' => 'completed'
            ),
            admin_url('admin.php')
        ));
        exit;
    }

    public function handle_revision_submission() {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'submit_revision_' . $_POST['document_id'])) {
            wp_die(__('Invalid security token.', 'doc-control-system'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'doc-control-system'));
        }

        // Validate required fields
        if (empty($_POST['document_id']) || empty($_POST['revised_by']) || empty($_FILES['document'])) {
            wp_die(__('All fields are required.', 'doc-control-system'));
        }

        // Process revision
        $result = $this->processor->process_revision(
            intval($_POST['document_id']),
            $_FILES['document'],
            sanitize_text_field($_POST['revised_by'])
        );

        if (is_wp_error($result)) {
            wp_die($result->get_error_message());
        }

        // Redirect back to admin page
        wp_redirect(add_query_arg(
            array(
                'page' => 'doc-control',
                'message' => 'revision_submitted'
            ),
            admin_url('admin.php')
        ));
        exit;
    }
} 