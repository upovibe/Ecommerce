<?php
session_start();
require_once '../../config/settings.php';
require_once './helpers.php'; // Include helpers

header('Content-Type: application/json');
// Add cache control headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// --- Data Retrieval & Validation ---
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$name = trim($_POST['product_name'] ?? '');
// $slug_input = trim($_POST['product_slug'] ?? ''); // Only if slug is editable
$description = trim($_POST['product_description'] ?? '');
$price = filter_input(INPUT_POST, 'product_price', FILTER_VALIDATE_FLOAT);
$stock = filter_input(INPUT_POST, 'product_stock', FILTER_VALIDATE_INT);
$category_id = empty($_POST['category_id']) ? null : (int)$_POST['category_id'];
$featured = isset($_POST['featured']) ? 1 : 0;
$product_options_json = $_POST['product_options'] ?? null; // JSON string from frontend
$discount_percentage_input = $_POST['discount_percentage'] ?? null;
$is_active = ($_POST['is_active'] ?? '0') === '1' ? 1 : 0;       // Expect '1' or '0' string
$allow_backorder = ($_POST['backorder'] ?? '0') === '1' ? 1 : 0; // Expect '1' or '0' string

// Flags (optional, depends on frontend implementation)
$remove_discount = isset($_POST['remove_discount']); 
$remove_options = isset($_POST['remove_options']);

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Product ID.']);
    exit;
}
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Product Name is required.']);
    exit;
}
if ($price === false || $price < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Price specified.']);
    exit;
}
if ($stock === false || $stock < 0) {
    $stock = 0; 
}
if ($category_id === false || $category_id <= 0) {
    $category_id = null; 
}

