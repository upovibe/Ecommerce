<?php

// Get the requested URI
$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Check if the requested URI is for a file that actually exists in the web root
// Make sure it's not the router script itself!
$requested_file = $_SERVER['DOCUMENT_ROOT'] . $uri;

// If the requested file exists and is not a directory, serve it directly.
// Returning false tells the built-in server to handle the request as a static file.
if ($uri !== '/' && file_exists($requested_file) && is_file($requested_file)) {
    // Check MIME type for CSS/JS for proper headers (optional but good practice)
    $mime_type = mime_content_type($requested_file);
    if (in_array($mime_type, ['text/css', 'application/javascript', 'text/javascript'])) {
        header('Content-Type: ' . $mime_type);
        readfile($requested_file);
        exit;
    } 
    // Let the server handle other file types
    return false;
}

// If the request is for the root directory, serve index.php
if ($uri === '/') {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/index.php';
    exit;
}

// --- Handle specific known paths/pages ---
// You might need to expand this if you have other top-level PHP files
// Example: If you had products.php directly in the root
// elseif ($uri === '/products.php') {
//     require_once $_SERVER['DOCUMENT_ROOT'] . '/products.php';
//     exit;
// }

// --- If none of the above match, it's likely a 404 ---

// Check if the request *looks* like a directory path but doesn't have a trailing slash
// (Browsers often add this automatically, but direct requests might miss it)
// if (is_dir($requested_file) && substr($uri, -1) !== '/') {
//     header('Location: ' . $uri . '/');
//     exit;
// }

// For any other request that wasn't a real file or the homepage, 
// assume it's a 404.
http_response_code(404);
require_once $_SERVER['DOCUMENT_ROOT'] . '/404.php';
exit;

?> 