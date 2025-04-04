<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/sessions-functions.php';

// Vérifier que l'utilisateur est connecté
if (!is_logged_in()) {
    header("Location: login.php?message=login_required");
    exit();
}

// Récupérer l'ID de l'URL à éditer
$url_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($url_id <= 0) {
    header("Location: dashboard.php?error=invalid_url");
    exit();
}

$error = "";
$success = false;
$url_info = null;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier que l'URL existe et appartient à l'utilisateur
    $stmt = $conn->prepare("
        SELECT * FROM shortened_urls 
        WHERE id = ? AND (user_id = ? OR ? = 1)
    ");
    $stmt->execute([$url_id, $_SESSION['user_id'], $_SESSION['is_admin']]);
    $url_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$url_info) {
        header("Location: dashboard.php?error=url_not_found");
        exit();
    }
    
    // Traitement du formulaire de modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $original_url = trim($_POST['original_url']);
        $short_code = trim($_POST['short_code']);
        $expiry = isset($_POST['expiry']) ? trim($_POST['expiry']) : null;
        
        if (empty($original_url)) {
            $error = "L'URL originale ne peut pas être vide.";
        } elseif (empty($short_code)) {
            $error = "Le code court ne peut pas être vide.";
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $short_code)) {
            $error = "Le code court ne peut contenir que des lettres et des chiffres.";
        } else {
            // Vérifier si le code court existe déjà (sauf s'il s'agit du même lien)
            $stmt = $conn->prepare("
                SELECT COUNT(*) FROM shortened_urls 
                WHERE short_code = ? AND id != ?
            ");
            $stmt->execute([$short_code, $url_id]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = "Ce code court est déjà utilisé. Veuillez en choisir un autre.";
            } else {
                // Définir la date d'expiration
                $expiryDatetime = null;
                if (!empty($expiry)) {
                    $expiryDatetime = date('Y-m-d H:i:s', strtotime("+{$expiry} hours"));
                }
                
                // Mettre à jour l'URL dans la base de données
                $stmt = $conn->prepare("
                    UPDATE shortened_urls 
                    SET original_url = ?, short_code = ?, expiry_datetime = ?
                    WHERE id = ?
                ");
                
                $stmt->execute([$original_url, $short_code, $expiryDatetime, $url_id]);
                
                $success = true;
                
                // Mettre à jour les informations de l'URL
                $stmt = $conn->prepare("SELECT * FROM shortened_urls WHERE id = ?");
                $stmt->execute([$url_id]);
                $url_info = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
    }
    
} catch(PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le lien - URLink</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-link me-2"></i>
                URLink
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Tableau de bord</a>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <div class="dropdown">
                            <a class="btn btn-primary btn-login dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Mon compte
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="dashboard.php">Tableau de bord</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="login.php?logout=true">Déconnexion</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h1 class="h4 mb-0">Modifier le lien raccourci</h1>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Le lien a été modifié avec succès !
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $url_id); ?>">
                            <div class="mb-3">
                                <label for="original_url" class="form-label">URL originale</label>
                                <input type="url" class="form-control" id="original_url" name="original_url" value="<?php echo htmlspecialchars($url_info['original_url']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="short_code" class="form-label">Code court</label>
                                <div class="input-group">
                                    <span class="input-group-text"><?php echo $base_url; ?></span>
                                    <input type="text" class="form-control" id="short_code" name="short_code" value="<?php echo htmlspecialchars($url_info['short_code']); ?>" pattern="[a-zA-Z0-9]+" title="Uniquement des lettres et des chiffres" required>
                                </div>
                                <div class="form-text">Utilisez uniquement des lettres et des chiffres (a-z, A-Z, 0-9)</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="expiry" class="form-label">Date d'expiration</label>
                                <select class="form-select" id="expiry" name="expiry">
                                    <option value="" <?php echo (empty($url_info['expiry_datetime'])) ? 'selected' : ''; ?>>Pas d'expiration</option>
                                    <option value="24" <?php echo (!empty($url_info['expiry_datetime']) && strtotime($url_info['expiry_datetime']) - strtotime($url_info['created_at']) <= 86400) ? 'selected' : ''; ?>>24 heures</option>
                                    <option value="168" <?php echo (!empty($url_info['expiry_datetime']) && strtotime($url_info['expiry_datetime']) - strtotime($url_info['created_at']) <= 604800) ? 'selected' : ''; ?>>7 jours</option>
                                    <option value="720" <?php echo (!empty($url_info['expiry_datetime']) && strtotime($url_info['expiry_datetime']) - strtotime($url_info['created_at']) <= 2592000) ? 'selected' : ''; ?>>30 jours</option>
                                    <option value="8760" <?php echo (!empty($url_info['expiry_datetime']) && strtotime($url_info['expiry_datetime']) - strtotime($url_info['created_at']) <= 31536000) ? 'selected' : ''; ?>>1 an</option>
                                </select>
                                <?php if (!empty($url_info['expiry_datetime'])): ?>
                                    <div class="form-text text-info">
                                        Date d'expiration actuelle: <?php echo date('d/m/Y H:i', strtotime($url_info['expiry_datetime'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Informations complémentaires</label>
                                <div class="card">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <strong>Date de création:</strong> <?php echo date('d/m/Y H:i', strtotime($url_info['created_at'])); ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Nombre total de clics:</strong> 
                                            <?php
                                            try {
                                                $stmt = $conn->prepare("SELECT COUNT(*) FROM click_stats WHERE url_id = ?");
                                                $stmt->execute([$url_id]);
                                                echo $stmt->fetchColumn();
                                            } catch(PDOException $e) {
                                                echo "Erreur";
                                            }
                                            ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="stats.php?id=<?php echo $url_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-chart-bar me-1"></i> Voir les statistiques
                                </a>
                                <div>
                                    <a href="dashboard.php" class="btn btn-outline-secondary me-2">Annuler</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>