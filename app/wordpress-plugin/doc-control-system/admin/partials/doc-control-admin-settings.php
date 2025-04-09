<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$settings = $this->get_settings();

// Display settings errors
settings_errors('doc_control_messages');
?>

<div class="wrap">
    <h1>Document Control System Settings</h1>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="doc-control-settings-form">
        <input type="hidden" name="action" value="save_doc_control_settings">
        <?php wp_nonce_field('doc_control_settings_nonce', 'doc_control_settings_nonce'); ?>

        <!-- File Storage Settings -->
        <div class="doc-control-settings-section">
            <h2>File Storage Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="master_doc_path">Master Document Path</label></th>
                    <td>
                        <input type="text" id="master_doc_path" name="master_doc_path" class="regular-text doc-control-path-input" 
                            value="<?php echo esc_attr($settings['master_doc_path'] ?? ''); ?>">
                        <p class="description">Absolute path to the master document storage directory</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pdf_doc_path">PDF Document Path</label></th>
                    <td>
                        <input type="text" id="pdf_doc_path" name="pdf_doc_path" class="regular-text doc-control-path-input" 
                            value="<?php echo esc_attr($settings['pdf_doc_path'] ?? ''); ?>">
                        <p class="description">Absolute path to the PDF document storage directory</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="backup_path">Backup Path</label></th>
                    <td>
                        <input type="text" id="backup_path" name="backup_path" class="regular-text doc-control-path-input" 
                            value="<?php echo esc_attr($settings['backup_path'] ?? ''); ?>">
                        <p class="description">Absolute path to the backup storage directory</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Verification Settings -->
        <div class="doc-control-settings-section">
            <h2>Verification Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="verification_frequency">Verification Frequency</label></th>
                    <td>
                        <select id="verification_frequency" name="verification_frequency">
                            <option value="hourly" <?php selected(($settings['verification_frequency'] ?? '') === 'hourly'); ?>>Hourly</option>
                            <option value="daily" <?php selected(($settings['verification_frequency'] ?? '') === 'daily'); ?>>Daily</option>
                            <option value="weekly" <?php selected(($settings['verification_frequency'] ?? '') === 'weekly'); ?>>Weekly</option>
                        </select>
                        <p class="description">How often should the system verify document locations?</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="excluded_paths">Excluded Paths</label></th>
                    <td>
                        <textarea id="excluded_paths" name="excluded_paths" class="large-text" rows="5"><?php echo esc_textarea($settings['excluded_paths'] ?? ''); ?></textarea>
                        <p class="description">Enter one path per line. These paths will be excluded from verification.</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Backup Settings -->
        <div class="doc-control-settings-section">
            <h2>Backup Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="backup_frequency">Backup Frequency</label></th>
                    <td>
                        <select id="backup_frequency" name="backup_frequency">
                            <option value="daily" <?php selected(($settings['backup_frequency'] ?? '') === 'daily'); ?>>Daily</option>
                            <option value="weekly" <?php selected(($settings['backup_frequency'] ?? '') === 'weekly'); ?>>Weekly</option>
                            <option value="monthly" <?php selected(($settings['backup_frequency'] ?? '') === 'monthly'); ?>>Monthly</option>
                        </select>
                        <p class="description">How often should the system create backups?</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="backup_retention">Backup Retention (days)</label></th>
                    <td>
                        <input type="number" id="backup_retention" name="backup_retention" class="small-text" 
                            value="<?php echo esc_attr($settings['backup_retention'] ?? '30'); ?>">
                        <p class="description">How many days should backups be retained?</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Notification Settings -->
        <div class="doc-control-settings-section">
            <h2>Notification Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Email Notifications</th>
                    <td>
                        <label>
                            <input type="checkbox" name="notify_missing_files" value="1" 
                                <?php checked(($settings['notify_missing_files'] ?? '') === '1'); ?>>
                            Notify when files are missing
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="notify_permission_issues" value="1" 
                                <?php checked(($settings['notify_permission_issues'] ?? '') === '1'); ?>>
                            Notify when permission issues are detected
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="notification_email">Notification Email</label></th>
                    <td>
                        <input type="email" id="notification_email" name="notification_email" class="regular-text" 
                            value="<?php echo esc_attr($settings['notification_email'] ?? ''); ?>">
                        <p class="description">Email address to receive notifications</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Submit Button -->
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings">
        </p>
    </form>

    <!-- System Information -->
    <div class="doc-control-settings-section">
        <h2>System Information</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Plugin Version</th>
                <td><?php echo esc_html(DOC_CONTROL_VERSION); ?></td>
            </tr>
            <tr>
                <th scope="row">PHP Version</th>
                <td><?php echo esc_html(PHP_VERSION); ?></td>
            </tr>
            <tr>
                <th scope="row">WordPress Version</th>
                <td><?php echo esc_html(get_bloginfo('version')); ?></td>
            </tr>
            <tr>
                <th scope="row">Server Software</th>
                <td><?php echo esc_html($_SERVER['SERVER_SOFTWARE']); ?></td>
            </tr>
        </table>
    </div>
</div> 