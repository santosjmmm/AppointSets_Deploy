<?php
session_start();

$conn = new mysqli("localhost", "root", "", "db_appsets");

// Fetch user name
$patient_id = $_SESSION['patient_id'];
$stmt = $conn->prepare("SELECT name FROM tb_patient WHERE patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ✅ HANDLE REBOOK (coming from records.php)
if (isset($_GET['rebook'])) {
    $_SESSION['service'] = $_GET['service'];
    $_SESSION['dentist'] = $_GET['dentist'];
}



// ❌ BLOCK if step1 data is missing
if (!isset($_SESSION['service']) || !isset($_SESSION['dentist'])) {
    header("Location: patient_dash.php");
    exit();
}

// ✅ NORMAL NEXT (go to step3)
if (isset($_POST['next'])) {
    $_SESSION['date'] = $_POST['date'];
    $_SESSION['time'] = $_POST['time'];

    header("Location: step3.php");

    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C'Smiles Dental Center - Step 2</title>
    <link rel="stylesheet" href="step2.css">
</head>
<body>
<header>
    <div class="brand">
        <div class="logo-circle"><img src="logo.jpg" alt="Logo"></div>
        <div class="clinic-title">C'Smiles<span>Dental Center</span></div>
    </div>
    <nav><a href="#">Home</a><a href="#">Services</a><a href="#">Contact</a><a href="login.html" class="logout-link">Log out</a></nav>
</header>
<div class="page-wrapper">
    <aside>
        
        <div class="user-section">
            <div class="user-name">
    <?php echo isset($user['name']) ? htmlspecialchars($user['name']) : 'Guest'; ?>
</div>
            <div class="avatar-circle">👤</div>
        </div>
        
        <a href="patient_dash.php" class="nav-item active">
            <span>💼</span> Book Appointment
        </a>
        
        <a href="appointments.php" class="nav-item">
            <span>📅</span> Appointments
        </a>
        
        <a href="records.php" class="nav-item">
            <span>📂</span> Records
        </a>

        <a href="rewards.php" class="nav-item">
            <span>⭐</span> Reward Points
        </a>
    </aside>

    <div class="main-content-area">
        <main>
            <div class="stepper-head">
                <span class="active">Select Service</span>
                <span class="active">Set date and time</span>
                <span>Confirm your details</span>
                <span>Confirm appointment</span>
            </div>
            <h2>What's your plan date?</h2>
            
            <div class="booking-grid">

    <!-- CALENDAR SECTION -->
    <div class="calendar-box">
        <div class="calendar-header">
            <button onclick="prevMonth()">◀</button>
            <h3 id="monthYear"></h3>
            <button onclick="nextMonth()">▶</button>
        </div>

        <table class="calendar">
            <thead>
                <tr>
                    <th>Sun</th><th>Mon</th><th>Tue</th>
                    <th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                </tr>
            </thead>
            <tbody id="calendarBody"></tbody>
        </table>
        
    </div>
                <div class="time-box">
                    <h3>Set your Time</h3>
                    <div style="display:flex; justify-content:center; gap:10px; margin-bottom:15px;">
                        <button class="ampm-btn selected" data-period="AM">AM</button>
                        <button class="ampm-btn" data-period="PM">PM</button>
                    </div>
                    <div class="time-grid" id="tGrid">
                        <!-- LEFT = AM -->
                        <button class="time-btn" data-period="AM">8:00</button>
                        <button class="time-btn" data-period="PM">1:00</button>

                        <button class="time-btn selected" data-period="AM">9:00</button>
                        <button class="time-btn" data-period="PM">2:00</button>

                        <button class="time-btn" data-period="AM">10:30</button>
                        <button class="time-btn" data-period="PM">3:30</button>
                    </div>
                </div>
            
                <form method="POST" id="step2Form">
                <input type="hidden" name="date" id="dateInput">
                <input type="hidden" name="time" id="timeInput">
                <input type="hidden" name="date_db" id="dateDbInput">

                <div id="errorMsg" class="error-message">
                    Please choose preferred date and time
                </div>

                <div class="footer-action">
                    <a href="patient_dash.php" class="btn">Prev</a>
                    <button type="submit" name="next" class="btn">Next</button>
                </div>
</form>
        </main>
    </div>
</div>
<script src="step2.js"></script>
</body>
</html>