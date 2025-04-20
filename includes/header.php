<?php
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../utils/cart.php'; // Needed for cart count

// Get cart count
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$themeColor = STORE_SETTINGS['theme_color'] ?? '#3B82F6'; // Default bg
$brandTextColor = STORE_SETTINGS['brand_text_color'] ?? '#FFFFFF';
$logoPath = !empty(STORE_SETTINGS['logo_path']) ? STORE_SETTINGS['logo_path'] : '/assets/images/logo.png'; 
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store'; // Store name variable

// Determine active page based on the example's logic
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$homePath = '/index.php'; // Assuming index.php is your root
$productsPath = '/pages/products.php';

$isHomeActive = ($currentPath === '/' || $currentPath === $homePath);
$isProductsActive = ($currentPath === $productsPath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(STORE_SETTINGS['store_name'] ?? 'E-Commerce Store') ?></title>
    <meta name="description" content="<?= htmlspecialchars(STORE_SETTINGS['store_description'] ?? 'Your one-stop shop for all your needs') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Link existing CSS -->
    <link rel="stylesheet" href="/assets/css/style.css"> 
    <link rel="stylesheet" href="/assets/css/animations.css">
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <div class="flex-grow">
        <!-- Modern Navbar -->
        <nav class="shadow-lg" style="background-color: <?= htmlspecialchars($themeColor) ?>;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 md:h-20">
                    <!-- Logo and main nav -->
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <a href="/" class="flex items-center space-x-2">
                                <?php // Use the determined $logoPath ?>
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
                                <?php /* Example uses subtle underline, keeping it for now */ ?>
                                <?php if ($isHomeActive): ?>
                                    <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-white/80 rounded-full"></span>
                                <?php else: ?>
                                    <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-transparent group-hover:bg-white/30 rounded-full transition-all duration-200"></span>
                                <?php endif; ?>
                            </a>
                            <a href="/pages/products.php" 
                               class="<?= $isProductsActive ? 'text-white bg-black/10' : 'text-white/90' ?> relative px-3 py-2 rounded-lg font-medium hover:bg-black/10 hover:text-white transition-all duration-200 group">
                                Products
                                <?php /* Example uses subtle underline, keeping it for now */ ?>
                                <?php if ($isProductsActive): ?>
                                    <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-white/80 rounded-full"></span>
                                <?php else: ?>
                                    <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-transparent group-hover:bg-white/30 rounded-full transition-all duration-200"></span>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>

                    <!-- Cart and mobile button -->
                    <div class="flex items-center">
                        <div class="hidden md:block">
                            <button id="cartButton" type="button" class="relative p-2 rounded-full hover:bg-black/10 transition-all duration-200" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                                <span class="sr-only">View cart</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <?php if ($cartCount > 0): ?>
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center transform hover:scale-110 transition-transform"><?= $cartCount ?></span>
                                <?php endif; ?>
                            </button>
                        </div>
                        <div class="md:hidden">
                            <button type="button" id="mobileMenuButton" class="inline-flex items-center justify-center p-2 rounded-md hover:bg-black/10 focus:outline-none transition-all duration-200" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                                <span class="sr-only">Open main menu</span>
                                <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                 <?php /* Note: Example JS doesn't toggle icons, so only burger is shown */ ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu with animation -->
            <div class="md:hidden hidden origin-top" id="mobileMenu" style="background-color: <?= htmlspecialchars($themeColor) ?>;">
                <div class="pt-2 pb-4 px-4 space-y-1">
                    <a href="/" 
                       class="<?= $isHomeActive ? 'bg-black/10 text-white' : 'text-white/90 hover:bg-black/10' ?> block px-3 py-2 rounded-md text-base font-medium transition-all duration-200">
                        Home
                    </a>
                    <a href="/pages/products.php" 
                       class="<?= $isProductsActive ? 'bg-black/10 text-white' : 'text-white/90 hover:bg-black/10' ?> block px-3 py-2 rounded-md text-base font-medium transition-all duration-200">
                        Products
                    </a>
                </div>
                <div class="pb-4 px-4">
                    <button id="mobileCartButton" class="w-full flex items-center justify-center px-4 py-2 rounded-md bg-black/10 text-white hover:bg-black/20 transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Cart
                        <?php if ($cartCount > 0): ?>
                            <span class="ml-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </button>
                </div>
            </div>
        </nav>
    </div>
    <script>
        // Enhanced mobile menu toggle with animations from example
        document.getElementById('mobileMenuButton').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            
            if (mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.remove('hidden');
                // Add animation classes (ensure Tailwind config is loaded in header)
                mobileMenu.classList.remove('animate-fade-out-up'); 
                mobileMenu.classList.add('animate-fade-in-down');
            } else {
                 // Add animation classes (ensure Tailwind config is loaded in header)
                mobileMenu.classList.remove('animate-fade-in-down');
                mobileMenu.classList.add('animate-fade-out-up');
                // Wait for animation to finish before hiding
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                }, 250); // Should match animation duration (0.3s -> 300ms, adjust if needed)
            }
        });
    </script>
</body>
</html>