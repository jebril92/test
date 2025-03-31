<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/mail-functions.php';

$error = "";
$success = false;
$email = "";

if (!isset($_SESSION['pending_verification_email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['pending_verification_email'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['resend'])) {
    $code = '';

    for ($i = 1; $i <= 6; $i++) {
        if (isset($_POST["code$i"]) && !empty($_POST["code$i"])) {
            $code .= $_POST["code$i"];
        } else {
            $error = "Veuillez saisir le code complet à 6 caractères.";
            break;
        }
    }


    if (strlen($code) === 6) {
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare("SELECT id, username, verification_token FROM users WHERE email = ? AND is_verified = 0");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['verification_token'] === $code) {
                $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = ?");
                $stmt->execute([$user['id']]);

                $success = true;
                unset($_SESSION['pending_verification_email']);

                header("refresh:3;url=login.php?success=verified");
            } else {
                $error = "Code de vérification invalide. Veuillez réessayer.";
            }
        } catch(PDOException $e) {
            $error = "Erreur de connexion à la base de données: " . $e->getMessage();
        }
    }
}

if (isset($_POST['resend'])) {
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ? AND is_verified = 0");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $verification_code = generate_verification_code(6);

            $stmt = $conn->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
            $stmt->execute([$verification_code, $user['id']]);

            $mail_sent = send_verification_email($email, $user['username'], $verification_code);

            if ($mail_sent) {
                $resend_success = true;
            } else {
                $error = "Nous n'avons pas pu envoyer le nouvel email de vérification. Veuillez réessayer plus tard.";
            }
        } else {
            $error = "Cet email n'est pas associé à un compte non vérifié.";
        }
    } catch(PDOException $e) {
        $error = "Erreur de connexion à la base de données: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/verify.css">
    <title>URLink - Vérification de Code</title>
</head>

<body>
    <div class="container">
        <div class="form-section">
            <div class="logo">
                <i class="fas fa-link"></i> URLink
            </div>
            <h1>Vérification de votre compte</h1>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($resend_success) && $resend_success): ?>
                <div class="alert alert-success">
                    <p>Un nouveau code a été envoyé à votre adresse email.</p>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message" style="display: block;">
                    <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Votre compte a été vérifié avec succès ! Vous allez être redirigé...</p>
                </div>
            <?php else: ?>
                <span>Veuillez entrer le code à 6 caractères envoyé à votre adresse email : <strong><?php echo htmlspecialchars($email); ?></strong></span>

                <form id="verificationForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="verification-code">
                        <input type="text" name="code1" maxlength="1" autofocus required>
                        <input type="text" name="code2" maxlength="1" required>
                        <input type="text" name="code3" maxlength="1" required>
                        <input type="text" name="code4" maxlength="1" required>
                        <input type="text" name="code5" maxlength="1" required>
                        <input type="text" name="code6" maxlength="1" required>
                    </div>

                    <div class="timer" id="timer">
                        Le code expire dans : <span>05:00</span>
                    </div>

                    <div class="resend">
                        <span>Vous n'avez pas reçu de code ?</span>
                        <button type="submit" name="resend" id="resendBtn">Renvoyer</button>
                    </div>

                    <div style="text-align: center; width: 100%;">
                        <button type="submit">Vérifier</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <h1>Bienvenue chez URLink !</h1>
            <p>Merci de vérifier votre adresse email. Cela nous aide à :</p>

            <div class="tip">
                <i class="fas fa-shield-alt"></i> Sécuriser votre compte
            </div>

            <div class="tip">
                <i class="fas fa-bell"></i> Vous informer des activités importantes
            </div>

            <div class="tip">
                <i class="fas fa-unlock-alt"></i> Vous aider à récupérer votre compte si besoin
            </div>

            <p>Vérifiez votre boîte de réception et votre dossier spam si vous ne trouvez pas le code.</p>

            <a href="login.php" style="color: white; border: 1px solid white; padding: 10px 20px; border-radius: 8px; margin-top: 20px; display: inline-block;">
                Retour à la connexion
            </a>
        </div>
    </div>

    <script src="js/verify.js"></script>
</body>

</html>