// --- Start Transaction ---
if ($db_connected && $conn) {
    $conn->begin_transaction();

    try {
        // --- Fetch Current Product Data (for image deletion and potentially slug check) ---
        $currentProductSql = "SELECT image, slug, name FROM products WHERE id = ?";
        $currentStmt = $conn->prepare($currentProductSql);
        $currentStmt->bind_param("i", $product_id);
        $currentStmt->execute();
        $currentResult = $currentStmt->get_result();
        $currentProduct = $currentResult->fetch_assoc();
        $currentStmt->close();

        if (!$currentProduct) {
            throw new Exception("Product not found.");
        }

        // --- Handle Image Upload/Update ---
        $imagePath = $currentProduct['image']; // Keep current image by default
        $oldImagePath = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            // If there's an existing image, extract its filename to reuse
            $existingFileName = null;
            if ($imagePath) {
                $pathInfo = pathinfo($imagePath);
                $existingFileName = $pathInfo['filename']; // Without extension
            }
            
            // Pass the existing filename to reuse for the new upload
            $newImagePath = handleImageUpload($_FILES['product_image'], '/assets/images/products/', $existingFileName ? $existingFileName : 'image');
            
            if ($newImagePath === null) {
                throw new Exception('Image upload failed. Check file type, size (max 4MB), and permissions.');
            }
            
            // If the paths are different (despite trying to reuse the filename), clean up the old file
            if ($imagePath && $imagePath !== $newImagePath && file_exists("../.." . $imagePath)) {
                @unlink("../.." . $imagePath);
            }
            
            $imagePath = $newImagePath; // Update image path to the new one
        }

        // --- Slug Generation (if name changed, consider updating slug) ---
        // Simple approach: Update slug only if name changed. More complex logic could preserve old slug or check uniqueness.
        $slug = $currentProduct['slug'];
        if ($name !== $currentProduct['name']) {
             $slug = generateSlug($name); 
             // Add check for slug uniqueness against other products if required by your logic
        }

        // --- Calculate Discount/Original Price ---
        $original_price = null;
        $discount_percentage = null;
        if (!$remove_discount && $discount_percentage_input !== null && is_numeric($discount_percentage_input)) {
            $discount_percentage_float = (float)$discount_percentage_input;
            if ($discount_percentage_float > 0 && $discount_percentage_float <= 100) {
                $divisor = 1 - ($discount_percentage_float / 100);
                if ($divisor > 0) { 
                    $original_price = round($price / $divisor, 2);
                    $discount_percentage = $discount_percentage_float;
                }
            }
        }

        // --- Update Products Table ---
        $updateSql = "UPDATE products SET 
                        name = ?, slug = ?, description = ?, price = ?, original_price = ?, 
                        discount_percentage = ?, image = ?, category_id = ?, stock = ?, featured = ?, 
                        is_active = ?, backorder = ?, 
                        updated_at = NOW() 
                      WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        if (!$updateStmt) {
             throw new Exception("Database error preparing product update: " . $conn->error);
        }
        // Debug Log: Check values just before binding
        error_log("[Update Product Debug] ID: {$product_id}, Name: {$name}, Active: {$is_active}, Backorder: {$allow_backorder}"); 

        $updateStmt->bind_param("sssdddsiiiiii", 
            $name, $slug, $description, $price, $original_price, $discount_percentage, 
            $imagePath, $category_id, $stock, $featured, 
            $is_active, $allow_backorder,
            $product_id
        );

        if (!$updateStmt->execute()) {
            if ($conn->errno === 1062) { // Duplicate slug check
                throw new Exception("A product with this slug already exists. Please use a unique slug.");
            } else {
                 throw new Exception("Database error executing product update: " . $updateStmt->error);
            }
        }
        $updateStmt->close();

        // --- Handle Product Options (Delete existing then Insert new) ---
        // Delete existing options first
        $deleteOptionsSql = "DELETE FROM product_options WHERE product_id = ?";
        $deleteStmt = $conn->prepare($deleteOptionsSql);
        $deleteStmt->bind_param("i", $product_id);
        if (!$deleteStmt->execute()) {
             throw new Exception("Database error deleting old options: " . $deleteStmt->error);
        }
        $deleteStmt->close();
        
        // Insert new options if provided and not explicitly removing
        if (!$remove_options && $product_options_json) {
            $options = json_decode($product_options_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($options)) {
                $insertOptionSql = "INSERT INTO product_options (product_id, option_name, option_values) VALUES (?, ?, ?)";
                $insertStmt = $conn->prepare($insertOptionSql);
                if (!$insertStmt) {
                    throw new Exception("Database error preparing option insert: " . $conn->error);
                }

                foreach ($options as $option) {
                    // Use isset for safety before accessing array keys
                    if (isset($option['name'], $option['values']) && $option['name'] !== '' && $option['values'] !== '') {
                        // Ensure values is treated as a string before explode
                        $optionValuesStr = is_array($option['values']) ? implode(',', $option['values']) : (string)$option['values']; 
                        $optionValuesArray = array_map('trim', explode(',', $optionValuesStr));
                        $optionValuesJson = json_encode(array_filter($optionValuesArray)); // Store as JSON array
                        
                        if ($optionValuesJson !== '[]') { // Avoid inserting empty option sets
                            $insertStmt->bind_param("iss", $product_id, $option['name'], $optionValuesJson);
                            if (!$insertStmt->execute()) {
                                 throw new Exception("Database error inserting option '{$option['name']}': " . $insertStmt->error);
                            }
                        }
                    } else {
                         // Optional: Log a warning if an option is skipped due to missing data
                         error_log("[Update Product Warning] Skipping option due to missing name or values: " . print_r($option, true));
                    }
                }
                $insertStmt->close();
            } else {
                 throw new Exception("Invalid product options format received.");
            }
        }
        // --- End Handle Product Options ---
        
        // --- Commit Transaction and Delete Old Image (if needed) ---
        $conn->commit();
        
        // Old image is already deleted during the upload process if needed
        
         // --- Fetch Updated Product Data to Send Back ---
         $finalProductSql = "SELECT p.*, c.name as category_name 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             WHERE p.id = ?";
         $finalStmt = $conn->prepare($finalProductSql);
         $finalStmt->bind_param("i", $product_id);
         $finalStmt->execute();
         $finalResult = $finalStmt->get_result();
         $updatedProductData = $finalResult->fetch_assoc();
         $finalStmt->close();
         if ($updatedProductData) {
            // Debug Log: Check data being sent back
            error_log("[Update Product Debug] Returning Product Data: " . print_r($updatedProductData, true));

            $updatedProductData['price'] = (float)$updatedProductData['price'];
            $updatedProductData['original_price'] = $updatedProductData['original_price'] === null ? null : (float)$updatedProductData['original_price'];
            $updatedProductData['discount_percentage'] = $updatedProductData['discount_percentage'] === null ? null : (float)$updatedProductData['discount_percentage'];
            $updatedProductData['stock'] = (int)$updatedProductData['stock'];
            $updatedProductData['featured'] = (bool)$updatedProductData['featured'];
            $updatedProductData['is_active'] = (bool)$updatedProductData['is_active'];
            $updatedProductData['backorder'] = (bool)$updatedProductData['backorder'];
            // Calculate availability status for the response
            if ($updatedProductData['stock'] > 0) {
                $updatedProductData['availability_status'] = 'in_stock';
            } else {
                $updatedProductData['availability_status'] = 'out_of_stock';
            }
         }

        echo json_encode(['success' => true, 'message' => 'Product updated successfully!', 'product' => $updatedProductData]);

    } catch (Exception $e) {
        $conn->rollback(); // Rollback on any error
        error_log("Product Update Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Database connection not available']);
}
exit;
?> 