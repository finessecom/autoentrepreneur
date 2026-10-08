<?php
$pageTitle = 'Simulateur de declaration';

$declModel = new Declaration();
$docModel = new Document();
$year = (int)date('Y');
$currentTrimestre = 4;
$cnssTranches = Declaration::cnssTranches();

// Données par trimestre pour les cards
$allDecls = $declModel->getByUser(Auth::userId(), $year);
$cardData = [];
$invoicesByTrim = [];
for ($t = 1; $t <= 4; $t++) {
    $cardData[$t] = ['ir' => 0, 'cnss' => 0, 'ras' => 0, 'total' => 0, 'penalites' => 0, 'nb_factures' => 0, 'ca_ht' => 0];
    $invoicesByTrim[$t] = [];
}
foreach ($allDecls as $d) {
    $t = $d['trimestre'];
    $cardData[$t]['ir'] += $d['ir_calcule'];
    $cardData[$t]['cnss'] += $d['cnss_calcule'];
    $cardData[$t]['ras'] += $d['retenue_source'];
    $cardData[$t]['total'] += $d['total_a_payer'];
    if ($d['est_declare'] && $d['date_declaration']) {
        $deadline = null;
        if ($t == 1) $deadline = $d['annee'] . '-04-30';
        elseif ($t == 2) $deadline = $d['annee'] . '-07-31';
        elseif ($t == 3) $deadline = $d['annee'] . '-10-31';
        elseif ($t == 4) $deadline = ($d['annee'] + 1) . '-01-31';
        if ($deadline && $d['date_declaration'] > $deadline) {
            $lateDays = (strtotime($d['date_declaration']) - strtotime($deadline)) / 86400;
            $lateMonths = ceil($lateDays / 30);
            $cardData[$t]['penalites'] += $d['total_a_payer'] * 0.10 * $lateMonths;
        }
    }
}
for ($t = 1; $t <= 4; $t++) {
    $invoicesByTrim[$t] = $docModel->getByDeclaration(Auth::userId(), $year, $t);
    $sum = 0;
    foreach ($invoicesByTrim[$t] as $f) {
        $sum += $f['decl_total'] ?? $f['total_ht'];
    }
    $cardData[$t]['ca_ht'] = $sum;
    $cardData[$t]['nb_factures'] = count($invoicesByTrim[$t]);
}
$d = $cardData[$currentTrimestre];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $annee = (int)$_POST['annee'];
    $trimestre = (int)$_POST['trimestre'];
    $caCommerce = (float)$_POST['ca_commerce'];
    $caService = (float)$_POST['ca_service'];
    $cnssTranche = $_POST['cnss_tranche'] ?? 'T0';
    $ir = $declModel->calculerIR($caCommerce, $caService);
    $cnssMontant = Declaration::cnssMontant($cnssTranche);
    $retenueSource = (float)($_POST['retenue_source'] ?? 0);
    $totalAPayerForm = $ir['ir_total'] + $cnssMontant + $retenueSource;

    $id = $declModel->create(Auth::userId(), [
        'annee' => $annee, 'trimestre' => $trimestre,
        'ca_commerce' => $caCommerce, 'ca_service' => $caService,
        'ir_calcule' => $ir['ir_total'], 'cnss_calcule' => $cnssMontant,
        'cnss_tranche' => $cnssTranche, 'retenue_source' => $retenueSource,
        'total_a_payer' => $totalAPayerForm,
        'est_declare' => isset($_POST['est_declare']) ? 1 : 0,
        'est_paye' => isset($_POST['est_paye']) ? 1 : 0,
        'mode_paiement' => $_POST['mode_paiement'] ?? null,
        'date_declaration' => !empty($_POST['date_declaration']) ? $_POST['date_declaration'] : null,
        'ref_declaration' => !empty($_POST['ref_declaration']) ? $_POST['ref_declaration'] : null,
        'ref_paiement' => !empty($_POST['ref_paiement']) ? $_POST['ref_paiement'] : null,
        'date_paiement' => !empty($_POST['date_paiement']) ? $_POST['date_paiement'] : null,
    ]);
    if ($id) {
        Helper::setSuccess('Declaration enregistrée.');
        Helper::redirect(APP_URL . '/?page=declarations&year=' . $annee);
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<!-- BLOC 1 : Sélection -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Année</label>
                <select class="form-select" id="selectAnnee">
                    <?php for ($y = (int)date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Trimestre</label>
                <select class="form-select" id="selectTrimestre">
                    <option value="1">T1 (Jan-Fév-Mar)</option>
                    <option value="2">T2 (Avr-Mai-Jun)</option>
                    <option value="3">T3 (Jul-Aoû-Sep)</option>
                    <option value="4" selected>T4 (Oct-Nov-Déc)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Type d'activité</label>
                <select class="form-select" id="selectActivite">
                    <option value="tous">Tous</option>
                    <option value="service">Services</option>
                    <option value="commerce">Produits / Commerce</option>
                </select>
            </div>
            <div class="col-md-3">
                <a href="<?= APP_URL ?>/?page=declarations/create" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-sync me-1"></i> Actualiser
                </a>
            </div>
        </div>
    </div>
</div>

<!-- BLOC 2 : 3 Cards -->
<div class="row g-4 mb-4">
    <!-- Card Factures -->
    <div class="col-md-4">
        <div class="card h-100 border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Factures</h6>
                <span class="badge bg-light text-primary" id="badgeTrim1">T<?= $currentTrimestre ?></span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Nombre</span>
                    <strong id="cardNbFactures"><?= $d['nb_factures'] ?></strong>
                </div>
                <div id="listFactures" class="mb-2" style="max-height:120px;overflow-y:auto;">
                    <?php if (empty($invoicesByTrim[$currentTrimestre])): ?>
                        <p class="text-muted small text-center mb-0">Aucune facture</p>
                    <?php else: ?>
                        <?php foreach ($invoicesByTrim[$currentTrimestre] as $f): ?>
                            <?php $montant = $f['decl_total'] ?? $f['total_ht']; ?>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted"><?= Helper::sanitize($f['numero']) ?></span>
                                <strong><?= Helper::formatMoney($montant, 'MAD') ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total factures</span>
                    <span class="fw-bold text-primary fs-5" id="cardTotalFactures"><?= Helper::formatMoney($d['ca_ht']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card CNSS -->
    <div class="col-md-4">
        <div class="card h-100 border-success">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-shield-alt me-2"></i> CNSS</h6>
                <span class="badge bg-light text-success" id="badgeTrim2">T<?= $currentTrimestre ?></span>
            </div>
            <div class="card-body">
                <label class="form-label fw-bold small">Tranche :</label>
                <select class="form-select form-select-sm mb-2" id="cnssTrancheCard" onchange="syncTranche(this.value)">
                    <?php foreach ($cnssTranches as $key => $t): ?>
                        <option value="<?= $key ?>"><?= $key ?> — <?= $t['label'] ?> (<?= Helper::formatMoney($t['montant']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Montant</span>
                    <strong class="text-success" id="cardCnssMontant">0,00 MAD</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total CNSS</span>
                    <span class="fw-bold text-success fs-5" id="totalCNSS">0,00 MAD</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card Pénalités et frais -->
    <div class="col-md-4">
        <div class="card h-100 border-danger">
            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Pénalités et frais</h6>
                <span class="badge bg-light text-danger" id="badgeTrim3">T<?= $currentTrimestre ?></span>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label small text-muted">Pénalités (MAD)</label>
                    <input type="number" class="form-control form-control-sm" id="inputPenalites" step="0.01" min="0" value="0" oninput="calcPenalitesFrais()">
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Frais (MAD)</label>
                    <input type="number" class="form-control form-control-sm" id="inputFrais" step="0.01" min="0" value="0" oninput="calcPenalitesFrais()">
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total</span>
                    <span class="fw-bold text-danger fs-5" id="totalPenalites">0,00 MAD</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BLOC 3 : Calcul automatique -->
<div class="card mb-4 border-primary">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="fas fa-calculator me-2"></i> Calcul automatique</h6>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <small class="text-muted d-block">Total factures</small>
                <span class="fw-bold fs-4 text-primary" id="totalFactures">0,00 MAD</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">CNSS</small>
                <span class="fw-bold fs-4 text-success" id="totalCNSSGlobal">0,00 MAD</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Pénalités et frais</small>
                <span class="fw-bold fs-4 text-danger" id="totalPenalitesGlobal">0,00 MAD</span>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">TOTAL À PAYER</small>
                <span class="fw-bold fs-3 text-danger" id="grandTotalGlobal">0,00 MAD</span>
            </div>
        </div>
    </div>
</div>

<!-- BLOC 4 : Déclaration -->
<form method="POST" id="formDeclaration">
    <?= csrf_field() ?>
    <input type="hidden" name="annee" id="formAnnee" value="<?= $year ?>">
    <input type="hidden" name="trimestre" id="formTrimestre" value="<?= $currentTrimestre ?>">
    <input type="hidden" name="ca_commerce" id="formCaCommerce" value="0">
    <input type="hidden" name="ca_service" id="formCaService" value="0">
    <input type="hidden" name="cnss_tranche" id="formCnssTranche" value="T0">
    <input type="hidden" name="retenue_source" id="formRas" value="0">

    <div class="row g-4 mb-4">
        <!-- Card Déclaration -->
        <div class="col-md-6">
            <div class="card h-100 border-primary">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i> Déclaration</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Réf. déclaration</label>
                        <input type="text" name="ref_declaration" class="form-control" placeholder="Ex: DECL-2026-T4">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date déclaration</label>
                        <input type="date" name="date_declaration" class="form-control">
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="est_declare" class="form-check-input" id="estDeclare">
                            <label class="form-check-label" for="estDeclare">Déjà déclaré</label>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">CA Déclaré</span>
                        <span class="fw-bold text-primary fs-5" id="caDeclare">0,00 MAD</span>
                    </div>
                    <input type="hidden" id="caDeclareValue" value="0">
                </div>
            </div>
        </div>

        <!-- Card Paiement -->
        <div class="col-md-6">
            <div class="card h-100 border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-credit-card me-2"></i> Paiement</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Réf. paiement</label>
                        <input type="text" name="ref_paiement" class="form-control" placeholder="Ex: PAI-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date paiement</label>
                        <input type="date" name="date_paiement" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mode de paiement</label>
                        <select name="mode_paiement" class="form-select" id="modePaiement" onchange="calcTotalPaye()">
                            <option value="">-- Non défini --</option>
                            <option value="especes">Espèces</option>
                            <option value="virement">Virement</option>
                            <option value="cashplus">Cashplus</option>
                            <option value="taptapsend">TapTapSend</option>
                            <option value="autre">Autres</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="est_paye" class="form-check-input" id="estPaye">
                            <label class="form-check-label" for="estPaye">Déjà payé</label>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total payé</span>
                        <span class="fw-bold text-success fs-5" id="totalPayeCard">0,00 MAD</span>
                    </div>
                    <small class="text-muted" id="tauxInfo"></small>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-primary btn-lg px-5">
            <i class="fas fa-save me-1"></i> Enregistrer la déclaration
        </button>
    </div>
</form>

<!-- Factures déclarées -->
<div class="card mt-4" id="facturesCard" style="display:none;">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i> Factures déclarées <span id="facturesPeriod"></span></h6>
        <span class="badge bg-success" id="facturesCount">0</span>
    </div>
    <div class="card-body p-0"><div id="facturesContent"></div></div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Avertissement légal :</strong> Cet outil est uniquement indicatif.
        </div>
    </div>
</div>

<script>
const CNSS_MONTANTS = {T0:0,T1:300,T2:390,T3:570,T4:720,T5:1050,T6:1500,T7:2250,T8:3600};
const CARD_DATA = <?= json_encode($cardData) ?>;
const INVOICES_DATA = <?= json_encode($invoicesByTrim) ?>;


function getSumFactures(trim) {
    const inv = INVOICES_DATA[trim] || [];
    let total = 0;
    inv.forEach(f => {
        total += parseFloat(f.decl_total || f.total_ht) || 0;
    });
    return total;
}

function updateCards(trim) {
    const d = CARD_DATA[trim] || CARD_DATA[4];
    const inv = INVOICES_DATA[trim] || [];
    const badge = 'T' + trim;
    const activite = document.getElementById('selectActivite').value;

    document.getElementById('badgeTrim1').textContent = badge;
    document.getElementById('badgeTrim2').textContent = badge;
    document.getElementById('badgeTrim3').textContent = badge;

    document.getElementById('cardNbFactures').textContent = d.nb_factures;

    // Total factures (somme des decl_total)
    const sumFactures = getSumFactures(trim);
    document.getElementById('cardTotalFactures').textContent = sumFactures.toFixed(2) + ' MAD';

    // Total factures dans le calcul automatique
    document.getElementById('totalFactures').textContent = sumFactures.toFixed(2) + ' MAD';

    // Liste des factures
    let html = '';
    if (inv.length === 0) {
        html = '<p class="text-muted small text-center mb-0">Aucune facture</p>';
    } else {
        inv.forEach(f => {
            const montant = parseFloat(f.decl_total || f.total_ht) || 0;
            html += '<div class="d-flex justify-content-between small mb-1">';
            html += '<span class="text-muted">' + f.numero + '</span>';
            html += '<strong>' + montant.toFixed(2) + ' MAD</strong>';
            html += '</div>';
        });
    }
    document.getElementById('listFactures').innerHTML = html;

    // CA Déclaré = factures + CNSS + pénalités/frais
    const cnss = CNSS_MONTANTS[document.getElementById('cnssTrancheCard').value] || 0;
    const penalites = parseFloat(document.getElementById('inputPenalites').value) || 0;
    const frais = parseFloat(document.getElementById('inputFrais').value) || 0;
    const caDeclare = sumFactures + cnss + penalites + frais;
    document.getElementById('caDeclare').textContent = caDeclare.toFixed(2) + ' MAD';
    document.getElementById('caDeclareValue').value = caDeclare;

    // Mettre à jour les champs cachés pour le formulaire
    if (activite === 'commerce') {
        document.getElementById('formCaCommerce').value = sumFactures;
        document.getElementById('formCaService').value = 0;
    } else {
        document.getElementById('formCaCommerce').value = 0;
        document.getElementById('formCaService').value = sumFactures;
    }

    calcTotalPaye();
    syncTranche(document.getElementById('cnssTrancheCard').value);
    document.getElementById('formAnnee').value = document.getElementById('selectAnnee').value;
    document.getElementById('formTrimestre').value = trim;
}

function syncTranche(value) {
    const cnss = CNSS_MONTANTS[value] || 0;
    document.getElementById('cardCnssMontant').textContent = cnss.toFixed(2) + ' MAD';
    document.getElementById('totalCNSS').textContent = cnss.toFixed(2) + ' MAD';
    document.getElementById('totalCNSSGlobal').textContent = cnss.toFixed(2) + ' MAD';
    const trim = parseInt(document.getElementById('selectTrimestre').value);
    calcPenalitesFrais();
    document.getElementById('formCnssTranche').value = value;
}

function calcPenalitesFrais() {
    const penalites = parseFloat(document.getElementById('inputPenalites').value) || 0;
    const frais = parseFloat(document.getElementById('inputFrais').value) || 0;
    const totalPF = penalites + frais;
    document.getElementById('totalPenalites').textContent = totalPF.toFixed(2) + ' MAD';
    document.getElementById('totalPenalitesGlobal').textContent = totalPF.toFixed(2) + ' MAD';

    const trim = parseInt(document.getElementById('selectTrimestre').value);
    const activite = document.getElementById('selectActivite').value;
    const cnss = CNSS_MONTANTS[document.getElementById('cnssTrancheCard').value] || 0;
    const sumFactures = getSumFactures(trim);
    const total = sumFactures + cnss + totalPF;
    document.getElementById('grandTotalGlobal').textContent = total.toFixed(2) + ' MAD';

    // CA Déclaré = factures + CNSS + pénalités/frais
    document.getElementById('caDeclare').textContent = total.toFixed(2) + ' MAD';
    document.getElementById('caDeclareValue').value = total;

    // Mettre à jour les champs cachés pour le formulaire
    if (activite === 'commerce') {
        document.getElementById('formCaCommerce').value = sumFactures;
        document.getElementById('formCaService').value = 0;
    } else {
        document.getElementById('formCaCommerce').value = 0;
        document.getElementById('formCaService').value = sumFactures;
    }
    document.getElementById('formRas').value = 0;

    calcTotalPaye();
}

function calcTotalPaye() {
    const activite = document.getElementById('selectActivite').value;
    const trim = parseInt(document.getElementById('selectTrimestre').value);
    const sumFactures = getSumFactures(trim);
    const taux = activite === 'commerce' ? 0.005 : 0.01;
    const totalPaye = sumFactures * taux;
    document.getElementById('totalPayeCard').textContent = totalPaye.toFixed(2) + ' MAD';
    document.getElementById('tauxInfo').textContent = 'Taux applicable : ' + (taux * 100) + '% (' + (activite === 'commerce' ? 'Produits/Commerce' : 'Services') + ')';
}

document.getElementById('selectTrimestre').addEventListener('change', function() {
    updateCards(parseInt(this.value));
});
document.getElementById('selectAnnee').addEventListener('change', function() {
    document.getElementById('formAnnee').value = this.value;
});
document.getElementById('selectActivite').addEventListener('change', function() {
    updateCards(parseInt(document.getElementById('selectTrimestre').value));
});

updateCards(document.getElementById('selectTrimestre').value);
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
