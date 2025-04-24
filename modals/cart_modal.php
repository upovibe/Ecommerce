<!-- Cart Modal -->
<div x-show="isCartModalOpen" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="cart-modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="isCartModalOpen = false">

    <div class="flex items-start justify-center min-h-screen pt-10 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isCartModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"
             @click="isCartModalOpen = false" aria-hidden="true"></div>

        <!-- Align vertical trick -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div x-show="isCartModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
            
            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50">
                <h3 class="text-lg font-medium text-gray-900" id="cart-modal-title">
                    Your Shopping Cart
                </h3>
                <button type="button" @click="isCartModalOpen = false" 
                        class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <!-- Cart Contents -->
            <div class="p-4 max-h-[60vh] overflow-y-auto" 
                 x-init="() => {
                     cartItems = Object.values(cart.getContents());
                     console.log('[Cart Modal] Initialized cartItems:', JSON.stringify(cartItems));
                     if (typeof lucide !== 'undefined') lucide.createIcons();
                 }"
                 @cart\\:updated.window="cartItems = Object.values(cart.getContents()); console.log('[Cart Modal] cart:updated triggered, cartItems:', JSON.stringify(cartItems));"
                 x-effect="
                     if(isCartModalOpen) {
                         cartItems = Object.values(cart.getContents());
                         console.log('[Cart Modal] Opened, cartItems:', JSON.stringify(cartItems));
                         setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 100);
                     }
                 ">
                <!-- Empty Cart State -->
                <div x-show="!cartItems || cartItems.length === 0" class="text-center py-8">
                    <i data-lucide="shopping-cart" class="h-16 w-16 mx-auto text-gray-300"></i>
                    <p class="mt-4 text-gray-600">Your cart is empty</p>
                </div>

                <!-- Cart Items List -->
                <div x-show="cartItems && cartItems.length > 0" class="space-y-4">
                    <template x-for="(item, index) in cartItems" :key="index">
                        <div class="flex items-start p-3 bg-white border border-gray-100 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <!-- Product Image -->
                            <div class="flex-shrink-0 w-16 h-16 bg-gray-100 rounded-md overflow-hidden">
                                <img :src="item.details.image || '/assets/images/placeholder.png'" :alt="item.details.name" class="w-full h-full object-cover">
                            </div>
                            
                            <!-- Product Details & Options -->
                            <div class="flex-1 ml-4 flex flex-col justify-between h-full">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 leading-tight" x-text="item.details.name"></h4>
                                    <div class="mt-1 flex items-center">
                                        <span class="text-sm text-gray-600" x-text="currencySymbol + parseFloat(item.details.price).toFixed(2)"></span>
                                    </div>
                                    <!-- Display Options -->
                                    <div x-data="{ options: item.details.options }" 
                                         x-show="options && Object.keys(options).length > 0" 
                                         class="mt-1.5 space-y-1 text-xs text-gray-600">
                                        <template x-for="(value, name) in options" :key="name">
                                            <div class="flex items-center">
                                                <span class="font-medium mr-1 capitalize" x-text="name + ': '"></span> 
                                                <span class="px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded-sm font-medium text-nowrap" x-text="value"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quantity Controls & Remove Button -->
                            <div class="flex flex-col items-end justify-between h-full ml-2">
                                <!-- Quantity Controls -->
                                <div class="flex items-center border border-gray-200 rounded-md">
                                    <button type="button" 
                                            @click="cart.decrementQuantity(item.details.id); cartItems = Object.values(cart.getContents()); $nextTick(() => { if(typeof lucide !== 'undefined') lucide.createIcons(); });"
                                            class="p-1 text-gray-500 hover:text-red-600 focus:outline-none" 
                                            title="Decrease quantity">
                                        <i data-lucide="minus" class="h-4 w-4"></i>
                                    </button>
                                    <span class="w-8 text-center text-sm font-medium" x-text="item.quantity"></span>
                                    <button type="button" 
                                            @click="cart.incrementQuantity(item.details.id); cartItems = Object.values(cart.getContents()); $nextTick(() => { if(typeof lucide !== 'undefined') lucide.createIcons(); });"
                                            class="p-1 text-gray-500 hover:text-green-600 focus:outline-none" 
                                            title="Increase quantity">
                                        <i data-lucide="plus" class="h-4 w-4"></i>
                                    </button>
                                </div>
                                <!-- Remove Button -->
                                <button type="button" 
                                        @click="cart.removeItem(item.details.id); cartItems = Object.values(cart.getContents()); $nextTick(() => { if(typeof lucide !== 'undefined') lucide.createIcons(); });"
                                        class="p-1 text-gray-400 hover:text-red-500 transition-colors mt-2" 
                                        aria-label="Remove item" 
                                        title="Remove from cart">
                                    <i data-lucide="trash-2" class="h-5 w-5"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- Cart Summary -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-medium text-gray-900">Total</span>
                            <span class="text-base font-medium text-gray-900" 
                                  x-text="currencySymbol + cartItems.reduce((total, item) => total + (parseFloat(item.details.price) * item.quantity), 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })">
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer/Actions -->
           <div x-show="cartItems && cartItems.length > 0"
     class="bg-gray-50/70 px-6 py-4 border-t border-gray-200 flex flex-row justify-between items-center gap-4 flex-wrap">

    <!-- Clear Cart Button (left side) -->
    <button type="button"
            @click="if(confirm('Are you sure you want to clear your cart?')) { cart.clearCart(); cartItems = []; }"
            class="flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-md hover:bg-red-200 transition-colors">
        <i data-lucide="trash-2" class="h-5 w-5"></i>
        <span class="hidden sm:inline-block ml-2">Clear Cart</span>
    </button>

    <!-- Action Buttons (right side) -->
    <div class="flex flex-row gap-3 ml-auto">
        <button type="button"
                @click="isCartModalOpen = false"
                class="flex items-center px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
            <i data-lucide="arrow-left" class="h-5 w-5"></i>
            <span class="hidden sm:inline-block ml-2">Continue Shopping</span>
        </button>
        <button type="button"
                @click="
                    console.log('[Cart Modal] Proceed clicked'); 
                    isCartModalOpen = false; 
                    isCompleteOrderModalOpen = true;
                    console.log('[Cart Modal] isCompleteOrderModalOpen set to:', isCompleteOrderModalOpen);
                "
                class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
            <i data-lucide="check-circle" class="h-5 w-5"></i>
            <span class="hidden sm:inline-block ml-2">Proceed</span>
        </button>
    </div>
</div>

</div>

        </div>
    </div>
</div>

<script>
// Make sure Lucide icons are initialized in the cart modal
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Listen for cart:updated events to keep UI in sync (handled by Alpine directive)
    document.addEventListener('cart:updated', function(event) {
        console.log('Cart updated event received in cart modal script:', event.detail);
    });
});
</script> 