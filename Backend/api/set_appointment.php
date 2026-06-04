<?php
// ✅ Include your master database and header configuration
include 'db.php';

$response = [
    "success" => false,
    "services" => [],
    "dentists" => [],
    "error" => null
];

try {
    // 1. SERVICES: Get available regular services
    $serviceSql = "SELECT service_name FROM tb_service WHERE status = 'Available'";
    $serviceResult = $conn->query($serviceSql);

    if (!$serviceResult) {
        throw new Exception("Service query failed: " . $conn->error);
    }

    while ($row = $serviceResult->fetch_assoc()) {
        $response["services"][] = $row["service_name"];
    }

    // 2. DENTISTS: Get active dentists
    $dentistSql = "SELECT dentist_name FROM tb_dentist WHERE status = 'Active'";
    $dentistResult = $conn->query($dentistSql);

    if (!$dentistResult) {
        throw new Exception("Dentist query failed: " . $conn->error);
    }

    while ($row = $dentistResult->fetch_assoc()) {
        $response["dentists"][] = $row["dentist_name"];
    }

    $response["success"] = true;

} catch (Exception $e) {
    $response["error"] = $e->getMessage();
}

echo json_encode($response);
exit();
?>