<?php
require_once __DIR__ . '/config/security.php';
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/src/Helper.php';
require_once __DIR__ . '/src/Auth.php';

set_security_headers();

$error = '';
$success = '';
$activeTab = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if (empty($email) || empty($password)) {
                $error = 'Veuillez remplir tous les champs.';
            } else {
                $auth = new Auth();
                if ($auth->login($email, $password)) {
                    header('Location: index.php');
                    exit;
                } else {
                    $error = Helper::getError() ?? 'Identifiants incorrects.';
                }
            }
            $activeTab = 'login';
        } elseif ($_POST['action'] === 'register') {
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
                    header('Location: index.php');
                    exit;
                } else {
                    $error = Helper::getError() ?? 'Erreur lors de l\'inscription.';
                }
            }
            $activeTab = 'register';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>L'Auto-Entrepreneur — Gestion simplifiée</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #1B4D89;
            --accent-dark: #143d6b;
            --accent-warm: #E63946;
            --n-50: #F8F9FA;
            --n-100: #F1F3F5;
            --n-200: #E9ECEF;
            --n-300: #DEE2E6;
            --n-600: #6C757D;
            --n-700: #495057;
            --n-800: #343A40;
            --n-900: #212529;
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            margin: 0;
            padding: 0;
            color: var(--n-800);
            background: #fff;
        }

        /* Header logo + baseline */
        .landing-header {
            background: #fff;
            border-bottom: 1px solid var(--n-200);
            padding: 14px 0;
        }
        .landing-header .container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }
        .landing-logo {
            height: 46px;
            width: auto;
            flex-shrink: 0;
            display: block;
        }
        .landing-tagline {
            font-size: 17px;
            font-weight: 600;
            color: var(--n-900);
            line-height: 1.4;
        }

        /* Hero */
        .hero {
            padding: 80px 0 96px;
        }
        .hero-eyebrow {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--accent);
            margin-bottom: 16px;
        }
        .hero-title {
            font-size: 44px;
            font-weight: 700;
            line-height: 1.15;
            color: var(--n-900);
            letter-spacing: -0.02em;
            margin-bottom: 24px;
        }
        .hero-body {
            font-size: 17px;
            line-height: 1.7;
            color: var(--n-600);
            margin-bottom: 20px;
            max-width: 480px;
        }
        .hero-detail {
            font-size: 14px;
            color: var(--accent);
            font-weight: 500;
            padding: 10px 16px;
            background: rgba(27, 77, 137, 0.06);
            border-left: 3px solid var(--accent);
            border-radius: 0 6px 6px 0;
            display: inline-block;
        }

        /* Auth card */
        .auth-card {
            border: 1px solid var(--n-200);
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
        }
        .auth-tabs {
            display: flex;
            border-bottom: 1px solid var(--n-200);
        }
        .auth-tab {
            flex: 1;
            padding: 14px;
            text-align: center;
            font-size: 14px;
            font-weight: 600;
            color: var(--n-600);
            background: none;
            border: none;
            cursor: pointer;
            transition: all 150ms ease;
            border-bottom: 2px solid transparent;
        }
        .auth-tab.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
        }
        .auth-tab:hover:not(.active) {
            color: var(--n-800);
            background: var(--n-50);
        }
        .auth-body {
            padding: 28px;
        }
        .auth-body .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--n-700);
            margin-bottom: 6px;
        }
        .auth-body .form-control {
            border-radius: 8px;
            border-color: var(--n-300);
            padding: 10px 14px;
            font-size: 14px;
        }
        .auth-body .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(27, 77, 137, 0.1);
        }
        .btn-primary-custom {
            background: var(--accent);
            border: none;
            border-radius: 8px;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            width: 100%;
            transition: background 150ms ease;
        }
        .btn-primary-custom:hover {
            background: var(--accent-dark);
        }
        .auth-panel { display: none; }
        .auth-panel.active { display: block; }

        /* Features section */
        .features {
            padding: 96px 0;
            background: var(--n-50);
            text-align: center;
        }
        .features-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--n-900);
            letter-spacing: -0.02em;
            margin-bottom: 12px;
        }
        .features-subtitle {
            font-size: 17px;
            color: var(--n-600);
            margin-bottom: 56px;
            max-width: 540px;
            margin-left: auto;
            margin-right: auto;
        }
        .feature-card {
            background: #fff;
            border: 1px solid var(--n-200);
            border-radius: 10px;
            padding: 36px 28px;
            text-align: left;
            transition: border-color 150ms ease, box-shadow 150ms ease;
        }
        .feature-card:hover {
            border-color: var(--accent);
            box-shadow: 0 4px 20px rgba(27, 77, 137, 0.08);
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: rgba(27, 77, 137, 0.08);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .feature-step {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--accent);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .feature-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--accent);
            margin-bottom: 8px;
        }
        .feature-heading {
            font-size: 18px;
            font-weight: 700;
            color: var(--n-900);
            margin-bottom: 10px;
            letter-spacing: -0.01em;
        }
        .feature-text {
            font-size: 14px;
            line-height: 1.65;
            color: var(--n-600);
            margin-bottom: 0;
        }

        /* Footer */
        .landing-footer {
            padding: 32px 0;
            text-align: center;
            font-size: 13px;
            color: var(--n-600);
            border-top: 1px solid var(--n-200);
        }
        .landing-footer a {
            color: var(--accent);
            text-decoration: none;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .hero-title { font-size: 32px; }
            .hero { padding: 48px 0 64px; }
            .features { padding: 64px 0; }
            .features-title { font-size: 26px; }
        }
        @media (max-width: 576px) {
            .landing-logo { height: 38px; }
            .landing-tagline { font-size: 14px; text-align: center; }
            .landing-header .container { gap: 12px; flex-wrap: wrap; justify-content: center; }
        }
    </style>
