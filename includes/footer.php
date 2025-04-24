    </div> <!-- Closing flex-grow div from header -->

    <?php
    // Ensure settings are included if not already (might be needed if footer is included independently)
    // If settings.php is always included via header.php, this line might be redundant, but safe to keep.
    if (!defined('STORE_SETTINGS')) {
        require_once __DIR__ . '/../config/settings.php';
    }

    $productsPath = '/products.php'; // Define path for consistency
    // Note: We don't define $isProductsActive here as footer links usually don't need active state highlighting.

    $footerBgColor = STORE_SETTINGS['theme_color'] ?? '#1F2937'; // Default bg
    $brandTextColor = STORE_SETTINGS['brand_text_color'] ?? '#FFFFFF'; // Default text
    $borderColorClass = 'border-white/20'; // Default semi-transparent white border
    ?>
    <footer class="py-8" style="background-color: <?= htmlspecialchars($footerBgColor) ?>; color: <?= htmlspecialchars($brandTextColor) ?>;" data-aos="fade-up" data-aos-offset="50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <!-- Logo and Store Name (Header Style) -->
                    <a href="/" class="flex items-center space-x-2 mb-4">
                        <img class="h-8 w-auto"
                            src="<?= htmlspecialchars(STORE_SETTINGS['store_logo'] ?? '/assets/images/logo.png') ?>"
                            alt="<?= htmlspecialchars(STORE_SETTINGS['store_name'] ?? 'E-Commerce Store') ?> Logo">
                        <span class="text-lg font-semibold" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                            <?= STORE_SETTINGS['store_name'] ?? 'E-Commerce Store' ?>
                        </span>
                    </a>
                    <!-- Store Description -->
                    <p class="text-sm" style="color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.8;">
                        <?= STORE_SETTINGS['store_description'] ?? 'Your one-stop shop for all your needs' ?>
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4" style="color: <?= htmlspecialchars($brandTextColor) ?>;">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/" class="hover:opacity-80" style="color: <?= htmlspecialchars($brandTextColor) ?>;">Home</a></li>
                        <li><a href="<?= htmlspecialchars($productsPath) ?>" class="hover:opacity-80" style="color: <?= htmlspecialchars($brandTextColor) ?>;">Products</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4" style="color: <?= htmlspecialchars($brandTextColor) ?>;">Contact Us</h3>
                    <p class="text-sm mb-2" style="color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.8;">
                        Have questions? Reach out to us on WhatsApp.
                    </p>
                    <a href="https://api.whatsapp.com/send?phone=<?= STORE_SETTINGS['whatsapp_number'] ?>&text=Hello!%20%F0%9F%91%8B%20I%20have%20a%20question%20about%20your%20store%27s%20hours%20and%20shipping%20policies.%20Could%20you%20please%20provide%20more%20details%3F%20%F0%9F%93%A6%F0%9F%9A%9B"
                        target="_blank"
                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 transition-all duration-200 hover:shadow-lg transform hover:-translate-y-0.5">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                        </svg>
                        Chat on WhatsApp
                    </a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t <?= $borderColorClass ?> text-center text-sm" style="color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.7;">
                <?= STORE_SETTINGS['footer_text'] ?? '© ' . date('Y') . ' E-Commerce Store. All rights reserved.' ?>
            </div>
        </div>
    </footer>

    <!-- ======= Custom JS Scripts ======= -->
    <!-- Scripts moved to header.php -->

    <!-- Alpine.js Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js Collapse Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- Swiper js plugins-->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="assets/js/swiper.js" defer></script>
    <script src="assets/js/main.js" defer></script>
    <!-- Add AOS JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom Scripts -->

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

<?php include_once __DIR__ . '/../modals/search_modal.php'; ?>
<?php include_once __DIR__ . '/../modals/cart_modal.php'; // Include Cart Modal ?>
<?php include_once __DIR__ . '/../modals/complete_order_modal.php'; // Include Complete Order Modal ?>
<?php include_once __DIR__ . '/../modals/finalise_order_modal.php'; // Include Finalise Order Modal ?>
<?php include_once __DIR__ . '/../modals/checkout_methods_modal.php'; // Include Checkout Methods Modal ?>
<?php include_once __DIR__ . '/../modals/thank_you_modal.php'; // Include Thank You Modal ?>
<?php include_once __DIR__ . '/toast.php'; // Include Toast Notifications ?>

</body>

    </html>