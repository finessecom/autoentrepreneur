<?php
$pageTitle = 'Administration';
Auth::requireAdmin();
require_once __DIR__ . '/../../includes/header.php';

$userModel = new User();
$docModel = new Document();
$declModel = new Declaration();
$clientModel = new Client();

$totalUsers = $userModel->count();
$allUsers = $userModel->getAll();
$docStats = $docModel->countByTypeAll();
$totalDocs = $docModel->countAll();
$totalCA = $docModel->totalCAAll();
$totalDeclarations = $declModel->countAll();

$usersJson = Helper::sanitize(json_encode($allUsers));
?>

<div x-data="adminDashboard(<?= $usersJson ?>)">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-cog me-2"></i> Administration</h4>
        <div class="d-flex gap-2 align-items-center">
            <div class="input-group" style="width: 280px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control" placeholder="Rechercher..." x-model="search">
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-primary-soft">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Utilisateurs</div>
                        <div class="stat-value"><?= $totalUsers ?></div>
                    </div>
                    <div class="stat-icon primary"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-primary-soft">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Devis</div>
                        <div class="stat-value"><?= $docStats['devis'] ?></div>
                    </div>
                    <div class="stat-icon primary"><i class="fas fa-file-alt"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-success-soft">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">Factures</div>
                        <div class="stat-value"><?= $docStats['facture'] ?></div>
                    </div>
                    <div class="stat-icon success"><i class="fas fa-file-invoice-dollar"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card bg-warning-soft">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-label">CA Global</div>
                        <div class="stat-value"><?= Helper::formatMoney($totalCA) ?></div>
                    </div>
                    <div class="stat-icon warning"><i class="fas fa-coins"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-users me-2"></i> Utilisateurs</h6>
            <span class="badge bg-primary" x-text="users.length + ' total'"></span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Ville</th>
                            <th>ICE</th>
                            <th>Inscription</th>
                        </tr>
                    </thead>
                    <tbody>
                    <template x-for="u in users" :key="u.id">
                        <tr>
                            <td x-text="u.id"></td>
                            <td>
                                <a href="#" @click.prevent="openUser(u)" class="text-decoration-none fw-semibold"
                                   x-text="u.nom_complet"></a>
                            </td>
                            <td x-text="u.email"></td>
                            <td>
                                <span class="badge" :class="u.role === 'admin' ? 'bg-danger' : 'bg-primary'"
                                      x-text="u.role === 'admin' ? 'Admin' : 'User'"></span>
                            </td>
                            <td x-text="u.ville || '-'"></td>
                            <td><code x-text="u.ice || '-'"></code></td>
                            <td x-text="formatDate(u.created_at)"></td>
                        </tr>
                    </template>
                    </tbody>
                </table>
            </div>
            <div x-show="users.length === 0" class="text-center py-4 text-muted">
                <i class="fas fa-search fa-2x mb-2 opacity-25"></i>
                <p>Aucun utilisateur trouvé.</p>
            </div>
        </div>
    </div>

    <!-- User Detail Modal (Bootstrap) -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true" ref="userModalEl">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user me-2"></i> Détails utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" x-show="selectedUser">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Nom complet</label>
                            <p class="fw-semibold" x-text="selectedUser?.nom_complet || '-'"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Email</label>
                            <p class="fw-semibold" x-text="selectedUser?.email || '-'"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Rôle</label>
                            <p>
                                <span class="badge" :class="selectedUser?.role === 'admin' ? 'bg-danger' : 'bg-primary'"
                                      x-text="selectedUser?.role === 'admin' ? 'Admin' : 'User'"></span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Ville</label>
                            <p x-text="selectedUser?.ville || '-'"></p>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">ICE</label>
                            <p><code x-text="selectedUser?.ice || '-'"></code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Date d'inscription</label>
                            <p x-text="formatDate(selectedUser?.created_at)"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function adminDashboard(allUsers) {
    return {
        search: '',
        allUsers: allUsers,
        selectedUser: null,

        get users() {
            if (!this.search) return this.allUsers;
            const q = this.search.toLowerCase();
            return this.allUsers.filter(u =>
                (u.nom_complet || '').toLowerCase().includes(q) ||
                (u.email || '').toLowerCase().includes(q) ||
                (u.ville || '').toLowerCase().includes(q) ||
                (u.ice || '').toLowerCase().includes(q)
            );
        },

        openUser(user) {
            this.selectedUser = user;
            const modal = new bootstrap.Modal(document.getElementById('userModal'));
            modal.show();
        },

        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
        }
    };
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
