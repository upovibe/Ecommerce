<?php
/**
 * Cart Actions Handler
 * Handles AJAX requests for cart operations
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/whatsapp.php';

// Set content type to JSON for most responses
header('Content-Type: application/json');

// Handle cart actions
$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'add_to_cart':
        // Validate required parameters
        if (empty($_POST['product_id']) || empty($_POST['name']) || !isset($_POST['price'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        
        // Get parameters
        $productId = $_POST['product_id'];
        $name = $_POST['name'];
        $price = (float) $_POST['price'];
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
        $image = $_POST['image'] ?? '';
        $options = isset($_POST['options']) ? json_decode($_POST['options'], true) : [];
        
        // Add to cart
        $success = addToCart($productId, $name, $price, $quantity, $image, $options);
        
        // Return response
        echo json_encode([
            'success' => $success,
            'cart_count' => count($_SESSION['cart'] ?? [])
        ]);
        break;
        
    case 'update_quantity':
        // Validate parameters
        if (!isset($_POST['index']) || !isset($_POST['change'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        
        $index = (int) $_POST['index'];
        $change = (int) $_POST['change'];
        
        // Ensure cart exists
        if (!isset($_SESSION['cart']) || !isset($_SESSION['cart'][$index])) {
            echo json_encode(['success' => false, 'message' => 'Item not found in cart']);
            exit;
        }
        
        // Update quantity
        $newQuantity = $_SESSION['cart'][$index]['quantity'] + $change;
        $success = updateCartItemQuantity($index, $newQuantity);
        
        // Return response
        echo json_encode([
            'success' => $success,
            'cart_count' => count($_SESSION['cart'] ?? [])
        ]);
        break;
        
    case 'remove_item':
        // Validate parameters
        if (!isset($_POST['index'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
            exit;
        }
        
        $index = (int) $_POST['index'];
        
        // Remove item
        $success = removeFromCart($index);
        
        // Return response
        echo json_encode([
            'success' => $success,
            'cart_count' => count($_SESSION['cart'] ?? [])
        ]);
        break;
        
    case 'clear_cart':
        // Clear cart
        $success = clearCart();
        
        // Return response
        echo json_encode([
            'success' => $success
        ]);
        break;
        
    case 'get_cart_html':
        // Set content type to HTML for this response
        header('Content-Type: text/html');
        
        // Get cart data
        $cartItems = getCartContents();
        $cartTotal = getCartTotal();
        $currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '₦';
        
        // Return HTML for cart items
        if (empty($cartItems)) {
            echo '<p class="text-gray-500 text-center py-4">Your cart is empty.</p>';
        } else {
            ?>
            <div class="divide-y divide-gray-200">
                <?php foreach ($cartItems as $index => $item): ?>
                    <div class="py-4 flex">
                        <div class="flex-shrink-0 w-16 h-16 border border-gray-200 rounded-md overflow-hidden">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-center object-cover">
                        </div>
                        <div class="ml-4 flex-1 flex flex-col">
                            <div>
                                <div class="flex justify-between text-base font-medium text-gray-900">
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                    <p class="ml-4"><?= $currencySymbol . number_format($item['price'] * $item['quantity'], 2) ?></p>
                                </div>
                                <?php if (!empty($item['options'])): ?>
                                    <p class="mt-1 text-sm text-gray-500">
                                        <?php
                                        $optionTexts = [];
                                        foreach ($item['options'] as $optionName => $optionValue) {
                                            $optionTexts[] = ucfirst($optionName) . ': ' . $optionValue;
                                        }
                                        echo implode(', ', $optionTexts);
                                        ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1 flex items-end justify-between text-sm">
                                <div class="flex items-center">
                                    <button data-index="<?= $index ?>" class="decreaseCartItem px-2 text-gray-600 hover:text-gray-800">-</button>
                                    <span class="cartItemQuantity mx-2"><?= $item['quantity'] ?></span>
                                    <button data-index="<?= $index ?>" class="increaseCartItem px-2 text-gray-600 hover:text-gray-800">+</button>
                                </div>
                                <div class="flex">
                                    <button type="button" data-index="<?= $index ?>" class="removeCartItem font-medium text-red-600 hover:text-red-500">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="border-t border-gray-200 py-4">
                <div class="flex justify-between text-base font-medium text-gray-900">
                    <p>Subtotal</p>
                    <p id="cartTotal"><?= $currencySymbol . number_format($cartTotal, 2) ?></p>
                </div>
            </div>
            
            <div class="mt-6 grid grid-cols-2 gap-3">
                <a href="<?= buildCartInquiryUrl() ?>" target="_blank" class="flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Make Inquiry
                </a>
                <button type="button" id="placeOrderBtn" class="flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-primary hover:bg-indigo-700">
                    Place Order
                </button>
            </div>
            <?php
        }
        break;
        
    case 'order_url':
        // Get order details from query parameters
        $orderDetails = [
            'name' => $_GET['name'] ?? '',
            'phone' => $_GET['phone'] ?? '',
            'delivery_type' => $_GET['delivery_type'] ?? 'Pickup',
            'address' => $_GET['address'] ?? '',
            'note' => $_GET['note'] ?? ''
        ];
        
        // Build WhatsApp URL
        $whatsappUrl = buildOrderPlacementUrl($orderDetails);
        
        // Set content type to text
        header('Content-Type: text/plain');
        
        // Return URL
        echo $whatsappUrl;
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
} 