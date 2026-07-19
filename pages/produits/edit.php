<?php
$pageTitle = 'Modifier produit/service';
require_once __DIR__ . '/../../includes/header.php';

$prodModel = new ProduitService();
$id = (int)($_GET['id'] ?? 0);
$produit = $prodModel->getById($id, Auth::userId());

if (!$produit) {
    Helper::setError('Produit introuvable.');
    Helper::redirect(APP_URL . '/?page=produits');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    if (!empty($_FILES['image']['tmp_name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'prod_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $dest = PRODUIT_DIR . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
            $data['image_url'] = 'uploads/produits/' . $filename;
        }
    } else {
        $data['image_url'] = $produit['image_url'];
    }

    $prodModel->update($id, Auth::userId(), $data);
    Helper::setSuccess('Produit modifié avec succès.');
    Helper::redirect(APP_URL . '/?page=produits');
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Modifier produit/service</h4>
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
                        <option value="commerce" <?= $produit['type_activite'] === 'commerce' ? 'selected' : '' ?>>Commerce (Produits)</option>
                        <option value="service" <?= $produit['type_activite'] === 'service' ? 'selected' : '' ?>>Service</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Désignation *</label>
                    <input type="text" name="designation" class="form-control" required
                           value="<?= Helper::sanitize($produit['designation']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Prix unitaire (MAD) *</label>
                    <input type="number" name="prix_unitaire" class="form-control" required step="0.01" min="0"
                           value="<?= $produit['prix_unitaire'] ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Image</label>
                    <?php if ($produit['image_url']): ?>
                        <div class="mb-2">
                            <img src="<?= APP_URL . '/' . $produit['image_url'] ?>" alt="" width="64" height="64" class="rounded" style="object-fit:cover">
                        </div>
                    <?php endif; ?>
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
