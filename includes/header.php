<?php
$userModel = new User();
$currentUser = $userModel->getById(Auth::userId());
$currentPage = $_GET['page'] ?? 'dashboard';
$pageTitles = [
    'dashboard' => 'Tableau de bord',
    'clients' => 'Clients',
    'clients/create' => 'Nouveau client',
    'clients/edit' => 'Modifier client',
    'produits' => 'Produits / Services',
    'produits/create' => 'Nouveau produit',
    'produits/edit' => 'Modifier produit',
    'charges' => 'Charges',
    'charges/create' => 'Nouvelle charge',
    'charges/edit' => 'Modifier charge',
    'documents' => 'Devis-Factures',
    'documents/create' => 'Nouveau document',
    'documents/view' => 'Document',
    'documents/edit' => 'Modifier document',
    'declarations' => 'Declarations',
    'declarations/create' => 'Simulateur de declaration',
    'declarations/edit' => 'Modifier declaration',
    'profil' => 'Mon Profil',
    'profil/edit' => 'Modifier profil',
    'admin' => 'Administration',
    'admin/annonces' => 'Annonces & Posts',
];
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
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    <div id="page-content-wrapper">
        <nav class="top-navbar">
            <div class="navbar-brand-section">
                <button class="btn btn-sm btn-outline-secondary d-lg-none" id="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h4><?= $pageTitles[$currentPage] ?? 'Page' ?></h4>
            </div>
            <div class="navbar-actions">
                <div class="dropdown">
                    <div class="user-dropdown" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            <?= strtoupper(substr($currentUser['nom_affichage'] ?? 'U', 0, 1)) ?>
                        </div>
                        <div class="user-info d-none d-md-block">
                            <div class="user-name"><?= Helper::sanitize($currentUser['nom_affichage'] ?? '') ?></div>
                            <div class="user-role"><?= ucfirst($currentUser['role'] ?? 'user') ?></div>
                        </div>
                        <i class="fas fa-chevron-down text-muted small"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width: 180px;">
                        <li><a class="dropdown-item" href="<?= APP_URL ?>/?page=profil"><i class="fas fa-user me-2"></i> Mon Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Deconnexion</a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container-fluid">
            <?php Helper::flash(); ?>
