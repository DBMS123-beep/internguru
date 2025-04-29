<?php
$page_title = "Dashboard";
require_once __DIR__ . '/includes/db.php';     // Database connection
require_once __DIR__ . '/includes/functions.php'; // Helper functions

require_login(); // Ensure user is logged in

$student_id = get_user_id();
$recommendations = [];
$fetch_error = null;

// --- Fetch Recommendations (Server-Side Rendering Approach) ---
$python_result = get_recommendations_from_python($student_id);

if ($python_result['error']) {
    $fetch_error = $python_result['error'];
} elseif (!empty($python_result['data'])) {
    // Python returned IDs and scores. Now fetch full details from our DB.
    try {
        $recommendations = get_internships_by_ids($pdo, $python_result['data']);
    } catch (PDOException $e) {
        error_log("DB Error fetching internship details: " . $e->getMessage());
        $fetch_error = "Could not retrieve internship details.";
    }
}
// --- End Fetching Recommendations ---

require_once __DIR__ . '/includes/header.php'; // Include header after logic
?>

<h2>Your Internship Recommendations</h2>

<?php if ($fetch_error): ?>
    <p class="message error"><?php echo escape($fetch_error); ?></p>
<?php endif; ?>

<div id="recommendation-list">
    <?php if (!empty($recommendations)): ?>
        <?php foreach ($recommendations as $internship): ?>
            <div class="internship-card">
                 <h3><a href="internship_detail.php?id=<?php echo $internship['internship_id']; ?>"><?php echo escape($internship['title']); ?></a></h3>
                 <p><span class="company"><?php echo escape($internship['company_name']); ?></span></p>
                 <p><span class="location"><?php echo escape($internship['location']); ?></span></p>
                 <p>Match Score: <span class="score"><?php echo round($internship['score'], 1); ?></span></p>
                 <?php if (!empty($internship['application_deadline'])):
                    $deadline = strtotime($internship['application_deadline']);
                    $is_past = $deadline < time();
                 ?>
                 <p style="<?php echo $is_past ? 'color: red;' : ''; ?>">
                     Apply by: <?php echo date('Y-m-d', $deadline); ?> <?php echo $is_past ? '(Deadline Past)' : ''; ?>
                 </p>
                 <?php endif; ?>
                 <a href="internship_detail.php?id=<?php echo $internship['internship_id']; ?>" class="details-link">View Details</a>
            </div>
        <?php endforeach; ?>
    <?php elseif (!$fetch_error): // Only show 'not found' if there wasn't an error ?>
        <p>No recommendations found matching your profile at this time.</p>
    <?php endif; ?>
</div>

<?php
// Note: If using AJAX loading via js/main.js, the PHP loop above can be removed,
// but keep the <div id="recommendation-list"></div> container.
?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
