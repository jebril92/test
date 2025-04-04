<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/sessions-functions.php';

if (!is_logged_in(true)) {
    header("Location: ../login.php?message=unauthorized");
    exit();
}

$total_users = 0;
$active_users = 0;
$total_links = 0;
$total_clicks = 0;
$recent_users = [];
$recent_links = [];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Statistiques utilisateurs
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();
    
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $active_users = $stmt->fetchColumn();
    
    // Statistiques liens
    $stmt = $conn->query("SELECT COUNT(*) FROM shortened_urls");
    $total_links = $stmt->fetchColumn();
    
    $stmt = $conn->query("SELECT COUNT(*) FROM click_stats");
    $total_clicks = $stmt->fetchColumn();
    
    // Utilisateurs récents
    $stmt = $conn->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Liens récents
    $stmt = $conn->query("
        SELECT s.*, u.username 
        FROM shortened_urls s 
        JOIN users u ON s.user_id = u.id 
        ORDER BY s.created_at DESC LIMIT 5
    ");
    $recent_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

// URL de base
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
?>

<?php include 'includes/header.php'; ?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <h1>Tableau de bord administrateur</h1>
            <p class="text-muted">Bienvenue dans le panel d'administration de URLink.</p>
        </div>
    </div>
    
    <!-- Statistiques globales -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Utilisateurs</h5>
                    <div class="d-flex align-items-center">
                        <div class="icon-stat">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="ml-auto">
                            <h2 class="mb-0"><?php echo $total_users; ?></h2>
                            <span>Total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Utilisateurs actifs</h5>
                    <div class="d-flex align-items-center">
                        <div class="icon-stat">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="ml-auto">
                            <h2 class="mb-0"><?php echo $active_users; ?></h2>
                            <span>Derniers 30 jours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Liens raccourcis</h5>
                    <div class="d-flex align-items-center">
                        <div class="icon-stat">
                            <i class="fas fa-link"></i>
                        </div>
                        <div class="ml-auto">
                            <h2 class="mb-0"><?php echo $total_links; ?></h2>
                            <span>Total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Clics</h5>
                    <div class="d-flex align-items-center">
                        <div class="icon-stat">
                            <i class="fas fa-mouse-pointer"></i>
                        </div>
                        <div class="ml-auto">
                            <h2 class="mb-0"><?php echo $total_clicks; ?></h2>
                            <span>Total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Graphiques et tableaux -->
    <div class="row mt-4">
        <!-- Derniers utilisateurs inscrits -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Derniers utilisateurs inscrits</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom d'utilisateur</th>
                                    <th>Email</th>
                                    <th>Date d'inscription</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="users.php" class="btn btn-outline-primary btn-sm">Voir tous les utilisateurs</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Derniers liens raccourcis -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Derniers liens raccourcis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>URL originale</th>
                                    <th>Créé par</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_links as $link): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $base_url . htmlspecialchars($link['short_code']); ?>" target="_blank">
                                            <?php echo htmlspecialchars($link['short_code']); ?>
                                        </a>
                                    </td>
                                    <td class="text-truncate" style="max-width: 200px;">
                                        <a href="<?php echo htmlspecialchars($link['original_url']); ?>" target="_blank" title="<?php echo htmlspecialchars($link['original_url']); ?>">
                                            <?php echo htmlspecialchars($link['original_url']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($link['username']); ?></td>
                                    <td>
                                        <a href="links.php?action=edit&id=<?php echo $link['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <a href="links.php" class="btn btn-outline-primary btn-sm">Voir tous les liens</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>