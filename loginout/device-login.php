<?php
header("Content-Type: application/json");
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$device_id = trim($data["device_id"] ?? "");

if (!$device_id) {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "Device ID tidak boleh kosong"]);
    exit;
}

// Simpan device_id ke session
$_SESSION["device_id"] = $device_id;
$_SESSION["logged_in"] = true;
$_SESSION["login_time"] = time();

// Simpan juga ke data.json untuk digunakan mqtt_listener.php
$dataFile = __DIR__ . "/../data.json";
$data = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];
if (!is_array($data)) $data = [];

$data["device_id"] = $device_id;
$data["logged_in"] = true;
$data["login_time"] = time();

file_put_contents($dataFile, json_encode($data, JSON_UNESCAPED_SLASHES));

echo json_encode([
    "ok" => true,
    "message" => "Login berhasil dengan Device ID: " . $device_id,
    "device_id" => $device_id
]);
