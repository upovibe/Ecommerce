<?php
require_once 'config/settings.php';

// Get featured categories
function getFeaturedCategories() {
    global $conn, $db_connected;
    
    $categories = [];
    
    // Try to get categories from database
    if ($db_connected && $conn) {
        $sql = "SELECT c.id, c.name, COUNT(p.id) as product_count 
                FROM categories c
                LEFT JOIN products p ON p.category_id = c.id
                WHERE c.featured = 1
                GROUP BY c.id
                ORDER BY c.display_order
                LIMIT 6";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'product_count' => $row['product_count']
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
                'product_count' => 3
            ],
            [
                'id' => 2,
                'name' => 'Groceries',
                'image' => '/assets/images/demo/groceries-category.jpg',
                'product_count' => 3
            ],
            [
                'id' => 3,
                'name' => 'Shoes',
                'image' => '/assets/images/demo/shoes-category.jpg',
                'product_count' => 3
            ]
        ];
    }
    
    // Ensure image key exists even if fetched from DB (set to placeholder)
    foreach ($categories as &$category) {
        if (!isset($category['image'])) {
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

// Include navbar
include_once 'includes/navbar.php';

// Show database connection notice if needed
if (!$db_connected):
?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
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
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            <?php foreach ($featuredCategories as $category): ?>
                <a href="/pages/products.php?category=<?= $category['id'] ?>" 
                   class="group block rounded-lg overflow-hidden shadow-lg hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 ease-in-out transform hover:-translate-y-1">
                    <div class="relative h-64 w-full">
                        <img src="<?= htmlspecialchars($category['image'] ?? '/assets/images/placeholder.png') ?>" 
                             alt="<?= htmlspecialchars($category['name']) ?>" 
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/60 to-transparent opacity-70 group-hover:opacity-80 transition-opacity duration-300"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <h3 class="text-lg font-semibold text-white mb-1"><?= htmlspecialchars($category['name']) ?></h3>
                            <p class="text-sm text-gray-200"><?= $category['product_count'] ?> Products</p>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
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
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
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