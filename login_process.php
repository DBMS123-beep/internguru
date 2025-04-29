<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php'; // Ensure session is started

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Email and password are required.';
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id, email, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Password is correct, set session variables
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];

        // Redirect to intended page or dashboard
        $redirect_url = $_SESSION['redirect_url'] ?? 'dashboard.php';
        unset($_SESSION['redirect_url']);
        header('Location: ' . $redirect_url);
        exit;
    } else {
        // Invalid credentials
        $_SESSION['login_error'] = 'Invalid email or password.';
        header('Location: login.php');
        exit;
    }

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage()); // Log error
    $_SESSION['login_error'] = 'An error occurred during login. Please try again.';
    header('Location: login.php');
    exit;
}
?>
