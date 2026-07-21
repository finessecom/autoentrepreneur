<?php
/**
 * Security Configuration
 * Include this file in all pages that handle user input.
 */

// =====================================================
// Session Security
// =====================================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Regenerate session ID periodically
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// =====================================================
// Input Sanitization
// =====================================================
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function sanitize_sql($data) {
    if (is_array($data)) {
        return array_map('sanitize_sql', $data);
    }
    return trim($data);
}

// =====================================================
// CSRF Token
// =====================================================
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . generate_csrf_token() . '">';
}

function verify_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die('Token CSRF invalide.');
        }
    }
}

// =====================================================
// Rate Limiting (simple file-based)
// =====================================================
function check_rate_limit($action, $max_attempts = 5, $window = 300) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $file = sys_get_temp_dir() . '/rate_' . md5($ip . $action);

    $attempts = 0;
    $first_attempt = time();

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        $attempts = $data['attempts'] ?? 0;
        $first_attempt = $data['first_attempt'] ?? time();

        if (time() - $first_attempt > $window) {
            $attempts = 0;
            $first_attempt = time();
        }
    }

    if ($attempts >= $max_attempts) {
        return false;
    }

    $attempts++;
    file_put_contents($file, json_encode(['attempts' => $attempts, 'first_attempt' => $first_attempt]));
    return true;
}

// =====================================================
// Content Security Policy (for inline styles/scripts)
// =====================================================
function set_security_headers() {
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
