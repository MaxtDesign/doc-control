-- MaxtDocs Complete Database Schema (MySQL)

CREATE DATABASE IF NOT EXISTS maxtdocs;
USE maxtdocs;

-- Users Table (already exists, but including for completeness)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('super_admin', 'dept_lead', 'employee', 'doc_controller') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Departments Table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    folder_path VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User_Departments Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS user_departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    is_lead BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_dept (user_id, department_id)
);

-- Documents Table
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_name VARCHAR(255) NOT NULL,
    current_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    document_type ENUM('master', 'working') NOT NULL,
    department_id INT,
    uploaded_by INT NOT NULL,
    version_number INT DEFAULT 1,
    parent_document_id INT,
    is_checked_out BOOLEAN DEFAULT FALSE,
    checked_out_by INT,
    checked_out_at TIMESTAMP NULL,
    status ENUM('active', 'pending_approval', 'archived') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_document_id) REFERENCES documents(id) ON DELETE SET NULL,
    FOREIGN KEY (checked_out_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Approval_Requests Table
CREATE TABLE IF NOT EXISTS approval_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    submitted_by INT NOT NULL,
    department_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    comments TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Audit_Log Table
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default super admin user (password: admin123)
INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active)
VALUES ('admin', 'admin@maxtdocs.com', '$2b$10$1BoABXYXbeMo2UcWDbSY3eKG99Qz6dYw76g/NYDf4Z61Y/nZprgny', 'Admin', 'User', 'super_admin', TRUE)
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample departments
INSERT INTO departments (name, folder_path, description) VALUES
('Marketing', 'marketing', 'Marketing department documents'),
('Engineering', 'engineering', 'Engineering department documents'),
('HR', 'hr', 'Human Resources department documents'),
('Finance', 'finance', 'Finance department documents')
ON DUPLICATE KEY UPDATE name=name;

-- Create indexes for better performance
CREATE INDEX idx_documents_department ON documents(department_id);
CREATE INDEX idx_documents_status ON documents(status);
CREATE INDEX idx_documents_uploaded_by ON documents(uploaded_by);
CREATE INDEX idx_approval_requests_status ON approval_requests(status);
CREATE INDEX idx_audit_log_user ON audit_log(user_id);
CREATE INDEX idx_audit_log_timestamp ON audit_log(timestamp); 