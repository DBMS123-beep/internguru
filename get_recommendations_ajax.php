<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json'); // Set JSON header

if (!is_logged_in()) {
    echo json_encode(['error' => 'Not authorized']);
    exit;
}

$student_id = get_user_id();
$recommendations = [];
$fetch_error = null;
$response_data = [];

$python_result = get_recommendations_from_python($student_id);

if ($python_result['error']) {
    $fetch_error = $python_result['error'];
} elseif (!empty($python_result['data'])) {
    try {
        $recommendations = get_internships_by_ids($pdo, $python_result['data']);
        $response_data['recommendations'] = $recommendations; // Assign data if successful
    } catch (PDOException $e) {
        error_log("AJAX DB Error fetching internship details: " . $e->getMessage());
        $fetch_error = "Could not retrieve internship details.";
    }
} else {
    // No error from Python, but no recommendations returned
    $response_data['recommendations'] = [];
}

// Set error in response if one occurred
if ($fetch_error) {
    $response_data['error'] = $fetch_error;
}

// Echo the final JSON response
echo json_encode($response_data);
exit;
?>
