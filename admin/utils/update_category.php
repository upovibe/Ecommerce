<?php
// admin/api/update_category.php
session_start();
header('Content-Type: application/json');

require_once '../../config/settings.php'; 
require_once '../../config/db.php';      
require_once 'helpers.php';             // Include helpers from same directory

$response = ['success' => false, 'message' => 'An error occurred.'];
$imageUpdated = false; // Flag to track image changes

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

// --- Fetch Current Category Data (Including Image) ---
$currentImagePath = null;
try {
    $stmt_fetch = $conn->prepare("SELECT image, parent_id FROM categories WHERE id = ?");
    if (!$stmt_fetch) throw new Exception("Failed to prepare fetch statement: " . $conn->error);
    $stmt_fetch->bind_param('i', $id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    if ($result_fetch->num_rows === 0) {
        throw new Exception("Category with ID {$id} not found.");
    }
    $currentCategory = $result_fetch->fetch_assoc();
    $currentImagePath = $currentCategory['image'];
    $currentParentId = $currentCategory['parent_id']; // Get current parent status
    $stmt_fetch->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Update Category - Fetch Error: " . $e->getMessage());
    echo json_encode($response);
    exit;
}

// --- Handle Image Upload/Removal (Only for Parent Categories) ---
$newImagePath = $currentImagePath; // Start with the current path

// Image operations only apply if it IS a parent or is BEING MADE a parent
$isParentOrBecomingParent = ($parentId === null);

if ($isParentOrBecomingParent) {
    // Define project root and upload dir
    $projectRoot = dirname(__DIR__, 2);
    $relativeUploadDir = 'assets/images/categories/';
    $absoluteUploadDir = $projectRoot . '/' . trim($relativeUploadDir, '/');

    // 1. Check for new image upload
    if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
        $uploadedPath = handleImageUpload($_FILES['category_image'], $relativeUploadDir, 'category');
        
        if ($uploadedPath !== null) {
            // Delete old image if it exists
            if ($currentImagePath) {
                $oldImageFullPath = $projectRoot . '/' . $currentImagePath;
                if (file_exists($oldImageFullPath)) {
                    @unlink($oldImageFullPath); // Suppress errors if file not found
                }
            }
            $newImagePath = $uploadedPath; // Use the new path
            $imageUpdated = true;
        } else {
            // Upload failed
            $response['message'] = 'New image upload failed. Please check file type and size.';
            error_log("Update Category - New image upload failed for ID: {$id}");
            echo json_encode($response);
            exit;
        }
    } 
    // 2. Check for image removal flag (only if no new image was uploaded)
    else if ($removeImageFlag) { 
        // Delete old image if it exists
        if ($currentImagePath) {
            $oldImageFullPath = $projectRoot . '/' . $currentImagePath;
            if (file_exists($oldImageFullPath)) {
                if (@unlink($oldImageFullPath)) {
                     $newImagePath = null; // Set path to null after successful delete
                     $imageUpdated = true;
                } else {
                     error_log("Update Category - Failed to delete image file: {$oldImageFullPath} for ID: {$id}");
                     // Optionally: report error, but maybe continue update without image change?
                     // $response['message'] = 'Failed to delete the existing image file. Update aborted.';
                     // echo json_encode($response);
                     // exit;
                } 
            } else {
                 // File didn't exist, still consider it removed
                 $newImagePath = null; 
                 $imageUpdated = true;
            }
        } else {
            // No current image, nothing to remove
             $newImagePath = null;
        }
    }
    // 3. Handle upload errors other than NO_FILE
    else if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] !== UPLOAD_ERR_NO_FILE) {
         $response['message'] = 'An error occurred during image upload (Error code: ' . $_FILES['category_image']['error'] . '). Update aborted.';
         error_log("Update Category - Image upload error code: " . $_FILES['category_image']['error'] . " for ID: {$id}");
         echo json_encode($response);
         exit;
    }
} else {
    // If category is being made a child, remove its image
    if ($currentImagePath) {
         $projectRoot = dirname(__DIR__, 2);
         $oldImageFullPath = $projectRoot . '/' . $currentImagePath;
         if (file_exists($oldImageFullPath)) {
             @unlink($oldImageFullPath); // Suppress errors
         }
         $newImagePath = null;
         $imageUpdated = true; // Mark as updated because image was removed
    }
}
// --- End Image Handling ---


// --- Generate Slug (Check uniqueness ignoring current ID) ---
$slug = generateSlug($name);
$slug = ensureUniqueSlug($conn, $slug, 'categories', 'slug', $id);

// --- Prepare SQL statement (Remove image) ---
$sql = "UPDATE categories SET name = ?, slug = ?, description = ?, parent_id = ?, image = ?, featured = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $response['message'] = 'Failed to prepare statement: ' . $conn->error;
    error_log('Update Category Prepare Error: ' . $conn->error);
    echo json_encode($response);
    exit;
}

$featuredInt = $featured ? 1 : 0;
// Bind parameters (types: sssisii - name, slug, desc, parentId, image, featured, id)
$stmt->bind_param('sssisii', $name, $slug, $description, $parentId, $newImagePath, $featuredInt, $id);

// --- Execute statement ---
$executed = $stmt->execute();
$affected_rows = $stmt->affected_rows;
$stmt_error = $stmt->error; // Capture error before closing
$stmt->close();

if ($executed) {
    // Check if any row was updated 
    if ($affected_rows > 0 || $imageUpdated) {
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