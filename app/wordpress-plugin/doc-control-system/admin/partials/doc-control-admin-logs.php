<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>Verification Logs</h1>

    <!-- Filter Options -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get">
                <input type="hidden" name="page" value="<?php echo esc_attr($this->plugin_name . '-logs'); ?>">
                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="found" <?php selected(isset($_GET['status']) && $_GET['status'] === 'found'); ?>>Found</option>
                    <option value="missing" <?php selected(isset($_GET['status']) && $_GET['status'] === 'missing'); ?>>Missing</option>
                    <option value="permission_denied" <?php selected(isset($_GET['status']) && $_GET['status'] === 'permission_denied'); ?>>Permission Denied</option>
                </select>
                <input type="date" name="date" value="<?php echo esc_attr(isset($_GET['date']) ? $_GET['date'] : ''); ?>">
                <input type="submit" class="button" value="Filter">
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Document Number</th>
                <th>Status</th>
                <th>Details</th>
                <th>Action Required</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $logs = $this->get_verification_logs();
            foreach ($logs as $log) {
                $status_class = '';
                $action_required = '';

                switch ($log->status) {
                    case 'found':
                        $status_class = 'status-found';
                        $action_required = 'None';
                        break;
                    case 'missing':
                        $status_class = 'status-missing';
                        $action_required = 'Manual Search Required';
                        break;
                    case 'permission_denied':
                        $status_class = 'status-permission';
                        $action_required = 'Check Permissions';
                        break;
                }
                ?>
                <tr>
                    <td><?php echo esc_html($log->verification_date); ?></td>
                    <td><?php echo esc_html($log->doc_number); ?></td>
                    <td class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($log->status)); ?></td>
                    <td><?php echo esc_html($log->details); ?></td>
                    <td><?php echo esc_html($action_required); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            $total_items = $this->get_total_verification_logs();
            $items_per_page = 20;
            $total_pages = ceil($total_items / $items_per_page);
            $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $current_page
            ));
            ?>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="doc-control-log-summary">
        <h2>Summary</h2>
        <div class="summary-boxes">
            <div class="summary-box">
                <h3>Total Documents</h3>
                <p class="summary-number"><?php echo esc_html($this->get_total_documents()); ?></p>
            </div>
            <div class="summary-box">
                <h3>Missing Files</h3>
                <p class="summary-number"><?php echo esc_html($this->get_missing_files()); ?></p>
            </div>
            <div class="summary-box">
                <h3>Permission Issues</h3>
                <p class="summary-number"><?php echo esc_html($this->get_permission_issues_count()); ?></p>
            </div>
            <div class="summary-box">
                <h3>Last Full Scan</h3>
                <p class="summary-number"><?php echo esc_html($this->get_last_verification()); ?></p>
            </div>
        </div>
    </div>

    <!-- Manual Verification Options -->
    <div class="doc-control-verification-actions">
        <h2>Verification Actions</h2>
        <div class="action-buttons">
            <button type="button" class="button button-primary" id="start-verification">Start Manual Verification</button>
            <button type="button" class="button" id="export-logs">Export Logs</button>
            <button type="button" class="button" id="clear-old-logs">Clear Old Logs</button>
        </div>
    </div>
</div> 