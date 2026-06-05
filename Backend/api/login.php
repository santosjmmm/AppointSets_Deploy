<?php
include 'db.php';

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
