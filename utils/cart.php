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
        $sql = "SELECT id, name, price, description, image, category_id FROM products WHERE id = ?";
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
    
    // If no database or product not found, check demo products
    $demoProducts = [
        1 => [
            'id' => 1,
            'name' => 'School Backpack',
            'price' => 4500,
            'description' => 'A durable backpack with multiple compartments, perfect for students. Made from high-quality materials that will last for years.',
            'image' => 'assets/images/demo/backpack.jpg',
            'category_id' => 101,
            'options' => [
                'Color' => ['Black', 'Blue', 'Red'],
                'Size' => ['Small', 'Medium', 'Large']
            ]
        ],
        2 => [
            'id' => 2,
            'name' => 'Travel Suitcase',
            'price' => 15000,
            'description' => 'Lightweight yet durable suitcase with spinner wheels and expandable capacity. Perfect for your next journey.',
            'image' => 'assets/images/demo/suitcase.jpg',
            'category_id' => 102,
            'options' => [
                'Color' => ['Silver', 'Black', 'Blue'],
                'Size' => ['Carry-on', 'Medium', 'Large']
            ]
        ],
        3 => [
            'id' => 3,
            'name' => 'Fresh Apples (1kg)',
            'price' => 800,
            'description' => 'Fresh, crisp apples sourced directly from local farmers. Rich in fiber and vitamins.',
            'image' => 'assets/images/demo/apples.jpg',
            'category_id' => 201,
            'options' => [
                'Type' => ['Red Delicious', 'Granny Smith', 'Gala']
            ]
        ],
        // Add more demo products as needed
    ];
    
    return $demoProducts[$productId] ?? null;
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