<?php
$pageTitle = 'Nouvelle declaration';
require_once __DIR__ . '/../../includes/header.php';

$declModel = new Declaration();

$result = null;
$caCommerce = 0;
$caService = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $annee = (int)$_POST['annee'];
    $trimestre = (int)$_POST['trimestre'];
    $caCommerce = (float)$_POST['ca_commerce'];
    $caService = (float)$_POST['ca_service'];

    $ir = $declModel->calculerIR($caCommerce, $caService);
    $caTotal = $caCommerce + $caService;
    $cnss = $declModel->calculerCNSS($caTotal);

    $retenueSource = 0;
    if (!empty($_POST['retenue_source'])) {
        $retenueSource = (float)$_POST['retenue_source'];
    }

    $totalAPayer = $ir['ir_total'] + $cnss['cnss_trimestre'] + $retenueSource;

    $data = [
        'annee'          => $annee,
        'trimestre'      => $trimestre,
        'ca_commerce'    => $caCommerce,
        'ca_service'     => $caService,
        'ir_calcule'     => $ir['ir_total'],
        'cnss_calcule'   => $cnss['cnss_trimestre'],
        'retenue_source' => $retenueSource,
        'total_a_payer'  => $totalAPayer,
        'est_declare'    => isset($_POST['est_declare']) ? 1 : 0,
        'mode_paiement'  => $_POST['mode_paiement'] ?? null,
        'date_declaration' => !empty($_POST['date_declaration']) ? $_POST['date_declaration'] : null,
    ];

    $id = $declModel->create(Auth::userId(), $data);
    if ($id) {
        Helper::setSuccess('Declaration enregistrée.');
        Helper::redirect(APP_URL . '/?page=declarations&year=' . $annee);
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-calculator me-2"></i> Simulateur de declaration</h4>
    <a href="<?= APP_URL ?>/?page=declarations" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour
    </a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Rappel :</strong> IR Commerce = 0,5% (plafond 500 000 DH) | IR Service = 1% (plafond 200 000 DH) | CNSS calculée par tranche
</div>

<form method="POST">
    <div class="row g-4">
        <!-- Saisie CA -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white"><h6 class="mb-0">Revenus du trimestre</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Année</label>
                            <select name="annee" class="form-select" required>
                                <?php for ($y = (int)date('Y'); $y >= 2020; $y--): ?>
                                    <option value="<?= $y ?>" <?= $y === (int)date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trimestre</label>
                            <select name="trimestre" class="form-select" required>
                                <option value="1">T1 (Jan-Fév-Mar)</option>
                                <option value="2">T2 (Avr-Mai-Jun)</option>
                                <option value="3">T3 (Jul-Aoû-Sep)</option>
                                <option value="4" selected>T4 (Oct-Nov-Déc)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CA Commerce (MAD)</label>
                            <input type="number" name="ca_commerce" class="form-control" step="0.01" min="0"
                                   value="<?= $caCommerce ?>" oninput="calculer()">
                            <small class="text-muted">Taux : 0,5% | Plafond : 500 000 DH</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CA Service (MAD)</label>
                            <input type="number" name="ca_service" class="form-control" step="0.01" min="0"
                                   value="<?= $caService ?>" oninput="calculer()">
                            <small class="text-muted">Taux : 1% | Plafond : 200 000 DH</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Retenue à la source (MAD)</label>
                            <input type="number" name="retenue_source" class="form-control" step="0.01" min="0"
                                   value="0" oninput="calculer()">
                            <small class="text-muted">30% si CA > 80 000 DH avec un même client (Art. 73 CGI)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résultats calculs -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white"><h6 class="mb-0">Calcul automatique</h6></div>
                <div class="card-body">
                    <table class="table table-bordered mb-3" id="resultTable">
                        <tbody>
                            <tr><td>IR Commerce (0,5%)</td><td class="text-end" id="irCommerce">0.00 MAD</td></tr>
                            <tr><td>IR Service (1%)</td><td class="text-end" id="irService">0.00 MAD</td></tr>
                            <tr class="table-active"><td><strong>IR Total</strong></td><td class="text-end" id="irTotal"><strong>0.00 MAD</strong></td></tr>
                            <tr><td>CNSS (par tranche)</td><td class="text-end" id="cnssTotal">0.00 MAD</td></tr>
                            <tr><td>Retenue à la source</td><td class="text-end" id="rasTotal">0.00 MAD</td></tr>
                            <tr class="table-danger"><td><strong>TOTAL A PAYER</strong></td><td class="text-end" id="grandTotal"><strong>0.00 MAD</strong></td></tr>
                        </tbody>
                    </table>

                    <div id="alertesCalcul"></div>
                </div>
            </div>
        </div>

        <!-- Divers -->
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Date de declaration</label>
                            <input type="date" name="date_declaration" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Mode de paiement</label>
                            <select name="mode_paiement" class="form-select">
                                <option value="">-- Non défini --</option>
                                <option value="especes">Espèces</option>
                                <option value="virement">Virement</option>
                                <option value="cheque">Chèque</option>
                                <option value="cnss">CNSS</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-check mt-4">
                                <input type="checkbox" name="est_declare" class="form-check-input" id="estDeclare">
                                <label class="form-check-label" for="estDeclare">Déjà déclaré</label>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card mt-4">
    <div class="card-body">
        <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Avertissement légal :</strong> Cet outil est uniquement indicatif. La responsabilité de déclaration auprès de la DGI incombe entièrement à l'utilisateur.
        </div>
    </div>
</div>

<script>
const TRANCHES = [
    {min:0, max:2000, taux:0},
    {min:2000, max:3000, taux:0.0448},
    {min:3000, max:4000, taux:0.0448},
    {min:4000, max:5000, taux:0.0448},
    {min:5000, max:6000, taux:0.0448},
    {min:6000, max:8000, taux:0.0448},
    {min:8000, max:10000, taux:0.0448},
    {min:10000, max:99999999, taux:0.0448},
];

function calculer() {
    const caC = parseFloat(document.querySelector('[name="ca_commerce"]').value) || 0;
    const caS = parseFloat(document.querySelector('[name="ca_service"]').value) || 0;
    const ras = parseFloat(document.querySelector('[name="retenue_source"]').value) || 0;

    const irC = Math.min(caC, 500000) * 0.005;
    const irS = Math.min(caS, 200000) * 0.01;
    const irTotal = irC + irS;

    const caTotal = caC + caS;
    const cnssTrim = caTotal / 4;
    let cnss = 0;
    for (const t of TRANCHES) {
        if (cnssTrim <= t.min) break;
        const base = Math.min(cnssTrim, t.max) - t.min;
        cnss += base * t.taux;
    }

    const total = irTotal + cnss + ras;

    document.getElementById('irCommerce').textContent = irC.toFixed(2) + ' MAD';
    document.getElementById('irService').textContent = irS.toFixed(2) + ' MAD';
    document.getElementById('irTotal').innerHTML = '<strong>' + irTotal.toFixed(2) + ' MAD</strong>';
    document.getElementById('cnssTotal').textContent = cnss.toFixed(2) + ' MAD';
    document.getElementById('rasTotal').textContent = ras.toFixed(2) + ' MAD';
    document.getElementById('grandTotal').innerHTML = '<strong>' + total.toFixed(2) + ' MAD</strong>';

    let alertes = '';
    if (caC > 500000) alertes += '<div class="alert alert-danger py-1 mb-1"><small>CA Commerce dépasse le plafond de 500 000 DH!</small></div>';
    if (caS > 200000) alertes += '<div class="alert alert-danger py-1 mb-1"><small>CA Service dépasse le plafond de 200 000 DH!</small></div>';
    if (caTotal > 80000) alertes += '<div class="alert alert-warning py-1 mb-1"><small>Attention : Art. 73 CGI - Retenue à la source de 30% possible si CA > 80 000 DH avec un même client.</small></div>';
    document.getElementById('alertesCalcul').innerHTML = alertes;
}

calculer();
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
