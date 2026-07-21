<?php
$_currentPage = $_GET['page'] ?? 'dashboard';
$_isActive = function($key) use ($_currentPage) {
    return str_starts_with($_currentPage, $key) ? 'active' : '';
};
?>
<div class="bg-dark text-white" id="sidebar-wrapper" style="width: 250px; min-height: 100vh;">
    <div class="sidebar-heading text-center py-3 border-bottom border-secondary">
        <i class="fas fa-briefcase me-2"></i>
        <strong>Auto-Entrepreneur</strong>
    </div>
    <div class="list-group list-group-flush">
        <a href="<?= APP_URL ?>/?page=dashboard"
           class="list-group-item list-group-item-action bg-dark text-white border-0 <?= $_isActive('dashboard') ?>">
            <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
        </a>
        <a href="<?= APP_URL ?>/?page=clients"
           class="list-group-item list-group-item-action bg-dark text-white border-0 <?= $_isActive('clients') ?>">
            <i class="fas fa-users me-2"></i> Clients
        </a>
        <a href="<?= APP_URL ?>/?page=produits"
           class="list-group-item list-group-item-action bg-dark text-white border-0 <?= $_isActive('produits') ?>">
            <i class="fas fa-box me-2"></i> Produits / Services
        </a>
        <a href="<?= APP_URL ?>/?page=documents"
           class="list-group-item list-group-item-action bg-dark text-white border-0 <?= $_isActive('documents') ?>">
            <i class="fas fa-file-invoice me-2"></i> Documents
        </a>
        <a href="<?= APP_URL ?>/?page=declarations"
           class="list-group-item list-group-item-action bg-dark text-white border-0 <?= $_isActive('declarations') ?>">
            <i class="fas fa-calculator me-2"></i> Declarations
        </a>
        <a href="<?= APP_URL ?>/?page=profil"
           class="list-group-item list-group-item-action bg-dark text-white border-0 <?= $_isActive('profil') ?>">
            <i class="fas fa-id-card me-2"></i> Mon Profil
        </a>
        <?php if (Auth::isAdmin()): ?>
        <div class="border-top border-secondary my-2"></div>
        <div class="px-3 py-1 text-uppercase small text-muted">Admin</div>
        <a href="<?= APP_URL ?>/?page=admin"
           class="list-group-item list-group-item-action bg-dark text-white border-0 <?= $_isActive('admin') ?>">
            <i class="fas fa-cog me-2"></i> Administration
        </a>
        <a href="<?= APP_URL ?>/?page=admin/annonces"
           class="list-group-item list-group-item-action bg-dark text-white border-0 <?= $_isActive('admin/annonces') ?>">
            <i class="fas fa-bullhorn me-2"></i> Annonces & Posts
        </a>
        <?php endif; ?>
    </div>
</div>
