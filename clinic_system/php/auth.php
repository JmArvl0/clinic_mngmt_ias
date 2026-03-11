<?php
require_once 'config.php';

// ============================================================
// AUTH HANDLER
// ============================================================

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'logout':
        handleLogout();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../index.php');
        exit();
    }

    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlash('error', 'Email and password are required.');
        header('Location: ../index.php');
        exit();
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT id, full_name, email, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        setFlash('error', 'Invalid email or password.');
        header('Location: ../index.php');
        exit();
    }

    if ($user['status'] === 'inactive') {
        setFlash('error', 'Your account has been deactivated. Contact administrator.');
        header('Location: ../index.php');
        exit();
    }

    // For demo: accept 'password' or verify hash
    $validPassword = ($password === 'password') || password_verify($password, $user['password']);

    if (!$validPassword) {
        setFlash('error', 'Invalid email or password.');
        header('Location: ../index.php');
        exit();
    }

    // Set session
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role']       = $user['role'];

    setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
    header('Location: ../dashboard.php');
    exit();
}

function handleLogout() {
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit();
}
?>
