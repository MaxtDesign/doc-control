<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get current action
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';

// Handle messages
$message = isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
$message_class = 'updated';
$message_text = '';

switch ($message) {
    case 'processing':
        $message_text = __('Document is being processed.', 'doc-control-system');
        break;
    case 'completed':
        $message_text = __('Document has been completed.', 'doc-control-system');
        break;
    case 'revision_submitted':
        $message_text = __('Document revision has been submitted.', 'doc-control-system');
        break;
    case 'error':
        $message_class = 'error';
        $message_text = __('An error occurred.', 'doc-control-system');
        break;
}

// Display message if any
if ($message_text) {
    printf('<div class="%s"><p>%s</p></div>', esc_attr($message_class), esc_html($message_text));
}

// Handle different actions
switch ($action) {
    case 'edit':
        $document_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($document_id) {
            $this->render_edit_form($document_id);
        }
        break;
        
    case 'process':
        $document_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($document_id) {
            $this->render_process_form($document_id);
        }
        break;
        
    case 'complete':
        $document_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($document_id) {
            $this->render_complete_form($document_id);
        }
        break;
        
    case 'revision':
        $document_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($document_id) {
            $this->render_revision_form($document_id);
        }
        break;
        
    default:
        $this->render_document_list();
        break;
}

// Helper function to render document list
function render_document_list() {
    $list_table = new Document_List_Table();
    $list_table->prepare_items();
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php _e('Document Control', 'doc-control-system'); ?></h1>
        <hr class="wp-header-end">
        
        <form method="post">
            <?php
            $list_table->search_box(__('Search Documents', 'doc-control-system'), 'search_id');
            $list_table->display();
            ?>
        </form>
    </div>
    <?php
}

// Helper function to render edit form
function render_edit_form($document_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'doc_control_documents';
    $document = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $document_id
    ));
    
    if (!$document) {
        wp_die(__('Document not found.', 'doc-control-system'));
    }
    ?>
    <div class="wrap">
        <h1><?php _e('Edit Document', 'doc-control-system'); ?></h1>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('edit_document_' . $document_id); ?>
            <input type="hidden" name="action" value="edit_document">
            <input type="hidden" name="document_id" value="<?php echo esc_attr($document_id); ?>">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Document Number', 'doc-control-system'); ?></th>
                    <td><?php echo esc_html($document->doc_number); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('File Name', 'doc-control-system'); ?></th>
                    <td><?php echo esc_html($document->file_name); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Department', 'doc-control-system'); ?></th>
                    <td><?php echo esc_html($document->department); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Originator', 'doc-control-system'); ?></th>
                    <td><?php echo esc_html($document->originator); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Created By', 'doc-control-system'); ?></th>
                    <td>
                        <input type="text" name="created_by" value="<?php echo esc_attr($document->created_by); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Revised By', 'doc-control-system'); ?></th>
                    <td>
                        <input type="text" name="revised_by" value="<?php echo esc_attr($document->revised_by); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Revision Number', 'doc-control-system'); ?></th>
                    <td>
                        <input type="number" name="revision_number" value="<?php echo esc_attr($document->revision_number); ?>" class="small-text" min="0">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Master File Location', 'doc-control-system'); ?></th>
                    <td>
                        <input type="text" name="master_file_location" value="<?php echo esc_attr($document->master_file_location); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('PDF File Location', 'doc-control-system'); ?></th>
                    <td>
                        <input type="text" name="pdf_file_location" value="<?php echo esc_attr($document->pdf_file_location); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Update Document', 'doc-control-system')); ?>
        </form>
    </div>
    <?php
}

