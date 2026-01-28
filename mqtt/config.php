<?php
return [

    // MQTT Broker
    'host' => 'broker.hivemq.com',
    'port' => 1883,

    // Client ID
    'client_id' => 'php_neoda_' . rand(1000, 9999),

    // Base Topic
    'base_topic' => 'neoda/mqtt_9F3aK2Lm7Qx1Vb8NwE4rT6yU0pHj5GcD',

    // Device ID
    'device_id' => 'NEO-001',

    // Topics
    'topic' => [
        // SENSOR
        'ph'    => 'sensor/ph',
        'ntu'   => 'sensor/ntu',
        'level' => 'sensor/level',

        // CONTROL
        'pump'  => 'control/pump',
    ]
];
