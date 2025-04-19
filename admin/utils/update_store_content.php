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
    $content_keys = [
        'hero_title',
        'hero_subtitle',
        'about_title',
        'about_content',
        'featured_title',
        'featured_subtitle'
    ];

    $content_to_update = [];
    foreach ($content_keys as $key) {
        // Allow HTML for about_content, sanitize others lightly
        if ($key === 'about_content') {
            // A more robust HTML purifier/validator should be used in production
            $content_to_update[$key] = $_POST[$key] ?? ''; 
        } else {
            $content_to_update[$key] = htmlspecialchars($_POST[$key] ?? '', ENT_QUOTES, 'UTF-8');
        }
    }
    
    if ($db_connected && $conn) {
        $success = true;
        $updatedContent = [];
        
        $conn->begin_transaction();

        try {
            foreach ($content_to_update as $key => $value) {
                // Check if the setting exists
                $checkStmt = $conn->prepare("SELECT COUNT(*) FROM store_content WHERE content_key = ?");
                $checkStmt->bind_param('s', $key);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                $exists = $checkResult->fetch_row()[0] > 0;
                $checkStmt->close();
                
                if ($exists) {
                    // Update existing setting
                    $stmt = $conn->prepare("UPDATE store_content SET content_value = ? WHERE content_key = ?");
                    $stmt->bind_param('ss', $value, $key);
                } else {
                    // Insert new setting
                    $stmt = $conn->prepare("INSERT INTO store_content (content_key, content_value) VALUES (?, ?)");
                    $stmt->bind_param('ss', $key, $value);
                }
                
                if (!$stmt->execute()) {
                    throw new Exception("Error processing key: " . $key . " - " . $stmt->error);
                }
                $stmt->close();
                $updatedContent[$key] = $value; // Store the value that was actually saved
            }
            
            $conn->commit();
            
            // Clear potential cached content settings
            if (isset($_SESSION['store_content'])) {
                unset($_SESSION['store_content']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Store content updated successfully!',
                'content' => $updatedContent
            ]);

        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
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