<?php
session_start();

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../config/settings.php';

header('Content-Type: application/json');

// Settings file path
$settingsFilePath = __DIR__ . '/../../config/settings.php';

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

// Check if logo file was uploaded
if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
        UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form",
        UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded",
        UPLOAD_ERR_NO_FILE    => "No file was uploaded",
        UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
        UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the file upload",
    ];
    $errorCode = $_FILES['logo']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => false, 'message' => $errorMessages[$errorCode] ?? 'Unknown upload error']);
    exit;
}

// --- File Validation ---
$upload_dir_relative = '/uploads/logos/'; // Relative path for storage and URL
$upload_dir_absolute = $_SERVER['DOCUMENT_ROOT'] . $upload_dir_relative;
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowed_extensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
$max_size = 2 * 1024 * 1024; // 2MB

$file_tmp_name = $_FILES['logo']['tmp_name'];
$file_size = $_FILES['logo']['size'];
$file_info = finfo_open(FILEINFO_MIME_TYPE);
$file_mime_type = finfo_file($file_info, $file_tmp_name);
finfo_close($file_info);
$file_original_name = $_FILES['logo']['name'];
$file_ext = strtolower(strrchr($file_original_name, '.'));

if (!in_array($file_mime_type, $allowed_mime_types) || !in_array($file_ext, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: JPG, PNG, GIF, WEBP.']);
    exit;
}

if ($file_size > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds the 2MB limit.']);
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
    $unique_name = 'logo_' . uniqid() . $file_ext;
    $destination_absolute = $upload_dir_absolute . $unique_name;
    $destination_relative = $upload_dir_relative . $unique_name;

    // Get current logo path to delete later
    $currentLogoPath = STORE_SETTINGS['store_logo'] ?? null;

    // Move the uploaded file
    if (!move_uploaded_file($file_tmp_name, $destination_absolute)) {
        throw new Exception('Failed to move uploaded file.');
    }

    // --- Update Database (if connected) ---
    $dbUpdateSuccess = true;
    if ($db_connected && $conn) {
        $sql = "INSERT INTO store_settings (setting_key, setting_value) VALUES ('store_logo', ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
             throw new Exception('DB prepare error: ' . $conn->error);
        }
        $stmt->bind_param('s', $destination_relative);
        if (!$stmt->execute()) {
            $dbUpdateSuccess = false;
            error_log("DB logo update Error: " . $stmt->error);
            // Don't throw exception, file was saved, try updating config file
        }
        $stmt->close();
    }

    // --- Update Settings File ---
    $fileUpdateSuccess = false;
    if (is_writable($settingsFilePath)) {
        $currentContent = file_get_contents($settingsFilePath);
        if ($currentContent !== false) {
            $pattern = "/'store_logo'\s*=>\s*'.*?',/";
            $replacement = "'store_logo' => '" . addslashes($destination_relative) . "',";
            $newContent = preg_replace($pattern, $replacement, $currentContent, 1); // Replace only once

            if ($newContent !== null && $newContent !== $currentContent) {
                if (file_put_contents($settingsFilePath, $newContent) !== false) {
                    $fileUpdateSuccess = true;
                    if (function_exists('opcache_invalidate')) {
                        opcache_invalidate($settingsFilePath, true);
                    }
                } else {
                     error_log("Failed to write updated logo path to settings file.");
                }
            } else {
                 error_log("Failed to update logo path in settings file content (regex might have failed).");
            }
        } else {
             error_log("Failed to read settings file for logo update.");
        }
    } else {
        error_log("Settings file is not writable for logo update.");
    }
    
    // --- Delete Old Logo ---
    if ($currentLogoPath && $currentLogoPath !== $destination_relative) {
        $oldLogoFullPath = $_SERVER['DOCUMENT_ROOT'] . $currentLogoPath;
        // Basic check to avoid deleting outside uploads
        if (strpos($currentLogoPath, '/uploads/') === 0 && file_exists($oldLogoFullPath)) {
            @unlink($oldLogoFullPath);
        }
    }

    // Set logo version in session for cache busting
    $_SESSION['logo_version'] = time();

    echo json_encode([
        'success' => true, 
        'message' => 'Logo updated successfully!' . (!$dbUpdateSuccess ? ' (DB update failed)' : '') . (!$fileUpdateSuccess ? ' (File update failed)' : ''),
        'newLogoPath' => $destination_relative // Send back the relative path
    ]);

} catch (Exception $e) {
    error_log("Logo Upload Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error processing logo: ' . $e->getMessage()]);
}

exit;
?> 