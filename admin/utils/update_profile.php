<?php
require_once '../../config/settings.php';

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

// 1. Check if admin is logged in and DB connection exists
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !isset($_SESSION['admin_id'])) {
    $response['message'] = 'Authentication required or session invalid.';
    echo json_encode($response);
    exit;
}

if (!$db_connected || !$conn) {
    $response['message'] = 'Database connection error.';
    echo json_encode($response);
    exit;
}

// 2. Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// 3. Decode JSON input from php://input
$input = json_decode(file_get_contents('php://input'), true); 

// Ensure data was decoded
if (!$input) {
    $response['message'] = 'Invalid input data.';
    echo json_encode($response);
    exit;
}

// 4. Extract and sanitize data (basic trim, add more if needed)
$admin_id = $_SESSION['admin_id'];
$full_name = isset($input['full_name']) ? trim($input['full_name']) : null;
$gender = isset($input['gender']) ? trim($input['gender']) : null;
$about = isset($input['about']) ? trim($input['about']) : null;

// Optional: Add more validation (e.g., gender value check)

// 5. Update profile in the database
try {
    $stmt_update = $conn->prepare("UPDATE admin_users SET full_name = ?, gender = ?, about = ? WHERE id = ?");
    if (!$stmt_update) throw new Exception("Prepare failed: " . $conn->error);
    
    $stmt_update->bind_param("sssi", $full_name, $gender, $about, $admin_id);

    if ($stmt_update->execute()) {
        $response['success'] = true;
        $response['message'] = 'Profile updated successfully!';
    } else {
        throw new Exception("Execute failed: " . $stmt_update->error);
    }
    $stmt_update->close();

} catch (Exception $e) {
    error_log("Error during profile update for admin ID {$admin_id}: " . $e->getMessage());
    $response['message'] = 'An error occurred during the profile update process.';
}

$conn->close();
echo json_encode($response);
exit;
?> 