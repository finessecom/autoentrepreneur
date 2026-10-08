<?php
$pageTitle = 'Charges';
require_once __DIR__ . '/../../includes/header.php';

$chargeModel = new Charge();
$search = $_GET['search'] ?? '';
$charges = $chargeModel->getByUser(Auth::userId(), $search);

// Stats
$nbCharges = count($charges);
$totalEUR = $chargeModel->totalEUR(Auth::userId());
$totalMAD = $chargeModel->totalMAD(Auth::userId());
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-receipt me-2"></i> Charges</h4>
    <a href="<?= APP_URL ?>/?page=charges/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouvelle charge
    </a>
</div>

<!-- Cards dashboard -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Nombre de charges</p>
                        <h3 class="mb-0 text-primary"><?= $nbCharges ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-receipt fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total EUR</p>
                        <h3 class="mb-0 text-warning"><?= Helper::formatMoney($totalEUR, 'EUR') ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-euro-sign fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total MAD</p>
                        <h3 class="mb-0 text-success"><?= Helper::formatMoney($totalMAD, 'MAD') ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-coins fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recherche -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <input type="hidden" name="page" value="charges">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Rechercher par désignation..."
                       value="<?= Helper::sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="fas fa-search me-1"></i> Rechercher
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Liste -->
<div class="card">
    <div class="card-body p-0">
        <?php if (empty($charges)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
                <p>Aucune charge trouvée.</p>
                <a href="<?= APP_URL ?>/?page=charges/create" class="btn btn-primary btn-sm">Ajouter une charge</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Désignation</th>
                            <th class="text-end">Montant EUR</th>
                            <th class="text-end">Montant MAD</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($charges as $ch): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($ch['date_charge'])) ?></td>
                            <td class="fw-semibold"><?= Helper::sanitize($ch['designation']) ?></td>
                            <td class="text-end"><?= $ch['montant_eur'] > 0 ? Helper::formatMoney($ch['montant_eur'], 'EUR') : '-' ?></td>
                            <td class="text-end fw-semibold"><?= Helper::formatMoney($ch['montant_mad'], 'MAD') ?></td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>/?page=charges/edit&id=<?= $ch['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= APP_URL ?>/?page=charges/delete&id=<?= $ch['id'] ?>" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                   onclick="return confirm('Supprimer cette charge ?')">
                                    <i class="fas fa-trash"></i>
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
