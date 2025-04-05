<?php
session_start();
require_once '../config/db-config.php';
require_once '../includes/sessions-functions.php';

if (!is_logged_in(true)) {
    header("Location: ../login.php?message=unauthorized");
    exit();
}

$users = [];
$user_info = null;
$error = "";
$success = "";
$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($action === 'delete' && $user_id > 0) {
        if ($user_id === $_SESSION['user_id']) {
            $error = "Vous ne pouvez pas supprimer votre propre compte.";
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            
            $success = "L'utilisateur a été supprimé avec succès.";
            header("Location: users.php?success=deleted");
            exit();
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
        $update_id = intval($_POST['user_id']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $is_admin = isset($_POST['is_admin']) ? 1 : 0;
        $is_verified = isset($_POST['is_verified']) ? 1 : 0;
        
        if (empty($username) || empty($email)) {
            $error = "Le nom d'utilisateur et l'email sont requis.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "L'adresse email est invalide.";
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $stmt->execute([$username, $email, $update_id]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = "Ce nom d'utilisateur ou cette adresse email est déjà utilisé.";
            } else {
                $stmt = $conn->prepare("
                    UPDATE users 
                    SET username = ?, email = ?, is_admin = ?, is_verified = ?
                    WHERE id = ?
                ");
                $stmt->execute([$username, $email, $is_admin, $is_verified, $update_id]);
                
                $success = "L'utilisateur a été mis à jour avec succès.";
                
                if ($update_id === $_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['email'] = $email;
                    $_SESSION['is_admin'] = $is_admin;
                }
            }
        }
    }
    
    if ($action === 'edit' && $user_id > 0) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user_info) {
            $error = "Utilisateur non trouvé.";
            $action = '';
        }
    }
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    if (!empty($search)) {
        $stmt = $conn->prepare("
            SELECT u.*, 
                   (SELECT COUNT(*) FROM shortened_urls WHERE user_id = u.id) as links_count
            FROM users u
            WHERE u.username LIKE ? OR u.email LIKE ?
            ORDER BY u.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(["%$search%", "%$search%", $limit, $offset]);
        
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username LIKE ? OR email LIKE ?");
        $countStmt->execute(["%$search%", "%$search%"]);
    } else {
        $stmt = $conn->prepare("
            SELECT u.*, 
                (SELECT COUNT(*) FROM shortened_urls WHERE user_id = u.id) as links_count
            FROM users u
            ORDER BY u.id DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM users");
        $countStmt->execute();
    }
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_users = $countStmt->fetchColumn();
    $total_pages = ceil($total_users / $limit);
    
} catch(PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <h1>Gestion des utilisateurs</h1>
            <p class="text-muted">Gérez les comptes utilisateurs de URLink.</p>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($action === 'edit' && $user_info): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Modifier l'utilisateur</h5>
            </div>
            <div class="card-body">
                <form action="users.php" method="post">
                    <input type="hidden" name="user_id" value="<?php echo $user_info['id']; ?>">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_info['username']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" <?php echo $user_info['is_admin'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_admin">Administrateur</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_verified" name="is_verified" <?php echo $user_info['is_verified'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_verified">Compte vérifié</label>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="users.php" class="btn btn-secondary">Annuler</a>
                        <button type="submit" name="update_user" class="btn btn-primary">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des utilisateurs</h5>
                    <form action="users.php" method="get" class="d-flex">
                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Rechercher un utilisateur..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom d'utilisateur</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                                <th>Status</th>
                                <th>Rôle</th>
                                <th>Liens</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if($user['is_verified']): ?>
                                        <span class="badge bg-success">Vérifié</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Non vérifié</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user['is_admin']): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-info">Utilisateur</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $user['links_count']; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur? Cette action est irréversible.');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if(empty($users)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Aucun utilisateur trouvé.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Affichage de <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $total_users); ?> sur <?php echo $total_users; ?> utilisateurs
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <?php if($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="users.php?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="users.php?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="users.php?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>