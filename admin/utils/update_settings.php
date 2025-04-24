<?php
// Removed output buffering comment
session_start();

require_once __DIR__ . '/../../config/settings.php';

// Development debugging: logs POST data. Remove or guard for production.
error_log("Received POST data in update_settings.php: " . print_r($_POST, true));

// Suppress PHP errors to ensure clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// === Security Checks ===
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// === Retrieve and Sanitize POST Data ===
$store_name = trim($_POST['store_name'] ?? '');
$store_description = trim($_POST['store_description'] ?? '');
$whatsapp_number = trim($_POST['whatsapp_number'] ?? '');
$currency_symbol = trim($_POST['currency_symbol'] ?? '');
$theme_color = trim($_POST['theme_color'] ?? '#3B82F6');
$whatsapp_message_template = trim($_POST['whatsapp_message_template'] ?? '');
$footer_text = trim($_POST['footer_text'] ?? '');
$brand_text_color = trim($_POST['brand_text_color'] ?? '#FFFFFF');
$facebook_username = trim($_POST['facebook_username'] ?? '');
$instagram_username = trim($_POST['instagram_username'] ?? '');
$twitter_username = trim($_POST['twitter_username'] ?? '');
$tiktok_username = trim($_POST['tiktok_username'] ?? '');
$linkedin_username = trim($_POST['linkedin_username'] ?? '');
$youtube_username = trim($_POST['youtube_username'] ?? '');

// === Basic Validation ===
if (empty($store_name)) {
    echo json_encode(['success' => false, 'message' => 'Store Name cannot be empty.']);
    exit;
}

// Validate hex color format (basic implementation)
$validateHex = function($color, $default) {
    if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
        return $default;
    }
    return $color;
};
$theme_color = $validateHex($theme_color, '#3B82F6');
$brand_text_color = $validateHex($brand_text_color, '#FFFFFF');

// === Update Database ===
if ($db_connected && $conn) {
    $settingsToUpdate = [
        'store_name' => $store_name,
        'store_description' => $store_description,
        'whatsapp_number' => $whatsapp_number,
        'currency_symbol' => $currency_symbol,
        'theme_color' => $theme_color,
        'whatsapp_message_template' => $whatsapp_message_template,
        'footer_text' => $footer_text,
        'brand_text_color' => $brand_text_color,
        'facebook_username' => $facebook_username,
        'instagram_username' => $instagram_username,
        'twitter_username' => $twitter_username,
        'tiktok_username' => $tiktok_username,
        'linkedin_username' => $linkedin_username,
        'youtube_username' => $youtube_username
    ];

    $conn->begin_transaction();
    $stmt = null; 
    try {
        // Prepare statement for inserting or updating settings
        $sql = "INSERT INTO store_settings (setting_key, setting_value) VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Database error preparing settings statement: " . $conn->error);
        }

        // Execute update for each setting
        foreach ($settingsToUpdate as $key => $value) {
            $stmt->bind_param('ss', $key, $value);
            if (!$stmt->execute()) {
                throw new Exception("Database error updating setting '{$key}': " . $stmt->error);
            }
        }
        
        $stmt->close(); 
        $conn->commit();
        
        // Send success response
        echo json_encode(['success' => true, 'message' => 'General settings updated successfully!']);

    } catch (Exception $e) {
        // Rollback transaction on error if possible
        if ($conn->errno) { 
             $conn->rollback();
        }
        error_log("DB Settings Update Error: " . $e->getMessage());
        
        // Send error response
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);

    } finally {
        // Ensure statement is closed if it was successfully prepared
        if ($stmt instanceof mysqli_stmt) { 
            $stmt->close();
        }
    }
} else {
    // Handle case where DB is not connected initially
    error_log('Database connection is not available for settings update.');
    
    // Send error response for DB connection failure
    echo json_encode([
        'success' => false,
        'message' => 'Database connection not available'
    ]);
}

exit;

?>