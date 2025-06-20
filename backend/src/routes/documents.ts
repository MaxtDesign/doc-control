import express from 'express';
import multer from 'multer';
import path from 'path';
import fs from 'fs';
import { createDocument, getDocumentsByDepartment, getDocumentById, checkoutDocument, checkinDocument } from '../models/document';
import { authenticateToken } from '../middleware/auth';
import { Document } from '../models/document';
import pool from '../db';

const router = express.Router();

// Configure multer for file uploads
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadDir = path.join(__dirname, '../../storage/general');
    if (!fs.existsSync(uploadDir)) {
      fs.mkdirSync(uploadDir, { recursive: true });
    }
    cb(null, uploadDir);
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1E9);
    cb(null, file.fieldname + '-' + uniqueSuffix + path.extname(file.originalname));
  }
});

const upload = multer({ 
  storage: storage,
  limits: {
    fileSize: 10 * 1024 * 1024 // 10MB limit
  }
});

// Upload document
router.post('/upload', authenticateToken, upload.single('document'), async (req, res) => {
  try {
    if (!req.file) {
      return res.status(400).json({ message: 'No file uploaded' });
    }

    const { document_type, department_id } = req.body;
    const user_id = (req as any).user.id;

    if (!document_type || !department_id) {
      return res.status(400).json({ message: 'Document type and department are required' });
    }

    const [result] = await pool.execute(
      'INSERT INTO documents (original_name, file_path, file_size, document_type, status, department_id, uploaded_by, version_number, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())',
      [
        req.file.originalname,
        req.file.filename,
        req.file.size,
        document_type,
        'active',
        department_id,
        user_id,
        1
      ]
    );

    res.status(201).json({ 
      message: 'Document uploaded successfully',
      document_id: (result as any).insertId
    });
  } catch (error) {
    console.error('Upload error:', error);
    res.status(500).json({ message: 'Upload failed' });
  }
});

// Get documents by department
router.get('/department/:departmentId', authenticateToken, async (req, res) => {
  try {
    const { departmentId } = req.params;
    const user_id = (req as any).user.id;

    const [rows] = await pool.execute(`
      SELECT 
        d.id,
        d.original_name,
        d.file_size,
        d.document_type,
        d.status,
        d.is_checked_out,
        d.version_number,
        d.created_at,
        d.checked_out_by,
        d.checked_out_at,
        u.username as uploaded_by_name,
        cu.username as checked_out_by_name
      FROM documents d
      LEFT JOIN users u ON d.uploaded_by = u.id
      LEFT JOIN users cu ON d.checked_out_by = cu.id
      WHERE d.department_id = ?
      ORDER BY d.created_at DESC
    `, [departmentId]);

    res.json({ documents: rows });
  } catch (error) {
    console.error('Fetch documents error:', error);
    res.status(500).json({ message: 'Failed to fetch documents' });
  }
});

// Download document
router.get('/download/:documentId', authenticateToken, async (req, res) => {
  try {
    const { documentId } = req.params;
    const user_id = (req as any).user.id;

    const [rows] = await pool.execute(
      'SELECT * FROM documents WHERE id = ?',
      [documentId]
    );

    if (!(rows as any[]).length) {
      return res.status(404).json({ message: 'Document not found' });
    }

    const document = (rows as any[])[0];
    const filePath = path.join(__dirname, '../../storage/general', document.file_path);

    if (!fs.existsSync(filePath)) {
      return res.status(404).json({ message: 'File not found on server' });
    }

    res.download(filePath, document.original_name);
  } catch (error) {
    console.error('Download error:', error);
    res.status(500).json({ message: 'Download failed' });
  }
});

// Checkout document
router.post('/:documentId/checkout', authenticateToken, async (req, res) => {
  try {
    const { documentId } = req.params;
    const user_id = (req as any).user.id;

    // Check if document exists and is not already checked out
    const [rows] = await pool.execute(
      'SELECT * FROM documents WHERE id = ?',
      [documentId]
    );

    if (!(rows as any[]).length) {
      return res.status(404).json({ message: 'Document not found' });
    }

    const document = (rows as any[])[0];
    if (document.is_checked_out) {
      return res.status(400).json({ message: 'Document is already checked out' });
    }

    // Update document to checked out
    await pool.execute(
      'UPDATE documents SET is_checked_out = 1, checked_out_by = ?, checked_out_at = NOW() WHERE id = ?',
      [user_id, documentId]
    );

    res.json({ message: 'Document checked out successfully' });
  } catch (error) {
    console.error('Checkout error:', error);
    res.status(500).json({ message: 'Checkout failed' });
  }
});

