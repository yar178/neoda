<?php
/**
 * Test Script untuk Sistem Autentikasi
 * File ini untuk testing, bisa dihapus setelah verifikasi
 */

header("Content-Type: application/json");
session_start();

// Get current test status
$tests = [
    'session_started' => isset($_SESSION),
    'device_id_in_session' => isset($_SESSION['device_id']),
    'data_file_exists' => file_exists(__DIR__ . '/data.json'),
    'check_auth_exists' => file_exists(__DIR__ . '/function/check-auth.php'),
    'device_login_exists' => file_exists(__DIR__ . '/loginout/device-login.php'),
    'device_logout_exists' => file_exists(__DIR__ . '/loginout/device-logout.php'),
];

// Check data.json content if exists
$dataFileStatus = [];
if (file_exists(__DIR__ . '/data.json')) {
    $data = json_decode(file_get_contents(__DIR__ . '/data.json'), true);
    $dataFileStatus = [
        'device_id' => $data['device_id'] ?? null,
        'logged_in' => $data['logged_in'] ?? null,
        'login_time' => $data['login_time'] ?? null,
    ];
}

// Current session info
$sessionInfo = [
    'device_id' => $_SESSION['device_id'] ?? null,
    'logged_in' => $_SESSION['logged_in'] ?? null,
    'login_time' => $_SESSION['login_time'] ?? null,
];

echo json_encode([
    'ok' => true,
    'status' => 'Authentication System Test',
    'file_checks' => $tests,
    'data_json' => $dataFileStatus,
    'session' => $sessionInfo,
    'timestamp' => time(),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

?>
