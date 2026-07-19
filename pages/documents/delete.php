<?php
require_once __DIR__ . '/../../includes/auth_check.php';

$docModel = new Document();
$id = (int)($_GET['id'] ?? 0);

if ($id && $docModel->delete($id, Auth::userId())) {
    Helper::setSuccess('Document supprimé.');
} else {
    Helper::setError('Impossible de supprimer ce document.');
}

Helper::redirect(APP_URL . '/?page=documents');
