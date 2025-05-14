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
    $faqId = isset($_POST['faq_id']) && !empty($_POST['faq_id']) ? (int)$_POST['faq_id'] : null;
    $question = $_POST['question'] ?? '';
    $answer = $_POST['answer'] ?? '';
    $displayOrder = (int)($_POST['display_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $pageLocation = $_POST['page_location'] ?? 'contact';
    
    // Validate required fields
    if (empty($question) || empty($answer)) {
        $_SESSION['admin_error'] = 'Question and answer are required';
        header('Location: ../settings.php?tab=contact');
        exit;
    }
    
    if ($faqId) {
        // Update existing FAQ
        $stmt = $conn->prepare("UPDATE faqs SET question = ?, answer = ?, display_order = ?, is_active = ?, page_location = ? WHERE id = ?");
        $stmt->bind_param('ssiisi', $question, $answer, $displayOrder, $isActive, $pageLocation, $faqId);
        
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = 'FAQ updated successfully';
        } else {
            $_SESSION['admin_error'] = 'Failed to update FAQ: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        // Add new FAQ
        $stmt = $conn->prepare("INSERT INTO faqs (question, answer, display_order, is_active, page_location) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiis', $question, $answer, $displayOrder, $isActive, $pageLocation);
        
        if ($stmt->execute()) {
            $_SESSION['admin_success'] = 'FAQ added successfully';
        } else {
            $_SESSION['admin_error'] = 'Failed to add FAQ: ' . $stmt->error;
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