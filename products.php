<?php
require_once 'config/settings.php';

require_once __DIR__ . '/includes/db_notice.php'; // Display notices if any
require_once __DIR__ . '/api/store_api.php';      // Load store content function
require_once __DIR__ . '/api/whatsapp.php';     // Load WhatsApp utilities
require_once __DIR__ . '/api/category_api.php';  // Load category data function

// Include header
require_once __DIR__ . '/includes/header.php';

// Function to get the first image from product image data
function getProductThumbnail($imageData) {
    if (!$imageData) {
        return '/assets/images/product-placeholder.png';
    }
    
    // Handle JSON array format (new format)
    if (is_string($imageData) && (strpos($imageData, '[') === 0)) {
        try {
            $imageArray = json_decode($imageData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($imageArray) && count($imageArray) > 0) {
                return $imageArray[0]; // Return first image
            }
        } catch (Exception $e) {
            error_log('Error parsing image JSON: ' . $e->getMessage());
        }
    }
    
    // Handle array format (if already parsed)
    if (is_array($imageData) && count($imageData) > 0) {
        return $imageData[0]; // Return first image
    }
    
    // Handle single string format (legacy format)
    if (is_string($imageData) && trim($imageData) !== '') {
        return $imageData;
    }
    
    // Fallback to placeholder
    return '/assets/images/product-placeholder.png';
}

// Fetch banner data
$bannerImage = getStoreContent('product_page_banner_image');
$bannerTitle = getStoreContent('product_banner_title');
$bannerSubtitle = getStoreContent('product_banner_subtitle');

// Fetch featured category data
$featuredCategories = getFeaturedCategories();

// Get selected category slug from query string
$selectedCategory = $_GET['category'] ?? null;

// Get demo data if in demo mode
$isDemoMode = !$db_connected;
if ($isDemoMode) {
    $demoDataFile = __DIR__ . '/config/demo_data.json';
    $demoData = json_decode(file_get_contents($demoDataFile), true);
    $initialProducts = $demoData['products'];
    if (!empty($selectedCategory)) {
        // Filter demo products by category or parent category slug
        $initialProducts = array_values(array_filter(
            $initialProducts,
            function($p) use ($selectedCategory) {
                return ($p['category_slug'] === $selectedCategory)
                    || ($p['parent_category_slug'] === $selectedCategory);
            }
        ));
    }
} else {
    // Function to fetch initial products from database
    function getInitialProducts() {
        global $conn, $selectedCategory;
        $products = [];
        
        $sql = "SELECT 
                p.id, 
                p.name, 
                p.slug as product_slug,
                p.description, 
                p.price, 
                p.image,
                p.category_id,
                p.stock,
                p.is_active,
                p.backorder,
                p.original_price,
                p.discount_percentage,
                c.name as category_name,
                c.slug as category_slug,
                parent.name as parent_category_name,
                parent.slug as parent_category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN categories parent ON c.parent_id = parent.id
            WHERE p.is_active = 1";
        if (!empty($selectedCategory)) {
            $slug = $conn->real_escape_string($selectedCategory);
            $sql .= " AND (c.slug = '{$slug}' OR parent.slug = '{$slug}')";
        }
        $sql .= " ORDER BY p.name ASC";
            
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'slug' => $row['product_slug'],
                    'description' => $row['description'],
                    'price' => (float)$row['price'],
                    'image' => $row['image'] ?: '/assets/images/product-placeholder.png',
                    'category_id' => (int)$row['category_id'],
                    'category_name' => $row['category_name'],
                    'category_slug' => $row['category_slug'],
                    'parent_category_name' => $row['parent_category_name'],
                    'parent_category_slug' => $row['parent_category_slug'],
                    'stock' => (int)$row['stock'],
                    'is_active' => (bool)$row['is_active'],
                    'backorder' => (bool)$row['backorder'],
                    'original_price' => $row['original_price'] ? (float)$row['original_price'] : null,
                    'discount_percentage' => $row['discount_percentage'] ? (float)$row['discount_percentage'] : null
                ];
            }
        }
        return $products;
    }
    
    // Fetch initial products from database
    $initialProducts = getInitialProducts();
}
?>

