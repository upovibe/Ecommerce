<?php

// Note: settings.php should already be included by the calling file (e.g., index.php)
// Global $conn and $db_connected should be available.

/**
 * Fetches featured categories from the database or demo data.
 * @return array List of featured categories.
 */
function getFeaturedCategories() {
    global $conn, $db_connected;
    
    $categories = [];
    
    // Try to get categories from database
    if ($db_connected && $conn) {
        $sql = "SELECT 
                    c.id, 
                    c.name, 
                    c.image, 
                    (SELECT COUNT(*) FROM categories sub WHERE sub.parent_id = c.id) as subcategory_count 
                FROM categories c
                WHERE c.featured = 1 AND c.parent_id IS NULL
                ORDER BY c.display_order";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'subcategory_count' => (int)$row['subcategory_count'],
                    'image' => $row['image']
                ];
            }
        }
    }
    
    // If no categories found in database, try loading from demo JSON file
    if (empty($categories)) {
        $jsonFilePath = __DIR__ . '/../config/demo_data.json'; // Path relative to this api file
        if (file_exists($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $decodedData = json_decode($jsonContent, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['categories']) && is_array($decodedData['categories'])) {
                $categories = $decodedData['categories']; 
                foreach ($categories as &$category) {
                     if (isset($category['subcategories']) && is_array($category['subcategories'])) {
                         $category['subcategory_count'] = count($category['subcategories']);
                     } else {
                         $category['subcategory_count'] = 0;
                     }
                     $category['id'] = $category['id'] ?? null; 
                     $category['name'] = $category['name'] ?? 'Unnamed Category';
                     $category['image'] = $category['image'] ?? null; 
                }
                unset($category); 
            } else {
                error_log('Error decoding demo categories JSON or missing "categories" key: ' . json_last_error_msg());
                $categories = []; 
            }
        } else {
             error_log('Demo data JSON file not found: ' . $jsonFilePath);
             $categories = [];
        }
    }
    
    // Ensure essential keys exist and provide defaults
    foreach ($categories as &$category) {
        if (!is_array($category)) {
            error_log('Invalid category data encountered: ' . print_r($category, true));
            continue; 
        }
        if (empty($category['image'])) {
            $category['image'] = '/assets/images/placeholder.png'; // Default placeholder
        }
        $category['subcategory_count'] = $category['subcategory_count'] ?? 0;
    }
    unset($category);
    
    return $categories;
}

// Fetch the featured categories data immediately when this file is included
$featuredCategories = getFeaturedCategories();

?> 