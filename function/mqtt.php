<!-- topic : neoda/mqtt_9F3aK2Lm7Qx1Vb8NwE4rT6yU0pHj5GcD/ -->

<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

/**CONFIG MQTT*/
function mqtt_config(): array {
    return [
        "host" => "broker.hivemq.com",
        "port" => 1883,
        "base" => "neoda/mqtt_9F3aK2Lm7Qx1Vb8NwE4rT6yU0pHj5GcD",
    ];
}


function mqtt_connect(string $clientIdPrefix = "neoda-php"): MqttClient {
    $cfg = mqtt_config();

    $clientId = $clientIdPrefix . "-" . rand(1000, 9999);

    $mqtt = new MqttClient($cfg["host"], $cfg["port"], $clientId);

    $settings = (new ConnectionSettings)
        ->setKeepAliveInterval(60);

    $mqtt->connect($settings, true);
    return $mqtt;
}

/**
 * Helper bikin topic full
 */
function mqtt_topic(string $suffix): string {
    $cfg = mqtt_config();
    return rtrim($cfg["base"], "/") . "/" . ltrim($suffix, "/");
}

/**
 * Publish pump 
 */
function mqtt_publish_pump(int $state): bool {
    $state = ($state === 1) ? 1 : 0;

    $mqtt = mqtt_connect("neoda-pump");
    $topic = mqtt_topic("control/pump");

    $mqtt->publish($topic, (string)$state, 1);
    $mqtt->disconnect();

    return true;
}
