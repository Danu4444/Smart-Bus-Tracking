<?php
require_once "db.php";

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
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
    $password = $data->password;

    $check = $conn->prepare("SELECT id FROM passengers WHERE phone = :phone");
    $check->execute([':phone' => $phone]);
    if ($check->rowCount() > 0) {
        http_response_code(409);
        echo json_encode(["error" => "Phone number already registered."]);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $query = "INSERT INTO passengers (phone, password) VALUES (:phone, :hash)";
    $stmt = $conn->prepare($query);

    if ($stmt->execute([':phone' => $phone, ':hash' => $hash])) {
        echo json_encode(["success" => true, "message" => "Registration successful!"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Internal server error."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Phone and password required."]);
}
?>
