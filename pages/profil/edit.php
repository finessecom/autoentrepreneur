<?php
$pageTitle = 'Modifier profil';
require_once __DIR__ . '/../../includes/header.php';

$userModel = new User();
$user = $userModel->getById(Auth::userId());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;

    // Handle signature upload
    if (!empty($_FILES['signature']['tmp_name'])) {
        $ext = pathinfo($_FILES['signature']['name'], PATHINFO_EXTENSION);
        $filename = 'sig_' . Auth::userId() . '_' . time() . '.' . $ext;
        $dest = SIGNATURE_DIR . $filename;
        if (move_uploaded_file($_FILES['signature']['tmp_name'], $dest)) {
            $userModel->updateSignature(Auth::userId(), 'uploads/signatures/' . $filename);
        }
    }

    // Handle logo upload
    if (!empty($_FILES['logo_pdf']['tmp_name'])) {
        $ext = pathinfo($_FILES['logo_pdf']['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . Auth::userId() . '_' . time() . '.' . $ext;
        $dest = LOGO_DIR . $filename;
        if (move_uploaded_file($_FILES['logo_pdf']['tmp_name'], $dest)) {
            $userModel->updateLogo(Auth::userId(), 'uploads/logos/' . $filename);
        }
    }

    // Handle password change
    if (!empty($_POST['new_password'])) {
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            Helper::setError('Les mots de passe ne correspondent pas.');
        } elseif (strlen($_POST['new_password']) < 6) {
            Helper::setError('Le mot de passe doit contenir au moins 6 caractères.');
        } elseif (!password_verify($_POST['current_password'], $user['password'])) {
            Helper::setError('Mot de passe actuel incorrect.');
        } else {
            $userModel->updatePassword(Auth::userId(), $_POST['new_password']);
        }
    }

    // Handle social networks
    $reseaux = [];
    if (!empty($_POST['social_platform']) && !empty($_POST['social_url'])) {
        foreach ($_POST['social_platform'] as $i => $platform) {
            if (!empty($platform) && !empty($_POST['social_url'][$i])) {
                $reseaux[$platform] = $_POST['social_url'][$i];
            }
        }
    }
    $data['reseaux_sociaux'] = $reseaux;

    unset($data['new_password'], $data['confirm_password'], $data['current_password']);
    unset($data['social_platform'], $data['social_url']);

    $userModel->update(Auth::userId(), $data);
    Helper::setSuccess('Profil mis à jour.');
    Helper::redirect(APP_URL . '/?page=profil');
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Modifier mon profil</h4>
    <a href="<?= APP_URL ?>/?page=profil" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<form method="POST" enctype="multipart/form-data">
    <div class="row g-4">
        <div class="col-md-8">
            <!-- Infos personnelles -->
            <div class="card mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Informations personnelles</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom complet *</label>
                            <input type="text" name="nom_complet" class="form-control" required value="<?= Helper::sanitize($user['nom_complet']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom d'affichage</label>
                            <input type="text" name="nom_affichage" class="form-control" value="<?= Helper::sanitize($user['nom_affichage']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titre professionnel (FR)</label>
                            <input type="text" name="titre_pro" class="form-control" value="<?= Helper::sanitize($user['titre_pro'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titre professionnel (AR)</label>
                            <input type="text" name="titre_pro_ar" class="form-control" dir="rtl" value="<?= Helper::sanitize($user['titre_pro_ar'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ville</label>
                            <input type="text" name="ville" class="form-control" value="<?= Helper::sanitize($user['ville'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Langue principale</label>
                            <select name="langue_principale" class="form-select">
                                <?php foreach (['Français', 'Arabe', 'Anglais'] as $lang): ?>
                                    <option value="<?= $lang ?>" <?= ($user['langue_principale'] ?? '') === $lang ? 'selected' : '' ?>><?= $lang ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" value="<?= Helper::sanitize($user['telephone'] ?? '') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">WhatsApp</label>
                            <input type="text" name="whatsapp" class="form-control" value="<?= Helper::sanitize($user['whatsapp'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Site web</label>
                            <input type="url" name="site_web" class="form-control" value="<?= Helper::sanitize($user['site_web'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mots-clés (séparés par virgule)</label>
                            <input type="text" name="mots_cles" class="form-control" value="<?= Helper::sanitize($user['mots_cles'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bio (FR)</label>
                            <textarea name="bio" class="form-control" rows="3"><?= Helper::sanitize($user['bio'] ?? '') ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Bio (AR)</label>
                            <textarea name="bio_ar" class="form-control" rows="3" dir="rtl"><?= Helper::sanitize($user['bio_ar'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Infos légales -->
            <div class="card mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Informations légales</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Raison sociale</label>
                            <input type="text" name="raison_sociale" class="form-control" value="<?= Helper::sanitize($user['raison_sociale'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email professionnel</label>
                            <input type="email" name="email_pro" class="form-control" value="<?= Helper::sanitize($user['email_pro'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ICE</label>
                            <input type="text" name="ice" class="form-control" value="<?= Helper::sanitize($user['ice'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Identifiant fiscal</label>
                            <input type="text" name="identifiant_fiscal" class="form-control" value="<?= Helper::sanitize($user['identifiant_fiscal'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CNIE</label>
                            <input type="text" name="cnie" class="form-control" value="<?= Helper::sanitize($user['cnie'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Taxe professionnelle</label>
                            <input type="text" name="taxe_professionnelle" class="form-control" value="<?= Helper::sanitize($user['taxe_professionnelle'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Préfixes documents -->
            <div class="card mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Préfixes des documents</h6></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Les numéros de documents seront générés automatiquement avec le préfixe choisi (ex: DEV-0001, FAC-0001).</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Préfixe Devis</label>
                            <input type="text" name="prefixe_devis" class="form-control" maxlength="20" value="<?= Helper::sanitize($user['prefixe_devis'] ?? 'DEV') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Préfixe Facture</label>
                            <input type="text" name="prefixe_facture" class="form-control" maxlength="20" value="<?= Helper::sanitize($user['prefixe_facture'] ?? 'FAC') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Préfixe Bon de livraison</label>
                            <input type="text" name="prefixe_livraison" class="form-control" maxlength="20" value="<?= Helper::sanitize($user['prefixe_livraison'] ?? 'BL') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Réseaux sociaux -->
            <div class="card mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Réseaux sociaux</h6></div>
                <div class="card-body" id="socialLinks">
                    <?php
                    $reseaux = json_decode($user['reseaux_sociaux'] ?? '{}', true) ?: [];
                    $first = true;
                    foreach ($reseaux as $platform => $url):
                    ?>
                    <div class="row g-2 mb-2 social-row">
                        <div class="col-md-4">
                            <select name="social_platform[]" class="form-select form-select-sm">
                                <?php foreach (['Instagram','Facebook','LinkedIn','Twitter','YouTube','TikTok','Autre'] as $p): ?>
                                    <option value="<?= $p ?>" <?= $p === $platform ? 'selected' : '' ?>><?= $p ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <input type="url" name="social_url[]" class="form-control form-control-sm" value="<?= Helper::sanitize($url) ?>">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.social-row').remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-sm btn-outline-success" id="addSocial">
                        <i class="fas fa-plus me-1"></i> Ajouter un réseau
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Uploads -->
            <div class="card mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Logo & Signature</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Logo (pour PDF)</label>
                        <?php if (!empty($user['logo_pdf_url'])): ?>
                            <div class="mb-2"><img src="<?= APP_URL . '/' . $user['logo_pdf_url'] ?>" alt="Logo" style="max-height:60px;"></div>
                        <?php endif; ?>
                        <input type="file" name="logo_pdf" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Signature</label>
                        <?php if (!empty($user['signature_url'])): ?>
                            <div class="mb-2"><img src="<?= APP_URL . '/' . $user['signature_url'] ?>" alt="Signature" style="max-height:60px;"></div>
                        <?php endif; ?>
                        <input type="file" name="signature" class="form-control" accept="image/*">
                    </div>
                    <div>
                        <label class="form-label">Taille signature</label>
                        <select name="signature_taille" class="form-select">
                            <?php foreach ([50 => 'Petite', 100 => 'Moyenne', 150 => 'Grande', 200 => 'Très grande'] as $val => $label): ?>
                                <option value="<?= $val ?>" <?= ($user['signature_taille'] ?? 100) == $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Mot de passe -->
            <div class="card mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Changer le mot de passe</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Mot de passe actuel</label>
                        <input type="password" name="current_password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control" minlength="6">
                    </div>
                    <div>
                        <label class="form-label">Confirmer</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>
                </div>
            </div>

            <!-- Préfixes documents -->
            <div class="card mb-4">
                <div class="card-header bg-white"><h6 class="mb-0">Préfixes des documents</h6></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Les numéros seront générés automatiquement (ex: DEV-0001).</p>
                    <div class="mb-3">
                        <label class="form-label">Préfixe Devis</label>
                        <input type="text" name="prefixe_devis" class="form-control" maxlength="10" value="<?= Helper::sanitize($user['prefixe_devis'] ?? 'DEV') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Préfixe Facture</label>
                        <input type="text" name="prefixe_facture" class="form-control" maxlength="10" value="<?= Helper::sanitize($user['prefixe_facture'] ?? 'FAC') ?>">
                    </div>
                    <div>
                        <label class="form-label">Préfixe Bon de livraison</label>
                        <input type="text" name="prefixe_livraison" class="form-control" maxlength="10" value="<?= Helper::sanitize($user['prefixe_livraison'] ?? 'BL') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="<?= APP_URL ?>/?page=profil" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    </div>
</form>

<script>
document.getElementById('addSocial').addEventListener('click', function() {
    const html = `<div class="row g-2 mb-2 social-row">
        <div class="col-md-4">
            <select name="social_platform[]" class="form-select form-select-sm">
                <?php foreach (['Instagram','Facebook','LinkedIn','Twitter','YouTube','TikTok','Autre'] as $p): ?>
                    <option value="<?= $p ?>"><?= $p ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-7">
            <input type="url" name="social_url[]" class="form-control form-control-sm" placeholder="https://...">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.social-row').remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>`;
    document.getElementById('socialLinks').insertAdjacentHTML('beforeend', html);
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
