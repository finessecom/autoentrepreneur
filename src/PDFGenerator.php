<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFGenerator {
    public static function generate(array $doc, array $items, array $user): string {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);

        $labels = ['devis' => 'DEVIS', 'facture' => 'FACTURE', 'bon_livraison' => 'BON DE LIVRAISON'];
        $docLabel = $labels[$doc['type_document']] ?? 'DOCUMENT';
        $ref = $doc['numero'] ?? Helper::generateRef($doc['type_document'], date('Y', strtotime($doc['date_document'])), $doc['id']);

        $logoHtml = '';
        if (!empty($user['logo_pdf_url']) && file_exists(__DIR__ . '/../' . $user['logo_pdf_url'])) {
            $logoData = base64_encode(file_get_contents(__DIR__ . '/../' . $user['logo_pdf_url']));
            $logoExt = pathinfo($user['logo_pdf_url'], PATHINFO_EXTENSION);
            $logoHtml = '<img src="data:image/' . $logoExt . ';base64,' . $logoData . '" style="height:60px;">';
        }

        $signatureHtml = '';
        if (!empty($user['signature_url']) && file_exists(__DIR__ . '/../' . $user['signature_url'])) {
            $sigData = base64_encode(file_get_contents(__DIR__ . '/../' . $user['signature_url']));
            $sigExt = pathinfo($user['signature_url'], PATHINFO_EXTENSION);
            $size = (int)($user['signature_taille'] ?? 100);
            $signatureHtml = '<img src="data:image/' . $sigExt . ';base64,' . $sigData . '" style="height:' . $size . 'px;">';
        }

        $rows = '';
        foreach ($items as $item) {
            $rows .= '<tr>
                <td>' . htmlspecialchars($item['designation']) . '</td>
                <td style="text-align:center">' . $item['quantite'] . '</td>
                <td style="text-align:right">' . number_format($item['prix_unitaire'], 2, '.', ',') . ' MAD</td>
                <td style="text-align:right;font-weight:bold">' . number_format($item['total_ligne'], 2, '.', ',') . ' MAD</td>
            </tr>';
        }

        $montantLettres = Helper::montantEnLettres($doc['total_ttc']);

        $html = '<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>
body { font-family: sans-serif; font-size: 11px; color: #333; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { border: 1px solid #ddd; padding: 8px; }
th { background-color: #f5f5f5; font-weight: bold; }
.header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
.title { text-align: center; font-size: 18px; font-weight: bold; color: #2c3e50; margin: 15px 0; text-transform: uppercase; }
.info-box { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 3px solid #3498db; }
.footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #666; }
.legal { font-size: 9px; color: #999; margin-top: 15px; }
</style></head><body>

<div class="header">
    <div>' . $logoHtml . '<br>
        <strong>' . htmlspecialchars($user['nom_complet'] ?? '') . '</strong><br>
        ' . htmlspecialchars($user['raison_sociale'] ?? '') . '<br>
        ' . htmlspecialchars($user['ville'] ?? '') . '<br>
        ICE: <strong>' . htmlspecialchars($user['ice'] ?? '-') . '</strong><br>
        IF: <strong>' . htmlspecialchars($user['identifiant_fiscal'] ?? '-') . '</strong>
    </div>
    <div style="text-align:right">
        <div style="font-size:24px;font-weight:bold;color:#2c3e50;">' . $docLabel . '</div>
        <div style="margin-top:5px;">Réf: <strong>' . $ref . '</strong></div>
        <div>Date: <strong>' . date('d/m/Y', strtotime($doc['date_document'])) . '</strong></div>
    </div>
</div>

<div class="info-box">
    <strong>Client :</strong><br>
    ' . htmlspecialchars($doc['nom_client']) . '<br>
    ICE: ' . htmlspecialchars($doc['client_ice'] ?? '-') . '<br>
    ' . htmlspecialchars($doc['client_email'] ?? '') . '<br>
    ' . htmlspecialchars($doc['client_adresse'] ?? '') . '
</div>

<table>
    <thead>
        <tr>
            <th>Désignation</th>
            <th style="text-align:center">Qté</th>
            <th style="text-align:right">Prix unitaire</th>
            <th style="text-align:right">Total HT</th>
        </tr>
    </thead>
    <tbody>' . $rows . '</tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="text-align:right;font-weight:bold;">Total HT :</td>
            <td style="text-align:right;font-weight:bold;font-size:13px;">' . number_format($doc['total_ht'], 2, '.', ',') . ' MAD</td>
        </tr>
        <tr>
            <td colspan="3" style="text-align:right;font-weight:bold;">Total TTC :</td>
            <td style="text-align:right;font-weight:bold;font-size:15px;color:#2c3e50;">' . number_format($doc['total_ttc'], 2, '.', ',') . ' MAD</td>
        </tr>
    </tfoot>
</table>

<div class="info-box">
    <strong>Montant en lettres :</strong> ' . $montantLettres . '
</div>

<div class="footer">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;">
        <div>
            <strong>Mention légale :</strong><br>
            <em>"Art 89 – II – 1° - c, Code Général des Impôts."</em>
            <br>TVA Non Applicable, article 29 III a du CGI.
        </div>
        <div style="text-align:center">
            <strong>Signature :</strong><br>
            ' . $signatureHtml . '
        </div>
    </div>
</div>

<div class="legal">
    Cet document est généré électroniquement. Il est valable sans signature manuscrite.
    Auto-entrepreneur - ' . htmlspecialchars($user['nom_complet'] ?? '') . '
</div>

</body></html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
