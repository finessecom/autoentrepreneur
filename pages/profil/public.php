<?php
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Helper.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/User.php';

$userModel = new User();
$id = (int)($_GET['id'] ?? 0);
$user = $userModel->getById($id);

if (!$user) {
    die('Profil introuvable.');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helper::sanitize($user['nom_affichage']) ?> - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .profile-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 60px 0 30px; }
    </style>
</head>
<body>
    <div class="profile-header text-center">
        <div class="container">
            <h2 class="mb-1"><?= Helper::sanitize($user['nom_complet']) ?></h2>
            <p class="mb-0 opacity-75"><?= Helper::sanitize($user['titre_pro'] ?? 'Auto-Entrepreneur') ?></p>
            <?php if ($user['ville']): ?>
                <p><i class="fas fa-map-marker-alt me-1"></i> <?= Helper::sanitize($user['ville']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-8">
                <?php if ($user['bio']): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">A propos</h5>
                        <p class="mb-0"><?= nl2br(Helper::sanitize($user['bio'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($user['bio_ar']): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title" dir="rtl">نبذة عني</h5>
                        <p dir="rtl" class="mb-0"><?= nl2br(Helper::sanitize($user['bio_ar'])) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($user['mots_cles'])): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Compétences</h5>
                        <?php foreach (explode(',', $user['mots_cles']) as $kw): ?>
                            <span class="badge bg-primary me-1 mb-1"><?= Helper::sanitize(trim($kw)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Contact</h5>
                        <ul class="list-unstyled mb-0">
                            <?php if ($user['email']): ?>
                                <li class="mb-2"><i class="fas fa-envelope me-2 text-muted"></i> <?= Helper::sanitize($user['email']) ?></li>
                            <?php endif; ?>
                            <?php if ($user['telephone']): ?>
                                <li class="mb-2"><i class="fas fa-phone me-2 text-muted"></i> <?= Helper::sanitize($user['telephone']) ?></li>
                            <?php endif; ?>
                            <?php if ($user['whatsapp']): ?>
                                <li class="mb-2"><i class="fab fa-whatsapp me-2 text-success"></i> <?= Helper::sanitize($user['whatsapp']) ?></li>
                            <?php endif; ?>
                            <?php if ($user['site_web']): ?>
                                <li class="mb-2"><i class="fas fa-globe me-2 text-muted"></i> <a href="<?= Helper::sanitize($user['site_web']) ?>" target="_blank">Site web</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <?php
                $reseaux = json_decode($user['reseaux_sociaux'] ?? '{}', true) ?: [];
                if (!empty($reseaux)):
                ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Réseaux sociaux</h5>
                        <ul class="list-unstyled mb-0">
                            <?php
                            $icons = ['Instagram' => 'fab fa-instagram', 'Facebook' => 'fab fa-facebook', 'LinkedIn' => 'fab fa-linkedin',
                                      'Twitter' => 'fab fa-twitter', 'YouTube' => 'fab fa-youtube', 'TikTok' => 'fab fa-tiktok'];
                            foreach ($reseaux as $platform => $url):
                            ?>
                                <li class="mb-2">
                                    <a href="<?= Helper::sanitize($url) ?>" target="_blank" class="text-decoration-none">
                                        <i class="<?= $icons[$platform] ?? 'fas fa-link' ?> me-2"></i> <?= Helper::sanitize($platform) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
