<?php
session_start();

// 1. Database Connection
$conn = new mysqli("localhost", "root", "", "db_appsets");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$patient_id = $_SESSION['patient_id'] ?? 0;
if ($patient_id == 0) { header("Location: login.html"); exit(); }

// --- CANCELLATION LOGIC ---
if (isset($_POST['confirm_cancel'])) {
    $apt_id = $_POST['appointment_id'];
    $reason = $_POST['cancel_reason'] ?? '';
    
    // Update status to Cancelled. The SELECT query below will then ignore this record.
    $stmt_cancel = $conn->prepare("UPDATE tb_appointment SET status = 'Cancelled', notes = CONCAT(IFNULL(notes,''), ' | Cancel Reason: ', ?) WHERE appointment_id = ? AND patient_id = ?");
    $stmt_cancel->bind_param("sii", $reason, $apt_id, $patient_id);
    
    if ($stmt_cancel->execute()) {
        $cancel_success = true;
    }
}

// 2. Handle Dynamic Month/Year
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

// 3. Fetch Data
$first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
$number_days = date('t', $first_day_of_month);
$month_name = strtoupper(date('F', $first_day_of_month));
$day_of_week = date('w', $first_day_of_month);

$appointments = [];
$start_date = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-01";
$end_date = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-$number_days";

// We exclude 'Cancelled' status so they disappear from the UI
$query = "SELECT a.*, s.service_name, d.dentist_name 
          FROM tb_appointment a
          JOIN tb_service s ON a.service_id = s.service_id
          JOIN tb_dentist d ON a.dentist_id = d.dentist_id
          WHERE a.patient_id = ? AND a.status != 'Cancelled'
          AND a.date BETWEEN ? AND ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $patient_id, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $day_idx = (int)date("j", strtotime($row['date']));
    $appointments[$day_idx][] = $row;
}

$stmt_u = $conn->prepare("SELECT * FROM tb_patient WHERE patient_id = ?");
$stmt_u->bind_param("i", $patient_id);
$stmt_u->execute();
$user = $stmt_u->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>C'Smiles Dental Center - Your Appointments</title>
    <link rel="stylesheet" href="appointments.css">
    <style>
        .month-header { display: flex; justify-content: space-between; align-items: center; padding: 15px 0; }
        .nav-btn { background: #1cb9d0; color: white; text-decoration: none; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        .nav-btn:hover { background: #159fb3; }
        .reason-section textarea { width: 100%; height: 80px; margin-top: 10px; border-radius: 5px; padding: 10px; border: 1px solid #ccc; box-sizing: border-box; }
        .confirm-actions { display: flex; gap: 10px; justify-content: center; margin-top: 20px; }
        .yes-btn { background: #ff4d4d; color: white; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; }
        .no-btn { background: #ccc; border: none; padding: 10px 25px; border-radius: 5px; cursor: pointer; }
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: white; padding: 20px; border-radius: 10px; width: 90%; max-width: 400px; }
        .apt-entry { background: #e0f7fa; margin: 5px 0; padding: 5px; border-radius: 3px; cursor: pointer; font-size: 0.8em; border-left: 3px solid #1cb9d0; }
    </style>
</head>
<body>

<header>
    <div class="brand">
        <div class="logo-circle"><img src="logo.jpg" alt="Logo"></div>
        <div class="clinic-title">C'Smiles<span>Dental Center</span></div>
    </div>
    <nav>
        <a href="patient_dash.php">Home</a>
        <a href="#">Services</a>
        <a href="#">Contact</a>
        <a href="logout.php">Log out</a>
    </nav>
</header>

<div class="page-wrapper">
    <aside>
        <div class="user-section">
            <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
            <div class="avatar-circle">👤</div>
        </div>
        <a href="patient_dash.php" class="nav-item"><span>💼</span> Book Appointment</a>
        <a href="appointments.php" class="nav-item active"><span>📅</span> Appointments</a>
        <a href="records.php" class="nav-item"><span>📂</span> Records</a>
    </aside>

    <div class="main-content-area">
        <main>
            <h1>Your Appointments</h1>
            
            <div class="month-header">
                <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="nav-btn">◀ PREV</a>
                <div class="month-year-title">
                    <span class="month-name"><?php echo $month_name; ?></span>
                    <span class="year-name"><?php echo $year; ?></span>
                </div>
                <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="nav-btn">NEXT ▶</a>
            </div>

            <table class="appointments-calendar">
                <thead>
                    <tr>
                        <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $current_day = 1; echo "<tr>";
                    for ($i = 0; $i < $day_of_week; $i++) { echo "<td></td>"; }

                    while ($current_day <= $number_days) {
                        if (($day_of_week + $current_day - 1) % 7 == 0 && $current_day != 1) { echo "</tr><tr>"; }

                        echo "<td>";
                        echo "<div class='day-number'>$current_day</div>";
                        if (isset($appointments[$current_day])) {
                            foreach ($appointments[$current_day] as $apt) {
                                $sName = addslashes(htmlspecialchars($apt['service_name']));
                                $dName = addslashes(htmlspecialchars($apt['dentist_name']));
                                $timeStr = date("h:i A", strtotime($apt['time']));
                                $fullDateStr = date("M j, Y", strtotime($apt['date'])) . " - " . $timeStr;
                                $aptId = $apt['appointment_id'];
                                
                                echo "<div class='apt-entry' onclick='openDetailsModal(\"$sName\", \"$dName\", \"$fullDateStr\", \"$aptId\")'>
                                        <strong>$sName</strong><br>$timeStr
                                      </div>";
                            }
                        }
                        echo "</td>";
                        $current_day++;
                    }
                    while (($day_of_week + $current_day - 1) % 7 != 0) { echo "<td></td>"; $current_day++; }
                    echo "</tr>";
                    ?>
                </tbody>
            </table>
        </main>
    </div>
</div>

<!-- Modal 1: Appointment Details -->
<div id="appointmentModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Appointment Details</h2>
        <div class="details-container">
            <p><strong>Service:</strong> <span id="modalService"></span></p>
            <p><strong>Dentist:</strong> <span id="modalDentist"></span></p>
            <p><strong>Schedule:</strong> <span id="modalDateTime"></span></p>
            <div class="confirm-actions">
                <button class="yes-btn" onclick="openCancelConfirm()">Cancel Appointment</button>
                <button class="no-btn" onclick="closeModal('appointmentModal')">Back</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 2: Cancellation Confirmation -->
<div id="cancelConfirmModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Cancel Appointment?</h2>
        <form method="POST" action="appointments.php">
            <input type="hidden" name="appointment_id" id="cancelAptId">
            <div class="reason-section">
                <label>Reason (Optional):</label>
                <textarea name="cancel_reason" placeholder="Why are you canceling?"></textarea>
            </div>
            <div class="confirm-actions">
                <button type="button" class="no-btn" onclick="closeModal('cancelConfirmModal')">NO</button>
                <button type="submit" name="confirm_cancel" class="yes-btn">YES, CANCEL</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Success Message -->
<?php if(isset($cancel_success)): ?>
<div id="cancelSuccessModal" class="modal-overlay" style="display:flex;">
    <div class="modal-content" style="text-align: center;">
        <h2>Success!</h2>
        <p>Your appointment has been canceled.</p>
        <button class="nav-btn" onclick="window.location.href='appointments.php'">OK</button>
    </div>
</div>
<?php endif; ?>

<script src="appointment.js">

</script>

</body>
</html>