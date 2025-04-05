<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/sessions-functions.php';
require_once 'includes/maintenance-check.php';

check_maintenance_mode();

$user_role = get_user_role();

$categories = [
    'general' => [
        'title' => 'Questions générales',
        'icon' => 'fas fa-question-circle',
        'faqs' => [
            [
                'question' => 'Qu\'est-ce que URLink ?',
                'answer' => 'URLink est un service de raccourcissement d\'URL qui vous permet de transformer vos liens longs en URLs courtes, facilement partageables. Notre plateforme offre également des statistiques détaillées de clics et d\'autres fonctionnalités avancées pour vous aider à suivre la performance de vos liens.'
            ],
            [
                'question' => 'Est-ce que URLink est gratuit ?',
                'answer' => 'Oui, URLink propose une version gratuite avec des fonctionnalités de base. Pour des fonctionnalités plus avancées et sans limitation, nous proposons également un plan Entreprise.'
            ],
            [
                'question' => 'Dois-je créer un compte pour utiliser URLink ?',
                'answer' => 'Non, vous pouvez raccourcir des liens sans créer de compte. Cependant, la création d\'un compte vous permet de gérer vos liens, d\'accéder à des statistiques détaillées et de bénéficier de fonctionnalités supplémentaires.'
            ]
        ]
    ],
    'technical' => [
        'title' => 'Questions techniques',
        'icon' => 'fas fa-cogs',
        'faqs' => [
            [
                'question' => 'Quelle est la durée de vie d\'un lien raccourci ?',
                'answer' => 'Pour les utilisateurs inscrits, les liens n\'expirent pas par défaut, sauf si une durée d\'expiration est définie. Pour les utilisateurs non inscrits (invités), les liens expirent après 48 heures.'
            ],
            [
                'question' => 'Comment puis-je personnaliser mes liens raccourcis ?',
                'answer' => 'Les utilisateurs inscrits peuvent personnaliser leurs liens en spécifiant un code personnalisé lors de la création du lien. Par exemple, au lieu d\'avoir "epita-alpha.13h37.io/XyZ123", vous pouvez créer "epita-alpha.13h37.io/mon-lien".'
            ],
            [
                'question' => 'Que se passe-t-il si je perds l\'accès à mon compte ?',
                'answer' => 'Si vous avez oublié votre mot de passe, vous pouvez utiliser la fonction "Mot de passe oublié" sur la page de connexion. Si vous avez perdu l\'accès à votre email, veuillez nous contacter via notre formulaire de contact.'
            ]
        ]
    ],
    'statistics' => [
        'title' => 'Statistiques et analyses',
        'icon' => 'fas fa-chart-bar',
        'faqs' => [
            [
                'question' => 'Quelles statistiques sont disponibles pour mes liens ?',
                'answer' => 'URLink fournit des statistiques détaillées pour chaque lien, incluant le nombre total de clics, la répartition par jour et par heure, les navigateurs et plateformes utilisés, les sources de trafic et plus encore.'
            ],
            [
                'question' => 'Comment puis-je accéder aux statistiques de mes liens ?',
                'answer' => 'Pour accéder aux statistiques d\'un lien, connectez-vous à votre compte, accédez à votre tableau de bord et cliquez sur l\'icône de statistiques à côté du lien concerné. Vous pouvez également ajouter un astérisque (*) à la fin de votre lien pour accéder directement aux statistiques (exemple: epita-alpha.13h37.io/abcd*).'
            ],
            [
                'question' => 'Est-ce que les statistiques sont en temps réel ?',
                'answer' => 'Oui, les statistiques sont mises à jour en temps réel. Chaque clic sur votre lien est immédiatement enregistré et reflété dans vos statistiques.'
            ]
        ]
    ],
    'security' => [
        'title' => 'Sécurité et confidentialité',
        'icon' => 'fas fa-shield-alt',
        'faqs' => [
            [
                'question' => 'Mes liens sont-ils sécurisés ?',
                'answer' => 'Oui, tous les liens URLink sont analysés pour détecter les menaces potentielles. De plus, tous nos liens sont servis via HTTPS pour garantir une connexion sécurisée.'
            ],
            [
                'question' => 'Comment URLink protège-t-il mes données ?',
                'answer' => 'Nous prenons la sécurité des données très au sérieux. Toutes les données sensibles sont cryptées, et nous n\'utilisons jamais vos informations personnelles à des fins commerciales sans votre consentement explicite.'
            ],
            [
                'question' => 'Puis-je supprimer mes liens et mes données ?',
                'answer' => 'Oui, vous pouvez supprimer n\'importe lequel de vos liens à tout moment depuis votre tableau de bord. Si vous souhaitez supprimer complètement votre compte et toutes vos données, veuillez nous contacter via notre formulaire de contact.'
            ]
        ]
    ],
    'billing' => [
        'title' => 'Facturation et abonnement',
        'icon' => 'fas fa-credit-card',
        'faqs' => [
            [
                'question' => 'Quels moyens de paiement acceptez-vous ?',
                'answer' => 'Pour les plans payants, nous acceptons les principales cartes de crédit (Visa, Mastercard, American Express) ainsi que PayPal.'
            ],
            [
                'question' => 'Comment puis-je passer du plan gratuit au plan Entreprise ?',
                'answer' => 'Pour passer au plan Entreprise, connectez-vous à votre compte, accédez à "Mon compte" > "Abonnement" et cliquez sur "Passer au plan Entreprise". Vous serez guidé à travers les étapes de mise à niveau.'
            ],
            [
                'question' => 'Puis-je recevoir une facture pour mon abonnement ?',
                'answer' => 'Oui, une facture est automatiquement générée et envoyée à votre adresse email à chaque paiement. Vous pouvez également accéder à toutes vos factures depuis votre tableau de bord dans la section "Facturation".'
            ]
        ]
    ]
];

