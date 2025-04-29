<?php
$page_title = "Register";
require_once __DIR__ . '/includes/functions.php'; // Use absolute path

if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = $_SESSION['register_error'] ?? null;
$success = $_SESSION['register_success'] ?? null;
unset($_SESSION['register_error']);
unset($_SESSION['register_success']);

require_once __DIR__ . '/includes/header.php';
?>

<h2>Register</h2>

<?php if ($error): ?><p class="message error"><?php echo escape($error); ?></p><?php endif; ?>
<?php if ($success): ?><p class="message success"><?php echo escape($success); ?></p><?php endif; ?>

<form action="register_process.php" method="post">
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required minlength="8">
    </div>
     <div>
        <label for="password_confirm">Confirm Password:</label>
        <input type="password" id="password_confirm" name="password_confirm" required>
    </div>
    <div>
        <button type="submit">Register</button>
    </div>
</form>
<p>Already have an account? <a href="login.php">Login here</a></p>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
