<?php
require_once __DIR__ . '/../utils/cart.php';

// Get cart count
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$themeColor = STORE_SETTINGS['theme_color'] ?? '#3B82F6'; // Default bg
$brandTextColor = STORE_SETTINGS['brand_text_color'] ?? '#FFFFFF'; // Default text

?>

<nav class="shadow-md" style="background-color: <?= htmlspecialchars($themeColor) ?>;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="font-bold text-xl" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                        <?= STORE_SETTINGS['store_name'] ?? 'E-Commerce Store' ?>
                    </a>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="/" class="border-transparent hover:opacity-80 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                        Home
                    </a>
                    <a href="/pages/products.php" class="border-transparent hover:opacity-80 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                        Products
                    </a>
                </div>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <button id="cartButton" type="button" class="p-2 rounded-full hover:opacity-80 relative" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                    <span class="sr-only">View cart</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $cartCount ?></span>
                    <?php endif; ?>
                </button>
            </div>
            <div class="-mr-2 flex items-center sm:hidden">
                <button type="button" id="mobileMenuButton" class="inline-flex items-center justify-center p-2 rounded-md hover:opacity-80 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                    <span class="sr-only">Open main menu</span>
                    <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state -->
    <div class="sm:hidden hidden" id="mobileMenu" style="background-color: <?= htmlspecialchars($themeColor) ?>;">
        <div class="pt-2 pb-3 space-y-1">
            <a href="/" class="hover:opacity-80 block pl-3 pr-4 py-2 text-base font-medium" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                Home
            </a>
            <a href="/pages/products.php" class="hover:opacity-80 block pl-3 pr-4 py-2 text-base font-medium" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                Products
            </a>
        </div>
        <div class="pt-4 pb-3 border-t border-gray-200 opacity-50">
            <div class="flex items-center px-4">
                <button id="mobileCartButton" class="ml-auto flex-shrink-0 p-1 rounded-full hover:opacity-80 relative" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                    <span class="sr-only">View cart</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"><?= $cartCount ?></span>
                    <?php endif; ?>
                </button>
            </div>
        </div>
    </div>
</nav>

<script>
    // Mobile menu toggle
    document.getElementById('mobileMenuButton').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobileMenu');
        mobileMenu.classList.toggle('hidden');
    });
</script> 