<?php
$userModel = new User();
$currentUser = $userModel->getById(Auth::userId());
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Helper::sanitize($pageTitle ?? APP_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= APP_URL ?>/assets/css/custom.css" rel="stylesheet">
</head>
<body>
<div class="d-flex" id="wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div id="page-content-wrapper" class="flex-grow-1">
        <nav class="navbar navbar-light bg-white border-bottom px-4">
            <button class="btn btn-sm btn-outline-secondary" id="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small">
                    <i class="fas fa-user-circle me-1"></i>
                    <?= Helper::sanitize($currentUser['nom_affichage'] ?? '') ?>
                </span>
                <a href="<?= APP_URL ?>/auth/logout.php" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </nav>
        <div class="container-fluid p-4">
            <?php Helper::flash(); ?>
