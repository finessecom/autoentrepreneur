<?php
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Helper.php';
require_once __DIR__ . '/src/Auth.php';
require_once __DIR__ . '/src/User.php';
require_once __DIR__ . '/src/Client.php';
require_once __DIR__ . '/src/ProduitService.php';
require_once __DIR__ . '/src/Document.php';
require_once __DIR__ . '/src/Declaration.php';
require_once __DIR__ . '/src/Annonce.php';

set_security_headers();

if (!isset($_SESSION['user_id'])) {
    header('Location: landing.php');
    exit;
}

$page = $_GET['page'] ?? 'dashboard';

// Route mapping
$routes = [
    'dashboard'     => 'pages/dashboard.php',
    'clients'       => 'pages/clients/index.php',
    'clients/create'=> 'pages/clients/create.php',
    'clients/edit'  => 'pages/clients/edit.php',
    'clients/delete'=> 'pages/clients/delete.php',
    'produits'       => 'pages/produits/index.php',
    'produits/create'=> 'pages/produits/create.php',
    'produits/edit'  => 'pages/produits/edit.php',
    'produits/delete'=> 'pages/produits/delete.php',
    'documents'       => 'pages/documents/index.php',
    'documents/create'=> 'pages/documents/create.php',
    'documents/edit'  => 'pages/documents/edit.php',
    'documents/view'  => 'pages/documents/view.php',
    'documents/pdf'   => 'pages/documents/pdf.php',
    'documents/delete'=> 'pages/documents/delete.php',
    'declarations'       => 'pages/declarations/index.php',
    'declarations/create'=> 'pages/declarations/create.php',
    'declarations/edit'  => 'pages/declarations/edit.php',
    'declarations/delete'=> 'pages/declarations/delete.php',
    'profil'        => 'pages/profil/index.php',
    'profil/edit'   => 'pages/profil/edit.php',
    'admin'         => 'pages/admin/dashboard.php',
    'admin/annonces'=> 'pages/admin/annonces.php',
    'annonces/view' => 'pages/annonces/view.php',
];

$routeFile = $routes[$page] ?? $routes['dashboard'];

if (!file_exists(__DIR__ . '/' . $routeFile)) {
    $routeFile = $routes['dashboard'];
}

require __DIR__ . '/' . $routeFile;
