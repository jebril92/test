<?php
session_start();
require_once 'config/db-config.php';

$request_uri = $_SERVER['REQUEST_URI'];
$path_parts = explode('/', trim($request_uri, '/'));
$code = isset($path_parts[0]) ? trim($path_parts[0]) : '';

$action = '';
if (substr($code, -1) === '+') {
    $action = '+';
    $code = substr($code, 0, -1);
} elseif (substr($code, -1) === '*') {
    $action = '*';
    $code = substr($code, 0, -1);
} elseif (substr($code, -1) === '-') {
    $action = '-';
    $code = substr($code, 0, -1);
}

if (empty($code)) {
    header("Location: index.php");
    exit();
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("
        SELECT id, original_url, short_code, user_id, created_at, expiry_datetime 
        FROM shortened_urls 
        WHERE short_code = ? AND (expiry_datetime IS NULL OR expiry_datetime > NOW())
    ");
    $stmt->execute([$code]);
    $url_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$url_info) {
        header("Location: index.php?error=url_not_found");
        exit();
    }
    
    $current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    switch ($action) {
        case '+':
            if ($current_user_id == $url_info['user_id'] || $url_info['user_id'] == 1) {
                displayOriginalUrl($url_info);
                exit();
            } else {
                header("Location: login.php?message=unauthorized");
                exit();
            }
            break;
            
        case '*':
            if ($current_user_id == $url_info['user_id']) {
                header("Location: stats.php?id=" . $url_info['id']);
            } else {
                header("Location: login.php?message=unauthorized_stats");
            }
            exit();
            break;
            
        case '-':
            if ($current_user_id == $url_info['user_id']) {
                header("Location: delete_url.php?id=" . $url_info['id'] . "&code=" . $code);
            } else {
                header("Location: login.php?message=unauthorized_delete");
            }
            exit();
            break;
            
        default:
            recordClick($conn, $url_info['id']);
        
            header("Location: " . $url_info['original_url']);
            exit();
    }
} catch(PDOException $e) {

    header("Location: index.php?error=database_error");
    exit();
}

function displayOriginalUrl($url_info) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>URLink - Détails du lien</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link href="css/styles.css" rel="stylesheet">
        <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
        <link rel="manifest" href="favicon/site.webmanifest">
    </head>
    <body>
        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h3 class="m-0"><i class="fas fa-link me-2"></i> Détails du lien raccourci</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h5>URL courte :</h5>
                                <div class="input-group">
                                    <?php
                                    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
                                    $shortUrl = $baseUrl . $url_info['short_code'];
                                    ?>
                                    <input type="text" class="form-control" value="<?php echo $shortUrl; ?>" readonly>
                                    <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('<?php echo $shortUrl; ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h5>URL originale :</h5>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($url_info['original_url']); ?>" readonly>
                                    <button class="btn btn-outline-primary" type="button" onclick="copyToClipboard('<?php echo htmlspecialchars($url_info['original_url']); ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h5>Date de création :</h5>
                                <p class="form-control"><?php echo date("d/m/Y H:i:s", strtotime($url_info['created_at'])); ?></p>
                            </div>
                            
                            <?php if (!empty($url_info['expiry_datetime'])): ?>
                            <div class="mb-4">
                                <h5>Date d'expiration :</h5>
                                <p class="form-control"><?php echo date("d/m/Y H:i:s", strtotime($url_info['expiry_datetime'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                <a href="<?php echo $url_info['original_url']; ?>" class="btn btn-primary" target="_blank">
                                    <i class="fas fa-external-link-alt me-2"></i> Visiter le lien
                                </a>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-home me-2"></i> Retour à l'accueil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert("Lien copié dans le presse-papiers !");
            }, function() {
                alert("Impossible de copier le lien. Votre navigateur ne supporte peut-être pas cette fonctionnalité.");
            });
        }
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit();
}

function recordClick($conn, $url_id) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO click_stats (url_id, ip_address, user_agent, referer)
            VALUES (:url_id, :ip_address, :user_agent, :referer)
        ");
        
        $stmt->bindParam(':url_id', $url_id, PDO::PARAM_INT);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);
        $stmt->bindParam(':referer', $referer);
        
        $stmt->execute();
    } catch(PDOException $e) {
        error_log("Erreur lors de l'enregistrement du clic: " . $e->getMessage());
    }
}