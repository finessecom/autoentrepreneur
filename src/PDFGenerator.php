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
        $devise = strtoupper($doc['devise'] ?? 'MAD');
        $docLabel = $labels[$doc['type_document']] ?? 'DOCUMENT';
        $ref = $doc['numero'] ?? Helper::generateRef($doc['type_document'], date('Y', strtotime($doc['date_document'])), $doc['id']);

        // Bannière
        $bannerPath = __DIR__ . '/../assets/images/banniere_autoentrepreneur.webp';
        $bannerHtml = '';
        if (file_exists($bannerPath)) {
            $bannerData = base64_encode(file_get_contents($bannerPath));
            $bannerHtml = '<img src="data:image/webp;base64,' . $bannerData . '" style="width:100%;height:auto;display:block;margin-bottom:20px;">';
        }

        // Logo utilisateur
        $logoHtml = '';
        if (!empty($user['logo_pdf_url']) && file_exists(__DIR__ . '/../' . $user['logo_pdf_url'])) {
            $logoData = base64_encode(file_get_contents(__DIR__ . '/../' . $user['logo_pdf_url']));
            $logoExt = pathinfo($user['logo_pdf_url'], PATHINFO_EXTENSION);
            $logoHtml = '<img src="data:image/' . $logoExt . ';base64,' . $logoData . '" style="height:50px;">';
        }

        // Signature
        $signatureHtml = '';
        if (!empty($user['signature_url']) && file_exists(__DIR__ . '/../' . $user['signature_url'])) {
            $sigData = base64_encode(file_get_contents(__DIR__ . '/../' . $user['signature_url']));
            $sigExt = pathinfo($user['signature_url'], PATHINFO_EXTENSION);
            $size = (int)($user['signature_taille'] ?? 100);
            $signatureHtml = '<img src="data:image/' . $sigExt . ';base64,' . $sigData . '" style="height:' . $size . 'px;">';
        }

        // Lignes du tableau
        $rows = '';
        foreach ($items as $item) {
            $detailHtml = !empty($item['detail']) ? '<br><small style="color:#666;">' . nl2br(htmlspecialchars($item['detail'])) . '</small>' : '';
            $rows .= '<tr>
                <td>' . nl2br(htmlspecialchars($item['designation'])) . $detailHtml . '</td>
                <td style="text-align:center">' . $item['quantite'] . '</td>
                <td style="text-align:right">' . number_format($item['prix_unitaire'], 2, '.', ',') . ' ' . $devise . '</td>
                <td style="text-align:right;font-weight:bold">' . number_format($item['total_ligne'], 2, '.', ',') . ' ' . $devise . '</td>
            </tr>';
        }

        $montantLettres = Helper::montantEnLettres($doc['total_ht'], $devise);

        $html = '<!DOCTYPE html>
<html><head><meta charset="UTF-8">
<style>
body { font-family: sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { border: 1px solid #ddd; padding: 8px; }
th { background-color: #2c3e50; color: white; font-weight: bold; }
.title { text-align: center; font-size: 22px; font-weight: bold; color: #2c3e50; margin: 15px 0; text-transform: uppercase; letter-spacing: 2px; }
.card { border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px; margin-bottom: 15px; background: #fafafa; }
.card-title { font-size: 12px; font-weight: bold; color: #2c3e50; text-transform: uppercase; margin-bottom: 8px; padding-bottom: 5px; border-bottom: 2px solid #3498db; }
.info-box { margin: 10px 0; padding: 12px; background: #f0f8ff; border-left: 4px solid #3498db; border-radius: 4px; }
.footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 10px; color: #666; }
.legal { font-size: 9px; color: #999; margin-top: 15px; }
.header-row { display: flex; justify-content: space-between; align-items: flex-start; }
</style></head><body>

' . $bannerHtml . '

<div class="title">' . $docLabel . '</div>

<table style="margin-bottom:15px;">
    <tr>
        <td style="text-align:right;border:none;padding:5px;"><strong>Réf:</strong> ' . $ref . '</td>
        <td style="text-align:right;border:none;padding:5px;"><strong>Date:</strong> ' . date('d/m/Y', strtotime($doc['date_document'])) . '</td>
    </tr>
</table>

<table>
    <tr>
        <td style="width:50%;border:1px solid #e0e0e0;background:#fafafa;vertical-align:top;padding:12px;">
            <div class="card-title">Émetteur</div>
            ' . $logoHtml . '<br>
            <strong>Auto-entrepreneur : ' . htmlspecialchars($user['nom_complet'] ?? '') . '</strong><br>
            ' . htmlspecialchars($user['raison_sociale'] ?? '') . '<br>
            ' . htmlspecialchars($user['ville'] ?? '') . '<br>
            ICE: <strong>' . htmlspecialchars($user['ice'] ?? '-') . '</strong><br>
            IF: <strong>' . htmlspecialchars($user['identifiant_fiscal'] ?? '-') . '</strong><br>
            Banque: <strong>' . htmlspecialchars($user['nom_banque'] ?? '-') . '</strong><br>
            RIB: <strong>' . htmlspecialchars($user['rib'] ?? '-') . '</strong>
        </td>
        <td style="width:50%;border:1px solid #e0e0e0;background:#fafafa;vertical-align:top;padding:12px;">
            <div class="card-title">Client</div>
            <strong>' . htmlspecialchars($doc['nom_client']) . '</strong><br>
            ICE: ' . htmlspecialchars($doc['client_ice'] ?? '-') . '<br>
            Tél: ' . htmlspecialchars($doc['client_telephone'] ?? '-') . '<br>
            Email: ' . htmlspecialchars($doc['client_email'] ?? '-') . '<br>
            ' . htmlspecialchars($doc['client_adresse'] ?? '') . '
        </td>
    </tr>
</table>

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
            <td colspan="3" style="text-align:right;font-weight:bold;background:#f5f5f5;">Total HT :</td>
            <td style="text-align:right;font-weight:bold;font-size:14px;color:#2c3e50;background:#f5f5f5;">' . number_format($doc['total_ht'], 2, '.', ',') . ' ' . $devise . '</td>
        </tr>
    </tfoot>
</table>

<div class="info-box">
    <strong>Facture arrêtée à la somme de :</strong> ' . $montantLettres . '
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

</body></html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
