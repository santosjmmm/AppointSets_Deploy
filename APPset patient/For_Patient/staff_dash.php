<?php
session_start();
if (!isset($_SESSION['staff_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C'Smiles Dental Center - Step 1</title>
   <link rel="stylesheet" href="dentist_dash.css">
</head>
<body>
<header>
    <div class="brand">
        <div class="logo-circle"><img src="logo.jpg" alt="Logo"></div>
        <div class="clinic-title">c'smiles<span>Dental Center</span></div>
    </div>
    <nav><a href="#">Home</a><a href="#">Services</a><a href="#">Contact</a><a href="login.php" class="logout-link">Log out</a></nav>
</header>
<div class="page-wrapper">
    <aside>
        <div class="menu-btn">☰</div>
        <div class="user-section">
            <div class="user-name">John Michael S.</div>
            <div class="avatar-circle">👤</div>
        </div>
        
        <a href="#.html" class="nav-item active">
            <span>💼</span> Dashboard
        </a>
        

    </aside>
	
	<div class="main-content-area">
    <main id="dash">
		
        <h1>Welcome, Staff!</h1>
        <div class="divider"></div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3>Total Appointments</h3>
                    <p class="stat-number">24</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>Completed</h3>
                    <p class="stat-number">18</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-info">
                    <h3>Pending</h3>
                    <p class="stat-number">6</p>
                </div>
            </div>
        </div>

        <div class="content-card">
            <h2>Recent Appointments</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient/Client</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sarah Connor</td>
                        <td>Oct 24, 2023</td>
                        <td><span class="status-tag confirmed">Confirmed</span></td>
                        <td><button class="btn-view">View</button></td>
                    </tr>
                    <tr>
                        <td>James Smith</td>
                        <td>Oct 25, 2023</td>
                        <td><span class="status-tag pending">Pending</span></td>
                        <td><button class="btn-view">View</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
</div>


</div>
</body>
</html>