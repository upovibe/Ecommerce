<?php
/**
 * Cart Utility Functions
 */

require_once __DIR__ . '/../config/settings.php';

// Initialize session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Add item to cart
 * 
 * @param int $productId
 * @param string $productName
 * @param float $price
 * @param int $quantity
 * @param string $image
 * @param array $options
 * @return bool
 */
function addToCart($productId, $productName, $price, $quantity = 1, $image = '', $options = []) {
    // Generate a unique cart item ID based on product ID and options
    $cartItemId = $productId;
    if (!empty($options)) {
        $cartItemId .= '_' . md5(json_encode($options));
    }
    
    // Check if product already exists in cart
    if (isset($_SESSION['cart'][$cartItemId])) {
        // Update quantity
        $_SESSION['cart'][$cartItemId]['quantity'] += $quantity;
    } else {
        // Add new product to cart
        $_SESSION['cart'][$cartItemId] = [
            'id' => $productId,
            'name' => $productName,
            'price' => $price,
            'quantity' => $quantity,
            'image' => $image,
            'options' => $options
        ];
    }
    
    // Save to database if available
    saveCartToDatabase();
    
    return true;
}

/**
 * Update cart item quantity
 * 
 * @param string $cartItemId
 * @param int $quantity
 * @return bool
 */
function updateCartItem($cartItemId, $quantity) {
    if (isset($_SESSION['cart'][$cartItemId])) {
        if ($quantity <= 0) {
            // Remove item if quantity is 0 or less
            unset($_SESSION['cart'][$cartItemId]);
        } else {
            // Update quantity
            $_SESSION['cart'][$cartItemId]['quantity'] = $quantity;
        }
        
        // Save to database if available
        saveCartToDatabase();
        
        return true;
    }
    
    return false;
}

/**
 * Remove item from cart
 * 
 * @param string $cartItemId
 * @return bool
 */
function removeFromCart($cartItemId) {
    if (isset($_SESSION['cart'][$cartItemId])) {
        unset($_SESSION['cart'][$cartItemId]);
        
        // Save to database if available
        saveCartToDatabase();
        
        return true;
    }
    
    return false;
}

/**
 * Clear cart
 * 
 * @return bool
 */
function clearCart() {
    $_SESSION['cart'] = [];
    
    // Save to database if available
    saveCartToDatabase();
    
    return true;
}

/**
 * Get cart items
 * 
 * @return array
 */
function getCartItems() {
    return $_SESSION['cart'] ?? [];
}

/**
 * Get cart total
 * 
 * @return float
 */
function getCartTotal() {
    $total = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    return $total;
}

/**
 * Get cart item count
 * 
 * @return int
 */
function getCartItemCount() {
    $count = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    
    return $count;
}

/**
 * Save cart to database for logged-in users
 * Only saves if user is logged in and database connection is available
 */
function saveCartToDatabase() {
    global $conn, $db_connected;
    
    // Check if user is logged in and database connection is available
    if (isset($_SESSION['user_id']) && $db_connected && $conn) {
        $userId = $_SESSION['user_id'];
        $cartData = json_encode($_SESSION['cart']);
        
        // Delete existing cart data
        $sql = "DELETE FROM user_carts WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        
        // Insert new cart data
        $sql = "INSERT INTO user_carts (user_id, cart_data, updated_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $userId, $cartData);
        $stmt->execute();
    }
}

/**
 * Load cart from database for logged-in users
 * Only loads if user is logged in and database connection is available
 */
function loadCartFromDatabase() {
    global $conn, $db_connected;
    
    // Check if user is logged in and database connection is available
    if (isset($_SESSION['user_id']) && $db_connected && $conn) {
        $userId = $_SESSION['user_id'];
        
        $sql = "SELECT cart_data FROM user_carts WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $cartData = json_decode($row['cart_data'], true);
            
            if (is_array($cartData)) {
                $_SESSION['cart'] = $cartData;
            }
        }
    }
}

/**
 * Get product details by ID
 * Will return from database if available, or from demo products if not
 * 
 * @param int $productId
 * @return array|null
 */
function getProductDetails($productId) {
    global $conn, $db_connected;
    
    // Try to get from database if connection is available
    if ($db_connected && $conn) {
        $sql = "SELECT p.id, p.name, p.price, p.original_price, p.discount_percentage, p.description, p.image, p.category_id, c.name as category_name, p.stock, p.is_active, p.backorder, p.slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            
            // Get product options
            $options = [];
            $optionSql = "SELECT option_name, option_values FROM product_options WHERE product_id = ?";
            $optStmt = $conn->prepare($optionSql);
            $optStmt->bind_param('i', $productId);
            $optStmt->execute();
            $optResult = $optStmt->get_result();
            
            if ($optResult && $optResult->num_rows > 0) {
                while ($optRow = $optResult->fetch_assoc()) {
                    $options[$optRow['option_name']] = json_decode($optRow['option_values'], true);
                }
            }
            
            $product['options'] = $options;
            return $product;
        }
    }
    
    // If no database or product not found, load from demo JSON
    $jsonPath = __DIR__ . '/../config/demo_data.json';
    if (file_exists($jsonPath)) {
        $jsonContent = file_get_contents($jsonPath);
        $demoData = json_decode($jsonContent, true);
        
        if (json_last_error() === JSON_ERROR_NONE && isset($demoData['products']) && is_array($demoData['products'])) {
            foreach ($demoData['products'] as $demoProduct) {
                if (isset($demoProduct['id']) && $demoProduct['id'] == $productId) {
                    // Return the found demo product (ensure keys match what might be expected)
                    return [
                        'id' => $demoProduct['id'],
                        'name' => $demoProduct['name'] ?? 'N/A',
                        'slug' => $demoProduct['slug'] ?? '',
                        'price' => $demoProduct['price'] ?? 0,
                        'original_price' => $demoProduct['original_price'] ?? null,
                        'discount_percentage' => $demoProduct['discount_percentage'] ?? null,
                        'description' => $demoProduct['description'] ?? '',
                        'image' => $demoProduct['image'] ?? '',
                        'category_id' => $demoProduct['category_id'] ?? null,
                        'category_name' => $demoProduct['category_name'] ?? 'Uncategorized',
                        'options' => $demoProduct['options'] ?? [],
                        'stock' => $demoProduct['stock'] ?? 0,
                        'is_active' => $demoProduct['is_active'] ?? false,
                        'backorder' => $demoProduct['backorder'] ?? false
                    ];
                }
            }
        }
    }
    
    // Return null if not found in DB or demo JSON
    return null;
}

/**
 * Get cart items formatted for WhatsApp message
 * 
 * @return string Formatted cart items
 */
function getCartItemsText() {
    $items = "";
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $optionsText = '';
            if (!empty($item['options'])) {
                $optionParts = [];
                foreach ($item['options'] as $optionName => $optionValue) {
                    $optionParts[] = ucfirst($optionName) . ': ' . $optionValue;
                }
                $optionsText = ' (' . implode(', ', $optionParts) . ')';
            }
            
            $items .= "- " . $item['quantity'] . "x " . $item['name'] . $optionsText . "\n";
        }
    }
    return $items;
} 