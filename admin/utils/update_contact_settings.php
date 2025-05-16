<?php
session_start();
require_once '../../config/settings.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if we have a database connection
if (!$db_connected || !$conn) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Process form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload if present
    $uploadedBannerImage = null;
    if (isset($_FILES['contact_banner_image_file']) && $_FILES['contact_banner_image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../assets/images/uploads/';
        
        // Ensure the upload directory exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate a unique filename
        $fileExtension = pathinfo($_FILES['contact_banner_image_file']['name'], PATHINFO_EXTENSION);
        $newFilename = 'contact_banner_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $newFilename;
        
        // Move the uploaded file to the target location
        if (move_uploaded_file($_FILES['contact_banner_image_file']['tmp_name'], $targetPath)) {
            // Set path relative to the root
            $uploadedBannerImage = '/assets/images/uploads/' . $newFilename;
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Failed to upload contact banner image']);
            exit;
        }
    }
    
    // Get submitted settings
    $contactPageTitle = $_POST['contact_page_title'] ?? 'Contact Us';
    $contactPageSubtitle = $_POST['contact_page_subtitle'] ?? 'We\'d love to hear from you! Send us a message and we\'ll respond as soon as possible.';
    $contactEmail = $_POST['contact_email'] ?? '';
    $contactPhone = $_POST['contact_phone'] ?? '';
    $contactFormEnabled = isset($_POST['contact_form_enabled']) ? 'true' : 'false';
    $businessHoursWeekdays = $_POST['business_hours_weekdays'] ?? '9am - 6pm';
    $businessHoursSaturday = $_POST['business_hours_saturday'] ?? '10am - 4pm';
    $businessHoursSunday = $_POST['business_hours_sunday'] ?? 'Closed';
    
    // Get SMTP settings
    $emailEnabled = isset($_POST['email_enabled']) ? 'true' : 'false';
    $smtpHost = $_POST['smtp_host'] ?? '';
    $smtpPort = $_POST['smtp_port'] ?? '';
    $smtpUsername = $_POST['smtp_username'] ?? '';
    $smtpAuthKey = $_POST['smtp_auth_key'] ?? '';
    $smtpFromEmail = $_POST['smtp_from_email'] ?? '';
    $smtpFromName = $_POST['smtp_from_name'] ?? '';
    
    // Handle contact banner image
    $contactBannerImage = $_POST['contact_banner_image'] ?? '/assets/images/Ecommerce-bg.jpg';
    $removeContactBannerImage = $_POST['remove_contact_banner_image'] ?? '0';
    
    // If file was uploaded, use the new path
    if ($uploadedBannerImage) {
        $contactBannerImage = $uploadedBannerImage;
    } 
    // If remove flag is set, reset to default
    else if ($removeContactBannerImage === '1') {
        $contactBannerImage = '/assets/images/Ecommerce-bg.jpg';
    }
    
    // Define settings to update
    $settings = [
        'contact_page_title' => $contactPageTitle,
        'contact_page_subtitle' => $contactPageSubtitle,
        'contact_email' => $contactEmail,
        'contact_phone' => $contactPhone,
        'contact_form_enabled' => $contactFormEnabled,
        'business_hours_weekdays' => $businessHoursWeekdays,
        'business_hours_saturday' => $businessHoursSaturday,
        'business_hours_sunday' => $businessHoursSunday,
        'contact_banner_image' => $contactBannerImage,
        'email_enabled' => $emailEnabled,
        'smtp_host' => $smtpHost,
        'smtp_port' => $smtpPort,
        'smtp_username' => $smtpUsername,
        'smtp_auth_key' => $smtpAuthKey,
        'smtp_from_email' => $smtpFromEmail,
        'smtp_from_name' => $smtpFromName
    ];
    
    // Update settings in database using prepared statements
    $success = true;
    $errors = [];
    
    foreach ($settings as $key => $value) {
        // Check if setting exists
        $checkStmt = $conn->prepare("SELECT id FROM contact_settings WHERE setting_key = ?");
        $checkStmt->bind_param('s', $key);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing setting
            $updateStmt = $conn->prepare("UPDATE contact_settings SET setting_value = ? WHERE setting_key = ?");
            $updateStmt->bind_param('ss', $value, $key);
            if (!$updateStmt->execute()) {
                $success = false;
                $errors[] = "Failed to update setting: $key";
            }
            $updateStmt->close();
        } else {
            // Insert new setting
            $insertStmt = $conn->prepare("INSERT INTO contact_settings (setting_key, setting_value) VALUES (?, ?)");
            $insertStmt->bind_param('ss', $key, $value);
            if (!$insertStmt->execute()) {
                $success = false;
                $errors[] = "Failed to insert setting: $key";
            }
            $insertStmt->close();
        }
        
        $checkStmt->close();
    }
    
    // Return response
    header('Content-Type: application/json');
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Contact settings updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update contact settings', 'errors' => $errors]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 