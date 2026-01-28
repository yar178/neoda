<?php
header("Content-Type: application/json");
session_start();

// Hapus session
$_SESSION = [];
session_destroy();

// Hapus device_id dari data.json
$dataFile = __DIR__ . "/../data.json";
if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
    if (is_array($data)) {
        unset($data["device_id"]);
        unset($data["logged_in"]);
        unset($data["login_time"]);
        file_put_contents($dataFile, json_encode($data, JSON_UNESCAPED_SLASHES));
    }
}

echo json_encode([
    "ok" => true,
    "message" => "Logout berhasil"
]);
