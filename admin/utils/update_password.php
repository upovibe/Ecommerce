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
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match']);
        exit;
    }
    
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'New password must be at least 8 characters long']);
        exit;
    }
    
    if ($db_connected && $conn) {
        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM admin_users WHERE username = ?");
        $stmt->bind_param('s', $_SESSION['admin_username']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($currentPassword, $row['password_hash'])) {
                // Update password
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admin_users SET password_hash = ?, password_changed = TRUE WHERE username = ?");
                $stmt->bind_param('ss', $newPasswordHash, $_SESSION['admin_username']);
                
                if ($stmt->execute()) {
                    // Update session to reflect password change
                    $_SESSION['password_changed'] = true;
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Password changed successfully!'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error updating password: ' . $conn->error
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
                'message' => 'User not found'
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