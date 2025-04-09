<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="doc-control-upload-form">
    <h2>Submit Document for Review</h2>

    <form id="doc-control-upload-form" method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('doc_control_public_upload', 'doc_control_public_nonce'); ?>
        <input type="hidden" name="action" value="doc_control_public_upload">

        <div class="form-group">
            <label for="document_title">Document Title</label>
            <input type="text" id="document_title" name="document_title" class="regular-text" required>
        </div>

        <div class="form-group">
            <label for="document_type">Document Type</label>
            <select id="document_type" name="document_type" required>
                <option value="">Select Document Type</option>
                <option value="procedure">Procedure</option>
                <option value="policy">Policy</option>
                <option value="form">Form</option>
                <option value="work_instruction">Work Instruction</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="master_document">Master Document</label>
            <input type="file" id="master_document" name="master_document" accept=".doc,.docx,.xls,.xlsx,.pdf" required>
            <p class="description">Upload the original document file</p>
        </div>

        <div class="form-group">
            <label for="pdf_document">PDF Version</label>
            <input type="file" id="pdf_document" name="pdf_document" accept=".pdf" required>
            <p class="description">Upload the PDF version of the document</p>
        </div>

        <div class="form-group">
            <label for="target_location">Target Location</label>
            <input type="text" id="target_location" name="target_location" class="regular-text" required>
            <p class="description">Specify where the document should be stored (e.g., /procedures/quality/)</p>
        </div>

        <div class="form-group">
            <label for="revision_notes">Revision Notes</label>
            <textarea id="revision_notes" name="revision_notes" class="large-text" rows="5"></textarea>
            <p class="description">Describe any changes made to the document</p>
        </div>

        <div class="form-group">
            <label for="department">Department</label>
            <select id="department" name="department" required>
                <option value="">Select Department</option>
                <option value="quality">Quality</option>
                <option value="production">Production</option>
                <option value="engineering">Engineering</option>
                <option value="hr">Human Resources</option>
                <option value="other">Other</option>
            </select>
        </div>

        <div class="form-group">
            <label for="contact_email">Contact Email</label>
            <input type="email" id="contact_email" name="contact_email" class="regular-text" required>
            <p class="description">Email address for notification when the document is processed</p>
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                <option value="normal">Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>

        <div class="form-group">
            <label for="additional_notes">Additional Notes</label>
            <textarea id="additional_notes" name="additional_notes" class="large-text" rows="3"></textarea>
            <p class="description">Any additional information that might be helpful</p>
        </div>

        <div class="form-group">
            <button type="submit" class="button button-primary">Submit Document</button>
        </div>

        <div id="upload-status" class="upload-status" style="display: none;">
            <div class="spinner"></div>
            <p class="status-message"></p>
        </div>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#doc-control-upload-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'doc_control_public_upload');
        formData.append('doc_control_public_nonce', '<?php echo wp_create_nonce('doc_control_public_upload'); ?>');

        $('#upload-status').show();
        $('.status-message').text('Uploading document...');

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('.status-message').text('Document submitted successfully!');
                    $('#doc-control-upload-form')[0].reset();
                } else {
                    $('.status-message').text('Error: ' + response.data);
                }
            },
            error: function() {
                $('.status-message').text('Error submitting document. Please try again.');
            },
            complete: function() {
                setTimeout(function() {
                    $('#upload-status').hide();
                }, 3000);
            }
        });
    });
});
</script> 