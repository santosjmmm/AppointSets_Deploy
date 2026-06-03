<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C'Smiles Dental Center - Step 1</title>
    <style>
        :root {
            --bg-gradient: linear-gradient(180deg, #b7e4d9 0%, #fefbe0 100%);
            --sidebar-bg: #e1f1ee;
            --text-gold: #c5a043;
            --button-teal: #1cb9d0;
            --dark-text: #333;
            --pill-gradient: linear-gradient(180deg, #faffd1 0%, #e1e9b7 100%);
            --sidebar-width: 280px;
        }

        body { margin: 0; font-family: 'Segoe UI', Tahoma, sans-serif; background: var(--bg-gradient); min-height: 100vh; display: flex; flex-direction: column; color: var(--dark-text); overflow-x: hidden; }

        /* --- Header --- */
        header { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; height: 80px; z-index: 20; }
        .brand { display: flex; align-items: center; gap: 15px; }
        .logo-circle { height: 85px; width: 85px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden; border: 2px solid white; }
        .logo-circle img { width: 100%; height: 100%; object-fit: cover; }
        .clinic-title { color: var(--text-gold); font-size: 2.6rem; font-weight: bold; line-height: 0.8; }
        .clinic-title span { display: block; font-size: 1rem; letter-spacing: 2px; text-transform: uppercase; border-top: 1px solid var(--text-gold); padding-top: 3px; margin-top: 3px; }
        nav a { margin-left: 25px; text-decoration: none; color: var(--text-gold); font-weight: bold; font-size: 1.2rem; }

        /* --- Seamless Sidebar --- */
        aside { position: fixed; left: 0; top: 110px; background-color: var(--sidebar-bg); width: var(--sidebar-width); height: calc(100vh - 110px); border-radius: 0 40px 40px 0; box-shadow: 5px 0 15px rgba(0,0,0,0.05); z-index: 10; padding-top: 10px; }
        .menu-btn { padding: 10px 25px; font-size: 1.8rem; color: #777; cursor: pointer; }
        .user-section { display: flex; align-items: center; justify-content: space-between; padding: 0 25px 20px; border-bottom: 2px solid rgba(0,0,0,0.08); margin-bottom: 20px; }
        .user-name { font-weight: 700; font-size: 1.1rem; }
        .avatar-circle { width: 45px; height: 45px; border-radius: 50%; background: #4a789c; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.6rem; }
        
        /* Fixed Navigation Items */
        .nav-item { display: flex; align-items: center; padding: 20px 30px; gap: 15px; font-weight: 640; cursor: pointer; text-decoration: none; color: inherit; }
        .nav-item.active { background: rgba(255,255,255,0.3); font-weight: 800; }
        .nav-item:hover { background: rgba(255, 255, 255, 0.5); }

        /* --- Content Area --- */
        .main-content-area { flex: 1; display: flex; justify-content: center; align-items: center; margin-left: var(--sidebar-width); padding: 40px; }
        main { background: white; width: 100%; max-width: 950px; padding: 50px 70px; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); }

        /* --- Form Elements --- */
        .stepper-head { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 3px solid #f2f2f2; padding-bottom: 15px; }
        .stepper-head span { color: var(--text-gold); font-weight: 800; font-size: 1.2rem; }
        .stepper-head .inactive { opacity: 0.3; }
        h2 { font-weight: 500; font-size: 2.2rem; margin: 0 0 12px 0; }
        .divider { height: 1px; background: #ddd; margin-bottom: 35px; }
        .pill-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .pill-option input { display: none; }
        .pill-label { display: block; background: var(--pill-gradient); padding: 18px; text-align: center; border-radius: 50px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: 0.2s; }
        .pill-option input:checked + .pill-label { background: #a3e635; transform: translateY(3px); }
        .dentist-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; width: 70%; }
        .footer-action { display: flex; justify-content: flex-end; margin-top: 30px; }
        .next-btn { background: var(--button-teal); color: white; border: none; padding: 14px 60px; border-radius: 30px; font-weight: bold; font-size: 1.3rem; cursor: pointer; }
		/* Layout Styling */
/* Layout for the Main Content */

.welcome-text {
    text-align: left;
    width: 100%;
    margin: 0 0 20px 0;
}
.main-content-area {
    margin-left: var(--sidebar-width);
    padding: 40px;
    display: flex;
    flex-direction: column;
    align-items: flex-start;   /* makes everything align left */
    gap: 20px;
}

/* Row 1: The three cards side-by-side */
.stats-container {
    display: flex;
    justify-content: space-between; /* Spreads them out evenly */
    gap: 20px;
    width: 100%;
}

.stat-card {
    flex: 1; /* Makes all three boxes the same width */
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

/* Row 2: The charts side-by-side */
.charts-row {
    display: flex;
    gap: 20px;
    width: 100%;
}

.chart-box {
    background: white;
    padding: 20px;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

/* Adjust sizes: Weekly is wider, Popular is narrower */
.chart-box.weekly { flex: 2; } 
.chart-box.popular { flex: 1; }

/* Keep canvas responsive */
canvas {
    width: 100% !important;
    height: auto !important;
}
	</style>
</head>
<body>
<header>
    <div class="brand">
        <div class="logo-circle"><img src="logo.jpg" alt="Logo"></div>
        <div class="clinic-title">c'smiles<span>Dental Center</span></div>
    </div>
    <nav><a href="#">Home</a><a href="../For_Admin/adminService.php">Services</a><a href="#">Contact</a><a href="login.php" class="logout-link">Log out</a></nav>
</header>
<div class="page-wrapper">
    <aside>
        <div class="menu-btn">☰</div>
        <div class="user-section">
            <div class="user-name">Admin </br>John Michael S.</div>
            <div class="avatar-circle">👤</div>
        </div>
        
        <a href="#.html" class="nav-item active">
            <span>💼</span> Reports and Analytics
        </a>
        
    </aside>
	
	<div class="main-content-area">
    <h2 class="welcome-text">Welcome, Admin John</h2>

    <div class="stats-container">
        <div class="stat-card">
            <span class="stat-label">Total Patients</span>
            <div class="stat-value">1,284</div>
            <span class="stat-trend positive">+5% this month</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Appointments</span>
            <div class="stat-value">42</div>
            <span class="stat-trend">Today's schedule</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Revenue</span>
            <div class="stat-value">₱45,000</div>
            <span class="stat-trend positive">+12% vs last week</span>
        </div>
    </div>

    <div class="charts-row">
        <div class="chart-box weekly">
            <h3>Weekly Appointments</h3>
            <canvas id="appointmentsChart"></canvas>
        </div>
        <div class="chart-box popular">
            <h3>Popular Services</h3>
            <canvas id="servicesChart"></canvas>
        </div>
    </div>
</div>

<script>
// 1. Line Chart for Appointments
const ctx1 = document.getElementById('appointmentsChart').getContext('2d');
new Chart(ctx1, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        datasets: [{
            label: 'Appointments',
            data: [12, 19, 15, 25, 22, 30],
            borderColor: '#3498db',
            tension: 0.4,
            fill: true,
            backgroundColor: 'rgba(52, 152, 219, 0.1)'
        }]
    }
});

// 2. Doughnut Chart for Services
const ctx2 = document.getElementById('servicesChart').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Cleaning', 'Extraction', 'Whitening', 'Braces'],
        datasets: [{
            data: [40, 20, 15, 25],
            backgroundColor: ['#3498db', '#2ecc71', '#9b59b6', '#f1c40f']
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});</script>


</div>
</body>
</html>