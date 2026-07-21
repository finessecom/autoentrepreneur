<?php
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Helper.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/Document.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$annee = (int)($_GET['annee'] ?? 0);
$trimestre = (int)($_GET['trimestre'] ?? 0);

if ($annee < 2020 || $trimestre < 1 || $trimestre > 4) {
    echo json_encode(['error' => 'Paramètres invalides']);
    exit;
}

$docModel = new Document();
$ca = $docModel->getSumByDeclaration(Auth::userId(), $annee, $trimestre);
$factures = $docModel->getByDeclaration(Auth::userId(), $annee, $trimestre);

echo json_encode([
    'ca_commerce' => $ca['commerce'],
    'ca_service' => $ca['service'],
    'factures' => $factures,
]);
