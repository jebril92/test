<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/sessions-functions.php';

if (!is_logged_in()) {
    header("Location: login.php?message=login_required");
    exit();
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
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - URLink</title>
    
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

    <?php if (!$user): ?>
        <div class="alert alert-danger">Impossible de récupérer vos infos</div>
    <?php else: ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Infos personnelles</h5>
            </div>
            <div class="card-body">
                <p><strong>Nom d’utilisateur :</strong> <?= htmlspecialchars($user['username']); ?></p>
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
                                <a href="edit_link.php?id=<?= $link['id']; ?>" class="btn btn-sm btn-warning me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="delete_url.php?id=<?= $link['id']; ?>&code=<?= $link['short_code']; ?>" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Vous n'avez encore raccourci aucun lien.</p>
        <?php endif; ?>

        <div class="card mb-5 shadow-sm border-danger">
            <div class="card-header bg-white">
                <h5 class="mb-0 text-danger">Supprimer mon compte</h5>
            </div>
            <div class="card-body">
                <p class="text-danger">Cette action est irréversible. Tous vos liens seront supprimés.</p>
                <form method="POST" action="actions/delete_account.php" onsubmit="return confirm('Etes-vous sûr de vouloir supprimer votre compte ?');">
                    <button type="submit" class="btn btn-outline-danger">Supprimer mon compte</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="js/theme-switcher.js"></script>
</body>
</html>
