-- MaxtDocs Database Schema (MySQL)

CREATE DATABASE IF NOT EXISTS maxtdocs;
USE maxtdocs;

-- Users Table
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

-- Insert default super admin user (password: admin123, hash must be generated and replaced here)
INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active)
VALUES ('admin', 'admin@maxtdocs.com', '$2b$10$1BoABXYXbeMo2UcWDbSY3eKG99Qz6dYw76g/NYDf4Z61Y/nZprgny', 'Admin', 'User', 'super_admin', TRUE)
ON DUPLICATE KEY UPDATE username=username; 