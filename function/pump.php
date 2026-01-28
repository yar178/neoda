<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../mqtt/config.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

header("Content-Type: application/json; charset=UTF-8");

// Load configuration from config.php
$config = require __DIR__ . '/../mqtt/config.php';

$cfg = [
    "host" => $config['host'],
    "port" => $config['port'],
    "base" => $config['base_topic'],
];

$input = json_decode(file_get_contents("php://input"), true);
$state = isset($input["pump"]) ? (int)$input["pump"] : 0;
$state = ($state === 1) ? 1 : 0;

// ambil device id
$data = json_decode(file_get_contents(__DIR__ . '/../data.json'), true);
$deviceId = $data['device_id'] ?? null;

if (!$deviceId) {
    echo json_encode(["ok"=>false,"error"=>"Device ID not found"]);
    exit;
}

$topic = $cfg["base"] . "/" . $deviceId . "/control/pump";


$clientId = "neoda-pump-" . rand(1000,9999);
$mqtt = new MqttClient($cfg["host"], $cfg["port"], $clientId);

$settings = (new ConnectionSettings)->setKeepAliveInterval(60);

try {
    $mqtt->connect($settings, true);
    $mqtt->publish($topic, (string)$state, 1);
    $mqtt->disconnect();

    http_response_code(200);
    echo json_encode(["ok" => true, "pump" => $state, "message" => "Pump state sent to broker"]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log("Pump error: " . $e->getMessage());
    echo json_encode(["ok" => false, "error" => $e->getMessage(), "details" => "Failed to connect to MQTT broker"]);
}
