<?php
require_once __DIR__ . '/../config/db-config.php';

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
        error_log("Erreur lors de l'enregistrement de la session : " . $e->getMessage());
    }
}

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

function get_user_role() {
    if (!isset($_SESSION['user_id'])) {
        return 'guest';
    }

    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 ? 'admin' : 'user';
}