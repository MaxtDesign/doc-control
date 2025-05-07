jQuery(document).ready(function($) {
    // Initialize tooltips
    $('.file-path').tooltip({
        content: function() {
            return $(this).data('path');
        },
        position: {
            my: 'left center',
            at: 'right center'
        }
    });

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

    // Handle bulk actions
    $('#doaction, #doaction2').on('click', function(e) {
        var action = $(this).prev('select').val();
        if (action === 'delete') {
            if (!confirm('Are you sure you want to delete the selected documents?')) {
                e.preventDefault();
            }
        }
    });

    // Handle document processing
    $('.process-document').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var documentId = $button.data('id');
        
        // Disable button
        $button.prop('disabled', true);
        
        // Send AJAX request
        $.post(ajaxurl, {
            action: 'process_document',
            document_id: documentId,
            _wpnonce: $button.data('nonce')
        }, function(response) {
            if (response.success) {
                // Reload page to show updated status
                window.location.reload();
            } else {
                alert(response.data);
                $button.prop('disabled', false);
            }
        }).fail(function() {
            alert('An error occurred. Please try again.');
            $button.prop('disabled', false);
        });
    });

    // Handle document completion
    $('.complete-document').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var documentId = $button.data('id');
        var createdBy = $('#created_by').val();
        
        if (!createdBy) {
            alert('Please enter your name.');
            return;
        }
        
        // Disable button
        $button.prop('disabled', true);
        
        // Send AJAX request
        $.post(ajaxurl, {
            action: 'complete_document',
            document_id: documentId,
            created_by: createdBy,
            _wpnonce: $button.data('nonce')
        }, function(response) {
            if (response.success) {
                // Reload page to show updated status
                window.location.reload();
            } else {
                alert(response.data);
                $button.prop('disabled', false);
            }
        }).fail(function() {
            alert('An error occurred. Please try again.');
            $button.prop('disabled', false);
        });
    });

    // Handle revision submission
    $('.submit-revision').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var $form = $button.closest('form');
        var formData = new FormData($form[0]);
        formData.append('action', 'submit_revision');
        formData.append('_wpnonce', $button.data('nonce'));
        
        // Disable button
        $button.prop('disabled', true);
        
        // Send AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Reload page to show updated status
                    window.location.reload();
                } else {
                    alert(response.data);
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $button.prop('disabled', false);
            }
        });
    });

    // Handle search
    var searchTimeout;
    $('#search_id-search-input').on('keyup', function() {
        clearTimeout(searchTimeout);
        var $input = $(this);
        
        searchTimeout = setTimeout(function() {
            var searchTerm = $input.val();
            if (searchTerm.length >= 2) {
                $input.closest('form').submit();
            }
        }, 500);
    });
}); 