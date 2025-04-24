<?php

// Note: settings.php should already be included by the calling file (e.g., index.php)
// Global $conn and $db_connected should be available.

/**
 * Fetches specific store content (like titles, descriptions) from the database or demo data.
 * @param string $key The key of the content to fetch (e.g., 'hero_title').
 * @return string The fetched content value.
 */
function getStoreContent($key) {
    global $conn, $db_connected;
    
    $content = '';
    
    // Try to get content from database
    if ($db_connected && $conn) {
        $sql = "SELECT content_value FROM store_content WHERE content_key = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) { // Check if prepare was successful
            $stmt->bind_param('s', $key);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $content = $row['content_value'];
            }
            $stmt->close(); // Close the statement
        } else {
            error_log("Error preparing statement for store content: " . $conn->error);
        }
    }
    
    // Return demo content from JSON if not found in database
    if (empty($content)) {
        $demoContentValue = '';
        $jsonFilePath = __DIR__ . '/../config/demo_data.json'; // Path relative to this api file
        if (file_exists($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $decodedData = json_decode($jsonContent, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['store_content']) && is_array($decodedData['store_content'])) {
                 $demoContentValue = $decodedData['store_content'][$key] ?? '';
            } else {
                // Log error if JSON is invalid or store_content key is missing
                error_log('Error decoding demo store_content JSON or missing key: ' . json_last_error_msg());
            }
        }
        return $demoContentValue; // Return value from JSON or empty string if not found
    }
    
    return $content; // Return content from database
}

?> 