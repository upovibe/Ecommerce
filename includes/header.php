<?php
require_once 'config/settings.php';

$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$themeColor = STORE_SETTINGS['theme_color'] ?? '#3B82F6';
$brandTextColor = STORE_SETTINGS['brand_text_color'] ?? '#FFFFFF';
$logoPath = !empty(STORE_SETTINGS['logo_path']) ? STORE_SETTINGS['logo_path'] : '/assets/images/logo.png';
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store';
$currencySymbolPhp = STORE_SETTINGS['currency_symbol'] ?? '$';

$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$homePath = '/index.php';
$productsPath = '/products.php';
$contactPath = '/contact.php';

$isHomeActive = ($currentPath === '/' || $currentPath === $homePath);
$isProductsActive = ($currentPath === $productsPath);
$isContactActive = ($currentPath === $contactPath);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($storeName) ?></title>
    <meta name="description" content="<?= htmlspecialchars(STORE_SETTINGS['store_description'] ?? 'Your one-stop shop for all your needs') ?>">
    <!-- Favicon -->
    <link rel="icon" href="<?= htmlspecialchars($logoPath) ?>" type="image/png">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/animations.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <!-- Add AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- x-cloak styles -->
    <style>
      [x-cloak] { display: none !important; }
    </style>

    <script src="/assets/js/cart.js" defer></script>
    <script src="/assets/js/utils.js" defer></script>
    <script src="/assets/js/main.js" defer></script>
    <script src="/assets/js/checkout.js" defer></script>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen" 
      x-data="{
          isSearchModalOpen: false, 
          isProductModalOpen: false, 
          selectedProduct: null, 
          currencySymbol: '<?= htmlspecialchars($currencySymbolPhp) ?>', 
          isImageLightboxOpen: false, 
          isCartModalOpen: false, 
          cartItems: [],
          viewedProductInCart: false,
          isCompleteOrderModalOpen: false,
          isFinaliseOrderModalOpen: false,
          isCheckoutMethodsModalOpen: false,
          whatsappNumber: '<?= htmlspecialchars(STORE_SETTINGS['whatsapp_number'] ?? '') ?>',
          
          orderFulfillmentMethod: 'delivery',
          pickupBy: 'myself',
          customerName: '',
          customerPhone: '',
          customerEmail: '',
          customerAddress: '',
          pickupPersonName: '',
          pickupPersonPhone: '',
          finalOrderDetails: null,
          isThankYouModalOpen: false,
          finalOrderDetailsForThankYou: null,

          init() {
              // Add the event listener for successful checkout
              window.addEventListener('checkout:success', (event) => {
                  console.log('[Header x-data] checkout:success event received:', event.detail);

                  // Set data for the Thank You modal
                  this.finalOrderDetailsForThankYou = event.detail;

                  // Close the checkout methods modal FIRST
                  this.isCheckoutMethodsModalOpen = false;
                  console.log('[Header x-data] Checkout methods modal closed.');

                   // Clear the main customer form fields
                  this.customerName = '';
                  this.customerPhone = '';
                  this.customerEmail = '';
                  this.customerAddress = '';
                  this.pickupPersonName = '';
                  this.pickupPersonPhone = '';
                  this.orderFulfillmentMethod = 'delivery'; // Reset fulfillment
                  this.pickupBy = 'myself'; // Reset pickup option
                  console.log('[Header x-data] Customer form fields cleared.');

                  // Open the Thank You modal using $nextTick for smoother transition
                  this.$nextTick(() => {
                       this.isThankYouModalOpen = true;
                       console.log('[Header x-data] isThankYouModalOpen set to:', this.isThankYouModalOpen);
                       // Ensure icons render in the new modal
                       if (typeof lucide !== 'undefined') {
                            setTimeout(() => {
                                lucide.createIcons();
                                console.log('[Header x-data] Lucide icons refreshed for Thank You modal.');
                            }, 50);
                       }
                  });
              });
              
              this.$watch('selectedProduct', (product) => {
                  console.log('Selected product changed:', product);
                  if (product && product.id) {
                      this.viewedProductInCart = cart.isInCart(product.id);
                      console.log('Updated viewedProductInCart based on selectedProduct:', this.viewedProductInCart);
                      this.$nextTick(() => { if(typeof lucide !== 'undefined') lucide.createIcons(); });
                  } else {
                      this.viewedProductInCart = false;
                  }
              });
              
              this.$watch('isCompleteOrderModalOpen', (isOpen) => {
                  console.log('[x-data] isCompleteOrderModalOpen changed to:', isOpen);
                  if (isOpen) {
                      this.$nextTick(() => { if(typeof lucide !== 'undefined') lucide.createIcons(); });
                  }
              });
              
              this.$watch('isFinaliseOrderModalOpen', (isOpen) => {
                  console.log('[x-data] isFinaliseOrderModalOpen changed to:', isOpen);
                  if (isOpen) {
                      this.$nextTick(() => { if(typeof lucide !== 'undefined') lucide.createIcons(); });
                  }
              });
              
              this.$watch('isCartModalOpen', (isOpen) => {
                  console.log('[x-data] isCartModalOpen changed to:', isOpen);
              });
          },

          handleCartUpdate(event) {
              console.log('Global cart update listener triggered by event:', event);
              if (this.selectedProduct && this.selectedProduct.id) {
                  const wasInCart = this.viewedProductInCart;
                  this.viewedProductInCart = cart.isInCart(this.selectedProduct.id);
                  console.log('Updated viewedProductInCart based on cart:updated event:', this.viewedProductInCart);
                  if (wasInCart !== this.viewedProductInCart) {
                      this.$nextTick(() => { 
                          console.log('Icon refresh needed due to cart update affecting viewed product');
                          if(typeof lucide !== 'undefined') lucide.createIcons(); 
                      });
                  }
              }
          },
          
          prepareAndOpenFinaliseModal() {
              let orderDetails = {
                  cart: cart.getContents(),
                  fulfillment: this.orderFulfillmentMethod,
                  customer: {
                      name: this.customerName,
                      phone: this.customerPhone,
                      email: this.customerEmail,
                      address: this.orderFulfillmentMethod === 'delivery' ? this.customerAddress : null
                  },
                  pickup: null
              };
              
              if (this.orderFulfillmentMethod === 'pickup' && this.pickupBy === 'someone_else') {
                  orderDetails.pickup = {
                      name: this.pickupPersonName,
                      phone: this.pickupPersonPhone
                  };
              }
              
              this.finalOrderDetails = orderDetails;
              console.log('Prepared finalOrderDetails:', this.finalOrderDetails);
              
              this.isCompleteOrderModalOpen = false;
              this.isFinaliseOrderModalOpen = true;
          }
      }"
      @cart:updated.window="handleCartUpdate($event)" >
    <nav class="shadow-lg sticky top-0 z-30 animate-header-load" style="background-color: <?= htmlspecialchars($themeColor) ?>;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 md:h-20">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="/" class="flex items-center space-x-2">
                            <img class="h-14 w-auto"
                            src="<?= htmlspecialchars(STORE_SETTINGS['store_logo'] ?? '/assets/images/logo.png') ?>"
                            alt="<?= htmlspecialchars(STORE_SETTINGS['store_name'] ?? 'E-Commerce Store') ?> Logo">
                            <span class="text-lg font-semibold" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                            <?= STORE_SETTINGS['store_name'] ?? 'E-Commerce Store' ?>
                        </span>
                        </a>
                    </div>
                    <div class="hidden md:ml-8 md:flex md:space-x-6">
                        <a href="/"
                            style="color: <?= htmlspecialchars($brandTextColor) ?>;"
                            class="<?= $isHomeActive ? 'bg-black/10' : 'opacity-90' ?> relative px-3 py-2 rounded-lg font-medium hover:bg-black/10 hover:opacity-100 transition-all duration-200 group">
                            Home
                            <?php if ($isHomeActive): ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 rounded-full" style="background-color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.8;"></span>
                            <?php else: ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-transparent rounded-full group-hover:bg-current group-hover:opacity-30 transition-all duration-200"></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= htmlspecialchars($productsPath) ?>"
                            style="color: <?= htmlspecialchars($brandTextColor) ?>;"
                            class="<?= $isProductsActive ? 'bg-black/10' : 'opacity-90' ?> relative px-3 py-2 rounded-lg font-medium hover:bg-black/10 hover:opacity-100 transition-all duration-200 group">
                            Products
                            <?php if ($isProductsActive): ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 rounded-full" style="background-color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.8;"></span>
                            <?php else: ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-transparent rounded-full group-hover:bg-current group-hover:opacity-30 transition-all duration-200"></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?= htmlspecialchars($contactPath) ?>"
                            style="color: <?= htmlspecialchars($brandTextColor) ?>;"
                            class="<?= $isContactActive ? 'bg-black/10' : 'opacity-90' ?> relative px-3 py-2 rounded-lg font-medium hover:bg-black/10 hover:opacity-100 transition-all duration-200 group">
                            Contact
                            <?php if ($isContactActive): ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 rounded-full" style="background-color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.8;"></span>
                            <?php else: ?>
                                <span class="absolute inset-x-1 -bottom-1 h-0.5 bg-transparent rounded-full group-hover:bg-current group-hover:opacity-30 transition-all duration-200"></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>

                <div class="flex items-center">
                    <button id="searchModalButton" type="button" 
                            @click="isSearchModalOpen = true" 
                            class="relative p-2 rounded-full hover:bg-black/10 transition-all duration-200 mr-2" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                        <span class="sr-only">Search</span>
                        <i data-lucide="search" class="h-6 w-6"></i>
                    </button>

                    <div class="hidden md:block">
                        <button id="cartButton" type="button" 
                                @click="isCartModalOpen = true"
                                class="relative p-2 rounded-full hover:bg-black/10 transition-all duration-200" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                            <span class="sr-only">View cart</span>
                            <i data-lucide="shopping-cart" class="h-6 w-6"></i>
                            <span id="cart-item-count" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center transform hover:scale-110 transition-transform">0</span>
                        </button>
                    </div>

                    <button id="mobileCartIcon" type="button" 
                            @click="isCartModalOpen = true"
                            class="relative md:hidden p-2 rounded-full hover:bg-black/10 transition-all duration-200 mr-2" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
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
                    style="color: <?= htmlspecialchars($brandTextColor) ?>;"
                    class="<?= $isHomeActive ? 'bg-black/10' : 'opacity-90 hover:bg-black/10 hover:opacity-100' ?> block px-3 py-2 rounded-md text-base font-medium transition-all duration-200">
                    Home
                </a>
                <a href="<?= htmlspecialchars($productsPath) ?>"
                    style="color: <?= htmlspecialchars($brandTextColor) ?>;"
                    class="<?= $isProductsActive ? 'bg-black/10' : 'opacity-90 hover:bg-black/10 hover:opacity-100' ?> block px-3 py-2 rounded-md text-base font-medium transition-all duration-200">
                    Products
                </a>
                <a href="<?= htmlspecialchars($contactPath) ?>"
                    style="color: <?= htmlspecialchars($brandTextColor) ?>;"
                    class="<?= $isContactActive ? 'bg-black/10' : 'opacity-90 hover:bg-black/10 hover:opacity-100' ?> block px-3 py-2 rounded-md text-base font-medium transition-all duration-200">
                    Contact
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