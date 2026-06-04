<?php
// 1. Allow your Vercel frontend to access this API
header("Access-Control-Allow-Origin: https://appoint-sets-deploy.vercel.app");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS requests from the browser gracefully
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'db.php';

// If db.php connection somehow failed, don't keep executing
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection unavailable"]);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Add fallback checks so empty data doesn't trigger a PHP crash
$patient_id   = isset($data['patient_id']) ? intval($data['patient_id']) : null;
$service_name = $data['service'] ?? null;
$dentist_name = $data['dentist'] ?? null;
$date         = $data['date'] ?? null;
$time_raw     = $data['time'] ?? null;

// Validate required parameters right away
if (!$patient_id || !$service_name || !$dentist_name || !$date || !$time_raw) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required appointment fields."
    ]);
    exit;
}

/* Fix time format safely */
$time = date("H:i:s", strtotime($time_raw));
$is_redeemed = $data['is_redeemed'] ?? false;
$redeemed_points = isset($data['redeemed_points']) ? intval($data['redeemed_points']) : 0;

// 1. Get service_id
$stmt = $conn->prepare("SELECT service_id FROM tb_service WHERE service_name=?");
$stmt->bind_param("s", $service_name);
$stmt->execute();

$s_res = $stmt->get_result()->fetch_assoc();
$service_id = $s_res['service_id'] ?? null;

$stmt->close();

// 2. Get dentist_id
$stmt = $conn->prepare("SELECT dentist_id FROM tb_dentist WHERE dentist_name=?");
$stmt->bind_param("s", $dentist_name);
$stmt->execute();

$d_res = $stmt->get_result()->fetch_assoc();
$dentist_id = $d_res['dentist_id'] ?? null;

$stmt->close();

if (!$service_id || !$dentist_id) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Service or Dentist"
    ]);
    exit;
}

// 3. Insert Appointment
$stmt = $conn->prepare("
    INSERT INTO tb_appointment 
    (date, time, patient_id, dentist_id, service_id) 
    VALUES (?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssiii",
    $date,
    $time,
    $patient_id,
    $dentist_id,
    $service_id
);

if ($stmt->execute()) {

    // Deduct points if reward was redeemed
    if ($is_redeemed && $redeemed_points > 0) {

        $deductStmt = $conn->prepare("
            UPDATE tb_patient
            SET points = points - ?
            WHERE patient_id = ?
        ");

        $deductStmt->bind_param(
            "ii",
            $redeemed_points,
            $patient_id
        );

        $deductStmt->execute();
        $deductStmt->close();
    }

    echo json_encode([
        "success" => true,
        "stored_time" => $time,
        "is_redeemed" => $is_redeemed,
        "redeemed_points" => $redeemed_points
    ]);

} else {
    echo json_encode([
        "success" => false,
        "message" => $stmt->error
    ]);
}
$stmt->close();
$conn->close();
?>