// Checkin document with revision
router.post('/:documentId/checkin', authenticateToken, upload.single('revised_document'), async (req, res) => {
  try {
    const { documentId } = req.params;
    const { revision_notes, requires_approval } = req.body;
    const user_id = (req as any).user.id;

    // Check if document exists and is checked out by this user
    const [rows] = await pool.execute(
      'SELECT * FROM documents WHERE id = ?',
      [documentId]
    );

    if (!(rows as any[]).length) {
      return res.status(404).json({ message: 'Document not found' });
    }

    const document = (rows as any[])[0];
    if (!document.is_checked_out) {
      return res.status(400).json({ message: 'Document is not checked out' });
    }

    if (document.checked_out_by !== user_id) {
      return res.status(403).json({ message: 'You can only checkin documents you checked out' });
    }

    // If a revised document was uploaded, create a new version
    if (req.file) {
      const newVersionNumber = document.version_number + 1;
      
      // Insert new version
      const [result] = await pool.execute(
        'INSERT INTO documents (original_name, file_path, file_size, document_type, status, department_id, uploaded_by, version_number, parent_document_id, revision_notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())',
        [
          req.file.originalname,
          req.file.filename,
          req.file.size,
          document.document_type,
          requires_approval === 'true' ? 'pending_approval' : 'active',
          document.department_id,
          user_id,
          newVersionNumber,
          documentId,
          revision_notes || null
        ]
      );

      // Update original document to archived
      await pool.execute(
        'UPDATE documents SET status = ? WHERE id = ?',
        ['archived', documentId]
      );

      res.json({ 
        message: 'Document checked in with revision successfully',
        new_document_id: (result as any).insertId,
        version: newVersionNumber
      });
    } else {
      // Simple checkin without revision
      await pool.execute(
        'UPDATE documents SET is_checked_out = 0, checked_out_by = NULL, checked_out_at = NULL WHERE id = ?',
        [documentId]
      );

      res.json({ message: 'Document checked in successfully' });
    }
  } catch (error) {
    console.error('Checkin error:', error);
    res.status(500).json({ message: 'Checkin failed' });
  }
});

// Get document version history
router.get('/:documentId/versions', authenticateToken, async (req, res) => {
  try {
    const { documentId } = req.params;

    const [rows] = await pool.execute(`
      SELECT 
        d.id,
        d.original_name,
        d.file_size,
        d.document_type,
        d.status,
        d.version_number,
        d.revision_notes,
        d.created_at,
        u.username as uploaded_by_name
      FROM documents d
      LEFT JOIN users u ON d.uploaded_by = u.id
      WHERE d.id = ? OR d.parent_document_id = ?
      ORDER BY d.version_number ASC
    `, [documentId, documentId]);

    res.json({ versions: rows });
  } catch (error) {
    console.error('Version history error:', error);
    res.status(500).json({ message: 'Failed to fetch version history' });
  }
});

// Approve document (for managers/admins)
router.post('/:documentId/approve', authenticateToken, async (req, res) => {
  try {
    const { documentId } = req.params;
    const user_id = (req as any).user.id;
    const { approved, approval_notes } = req.body;

    // Check if user has approval permissions (manager or admin)
    const [userRows] = await pool.execute(
      'SELECT role FROM users WHERE id = ?',
      [user_id]
    );

    const user = (userRows as any[])[0];
    if (!user || (user.role !== 'admin' && user.role !== 'manager')) {
      return res.status(403).json({ message: 'Insufficient permissions for approval' });
    }

    // Update document status
    const newStatus = approved === 'true' ? 'active' : 'rejected';
    await pool.execute(
      'UPDATE documents SET status = ?, approved_by = ?, approved_at = NOW(), approval_notes = ? WHERE id = ?',
      [newStatus, user_id, approval_notes || null, documentId]
    );

    res.json({ 
      message: `Document ${approved === 'true' ? 'approved' : 'rejected'} successfully`,
      status: newStatus
    });
  } catch (error) {
    console.error('Approval error:', error);
    res.status(500).json({ message: 'Approval failed' });
  }
});

export default router; 