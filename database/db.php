<?php
// DATABASE DINONAKTIFKAN - Tidak ada koneksi database
// Fitur login dan register telah dinonaktifkan
// Akses publik tanpa autentikasi

// $pdo adalah null (tidak ada database)
$pdo = null;

$dsn = "pgsql:host=$host;port=$port;dbname=$name;sslmode=$sslMode";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["ok" => false, "error" => "DB connect failed: " . $e->getMessage()]);
    exit;
}
