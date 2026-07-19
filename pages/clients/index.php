<?php
$pageTitle = 'Clients';
require_once __DIR__ . '/../../includes/header.php';

$clientModel = new Client();
$search = $_GET['search'] ?? '';
$clients = $clientModel->getByUser(Auth::userId(), $search);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-users me-2"></i> Clients</h4>
    <a href="<?= APP_URL ?>/?page=clients/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau client
    </a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2">
            <input type="hidden" name="page" value="clients">
            <div class="col-md-10">
                <input type="text" name="search" class="form-control" placeholder="Rechercher par nom, ICE, email..."
                       value="<?= Helper::sanitize($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="fas fa-search me-1"></i> Rechercher
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($clients)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3 opacity-25"></i>
                <p>Aucun client trouvé.</p>
                <a href="<?= APP_URL ?>/?page=clients/create" class="btn btn-primary btn-sm">Ajouter un client</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>ICE</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Ville</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td class="fw-semibold"><?= Helper::sanitize($client['nom_client']) ?></td>
                            <td><code><?= Helper::sanitize($client['ice'] ?? '-') ?></code></td>
                            <td><?= Helper::sanitize($client['email'] ?? '-') ?></td>
                            <td><?= Helper::sanitize($client['telephone'] ?? '-') ?></td>
                            <td><?= Helper::sanitize(explode("\n", $client['adresse'] ?? '')[0] ?? '-') ?></td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>/?page=clients/edit&id=<?= $client['id'] ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= APP_URL ?>/?page=clients/delete&id=<?= $client['id'] ?>" class="btn btn-sm btn-outline-danger" title="Supprimer"
                                   onclick="return confirm('Supprimer ce client ?')">
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
