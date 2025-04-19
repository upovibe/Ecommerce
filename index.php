<?php
require_once 'config/settings.php';

// Get featured categories
function getFeaturedCategories() {
    global $conn, $db_connected;
    
    $categories = [];
    
    // Try to get categories from database
    if ($db_connected && $conn) {
        $sql = "SELECT 
                    c.id, 
                    c.name, 
                    c.image, 
                    (SELECT COUNT(*) FROM categories sub WHERE sub.parent_id = c.id) as subcategory_count 
                FROM categories c
                WHERE c.featured = 1 AND c.parent_id IS NULL
                ORDER BY c.display_order";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'subcategory_count' => (int)$row['subcategory_count'],
                    'image' => $row['image']
                ];
            }
        }
    }
    
    // If no categories found in database, use demo categories
    if (empty($categories)) {
        $categories = [
            [
                'id' => 1,
                'name' => 'Bags',
                'image' => '/assets/images/demo/bags-category.jpg',
                'subcategory_count' => 3
            ],
            [
                'id' => 2,
                'name' => 'Groceries',
                'image' => '/assets/images/demo/groceries-category.jpg',
                'subcategory_count' => 3
            ],
            [
                'id' => 3,
                'name' => 'Shoes',
                'image' => '/assets/images/demo/shoes-category.jpg',
                'subcategory_count' => 3
            ]
        ];
    }
    
    // Ensure image key exists even if fetched from DB (set to placeholder)
    foreach ($categories as &$category) {
        if (empty($category['image'])) {
            $category['image'] = '/assets/images/placeholder.png';
        }
    }
    unset($category);
    
    return $categories;
}

// Get store content
function getStoreContent($key) {
    global $conn, $db_connected;
    
    $content = '';
    
    // Try to get content from database
    if ($db_connected && $conn) {
        $sql = "SELECT content_value FROM store_content WHERE content_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $content = $row['content_value'];
        }
    }
    
    // Return demo content if not found in database
    if (empty($content)) {
        $demoContent = [
            'hero_title' => 'Welcome to our Online Store',
            'hero_subtitle' => 'Find everything you need, from essentials to luxuries.',
            'about_title' => 'About Our Store',
            'about_content' => '<p>We are dedicated to providing high-quality products at affordable prices. Our store features a wide range of items including bags, groceries, and shoes.</p><p>With a focus on customer satisfaction, we ensure that every purchase meets our high standards for quality and durability.</p>',
            'featured_title' => 'Shop by Category',
            'featured_subtitle' => 'Explore our popular categories and find exactly what you\'re looking for.',
            'hero_image' => '/assets/images/demo/hero-bg.jpg',
            'about_image' => '/assets/images/demo/about-image.jpg'
        ];
        
        return $demoContent[$key] ?? '';
    }
    
    return $content;
}

$featuredCategories = getFeaturedCategories();

// Include header
include_once 'includes/header.php';
?>
<!-- Add SwiperJS CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<!-- Link to Custom CSS -->
<link rel="stylesheet" href="assets/css/style.css">
<?php
// Include navbar
include_once 'includes/navbar.php';

// Show database connection notice if needed
if (!$db_connected):
?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <i data-lucide="alert-triangle" class="h-5 w-5 text-yellow-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-yellow-700">
                <strong>Note:</strong> The site is running in demo mode because the database connection failed. Demo products and content are being displayed. To connect to a database, please check your configuration in <code>config/db.php</code>.
            </p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Hero Section -->
<section class="bg-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:items-center lg:gap-12">
            <!-- Text Content (Left) -->
            <div class="lg:w-1/2 text-center lg:text-left mb-10 lg:mb-0">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-4 leading-tight">
                    <?= htmlspecialchars(getStoreContent('hero_title')) ?>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8">
                    <?= htmlspecialchars(getStoreContent('hero_subtitle')) ?>
                </p>
                <a href="/pages/products.php" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-primary hover:bg-indigo-700 transition duration-150 ease-in-out md:py-4 md:text-lg md:px-10 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    Shop Now
                </a>
            </div>
            
            <!-- Image (Right) -->
            <div class="lg:w-1/2">
                <img src="<?= htmlspecialchars(getStoreContent('hero_image')) ?>" alt="Hero Image" class="w-full h-auto rounded-xl shadow-2xl object-cover max-h-[500px]">
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories Section -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl"><?= htmlspecialchars(getStoreContent('featured_title')) ?></h2>
            <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4"><?= htmlspecialchars(getStoreContent('featured_subtitle')) ?></p>
        </div>
        
        <!-- Slider main container -->
        <div class="swiper featured-categories-slider relative group"> <!-- Added relative group -->
            <!-- Additional required wrapper -->
            <div class="swiper-wrapper">
                <!-- Slides -->
                <?php foreach ($featuredCategories as $category): ?>
                    <div class="swiper-slide">
                         <a href="#" data-category-id="<?= $category['id'] ?>" 
                           data-category-name="<?= htmlspecialchars($category['name']) ?>" 
                           onclick="fetchSubcategories(event, this)"
                           class="category-card group block rounded-lg overflow-hidden shadow-lg hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 ease-in-out transform hover:-translate-y-1 bg-white">
                            <!-- Existing Card Content -->
                             <div class="category-parent-content relative h-64 w-full">
                                <img src="<?= htmlspecialchars($category['image'] ?? '/assets/images/placeholder.png') ?>" 
                                     alt="<?= htmlspecialchars($category['name']) ?>" 
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent opacity-70 group-hover:opacity-80 transition-opacity duration-300"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-4">
                                    <h3 class="text-lg font-semibold text-white mb-1"><?= htmlspecialchars($category['name']) ?></h3>
                                    <p class="text-sm text-gray-200"><?= $category['subcategory_count'] ?> Subcategories</p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="text-center mt-12">
            <a href="/pages/products.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-primary hover:bg-indigo-700">
                View All Products
            </a>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:items-center lg:justify-between">
            <div class="lg:w-1/2 lg:pr-12 mb-8 lg:mb-0">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl mb-6"><?= htmlspecialchars(getStoreContent('about_title')) ?></h2>
                <div class="prose prose-lg text-gray-500">
                    <?= getStoreContent('about_content') ?>
                </div>
                <div class="mt-8">
                    <a href="https://wa.me/<?= STORE_SETTINGS['whatsapp_number'] ?? '2348012345678' ?>" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                        <i data-lucide="message-circle" class="w-5 h-5 mr-2"></i>
                        Contact Us on WhatsApp
                    </a>
                </div>
            </div>
            <div class="lg:w-1/2 mt-8 lg:mt-0">
                <img src="<?= htmlspecialchars(getStoreContent('about_image')) ?>" alt="About our store" class="rounded-xl shadow-xl w-full h-auto object-cover max-h-[450px]">
            </div>
        </div>
    </div>
</section>

<!-- Include modals -->
<?php include_once 'modals/cartModal.php'; ?>
<?php include_once 'modals/orderModal.php'; ?>

<?php
// Include footer
include_once 'includes/footer.php';
?>
<script src="assets/js/swiper.js" defer></script>
<script src="assets/js/main.js" defer></script>
<script>
    // Swiper initialization removed - moved to assets/js/swiper.js

    // Subcategory fetching and display logic moved to assets/js/main.js

</script> 