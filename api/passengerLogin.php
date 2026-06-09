<?php
require_once dirname(__DIR__) . '/includes/session.php';
require_once "db.php";

app_session_start();

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->phone) && !empty($data->password)) {
    $phone = htmlspecialchars(strip_tags($data->phone));

    $stmt = $conn->prepare("SELECT id, password FROM passengers WHERE phone = :phone");
    $stmt->execute([':phone' => $phone]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($data->password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['role'] = 'passenger';
            $_SESSION['passenger_phone'] = $phone;
            $_SESSION['last_activity'] = time();
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "phone" => $phone
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Invalid credentials."]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["error" => "User not found."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Phone and password required."]);
}
