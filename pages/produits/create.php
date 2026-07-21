<?php
$pageTitle = 'Nouveau produit/service';
require_once __DIR__ . '/../../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prodModel = new ProduitService();

    $data = $_POST;
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $dest = PRODUIT_DIR . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $data['image_url'] = 'uploads/produits/' . $filename;
        }
    }

    $id = $prodModel->create(Auth::userId(), $data);
    if ($id) {
        Helper::setSuccess('Produit/service créé avec succès.');
        Helper::redirect(APP_URL . '/?page=produits');
    } else {
        Helper::setError('Erreur lors de la création.');
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-plus me-2"></i> Nouveau produit/service</h4>
    <a href="<?= APP_URL ?>/?page=produits" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Type d'activité *</label>
                    <select name="type_activite" class="form-select" required>
                        <option value="commerce">Commerce (Produits)</option>
                        <option value="service">Service</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Désignation *</label>
                    <textarea name="designation" class="form-control" rows="3" required
                              placeholder="Nom du produit/service..."><?= Helper::sanitize($_POST['designation'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Détail</label>
                    <textarea name="detail" class="form-control" rows="2"
                              placeholder="Description détaillée..."><?= Helper::sanitize($_POST['detail'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prix unitaire *</label>
                    <input type="number" name="prix_unitaire" class="form-control" required step="0.01" min="0"
                           value="<?= $_POST['prix_unitaire'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Devise</label>
                    <select name="devise" class="form-select">
                        <?php foreach (Helper::devises() as $d): ?>
                            <option value="<?= $d ?>" <?= ($_POST['devise'] ?? 'MAD') === $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
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
