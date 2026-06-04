<?php
include 'db.php'

$patient_id = isset($_GET['patient_id']) ? (int)$_GET['patient_id'] : 0;

if ($patient_id > 0) {
    // Query to get only COMPLETED appointments
    $sql = "SELECT a.date, a.time, a.notes, d.dentist_name, s.service_name 
            FROM tb_appointment a
            JOIN tb_dentist d ON a.dentist_id = d.dentist_id
            JOIN tb_service s ON a.service_id = s.service_id
            WHERE a.patient_id = ? AND a.status = 'Completed'
            ORDER BY a.date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    echo json_encode($records);
} else {
    echo json_encode(["error" => "Invalid Patient ID"]);
}

$conn->close();
?>
