<?php

include_once 'db.php';

header("Content-Type: application/json; charset=UTF-8");

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$action = $data['action'] ?? '';

// -------------------------
// PHPMailer loader (safe)
// -------------------------
$mailerPath = __DIR__ . '/PHPMailer/';

if (!file_exists($mailerPath . 'PHPMailer.php')) {
    echo json_encode([
        "success" => false,
        "message" => "PHPMailer not found",
        "debug" => $mailerPath
    ]);
    exit;
}

require_once $mailerPath . 'Exception.php';
require_once $mailerPath . 'PHPMailer.php';
require_once $mailerPath . 'SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST" && $data) {
    
    // ==========================================
    // ACTION 1: VALIDATE DETAILS AND SEND OTP
    // ==========================================
    if ($action === 'send_signup_otp') {
        $email = trim($data['email'] ?? '');
        $full_name = trim($data['full_name'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($full_name) || empty($email) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Required fields are missing!"]);
            exit();
        }

        if (strlen($password) < 8 || strlen($password) > 12) {
            echo json_encode(["success" => false, "message" => "Password must be 8-12 characters"]);
            exit();
        }

        // Validate unique target record structure matching tables cleanly
        $check = $conn->prepare("SELECT patient_id FROM tb_patient WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            echo json_encode(["success" => false, "message" => "Email is already registered!"]);
            $check->close();
            exit();
        }
        $check->close();

        // Safe randomly populated verification tracking values
        $otp = (string)rand(100000, 999999);
        $expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Flush expired historical logs matching the user target scope
        $clear = $conn->prepare("DELETE FROM tb_otp_verification WHERE email = ?");
        $clear->bind_param("s", $email);
        $clear->execute();
        $clear->close();

        $ins = $conn->prepare("INSERT INTO tb_otp_verification (email, otp_code, expires_at) VALUES (?, ?, ?)");
        $ins->bind_param("sss", $email, $otp, $expires_at);
        $ins->execute();
        $ins->close();

        // SMTP Authentication Variables
        $smtp_user = 'santosjm62904@gmail.com'; 
        $smtp_pass = 'sief nmae lsst ermn';        
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_user;
    $mail->Password = $smtp_pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // ❌ REMOVE DEBUG (THIS BREAKS JSON ON RAILWAY)
    $mail->SMTPDebug = 0;

    $mail->setFrom($smtp_user, "C'Smiles Dental");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Verification Code";

    $mail->Body = "
        <div style='font-family:Arial;padding:20px'>
            <h2>Verification Code</h2>
            <h1 style='letter-spacing:5px'>{$otp}</h1>
        </div>
    ";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "OTP sent successfully"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Email failed",
        "error" => $mail->ErrorInfo
    ]);
}
exit;

    // ==========================================
    // ACTION 2: VERIFY OTP AND COMMIT USER REGISTRATION
    // ==========================================
    elseif ($action === 'verify_and_register') {
        $full_name = trim($data['full_name'] ?? '');
        $age = trim($data['age'] ?? '');
        $contact_number = trim($data['contact_num'] ?? '');
        $address = trim($data['address'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');
        $otp = trim($data['otp'] ?? '');

        if (empty($email) || empty($otp) || empty($full_name) || empty($password)) {
            echo json_encode(["success" => false, "message" => "All form context parameters are required."]);
            exit();
        }

        $current_time = date("Y-m-d H:i:s");

        // Scan verification matrix layers securely
        $check = $conn->prepare("SELECT id FROM tb_otp_verification WHERE email = ? AND otp_code = ? AND expires_at > ? ORDER BY id DESC LIMIT 1");
        $check->bind_param("sss", $email, $otp, $current_time);
        $check->execute();
        $verification_valid = $check->get_result()->num_rows > 0;
        $check->close();

        if (!$verification_valid) {
            echo json_encode(["success" => false, "message" => "Invalid or expired verification OTP code."]);
            exit();
        }

        // Encrypt the plain text string securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO tb_patient (name, age, contact_num, address, email, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $full_name, $age, $contact_number, $address, $email, $hashed_password);

        if ($stmt->execute()) {
            // Delete code traces on complete record generation sequence
            $del = $conn->prepare("DELETE FROM tb_otp_verification WHERE email = ?");
            $del->bind_param("s", $email);
            $del->execute();
            $del->close();

            echo json_encode(["success" => true, "message" => "Registration successful! Account created."]);
        } else {
            echo json_encode(["success" => false, "message" => "Registration SQL execution failure: " . $stmt->error]);
        }
        $stmt->close();
        exit();
    } 
    
    else {
        echo json_encode(["success" => false, "message" => "Invalid request routing sequence."]);
        exit();
    }
}

$conn->close();
?>
