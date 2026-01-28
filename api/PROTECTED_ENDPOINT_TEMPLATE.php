<?php
/**
 * CONTOH: Protected API Endpoint dengan Authentication
 * Copy & paste pattern ini ke semua endpoint API yang perlu proteksi
 */

header("Content-Type: application/json");
session_start();

// ==========================================
// STEP 1: AUTHENTICATION CHECK
// ==========================================
if (!isset($_SESSION['device_id']) || empty($_SESSION['device_id'])) {
    http_response_code(401);
    echo json_encode([
        "ok" => false,
        "error" => "Anda harus login terlebih dahulu",
        "code" => "UNAUTHORIZED"
    ]);
    exit;
}

// ==========================================
// STEP 2: GET DEVICE ID FROM SESSION
// ==========================================
$device_id = $_SESSION['device_id'];

// ==========================================
// STEP 3: VALIDATE SESSION WITH DATA.JSON
// ==========================================
$dataFile = __DIR__ . '/../data.json';
if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
    
    // Jika device_id di session tidak match dengan data.json, session invalid
    if (!isset($data['device_id']) || $data['device_id'] !== $device_id) {
        http_response_code(401);
        echo json_encode([
            "ok" => false,
            "error" => "Session tidak valid",
            "code" => "INVALID_SESSION"
        ]);
        exit;
    }
    
    // Check jika logged_in flag false
    if (!isset($data['logged_in']) || $data['logged_in'] !== true) {
        http_response_code(401);
        echo json_encode([
            "ok" => false,
            "error" => "User tidak terdaftar sebagai login",
            "code" => "NOT_LOGGED_IN"
        ]);
        exit;
    }
}

// ==========================================
// STEP 4: HANDLE REQUEST (GET, POST, etc)
// ==========================================

// GET Request Example
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Process GET request
    echo json_encode([
        "ok" => true,
        "device_id" => $device_id,
        "message" => "GET request berhasil diproses",
        "data" => null
    ]);
    exit;
}

// POST Request Example
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate input
    if (empty($input)) {
        http_response_code(400);
        echo json_encode([
            "ok" => false,
            "error" => "Request body tidak valid"
        ]);
        exit;
    }
    
    // Process POST request
    echo json_encode([
        "ok" => true,
        "device_id" => $device_id,
        "message" => "POST request berhasil diproses",
        "received" => $input
    ]);
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode([
    "ok" => false,
    "error" => "Method tidak diizinkan"
]);

?>
