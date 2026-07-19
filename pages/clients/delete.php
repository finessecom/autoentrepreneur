<?php
require_once __DIR__ . '/../../includes/auth_check.php';

$clientModel = new Client();
$id = (int)($_GET['id'] ?? 0);

if ($id && $clientModel->delete($id, Auth::userId())) {
    Helper::setSuccess('Client supprimé.');
} else {
    Helper::setError('Impossible de supprimer ce client.');
}

Helper::redirect(APP_URL . '/?page=clients');