</head>
<body>

<!-- Header : logo + baseline -->
<header class="landing-header">
    <div class="container">
        <img src="<?= APP_URL ?>/assets/images/logo-auto-entrepreneur-maroc.png" alt="Logo Auto-Entrepreneur Maroc" class="landing-logo">
        <span class="landing-tagline">Une seule application pour gérer vos documents, vos déclarations et vos clients</span>
    </div>
</header>

<!-- Hero -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-eyebrow">Gestion complète pour auto-entrepreneurs</div>
                <h1 class="hero-title">Vos factures, vos déclarations,<br>votre comptabilité. Simplifié.</h1>
                <p class="hero-body">
                    Créez des devis et factures en quelques clics. Suivez vos obligations fiscales trimestriellement. Gérez vos clients et vos produits. Concentrez-vous sur votre activité.
                </p>
                <div class="hero-detail">
                    <i class="fas fa-shield-alt me-2"></i>
                    Conforme à la réglementation marocaine — IR, CNSS, retenue à la source
                </div>
            </div>
            <div class="col-lg-5 offset-lg-1">
                <div class="auth-card">
                    <div class="auth-tabs">
                        <button class="auth-tab <?= $activeTab === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">Connexion</button>
                        <button class="auth-tab <?= $activeTab === 'register' ? 'active' : '' ?>" onclick="switchTab('register')">Inscription</button>
                    </div>
                    <div class="auth-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger py-2 px-3 mb-3" style="font-size:13px;border-radius:8px;">
                                <?= Helper::sanitize($error) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Login -->
                        <div id="panel-login" class="auth-panel <?= $activeTab === 'login' ? 'active' : '' ?>">
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="login">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required placeholder="votre@email.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mot de passe</label>
                                    <input type="password" name="password" class="form-control" required placeholder="Votre mot de passe">
                                </div>
                                <button type="submit" class="btn-primary-custom">
                                    <i class="fas fa-sign-in-alt me-2"></i> Se connecter
                                </button>
                            </form>
                        </div>

                        <!-- Register -->
                        <div id="panel-register" class="auth-panel <?= $activeTab === 'register' ? 'active' : '' ?>">
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="register">
                                <div class="mb-3">
                                    <label class="form-label">Nom complet *</label>
                                    <input type="text" name="nom_complet" class="form-control" required placeholder="Mohamed Alami">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="email" class="form-control" required placeholder="votre@email.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Ville</label>
                                    <input type="text" name="ville" class="form-control" placeholder="Casablanca">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mot de passe * <small class="text-muted">(min 6 car.)</small></label>
                                    <input type="password" name="password" class="form-control" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confirmer le mot de passe *</label>
                                    <input type="password" name="password_confirm" class="form-control" required>
                                </div>
                                <button type="submit" class="btn-primary-custom">
                                    <i class="fas fa-user-plus me-2"></i> Créer mon compte
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section class="features">
    <div class="container">
        <div class="features-title">Prêt en 4 étapes, sans compétences comptables</div>
        <p class="features-subtitle">Une utilisation d'une simplicité absolue : créez votre compte, ajoutez vos clients et commencez à facturer en quelques minutes.</p>

        <div class="row g-4">
            <!-- Étape 1 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card h-100">
                    <div class="feature-step">1</div>
                    <div class="feature-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="feature-label">Étape 1</div>
                    <h3 class="feature-heading">Créer votre compte</h3>
                    <p class="feature-text">
                        Inscription en 30 secondes : nom, email, ville. Aucune carte bancaire, aucun engagement.
                    </p>
                </div>
            </div>

            <!-- Étape 2 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card h-100">
                    <div class="feature-step">2</div>
                    <div class="feature-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="feature-label">Étape 2</div>
                    <h3 class="feature-heading">Ajouter vos clients</h3>
                    <p class="feature-text">
                        Créez vos clients et votre catalogue de produits / services en quelques clics, avec ICE et échéances.
                    </p>
                </div>
            </div>

            <!-- Étape 3 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card h-100">
                    <div class="feature-step">3</div>
                    <div class="feature-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div class="feature-label">Étape 3</div>
                    <h3 class="feature-heading">Devis & factures</h3>
                    <p class="feature-text">
                        Choisissez un client, ajoutez vos lignes : numérotation automatique et PDF prêts à envoyer.
                    </p>
                </div>
            </div>

            <!-- Étape 4 -->
            <div class="col-md-6 col-lg-3">
                <div class="feature-card h-100">
                    <div class="feature-step">4</div>
                    <div class="feature-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="feature-label">Étape 4</div>
                    <h3 class="feature-heading">Déclarer votre CA</h3>
                    <p class="feature-text">
                        Simulateur IR / CNSS, déclaration trimestrielle et suivi de vos paiements en toute sérénité.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer class="landing-footer">
    <div class="container">
        L'Auto-Entrepreneur — Gestion simplifiée pour auto-entrepreneurs au Maroc
    </div>
</footer>

<script>
function switchTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-panel').forEach(p => p.classList.remove('active'));

    if (tab === 'login') {
        document.querySelectorAll('.auth-tab')[0].classList.add('active');
        document.getElementById('panel-login').classList.add('active');
    } else {
        document.querySelectorAll('.auth-tab')[1].classList.add('active');
        document.getElementById('panel-register').classList.add('active');
    }
}
</script>

</body>
</html>
