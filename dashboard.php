<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/sessions-functions.php';

// Vérifier que l'utilisateur est connecté
if (!is_logged_in()) {
    header("Location: login.php?message=login_required");
    exit();
}

// Récupérer les messages d'erreur ou de succès depuis l'URL
$error = isset($_GET['error']) ? $_GET['error'] : '';
$success = isset($_GET['success']) ? $_GET['success'] : '';

// Variables pour les données
$user_links = [];
$total_clicks = 0;
$active_links = 0;
$expired_links = 0;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les informations de l'utilisateur
    $stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Récupérer tous les liens de l'utilisateur
    $stmt = $conn->prepare("
        SELECT s.*, 
               (SELECT COUNT(*) FROM click_stats WHERE url_id = s.id) as clicks_count
        FROM shortened_urls s 
        WHERE s.user_id = ? 
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculer les statistiques
    foreach ($user_links as $link) {
        $total_clicks += $link['clicks_count'];
        
        if (empty($link['expiry_datetime']) || strtotime($link['expiry_datetime']) > time()) {
            $active_links++;
        } else {
            $expired_links++;
        }
    }
    
} catch(PDOException $e) {
    $error = "database_error";
}

// Messages d'erreur et de succès
$error_messages = [
    'url_not_found' => 'Le lien demandé n\'existe pas ou n\'appartient pas à votre compte.',
    'invalid_url' => 'L\'URL fournie est invalide.',
    'database_error' => 'Une erreur de base de données s\'est produite. Veuillez réessayer plus tard.',
    'invalid_params' => 'Paramètres invalides.'
];

$success_messages = [
    'url_deleted' => 'Le lien a été supprimé avec succès.',
    'url_updated' => 'Le lien a été mis à jour avec succès.'
];

// URL de base
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - URLink</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
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
                        <a class="nav-link active" href="dashboard.php">Tableau de bord</a>
                    </li>
                    <?php if ($_SESSION['is_admin'] == 1): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/dashboard.php">Administration</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-3">
                        <div class="dropdown">
                            <a class="btn btn-primary btn-login dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="dashboard.php">Tableau de bord</a></li>
                                <li><a class="dropdown-item" href="profile.php">Mon profil</a></li>
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
        <!-- Messages d'erreur et de succès -->
        <?php if (!empty($error) && isset($error_messages[$error])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_messages[$error]; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success) && isset($success_messages[$success])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $success_messages[$success]; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Bienvenue et statistiques -->
        <div class="user-welcome">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-3">Bonjour, <?php echo htmlspecialchars($_SESSION['username']); ?> !</h1>
                    <p class="text-muted">Gérez vos liens raccourcis et analysez leurs performances.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLinkModal">
                        <i class="fas fa-plus-circle me-2"></i> Créer un nouveau lien
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card bg-primary text-white">
                    <div class="stats-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="stats-number"><?php echo count($user_links); ?></div>
                    <div>Liens raccourcis</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card bg-success text-white">
                    <div class="stats-icon">
                        <i class="fas fa-mouse-pointer"></i>
                    </div>
                    <div class="stats-number"><?php echo $total_clicks; ?></div>
                    <div>Clics totaux</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card bg-info text-white">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number"><?php echo $active_links; ?></div>
                    <div>Liens actifs</div>
                </div>
            </div>
        </div>
        
        <!-- Tableau des liens -->
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Mes liens raccourcis</h5>
            </div>
            <div class="card-body">
                <?php if (empty($user_links)): ?>
                    <div class="text-center py-5">
                        <div class="display-6 text-muted mb-3">
                            <i class="fas fa-link-slash"></i>
                        </div>
                        <h4 class="text-muted">Vous n'avez pas encore créé de liens raccourcis</h4>
                        <p class="mb-4">Commencez par créer votre premier lien raccourci !</p>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newLinkModal">
                            <i class="fas fa-plus-circle me-2"></i> Créer un nouveau lien
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-links" id="linksTable">
                            <thead>
                                <tr>
                                    <th>URL raccourcie</th>
                                    <th>URL originale</th>
                                    <th>Date de création</th>
                                    <th>Expiration</th>
                                    <th>Clics</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_links as $link): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo $base_url . htmlspecialchars($link['short_code']); ?>" target="_blank">
                                                <?php echo $base_url . htmlspecialchars($link['short_code']); ?>
                                            </a>
                                            <button class="btn btn-sm btn-link text-primary p-0 ms-2" onclick="copyToClipboard('<?php echo $base_url . htmlspecialchars($link['short_code']); ?>')">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </td>
                                        <td class="original-url">
                                            <a href="<?php echo htmlspecialchars($link['original_url']); ?>" target="_blank" title="<?php echo htmlspecialchars($link['original_url']); ?>">
                                                <?php echo htmlspecialchars($link['original_url']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($link['created_at'])); ?></td>
                                        <td>
                                            <?php if (empty($link['expiry_datetime'])): ?>
                                                <span class="text-muted">Jamais</span>
                                            <?php else: ?>
                                                <?php echo date('d/m/Y H:i', strtotime($link['expiry_datetime'])); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $link['clicks_count']; ?></td>
                                        <td>
                                            <?php if (empty($link['expiry_datetime']) || strtotime($link['expiry_datetime']) > time()): ?>
                                                <span class="status-badge bg-success bg-opacity-25 text-success">Actif</span>
                                            <?php else: ?>
                                                <span class="status-badge bg-danger bg-opacity-25 text-danger">Expiré</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="stats.php?id=<?php echo $link['id']; ?>" class="btn btn-sm btn-primary btn-icon" title="Statistiques">
                                                    <i class="fas fa-chart-bar"></i>
                                                </a>
                                                <a href="edit_link.php?id=<?php echo $link['id']; ?>" class="btn btn-sm btn-info btn-icon text-white" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo $base_url . htmlspecialchars($link['short_code']); ?>-" class="btn btn-sm btn-danger btn-icon" title="Supprimer">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal pour créer un nouveau lien -->
    <div class="modal fade" id="newLinkModal" tabindex="-1" aria-labelledby="newLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newLinkModalLabel">Créer un nouveau lien raccourci</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalError" class="alert alert-danger" style="display: none;"></div>
                    <form id="newLinkForm">
                        <div class="mb-3">
                            <label for="original_url" class="form-label">URL à raccourcir</label>
                            <input type="url" class="form-control" id="original_url" name="url" placeholder="https://example.com/lien-tres-long" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="custom_code" class="form-label">Code personnalisé (optionnel)</label>
                            <input type="text" class="form-control" id="custom_code" name="custom_code" placeholder="moncode" pattern="[a-zA-Z0-9]+" title="Uniquement des lettres et des chiffres">
                            <div class="form-text">Laissez vide pour générer automatiquement</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="expiry" class="form-label">Durée de validité</label>
                            <select class="form-select" id="expiry" name="expiry">
                                <option value="">Pas d'expiration</option>
                                <option value="24">24 heures</option>
                                <option value="168">7 jours</option>
                                <option value="720">30 jours</option>
                                <option value="8760">1 an</option>
                            </select>
                        </div>
                    </form>
                    
                    <div id="newLinkResult" style="display: none;">
                        <div class="alert alert-success">
                            <strong>Succès !</strong> Votre lien a été raccourci.
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Votre lien raccourci :</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="shortened_url" readonly>
                                <button class="btn btn-outline-primary" type="button" onclick="copyNewLink()">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" id="createLinkBtn">Créer le lien</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>