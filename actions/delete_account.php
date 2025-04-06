<?php
session_start();
require_once '../config/db-config.php';
require_once '../includes/sessions-functions.php';

if (!is_logged_in()) {
    header("Location: ../login.php?message=login_required");
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->beginTransaction();

    $stmt = $conn->prepare("SELECT id FROM shortened_urls WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $links = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($links as $link_id) {
        $stmt = $conn->prepare("DELETE FROM click_stats WHERE url_id = ?");
        $stmt->execute([$link_id]);
    }
    
    $stmt = $conn->prepare("DELETE FROM shortened_urls WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    $stmt = $conn->prepare("DELETE FROM sessions WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    
    $conn->commit();
    
    destroy_session();
    
    header("Location: ../index.php?message=account_deleted");
    exit();
    
} catch(PDOException $e) {
    if ($conn) {
        $conn->rollBack();
    }
    
    header("Location: ../profile.php?error=delete_account_error");
    exit();
}
?>