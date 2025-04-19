<?php
session_start();

// Suppress errors for JSON endpoint
error_reporting(0);
ini_set('display_errors', 0);

require_once '../../config/settings.php'; // For DB connection

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check DB connection
if (!$db_connected || !$conn) {
     echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
     exit;
}

// --- Retrieve POST data ---
// Define expected keys from the form
$content_keys = ['hero_title', 'hero_subtitle', 'featured_title', 'featured_subtitle', 'about_title', 'about_content'];
$content_data = [];
foreach ($content_keys as $key) {
    // Allow empty values, trim whitespace
    $content_data[$key] = trim($_POST[$key] ?? ''); 
}

// --- Update Database --- 
$conn->begin_transaction();
try {
    // Use INSERT ... ON DUPLICATE KEY UPDATE for efficiency
    $sql = "INSERT INTO store_content (content_key, content_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE content_value = VALUES(content_value)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Database error preparing statement: " . $conn->error);
    }

    foreach ($content_data as $key => $value) {
        $stmt->bind_param('ss', $key, $value);
        if (!$stmt->execute()) {
            throw new Exception("Database error updating content for key '{$key}': " . $stmt->error);
        }
    }
    
    $stmt->close();
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Store content updated successfully!']);

} catch (Exception $e) {
    $conn->rollback(); // Rollback on error
    error_log("Update Content Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating content: ' . $e->getMessage()]);
} finally {
    // Close statement if it was created
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    // Close connection if needed
    // $conn->close();
}

exit;
?> 