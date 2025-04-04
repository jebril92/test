<?php
require_once __DIR__ . '/../config/db-config.php';

/**
 * Crée une session sécurisée pour l'utilisateur
 * 
 * @param int $user_id ID de l'utilisateur
 * @param string $username Nom d'utilisateur
 * @param string $email Email de l'utilisateur
 * @param int $is_admin Indique si l'utilisateur est un admin
 */
function create_secure_session($user_id, $username, $email, $is_admin) {
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['is_admin'] = $is_admin;
    
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    
    try {
        $conn = new PDO("mysql:host=$GLOBALS[host];dbname=$GLOBALS[dbname]", $GLOBALS['username_db'], $GLOBALS['password_db']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("
            INSERT INTO sessions (user_id, ip_address, user_agent, payload, last_activity) 
            VALUES (:user_id, :ip_address, :user_agent, :payload, NOW())
        ");
        
        $stmt->execute([
            ':user_id' => $user_id,
            ':ip_address' => $_SESSION['ip_address'],
            ':user_agent' => $_SESSION['user_agent'],
            ':payload' => serialize($_SESSION)
        ]);
    } catch(PDOException $e) {
        // Log de l'erreur sans l'afficher à l'utilisateur
        error_log("Erreur lors de l'enregistrement de la session : " . $e->getMessage());
    }
}

/**
 * Vérifie si l'utilisateur est connecté
 * 
 * @param bool $admin_only Vérifie si l'utilisateur est un admin
 * @return bool
 */
function is_logged_in($admin_only = false) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    if ($admin_only && (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1)) {
        return false;
    }
    
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        destroy_session();
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    
    if ($admin_only && (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1)) {
        return false;
    }
    
    return true;
}

/**
 * Détruit la session de l'utilisateur
 */
function destroy_session() {
    if (isset($_SESSION['user_id'])) {
        try {
            $conn = new PDO("mysql:host=$GLOBALS[host];dbname=$GLOBALS[dbname]", $GLOBALS['username_db'], $GLOBALS['password_db']);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("DELETE FROM sessions WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
        } catch(PDOException $e) {
            error_log("Erreur lors de la suppression de la session : " . $e->getMessage());
        }
    }
    
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();  
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_unset();
    session_destroy();
}

/**
 * Redirige l'utilisateur en fonction de son rôle
 */
function redirect_by_role() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
    
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

/**
 * Vérifie et récupère le rôle de l'utilisateur
 * 
 * @return string 'admin', 'user', ou 'guest'
 */
function get_user_role() {
    if (!isset($_SESSION['user_id'])) {
        return 'guest';
    }

    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 ? 'admin' : 'user';
}