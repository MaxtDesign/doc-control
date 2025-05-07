<?php

class Document_Metabox {
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_document_metabox'));
        add_action('save_post_document', array($this, 'save_document_metabox'));
    }

    public function add_document_metabox() {
        add_meta_box(
            'document_details',
            __('Document Details', 'doc-control-system'),
            array($this, 'render_document_metabox'),
            'document',
            'normal',
            'high'
        );
    }

    public function render_document_metabox($post) {
        // Add nonce for security
        wp_nonce_field('document_metabox_nonce', 'document_metabox_nonce');

        // Get existing values
        $doc_number = get_post_meta($post->ID, '_doc_number', true);
        $file_name = get_post_meta($post->ID, '_file_name', true);
        $originator = get_post_meta($post->ID, '_originator', true);
        $created_by = get_post_meta($post->ID, '_created_by', true);
        $revised_by = get_post_meta($post->ID, '_revised_by', true);
        $revision_number = get_post_meta($post->ID, '_revision_number', true);
        $master_file_location = get_post_meta($post->ID, '_master_file_location', true);
        $pdf_file_location = get_post_meta($post->ID, '_pdf_file_location', true);

        // Output fields
        ?>
        <div class="document-metabox">
            <p>
                <label for="doc_number"><?php _e('Document Number:', 'doc-control-system'); ?></label>
                <input type="text" id="doc_number" name="doc_number" value="<?php echo esc_attr($doc_number); ?>" readonly>
            </p>
            <p>
                <label for="file_name"><?php _e('File Name:', 'doc-control-system'); ?></label>
                <input type="text" id="file_name" name="file_name" value="<?php echo esc_attr($file_name); ?>" readonly>
            </p>
            <p>
                <label for="originator"><?php _e('Originator:', 'doc-control-system'); ?></label>
                <input type="text" id="originator" name="originator" value="<?php echo esc_attr($originator); ?>" readonly>
            </p>
            <p>
                <label for="created_by"><?php _e('Created By:', 'doc-control-system'); ?></label>
                <input type="text" id="created_by" name="created_by" value="<?php echo esc_attr($created_by); ?>">
            </p>
            <p>
                <label for="revised_by"><?php _e('Revised By:', 'doc-control-system'); ?></label>
                <input type="text" id="revised_by" name="revised_by" value="<?php echo esc_attr($revised_by); ?>">
            </p>
            <p>
                <label for="revision_number"><?php _e('Revision Number:', 'doc-control-system'); ?></label>
                <input type="number" id="revision_number" name="revision_number" value="<?php echo esc_attr($revision_number); ?>" min="0">
            </p>
            <p>
                <label for="master_file_location"><?php _e('Master File Location:', 'doc-control-system'); ?></label>
                <input type="text" id="master_file_location" name="master_file_location" value="<?php echo esc_attr($master_file_location); ?>" class="widefat">
            </p>
            <p>
                <label for="pdf_file_location"><?php _e('PDF File Location:', 'doc-control-system'); ?></label>
                <input type="text" id="pdf_file_location" name="pdf_file_location" value="<?php echo esc_attr($pdf_file_location); ?>" class="widefat">
            </p>
        </div>
        <?php
    }

    public function save_document_metabox($post_id) {
        // Check if nonce is set
        if (!isset($_POST['document_metabox_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['document_metabox_nonce'], 'document_metabox_nonce')) {
            return;
        }

        // Check if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save fields
        $fields = array(
            'doc_number',
            'file_name',
            'originator',
            'created_by',
            'revised_by',
            'revision_number',
            'master_file_location',
            'pdf_file_location'
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
} 