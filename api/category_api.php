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
    
    // If connected to database, ONLY use database data
    if ($db_connected && $conn) {
        // First get all featured parent categories
        $sql = "SELECT 
                    c.id, 
                    c.name, 
                    c.slug, 
                    c.image
                FROM categories c
                WHERE c.featured = 1 AND c.parent_id IS NULL
                ORDER BY c.display_order";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Get subcategories for this parent
                $subSql = "SELECT id, name, slug 
                          FROM categories 
                          WHERE parent_id = ? 
                          ORDER BY display_order, name";
                $stmt = $conn->prepare($subSql);
                $stmt->bind_param('i', $row['id']);
                $stmt->execute();
                $subResult = $stmt->get_result();
                
                $subcategories = [];
                while ($subRow = $subResult->fetch_assoc()) {
                    $subcategories[] = [
                        'id' => (int)$subRow['id'],
                        'name' => $subRow['name'],
                        'slug' => $subRow['slug']
                    ];
                }
                $stmt->close();

                $categories[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'subcategories' => $subcategories,
                    'subcategory_count' => count($subcategories),
                    'image' => $row['image'] ?: '/assets/images/placeholder.png'
                ];
            }
        }
        return $categories; // Return database results (even if empty) when connected
    }
    
    // Only load demo data if NOT connected to database
    $demoDataFile = __DIR__ . '/../config/demo_data.json';
    if (file_exists($demoDataFile)) {
        $demoData = json_decode(file_get_contents($demoDataFile), true);
        if (isset($demoData['categories']) && is_array($demoData['categories'])) {
            foreach ($demoData['categories'] as $category) {
                $categories[] = [
                    'id' => (int)$category['id'],
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'subcategories' => $category['subcategories'] ?? [],
                    'subcategory_count' => count($category['subcategories'] ?? []),
                    'image' => $category['image'] ?: '/assets/images/placeholder.png'
                ];
            }
        }
    }
    
    return $categories;
}

// Fetch the featured categories data immediately when this file is included
$featuredCategories = getFeaturedCategories();

?> 