<?php
require_once __DIR__ . '/../../includes/auth_check.php';

$declModel = new Declaration();
$id = (int)($_GET['id'] ?? 0);

if ($id && $declModel->delete($id, Auth::userId())) {
    Helper::setSuccess('Declaration supprimée.');
} else {
    Helper::setError('Impossible de supprimer cette declaration.');
}

Helper::redirect(APP_URL . '/?page=declarations');
