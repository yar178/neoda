<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../mqtt/config.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration from config.php
$config = require __DIR__ . '/../mqtt/config.php';

$host = $config['host'];
$port = $config['port'];
$sessionKey = $config['base_topic']; // Use base_topic instead of hardcoded session key

// Baca device_id dari data.json
$dataFile = __DIR__ . "/../data.json";
$deviceId = null;

if (file_exists($dataFile)) {
    $dataContent = json_decode(file_get_contents($dataFile), true);
    if (is_array($dataContent) && isset($dataContent['device_id'])) {
        $deviceId = $dataContent['device_id'];
    }
}

if (!$deviceId) {
    echo "❌ MQTT LISTENER: Device ID tidak ditemukan di data.json\n";
    echo "Mohon login terlebih dahulu untuk mengatur Device ID\n";
    exit;
}

// Topic dengan struktur: sessionKey/deviceId/sensor dan sessionKey/deviceId/control/pump
$topicSensor = $sessionKey . "/" . $deviceId . "/sensor";
$topicPump   = $sessionKey . "/" . $deviceId . "/control/pump";

$clientId = "neoda-listener-" . rand(1000, 9999);
$mqtt = new MqttClient($host, $port, $clientId);

$settings = (new ConnectionSettings)
    ->setKeepAliveInterval(60);

function readData($file) {
    if (!file_exists($file)) return [];
    $raw = file_get_contents($file);
    if ($raw === false || trim($raw) === '') return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function saveData($file, $newData) {
    $old = [];

    if (file_exists($file)) {
        $old = json_decode(file_get_contents($file), true);
        if (!is_array($old)) $old = [];
    }

    // gabungkan data lama + baru
    $merged = array_merge($old, $newData);

    $merged["ts"] = time();

    $result = file_put_contents(
        $file,
        json_encode($merged, JSON_UNESCAPED_SLASHES)
    );
    
    return $result !== false;
}


echo "=== MQTT LISTENER START ===\n";
echo "Client ID : $clientId\n";
echo "Broker    : $host:$port\n";
echo "Device ID : $deviceId\n";
echo "Sensor    : $topicSensor\n";
echo "Pump      : $topicPump\n";
echo "Data file : $dataFile\n";
echo "==========================\n\n";

$mqtt->connect($settings, true);

// ===================== SENSOR =====================
// ===================== SENSOR =====================
$topicSensor = $sessionKey . "/" . $deviceId . "/sensor/#";

$mqtt->subscribe($topicSensor, function (string $topic, string $message) use ($dataFile) {

    echo "[SENSOR] $topic => $message\n";

    // ambil nama sensor dari topic
    // contoh: neoda/.../sensor/ph → ph
    $parts = explode('/', $topic);
    $sensorName = end($parts);

    // validasi angka
    if (!is_numeric($message)) {
        echo "❌ Payload bukan angka\n";
        return;
    }

    $value = (float)$message;

    $data = readData($dataFile);
    $data[$sensorName] = $value;

    $ok = saveData($dataFile, $data);
    if ($ok) {
        echo "✅ Saved $sensorName = $value\n";
    } else {
        echo "❌ Gagal menyimpan data sensor\n";
    }
});



// ===================== PUMP =====================
$mqtt->subscribe($topicPump, function (string $topic, string $message) use ($dataFile) {
    echo "[PUMP] $message\n";

    $val = trim($message);

    // support JSON {"pump":1}
    $decoded = json_decode($val, true);
    if (is_array($decoded) && isset($decoded["pump"])) {
        $pump = (int) $decoded["pump"];
    } else {
        $pump = (int) $val; // "0" atau "1"
    }

    if ($pump !== 0 && $pump !== 1) {
        echo "❌ Payload pump harus 0/1, dapat: $val\n";
        return;
    }

    $old = readData($dataFile);
    $old["pump"] = $pump;

    $ok = saveData($dataFile, $old);
    if ($ok) echo "✅ Saved pump=$pump -> data.json\n";
    else echo "❌ GAGAL NULIS data.json (pump)\n";
}, 0);

// ===================== RUN LOOP =====================
while (true) {
    try {
        $mqtt->loop(true);
    } catch (Throwable $e) {
        echo "⚠️ MQTT error: " . $e->getMessage() . "\n";
        sleep(2);
        $mqtt->connect($settings, true);
    }

    usleep(200000); // 0.2 detik
}

