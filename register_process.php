<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php'; // Ensure session is started

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// --- Basic Validation ---
if (empty($email) || empty($password) || empty($password_confirm)) {
    $_SESSION['register_error'] = 'All fields are required.';
    header('Location: register.php');
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
     $_SESSION['register_error'] = 'Invalid email format.';
    header('Location: register.php');
    exit;
}
if (strlen($password) < 8) {
     $_SESSION['register_error'] = 'Password must be at least 8 characters long.';
    header('Location: register.php');
    exit;
}
if ($password !== $password_confirm) {
     $_SESSION['register_error'] = 'Passwords do not match.';
    header('Location: register.php');
    exit;
}

// --- Check if email already exists ---
try {
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['register_error'] = 'Email address already registered.';
        header('Location: register.php');
        exit;
    }

    // --- Hash Password ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Use default algorithm

    // --- Insert User ---
    $pdo->beginTransaction(); // Start transaction

    $sql_user = "INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)";
    $stmt_user = $pdo->prepare($sql_user);
    $stmt_user->execute([$email, $hashed_password, 'student']);
    $user_id = $pdo->lastInsertId();

    // --- Create corresponding student record ---
    $sql_student = "INSERT INTO students (student_id) VALUES (?)";
    $stmt_student = $pdo->prepare($sql_student);
    $stmt_student->execute([$user_id]);

    $pdo->commit(); // Commit transaction

    $_SESSION['register_success'] = 'Registration successful! You can now log in.';
    header('Location: login.php'); // Redirect to login page
    exit;

} catch (PDOException $e) {
    $pdo->rollBack(); // Roll back changes on error
    error_log("Registration Error: " . $e->getMessage()); // Log the error
    $_SESSION['register_error'] = 'An error occurred during registration. Please try again. Code: ' . $e->getCode();
     if ($e->getCode() == '23000') { // Integrity constraint violation (e.g., duplicate email - race condition)
         $_SESSION['register_error'] = 'Email address already registered.';
     }
    header('Location: register.php');
    exit;
}
?>
