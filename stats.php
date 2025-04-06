<?php
session_start();
require_once 'config/db-config.php';
require_once 'includes/sessions-functions.php';

if (!is_logged_in()) {
    header("Location: login.php?message=login_required");
    exit();
}

$url_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($url_id <= 0) {
    header("Location: dashboard.php?error=invalid_url");
    exit();
}

$url_info = null;
$click_stats_by_day = [];
$click_stats_by_hour = [];
$click_stats_by_browser = [];
$click_stats_by_platform = [];
$click_stats_by_location = [];
$click_stats_by_referrer = [];
$total_clicks = 0;
$recent_clicks = [];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT s.*, u.username 
                           FROM shortened_urls s 
                           JOIN users u ON s.user_id = u.id 
                           WHERE s.id = ? AND (s.user_id = ? OR ? = 1)");
    $stmt->execute([$url_id, $_SESSION['user_id'], $_SESSION['is_admin']]);
    $url_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$url_info) {
        header("Location: dashboard.php?error=url_not_found");
        exit();
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) FROM click_stats WHERE url_id = ?");
    $stmt->execute([$url_id]);
    $total_clicks = $stmt->fetchColumn();
    
    $stmt = $conn->prepare("
        SELECT 
            DATE(date_clicked) as click_date,
            HOUR(date_clicked) as click_hour,
            COUNT(*) as count
        FROM click_stats 
        WHERE url_id = ? 
        GROUP BY DATE(date_clicked), HOUR(date_clicked)
        ORDER BY click_date, click_hour
    ");
    $stmt->execute([$url_id]);
    $click_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $daily_data = [];
    $hours_data = array_fill(0, 24, 0);

    foreach ($click_stats as $stat) {
        $date = $stat['click_date'];
        $hour = (int)$stat['click_hour'];
        $count = (int)$stat['count'];
        
        if (!isset($daily_data[$date])) {
            $daily_data[$date] = 0;
        }
        $daily_data[$date] += $count;
        
        $hours_data[$hour] += $count;
    }
    
    $click_stats_by_day = [];
    foreach ($daily_data as $date => $count) {
        $click_stats_by_day[] = [
            'click_date' => $date,
            'click_count' => $count
        ];
    }
    
    $stmt = $conn->prepare("
        SELECT 
            CASE
                WHEN user_agent LIKE '%Chrome%' THEN 'Chrome'
                WHEN user_agent LIKE '%Firefox%' THEN 'Firefox'
                WHEN user_agent LIKE '%Safari%' THEN 'Safari'
                WHEN user_agent LIKE '%Edge%' THEN 'Edge'
                WHEN user_agent LIKE '%Opera%' THEN 'Opera'
                WHEN user_agent LIKE '%MSIE%' OR user_agent LIKE '%Trident%' THEN 'Internet Explorer'
                ELSE 'Autre'
            END as browser,
            COUNT(*) as count
        FROM click_stats 
        WHERE url_id = ? 
        GROUP BY browser
        ORDER BY count DESC
    ");
    $stmt->execute([$url_id]);
    $click_stats_by_browser = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $conn->prepare("
        SELECT 
            CASE
                WHEN user_agent LIKE '%Windows%' THEN 'Windows'
                WHEN user_agent LIKE '%Mac OS%' THEN 'MacOS'
                WHEN user_agent LIKE '%Linux%' THEN 'Linux'
                WHEN user_agent LIKE '%Android%' THEN 'Android'
                WHEN user_agent LIKE '%iPhone%' OR user_agent LIKE '%iPad%' THEN 'iOS'
                ELSE 'Autre'
            END as platform,
            COUNT(*) as count
        FROM click_stats 
        WHERE url_id = ? 
        GROUP BY platform
        ORDER BY count DESC
    ");
    $stmt->execute([$url_id]);
    $click_stats_by_platform = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $conn->prepare("
        SELECT 
            CASE
                WHEN referer IS NULL THEN 'Direct'
                WHEN referer LIKE '%google%' THEN 'Google'
                WHEN referer LIKE '%facebook%' THEN 'Facebook'
                WHEN referer LIKE '%twitter%' THEN 'Twitter'
                WHEN referer LIKE '%instagram%' THEN 'Instagram'
                WHEN referer LIKE '%linkedin%' THEN 'LinkedIn'
                ELSE 'Autre'
            END as referrer,
            COUNT(*) as count
        FROM click_stats 
        WHERE url_id = ? 
        GROUP BY referrer
        ORDER BY count DESC
        LIMIT 5
    ");
    $stmt->execute([$url_id]);
    $click_stats_by_referrer = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($conn->query("SHOW COLUMNS FROM click_stats LIKE 'location'")->rowCount() > 0) {
        $stmt = $conn->prepare("
            SELECT 
                IFNULL(location, 'Non spécifié') as location,
                COUNT(*) as count
            FROM click_stats 
            WHERE url_id = ? 
            GROUP BY location
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmt->execute([$url_id]);
        $click_stats_by_location = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    $stmt = $conn->prepare("
        SELECT 
            date_clicked,
            ip_address,
            user_agent,
            referer,
            location
        FROM click_stats 
        WHERE url_id = ? 
        ORDER BY date_clicked DESC
        LIMIT 10
    ");
    $stmt->execute([$url_id]);
    $recent_clicks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Erreur de base de données: " . $e->getMessage());
}

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/";
$short_url = $base_url . $url_info['short_code'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques du lien - URLink</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="css/styles.css" rel="stylesheet">
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
                        <a class="nav-link" href="dashboard.php">Tableau de bord</a>
                    </li>
                    <li class="nav-item ms-lg-3">
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
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Statistiques du lien raccourci</h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="card-title">Détails du lien</h5>
                        <p><strong>URL originale:</strong> <a href="<?php echo htmlspecialchars($url_info['original_url']); ?>" target="_blank"><?php echo htmlspecialchars($url_info['original_url']); ?></a></p>
                        <p><strong>URL raccourcie:</strong> <span id="short-url"><?php echo $short_url; ?></span> 
                            <button class="btn btn-sm btn-primary" onclick="copyToClipboard()"><i class="fas fa-copy"></i> Copier</button>
                            <a href="<?php echo $short_url; ?>" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt"></i> Ouvrir</a>
                        </p>
                        <p><strong>Créé par:</strong> <?php echo htmlspecialchars($url_info['username']); ?></p>
                        <p><strong>Date de création:</strong> <?php echo date('d/m/Y H:i', strtotime($url_info['created_at'])); ?></p>
                        <?php if ($url_info['expiry_datetime']): ?>
                            <p><strong>Expire le:</strong> <?php echo date('d/m/Y H:i', strtotime($url_info['expiry_datetime'])); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="display-4"><?php echo $total_clicks; ?></h2>
                                <p class="lead">Clics totaux</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Clics par jour</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="clicksByDayChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Clics par heure</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="clicksByHourChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Navigateurs</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="browserChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Plateformes</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="platformChart"></canvas>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($click_stats_by_referrer)): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Principales sources de trafic</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="referrerChart"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($click_stats_by_location)): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h5>Principales localisations</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="locationChart"></canvas>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Derniers clics</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date et heure</th>
                                <th>Adresse IP</th>
                                <th>Navigateur</th>
                                <th>Référent</th>
                                <?php if (array_key_exists('location', $recent_clicks[0] ?? [])): ?>
                                <th>Localisation</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_clicks as $click): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($click['date_clicked'])); ?></td>
                                <td><?php echo htmlspecialchars($click['ip_address']); ?></td>
                                <td>
                                    <?php 
                                    $ua = htmlspecialchars($click['user_agent']);
                                    echo (strlen($ua) > 50) ? substr($ua, 0, 50) . '...' : $ua; 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if (empty($click['referer'])) {
                                        echo 'Direct';
                                    } else {
                                        $referer = htmlspecialchars($click['referer']);
                                        echo (strlen($referer) > 50) ? substr($referer, 0, 50) . '...' : $referer;
                                    }
                                    ?>
                                </td>
                                <?php if (array_key_exists('location', $click)): ?>
                                <td><?php echo !empty($click['location']) ? htmlspecialchars($click['location']) : 'Non spécifié'; ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="text-center mb-5">
            <a href="dashboard.php" class="btn btn-secondary me-2"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>
            <?php if ($_SESSION['user_id'] == $url_info['user_id'] || $_SESSION['is_admin'] == 1): ?>
            <a href="edit_link.php?id=<?php echo $url_id; ?>" class="btn btn-primary me-2"><i class="fas fa-edit"></i> Modifier le lien</a>
            <a href="<?php echo $short_url; ?>-" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lien?');"><i class="fas fa-trash"></i> Supprimer le lien</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function copyToClipboard() {
            const shortUrl = document.getElementById('short-url').textContent;
            navigator.clipboard.writeText(shortUrl)
                .then(() => {
                    alert('URL copiée dans le presse-papiers !');
                })
                .catch(err => {
                    alert('Erreur lors de la copie : ' + err);
                });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const ctxDay = document.getElementById('clicksByDayChart').getContext('2d');
            new Chart(ctxDay, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode(array_column($click_stats_by_day, 'click_date')); ?>,
                    datasets: [{
                        label: 'Nombre de clics',
                        data: <?php echo json_encode(array_column($click_stats_by_day, 'click_count')); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        tension: 0.1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    maintainAspectRatio: false
                }
            });
            
            const ctxHour = document.getElementById('clicksByHourChart').getContext('2d');
            new Chart(ctxHour, {
                type: 'bar',
                data: {
                    labels: Array.from({length: 24}, (_, i) => i + 'h'),
                    datasets: [{
                        label: 'Nombre de clics',
                        data: <?php echo json_encode(array_values($hours_data)); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    maintainAspectRatio: false
                }
            });

            const ctxBrowser = document.getElementById('browserChart').getContext('2d');
            new Chart(ctxBrowser, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode(array_column($click_stats_by_browser, 'browser')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($click_stats_by_browser, 'count')); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    },
                    maintainAspectRatio: false
                }
            });
            
            const ctxPlatform = document.getElementById('platformChart').getContext('2d');
            new Chart(ctxPlatform, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_column($click_stats_by_platform, 'platform')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($click_stats_by_platform, 'count')); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    },
                    maintainAspectRatio: false
                }
            });
            
            <?php if (!empty($click_stats_by_referrer)): ?>
            const ctxReferrer = document.getElementById('referrerChart').getContext('2d');
            new Chart(ctxReferrer, {
                type: 'polarArea',
                data: {
                    labels: <?php echo json_encode(array_column($click_stats_by_referrer, 'referrer')); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_column($click_stats_by_referrer, 'count')); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 206, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    },
                    maintainAspectRatio: false
                }
            });
            <?php endif; ?>
            
            <?php if (!empty($click_stats_by_location)): ?>
            const ctxLocation = document.getElementById('locationChart').getContext('2d');
            new Chart(ctxLocation, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($click_stats_by_location, 'location')); ?>,
                    datasets: [{
                        label: 'Nombre de clics',
                        data: <?php echo json_encode(array_column($click_stats_by_location, 'count')); ?>,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    maintainAspectRatio: false
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>

