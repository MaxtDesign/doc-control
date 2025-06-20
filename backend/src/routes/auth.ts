import express from 'express';
import bcrypt from 'bcrypt';
import jwt from 'jsonwebtoken';
import { findUserByUsername, createUser } from '../models/user';

const router = express.Router();

router.post('/login', async (req, res) => {
  const { username, password } = req.body;
  const user = await findUserByUsername(username);
  if (!user || !user.is_active) {
    return res.status(401).json({ message: 'Invalid credentials' });
  }
  const valid = await bcrypt.compare(password, user.password_hash);
  if (!valid) {
    return res.status(401).json({ message: 'Invalid credentials' });
  }
  const token = jwt.sign(
    { id: user.id, username: user.username, role: user.role },
    process.env.JWT_SECRET as string,
    { expiresIn: process.env.JWT_EXPIRES_IN || '24h' }
  );
  res.json({ token, user: { id: user.id, username: user.username, role: user.role } });
});

router.post('/register', async (req, res) => {
  const { username, email, password, first_name, last_name, role } = req.body;
  const password_hash = await bcrypt.hash(password, 10);
  try {
    const user = await createUser({
      username,
      email,
      password_hash,
      first_name,
      last_name,
      role,
      is_active: true,
    });
    res.status(201).json({ id: user.id, username: user.username, email: user.email });
  } catch (err) {
    res.status(400).json({ message: 'User creation failed', error: err });
  }
});

export default router; 