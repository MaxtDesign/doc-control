<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="doc-control-form-wrapper">
    <form id="doc-control-form" class="doc-control-form" enctype="multipart/form-data">
        <?php wp_nonce_field('submit_document', 'doc_control_nonce'); ?>
        
        <div class="form-group">
            <label for="department"><?php _e('Department:', 'doc-control-system'); ?></label>
            <select name="department" id="department" required>
                <option value=""><?php _e('Select Department', 'doc-control-system'); ?></option>
                <?php
                $departments = get_terms(array(
                    'taxonomy' => 'department',
                    'hide_empty' => false
                ));
                
                foreach ($departments as $department) {
                    printf(
                        '<option value="%s">%s</option>',
                        esc_attr($department->slug),
                        esc_html($department->name)
                    );
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="originator"><?php _e('Your Name:', 'doc-control-system'); ?></label>
            <input type="text" name="originator" id="originator" required>
        </div>

        <div class="form-group">
            <label for="document"><?php _e('Document (PPTX):', 'doc-control-system'); ?></label>
            <input type="file" name="document" id="document" accept=".pptx" required>
            <p class="description"><?php _e('Only PPTX files are allowed.', 'doc-control-system'); ?></p>
        </div>

        <div class="form-group">
            <label for="pdf_destination"><?php _e('PDF Destination Path:', 'doc-control-system'); ?></label>
            <input type="text" name="pdf_destination" id="pdf_destination" required>
            <p class="description"><?php _e('Enter the full network path where the PDF should be saved.', 'doc-control-system'); ?></p>
        </div>

        <div class="form-group">
            <button type="submit" class="button button-primary"><?php _e('Submit Document', 'doc-control-system'); ?></button>
        </div>

        <div id="form-message" class="form-message" style="display: none;"></div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    $('#doc-control-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $message = $('#form-message');
        var $submitButton = $form.find('button[type="submit"]');
        
        // Disable submit button
        $submitButton.prop('disabled', true);
        
        // Clear previous message
        $message.removeClass('success error').hide();
        
        // Create FormData object
        var formData = new FormData(this);
        formData.append('action', 'submit_document');
        
        // Submit form
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $message
                        .addClass('success')
                        .html(response.data.message + '<br>Document Number: ' + response.data.doc_number)
                        .show();
                    
                    // Reset form
                    $form[0].reset();
                } else {
                    $message
                        .addClass('error')
                        .html(response.data)
                        .show();
                }
            },
            error: function() {
                $message
                    .addClass('error')
                    .html('<?php _e('An error occurred. Please try again.', 'doc-control-system'); ?>')
                    .show();
            },
            complete: function() {
                // Re-enable submit button
                $submitButton.prop('disabled', false);
            }
        });
    });
});
</script>

<style>
.doc-control-form-wrapper {
    max-width: 600px;
    margin: 2em auto;
    padding: 2em;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.doc-control-form .form-group {
    margin-bottom: 1.5em;
}

.doc-control-form label {
    display: block;
    margin-bottom: 0.5em;
    font-weight: 600;
}

.doc-control-form input[type="text"],
.doc-control-form select {
    width: 100%;
    padding: 0.5em;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.doc-control-form .description {
    margin-top: 0.5em;
    color: #666;
    font-size: 0.9em;
}

.form-message {
    margin-top: 1em;
    padding: 1em;
    border-radius: 4px;
}

.form-message.success {
    background: #dff0d8;
    color: #3c763d;
    border: 1px solid #d6e9c6;
}

.form-message.error {
    background: #f2dede;
    color: #a94442;
    border: 1px solid #ebccd1;
}
</style> 