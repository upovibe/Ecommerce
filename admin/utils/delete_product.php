<?php
session_start();
require_once '../../config/settings.php';
require_once './helpers.php'; // If image deletion helper is there

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

// Get Product ID
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Product ID provided.']);
    exit;
}

// --- Start Transaction ---
if ($db_connected && $conn) {
    $conn->begin_transaction();

    try {
        // --- Fetch Product Image Path Before Deleting Product Record ---
        $getImageSql = "SELECT image FROM products WHERE id = ?";
        $imageStmt = $conn->prepare($getImageSql);
        $imageStmt->bind_param("i", $product_id);
        $imageStmt->execute();
        $imageResult = $imageStmt->get_result();
        $productData = $imageResult->fetch_assoc();
        $imagePathToDelete = $productData ? $productData['image'] : null;
        $imageStmt->close();

        // --- Delete Associated Product Options First ---
        $deleteOptionsSql = "DELETE FROM product_options WHERE product_id = ?";
        $optionsStmt = $conn->prepare($deleteOptionsSql);
        if (!$optionsStmt) {
             throw new Exception("Database error preparing options delete: " . $conn->error);
        }
        $optionsStmt->bind_param("i", $product_id);
        if (!$optionsStmt->execute()) {
            throw new Exception("Database error deleting options: " . $optionsStmt->error);
        }
        $optionsStmt->close();

        // --- Delete the Product Record ---
        $deleteProductSql = "DELETE FROM products WHERE id = ?";
        $productStmt = $conn->prepare($deleteProductSql);
         if (!$productStmt) {
             throw new Exception("Database error preparing product delete: " . $conn->error);
        }
        $productStmt->bind_param("i", $product_id);

        if (!$productStmt->execute()) {
            throw new Exception("Database error deleting product: " . $productStmt->error);
        }
        
        // Check if any row was actually deleted
        $affected_rows = $productStmt->affected_rows;
        $productStmt->close();

        if ($affected_rows === 0) {
            // Product might have been deleted by another process already
            // We can choose to treat this as success or failure depending on desired behaviour
            // For now, treat as success since the product is gone.
            // throw new Exception("Product not found or already deleted.");
        }

        // --- Commit Transaction ---
        $conn->commit();

        // --- Delete Image File (After successful commit) ---
        if ($imagePathToDelete) {
            $fullImagePath = "../.." . $imagePathToDelete; // Adjust path relative to this utils script
            if (file_exists($fullImagePath)) {
                @unlink($fullImagePath); // Use @ to suppress errors if deletion fails
            }
        }

        echo json_encode(['success' => true, 'message' => 'Product deleted successfully!']);

    } catch (Exception $e) {
        $conn->rollback(); // Rollback on any error
        error_log("Product Delete Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Database connection not available']);
}
exit;
?> 