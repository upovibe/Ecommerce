<?php
session_start();

// Suppress errors for JSON endpoint
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Settings file path
$settingsFilePath = __DIR__ . '/../../config/settings.php';

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

// Check if settings file is writable
if (!is_writable($settingsFilePath)) {
    error_log("Settings file is not writable: " . $settingsFilePath);
    echo json_encode(['success' => false, 'message' => 'Configuration file is not writable. Check server permissions.']);
    exit;
}

// --- Retrieve POST data ---
$store_name = trim($_POST['store_name'] ?? '');
$store_description = trim($_POST['store_description'] ?? '');
$whatsapp_number = trim($_POST['whatsapp_number'] ?? '');
$currency_symbol = trim($_POST['currency_symbol'] ?? '');
$theme_color = trim($_POST['theme_color'] ?? '#3B82F6'); // From JS
$whatsapp_message_template = trim($_POST['whatsapp_message_template'] ?? '');
$footer_text = trim($_POST['footer_text'] ?? '');

// Basic validation (can be more robust)
if (empty($store_name)) {
    echo json_encode(['success' => false, 'message' => 'Store Name cannot be empty.']);
    exit;
}
// Validate theme color format
if (!preg_match('/^#[a-fA-F0-9]{6}$/', $theme_color)) {
    $theme_color = '#3B82F6'; // Default if invalid
}

// --- Update Settings File ---
try {
    // Read the current content
    $currentContent = file_get_contents($settingsFilePath);
    if ($currentContent === false) {
        throw new Exception("Could not read settings file.");
    }

    // Use regex to replace values within the define('STORE_SETTINGS', [...]); block
    // This is fragile and depends heavily on the exact format of settings.php
    $patterns = [
        "/'store_name'\s*=>\s*'.*?',/",
        "/'store_description'\s*=>\s*'.*?',/",
        "/'whatsapp_number'\s*=>\s*'.*?',/",
        "/'currency_symbol'\s*=>\s*'.*?',/",
        "/'theme_color'\s*=>\s*'.*?',/",
        "/'whatsapp_message_template'\s*=>\s*'.*?',/", // Assumes single quotes
        "/'footer_text'\s*=>\s*'.*?',/"             // Assumes single quotes
    ];
    $replacements = [
        "'store_name' => '" . addslashes($store_name) . "',",
        "'store_description' => '" . addslashes($store_description) . "',",
        "'whatsapp_number' => '" . addslashes($whatsapp_number) . "',",
        "'currency_symbol' => '" . addslashes($currency_symbol) . "',",
        "'theme_color' => '" . addslashes($theme_color) . "',",
        "'whatsapp_message_template' => '" . addslashes($whatsapp_message_template) . "',",
        "'footer_text' => '" . addslashes($footer_text) . "',"
    ];

    $newContent = preg_replace($patterns, $replacements, $currentContent);

    // Check if replacement worked (basic check)
    if ($newContent === null || $newContent === $currentContent) {
        // Fallback or more robust parsing might be needed if regex fails
        // For now, assume failure if content didn't change significantly
        // or if preg_replace returned null (error)
        // Note: This might fail if user submits identical values
        if($newContent !== null && $newContent === $currentContent && file_put_contents($settingsFilePath, $newContent) !== false) {
             // Allow saving if content is identical but write succeeds (e.g., user clicked save without changes)
        } else {
             throw new Exception("Failed to update settings content using regex. Check settings.php format.");
        }
    }

    // Write the updated content back to the file
    if (file_put_contents($settingsFilePath, $newContent) === false) {
        throw new Exception("Could not write updated settings to file.");
    }

    // Clear OPcache if enabled, essential for changes to take effect immediately
    if (function_exists('opcache_invalidate')) {
        opcache_invalidate($settingsFilePath, true);
    }

    echo json_encode(['success' => true, 'message' => 'General settings updated successfully!']);

} catch (Exception $e) {
    error_log("Update Settings Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating settings: ' . $e->getMessage()]);
}

exit;
?>