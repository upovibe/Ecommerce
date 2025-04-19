<?php
session_start();
require_once '../../config/db.php'; // Adjust path as needed
require_once '../../config/settings.php'; // For password hashing compatibility

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

$currentPassword = $input['current_password'] ?? '';
$newPassword = $input['new_password'] ?? '';
$adminId = $_SESSION['admin_id'];

// Basic Validation
if (empty($currentPassword) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'Current and new passwords are required.']);
    exit;
}

// Password Strength (Example: minimum 8 characters)
if (strlen($newPassword) < 8) {
    echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long.']);
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

    // Hash the new password
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    if ($newPasswordHash === false) {
         throw new Exception("Failed to hash new password.");
    }

    // Update password in the database
    $stmt_update = $conn->prepare("UPDATE admin_users SET password = ?, password_changed = 1 WHERE id = ?");
    if (!$stmt_update) {
         throw new Exception("Failed to prepare update statement: " . $conn->error);
    }
    // Assuming password_changed is a boolean/tinyint column
    $stmt_update->bind_param("si", $newPasswordHash, $adminId);
    
    if ($stmt_update->execute()) {
        // Update session variable
        $_SESSION['password_changed'] = true;
        echo json_encode(['success' => true, 'message' => 'Password changed successfully.']);
    } else {
        throw new Exception("Failed to update password: " . $stmt_update->error);
    }
    $stmt_update->close();

} catch (Exception $e) {
    error_log("Error changing password for admin ID {$adminId}: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An internal error occurred. Please try again.']);
}

$conn->close();
?> 