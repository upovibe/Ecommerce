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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faq_id'])) {
    $faqId = (int)$_POST['faq_id'];
    
    // Delete FAQ
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->bind_param('i', $faqId);
    
    if ($stmt->execute()) {
        $_SESSION['admin_success'] = 'FAQ deleted successfully';
    } else {
        $_SESSION['admin_error'] = 'Failed to delete FAQ: ' . $stmt->error;
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