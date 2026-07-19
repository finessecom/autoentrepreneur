<?php
require_once __DIR__ . '/../../includes/auth_check.php';

$prodModel = new ProduitService();
$id = (int)($_GET['id'] ?? 0);

if ($id && $prodModel->delete($id, Auth::userId())) {
    Helper::setSuccess('Produit supprimé.');
} else {
    Helper::setError('Impossible de supprimer ce produit.');
}

Helper::redirect(APP_URL . '/?page=produits');
