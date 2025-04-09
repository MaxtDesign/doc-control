<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="mx-doc-control-requests">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>User</th>
                    <th>Department</th>
                    <th>File Name</th>
                    <th>Original File</th>
                    <th>Requested Destination</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="mx-doc-control-requests-list">
                <!-- Requests will be loaded here via AJAX -->
            </tbody>
        </table>
        
        <div class="mx-doc-control-pagination">
            <!-- Pagination will be loaded here via AJAX -->
        </div>
    </div>
</div>

<!-- Document Request Processing Modal -->
<div id="mx-doc-control-process-modal" class="mx-doc-control-modal" style="display: none;">
    <div class="mx-doc-control-modal-content">
        <span class="mx-doc-control-modal-close">&times;</span>
        <h2>Process Document Request</h2>
        
        <form id="mx-doc-control-process-form">
            <input type="hidden" id="request_id" name="request_id">
            
            <div class="form-field">
                <label for="doc_id">Document ID:</label>
                <input type="text" id="doc_id" name="doc_id" required>
            </div>
            
            <div class="form-field">
                <label for="master_path">Master File Path:</label>
                <input type="text" id="master_path" name="master_path" required>
            </div>
            
            <div class="form-field">
                <label for="document_path">Document Path:</label>
                <input type="text" id="document_path" name="document_path" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="button button-primary">Process Request</button>
                <button type="button" class="button mx-doc-control-modal-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>

<template id="mx-doc-control-request-row">
    <tr>
        <td class="request-id"></td>
        <td class="user"></td>
        <td class="department"></td>
        <td class="file-name"></td>
        <td class="original-file">
            <span class="dashicons dashicons-admin-page file-icon" title=""></span>
        </td>
        <td class="requested-destination"></td>
        <td class="request-type"></td>
        <td class="status"></td>
        <td class="actions">
            <button class="button process-request">Process</button>
        </td>
    </tr>
</template>

<script type="text/javascript">
jQuery(document).ready(function($) {
    function loadRequests(page = 1) {
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'mx_doc_control_get_requests',
                nonce: mx_doc_control.nonce,
                page: page
            },
            success: function(response) {
                if (response.success) {
                    var tbody = $('#mx-doc-control-requests-list');
                    tbody.empty();
                    
                    response.data.requests.forEach(function(request) {
                        var template = $('#mx-doc-control-request-row').html();
                        var row = $(template);
                        
                        row.find('.request-id').text(request.id);
                        row.find('.user').text(request.user_name);
                        row.find('.department').text(request.department);
                        row.find('.file-name').text(request.file_name);
                        row.find('.original-file .file-icon').attr('title', request.original_file_path);
                        row.find('.requested-destination').text(request.requested_destination);
                        row.find('.request-type').text(request.is_revision ? 'Revision' : 'New');
                        row.find('.status').text(request.status);
                        
                        if (request.status === 'completed') {
                            row.find('.process-request').prop('disabled', true);
                        }
                        
                        tbody.append(row);
                    });
                    
                    updatePagination(response.data.current_page, response.data.total_pages);
                }
            }
        });
    }
    
    function updatePagination(currentPage, totalPages) {
        var pagination = $('.mx-doc-control-pagination');
        pagination.empty();
        
        if (totalPages > 1) {
            var paginationHtml = '<div class="tablenav-pages">';
            
            if (currentPage > 1) {
                paginationHtml += '<a class="prev-page button" href="#" data-page="' + (currentPage - 1) + '">‹</a>';
            }
            
            for (var i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    paginationHtml += '<span class="current-page">' + i + '</span>';
                } else {
                    paginationHtml += '<a class="page-number" href="#" data-page="' + i + '">' + i + '</a>';
                }
            }
            
            if (currentPage < totalPages) {
                paginationHtml += '<a class="next-page button" href="#" data-page="' + (currentPage + 1) + '">›</a>';
            }
            
            paginationHtml += '</div>';
            pagination.html(paginationHtml);
        }
    }
    
    // Initial load
    loadRequests();
    
    // Pagination handler
    $(document).on('click', '.mx-doc-control-pagination a', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadRequests(page);
    });
    
    // Copy path to clipboard
    $(document).on('click', '.file-icon', function() {
        var path = $(this).attr('title');
        navigator.clipboard.writeText(path).then(function() {
            alert('Path copied to clipboard!');
        });
    });
    
    // Process request modal
    $(document).on('click', '.process-request', function() {
        var row = $(this).closest('tr');
        var requestId = row.find('.request-id').text();
        var fileName = row.find('.file-name').text();
        var originalPath = row.find('.file-icon').attr('title');
        var destination = row.find('.requested-destination').text();
        
        $('#request_id').val(requestId);
        $('#doc_id').val('DOC' + requestId.padStart(4, '0'));
        $('#master_path').val(originalPath);
        $('#document_path').val(destination);
        
        $('#mx-doc-control-process-modal').show();
    });
    
    // Close modal
    $('.mx-doc-control-modal-close, .mx-doc-control-modal-cancel').on('click', function() {
        $('#mx-doc-control-process-modal').hide();
    });
    
    // Process form submission
    $('#mx-doc-control-process-form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mx_doc_control_process_request',
                nonce: mx_doc_control.nonce,
                request_id: $('#request_id').val(),
                doc_id: $('#doc_id').val(),
                master_path: $('#master_path').val(),
                document_path: $('#document_path').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#mx-doc-control-process-modal').hide();
                    loadRequests();
                } else {
                    alert('Error processing request: ' + response.data);
                }
            }
        });
    });
});
</script>

<style>
.mx-doc-control-modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
}

.mx-doc-control-modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 50%;
    position: relative;
}

.mx-doc-control-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.mx-doc-control-modal-close:hover {
    color: black;
}

.form-field {
    margin-bottom: 15px;
}

.form-field label {
    display: block;
    margin-bottom: 5px;
}

.form-field input {
    width: 100%;
}

.form-actions {
    margin-top: 20px;
    text-align: right;
}

.form-actions button {
    margin-left: 10px;
}

.file-icon {
    cursor: pointer;
}

.tablenav-pages {
    margin: 1em 0;
    text-align: right;
}

.tablenav-pages a,
.tablenav-pages span {
    display: inline-block;
    min-width: 30px;
    height: 30px;
    margin: 0 2px;
    padding: 0 4px;
    font-size: 16px;
    line-height: 28px;
    text-align: center;
    text-decoration: none;
    color: #2271b1;
    border: 1px solid #2271b1;
    background: #f6f7f7;
}

.tablenav-pages .current-page {
    color: #000;
    border-color: #2271b1;
    background: #2271b1;
    color: #fff;
}
</style> 