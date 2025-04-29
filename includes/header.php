<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Ensure session is started
}
require_once __DIR__ . '/functions.php'; // Use absolute path
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? escape($page_title) : 'Internship Recommender'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Add any other CSS/JS headers -->
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-mode' : ''; ?>">
    <header>
        <h1>Internship Recommender</h1>
        <nav>
            <ul>
                <?php if (is_logged_in()): ?>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout (<?php echo escape($_SESSION['email'] ?? ''); ?>)</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
                <li><button id="dark-mode-toggle">Toggle Theme</button></li>
            </ul>
        </nav>
    </header>
    <main>
