<?php
// utils/get_subcategories.php
header('Content-Type: application/json');

require_once '../config/settings.php'; 
require_once '../config/db.php';      

$response = ['success' => false, 'subcategories' => [], 'message' => 'An error occurred.'];

// Get parent_id from query string
$parentId = filter_input(INPUT_GET, 'parent_id', FILTER_VALIDATE_INT);

if ($parentId === false || $parentId === null) {
    $response['message'] = 'Invalid or missing parent_id.';
    echo json_encode($response);
    exit;
}

// Check DB connection
if (!$db_connected || !$conn) {
    $response['message'] = 'Database connection error.';
    echo json_encode($response);
    exit;
}

// Fetch subcategories
try {
    // Optional: Also fetch product count for subcategories
    // $sql = "SELECT c.id, c.name, COUNT(p.id) as product_count 
    //         FROM categories c 
    //         LEFT JOIN products p ON c.id = p.category_id 
    //         WHERE c.parent_id = ? 
    //         GROUP BY c.id 
    //         ORDER BY c.name ASC";
    
    // Simpler query without product count for now
     $sql = "SELECT c.id, c.name 
             FROM categories c 
             WHERE c.parent_id = ? 
             ORDER BY c.name ASC";
             
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param('i', $parentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subcategories = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Ensure correct types
            $row['id'] = (int)$row['id'];
            // If fetching product_count, uncomment below:
            // $row['product_count'] = (int)$row['product_count']; 
            $subcategories[] = $row;
        }
        $response['success'] = true;
        $response['subcategories'] = $subcategories;
        $response['message'] = 'Subcategories fetched successfully.';
    } else {
         $response['message'] = 'Failed to execute query: ' . $stmt->error;
    }
    $stmt->close();
} catch (Exception $e) {
    error_log("Get Subcategories Error: " . $e->getMessage());
    $response['message'] = 'An exception occurred while fetching subcategories.';
}

$conn->close();
echo json_encode($response); 
?> 