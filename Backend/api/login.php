<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configure CORS handshakes safely for Vercel distributions
header("Access-Control-Allow-Origin: https://appoint-sets-deploy.vercel.app");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Map the local absolute execution directory to safely locate db.php
if (file_exists(__DIR__ . '/db.php')) {
    include_once __DIR__ . '/db.php';
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database link mapping error. db.php was not found.",
        "debug_execution_path" => __DIR__
    ]);
    exit();
}

// Processing code continues below...
// (Make sure to remove any closing '?>' tag at the very end of your file!)

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

// --- 1. Check Admin Table ---
$stmt = $conn->prepare("SELECT admin_id, name, password FROM tb_admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($user = $res->fetch_assoc()) {
    if (password_verify($password, $user['password']) || $password === $user['password']) {
        echo json_encode(["success" => true, "role" => "admin", "name" => $user['name'], "admin_id" => $user['admin_id']]);
        exit;
    }
}

// --- 2. Check Staff Table ---
$stmt = $conn->prepare("SELECT staff_id, name, password FROM tb_staff WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($user = $res->fetch_assoc()) {
    if (password_verify($password, $user['password']) || $password === $user['password']) {
        echo json_encode(["success" => true, "role" => "staff", "user_type" => "staff", "name" => $user['name'], "staff_id" => $user['staff_id']]);
        exit;
    }
}

// --- 3. Check Dentist Table ---
$stmt = $conn->prepare("SELECT dentist_id, dentist_name, password FROM tb_dentist WHERE email = ?");
if ($stmt) {
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($user = $res->fetch_assoc()) {
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            echo json_encode([
                "success" => true, 
                "role" => "staff", 
                "user_type" => "dentist", 
                "name" => $user['dentist_name'], 
                "dentist_id" => $user['dentist_id']
            ]);
            exit;
        }
    }
}

// --- 4. Check Patient Table ---
$stmt = $conn->prepare("SELECT patient_id, name, password FROM tb_patient WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();
if ($user = $res->fetch_assoc()) {
    if (password_verify($password, $user['password']) || $password === $user['password']) {
        echo json_encode([
            "success" => true, 
            "role" => "patient", 
            "name" => $user['name'],
            "patient_id" => $user['patient_id']
        ]);
        exit;
    }
}

echo json_encode(["success" => false, "message" => "Invalid email or password."]);
?>
