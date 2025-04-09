(function($) {
    'use strict';

    // Document ready
    $(document).ready(function() {
        // Handle path validation
        $('.doc-control-path-input').on('change', function() {
            validatePath($(this));
        });

        // Handle form submission
        $('#doc-control-settings-form').on('submit', function(e) {
            // Add any form validation here if needed
        });
    });

    // Path validation function
    function validatePath($input) {
        const path = $input.val();
        if (!path) {
            showError($input, 'Path cannot be empty');
            return false;
        }

        // Check if path exists
        $.ajax({
            url: docControlAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'doc_control_validate_path',
                path: path,
                nonce: docControlAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    showSuccess($input, 'Path is valid');
                } else {
                    showError($input, response.data.message || 'Invalid path');
                }
            },
            error: function() {
                showError($input, 'Error validating path');
            }
        });
    }

    // Show error message
    function showError($input, message) {
        const $error = $input.siblings('.error-message');
        if (!$error.length) {
            $error = $('<div class="error-message"></div>');
            $input.after($error);
        }
        $error.text(message).show();
    }

    // Show success message
    function showSuccess($input, message) {
        const $success = $input.siblings('.success-message');
        if (!$success.length) {
            $success = $('<div class="success-message"></div>');
            $input.after($success);
        }
        $success.text(message).show();
    }

})(jQuery); 