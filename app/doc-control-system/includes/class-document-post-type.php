<?php

class Document_Post_Type {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
    }

    public function register_post_type() {
        $labels = array(
            'name'               => _x('Documents', 'post type general name', 'doc-control-system'),
            'singular_name'      => _x('Document', 'post type singular name', 'doc-control-system'),
            'menu_name'          => _x('Documents', 'admin menu', 'doc-control-system'),
            'name_admin_bar'     => _x('Document', 'add new on admin bar', 'doc-control-system'),
            'add_new'            => _x('Add New', 'document', 'doc-control-system'),
            'add_new_item'       => __('Add New Document', 'doc-control-system'),
            'new_item'           => __('New Document', 'doc-control-system'),
            'edit_item'          => __('Edit Document', 'doc-control-system'),
            'view_item'          => __('View Document', 'doc-control-system'),
            'all_items'          => __('All Documents', 'doc-control-system'),
            'search_items'       => __('Search Documents', 'doc-control-system'),
            'not_found'          => __('No documents found.', 'doc-control-system'),
            'not_found_in_trash' => __('No documents found in Trash.', 'doc-control-system')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => false, // We'll use our custom menu
            'query_var'          => true,
            'rewrite'            => array('slug' => 'document'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array('title', 'author'),
            'show_in_rest'       => true,
        );

        register_post_type('document', $args);

        // Register document status taxonomy
        $status_labels = array(
            'name'              => _x('Document Status', 'taxonomy general name', 'doc-control-system'),
            'singular_name'     => _x('Document Status', 'taxonomy singular name', 'doc-control-system'),
            'search_items'      => __('Search Document Statuses', 'doc-control-system'),
            'all_items'         => __('All Document Statuses', 'doc-control-system'),
            'edit_item'         => __('Edit Document Status', 'doc-control-system'),
            'update_item'       => __('Update Document Status', 'doc-control-system'),
            'add_new_item'      => __('Add New Document Status', 'doc-control-system'),
            'new_item_name'     => __('New Document Status Name', 'doc-control-system'),
            'menu_name'         => __('Status', 'doc-control-system'),
        );

        $status_args = array(
            'hierarchical'      => true,
            'labels'            => $status_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'document-status'),
            'show_in_rest'      => true,
        );

        register_taxonomy('document_status', array('document'), $status_args);

        // Register department taxonomy
        $department_labels = array(
            'name'              => _x('Departments', 'taxonomy general name', 'doc-control-system'),
            'singular_name'     => _x('Department', 'taxonomy singular name', 'doc-control-system'),
            'search_items'      => __('Search Departments', 'doc-control-system'),
            'all_items'         => __('All Departments', 'doc-control-system'),
            'edit_item'         => __('Edit Department', 'doc-control-system'),
            'update_item'       => __('Update Department', 'doc-control-system'),
            'add_new_item'      => __('Add New Department', 'doc-control-system'),
            'new_item_name'     => __('New Department Name', 'doc-control-system'),
            'menu_name'         => __('Department', 'doc-control-system'),
        );

        $department_args = array(
            'hierarchical'      => true,
            'labels'            => $department_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'department'),
            'show_in_rest'      => true,
        );

        register_taxonomy('department', array('document'), $department_args);

        // Add default departments
        $this->add_default_departments();
    }

    private function add_default_departments() {
        $departments = array(
            'Accounting',
            'Admin',
            'IT',
            'Marketing',
            'Sales',
            'Service',
            'Shop'
        );

        foreach ($departments as $department) {
            if (!term_exists($department, 'department')) {
                wp_insert_term($department, 'department');
            }
        }
    }
} 