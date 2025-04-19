<?php
session_start();
require_once '../../config/db.php'; // Adjust path as needed
require_once '../../config/settings.php'; // For password hashing compatibility if needed

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

// Check database connection
if (!$db_connected || !$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get input data from JSON body
$input = json_decode(file_get_contents('php://input'), true);

$newUsername = trim($input['new_username'] ?? '');
$currentPassword = $input['current_password'] ?? '';
$adminId = $_SESSION['admin_id'];

// Basic Validation
if (empty($newUsername) || empty($currentPassword)) {
    echo json_encode(['success' => false, 'message' => 'New username and current password are required.']);
    exit;
}

if (strlen($newUsername) < 3 || strlen($newUsername) > 50) {
    echo json_encode(['success' => false, 'message' => 'Username must be between 3 and 50 characters.']);
    exit;
}

// Check if new username already exists (optional but recommended)
$stmt_check = $conn->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
if ($stmt_check) {
    $stmt_check->bind_param("si", $newUsername, $adminId);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already taken. Please choose another.']);
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();
} else {
    error_log("Failed to prepare username check statement: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error checking username availability.']);
    exit;
}


try {
    // Fetch current user details (including password hash)
    $stmt_fetch = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
    if (!$stmt_fetch) {
        throw new Exception("Failed to prepare fetch statement: " . $conn->error);
    }
    $stmt_fetch->bind_param("i", $adminId);
    $stmt_fetch->execute();
    $result = $stmt_fetch->get_result();
    $user = $result->fetch_assoc();
    $stmt_fetch->close();

    if (!$user) {
        throw new Exception("User not found.");
    }

    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
        exit;
    }

    // Update username
    $stmt_update = $conn->prepare("UPDATE admin_users SET username = ? WHERE id = ?");
    if (!$stmt_update) {
         throw new Exception("Failed to prepare update statement: " . $conn->error);
    }
    $stmt_update->bind_param("si", $newUsername, $adminId);
    
    if ($stmt_update->execute()) {
        // Update session variable
        $_SESSION['admin_username'] = $newUsername;
        echo json_encode(['success' => true, 'message' => 'Username updated successfully.']);
    } else {
        throw new Exception("Failed to update username: " . $stmt_update->error);
    }
    $stmt_update->close();

} catch (Exception $e) {
    error_log("Error updating username for admin ID {$adminId}: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An internal error occurred. Please try again.']);
}

$conn->close(); 