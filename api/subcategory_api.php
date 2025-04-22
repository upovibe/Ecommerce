<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/settings.php';

function getSubcategories($parentSlug = null) {
    global $conn, $db_connected;
    $subcategories = [];
    $fetchAll = ($parentSlug === null || $parentSlug === 'all');

    // --- Try Database First ---
    if ($db_connected && $conn) {
        if ($fetchAll) {
             // Fetch all subcategories (categories with a parent)
             $sql = "SELECT sub.id, sub.name, sub.slug 
                     FROM categories sub
                     WHERE sub.parent_id IS NOT NULL
                     ORDER BY sub.name";
             $types = '';
             $params = [];
             error_log("[Get Subcategories] Fetching ALL subcategories from DB.");
        } else {
            // Fetch subcategories for a specific parent
             $sql = "SELECT sub.id, sub.name, sub.slug 
                    FROM categories sub
                    JOIN categories parent ON sub.parent_id = parent.id
                    WHERE parent.slug = ? AND sub.parent_id IS NOT NULL
                    ORDER BY sub.display_order, sub.name";
            $types = 's';
            $params = [$parentSlug];
            error_log("[Get Subcategories] Fetching subcategories for parent slug '{$parentSlug}' from DB.");
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
            } else {
                 throw new Exception("Query failed: (" . $stmt->errno . ") " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("[Get Subcategories] DB Error: " . $e->getMessage());
            // Continue to fallback
        }
    }

    // --- Fallback to Demo Data ---
    if (!$fetchAll && empty($subcategories)) {
         error_log("[Get Subcategories] No subcategories found in DB for specific slug '{$parentSlug}' or DB error. Trying demo data.");
         $jsonFilePath = __DIR__ . '/../config/demo_data.json';
         if (file_exists($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $decodedData = json_decode($jsonContent, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['categories']) && is_array($decodedData['categories'])) {
                foreach ($decodedData['categories'] as $parentCat) {
                    if (isset($parentCat['slug']) && $parentCat['slug'] === $parentSlug && isset($parentCat['subcategories']) && is_array($parentCat['subcategories'])) {
                        foreach($parentCat['subcategories'] as $subCat) {
                             $subcategories[] = [
                                 'id' => $subCat['id'] ?? null,
                                 'name' => $subCat['name'] ?? 'Unnamed Subcategory',
                                 'slug' => $subCat['slug'] ?? null
                             ];
                        }
                        error_log("[Get Subcategories] Found " . count($subcategories) . " subcategories in demo data for slug '{$parentSlug}'.");
                        break;
                    }
                }
                 if (empty($subcategories)) {
                     error_log("[Get Subcategories] Parent slug '{$parentSlug}' not found in demo data categories.");
                 }
            } else {
                 error_log("[Get Subcategories] Error decoding demo JSON or missing 'categories' key: " . json_last_error_msg());
            }
         } else {
             error_log("[Get Subcategories] Demo data file not found: " . $jsonFilePath);
         }
     } else if ($fetchAll && empty($subcategories)) {
          error_log("[Get Subcategories] No subcategories found in DB when fetching all. Trying demo data.");
          $jsonFilePath = __DIR__ . '/../config/demo_data.json';
          if (file_exists($jsonFilePath)) {
              $jsonContent = file_get_contents($jsonFilePath);
              $decodedData = json_decode($jsonContent, true);
              if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['categories']) && is_array($decodedData['categories'])) {
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
                   error_log("[Get Subcategories] Found " . count($subcategories) . " total subcategories in demo data.");
              } else {
                   error_log("[Get Subcategories] Error decoding demo JSON or missing 'categories' key: " . json_last_error_msg());
              }
          } else {
               error_log("[Get Subcategories] Demo data file not found: " . $jsonFilePath);
          }
     }

    return $subcategories;
}

// --- Script Execution ---
$parentSlug = isset($_GET['parent_slug']) ? trim($_GET['parent_slug']) : null;

$subcategoriesResult = getSubcategories($parentSlug);

echo json_encode(['success' => true, 'subcategories' => $subcategoriesResult]);

?> 