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

// Function to get store settings from database
function getStoreSettings() {
    global $conn, $db_connected;
    $settings = [];
    
    // Only attempt database query if connection is successful
    if ($db_connected && $conn) {
        $sql = "SELECT setting_key, setting_value FROM store_settings";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }
    }
    
    return $settings;
}

// Default settings if database query fails
$defaults = [
    'store_name' => 'E-Commerce Store',
    'store_description' => 'Your one-stop shop for all your needs',
    'whatsapp_number' => '', // Leave empty or add a placeholder
    'whatsapp_message_template' => 'Hello, I want to inquire about:\n{ITEMS}\nTotal: {CURRENCY}{TOTAL}',
    'currency_symbol' => '₵', // Changed default to Cedis
    'footer_text' => '© ' . date('Y') . ' E-Commerce Store. All rights reserved.',
    'theme_color' => '#3B82F6', // Default Tailwind Blue 500
    'brand_text_color' => '#FFFFFF', // Default White
    'logo_path' => '/assets/images/logo.png' // Default logo path
];

// Ensure database is connected before continuing in admin areas
$isAdminArea = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
if ($isAdminArea && !$db_connected && basename($_SERVER['PHP_SELF']) !== 'index.php') {
    // Redirect to installation page if it exists and we're in admin
    if (file_exists(__DIR__ . '/../install.php')) {
        header('Location: ../install.php');
        exit;
    }
}

// Get settings from database or use defaults
$storeSettings = array_merge($defaults, getStoreSettings());

// Make settings available globally
define('STORE_SETTINGS', $storeSettings);

// Also define the database connection status for backward compatibility
define('DB_CONNECTED', $db_connected);

?> 