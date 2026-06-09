<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['driver']);

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$username = isset($_GET['username']) ? trim($_GET['username']) : '';
require_driver_username_matches($username);

try {
    $stmt = $conn->prepare("SELECT bus_id, is_active FROM drivers WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$driver) {
        http_response_code(404);
        echo json_encode(["error" => "Driver not found"]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "bus_id" => $driver['bus_id'],
        "is_active" => intval($driver['is_active']) === 1
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
