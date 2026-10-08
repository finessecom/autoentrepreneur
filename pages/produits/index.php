<?php
$pageTitle = 'Produits';
require_once __DIR__ . '/../../includes/header.php';

$prodModel = new ProduitService();
$type = $_GET['type'] ?? '';
$produits = $prodModel->getByUser(Auth::userId(), $type);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-box me-2"></i> Produits / Services</h4>
    <a href="<?= APP_URL ?>/?page=produits/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau Produit ou Service
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <input type="hidden" name="page" value="produits">
            <div class="col-md-4">
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="commerce" <?= $type === 'commerce' ? 'selected' : '' ?>>Commerce (Produits)</option>
                    <option value="service" <?= $type === 'service' ? 'selected' : '' ?>>Services</option>
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
        <?php if (empty($produits)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-box fa-3x mb-3 opacity-25"></i>
                <p>Aucun produit/service trouvé.</p>
                <a href="<?= APP_URL ?>/?page=produits/create" class="btn btn-primary btn-sm">Ajouter un produit</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Désignation</th>
                            <th>Type</th>
                            <th class="text-end">Prix unitaire</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($produits as $prod): ?>
                        <tr>
                            <td>
                                <?php if ($prod['image_url']): ?>
                                    <img src="<?= APP_URL . '/' . $prod['image_url'] ?>" alt="" width="32" height="32" class="rounded me-2" style="object-fit:cover">
                                <?php endif; ?>
                                <?= nl2br(Helper::sanitize($prod['designation'])) ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $prod['type_activite'] === 'commerce' ? 'primary' : 'success' ?>">
                                    <?= ucfirst($prod['type_activite']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-semibold"><?= Helper::formatMoney($prod['prix_unitaire'], $prod['devise'] ?? 'MAD') ?></td>
                            <td><?= date('d/m/Y', strtotime($prod['created_at'])) ?></td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>/?page=produits/edit&id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= APP_URL ?>/?page=produits/delete&id=<?= $prod['id'] ?>" class="btn btn-sm btn-outline-danger"
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

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
