<?php
// Database Configuration (Use Environment Variables in production!)
$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_name = getenv('DB_NAME') ?: 'internship_db';
$db_user = getenv('DB_USER') ?: 'root'; // CHANGE DEFAULT USER
$db_pass = getenv('DB_PASSWORD') ?: ''; // CHANGE DEFAULT PASSWORD
$db_charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements
];

try {
     $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
     // In production, log this error and show a generic message
     // error_log("Database Connection Error: " . $e->getMessage());
     // die("Database connection failed. Please try again later."); // Simple user message
     throw new \PDOException($e->getMessage(), (int)$e->getCode()); // Re-throw for detailed debug (dev only)
}
?>
