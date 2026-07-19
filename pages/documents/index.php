<?php
$pageTitle = 'Documents';
require_once __DIR__ . '/../../includes/header.php';

$docModel = new Document();
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';
$documents = $docModel->getByUser(Auth::userId(), $type, $search);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Documents</h4>
    <a href="<?= APP_URL ?>/?page=documents/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau document
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <input type="hidden" name="page" value="documents">
            <div class="col-md-3">
                <select name="type" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="devis" <?= $type === 'devis' ? 'selected' : '' ?>>Devis</option>
                    <option value="facture" <?= $type === 'facture' ? 'selected' : '' ?>>Facture</option>
                    <option value="bon_livraison" <?= $type === 'bon_livraison' ? 'selected' : '' ?>>Bon de livraison</option>
                </select>
            </div>
            <div class="col-md-7">
                <input type="text" name="search" class="form-control" placeholder="Rechercher par client..."
                       value="<?= Helper::sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="fas fa-search me-1"></i> Filtrer
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($documents)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-file-circle-plus fa-3x mb-3 opacity-25"></i>
                <p>Aucun document trouvé.</p>
                <a href="<?= APP_URL ?>/?page=documents/create" class="btn btn-primary btn-sm">Créer un document</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Réf</th>
                            <th>Type</th>
                            <th>Client</th>
                            <th>Date</th>
                            <th class="text-end">Total TTC</th>
                            <th>Statut</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><code><?= Helper::sanitize($doc['numero'] ?? Helper::generateRef($doc['type_document'], date('Y', strtotime($doc['date_document'])), $doc['id'])) ?></code></td>
                            <td>
                                <?php
                                $labels = ['devis' => 'Devis', 'facture' => 'Facture', 'bon_livraison' => 'Bon livr.'];
                                $colors = ['devis' => 'primary', 'facture' => 'success', 'bon_livraison' => 'warning'];
                                ?>
                                <span class="badge bg-<?= $colors[$doc['type_document']] ?? 'secondary' ?>">
                                    <?= $labels[$doc['type_document']] ?? '' ?>
                                </span>
                            </td>
                            <td><?= Helper::sanitize($doc['nom_client']) ?></td>
                            <td><?= date('d/m/Y', strtotime($doc['date_document'])) ?></td>
                            <td class="text-end fw-semibold"><?= Helper::formatMoney($doc['total_ttc']) ?></td>
                            <td>
                                <?php
                                $badges = ['brouillon' => 'secondary', 'envoye' => 'info', 'paye' => 'success', 'annule' => 'danger'];
                                ?>
                                <span class="badge bg-<?= $badges[$doc['statut']] ?? 'secondary' ?>">
                                    <?= ucfirst($doc['statut']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>/?page=documents/view&id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-info" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= APP_URL ?>/?page=documents/pdf&id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-danger" title="PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <?php if ($doc['statut'] === 'brouillon'): ?>
                                <a href="<?= APP_URL ?>/?page=documents/edit&id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php endif; ?>
                                <a href="<?= APP_URL ?>/?page=documents/delete&id=<?= $doc['id'] ?>" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                   onclick="return confirm('Supprimer ce document ?')">
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
