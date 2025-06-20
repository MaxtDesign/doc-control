import pool from '../db';

export type User = {
  id: number;
  username: string;
  email: string;
  password_hash: string;
  first_name: string;
  last_name: string;
  role: string;
  is_active: boolean;
  created_at: Date;
  updated_at: Date;
};

export async function findUserByUsername(username: string): Promise<User | null> {
  const [rows] = await pool.execute('SELECT * FROM users WHERE username = ?', [username]);
  return (rows as any[])[0] || null;
}

export async function createUser(user: Omit<User, 'id' | 'created_at' | 'updated_at'> & { password_hash: string }): Promise<User> {
  const [result] = await pool.execute(
    `INSERT INTO users (username, email, password_hash, first_name, last_name, role, is_active)
     VALUES (?, ?, ?, ?, ?, ?, ?)`,
    [user.username, user.email, user.password_hash, user.first_name, user.last_name, user.role, user.is_active]
  );
  const [rows] = await pool.execute('SELECT * FROM users WHERE id = ?', [(result as any).insertId]);
  return (rows as any[])[0];
} 