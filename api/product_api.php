<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/settings.php'; // Essential for standalone API

/**
 * Fetches products from the database, optionally filtered by direct category slug
 * or by parent category slug (fetching products from all subcategories).
 * Includes fallback to demo data.
 *
 * @param string|null $categorySlug Optional direct category slug to filter by.
 * @param string|null $parentCategorySlug Optional parent category slug to filter by subcategories.
 * @param string|null $searchTerm Optional search term to filter by name/description.
 * @return array List of products.
 */
function getProducts($categorySlug = null, $parentCategorySlug = null, $searchTerm = null) { 
    global $conn, $db_connected;
    $products = [];
    $usingDemoData = false;

    // --- Try fetching from Database first ---
    if ($db_connected && $conn) {
        // Base SQL
        $sql = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, c.name as category_name, c.slug as category_slug 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id";
        
        $types = '';
        $params = [];
        $whereClauses = [];

        // Filter by direct category slug
        if ($categorySlug !== null && $parentCategorySlug === null) {
            $whereClauses[] = "c.slug = ?";
            $types .= 's';
            $params[] = $categorySlug;
        }
        // Filter by parent category slug (products in subcategories)
        else if ($parentCategorySlug !== null && $categorySlug === null) {
            // This condition changes the base SQL query structure, so it's handled separately
            // The WHERE clause `parent_cat.slug = ?` is added directly in the SQL assignment below
        }

        // Add search term filter (applies to both direct and parent category filtering if needed)
        if ($searchTerm !== null) {
            $searchLike = "%" . $searchTerm . "%";
            // Add parenthesis for correct precedence if other clauses exist
            $searchClause = "(p.name LIKE ? OR p.description LIKE ?)"; 
            $whereClauses[] = $searchClause;
            $types .= 'ss';
            $params[] = $searchLike;
            $params[] = $searchLike;
        }

        // Note: Add handling if both slugs are provided? Currently prioritizes parent.

        // --- Construct Final SQL --- 

        // If filtering by parent slug, the base query and initial params are different
        if ($parentCategorySlug !== null && $categorySlug === null) {
            $sql = "SELECT p.id, p.name, p.description, p.price, p.image, p.category_id, sub_cat.name as category_name, sub_cat.slug as category_slug 
                    FROM products p
                    JOIN categories sub_cat ON p.category_id = sub_cat.id
                    JOIN categories parent_cat ON sub_cat.parent_id = parent_cat.id
                    WHERE parent_cat.slug = ?";
            $types = 's'; // Start with string type for parent slug
            $parentSlugParam = [$parentCategorySlug]; // Initial param is parent slug

            // Now, append any additional WHERE clauses (like search)
            if (!empty($whereClauses)) {
                $sql .= " AND (" . implode(' AND ', $whereClauses) . ")"; // Add remaining clauses
                // Prepend the parent slug param type to the types string
                $types .= 's'; // This seems wrong, types should be appended based on the clause added
                // Combine params: parent slug first, then others
                $params = array_merge($parentSlugParam, $params);
            } else {
                 $params = $parentSlugParam; // Only parent slug param
            }
           
            // ** Correction for types string when merging parent and search **
            $finalTypes = 's'; // Start with parent slug type
            if ($searchTerm !== null) { $finalTypes .= 'ss'; } // Add types for search
            $types = $finalTypes; 

        } else {
             // Base SQL is used (fetching all or filtering by direct slug)
             // Append WHERE clause if needed
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(' AND ', $whereClauses);
            }
        }

        $sql .= " ORDER BY p.name ASC";

        try {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("[API Product Fetch] Prepare failed: (" . $conn->errno . ") " . $conn->error . " SQL: " . $sql);
            } else {
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        if (empty($row['image'])) {
                            $row['image'] = '/assets/images/product-placeholder.png';
                        }
                        $row['id'] = (int)$row['id'];
                        $row['price'] = (float)$row['price'];
                        $row['category_id'] = isset($row['category_id']) ? (int)$row['category_id'] : null;
                        $products[] = $row;
                    }
                    error_log("[API Product Fetch] Found " . count($products) . " products for filter (Slug: '$categorySlug', ParentSlug: '$parentCategorySlug').");
                } else {
                    error_log("[API Product Fetch] Query failed: (" . $stmt->errno . ") " . $stmt->error);
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log("[API Product Fetch] Error: " . $e->getMessage());
        }
    }

    // --- Fallback to Demo JSON if DB fetch yielded no results ---
    if (empty($products)) {
        error_log("[API Demo Product Fetch] No products found in DB or DB error, attempting to load demo data.");
        $jsonFilePath = __DIR__ . '/../config/demo_data.json';
        if (file_exists($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $decodedData = json_decode($jsonContent, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['products']) && is_array($decodedData['products']) && isset($decodedData['categories']) && is_array($decodedData['categories'])) {
                error_log("[API Demo Product Fetch] Successfully loaded demo product and category data.");
                $allDemoProducts = $decodedData['products'];
                $allDemoCategories = $decodedData['categories'];
                $usingDemoData = true;

                $targetCategoryIds = [];
                $tempFilteredProducts = []; // Start with all demo products if no category filter

                // Filter by direct category slug
                if ($categorySlug !== null && $parentCategorySlug === null) {
                    foreach ($allDemoCategories as $cat) {
                        if (isset($cat['slug']) && $cat['slug'] === $categorySlug) {
                            $targetCategoryIds[] = $cat['id'] ?? null;
                            // Assuming direct slug means only one category
                            break; 
                        }
                    }
                     error_log("[API Demo Product Fetch] Filtering by direct slug '$categorySlug'. Target IDs: " . implode(', ', $targetCategoryIds));
                }
                // Filter by parent category slug
                else if ($parentCategorySlug !== null && $categorySlug === null) {
                    $parentCategoryId = null;
                    foreach ($allDemoCategories as $cat) {
                         if (isset($cat['slug']) && $cat['slug'] === $parentCategorySlug) {
                            $parentCategoryId = $cat['id'] ?? null;
                            // Assuming parent slug maps to a parent category, get its subcategory IDs
                            if (isset($cat['subcategories']) && is_array($cat['subcategories'])) {
                                foreach ($cat['subcategories'] as $subCat) {
                                     if (isset($subCat['id'])) {
                                         $targetCategoryIds[] = $subCat['id'];
                                     }
                                }
                            }
                            break;
                        }
                    }
                     error_log("[API Demo Product Fetch] Filtering by parent slug '$parentCategorySlug'. Found parent ID: $parentCategoryId. Target SubCategory IDs: " . implode(', ', $targetCategoryIds));
                }

                // Filter products based on target category IDs
                if (!empty($targetCategoryIds)) {
                    $filteredProducts = [];
                    $validTargetIds = array_filter($targetCategoryIds, fn($id) => $id !== null); // Remove nulls
                    if (!empty($validTargetIds)) {
                        foreach ($allDemoProducts as $product) {
                            if (isset($product['category_id']) && in_array((int)$product['category_id'], $validTargetIds, true)) {
                                $filteredProducts[] = $product;
                            }
                        }
                         $products = $filteredProducts;
                          error_log("[API Demo Product Fetch] Filtered demo products. Found " . count($products) . ".");
                    } else {
                         error_log("[API Demo Product Fetch] No valid target category IDs found for filtering.");
                         $products = [];
                    }
                } else if ($categorySlug !== null || $parentCategorySlug !== null) {
                    // Category filter was applied
                    if (!empty($validTargetIds)) {
                         foreach ($allDemoProducts as $product) {
                             if (isset($product['category_id']) && in_array((int)$product['category_id'], $validTargetIds, true)) {
                                 $tempFilteredProducts[] = $product;
                             }
                         }
                          error_log("[API Demo Product Fetch] After category filter: " . count($tempFilteredProducts) . " products.");
                     } else {
                          // Category filter applied but no valid IDs found
                         error_log("[API Demo Product Fetch] No valid target category IDs found for filtering.");
                          $tempFilteredProducts = []; // Start with empty set for search
                     }
                } else {
                     // No category filter, start with all products for potential search filter
                     $tempFilteredProducts = $allDemoProducts;
                }

                // Determine which products to start filtering for search
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
                     $products = $searchFilteredProducts; // Final list after search
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

    return $products;
}

// --- API Logic (Executing the Request) ---

// Determine category filter based on GET parameters
$categorySlugFilter = isset($_GET['category_slug']) ? trim($_GET['category_slug']) : null;
$parentCategorySlugFilter = isset($_GET['parent_category_slug']) ? trim($_GET['parent_category_slug']) : null;
$searchTermFilter = isset($_GET['search']) ? trim($_GET['search']) : null; // Read search param

// Fetch products using the function
$productList = getProducts($categorySlugFilter, $parentCategorySlugFilter, $searchTermFilter);

// Output the results as JSON
echo json_encode($productList);

?> 