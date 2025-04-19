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

// --- Handle Hero Image Upload ---
$hero_image_path = null;
if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] == UPLOAD_ERR_OK) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/images/';
    $allowed_types = [
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        'image/webp' => '.webp',
        'image/gif' => '.gif'
    ];
    $max_size = 2 * 1024 * 1024; // 2MB

    $file_tmp_name = $_FILES['hero_image']['tmp_name'];
    $file_size = $_FILES['hero_image']['size'];
    $file_type = mime_content_type($file_tmp_name);

    if (!isset($allowed_types[$file_type])) {
        echo json_encode(['success' => false, 'message' => 'Invalid image file type. Allowed types: JPG, PNG, WEBP, GIF.']);
        exit;
    }

    if ($file_size > $max_size) {
        echo json_encode(['success' => false, 'message' => 'Image file size exceeds the 2MB limit.']);
        exit;
    }

    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
             echo json_encode(['success' => false, 'message' => 'Failed to create upload directory.']);
             exit;
        }
    }

    // Generate unique filename
    $file_ext = $allowed_types[$file_type];
    $unique_name = 'hero_' . uniqid() . $file_ext;
    $destination = $upload_dir . $unique_name;

    // Move the file
    if (!move_uploaded_file($file_tmp_name, $destination)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload hero image.']);
        exit;
    }
    
    $hero_image_path = '/uploads/images/' . $unique_name; // Relative path for storage

    // Optional: Delete old hero image
    $old_image_sql = "SELECT content_value FROM store_content WHERE content_key = 'hero_image'";
    $old_image_result = $conn->query($old_image_sql);
    if ($old_image_result && $old_image_result->num_rows > 0) {
        $old_image_row = $old_image_result->fetch_assoc();
        $old_image_db_path = $old_image_row['content_value'];
        // Avoid deleting demo/placeholder images
        if ($old_image_db_path && strpos($old_image_db_path, '/uploads/') === 0) {
            $old_image_server_path = $_SERVER['DOCUMENT_ROOT'] . $old_image_db_path;
            if (file_exists($old_image_server_path)) {
                @unlink($old_image_server_path); // Suppress error if deletion fails
            }
        }
    }
}

// Add hero image path to content data if it was uploaded
if ($hero_image_path !== null) {
    $content_data['hero_image'] = $hero_image_path;
} else {
    // Ensure hero_image key exists even if no file uploaded, 
    // so it doesn't get accidentally removed if it wasn't in the initial $content_keys array.
    // We get its current value to avoid overwriting if no file is uploaded.
    if (!isset($content_data['hero_image'])) {
        $current_image_sql = "SELECT content_value FROM store_content WHERE content_key = 'hero_image'";
        $current_image_result = $conn->query($current_image_sql);
        if ($current_image_result && $current_image_result->num_rows > 0) {
            $current_image_row = $current_image_result->fetch_assoc();
            $content_data['hero_image'] = $current_image_row['content_value'];
        } else {
             // If somehow it doesn't exist, set a default or leave it out
             // For safety, let's not insert it if it wasn't there and wasn't uploaded
             // $content_data['hero_image'] = ''; 
        }
    }
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