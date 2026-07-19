<?php
$pageTitle = 'Modifier document';
require_once __DIR__ . '/../../includes/header.php';

$docModel = new Document();
$clientModel = new Client();
$prodModel = new ProduitService();

$id = (int)($_GET['id'] ?? 0);
$doc = $docModel->getById($id, Auth::userId());

if (!$doc || $doc['statut'] !== 'brouillon') {
    Helper::setError('Document introuvable ou non modifiable.');
    Helper::redirect(APP_URL . '/?page=documents');
}

$items = $docModel->getItems($id);
$clients = $clientModel->getByUser(Auth::userId());
$produits = $prodModel->getByUser(Auth::userId());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'client_id'     => (int)$_POST['client_id'],
        'type_document' => $_POST['type_document'],
        'date_document' => $_POST['date_document'],
        'statut'        => $_POST['statut'] ?? 'brouillon',
    ];

    $newItems = [];
    if (!empty($_POST['item_designation'])) {
        foreach ($_POST['item_designation'] as $i => $designation) {
            if (empty(trim($designation))) continue;
            $newItems[] = [
                'produit_service_id' => !empty($_POST['item_produit_id'][$i]) ? (int)$_POST['item_produit_id'][$i] : null,
                'designation'        => trim($designation),
                'quantite'           => (int)$_POST['item_quantite'][$i],
                'prix_unitaire'      => (float)$_POST['item_prix'][$i],
            ];
        }
    }

    if (empty($newItems)) {
        Helper::setError('Ajoutez au moins une ligne.');
    } else {
        $docModel->update($id, Auth::userId(), $data, $newItems);
        Helper::setSuccess('Document modifié.');
        Helper::redirect(APP_URL . '/?page=documents/view&id=' . $id);
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Modifier document <code class="fs-6"><?= Helper::sanitize($doc['numero'] ?? '') ?></code></h4>
    <a href="<?= APP_URL ?>/?page=documents" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<form method="POST">
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Type</label>
                    <select name="type_document" class="form-select" required>
                        <?php foreach (['devis' => 'Devis', 'facture' => 'Facture', 'bon_livraison' => 'Bon de livraison'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $doc['type_document'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date_document" class="form-control" required value="<?= $doc['date_document'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach (['brouillon' => 'Brouillon', 'envoye' => 'Envoyé', 'paye' => 'Payé', 'annule' => 'Annulé'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $doc['statut'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-select" required>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $doc['client_id'] == $c['id'] ? 'selected' : '' ?>><?= Helper::sanitize($c['nom_client']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Lignes</h6>
            <button type="button" class="btn btn-sm btn-outline-success" id="addLine">
                <i class="fas fa-plus me-1"></i> Ajouter
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="itemsTable">
                    <thead><tr><th>Désignation</th><th>Produit</th><th>Qté</th><th>Prix</th><th>Total</th><th></th></tr></thead>
                    <tbody id="itemsBody"></tbody>
                    <tfoot><tr class="table-active"><td colspan="4" class="text-end fw-bold">Total :</td><td id="totalTTC" class="fw-bold">0.00 MAD</td><td></td></tr></tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="<?= APP_URL ?>/?page=documents" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Enregistrer</button>
    </div>
</form>

<script>
const produits = <?= json_encode($produits) ?>;
const existingItems = <?= json_encode($items) ?>;

function addLine(designation = '', produitId = '', qte = 1, prix = 0) {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    let options = '<option value="">Saisie libre</option>';
    produits.forEach(p => {
        options += `<option value="${p.id}" data-prix="${p.prix_unitaire}" ${p.id == produitId ? 'selected' : ''}>${p.designation}</option>`;
    });
    row.innerHTML = `
        <td><input type="text" name="item_designation[]" class="form-control form-control-sm" required value="${designation}"></td>
        <td><select name="item_produit_id[]" class="form-select form-select-sm" onchange="selectProduit(this)">${options}</select></td>
        <td><input type="number" name="item_quantite[]" class="form-control form-control-sm" min="1" value="${qte}" oninput="calcTotal(this)"></td>
        <td><input type="number" name="item_prix[]" class="form-control form-control-sm" step="0.01" value="${parseFloat(prix).toFixed(2)}" oninput="calcTotal(this)"></td>
        <td class="align-middle fw-semibold total-ligne">0.00</td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(row);
    calcTotal(row.querySelector('input[name="item_prix[]"]'));
}

function selectProduit(sel) {
    const row = sel.closest('tr');
    const opt = sel.options[sel.selectedIndex];
    row.querySelector('input[name="item_designation[]"]').value = opt.textContent;
    row.querySelector('input[name="item_prix[]"]').value = parseFloat(opt.dataset.prix || 0).toFixed(2);
    calcTotal(sel);
}

function calcTotal(el) {
    const row = el.closest('tr');
    const qte = parseFloat(row.querySelector('input[name="item_quantite[]"]').value) || 0;
    const prix = parseFloat(row.querySelector('input[name="item_prix[]"]').value) || 0;
    row.querySelector('.total-ligne').textContent = (qte * prix).toFixed(2);
    let grand = 0;
    document.querySelectorAll('.total-ligne').forEach(td => grand += parseFloat(td.textContent) || 0);
    document.getElementById('totalTTC').textContent = grand.toFixed(2) + ' MAD';
}

function removeLine(btn) { btn.closest('tr').remove(); }

existingItems.forEach(item => addLine(item.designation, item.produit_service_id, item.quantite, item.prix_unitaire));
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
