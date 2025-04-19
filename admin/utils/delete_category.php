<?php
// admin/api/delete_category.php
session_start();
header('Content-Type: application/json');

require_once '../../config/settings.php'; 
require_once '../../config/db.php';      

$response = ['success' => false, 'message' => 'An error occurred.'];

// Security Check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = 'Access denied.';
    echo json_encode($response);
    exit;
}

// Check DB connection
if (!$db_connected || !$conn) {
    $response['message'] = 'Database connection error.';
    echo json_encode($response);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Get ID from JSON payload
$input = json_decode(file_get_contents('php://input'), true);
$id = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);

// Validation
if (!$id) {
    $response['message'] = 'Invalid or missing category ID.';
    echo json_encode($response);
    exit;
}

// Prepare SQL statement
// Note: Cascading deletes for parent_id and SET NULL for products.category_id are handled by DB schema
$sql = "DELETE FROM categories WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $response['message'] = 'Failed to prepare statement: ' . $conn->error;
    error_log('Delete Category Prepare Error: ' . $conn->error);
    echo json_encode($response);
    exit;
}

$stmt->bind_param('i', $id);

// Execute statement
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Category deleted successfully!';
    } else {
        $response['message'] = 'Category not found or already deleted.';
        // We might consider this success if the goal is removal, but setting success=false is safer
        $response['success'] = false; 
    }
} else {
    // Handle potential foreign key constraint errors if schema changes
    $response['message'] = 'Failed to delete category. It might be in use or another error occurred: ' . $stmt->error;
    error_log('Delete Category Execute Error: ' . $stmt->error);
}

$stmt->close();
$conn->close();

echo json_encode($response); 