$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

$show_all = empty($category_filter) && empty($search_term);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centre d'aide - URLink</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <link href="css/help-center.css" rel="stylesheet">
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
                        <a class="nav-link active" href="help-center.php">Aide</a>
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

    <section class="help-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h1 class="display-4 fw-bold mb-4">Centre d'aide URLink</h1>
                    <p class="lead mb-5">Trouvez rapidement des réponses à vos questions</p>
                    
                    <form action="help-center.php" method="get" class="search-form">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Rechercher dans le centre d'aide..." value="<?php echo htmlspecialchars($search_term); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="help-content py-5">
        <div class="container">
            <?php if (!empty($search_term)): ?>
                <div class="search-results-header">
                    <h2>Résultats de recherche pour "<?php echo htmlspecialchars($search_term); ?>"</h2>
                    <a href="help-center.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-times me-1"></i> Effacer la recherche
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($show_all): ?>
                <div class="row mb-5">
                    <?php foreach ($categories as $key => $category): ?>
                        <div class="col-md-4 mb-4">
                            <a href="?category=<?php echo $key; ?>" class="category-card">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <div class="category-icon">
                                            <i class="<?php echo $category['icon']; ?>"></i>
                                        </div>
                                        <h3><?php echo $category['title']; ?></h3>
                                        <p class="text-muted"><?php echo count($category['faqs']); ?> articles</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-12">
                    <?php if (!empty($category_filter) && isset($categories[$category_filter])): ?>
                        <div class="category-header mb-4">
                            <h2><i class="<?php echo $categories[$category_filter]['icon']; ?> me-2"></i> <?php echo $categories[$category_filter]['title']; ?></h2>
                            <a href="help-center.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-1"></i> Retour à toutes les catégories
                            </a>
                        </div>
                        
                        <div class="accordion faq-accordion" id="faqAccordion">
                            <?php foreach ($categories[$category_filter]['faqs'] as $index => $faq): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
                                            <?php echo htmlspecialchars($faq['question']); ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            <?php echo htmlspecialchars($faq['answer']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($search_term)): ?>
                        <?php
                        $search_results = [];
                        $result_count = 0;
                        
                        foreach ($categories as $cat_key => $category) {
                            foreach ($category['faqs'] as $faq) {
                                if (stripos($faq['question'], $search_term) !== false || 
                                    stripos($faq['answer'], $search_term) !== false) {
                                    $search_results[] = [
                                        'category' => $cat_key,
                                        'category_title' => $category['title'],
                                        'category_icon' => $category['icon'],
                                        'question' => $faq['question'],
                                        'answer' => $faq['answer']
                                    ];
                                    $result_count++;
                                }
                            }
                        }
                        ?>
                        
                        <?php if ($result_count > 0): ?>
                            <div class="search-results">
                                <p class="results-count"><?php echo $result_count; ?> résultat(s) trouvé(s)</p>
                                
                                <div class="accordion faq-accordion" id="searchAccordion">
                                    <?php foreach ($search_results as $index => $result): ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="searchHeading<?php echo $index; ?>">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse<?php echo $index; ?>" aria-expanded="false" aria-controls="searchCollapse<?php echo $index; ?>">
                                                    <?php echo htmlspecialchars($result['question']); ?>
                                                    <span class="category-badge">
                                                        <i class="<?php echo $result['category_icon']; ?> me-1"></i>
                                                        <?php echo htmlspecialchars($result['category_title']); ?>
                                                    </span>
                                                </button>
                                            </h2>
                                            <div id="searchCollapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="searchHeading<?php echo $index; ?>" data-bs-parent="#searchAccordion">
                                                <div class="accordion-body">
                                                    <?php echo htmlspecialchars($result['answer']); ?>
                                                    <div class="mt-3">
                                                        <a href="?category=<?php echo $result['category']; ?>" class="btn btn-sm btn-outline-primary">
                                                            Voir plus dans "<?php echo htmlspecialchars($result['category_title']); ?>"
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="no-results text-center">
                                <div class="no-results-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h3>Aucun résultat trouvé</h3>
                                <p>Nous n'avons pas trouvé de résultats pour "<?php echo htmlspecialchars($search_term); ?>".</p>
                                <p>Suggestions:</p>
                                <ul class="list-unstyled">
                                    <li>Vérifiez l'orthographe des termes de recherche.</li>
                                    <li>Essayez d'utiliser des mots-clés différents.</li>
                                    <li>Utilisez des termes plus généraux.</li>
                                </ul>
                                <a href="help-center.php" class="btn btn-primary mt-3">Parcourir toutes les catégories</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-cta py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2>Vous n'avez pas trouvé votre réponse ?</h2>
                    <p class="lead mb-4">Notre équipe de support est prête à vous aider avec toutes vos questions.</p>
                    <a href="contact.php" class="btn btn-primary btn-lg">Contactez-nous</a>
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
    <script src="js/help-center.js"></script>
    <script src="js/theme-switcher.js"></script>
</body>
</html>