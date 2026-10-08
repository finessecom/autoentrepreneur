<?php
$pageTitle = 'Modifier produit/service';
$prodModel = new ProduitService();
$id = (int)($_GET['id'] ?? 0);
$produit = $prodModel->getById($id, Auth::userId());

if (!$produit) {
    Helper::setError('Produit introuvable.');
    Helper::redirect(APP_URL . '/?page=produits');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $data = $_POST;
    if (!empty($_FILES['image']['tmp_name'])) {
        $error = Helper::validateUpload($_FILES['image'], ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        if ($error) {
            Helper::setError($error);
            Helper::redirect(APP_URL . '/?page=produits/edit&id=' . $id);
        }
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
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

require_once __DIR__ . '/../../includes/header.php';
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
            <?= csrf_field() ?>
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
                    <textarea name="designation" class="form-control" rows="3" required
                              placeholder="Nom du produit/service..."><?= Helper::sanitize($produit['designation']) ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Détail</label>
                    <textarea name="detail" class="form-control" rows="2"
                              placeholder="Description détaillée..."><?= Helper::sanitize($produit['detail'] ?? '') ?></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Prix unitaire *</label>
                    <input type="number" name="prix_unitaire" class="form-control" required step="0.01" min="0"
                           value="<?= $produit['prix_unitaire'] ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Devise</label>
                    <select name="devise" class="form-select">
                        <?php foreach (Helper::devises() as $d): ?>
                            <option value="<?= $d ?>" <?= ($produit['devise'] ?? 'MAD') === $d ? 'selected' : '' ?>><?= $d ?></option>
                        <?php endforeach; ?>
                    </select>
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
