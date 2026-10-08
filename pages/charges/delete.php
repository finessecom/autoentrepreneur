<?php
require_once __DIR__ . '/../../includes/header.php';

$chargeModel = new Charge();
$id = (int)($_GET['id'] ?? 0);

if ($chargeModel->delete($id, Auth::userId())) {
    Helper::setSuccess('Charge supprimée.');
} else {
    Helper::setError('Erreur lors de la suppression.');
}

Helper::redirect(APP_URL . '/?page=charges');
