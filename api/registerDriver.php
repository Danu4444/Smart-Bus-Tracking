<?php
require_once "db.php";
require_once dirname(__DIR__) . '/includes/session.php';

api_send_cors_headers();
api_handle_options_preflight();
require_role_json(['admin']);

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$username = isset($data['username']) ? trim($data['username']) : '';
$password = isset($data['password']) ? trim($data['password']) : '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["error" => "Username and password are required"]);
    exit;
}

try {
    // Check if username already exists
    $check = $conn->prepare("SELECT id FROM drivers WHERE username = :usr");
    $check->execute([':usr' => $username]);
    if ($check->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(["error" => "Driver username already exists"]);
        exit;
    }

    // Insert driver without a fixed bus assignment. Drivers choose a bus when starting a trip.
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    try {
        $stmt = $conn->prepare("INSERT INTO drivers (username, password, bus_id) VALUES (:username, :password, '')");
        $stmt->execute([
            ':username' => $username,
            ':password' => $passwordHash
        ]);
    } catch (PDOException $insertError) {
        // Backward-compatible fallback for older databases where bus_id is nullable or absent.
        $stmt = $conn->prepare("INSERT INTO drivers (username, password) VALUES (:username, :password)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $passwordHash
        ]);
    }

    echo json_encode(["success" => true, "message" => "Driver successfully registered"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
