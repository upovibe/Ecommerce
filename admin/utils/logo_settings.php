<?php
session_start();
require_once '../config/settings.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['logo'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            $message = 'Invalid file type. Please upload a JPG or PNG image.';
            $messageType = 'error';
        } elseif ($file['size'] > $maxFileSize) {
            $message = 'File is too large. Maximum size is 2MB.';
            $messageType = 'error';
        } else {
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $extension;
            $uploadDir = __DIR__ . '/../assets/images/';
            $uploadPath = $uploadDir . $filename;
            
            // Ensure upload directory exists
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Update database
                if ($db_connected && $conn) {
                    $logoPath = '/assets/images/' . $filename;
                    
                    // Get old logo path before updating
                    $oldLogoPath = '';
                    $stmt = $conn->prepare("SELECT setting_value FROM store_settings WHERE setting_key = 'store_logo'");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($row = $result->fetch_assoc()) {
                        $oldLogoPath = $row['setting_value'];
                    }
                    
                    // Update or insert new logo path
                    $stmt = $conn->prepare("UPDATE store_settings SET setting_value = ? WHERE setting_key = 'store_logo'");
                    $stmt->bind_param('s', $logoPath);
                    $stmt->execute();
                    
                    if ($conn->affected_rows === 0) {
                        $stmt = $conn->prepare("INSERT INTO store_settings (setting_key, setting_value) VALUES ('store_logo', ?)");
                        $stmt->bind_param('s', $logoPath);
                        $stmt->execute();
                    }
                    
                    // Delete old logo file if it exists and isn't the default
                    if ($oldLogoPath && $oldLogoPath !== '/assets/images/logo.png') {
                        $oldFullPath = __DIR__ . '/..' . $oldLogoPath;
                        if (file_exists($oldFullPath)) {
                            unlink($oldFullPath);
                        }
                        
                        // Clear any potential cached versions
                        if (function_exists('opcache_invalidate')) {
                            opcache_invalidate($oldFullPath, true);
                        }
                    }
                    
                    // Clear settings cache to force refresh
                    if (isset($_SESSION['store_settings'])) {
                        unset($_SESSION['store_settings']);
                    }
                    
                    $message = 'Logo updated successfully!';
                    $messageType = 'success';
                    
                    // Add cache-busting parameter to force logo refresh
                    $_SESSION['logo_version'] = time();
                    
                    // Redirect back to settings with success message
                    $_SESSION['message'] = $message;
                    $_SESSION['message_type'] = $messageType;
                    header('Location: settings.php');
                    exit;
                } else {
                    // If database update fails, remove the uploaded file
                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }
                    $message = 'Database error. Could not update logo settings.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Error uploading file. Please try again.';
                $messageType = 'error';
            }
        }
    } else {
        $message = 'Please select a file to upload.';
        $messageType = 'error';
    }
    
    // If we reach here, there was an error
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $messageType;
    header('Location: settings.php');
    exit;
}

// If accessed directly without POST, redirect to settings
header('Location: settings.php');
exit;
?>