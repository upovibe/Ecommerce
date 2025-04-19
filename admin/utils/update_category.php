<?php
// admin/api/update_category.php
session_start();
header('Content-Type: application/json');

require_once '../../config/settings.php'; 
require_once '../../config/db.php';      
require_once 'helpers.php';             // Include helpers from same directory

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

// Get data
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$parentId = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
// Explicitly check for '1' string from JS FormData
$featured = isset($_POST['featured']) && $_POST['featured'] === '1'; 
$removeImageFlag = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';

// Validation
if (!$id) {
    $response['message'] = 'Invalid or missing category ID.';
    echo json_encode($response);
    exit;
}
if (empty($name)) {
    $response['message'] = 'Category name is required.';
    echo json_encode($response);
    exit;
}
// Prevent setting category as its own parent
if ($id === $parentId) {
     $response['message'] = 'A category cannot be its own parent.';
    echo json_encode($response);
    exit;
}
// Prevent making a parent category a child of one of its descendants (complex check, omitted for brevity)

// --- Fetch Current Category Data (No longer need image) ---

// --- Removed Image Update/Removal Logic --- 


// --- Generate Slug (Check uniqueness ignoring current ID) ---
$slug = generateSlug($name);
$slug = ensureUniqueSlug($conn, $slug, 'categories', 'slug', $id);

// --- Prepare SQL statement (Remove image) ---
$sql = "UPDATE categories SET name = ?, slug = ?, description = ?, parent_id = ?, featured = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $response['message'] = 'Failed to prepare statement: ' . $conn->error;
    error_log('Update Category Prepare Error: ' . $conn->error);
    echo json_encode($response);
    exit;
}

$featuredInt = $featured ? 1 : 0;
// Bind parameters (types: sssiii - name, slug, desc, parentId, featured, id)
$stmt->bind_param('sssiii', $name, $slug, $description, $parentId, $featuredInt, $id);

// --- Execute statement ---
$executed = $stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt_error = $stmt->error; // Capture error before closing
$stmt->close();

if ($executed) {
    // Check if any row was updated 
    if ($affected_rows > 0) { // Removed || $imageUpdated check
        $response['success'] = true;
        $response['message'] = 'Category updated successfully!';
    } else {
        // This happens if submitted data is identical 
        $response['success'] = true; // Still considered success
        $response['message'] = 'No changes detected.'; 
    }
} else {
    $response['message'] = 'Failed to execute statement: ' . $stmt_error;
    error_log('Update Category Execute Error: ' . $stmt_error);
}

$conn->close();

echo json_encode($response); 