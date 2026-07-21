<?php
$pageTitle = 'Détail';
require_once __DIR__ . '/../../includes/header.php';

$annonceModel = new Annonce();
$id = (int)($_GET['id'] ?? 0);
$annonce = $annonceModel->getById($id);

if (!$annonce) {
    Helper::setError('Annonce introuvable.');
    Helper::redirect(APP_URL . '/?page=dashboard');
}

$auteurModel = new User();
$auteur = $auteurModel->getById($annonce['user_id']);
$typeLabel = $annonce['type'] === 'post' ? 'Post' : 'Annonce';
$typeColor = $annonce['type'] === 'post' ? 'info' : 'warning';
$typeIcon = $annonce['type'] === 'post' ? 'fa-newspaper' : 'fa-bullhorn';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="fas <?= $typeIcon ?> me-2 text-<?= $typeColor ?>"></i>
        <?= $typeLabel ?>
    </h4>
    <a href="<?= APP_URL ?>/?page=dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h3 class="mb-0"><?= Helper::sanitize($annonce['titre']) ?></h3>
            <span class="badge bg-<?= $typeColor ?>"><?= $typeLabel ?></span>
        </div>
        <div class="text-muted mb-4">
            <i class="fas fa-calendar me-1"></i> <?= date('d/m/Y H:i', strtotime($annonce['created_at'])) ?>
            <?php if ($auteur): ?>
                <span class="ms-3"><i class="fas fa-user me-1"></i> <?= Helper::sanitize($auteur['nom_affichage'] ?? $auteur['nom_complet']) ?></span>
            <?php endif; ?>
        </div>
        <hr>
        <div class="fs-6" style="line-height: 1.8;">
            <?= nl2br(Helper::sanitize($annonce['texte'])) ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
