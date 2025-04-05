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
    <title>Politique de confidentialité - URLink</title>
    
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
            <h1>Politique de confidentialité</h1>
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
                            <li class="nav-item"><a class="nav-link scrollto" href="#introduction">Introduction</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#collection">Informations collectées</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#usage">Utilisation des informations</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#sharing">Partage des informations</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#cookies">Cookies et technologies similaires</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#security">Sécurité des données</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#retention">Conservation des données</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#rights">Vos droits</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#children">Protection des mineurs</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#international">Transferts internationaux</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#changes">Modifications de la politique</a></li>
                            <li class="nav-item"><a class="nav-link scrollto" href="#contact">Contact</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="terms-content">
                    <section id="introduction">
                        <h2>1. Introduction</h2>
                        <p>Chez URLink, nous prenons la protection de vos données personnelles très au sérieux. Cette politique de confidentialité explique comment nous collectons, utilisons, partageons et protégeons vos informations lorsque vous utilisez notre service de raccourcissement d'URL.</p>
                        <p>Nous vous encourageons à lire attentivement cette politique pour comprendre comment nous traitons vos données personnelles. En utilisant nos services, vous consentez aux pratiques décrites dans cette politique.</p>
                    </section>
                    
                    <section id="collection">
                        <h2>2. Informations collectées</h2>
                        <p>Nous collectons plusieurs types d'informations vous concernant :</p>
                        
                        <h3>Informations que vous nous fournissez</h3>
                        <ul>
                            <li><strong>Informations de compte</strong> : Lorsque vous créez un compte, nous collectons votre nom d'utilisateur, adresse e-mail et mot de passe.</li>
                            <li><strong>URLs soumises</strong> : Les URLs longues que vous souhaitez raccourcir.</li>
                            <li><strong>Contenu des communications</strong> : Les informations que vous partagez lorsque vous contactez notre service client ou répondez à nos enquêtes.</li>
                        </ul>
                        
                        <h3>Informations collectées automatiquement</h3>
                        <ul>
                            <li><strong>Données d'utilisation</strong> : Comment vous interagissez avec nos services, les pages que vous visitez et les fonctionnalités que vous utilisez.</li>
                            <li><strong>Informations sur l'appareil</strong> : Type d'appareil, système d'exploitation, type de navigateur, paramètres de langue et adresse IP.</li>
                            <li><strong>Données de localisation</strong> : Nous pouvons déduire votre localisation générale à partir de votre adresse IP.</li>
                            <li><strong>Données de statistiques</strong> : Pour les liens raccourcis, nous collectons des informations sur le nombre de clics, l'heure des clics, le pays d'origine, le navigateur et le système d'exploitation des visiteurs.</li>
                        </ul>
                    </section>
                    
                    <section id="usage">
                        <h2>3. Utilisation des informations</h2>
                        <p>Nous utilisons les informations collectées pour :</p>
                        <ul>
                            <li>Fournir, maintenir et améliorer nos services de raccourcissement d'URL.</li>
                            <li>Créer et gérer votre compte.</li>
                            <li>Fournir des statistiques et des analyses sur l'utilisation de vos liens raccourcis.</li>
                            <li>Communiquer avec vous, notamment pour vous envoyer des notifications, des mises à jour de service et des informations sur la sécurité.</li>
                            <li>Résoudre les problèmes techniques et assurer la sécurité de notre plateforme.</li>
                            <li>Respecter nos obligations légales et réglementaires.</li>
                            <li>Détecter et prévenir les fraudes et les abus.</li>
                        </ul>
                    </section>
                    
                    <section id="sharing">
                        <h2>4. Partage des informations</h2>
                        <p>Nous pouvons partager vos informations dans les contextes suivants :</p>
                        
                        <h3>Avec votre consentement</h3>
                        <p>Nous pouvons partager vos informations lorsque vous nous donnez explicitement votre consentement pour le faire.</p>
                        
                        <h3>Prestataires de services</h3>
                        <p>Nous travaillons avec des prestataires de services tiers qui nous aident à fournir et à améliorer nos services (hébergement, analyse, support client). Ces prestataires n'ont accès qu'aux informations nécessaires pour effectuer leur travail et sont contractuellement tenus de ne pas les divulguer ni les utiliser à d'autres fins.</p>
                        
                        <h3>Exigences légales</h3>
                        <p>Nous pouvons divulguer vos informations si nous estimons de bonne foi que cela est nécessaire pour :</p>
                        <ul>
                            <li>Se conformer à une obligation légale, réglementaire ou à une procédure judiciaire.</li>
                            <li>Protéger les droits, la propriété ou la sécurité d'URLink, de nos utilisateurs ou du public.</li>
                            <li>Détecter, prévenir ou traiter les fraudes, abus ou problèmes de sécurité.</li>
                        </ul>
                        
                        <h3>Transferts d'entreprise</h3>
                        <p>Si URLink est impliqué dans une fusion, acquisition, vente d'actifs ou procédure d'insolvabilité, vos informations peuvent être transférées ou vendues dans le cadre de cette transaction. Nous vous informerons de tout changement de propriété ou d'utilisation de vos données personnelles.</p>
                    </section>
                    
                    <section id="cookies">
                        <h2>5. Cookies et technologies similaires</h2>
                        <p>Nous utilisons des cookies et des technologies similaires pour améliorer votre expérience sur notre site, comprendre comment vous interagissez avec nos services et personnaliser notre contenu.</p>
                        
                        <h3>Types de cookies que nous utilisons</h3>
                        <ul>
                            <li><strong>Cookies essentiels</strong> : Nécessaires au fonctionnement de notre site web. Ils vous permettent de naviguer sur le site et d'utiliser ses fonctionnalités.</li>
                            <li><strong>Cookies de performance et d'analyse</strong> : Nous aident à comprendre comment les visiteurs interagissent avec notre site en collectant des informations anonymes.</li>
                            <li><strong>Cookies de fonctionnalité</strong> : Permettent au site de mémoriser vos choix (comme votre nom d'utilisateur, votre langue ou votre région) pour proposer des fonctionnalités améliorées et personnalisées.</li>
                            <li><strong>Cookies de ciblage ou publicitaires</strong> : Utilisés pour diffuser des publicités plus pertinentes pour vous et vos intérêts.</li>
                        </ul>
                        
                        <h3>Contrôle des cookies</h3>
                        <p>La plupart des navigateurs web vous permettent de contrôler les cookies via leurs paramètres. Vous pouvez généralement modifier les paramètres de votre navigateur pour refuser les nouveaux cookies, être averti lorsqu'un nouveau cookie est créé, ou désactiver complètement les cookies. Notez cependant que certaines parties de notre service peuvent ne pas fonctionner correctement si vous désactivez les cookies.</p>
                    </section>
                    
                    <section id="security">
                        <h2>6. Sécurité des données</h2>
                        <p>Nous mettons en œuvre des mesures de sécurité techniques, administratives et physiques appropriées pour protéger vos informations personnelles contre la perte, le vol, l'utilisation abusive et l'accès non autorisé, la divulgation, l'altération et la destruction. Ces mesures comprennent :</p>
                        <ul>
                            <li>Le chiffrement des informations sensibles.</li>
                            <li>L'utilisation de connexions HTTPS pour toutes les transactions.</li>
                            <li>Des examens réguliers de nos pratiques de collecte, de stockage et de traitement des données.</li>
                            <li>Des restrictions d'accès aux informations personnelles aux employés, contractants et agents qui ont besoin de ces informations pour traiter les données pour nous.</li>
                        </ul>
                        <p>Cependant, aucune méthode de transmission sur Internet ou de stockage électronique n'est totalement sécurisée. Par conséquent, nous ne pouvons pas garantir la sécurité absolue de vos informations.</p>
                    </section>
                    
                    <section id="retention">
                        <h2>7. Conservation des données</h2>
                        <p>Nous conservons vos informations personnelles aussi longtemps que nécessaire pour fournir les services que vous avez demandés, ou pour d'autres fins essentielles telles que :</p>
                        <ul>
                            <li>Se conformer à nos obligations légales.</li>
                            <li>Résoudre les litiges.</li>
                            <li>Appliquer nos accords.</li>
                        </ul>
                        <p>Les périodes de conservation spécifiques dépendent du type d'information et de son usage :</p>
                        <ul>
                            <li><strong>Informations de compte</strong> : Conservées tant que votre compte est actif ou aussi longtemps que nécessaire pour vous fournir nos services.</li>
                            <li><strong>URLs raccourcies et données associées</strong> : Conservées selon la durée que vous avez spécifiée lors de la création du lien ou, pour les utilisateurs inscrits, jusqu'à la suppression du lien ou du compte.</li>
                            <li><strong>Données de logs et d'analyse</strong> : Généralement conservées pendant 90 jours avant d'être anonymisées ou supprimées.</li>
                        </ul>
                    </section>
                    
                    <section id="rights">
                        <h2>8. Vos droits</h2>
                        <p>Selon votre lieu de résidence, vous pouvez avoir certains droits concernant vos informations personnelles. Ces droits peuvent inclure :</p>
                        
                        <h3>Droits d'accès et de contrôle</h3>
                        <ul>
                            <li><strong>Droit d'accès</strong> : Vous pouvez demander une copie des informations personnelles que nous détenons à votre sujet.</li>
                            <li><strong>Droit de rectification</strong> : Vous pouvez demander la correction des informations inexactes ou incomplètes vous concernant.</li>
                            <li><strong>Droit à l'effacement</strong> : Dans certains cas, vous pouvez demander la suppression de vos informations personnelles.</li>
                            <li><strong>Droit à la limitation du traitement</strong> : Dans certains cas, vous pouvez demander de limiter l'utilisation de vos informations.</li>
                            <li><strong>Droit à la portabilité des données</strong> : Vous pouvez demander à recevoir vos informations personnelles dans un format structuré, couramment utilisé et lisible par machine.</li>
                            <li><strong>Droit d'opposition</strong> : Vous pouvez vous opposer au traitement de vos informations personnelles dans certaines circonstances.</li>
                        </ul>
                        
                        <h3>Comment exercer vos droits</h3>
                        <p>Pour exercer vos droits, vous pouvez :</p>
                        <ul>
                            <li>Accéder à votre compte et modifier directement certaines informations.</li>
                            <li>Nous contacter par e-mail à <a href="mailto:privacy@urlink.com">privacy@urlink.com</a>.</li>
                            <li>Nous contacter par courrier à l'adresse indiquée dans la section Contact ci-dessous.</li>
                        </ul>
                        <p>Nous répondrons à votre demande dans les délais prévus par la loi applicable (généralement dans un délai de 30 jours). Nous pouvons vous demander des informations supplémentaires pour confirmer votre identité avant de répondre à votre demande.</p>
                    </section>
                    
                    <section id="children">
                        <h2>9. Protection des mineurs</h2>
                        <p>Nos services ne s'adressent pas aux enfants de moins de 16 ans et nous ne collectons pas sciemment des informations personnelles auprès d'enfants de moins de 16 ans. Si vous êtes parent ou tuteur et que vous pensez que votre enfant nous a fourni des informations personnelles, veuillez nous contacter immédiatement. Si nous apprenons que nous avons collecté des informations personnelles auprès d'un enfant de moins de 16 ans sans vérification du consentement parental, nous prendrons des mesures pour supprimer ces informations de nos serveurs.</p>
                    </section>
                    
                    <section id="international">
                        <h2>10. Transferts internationaux</h2>
                        <p>URLink est basé en France et les informations que nous collectons sont régies par la loi française et européenne. Si vous accédez à nos services depuis d'autres régions du monde où les lois concernant la collecte et l'utilisation des données peuvent différer de la législation européenne, veuillez noter que vous nous fournissez vos informations et que vous consentez au transfert et au traitement de ces informations en France et dans d'autres pays où nous opérons.</p>
                        <p>Lorsque nous transférons des informations personnelles en dehors de l'Espace Économique Européen (EEE), nous prenons des mesures appropriées pour garantir que vos informations bénéficient d'un niveau adéquat de protection, notamment par :</p>
                        <ul>
                            <li>L'utilisation de clauses contractuelles types approuvées par la Commission européenne.</li>
                            <li>Le transfert de données vers des pays reconnus par la Commission européenne comme offrant un niveau adéquat de protection.</li>
                            <li>L'obtention de certifications ou d'adhésions à des codes de conduite qui répondent aux exigences européennes.</li>
                        </ul>
                    </section>
                    
                    <section id="changes">
                        <h2>11. Modifications de la politique</h2>
                        <p>Nous pouvons mettre à jour cette politique de confidentialité de temps à autre. La version la plus récente sera toujours disponible sur notre site web avec la date de "dernière mise à jour" en haut de la page.</p>
                        <p>Nous vous encourageons à consulter régulièrement cette politique pour rester informé des modifications. Si nous apportons des changements substantiels à la façon dont nous traitons vos informations personnelles, nous vous informerons par e-mail (si nous avons votre adresse e-mail) ou par un avis bien visible sur notre site web.</p>
                    </section>
                    
                    <section id="contact">
                        <h2>12. Contact</h2>
                        <p>Si vous avez des questions, des préoccupations ou des demandes concernant cette politique de confidentialité ou nos pratiques en matière de données, veuillez nous contacter :</p>
                        <ul>
                            <li>Par e-mail : <a href="mailto:privacy@urlink.com">privacy@urlink.com</a></li>
                            <li>Via notre <a href="contact.php">formulaire de contact</a></li>
                            <li>Par courrier : URLink, 14-16 Rue Voltaire, 94270 Le Kremlin-Bicêtre, France</li>
                        </ul>
                        <p>Délégué à la protection des données (DPO) : <a href="mailto:dpo@urlink.com">dpo@urlink.com</a></p>
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