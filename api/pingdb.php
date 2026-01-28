<?php
header("Content-Type: application/json");

$databaseUrl = getenv("DATABASE_URL");
if (!$databaseUrl) {
  http_response_code(500);
  echo json_encode(["ok"=>false,"error"=>"DATABASE_URL not set"]);
  exit;
}

$db = parse_url($databaseUrl);

$dsn = "pgsql:host={$db['host']};port={$db['port']};dbname=" . ltrim($db["path"], "/");

try {
  $pdo = new PDO($dsn, $db["user"], $db["pass"], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);

  $time = $pdo->query("SELECT NOW()")->fetchColumn();
  echo json_encode(["ok"=>true,"db_time"=>$time]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["ok"=>false,"error"=>$e->getMessage()]);
}
