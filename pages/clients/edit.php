<?php
$pageTitle = 'Modifier client';
require_once __DIR__ . '/../../includes/header.php';

$clientModel = new Client();
$id = (int)($_GET['id'] ?? 0);
$client = $clientModel->getById($id, Auth::userId());

if (!$client) {
    Helper::setError('Client introuvable.');
    Helper::redirect(APP_URL . '/?page=clients');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientModel->update($id, Auth::userId(), $_POST);
    Helper::setSuccess('Client modifié avec succès.');
    Helper::redirect(APP_URL . '/?page=clients');
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Modifier client</h4>
    <a href="<?= APP_URL ?>/?page=clients" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nom du client *</label>
                    <input type="text" name="nom_client" class="form-control" required
                           value="<?= Helper::sanitize($client['nom_client']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">ICE</label>
                    <input type="text" name="ice" class="form-control"
                           value="<?= Helper::sanitize($client['ice'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= Helper::sanitize($client['email'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control"
                           value="<?= Helper::sanitize($client['telephone'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Adresse</label>
                    <textarea name="adresse" class="form-control" rows="2"><?= Helper::sanitize($client['adresse'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Devise</label>
                    <select name="devise" class="form-select">
                        <?php foreach (['MAD' => 'MAD - Dirham Marocain', 'EUR' => 'EUR - Euro', 'USD' => 'USD - Dollar US'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $client['devise'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
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
