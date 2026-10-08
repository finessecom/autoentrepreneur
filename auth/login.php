<?php
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Helper.php';
require_once __DIR__ . '/../src/Auth.php';

set_security_headers();

if (isset($_SESSION['user_id'])) {
    Helper::redirect(APP_URL . '/?page=dashboard');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    if (!check_rate_limit('login', 5, 300)) {
        $error = 'Trop de tentatives. Réessayez dans 5 minutes.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = 'Veuillez remplir tous les champs.';
        } else {
            $auth = new Auth();
            if ($auth->login($email, $password)) {
                Helper::redirect(APP_URL . '/?page=dashboard');
            } else {
                $error = Helper::getError() ?? 'Identifiants incorrects.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .login-card { max-width: 420px; margin: 0 auto; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="login-card">
        <div class="card shadow-lg border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                    <h3 class="fw-bold">Auto-Entrepreneur</h3>
                    <p class="text-muted">Connectez-vous à votre espace</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= Helper::sanitize($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required
                                   value="<?= Helper::sanitize($_POST['email'] ?? '') ?>" placeholder="votre@email.com">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required placeholder="Votre mot de passe">
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                    </button>
                </form>

                <div class="text-center mt-4">
                    <a href="register.php" class="text-decoration-none">Créer un compte</a>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.querySelectorAll('.toggle-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.previousElementSibling;
            var icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });
    </script>
</body>
</html>
