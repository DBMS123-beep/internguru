<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$student_id = get_user_id();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

// --- Sanitize and Validate Input ---
// Add more validation as needed
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$major = trim($_POST['major'] ?? '');
$academic_year = $_POST['academic_year'] ?? ''; // TODO: Validate against allowed values
$gpa = filter_input(INPUT_POST, 'gpa', FILTER_VALIDATE_FLOAT, ["options" => ["min_range"=>0, "max_range"=>4]]);
$profile_summary = trim($_POST['profile_summary'] ?? '');
$linkedin_url = filter_input(INPUT_POST, 'linkedin_url', FILTER_VALIDATE_URL) ?: null;
$github_url = filter_input(INPUT_POST, 'github_url', FILTER_VALIDATE_URL) ?: null;


// --- Update Student Record ---
try {
    $sql = "UPDATE students SET
                first_name = :first_name,
                last_name = :last_name,
                major = :major,
                academic_year = :academic_year,
                gpa = :gpa,
                profile_summary = :profile_summary,
                linkedin_url = :linkedin_url,
                github_url = :github_url,
                updated_at = CURRENT_TIMESTAMP
            WHERE student_id = :student_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':major' => $major,
        ':academic_year' => $academic_year ?: null, // Store null if empty
        ':gpa' => $gpa === false ? null : $gpa, // Store null if validation failed
        ':profile_summary' => $profile_summary,
        ':linkedin_url' => $linkedin_url,
        ':github_url' => $github_url,
        ':student_id' => $student_id
    ]);

    // --- TODO: Update Skills/Courses/Preferences ---
    // This involves deleting old entries and inserting new ones in the join tables
    // based on the submitted checkboxes/multi-selects. Requires careful handling.

    $_SESSION['profile_success'] = 'Profile updated successfully!';

} catch (PDOException $e) {
     error_log("Profile Update Error: " . $e->getMessage());
     $_SESSION['profile_error'] = 'An error occurred while updating the profile. Please try again.';
}

header('Location: profile.php');
exit;
?>
