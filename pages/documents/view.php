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

// Traitement mise à jour déclaration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_declaration'])) {
    $trimestre = !empty($_POST['decl_trimestre']) ? (int)$_POST['decl_trimestre'] : null;
    $annee = !empty($_POST['decl_annee']) ? (int)$_POST['decl_annee'] : null;
    $declTotal = !empty($_POST['decl_total']) ? (float)$_POST['decl_total'] : null;
    $docModel->updateDeclaration($id, Auth::userId(), $trimestre, $annee, $declTotal);
    Helper::setSuccess('Paramètres de déclaration mis à jour.');
    Helper::redirect(APP_URL . '/?page=documents/view&id=' . $id);
}
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

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100 border-primary">
            <div class="card-header bg-primary text-white py-2">
                <h6 class="mb-0"><i class="fas fa-building me-1"></i> Émetteur</h6>
            </div>
            <div class="card-body py-2">
                <strong><?= Helper::sanitize($user['nom_complet']) ?></strong><br>
                <?= Helper::sanitize($user['raison_sociale'] ?? '') ?><br>
                <?= Helper::sanitize($user['ville'] ?? '') ?><br>
                ICE: <code><?= Helper::sanitize($user['ice'] ?? '-') ?></code><br>
                IF: <code><?= Helper::sanitize($user['identifiant_fiscal'] ?? '-') ?></code><br>
                Banque: <strong><?= Helper::sanitize($user['nom_banque'] ?? '-') ?></strong><br>
                RIB: <code><?= Helper::sanitize($user['rib'] ?? '-') ?></code>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 border-success">
            <div class="card-header bg-success text-white py-2">
                <h6 class="mb-0"><i class="fas fa-user-tie me-1"></i> Client</h6>
            </div>
            <div class="card-body py-2">
                <strong><?= Helper::sanitize($doc['nom_client']) ?></strong><br>
                ICE: <code><?= Helper::sanitize($doc['client_ice'] ?? '-') ?></code><br>
                Tél: <?= Helper::sanitize($doc['client_telephone'] ?? '-') ?><br>
                Email: <?= Helper::sanitize($doc['client_email'] ?? '-') ?><br>
                <?= Helper::sanitize($doc['client_adresse'] ?? '') ?>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body py-2">
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
                        <td>
                            <?= nl2br(Helper::sanitize($item['designation'])) ?>
                            <?php if (!empty($item['detail'])): ?>
                                <br><small class="text-muted"><?= nl2br(Helper::sanitize($item['detail'])) ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $item['quantite'] ?></td>
                        <td class="text-end"><?= Helper::formatMoney($item['prix_unitaire'], $doc['devise'] ?? 'MAD') ?></td>
                        <td class="text-end fw-semibold"><?= Helper::formatMoney($item['total_ligne'], $doc['devise'] ?? 'MAD') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot class="table-active">
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Total HT :</td>
                        <td class="text-end fw-bold fs-5"><?= Helper::formatMoney($doc['total_ht'], $doc['devise'] ?? 'MAD') ?></td>
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
            Montant en lettres : <strong><?= Helper::montantEnLettres($doc['total_ht'], $doc['devise'] ?? 'MAD') ?></strong>
        </small>
    </div>
</div>

<?php if ($doc['type_document'] === 'facture' && $doc['statut'] === 'paye'): ?>
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white">
        <h6 class="mb-0"><i class="fas fa-calculator me-2"></i> Déclaration fiscale</h6>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="update_declaration" value="1">
            <p class="text-muted small mb-3">Associez cette facture payée à une déclaration trimestrielle.</p>
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Année</label>
                    <select name="decl_annee" class="form-select">
                        <option value="">-- Non définie --</option>
                        <?php for ($y = (int)date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?= $y ?>" <?= ($doc['decl_annee'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trimestre</label>
                    <select name="decl_trimestre" class="form-select">
                        <option value="">-- Non défini --</option>
                        <option value="1" <?= ($doc['decl_trimestre'] ?? '') == '1' ? 'selected' : '' ?>>T1 (Jan-Fév-Mar)</option>
                        <option value="2" <?= ($doc['decl_trimestre'] ?? '') == '2' ? 'selected' : '' ?>>T2 (Avr-Mai-Jun)</option>
                        <option value="3" <?= ($doc['decl_trimestre'] ?? '') == '3' ? 'selected' : '' ?>>T3 (Jul-Aoû-Sep)</option>
                        <option value="4" <?= ($doc['decl_trimestre'] ?? '') == '4' ? 'selected' : '' ?>>T4 (Oct-Nov-Déc)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Total facture (MAD)</label>
                    <input type="number" name="decl_total" class="form-control" step="0.01" min="0"
                           value="<?= Helper::sanitize($doc['decl_total'] ?? $doc['total_ht']) ?>"
                           placeholder="Montant en MAD">
                    <small class="text-muted">Montant pris en compte dans la déclaration</small>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </div>
            <?php if (!empty($doc['decl_annee']) && !empty($doc['decl_trimestre'])): ?>
                <div class="mt-2">
                    <small class="text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        Associé à la déclaration T<?= $doc['decl_trimestre'] ?> <?= $doc['decl_annee'] ?>
                    </small>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
