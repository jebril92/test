<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/sessions-functions.php';

if (!is_logged_in()) {
    header("Location: login.php?message=login_required");
    exit();
}

$error_message = "";
$success_message = "";

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_fields':
            $error_message = "Veuillez remplir tous les champs.";
            break;
        case 'password_mismatch':
            $error_message = "Les nouveaux mots de passe ne correspondent pas.";
            break;
        case 'password_too_short':
            $error_message = "Le mot de passe doit contenir au moins 8 caractères.";
            break;
        case 'password_complexity':
            $error_message = "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.";
            break;
        case 'incorrect_current_password':
            $error_message = "Le mot de passe actuel est incorrect.";
            break;
        case 'database_error':
            $error_message = "Une erreur de base de données s'est produite. Veuillez réessayer plus tard.";
            break;
        case 'delete_account_error':
            $error_message = "Une erreur s'est produite lors de la suppression du compte. Veuillez réessayer.";
            break;
        default:
            $error_message = "Une erreur s'est produite. Veuillez réessayer.";
    }
}

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'password_updated':
            $success_message = "Votre mot de passe a été mis à jour avec succès.";
            break;
        default:
            $success_message = "Opération réussie.";
    }
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT * FROM shortened_urls WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_links = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Erreur de base de données : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - URLink</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
    <link href="css/dark-theme.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">
</head>
<body>
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
                                <li><a class="dropdown-item active" href="profile.php">Mon profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="login.php?logout=true">Déconnexion</a></li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item ms-2">
                        <button id="theme-toggle" class="btn btn-link nav-link theme-toggle-icon" aria-label="Changer de thème">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container mt-5 pt-5">
    <div class="row mb-4">
        <div class="col">
            <h1>Mon profil</h1>
            <p class="text-muted">Gérez vos infos personnelles ici.</p>
        </div>
    </div>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!$user): ?>
        <div class="alert alert-danger">Impossible de récupérer vos infos</div>
    <?php else: ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Infos personnelles</h5>
            </div>
            <div class="card-body">
                <p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($user['username']); ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($user['email']); ?></p>
                <p><strong>Membre depuis :</strong> <?= date('d/m/Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>

        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Changer de mot de passe</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="actions/update_password.php">
                    <div class="mb-3">
                        <label class="form-label">Mot de passe actuel</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control" required>
                        <div class="form-text">Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </form>
            </div>
        </div>

        <?php if (!empty($user_links)): ?>
            <h2 class="mt-4">Mes liens raccourcis</h2>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Original</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_links as $link): ?>
                            <tr>
                                <td><?= htmlspecialchars($link['short_code']); ?></td>
                                <td>
                                    <a href="<?= htmlspecialchars($link['original_url']); ?>" target="_blank">
                                        <?= strlen($link['original_url']) > 40 ? substr($link['original_url'], 0, 40) . '...' : $link['original_url']; ?>
                                    </a>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($link['created_at'])); ?></td>
                                <td>
                                    <a href="edit_link.php?id=<?= $link['id']; ?>" class="btn btn-sm btn-warning me-1" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="stats.php?id=<?= $link['id']; ?>" class="btn btn-sm btn-info me-1" title="Statistiques">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <a href="delete_url.php?id=<?= $link['id']; ?>&code=<?= $link['short_code']; ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lien ?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                <i class="fas fa-info-circle me-2"></i> Vous n'avez encore raccourci aucun lien.
                <a href="dashboard.php" class="alert-link">Cliquez ici</a> pour créer votre premier lien raccourci.
            </div>
        <?php endif; ?>

        <div class="card mb-5 shadow-sm border-danger">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-danger">Supprimer mon compte</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i> Attention ! Cette action est irréversible. Tous vos liens raccourcis et les statistiques associées seront définitivement supprimés.
                </div>
                <form method="POST" action="actions/delete_account.php" onsubmit="return confirm('Êtes-vous vraiment sûr de vouloir supprimer votre compte ? Cette action est irréversible et toutes vos données seront perdues.');">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i> Supprimer définitivement mon compte
                    </button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="js/theme-switcher.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.querySelector('form[action="actions/update_password.php"]');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = this.querySelector('input[name="new_password"]').value;
            const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas.');
                return false;
            }
            
            const hasUpperCase = /[A-Z]/.test(newPassword);
            const hasLowerCase = /[a-z]/.test(newPassword);
            const hasNumbers = /[0-9]/.test(newPassword);
            const hasSpecial = /[^A-Za-z0-9]/.test(newPassword);
            
            if (newPassword.length < 8 || !hasUpperCase || !hasLowerCase || !hasNumbers || !hasSpecial) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.');
                return false;
            }
            
            return true;
        });
    }
});
</script>
</body>
</html>