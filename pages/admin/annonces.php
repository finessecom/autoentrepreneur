<?php
$pageTitle = 'Annonces & Posts';
Auth::requireAdmin();

$annonceModel = new Annonce();

// Suppression
if (isset($_GET['delete'])) {
    $annonceModel->delete((int) $_GET['delete']);
    Helper::setSuccess('Supprimé avec succès.');
    Helper::redirect(APP_URL . '/?page=admin/annonces');
}

// Création / Modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $type = $_POST['type'] ?? 'annonce';
    $titre = trim($_POST['titre'] ?? '');
    $texte = trim($_POST['texte'] ?? '');
    $editId = (int) ($_POST['edit_id'] ?? 0);

    if (empty($titre) || empty($texte)) {
        Helper::setError('Titre et texte sont obligatoires.');
    } else {
        if ($editId) {
            $annonceModel->update($editId, $type, $titre, $texte);
            Helper::setSuccess('Annonce modifiée.');
        } else {
            $annonceModel->create(Auth::userId(), $type, $titre, $texte);
            Helper::setSuccess('Annonce publiée.');
        }
        Helper::redirect(APP_URL . '/?page=admin/annonces');
    }
}

require_once __DIR__ . '/../../includes/header.php';

$annonces = $annonceModel->getAll();
$editAnnonce = null;
if (isset($_GET['edit'])) {
    $editAnnonce = $annonceModel->getById((int) $_GET['edit']);
}
?>

<div x-data="{ deleteId: null, deleteTitle: '' }">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-bullhorn me-2"></i> Annonces & Posts</h4>
        <a href="<?= APP_URL ?>/?page=admin" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Retour
        </a>
    </div>

    <div class="row g-4">
        <!-- Form Column -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><?= $editAnnonce ? 'Modifier' : 'Nouvelle annonce' ?></h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?= csrf_field() ?>
                        <?php if ($editAnnonce): ?>
                            <input type="hidden" name="edit_id" value="<?= $editAnnonce['id'] ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select" required>
                                <option value="annonce" <?= ($editAnnonce['type'] ?? '') === 'annonce' ? 'selected' : '' ?>>Annonce</option>
                                <option value="post" <?= ($editAnnonce['type'] ?? '') === 'post' ? 'selected' : '' ?>>Post</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Titre *</label>
                            <input type="text" name="titre" class="form-control" required
                                   value="<?= Helper::sanitize($editAnnonce['titre'] ?? '') ?>" placeholder="Titre de l'annonce">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Texte *</label>
                            <textarea name="texte" class="form-control" rows="5" required
                                      placeholder="Contenu..."><?= Helper::sanitize($editAnnonce['texte'] ?? '') ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> <?= $editAnnonce ? 'Modifier' : 'Publier' ?>
                            </button>
                            <?php if ($editAnnonce): ?>
                                <a href="<?= APP_URL ?>/?page=admin/annonces" class="btn btn-outline-secondary">Annuler</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- List Column -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Publications (<?= count($annonces) ?>)</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($annonces)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-bullhorn fa-3x mb-3 opacity-25"></i>
                            <p>Aucune publication.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Titre</th>
                                        <th>Date</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($annonces as $a): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-<?= $a['type'] === 'post' ? 'info' : 'warning' ?>">
                                                <?= $a['type'] === 'post' ? 'Post' : 'Annonce' ?>
                                            </span>
                                        </td>
                                        <td><?= Helper::sanitize($a['titre']) ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></td>
                                        <td class="text-center">
                                            <a href="<?= APP_URL ?>/?page=admin/annonces&edit=<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                                    @click="deleteId = <?= $a['id'] ?>; deleteTitle = '<?= Helper::sanitize(addslashes($a['titre'])) ?>'"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i> Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer cette publication ?</p>
                    <p class="fw-semibold text-muted" x-text="deleteTitle"></p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a :href="'<?= APP_URL ?>/?page=admin/annonces&delete=' + deleteId" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
