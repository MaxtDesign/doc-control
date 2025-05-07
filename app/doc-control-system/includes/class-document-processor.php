<?php

class Document_Processor {
    private $upload_dir;
    private $temp_dir;

    public function __construct() {
        $this->upload_dir = wp_upload_dir();
        $this->temp_dir = $this->upload_dir['basedir'] . '/doc-control-temp';
        
        // Create temp directory if it doesn't exist
        if (!file_exists($this->temp_dir)) {
            wp_mkdir_p($this->temp_dir);
        }
    }

    public function process_document($file, $department, $originator, $pdf_destination) {
        // Validate file
        if (!$this->validate_file($file)) {
            return new WP_Error('invalid_file', __('Invalid file type. Only PPTX files are allowed.', 'doc-control-system'));
        }

        // Generate document number
        $doc_number = $this->generate_doc_number();

        // Process file
        $file_info = pathinfo($file['name']);
        $new_filename = $this->generate_filename($file_info['filename'], $doc_number);
        
        // Move file to temp directory
        $temp_path = $this->temp_dir . '/' . $new_filename . '.pptx';
        if (!move_uploaded_file($file['tmp_name'], $temp_path)) {
            return new WP_Error('upload_error', __('Failed to move uploaded file.', 'doc-control-system'));
        }

        // Create document record
        $document_id = $this->create_document_record(array(
            'doc_number' => $doc_number,
            'file_name' => $new_filename,
            'department' => $department,
            'originator' => $originator,
            'status' => 'pending',
            'master_file_location' => $temp_path,
            'pdf_file_location' => $pdf_destination
        ));

        if (is_wp_error($document_id)) {
            unlink($temp_path);
            return $document_id;
        }

        return array(
            'document_id' => $document_id,
            'doc_number' => $doc_number,
            'temp_path' => $temp_path
        );
    }

    private function validate_file($file) {
        $allowed_types = array('application/vnd.openxmlformats-officedocument.presentationml.presentation');
        return in_array($file['type'], $allowed_types);
    }

    private function generate_doc_number() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'doc_control_documents';
        
        // Get the last document number
        $last_number = $wpdb->get_var("SELECT doc_number FROM $table_name ORDER BY id DESC LIMIT 1");
        
        if (!$last_number) {
            return 'DOC001';
        }
        
        // Extract the number and increment
        $number = intval(substr($last_number, 3)) + 1;
        return 'DOC' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    private function generate_filename($original_name, $doc_number) {
        // Remove any existing doc number from the filename
        $name = preg_replace('/-DOC\d{3}$/', '', $original_name);
        
        // Remove WIP if present
        $name = str_replace('-WIP', '', $name);
        
        // Add the new doc number
        return $name . '-' . $doc_number;
    }

    private function create_document_record($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'doc_control_documents';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'doc_number' => $data['doc_number'],
                'file_name' => $data['file_name'],
                'department' => $data['department'],
                'originator' => $data['originator'],
                'created_date' => current_time('mysql'),
                'status' => $data['status'],
                'master_file_location' => $data['master_file_location'],
                'pdf_file_location' => $data['pdf_file_location']
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create document record.', 'doc-control-system'));
        }
        
        return $wpdb->insert_id;
    }

    public function complete_document($document_id, $created_by) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'doc_control_documents';
        
        // Get document data
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $document_id
        ));
        
        if (!$document) {
            return new WP_Error('not_found', __('Document not found.', 'doc-control-system'));
        }
        
        // Update document record
        $result = $wpdb->update(
            $table_name,
            array(
                'created_by' => $created_by,
                'status' => 'completed',
                'created_date' => current_time('mysql')
            ),
            array('id' => $document_id),
            array('%s', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to update document record.', 'doc-control-system'));
        }
        
        // Clean up temp file
        if (file_exists($document->master_file_location)) {
            unlink($document->master_file_location);
        }
        
        return true;
    }

    public function process_revision($document_id, $file, $revised_by) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'doc_control_documents';
        
        // Get document data
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $document_id
        ));
        
        if (!$document) {
            return new WP_Error('not_found', __('Document not found.', 'doc-control-system'));
        }
        
        // Validate file
        if (!$this->validate_file($file)) {
            return new WP_Error('invalid_file', __('Invalid file type. Only PPTX files are allowed.', 'doc-control-system'));
        }
        
        // Move file to temp directory
        $temp_path = $this->temp_dir . '/' . $document->file_name . '.pptx';
        if (!move_uploaded_file($file['tmp_name'], $temp_path)) {
            return new WP_Error('upload_error', __('Failed to move uploaded file.', 'doc-control-system'));
        }
        
        // Update document record
        $result = $wpdb->update(
            $table_name,
            array(
                'revised_by' => $revised_by,
                'revision_number' => $document->revision_number + 1,
                'status' => 'pending',
                'master_file_location' => $temp_path
            ),
            array('id' => $document_id),
            array('%s', '%d', '%s', '%s'),
            array('%d')
        );
        
        if ($result === false) {
            unlink($temp_path);
            return new WP_Error('db_error', __('Failed to update document record.', 'doc-control-system'));
        }
        
        return true;
    }
} 