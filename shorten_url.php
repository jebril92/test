<?php
header('Content-Type: application/json');
session_start();
require_once 'config/db-config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
    exit;
}

$originalUrl = isset($_POST['url']) ? trim($_POST['url']) : '';
$customCode = isset($_POST['custom_code']) ? trim($_POST['custom_code']) : null;
$expiry = isset($_POST['expiry']) && !empty($_POST['expiry']) ? intval($_POST['expiry']) : null;

if (empty($originalUrl)) {
    echo json_encode(['status' => 'error', 'message' => 'L\'URL ne peut pas être vide']);
    exit;
}

$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

if (!empty($customCode) && $userId == 1) {
    echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté pour créer un code personnalisé']);
    exit;
}

if (!empty($customCode) && !preg_match('/^[a-zA-Z0-9]+$/', $customCode)) {
    echo json_encode(['status' => 'error', 'message' => 'Le code personnalisé ne peut contenir que des lettres et des chiffres']);
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $conn->exec("SET time_zone = '+02:00'");
    
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    if ($userId == 1) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM shortened_urls 
            WHERE user_id = 1 
            AND ip_address = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute([$ip_address]);
        $link_count = $stmt->fetchColumn();
        
        $max_links_per_day = 10;
        
        if ($link_count >= $max_links_per_day) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Limite quotidienne atteinte. Veuillez vous connecter pour créer plus de liens ou réessayez demain.'
            ]);
            exit;
        }
        
        $ip_track = $ip_address;
    } else {
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'max_links_per_user'");
        $stmt->execute();
        $max_links_per_user = (int) $stmt->fetchColumn();
        
        if ($max_links_per_user > 0) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM shortened_urls WHERE user_id = ?");
            $stmt->execute([$userId]);
            $user_links_count = $stmt->fetchColumn();
            
            if ($user_links_count >= $max_links_per_user) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => "Vous avez atteint votre limite de {$max_links_per_user} liens. Veuillez contacter l'administrateur pour obtenir plus de crédits."
                ]);
                exit;
            }
        }
        
        $ip_track = null;
    }
    
    if (!empty($customCode)) {
        $shortCode = $customCode;
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM shortened_urls WHERE short_code = ?");
        $stmt->execute([$shortCode]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Ce code personnalisé est déjà utilisé']);
            exit;
        }
    } else {
        $shortCode = generateUniqueCode($conn);
    }
    
    $expiryDatetime = null;
    
    if ($userId == 1) {
        $expiryDatetime = date('Y-m-d H:i:s', strtotime("+2 hours"));
    } else {
        if ($expiry !== null) {
            $expiryDatetime = date('Y-m-d H:i:s', strtotime("+{$expiry} hours"));
        }
    }
    
    $stmt = $conn->prepare("
        INSERT INTO shortened_urls (original_url, short_code, user_id, ip_address, created_at, expiry_datetime)
        VALUES (:original_url, :short_code, :user_id, :ip_address, NOW(), :expiry_datetime)
    ");
    
    $stmt->bindParam(':original_url', $originalUrl);
    $stmt->bindParam(':short_code', $shortCode);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':ip_address', $ip_track);
    
    if ($expiryDatetime === null) {
        $stmt->bindValue(':expiry_datetime', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':expiry_datetime', $expiryDatetime);
    }
    
    $stmt->execute();
    
    $urlId = $conn->lastInsertId();
    
    $stmt = $conn->prepare("
        SELECT id, original_url, short_code, created_at, expiry_datetime
        FROM shortened_urls
        WHERE id = ?
    ");
    $stmt->execute([$urlId]);
    $urlInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
    $shortUrl = $baseUrl . $urlInfo['short_code'];
    
    echo json_encode([
        'status' => 'success',
        'original_url' => $originalUrl,
        'short_url' => $shortUrl,
        'short_code' => $urlInfo['short_code'],
        'created_at' => $urlInfo['created_at'],
        'expiry' => $urlInfo['expiry_datetime']
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
    exit;
}

function generateUniqueCode($conn, $length = 6) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $maxTentatives = 10;
    
    for ($tentative = 0; $tentative < $maxTentatives; $tentative++) {
        $longueurActuelle = $length + $tentative;
        
        $code = '';
        for ($i = 0; $i < $longueurActuelle; $i++) {
            $code .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM shortened_urls WHERE short_code = ?");
        $stmt->execute([$code]);
        
        if ($stmt->fetchColumn() == 0) {
            return $code;
        }
    }
    
    return substr(base_convert(time(), 10, 36), 0, $length);
}
?>