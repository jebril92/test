<?php
session_start();
require_once '../config/db-config.php';
require_once '../includes/sessions-functions.php';

if (!is_logged_in(true)) {
    header("Location: ../login.php?message=unauthorized");
    exit();
}

$links = [];
$link_info = null;
$error = "";
$success = "";
$action = isset($_GET['action']) ? $_GET['action'] : '';
$link_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($action === 'delete' && $link_id > 0) {
        $stmt = $conn->prepare("DELETE FROM click_stats WHERE url_id = ?");
        $stmt->execute([$link_id]);
        
        $stmt = $conn->prepare("DELETE FROM shortened_urls WHERE id = ?");
        $stmt->execute([$link_id]);
        
        $success = "Le lien a été supprimé avec succès.";
        header("Location: links.php?success=deleted");
        exit();
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_link'])) {
        $update_id = intval($_POST['link_id']);
        $original_url = trim($_POST['original_url']);
        $short_code = trim($_POST['short_code']);
        $expiry_datetime = !empty($_POST['expiry_datetime']) ? $_POST['expiry_datetime'] : null;
        
        if (empty($original_url) || empty($short_code)) {
            $error = "L'URL originale et le code court sont requis.";
        } elseif (!filter_var($original_url, FILTER_VALIDATE_URL)) {
            $error = "L'URL originale est invalide.";
        } elseif (!preg_match('/^[a-zA-Z0-9]+$/', $short_code)) {
            $error = "Le code court ne peut contenir que des lettres et des chiffres.";
        } else {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM shortened_urls WHERE short_code = ? AND id != ?");
            $stmt->execute([$short_code, $update_id]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = "Ce code court est déjà utilisé. Veuillez en choisir un autre.";
            } else {
                $stmt = $conn->prepare("
                    UPDATE shortened_urls 
                    SET original_url = ?, short_code = ?, expiry_datetime = ?
                    WHERE id = ?
                ");
                $stmt->execute([$original_url, $short_code, $expiry_datetime, $update_id]);
                
                $success = "Le lien a été mis à jour avec succès.";
                header("Location: links.php?action=edit&id=$update_id&success=updated");
                exit();
            }
        }
    }
    
    if ($action === 'edit' && $link_id > 0) {
        $stmt = $conn->prepare("
            SELECT s.*, u.username 
            FROM shortened_urls s 
            JOIN users u ON s.user_id = u.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$link_id]);
        $link_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$link_info) {
            $error = "Lien non trouvé.";
            $action = '';
        }
    }
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    if (!empty($search)) {
        $stmt = $conn->prepare("
            SELECT s.*, u.username,
                   (SELECT COUNT(*) FROM click_stats WHERE url_id = s.id) as clicks_count
            FROM shortened_urls s
            JOIN users u ON s.user_id = u.id
            WHERE s.original_url LIKE ? OR s.short_code LIKE ? OR u.username LIKE ?
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute(["%$search%", "%$search%", "%$search%", $limit, $offset]);
        
        $countStmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM shortened_urls s
            JOIN users u ON s.user_id = u.id
            WHERE s.original_url LIKE ? OR s.short_code LIKE ? OR u.username LIKE ?
        ");
        $countStmt->execute(["%$search%", "%$search%", "%$search%"]);
    } else {
        $stmt = $conn->prepare("
            SELECT s.*, u.username,
                (SELECT COUNT(*) FROM click_stats WHERE url_id = s.id) as clicks_count
            FROM shortened_urls s
            JOIN users u ON s.user_id = u.id
            ORDER BY s.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM shortened_urls");
        $countStmt->execute();
    }
    
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total_links = $countStmt->fetchColumn();
    $total_pages = ceil($total_links / $limit);
    
} catch(PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <h1>Gestion des liens raccourcis</h1>
            <p class="text-muted">Gérez tous les liens raccourcis de URLink.</p>
        </div>
    </div>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success) || (isset($_GET['success']) && $_GET['success'] == 'deleted')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
                if (!empty($success)) {
                    echo $success;
                } elseif (isset($_GET['success']) && $_GET['success'] == 'deleted') {
                    echo "Le lien a été supprimé avec succès.";
                }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['success']) && $_GET['success'] == 'updated'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Le lien a été mis à jour avec succès.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($action === 'edit' && $link_info): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Modifier le lien</h5>
            </div>
            <div class="card-body">
                <form action="links.php" method="post">
                    <input type="hidden" name="link_id" value="<?php echo $link_info['id']; ?>">
                    
                    <div class="mb-3">
                        <label for="original_url" class="form-label">URL originale</label>
                        <input type="url" class="form-control" id="original_url" name="original_url" value="<?php echo htmlspecialchars($link_info['original_url']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="short_code" class="form-label">Code court</label>
                        <div class="input-group">
                            <span class="input-group-text"><?php echo $base_url; ?></span>
                            <input type="text" class="form-control" id="short_code" name="short_code" value="<?php echo htmlspecialchars($link_info['short_code']); ?>" pattern="[a-zA-Z0-9]+" required>
                        </div>
                        <div class="form-text">Utilisez uniquement des lettres et des chiffres (a-z, A-Z, 0-9)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="expiry_datetime" class="form-label">Date d'expiration (optionnel)</label>
                        <input type="datetime-local" class="form-control" id="expiry_datetime" name="expiry_datetime" value="<?php echo !empty($link_info['expiry_datetime']) ? date('Y-m-d\TH:i', strtotime($link_info['expiry_datetime'])) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Informations complémentaires</label>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Créé par
                                <span><?php echo htmlspecialchars($link_info['username']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Date de création
                                <span><?php echo date('d/m/Y H:i', strtotime($link_info['created_at'])); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Nombre de clics
                                <?php
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM click_stats WHERE url_id = ?");
                                $stmt->execute([$link_info['id']]);
                                $clicks = $stmt->fetchColumn();
                                ?>
                                <span class="badge bg-primary rounded-pill"><?php echo $clicks; ?></span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="links.php" class="btn btn-secondary">Annuler</a>
                        <div>
                            <a href="../stats.php?id=<?php echo $link_info['id']; ?>" class="btn btn-info" target="_blank">
                                <i class="fas fa-chart-bar me-1"></i> Voir les statistiques
                            </a>
                            <button type="submit" name="update_link" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Liste des liens raccourcis</h5>
                    <form action="links.php" method="get" class="d-flex">
                        <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Rechercher un lien..." value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Code court</th>
                                <th>URL originale</th>
                                <th>Créé par</th>
                                <th>Date de création</th>
                                <th>Expiration</th>
                                <th>Clics</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($links as $link): ?>
                            <tr>
                                <td><?php echo $link['id']; ?></td>
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
                                <td><?php echo date('d/m/Y H:i', strtotime($link['created_at'])); ?></td>
                                <td>
                                    <?php if (empty($link['expiry_datetime'])): ?>
                                        <span class="badge bg-secondary">Jamais</span>
                                    <?php elseif (strtotime($link['expiry_datetime']) < time()): ?>
                                        <span class="badge bg-danger">Expiré</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">
                                            <?php echo date('d/m/Y H:i', strtotime($link['expiry_datetime'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $link['clicks_count']; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="links.php?action=edit&id=<?php echo $link['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="../stats.php?id=<?php echo $link['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                        <a href="links.php?action=delete&id=<?php echo $link['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lien? Cette action est irréversible.');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if(empty($links)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Aucun lien trouvé.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if($total_pages > 1): ?>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Affichage de <?php echo $offset + 1; ?>-<?php echo min($offset + $limit, $total_links); ?> sur <?php echo $total_links; ?> liens
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <?php if($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="links.php?page=<?php echo $page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="links.php?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="links.php?page=<?php echo $page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" aria-label="Next">
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