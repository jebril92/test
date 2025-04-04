<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/sessions-functions.php';

// Vérifier que l'utilisateur est connecté
if (!is_logged_in()) {
    header("Location: login.php?message=login_required");
    exit();
}

// Récupérer l'ID de l'URL à supprimer et le code court pour la vérification
$url_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$confirmation = isset($_GET['confirm']) && $_GET['confirm'] == 'yes';

if ($url_id <= 0 || empty($code)) {
    header("Location: dashboard.php?error=invalid_params");
    exit();
}

$error = "";
$url_info = null;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier que l'URL existe et appartient à l'utilisateur
    $stmt = $conn->prepare("
        SELECT s.*, u.username 
        FROM shortened_urls s 
        JOIN users u ON s.user_id = u.id 
        WHERE s.id = ? AND s.short_code = ? AND (s.user_id = ? OR ? = 1)
    ");
    $stmt->execute([$url_id, $code, $_SESSION['user_id'], $_SESSION['is_admin']]);
    $url_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$url_info) {
        header("Location: dashboard.php?error=url_not_found");
        exit();
    }
    
    // Si l'utilisateur a confirmé la suppression
    if ($confirmation) {
        // Supprimer les statistiques associées
        $stmt = $conn->prepare("DELETE FROM click_stats WHERE url_id = ?");
        $stmt->execute([$url_id]);
        
        // Supprimer l'URL
        $stmt = $conn->prepare("DELETE FROM shortened_urls WHERE id = ?");
        $stmt->execute([$url_id]);
        
        // Rediriger vers le tableau de bord avec un message de succès
        header("Location: dashboard.php?success=url_deleted");
        exit();
    }
    
} catch(PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
$short_url = $base_url . $url_info['short_code'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer le lien - URLink</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
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
                    <div class="card-header bg-danger text-white">
                        <h1 class="h4 mb-0">Supprimer le lien raccourci</h1>
                    </div>
                    <div class="card-body">
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <div class="display-1 text-danger mb-3">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <h2>Êtes-vous sûr de vouloir supprimer ce lien ?</h2>
                            <p class="text-muted">Cette action est irréversible et supprimera définitivement le lien raccourci ainsi que toutes ses statistiques associées.</p>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>URL raccourcie :</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <a href="<?php echo $short_url; ?>" target="_blank"><?php echo $short_url; ?></a>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>URL originale :</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <a href="<?php echo htmlspecialchars($url_info['original_url']); ?>" target="_blank">
                                            <?php 
                                            $display_url = htmlspecialchars($url_info['original_url']);
                                            echo (strlen($display_url) > 50) ? substr($display_url, 0, 50) . '...' : $display_url; 
                                            ?>
                                        </a>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Date de création :</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <?php echo date('d/m/Y H:i', strtotime($url_info['created_at'])); ?>
                                    </div>
                                </div>
                                <?php if (!empty($url_info['expiry_datetime'])): ?>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Date d'expiration :</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <?php echo date('d/m/Y H:i', strtotime($url_info['expiry_datetime'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <hr>
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Statistiques :</strong>
                                    </div>
                                    <div class="col-md-8">
                                        <?php
                                        try {
                                            $stmt = $conn->prepare("SELECT COUNT(*) FROM click_stats WHERE url_id = ?");
                                            $stmt->execute([$url_id]);
                                            echo $stmt->fetchColumn() . " clics au total";
                                        } catch(PDOException $e) {
                                            echo "Erreur lors de la récupération des statistiques";
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            <a href="dashboard.php" class="btn btn-secondary me-3">
                                <i class="fas fa-arrow-left me-1"></i> Annuler
                            </a>
                            <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $url_id . "&code=" . $code . "&confirm=yes"); ?>" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-1"></i> Confirmer la suppression
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>