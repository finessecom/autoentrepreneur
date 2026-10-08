<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';

$docModel = new Document();
$clientModel = new Client();
$prodModel = new ProduitService();
$declModel = new Declaration();
$annonceModel = new Annonce();
$userModel = new User();
$chargeModel = new Charge();

if (Auth::isAdmin()) {
    $totalUsers = $userModel->count();
    $docStats = $docModel->countByTypeAll();
    $totalDocs = $docModel->countAll();
    $totalCA = $docModel->totalCAAll();
    $totalDeclarations = $declModel->countAll();
    $statsUsers = $userModel->statsParUser();
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

$allClients = $clientModel->getByUser(Auth::userId());
$caPrevisionnel = 0;
foreach ($allClients as $c) {
    $caPrevisionnel += $c['montant'] ?? 0;
}

$caRealise = $totalCA;
$totalCharges = $chargeModel->totalMAD(Auth::userId());
$resultat = $caPrevisionnel - $totalCharges;
?>

<div class="d-flex justify-content-between align-items-center page-header">
    <h4 class="page-title">Tableau de bord</h4>
</div>

<?php if (Auth::isAdmin()): ?>
<!-- Stats Globaux -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i> Stats Globaux</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card bg-primary-soft h-100">
                    <div class="card-body d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="stat-label">Utilisateurs</div>
                            <div class="stat-value"><?= $totalUsers ?></div>
                        </div>
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card bg-info-soft h-100">
                    <div class="card-body d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="stat-label">Clients</div>
                            <div class="stat-value"><?= $totalClients ?></div>
                        </div>
                        <div class="stat-icon info">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card bg-warning-soft h-100">
                    <div class="card-body d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="stat-label">Devis</div>
                            <div class="stat-value"><?= $docStats['devis'] ?></div>
                        </div>
                        <div class="stat-icon warning">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card bg-danger-soft h-100">
                    <div class="card-body d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="stat-label">Factures</div>
                            <div class="stat-value"><?= $docStats['facture'] ?></div>
                        </div>
                        <div class="stat-icon danger">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl">
                <div class="card stat-card bg-success-soft h-100">
                    <div class="card-body d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="stat-label">CA Global DH</div>
                            <div class="stat-value"><?= number_format($totalCA, 2, '.', ' ') ?></div>
                        </div>
                        <div class="stat-icon success">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats par user -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fas fa-table me-2 text-primary"></i> Stats par user</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nom user</th>
                        <th>Email</th>
                        <th>Date creation</th>
                        <th class="text-center">Nb clients</th>
                        <th class="text-center">Nb devis et factures</th>
                        <th class="text-end">CA Global</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($statsUsers as $s): ?>
                    <tr>
                        <td>
                            <?= Helper::sanitize($s['nom_complet']) ?>
                            <?php if ($s['role'] === 'admin'): ?>
                                <span class="badge bg-danger ms-1">Admin</span>
                            <?php endif; ?>
                        </td>
                        <td><?= Helper::sanitize($s['email']) ?></td>
                        <td><?= date('d/m/Y', strtotime($s['created_at'])) ?></td>
                        <td class="text-center"><?= (int) $s['nb_clients'] ?></td>
                        <td class="text-center"><?= (int) $s['nb_docs'] ?></td>
                        <td class="text-end fw-semibold"><?= Helper::formatMoney($s['ca_global']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<!-- Row 1 -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-info-soft h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Clients</div>
                    <div class="stat-value"><?= $totalClients ?></div>
                </div>
                <div class="stat-icon info">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-warning-soft h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Produits / Services</div>
                    <div class="stat-value"><?= $totalProduits ?></div>
                </div>
                <div class="stat-icon warning">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-danger-soft h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Declarations</div>
                    <div class="stat-value"><?= $totalDeclarations ?></div>
                </div>
                <div class="stat-icon danger">
                    <i class="fas fa-calculator"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-success-soft h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">CA Global</div>
                    <div class="stat-value"><?= Helper::formatMoney($totalCA) ?></div>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Devis, Factures -->
<div class="row g-4 mb-4">
    <div class="col-lg-6 col-md-6">
        <div class="card stat-card bg-primary-soft h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Devis</div>
                    <div class="stat-value"><?= $docStats['devis'] ?></div>
                </div>
                <div class="stat-icon primary">
                    <i class="fas fa-file-alt"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6">
        <div class="card stat-card bg-success-soft h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-label">Factures</div>
                    <div class="stat-value"><?= $docStats['facture'] ?></div>
                </div>
                <div class="stat-icon success">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Financials -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-info-soft h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">CA Previsionnel</div>
                        <div class="stat-value"><?= Helper::formatMoney($caPrevisionnel) ?></div>
                        <small class="text-muted">Montants clients</small>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-danger-soft h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Charges</div>
                        <div class="stat-value"><?= Helper::formatMoney($totalCharges) ?></div>
                        <small class="text-muted">Total charges</small>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fas fa-receipt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card bg-success-soft h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">CA Realise</div>
                        <div class="stat-value"><?= Helper::formatMoney($caRealise) ?></div>
                        <small class="text-muted">Total factures payees</small>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card stat-card <?= $resultat >= 0 ? 'bg-success-soft' : 'bg-danger-soft' ?> h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Resultat</div>
                        <div class="stat-value"><?= Helper::formatMoney($resultat) ?></div>
                        <small class="text-muted">CA Previsionnel - Charges</small>
                    </div>
                    <div class="stat-icon <?= $resultat >= 0 ? 'success' : 'danger' ?>">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Posts & Annonces -->
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-newspaper me-2 text-info"></i> Posts</h6>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= APP_URL ?>/?page=admin/annonces" class="btn btn-sm btn-outline-primary">Gerer</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($posts)): ?>
                    <p class="text-muted text-center py-3 mb-0">Aucun post pour l'instant.</p>
                <?php else: ?>
                    <?php foreach ($posts as $p): ?>
                        <a href="<?= APP_URL ?>/?page=annonces/view&id=<?= $p['id'] ?>" class="d-flex justify-content-between align-items-center border-bottom py-2 text-decoration-none text-dark hover-primary">
                            <span class="fw-semibold"><i class="fas fa-chevron-right me-2 text-muted small"></i><?= Helper::sanitize($p['titre']) ?></span>
                            <small class="text-muted text-nowrap ms-3"><?= date('d/m/Y', strtotime($p['created_at'])) ?></small>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-bullhorn me-2 text-warning"></i> Annonces</h6>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= APP_URL ?>/?page=admin/annonces" class="btn btn-sm btn-outline-primary">Gerer</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($annoncesList)): ?>
                    <p class="text-muted text-center py-3 mb-0">Aucune annonce pour l'instant.</p>
                <?php else: ?>
                    <?php foreach ($annoncesList as $a): ?>
                        <a href="<?= APP_URL ?>/?page=annonces/view&id=<?= $a['id'] ?>" class="d-flex justify-content-between align-items-center border-bottom py-2 text-decoration-none text-dark hover-primary">
                            <span class="fw-semibold"><i class="fas fa-chevron-right me-2 text-muted small"></i><?= Helper::sanitize($a['titre']) ?></span>
                            <small class="text-muted text-nowrap ms-3"><?= date('d/m/Y', strtotime($a['created_at'])) ?></small>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (!Auth::isAdmin()): ?>
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
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th class="text-end">Total HT</th>
                            <th>Statut</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
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
                            <td class="text-center">
                                <a href="<?= APP_URL ?>/?page=documents/view&id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>