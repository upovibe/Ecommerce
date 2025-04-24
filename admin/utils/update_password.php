<?php
session_start();
require_once '../../config/settings.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Ensure POST request and proper content type
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input from php://input
    $input = json_decode(file_get_contents('php://input'), true);

    // Ensure data was decoded and keys exist
    if (!$input) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
        exit;
    }
    
    $currentPassword = $input['current_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';
    $confirmPassword = $input['confirm_password'] ?? ''; // Get from decoded JSON
    
    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        exit;
    }
    
    // Backend length check (can differ from client-side, e.g., 8 vs 6)
    if (strlen($newPassword) < 6) { // Adjusted to match client-side for consistency here
        echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters long']);
        exit;
    }
    
    if ($db_connected && $conn) {
        // Verify current password using admin_id from session
        $admin_id = $_SESSION['admin_id'] ?? null;
        if (!$admin_id) {
             echo json_encode(['success' => false, 'message' => 'Admin session error. Please log out and back in.']);
             exit;
        }

        $stmt = $conn->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
        $stmt->bind_param('i', $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($currentPassword, $row['password_hash'])) {
                // Update password
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admin_users SET password_hash = ?, password_changed = TRUE WHERE id = ?");
                $stmt->bind_param('si', $newPasswordHash, $admin_id);
                
                if ($stmt->execute()) {
                    // Update session to reflect password change
                    $_SESSION['password_changed'] = true;
                    $_SESSION['password_needs_change'] = false; // Also update this flag
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Password changed successfully!'
                    ]);
                } else {
                    error_log("Error updating password for admin ID {$admin_id}: " . $conn->error);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error updating password in database.'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Admin user not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection not available'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}