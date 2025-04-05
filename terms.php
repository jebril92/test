<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/sessions-functions.php';
require_once 'includes/maintenance-check.php';

check_maintenance_mode();

$user_role = get_user_role();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions d'utilisation - URLink</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/terms.css" rel="stylesheet">
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
                        <a class="nav-link" href="contact.php">Contact</a>
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

    <div class="page-header">
        <div class="container">
            <h1>Conditions d'utilisation</h1>
            <p class="lead">Dernière mise à jour : 1 avril 2025</p>
        </div>
    </div>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="sticky-top" style="top: 100px;">
                    <div class="terms-nav">
                        <h5>Table des matières</h5>
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link" href="#introduction">Introduction</a></li>
                            <li class="nav-item"><a class="nav-link" href="#definitions">Définitions</a></li>
                            <li class="nav-item"><a class="nav-link" href="#account">Création de compte</a></li>
                            <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                            <li class="nav-item"><a class="nav-link" href="#usage">Utilisation acceptable</a></li>
                            <li class="nav-item"><a class="nav-link" href="#prohibited">Contenu prohibé</a></li>
                            <li class="nav-item"><a class="nav-link" href="#termination">Résiliation</a></li>
                            <li class="nav-item"><a class="nav-link" href="#liability">Limitation de responsabilité</a></li>
                            <li class="nav-item"><a class="nav-link" href="#privacy">Confidentialité</a></li>
                            <li class="nav-item"><a class="nav-link" href="#modification">Modification des conditions</a></li>
                            <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="terms-content">
                    <section id="introduction">
                        <h2>1. Introduction</h2>
                        <p>Bienvenue sur URLink, un service de raccourcissement d'URL. Les présentes conditions d'utilisation régissent votre utilisation de notre site web et de tous les services associés offerts par URLink.</p>
                        <p>En utilisant notre site ou nos services, vous acceptez d'être lié par ces conditions. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre site ou nos services.</p>
                    </section>
                    
                    <section id="definitions">
                        <h2>2. Définitions</h2>
                        <p>Dans ces conditions d'utilisation :</p>
                        <ul>
                            <li><strong>"URLink"</strong>, <strong>"nous"</strong>, <strong>"notre"</strong> ou <strong>"nos"</strong> désigne le service de raccourcissement d'URL URLink.</li>
                            <li><strong>"Utilisateur"</strong>, <strong>"vous"</strong>, <strong>"votre"</strong> ou <strong>"vos"</strong> désigne toute personne qui accède à notre site ou utilise nos services.</li>
                            <li><strong>"Service"</strong> désigne le raccourcissement d'URL et tous les autres services fournis par URLink.</li>
                            <li><strong>"URL raccourcie"</strong> désigne un lien court qui redirige vers un site web spécifié par l'utilisateur.</li>
                        </ul>
                    </section>
                    
                    <section id="account">
                        <h2>3. Création de compte</h2>
                        <p>Pour profiter pleinement de nos services, vous pouvez créer un compte. En créant un compte, vous acceptez :</p>
                        <ul>
                            <li>De fournir des informations exactes, complètes et à jour.</li>
                            <li>De maintenir la confidentialité de votre mot de passe et d'être responsable de toutes les activités effectuées sous votre compte.</li>
                            <li>De nous informer immédiatement de toute utilisation non autorisée de votre compte ou de toute autre violation de sécurité.</li>
                        </ul>
                        <p>Nous nous réservons le droit de désactiver tout compte à notre seule discrétion, notamment si nous estimons que vous avez enfreint les présentes conditions.</p>
                    </section>
                    
                    <section id="services">
                        <h2>4. Services</h2>
                        <p>URLink fournit un service de raccourcissement d'URL qui vous permet de créer des liens courts pour des URLs plus longues. Notre service comprend :</p>
                        <ul>
                            <li>La création de liens courts pour des URLs longues.</li>
                            <li>Le suivi des statistiques de clics (pour les utilisateurs enregistrés).</li>
                            <li>La personnalisation des liens raccourcis (pour les utilisateurs enregistrés).</li>
                            <li>La création de codes QR pour vos liens raccourcis.</li>
                        </ul>
                        <p>Les services gratuits peuvent être soumis à des limitations, comme indiqué dans nos offres actuelles. Des fonctionnalités supplémentaires sont disponibles dans nos forfaits payants.</p>
                    </section>
                    
                    <section id="usage">
                        <h2>5. Utilisation acceptable</h2>
                        <p>Vous acceptez d'utiliser URLink uniquement à des fins légales et conformément aux présentes conditions. Vous vous engagez à ne pas utiliser nos services pour :</p>
                        <ul>
                            <li>Violer les lois applicables ou les droits d'autrui.</li>
                            <li>Distribuer des logiciels malveillants, des virus ou tout autre code nuisible.</li>
                            <li>Interférer avec ou perturber l'intégrité ou les performances de nos services.</li>
                            <li>Collecter ou stocker des informations personnelles sur d'autres utilisateurs sans leur consentement.</li>
                        </ul>
                    </section>
                    
                    <section id="prohibited">
                        <h2>6. Contenu prohibé</h2>
                        <p>Vous ne pouvez pas utiliser URLink pour raccourcir des URLs menant à du contenu :</p>
                        <ul>
                            <li>Illégal selon les lois françaises ou internationales.</li>
                            <li>Pornographique, obscène ou sexuellement explicite.</li>
                            <li>Faisant la promotion de la violence, de la haine ou de la discrimination.</li>
                            <li>Contenant des logiciels malveillants ou des virus.</li>
                            <li>Violant les droits de propriété intellectuelle d'autrui.</li>
                            <li>Lié au phishing, aux arnaques ou autres activités frauduleuses.</li>
                        </ul>
                        <p>Nous nous réservons le droit de désactiver tout lien à notre seule discrétion si nous estimons qu'il viole ces conditions.</p>
                    </section>
                    
                    <section id="termination">
                        <h2>7. Résiliation</h2>
                        <p>Nous pouvons résilier ou suspendre votre accès à nos services immédiatement, sans préavis ni responsabilité, pour quelque raison que ce soit, y compris, sans limitation, si vous enfreignez les présentes conditions.</p>
                        <p>Vous pouvez également mettre fin à votre compte à tout moment en nous contactant. À la résiliation, votre droit d'utiliser nos services cessera immédiatement.</p>
                    </section>
                    
                    <section id="liability">
                        <h2>8. Limitation de responsabilité</h2>
                        <p>Dans toute la mesure permise par la loi applicable, URLink et ses dirigeants, employés, partenaires et agents ne seront pas responsables :</p>
                        <ul>
                            <li>Des dommages indirects, accessoires, spéciaux, consécutifs ou punitifs.</li>
                            <li>De toute perte de profits, de revenus, de données, d'opportunités commerciales.</li>
                            <li>Des dommages liés à l'accès, à l'utilisation ou à l'incapacité d'accéder ou d'utiliser nos services.</li>
                            <li>Du contenu des sites web vers lesquels nos URLs raccourcies redirigent.</li>
                        </ul>
                        <p>Notre service est fourni "tel quel" et "tel que disponible" sans garanties d'aucune sorte, expresses ou implicites.</p>
                    </section>
                    
                    <section id="privacy">
                        <h2>9. Confidentialité</h2>
                        <p>Notre <a href="privacy.php">Politique de Confidentialité</a> décrit comment nous collectons, utilisons et partageons vos informations personnelles. En utilisant nos services, vous acceptez notre politique de confidentialité.</p>
                    </section>
                    
                    <section id="modification">
                        <h2>10. Modification des conditions</h2>
                        <p>Nous nous réservons le droit de modifier ces conditions à tout moment. Nous informerons les utilisateurs des modifications importantes par e-mail ou par un avis sur notre site.</p>
                        <p>Votre utilisation continue de nos services après la publication des conditions modifiées constitue votre acceptation de ces modifications.</p>
                    </section>
                    
                    <section id="contact">
                        <h2>11. Contact</h2>
                        <p>Si vous avez des questions concernant ces conditions, veuillez nous contacter :</p>
                        <ul>
                            <li>Par e-mail : <a href="mailto:support@urlink.com">support@urlink.com</a></li>
                            <li>Via notre <a href="contact.php">formulaire de contact</a></li>
                            <li>Par courrier : URLink, 14-16 Rue Voltaire, 94270 Le Kremlin-Bicêtre, France</li>
                        </ul>
                    </section>
                </div>
            </div>
        </div>
    </div>

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
    <script src="js/terms.js"></script>
    <script src="js/theme-switcher.js"></script>
</body>
</html>