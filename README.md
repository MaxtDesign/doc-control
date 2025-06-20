# MaxtDocs - Document Control System

A comprehensive internal document control system designed for small businesses, featuring version control, approval workflows, and secure document management.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Database Setup](#database-setup)
- [Configuration](#configuration)
- [Running the Application](#running-the-application)
- [Usage Guide](#usage-guide)
- [API Documentation](#api-documentation)
- [Project Structure](#project-structure)
- [Contributing](#contributing)
- [License](#license)

## Overview

MaxtDocs is a full-stack document management system that provides enterprise-level document control for small businesses. The system includes user authentication, role-based access control, document versioning, approval workflows, and secure file storage.

## Features

### Core Functionality
- [x] User authentication with JWT tokens
- [x] Role-based access control (Admin, Manager, User, Viewer)
- [x] Department-based document organization
- [x] Document upload with file type validation
- [x] Document checkout/checkin system
- [x] Version control with revision history
- [x] Approval workflow for document changes
- [x] Document download functionality
- [x] Search and filter by department

### Document Management
- [x] Support for multiple file types (PDF, Word, Excel, etc.)
- [x] File size limits (10MB per file)
- [x] Automatic version numbering
- [x] Revision notes and change tracking
- [x] Document status tracking (Active, Pending Approval, Archived, Rejected)
- [x] Complete audit trail

### User Interface
- [x] Modern, responsive design
- [x] Intuitive document management interface
- [x] Modal dialogs for complex operations
- [x] Real-time status updates
- [x] Professional styling without external CSS frameworks

## Technology Stack

### Backend
- **Runtime**: Node.js
- **Framework**: Express.js
- **Language**: TypeScript
- **Database**: MySQL (via XAMPP)
- **Authentication**: JWT (JSON Web Tokens)
- **File Upload**: Multer
- **Database Driver**: mysql2

### Frontend
- **Framework**: React 18
- **Language**: TypeScript
- **Routing**: React Router
- **HTTP Client**: Fetch API
- **Styling**: Custom CSS
- **Build Tool**: Create React App

### Development Tools
- **Package Manager**: npm
- **TypeScript Compiler**: tsc
- **Development Server**: nodemon (backend), react-scripts (frontend)

## Prerequisites

Before installing MaxtDocs, ensure you have the following installed:

- **Node.js** (v16 or higher)
- **npm** (v8 or higher)
- **XAMPP** (for MySQL database)
- **Git** (for version control)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd MaxtDocs
   ```

2. **Install backend dependencies**
   ```bash
   cd backend
   npm install
   ```

3. **Install frontend dependencies**
   ```bash
   cd ../frontend
   npm install
   ```

## Database Setup

1. **Start XAMPP**
   - Launch XAMPP Control Panel
   - Start Apache and MySQL services

2. **Create database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `maxtdocs`

3. **Import schema**
   - Navigate to the `backend/database/` directory
   - Import `complete_schema.sql` into your `maxtdocs` database

4. **Verify setup**
   - Check that all tables are created successfully
   - Verify that sample data is imported (users, departments)

## Configuration

### Backend Configuration

1. **Environment variables** (create `.env` file in `backend/` directory)
   ```env
   DB_HOST=localhost
   DB_PORT=3306
   DB_USER=root
   DB_PASSWORD=
   DB_NAME=maxtdocs
   JWT_SECRET=your-secret-key-here
   PORT=3001
   ```

2. **Database connection**
   - Ensure MySQL is running on port 3306
   - Verify database credentials match your XAMPP setup

### Frontend Configuration

The frontend is configured to connect to the backend on `http://localhost:3001`. If you change the backend port, update the API calls in the frontend code.

## Running the Application

### Development Mode

1. **Start the backend server**
   ```bash
   cd backend
   npm start
   ```
   The backend will run on http://localhost:3001

2. **Start the frontend server** (in a new terminal)
   ```bash
   cd frontend
   npm start
   ```
   The frontend will run on http://localhost:3000

### Production Build

1. **Build the frontend**
   ```bash
   cd frontend
   npm run build
   ```

2. **Serve the production build**
   ```bash
   npm install -g serve
   serve -s build -l 3000
   ```

## Usage Guide

### Initial Login

1. Open http://localhost:3000 in your browser
2. Use the default credentials:
   - **Username**: admin
   - **Password**: password123

### Document Management Workflow

1. **Upload Document**
   - Select a file to upload
   - Choose document type (Master or Working)
   - Select department
   - Click "Upload Document"

2. **Checkout Document**
   - Find the document in the list
   - Click "Checkout" button
   - Download the document for editing

3. **Edit and Checkin**
   - Make changes to the downloaded document
   - Click "Checkin" button
   - Upload the revised file
   - Add revision notes
   - Choose if approval is required

4. **Approval Process** (if required)
   - Managers/Admins see pending documents
   - Click "Approve" or "Reject"
   - Add approval/rejection notes

5. **Version History**
   - Click "Versions" button to view document history
   - Download any previous version
   - Review revision notes and changes

### User Roles

- **Admin**: Full access to all departments and documents
- **Manager**: Access to their department's documents, can approve/reject
- **User**: Access to their department's documents, can checkout/checkin
- **Viewer**: Read-only access to their department's documents

## API Documentation

### Authentication Endpoints

- `POST /api/auth/login` - User login
- `POST /api/auth/register` - User registration
- `GET /api/auth/me` - Get current user info

### Document Endpoints

- `POST /api/documents/upload` - Upload new document
- `GET /api/documents/department/:id` - Get documents by department
- `GET /api/documents/download/:id` - Download document
- `POST /api/documents/:id/checkout` - Checkout document
- `POST /api/documents/:id/checkin` - Checkin document with revision
- `GET /api/documents/:id/versions` - Get version history
- `POST /api/documents/:id/approve` - Approve/reject document

## Project Structure

```
MaxtDocs/
├── backend/
│   ├── database/
│   │   ├── complete_schema.sql
│   │   └── schema.sql
│   ├── src/
│   │   ├── middleware/
│   │   │   └── auth.ts
│   │   ├── models/
│   │   │   ├── document.ts
│   │   │   └── user.ts
│   │   ├── routes/
│   │   │   ├── auth.ts
│   │   │   ├── documents.ts
│   │   │   └── index.ts
│   │   ├── db.ts
│   │   └── index.ts
│   ├── storage/
│   │   └── general/
│   ├── package.json
│   └── tsconfig.json
├── frontend/
│   ├── public/
│   ├── src/
│   │   ├── components/
│   │   │   ├── Dashboard.tsx
│   │   │   ├── DocumentList.tsx
│   │   │   ├── DocumentUpload.tsx
│   │   │   ├── Login.tsx
│   │   │   └── ProtectedRoute.tsx
│   │   ├── services/
│   │   │   └── api.ts
│   │   ├── App.css
│   │   ├── App.tsx
│   │   └── index.tsx
│   ├── package.json
│   └── tsconfig.json
├── .gitignore
├── package.json
└── README.md
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow TypeScript best practices
- Maintain consistent code formatting
- Write meaningful commit messages
- Test thoroughly before submitting PRs
- Update documentation for new features

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:
- Create an issue in the repository
- Check the documentation
- Review the code comments

## Status

- [x] Core functionality implemented
- [x] User authentication working
- [x] Document management complete
- [x] Version control implemented
- [x] Approval workflow functional
- [x] UI/UX polished
- [ ] Unit tests (planned)
- [ ] Integration tests (planned)
- [ ] Performance optimization (planned) 