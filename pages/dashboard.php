<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

$docModel = new Document();
$clientModel = new Client();
$prodModel = new ProduitService();
$declModel = new Declaration();
$annonceModel = new Annonce();
$userModel = new User();

if (Auth::isAdmin()) {
    $totalUsers = $userModel->count();
    $docStats = $docModel->countByTypeAll();
    $totalDocs = $docModel->countAll();
    $totalCA = $docModel->totalCAAll();
    $totalDeclarations = $declModel->countAll();
    $allUsers = $userModel->getAll();
    $totalClients = $clientModel->countAll();
    $totalProduits = $prodModel->countAll();
    $posts = $annonceModel->getByType('post');
    $annoncesList = $annonceModel->getByType('annonce');
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
    $posts = $annonceModel->getByType('post');
    $annoncesList = $annonceModel->getByType('annonce');
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i> Tableau de bord</h4>
    <?php if (Auth::isAdmin()): ?>
    <a href="<?= APP_URL ?>/?page=admin/annonces" class="btn btn-primary btn-sm">
        <i class="fas fa-bullhorn me-1"></i> Nouvelle annonce
    </a>
    <?php endif; ?>
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
    <?php else: ?>
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
<!-- User-only stats -->
<div class="row g-4 mb-4">
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
<?php endif; ?>

<!-- Posts & Annonces -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-newspaper me-2 text-info"></i> Posts</h6>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= APP_URL ?>/?page=admin/annonces" class="btn btn-sm btn-outline-primary">Gérer</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($posts)): ?>
                    <p class="text-muted text-center py-3 mb-0">Aucun post pour l'instant.</p>
                <?php else: ?>
                    <?php foreach ($posts as $p): ?>
                        <a href="<?= APP_URL ?>/?page=annonces/view&id=<?= $p['id'] ?>" class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 text-decoration-none text-dark">
                            <span class="fw-semibold"><?= Helper::sanitize($p['titre']) ?></span>
                            <small class="text-muted text-nowrap ms-3"><?= date('d/m/Y', strtotime($p['created_at'])) ?></small>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-bullhorn me-2 text-warning"></i> Annonces</h6>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= APP_URL ?>/?page=admin/annonces" class="btn btn-sm btn-outline-primary">Gérer</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($annoncesList)): ?>
                    <p class="text-muted text-center py-3 mb-0">Aucune annonce pour l'instant.</p>
                <?php else: ?>
                    <?php foreach ($annoncesList as $a): ?>
                        <a href="<?= APP_URL ?>/?page=annonces/view&id=<?= $a['id'] ?>" class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2 text-decoration-none text-dark">
                            <span class="fw-semibold"><?= Helper::sanitize($a['titre']) ?></span>
                            <small class="text-muted text-nowrap ms-3"><?= date('d/m/Y', strtotime($a['created_at'])) ?></small>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!Auth::isAdmin()): ?>
<!-- Documents recents (User) -->
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
                    <thead><tr><th>Type</th><th>Client</th><th>Date</th><th class="text-end">Total HT</th><th>Statut</th></tr></thead>
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
                            <td class="text-end fw-semibold"><?= Helper::formatMoney($doc['total_ht'], $doc['devise'] ?? 'MAD') ?></td>
                            <td>
                                <?php $badges = ['brouillon' => 'secondary', 'envoye' => 'info', 'paye' => 'success', 'annule' => 'danger']; ?>
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
<!-- Admin : Utilisateurs + Derniers documents -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-users me-2"></i> Utilisateurs (<?= $totalUsers ?>)</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Nom</th><th>Email</th><th>Ville</th><th>Inscription</th></tr></thead>
                        <tbody>
                        <?php foreach ($allUsers as $u): ?>
                            <tr>
                                <td>
                                    <?= Helper::sanitize($u['nom_complet']) ?>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-danger ms-1">Admin</span>
                                    <?php endif; ?>
                                </td>
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
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Derniers documents</h6>
                <a href="<?= APP_URL ?>/?page=documents" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body p-0">
                <?php
                $recentDocsAll = $docModel->getAll();
                array_splice($recentDocsAll, 5);
                ?>
                <?php if (empty($recentDocsAll)): ?>
                    <p class="text-muted text-center py-4 mb-0">Aucun document.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Type</th><th>Client</th><th>Date</th><th class="text-end">Total HT</th></tr></thead>
                            <tbody>
                            <?php foreach ($recentDocsAll as $doc): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $labels = ['devis' => 'Devis', 'facture' => 'Facture', 'bon_livraison' => 'Bon livr.'];
                                        $colors = ['devis' => 'primary', 'facture' => 'success', 'bon_livraison' => 'warning'];
                                        ?>
                                        <span class="badge bg-<?= $colors[$doc['type_document']] ?? 'secondary' ?>">
                                            <?= $labels[$doc['type_document']] ?? '' ?>
                                        </span>
                                    </td>
                                    <td><?= Helper::sanitize($doc['nom_client']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($doc['date_document'])) ?></td>
                                    <td class="text-end fw-semibold"><?= Helper::formatMoney($doc['total_ht'], $doc['devise'] ?? 'MAD') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
