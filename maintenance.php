<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/maintenance-check.php';

// Si l'utilisateur est un administrateur, rediriger vers la page d'accueil
if (is_admin()) {
    header("Location: index.php");
    exit();
}

// Si le site n'est pas en maintenance, rediriger vers la page d'accueil
if (!is_maintenance_mode()) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site en maintenance - URLink</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/site.webmanifest">
    <style>
        body {
            background: linear-gradient(135deg, #4361ee, #3a0ca3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .maintenance-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .maintenance-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            color: #4cc9f0;
        }
        
        h1 {
            margin-bottom: 1.5rem;
        }
        
        .login-link {
            display: inline-block;
            background-color: #4cc9f0;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            margin-top: 2rem;
            transition: all 0.3s ease;
        }
        
        .login-link:hover {
            background-color: #3a0ca3;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">
            <i class="fas fa-tools"></i>
        </div>
        <h1>Site en maintenance</h1>
        <p>URLink est actuellement en cours de maintenance.</p>
        <p>Nous travaillons pour améliorer votre expérience et serons de retour très bientôt.</p>
        <p>Nous vous remercions pour votre patience et votre compréhension.</p>
        
        <a href="login.php" class="login-link">
            <i class="fas fa-sign-in-alt me-2"></i> Connexion administrateur
        </a>
    </div>
</body>
</html>