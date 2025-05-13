<?php
/**
 * Store Settings Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/db.php';

// Include the settings API function (handles DB fetch and JSON defaults)
require_once __DIR__ . '/../api/settings_api.php';

// Ensure database is connected before continuing in admin areas
$isAdminArea = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
if ($isAdminArea && !$db_connected && basename($_SERVER['PHP_SELF']) !== 'index.php') {
    // Redirect to installation page if it exists and we're in admin
    if (file_exists(__DIR__ . '/../install.php')) {
        header('Location: ../install.php');
        exit;
    }
    // If install.php doesn't exist, maybe show a minimal error page or die
    // die('Database connection required for admin area. Please run installation.'); 
}

// Get settings (function now handles defaults internally)
$storeSettings = getStoreSettings();

// Set a default WhatsApp number if not configured
if (empty($storeSettings['whatsapp_number'])) {
    // Add your WhatsApp number here (with country code, no spaces or symbols)
    $storeSettings['whatsapp_number'] = '233542838165'; // Replace with your actual number
}

// Set default social media usernames if not configured
if (empty($storeSettings['facebook_username'])) {
    $storeSettings['facebook_username'] = 'yourstorename'; // Replace with your actual Facebook username
}

if (empty($storeSettings['instagram_username'])) {
    $storeSettings['instagram_username'] = 'yourstorename'; // Replace with your actual Instagram username
}

if (empty($storeSettings['twitter_username'])) {
    $storeSettings['twitter_username'] = 'yourstorename'; // Replace with your actual Twitter username
}

if (empty($storeSettings['tiktok_username'])) {
    $storeSettings['tiktok_username'] = 'yourstorename'; // Replace with your actual TikTok username
}

if (empty($storeSettings['linkedin_username'])) {
    $storeSettings['linkedin_username'] = ''; // Replace with your actual LinkedIn username (optional)
}

if (empty($storeSettings['youtube_username'])) {
    $storeSettings['youtube_username'] = ''; // Replace with your actual YouTube username (optional)
}

// Make settings available globally via constant
define('STORE_SETTINGS', $storeSettings);

// Define global URL constant
define('STORE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']));

// Also define the database connection status for potential checks elsewhere
define('DB_CONNECTED', $db_connected);