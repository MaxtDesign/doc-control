<?php
if (!defined('ABSPATH')) {
    exit;
}

$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$current_user = wp_get_current_user();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Document Management</h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->plugin_name . '-documents&action=new')); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">

    <?php if ($action === 'new' || $action === 'edit'): ?>
        <!-- Document Form -->
        <div class="doc-control-form">
            <form method="post" action="">
                <?php wp_nonce_field('doc_control_document_action', 'doc_control_nonce'); ?>
                <input type="hidden" name="action" value="<?php echo esc_attr($action === 'new' ? 'create' : 'update'); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="doc_number">Document Number</label></th>
                        <td>
                            <input type="text" id="doc_number" name="doc_number" class="regular-text" 
                                value="<?php echo esc_attr($document->doc_number ?? ''); ?>" 
                                <?php echo $action === 'edit' ? 'readonly' : ''; ?>>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="description">Description</label></th>
                        <td>
                            <input type="text" id="description" name="description" class="regular-text" 
                                value="<?php echo esc_attr($document->description ?? ''); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="master_doc_location">Master Document Location</label></th>
                        <td>
                            <input type="text" id="master_doc_location" name="master_doc_location" class="regular-text" 
                                value="<?php echo esc_attr($document->master_doc_location ?? ''); ?>">
                            <p class="description">Enter the full path where the master document should be stored</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="file_location">PDF Version Location</label></th>
                        <td>
                            <input type="text" id="file_location" name="file_location" class="regular-text" 
                                value="<?php echo esc_attr($document->file_location ?? ''); ?>">
                            <p class="description">Review and verify the user's desired PDF version location</p>
                        </td>
                    </tr>
                    <?php if ($action === 'edit'): ?>
                    <tr>
                        <th scope="row">Document Creator</th>
                        <td>
                            <p><?php echo esc_html($document->created_by); ?></p>
                            <p class="description">Original document creator</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Created Date</th>
                        <td>
                            <p><?php echo esc_html($document->created_date); ?></p>
                            <p class="description">Date when the document was first created</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th scope="row"><label for="revision_number">Revision Number</label></th>
                        <td>
                            <input type="text" id="revision_number" name="revision_number" class="small-text" 
                                value="<?php echo esc_attr($document->revision_number ?? ''); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="revised_by">Revised By</label></th>
                        <td>
                            <input type="text" id="revised_by" name="revised_by" class="regular-text" 
                                value="<?php echo esc_attr($document->revised_by ?? $current_user->display_name); ?>" readonly>
                            <p class="description">Current administrator making the revision</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="revision_date">Revision Date</label></th>
                        <td>
                            <input type="datetime-local" id="revision_date" name="revision_date" 
                                value="<?php echo esc_attr($document->revision_date ?? current_time('mysql')); ?>">
                            <p class="description">Date and time of the revision</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notes">Notes</label></th>
                        <td>
                            <textarea id="notes" name="notes" class="large-text" rows="5"><?php echo esc_textarea($document->notes ?? ''); ?></textarea>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr($action === 'new' ? 'Create Document' : 'Update Document'); ?>">
                </p>
            </form>
        </div>
    <?php else: ?>
        <!-- Document List -->
        <div class="doc-control-list">
            <!-- Search and Filter -->
            <div class="tablenav top">
                <div class="alignleft actions">
                    <form method="get">
                        <input type="hidden" name="page" value="<?php echo esc_attr($this->plugin_name . '-documents'); ?>">
                        <input type="text" name="s" value="<?php echo esc_attr(isset($_GET['s']) ? $_GET['s'] : ''); ?>" placeholder="Search documents...">
                        <input type="submit" class="button" value="Search">
                    </form>
                </div>
            </div>

            <!-- Documents Table -->
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Document Number</th>
                        <th>Description</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Revision</th>
                        <th>Revised By</th>
                        <th>Revision Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $documents = $this->get_documents();
                    foreach ($documents as $doc) {
                        ?>
                        <tr>
                            <td><?php echo esc_html($doc->doc_number); ?></td>
                            <td><?php echo esc_html($doc->description); ?></td>
                            <td><?php echo esc_html($doc->created_by); ?></td>
                            <td><?php echo esc_html($doc->created_date); ?></td>
                            <td><?php echo esc_html($doc->revision_number); ?></td>
                            <td><?php echo esc_html($doc->revised_by); ?></td>
                            <td><?php echo esc_html($doc->revision_date); ?></td>
                            <td><?php echo esc_html($doc->status); ?></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->plugin_name . '-documents&action=edit&id=' . $doc->id)); ?>" class="button button-small">Edit</a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=' . $this->plugin_name . '-documents&action=view&id=' . $doc->id)); ?>" class="button button-small">View</a>
                                <button type="button" class="button button-small delete-document" data-id="<?php echo esc_attr($doc->id); ?>">Delete</button>
                            </td>
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
                    $total_items = $this->get_total_documents();
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
        </div>
    <?php endif; ?>
</div> 