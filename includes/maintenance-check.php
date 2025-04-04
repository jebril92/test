<?php
// Vérification du mode maintenance
function is_maintenance_mode() {
    global $conn, $host, $dbname, $username_db, $password_db;
    
    try {
        // Si la connexion n'existe pas déjà
        if (!isset($conn)) {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        // Vérifier si la table settings existe
        $stmt = $conn->query("SHOW TABLES LIKE 'settings'");
        if ($stmt->rowCount() > 0) {
            // Récupérer le paramètre de mode maintenance
            $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return (bool)$stmt->fetchColumn();
            }
        }
    } catch(PDOException $e) {
        // En cas d'erreur, supposer que le site n'est pas en maintenance
        error_log("Erreur lors de la vérification du mode maintenance: " . $e->getMessage());
    }
    
    return false;
}

// Vérifier si l'utilisateur actuel est un administrateur
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Rediriger vers la page de maintenance si nécessaire
function check_maintenance_mode() {
    // Si le site est en maintenance et l'utilisateur n'est pas un admin
    if (is_maintenance_mode() && !is_admin()) {
        // Sauvegarder l'URL demandée pour y retourner après
        $_SESSION['redirect_after_maintenance'] = $_SERVER['REQUEST_URI'];
        
        // Rediriger vers la page de maintenance
        header("Location: /maintenance.php");
        exit();
    }
}
?>