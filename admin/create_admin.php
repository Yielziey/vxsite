<?php
require_once __DIR__ . '/../db_connect.php';

// Credentials you provided
$username = "superadmin";
$password_plain = "";

// Create users table if not exists (id, username unique, password_hash)
$createSQL = "
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(50) DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$pdo->exec($createSQL);

// Check if user already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);
if ($stmt->fetch()) {
    echo "User '$username' already exists. Exiting.\n";
    exit;
}

// Hash the password
$hash = password_hash($password_plain, PASSWORD_DEFAULT);

// Insert user
$ins = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
$ins->execute([$username, $hash, 'superadmin']);

echo "User '$username' created with hashed password. Delete or protect setup_admin.php now.\n";
