<?php
session_start();

// 1. DATABASE CONNECTION
$conn = new mysqli("localhost", "root", "", "db_appsets");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. AUTHENTICATION
if (!isset($_SESSION['patient_id'])) {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];

// 3. FETCH PATIENT DATA
$stmt = $conn->prepare("SELECT name, points FROM tb_patient WHERE patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$current_points = $user['points'] ?? 0;
$patient_name = $user['name'] ?? 'Guest';

// 4. FETCH AVAILABLE SERVICES[cite: 1]
$services_result = $conn->query("SELECT service_name, price FROM tb_service WHERE status = 'Available'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C'Smiles Dental Center - Reward Points</title>
    <style>
        :root {
            --bg-gradient: linear-gradient(180deg, #b7e4d9 0%, #fefbe0 100%);
            --sidebar-bg: #e1f1ee;
            --text-gold: #c5a043;
            --button-teal: #1cb9d0;
            --dark-text: #333;
            --sidebar-width: 280px;
            --white: #ffffff;
        }

        body { margin: 0; font-family: 'Segoe UI', Tahoma, sans-serif; background: var(--bg-gradient); min-height: 100vh; display: flex; flex-direction: column; color: var(--dark-text); overflow-x: hidden; }

        /* --- Header --- */
        header { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; height: 80px; z-index: 20; position:sticky; top: 0;}
        .brand { display: flex; align-items: center; gap: 15px; }
        .logo-circle { height: 85px; width: 85px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden; border: 2px solid white; }
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
        .clinic-title { color: var(--text-gold); font-size: 2.6rem; font-weight: bold; line-height: 0.8; }
        .clinic-title span { display: block; font-size: 1rem; letter-spacing: 2px; text-transform: uppercase; border-top: 1px solid var(--text-gold); padding-top: 3px; margin-top: 3px; }
        nav a { margin-left: 25px; text-decoration: none; color: var(--text-gold); font-weight: bold; font-size: 1.2rem; }

        /* --- Sidebar --- */
        aside { position: fixed; left: 0; top: 110px; background-color: var(--sidebar-bg); width: var(--sidebar-width); height: calc(100vh - 110px); border-radius: 0 40px 40px 0; box-shadow: 5px 0 15px rgba(0,0,0,0.05); z-index: 10; padding-top: 10px; }
        .user-section { display: flex; align-items: center; justify-content: space-between; padding: 0 25px 20px; border-bottom: 2px solid rgba(0,0,0,0.08); margin-bottom: 20px; }
        .user-name { font-weight: 700; font-size: 1.1rem; }
        .avatar-circle { width: 45px; height: 45px; border-radius: 50%; background: #4a789c; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.6rem; }
        .nav-item { display: flex; align-items: center; padding: 20px 30px; gap: 15px; font-weight: 640; cursor: pointer; text-decoration: none; color: inherit; }
        .nav-item.active { background: rgba(255,255,255,0.3); font-weight: 800; }


        /* --- Main Layout --- */
        .main-content-area { flex: 1; display: flex; justify-content: center; align-items: flex-start; margin-left: var(--sidebar-width); padding: 60px 40px; }
        main { background: var(--white); width: 100%; max-width: 900px; padding: 60px; border-radius: 30px; box-shadow: 0 20px 50px rgba(0,0,0,0.1); }

        /* --- Centered Navigation Tabs --- */
        .rewards-tabs { display: flex; justify-content: center; gap: 20px; margin-bottom: 50px; background: #f0f7f6; padding: 10px; border-radius: 50px; }
        .tab { padding: 15px 35px; border-radius: 40px; color: var(--dark-text); font-weight: 800; font-size: 1.1rem; cursor: pointer; transition: 0.3s; }
        .tab.active { background: var(--button-teal); color: white; box-shadow: 0 4px 12px rgba(28, 185, 208, 0.3); }
        .tab.inactive { opacity: 0.5; }

        /* --- Points Display --- */
        .points-card { background: #fffcf0; padding: 50px; border-radius: 25px; text-align: center; border: 2px dashed #f1e5bc; margin-top: 20px; }
        .points-large { font-size: 6.5rem; font-weight: 900; color: var(--dark-text); line-height: 1; margin: 20px 0; }
        .star { color: #f39c12; }
        .points-label { font-size: 1.4rem; font-weight: 700; color: #666; }

        /* --- Rewards Grid --- */
        .pill-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-top: 30px; }
        .reward-pill { background: #f8f8f8; padding: 30px; border-radius: 20px; text-align: center; cursor: pointer; border: 2px solid transparent; transition: 0.3s; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .reward-pill:hover { border-color: var(--button-teal); background: #f0fbff; transform: translateY(-5px); }
        .reward-pill h3 { margin: 0; font-size: 1.6rem; font-weight: 800; }
        .cost-tag { margin-top: 10px; display: inline-block; background: #ffeaa7; color: #d35400; padding: 5px 15px; border-radius: 10px; font-weight: 800; }

        /* --- Modal --- */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(4px); }
        .popup-box { background: white; padding: 50px; border-radius: 25px; width: 480px; text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.3); }
        .service-tag { background: #e1f1ee; color: var(--button-teal); padding: 12px 30px; display: inline-block; margin: 20px 0; font-weight: 800; border-radius: 50px; font-size: 1.2rem; }
        
        .btn-group { display: flex; flex-direction: column; gap: 15px; margin-top: 30px; }
        .confirm-btn { background: var(--button-teal); color: white; border: none; padding: 18px; border-radius: 15px; font-weight: 800; font-size: 1.2rem; cursor: pointer; transition: 0.3s; }
        .confirm-btn:hover { background: #16a2b8; }
        .cancel-btn { background: none; color: #888; border: none; font-weight: 600; cursor: pointer; text-decoration: underline; }

        h2 { font-size: 2.2rem; text-align: center; }
        .divider { width: 60px; height: 5px; background: var(--button-teal); margin: 0 auto 40px; border-radius: 10px; }
    </style>
</head>
<body>

<header>
    <div class="brand">
        <div class="logo-circle"><img src="logo.jpg" alt="Logo"></div>
        <div class="clinic-title">C'Smiles<span>Dental Center</span></div>
    </div>
    <nav><a href="#">Home</a><a href="login.php">Log out</a></nav>
</header>

<div class="page-wrapper">
    <aside>
        <div class="user-section">
            <div class="user-name"><?php echo htmlspecialchars($patient_name); ?></div>
            <div class="avatar-circle">👤</div>
        </div>
        <a href="patient_dash.php" class="nav-item"><span>💼</span> Book Appointment</a>
        <a href="appointments.php" class="nav-item"><span>📅</span> Appointments</a>
        <a href="records.php" class="nav-item"><span>📂</span> Records</a>
        <a href="rewards.php" class="nav-item active"><span>⭐</span> Rewards</a>
    </aside>

    <div class="main-content-area">
        <main>
            <div class="rewards-tabs">
                <div id="tabPoints" class="tab active" onclick="showView('points')">Current Balance</div>
                <div id="tabRedeem" class="tab inactive" onclick="showView('redeem')">Redeem Services</div>
            </div>

            <!-- BALANCE VIEW -->
            <div id="pointsView">
                <h2>Points Summary</h2>
                <div class="divider"></div>
                <div class="points-card">
                    <div class="points-label">
                        <?php if($current_points > 0): ?>
                            🎉 AMAZING! YOU HAVE EARNED:
                        <?php else: ?>
                            YOU CURRENTLY HAVE:
                        <?php endif; ?>
                    </div>
                    <div class="points-large"><?php echo $current_points; ?> <span class="star">★</span></div>
                    <div class="points-label">Total Reward Points</div>
                </div>
            </div>

            <!-- REDEEM VIEW -->
            <div id="redeemView" style="display:none;">
                <h2>Choose Your Reward</h2>
                <div class="divider"></div>
                <div class="pill-grid">
                    <?php while($row = $services_result->fetch_assoc()): ?>
                        <div class="reward-pill" onclick="openRedeemModal('<?php echo addslashes($row['service_name']); ?>', <?php echo $row['price']; ?>)">
                            <h3><?php echo htmlspecialchars($row['service_name']); ?></h3>
                            <div class="cost-tag"><?php echo $row['price']; ?> ★ Required</div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="redeemModal" class="modal-overlay">
    <div class="popup-box">
        <h2 id="modalMsg" style="font-size: 1.8rem;">Confirm Redemption?</h2>
        <div id="modalService" class="service-tag"></div>
        <div class="btn-group">
            <button id="confirmBtn" class="confirm-btn" onclick="processRedeem()">Confirm Redeem</button>
            <button class="cancel-btn" onclick="closeModal()">Maybe later</button>
        </div>
    </div>
</div>

<script>
    const userPoints = <?php echo $current_points; ?>;

    function showView(view) {
        const isPoints = view === 'points';
        document.getElementById('pointsView').style.display = isPoints ? 'block' : 'none';
        document.getElementById('redeemView').style.display = isPoints ? 'none' : 'block';
        document.getElementById('tabPoints').className = isPoints ? 'tab active' : 'tab inactive';
        document.getElementById('tabRedeem').className = isPoints ? 'tab inactive' : 'tab active';
    }

    function openRedeemModal(serviceName, cost) {
        const modal = document.getElementById('redeemModal');
        const confirmBtn = document.getElementById('confirmBtn');
        document.getElementById('modalService').innerText = serviceName;
        
        if (userPoints >= cost) {
            document.getElementById('modalMsg').innerText = "Confirm Redemption?";
            confirmBtn.style.display = "block";
        } else {
            document.getElementById('modalMsg').innerText = "Not Enough Points";
            confirmBtn.style.display = "none";
        }
        modal.style.display = 'flex';
    }

    function processRedeem() {
        const service = document.getElementById('modalService').innerText;
        // Logic: Redirect to dashboard with the service name in the URL
        window.location.href = "patient_dash.php?redeemed_service=" + encodeURIComponent(service);
    }

    function closeModal() {
        document.getElementById('redeemModal').style.display = 'none';
    }
</script>
</body>
</html>
</body>
</html>