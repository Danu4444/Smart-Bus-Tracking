<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_role_json(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data_raw = file_get_contents("php://input");
$data_pre = json_decode($data_raw, true);
$data = $data_pre;
$driver_id = isset($data['driver_id']) ? intval($data['driver_id']) : 0;
$action = isset($data['action']) ? trim($data['action']) : '';

if ($driver_id <= 0 || $action === '') {
    http_response_code(400);
    echo json_encode(["error" => "driver_id and action required"]);
    exit;
}

try {
    if ($action === 'disable') {
        $stmt = $conn->prepare("UPDATE drivers SET is_active = 0 WHERE id = :id");
        $stmt->execute([':id' => $driver_id]);
        echo json_encode(["success" => true, "message" => "Driver disabled"]);
        exit;
    }

    if ($action === 'enable') {
        $stmt = $conn->prepare("UPDATE drivers SET is_active = 1 WHERE id = :id");
        $stmt->execute([':id' => $driver_id]);
        echo json_encode(["success" => true, "message" => "Driver enabled"]);
        exit;
    }

    if ($action === 'reset_password') {
        $new_password = isset($data['new_password']) ? trim($data['new_password']) : '';
        if ($new_password === '') {
            http_response_code(400);
            echo json_encode(["error" => "new_password required"]);
            exit;
        }
        $stmt = $conn->prepare("UPDATE drivers SET password = :p WHERE id = :id");
        $stmt->execute([
            ':p' => password_hash($new_password, PASSWORD_BCRYPT),
            ':id' => $driver_id
        ]);
        echo json_encode(["success" => true, "message" => "Password reset"]);
        exit;
    }

    http_response_code(400);
    echo json_encode(["error" => "Unsupported action"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
