<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Helper.php';
require_once __DIR__ . '/../src/Auth.php';

if (isset($_SESSION['user_id'])) {
    Helper::redirect(APP_URL . '/?page=dashboard');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomComplet = trim($_POST['nom_complet'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';
    $ville = trim($_POST['ville'] ?? '');

    if (empty($nomComplet) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif ($password !== $passwordConfirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } else {
        $auth = new Auth();
        if ($auth->register($nomComplet, $email, $password, $ville)) {
            Helper::redirect(APP_URL . '/?page=dashboard');
        } else {
            $error = Helper::getError() ?? 'Erreur lors de l\'inscription.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .register-card { max-width: 480px; margin: 0 auto; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="register-card">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                    <h3 class="fw-bold">Créer un compte</h3>
                    <p class="text-muted">Rejoignez la plateforme</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= Helper::sanitize($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nom complet *</label>
                        <input type="text" name="nom_complet" class="form-control" required
                               value="<?= Helper::sanitize($_POST['nom_complet'] ?? '') ?>" placeholder="Mohamed Alami">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required
                               value="<?= Helper::sanitize($_POST['email'] ?? '') ?>" placeholder="votre@email.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control"
                               value="<?= Helper::sanitize($_POST['ville'] ?? '') ?>" placeholder="Casablanca">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mot de passe * (min 6 car.)</label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Confirmer le mot de passe *</label>
                        <input type="password" name="password_confirm" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="fas fa-user-plus me-2"></i> S'inscrire
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="login.php" class="text-decoration-none">Déjà un compte ? Se connecter</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
