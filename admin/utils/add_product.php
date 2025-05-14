<?php
session_start();

// Suppress PHP error display for JSON endpoint consistency
error_reporting(0);
ini_set('display_errors', 0);

require_once '../../config/settings.php';
require_once './helpers.php'; // Include helpers

header('Content-Type: application/json');

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

// --- Data Retrieval & Basic Validation ---
$name = trim($_POST['product_name'] ?? '');
$slug_input = trim($_POST['product_slug'] ?? '');
$description = trim($_POST['product_description'] ?? '');
$price = filter_input(INPUT_POST, 'product_price', FILTER_VALIDATE_FLOAT);
$stock = filter_input(INPUT_POST, 'product_stock', FILTER_VALIDATE_INT);
$category_id = empty($_POST['category_id']) ? null : (int)$_POST['category_id'];
$featured = isset($_POST['featured']) ? 1 : 0;
$product_options_json = $_POST['product_options'] ?? null;
$is_active = isset($_POST['is_active']) ? 1 : 0;
$allow_backorder = ($_POST['backorder'] ?? '0') === '1' ? 1 : 0;
$discount_percentage_input = $_POST['discount_percentage'] ?? null;

if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Product Name is required.']);
    exit;
}
if ($price === false || $price < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Price specified.']);
    exit;
}
if ($stock === false || $stock < 0) {
    $stock = 0; // Default stock to 0 if invalid
}
if ($category_id === false || $category_id <= 0) {
    $category_id = null; // Set category to NULL if invalid or empty
}

// --- Slug Generation ---
$slug = !empty($slug_input) ? generateSlug($slug_input) : generateSlug($name);

// Check if slug already exists (implement database check if needed for uniqueness)
// For now, we assume the UNIQUE constraint handles it, but a pre-check is better UX.

// --- Image Upload ---
$imagePath = null;
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $imagePath = handleImageUpload($_FILES['product_image'], '/assets/images/products/'); 
    if ($imagePath === null) {
        echo json_encode(['success' => false, 'message' => 'Image upload failed. Check file type, size (max 2MB), and permissions.']);
        exit;
    }
}

