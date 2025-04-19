<?php
require_once __DIR__ . '/../utils/cart.php';
require_once __DIR__ . '/../utils/whatsapp.php';

$cartItems = getCartContents();
$cartTotal = getCartTotal();
$currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '₦';
?>

<div id="cartModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- This element centers the modal content -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Close button -->
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" id="closeCartModal" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal content -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                            Your Shopping Cart
                        </h3>
                        
                        <div id="cartItemsContainer" class="mt-2">
                            <?php if (empty($cartItems)): ?>
                                <p class="text-gray-500 text-center py-4">Your cart is empty.</p>
                            <?php else: ?>
                                <div class="divide-y divide-gray-200">
                                    <?php foreach ($cartItems as $index => $item): ?>
                                        <div class="py-4 flex">
                                            <div class="flex-shrink-0 w-16 h-16 border border-gray-200 rounded-md overflow-hidden">
                                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-center object-cover">
                                            </div>
                                            <div class="ml-4 flex-1 flex flex-col">
                                                <div>
                                                    <div class="flex justify-between text-base font-medium text-gray-900">
                                                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                                                        <p class="ml-4"><?= $currencySymbol . number_format($item['price'] * $item['quantity'], 2) ?></p>
                                                    </div>
                                                    <?php if (!empty($item['options'])): ?>
                                                        <p class="mt-1 text-sm text-gray-500">
                                                            <?php
                                                            $optionTexts = [];
                                                            foreach ($item['options'] as $optionName => $optionValue) {
                                                                $optionTexts[] = ucfirst($optionName) . ': ' . $optionValue;
                                                            }
                                                            echo implode(', ', $optionTexts);
                                                            ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-1 flex items-end justify-between text-sm">
                                                    <div class="flex items-center">
                                                        <button data-index="<?= $index ?>" class="decreaseCartItem px-2 text-gray-600 hover:text-gray-800">-</button>
                                                        <span class="cartItemQuantity mx-2"><?= $item['quantity'] ?></span>
                                                        <button data-index="<?= $index ?>" class="increaseCartItem px-2 text-gray-600 hover:text-gray-800">+</button>
                                                    </div>
                                                    <div class="flex">
                                                        <button type="button" data-index="<?= $index ?>" class="removeCartItem font-medium text-red-600 hover:text-red-500">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="border-t border-gray-200 py-4">
                                    <div class="flex justify-between text-base font-medium text-gray-900">
                                        <p>Subtotal</p>
                                        <p id="cartTotal"><?= $currencySymbol . number_format($cartTotal, 2) ?></p>
                                    </div>
                                </div>
                                
                                <div class="mt-6 grid grid-cols-2 gap-3">
                                    <a href="<?= buildCartInquiryUrl() ?>" target="_blank" class="flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-green-600 hover:bg-green-700">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                        </svg>
                                        Make Inquiry
                                    </a>
                                    <button type="button" id="placeOrderBtn" class="flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-primary hover:bg-indigo-700">
                                        Place Order
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Open cart modal
    function openCartModal() {
        document.getElementById('cartModal').classList.remove('hidden');
        
        // Refresh cart contents via AJAX
        refreshCartContents();
    }
    
    // Close cart modal
    document.getElementById('closeCartModal').addEventListener('click', function() {
        document.getElementById('cartModal').classList.add('hidden');
    });
    
    // Refresh cart contents
    function refreshCartContents() {
        fetch('/utils/cart_actions.php?action=get_cart_html')
            .then(response => response.text())
            .then(html => {
                document.getElementById('cartItemsContainer').innerHTML = html;
                
                // Reattach event listeners
                attachCartItemEventListeners();
            })
            .catch(error => {
                console.error('Error refreshing cart:', error);
            });
    }
    
    // Attach event listeners to cart item buttons
    function attachCartItemEventListeners() {
        // Increase quantity buttons
        document.querySelectorAll('.increaseCartItem').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.dataset.index;
                updateCartItemQuantity(index, 1);
            });
        });
        
        // Decrease quantity buttons
        document.querySelectorAll('.decreaseCartItem').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.dataset.index;
                updateCartItemQuantity(index, -1);
            });
        });
        
        // Remove buttons
        document.querySelectorAll('.removeCartItem').forEach(button => {
            button.addEventListener('click', function() {
                const index = this.dataset.index;
                removeCartItem(index);
            });
        });
        
        // Place order button
        const placeOrderBtn = document.getElementById('placeOrderBtn');
        if (placeOrderBtn) {
            placeOrderBtn.addEventListener('click', function() {
                document.getElementById('cartModal').classList.add('hidden');
                
                // Show order modal
                const orderModal = document.getElementById('orderModal');
                if (orderModal) {
                    orderModal.classList.remove('hidden');
                }
            });
        }
    }
    
    // Update cart item quantity
    function updateCartItemQuantity(index, change) {
        const formData = new FormData();
        formData.append('action', 'update_quantity');
        formData.append('index', index);
        formData.append('change', change);
        
        fetch('/utils/cart_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                refreshCartContents();
                
                // Update cart count in navbar
                if (data.cart_count !== undefined) {
                    updateCartCount(data.cart_count);
                }
            }
        })
        .catch(error => {
            console.error('Error updating quantity:', error);
        });
    }
    
    // Remove cart item
    function removeCartItem(index) {
        const formData = new FormData();
        formData.append('action', 'remove_item');
        formData.append('index', index);
        
        fetch('/utils/cart_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                refreshCartContents();
                
                // Update cart count in navbar
                if (data.cart_count !== undefined) {
                    updateCartCount(data.cart_count);
                }
            }
        })
        .catch(error => {
            console.error('Error removing item:', error);
        });
    }
    
    // Update cart count display
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            if (count > 0) {
                element.textContent = count;
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        });
    }
    
    // Initial attachment of event listeners
    document.addEventListener('DOMContentLoaded', function() {
        attachCartItemEventListeners();
        
        // Attach cart button click events
        document.getElementById('cartButton')?.addEventListener('click', openCartModal);
        document.getElementById('mobileCartButton')?.addEventListener('click', openCartModal);
    });
</script> 