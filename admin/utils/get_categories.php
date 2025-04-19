<?php
// admin/utils/get_categories.php
header('Content-Type: application/json');

require_once '../../config/settings.php'; 
require_once '../../config/db.php';      

$response = ['success' => false, 'categories' => [], 'message' => 'An error occurred.'];

// No session check needed for read-only typically, but depends on your security model
// If needed, uncomment:
// session_start();
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     $response['message'] = 'Access denied.';
//     echo json_encode($response);
//     exit;
// }

// Check DB connection
if (!$db_connected || !$conn) {
    $response['message'] = 'Database connection error.';
    echo json_encode($response);
    exit;
}

// Fetch categories
try {
    $sql = "SELECT c.id, c.name, c.slug, c.parent_id, c.featured, c.description, c.image, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id 
            GROUP BY c.id 
            ORDER BY c.name ASC"; 
    $result = $conn->query($sql);
    
    if ($result) {
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            // Ensure correct types
            $row['id'] = (int)$row['id'];
            $row['featured'] = (bool)$row['featured'];
            $row['product_count'] = (int)$row['product_count'];
            $row['parent_id'] = $row['parent_id'] ? (int)$row['parent_id'] : null;
            $row['image'] = $row['image'];
            $categories[] = $row;
        }
        $response['success'] = true;
        $response['categories'] = $categories;
        $response['message'] = 'Categories fetched successfully.';
    } else {
         $response['message'] = 'Failed to execute query: ' . $conn->error;
    }
} catch (Exception $e) {
    error_log("Get Categories Error: " . $e->getMessage());
    $response['message'] = 'An exception occurred while fetching categories.';
}

$conn->close();
echo json_encode($response); 