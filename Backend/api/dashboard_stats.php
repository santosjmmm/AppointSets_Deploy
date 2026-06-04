<?php
include 'db.php';
// 1. Total Appointments Count
$total_query = $conn->query("SELECT COUNT(*) AS total FROM tb_appointment");
$total_appointments = ($total_query) ? $total_query->fetch_assoc()['total'] : 0;

// 2. Completed Appointments Count
$completed_query = $conn->query("SELECT COUNT(*) AS total FROM tb_appointment WHERE status = 'Completed'");
$completed_appointments = ($completed_query) ? $completed_query->fetch_assoc()['total'] : 0;

// 3. Pending/Incomplete Appointments Count
$pending_query = $conn->query("SELECT COUNT(*) AS total FROM tb_appointment WHERE status = 'Incomplete'");
$pending_appointments = ($pending_query) ? $pending_query->fetch_assoc()['total'] : 0;

// 4. Fetch ALL appointments for the client-side log matrix view mapping
$all_sql = "SELECT a.appointment_id, a.date, a.status, p.name AS patient_name 
            FROM tb_appointment a
            JOIN tb_patient p ON a.patient_id = p.patient_id
            ORDER BY a.date DESC, a.time DESC";
$all_result = $conn->query($all_sql);

$appointment_logs = [];
if ($all_result) {
    while ($row = $all_result->fetch_assoc()) {
        $row['formatted_date'] = date("M d, Y", strtotime($row['date']));
        $appointment_logs[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "stats" => [
        "total" => $total_appointments,
        "completed" => $completed_appointments,
        "pending" => $pending_appointments
    ],
    "all_appointments" => $appointment_logs
]);

$conn->close();
?>
