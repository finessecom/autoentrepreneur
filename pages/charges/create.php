<?php
$pageTitle = 'Nouvelle charge';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $chargeModel = new Charge();
    $id = $chargeModel->create(Auth::userId(), $_POST);
    if ($id) {
        Helper::setSuccess('Charge créée avec succès.');
        Helper::redirect(APP_URL . '/?page=charges');
    } else {
        Helper::setError('Erreur lors de la création.');
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-plus me-2"></i> Nouvelle charge</h4>
    <a href="<?= APP_URL ?>/?page=charges" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Date *</label>
                    <input type="date" name="date_charge" class="form-control" required
                           value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Désignation *</label>
                    <input type="text" name="designation" class="form-control" required
                           value="<?= Helper::sanitize($_POST['designation'] ?? '') ?>" placeholder="Ex: Achat fournitures, Loyer...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Montant EUR</label>
                    <input type="number" name="montant_eur" class="form-control" step="0.01" min="0"
                           value="<?= Helper::sanitize($_POST['montant_eur'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Montant MAD *</label>
                    <input type="number" name="montant_mad" class="form-control" step="0.01" min="0" required
                           value="<?= Helper::sanitize($_POST['montant_mad'] ?? '') ?>">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
