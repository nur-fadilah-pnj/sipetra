<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sipentra');
define('DB_USER', 'root');
define('DB_PASS', '');           
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL', '/sipentra');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . '/uploads/');

// ====== KONEKSI PDO ======
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Error sebagai exception
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Hasil query = array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                     // Prepared statement asli
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("
    <div style='font-family:sans-serif;padding:2rem;background:#fee2e2;color:#991b1b;border-radius:8px;margin:2rem'>
        <h2>❌ Koneksi Database Gagal</h2>
        <p><b>Pesan:</b> " . htmlspecialchars($e->getMessage()) . "</p>
        <p>Pastikan MySQL sudah berjalan dan database <b>sipentra</b> sudah dibuat.</p>
    </div>");
}

// ====== SESSION ======
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}