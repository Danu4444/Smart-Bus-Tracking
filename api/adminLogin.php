<?php
require_once dirname(__DIR__) . '/includes/session.php';
require_once "db.php";

app_session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true"); // Needed for sessions over fetch

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
    // Check admin credentials
    $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = :username");
    $stmt->bindParam(":username", $username);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Match BCRYPT hash or MD5 (fallback for default schema)
        if (password_verify($password, $admin['password']) || md5($password) === $admin['password']) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $admin['id'];
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_auth'] = true;
            $_SESSION['admin_id'] = (int) $admin['id'];
            $_SESSION['last_activity'] = time();
            echo json_encode(["success" => true, "message" => "Login successful"]);
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
