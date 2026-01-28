<?php
/**
 * Authentication Check - Middleware untuk memastikan user sudah login
 * Jika belum login, redirect ke halaman login
 */

session_start();

// Daftar halaman yang BOLEH diakses tanpa login
$allowedPages = [
    '/index.php',
    '/loginout/device-login.php',
    '/api/auth.php',
    '/register.php'
];

// Daftar ekstensi file yang BOLEH diakses tanpa login
$allowedExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'json'];

$currentPage = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$fileExtension = pathinfo($currentPage, PATHINFO_EXTENSION);

// Jika mengakses file statis (css, js, dll), bypass authentication
if (in_array(strtolower($fileExtension), $allowedExtensions)) {
    return;
}

// Cek apakah halaman termasuk allowed pages
$isAllowedPage = false;
foreach ($allowedPages as $page) {
    if (strpos($currentPage, $page) !== false) {
        $isAllowedPage = true;
        break;
    }
}

// Cek session login
$isLoggedIn = isset($_SESSION['device_id']) && !empty($_SESSION['device_id']);
$dataFile = __DIR__ . '/../data.json';
$dataLoggedIn = false;

if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
    $dataLoggedIn = isset($data['logged_in']) && $data['logged_in'] === true;
}

// Jika belum login dan bukan halaman yang diizinkan, redirect ke login
if (!$isLoggedIn && !$isAllowedPage) {
    // Jika AJAX request, return JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'error' => 'Anda harus login terlebih dahulu'
        ]);
        exit;
    }
    
    // Redirect ke halaman login
    header('Location: /index.php?login=required');
    exit;
}

// Jika session tidak sesuai dengan data.json, singkirkan user
if ($isLoggedIn && !$dataLoggedIn) {
    session_destroy();
    header('Location: /index.php?login=required');
    exit;
}
