<?php
require_once __DIR__ . '/../utils/cart.php';
require_once __DIR__ . '/../utils/whatsapp.php';

$cartItems = getCartItems();
$cartTotal = getCartTotal();
$currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '₦';
?>

<div id="orderModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

        <!-- This element centers the modal content -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <!-- Close button -->
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" id="closeOrderModal" class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
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
                            Complete Your Order
                        </h3>
                        
                        <?php if (empty($cartItems)): ?>
                            <p class="text-gray-500 text-center py-4">Your cart is empty. Cannot place an order.</p>
                        <?php else: ?>
                            <form id="orderForm" class="space-y-6">
                                <!-- Delivery Type -->
                                <div>
                                    <label class="text-sm font-medium text-gray-700 block mb-2">Delivery Method</label>
                                    <div class="flex space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="delivery_type" value="Pickup" class="form-radio text-primary" checked>
                                            <span class="ml-2">Pickup</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="delivery_type" value="Delivery" class="form-radio text-primary">
                                            <span class="ml-2">Delivery</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Recipient Type -->
                                <div>
                                    <label class="text-sm font-medium text-gray-700 block mb-2">Recipient</label>
                                    <div class="flex space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="recipient_type" value="Self" class="form-radio text-primary" checked>
                                            <span class="ml-2">Myself</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" name="recipient_type" value="Someone else" class="form-radio text-primary">
                                            <span class="ml-2">Someone else</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Contact Information -->
                                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                        <div class="mt-1">
                                            <input type="text" name="name" id="name" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        </div>
                                    </div>
                                    
                                    <div class="sm:col-span-2">
                                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                        <div class="mt-1">
                                            <input type="tel" name="phone" id="phone" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md" required>
                                        </div>
                                    </div>
                                    
                                    <!-- Address (conditionally shown for delivery) -->
                                    <div id="addressContainer" class="sm:col-span-2 hidden">
                                        <label for="address" class="block text-sm font-medium text-gray-700">Delivery Address</label>
                                        <div class="mt-1">
                                            <textarea name="address" id="address" rows="3" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                        </div>
                                    </div>
                                    
                                    <!-- Note -->
                                    <div class="sm:col-span-2">
                                        <label for="note" class="block text-sm font-medium text-gray-700">Additional Note (Optional)</label>
                                        <div class="mt-1">
                                            <textarea name="note" id="note" rows="3" class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Order Summary -->
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between text-sm font-medium">
                                        <p class="text-gray-600">Order Total</p>
                                        <p class="text-primary"><?= $currencySymbol . number_format($cartTotal, 2) ?></p>
                                    </div>
                                </div>
                                
                                <!-- Submit Buttons -->
                                <div class="flex">
                                    <button type="button" id="backToCartBtn" class="w-1/2 bg-gray-200 text-gray-800 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mr-2">
                                        Back to Cart
                                    </button>
                                    <button type="submit" class="w-1/2 bg-primary text-white py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                        Complete Order
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const orderModal = document.getElementById('orderModal');
        const orderForm = document.getElementById('orderForm');
        const addressContainer = document.getElementById('addressContainer');
        
        // Close order modal
        document.getElementById('closeOrderModal')?.addEventListener('click', function() {
            orderModal.classList.add('hidden');
        });
        
        // Back to cart button
        document.getElementById('backToCartBtn')?.addEventListener('click', function() {
            orderModal.classList.add('hidden');
            document.getElementById('cartModal').classList.remove('hidden');
        });
        
        // Toggle address field based on delivery type
        const deliveryRadios = document.querySelectorAll('input[name="delivery_type"]');
        deliveryRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Delivery') {
                    addressContainer.classList.remove('hidden');
                    document.getElementById('address').setAttribute('required', 'required');
                } else {
                    addressContainer.classList.add('hidden');
                    document.getElementById('address').removeAttribute('required');
                }
            });
        });
        
        // Handle form submission
        if (orderForm) {
            orderForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Collect form data
                const formData = new FormData(orderForm);
                const orderDetails = Object.fromEntries(formData.entries());
                
                // Generate WhatsApp URL
                const whatsappUrl = generateWhatsAppOrderUrl(orderDetails);
                
                // Open WhatsApp in new window
                window.open(whatsappUrl, '_blank');
                
                // Close modal
                orderModal.classList.add('hidden');
                
                // Clear cart (optional)
                clearCart();
            });
        }
        
        // Generate WhatsApp URL for order
        function generateWhatsAppOrderUrl(orderDetails) {
            // Send AJAX request to build WhatsApp URL
            const params = new URLSearchParams(orderDetails);
            return `/utils/cart_actions.php?action=order_url&${params.toString()}`;
        }
        
        // Clear cart after order
        function clearCart() {
            fetch('/utils/cart_actions.php', {
                method: 'POST',
                body: new URLSearchParams({
                    'action': 'clear_cart'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart count to 0
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(element => {
                        element.classList.add('hidden');
                    });
                    
                    // Show success message
                    alert('Your order has been sent! The cart has been cleared.');
                }
            })
            .catch(error => {
                console.error('Error clearing cart:', error);
            });
        }
    });
</script> 