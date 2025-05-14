<?php
session_start();
require_once '../../config/settings.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Check if we have a database connection
if (!$db_connected || !$conn) {
    $_SESSION['admin_error'] = 'Database connection failed';
    header('Location: ../settings.php?tab=contact');
    exit;
}

// Process form data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['map_id'])) {
    $mapId = (int)$_POST['map_id'];
    
    // Delete map
    $stmt = $conn->prepare("DELETE FROM store_maps WHERE id = ?");
    $stmt->bind_param('i', $mapId);
    
    if ($stmt->execute()) {
        $_SESSION['admin_success'] = 'Store location deleted successfully';
    } else {
        $_SESSION['admin_error'] = 'Failed to delete store location: ' . $stmt->error;
    }
    $stmt->close();
    
    // Redirect back to contact settings
    header('Location: ../settings.php?tab=contact');
    exit;
} else {
    // Invalid request
    $_SESSION['admin_error'] = 'Invalid request';
    header('Location: ../settings.php?tab=contact');
    exit;
}
?> 