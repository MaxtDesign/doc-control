<?php
/**
 * Fired during plugin deactivation
 *
 * @link       https://yourcompany.com/mx-doc-control
 * @since      1.0.0
 *
 * @package    MX_Doc_Control
 * @subpackage MX_Doc_Control/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    MX_Doc_Control
 * @subpackage MX_Doc_Control/includes
 * @author     Your Company <your@email.com>
 */
class MX_Doc_Control_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Clear any scheduled hooks
        wp_clear_scheduled_hook('mx_doc_control_daily_cleanup');
        
        // Clear transients
        delete_transient('mx_doc_control_cache');
        
        // Note: We intentionally don't delete:
        // - Custom tables (to preserve user data)
        // - Options (to maintain settings if reactivated)
        // - Uploaded files (to prevent data loss)
    }
} 