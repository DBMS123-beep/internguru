<?php
$page_title = "Login";
require_once __DIR__ . '/includes/functions.php'; // Use absolute path

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']); // Clear error after displaying

require_once __DIR__ . '/includes/header.php';
?>

<h2>Login</h2>

<?php if ($error): ?>
    <p class="message error"><?php echo escape($error); ?></p>
<?php endif; ?>

<form action="login_process.php" method="post">
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <button type="submit">Login</button>
    </div>
</form>
<p>Don't have an account? <a href="register.php">Register here</a></p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
