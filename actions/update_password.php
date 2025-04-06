<?php
session_start();
require_once '../config/db-config.php';
require_once '../includes/sessions-functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php?message=login_required");
    exit();
}

$current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
    header("Location: ../profile.php?error=empty_fields");
    exit();
}

if ($new_password !== $confirm_password) {
    header("Location: ../profile.php?error=password_mismatch");
    exit();
}

if (strlen($new_password) < 8) {
    header("Location: ../profile.php?error=password_too_short");
    exit();
}

if (!preg_match('/[A-Z]/', $new_password) || 
    !preg_match('/[a-z]/', $new_password) || 
    !preg_match('/[0-9]/', $new_password) || 
    !preg_match('/[^A-Za-z0-9]/', $new_password)) {
    header("Location: ../profile.php?error=password_complexity");
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        header("Location: ../profile.php?error=incorrect_current_password");
        exit();
    }
    
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed_password, $_SESSION['user_id']]);
    
    header("Location: ../profile.php?success=password_updated");
    exit();
    
} catch(PDOException $e) {
    header("Location: ../profile.php?error=database_error");
    exit();
}
?>