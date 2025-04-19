<?php
require_once '../../config/settings.php';

header('Content-Type: application/json');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

// 1. Check if admin is logged in and DB connection exists
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = 'Authentication required.';
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

// Ensure data was decoded and keys exist
if (!$input || !isset($input['current_password']) || !isset($input['new_password'])) {
    $response['message'] = 'Invalid input data.';
    echo json_encode($response);
    exit;
}

$current_password = $input['current_password'];
$new_password = $input['new_password'];
$admin_id = $_SESSION['admin_id'] ?? null; // Assuming admin_id is stored in session

// 4. Validate inputs
if (empty($current_password) || empty($new_password)) {
    $response['message'] = 'Both current and new passwords are required.';
    echo json_encode($response);
    exit;
}

// Basic password strength check (example)
if (strlen($new_password) < 8) { // You might want a more robust check
    $response['message'] = 'New password must be at least 8 characters long.';
    echo json_encode($response);
    exit;
}

if (!$admin_id) {
    $response['message'] = 'Admin session error.';
    echo json_encode($response);
    exit;
}

// 5. Fetch current admin's password hash
try {
    // Ensure 'password_hash' is the correct column name
    $stmt_fetch = $conn->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $stmt_fetch->bind_param("i", $admin_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows === 1) {
        $admin_data = $result_fetch->fetch_assoc();
        $current_hash = $admin_data['password_hash'];

        // 6. Verify current password
        if (password_verify($current_password, $current_hash)) {
            // 7. Hash the new password
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // 8. Update password hash in the database
            // Optional: Add a password_last_changed timestamp column for security auditing
            $stmt_update = $conn->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
            $stmt_update->bind_param("si", $new_hash, $admin_id);

            if ($stmt_update->execute()) {
                // 9. Update session flag (important if using forced password change)
                $_SESSION['password_changed'] = true; // Mark as changed
                // If you have a forced change mechanism, remove the flag that triggers it, e.g.:
                // unset($_SESSION['force_password_change']); 

                $response['success'] = true;
                $response['message'] = 'Password changed successfully!';
            } else {
                $response['message'] = 'Failed to update password in database.';
                error_log("Failed to update password for admin ID {$admin_id}: " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            $response['message'] = 'Incorrect current password.';
        }
    } else {
        $response['message'] = 'Admin user not found.';
    }
    $stmt_fetch->close();

} catch (Exception $e) {
    error_log("Error during password change process: " . $e->getMessage());
    $response['message'] = 'An error occurred during the password change process.';
}

$conn->close();
echo json_encode($response);
exit;
?> 