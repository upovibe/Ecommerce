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
if (!$input || !isset($input['new_username']) || !isset($input['current_password'])) {
    $response['message'] = 'Invalid input data (username or password missing).';
    echo json_encode($response);
    exit;
}

$new_username = trim($input['new_username']);
$current_password_for_confirm = $input['current_password']; // Renamed for clarity
$admin_id = $_SESSION['admin_id'] ?? null;

// 4. Validate inputs
if (empty($new_username)) {
    $response['message'] = 'New username cannot be empty.';
    echo json_encode($response);
    exit;
}
if (empty($current_password_for_confirm)) {
    $response['message'] = 'Current password is required to change username.';
    echo json_encode($response);
    exit;
}
if (!$admin_id) {
    $response['message'] = 'Admin session error. Please log out and back in.';
    echo json_encode($response);
    exit;
}

// Prevent changing to the same username
if ($new_username === ($_SESSION['admin_username'] ?? '')) {
    $response['message'] = 'New username cannot be the same as the current one.';
    echo json_encode($response);
    exit;
}

// Prevent username conflicts (check if new username already exists for another user)
try {
    $stmt_check = $conn->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
    if (!$stmt_check) throw new Exception("Prepare failed (username check): " . $conn->error); 
    $stmt_check->bind_param("si", $new_username, $admin_id);
    if(!$stmt_check->execute()) throw new Exception("Execute failed (username check): " . $stmt_check->error);
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $response['message'] = 'This username is already taken.';
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

// 5. Fetch current admin's password hash to verify confirmation password
try {
    $stmt_fetch = $conn->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    if (!$stmt_fetch) throw new Exception("Prepare failed (fetch hash): " . $conn->error);
    $stmt_fetch->bind_param("i", $admin_id);
    if(!$stmt_fetch->execute()) throw new Exception("Execute failed (fetch hash): " . $stmt_fetch->error);
    $result_fetch = $stmt_fetch->get_result();

    if ($result_fetch->num_rows === 1) {
        $admin_data = $result_fetch->fetch_assoc();
        $current_hash = $admin_data['password_hash'];

        // 6. Verify the *current* password provided for confirmation
        if (password_verify($current_password_for_confirm, $current_hash)) {
            // 7. Update username in the database
            $stmt_update = $conn->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
            if (!$stmt_update) throw new Exception("Prepare failed (update username): " . $conn->error);
            $stmt_update->bind_param("si", $new_username, $admin_id);

            if ($stmt_update->execute()) {
                // 8. Update session username
                $_SESSION['admin_username'] = $new_username;
                $response['success'] = true;
                $response['message'] = 'Username updated successfully! You will be logged out for the change to fully take effect.';
            } else {
                throw new Exception("Execute failed (update username): " . $stmt_update->error);
            }
            $stmt_update->close();
        } else {
            $response['message'] = 'Incorrect current password provided for confirmation.';
        }
    } else {
        $response['message'] = 'Admin user not found.';
    }
    $stmt_fetch->close();

} catch (Exception $e) {
    error_log("Error during username update process: " . $e->getMessage());
    $response['message'] = 'An error occurred during the username update process.';
}

$conn->close();
echo json_encode($response);
exit;
?> 