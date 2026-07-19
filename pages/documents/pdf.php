<?php
session_start();
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Helper.php';
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/User.php';
require_once __DIR__ . '/../../src/Document.php';
require_once __DIR__ . '/../../src/PDFGenerator.php';

Auth::check();

$docModel = new Document();
$userModel = new User();
$id = (int)($_GET['id'] ?? 0);

$doc = $docModel->getById($id, Auth::userId());
if (!$doc) {
    Helper::setError('Document introuvable.');
    Helper::redirect(APP_URL . '/?page=documents');
}

$items = $docModel->getItems($id);
$user = $userModel->getById(Auth::userId());

$pdfContent = PDFGenerator::generate($doc, $items, $user);

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $doc['type_document'] . '_' . $doc['id'] . '.pdf"');
header('Content-Length: ' . strlen($pdfContent));
echo $pdfContent;
exit;
