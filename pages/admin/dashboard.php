<?php
$pageTitle = 'Admin';
require_once __DIR__ . '/../../includes/header.php';

Auth::requireAdmin();

$userModel = new User();
$docModel = new Document();
$declModel = new Declaration();
$clientModel = new Client();

$totalUsers = $userModel->count();
$allUsers = $userModel->getAll();
$docStats = $docModel->countByTypeAll();
$totalDocs = $docModel->countAll();
$totalCA = $docModel->totalCAAll();
$totalDeclarations = $declModel->countAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-cog me-2"></i> Administration</h4>
</div>

<div class="row g-4 mb-4">
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
                    <div><h5 class="card-title mb-0"><?= Helper::formatMoney($totalCA) ?></h5><p class="text-muted small mb-0">CA Global</p></div>
                    <i class="fas fa-coins fa-2x text-warning opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-users me-2"></i> Tous les utilisateurs</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Ville</th>
                                <th>ICE</th>
                                <th>Date inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($allUsers as $u): ?>
                            <tr>
                                <td><?= $u['id'] ?></td>
                                <td><?= Helper::sanitize($u['nom_complet']) ?></td>
                                <td><?= Helper::sanitize($u['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'primary' ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                                <td><?= Helper::sanitize($u['ville'] ?? '-') ?></td>
                                <td><code><?= Helper::sanitize($u['ice'] ?? '-') ?></code></td>
                                <td><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
