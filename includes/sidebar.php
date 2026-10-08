<?php
$_currentPage = $_GET['page'] ?? 'dashboard';
$_isActive = function($key) use ($_currentPage) {
    return str_starts_with($_currentPage, $key) ? 'active' : '';
};
?>
<div id="sidebar-wrapper">
    <div class="sidebar-brand">
        <img src="<?= APP_URL ?>/assets/images/banniere_autoentrepreneur.webp" alt="Logo" style="object-fit: contain;">
        <div>
            <h5>L'Auto-Entrepreneur</h5>
            <small>Gestion simplifiee</small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="sidebar-label">Menu principal</div>

        <a href="<?= APP_URL ?>/?page=dashboard"
           class="sidebar-link <?= $_isActive('dashboard') ?>">
            <i class="fas fa-th-large"></i> Tableau de bord
        </a>
        <a href="<?= APP_URL ?>/?page=clients"
           class="sidebar-link <?= $_isActive('clients') ?>">
            <i class="fas fa-user-tie"></i> Clients
        </a>
        <a href="<?= APP_URL ?>/?page=produits"
           class="sidebar-link <?= $_isActive('produits') ?>">
            <i class="fas fa-box"></i> Produits / Services
        </a>
        <a href="<?= APP_URL ?>/?page=charges"
           class="sidebar-link <?= $_isActive('charges') ?>">
            <i class="fas fa-receipt"></i> Charges
        </a>
        <a href="<?= APP_URL ?>/?page=documents"
           class="sidebar-link <?= $_isActive('documents') ?>">
            <i class="fas fa-file-invoice"></i> Devis-Factures
        </a>
        <a href="<?= APP_URL ?>/?page=declarations"
           class="sidebar-link <?= $_isActive('declarations') ?>">
            <i class="fas fa-calculator"></i> Declarations
        </a>

        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Personnel</div>

        <a href="<?= APP_URL ?>/?page=profil"
           class="sidebar-link <?= $_isActive('profil') ?>">
            <i class="fas fa-user-circle"></i> Mon Profil
        </a>

        <?php if (Auth::isAdmin()): ?>
        <div class="sidebar-divider"></div>
        <div class="sidebar-label">Administration</div>
        <a href="<?= APP_URL ?>/?page=admin"
           class="sidebar-link <?= $_isActive('admin') ?>">
            <i class="fas fa-cog"></i> Administration
        </a>
        <a href="<?= APP_URL ?>/?page=admin/annonces"
           class="sidebar-link <?= $_isActive('admin/annonces') ?>">
            <i class="fas fa-bullhorn"></i> Annonces & Posts
        </a>
        <?php endif; ?>
    </nav>
</div>
