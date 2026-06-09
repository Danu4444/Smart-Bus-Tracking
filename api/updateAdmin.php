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
$new_username = isset($data['username']) ? trim($data['username']) : '';
$new_password = isset($data['password']) ? trim($data['password']) : '';

if (empty($new_username) && empty($new_password)) {
    echo json_encode(["error" => "Nothing to update"]);
    exit;
}

try {
    $admin_id = $_SESSION['admin_id'];
    
    if (!empty($new_username) && !empty($new_password)) {
        $stmt = $conn->prepare("UPDATE admins SET username = :u, password = :p WHERE id = :id");
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt->bindParam(":u", $new_username);
        $stmt->bindParam(":p", $hash);
        $stmt->bindParam(":id", $admin_id);
    } else if (!empty($new_username)) {
        $stmt = $conn->prepare("UPDATE admins SET username = :u WHERE id = :id");
        $stmt->bindParam(":u", $new_username);
        $stmt->bindParam(":id", $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE admins SET password = :p WHERE id = :id");
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt->bindParam(":p", $hash);
        $stmt->bindParam(":id", $admin_id);
    }
    
    $stmt->execute();
    echo json_encode(["success" => true, "message" => "Profile updated successfully"]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>
