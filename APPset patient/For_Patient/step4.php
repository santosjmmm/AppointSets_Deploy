<?php
session_start();
$conn = new mysqli("localhost","root","","db_appsets");
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// Required session variables
$required = ['patient_id','service','dentist','date','time'];
foreach($required as $key){
    if(!isset($_SESSION[$key])){
        header("Location: patient_dash.php");
        exit();
    }
}

$patient_id = $_SESSION['patient_id'];
$service_name = $_SESSION['service'];
$dentist_name = $_SESSION['dentist'];
$date = $_SESSION['date'];   // YYYY-MM-DD
$time = $_SESSION['time'];   // HH:MM:SS

$error = "";

// Insert appointment if form submitted
if(isset($_POST['confirm'])){

    // Get service_id
    $stmt = $conn->prepare("SELECT service_id FROM tb_service WHERE service_name=?");
    $stmt->bind_param("s", $service_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows === 0){
        $error = "Invalid service selected.";
    } else {
        $service = $result->fetch_assoc();
        $service_id = $service['service_id'];
    }
    $stmt->close();

    // Get dentist_id
    if(empty($error)){
        $stmt = $conn->prepare("SELECT dentist_id FROM tb_dentist WHERE dentist_name=?");
        $stmt->bind_param("s", $dentist_name);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows === 0){
            $error = "Invalid dentist selected.";
        } else {
            $dentist = $result->fetch_assoc();
            $dentist_id = $dentist['dentist_id'];
        }
        $stmt->close();
    }

    // Insert into tb_appointment
    if(empty($error)){
        $stmt = $conn->prepare("INSERT INTO tb_appointment (date, time, patient_id, dentist_id, service_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiii", $date, $time, $patient_id, $dentist_id, $service_id);

        if($stmt->execute()){
            // Clear appointment session data
            unset($_SESSION['service'], $_SESSION['dentist'], $_SESSION['date'], $_SESSION['time']);
            header("Location: success.html");
            exit();
        } else {
            $error = "Failed to save appointment: " . $stmt->error;
        }

        $stmt->close();
    }
}

$mysqlDate = $_SESSION['date']; // YYYY-MM-DD
$mysqlTime = $_SESSION['time']; // HH:MM:SS

$displayDate = date("M d, Y", strtotime($mysqlDate)); // Mar 19, 2026
$displayTime = date("h:i A", strtotime($mysqlTime));   // 10:30 AM
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirm Appointment</title>
<link rel="stylesheet" href="step4.css">
</head>
<body>

<header>
    <div class="brand">
        <div class="logo-circle"><img src="logo.jpg" alt="Logo"></div>
        <div class="clinic-title">C'Smiles<span>Dental Center</span></div>
    </div>
    <nav><a href="#">Home</a><a href="#">Services</a><a href="#">Contact</a><a href="login.php" class="logout-link">Log out</a></nav>
</header>

<div class="page-wrapper">
    <aside>
        <div class="user-section">
            <div class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
            <div class="avatar-circle">👤</div>
        </div>
        <a href="step1.php" class="nav-item active"><span>💼</span> Book Appointment</a>
        <a href="appointments.php" class="nav-item"><span>📅</span> Appointments</a>
        <a href="records.php" class="nav-item"><span>📂</span> Records</a>
        <a href="rewards.php" class="nav-item"><span>⭐</span> Reward Points</a>
    </aside>

    <div class="main-content-area">
        <main>
            <div class="stepper-head">
                <span class="active">Select Service</span>
                <span class="active">Set date and time</span>
                <span class="active">Confirm your details</span>
                <span class="active">Confirm appointment</span>
            </div>

            <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>

            <div class="summary-container">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                <p><strong>Service:</strong> <?php echo htmlspecialchars($_SESSION['service']); ?></p>
                <p><strong>Dentist:</strong> <?php echo htmlspecialchars($_SESSION['dentist']); ?></p>
                <p><strong>Date & Time:</strong> <?php echo $displayDate . " - " . htmlspecialchars($displayTime); ?></p>
            </div>

        <div class="footer-action">

            <a href="step3.php" class="prev-btn">Prev</a>
            <form method="POST">
                <button type="submit" name="confirm" class="confirm-btn">Book Now</button>
            </form>

            
        </div>
        </main>
    </div>
</div>

</body>
</html>