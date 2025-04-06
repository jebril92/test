<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/sessions-functions.php';
require_once 'includes/maintenance-check.php';
require_once 'includes/mail-functions.php';

check_maintenance_mode();

$user_role = get_user_role();

$error = "";
$success = false;

$name = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$subject = '';
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Veuillez remplir tous les champs";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide";
    } else {
        require 'vendor/autoload.php';

        $smtp_host = 'smtp.gmail.com';
        $smtp_port = 465;
        $smtp_username = 'jebrilhocine@gmail.com';
        $smtp_password = 'wlff funy povv cpdj';
        $smtp_from_name = 'URLink Contact';

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
            $mail->addAddress($smtp_username);
            $mail->isHTML(true);
            $mail->Subject = "Contact URLink: " . $subject;

            $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        line-height: 1.6; 
                        color: #333333;
                        margin: 0;
                        padding: 0;
                    }
                    .container { 
                        max-width: 600px; 
                        margin: 0 auto; 
                        padding: 20px;
                        background-color: #ffffff;
                        border-radius: 8px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
                    }
                    .header { 
                        background: linear-gradient(135deg, #4361ee, #3a0ca3);
                        color: white; 
                        padding: 20px; 
                        text-align: center;
                        border-radius: 8px 8px 0 0;
                    }
                    .content { 
                        padding: 20px; 
                    }
                    .field {
                        margin-bottom: 20px;
                    }
                    .field strong {
                        display: block;
                        margin-bottom: 5px;
                        color: #4361ee;
                    }
                    .footer { 
                        text-align: center;
                        font-size: 12px; 
                        color: #777;
                        padding: 20px;
                        border-top: 1px solid #eee;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>Nouveau message depuis le formulaire de contact</h2>
                    </div>
                    <div class="content">
                        <div class="field">
                            <strong>Nom:</strong>
                            ' . htmlspecialchars($name) . '
                        </div>
                        <div class="field">
                            <strong>Email:</strong>
                            ' . htmlspecialchars($email) . '
                        </div>
                        <div class="field">
                            <strong>Sujet:</strong>
                            ' . htmlspecialchars($subject) . '
                        </div>
                        <div class="field">
                            <strong>Message:</strong>
                            ' . nl2br(htmlspecialchars($message)) . '
                        </div>
                    </div>
                    <div class="footer">
                        <p>Ce message a été envoyé depuis le formulaire de contact de URLink.</p>
                    </div>
                </div>
            </body>
            </html>';

            $mail->AltBody = "Nom: $name\nEmail: $email\nSujet: $subject\nMessage: $message";

            $mail->send();
            $success = true;
            
            $name = '';
            $email = '';
            $subject = '';
            $message = '';
        } catch (Exception $e) {
            $error = "L'envoi du message a échoué. Erreur: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - URLink</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/contact.css" rel="stylesheet">
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
                        <a class="nav-link" href="index.php#features">Fonctionnalités</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#pricing">Tarifs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="help-center.php">Aide</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">Contact</a>
                    </li>
                    <li class="nav-item ms-2">
                        <button id="theme-toggle" class="btn btn-link nav-link theme-toggle-icon" aria-label="Changer de thème">
                            <i class="fas fa-moon"></i>
                        </button>
                    </li>
                    <li class="nav-item ms-lg-3">
                        <?php if ($user_role === 'guest'): ?>
                            <a class="btn btn-primary btn-login" href="login.php">Se connecter</a>
                        <?php elseif ($user_role === 'admin'): ?>
                            <div class="dropdown">
                                <a class="btn btn-danger btn-login dropdown-toggle" href="#" role="button" id="adminDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Administration
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                    <li><a class="dropdown-item" href="admin/dashboard.php">Tableau de bord admin</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="login.php?logout=true">Déconnexion</a></li>
                                </ul>
                            </div>
                        <?php elseif ($user_role === 'user'): ?>
                            <div class="dropdown">
                                <a class="btn btn-primary btn-login dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    Mon compte
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="dashboard.php">Tableau de bord</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="login.php?logout=true">Déconnexion</a></li>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="contact-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">Contactez-nous</h1>
                    <p class="lead mb-5">Notre équipe est disponible pour répondre à toutes vos questions</p>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-form-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if ($success): ?>
                        <div class="success-message">
                            <div class="text-center">
                                <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                                <h2>Message envoyé avec succès !</h2>
                                <p>Merci de nous avoir contactés. Notre équipe vous répondra dans les plus brefs délais.</p>
                                <a href="index.php" class="btn btn-primary mt-3">Retour à l'accueil</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="contact-form-container">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <form id="contactForm" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Nom complet</label>
                                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-4">
                                        <div class="form-group">
                                            <label for="email" class="form-label">Adresse e-mail</label>
                                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <label for="subject" class="form-label">Sujet</label>
                                    <select class="form-select" id="subject" name="subject" required>
                                        <option value="" disabled <?php echo empty($subject) ? 'selected' : ''; ?>>Choisissez un sujet</option>
                                        <option value="Support technique" <?php echo $subject === 'Support technique' ? 'selected' : ''; ?>>Support technique</option>
                                        <option value="Question sur l'abonnement" <?php echo $subject === 'Question sur l\'abonnement' ? 'selected' : ''; ?>>Question sur l'abonnement</option>
                                        <option value="Problème de compte" <?php echo $subject === 'Problème de compte' ? 'selected' : ''; ?>>Problème de compte</option>
                                        <option value="Partenariat" <?php echo $subject === 'Partenariat' ? 'selected' : ''; ?>>Partenariat</option>
                                        <option value="Autre" <?php echo $subject === 'Autre' ? 'selected' : ''; ?>>Autre</option>
                                    </select>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <label for="message" class="form-label">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="6" required><?php echo htmlspecialchars($message); ?></textarea>
                                </div>
                                
                                <div class="privacy-notice mb-4">
                                    <p>En soumettant ce formulaire, vous acceptez notre <a href="privacy.php">politique de confidentialité</a>. Nous utilisons vos informations uniquement pour répondre à votre demande.</p>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">Envoyer le message</button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-info-section py-5">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-12">
                    <h2 class="mb-5">Autres moyens de nous contacter</h2>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="contact-info-card">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email</h3>
                        <p>support@urlink.com</p>
                        <p class="response-time">Réponse sous 24-48h</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="contact-info-card">
                        <div class="info-icon">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <h3>Chat en direct</h3>
                        <p>Disponible du lundi au vendredi</p>
                        <p class="response-time">9h à 18h (CET)</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="contact-info-card">
                        <div class="info-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <h3>Centre d'aide</h3>
                        <p>Consultez notre FAQ</p>
                        <a href="help-center.php" class="btn btn-outline-primary mt-2">Accéder</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="office-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <div class="map-container">
                        <a href="https://maps.google.com/?q=14-16+Rue+Voltaire,+94270+Le+Kremlin-Bicêtre,+France" target="_blank" class="map-link">
                            <div class="static-map">
                                <i class="fas fa-map-marked-alt"></i>
                                <h4>Voir notre emplacement</h4>
                                <p>14-16 Rue Voltaire, 94270 Le Kremlin-Bicêtre</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="office-info">
                        <h2>Nos bureaux</h2>
                        <p class="lead">Vous préférez nous rencontrer en personne ? Rendez-nous visite !</p>
                        
                        <div class="office-details mt-4">
                            <div class="office-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <h4>Adresse</h4>
                                    <p>14-16 Rue Voltaire <br>94270 Le Kremlin-Bicêtre, France</p>
                                </div>
                            </div>
                            
                            <div class="office-detail">
                                <i class="fas fa-phone-alt"></i>
                                <div>
                                    <h4>Téléphone</h4>
                                    <p>+33 (0)1 84 07 16 00</p>
                                </div>
                            </div>
                            
                            <div class="office-detail">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <h4>Heures d'ouverture</h4>
                                    <p>Lundi - Vendredi: 9h à 18h<br>Fermé les week-ends et jours fériés</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <h3 class="mb-4">
                        <i class="fas fa-link me-2"></i>
                        URLink
                    </h3>
                    <p>Simplifiez vos liens, amplifiez votre impact. URLink est le service de raccourcissement d'URL préféré des professionnels du marketing et des créateurs de contenu.</p>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-4">Liens rapides</h5>
                    <div class="footer-links">
                        <a href="index.php#features">Fonctionnalités</a>
                        <a href="index.php#how-it-works">Comment ça marche</a>
                        <a href="index.php#pricing">Tarifs</a>
                        <a href="index.php#faq">FAQ</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-4">Contact et support</h5>
                    <div class="footer-links">
                        <a href="help-center.php">Centre d'aide</a>
                        <a href="contact.php">Contact</a>
                        <a href="terms.php">Conditions d'utilisation</a>
                        <a href="privacy.php">Politique de confidentialité</a>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-12">
                    <p class="text-center mb-0">© 2025 URLink. Tous droits réservés.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="js/contact.js"></script>
    <script src="js/theme-switcher.js"></script>
</body>
</html>