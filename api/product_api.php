<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/settings.php'; // Essential for standalone API

// --- Log Raw GET Parameters ---
error_log("[API Product Start] GET Params: " . print_r($_GET, true));

/**
 * Fetches products from the database, optionally filtered by direct category slug
 * or by parent category slug (fetching products from all subcategories).
 * Includes fallback to demo data.
 *
 * @param string|null $categorySlug Optional direct category slug to filter by.
 * @param string|null $parentCategorySlug Optional parent category slug to filter by subcategories.
 * @param string|null $searchTerm Optional search term to filter by name/description.
 * @param string|null $subcategorySlug Optional subcategory slug to filter by.
 * @return array List of products.
 */
function getProducts($categorySlug = null, $parentCategorySlug = null, $searchTerm = null, $subcategorySlug = null) { 
    global $conn, $db_connected;
    $products = [];
    $usingDemoData = false;

    // --- Try fetching from Database first ---
    if ($db_connected && $conn) {
        // Base SQL (Select all necessary fields)
        $baseSelect = "SELECT p.id, p.name, p.slug, p.description, p.price, p.image, p.category_id, 
                          c.name as category_name, c.slug as category_slug, 
                          p.stock, p.is_active, p.backorder, p.original_price, p.discount_percentage"; // Restored fields

        $sql = "{$baseSelect} 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id";
        
        $types = '';
        $params = [];
        $whereClauses = [];

        // Determine the primary filter: Subcategory > Parent Category > Direct Category (if needed) > Search
        // Note: $categorySlug isn't currently used by the JS, but kept for potential future use.

        if ($subcategorySlug !== null) {
            // Priority 1: Filter by subcategory slug
             $whereClauses[] = "c.slug = ?";
             $types .= 's';
             $params[] = $subcategorySlug;
             $whereClauses[] = "p.is_active = 1"; // Add active filter
             error_log("[API Product Fetch] Filtering by SUBCATEGORY slug: '{$subcategorySlug}'");

        } else if ($parentCategorySlug !== null) {
            // Priority 2: Filter by parent category slug (products in its subcategories)
            // Ensure ALL fields are selected here too (EXCEPT the non-existent p.options)
            $sql = "SELECT p.id, p.name, p.slug, p.description, p.price, p.image, p.category_id, 
                           sub_cat.name as category_name, sub_cat.slug as category_slug, 
                           p.stock, p.is_active, p.backorder, p.original_price, p.discount_percentage 
                    FROM products p
                    JOIN categories sub_cat ON p.category_id = sub_cat.id
                    JOIN categories parent_cat ON sub_cat.parent_id = parent_cat.id
                    WHERE parent_cat.slug = ? AND p.is_active = 1"; // Removed p.options
            $types = 's'; 
            $params = [$parentCategorySlug];
            error_log("[API Product Fetch] Filtering by PARENT slug: '{$parentCategorySlug}'");
            // Note: Search clause will be appended later if present
        
        } else if ($categorySlug !== null) {
             // Priority 3: Filter by direct category slug (if not filtering by parent/sub)
             $whereClauses[] = "c.slug = ?";
             $types .= 's';
             $params[] = $categorySlug;
             $whereClauses[] = "p.is_active = 1"; // Add active filter
             error_log("[API Product Fetch] Filtering by DIRECT category slug: '{$categorySlug}'");
        } else {
            // No specific category filter, but still filter by active status
            $whereClauses[] = "p.is_active = 1";
            error_log("[API Product Fetch] No category filter, applying global active filter.");
        }

        // Add search term filter (applies regardless of category filter)
        if ($searchTerm !== null) {
            $searchLike = "%" . $searchTerm . "%";
            $searchClause = "(p.name LIKE ? OR p.description LIKE ?)"; 
            
            if ($parentCategorySlug !== null && $subcategorySlug === null) {
                 // If filtering by parent, append search to the specific parent SQL
                 $sql .= " AND " . $searchClause; // Append directly
                 $types .= 'ss';
                 $params[] = $searchLike;
                 $params[] = $searchLike;
            } else {
                // For subcategory, direct category, or no category filter, add to $whereClauses
                 $whereClauses[] = $searchClause;
                 $types .= 'ss';
                 $params[] = $searchLike;
                 $params[] = $searchLike;
            }
             error_log("[API Product Fetch] Adding SEARCH filter: '{$searchTerm}'");
        }

        // --- Construct Final SQL (if not already set by parent filter) --- 
        if (!($parentCategorySlug !== null && $subcategorySlug === null)) { // Check if base SQL needs WHERE clauses appended
             if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
             }
        }

        $sql .= " ORDER BY p.name ASC";
         error_log("[API Product Fetch] Final SQL: " . $sql);
         error_log("[API Product Fetch] Final Params: " . print_r($params, true));
         error_log("[API Product Fetch] Final Types: " . $types);

        try {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                 $db_error = "Prepare failed: (" . $conn->errno . ") " . $conn->error . " SQL: " . $sql;
                 error_log("[API Product Fetch] DB ERROR: " . $db_error);
                 // Consider returning an error JSON here? 
                 // For now, logging and proceeding to demo data fallback.
            } else {
                if (!empty($params)) {
                    // Check if number of params matches types
                    if (strlen($types) !== count($params)) {
                         error_log("[API Product Fetch] BIND ERROR: Type count (".strlen($types).") doesn't match param count (".count($params).").");
                         // Handle error - maybe throw exception or set products to empty
                    } else {
                         error_log("[API Product Fetch] Attempting to bind params...");
                         $stmt->bind_param($types, ...$params);
                         error_log("[API Product Fetch] Bind successful.");
                    }
                }
                 error_log("[API Product Fetch] Executing statement...");
                $stmt->execute();
                $result = $stmt->get_result();
                 error_log("[API Product Fetch] Statement executed.");

                // Prepare statement for fetching options (prepare ONCE outside the loop)
                $optionsSql = "SELECT option_name, option_values FROM product_options WHERE product_id = ?";
                $optionsStmt = $conn->prepare($optionsSql);

                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        if (empty($row['image'])) {
                            $row['image'] = '/assets/images/product-placeholder.png';
                        }

                        // Fetch options for this product
                        $productId = $row['id'];
                        $productOptions = [];
                        if ($optionsStmt) {
                            $optionsStmt->bind_param('i', $productId);
                            $optionsStmt->execute();
                            $optionsResult = $optionsStmt->get_result();
                            if ($optionsResult) {
                                while ($optionRow = $optionsResult->fetch_assoc()) {
                                    $optionName = $optionRow['option_name'];
                                    $optionValuesJson = $optionRow['option_values'];
                                    $decodedValues = json_decode($optionValuesJson, true);

                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedValues)) {
                                        $productOptions[$optionName] = $decodedValues;
                                    } else {
                                        error_log("[API Product Options] JSON Decode Error for product ID {$productId}, option '{$optionName}': " . json_last_error_msg());
                                    }
                                }
                                $optionsResult->free(); // Free result set
                            } else {
                                 error_log("[API Product Options] Error fetching options for product ID {$productId}: (" . $optionsStmt->errno . ") " . $optionsStmt->error);
                            }
                            $optionsStmt->reset(); // Reset statement for next iteration if needed (though bind_param should handle it)
                        } else {
                             error_log("[API Product Options] Failed to prepare options statement: (" . $conn->errno . ") " . $conn->error);
                        }
                        $row['options'] = $productOptions; // Assign the fetched & structured options

                        $row['id'] = (int)$row['id'];
                        $row['price'] = (float)$row['price'];
                        // Ensure numeric types are cast correctly
                        $row['original_price'] = isset($row['original_price']) ? (float)$row['original_price'] : null;
                        $row['discount_percentage'] = isset($row['discount_percentage']) ? (float)$row['discount_percentage'] : null;
                        $row['stock'] = isset($row['stock']) ? (int)$row['stock'] : 0;
                        $row['is_active'] = isset($row['is_active']) ? (bool)$row['is_active'] : false;
                        $row['backorder'] = isset($row['backorder']) ? (bool)$row['backorder'] : false;
                        $row['category_id'] = isset($row['category_id']) ? (int)$row['category_id'] : null;
                        $products[] = $row;
                    }
                    error_log("[API Product Fetch] DB Found " . count($products) . " products for filter (SubSlug: '{$subcategorySlug}', ParentSlug: '{$parentCategorySlug}', Search: '{$searchTerm}').");
                } else {
                    $db_error = "Query execution failed or returned no result object: (" . $stmt->errno . ") " . $stmt->error;
                    error_log("[API Product Fetch] DB ERROR: " . $db_error);
                }
                $stmt->close();
                // Close the options statement AFTER the loop
                if ($optionsStmt) {
                    $optionsStmt->close();
                }
            }
        } catch (Exception $e) {
            error_log("[API Product Fetch] Error: " . $e->getMessage());
        }
    }

    // --- Log reason before potentially falling back to Demo JSON --- 
    $fallbackReason = "";
    if (!$db_connected || !$conn) {
        $fallbackReason = "Database not connected.";
    } elseif (isset($db_error)) { // Check if a DB error occurred during query
        $fallbackReason = "Database query error: " . $db_error;
    } elseif (empty($products)) {
        $fallbackReason = "Database query returned 0 active products matching filters.";
    }

    // --- Fallback to Demo JSON if DB fetch yielded no results --- 
    
    if (empty($products)) {
        error_log("[API Demo Trigger] {$fallbackReason} Attempting to load demo data."); // Use the determined reason
        $jsonFilePath = __DIR__ . '/../config/demo_data.json';
        if (file_exists($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $decodedData = json_decode($jsonContent, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['products']) && is_array($decodedData['products']) && isset($decodedData['categories']) && is_array($decodedData['categories'])) {
                error_log("[API Demo Product Fetch] Successfully loaded demo product and category data.");
                $allDemoProducts = $decodedData['products'];
                $allDemoCategories = $decodedData['categories'];
                $usingDemoData = true;

                $tempFilteredProducts = $allDemoProducts; // Start with all for potential filtering

                // Apply filters in order: Active > Subcategory > Parent > Search
                
                // Filter by active status first
                $activeProducts = array_filter($allDemoProducts, fn($p) => isset($p['is_active']) && $p['is_active'] === true);
                error_log("[API Demo Product Fetch] Filtered by active status. Count: " . count($activeProducts));
                $tempFilteredProducts = $activeProducts; // Start filtering from active products

                if ($subcategorySlug !== null) {
                    $targetCategoryId = null;
                    // Find the ID for the subcategory slug (need to look through all cats/subcats)
                    foreach ($allDemoCategories as $parentCat) {
                        if (isset($parentCat['subcategories']) && is_array($parentCat['subcategories'])) {
                            foreach ($parentCat['subcategories'] as $subCat) {
                                if (isset($subCat['slug']) && $subCat['slug'] === $subcategorySlug) {
                                    $targetCategoryId = $subCat['id'] ?? null;
                                    break 2; // Found it, exit both loops
                                }
                            }
                        }
                    }
                    if ($targetCategoryId !== null) {
                         $tempFilteredProducts = array_filter($tempFilteredProducts, fn($p) => isset($p['category_id']) && (int)$p['category_id'] === (int)$targetCategoryId);
                         error_log("[API Demo Product Fetch] Filtered by subcategory slug '$subcategorySlug'. Found ID: $targetCategoryId. Count: " . count($tempFilteredProducts));
                    } else {
                         error_log("[API Demo Product Fetch] Subcategory slug '$subcategorySlug' not found in demo data.");
                         $tempFilteredProducts = [];
                    }
                } else if ($parentCategorySlug !== null) {
                    $targetSubCategoryIds = [];
                    foreach ($allDemoCategories as $cat) {
                        if (isset($cat['slug']) && $cat['slug'] === $parentCategorySlug && isset($cat['subcategories']) && is_array($cat['subcategories'])) {
                             $targetSubCategoryIds = array_map(fn($sub) => $sub['id'] ?? null, $cat['subcategories']);
                             $targetSubCategoryIds = array_filter($targetSubCategoryIds); // Remove nulls
                             break;
                        }
                    }
                    if (!empty($targetSubCategoryIds)) {
                         $tempFilteredProducts = array_filter($tempFilteredProducts, fn($p) => isset($p['category_id']) && in_array((int)$p['category_id'], $targetSubCategoryIds, true));
                         error_log("[API Demo Product Fetch] Filtered by parent slug '$parentCategorySlug'. Target SubIDs: " . implode(', ', $targetSubCategoryIds) . ". Count: " . count($tempFilteredProducts));
                    } else {
                         error_log("[API Demo Product Fetch] Parent slug '$parentCategorySlug' not found or has no subcategories in demo data.");
                         $tempFilteredProducts = [];
                    }
                }

                // Apply search filter on top of category results
                if ($searchTerm !== null) {
                     $searchFilteredProducts = [];
                     $searchTermLower = strtolower($searchTerm);
                     foreach ($tempFilteredProducts as $product) {
                         $nameMatch = isset($product['name']) && stripos($product['name'], $searchTermLower) !== false;
                         $descMatch = isset($product['description']) && stripos($product['description'], $searchTermLower) !== false;
                         if ($nameMatch || $descMatch) {
                             $searchFilteredProducts[] = $product;
                         }
                     }
                     $products = $searchFilteredProducts; 
                     error_log("[API Demo Product Fetch] After search filter ('$searchTerm'): " . count($products) . " products.");
                } else {
                     // No search term, use the result from category filtering
                     $products = $tempFilteredProducts;
                }
            } else {
                error_log("[API Demo Product Fetch] Error decoding demo JSON or missing 'products'/'categories' key: " . json_last_error_msg());
                $products = [];
            }
        } else {
            error_log("[API Demo Product Fetch] Demo data JSON file not found: " . $jsonFilePath);
            $products = [];
        }
    }
    

    // Ensure the final result is a zero-indexed array for correct JSON encoding
    return array_values($products);
}

// --- API Logic (Executing the Request) ---

// Determine category filter based on GET parameters
$categorySlugFilter = isset($_GET['category_slug']) ? trim($_GET['category_slug']) : null;
$parentCategorySlugFilter = isset($_GET['parent_category_slug']) ? trim($_GET['parent_category_slug']) : null;
$searchTermFilter = isset($_GET['search']) ? trim($_GET['search']) : null; // Read search param
$subcategorySlugFilter = isset($_GET['subcategory_slug']) ? trim($_GET['subcategory_slug']) : null; // Read subcategory slug

// Fetch products using the function
$productList = getProducts($categorySlugFilter, $parentCategorySlugFilter, $searchTermFilter, $subcategorySlugFilter);

// Log the final product list before encoding
error_log("[API Product End] Product list before JSON encode: " . print_r($productList, true));

// Output the results as JSON
echo json_encode($productList);

?> 