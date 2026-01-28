<?php
header('Content-Type: application/json; charset=utf-8');

$file = __DIR__ . '/../data.json';

// kalau file belum ada (MQTT belum ngirim data)
if (!file_exists($file)) {
    echo json_encode([
        "ok" => false,
        "message" => "Belum ada data MQTT",
        "level" => 0,
        "ph" => 7.0,
        "turbidity" => 0,
        "pump" => 0,
        "ts" => time()
    ]);
    exit;
}

// ambil isi file
$data = file_get_contents($file);

// kalau file kosong
if ($data === false || trim($data) === '') {
    echo json_encode([
        "ok" => false,
        "message" => "data.json kosong",
        "level" => 0,
        "ph" => 7.0,
        "turbidity" => 0,
        "pump" => 0,
        "ts" => time()
    ]);
    exit;
}

// pastikan JSON valid
$decoded = json_decode($data, true);
if (!is_array($decoded)) {
    echo json_encode([
        "ok" => false,
        "message" => "data.json bukan JSON valid",
        "raw" => $data
    ]);
    exit;
}

$decoded["ok"] = true;
echo json_encode($decoded);
