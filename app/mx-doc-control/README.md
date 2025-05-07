# MX Doc Control

A WordPress plugin for internal document control management. This plugin helps organizations manage their documents by providing a structured way to track, version, and manage document metadata.

## Features

- Document submission form for team members
- Admin interface for document management
- Automatic document ID generation
- Revision tracking
- File path management
- Department-based organization
- Searchable document list
- Secure file handling

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Write permissions for the WordPress uploads directory

## Installation

1. Download the plugin files
2. Upload the `mx-doc-control` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the plugin settings under the 'MX Doc Control' menu

## Usage

### For Team Members

1. Access the document submission form using the shortcode `[mx_doc_control_form]`
2. Fill in the required information:
   - Department
   - Document Name
   - Upload File
   - Destination Path
   - Revision Information (if applicable)
3. Submit the form

### For Administrators

1. Access the admin interface through the WordPress admin menu
2. View and manage document requests
3. Process new document submissions
4. Manage document metadata
5. Search and filter documents

## Document ID Format

Documents are assigned IDs in the format `DOCXXXX` where XXXX is a sequential number.

## File Paths

The plugin manages two types of file paths:
1. Master File Path: The original working file location
2. Document Path: The final location where the document will be accessible to team members

## Security

- Only administrators can access the admin interface
- File uploads are restricted to specific file types
- All form submissions are validated and sanitized
- Nonce verification is implemented for all AJAX requests

## Support

For support, please contact your system administrator or the plugin developer.

## License

This plugin is proprietary software. All rights reserved.

## Changelog

### 1.0.0
- Initial release
- Basic document management functionality
- Document submission form
- Admin interface
- File handling system 