<?php
require_once dirname(__DIR__) . '/includes/session.php';
require_once "db.php";

app_session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");

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
    echo json_encode(["error" => "Username and password required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, username, password, is_active, bus_id FROM drivers WHERE username = :username");
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $driver = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (intval($driver['is_active']) !== 1) {
            http_response_code(403);
            echo json_encode(["error" => "Driver disabled by admin"]);
            exit;
        }

        // Match BCRYPT hash or MD5 fallback
        if (password_verify($password, $driver['password']) || md5($password) === $driver['password']) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $driver['id'];
            $_SESSION['role'] = 'driver';
            $_SESSION['driver_auth'] = true;
            $_SESSION['driver_id'] = (int) $driver['id'];
            $_SESSION['driver_username'] = $driver['username'];
            $_SESSION['driver_assigned_bus'] = $driver['bus_id'] ?? '';
            $_SESSION['last_activity'] = time();
            echo json_encode([
                "success" => true, 
                "message" => "Login successful",
                "username" => $driver['username'],
                "assigned_bus_id" => $driver['bus_id']
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid username or password"]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["error" => "Invalid username or password"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