// --- Database Insertion ---
try {
    if ($db_connected && $conn) {
        $sql = "INSERT INTO products (name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            $conn->rollback(); // Rollback transaction
            error_log("Database Prepare Error: " . $conn->error);
            throw new Exception("Database error: Could not prepare statement.");
        }
        
        // Calculate original_price if discount percentage is valid
        $original_price = null;
        $discount_percentage = null;
        if ($discount_percentage_input !== null && is_numeric($discount_percentage_input)) {
            $discount_percentage_float = (float)$discount_percentage_input;
            if ($discount_percentage_float > 0 && $discount_percentage_float <= 100) {
                 // Avoid division by zero or negative percentage > 100 which makes no sense here
                $divisor = 1 - ($discount_percentage_float / 100);
                 if ($divisor > 0) { 
                     $original_price = round($price / $divisor, 2);
                     $discount_percentage = $discount_percentage_float;
                 } else {
                      // Handle edge case: 100% discount means original price is theoretically infinite or undefined
                      // Or invalid percentage > 100 entered. For now, treat as no discount.
                     // Log this perhaps? error_log("Invalid discount percentage for calculation: ".$discount_percentage_float);
                     $original_price = null;
                     $discount_percentage = null;
                 }
             } // else: Percentage is 0 or invalid format, treat as no discount
        } // else: No percentage provided

        $stmt->bind_param("sssdddsiiiii", 
            $name, $slug, $description, 
            $price, $original_price, $discount_percentage, // Added original_price, discount_percentage
            $imagePath, $category_id, $stock, $featured, $is_active, $allow_backorder
        );
        
        if (!$stmt->execute()) {
            $conn->rollback(); // Rollback transaction
            // Check for duplicate slug error (Error code 1062 for UNIQUE constraint)
            if ($conn->errno === 1062) {
                throw new Exception("Database error: A product with this slug already exists. Please use a unique slug.");
            } else {
                error_log("Database Error: " . $stmt->error);
                throw new Exception("Database error while adding product.");
            }
        }
        $newProductId = $stmt->insert_id;
        $stmt->close();
        
        // Fetch the newly created product to return it (optional, but good for reactivity)
        $newProductSql = "SELECT 
                              p.id, p.name, p.slug, p.description, p.price, 
                              p.original_price, p.discount_percentage, 
                              p.image, p.category_id, p.stock, p.featured, 
                              p.is_active, p.backorder,
                              p.created_at, p.updated_at, 
                              c.name as category_name 
                           FROM products p LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.id = ?";
        $newStmt = $conn->prepare($newProductSql);
        $newStmt->bind_param("i", $newProductId);
        $newStmt->execute();
        $result = $newStmt->get_result();
        $newProductData = $result->fetch_assoc();
        if ($newProductData) {
             $newProductData['price'] = (float)$newProductData['price']; // Ensure float for consistency
             $newProductData['original_price'] = $newProductData['original_price'] === null ? null : (float)$newProductData['original_price'];
             $newProductData['discount_percentage'] = $newProductData['discount_percentage'] === null ? null : (float)$newProductData['discount_percentage'];
             $newProductData['stock'] = (int)$newProductData['stock'];
             $newProductData['featured'] = (bool)$newProductData['featured'];
             $newProductData['is_active'] = (bool)$newProductData['is_active'];
             $newProductData['backorder'] = (bool)$newProductData['backorder'];
             // Calculate availability status for the response
             if ($newProductData['stock'] > 0) {
                 $newProductData['availability_status'] = 'in_stock';
             } else {
                 $newProductData['availability_status'] = 'out_of_stock';
             }
        }
        $newStmt->close();
        
        // --- Handle Product Options --- 
        if ($product_options_json) {
            $options = json_decode($product_options_json, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($options)) {
                $insertOptionSql = "INSERT INTO product_options (product_id, option_name, option_values) VALUES (?, ?, ?)";
                $insertStmt = $conn->prepare($insertOptionSql);
                if (!$insertStmt) {
                    $conn->rollback(); // Rollback if prepare fails
                    throw new Exception("Database error preparing option insert: " . $conn->error);
                }

                foreach ($options as $option) {
                    if (!empty($option['name']) && !empty($option['values'])) {
                        $optionValuesArray = array_map('trim', explode(',', $option['values']));
                        $optionValuesJson = json_encode(array_filter($optionValuesArray)); // Store as JSON array
                        
                        $insertStmt->bind_param("iss", $newProductId, $option['name'], $optionValuesJson);
                        if (!$insertStmt->execute()) {
                            $conn->rollback(); // Rollback if execute fails
                            throw new Exception("Database error inserting option '{$option['name']}': " . $insertStmt->error);
                        }
                    }
                }
                $insertStmt->close();
                // Fetch options again to include in the response? Or assume frontend handles it.
            } else {
                 $conn->rollback(); // Rollback if JSON is invalid
                 throw new Exception("Invalid product options format received.");
            }
        }
        // --- End Handle Product Options ---
        
        $conn->commit(); // Commit transaction ONLY AFTER options are handled

        // Fetch product data *after* commit if needed, or just send success
        // For consistency, let's re-fetch to include options if necessary or just use $newProductData from before
        // If options need to be in the response, fetch them here and add to $newProductData

        echo json_encode([
            'success' => true, 
            'message' => 'Product added successfully!',
            'product' => $newProductData // Send back the new product data (options not included unless fetched again)
        ]);

    } else {
        throw new Exception("Database connection not available");
    }

} catch (Exception $e) { // Add a try-catch block around the main logic
    if ($conn && $conn->ping()) { // Check if connection exists before rollback
       $conn->rollback();
    }
    error_log("Add Product Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);

} finally { // Ensure connection is closed
     if (isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
     if (isset($newStmt) && $newStmt instanceof mysqli_stmt) $newStmt->close();
     if (isset($insertStmt) && $insertStmt instanceof mysqli_stmt) $insertStmt->close();
     // $conn->close(); // Consider closing connection if not persistent 
}

exit; // Ensure no further output

?> 