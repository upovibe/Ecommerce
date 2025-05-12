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

    // Helper function to generate social link if username is set
    function renderSocialLink($platform, $usernameKey, $baseUrl, $iconIdentifier, $hoverColorClass, $isLucide = false)
    {
        global $brandTextColor; // Make brand text color accessible
        $username = STORE_SETTINGS[$usernameKey] ?? '';
        if (!empty($username)) {
            // Specific URL format adjustments
            if ($platform === 'whatsapp') {
                $url = rtrim($baseUrl, '?') . '=' . ltrim($username, '+');
            } elseif ($platform === 'tiktok') {
                $url = rtrim($baseUrl, '/') . '/@' . ltrim($username, '@');
            } elseif ($platform === 'youtube') {
                $url = rtrim($baseUrl, '/') . '/' . ltrim($username, '@'); // Handles channels and handles like @channel
            } elseif ($platform === 'linkedin') {
                $url = rtrim($baseUrl, '/') . '/company/' . ltrim($username, '@'); // Assuming company page
            } else {
                $url = rtrim($baseUrl, '/') . '/' . ltrim($username, '@');
            }

            // Use inline style for the brand text color, keep hover class for color change on hover
            echo '<a href="' . htmlspecialchars($url) . '" 
                       target="_blank" 
                       style="color: ' . htmlspecialchars($brandTextColor) . ';" 
                       class="' . $hoverColorClass . ' transition-colors duration-300 opacity-80 hover:opacity-100">
                        <span class="sr-only">' . ucfirst($platform) . '</span>';

            if ($isLucide) {
                echo '<i data-lucide="' . htmlspecialchars($iconIdentifier) . '" class="h-6 w-6"></i>';
            } else {
                echo $iconIdentifier; // Echo the raw SVG string
            }

            echo '</a>';
        }
    }

    // SVG Icons (Keep only TikTok)
    $tiktokIcon = '<svg class="h-6 w-6" fill="currentColor" viewBox="0 0 32 32" version="1.1" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title>tiktok</title> <path d="M16.656 1.029c1.637-0.025 3.262-0.012 4.886-0.025 0.054 2.031 0.878 3.859 2.189 5.213l-0.002-0.002c1.411 1.271 3.247 2.095 5.271 2.235l0.028 0.002v5.036c-1.912-0.048-3.71-0.489-5.331-1.247l0.082 0.034c-0.784-0.377-1.447-0.764-2.077-1.196l0.052 0.034c-0.012 3.649 0.012 7.298-0.025 10.934-0.103 1.853-0.719 3.543-1.707 4.954l0.020-0.031c-1.652 2.366-4.328 3.919-7.371 4.011l-0.014 0c-0.123 0.006-0.268 0.009-0.414 0.009-1.73 0-3.347-0.482-4.725-1.319l0.040 0.023c-2.508-1.509-4.238-4.091-4.558-7.094l-0.004-0.041c-0.025-0.625-0.037-1.25-0.012-1.862 0.49-4.779 4.494-8.476 9.361-8.476 0.547 0 1.083 0.047 1.604 0.136l-0.056-0.008c0.025 1.849-0.050 3.699-0.050 5.548-0.423-0.153-0.911-0.242-1.42-0.242-1.868 0-3.457 1.194-4.045 2.861l-0.009 0.030c-0.133 0.427-0.21 0.918-0.21 1.426 0 0.206 0.013 0.41 0.037 0.61l-0.002-0.024c0.332 2.046 2.086 3.59 4.201 3.59 0.061 0 0.121-0.001 0.181-0.004l-0.009 0c1.463-0.044 2.733-0.831 3.451-1.994l0.010-0.018c0.267-0.372 0.45-0.822 0.511-1.311l0.001-0.014c0.125-2.237 0.075-4.461 0.087-6.698 0.012-5.036-0.012-10.060 0.025-15.083z"/></path> </g></svg>';
    // Remove other SVG Icon variables
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
                    <!-- PhirmHost Logo and Text -->
                    <div class="mt-4 flex items-center space-x-2 bg-gray-800/50 px-3 py-2 rounded-lg w-fit group relative">
                        <span class="text-sm font-medium" style="color: <?= htmlspecialchars($brandTextColor) ?>;">Powered by</span>
                        <img src="/assets/images/phirmhost-ads.png" alt="PhirmHost" class="h-6 w-auto">
                        <!-- Tooltip -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-4 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 w-64 text-center">
                            Want a stunning website like this? 
                            <a href="https://wa.me/233542838165?text=Hello!%20I%20saw%20your%20amazing%20website%20and%20I%20would%20love%20to%20get%20one%20for%20my%20business.%20Could%20you%20please%20tell%20me%20more%20about%20your%20web%20development%20services%3F" target="_blank" class="block mt-1 text-blue-400 hover:text-blue-300 hover:underline">
                                Let's build you one →
                            </a>
                            <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-1/2 rotate-45 w-2 h-2 bg-gray-900"></div>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4" style="color: <?= htmlspecialchars($brandTextColor) ?>;">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="/" class="hover:opacity-80" style="color: <?= htmlspecialchars($brandTextColor) ?>;">Home</a></li>
                        <li><a href="<?= htmlspecialchars($productsPath) ?>" class="hover:opacity-80" style="color: <?= htmlspecialchars($brandTextColor) ?>;">Products</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4" style="color: <?= htmlspecialchars($brandTextColor) ?>;">Follow Us</h3>
                    <div class="flex space-x-4 md:space-x-6">
                        <?php
                        renderSocialLink('facebook', 'facebook_username', 'https://facebook.com', 'facebook', 'hover:text-blue-300', true); // Use Lucide
                        renderSocialLink('instagram', 'instagram_username', 'https://instagram.com', 'instagram', 'hover:text-pink-300', true); // Use Lucide
                        renderSocialLink('twitter', 'twitter_username', 'https://twitter.com', 'twitter', 'hover:text-gray-300', true); // Use Lucide
                        renderSocialLink('tiktok', 'tiktok_username', 'https://tiktok.com', $tiktokIcon, 'hover:text-purple-300', false); // Use SVG
                        renderSocialLink('linkedin', 'linkedin_username', 'https://linkedin.com', 'linkedin', 'hover:text-blue-400', true); // Use Lucide
                        renderSocialLink('youtube', 'youtube_username', 'https://youtube.com', 'youtube', 'hover:text-red-400', true); // Use Lucide
                        ?>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t flex flex-col md:flex-row items-start md:items-center gap-4 justify-between <?= $borderColorClass ?> text-sm" style="color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.7;">
                <p class="mt-2 text-sm" style="color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.9;">
                    Want a stunning website like this?
                    <a href="https://wa.me/233542838165?text=Hello!%20I%20saw%20your%20amazing%20website%20and%20I%20would%20love%20to%20get%20one%20for%20my%20business.%20Could%20you%20please%20tell%20me%20more%20about%20your%20web%20development%20services%3F" target="_blank" class="font-medium hover:underline" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                        Let's build you one →
                    </a>
                </p>
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
    <?php include_once __DIR__ . '/../modals/cart_modal.php'; // Include Cart Modal 
    ?>
    <?php include_once __DIR__ . '/../modals/complete_order_modal.php'; // Include Complete Order Modal 
    ?>
    <?php include_once __DIR__ . '/../modals/finalise_order_modal.php'; // Include Finalise Order Modal 
    ?>
    <?php include_once __DIR__ . '/../modals/checkout_methods_modal.php'; // Include Checkout Methods Modal 
    ?>
    <?php include_once __DIR__ . '/../modals/thank_you_modal.php'; // Include Thank You Modal 
    ?>
    <?php include_once __DIR__ . '/toast.php'; // Include Toast Notifications 
    ?>

    <!-- Floating WhatsApp Button -->
    <div class="fixed bottom-6 right-6 z-50">
        <a href="https://api.whatsapp.com/send?phone=<?= STORE_SETTINGS['whatsapp_number'] ?>&text=Hello!%20%F0%9F%91%8B%20I%20have%20a%20question%20about%20your%20store%27s%20hours%20and%20shipping%20policies.%20Could%20you%20please%20provide%20more%20details%3F%20%F0%9F%93%A6%F0%9F%9A%9B"
            class="relative flex items-center justify-center w-14 h-14 rounded-full bg-green-500 hover:bg-green-600 shadow-lg transition-all duration-300 hover:scale-110"
            target="_blank"
            aria-label="Chat on WhatsApp">
            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
            </svg>
            <!-- Glowing Green Dot Indicator -->
            <span class="absolute top-0 right-0 block h-3 w-3 rounded-full bg-green-500 ring-2 ring-white animate-ping"></span>
        </a>
    </div>

    </body>

    </html>