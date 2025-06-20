import pool from '../db';

export type Document = {
  id: number;
  original_name: string;
  current_name: string;
  file_path: string;
  file_size: number;
  mime_type: string;
  document_type: 'master' | 'working';
  department_id: number | null;
  uploaded_by: number;
  version_number: number;
  parent_document_id: number | null;
  is_checked_out: boolean;
  checked_out_by: number | null;
  checked_out_at: Date | null;
  status: 'active' | 'pending_approval' | 'archived';
  created_at: Date;
  updated_at: Date;
};

export async function createDocument(document: Omit<Document, 'id' | 'created_at' | 'updated_at'>): Promise<Document> {
  const [result] = await pool.execute(
    `INSERT INTO documents (original_name, current_name, file_path, file_size, mime_type, document_type, department_id, uploaded_by, version_number, parent_document_id, is_checked_out, checked_out_by, checked_out_at, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [
      document.original_name,
      document.current_name,
      document.file_path,
      document.file_size,
      document.mime_type,
      document.document_type,
      document.department_id,
      document.uploaded_by,
      document.version_number,
      document.parent_document_id,
      document.is_checked_out,
      document.checked_out_by,
      document.checked_out_at,
      document.status
    ]
  );
  
  const [rows] = await pool.execute('SELECT * FROM documents WHERE id = ?', [(result as any).insertId]);
  return (rows as any[])[0];
}

export async function getDocumentsByDepartment(departmentId: number): Promise<Document[]> {
  const [rows] = await pool.execute(
    'SELECT * FROM documents WHERE department_id = ? ORDER BY created_at DESC',
    [departmentId]
  );
  return rows as Document[];
}

export async function getDocumentById(id: number): Promise<Document | null> {
  const [rows] = await pool.execute('SELECT * FROM documents WHERE id = ?', [id]);
  return (rows as any[])[0] || null;
}

export async function checkoutDocument(documentId: number, userId: number): Promise<boolean> {
  const [result] = await pool.execute(
    'UPDATE documents SET is_checked_out = TRUE, checked_out_by = ?, checked_out_at = NOW() WHERE id = ? AND is_checked_out = FALSE',
    [userId, documentId]
  );
  return (result as any).affectedRows > 0;
}

export async function checkinDocument(documentId: number): Promise<boolean> {
  const [result] = await pool.execute(
    'UPDATE documents SET is_checked_out = FALSE, checked_out_by = NULL, checked_out_at = NULL WHERE id = ?',
    [documentId]
  );
  return (result as any).affectedRows > 0;
} 