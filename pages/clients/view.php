<?php
$pageTitle = 'Fiche client';
require_once __DIR__ . '/../../includes/header.php';

$clientModel = new Client();
$echangeModel = new ClientEchange();
$docModel = new Document();
$id = (int)($_GET['id'] ?? 0);
$client = $clientModel->getById($id, Auth::userId());

if (!$client) {
    Helper::setError('Client introuvable.');
    Helper::redirect(APP_URL . '/?page=clients');
}

$echanges = $echangeModel->getByClient($id);
$documents = $docModel->getByClientId($id, Auth::userId());
?>

<div class="d-flex justify-content-between align-items-center page-header">
    <h4 class="page-title"><i class="fas fa-user me-2"></i> Fiche client</h4>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/?page=clients/edit&id=<?= $id ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-edit me-1"></i> Modifier
        </a>
        <a href="<?= APP_URL ?>/?page=clients" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
    </div>
</div>

<!-- Card info client (horizontale) -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="d-flex align-items-start gap-4">
                    <div class="stat-icon primary flex-shrink-0">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="mb-1"><?= Helper::sanitize($client['nom_client']) ?></h4>
                        <?php if (!empty($client['ice'])): ?>
                            <span class="me-3"><i class="fas fa-hashtag me-1 text-muted"></i> ICE: <code><?= Helper::sanitize($client['ice']) ?></code></span>
                        <?php endif; ?>
                        <?php if (!empty($client['email'])): ?>
                            <span class="me-3"><i class="fas fa-envelope me-1 text-muted"></i> <?= Helper::sanitize($client['email']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($client['telephone'])): ?>
                            <span class="me-3"><i class="fas fa-phone me-1 text-muted"></i> <?= Helper::sanitize($client['telephone']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($client['adresse'])): ?>
                            <div class="mt-1"><i class="fas fa-map-marker-alt me-1 text-muted"></i> <?= Helper::sanitize($client['adresse']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="mb-2">
                    <span class="text-muted">Devise :</span>
                    <span class="badge bg-secondary"><?= $client['devise'] ?? 'MAD' ?></span>
                </div>
                <?php if (!empty($client['date_echance'])): ?>
                    <div class="mb-2">
                        <span class="text-muted">Échéance :</span>
                        <strong class="<?= (new DateTime($client['date_echance']) < new DateTime()) ? 'text-danger' : '' ?>">
                            <?= date('d/m/Y', strtotime($client['date_echance'])) ?>
                        </strong>
                    </div>
                <?php endif; ?>
                <?php if (!empty($client['montant'])): ?>
                    <div>
                        <span class="text-muted">Montant :</span>
                        <strong class="text-primary fs-5"><?= Helper::formatMoney($client['montant'], $client['devise'] ?? 'MAD') ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Card Échanges -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-comments me-2"></i> Échanges (<?= count($echanges) ?>)</h6>
                <a href="<?= APP_URL ?>/?page=clients/edit&id=<?= $id ?>" class="btn btn-sm btn-outline-primary">Gérer</a>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($echanges)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-comments fa-2x mb-2 opacity-25"></i>
                        <p class="mb-0 small">Aucun échange.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($echanges as $e): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted"><?= date('d/m/Y', strtotime($e['date_echange'])) ?></small>
                                <strong class="small"><?= Helper::sanitize($e['titre']) ?></strong>
                            </div>
                            <p class="mb-1 small text-muted"><?= nl2br(Helper::sanitize($e['message'])) ?></p>
                            <?php if (!empty($e['document_url'])): ?>
                                <a href="<?= APP_URL ?>/<?= $e['document_url'] ?>" class="small" target="_blank">
                                    <i class="fas fa-paperclip me-1"></i> Document
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Card Documents -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Documents (<?= count($documents) ?>)</h6>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php if (empty($documents)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-file-invoice fa-2x mb-2 opacity-25"></i>
                        <p class="mb-0 small">Aucun document.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-<?= $doc['type_document'] === 'facture' ? 'success' : ($doc['type_document'] === 'devis' ? 'primary' : 'warning') ?> me-1">
                                        <?= ucfirst(str_replace('_', ' ', $doc['type_document'])) ?>
                                    </span>
                                    <strong class="small"><?= Helper::sanitize($doc['numero'] ?? '-') ?></strong>
                                </div>
                                <div class="text-end">
                                    <strong class="small"><?= Helper::formatMoney($doc['total_ht'], $doc['devise'] ?? 'MAD') ?></strong>
                                    <br>
                                    <span class="badge bg-<?= $doc['statut'] === 'paye' ? 'success' : ($doc['statut'] === 'envoye' ? 'info' : 'secondary') ?>">
                                        <?= ucfirst($doc['statut']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
