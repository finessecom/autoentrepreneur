<?php
$pageTitle = 'Nouveau client';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $clientModel = new Client();
    $id = $clientModel->create(Auth::userId(), $_POST);
    if ($id) {
        Helper::setSuccess('Client créé avec succès.');
        Helper::redirect(APP_URL . '/?page=clients');
    } else {
        Helper::setError('Erreur lors de la création.');
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i> Nouveau client</h4>
    <a href="<?= APP_URL ?>/?page=clients" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nom du client *</label>
                    <input type="text" name="nom_client" class="form-control" required
                           value="<?= Helper::sanitize($_POST['nom_client'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ICE</label>
                    <input type="text" name="ice" class="form-control"
                           value="<?= Helper::sanitize($_POST['ice'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= Helper::sanitize($_POST['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control"
                           value="<?= Helper::sanitize($_POST['telephone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" class="form-control" rows="2"><?= Helper::sanitize($_POST['adresse'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Devise</label>
                    <select name="devise" class="form-select">
                        <option value="MAD" selected>MAD - Dirham Marocain</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="USD">USD - Dollar US</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date échéance</label>
                    <input type="date" name="date_echance" class="form-control"
                           value="<?= Helper::sanitize($_POST['date_echance'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Montant</label>
                    <input type="number" name="montant" class="form-control" step="0.01" min="0"
                           value="<?= Helper::sanitize($_POST['montant'] ?? '') ?>">
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
