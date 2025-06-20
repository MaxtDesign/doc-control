import React from 'react';
import { useNavigate } from 'react-router-dom';
import { authService } from '../services/api';
import DocumentUpload from './DocumentUpload';
import DocumentList from './DocumentList';

const Dashboard: React.FC = () => {
  const navigate = useNavigate();
  const user = authService.getCurrentUser();

  const handleLogout = () => {
    authService.logout();
    navigate('/login');
  };

  return (
    <div className="dashboard">
      <nav className="navbar">
        <div className="navbar-content">
          <h1>MaxtDocs - Document Control System</h1>
          <div className="navbar-right">
            <span className="welcome-text">Welcome, {user?.username}!</span>
            <button className="logout-button" onClick={handleLogout}>
              Logout
            </button>
          </div>
        </div>
      </nav>

      <div className="main-content">
        <div className="dashboard-content">
          <div className="dashboard-text">
            <h2>Document Management Dashboard</h2>
            <p>Upload, manage, and track your documents with full version control.</p>
            
            <div className="user-info">
              <p><strong>User ID:</strong> {user?.id}</p>
              <p><strong>Email:</strong> {user?.email}</p>
              <p><strong>Role:</strong> {user?.role}</p>
              <p><strong>Department:</strong> {user?.department_name}</p>
            </div>

            <DocumentUpload />
            <DocumentList />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard; 