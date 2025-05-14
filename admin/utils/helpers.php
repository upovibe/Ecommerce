<?php

if (!function_exists("generateSlug")) {
    /**
     * Generate a URL-friendly slug from a string.
     *
     * @param string $string The input string.
     * @param string $separator Separator character (default: -).
     * @return string The generated slug.
     */
    function generateSlug(string $string, string $separator = '-'): string
    {
        // Convert to lowercase
        $string = mb_strtolower($string, 'UTF-8');

        // Replace non-alphanumeric characters with separator
        $string = preg_replace('/[^\p{L}\p{N}]+/u', $separator, $string);

        // Remove duplicate separators
        $string = preg_replace('/[' . preg_quote($separator) . ']{2,}/', $separator, $string);

        // Trim separators from the beginning and end
        $string = trim($string, $separator);

        // Handle empty string case
        if (empty($string)) {
            return 'n-a-' . uniqid(); // Return a default slug if empty
        }

        return $string;
    }
}

if (!function_exists("handleImageUpload")) {
    /**
     * Handle image upload.
     *
     * @param array $file The $_FILES["image_field_name"] array.
     * @param string $relativeUploadDir The target directory relative to the project root (e.g., 'uploads/categories/').
     * @param string $filePrefix Prefix for the saved filename (e.g., 'product', 'category').
     * @return string|null The path to the uploaded file relative to the project root, or null on failure.
     */
    function handleImageUpload(array $file, string $relativeUploadDir, string $filePrefix = 'image'): ?string
    {
        if (!isset($file) || $file["error"] !== UPLOAD_ERR_OK) {
            if (isset($file["error"])) {
                error_log("Image Upload Error Code: " . $file["error"]);
            }
            return null; // No file uploaded or upload error
        }
        
        // Define project root (adjust if necessary, assumes helpers.php is in admin/utils/)
        $projectRoot = dirname(__DIR__, 2); // Goes up two levels from utils/ to project root
        $absoluteUploadDir = $projectRoot . '/' . trim($relativeUploadDir, '/');

        // Clean the relative upload dir to remove any ../ references but preserve the leading slash
        $cleanRelativeDir = str_replace('../', '', $relativeUploadDir);
        
        // Ensure the directory exists and is writable
        if (!is_dir($absoluteUploadDir)) {
            error_log("Attempting to create directory: " . $absoluteUploadDir);
            if (!mkdir($absoluteUploadDir, 0775, true)) { 
                error_log("Failed to create upload directory: " . $absoluteUploadDir . " - Check permissions.");
                return null; 
            }
            error_log("Successfully created directory: " . $absoluteUploadDir);
        }
        if (!is_writable($absoluteUploadDir)) {
             error_log("Upload directory is not writable: " . $absoluteUploadDir . " - Check permissions.");
             return null;
        }

        $fileName = basename($file["name"]);
        $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        // Use the provided file prefix
        $newFileName = $filePrefix . "_" . time() . "_" . uniqid() . "." . $imageFileType;
        $targetFilePath = $absoluteUploadDir . '/' . $newFileName; 
        
        // The web path is the CLEAN relative path (preserving leading slash if present) + the new filename
        $webPath = ltrim($cleanRelativeDir, '/') === '' ? $newFileName : $cleanRelativeDir . '/' . $newFileName;

        // Allow certain file formats
        $allowedTypes = ["jpg", "png", "jpeg", "gif", "webp"];
        if (!in_array($imageFileType, $allowedTypes)) {
            error_log("Invalid image file type: " . $imageFileType);
            return null;
        }

        // Check file size (e.g., 2MB maximum)
        if ($file["size"] > 2 * 1024 * 1024) {
            error_log("Image file size too large: " . $file["size"]);
            return null;
        }

        // Use is_uploaded_file and move_uploaded_file
        if (is_uploaded_file($file["tmp_name"]) && move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            error_log("Successfully moved uploaded file to: " . $targetFilePath);
            return $webPath; // Return the web-accessible path relative to project root
        } else {
            error_log("Failed to move uploaded file to: " . $targetFilePath . " from " . $file["tmp_name"] . " is_uploaded_file: " . (is_uploaded_file($file["tmp_name"]) ? 'Yes' : 'No'));
            return null;
        }
    }
}

/**
 * Ensures a unique slug in the database.
 *
 * @param mysqli $conn Database connection.
 * @param string $slug Proposed slug.
 * @param string $tableName Table name.
 * @param string $columnName Slug column name.
 * @param int|null $ignoreId ID to ignore during check (for updates).
 * @return string Unique slug.
 */
function ensureUniqueSlug(mysqli $conn, string $slug, string $tableName, string $columnName = 'slug', ?int $ignoreId = null): string {
    $originalSlug = $slug;
    $counter = 1;
    
    while (true) {
        $sql = "SELECT id FROM {$tableName} WHERE {$columnName} = ?";
        $params = [$slug];
        $types = 's';

        if ($ignoreId !== null) {
            $sql .= " AND id != ?";
            $params[] = $ignoreId;
            $types .= 'i';
        }
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            // Handle prepare error - maybe log it and return original slug or throw exception
            error_log("Slug check prepare failed: " . $conn->error);
            return $slug; // Fallback
        }
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows === 0) {
            return $slug; // Slug is unique
        }

        // Slug exists, append counter and check again
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }
} 