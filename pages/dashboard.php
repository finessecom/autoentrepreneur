<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

$docModel = new Document();
$clientModel = new Client();
$prodModel = new ProduitService();
$declModel = new Declaration();

if (Auth::isAdmin()) {
    $totalUsers = $clientModel->count(0);
    $userModel2 = new User();
    $totalUsers = $userModel2->count();
    $docStats = $docModel->countByTypeAll();
    $totalDocs = $docModel->countAll();
    $totalCA = $docModel->totalCAAll();
    $totalDeclarations = $declModel->countAll();
} else {
    $userId = Auth::userId();
    $docStats = $docModel->countByType($userId);
    $totalDocs = $docModel->count($userId);
    $totalCA = $docModel->totalCA($userId);
    $totalClients = $clientModel->count($userId);
    $totalProduits = $prodModel->count($userId);
    $totalDeclarations = $declModel->count($userId);
    $recentDocs = $docModel->getByUser($userId, '', '');
    array_splice($recentDocs, 5);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i> Tableau de bord</h4>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <?php if (Auth::isAdmin()): ?>
    <div class="col-md-3">
        <div class="card stat-card blue">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><h5 class="card-title mb-0"><?= $totalUsers ?></h5><p class="text-muted small mb-0">Utilisateurs</p></div>
                    <i class="fas fa-users fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-md-3">
        <div class="card stat-card blue">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><h5 class="card-title mb-0"><?= $docStats['devis'] ?></h5><p class="text-muted small mb-0">Devis</p></div>
                    <i class="fas fa-file-alt fa-2x text-primary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card green">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><h5 class="card-title mb-0"><?= $docStats['facture'] ?></h5><p class="text-muted small mb-0">Factures</p></div>
                    <i class="fas fa-file-invoice-dollar fa-2x text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card orange">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><h5 class="card-title mb-0"><?= Helper::formatMoney($totalCA) ?></h5><p class="text-muted small mb-0">CA Total</p></div>
                    <i class="fas fa-coins fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!Auth::isAdmin()): ?>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><h5 class="card-title mb-0"><?= $totalClients ?></h5><p class="text-muted small mb-0">Clients</p></div>
                    <i class="fas fa-user-tie fa-2x text-info opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><h5 class="card-title mb-0"><?= $totalProduits ?></h5><p class="text-muted small mb-0">Produits/Services</p></div>
                    <i class="fas fa-box fa-2x text-secondary opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card red">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div><h5 class="card-title mb-0"><?= $totalDeclarations ?></h5><p class="text-muted small mb-0">Declarations</p></div>
                    <i class="fas fa-calculator fa-2x text-danger opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Documents recents -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-clock me-2"></i> Documents recents</h6>
        <a href="<?= APP_URL ?>/?page=documents" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($recentDocs)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-file-circle-plus fa-3x mb-3 opacity-25"></i>
                <p>Aucun document pour l'instant.</p>
                <a href="<?= APP_URL ?>/?page=documents/create" class="btn btn-primary btn-sm">Creer un document</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Type</th><th>Client</th><th>Date</th><th class="text-end">Total TTC</th><th>Statut</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentDocs as $doc): ?>
                        <tr>
                            <td>
                                <?php
                                $icons = ['devis' => 'fa-file-alt text-primary', 'facture' => 'fa-file-invoice-dollar text-success', 'bon_livraison' => 'fa-truck text-warning'];
                                $labels = ['devis' => 'Devis', 'facture' => 'Facture', 'bon_livraison' => 'Bon de livraison'];
                                ?>
                                <i class="fas <?= $icons[$doc['type_document']] ?? '' ?> me-1"></i>
                                <?= $labels[$doc['type_document']] ?? '' ?>
                            </td>
                            <td><?= Helper::sanitize($doc['nom_client']) ?></td>
                            <td><?= date('d/m/Y', strtotime($doc['date_document'])) ?></td>
                            <td class="text-end fw-semibold"><?= Helper::formatMoney($doc['total_ttc']) ?></td>
                            <td>
                                <?php
                                $badges = ['brouillon' => 'secondary', 'envoye' => 'info', 'paye' => 'success', 'annule' => 'danger'];
                                ?>
                                <span class="badge bg-<?= $badges[$doc['statut']] ?? 'secondary' ?>">
                                    <?= ucfirst($doc['statut']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<!-- Admin dashboard - recent users -->
<div class="card mt-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-users me-2"></i> Utilisateurs inscrits</h6></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Nom</th><th>Email</th><th>Ville</th><th>Date inscription</th></tr></thead>
                <tbody>
                <?php
                $allUsers = (new User())->getAll();
                foreach ($allUsers as $u): ?>
                    <tr>
                        <td><?= Helper::sanitize($u['nom_complet']) ?></td>
                        <td><?= Helper::sanitize($u['email']) ?></td>
                        <td><?= Helper::sanitize($u['ville'] ?? '-') ?></td>
                        <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
