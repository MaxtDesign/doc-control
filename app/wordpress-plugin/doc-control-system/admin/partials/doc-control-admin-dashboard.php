<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="doc-control-dashboard">
        <!-- Quick Stats -->
        <div class="doc-control-stats">
            <div class="stat-box">
                <h3>Total Documents</h3>
                <p class="stat-number"><?php echo esc_html($this->get_total_documents()); ?></p>
            </div>
            <div class="stat-box">
                <h3>Pending Review</h3>
                <p class="stat-number"><?php echo esc_html($this->get_pending_documents()); ?></p>
            </div>
            <div class="stat-box">
                <h3>Missing Files</h3>
                <p class="stat-number"><?php echo esc_html($this->get_missing_files()); ?></p>
            </div>
            <div class="stat-box">
                <h3>Last Verification</h3>
                <p class="stat-number"><?php echo esc_html($this->get_last_verification()); ?></p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="doc-control-recent-activity">
            <h2>Recent Activity</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Action</th>
                        <th>Document</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recent_activity = $this->get_recent_activity();
                    foreach ($recent_activity as $activity) {
                        ?>
                        <tr>
                            <td><?php echo esc_html($activity->action_date); ?></td>
                            <td><?php echo esc_html($activity->action); ?></td>
                            <td><?php echo esc_html($activity->doc_number); ?></td>
                            <td><?php echo esc_html($activity->action_by); ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions -->
        <div class="doc-control-quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->plugin_name . '-documents&action=new')); ?>" class="button button-primary">
                    Add New Document
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->plugin_name . '-documents&action=import')); ?>" class="button">
                    Import Documents
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->plugin_name . '-logs')); ?>" class="button">
                    View Verification Logs
                </a>
            </div>
        </div>

        <!-- System Status -->
        <div class="doc-control-system-status">
            <h2>System Status</h2>
            <table class="wp-list-table widefat fixed striped">
                <tbody>
                    <tr>
                        <th>Backup Status</th>
                        <td><?php echo esc_html($this->get_backup_status()); ?></td>
                    </tr>
                    <tr>
                        <th>Storage Space</th>
                        <td><?php echo esc_html($this->get_storage_status()); ?></td>
                    </tr>
                    <tr>
                        <th>Last Backup</th>
                        <td><?php echo esc_html($this->get_last_backup()); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div> 