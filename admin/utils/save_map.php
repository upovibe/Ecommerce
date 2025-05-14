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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $mapId = isset($_POST['map_id']) && !empty($_POST['map_id']) ? (int)$_POST['map_id'] : null;
    $locationName = $_POST['location_name'] ?? '';
    $address = $_POST['address'] ?? '';
    $mapUrl = $_POST['map_url'] ?? '';
    $displayOrder = (int)($_POST['display_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate required fields
    if (empty($locationName) || empty($mapUrl)) {
        $_SESSION['admin_error'] = 'Location name and map URL are required';
        header('Location: ../settings.php?tab=contact');
        exit;
    }
    
    if ($mapId) {
        // Update existing map
        $stmt = $conn->prepare("UPDATE store_maps SET location_name = ?, address = ?, map_url = ?, display_order = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param('sssiii', $locationName, $address, $mapUrl, $displayOrder, $isActive, $mapId);
        
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = 'Store location updated successfully';
        } else {
            $_SESSION['admin_error'] = 'Failed to update store location: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        // Add new map
        $stmt = $conn->prepare("INSERT INTO store_maps (location_name, address, map_url, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssii', $locationName, $address, $mapUrl, $displayOrder, $isActive);
        
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = 'Store location added successfully';
        } else {
            $_SESSION['admin_error'] = 'Failed to add store location: ' . $stmt->error;
        }
        $stmt->close();
    }
    
    // Redirect back to contact settings
    header('Location: ../settings.php?tab=contact');
    exit;
} else {
    // Invalid request method
    $_SESSION['admin_error'] = 'Invalid request method';
    header('Location: ../settings.php?tab=contact');
    exit;
}
?> 