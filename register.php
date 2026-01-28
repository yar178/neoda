<?php
header("Content-Type: application/json");

// FITUR REGISTER DINONAKTIFKAN - Database tidak digunakan
// Gunakan device login untuk akses

http_response_code(403);
echo json_encode([
    "ok" => false, 
    "error" => "Fitur register telah dinonaktifkan",
    "note" => "Gunakan device login dengan Device ID yang telah tersimpan pada perangkat"
]);
exit;
