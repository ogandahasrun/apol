<?php
/**
 * Koneksi Database SIMRS Khanza (PHP Native)
 * Mendukung $koneksi (mysqli) dan $pdo (PDO)
 */

require_once __DIR__ . '/config.php';

// 1. Koneksi MySQLi
$koneksi = null;
try {
    $koneksi = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int)DB_PORT);
    if ($koneksi->connect_errno) {
        $koneksi = null; // Gagal koneksi
    } else {
        $koneksi->set_charset('utf8mb4');
        $koneksi->query("SET time_zone = '+07:00'");
    }
} catch (Throwable $e) {
    // Tangkap mysqli_sql_exception atau error lainnya
    $koneksi = null;
}

// 2. Koneksi PDO
$pdo = null;
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5, // Set timeout agar tidak hang terlalu lama
    ]);
    $pdo->exec("SET time_zone = '+07:00'");
} catch (PDOException $e) {
    $pdo = null; // Gagal koneksi, biarkan null
}