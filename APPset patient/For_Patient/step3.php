<?php
session_start();

$conn = new mysqli("localhost","root","","db_appsets");
if($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Check session exists
if (!isset($_SESSION['patient_id'], $_SESSION['date'], $_SESSION['time'], $_SESSION['service'], $_SESSION['dentist'])) {
    header("Location: step1.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];

// Fetch patient info
$stmt = $conn->prepare("SELECT name, age, address, contact_num, email FROM tb_patient WHERE patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();

$patient_id = $_SESSION['patient_id'];

$stmt = $conn->prepare("SELECT name FROM tb_patient WHERE patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Store again (optional but fine)
$_SESSION['name'] = $patient['name'];
$_SESSION['age'] = $patient['age'];
$_SESSION['address'] = $patient['address'];
$_SESSION['contact_num'] = $patient['contact_num'];
$_SESSION['email'] = $patient['email'];

// ✅ Safe formatting
$date = $_SESSION['date'];
$time = $_SESSION['time'];

$displayDate = date("M d, Y", strtotime($date));

// If your time is already like "9:30 AM", no need strtotime
$displayTime = $time;

// Step 1 data
$service = $_SESSION['service'];
$dentist = $_SESSION['dentist'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C'Smiles - Confirm Your Details</title>
    <link rel="stylesheet" href="step3.css">
</head>
<body>

<header>
    <div class="brand">
        <div class="logo-circle"><img src="logo.jpg" alt="Logo"></div>
        <div class="clinic-title">C'Smiles<span>Dental Center</span></div>
    </div>
    <nav>
       <nav><a href="#">Home</a><a href="#">Services</a><a href="#">Contact</a><a href="login.php" class="logout-link">Log out</a></nav>
    </nav>
</header>

<div class="page-wrapper">
    <aside>
      
        <div class="user-section">
            <div class="user-name">
    <?php echo isset($user['name']) ? htmlspecialchars($user['name']) : 'Guest'; ?>
</div>
            <div class="avatar-circle">👤</div>
        </div>
        <a href="step1.html" class="nav-item active"><span>💼</span> Book Appointment</a>
        <a href="appointments.html" class="nav-item"><span>📅</span> Appointments</a>
        <a href="records.html" class="nav-item"><span>📂</span> Records</a>
        <a href="rewards.html" class="nav-item"><span>⭐</span> Reward Points</a>
    </aside>

    <div class="main-content-area">
        <main>
            <div class="stepper-head">
                <span class="active">Select Service</span>
                <span class="active">Set date and time</span>
                <span class="active">Confirm your details</span>
                <span>Confirm appointment</span>
            </div>

           <div class="form-container">

                <div class="input-group">
                <label>Name:</label>
                <input type="text" class="pink-input" value="<?php echo $patient['name']; ?>" readonly>
                </div>

                <div class="input-group">
                <label>Age:</label>
                <input type="text" class="pink-input" value="<?php echo $patient['age']; ?>" readonly>
                </div>

                <div class="input-group">
                <label>Address:</label>
                <input type="text" class="pink-input" value="<?php echo $patient['address']; ?>" readonly>
                </div>

                <div class="input-group">
                <label>Contact No:</label>
                <input type="text" class="pink-input" value="<?php echo $patient['contact_num']; ?>" readonly>
                </div>

                <div class="input-group">
                <label>Email:</label>
                <input type="text" class="pink-input" value="<?php echo $patient['email']; ?>" readonly>
                </div>

                <div class="input-group">
                <label>Service:</label>
                <input type="text" class="pink-input" value="<?= htmlspecialchars($service) ?>" readonly>
                </div>

                <div class="input-group">
                <label>Dentist:</label>
                <input type="text" class="pink-input" value="<?= htmlspecialchars($dentist) ?>" readonly>
                </div>
                
                <div class="input-group">
                    <label>Date:</label>
                    <input type="text" class="pink-input" value="<?= htmlspecialchars($displayDate) ?>" readonly>
                </div>

                <div class="input-group">
                    <label>Time:</label>
                    <input type="text" class="pink-input" value="<?= htmlspecialchars($displayTime) ?>" readonly>
                </div>

                </div>

            <div class="button-row">
                <a href="step2.php" class="nav-btn">Prev</a>
                <a href="step4.php" class="nav-btn">Next</a>
                </div>
        </main>
    </div>
</div>

</body>
</html>