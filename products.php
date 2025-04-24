<?php
require_once 'config/settings.php';

require_once __DIR__ . '/includes/db_notice.php'; // Display notices if any
require_once __DIR__ . '/api/store_api.php';      // Load store content function
require_once __DIR__ . '/api/whatsapp.php';     // Load WhatsApp utilities
require_once __DIR__ . '/api/category_api.php';  // Load category data function

// Include header
require_once __DIR__ . '/includes/header.php';

// Fetch banner data
$bannerImage = getStoreContent('product_page_banner_image');
$bannerTitle = getStoreContent('product_banner_title');
$bannerSubtitle = getStoreContent('product_banner_subtitle');

// Fetch featured category data
$featuredCategories = getFeaturedCategories();
?>


<div class="spacey-4 pb-24">

    <!-- Product Page Banner -->
    <section
        class="relative bg-cover bg-center py-24 md:py-32"
        style="background-image: url('<?= htmlspecialchars($bannerImage) ?>');">
        <div class="absolute inset-0 bg-black/50"></div> <!-- Dark overlay -->
        <div class="relative max-w-7xl mx-auto px-4 text-center space-y-4 text-white">
            <h1 class="text-4xl md:text-5xl font-bold"><?= htmlspecialchars($bannerTitle) ?></h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto"><?= htmlspecialchars($bannerSubtitle) ?></p>
        </div>
    </section>

    <!-- Product List -->
    <section class="flex-grow max-w-7xl mx-auto px-4 py-8 w-full">
        <div class="flex justify-between items-center mb-4">
            <h2 id="product-list-title" class="text-lg md:text-2xl font-semibold text-gray-800">Products</h2>
            <button id="reset-filters-title-btn"
                class="hidden items-center gap-1 p-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500 transition-colors">
                <i data-lucide="refresh-cw" class="size-4"></i>
                <span class="hidden md:inline text-nowrap">Reset Filters</span>
            </button>
        </div>

        <!-- Parent Category Tabs -->
        <div id="parent-category-tabs" class="mb-4 flex flex-wrap gap-2 border-b border-gray-200 pb-3">
            <button
                data-category-slug="all"
                class="category-tab px-4 py-2 text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 active">
                All Products
            </button>
            <?php foreach ($featuredCategories as $category): ?>
                <?php if (!is_array($category) || empty($category['slug'])) continue; ?>
                <button
                    data-category-slug="<?= htmlspecialchars($category['slug']) ?>"
                    class="category-tab px-4 py-2 text-sm font-medium rounded-md text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <?= htmlspecialchars($category['name'] ?? 'Unnamed Category') ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Subcategory Display Area -->
        <div id="subcategory-display" class="mb-6 flex flex-wrap gap-2 min-h-[2rem]">
            <!-- Subcategories will be loaded here by JavaScript -->
        </div>

        <div id="product-grid"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            data-currency-symbol="<?= htmlspecialchars(STORE_SETTINGS['currency_symbol'] ?? '$') ?>">
            <!-- Products will be loaded here by JavaScript -->
            <div id="loading-products" class="col-span-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php for ($i = 0; $i < 8; $i++): ?>
                    <div class="product-card bg-white rounded-lg shadow overflow-hidden animate-pulse transition-shadow duration-300 hover:shadow-lg flex flex-col cursor-pointer  w-full">
                        <div class="product-image-container relative h-56 bg-gray-200 w-full min-w-max">
                            <div class="absolute bg-gray-300 top-2 right-2 rounded h-5 w-12"></div>
                            <div class="absolute bg-gray-300 top-2 left-2 rounded h-5 w-14"></div>
                            <div class="absolute bg-gray-200 bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/20 to-transparent">
                                <div class="h-5 bg-gray-300 rounded w-2/3"></div>
                            </div>
                        </div>
                        <div class="product-details px-4 pb-4 pt-2 flex flex-col flex-grow gap-2">
                            <div class="product-header flex justify-between items-center mt-1">
                                <div class="h-6 bg-gray-300 rounded w-1/2"></div>
                                <div class="bg-gray-300 rounded h-6 w-6 ml-auto"></div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

</div>

<!-- Include Product Modal -->
<?php require_once __DIR__ . '/modals/product_modal.php'; ?>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

<!-- Load the product list script -->
<script src="/assets/js/product-management.js" defer></script>