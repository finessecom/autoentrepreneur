<?php
$pageTitle = 'Nouveau document';

$clientModel = new Client();
$prodModel = new ProduitService();
$docModel = new Document();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $data = [
        'client_id'     => (int)$_POST['client_id'],
        'type_document' => $_POST['type_document'],
        'date_document' => $_POST['date_document'],
        'statut'        => $_POST['statut'] ?? 'brouillon',
        'devise'        => $_POST['devise'] ?? 'MAD',
    ];

    $items = [];
    if (!empty($_POST['item_designation'])) {
        foreach ($_POST['item_designation'] as $i => $designation) {
            if (empty(trim($designation))) continue;
            $items[] = [
                'produit_service_id' => !empty($_POST['item_produit_id'][$i]) ? (int)$_POST['item_produit_id'][$i] : null,
                'designation'        => trim($designation),
                'detail'             => trim($_POST['item_detail'][$i] ?? ''),
                'quantite'           => (int)$_POST['item_quantite'][$i],
                'prix_unitaire'      => (float)$_POST['item_prix'][$i],
            ];
        }
    }

    if (empty($items)) {
        Helper::setError('Ajoutez au moins une ligne.');
    } else {
        $id = $docModel->create(Auth::userId(), $data, $items);
        if ($id) {
            Helper::setSuccess('Document créé avec succès.');
            Helper::redirect(APP_URL . '/?page=documents/view&id=' . $id);
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

$clients = $clientModel->getByUser(Auth::userId());
$produits = $prodModel->getByUser(Auth::userId());
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-file-circle-plus me-2"></i> Nouveau document</h4>
    <a href="<?= APP_URL ?>/?page=documents" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<form method="POST" id="docForm">
    <?= csrf_field() ?>
    <div class="card mb-4">
        <div class="card-header bg-white"><h6 class="mb-0">Informations générales</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Type de document *</label>
                    <select name="type_document" class="form-select" required>
                        <option value="devis">Devis</option>
                        <option value="facture">Facture</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date *</label>
                    <input type="date" name="date_document" class="form-control" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="brouillon">Brouillon</option>
                        <option value="envoye">Envoyé</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Client *</label>
                    <select name="client_id" class="form-select" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= Helper::sanitize($c['nom_client']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Devise *</label>
                    <select name="devise" class="form-select" required id="docDevise">
                        <option value="MAD">MAD - Dirham</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="USD">USD - Dollar</option>
                        <option value="GBP">GBP - Livre</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Lignes d'articles</h6>
            <button type="button" class="btn btn-sm btn-outline-success" id="addLine">
                <i class="fas fa-plus me-1"></i> Ajouter une ligne
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="itemsTable">
                    <thead>
                        <tr>
                            <th style="width:20%">Désignation</th>
                            <th style="width:15%">Produit/Service</th>
                            <th style="width:25%">Détail</th>
                            <th style="width:8%">Qté</th>
                            <th style="width:12%">Prix unitaire</th>
                            <th style="width:12%">Total ligne</th>
                            <th style="width:8%"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                    </tbody>
                    <tfoot>
                        <tr class="table-active">
                            <td colspan="4" class="text-end fw-bold">Total HT :</td>
                            <td class="fw-bold" id="totalHT">0.00 MAD</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="<?= APP_URL ?>/?page=documents" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Enregistrer
        </button>
    </div>
</form>

<script>
const produits = <?= json_encode($produits) ?>;

function addLine(designation = '', produitId = '', detail = '', qte = 1, prix = 0) {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    let options = '<option value="">Saisie libre</option>';
    produits.forEach(p => {
        const selected = p.id == produitId ? 'selected' : '';
        options += `<option value="${p.id}" data-prix="${p.prix_unitaire}" data-detail="${(p.detail || '').replace(/"/g, '&quot;')}" ${selected}>${p.designation}</option>`;
    });

    row.innerHTML = `
        <td><textarea name="item_designation[]" class="form-control form-control-sm" rows="2" required placeholder="Désignation...">${designation}</textarea></td>
        <td><select name="item_produit_id[]" class="form-select form-select-sm" onchange="selectProduit(this)">${options}</select></td>
        <td><textarea name="item_detail[]" class="form-control form-control-sm" rows="1" placeholder="Détail...">${detail}</textarea></td>
        <td><input type="number" name="item_quantite[]" class="form-control form-control-sm" min="1" value="${qte}" onchange="calcTotal(this)" oninput="calcTotal(this)"></td>
        <td><input type="number" name="item_prix[]" class="form-control form-control-sm" step="0.01" min="0" value="${prix}" onchange="calcTotal(this)" oninput="calcTotal(this)"></td>
        <td class="align-middle fw-semibold total-ligne">0.00</td>
        <td class="align-middle"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeLine(this)"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(row);
    calcTotal(row.querySelector('input[name="item_prix[]"]'));
}

function selectProduit(sel) {
    const row = sel.closest('tr');
    const opt = sel.options[sel.selectedIndex];
    const prix = opt.dataset.prix || 0;
    const detail = opt.dataset.detail || '';
    row.querySelector('textarea[name="item_designation[]"]').value = opt.textContent;
    row.querySelector('input[name="item_prix[]"]').value = parseFloat(prix).toFixed(2);
    row.querySelector('textarea[name="item_detail[]"]').value = detail;
    calcTotal(sel);
}

function calcTotal(el) {
    const row = el.closest('tr');
    const qte = parseFloat(row.querySelector('input[name="item_quantite[]"]').value) || 0;
    const prix = parseFloat(row.querySelector('input[name="item_prix[]"]').value) || 0;
    const total = qte * prix;
    row.querySelector('.total-ligne').textContent = total.toFixed(2);

    let grand = 0;
    document.querySelectorAll('.total-ligne').forEach(td => {
        grand += parseFloat(td.textContent) || 0;
    });
    const devise = document.getElementById('docDevise').value;
    document.getElementById('totalHT').textContent = grand.toFixed(2) + ' ' + devise;
}

document.getElementById('docDevise').addEventListener('change', function() {
    const firstRow = document.querySelector('#itemsBody tr');
    if (firstRow) {
        calcTotal(firstRow.querySelector('input[name="item_prix[]"]'));
    } else {
        const devise = this.value;
        document.getElementById('totalHT').textContent = '0.00 ' + devise;
    }
});

function removeLine(btn) {
    btn.closest('tr').remove();
    calcTotal(document.getElementById('totalHT'));
}

document.getElementById('addLine').addEventListener('click', () => addLine());
addLine();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
