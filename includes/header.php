<?php
require_once 'config/settings.php';

$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$themeColor = STORE_SETTINGS['theme_color'] ?? '#3B82F6';
$brandTextColor = STORE_SETTINGS['brand_text_color'] ?? '#FFFFFF';
$logoPath = !empty(STORE_SETTINGS['logo_path']) ? STORE_SETTINGS['logo_path'] : '/assets/images/logo.png';
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store';

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$homePath = '/index.php';
$productsPath = '/products.php';

$isHomeActive = ($currentPath === '/' || $currentPath === $homePath);
$isProductsActive = ($currentPath === $productsPath);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($storeName) ?></title>
    <meta name="description" content="<?= htmlspecialchars(STORE_SETTINGS['store_description'] ?? 'Your one-stop shop for all your needs') ?>">
    <!-- Favicon -->
    <link rel="icon" href="<?= htmlspecialchars($logoPath) ?>" type="image/png"> <!-- Adjust type if logo isn't PNG -->

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/animations.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <!-- Add AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <!-- Link to Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <script src="/assets/js/utils.js" defer></script>
    <script src="/assets/js/main.js" defer></script>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">
    <nav class="shadow-lg sticky top-0 z-30" style="background-color: <?= htmlspecialchars($themeColor) ?>;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 md:h-20">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="/" class="flex items-center space-x-2">
                            <img class="h-8 md:h-10 w-auto" src="<?= htmlspecialchars($logoPath) ?>" alt="<?= htmlspecialchars($storeName) ?> Logo">
                            <span class="font-bold text-xl md:text-2xl tracking-tight" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                                <?= htmlspecialchars($storeName) ?>
                            </span>
                        </a>
                    </div>
                    <div class="hidden md:ml-8 md:flex md:space-x-6">
                        <a href="/"
                            class="<?= $isHomeActive ? 'text-white bg-black/10' : 'text-white/90' ?> relative px-3 py-2 rounded-lg font-medium hover:bg-black/10 hover:text-white transition-all duration-200 group">
                            Home
                            <?php if ($isHomeActive): ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-white/80 rounded-full"></span>
                            <?php else: ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-transparent group-hover:bg-white/30 rounded-full transition-all duration-200"></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= htmlspecialchars($productsPath) ?>"
                            class="<?= $isProductsActive ? 'text-white bg-black/10' : 'text-white/90' ?> relative px-3 py-2 rounded-lg font-medium hover:bg-black/10 hover:text-white transition-all duration-200 group">
                            Products
                            <?php if ($isProductsActive): ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-white/80 rounded-full"></span>
                            <?php else: ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-transparent group-hover:bg-white/30 rounded-full transition-all duration-200"></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>

                <div class="flex items-center">
                    <button id="searchModalButton" type="button" class="relative p-2 rounded-full hover:bg-black/10 transition-all duration-200 mr-2" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                        <span class="sr-only">Search</span>
                        <i data-lucide="search" class="h-6 w-6"></i>
                    </button>

                    <div class="hidden md:block">
                        <button id="cartButton" type="button" class="relative p-2 rounded-full hover:bg-black/10 transition-all duration-200" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                            <span class="sr-only">View cart</span>
                            <i data-lucide="shopping-cart" class="h-6 w-6"></i>
                            <span id="cart-item-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center transform hover:scale-110 transition-transform">0</span>
                        </button>
                    </div>

                    <button id="mobileCartIcon" type="button" class="relative md:hidden p-2 rounded-full hover:bg-black/10 transition-all duration-200 mr-2" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                        <span class="sr-only">View cart</span>
                        <i data-lucide="shopping-cart" class="h-6 w-6"></i>
                        <span id="mobile-cart-item-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center transform hover:scale-110 transition-transform ">0</span>
                    </button>

                    <div class="md:hidden">
                        <button type="button" id="mobileMenuButton" class="inline-flex items-center justify-center p-2 rounded-md hover:bg-black/10 focus:outline-none transition-all duration-200" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                            <span class="sr-only">Open main menu</span>
                            <i data-lucide="menu" class="block h-6 w-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="md:hidden hidden origin-top" id="mobileMenu" style="background-color: <?= htmlspecialchars($themeColor) ?>;">
            <div class="pt-2 pb-4 px-4 space-y-1">
                <a href="/"
                    class="<?= $isHomeActive ? 'bg-black/10 text-white' : 'text-white/90 hover:bg-black/10' ?> block px-3 py-2 rounded-md text-base font-medium transition-all duration-200">
                    Home
                </a>
                <a href="<?= htmlspecialchars($productsPath) ?>"
                    class="<?= $isProductsActive ? 'bg-black/10 text-white' : 'text-white/90 hover:bg-black/10' ?> block px-3 py-2 rounded-md text-base font-medium transition-all duration-200">
                    Products
                </a>
            </div>
        </div>
    </nav>
    <script>
        document.getElementById('mobileMenuButton').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.remove('animate-fade-out-up');
                mobileMenu.classList.add('animate-fade-in-down');
            } else {
                mobileMenu.classList.remove('animate-fade-in-down');
                mobileMenu.classList.add('animate-fade-out-up');
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                }, 250);
            }
        });

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    </script>
</body>

</html>