<?php
session_start();
require_once '../config/db-config.php';
require_once '../includes/sessions-functions.php';

if (!is_logged_in(true)) {
    header("Location: ../login.php?message=unauthorized");
    exit();
}

$error = "";
$success = "";

// Paramètres simplifiés - seulement 2 conservés
$site_settings = [
    'max_links_per_user' => 50,
    'maintenance_mode' => 0
];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si la table existe
    $stmt = $conn->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("
            CREATE TABLE settings (
                setting_key VARCHAR(50) PRIMARY KEY,
                setting_value TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        foreach ($site_settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    } else {
        // Charger les paramètres existants
        $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        
        $stmt->execute(['max_links_per_user']);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $site_settings['max_links_per_user'] = $row['setting_value'];
        }
        
        $stmt->execute(['maintenance_mode']);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $site_settings['maintenance_mode'] = $row['setting_value'];
        }
    }

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
        $site_settings['max_links_per_user'] = intval($_POST['max_links_per_user']);
        $site_settings['maintenance_mode'] = isset($_POST['maintenance_mode']) ? 1 : 0;

        // Mettre à jour les paramètres dans la base de données
        foreach ($site_settings as $key => $value) {
            $stmt = $conn->prepare("
                INSERT INTO settings (setting_key, setting_value) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = ?
            ");
            $stmt->execute([$key, $value, $value]);
        }
        
        $success = "Les paramètres ont été mis à jour avec succès.";
    }
} catch(PDOException $e) {
    $error = "Erreur de base de données: " . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="content-wrapper">
    <div class="row">
        <div class="col-12">
            <h1>Paramètres du site</h1>
            <p class="text-muted">Configurez les paramètres essentiels de URLink.</p>
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
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Paramètres essentiels</h5>
        </div>
        <div class="card-body">
            <form action="settings.php" method="post">
                <div class="mb-4">
                    <label for="max_links_per_user" class="form-label">Nombre maximal de liens par utilisateur</label>
                    <input type="number" class="form-control" id="max_links_per_user" name="max_links_per_user" value="<?php echo intval($site_settings['max_links_per_user']); ?>" min="0">
                    <div class="form-text">0 = illimité</div>
                </div>
                
                <div class="mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php echo $site_settings['maintenance_mode'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="maintenance_mode">Mode maintenance</label>
                    </div>
                    <div class="form-text">Si activé, seuls les administrateurs pourront accéder au site.</div>
                </div>
                
                <button type="submit" name="update_settings" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Enregistrer les paramètres
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>