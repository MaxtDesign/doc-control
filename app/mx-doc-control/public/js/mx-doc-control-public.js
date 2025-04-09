jQuery(document).ready(function($) {
    // Form validation
    function validateForm() {
        var isValid = true;
        var form = $('#mx-doc-control-submission-form');
        
        // Reset previous errors
        form.find('.has-error').removeClass('has-error');
        form.find('.error-message').remove();
        
        // Validate department
        if (!$('#department').val()) {
            showError('#department', 'Please select a department');
            isValid = false;
        }
        
        // Validate file name
        if (!$('#file_name').val()) {
            showError('#file_name', 'Please enter a document name');
            isValid = false;
        }
        
        // Validate file upload
        if (!$('#document').val()) {
            showError('#document', 'Please select a file to upload');
            isValid = false;
        } else {
            // Validate file type
            var file = $('#document')[0].files[0];
            var allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                              'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
            
            if (!allowedTypes.includes(file.type)) {
                showError('#document', 'Please upload a valid file type (PDF, DOCX, XLSX, or PPTX)');
                isValid = false;
            }
        }
        
        // Validate destination path
        if (!$('#destination').val()) {
            showError('#destination', 'Please enter a destination path');
            isValid = false;
        }
        
        // Validate parent document ID for revisions
        if ($('#is_revision').is(':checked') && !$('#parent_doc_id').val()) {
            showError('#parent_doc_id', 'Please enter the parent document ID');
            isValid = false;
        }
        
        return isValid;
    }
    
    function showError(fieldId, message) {
        var field = $(fieldId);
        field.addClass('has-error');
        $('<div class="error-message">' + message + '</div>').insertAfter(field);
    }
    
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
        
        if (!validateForm()) {
            return;
        }
        
        var formData = new FormData(this);
        formData.append('action', 'mx_doc_control_submit_document');
        formData.append('nonce', mx_doc_control.nonce);
        
        $.ajax({
            url: mx_doc_control.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).text('Submitting...');
                $('#mx-doc-control-submission-form').addClass('mx-doc-control-loading');
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    var successMessage = $('<div class="mx-doc-control-success">' + 
                        'Document submitted successfully! Your request will be processed by the admin team.' +
                        '</div>');
                    
                    $('#mx-doc-control-submission-form').before(successMessage);
                    successMessage.fadeIn();
                    
                    // Reset form
                    $('#mx-doc-control-submission-form')[0].reset();
                    $('.revision-field').hide();
                    
                    // Hide success message after 5 seconds
                    setTimeout(function() {
                        successMessage.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 5000);
                } else {
                    alert('Error submitting document: ' + response.data);
                }
            },
            error: function() {
                alert('Error submitting document. Please try again.');
            },
            complete: function() {
                $('button[type="submit"]').prop('disabled', false).text('Submit Document');
                $('#mx-doc-control-submission-form').removeClass('mx-doc-control-loading');
            }
        });
    });
    
    // File input change handler
    $('#document').on('change', function() {
        var file = this.files[0];
        if (file) {
            // Update file name field if empty
            if (!$('#file_name').val()) {
                var fileName = file.name.replace(/\.[^/.]+$/, ""); // Remove extension
                $('#file_name').val(fileName);
            }
        }
    });
}); 