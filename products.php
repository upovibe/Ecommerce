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

        <div id="product-grid"
            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            data-currency-symbol="<?= htmlspecialchars(STORE_SETTINGS['currency_symbol'] ?? '$') ?>">
            <!-- Loading State -->
            <div class="col-span-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php for ($i = 0; $i < 8; $i++): ?>
                    <div class="product-card bg-white rounded-lg shadow overflow-hidden animate-pulse transition-shadow duration-300 hover:shadow-lg flex flex-col cursor-pointer w-full">
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

    <!-- Include Product Modal -->
    <?php require_once __DIR__ . '/modals/product_modal.php'; ?>

</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>

<!-- Load the product list script -->
<script src="/assets/js/product-management.js" defer></script>