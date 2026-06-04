<?php
include 'db.php';

// If db.php connection somehow failed, don't keep executing
if (!$conn) {
    echo json_encode(["success" => false, "message" => "Database connection unavailable"]);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

// ✅ FIX: Add fallback checks so empty data doesn't trigger a PHP crash
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
// ... Rest of your SQL code remains exactly the same!
