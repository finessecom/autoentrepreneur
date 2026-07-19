<?php
$pageTitle = 'Modifier declaration';
require_once __DIR__ . '/../../includes/header.php';

$declModel = new Declaration();
$id = (int)($_GET['id'] ?? 0);
$decl = $declModel->getById($id, Auth::userId());

if (!$decl) {
    Helper::setError('Declaration introuvable.');
    Helper::redirect(APP_URL . '/?page=declarations');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caCommerce = (float)$_POST['ca_commerce'];
    $caService = (float)$_POST['ca_service'];

    $ir = $declModel->calculerIR($caCommerce, $caService);
    $cnss = $declModel->calculerCNSS($caCommerce + $caService);
    $retenueSource = (float)($_POST['retenue_source'] ?? 0);
    $totalAPayer = $ir['ir_total'] + $cnss['cnss_trimestre'] + $retenueSource;

    $data = [
        'annee'          => (int)$_POST['annee'],
        'trimestre'      => (int)$_POST['trimestre'],
        'ca_commerce'    => $caCommerce,
        'ca_service'     => $caService,
        'ir_calcule'     => $ir['ir_total'],
        'cnss_calcule'   => $cnss['cnss_trimestre'],
        'retenue_source' => $retenueSource,
        'total_a_payer'  => $totalAPayer,
        'est_declare'    => isset($_POST['est_declare']) ? 1 : 0,
        'mode_paiement'  => $_POST['mode_paiement'] ?? null,
        'date_declaration' => !empty($_POST['date_declaration']) ? $_POST['date_declaration'] : null,
    ];

    $declModel->create(Auth::userId(), $data);
    Helper::setSuccess('Declaration mise a jour.');
    Helper::redirect(APP_URL . '/?page=declarations&year=' . $data['annee']);
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Modifier declaration T<?= $decl['trimestre'] ?> - <?= $decl['annee'] ?></h4>
    <a href="<?= APP_URL ?>/?page=declarations" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<form method="POST">
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Année</label>
                    <select name="annee" class="form-select" required>
                        <?php for ($y = (int)date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= $y === $decl['annee'] ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trimestre</label>
                    <select name="trimestre" class="form-select" required>
                        <?php for ($t = 1; $t <= 4; $t++): ?>
                            <option value="<?= $t ?>" <?= $t === $decl['trimestre'] ? 'selected' : '' ?>>T<?= $t ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">CA Commerce</label>
                    <input type="number" name="ca_commerce" class="form-control" step="0.01" min="0" value="<?= $decl['ca_commerce'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">CA Service</label>
                    <input type="number" name="ca_service" class="form-control" step="0.01" min="0" value="<?= $decl['ca_service'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Retenue source</label>
                    <input type="number" name="retenue_source" class="form-control" step="0.01" min="0" value="<?= $decl['retenue_source'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date declaration</label>
                    <input type="date" name="date_declaration" class="form-control" value="<?= $decl['date_declaration'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Mode paiement</label>
                    <select name="mode_paiement" class="form-select">
                        <option value="">-- Non défini --</option>
                        <?php foreach (['especes' => 'Espèces', 'virement' => 'Virement', 'cheque' => 'Chèque', 'cnss' => 'CNSS'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $decl['mode_paiement'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="est_declare" class="form-check-input" id="estDeclare" <?= $decl['est_declare'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="estDeclare">Déjà déclaré</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2">
        <a href="<?= APP_URL ?>/?page=declarations" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    </div>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
