<?php
function is_maintenance_mode() {
    global $conn, $host, $dbname, $username_db, $password_db;
    
    try {
        if (!isset($conn)) {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        $stmt = $conn->query("SHOW TABLES LIKE 'settings'");
        if ($stmt->rowCount() > 0) {
            $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return (bool)$stmt->fetchColumn();
            }
        }
    } catch(PDOException $e) {
        error_log("Erreur lors de la vérification du mode maintenance: " . $e->getMessage());
    }
    
    return false;
}

function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

function check_maintenance_mode() {
    if (is_maintenance_mode() && !is_admin()) {
        $_SESSION['redirect_after_maintenance'] = $_SERVER['REQUEST_URI'];
        
        header("Location: /maintenance.php");
        exit();
    }
}
?>