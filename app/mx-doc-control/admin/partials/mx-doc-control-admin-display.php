<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="mx-doc-control-admin-header">
        <div class="mx-doc-control-search">
            <input type="text" id="mx-doc-control-search" placeholder="Search documents...">
            <button class="button" id="mx-doc-control-search-button">Search</button>
        </div>
    </div>

    <div class="mx-doc-control-documents">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>DOC ID</th>
                    <th>Description</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                    <th>Master File</th>
                    <th>Living File</th>
                    <th>Revision</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="mx-doc-control-documents-list">
                <!-- Documents will be loaded here via AJAX -->
            </tbody>
        </table>
        
        <div class="mx-doc-control-pagination">
            <!-- Pagination will be loaded here via AJAX -->
        </div>
    </div>
</div>

<template id="mx-doc-control-document-row">
    <tr>
        <td class="doc-id"></td>
        <td class="description">
            <span class="description-text"></span>
            <span class="edit-icon dashicons dashicons-edit"></span>
        </td>
        <td class="created-by"></td>
        <td class="created-date"></td>
        <td class="master-file">
            <span class="dashicons dashicons-admin-page file-icon" title=""></span>
        </td>
        <td class="document-path">
            <span class="dashicons dashicons-admin-page file-icon" title=""></span>
        </td>
        <td class="revision"></td>
        <td class="actions">
            <button class="button edit-document">Edit</button>
        </td>
    </tr>
</template>

<script type="text/javascript">
jQuery(document).ready(function($) {
    function loadDocuments(page = 1) {
        var search = $('#mx-doc-control-search').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'mx_doc_control_get_documents',
                nonce: mx_doc_control.nonce,
                page: page,
                search: search
            },
            success: function(response) {
                if (response.success) {
                    var tbody = $('#mx-doc-control-documents-list');
                    tbody.empty();
                    
                    response.data.documents.forEach(function(doc) {
                        var template = $('#mx-doc-control-document-row').html();
                        var row = $(template);
                        
                        row.find('.doc-id').text(doc.doc_id);
                        row.find('.description-text').text(doc.description);
                        row.find('.created-by').text(doc.created_by);
                        row.find('.created-date').text(doc.created_date);
                        row.find('.master-file .file-icon').attr('title', doc.master_file_path);
                        row.find('.document-path .file-icon').attr('title', doc.document_path);
                        row.find('.revision').text(doc.revision || '');
                        
                        tbody.append(row);
                    });
                    
                    // Update pagination
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
            
            // Previous button
            if (currentPage > 1) {
                paginationHtml += '<a class="prev-page button" href="#" data-page="' + (currentPage - 1) + '">‹</a>';
            }
            
            // Page numbers
            for (var i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    paginationHtml += '<span class="current-page">' + i + '</span>';
                } else {
                    paginationHtml += '<a class="page-number" href="#" data-page="' + i + '">' + i + '</a>';
                }
            }
            
            // Next button
            if (currentPage < totalPages) {
                paginationHtml += '<a class="next-page button" href="#" data-page="' + (currentPage + 1) + '">›</a>';
            }
            
            paginationHtml += '</div>';
            pagination.html(paginationHtml);
        }
    }
    
    // Initial load
    loadDocuments();
    
    // Search handler
    $('#mx-doc-control-search-button').on('click', function() {
        loadDocuments(1);
    });
    
    // Pagination handler
    $(document).on('click', '.mx-doc-control-pagination a', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadDocuments(page);
    });
    
    // Copy path to clipboard
    $(document).on('click', '.file-icon', function() {
        var path = $(this).attr('title');
        navigator.clipboard.writeText(path).then(function() {
            alert('Path copied to clipboard!');
        });
    });
});
</script>

<style>
.mx-doc-control-admin-header {
    margin: 20px 0;
}

.mx-doc-control-search {
    display: flex;
    gap: 10px;
}

.mx-doc-control-search input {
    width: 300px;
}

.file-icon {
    cursor: pointer;
}

.edit-icon {
    margin-left: 5px;
    cursor: pointer;
    opacity: 0.5;
}

.edit-icon:hover {
    opacity: 1;
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