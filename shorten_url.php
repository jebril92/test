<?php
header('Content-Type: application/json');
session_start();
require_once 'config/db-config.php';

// Vérifier si les données ont été envoyées en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
    exit;
}

// Récupérer l'URL longue
$originalUrl = isset($_POST['url']) ? trim($_POST['url']) : '';
$customCode = isset($_POST['custom_code']) ? trim($_POST['custom_code']) : null;
$expiry = isset($_POST['expiry']) ? intval($_POST['expiry']) : null;

// Vérifier que l'URL n'est pas vide
if (empty($originalUrl)) {
    echo json_encode(['status' => 'error', 'message' => 'L\'URL ne peut pas être vide']);
    exit;
}

// Récupérer l'ID utilisateur s'il est connecté, sinon utiliser l'ID anonyme (1)
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

// Si un code personnalisé est demandé, vérifier que l'utilisateur est connecté
if (!empty($customCode) && $userId == 1) {
    echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté pour créer un code personnalisé']);
    exit;
}

// Vérifier que le code personnalisé ne contient que des caractères alphanumériques
if (!empty($customCode) && !preg_match('/^[a-zA-Z0-9]+$/', $customCode)) {
    echo json_encode(['status' => 'error', 'message' => 'Le code personnalisé ne peut contenir que des lettres et des chiffres']);
    exit;
}

try {
    // Créer la connexion PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Générer ou utiliser le code court
    if (!empty($customCode)) {
        $shortCode = $customCode;
        
        // Vérifier si le code personnalisé existe déjà
        $stmt = $conn->prepare("SELECT COUNT(*) FROM shortened_urls WHERE short_code = ?");
        $stmt->execute([$shortCode]);
        
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Ce code personnalisé est déjà utilisé']);
            exit;
        }
    } else {
        // Générer un code court aléatoire
        $shortCode = generateUniqueCode($conn);
    }
    
    // Définir la date d'expiration
    $expiryDatetime = null;
    if ($expiry !== null) {
        $expiryDatetime = date('Y-m-d H:i:s', strtotime("+{$expiry} hours"));
    }
    
    // Insérer l'URL dans la base de données
    $stmt = $conn->prepare("
        INSERT INTO shortened_urls (original_url, short_code, user_id, created_at, expiry_datetime)
        VALUES (:original_url, :short_code, :user_id, NOW(), :expiry_datetime)
    ");
    
    $stmt->bindParam(':original_url', $originalUrl);
    $stmt->bindParam(':short_code', $shortCode);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':expiry_datetime', $expiryDatetime);
    
    $stmt->execute();
    
    // Récupérer l'ID de l'URL insérée
    $urlId = $conn->lastInsertId();
    
    // Récupérer les détails de l'URL raccourcie
    $stmt = $conn->prepare("
        SELECT id, original_url, short_code, created_at, expiry_datetime
        FROM shortened_urls
        WHERE id = ?
    ");
    $stmt->execute([$urlId]);
    $urlInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Construire l'URL complète
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
    $shortUrl = $baseUrl . $urlInfo['short_code'];
    
    // Renvoyer le résultat
    echo json_encode([
        'status' => 'success',
        'original_url' => $originalUrl,
        'short_url' => $shortUrl,
        'short_code' => $urlInfo['short_code'],
        'created_at' => $urlInfo['created_at'],
        'expiry' => $urlInfo['expiry_datetime']
    ]);
    
} catch(PDOException $e) {
    // En mode développement, vous pouvez renvoyer le message d'erreur complet
    echo json_encode(['status' => 'error', 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
    exit;
}

/**
 * Génère un code court unique
 * 
 * @param PDO $conn Connexion à la base de données
 * @param int $length Longueur du code à générer
 * @return string Code court unique
 */
function generateUniqueCode($conn, $length = 6) {
    $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $maxTentatives = 10;
    
    for ($tentative = 0; $tentative < $maxTentatives; $tentative++) {
        $longueurActuelle = $length + $tentative;
        
        $code = '';
        for ($i = 0; $i < $longueurActuelle; $i++) {
            $code .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        
        // Vérifier si le code existe déjà
        $stmt = $conn->prepare("SELECT COUNT(*) FROM shortened_urls WHERE short_code = ?");
        $stmt->execute([$code]);
        
        if ($stmt->fetchColumn() == 0) {
            return $code;
        }
    }
    
    // Si génération impossible après plusieurs tentatives, utiliser un timestamp
    return substr(base_convert(time(), 10, 36), 0, $length);
}