<?php
$pageTitle = 'Modifier client';
$clientModel = new Client();
$echangeModel = new ClientEchange();
$id = (int)($_GET['id'] ?? 0);
$client = $clientModel->getById($id, Auth::userId());

if (!$client) {
    Helper::setError('Client introuvable.');
    Helper::redirect(APP_URL . '/?page=clients');
}

// Traitement modification client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_client'])) {
    verify_csrf_token();
    $clientModel->update($id, Auth::userId(), $_POST);
    Helper::setSuccess('Client modifié avec succès.');
    Helper::redirect(APP_URL . '/?page=clients/edit&id=' . $id);
}

// Traitement ajout echange
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_echange'])) {
    verify_csrf_token();
    $data = $_POST;
    $data['date_echange'] = $_POST['date_echange'] ?? date('Y-m-d');

    // Gestion upload document
    if (!empty($_FILES['document']['tmp_name'])) {
        $error = Helper::validateUpload($_FILES['document'], ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'], 10485760);
        if ($error) {
            Helper::setError($error);
            Helper::redirect(APP_URL . '/?page=clients/edit&id=' . $id);
        }
        $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        $filename = 'echg_' . $id . '_' . time() . '.' . $ext;
        $dest = UPLOAD_DIR . 'echanges/' . $filename;
        if (!is_dir(UPLOAD_DIR . 'echanges/')) {
            mkdir(UPLOAD_DIR . 'echanges/', 0755, true);
        }
        if (move_uploaded_file($_FILES['document']['tmp_name'], $dest)) {
            $data['document_url'] = 'uploads/echanges/' . $filename;
        }
    }

    $echangeModel->create($id, Auth::userId(), $data);
    Helper::setSuccess('Échange ajouté.');
    Helper::redirect(APP_URL . '/?page=clients/edit&id=' . $id);
}

// Suppression echange
if (isset($_GET['delete_echange'])) {
    $echangeModel->delete((int) $_GET['delete_echange'], Auth::userId());
    Helper::setSuccess('Échange supprimé.');
    Helper::redirect(APP_URL . '/?page=clients/edit&id=' . $id);
}

require_once __DIR__ . '/../../includes/header.php';

$echanges = $echangeModel->getByClient($id);
?>

<div class="d-flex justify-content-between align-items-center page-header">
    <h4 class="page-title"><i class="fas fa-edit me-2"></i> Modifier client</h4>
    <a href="<?= APP_URL ?>/?page=clients" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<!-- Infos client -->
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">Informations client</h6></div>
    <div class="card-body">
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="update_client" value="1">
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
                <div class="col-md-6">
                    <label class="form-label">Date échéance</label>
                    <input type="date" name="date_echance" class="form-control"
                           value="<?= Helper::sanitize($client['date_echance'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Montant</label>
                    <input type="number" name="montant" class="form-control" step="0.01" min="0"
                           value="<?= Helper::sanitize($client['montant'] ?? '') ?>">
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

<!-- Échanges avec le client -->
<div class="card mb-4">
    <div class="card-header"><h6 class="mb-0"><i class="fas fa-comments me-2"></i> Échanges avec le client (<?= count($echanges) ?>)</h6></div>
    <div class="card-body">
        <!-- Formulaire ajout -->
        <form method="POST" enctype="multipart/form-data" class="mb-4 p-3 bg-light rounded">
            <?= csrf_field() ?>
            <input type="hidden" name="add_echange" value="1">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date *</label>
                    <input type="date" name="date_echange" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Titre *</label>
                    <input type="text" name="titre" class="form-control" required placeholder="Ex: Appel téléphonique, Email envoyé...">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Document joint</label>
                    <input type="file" name="document" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg">
                </div>
                <div class="col-12">
                    <label class="form-label">Message *</label>
                    <textarea name="message" class="form-control" rows="3" required placeholder="Détails de l'échange..."></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Ajouter l'échange
                    </button>
                </div>
            </div>
        </form>

        <!-- Liste des échanges -->
        <?php if (empty($echanges)): ?>
            <div class="text-center py-4 text-muted">
                <i class="fas fa-comments fa-3x mb-3 opacity-25"></i>
                <p>Aucun échange avec ce client.</p>
            </div>
        <?php else: ?>
            <?php foreach ($echanges as $e): ?>
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">
                                <i class="fas fa-calendar me-1 text-muted"></i>
                                <?= date('d/m/Y', strtotime($e['date_echange'])) ?>
                                <span class="ms-2 fw-bold"><?= Helper::sanitize($e['titre']) ?></span>
                            </h6>
                            <p class="mb-2 text-muted"><?= nl2br(Helper::sanitize($e['message'])) ?></p>
                            <?php if (!empty($e['document_url'])): ?>
                                <a href="<?= APP_URL ?>/<?= $e['document_url'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-paperclip me-1"></i> Document joint
                                </a>
                            <?php endif; ?>
                        </div>
                        <a href="<?= APP_URL ?>/?page=clients/edit&id=<?= $id ?>&delete_echange=<?= $e['id'] ?>"
                           class="btn btn-sm btn-outline-danger" title="Supprimer"
                           onclick="return confirm('Supprimer cet échange ?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
