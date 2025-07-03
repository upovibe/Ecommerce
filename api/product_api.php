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
 * @param float|null $priceMin Optional minimum price to filter by.
 * @param float|null $priceMax Optional maximum price to filter by.
 * @return array List of products.
 */
function getProducts($categorySlug = null, $parentCategorySlug = null, $searchTerm = null, $subcategorySlug = null, $priceMin = null, $priceMax = null) { 
    global $conn, $db_connected;
    $products = [];
    $usingDemoData = !$db_connected || !$conn;

    // If we're in demo mode or database is not connected, load demo data first
    if ($usingDemoData) {
        error_log("[API Demo Mode] Loading demo data...");
        $jsonFilePath = __DIR__ . '/../config/demo_data.json';
        
        if (file_exists($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $decodedData = json_decode($jsonContent, true);
            
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['products']) && is_array($decodedData['products'])) {
                error_log("[API Demo Mode] Successfully loaded demo data with " . count($decodedData['products']) . " products");
                $allDemoProducts = $decodedData['products'];
                $allDemoCategories = $decodedData['categories'] ?? [];
                
                // Start with all products
                $tempFilteredProducts = $allDemoProducts;

                // Apply price filters
                if ($priceMin !== null && is_numeric($priceMin)) {
                    $tempFilteredProducts = array_filter($tempFilteredProducts, fn($p) => 
                        isset($p['price']) && (float)$p['price'] >= (float)$priceMin
                    );
                }
                if ($priceMax !== null && is_numeric($priceMax)) {
                    $tempFilteredProducts = array_filter($tempFilteredProducts, fn($p) => 
                        isset($p['price']) && (float)$p['price'] <= (float)$priceMax
                    );
                }

                // Apply category/subcategory filters
                if ($subcategorySlug !== null) {
                    // Find the subcategory ID from the categories data
                    $targetCategoryId = null;
                    foreach ($allDemoCategories as $parentCat) {
                        if (!empty($parentCat['subcategories'])) {
                            foreach ($parentCat['subcategories'] as $subCat) {
                                if ($subCat['slug'] === $subcategorySlug) {
                                    $targetCategoryId = $subCat['id'];
                                    break 2;
                                }
                            }
                        }
                    }
                    if ($targetCategoryId !== null) {
                        $tempFilteredProducts = array_filter($tempFilteredProducts, fn($p) => 
                            isset($p['category_id']) && (int)$p['category_id'] === (int)$targetCategoryId
                        );
                    }
                } else if ($parentCategorySlug !== null) {
                    // Find all subcategory IDs for this parent category
                    $subcategoryIds = [];
                    foreach ($allDemoCategories as $cat) {
                        if ($cat['slug'] === $parentCategorySlug && !empty($cat['subcategories'])) {
                            $subcategoryIds = array_map(fn($sub) => $sub['id'], $cat['subcategories']);
                            break;
                        }
                    }
                    if (!empty($subcategoryIds)) {
                        $tempFilteredProducts = array_filter($tempFilteredProducts, fn($p) => 
                            isset($p['category_id']) && in_array((int)$p['category_id'], $subcategoryIds)
                        );
                    }
                }

                // Apply search filter
                if ($searchTerm !== null) {
                    $searchTermLower = strtolower($searchTerm);
                    $tempFilteredProducts = array_filter($tempFilteredProducts, function($p) use ($searchTermLower) {
                        return (isset($p['name']) && stripos($p['name'], $searchTermLower) !== false) ||
                               (isset($p['description']) && stripos($p['description'], $searchTermLower) !== false);
                    });
                }

                // Normalize the product data
                $products = array_map(function($p) use ($allDemoCategories) {
                    // Find category info
                    $categoryInfo = ['name' => 'Uncategorized', 'slug' => 'uncategorized'];
                    $parentInfo = null;
                    
                    foreach ($allDemoCategories as $parentCat) {
                        if (!empty($parentCat['subcategories'])) {
                            foreach ($parentCat['subcategories'] as $subCat) {
                                if ((int)$subCat['id'] === (int)$p['category_id']) {
                                    $categoryInfo = $subCat;
                                    $parentInfo = $parentCat;
                                    break 2;
                                }
                            }
                        }
                    }

                    return [
                        'id' => (int)($p['id'] ?? 0),
                        'name' => $p['name'] ?? '',
                        'slug' => $p['slug'] ?? '',
                        'description' => $p['description'] ?? '',
                        'price' => (float)($p['price'] ?? 0),
                        'image' => isset($p['image']) ? (is_array($p['image']) ? $p['image'] : [$p['image']]) : ['/assets/images/product-placeholder.png'],
                        'category_id' => (int)($p['category_id'] ?? 0),
                        'category_name' => $categoryInfo['name'] ?? 'Uncategorized',
                        'category_slug' => $categoryInfo['slug'] ?? 'uncategorized',
                        'parent_category_slug' => $parentInfo['slug'] ?? null,
                        'stock' => (int)($p['stock'] ?? 0),
                        'is_active' => (bool)($p['is_active'] ?? true),
                        'backorder' => (bool)($p['backorder'] ?? false),
                        'original_price' => isset($p['original_price']) ? (float)$p['original_price'] : null,
                        'discount_percentage' => isset($p['discount_percentage']) ? (float)$p['discount_percentage'] : null,
                        'options' => $p['options'] ?? []
                    ];
                }, array_values($tempFilteredProducts));

                error_log("[API Demo Mode] Returning " . count($products) . " filtered products");
                return $products;
            } else {
                error_log("[API Demo Mode] Failed to decode demo data: " . json_last_error_msg());
            }
        } else {
            error_log("[API Demo Mode] Demo data file not found at: " . $jsonFilePath);
        }
        
        // If we get here, demo data loading failed
        error_log("[API Demo Mode] Failed to load demo data, returning empty array");
        return [];
    }

    // Only proceed with database logic if we're not in demo mode
    // --- Try fetching from Database first ---
    if ($db_connected && $conn) {
        // Base SQL (Select all necessary fields)
        $baseSelect = "SELECT p.id, p.name, p.slug, p.description, p.price, p.image, p.category_id, 
                          c.name as category_name, c.slug as category_slug,
                          parent.slug as parent_category_slug,
                          p.stock, p.is_active, p.backorder, p.original_price, p.discount_percentage";

        $sql = "{$baseSelect} 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN categories parent ON c.parent_id = parent.id";
        
        $types = '';
        $params = [];
        $whereClauses = [];

        // Add price filter conditions
        if ($priceMin !== null && is_numeric($priceMin)) {
            $whereClauses[] = "p.price >= ?";
            $types .= 'd';
            $params[] = $priceMin;
        }
        if ($priceMax !== null && is_numeric($priceMax)) {
            $whereClauses[] = "p.price <= ?";
            $types .= 'd';
            $params[] = $priceMax;
        }

        // Determine the primary filter: Subcategory > Parent Category > Direct Category > Search
        if ($subcategorySlug !== null) {
            $whereClauses[] = "c.slug = ?";
            $types .= 's';
            $params[] = $subcategorySlug;
            $whereClauses[] = "p.is_active = 1";
        } else if ($parentCategorySlug !== null) {
            $sql = "SELECT p.id, p.name, p.slug, p.description, p.price, p.image, p.category_id, 
                           sub_cat.name as category_name, sub_cat.slug as category_slug, 
                           p.stock, p.is_active, p.backorder, p.original_price, p.discount_percentage 
                    FROM products p
                    JOIN categories sub_cat ON p.category_id = sub_cat.id
                    JOIN categories parent_cat ON sub_cat.parent_id = parent_cat.id
                    WHERE parent_cat.slug = ? AND p.is_active = 1";
            $types = 's';
            $params = [$parentCategorySlug];

            // Add price conditions to parent category query if needed
            if ($priceMin !== null && is_numeric($priceMin)) {
                $sql .= " AND p.price >= ?";
                $types .= 'd';
                $params[] = $priceMin;
            }
            if ($priceMax !== null && is_numeric($priceMax)) {
                $sql .= " AND p.price <= ?";
                $types .= 'd';
                $params[] = $priceMax;
            }
        } else if ($categorySlug !== null) {
            $whereClauses[] = "c.slug = ?";
            $types .= 's';
            $params[] = $categorySlug;
            $whereClauses[] = "p.is_active = 1";
        } else {
            $whereClauses[] = "p.is_active = 1";
        }

        // Add search term filter
        if ($searchTerm !== null) {
            $searchLike = "%" . $searchTerm . "%";
            $searchClause = "(p.name LIKE ? OR p.description LIKE ?)";
            
            if ($parentCategorySlug !== null && $subcategorySlug === null) {
                $sql .= " AND " . $searchClause;
                $types .= 'ss';
                $params[] = $searchLike;
                $params[] = $searchLike;
            } else {
                $whereClauses[] = $searchClause;
                $types .= 'ss';
                $params[] = $searchLike;
                $params[] = $searchLike;
            }
        }

        // Construct final SQL if not already set by parent filter
        if (!($parentCategorySlug !== null && $subcategorySlug === null)) {
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
                throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
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
                    // Handle multiple images format
                    if (empty($row['image'])) {
                        $row['image'] = ['/assets/images/product-placeholder.png'];
                    } else {
                        try {
                            // Check if image is JSON array or single string
                            if (is_string($row['image']) && (strpos($row['image'], '[') === 0 || strpos($row['image'], '"') === 0)) {
                                $decodedImages = json_decode($row['image'], true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedImages)) {
                                    $row['image'] = $decodedImages;
                                } else {
                                    // Fallback to single image
                                    $row['image'] = [$row['image']];
                                }
                            } else {
                                // Convert single image to array
                                $row['image'] = [$row['image']];
                            }
                        } catch (Exception $e) {
                            error_log("Error processing images for product {$row['id']}: " . $e->getMessage());
                            $row['image'] = [$row['image']]; // Fallback
                        }
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
    }

    // --- Fallback to Demo JSON ONLY if DB is not connected or has error --- 
    if ((!$db_connected || !$conn || isset($db_error)) && empty($products)) {
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

                // Apply price filters to demo data
                if ($priceMin !== null && is_numeric($priceMin)) {
                    $tempFilteredProducts = array_filter($tempFilteredProducts, fn($p) => 
                        isset($p['price']) && (float)$p['price'] >= (float)$priceMin
                    );
                }
                if ($priceMax !== null && is_numeric($priceMax)) {
                    $tempFilteredProducts = array_filter($tempFilteredProducts, fn($p) => 
                        isset($p['price']) && (float)$p['price'] <= (float)$priceMax
                    );
                }

                // Apply other filters (category, search, etc.)
                if ($subcategorySlug !== null) {
                    $targetCategoryId = null;
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

// Get parameters from request
$categorySlugFilter = isset($_GET['category']) ? trim($_GET['category']) : null;
$parentCategorySlugFilter = isset($_GET['parent_category_slug']) ? trim($_GET['parent_category_slug']) : null;
$searchTermFilter = isset($_GET['search']) ? trim($_GET['search']) : null;
$subcategorySlugFilter = isset($_GET['subcategory_slug']) ? trim($_GET['subcategory_slug']) : null;
$priceMinFilter = isset($_GET['price_min']) ? floatval($_GET['price_min']) : null;
$priceMaxFilter = isset($_GET['price_max']) ? floatval($_GET['price_max']) : null;

// Check if we're using demo data
$usingDemoData = !$db_connected || !$conn;

// Fetch products using the function
$productList = getProducts(
    $categorySlugFilter, 
    $parentCategorySlugFilter, 
    $searchTermFilter, 
    $subcategorySlugFilter,
    $priceMinFilter,
    $priceMaxFilter
);

// Log the final product list before encoding
error_log("[API Product End] Product list before JSON encode: " . print_r($productList, true));

// Output the results as JSON with demo data indicator
echo json_encode([
    'products' => $productList,
    'using_demo_data' => $usingDemoData
]);

?> 