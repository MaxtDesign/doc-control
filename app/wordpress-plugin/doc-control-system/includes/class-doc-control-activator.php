<?php

class Doc_Control_Activator {
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create documents table first
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}doc_control_documents (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            doc_number varchar(20) NOT NULL,
            description varchar(255) NOT NULL,
            created_by varchar(100) NOT NULL,
            created_date datetime NOT NULL,
            revised_by varchar(100),
            revision_number varchar(10),
            revision_date datetime,
            master_doc_location text NOT NULL,
            file_location text NOT NULL,
            notes text,
            status varchar(20) DEFAULT 'active',
            last_verified datetime,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY doc_number (doc_number)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Create document history table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}doc_control_history (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            doc_id bigint(20) NOT NULL,
            action varchar(50) NOT NULL,
            action_by varchar(100) NOT NULL,
            action_date datetime NOT NULL,
            details text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY doc_id (doc_id),
            FOREIGN KEY (doc_id) REFERENCES {$wpdb->prefix}doc_control_documents(id) ON DELETE CASCADE
        ) $charset_collate;";

        dbDelta($sql);

        // Create verification logs table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}doc_control_verification_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            doc_id bigint(20) NOT NULL,
            verification_date datetime NOT NULL,
            status varchar(20) NOT NULL,
            details text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY doc_id (doc_id),
            FOREIGN KEY (doc_id) REFERENCES {$wpdb->prefix}doc_control_documents(id) ON DELETE CASCADE
        ) $charset_collate;";

        dbDelta($sql);

        // Create backup directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $backup_dir = $upload_dir['basedir'] . '/doc-control-backups';
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }

        // Add capabilities to administrator role
        $role = get_role('administrator');
        $capabilities = array(
            'manage_doc_control',
            'upload_documents',
            'edit_documents',
            'delete_documents',
            'view_document_history',
            'manage_document_verification'
        );

        foreach ($capabilities as $cap) {
            $role->add_cap($cap);
        }

        // Set version in options
        add_option('doc_control_version', DOC_CONTROL_VERSION);
    }
} 