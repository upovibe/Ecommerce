<?php
session_start();
require_once '../../config/settings.php';
// require_once './helpers.php'; // Include if helpers are needed

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access', 'products' => []]);
    exit;
}

function getAllAdminProducts() {
    global $conn, $db_connected;
    $products = [];
    if ($db_connected && $conn) {
        $sql = "SELECT p.*, c.name as category_name, 
                       p.is_active, p.backorder -- Select status columns
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY p.id DESC";
        
        $result = $conn->query($sql);
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Ensure correct types
                $row['price'] = (float)$row['price']; 
                $row['original_price'] = $row['original_price'] === null ? null : (float)$row['original_price'];
                $row['discount_percentage'] = $row['discount_percentage'] === null ? null : (float)$row['discount_percentage'];
                $row['stock'] = (int)$row['stock'];
                $row['is_active'] = (bool)$row['is_active'];
                $row['backorder'] = (bool)$row['backorder'];
                $row['featured'] = (bool)$row['featured'];

                // Calculate availability_status
                if ($row['stock'] > 0) {
                    $row['availability_status'] = 'in_stock';
                } elseif ($row['backorder']) {
                    $row['availability_status'] = 'backorder';
                } else {
                    $row['availability_status'] = 'sold_out';
                }
                $products[] = $row;
            }
            $result->free(); // Free result set
        } else {
             error_log("Get All Products Error: " . $conn->error); 
             // Return success=false if query fails?
             // For now, just returns empty array if query fails
        }
    } else {
         error_log("Get All Products Error: DB connection failed.");
    }
    return $products;
}

$allProducts = getAllAdminProducts();

echo json_encode(['success' => true, 'products' => $allProducts]);
exit;
?> 