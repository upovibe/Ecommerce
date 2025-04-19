<?php
// admin/api/add_category.php
session_start();
header('Content-Type: application/json');

require_once '../../config/settings.php'; // Adjust path as needed
require_once '../../config/db.php';      // Adjust path as needed
require_once 'helpers.php';             // Include helpers from same directory
// require_once 'utils/functions.php';     // REMOVED - Content moved to helpers.php

$response = ['success' => false, 'message' => 'An error occurred.'];

// Security Check: Ensure user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    $response['message'] = 'Access denied. Please log in.';
    echo json_encode($response);
    exit;
}

// Check database connection
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

// Get data from POST request (adjust if using FormData)
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
$featured = isset($_POST['featured']) && ($_POST['featured'] === 'true' || $_POST['featured'] === '1');

// Basic Validation
if (empty($name)) {
    $response['message'] = 'Category name is required.';
    echo json_encode($response);
    exit;
}

// Removed Image Upload Handling

// Generate slug
$slug = generateSlug($name);
$slug = ensureUniqueSlug($conn, $slug, 'categories');

// Prepare SQL statement (remove image column)
$sql = "INSERT INTO categories (name, slug, description, parent_id, featured) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $response['message'] = 'Failed to prepare statement: ' . $conn->error;
    error_log('Add Category Prepare Error: ' . $conn->error);
    echo json_encode($response);
    exit;
}

// Bind parameters (s = string, i = integer)
// parent_id can be null, so use 'i' but pass null if needed
// featured is boolean, pass 0 or 1 (integer)
// image is removed
$featuredInt = $featured ? 1 : 0;
// Change bind_param types back to 'sssii'
$stmt->bind_param('sssii', $name, $slug, $description, $parentId, $featuredInt);

// Execute statement
if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Category added successfully!';
        // Optional: Return the new category ID
        // $response['new_id'] = $stmt->insert_id;
    } else {
        $response['message'] = 'Failed to add category. No rows affected.';
    }
} else {
    $response['message'] = 'Failed to execute statement: ' . $stmt->error;
    error_log('Add Category Execute Error: ' . $stmt->error);
}

$stmt->close();
$conn->close();

echo json_encode($response); 