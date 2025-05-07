<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Document_List_Table extends WP_List_Table {
    public function __construct() {
        parent::__construct(array(
            'singular' => 'document',
            'plural'   => 'documents',
            'ajax'     => false
        ));
    }

    public function get_columns() {
        return array(
            'cb'            => '<input type="checkbox" />',
            'doc_number'    => __('Doc Number', 'doc-control-system'),
            'file_name'     => __('File Name', 'doc-control-system'),
            'originator'    => __('Originator', 'doc-control-system'),
            'created_by'    => __('Created By', 'doc-control-system'),
            'created_date'  => __('Created Date', 'doc-control-system'),
            'status'        => __('Status', 'doc-control-system'),
            'department'    => __('Department', 'doc-control-system')
        );
    }

    public function prepare_items() {
        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = $this->get_total_items();

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ));

        $this->items = $this->get_items($per_page, $current_page);
    }

    private function get_total_items() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'doc_control_documents';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    }

    private function get_items($per_page, $page_number) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'doc_control_documents';
        
        $sql = "SELECT * FROM $table_name";
        
        // Handle search
        if (!empty($_REQUEST['s'])) {
            $search = sanitize_text_field($_REQUEST['s']);
            $sql .= $wpdb->prepare(
                " WHERE doc_number LIKE %s OR file_name LIKE %s OR originator LIKE %s",
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%',
                '%' . $wpdb->esc_like($search) . '%'
            );
        }
        
        // Handle sorting
        if (!empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        } else {
            $sql .= ' ORDER BY created_date DESC';
        }
        
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;
        
        return $wpdb->get_results($sql, ARRAY_A);
    }

    public function column_default($item, $column_name) {
        return $item[$column_name];
    }

    public function column_doc_number($item) {
        $actions = array(
            'edit'   => sprintf('<a href="?page=doc-control&action=edit&id=%s">%s</a>', $item['id'], __('Edit', 'doc-control-system')),
            'delete' => sprintf('<a href="?page=doc-control&action=delete&id=%s&_wpnonce=%s">%s</a>', $item['id'], wp_create_nonce('delete_document_' . $item['id']), __('Delete', 'doc-control-system'))
        );
        
        return sprintf('%1$s %2$s', $item['doc_number'], $this->row_actions($actions));
    }

    public function column_master_file_location($item) {
        return sprintf(
            '<span class="file-path" data-path="%s"><i class="dashicons dashicons-media-document"></i></span>',
            esc_attr($item['master_file_location'])
        );
    }

    public function column_pdf_file_location($item) {
        return sprintf(
            '<span class="file-path" data-path="%s"><i class="dashicons dashicons-media-document"></i></span>',
            esc_attr($item['pdf_file_location'])
        );
    }

    public function get_sortable_columns() {
        return array(
            'doc_number'   => array('doc_number', true),
            'file_name'    => array('file_name', false),
            'originator'   => array('originator', false),
            'created_by'   => array('created_by', false),
            'created_date' => array('created_date', false),
            'status'       => array('status', false),
            'department'   => array('department', false)
        );
    }

    public function get_bulk_actions() {
        return array(
            'delete' => __('Delete', 'doc-control-system')
        );
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
        );
    }
} 