import React, { useState, useEffect } from 'react';

interface Document {
  id: number;
  original_name: string;
  file_size: number;
  document_type: 'master' | 'working';
  status: 'active' | 'pending_approval' | 'archived' | 'rejected';
  is_checked_out: boolean;
  version_number: number;
  created_at: string;
  checked_out_by?: number;
  checked_out_at?: string;
  uploaded_by_name: string;
  checked_out_by_name?: string;
  revision_notes?: string;
}

interface Version {
  id: number;
  original_name: string;
  file_size: number;
  document_type: 'master' | 'working';
  status: 'active' | 'pending_approval' | 'archived' | 'rejected';
  version_number: number;
  revision_notes?: string;
  created_at: string;
  uploaded_by_name: string;
}

const DocumentList: React.FC = () => {
  const [documents, setDocuments] = useState<Document[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [selectedDepartment, setSelectedDepartment] = useState<string>('1');
  const [showCheckinModal, setShowCheckinModal] = useState(false);
  const [selectedDocument, setSelectedDocument] = useState<Document | null>(null);
  const [showVersionHistory, setShowVersionHistory] = useState(false);
  const [versionHistory, setVersionHistory] = useState<Version[]>([]);
  const [checkinForm, setCheckinForm] = useState({
    revisedFile: null as File | null,
    revisionNotes: '',
    requiresApproval: false
  });

  const departments = [
    { id: 1, name: 'Marketing' },
    { id: 2, name: 'Engineering' },
    { id: 3, name: 'HR' },
    { id: 4, name: 'Finance' }
  ];

  useEffect(() => {
    fetchDocuments();
  }, [selectedDepartment]);

  const fetchDocuments = async () => {
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        throw new Error('Not authenticated');
      }

      const response = await fetch(`http://localhost:3001/api/documents/department/${selectedDepartment}`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      if (!response.ok) {
        throw new Error('Failed to fetch documents');
      }

      const data = await response.json();
      setDocuments(data.documents || []);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleDownload = async (documentId: number, fileName: string) => {
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        throw new Error('Not authenticated');
      }

      const response = await fetch(`http://localhost:3001/api/documents/download/${documentId}`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      if (!response.ok) {
        throw new Error('Download failed');
      }

      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
    } catch (err: any) {
      alert('Download failed: ' + err.message);
    }
  };

  const handleCheckout = async (documentId: number) => {
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        throw new Error('Not authenticated');
      }

      const response = await fetch(`http://localhost:3001/api/documents/${documentId}/checkout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Checkout failed');
      }

      fetchDocuments();
      alert('Document checked out successfully!');
    } catch (err: any) {
      alert('Checkout failed: ' + err.message);
    }
  };

  const handleCheckinClick = (document: Document) => {
    setSelectedDocument(document);
    setShowCheckinModal(true);
  };

  const handleCheckin = async () => {
    if (!selectedDocument) return;

    try {
      const token = localStorage.getItem('token');
      if (!token) {
        throw new Error('Not authenticated');
      }

      const formData = new FormData();
      if (checkinForm.revisedFile) {
        formData.append('revised_document', checkinForm.revisedFile);
      }
      formData.append('revision_notes', checkinForm.revisionNotes);
      formData.append('requires_approval', checkinForm.requiresApproval.toString());

      const response = await fetch(`http://localhost:3001/api/documents/${selectedDocument.id}/checkin`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Checkin failed');
      }

      const result = await response.json();
      fetchDocuments();
      setShowCheckinModal(false);
      setSelectedDocument(null);
      setCheckinForm({ revisedFile: null, revisionNotes: '', requiresApproval: false });
      
      if (result.version) {
        alert(`Document checked in successfully! New version ${result.version} created.`);
      } else {
        alert('Document checked in successfully!');
      }
    } catch (err: any) {
      alert('Checkin failed: ' + err.message);
    }
  };

  const handleViewVersions = async (documentId: number) => {
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        throw new Error('Not authenticated');
      }

      const response = await fetch(`http://localhost:3001/api/documents/${documentId}/versions`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });

      if (!response.ok) {
        throw new Error('Failed to fetch version history');
      }

      const data = await response.json();
      setVersionHistory(data.versions || []);
      setShowVersionHistory(true);
    } catch (err: any) {
      alert('Failed to fetch version history: ' + err.message);
    }
  };

  const handleApprove = async (documentId: number, approved: boolean) => {
    try {
      const token = localStorage.getItem('token');
      if (!token) {
        throw new Error('Not authenticated');
      }

      const approvalNotes = prompt(approved ? 'Enter approval notes (optional):' : 'Enter rejection reason:');
      
      const response = await fetch(`http://localhost:3001/api/documents/${documentId}/approve`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          approved: approved.toString(),
          approval_notes: approvalNotes
        })
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Approval failed');
      }

      fetchDocuments();
      alert(`Document ${approved ? 'approved' : 'rejected'} successfully!`);
    } catch (err: any) {
      alert('Approval failed: ' + err.message);
    }
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
  };

  const getStatusBadgeClass = (status: string) => {
    switch (status) {
      case 'active': return 'status-badge status-active';
      case 'pending_approval': return 'status-badge status-pending';
      case 'archived': return 'status-badge status-archived';
      case 'rejected': return 'status-badge status-rejected';
      default: return 'status-badge status-active';
    }
  };

  if (loading) {
    return <div className="document-list">Loading documents...</div>;
  }

  return (
    <div className="document-list">
      <h3>Documents</h3>
      
      <div style={{ marginBottom: '1rem' }}>
        <label htmlFor="department-select">Department: </label>
        <select
          id="department-select"
          value={selectedDepartment}
          onChange={(e) => setSelectedDepartment(e.target.value)}
          style={{ marginLeft: '0.5rem', padding: '0.25rem' }}
        >
          {departments.map(dept => (
            <option key={dept.id} value={dept.id}>
              {dept.name}
            </option>
          ))}
        </select>
      </div>

      {error && <div className="error-message">{error}</div>}

      {documents.length === 0 ? (
        <p style={{ textAlign: 'center', color: '#6b7280' }}>
          No documents found in this department.
        </p>
      ) : (
        <table className="document-table">
          <thead>
            <tr>
              <th>Document Name</th>
              <th>Type</th>
              <th>Version</th>
              <th>Size</th>
              <th>Status</th>
              <th>Checked Out</th>
              <th>Uploaded</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {documents.map(doc => (
              <tr key={doc.id}>
                <td>{doc.original_name}</td>
                <td style={{ textTransform: 'capitalize' }}>{doc.document_type}</td>
                <td>v{doc.version_number}</td>
                <td>{formatFileSize(doc.file_size)}</td>
                <td>
                  <span className={getStatusBadgeClass(doc.status)}>
                    {doc.status.replace('_', ' ')}
                  </span>
                </td>
                <td>
                  {doc.is_checked_out ? (
                    <span style={{ color: '#dc2626' }}>
                      Yes by {doc.checked_out_by_name}
                    </span>
                  ) : (
                    'No'
                  )}
                </td>
                <td>{formatDate(doc.created_at)}</td>
                <td>
                  <div className="document-actions">
                    <button
                      className="action-button download-button"
                      onClick={() => handleDownload(doc.id, doc.original_name)}
                    >
                      Download
                    </button>
                    <button
                      className="action-button version-button"
                      onClick={() => handleViewVersions(doc.id)}
                    >
                      Versions
                    </button>
                    {!doc.is_checked_out ? (
                      <button
                        className="action-button checkout-button"
                        onClick={() => handleCheckout(doc.id)}
                      >
                        Checkout
                      </button>
                    ) : (
                      <button
                        className="action-button checkin-button"
                        onClick={() => handleCheckinClick(doc)}
                      >
                        Checkin
                      </button>
                    )}
                    {doc.status === 'pending_approval' && (
                      <>
                        <button
                          className="action-button approve-button"
                          onClick={() => handleApprove(doc.id, true)}
                        >
                          Approve
                        </button>
                        <button
                          className="action-button reject-button"
                          onClick={() => handleApprove(doc.id, false)}
                        >
                          Reject
                        </button>
                      </>
                    )}
                  </div>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}

      {/* Checkin Modal */}
      {showCheckinModal && selectedDocument && (
        <div className="modal-overlay">
          <div className="modal">
            <h3>Checkin Document: {selectedDocument.original_name}</h3>
            <div className="form-group">
              <label>Upload Revised Document (optional):</label>
              <input
                type="file"
                onChange={(e) => setCheckinForm({
                  ...checkinForm,
                  revisedFile: e.target.files?.[0] || null
                })}
              />
            </div>
            <div className="form-group">
              <label>Revision Notes:</label>
              <textarea
                value={checkinForm.revisionNotes}
                onChange={(e) => setCheckinForm({
                  ...checkinForm,
                  revisionNotes: e.target.value
                })}
                placeholder="Describe what changes were made..."
                rows={3}
              />
            </div>
            <div className="form-group">
              <label>
                <input
                  type="checkbox"
                  checked={checkinForm.requiresApproval}
                  onChange={(e) => setCheckinForm({
                    ...checkinForm,
                    requiresApproval: e.target.checked
                  })}
                />
                Requires approval
              </label>
            </div>
            <div className="modal-actions">
              <button onClick={handleCheckin} className="upload-button">
                Checkin Document
              </button>
              <button 
                onClick={() => {
                  setShowCheckinModal(false);
                  setSelectedDocument(null);
                  setCheckinForm({ revisedFile: null, revisionNotes: '', requiresApproval: false });
                }}
                className="cancel-button"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Version History Modal */}
      {showVersionHistory && (
        <div className="modal-overlay">
          <div className="modal">
            <h3>Version History</h3>
            <table className="document-table">
              <thead>
                <tr>
                  <th>Version</th>
                  <th>Document Name</th>
                  <th>Status</th>
                  <th>Size</th>
                  <th>Uploaded</th>
                  <th>Uploaded By</th>
                  <th>Revision Notes</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {versionHistory.map(version => (
                  <tr key={version.id}>
                    <td>v{version.version_number}</td>
                    <td>{version.original_name}</td>
                    <td>
                      <span className={getStatusBadgeClass(version.status)}>
                        {version.status.replace('_', ' ')}
                      </span>
                    </td>
                    <td>{formatFileSize(version.file_size)}</td>
                    <td>{formatDate(version.created_at)}</td>
                    <td>{version.uploaded_by_name}</td>
                    <td>{version.revision_notes || '-'}</td>
                    <td>
                      <button
                        className="action-button download-button"
                        onClick={() => handleDownload(version.id, version.original_name)}
                      >
                        Download
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <div className="modal-actions">
              <button 
                onClick={() => setShowVersionHistory(false)}
                className="cancel-button"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default DocumentList; 