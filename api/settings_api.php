<?php

// Note: db.php should already be included by config/settings.php before this file is required.
// Global $conn and $db_connected should be available.

/**
 * Fetches all store settings from the database, falling back to demo_data.json defaults.
 * @return array Associative array of all store settings.
 */
function getStoreSettings() {
    global $conn, $db_connected;
    $settings = [];
    $dbSettings = [];
    
    // 1. Load defaults from JSON first
    $defaults = [];
    $jsonFilePath = __DIR__ . '/../config/demo_data.json';
    if (file_exists($jsonFilePath)) {
        $jsonContent = file_get_contents($jsonFilePath);
        $decodedData = json_decode($jsonContent, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['store_settings']) && is_array($decodedData['store_settings'])) {
            $defaults = $decodedData['store_settings'];
        } else {
            error_log('Error decoding demo store_settings JSON or missing key: ' . json_last_error_msg());
            // Optional: Define minimal hardcoded defaults here if JSON fails critically
        }
    }
    
    // 2. Try to get settings from database
    if ($db_connected && $conn) {
        $sql = "SELECT setting_key, setting_value FROM store_settings";
        $result = $conn->query($sql);
        
        if ($result) { // Check if query was successful
            while ($row = $result->fetch_assoc()) {
                $dbSettings[$row['setting_key']] = $row['setting_value'];
            }
        } else {
             error_log("Error fetching store settings from database: " . $conn->error);
        }
    }
    
    // 3. Merge defaults with database settings (database values override defaults)
    $settings = array_merge($defaults, $dbSettings);
    
    // Special handling for footer text default year
    if (!isset($dbSettings['footer_text']) || empty($dbSettings['footer_text'])) { 
        $settings['footer_text'] = '© ' . date('Y') . ' ' . ($settings['store_name'] ?? 'E-Commerce Store') . '. All rights reserved.';
    }
    
    return $settings;
}

?> 