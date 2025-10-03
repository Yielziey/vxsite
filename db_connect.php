<?php
// filename: db_connect.php (The Only Connection File You Need)

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_port = getenv('DB_PORT') ?: '3306'; // Idinagdag ang port para mas sigurado
$db_name = getenv('DB_NAME') ?: 'vxsite_db';
$db_user = getenv('DB_USERNAME') ?: 'root'; // TANDAAN: DB_USERNAME ang key na ginagamit ng Wasmer
$db_pass = getenv('DB_PASSWORD') ?: '';

// Data Source Name (DSN)
$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

// Options para sa PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Gumawa ng bagong PDO instance
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // I-handle ang connection error
    error_log("PDO Connection Error: " . $e->getMessage());
    die("Database connection failed. Please check your configuration.");
}
?>