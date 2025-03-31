<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/mail-functions.php';

$error = "";
$success = false;

function generate_reset_token() {
    return bin2hex(random_bytes(32));
}

function send_reset_email($to_email, $username, $reset_token) {
    require 'vendor/autoload.php';

    $smtp_host = 'smtp.gmail.com';
    $smtp_port = 465;
    $smtp_username = 'jebrilhocine@gmail.com';
    $smtp_password = 'wlff funy povv cpdj';
    $smtp_from_name = 'URLink Service';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_username;
        $mail->Password = $smtp_password;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $smtp_port;

        $mail->setFrom($smtp_username, $smtp_from_name);
        $mail->addAddress($to_email);

        $mail->isHTML(true);
        $mail->Subject = 'Réinitialisation de mot de passe pour votre compte URLink';

        $reset_link = 'https://' . $_SERVER['HTTP_HOST'] . '/reset_password.php?token=' . $reset_token;

        $mail->Body = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4361ee; color: white; padding: 10px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .button { display: inline-block; padding: 10px 20px; background-color: #4361ee; color: white; text-decoration: none; border-radius: 4px; }
                .footer { margin-top: 20px; font-size: 12px; color: #777; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>URLink - Réinitialisation de mot de passe</h2>
                </div>
                <div class="content">
                    <p>Bonjour ' . htmlspecialchars($username) . ',</p>
                    <p>Nous avons reçu une demande de réinitialisation de votre mot de passe. Si vous n\'avez pas fait cette demande, vous pouvez ignorer cet email.</p>
                    <p>Pour réinitialiser votre mot de passe, veuillez cliquer sur le bouton ci-dessous :</p>
                    <p style="text-align: center;">
                        <a href="' . $reset_link . '" class="button">Réinitialiser mon mot de passe</a>
                    </p>
                    <p>Ou copiez et collez le lien suivant dans votre navigateur :</p>
                    <p>' . $reset_link . '</p>
                    <p>Ce lien expirera dans 1 heure pour des raisons de sécurité.</p>
                </div>
                <div class="footer">
                    <p>© ' . date('Y') . ' URLink. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->AltBody = "Bonjour $username,\n\n"
            . "Nous avons reçu une demande de réinitialisation de votre mot de passe. Si vous n'avez pas fait cette demande, vous pouvez ignorer cet email.\n\n"
            . "Pour réinitialiser votre mot de passe, veuillez visiter ce lien :\n\n"
            . "$reset_link\n\n"
            . "Ce lien expirera dans 1 heure pour des raisons de sécurité.\n\n"
            . "© " . date('Y') . " URLink. Tous droits réservés.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("L'email de réinitialisation n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}");
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = "Veuillez entrer votre adresse email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } else {
        try {
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $reset_token = generate_reset_token();

                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                $stmt->execute([$reset_token, $expiry, $user['id']]);

                $mail_sent = send_reset_email($user['email'], $user['username'], $reset_token);

                if ($mail_sent) {
                    $success = true;
                } else {
                    $error = "Nous n'avons pas pu envoyer l'email de réinitialisation. Veuillez réessayer plus tard.";
                }
            } else {
                $success = true;
            }
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
    <link rel="stylesheet" href="css/forgot_password.css">
    <title>URLink - Mot de passe oublié</title>
</head>

<body>
    <div class="container">
        <div class="form-section">
            <div class="logo">
                <i class="fas fa-link"></i> URLink
            </div>
            <h2>Mot de passe oublié?</h2>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message" style="display: block;">
                    <i class="fas fa-check-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                    <p>Un email de réinitialisation a été envoyé à votre adresse. Veuillez vérifier votre boîte de réception.</p>
                </div>

                <a href="login.php">Retour à la page de connexion</a>
            <?php else: ?>
                <span>Entrez votre email pour réinitialiser votre mot de passe</span>

                <form id="resetPasswordForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="email" name="email" placeholder="Email" required>
                    <div style="text-align: center; width: 100%;">
                        <button type="submit">Envoyer le lien</button>
                    </div>
                </form>

                <a href="login.php" id="backToLogin">Retour à la page de connexion</a>
            <?php endif; ?>
        </div>

        <div class="info-section">
            <h1>Procédure de récupération</h1>
            <p>Suivez ces étapes simples pour récupérer l'accès à votre compte :</p>

            <div class="steps">
                <div class="step">
                    <span class="step-number">1</span>
                    <span class="step-text">Entrez l'adresse email associée à votre compte</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span class="step-text">Vérifiez votre boîte de réception pour le lien de réinitialisation</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-text">Créez un nouveau mot de passe sécurisé à l'aide du lien dans votre boite</span>
                </div>
            </div>

            <button id="needHelp">Besoin d'aide ?</button>
        </div>
    </div>

    <script src="js/forgot_password.js"></script>
</body>

</html>