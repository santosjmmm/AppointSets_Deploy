<?php
include 'db.php';

// Get JSON data from React
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['appointment_id']) && isset($data['patient_id'])) {
    $apt_id = $data['appointment_id'];
    $patient_id = $data['patient_id'];
    $reason = $data['reason'] ?? '';

    // Update status to Cancelled (Logic borrowed from your appointments.php)
    $stmt = $conn->prepare("UPDATE tb_appointment SET status = 'Cancelled', notes = CONCAT(IFNULL(notes,''), ' | Cancel Reason: ', ?) WHERE appointment_id = ? AND patient_id = ?");
    $stmt->bind_param("sii", $reason, $apt_id, $patient_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
}
?>
