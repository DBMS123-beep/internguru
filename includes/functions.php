<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Authentication Functions ---

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        // Store the requested page to redirect back after login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: login.php');
        exit;
    }
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_role() {
     return $_SESSION['role'] ?? null;
}

// --- Security Functions ---

function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// --- Recommendation Service Call Function ---

function get_recommendations_from_python($student_id) {
    // URL of your running Python Flask service (Use env var in production)
    $python_service_url = getenv('RECOMMENDATION_SERVICE_URL') ?: 'http://127.0.0.1:5001/recommend';

    $data_to_send = json_encode(['student_id' => $student_id]);
    $recommendations = [];
    $error_message = null;

    $ch = curl_init($python_service_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_to_send);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_to_send)
    ]);
    // Add timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 seconds connection timeout
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);       // 10 seconds total timeout

    $response_json = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        $error_message = "Error calling recommendation service: " . $curl_error;
        error_log($error_message); // Log detailed error
    } elseif ($response_json !== false && $http_code == 200) {
        $recommendation_data = json_decode($response_json, true);
        if (isset($recommendation_data['recommendations'])) {
            $recommendations = $recommendation_data['recommendations'];
            // $recommendations should be like [{'internship_id': X, 'score': Y}, ...]
        } else {
             $error_message = "Invalid response format from recommendation service.";
             error_log($error_message . " Response: " . $response_json);
        }
    } else {
        $error_message = "Recommendation service returned HTTP status " . $http_code;
        error_log($error_message . " Response: " . $response_json);
    }

    return ['data' => $recommendations, 'error' => $error_message];
}

// --- Database Fetching Functions (Example) ---

function get_student_profile($pdo, $student_id) {
    $sql = "SELECT s.*, u.email FROM students s JOIN users u ON s.student_id = u.user_id WHERE s.student_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id]);
    return $stmt->fetch();
}

function get_internship_details($pdo, $internship_id) {
     $sql = "SELECT i.*, c.company_name, c.website
             FROM internships i
             JOIN companies c ON i.company_id = c.company_id
             WHERE i.internship_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$internship_id]);
    return $stmt->fetch();
}

function get_internships_by_ids($pdo, $ids_with_scores) {
    if (empty($ids_with_scores)) {
        return [];
    }

    $internship_ids = array_column($ids_with_scores, 'internship_id');
    $scores_map = array_column($ids_with_scores, 'score', 'internship_id');

    // Create placeholders for IN clause
    $placeholders = implode(',', array_fill(0, count($internship_ids), '?'));

    $sql = "SELECT i.*, c.company_name
            FROM internships i
            JOIN companies c ON i.company_id = c.company_id
            WHERE i.internship_id IN ($placeholders)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($internship_ids);
    $raw_recommendations = $stmt->fetchAll();

    // Map scores back and prepare final list
    $recommendations = [];
    foreach($raw_recommendations as $internship) {
        $internship['score'] = $scores_map[$internship['internship_id']] ?? 0; // Add score
        $recommendations[] = $internship;
    }

    // Sort by score again (Python order might be lost)
    usort($recommendations, function($a, $b) {
        return $b['score'] <=> $a['score']; // Descending score
    });

    return $recommendations;
}


// Add more functions as needed (e.g., getting student skills/courses)

?>
