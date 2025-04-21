<?php
require_once __DIR__ . '/config/settings.php'; // Ensure settings are loaded first
require_once __DIR__ . '/api/store_api.php';      // Load store content function
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db_notice.php'; // Display notices if any

// Fetch banner data
$bannerImage = getStoreContent('product_page_banner_image');
$bannerTitle = getStoreContent('product_banner_title');
$bannerSubtitle = getStoreContent('product_banner_subtitle');
?>

<!-- Product Page Banner -->
<section 
    class="relative bg-cover bg-center py-24 md:py-32"
    style="background-image: url('<?= htmlspecialchars($bannerImage) ?>');">
    <div class="absolute inset-0 bg-black/50"></div> <!-- Dark overlay -->
    <div class="relative container mx-auto px-4 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-4"><?= htmlspecialchars($bannerTitle) ?></h1>
        <p class="text-lg md:text-xl max-w-2xl mx-auto"><?= htmlspecialchars($bannerSubtitle) ?></p>
    </div>
</section>

<main class="flex-grow container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Our Products</h1>

    <!-- Product listing will go here -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <!-- Example Product Card (Replace with dynamic data) -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <img src="/assets/images/placeholder.png" alt="Product Image" class="w-full h-48 object-cover">
            <div class="p-4">
                <h2 class="text-lg font-semibold text-gray-800">Product Name</h2>
                <p class="text-gray-600 mt-1">$99.99</p>
                <a href="#" class="mt-3 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors">View Details</a>
            </div>
        </div>
        
        <!-- Add more product cards here -->

    </div>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>