import React, { useState } from 'react';
import { authService } from '../services/api';

interface Department {
  id: number;
  name: string;
  folder_path: string;
}

const DocumentUpload: React.FC = () => {
  const [file, setFile] = useState<File | null>(null);
  const [departmentId, setDepartmentId] = useState<string>('');
  const [documentType, setDocumentType] = useState<'master' | 'working'>('working');
  const [uploading, setUploading] = useState(false);
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');

  // Sample departments - in real app, fetch from API
  const departments: Department[] = [
    { id: 1, name: 'Marketing', folder_path: 'marketing' },
    { id: 2, name: 'Engineering', folder_path: 'engineering' },
    { id: 3, name: 'HR', folder_path: 'hr' },
    { id: 4, name: 'Finance', folder_path: 'finance' }
  ];

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const selectedFile = e.target.files?.[0];
    if (selectedFile) {
      setFile(selectedFile);
      setError('');
    }
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!file) {
      setError('Please select a file');
      return;
    }

    if (!departmentId) {
      setError('Please select a department');
      return;
    }

    setUploading(true);
    setError('');
    setMessage('');

    try {
      const token = localStorage.getItem('token');
      if (!token) {
        throw new Error('Not authenticated');
      }

      const formData = new FormData();
      formData.append('document', file);
      formData.append('department_id', departmentId);
      formData.append('document_type', documentType);

      const response = await fetch('http://localhost:3001/api/documents/upload', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Upload failed');
      }

      const result = await response.json();
      setMessage('Document uploaded successfully!');
      setFile(null);
      setDepartmentId('');
      setDocumentType('working');
      
      // Reset file input
      const fileInput = document.getElementById('file-input') as HTMLInputElement;
      if (fileInput) fileInput.value = '';
      
    } catch (err: any) {
      setError(err.message || 'Upload failed');
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="document-upload">
      <h3>Upload Document</h3>
      
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label htmlFor="file-input">Select File:</label>
          <input
            id="file-input"
            type="file"
            onChange={handleFileChange}
            accept=".pdf,.doc,.docx,.xls,.xlsx,.txt,.jpg,.jpeg,.png"
            required
          />
          {file && (
            <p className="file-info">
              Selected: {file.name} ({(file.size / 1024 / 1024).toFixed(2)} MB)
            </p>
          )}
        </div>

        <div className="form-group">
          <label htmlFor="department">Department:</label>
          <select
            id="department"
            value={departmentId}
            onChange={(e) => setDepartmentId(e.target.value)}
            required
          >
            <option value="">Select Department</option>
            {departments.map(dept => (
              <option key={dept.id} value={dept.id}>
                {dept.name}
              </option>
            ))}
          </select>
        </div>

        <div className="form-group">
          <label htmlFor="document-type">Document Type:</label>
          <select
            id="document-type"
            value={documentType}
            onChange={(e) => setDocumentType(e.target.value as 'master' | 'working')}
          >
            <option value="working">Working Document</option>
            <option value="master">Master Document</option>
          </select>
        </div>

        {error && <div className="error-message">{error}</div>}
        {message && <div className="success-message">{message}</div>}

        <button
          type="submit"
          disabled={uploading || !file || !departmentId}
          className="upload-button"
        >
          {uploading ? 'Uploading...' : 'Upload Document'}
        </button>
      </form>
    </div>
  );
};

export default DocumentUpload; 