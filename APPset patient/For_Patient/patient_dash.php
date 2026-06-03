<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "db_appsets");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure user is logged in
if (!isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];

// --- NEW LOGIC: Get service from URL (either from Rewards OR Services page) ---
$selected_service_name = null;

// 1. Check if redirected from Services page via ID
if (isset($_GET['service_id'])) {
    $sid = (int)$_GET['service_id'];
    $res = $conn->query("SELECT service_name FROM tb_service WHERE service_id = $sid");
    if ($row = $res->fetch_assoc()) {
        $selected_service_name = $row['service_name'];
    }
} 
// 2. Fallback to your existing redeemed_service logic (Rewards)
elseif (isset($_GET['redeemed_service'])) {
    $selected_service_name = $_GET['redeemed_service'];
}

// Fetch user data
$stmt = $conn->prepare("SELECT name, points FROM tb_patient WHERE patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$points = $user['points'] ?? 0;

// Fetch Dentists from DB (to make the dentist selection dynamic too)
$dentists_res = $conn->query("SELECT dentist_name FROM tb_dentist WHERE status = 'Active'");

// Handle form submit
if (isset($_POST['next'])) {
    $_SESSION['service'] = $_POST['service'];
    $_SESSION['dentist'] = $_POST['dentist'];
    
    if (isset($_GET['redeemed_service'])) {
        $_SESSION['is_reward'] = true;
    }

    header("Location: step2.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C'Smiles Dental Center - Dashboard</title>
    <link rel="stylesheet" href="patient_dash.css">
    <style>
        /* --- Compact Points Bar --- */
        .points-hero-slim {
            border: 1px solid white;
            border-radius: 15px;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .points-left-group { display: flex; align-items: center; gap: 12px; }
        .points-icon-mini { width: 35px; height: 35px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .points-label-mini { color: #7a8a4d; font-weight: 800; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; }
        .points-value-mini { font-size: 1.2rem; font-weight: 900; color: #333; }
        .membership-badge-mini { background: rgba(255, 255, 255, 0.6); padding: 4px 12px; border-radius: 20px; color: #c5a043; font-weight: 700; font-size: 1rem; }
        h2.form-title { font-size: 1.6rem; margin-bottom: 8px; }

        /* Style for disabled/locked service */
        .pill-option.locked {
            opacity: 0.4;
            cursor: not-allowed;
            background: #f0f0f0;
            filter: grayscale(1);
            border: 1px dashed #ccc;
        }
        .reward-notice {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: bold;
            border: 1px solid #c3e6cb;
        }
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
        <a href="Services.php">Services</a>
        <a href="login.php" class="logout-link">Log out</a>
    </nav>
</header>

<div class="page-wrapper">
    <aside>
        <div class="user-section">
            <div class="user-name"><?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?></div>
            <div class="avatar-circle">👤</div>
        </div>
        <a href="patient_dash.php" class="nav-item active"><span>💼</span> Book Appointment</a>
        <a href="appointments.php" class="nav-item"><span>📅</span> Appointments</a>
        <a href="records.php" class="nav-item"><span>📂</span> Records</a>
        <a href="rewards.php" class="nav-item"><span>⭐</span> Rewards</a>
    </aside>

    <div class="main-content-area">
        <main>
            <div class="points-hero-slim">
                <div class="membership-badge-mini">🎖️ Loyalty Member</div>
                <div class="points-left-group">
                    <div class="points-icon-mini">⭐</div>
                    <div>
                        <div class="points-label-mini">Loyalty Balance</div>
                        <div class="points-value-mini"><?php echo number_format($points); ?> <span>PTS</span></div>
                    </div>
                </div>
            </div>

            <?php if ($selected_service_name): ?>
                <div class="reward-notice">
                    📍 Selected Service: <strong><?php echo htmlspecialchars($selected_service_name); ?></strong>. <a href="patient_dash.php" style="font-size: 0.8rem; margin-left: 10px; color: #155724;">(Change)</a>
                </div>
            <?php endif; ?>

            <form method="POST" id="bookingForm">
                <h2 class="form-title">What's your plan today?</h2>
                <div class="divider" style="margin-bottom: 20px;"></div>
                
                <div class="pill-grid">
                    <?php 
                    // Fetch real services from DB for the pills
                    $db_services = $conn->query("SELECT service_name FROM tb_service");
                    while ($row = $db_services->fetch_assoc()): 
                        $s = $row['service_name'];
                        // Logic to "Cross out" others
                        $is_selected = ($selected_service_name == $s);
                        $is_disabled = ($selected_service_name && !$is_selected);
                        
                        $checked = $is_selected ? "checked" : "";
                        $disabled = $is_disabled ? "disabled" : "";
                        $lockedClass = $is_disabled ? "locked" : "";
                    ?>
                        <label class="pill-option <?php echo $lockedClass; ?>">
                            <input type="radio" name="service" value="<?php echo $s; ?>" <?php echo $checked; ?> <?php echo $disabled; ?>>
                            <div class="pill-label"><?php echo $s; ?></div>
                        </label>
                        
                        <?php if($is_selected): ?>
                            <input type="hidden" name="service" value="<?php echo $s; ?>">
                        <?php endif; ?>
                    <?php endwhile; ?>
                </div>

                <h2 class="form-title">Who's your Dentist?</h2>
                <div class="divider" style="margin-bottom: 20px;"></div>
                
                <div class="dentist-grid">
                    <?php while($d = $dentists_res->fetch_assoc()): ?>
                        <label class="pill-option">
                            <input type="radio" name="dentist" value="<?php echo $d['dentist_name']; ?>">
                            <div class="pill-label">Dr. <?php echo $d['dentist_name']; ?></div>
                        </label>
                    <?php endwhile; ?>
                </div>

                <div id="errorTop" class="error-box" style="color: red; display: none; margin-top: 10px;"></div>

                <div class="footer-action">
                    <button type="submit" name="next" class="next-btn">Next</button>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
    document.getElementById("bookingForm").addEventListener("submit", function(e){
        // Check for hidden service input or checked radio
        const service = document.querySelector('input[name="service"]');
        const dentist = document.querySelector('input[name="dentist"]:checked');
        const errorTop = document.getElementById("errorTop");

        if(!service.value || !dentist){
            e.preventDefault();
            errorTop.innerText = "Please choose a service and a dentist.";
            errorTop.style.display = "block";
        }
    });
</script>

</body>
</html>