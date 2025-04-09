<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mx-doc-control-form-container">
    <form id="mx-doc-control-submission-form" class="mx-doc-control-form">
        <div class="form-field">
            <label for="requestee_name">Created By:</label>
            <input type="text" id="requestee_name" name="requestee_name" required>
            <p class="description">Enter the name of the person creating this document</p>
        </div>

        <div class="form-field">
            <label for="department">Department:</label>
            <select id="department" name="department" required>
                <option value="">Select Department</option>
                <option value="sales">Sales</option>
                <option value="marketing">Marketing</option>
                <option value="engineering">Engineering</option>
                <option value="operations">Operations</option>
                <option value="hr">Human Resources</option>
                <option value="finance">Finance</option>
            </select>
        </div>

        <div class="form-field">
            <label for="document">Upload Document:</label>
            <input type="file" id="document" name="document" required>
            <p class="description">Supported formats: PDF, DOCX, XLSX, PPTX</p>
        </div>

        <div class="form-field">
            <label for="destination">Destination Folder Path:</label>
            <input type="text" id="destination" name="destination" required>
            <p class="description">Enter the full network path where the document should be stored</p>
        </div>

        <div class="form-field">
            <label>
                <input type="checkbox" id="is_revision" name="is_revision">
                This is a revision of an existing document
            </label>
        </div>

        <div class="form-field revision-field" style="display: none;">
            <label for="parent_doc_id">Parent Document ID:</label>
            <input type="text" id="parent_doc_id" name="parent_doc_id" placeholder="DOCXXXX">
        </div>

        <div class="form-actions">
            <button type="submit" class="button button-primary">Submit Document</button>
        </div>
    </form>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Toggle revision fields
    $('#is_revision').on('change', function() {
        $('.revision-field').toggle(this.checked);
        if (this.checked) {
            $('#parent_doc_id').prop('required', true);
        } else {
            $('#parent_doc_id').prop('required', false);
        }
    });

    // Form submission
    $('#mx-doc-control-submission-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'mx_doc_control_submit_document');
        formData.append('nonce', mx_doc_control.nonce);
        
        // Debug logging
        console.log('Form Data Contents:');
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        $.ajax({
            url: mx_doc_control.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                console.log('Sending request to:', mx_doc_control.ajax_url);
                $('button[type="submit"]').prop('disabled', true).text('Submitting...');
            },
            success: function(response) {
                console.log('Success response:', response);
                if (response.success) {
                    alert('Document submitted successfully!');
                    $('#mx-doc-control-submission-form')[0].reset();
                    $('.revision-field').hide();
                } else {
                    alert('Error submitting document: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                alert('Error submitting document. Please try again.');
            },
            complete: function() {
                $('button[type="submit"]').prop('disabled', false).text('Submit Document');
            }
        });
    });
});
</script>

<style>
.mx-doc-control-form-container {
    max-width: 600px;
    margin: 2em auto;
    padding: 20px;
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.mx-doc-control-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-field label {
    font-weight: 600;
}

.form-field input[type="text"],
.form-field input[type="file"],
.form-field select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-field .description {
    font-size: 0.9em;
    color: #666;
    margin-top: 5px;
}

.form-actions {
    margin-top: 20px;
    text-align: right;
}

.form-actions button {
    padding: 10px 20px;
}

/* Error states */
.form-field.has-error input {
    border-color: #dc3232;
}

.form-field .error-message {
    color: #dc3232;
    font-size: 0.9em;
    margin-top: 5px;
}

/* Success message */
.mx-doc-control-success {
    background: #46b450;
    color: #fff;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
    display: none;
}
</style> 