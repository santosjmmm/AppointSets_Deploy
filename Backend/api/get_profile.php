<?php
include 'db.php';

if (isset($_GET['patient_id'])) {
    $id = $_GET['patient_id'];
    $stmt = $conn->prepare("SELECT name, age, address, contact_num, email FROM tb_patient WHERE patient_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        echo json_encode($user);
    } else {
        echo json_encode(["error" => "User not found"]);
    }
    $stmt->close();
}
$conn->close();
?>