// Helper function to render process form
function render_process_form($document_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'doc_control_documents';
    $document = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $document_id
    ));
    
    if (!$document) {
        wp_die(__('Document not found.', 'doc-control-system'));
    }
    ?>
    <div class="wrap">
        <h1><?php _e('Process Document', 'doc-control-system'); ?></h1>
        
        <div class="document-info">
            <p><strong><?php _e('Document Number:', 'doc-control-system'); ?></strong> <?php echo esc_html($document->doc_number); ?></p>
            <p><strong><?php _e('File Name:', 'doc-control-system'); ?></strong> <?php echo esc_html($document->file_name); ?></p>
            <p><strong><?php _e('Department:', 'doc-control-system'); ?></strong> <?php echo esc_html($document->department); ?></p>
            <p><strong><?php _e('Originator:', 'doc-control-system'); ?></strong> <?php echo esc_html($document->originator); ?></p>
        </div>
        
        <div class="document-actions">
            <a href="<?php echo esc_url($document->master_file_location); ?>" class="button button-primary" download>
                <?php _e('Download Document', 'doc-control-system'); ?>
            </a>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display: inline-block;">
                <?php wp_nonce_field('process_document_' . $document_id); ?>
                <input type="hidden" name="action" value="process_document">
                <input type="hidden" name="document_id" value="<?php echo esc_attr($document_id); ?>">
                <?php submit_button(__('Mark as Processing', 'doc-control-system')); ?>
            </form>
        </div>
    </div>
    <?php
}

// Helper function to render complete form
function render_complete_form($document_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'doc_control_documents';
    $document = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $document_id
    ));
    
    if (!$document) {
        wp_die(__('Document not found.', 'doc-control-system')); 
    }
    ?>
    <div class="wrap">
        <h1><?php _e('Complete Document', 'doc-control-system'); ?></h1>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('complete_document_' . $document_id); ?>
            <input type="hidden" name="action" value="complete_document">
            <input type="hidden" name="document_id" value="<?php echo esc_attr($document_id); ?>">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Document Number', 'doc-control-system'); ?></th>
                    <td><?php echo esc_html($document->doc_number); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('File Name', 'doc-control-system'); ?></th>
                    <td><?php echo esc_html($document->file_name); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Created By', 'doc-control-system'); ?></th>
                    <td>
                        <input type="text" name="created_by" required class="regular-text">
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Mark as Completed', 'doc-control-system')); ?>
        </form>
    </div>
    <?php
}

// Helper function to render revision form
function render_revision_form($document_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'doc_control_documents';
    $document = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $document_id
    ));
    
    if (!$document) {
        wp_die(__('Document not found.', 'doc-control-system'));
    }
    ?>
    <div class="wrap">
        <h1><?php _e('Submit Revision', 'doc-control-system'); ?></h1>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('submit_revision_' . $document_id); ?>
            <input type="hidden" name="action" value="submit_revision">
            <input type="hidden" name="document_id" value="<?php echo esc_attr($document_id); ?>">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Document Number', 'doc-control-system'); ?></th>
                    <td><?php echo esc_html($document->doc_number); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('File Name', 'doc-control-system'); ?></th>
                    <td><?php echo esc_html($document->file_name); ?></td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Revised By', 'doc-control-system'); ?></th>
                    <td>
                        <input type="text" name="revised_by" required class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Revision File', 'doc-control-system'); ?></th>
                    <td>
                        <input type="file" name="document" accept=".pptx" required>
                        <p class="description"><?php _e('Upload the revised PPTX file.', 'doc-control-system'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(__('Submit Revision', 'doc-control-system')); ?>
        </form>
    </div>
    <?php
}
?>

<style>
.document-info {
    background: #fff;
    padding: 1em;
    margin: 1em 0;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.document-info p {
    margin: 0.5em 0;
}

.document-actions {
    margin: 1em 0;
}

.document-actions .button {
    margin-right: 1em;
}

.file-path {
    cursor: pointer;
}

.file-path:hover {
    color: #0073aa;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle file path copy on click
    $('.file-path').on('click', function() {
        var path = $(this).data('path');
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(path).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Show copied message
        var $message = $('<div class="notice notice-success is-dismissible"><p>Path copied to clipboard</p></div>');
        $('.wrap h1').after($message);
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 2000);
    });
    
    // Show file path on hover
    $('.file-path').tooltip({
        content: function() {
            return $(this).data('path');
        },
        position: {
            my: 'left center',
            at: 'right center'
        }
    });
});
</script> 