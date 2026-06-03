<?php
$conn = new mysqli("localhost", "root", "", "db_appsets");

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$error = "";
$full_name = $age = $contact_number = $address = $email = "";
$password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST['full_name']);
    $age = trim($_POST['age']);
    $contact_number = trim($_POST['contact_num']);
    $address = trim($_POST['address']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // EMPTY CHECK
    if (
        empty($full_name) || empty($age) || empty($contact_number) ||
        empty($address) || empty($email) ||
        empty($password) || empty($confirm_password)
    ) {
        $error = "All fields are required!";
    }

    elseif ($age <= 0) {
        $error = "Age must be a positive number!";
    }

    // PASSWORD VALIDATION
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    }

    elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "Password must include at least one uppercase letter";
    }

    elseif (!preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
        $error = "Password must include a number and a special character";
    }

    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    }

    else {

        // CHECK EMAIL
        $check = $conn->prepare("SELECT patient_id FROM tb_patient WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = "Email is already registered!";
        } else {

            // ✅ HASH PASSWORD
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO tb_patient 
                (name, age, contact_num, address, email, password) 
                VALUES (?, ?, ?, ?, ?, ?)");

            $stmt->bind_param(
                "sissss",
                $full_name,
                $age,
                $contact_number,
                $address,
                $email,
                $hashed_password
            );

            if ($stmt->execute()) {
                header("Location: accsuccess.html");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }

            $stmt->close();
        }

        $check->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>C'Smiles Dental Center - Sign Up</title>
<link rel="stylesheet" href="signup.css">
</head>

<script>
function togglePassword(fieldId, icon) {
    const input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
        icon.textContent = "🙈";
    } else {
        input.type = "password";
        icon.textContent = "👁";
    }
}
</script>

<body>

<div class="login-card">
    <div class="logo-container">
        <img src="logo.jpg" alt="C'Smiles Logo">
    </div>


    <form action="" method="POST" autocomplete="off">

    <div class="input-group">
        <input type="text" name="full_name" placeholder="Full Name" autocomplete="off"
        value="<?php echo htmlspecialchars($full_name); ?>" required></div>

    <div class="input-group">
        <input type="number" name="age" placeholder="Age" min="3" autocomplete="off"
        value="<?php echo htmlspecialchars($age); ?>" required></div>

    <div class="input-group">
        <input type="tel" name="contact_num" placeholder="Contact Number" autocomplete="off"
        value="<?php echo htmlspecialchars($contact_number); ?>" required></div>

    <div class="input-group">
        <input type="text" name="address" placeholder="Address" autocomplete="off"
        value="<?php echo htmlspecialchars($address); ?>" required></div>

    <div class="input-group">
        <input type="email" name="email" placeholder="Email" autocomplete="off"
        value="<?php echo htmlspecialchars($email); ?>" required>    </div>
    
    <div class="input-group password-group">
        <input type="password" name="password" autocomplete="off" id="password" placeholder="Password" required>
        <span class="toggle-eye" onclick="togglePassword('password', this)">👁</span>
    </div>

      <div id="password-rules" class="password-rules">
    <p id="rule-length"><span class="circle">o</span> 8 - 12 Characters</p>
    <p id="rule-case"><span class="circle">o</span> At least one uppercase & lowercase</p>
    <p id="rule-special"><span class="circle">o</span> At least one special character</p>
</div>

    <div class="input-group password-group">
        <input type="password" name="confirm_password" autocomplete="off" id="confirm_password" placeholder="Confirm Password" required>
        <span class="toggle-eye" onclick="togglePassword('confirm_password', this)">👁</span>
    </div>

    <!-- ✅ ERROR MESSAGE HERE -->
    <?php if (!empty($error)): ?>
        <div class="error-message">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <button type="submit" class="login-btn">Sign Up</button>

</form>

    <div class="footer-links">
        Already have an account? <a href="login.php">Log In</a>
    </div>
</div>
<script src="signup.js"></script>
</body>
</html>