<?php
require_once __DIR__ . '/../utils/cart.php';

// Process cart actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'update':
            if (isset($_POST['cart_item_id']) && isset($_POST['quantity'])) {
                $cartItemId = $_POST['cart_item_id'];
                $quantity = (int)$_POST['quantity'];
                updateCartItem($cartItemId, $quantity);
            }
            break;
            
        case 'remove':
            if (isset($_POST['cart_item_id'])) {
                $cartItemId = $_POST['cart_item_id'];
                removeFromCart($cartItemId);
            }
            break;
            
        case 'clear':
            clearCart();
            break;
    }
    
    // Redirect to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get cart items
$cartItems = getCartItems();
$cartTotal = getCartTotal();
$cartCount = getCartItemCount();

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<?php 
// Include the database connection notice component right after the header
// This needs $db_connected which should be available from settings.php (included via header/cart.php)
include __DIR__ . '/../includes/components/db_notice.php'; 
?>

<div class="container my-5">
    <h1>Your Shopping Cart</h1>
    
    <?php if (empty($cartItems)): ?>
    <div class="alert alert-info">
        Your cart is empty. <a href="products.php">Continue shopping</a>
    </div>
    <?php else: ?>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Cart items -->
            <div class="card mb-4">
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $cartItemId => $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($item['image'])): ?>
                                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="img-fluid rounded" style="max-width: 60px; max-height: 60px;">
                                        <?php else: ?>
                                        <div class="bg-light rounded" style="width: 60px; height: 60px;"></div>
                                        <?php endif; ?>
                                        <div class="ms-3">
                                            <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                            <?php if (!empty($item['options'])): ?>
                                            <small class="text-muted">
                                                <?php 
                                                $optionTexts = [];
                                                foreach ($item['options'] as $optionName => $optionValue) {
                                                    $optionTexts[] = htmlspecialchars($optionName) . ': ' . htmlspecialchars($optionValue);
                                                }
                                                echo implode(', ', $optionTexts);
                                                ?>
                                            </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">$<?= number_format($item['price'] / 100, 2) ?></td>
                                <td class="text-center">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="cart_item_id" value="<?= htmlspecialchars($cartItemId) ?>">
                                        <div class="input-group input-group-sm" style="width: 100px;">
                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" data-action="decrease">-</button>
                                            <input type="number" name="quantity" class="form-control text-center quantity-input" value="<?= $item['quantity'] ?>" min="1" max="99">
                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" data-action="increase">+</button>
                                        </div>
                                    </form>
                                </td>
                                <td class="text-center">$<?= number_format(($item['price'] * $item['quantity']) / 100, 2) ?></td>
                                <td class="text-end">
                                    <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="cart_item_id" value="<?= htmlspecialchars($cartItemId) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="d-flex justify-content-between">
                        <a href="products.php" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                        </a>
                        <form method="post">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-trash me-2"></i>Clear Cart
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order summary -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal</span>
                        <span>$<?= number_format($cartTotal / 100, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping</span>
                        <span>Free</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total</strong>
                        <strong>$<?= number_format($cartTotal / 100, 2) ?></strong>
                    </div>
                    <a href="checkout.php" class="btn btn-primary w-100">
                        <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<!-- Cart quantity update script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle quantity buttons
    document.querySelectorAll('.quantity-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('.quantity-input');
            let value = parseInt(input.value);
            
            if (this.dataset.action === 'increase') {
                input.value = Math.min(value + 1, 99);
            } else {
                input.value = Math.max(value - 1, 1);
            }
            
            // Submit the form
            this.closest('form').submit();
        });
    });
    
    // Auto-submit on quantity change
    document.querySelectorAll('.quantity-input').forEach(function(input) {
        input.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> 