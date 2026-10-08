<?php
$pageTitle = 'Clients';
require_once __DIR__ . '/../../includes/header.php';

$clientModel = new Client();
$search = $_GET['search'] ?? '';
$clients = $clientModel->getByUser(Auth::userId(), $search);

// Stats
$nbClients = count($clients);
$caTotal = 0;
foreach ($clients as $c) {
    $caTotal += $c['montant'] ?? 0;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-users me-2"></i> Clients</h4>
    <a href="<?= APP_URL ?>/?page=clients/create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau client
    </a>
</div>

<!-- Cards dashboard -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Nombre de clients</p>
                        <h3 class="mb-0 text-primary"><?= $nbClients ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">CA Previsionnel</p>
                        <h3 class="mb-0 text-success"><?= Helper::formatMoney($caTotal) ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="fas fa-coins fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                            <th>Échéance</th>
                            <th class="text-end">Montant</th>
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
                            <td>
                                <?php if (!empty($client['date_echance'])): ?>
                                    <?php
                                    $echance = new DateTime($client['date_echance']);
                                    $now = new DateTime();
                                    $diff = $now->diff($echance);
                                    $isPast = $echance < $now;
                                    ?>
                                    <span class="<?= $isPast ? 'text-danger fw-bold' : '' ?>">
                                        <?= $echance->format('d/m/Y') ?>
                                        <?php if ($isPast): ?>
                                            <small>(<?= $diff->days ?>j retard)</small>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-end fw-semibold">
                                <?php if (!empty($client['montant'])): ?>
                                    <?= Helper::formatMoney($client['montant'], $client['devise'] ?? 'MAD') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= APP_URL ?>/?page=clients/view&id=<?= $client['id'] ?>" class="btn btn-sm btn-outline-info" title="Visualiser">
                                    <i class="fas fa-eye"></i>
                                </a>
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
