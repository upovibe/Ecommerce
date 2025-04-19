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

// Commenting out DB check temporarily for debugging
// if (!$db_connected || !$conn) {
//     $response['message'] = 'Database connection error.';
//     echo json_encode($response);
//     exit;
// }

// 2. Check request method (Temporarily commented out for debugging)
/*
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}
*/

// 3. Decode JSON input from php://input
$input = json_decode(file_get_contents('php://input'), true); 

// Ensure data was decoded and keys exist
if (!$input || !isset($input['new_username']) || !isset($input['current_password'])) {
    $response['message'] = 'Invalid input data.';
    echo json_encode($response);
    exit;
}

$new_username = trim($input['new_username']);
$current_password = $input['current_password'];
$admin_id = $_SESSION['admin_id'] ?? null; // Assuming admin_id is stored in session

// 4. Validate inputs
if (empty($new_username)) {
    $response['message'] = 'New username cannot be empty.';
    echo json_encode($response);
    exit;
}
if (empty($current_password)) {
    $response['message'] = 'Current password is required to change username.';
    echo json_encode($response);
    exit;
}
if (!$admin_id) {
    $response['message'] = 'Admin session error.';
    echo json_encode($response);
    exit;
}

// Prevent changing to the same username
if ($new_username === ($_SESSION['admin_username'] ?? '')) {
    $response['message'] = 'New username cannot be the same as the current one.';
    echo json_encode($response);
    exit;
}

// Prevent potential conflict (check if new username already exists FOR ANOTHER USER)
// This assumes you have a unique constraint on the username column
try {
    $stmt_check = $conn->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
    $stmt_check->bind_param("si", $new_username, $admin_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $response['message'] = 'This username is already taken by another account.';
        echo json_encode($response);
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();
} catch (Exception $e) {
    error_log("Error checking username existence: " . $e->getMessage());
    $response['message'] = 'Error checking username availability.';
    echo json_encode($response);
    exit;
}


// 5. Fetch current admin's password hash
try {
    // Ensure 'password_hash' is the correct column name in your admin_users table
    $stmt_fetch = $conn->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $stmt_fetch->bind_param("i", $admin_id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows === 1) {
        $admin_data = $result_fetch->fetch_assoc();
        $current_hash = $admin_data['password_hash'];

        // 6. Verify current password
        if (password_verify($current_password, $current_hash)) {
            // 7. Update username in the database
            $stmt_update = $conn->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
            $stmt_update->bind_param("si", $new_username, $admin_id);

            if ($stmt_update->execute()) {
                // 8. Update session username
                $_SESSION['admin_username'] = $new_username;
                $response['success'] = true;
                $response['message'] = 'Username updated successfully!';
            } else {
                $response['message'] = 'Failed to update username in database.';
                 error_log("Failed to update username for admin ID {$admin_id}: " . $stmt_update->error);
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
    error_log("Error during username update process: " . $e->getMessage());
    $response['message'] = 'An error occurred during the update process.';
}

$conn->close();
echo json_encode($response);
exit;
?> 