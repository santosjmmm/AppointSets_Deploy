<?php
// 1. Enable strict error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Set globally required security and CORS headers
header("Access-Control-Allow-Origin: https://appoint-sets-deploy.vercel.app");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// 3. Instantly handle browser CORS preflight handshakes
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 4. Dynamic environment mapping (Railway vs Local XAMPP)
$host     = getenv('MYSQLHOST') ?: 'localhost';
$user     = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';
$database = getenv('MYSQLDATABASE') ?: 'db_appsets';
$port     = getenv('MYSQLPORT') ?: '3306';

// 5. Establish connection
$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    echo json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}
