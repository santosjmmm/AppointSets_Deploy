<?php
include 'db.php';

// FIXED: Removed "WHERE status = 'Available'" and explicitly selected the 'status' column
$sql = "SELECT service_id, service_name, description, price, service_image, status FROM tb_service";
$result = $conn->query($sql);

$services = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

echo json_encode($services);
$conn->close();
?>
