<?php
session_start();

$error = "";

// ==========================
// LOGIN ATTEMPTS CONTROL
// ==========================
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['lock_time'] = $_SESSION['lock_time'] ?? 0;

$cooldown = 30;
$remaining_time = 0;

if ($_SESSION['login_attempts'] >= 3) {
    $elapsed = time() - $_SESSION['lock_time'];
    $remaining_time = $cooldown - $elapsed;

    if ($remaining_time > 0) {
        $error = "Too many failed attempts. Try again in $remaining_time seconds.";
    } else {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lock_time'] = 0;
    }
}

// ==========================
// LOGIN PROCESS
// ==========================
if (isset($_POST['login']) && $remaining_time <= 0) {

    $conn = new mysqli("localhost", "root", "", "db_appsets");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Tables config (cleaner 🔥)
    $tables = [
        ["table" => "tb_admin", "id" => "admin_id", "role" => "admin", "redirect" => "admin_dash.php"],
        ["table" => "tb_patient", "id" => "patient_id", "role" => "patient", "redirect" => "patient_dash.php"],
        ["table" => "tb_dentist", "id" => "dentist_id", "role" => "dentist", "redirect" => "dentist_dash.php"],
        ["table" => "tb_staff", "id" => "staff_id", "role" => "staff", "redirect" => "staff_dash.php"]
    ];

    $account_found = false;

    foreach ($tables as $t) {
        $stmt = $conn->prepare("SELECT * FROM {$t['table']} WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $account_found = true;
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                // ✅ SUCCESS LOGIN
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lock_time'] = 0;

                $_SESSION['role'] = $t['role'];
                $_SESSION[$t['id']] = $user[$t['id']];

                header("Location: " . $t['redirect']);
                exit();
            } else {
                $error = "Incorrect password!";
            }
        }

        $stmt->close();

        // stop checking once found
        if ($account_found) break;
    }

    if (!$account_found) {
        $error = "No account found with that email!";
    }

    // Count failed attempts
    if ($account_found && $error === "Incorrect password!") {
        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lock_time'] = time();
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>C'Smiles Dental Center - Log In</title>
<link rel="stylesheet" href="login.css">
</head>
<body>

<div class="login-card">
    <div class="logo-container">
        <img src="logo.jpg" alt="C'Smiles Logo">
    </div>

    <form action="" method="POST">
        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>
        
        <div class="input-group password-group" style="position: relative;">
            <input type="password" name="password" id="password" placeholder="Password" required>
            <span onclick="togglePassword()" 
                  style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);
                         cursor: pointer;">👁</span>
        </div>

        <!-- ERROR MESSAGE -->
        <?php if($error != ""): ?>
            <div id="errorBox" style="color: red; margin-bottom: 10px; font-weight: bold;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <a href="#" class="forgot-password">Forgot Password?</a>

        <!-- LOGIN BUTTON -->
        <button type="submit" name="login" class="login-btn"
            <?php if($remaining_time > 0) echo 'disabled'; ?>>
            Log In
        </button>
    </form>

    <div class="footer-links">
        Don't have an account? <a href="signup.php">Sign Up</a>
    </div>
</div>

<!-- SHOW/HIDE PASSWORD -->
<script>
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const icon = event.target;

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.textContent = "🙈";
    } else {
        passwordInput.type = "password";
        icon.textContent = "👁";
    }
}
</script>

<!-- COUNTDOWN SCRIPT -->
<?php if($remaining_time > 0): ?>
<script>
let timeLeft = <?php echo $remaining_time; ?>;
const errorBox = document.getElementById("errorBox");
const loginBtn = document.querySelector(".login-btn");

if (loginBtn) loginBtn.disabled = true;

const countdown = setInterval(() => {
    timeLeft--;

    if (timeLeft > 0) {
        if (errorBox) {
            errorBox.textContent = "Too many failed attempts. Try again in " + timeLeft + " seconds.";
        }
    } else {
        clearInterval(countdown);

        if (errorBox) {
            errorBox.textContent = "You can try logging in again.";
        }

        if (loginBtn) loginBtn.disabled = false;
    }
}, 1000);
</script>
<?php endif; ?>

</body>
</html>