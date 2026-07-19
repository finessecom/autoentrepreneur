<?php
$pageTitle = 'Visualiser document';
require_once __DIR__ . '/../../includes/header.php';

$docModel = new Document();
$userModel = new User();
$id = (int)($_GET['id'] ?? 0);
$doc = $docModel->getById($id, Auth::userId());

if (!$doc) {
    Helper::setError('Document introuvable.');
    Helper::redirect(APP_URL . '/?page=documents');
}

$items = $docModel->getItems($id);
$user = $userModel->getById(Auth::userId());
$ref = $doc['numero'] ?? Helper::generateRef($doc['type_document'], date('Y', strtotime($doc['date_document'])), $doc['id']);

$labels = ['devis' => 'DEVIS', 'facture' => 'FACTURE', 'bon_livraison' => 'BON DE LIVRAISON'];
$badges = ['brouillon' => 'secondary', 'envoye' => 'info', 'paye' => 'success', 'annule' => 'danger'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <?= $labels[$doc['type_document']] ?? 'Document' ?>
        <code class="ms-2 fs-6"><?= $ref ?></code>
    </h4>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/?page=documents" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
        <a href="<?= APP_URL ?>/?page=documents/pdf&id=<?= $id ?>" class="btn btn-danger btn-sm" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </a>
        <?php if ($doc['statut'] === 'brouillon'): ?>
        <a href="<?= APP_URL ?>/?page=documents/edit&id=<?= $id ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-edit me-1"></i> Modifier
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted">Émetteur</h6>
                <strong><?= Helper::sanitize($user['nom_complet']) ?></strong><br>
                <?= Helper::sanitize($user['raison_sociale'] ?? '') ?><br>
                ICE: <code><?= Helper::sanitize($user['ice'] ?? '-') ?></code><br>
                <?= Helper::sanitize($user['ville'] ?? '') ?>
            </div>
            <div class="col-md-6 text-md-end">
                <h6 class="text-muted">Client</h6>
                <strong><?= Helper::sanitize($doc['nom_client']) ?></strong><br>
                ICE: <code><?= Helper::sanitize($doc['client_ice'] ?? '-') ?></code><br>
                <?= Helper::sanitize($doc['client_email'] ?? '') ?><br>
                <?= Helper::sanitize($doc['client_adresse'] ?? '') ?>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4">
                <strong>Date :</strong> <?= date('d/m/Y', strtotime($doc['date_document'])) ?>
            </div>
            <div class="col-md-4">
                <strong>Statut :</strong>
                <span class="badge bg-<?= $badges[$doc['statut']] ?? 'secondary' ?>"><?= ucfirst($doc['statut']) ?></span>
            </div>
            <div class="col-md-4 text-md-end">
                <strong>Réf :</strong> <?= $ref ?>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr><th>Désignation</th><th class="text-center">Qté</th><th class="text-end">Prix unitaire</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= Helper::sanitize($item['designation']) ?></td>
                        <td class="text-center"><?= $item['quantite'] ?></td>
                        <td class="text-end"><?= Helper::formatMoney($item['prix_unitaire']) ?></td>
                        <td class="text-end fw-semibold"><?= Helper::formatMoney($item['total_ligne']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-active">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total TTC :</td>
                        <td class="text-end fw-bold fs-5"><?= Helper::formatMoney($doc['total_ttc']) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Montant en lettres : <strong><?= Helper::montantEnLettres($doc['total_ttc']) ?></strong>
        </small>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
