<?php
// Start session to get the logged-in patient's ID
session_start();

// Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_appsets";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// For demonstration, we'll use Patient ID 22 from your SQL dump. 
// In a real app, use: $patient_id = $_SESSION['patient_id'];
$patient_id = 22; 

// Query to get COMPLETED appointments only
// We JOIN tb_dentist and tb_service to get the names instead of just IDs
$sql = "SELECT a.date, a.time, a.notes, d.dentist_name, s.service_name 
        FROM tb_appointment a
        JOIN tb_dentist d ON a.dentist_id = d.dentist_id
        JOIN tb_service s ON a.service_id = s.service_id
        WHERE a.patient_id = ? AND a.status = 'Completed'
        ORDER BY a.date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C'Smiles Dental Center - Records</title>
    <style>
        :root {
            --bg-gradient: linear-gradient(180deg, #b7e4d9 0%, #fefbe0 100%);
            --sidebar-bg: #e1f1ee;
            --text-gold: #c5a043;
            --button-teal: #1cb9d0;
            --sidebar-width: 280px;
        }

        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-gradient); min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }

        /* --- Header --- */
        header { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; height: 80px; z-index: 20; position:sticky; top:0; background: rgba(255,255,255,0.1); backdrop-filter: blur(5px); }
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
        .nav-item { display: flex; align-items: center; padding: 20px 30px; gap: 15px; font-weight: 600; cursor: pointer; text-decoration: none; color: inherit; }
        .nav-item.active { background: rgba(255,255,255,0.3); font-weight: 800; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.5); }

        /* --- Main --- */
        .main-content-area { flex: 1; display: flex; justify-content: center; align-items: flex-start; margin-left: var(--sidebar-width); padding: 40px; }
        main { background: white; width: 100%; max-width: 1000px; padding: 50px; border-radius: 20px; min-height: 600px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        h1 { font-size: 2.5rem; margin: 0 0 10px 0; font-family: 'Arial Black', sans-serif; }
        .divider { height: 2px; background: #999; width: 100%; margin-bottom: 40px; }

        /* --- Cards --- */
        .record-card { 
            background: #f9fbfb; border-radius: 10px; padding: 25px 40px; margin-bottom: 20px; 
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04); border: 1px solid #eee;
        }
        .record-info h3 { margin: 0; font-size: 1.8rem; font-weight: 800; color: #2c3e50; }
        .record-info p { margin: 5px 0 0; color: #555; font-size: 1.1rem; }

        .see-details-btn { 
            background: var(--button-teal); color: white; border: none; padding: 10px 30px; 
            border-radius: 30px; font-weight: 900; font-size: 1rem; cursor: pointer; transition: 0.3s;
        }
        .see-details-btn:hover { transform: scale(1.05); filter: brightness(1.1); }

        /* --- Modal Popup --- */
        .modal-overlay { 
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.4); z-index: 100; justify-content: center; align-items: center; 
        }
        .modal-content { 
            background: #f0f0f0; padding: 30px; border-radius: 15px; width: 450px; 
            border: 3px solid var(--button-teal); position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        .modal-content h2 { margin: 0 0 15px 0; border-bottom: 2px solid #ccc; padding-bottom: 10px; font-size: 1.8rem; }
        .detail-item { margin-bottom: 15px; font-size: 1.2rem; line-height: 1.4; color: #333; }
        .detail-item strong { font-weight: 900; color: #000; }
        
        .modal-actions { display: flex; gap: 15px; justify-content: center; margin-top: 25px; }
        .confirm-btn { 
            background: var(--button-teal); color: white; border: none; padding: 10px 40px; 
            border-radius: 30px; font-weight: 900; font-size: 1.1rem; cursor: pointer; transition: 0.2s;
        }
        .confirm-btn:hover { transform: scale(1.05); filter: brightness(1.1); }
    </style>
</head>
<body>

<header>
    <div class="brand">
        <div class="logo-circle"><img src="logo.jpg" alt="Logo"></div>
        <div class="clinic-title">c'smiles<span>Dental Center</span></div>
    </div>
    <nav>
        <a href="index.php">Home</a>
        <a href="#">Services</a>
        <a href="#">Contact</a>
        <a href="logout.php" class="logout-link">Log out</a>
    </nav>
</header>

<div class="page-wrapper">
    <aside>
        <div class="user-section">
            <div class="user-name">John Michael S.</div>
            <div class="avatar-circle">👤</div>
        </div>
        <a href="step1.php" class="nav-item"><span>💼</span> Book Appointment</a>
        <a href="appointments.php" class="nav-item"><span>📅</span> Appointments</a>
        <a href="records.php" class="nav-item active"><span>📂</span> Records</a>
        <a href="rewards.php" class="nav-item"><span>⭐</span> Reward Points</a>
    </aside>

    <div class="main-content-area">
        <main>
            <h1>Records</h1>
            <p style="color: #666; margin-top: -10px; margin-bottom: 20px;">Your completed dental history</p>
            <div class="divider"></div>

            <?php 
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Format date for display
                    $formattedDate = date("M d, Y", strtotime($row['date']));
                    $formattedTime = date("g:i A", strtotime($row['time']));
                    $fullDateTime = $formattedDate . " - " . $formattedTime;
                    
                    // Clean notes for JS string
                    $cleanNotes = htmlspecialchars($row['notes'], ENT_QUOTES);
                    if(empty($cleanNotes)) $cleanNotes = "No specific remarks provided.";
            ?>
                    <div class="record-card">
                        <div class="record-info">
                            <h3><?php echo htmlspecialchars($row['dentist_name']); ?></h3>
                            <p><?php echo htmlspecialchars($row['service_name']); ?></p>
                            <p><?php echo $formattedDate; ?></p>
                        </div>
                        <button class="see-details-btn" 
                                onclick="openDetails('<?php echo htmlspecialchars($row['dentist_name']); ?>', 
                                                    '<?php echo htmlspecialchars($row['service_name']); ?>', 
                                                    '<?php echo $fullDateTime; ?>', 
                                                    '<?php echo $cleanNotes; ?>')">
                            See details
                        </button>
                    </div>
            <?php 
                }
            } else {
                echo "<div style='text-align:center; padding: 50px;'>
                        <p style='font-size: 1.2rem; color: #888;'>No completed medical records found yet.</p>
                      </div>";
            }
            ?>
        </main>
    </div>
</div>

<div id="detailsModal" class="modal-overlay">
    <div class="modal-content">
        <h2>Details</h2>
        <div class="detail-item"><strong>Dentist:</strong> <span id="detDentist"></span></div>
        <div class="detail-item"><strong>Service:</strong> <span id="detService"></span></div>
        <div class="detail-item"><strong>Date:</strong> <span id="detDate"></span></div>
        <div class="detail-item"><strong>Remarks:</strong> <span id="detRemarks"></span></div>
        
<div class="modal-actions">
    <button class="confirm-btn" onclick="goToRebook()">Rebook</button>
    <button class="confirm-btn" style="background-color: #ff4d4d;" onclick="closeDetails()">Close</button>
</div>

<script>
    function openDetails(dentist, service, date, remarks) {
        document.getElementById('detDentist').innerText = dentist;
        document.getElementById('detService').innerText = service;
        document.getElementById('detDate').innerText = date;
        document.getElementById('detRemarks').innerText = remarks;
        document.getElementById('detailsModal').style.display = 'flex';
    }

    function closeDetails() {
        document.getElementById('detailsModal').style.display = 'none';
    }

    function goToRebook() {
        // Get the names from the modal
        const dentistName = document.getElementById('detDentist').innerText;
        const serviceName = document.getElementById('detService').innerText;
        
        // Redirect to step2.php with parameters
        // encodeURIComponent ensures spaces and special characters are handled safely
        window.location.href = 'step2.php?rebook=true&service=' + encodeURIComponent(serviceName) + '&dentist=' + encodeURIComponent(dentistName);
    }

    window.onclick = function(event) {
        let modal = document.getElementById('detailsModal');
        if (event.target == modal) { closeDetails(); }
    }
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>