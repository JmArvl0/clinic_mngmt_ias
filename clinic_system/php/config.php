<?php
// ============================================================
// DATABASE CONFIGURATION
// ============================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'clinic_system');
define('APP_NAME', 'UniClinic');
// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = dirname($_SERVER['SCRIPT_NAME'] ?? '');
// Navigate to root of project
$base     = rtrim(str_replace('/php','',str_replace('/includes','',$script)),'/');
define('APP_URL', $protocol . '://' . $host . $base);

// Session settings
if (session_status() === PHP_SESSION_NONE) {
    if (!headers_sent()) {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        session_start();
    }
}

// Connect to database
function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Auth helpers
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . APP_URL . '/index.php');
        exit();
    }
}

function hasRole(...$roles) {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
}

function requireRole(...$roles) {
    requireLogin();
    if (!hasRole(...$roles)) {
        $_SESSION['error'] = 'Access denied. Insufficient permissions.';
        header('Location: ' . APP_URL . '/dashboard.php');
        exit();
    }
}

function currentUser() {
    return [
        'id'        => $_SESSION['user_id'] ?? null,
        'name'      => $_SESSION['user_name'] ?? '',
        'email'     => $_SESSION['user_email'] ?? '',
        'role'      => $_SESSION['role'] ?? '',
    ];
}

// Sanitize input
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Flash messages
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Generate clearance/incident numbers
function generateNumber($prefix) {
    return $prefix . '-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}