<div class="spacey-4 pb-24">
    <!-- Product Page Banner -->
    <section
        class="relative bg-cover bg-center py-24 md:py-32"
        style="background-image: url('<?= htmlspecialchars($bannerImage) ?>');">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative max-w-7xl mx-auto px-4 text-center space-y-4 text-white">
            <h1 class="text-4xl md:text-5xl font-bold"><?= htmlspecialchars($bannerTitle) ?></h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto"><?= htmlspecialchars($bannerSubtitle) ?></p>
        </div>
    </section>

    <!-- Product List -->
    <section class="flex-grow max-w-7xl mx-auto px-4 py-8 w-full">
        <div class="flex flex-col items-start mb-8 gap-3">
            <h2 id="product-list-title" class="text-lg md:text-2xl font-semibold text-gray-800 whitespace-nowrap">Products</h2>
            <!-- Search Input -->
            <div class="flex w-full justify-between items-center gap-4 bg-white p-1 rounded-lg shadow-md">
                <div class="relative flex-grow w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                    </div>
                    <input type="search"
                        id="product-search"
                        placeholder="Search products..."
                        class="block w-full pl-10 pr-3 py-1.5 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <!-- Add the filter button here -->
                <?php require_once __DIR__ . '/includes/product_filters.php'; ?>
            </div>
        </div>

        <!-- No Products Feedback -->
        <?php if (empty($initialProducts)): ?>
        <div class="text-center py-12 rounded-lg shadow-sm">
            <i data-lucide="package-x" class="w-16 h-16 mx-auto text-gray-300"></i>
            <p class="mt-4 text-lg font-medium text-gray-600">No Products Available</p>
            <p class="mt-1 text-sm text-gray-500">There are no products in the store yet.</p>
        </div>
        <?php endif; ?>

        <div id="product-grid"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            data-currency-symbol="<?= htmlspecialchars(STORE_SETTINGS['currency_symbol'] ?? '$') ?>"
            data-initial-products='<?= htmlspecialchars(json_encode($initialProducts)) ?>'
            data-is-demo="<?= $isDemoMode ? 'true' : 'false' ?>">
            <!-- Products will be rendered here -->
            <?php if (!empty($initialProducts)): ?>
                <?php foreach ($initialProducts as $product): ?>
                    <div class="product-card bg-white rounded-lg shadow overflow-hidden transition-shadow duration-300 hover:shadow-lg flex flex-col cursor-pointer"
                         data-product-id="<?= htmlspecialchars($product['id']) ?>"
                         data-product-name="<?= htmlspecialchars($product['name']) ?>"
                         data-product-price="<?= htmlspecialchars($product['price']) ?>"
                         data-product-image="<?= htmlspecialchars($product['image']) ?>"
                         data-product-description="<?= htmlspecialchars($product['description']) ?>"
                         data-product-slug="<?= htmlspecialchars($product['slug']) ?>"
                         data-category-name="<?= htmlspecialchars($product['category_name']) ?>"
                         data-stock="<?= htmlspecialchars($product['stock']) ?>"
                         data-is-active="<?= htmlspecialchars($product['is_active'] ? 'true' : 'false') ?>"
                         data-backorder="<?= htmlspecialchars($product['backorder'] ? 'true' : 'false') ?>"
                         data-original-price="<?= htmlspecialchars($product['original_price'] ?? '') ?>"
                         data-discount-percentage="<?= htmlspecialchars($product['discount_percentage'] ?? '') ?>"
                         data-product-options='<?= htmlspecialchars(json_encode($product['options'] ?? new stdClass())) ?>'>
                        <div class="product-image-container relative h-56 bg-gray-200">
                            <?php if (!empty($product['discount_percentage'])): ?>
                                <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full"><?= (int)$product['discount_percentage'] ?>%<span class="hidden md:inline"> OFF</span></span>
                            <?php endif; ?>

                            <?php if (!$product['is_active']): ?>
                                <span class="absolute top-2 left-2 bg-gray-700 text-white text-xs font-semibold px-2 py-0.5 rounded">Unavailable</span>
                            <?php elseif ($product['stock'] === 0): ?>
                                <?php if ($product['backorder']): ?>
                                    <span class="absolute top-2 left-2 bg-yellow-500 text-white text-xs font-semibold px-2 py-0.5 rounded">Backorder</span>
                                <?php else: ?>
                                    <span class="absolute top-2 left-2 bg-gray-500 text-white text-xs font-semibold px-2 py-0.5 rounded">Out of Stock</span>
                                <?php endif; ?>
                            <?php elseif ($product['stock'] > 0): ?>
                                <span class="absolute top-2 left-2 bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded"><?= $product['stock'] ?> in Stock</span>
                            <?php endif; ?>

                            <img src="<?= htmlspecialchars(getProductThumbnail($product['image'])) ?>"
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 class="product-image w-full h-full object-cover"
                                 loading="lazy">
                            <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/50 to-transparent">
                                <h3 class="product-name font-semibold text-sm md:text-base text-white truncate pointer-events-none"><?= htmlspecialchars($product['name']) ?></h3>
                            </div>
                        </div>
                        <div class="product-details px-4 pb-4 pt-2 flex flex-col flex-grow gap-2">
                            <div class="product-header flex justify-between items-center mt-1">
                                <div class="price-container">
                                    <?php if (!empty($product['original_price'])): ?>
                                        <span class="text-gray-500 line-through text-sm"><?= htmlspecialchars(STORE_SETTINGS['currency_symbol'] ?? '$') ?><?= number_format($product['original_price'], 2) ?></span>
                                    <?php endif; ?>
                                    <span class="text-gray-900 font-semibold"><?= htmlspecialchars(STORE_SETTINGS['currency_symbol'] ?? '$') ?><?= number_format($product['price'], 2) ?></span>
                                </div>
                                <button class="add-to-cart-icon-btn p-1.5 rounded-full text-gray-500 hover:text-primary hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary transition-colors"
                                        data-product-id="<?= htmlspecialchars($product['id']) ?>"
                                        data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                        data-product-price="<?= htmlspecialchars($product['price']) ?>"
                                        data-product-image="<?= htmlspecialchars($product['image']) ?>"
                                        <?= (!$product['is_active'] || ($product['stock'] === 0 && !$product['backorder'])) ? 'disabled' : '' ?>>
                                    <i data-lucide="shopping-cart" class="size-4"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Include Product Modal -->
    <?php require_once __DIR__ . '/modals/product_modal.php'; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- Load the product list script -->
<script src="/assets/js/product-management.js" defer></script>