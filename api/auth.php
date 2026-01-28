<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

// FITUR LOGIN DAN REGISTER DINONAKTIFKAN - Database tidak digunakan
// Gunakan loginout/device-login.php untuk login dengan Device ID
// Gunakan loginout/device-logout.php untuk logout

http_response_code(403);
echo json_encode([
    'ok' => false, 
    'error' => 'Fitur autentikasi telah dinonaktifkan. Gunakan device login endpoint.',
    'note' => 'POST ke /loginout/device-login.php dengan body: {"device_id": "NEO-001"}'
]);
exit;
