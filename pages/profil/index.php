<?php
$pageTitle = 'Profil';
require_once __DIR__ . '/../../includes/header.php';

$userModel = new User();
$user = $userModel->getById(Auth::userId());
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-id-card me-2"></i> Mon Profil</h4>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/?page=profil/edit" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Modifier
        </a>
        <?php if ($user['id']): ?>
        <a href="<?= APP_URL ?>/pages/profil/public.php?id=<?= $user['id'] ?>" class="btn btn-outline-info" target="_blank">
            <i class="fas fa-external-link-alt me-1"></i> Voir profil public
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <?php if (!empty($user['signature_url']) && file_exists(__DIR__ . '/../../' . $user['signature_url'])): ?>
                    <img src="<?= APP_URL . '/' . $user['signature_url'] ?>" alt="Signature"
                         style="max-height:100px;" class="mb-3">
                <?php endif; ?>
                <h5 class="mb-1"><?= Helper::sanitize($user['nom_complet']) ?></h5>
                <p class="text-muted mb-2"><?= Helper::sanitize($user['titre_pro'] ?? 'Auto-Entrepreneur') ?></p>
                <p class="text-muted small">
                    <i class="fas fa-map-marker-alt me-1"></i> <?= Helper::sanitize($user['ville'] ?? 'Non défini') ?>
                </p>
                <?php if (!empty($user['mots_cles'])): ?>
                    <div class="mt-2">
                        <?php foreach (explode(',', $user['mots_cles']) as $kw): ?>
                            <span class="badge bg-light text-dark"><?= Helper::sanitize(trim($kw)) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white"><h6 class="mb-0">Informations personnelles</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">Nom complet</strong>
                        <?= Helper::sanitize($user['nom_complet']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">Email</strong>
                        <?= Helper::sanitize($user['email']) ?>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">Téléphone</strong>
                        <?= Helper::sanitize($user['telephone'] ?? '-') ?>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">WhatsApp</strong>
                        <?= Helper::sanitize($user['whatsapp'] ?? '-') ?>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">Site web</strong>
                        <?= Helper::sanitize($user['site_web'] ?? '-') ?>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">Langue principale</strong>
                        <?= Helper::sanitize($user['langue_principale'] ?? '-') ?>
                    </div>
                    <div class="col-12">
                        <strong class="text-muted d-block small">Bio</strong>
                        <?= nl2br(Helper::sanitize($user['bio'] ?? '-')) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white"><h6 class="mb-0">Informations légales</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">Raison sociale</strong>
                        <?= Helper::sanitize($user['raison_sociale'] ?? '-') ?>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">ICE</strong>
                        <code><?= Helper::sanitize($user['ice'] ?? '-') ?></code>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">Identifiant fiscal</strong>
                        <code><?= Helper::sanitize($user['identifiant_fiscal'] ?? '-') ?></code>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">CNIE</strong>
                        <code><?= Helper::sanitize($user['cnie'] ?? '-') ?></code>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">Taxe professionnelle</strong>
                        <code><?= Helper::sanitize($user['taxe_professionnelle'] ?? '-') ?></code>
                    </div>
                    <div class="col-md-6">
                        <strong class="text-muted d-block small">Email professionnel</strong>
                        <?= Helper::sanitize($user['email_pro'] ?? '-') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
