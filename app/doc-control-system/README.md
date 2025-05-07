# Document Control System

A friendly, professional WordPress plugin for internal document control between departments and marketing. Designed to automate document submission, renaming, storage, and revision workflows—especially for PowerPoint files—while maintaining a searchable database and robust admin tools.

---

## Features
- **Automated Document Submission:** Simple frontend form for department staff to submit PowerPoint files with department, originator, and PDF destination path.
- **Auto-assign Document Numbers:** Each document receives a unique, sequential control number.
- **File Renaming & Storage:** Files are renamed and stored in a secure, organized structure.
- **Searchable Database:** Admins can search, filter, and manage all documents from a custom admin interface.
- **Revision Management:** Submit, track, and process document revisions with clear status updates.
- **Admin Process View:** Marketing/admin staff can process, complete, and manage document workflows with dedicated screens.
- **Bulk Actions:** Delete and manage multiple documents at once.
- **Accessible & Responsive:** UI follows accessibility (WCAG 2.1) and responsive design best practices.

---

## Setup & Installation
1. **Requirements:**
   - WordPress 5.8+
   - PHP 7.4+
   - Node.js v18+ (for development)
2. **Installation:**
   - Copy the `doc-control-system` folder into your WordPress `/wp-content/plugins/` directory.
   - Activate the plugin from the WordPress admin Plugins page.
3. **Database:**
   - On activation, the plugin creates a custom table for document records.

---

## Usage
- **Frontend Submission:** Use the `[doc_control_form]` shortcode on any page to display the document submission form.
- **Admin Management:**
  - Go to **Doc Control** in the WordPress admin menu.
  - Search, filter, and manage documents.
  - Process, complete, or submit revisions using the action links.
- **File Path Copy:** Click file path icons to copy file locations for easy sharing.

---

## Development Standards
- **Tech Stack:** PHP, WordPress, jQuery, HTML/CSS (admin uses custom CSS, public uses separate styles)
- **Coding Style:**
  - Strict TypeScript typing (for React Native/mobile, not this plugin)
  - Functional, single-responsibility PHP classes
  - Clear, concise comments (friendly, direct, and supportive tone)
  - Follows SimpliFi's brand guidelines: approachable, clear, gently humorous, and mindful of user stress
- **Accessibility:**
  - All forms and admin screens are accessible and keyboard-friendly
  - Proper color contrast and ARIA attributes where needed
- **Security:**
  - Nonces for all form submissions
  - User capability checks for admin actions
  - Input validation and sanitization throughout

---

## File Structure
```
doc-control-system/
├── admin/
│   ├── css/admin.css
│   └── js/admin.js
├── includes/
│   ├── class-document-post-type.php
│   ├── class-document-metabox.php
│   ├── class-document-list-table.php
│   ├── class-document-processor.php
│   └── class-form-handler.php
├── public/
│   ├── css/public.css
│   └── js/public.js
├── templates/
│   ├── admin-process-view.php
│   └── submission-form.php
└── doc-control-system.php
```

---

## Contributing
We welcome improvements! Please:
- Follow the existing code style and SimpliFi's brand tone
- Document any new features or changes
- Test thoroughly (unit, integration, and accessibility)
- Open a pull request with a clear description

---

## Support & Feedback
If you have questions, suggestions, or need a gentle nudge in the right direction, please contact the project maintainer or open an issue.

---

*SimpliFi: making document control quietly confident, gently humorous, and always supportive.* 