<?php
session_start();

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/settings.php';

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

// Check if favicon file was uploaded
if (!isset($_FILES['favicon']) || $_FILES['favicon']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
        UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form",
        UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded",
        UPLOAD_ERR_NO_FILE    => "No file was uploaded",
        UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
        UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the file upload",
    ];
    $errorCode = $_FILES['favicon']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => false, 'message' => $errorMessages[$errorCode] ?? 'Unknown upload error']);
    exit;
}

// --- File Validation ---
$upload_dir_relative = '/uploads/favicons/'; // Relative path for storage and URL
$upload_dir_absolute = $_SERVER['DOCUMENT_ROOT'] . $upload_dir_relative;
$allowed_mime_types = ['image/x-icon', 'image/png', 'image/jpeg'];
$allowed_extensions = ['.ico', '.png', '.jpg', '.jpeg'];
$max_size = 1 * 1024 * 1024; // 1MB

$file_tmp_name = $_FILES['favicon']['tmp_name'];
$file_size = $_FILES['favicon']['size'];
$file_info = finfo_open(FILEINFO_MIME_TYPE);
$file_mime_type = finfo_file($file_info, $file_tmp_name);
finfo_close($file_info);
$file_original_name = $_FILES['favicon']['name'];
$file_ext = strtolower(strrchr($file_original_name, '.'));

if (!in_array($file_mime_type, $allowed_mime_types) || !in_array($file_ext, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: ICO, PNG, JPG.']);
    exit;
}

if ($file_size > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds the 1MB limit.']);
    exit;
}

// --- Process Upload ---
try {
    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir_absolute)) {
        if (!mkdir($upload_dir_absolute, 0777, true)) {
            throw new Exception('Failed to create upload directory.');
        }
    }

    // Generate unique filename
    $unique_name = 'favicon_' . uniqid() . $file_ext;
    $destination_absolute = $upload_dir_absolute . $unique_name;
    $destination_relative = $upload_dir_relative . $unique_name;

    // Get current favicon path to delete later
    $currentFaviconPath = STORE_SETTINGS['store_favicon'] ?? null;

    // Move the uploaded file
    if (!move_uploaded_file($file_tmp_name, $destination_absolute)) {
        throw new Exception('Failed to move uploaded file.');
    }

    // --- Update Database ---
    $dbUpdateSuccess = true;
    if ($db_connected && $conn) {
        $sql = "INSERT INTO store_settings (setting_key, setting_value) VALUES ('store_favicon', ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('DB prepare error: ' . $conn->error);
        }
        $stmt->bind_param('s', $destination_relative);
        if (!$stmt->execute()) {
            $dbUpdateSuccess = false;
            error_log("DB favicon update Error: " . $stmt->error);
        }
        $stmt->close();
    }

    // Delete old favicon if it exists and isn't the default
    if ($currentFaviconPath && $currentFaviconPath !== '/assets/images/favicon.ico') {
        $old_file_path = $_SERVER['DOCUMENT_ROOT'] . $currentFaviconPath;
        if (file_exists($old_file_path)) {
            unlink($old_file_path);
        }
    }

    // Update session version to force cache refresh
    $_SESSION['favicon_version'] = time();

    echo json_encode([
        'success' => true,
        'message' => 'Favicon updated successfully',
        'path' => $destination_relative
    ]);

} catch (Exception $e) {
    error_log("Favicon Update Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update favicon: ' . $e->getMessage()
    ]);
} 