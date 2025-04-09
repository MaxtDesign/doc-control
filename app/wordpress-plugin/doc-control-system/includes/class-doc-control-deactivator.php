<?php

class Doc_Control_Deactivator {
    public static function deactivate() {
        // Remove capabilities from administrator role
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
            $role->remove_cap($cap);
        }

        // Clear any scheduled events
        wp_clear_scheduled_hook('doc_control_daily_verification');
        wp_clear_scheduled_hook('doc_control_backup');

        // Note: We don't delete the database tables or uploaded files
        // This allows for reactivation without data loss
    }
} 