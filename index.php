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
  <title>URLink - Raccourcisseur d'URL</title>
  
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <link href="css/styles.css" rel="stylesheet">
  <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
  <link rel="manifest" href="favicon/site.webmanifest">
  <link href="css/dark-theme.css" rel="stylesheet">
  
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
    <div class="container">
      <a class="navbar-brand" href="#">
        <i class="fas fa-link me-2"></i>
        URLink
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="#features">Fonctionnalités</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#how-it-works">Comment ça marche</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#pricing">Tarifs</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#faq">FAQ</a>
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

  <section class="hero-section" id="home">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <h1 class="display-4 fw-bold mb-4">Simplifiez vos liens, amplifiez votre impact</h1>
          <p class="lead mb-5">Raccourcissez vos URL longues en un instant. Suivez les clics, personnalisez vos liens et boostez votre présence en ligne.</p>
          <a href="#url-shortener" class="btn btn-light btn-lg px-4 me-md-2 btn-water">Raccourcir maintenant</a>
          <a href="#how-it-works" class="btn btn-outline-light btn-lg px-4 btn-water">Comment ça marche</a>
        </div>
      </div>
    </div>
  </section>

  <section id="url-shortener" class="container">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <div class="url-form">
          <h2 class="text-center mb-4">Raccourcissez votre URL</h2>
          <form id="shorten-form">
            <div class="input-group mb-3">
              <input type="url" class="form-control" id="long-url" placeholder="Collez votre lien long ici..." required>
              <button class="btn btn-primary" type="submit">Raccourcir</button>
            </div>
            
            <?php if ($user_role !== 'guest'): ?>
            <div class="card mt-3 mb-3">
              <div class="card-header bg-light">
                Options avancées
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="custom-code" class="form-label">Code personnalisé (optionnel)</label>
                    <input type="text" class="form-control" id="custom-code" placeholder="moncode" pattern="[a-zA-Z0-9]+" title="Uniquement des lettres et des chiffres">
                    <div class="form-text">Laissez vide pour générer automatiquement</div>
                  </div>
                  <div class="col-md-6">
                    <label for="expiry" class="form-label">Durée de validité</label>
                    <select class="form-select" id="expiry">
                      <option value="">Pas d'expiration</option>
                      <option value="24">24 heures</option>
                      <option value="168">7 jours</option>
                      <option value="720">30 jours</option>
                      <option value="8760">1 an</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <?php endif; ?>
          </form>
          
          <div class="modal fade" id="qr-modal" tabindex="-1" aria-labelledby="qr-modal-label" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="qr-modal-label">QR Code</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                  <img src="" alt="QR Code" class="qr-image img-fluid">
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                  <button type="button" class="btn btn-primary" id="download-qr">Télécharger</button>
                </div>
              </div>
            </div>
          </div>
          
          <div class="link-preview" id="link-result">
            <div class="row align-items-center">
              <div class="col-md-8">
                <p class="mb-1">Votre lien raccourci :</p>
                <p class="shortened-url mb-0" id="short-url"></p>
                
                <div id="link-info-container" class="mt-3 small text-muted">
                  <p class="mb-1">Créé le: <span id="created-at"></span></p>
                  <p class="mb-1" style="display: none;">Expire le: <span id="expiry-datetime"></span></p>
                </div>
                
                <div class="mt-3">
                  <p class="mb-1">Utilisez ces suffixes spéciaux :</p>
                  <ul class="list-unstyled small text-muted">
                    <li><code>+</code> - Pour voir l'URL originale</li>
                    <li><code>*</code> - Pour voir les statistiques (utilisateurs inscrits uniquement)</li>
                    <li><code>-</code> - Pour supprimer le lien (utilisateurs inscrits uniquement)</li>
                  </ul>
                </div>
              </div>
              <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button class="btn btn-sm btn-outline-primary me-2" id="copy-btn">
                  <i class="fas fa-copy me-1"></i> Copier
                </button>
                <button class="btn btn-sm btn-outline-secondary" id="qr-btn">
                  <i class="fas fa-qrcode me-1"></i> QR Code
                </button>
              </div>
            </div>

            <div id="qr-container" style="display: none; margin-top: 15px; text-align: center; background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
              <h5>QR Code pour votre lien :</h5>
              <div id="qrcode" style="margin: 15px auto;"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="features-section" id="features">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold">Fonctionnalités puissantes</h2>
        <p class="lead">Tout ce dont vous avez besoin pour gérer vos liens efficacement</p>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-card border">
            <div class="feature-icon">
              <i class="fas fa-bolt"></i>
            </div>
            <h3>Raccourcissement rapide</h3>
            <p>Transformez vos URLs longues en liens courts et élégants en quelques secondes seulement.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card border">
            <div class="feature-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <h3>Statistiques détaillées</h3>
            <p>Suivez les performances de vos liens avec des analyses en temps réel sur les clics et l'engagement.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card border">
            <div class="feature-icon">
              <i class="fas fa-edit"></i>
            </div>
            <h3>Liens personnalisables</h3>
            <p>Créez des URLs personnalisées qui correspondent à votre marque et sont faciles à mémoriser.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card border">
            <div class="feature-icon">
              <i class="fas fa-qrcode"></i>
            </div>
            <h3>Codes QR intégrés</h3>
            <p>Générez automatiquement des codes QR pour vos liens raccourcis, idéals pour le marketing offline.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card border">
            <div class="feature-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Sécurisé et fiable</h3>
            <p>Protection contre les spams et analyses de sécurité pour chaque lien généré.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card border">
            <div class="feature-icon">
              <i class="fas fa-history"></i>
            </div>
            <h3>Historique des liens</h3>
            <p>Conservez l'historique complet de tous vos liens raccourcis pour une gestion simplifiée.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="stats-section">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-number" data-count="10">10M+</div>
            <div class="stat-label">Liens raccourcis</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-number" data-count="98">98%</div>
            <div class="stat-label">Fiabilité garantie</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card">
            <div class="stat-number" data-count="150">150K+</div>
            <div class="stat-label">Utilisateurs satisfaits</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="how-it-works-section" id="how-it-works">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold">Comment ça marche</h2>
        <p class="lead">Un processus simple en 3 étapes pour raccourcir vos URLs</p>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="step-card">
            <div class="step-number">1</div>
            <h3>Collez votre lien long</h3>
            <p>Copiez votre URL longue et collez-la dans notre barre de raccourcissement. Vous pouvez même raccourcir plusieurs liens à la fois.</p>
          </div>
          <div class="step-card">
            <div class="step-number">2</div>
            <h3>Cliquez sur "Raccourcir"</h3>
            <p>Notre système traitera instantanément votre lien et générera une URL courte et facile à partager.</p>
          </div>
          <div class="step-card">
            <div class="step-number">3</div>
            <h3>Copiez et partagez</h3>
            <p>Votre lien raccourci est prêt ! Copiez-le et partagez-le sur vos réseaux sociaux, emails, messages ou n'importe où ailleurs.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="pricing-section" id="pricing">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold">Plans et tarifs</h2>
        <p class="lead">Des forfaits flexibles pour tous les besoins</p>
      </div>
      <div class="row g-4 justify-content-center">
        <div class="col-lg-4 col-md-6">
          <div class="pricing-card">
            <div class="pricing-header">
              <h3>Entreprise</h3>
              <div class="pricing-price">29,99€</div>
              <div class="pricing-period">par mois</div>
            </div>
            <ul class="pricing-features">
              <li>Liens illimités</li>
              <li>Statistiques avancées</li>
              <li>Générateur de QR code</li>
              <li>Liens personnalisés</li>
              <li>Support dédié 24/7</li>
              <li>API complète</li>
              <li>Intégration de marque</li>
            </ul>
            <a href="#url-shortener" class="btn btn-primary w-100 py-2">Contacter les ventes</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="testimonials-section" id="testimonials">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold">Ce que disent nos clients</h2>
        <p class="lead">Rejoignez des milliers d'utilisateurs satisfaits</p>
      </div>
      <div class="row g-4">
        <div class="col-lg-4 col-md-6">
          <div class="testimonial-card">
            <div class="testimonial-text">
              "URLink a transformé notre stratégie marketing. Les liens courts et personnalisés ont considérablement augmenté notre taux de clics."
            </div>
            <div class="testimonial-author">
              <div class="testimonial-avatar">
                <i class="fas fa-user"></i>
              </div>
              <div>
                <h5 class="mb-0">Sophie Martin</h5>
                <p class="mb-0 text-muted">Responsable Marketing, TechCorp</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="testimonial-card">
            <div class="testimonial-text">
              "Le tableau de bord analytique est incroyable. Je peux suivre tous mes liens en un coup d'œil et optimiser mes campagnes en temps réel."
            </div>
            <div class="testimonial-author">
              <div class="testimonial-avatar">
                <i class="fas fa-user"></i>
              </div>
              <div>
                <h5 class="mb-0">Thomas Dubois</h5>
                <p class="mb-0 text-muted">Entrepreneur indépendant</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="testimonial-card">
            <div class="testimonial-text">
              "En tant que freelance, je gère de nombreux liens pour différents clients. URLink simplifie mon travail quotidien et me permet de paraître plus professionnel."
            </div>
            <div class="testimonial-author">
              <div class="testimonial-avatar">
                <i class="fas fa-user"></i>
              </div>
              <div>
                <h5 class="mb-0">Julie Moreau</h5>
                <p class="mb-0 text-muted">Designer Freelance</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="faq-section" id="faq">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="display-5 fw-bold">Questions fréquentes</h2>
        <p class="lead">Tout ce que vous devez savoir sur notre service</p>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="accordion" id="faqAccordion">
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                  Combien de temps mes liens raccourcis restent-ils actifs ?
                </button>
              </h2>
              <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  Avec notre plan gratuit, vos liens restent actifs à vie. De plus, avec l'abonnement Entreprise, vous bénéficiez d'un support dédié pour répondre à toutes vos questions et besoins spécifiques.
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                  Puis-je personnaliser mes liens raccourcis ?
                </button>
              </h2>
              <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  Oui, la personnalisation des liens est disponible. Vous pouvez créer des URLs personnalisées qui reflètent votre marque ou le contenu de votre lien, ce qui les rend plus mémorables et professionnels.
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                  Quelles statistiques sont disponibles pour mes liens ?
                </button>
              </h2>
              <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  Notre plan gratuit fournit des statistiques de base comme le nombre de clics et leur provenance géographique. Les plans Pro et Entreprise offrent des statistiques avancées incluant des informations démographiques, les appareils utilisés, les heures de pointe et bien plus encore.
                </div>
              </div>
            </div>
            <div class="accordion-item">
              <h2 class="accordion-header" id="headingFour">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                  Mes liens sont-ils sécurisés ?
                </button>
              </h2>
              <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                <div class="accordion-body">
                  Absolument. Nous analysons chaque URL soumise pour détecter les menaces potentielles. Tous nos liens sont également servis via HTTPS pour garantir une connexion sécurisée.
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="cta-section" id="cta">
    <div class="container">
      <div class="row justify-content-center text-center">
        <div class="col-lg-8">
          <h2 class="display-5 fw-bold mb-4">Prêt à raccourcir vos liens ?</h2>
          <p class="lead mb-5">Rejoignez des milliers d'utilisateurs qui font confiance à URLink pour leurs besoins de gestion de liens.</p>
          <div class="d-grid gap-3 d-md-flex justify-content-md-center">
            <a href="#url-shortener" class="btn btn-light btn-lg px-4 btn-water">Commencer gratuitement</a>
            <a href="#pricing" class="btn btn-outline-light btn-lg px-4 btn-water">Voir les tarifs</a>
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
            <a href="#features">Fonctionnalités</a>
            <a href="#how-it-works">Comment ça marche</a>
            <a href="#pricing">Tarifs</a>
            <a href="#faq">FAQ</a>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <h5 class="mb-4">Contact et support</h5>
          <div class="footer-links">
            <a href="help-center.php">Centre d'aide</a>
            <a href="contact.php">Contact</a>
            <a href="#">Conditions d'utilisation</a>
            <a href="#">Politique de confidentialité</a>
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
  <script src="js/script.js"></script>
  <script src="js/shorten.js"></script>
  <script src="js/theme-switcher.js"></script>
</body>
</html>