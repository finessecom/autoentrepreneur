<?php
$pageTitle = 'Declarations';
require_once __DIR__ . '/../../includes/header.php';

$declModel = new Declaration();
$docModel = new Document();
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$declarations = $declModel->getByUser(Auth::userId(), $year);

// Charger les factures déclarées pour chaque trimestre
$invoicesByTrim = [];
foreach ($declarations as $decl) {
    $invoicesByTrim[$decl['trimestre']] = $docModel->getByDeclaration(Auth::userId(), $year, $decl['trimestre']);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-calculator me-2"></i> Declarations fiscales</h4>
    <a href="<?= APP_URL ?>/?page=declarations/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouvelle declaration
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="declarations">
            <div class="col-md-3">
                <label class="form-label">Année</label>
                <select name="year" class="form-select">
                    <?php for ($y = (int)date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="fas fa-filter me-1"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($declarations)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-calculator fa-3x mb-3 opacity-25"></i>
                <p>Aucune declaration pour l'année <?= $year ?>.</p>
                <a href="<?= APP_URL ?>/?page=declarations/create" class="btn btn-primary btn-sm">Créer une declaration</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Trimestre</th>
                            <th class="text-end">CA Service</th>
                            <th class="text-end">IR</th>
                            <th class="text-end">CNSS</th>
                            <th class="text-end">RAS</th>
                            <th class="text-end">Total a payer</th>
                            <th>Factures</th>
                            <th>Declare</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($declarations as $decl): ?>
                        <tr>
                            <td><strong>T<?= $decl['trimestre'] ?></strong></td>
                            <td class="text-end"><?= Helper::formatMoney($decl['ca_service']) ?></td>
                            <td class="text-end"><?= Helper::formatMoney($decl['ir_calcule']) ?></td>
                            <td class="text-end"><?= Helper::formatMoney($decl['cnss_calcule']) ?></td>
                            <td class="text-end"><?= Helper::formatMoney($decl['retenue_source']) ?></td>
                            <td class="text-end fw-bold text-danger"><?= Helper::formatMoney($decl['total_a_payer']) ?></td>
                            <td>
                                <?php
                                $invoices = $invoicesByTrim[$decl['trimestre']] ?? [];
                                if (empty($invoices)):
                                ?>
                                    <span class="text-muted">-</span>
                                <?php else: ?>
                                    <?php foreach ($invoices as $inv): ?>
                                        <span class="badge bg-light text-dark border me-1 mb-1">
                                            <?= Helper::sanitize($inv['numero']) ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($decl['est_declare']): ?>
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> Oui</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Non</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>/?page=declarations/edit&id=<?= $decl['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= APP_URL ?>/?page=declarations/delete&id=<?= $decl['id'] ?>" class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Supprimer ?')">
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

<div class="card mt-4">
    <div class="card-body">
        <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Avertissement légal :</strong> Cet outil est uniquement indicatif. La responsabilité de déclaration auprès de la DGI incombe entièrement à l'utilisateur.
            Les calculs sont basés sur les barèmes en vigueur et peuvent ne pas refléter votre situation exacte.
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
