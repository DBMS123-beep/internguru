<?php
$page_title = "My Profile";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$student_id = get_user_id();

// Fetch student data
try {
    $student = get_student_profile($pdo, $student_id);
    if (!$student) {
        die("Error: Student profile not found."); // Or handle more gracefully
    }

    // Fetch current skills and courses (You'll need functions for these in functions.php)
    // $current_skills = get_student_skills($pdo, $student_id); // Returns array of skill IDs or objects
    // $current_courses = get_student_courses($pdo, $student_id);

    // Fetch all available skills/courses for dropdowns/checkboxes
    // $all_skills = $pdo->query("SELECT skill_id, skill_name FROM skills ORDER BY skill_name")->fetchAll();
    // $all_courses = $pdo->query("SELECT course_id, course_code, course_name FROM courses ORDER BY course_code")->fetchAll();

} catch (PDOException $e) {
     error_log("Profile fetch error: " . $e->getMessage());
     die("Error loading profile data.");
}

$error = $_SESSION['profile_error'] ?? null;
$success = $_SESSION['profile_success'] ?? null;
unset($_SESSION['profile_error']);
unset($_SESSION['profile_success']);

require_once __DIR__ . '/includes/header.php';
?>

<h2>My Profile</h2>

<?php if ($error): ?><p class="message error"><?php echo escape($error); ?></p><?php endif; ?>
<?php if ($success): ?><p class="message success"><?php echo escape($success); ?></p><?php endif; ?>

<form action="profile_update.php" method="post">
    <fieldset>
        <legend>Basic Information</legend>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo escape($student['email']); ?>" readonly disabled>
            <small>Email cannot be changed here.</small>
        </div>
        <div>
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo escape($student['first_name'] ?? ''); ?>">
        </div>
         <div>
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo escape($student['last_name'] ?? ''); ?>">
        </div>
    </fieldset>

    <fieldset>
         <legend>Academic Information</legend>
         <div>
            <label for="major">Major:</label>
            <input type="text" id="major" name="major" value="<?php echo escape($student['major'] ?? ''); ?>">
         </div>
         <div>
             <label for="academic_year">Year:</label>
             <select id="academic_year" name="academic_year">
                 <option value="" <?php echo empty($student['academic_year']) ? 'selected' : ''; ?>>-- Select Year --</option>
                 <?php $years = ['Freshman', 'Sophomore', 'Junior', 'Senior', 'Graduate']; ?>
                 <?php foreach ($years as $year): ?>
                 <option value="<?php echo $year; ?>" <?php echo ($student['academic_year'] ?? '') === $year ? 'selected' : ''; ?>>
                     <?php echo $year; ?>
                 </option>
                 <?php endforeach; ?>
             </select>
         </div>
         <div>
             <label for="gpa">GPA:</label>
             <input type="number" id="gpa" name="gpa" step="0.01" min="0" max="4.0" value="<?php echo escape($student['gpa'] ?? ''); ?>">
         </div>
    </fieldset>

     <fieldset>
         <legend>Profile Details</legend>
          <div>
            <label for="profile_summary">Profile Summary:</label>
            <textarea id="profile_summary" name="profile_summary"><?php echo escape($student['profile_summary'] ?? ''); ?></textarea>
         </div>
          <div>
            <label for="linkedin_url">LinkedIn URL:</label>
            <input type="text" id="linkedin_url" name="linkedin_url" value="<?php echo escape($student['linkedin_url'] ?? ''); ?>">
         </div>
         <div>
            <label for="github_url">GitHub URL:</label>
            <input type="text" id="github_url" name="github_url" value="<?php echo escape($student['github_url'] ?? ''); ?>">
         </div>
    </fieldset>

    <?php /*
    // --- TODO: Skills & Courses Selection ---
    // This requires more complex handling: fetching all, checking current, updating join tables.
    <fieldset>
        <legend>Skills</legend>
        <!-- Display checkboxes or multi-select for $all_skills, checking ones in $current_skills -->
    </fieldset>
     <fieldset>
        <legend>Courses Taken</legend>
        <!-- Display checkboxes or multi-select for $all_courses, checking ones in $current_courses -->
    </fieldset>
     <fieldset>
        <legend>Preferences</legend>
        <!-- Input fields for Industry/Location preferences -->
    </fieldset>
    */ ?>


    <div>
        <button type="submit">Update Profile</button>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
