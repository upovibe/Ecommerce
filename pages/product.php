<?php
require_once __DIR__ . '/../utils/database.php';
require_once __DIR__ . '/../utils/cart.php';
require_once __DIR__ . '/../utils/products.php';

// Process add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Collect product options if any
    $options = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'option_') === 0 && !empty($value)) {
            $optionName = substr($key, 7); // Remove 'option_' prefix
            $options[$optionName] = $value;
        }
    }
    
    if ($productId > 0) {
        addToCart($productId, $quantity, $options);
        header('Location: cart.php');
        exit;
    }
}

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

if ($productId > 0) {
    // Get product details
    $product = getProductDetails($productId);
}

// If product not found, redirect to products page
if (!$product) {
    header('Location: products.php');
    exit;
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <?php if (!empty($product['image'])): ?>
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid rounded">
            <?php else: ?>
            <div class="bg-light rounded" style="height: 400px; display: flex; align-items: center; justify-content: center;">
                <span class="text-muted">No image available</span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-md-6">
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="mb-3">
                <span class="badge bg-primary"><?= htmlspecialchars($product['category']) ?></span>
                <?php if ($product['in_stock']): ?>
                <span class="badge bg-success">In Stock</span>
                <?php else: ?>
                <span class="badge bg-danger">Out of Stock</span>
                <?php endif; ?>
            </div>
            
            <p class="fs-4 fw-bold text-primary mb-4">$<?= number_format($product['price'] / 100, 2) ?></p>
            
            <p class="mb-4"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            
            <?php if ($product['in_stock']): ?>
            <form method="post" class="mb-4">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <?php if (!empty($product['options'])): ?>
                <div class="mb-3">
                    <?php foreach ($product['options'] as $optionName => $optionValues): ?>
                    <div class="mb-2">
                        <label for="option_<?= htmlspecialchars($optionName) ?>" class="form-label"><?= htmlspecialchars(ucfirst($optionName)) ?></label>
                        <select name="option_<?= htmlspecialchars($optionName) ?>" id="option_<?= htmlspecialchars($optionName) ?>" class="form-select">
                            <?php foreach ($optionValues as $value): ?>
                            <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($value) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="d-flex align-items-center mb-3">
                    <label for="quantity" class="form-label me-3 mb-0">Quantity:</label>
                    <div class="input-group" style="width: 130px;">
                        <button type="button" class="btn btn-outline-secondary" id="decrease-quantity">-</button>
                        <input type="number" name="quantity" id="quantity" class="form-control text-center" value="1" min="1" max="99">
                        <button type="button" class="btn btn-outline-secondary" id="increase-quantity">+</button>
                    </div>
                </div>
                
                <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg">
                    <i class="bi bi-cart-plus me-2"></i>Add to Cart
                </button>
            </form>
            <?php else: ?>
            <div class="alert alert-warning">
                This product is currently out of stock. Please check back later.
            </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <h4>Product Details</h4>
                <ul class="list-group list-group-flush">
                    <?php if (!empty($product['sku'])): ?>
                    <li class="list-group-item d-flex">
                        <strong class="me-2">SKU:</strong>
                        <span><?= htmlspecialchars($product['sku']) ?></span>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['weight'])): ?>
                    <li class="list-group-item d-flex">
                        <strong class="me-2">Weight:</strong>
                        <span><?= htmlspecialchars($product['weight']) ?></span>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (!empty($product['dimensions'])): ?>
                    <li class="list-group-item d-flex">
                        <strong class="me-2">Dimensions:</strong>
                        <span><?= htmlspecialchars($product['dimensions']) ?></span>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Quantity controls script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease-quantity');
    const increaseBtn = document.getElementById('increase-quantity');
    
    decreaseBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        quantityInput.value = Math.max(value - 1, 1);
    });
    
    increaseBtn.addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        quantityInput.value = Math.min(value + 1, 99);
    });
    
    quantityInput.addEventListener('change', function() {
        let value = parseInt(this.value);
        if (isNaN(value) || value < 1) {
            this.value = 1;
        } else if (value > 99) {
            this.value = 99;
        }
    });
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?> 