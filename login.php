<?php
session_start();

$login_error = "";
$register_error = "";

if (file_exists('config/db-config.php')) {
    include_once 'config/db-config.php';
} else {
    echo "Erreur: fichier db-config.php introuvable";
}

if (file_exists('includes/mail-functions.php')) {
    include_once 'includes/mail-functions.php';
} else {
    echo "Erreur: fichier mail-functions.php introuvable";
}

if (file_exists('includes/sessions-functions.php')) {
    include_once 'includes/sessions-functions.php';
} else {
    echo "Erreur: fichier sessions-functions.php introuvable";
}

if (file_exists('includes/maintenance-check.php')) {
    include_once 'includes/maintenance-check.php';
} else {
    echo "Erreur: fichier maintenance-check.php introuvable";
}

if (isset($_GET['logout'])) {
    destroy_session();
    header("Location: login.php");
    exit();
}

if (is_maintenance_mode() && !isset($_POST['action'])) {
    $maintenance_message = true;
}

// Traitement du formulaire d'inscription
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'register') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $register_error = "Veuillez remplir tous les champs.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Adresse email invalide.";
    } elseif ($password !== $confirm_password) {
        $register_error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $register_error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $password) || 
              !preg_match('/[a-z]/', $password) || 
              !preg_match('/[0-9]/', $password) || 
              !preg_match('/[^A-Za-z0-9]/', $password)) {
        $register_error = "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.";
    } else {
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ? OR email = ?");
                $stmt->execute([$username, $email]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing['username'] === $username) {
                    $register_error = "Ce nom d'utilisateur est déjà pris.";
                } else {
                    $register_error = "Cette adresse email est déjà enregistrée.";
                }
            } else {
                $verification_code = generate_verification_code(6);
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, verification_token, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$username, $email, $hashed_password, $verification_code]);
                
                $mail_sent = send_verification_email($email, $username, $verification_code);
                
                if ($mail_sent) {
                    $_SESSION['pending_verification_email'] = $email;
                    header("Location: verify.php");
                    exit();
                } else {
                    $register_error = "Votre compte a été créé, mais nous n'avons pas pu envoyer l'email de vérification. Veuillez contacter le support.";
                }
            }
        } catch(PDOException $e) {
            $register_error = "Erreur de connexion à la base de données: " . $e->getMessage();
        }
    }
}

// Traitement du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $login_error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("SELECT id, username, email, password, is_verified, is_admin FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                if ($user['is_verified'] == 1) {
                    create_secure_session($user['id'], $user['username'], $user['email'], $user['is_admin']);

                    header("Location: index.php");
                    exit();
                } else {
                    $_SESSION['pending_verification_email'] = $email;
                    $login_error = "Votre compte n'est pas encore vérifié. <a href='verify.php'>Cliquez ici</a> pour entrer votre code de vérification.";
                }
            } else {
                $login_error = "Email ou mot de passe incorrect.";
            }
        } catch(PDOException $e) {
            $login_error = "Erreur de connexion à la base de données: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">
    <title>URLink - Connexion</title>
</head>

<body>
    <a href="index.php" class="back-to-site">
        <i class="fas fa-arrow-left"></i> Retour au site
    </a>

<?php if (isset($maintenance_message)): ?>
    <div class="maintenance-alert">
        <i class="fas fa-tools"></i> Le site est actuellement en mode maintenance. Seuls les administrateurs peuvent s'y connecter.
    </div>
<?php endif; ?>
    <div class="container <?php echo (!empty($register_error) || isset($_GET['register'])) ? 'active' : ''; ?>" id="container">
        <div class="form-container sign-up">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="action" value="register">
                <div class="logo">
                    <i class="fas fa-link"></i> URLink
                </div>
                <h1 class="form-title">Créer un compte</h1>

                <?php if (!empty($register_error)): ?>
                    <div class="alert alert-danger"><?php echo $register_error; ?></div>
                <?php endif; ?>

                <span class="form-subtitle">Utilisez votre email pour vous inscrire</span>

                <input type="text" name="username" placeholder="Nom d'utilisateur" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>

                <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) && isset($_POST['action']) && $_POST['action'] == 'register' ? htmlspecialchars($_POST['email']) : ''; ?>" required>

                <div class="password-input-container" style="width: 100%;">
                    <input type="password" name="password" id="register-password" placeholder="Mot de passe" required>

                    <div class="password-strength">
                        <div class="password-strength-bar" id="password-strength-bar"></div>
                    </div>

                    <div class="password-criteria">
                        <div class="criteria-item" id="length"><i class="fas fa-circle"></i> Au moins 8 caractères</div>
                        <div class="criteria-item" id="uppercase"><i class="fas fa-circle"></i> Au moins 1 majuscule</div>
                        <div class="criteria-item" id="lowercase"><i class="fas fa-circle"></i> Au moins 1 minuscule</div>
                        <div class="criteria-item" id="number"><i class="fas fa-circle"></i> Au moins 1 chiffre</div>
                        <div class="criteria-item" id="special"><i class="fas fa-circle"></i> Au moins 1 caractère spécial</div>
                    </div>
                </div>

                <input type="password" name="confirm_password" id="confirm-password" placeholder="Confirmation du mot de passe" required>

                <button type="submit">S'inscrire</button>

                <div class="mobile-toggle">
                    <p>Vous avez déjà un compte? <a id="mobile-login">Se connecter</a></p>
                </div>
            </form>
        </div>

        <div class="form-container sign-in">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="action" value="login">
                <div class="logo">
                    <i class="fas fa-link"></i> URLink
                </div>
                <h1 class="form-title">Connexion</h1>

                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] == 'registered'): ?>
                    <div class="alert alert-success">Inscription réussie ! Veuillez vous connecter.</div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] == 'reset'): ?>
                    <div class="alert alert-success">Votre mot de passe a été réinitialisé avec succès.</div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] == 'verified'): ?>
                    <div class="alert alert-success">Votre compte a été vérifié avec succès. Vous pouvez maintenant vous connecter.</div>
                <?php endif; ?>

                <span class="form-subtitle">Utilisez votre email et mot de passe</span>
                <input type="email" name="email" placeholder="Email" value="<?php echo isset($_POST['email']) && isset($_POST['action']) && $_POST['action'] == 'login' ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <a href="forgot_password.php">Mot de passe oublié ?</a>
                <button type="submit">Se connecter</button>

                <div class="mobile-toggle">
                    <p>Pas encore de compte? <a id="mobile-register">S'inscrire</a></p>
                </div>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Bon retour parmi nous !</h1>
                    <p>Connectez-vous avec vos informations personnelles pour accéder à toutes les fonctionnalités du site</p>
                    <button class="hidden" id="login">Se connecter</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Bonjour !</h1>
                    <p>Inscrivez-vous avec vos données personnelles pour accéder à toutes les fonctionnalités du site</p>
                    <button class="hidden" id="register">S'inscrire</button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/login.js"></script>
    <script>
        <?php if (!empty($login_error) || isset($_GET['success'])): ?>
        document.getElementById('container').classList.remove('active');
        <?php endif; ?>

        <?php if (!empty($register_error) || isset($_GET['register'])): ?>
        document.getElementById('container').classList.add('active');
        <?php endif; ?>

        <?php if (isset($_GET['verification']) && $_GET['verification'] == 'failed'): ?>
        document.getElementById('container').classList.add('active');
        <?php endif; ?>
    </script>
</body>

</html>