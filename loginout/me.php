<?php
header("Content-Type: application/json");
session_start();

if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    http_response_code(401);
    echo json_encode(["ok" => false, "error" => "Belum login"]);
    exit;
}

echo json_encode([
    "ok" => true,
    "user" => [
        "id" => $_SESSION["user_id"],
        "email" => $_SESSION["email"]
    ]
]);
