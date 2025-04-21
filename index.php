<?php
require_once 'config/settings.php';

require_once __DIR__ . '/includes/db_notice.php'; // Display notices if any
require_once __DIR__ . '/api/store_api.php';      // Load store content function
require_once __DIR__ . '/api/category_api.php';    // Load category data function and fetch data
require_once __DIR__ . '/api/whatsapp.php';      // Load WhatsApp utilities

// Include header
require_once __DIR__ . '/includes/header.php';

// Fetch other page content
$heroTitle = getStoreContent('hero_title');
$heroSubtitle = getStoreContent('hero_subtitle');
$heroImage = getStoreContent('hero_image');
$featuredTitle = getStoreContent('featured_title');
$featuredSubtitle = getStoreContent('featured_subtitle');
$aboutTitle = getStoreContent('about_title');
$aboutContent = getStoreContent('about_content'); // Note: This might contain HTML
$aboutImage = getStoreContent('about_image');

// Generate WhatsApp link (used in Hero)
$whatsappLink = generateWhatsAppLink(STORE_SETTINGS['whatsapp_number'] ?? null);

?>

<!-- Hero Section -->
<section class="bg-white py-16 md:py-24 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:items-center lg:gap-12">
            <!-- Text Content (Left) -->
            <div class="lg:w-1/2 text-center lg:text-left mb-10 lg:mb-0" data-aos="fade-right">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-4 leading-tight" data-aos="fade-right" data-aos-delay="100">
                    <?= htmlspecialchars($heroTitle) ?>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8" data-aos="fade-right" data-aos-delay="200">
                    <?= htmlspecialchars($heroSubtitle) ?>
                </p>
                <a href="/products.php" 
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
                <img src="<?= htmlspecialchars($heroImage) ?>" alt="Hero Image" class="w-full h-auto rounded-xl object-cover max-h-[500px]">
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories Section -->
<section class="py-20 md:py-24 bg-gray-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl"><?= htmlspecialchars($featuredTitle) ?></h2>
            <p class="mt-3 max-w-2xl mx-auto text-xl text-gray-500 sm:mt-4"><?= htmlspecialchars($featuredSubtitle) ?></p>
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
                            continue; 
                        }
                        // Use null coalescing operator for safety
                        $categoryId = $category['id'] ?? null; 
                        $categoryName = $category['name'] ?? 'Unknown Category';
                        $categorySlug = $category['slug'] ?? 'unknown-category'; // Define slug variable
                        $categoryImage = $category['image'] ?? '/assets/images/placeholder.png';
                        $subcategoryCount = $category['subcategory_count'] ?? 0;
                    ?>
                    <div class="swiper-slide">
                         <a href="/products.php?category=<?= htmlspecialchars($categorySlug) ?>" 
                           target="_blank" 
                           data-category-id="<?= $categoryId ?>" 
                           data-category-name="<?= htmlspecialchars($categoryName) ?>" 
                           data-category-slug="<?= htmlspecialchars($categorySlug) ?>" 
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
        </div>
        
    </div>
</section>

<!-- About Section -->
<section class="py-20 md:py-24 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:flex lg:items-center lg:justify-between">
            <div class="lg:w-1/2 lg:pr-12 mb-8 lg:mb-0" data-aos="fade-right">
                <h2 class="text-3xl font-extrabold text-gray-900 sm:text-4xl mb-6"><?= htmlspecialchars($aboutTitle) ?></h2>
                <div class="prose prose-lg text-gray-500">
                    <?= $aboutContent ?> 
                </div>
                <div class="mt-8">
                    <a href="<?= htmlspecialchars($whatsappLink) ?>" 
                       target="_blank" 
                       class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5 <?= $whatsappLink === '#' ? 'opacity-50 cursor-not-allowed' : '' ?>">
                        <svg class="size-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Contact Us on WhatsApp
                    </a>
                </div>
            </div>
            <div class="lg:w-1/2 mt-8 lg:mt-0" data-aos="fade-left">
                <img src="<?= htmlspecialchars($aboutImage) ?>" alt="About our store" class="rounded-xl shadow w-full h-auto object-cover max-h-[450px]">
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once 'includes/footer.php';
?>