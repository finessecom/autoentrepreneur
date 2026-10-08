<?php
class Auth {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register(string $nomComplet, string $email, string $password, string $ville = '', string $prefixeDevis = 'DEV', string $prefixeFacture = 'FAC', string $prefixeLivraison = 'BL'): bool {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            Helper::setError('Cet email est déjà utilisé.');
            return false;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $nomAffichage = explode(' ', $nomComplet)[0];

        $stmt = $this->db->prepare(
            'INSERT INTO users (nom_complet, nom_affichage, email, password, ville, prefixe_devis, prefixe_facture, prefixe_livraison) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$nomComplet, $nomAffichage, $email, $hash, $ville, $prefixeDevis, $prefixeFacture, $prefixeLivraison]);

        $userId = (int) $this->db->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'user';
        session_regenerate_id(true);

        Helper::setSuccess('Compte créé avec succès ! Bienvenue.');
        return true;
    }

    public function login(string $email, string $password): bool {
        $stmt = $this->db->prepare('SELECT id, role, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            Helper::setError('Email ou mot de passe incorrect.');
            return false;
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['role'] = $user['role'];
        session_regenerate_id(true);

        Helper::setSuccess('Connexion réussie.');
        return true;
    }

    public function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
        Helper::redirect(APP_URL . '/auth/login.php');
    }

    public static function check(): void {
        if (!isset($_SESSION['user_id'])) {
            Helper::redirect(APP_URL . '/auth/login.php');
        }
    }

    public static function isAdmin(): bool {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function userId(): int {
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    public static function requireAdmin(): void {
        self::check();
        if (!self::isAdmin()) {
            Helper::setError('Accès réservé aux administrateurs.');
            Helper::redirect(APP_URL . '/?page=dashboard');
        }
    }
}
