<?php

class MX_Doc_Control_Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Check current plugin version
        $current_version = get_option('mx_doc_control_version', '0');
        
        // If this is a new installation or upgrade, drop and recreate tables
        if (version_compare($current_version, MX_DOC_CONTROL_VERSION, '<')) {
            // Drop existing tables if they exist
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mx_document_requests");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mx_documents");
            
            // Documents table
            $table_name = $wpdb->prefix . 'mx_documents';
            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                doc_id varchar(50) NOT NULL,
                description varchar(255) NOT NULL,
                created_by varchar(100) NOT NULL,
                created_date datetime NOT NULL,
                revision varchar(5) DEFAULT NULL,
                revised_by varchar(100) DEFAULT NULL,
                revised_date datetime DEFAULT NULL,
                master_file_path text NOT NULL,
                document_path text NOT NULL,
                department varchar(100) NOT NULL,
                status varchar(20) NOT NULL DEFAULT 'pending',
                PRIMARY KEY  (id),
                UNIQUE KEY doc_id (doc_id)
            ) $charset_collate;";
            
            // Document requests table
            $requests_table = $wpdb->prefix . 'mx_document_requests';
            $sql2 = "CREATE TABLE IF NOT EXISTS $requests_table (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                department varchar(100) NOT NULL,
                file_name varchar(255) NOT NULL,
                original_file_path text NOT NULL,
                requested_destination text NOT NULL,
                is_revision tinyint(1) NOT NULL DEFAULT 0,
                parent_doc_id varchar(50) DEFAULT NULL,
                status varchar(20) NOT NULL DEFAULT 'pending',
                created_at datetime NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            dbDelta($sql2);
            
            // Update version in options
            update_option('mx_doc_control_version', MX_DOC_CONTROL_VERSION);
        }
        
        // Create upload directories
        $upload_dir = wp_upload_dir();
        $doc_control_dir = $upload_dir['basedir'] . '/mx-doc-control';
        
        if (!file_exists($doc_control_dir)) {
            wp_mkdir_p($doc_control_dir);
        }
        
        // Create .htaccess to protect direct access
        $htaccess_file = $doc_control_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "deny from all\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
    }
} 