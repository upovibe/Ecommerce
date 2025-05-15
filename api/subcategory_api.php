<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/settings.php';

function getSubcategories($parentSlug = null) {
    global $conn, $db_connected;
    $subcategories = [];
    $fetchAll = ($parentSlug === null || $parentSlug === 'all');

    // If connected to database, ONLY use database data
    if ($db_connected && $conn) {
        if ($fetchAll) {
             // Fetch all subcategories (categories with a parent)
             $sql = "SELECT sub.id, sub.name, sub.slug 
                     FROM categories sub
                     WHERE sub.parent_id IS NOT NULL
                     ORDER BY sub.name";
             $types = '';
             $params = [];
        } else {
            // Fetch subcategories for a specific parent
             $sql = "SELECT sub.id, sub.name, sub.slug 
                    FROM categories sub
                    JOIN categories parent ON sub.parent_id = parent.id
                    WHERE parent.slug = ? AND sub.parent_id IS NOT NULL
                    ORDER BY sub.display_order, sub.name";
            $types = 's';
            $params = [$parentSlug];
        }
        
        try {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            if (!empty($params)) {
                 $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $subcategories[] = [
                        'id' => (int)$row['id'],
                        'name' => $row['name'],
                        'slug' => $row['slug']
                    ];
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("[Get Subcategories] DB Error: " . $e->getMessage());
        }
        
        return $subcategories; // Return database results (even if empty) when connected
    }

    // Only use demo data if NOT connected to database
    $jsonFilePath = __DIR__ . '/../config/demo_data.json';
    if (file_exists($jsonFilePath)) {
        $jsonContent = file_get_contents($jsonFilePath);
        $decodedData = json_decode($jsonContent, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['categories']) && is_array($decodedData['categories'])) {
            if ($fetchAll) {
                // Get all subcategories from demo data
                foreach ($decodedData['categories'] as $parentCat) {
                    if (isset($parentCat['subcategories']) && is_array($parentCat['subcategories'])) {
                        foreach($parentCat['subcategories'] as $subCat) {
                            $subcategories[] = [
                                'id' => $subCat['id'] ?? null,
                                'name' => $subCat['name'] ?? 'Unnamed Subcategory',
                                'slug' => $subCat['slug'] ?? null
                            ];
                        }
                    }
                }
            } else {
                // Get subcategories for specific parent from demo data
                foreach ($decodedData['categories'] as $parentCat) {
                    if (isset($parentCat['slug']) && $parentCat['slug'] === $parentSlug && 
                        isset($parentCat['subcategories']) && is_array($parentCat['subcategories'])) {
                        foreach($parentCat['subcategories'] as $subCat) {
                            $subcategories[] = [
                                'id' => $subCat['id'] ?? null,
                                'name' => $subCat['name'] ?? 'Unnamed Subcategory',
                                'slug' => $subCat['slug'] ?? null
                            ];
                        }
                        break;
                    }
                }
            }
        }
    }

    return $subcategories;
}

// --- Script Execution ---
$parentSlug = isset($_GET['parent_slug']) ? trim($_GET['parent_slug']) : null;
$subcategoriesResult = getSubcategories($parentSlug);
echo json_encode(['success' => true, 'subcategories' => $subcategoriesResult]);
?> 