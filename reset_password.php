<?php
session_start();
require_once 'config/db-config.php';

$error = "";
$success = false;
$token_valid = false;
$token = "";

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT id, username, email, reset_token, reset_token_expiry FROM users WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (strtotime($user['reset_token_expiry']) > time()) {
                $token_valid = true;
            } else {
                $error = "Ce lien de réinitialisation a expiré. Veuillez en demander un nouveau.";
            }
        } else {
            $error = "Lien de réinitialisation invalide. Veuillez en demander un nouveau.";
        }
    } catch(PDOException $e) {
        $error = "Erreur de connexion à la base de données: " . $e->getMessage();
    }
} else {
    $error = "Aucun jeton de réinitialisation fourni.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($password) || empty($confirm_password)) {
        $error = "Veuillez remplir tous les champs.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match('/[A-Z]/', $password) ||
              !preg_match('/[a-z]/', $password) ||
              !preg_match('/[0-9]/', $password) ||
              !preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = "Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et un caractère spécial.";
    } else {
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
            $stmt->execute([$hashed_password, $token]);

            $success = true;

            header("refresh:3;url=login.php?success=reset");
        } catch(PDOException $e) {
            $error = "Erreur de connexion à la base de données: " . $e->getMessage();
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
    <link rel="stylesheet" href="css/reset_password.css">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">
    <title>URLink - Réinitialisation de mot de passe</title>
</head>

<body>
    <div class="container">
        <div class="form-section">
            <div class="logo">
                <i class="fas fa-link"></i> URLink
            </div>
            <h2>Réinitialisation de mot de passe</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php if ($error != "Erreur de connexion à la base de données: " . $e->getMessage() && $error != "Aucun jeton de réinitialisation fourni."): ?>
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="btn btn-primary">Demander un nouveau lien</a>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Votre mot de passe a été réinitialisé avec succès!</p>
                    <p>Vous allez être redirigé vers la page de connexion dans quelques secondes...</p>
                </div>
                <div class="text-center">
                    <a href="login.php">Aller à la page de connexion</a>
                </div>
            <?php elseif ($token_valid): ?>
                <p>Veuillez entrer votre nouveau mot de passe ci-dessous.</p>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?token=" . $token); ?>" method="post">
                    <div class="form-group">
                        <label for="password">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Le mot de passe doit contenir au moins 8 caractères, dont des lettres majuscules, des lettres minuscules, des chiffres et des caractères spéciaux.</div>
                    </div>

                    <div class="password-criteria">
                        <div class="criteria-item" id="length">
                            <i class="fas fa-circle"></i> Au moins 8 caractères
                        </div>
                        <div class="criteria-item" id="uppercase">
                            <i class="fas fa-circle"></i> Au moins une lettre majuscule
                        </div>
                        <div class="criteria-item" id="lowercase">
                            <i class="fas fa-circle"></i> Au moins une lettre minuscule
                        </div>
                        <div class="criteria-item" id="number">
                            <i class="fas fa-circle"></i> Au moins un chiffre
                        </div>
                        <div class="criteria-item" id="special">
                            <i class="fas fa-circle"></i> Au moins un caractère spécial
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmation du nouveau mot de passe</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div style="text-align: center; width: 100%;">
                        <button type="submit">Réinitialiser le mot de passe</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <h1>Sécurité du compte</h1>
            <p>La réinitialisation de votre mot de passe vous aidera à protéger votre compte URLink.</p>

            <div style="text-align: left; width: 100%;">
                <p><i class="fas fa-lock"></i> Assurez-vous que votre nouveau mot de passe :</p>
                <ul style="list-style-type: none; padding-left: 20px; margin-bottom: 30px;">
                    <li style="margin-bottom: 10px;"><i class="fas fa-check"></i> Est unique et n'est pas utilisé sur d'autres sites</li>
                    <li style="margin-bottom: 10px;"><i class="fas fa-check"></i> Est suffisamment long et complexe</li>
                    <li style="margin-bottom: 10px;"><i class="fas fa-check"></i> Ne contient pas d'informations personnelles facilement devinables</li>
                    <li><i class="fas fa-check"></i> Est mémorisable pour vous mais difficile à deviner pour les autres</li>
                </ul>
            </div>

            <p>Après avoir réinitialisé votre mot de passe, vous serez redirigé vers la page de connexion où vous pourrez vous connecter avec vos nouvelles informations d'identification.</p>
        </div>
    </div>

    <script src="js/reset_password.js"></script>
</body>

</html>