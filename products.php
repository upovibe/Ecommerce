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
            <a href="https://api.whatsapp.com/send?phone=<?= STORE_SETTINGS['whatsapp_number'] ?>&text=Hi!%20%F0%9F%91%8B%20I%27m%20browsing%20your%20*Products*%20page%20and%20have%20a%20question.%20Could%20you%20help%20me%3F%20%F0%9F%9B%8D%EF%B8%8F"
                target="_blank"
                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5">
                <svg class="size-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                </svg>
                Contact Us on WhatsApp
            </a>
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