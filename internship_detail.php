<?php
$page_title = "Internship Details";
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

require_login(); // Require login to view details

$internship_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$internship = null;

if (!$internship_id) {
    die("Invalid Internship ID."); // Or redirect
}

try {
    $internship = get_internship_details($pdo, $internship_id);
    if (!$internship) {
        die("Internship not found."); // Or redirect
    }

    // TODO: Fetch required/preferred skills and courses for this internship
    // $required_skills = ...
    // $preferred_skills = ...

} catch (PDOException $e) {
     error_log("Internship Detail Error: " . $e->getMessage());
     die("Error loading internship details.");
}


require_once __DIR__ . '/includes/header.php';
?>

<?php if ($internship): ?>
    <h2><?php echo escape($internship['title']); ?></h2>
    <h3><?php echo escape($internship['company_name']); ?></h3>

    <p><strong>Location:</strong> <?php echo escape($internship['location'] ?? 'N/A'); ?></p>
    <p><strong>Industry:</strong> <?php echo escape($internship['industry'] ?? 'N/A'); ?></p>
    <p><strong>Duration:</strong> <?php echo escape($internship['duration'] ?? 'N/A'); ?></p>
    <p><strong>Paid:</strong> <?php echo isset($internship['is_paid']) ? ($internship['is_paid'] ? 'Yes' : 'No') : 'N/A'; ?></p>

    <h4>Description</h4>
    <div><?php echo nl2br(escape($internship['description'] ?? 'No description available.')); // nl2br to respect newlines ?></div>

    <h4>Requirements</h4>
    <ul>
        <?php if ($internship['required_gpa']): ?><li>Minimum GPA: <?php echo escape($internship['required_gpa']); ?></li><?php endif; ?>
        <?php if ($internship['required_major']): ?><li>Major(s): <?php echo escape($internship['required_major']); ?></li><?php endif; ?>
        <?php if ($internship['required_year'] && $internship['required_year'] !== 'Any'): ?><li>Year: <?php echo escape($internship['required_year']); ?></li><?php endif; ?>
        <!-- TODO: List required skills/courses -->
    </ul>

    <?php /* TODO: Add Preferred Skills/Courses section */ ?>


    <?php if ($internship['application_deadline']): ?>
        <p><strong>Apply By:</strong> <?php echo date('F j, Y', strtotime($internship['application_deadline'])); ?></p>
    <?php endif; ?>

    <?php if ($internship['url']): ?>
        <p><a href="<?php echo escape($internship['url']); ?>" target="_blank" rel="noopener noreferrer">Apply / More Info</a></p>
    <?php endif; ?>
     <?php if ($internship['website']): ?>
        <p><a href="<?php echo escape($internship['website']); ?>" target="_blank" rel="noopener noreferrer">Company Website</a></p>
    <?php endif; ?>

    <p><a href="dashboard.php">« Back to Dashboard</a></p>

<?php else: ?>
    <p>Internship details could not be loaded.</p>
<?php endif; ?>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
