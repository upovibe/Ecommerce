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
    
    // If no categories found in database, try loading from demo JSON file
    if (empty($categories)) {
        $jsonFilePath = __DIR__ . '/config/demo_data.json';
        if (file_exists($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $decodedData = json_decode($jsonContent, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decodedData['categories']) && is_array($decodedData['categories'])) {
                $categories = $decodedData['categories']; // Extract only the categories array
                // Calculate subcategory_count for demo data
                foreach ($categories as &$category) {
                     if (isset($category['subcategories']) && is_array($category['subcategories'])) {
                         $category['subcategory_count'] = count($category['subcategories']);
                     } else {
                         $category['subcategory_count'] = 0; // Default if no subcategories key or not an array
                     }
                     // Ensure other expected keys exist, provide defaults if necessary (optional, good practice)
                     $category['id'] = $category['id'] ?? null; 
                     $category['name'] = $category['name'] ?? 'Unnamed Category';
                     $category['image'] = $category['image'] ?? null; // Default will be handled below
                }
                unset($category); // Unset reference
            } else {
                // Handle JSON decode error or missing 'categories' key
                error_log('Error decoding demo categories JSON or missing "categories" key: ' . json_last_error_msg());
                $categories = []; // Fallback to empty array
            }
        } else {
             // Fallback: Keep the original hardcoded array if JSON file doesn't exist (optional)
             // Or set to empty array if JSON is the only source for demo data
             error_log('Demo data JSON file not found: ' . $jsonFilePath);
             $categories = []; // Fallback to empty array
        }
    }
    
    // Ensure image key exists even if fetched from DB or JSON (set to placeholder)
    foreach ($categories as &$category) {
        // Ensure category is an array before proceeding
        if (!is_array($category)) {
            // Log or handle the case where an element in $categories is not an array
            error_log('Invalid category data encountered: ' . print_r($category, true));
            continue; // Skip this iteration
        }
        if (empty($category['image'])) {
            $category['image'] = '/assets/images/demo/bags-category.png';
        }
        // Ensure subcategory_count is set if it wasn't (e.g., from DB query where count might be null)
         $category['subcategory_count'] = $category['subcategory_count'] ?? 0;
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
            'hero_image' => '/assets/images/demo/hero-bg.png',
            'about_image' => '/assets/images/demo/about-image.png'
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
<!-- Add AOS CSS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<!-- Link to Custom CSS -->
<link rel="stylesheet" href="assets/css/style.css">
<?php
// Include the database connection notice component
include __DIR__ . '/includes/db_notice.php'; 
?>

<!-- Hero Section -->
<section class="bg-white py-16 md:py-24 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:items-center lg:gap-12">
            <!-- Text Content (Left) -->
            <div class="lg:w-1/2 text-center lg:text-left mb-10 lg:mb-0" data-aos="fade-right">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-4 leading-tight" data-aos="fade-right" data-aos-delay="100">
                    <?= htmlspecialchars(getStoreContent('hero_title')) ?>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8" data-aos="fade-right" data-aos-delay="200">
                    <?= htmlspecialchars(getStoreContent('hero_subtitle')) ?>
                </p>
                <a href="/pages/products.php" 
                   class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md transition duration-150 ease-in-out md:py-4 md:text-lg md:px-10 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5" 
                   style="background-color: <?= htmlspecialchars(STORE_SETTINGS['theme_color']) ?>; color: <?= htmlspecialchars(STORE_SETTINGS['brand_text_color']) ?>;"
                   data-aos="fade-up" data-aos-delay="300">
                    <!-- Replace SVG with Lucide icon -->
                    <i data-lucide="shopping-cart" class="size-5 mr-2"></i>
                    Shop Now
                </a>
            </div>
            
            <!-- Image (Right) -->
            <div class="lg:w-1/2" data-aos="fade-left">
                <img src="<?= htmlspecialchars(getStoreContent('hero_image')) ?>" alt="Hero Image" class="w-full h-auto rounded-xl object-cover max-h-[500px]">
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories Section -->
<section class="py-20 md:py-24 bg-gray-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl"><?= htmlspecialchars(getStoreContent('featured_title')) ?></h2>
            <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4"><?= htmlspecialchars(getStoreContent('featured_subtitle')) ?></p>
        </div>
        
        <!-- Slider main container -->
        <div class="swiper featured-categories-slider relative group" data-aos="fade-up" data-aos-delay="100">
            <!-- Additional required wrapper -->
            <div class="swiper-wrapper">
                <!-- Slides -->
                <?php foreach ($featuredCategories as $category): ?>
                    <?php 
                        // Ensure $category is an array before trying to access keys
                        if (!is_array($category)) {
                            // Skip this iteration or log an error if $category is not an array
                            // error_log('Invalid category data in loop: ' . print_r($category, true)); 
                            continue; 
                        }
                        // Use null coalescing operator for safety
                        $categoryId = $category['id'] ?? null; 
                        $categoryName = $category['name'] ?? 'Unknown Category';
                        $categoryImage = $category['image'] ?? '/assets/images/placeholder.png';
                        $subcategoryCount = $category['subcategory_count'] ?? 0;
                    ?>
                    <div class="swiper-slide">
                         <a href="/pages/products.php?category=<?= $categoryId ?>" 
                           target="_blank" 
                           data-category-id="<?= $categoryId ?>" 
                           data-category-name="<?= htmlspecialchars($categoryName) ?>" 
                           class="category-card group block rounded-lg overflow-hidden shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 ease-in-out transform hover:-translate-y-1 bg-white">
                            <!-- Existing Card Content -->
                             <div class="category-parent-content relative h-64 w-full">
                                <img src="<?= htmlspecialchars($categoryImage) ?>" 
                                     alt="<?= htmlspecialchars($categoryName) ?>" 
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent opacity-70 group-hover:opacity-80 transition-opacity duration-300"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-4">
                                    <h3 class="text-lg font-semibold text-white mb-1"><?= htmlspecialchars($categoryName) ?></h3>
                                    <p class="text-sm text-gray-200"><?= $subcategoryCount ?> Subcategories</p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <div class="text-center mt-12" data-aos="fade-up">
            <a href="/pages/products.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm"
               style="background-color: <?= htmlspecialchars(STORE_SETTINGS['theme_color']) ?>; color: <?= htmlspecialchars(STORE_SETTINGS['brand_text_color']) ?>;">
                 <i data-lucide="layout-grid" class="size-5 mr-2"></i>
                View All Products
            </a>
        </div>
        </div>
        
    </div>
</section>

<!-- About Section -->
<section class="py-20 md:py-24 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:items-center lg:justify-between">
            <div class="lg:w-1/2 lg:pr-12 mb-8 lg:mb-0" data-aos="fade-right">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl mb-6"><?= htmlspecialchars(getStoreContent('about_title')) ?></h2>
                <div class="prose prose-lg text-gray-500">
                    <?= getStoreContent('about_content') ?>
                </div>
                <div class="mt-8">
                    <a href="https://wa.me/<?= STORE_SETTINGS['whatsapp_number'] ?? '2348012345678' ?>" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5">
                    <svg class="size-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Contact Us on WhatsApp
                    </a>
                </div>
            </div>
            <div class="lg:w-1/2 mt-8 lg:mt-0" data-aos="fade-left">
                <img src="<?= htmlspecialchars(getStoreContent('about_image')) ?>" alt="About our store" class="rounded-xl shadow w-full h-auto object-cover max-h-[450px]">
            </div>
        </div>
    </div>
</section>

<!-- Include modals -->
<?php include_once 'modals/cartModal.php'; ?>
<?php include_once 'modals/completeOrderModal.php'; ?>
<?php include_once 'modals/searchModal.php'; ?>

<?php
// Include footer
include_once 'includes/footer.php';
?>
<script src="assets/js/swiper.js" defer></script>
<script src="assets/js/main.js" defer></script>
<!-- Add AOS JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize AOS
    AOS.init({
        duration: 800, // Animation duration
        once: true // Only animate elements once
    });

    // Subcategory fetching and display logic removed from here

    // Render Lucide icons added via PHP/HTML
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script> 