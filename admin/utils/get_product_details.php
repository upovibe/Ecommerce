<?php
session_start();
require_once '../../config/settings.php'; // Adjust path as needed

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get Product ID
$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$productId) {
    echo json_encode(['success' => false, 'message' => 'Invalid Product ID']);
    exit;
}

$productData = null;
$options = [];

// Fetch product data and options from database
if ($db_connected && $conn) {
    // Fetch main product details
    $productSql = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   WHERE p.id = ?";
    $productStmt = $conn->prepare($productSql);
    if ($productStmt) {
        $productStmt->bind_param("i", $productId);
        if ($productStmt->execute()) {
            $result = $productStmt->get_result();
            $productData = $result->fetch_assoc();
            if ($productData) {
                 // Ensure correct types
                 $productData['price'] = (float)$productData['price']; 
                 $productData['original_price'] = $productData['original_price'] === null ? null : (float)$productData['original_price'];
                 $productData['discount_percentage'] = $productData['discount_percentage'] === null ? null : (float)$productData['discount_percentage'];
                 $productData['stock'] = (int)$productData['stock'];
                 $productData['featured'] = (bool)$productData['featured'];
            } else {
                 echo json_encode(['success' => false, 'message' => 'Product not found.']);
                 $productStmt->close();
                 exit;
            }
        } else {
             error_log("Database Execute Error (Product): " . $productStmt->error);
            echo json_encode(['success' => false, 'message' => 'Database error while fetching product.']);
            $productStmt->close();
            exit;
        }
        $productStmt->close();
    } else {
         error_log("Database Prepare Error (Product): " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare product statement.']);
        exit;
    }

    // Fetch options
    $optionsSql = "SELECT option_name, option_values FROM product_options WHERE product_id = ? ORDER BY id ASC";
    $stmt = $conn->prepare($optionsSql);
    
    if ($stmt) {
        $stmt->bind_param("i", $productId);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $values = json_decode($row['option_values'], true); // Decode JSON
                // Ensure decoding was successful and it's an array
                if (json_last_error() === JSON_ERROR_NONE && is_array($values)) {
                    $options[] = [
                        'name' => $row['option_name'],
                        'values' => $values
                    ];
                } else {
                    // Handle JSON decode error or if it's not an array
                    error_log("Failed to decode JSON options for product ID: $productId, option: {$row['option_name']}");
                    // Optionally add a default or skip this option
                }
            }
        } else {
            error_log("Database Execute Error: " . $stmt->error);
            // Don't exit here if product data was fetched, options might just be empty
             echo json_encode(['success' => false, 'message' => 'Database error while fetching options.']); 
            $stmt->close();
            exit;
        }
        $stmt->close();
    } else {
        error_log("Database Prepare Error: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Database error: Could not prepare statement for options.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Database connection not available']);
    exit;
}

// Return combined data
echo json_encode(['success' => true, 'product' => $productData, 'options' => $options]);

?> 