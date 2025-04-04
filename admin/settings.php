<?php
session_start();
require_once '../config/db-config.php';
require_once '../includes/sessions-functions.php';

// Vérifier que l'utilisateur est un administrateur
if (!is_logged_in(true)) {
    header("Location: ../login.php?message=unauthorized");
    exit();
}

$error = "";
$success = "";

// Configuration du site
$site_settings = [
    'site_name' => 'URLink',
    'admin_email' => '',
    'max_links_per_user' => 50,
    'default_link_expiry' => 0, // 0 = pas d'expiration
    'guest_links_expiry' => 48, // en heures
    'allow_guest_links' => 1,
    'maintenance_mode' => 0
];

// Charger les paramètres depuis la base de données
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si la table settings existe
    $stmt = $conn->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() == 0) {
        // Créer la table si elle n'existe pas
        $conn->exec("
            CREATE TABLE settings (
                setting_key VARCHAR(50) PRIMARY KEY,
                setting_value TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Insérer les paramètres par défaut
        foreach ($site_settings as $key => $value) {
            $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute([$key, $value]);
        }
    } else {
        // Charger les paramètres existants
        $stmt = $conn->query("SELECT setting_key, setting_value FROM settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $site_settings[$row['setting_key']] = $row['setting_value'];
        }
    }

    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
        // Récupérer les valeurs du formulaire
        $site_settings['site_name'] = trim($_POST['site_name']);
        $site_settings['admin_email'] = trim($_POST['admin_email']);
        $site_settings['max_links_per_user'] = intval($_POST['max_links_per_user']);
        $site_settings['default_link_expiry'] = intval($_POST['default_link_expiry']);
        $site_settings['guest_links_expiry'] = intval($_POST['guest_links_expiry']);
        $site_settings['allow_guest_links'] = isset($_POST['allow_guest_links']) ? 1 : 0;
        $site_settings['maintenance_mode'] = isset($_POST['maintenance_mode']) ? 1 : 0;

        // Validation des données
        if (empty($site_settings['site_name'])) {
            $error = "Le nom du site est requis.";
        } elseif (!empty($site_settings['admin_email']) && !filter_var($site_settings['admin_email'], FILTER_VALIDATE_EMAIL)) {
            $error = "L'adresse email d'administration est invalide.";
        } else {
            // Mettre à jour les paramètres
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
            <p class="text-muted">Configurez les paramètres généraux de URLink.</p>
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
            <h5 class="mb-0">Paramètres généraux</h5>
        </div>
        <div class="card-body">
            <form action="settings.php" method="post">
                <div class="mb-3">
                    <label for="site_name" class="form-label">Nom du site</label>
                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($site_settings['site_name']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="admin_email" class="form-label">Email d'administration</label>
                    <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($site_settings['admin_email']); ?>">
                    <div class="form-text">Les notifications système seront envoyées à cette adresse.</div>
                </div>
                
                <div class="mb-3">
                    <label for="max_links_per_user" class="form-label">Nombre maximal de liens par utilisateur</label>
                    <input type="number" class="form-control" id="max_links_per_user" name="max_links_per_user" value="<?php echo intval($site_settings['max_links_per_user']); ?>" min="0">
                    <div class="form-text">0 = illimité</div>
                </div>
                
                <div class="mb-3">
                    <label for="default_link_expiry" class="form-label">Durée d'expiration par défaut pour les liens (en heures)</label>
                    <input type="number" class="form-control" id="default_link_expiry" name="default_link_expiry" value="<?php echo intval($site_settings['default_link_expiry']); ?>" min="0">
                    <div class="form-text">0 = pas d'expiration</div>
                </div>
                
                <div class="mb-3">
                    <label for="guest_links_expiry" class="form-label">Durée d'expiration pour les liens des invités (en heures)</label>
                    <input type="number" class="form-control" id="guest_links_expiry" name="guest_links_expiry" value="<?php echo intval($site_settings['guest_links_expiry']); ?>" min="0">
                    <div class="form-text">0 = pas d'expiration</div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allow_guest_links" name="allow_guest_links" <?php echo $site_settings['allow_guest_links'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="allow_guest_links">Autoriser les liens pour les invités</label>
                    </div>
                    <div class="form-text">Si désactivé, seuls les utilisateurs connectés pourront créer des liens.</div>
                </div>
                
                <div class="mb-3">
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