<?php
// Load WhatsApp utilities
require_once __DIR__ . '/../api/whatsapp.php';
$whatsappNumber = STORE_SETTINGS['whatsapp_number'] ?? '';
?>
<!-- Product Modal -->
<div x-show="isProductModalOpen" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="product-modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="isProductModalOpen = false"
     @cart\\:updated.window="$nextTick(() => console.log('Product modal reacting to cart update'))" >

    <div class="flex items-start justify-center min-h-screen pt-10 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isProductModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"
             @click="isProductModalOpen = false" aria-hidden="true"></div>

        <!-- Align vertical trick -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div x-show="isProductModalOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl w-full">
            
             <!-- Header with Slug, Copy Link, Close -->
             <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50">
                 <div class="flex items-center space-x-2">
                     <!-- Slug Display (Alpine version) -->
                     <span x-show="selectedProduct?.slug" class="text-sm font-medium text-gray-500">
                         <span class="font-mono text-blue-600">@<span x-text="selectedProduct?.slug"></span></span>
                     </span>
                     <!-- Copy Link Button (Placeholder) -->
                     <button type="button" x-show="selectedProduct?.slug" title="Copy product link" class="text-gray-400 hover:text-blue-600 focus:outline-none p-1 rounded-md hover:bg-gray-100" @click="alert('Copy link functionality TBD')">
                        <span class="sr-only">Copy link</span>
                        <i data-lucide="link-2" class="h-4 w-4"></i>
                    </button>
                    <!-- Copy Feedback (Placeholder) -->
                    <!-- <span id="productModalCopyFeedback" class="text-xs text-green-600 hidden ml-1">Copied!</span> -->
                 </div>
                 <button type="button" @click="isProductModalOpen = false" class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
             </div>

            <!-- Modal Content -->
            <div class="flex flex-col md:flex-row" style="max-height: 75vh;">
                <!-- Left Side: Image -->
                <div class="md:w-2/5 p-6 flex-shrink-0 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center border-r border-gray-200/60">
                    <div class="w-full max-w-xs mx-auto md:max-w-none">
                        <!-- Main Image Display -->
                        <div class="aspect-w-1 aspect-h-1 mb-3">
                            <img x-show="selectedProduct?.image && getProductImages(selectedProduct.image).length > 0" 
                                 :src="selectedProduct ? getProductImages(selectedProduct.image)[selectedProduct.currentImageIndex || 0] : '/assets/images/placeholder.png'" 
                                 :alt="selectedProduct?.name" 
                                 @click="isImageLightboxOpen = true" 
                                 class="w-full h-44 md:h-full object-cover rounded-lg shadow-lg bg-white/50 backdrop-blur-sm cursor-pointer transition-transform hover:scale-105">
                            <img x-show="!selectedProduct?.image || getProductImages(selectedProduct?.image || '').length === 0" 
                                 src="/assets/images/placeholder.png" 
                                 alt="Placeholder" 
                                 class="w-full h-44 md:h-full object-contain rounded-lg shadow-lg bg-white/50 backdrop-blur-sm">
                        </div>
                        
                        <!-- Thumbnail Navigation (if multiple images) -->
                        <div x-show="selectedProduct?.image && getProductImages(selectedProduct.image).length > 1" class="flex gap-2 justify-center">
                            <template x-for="(imageUrl, index) in getProductImages(selectedProduct?.image || '')" :key="index">
                                <button @click="selectedProduct.currentImageIndex = index"
                                        :class="{'ring-2 ring-blue-500': (selectedProduct.currentImageIndex || 0) === index}"
                                        class="w-12 h-12 rounded-md overflow-hidden border hover:border-blue-300 transition-all">
                                    <img :src="imageUrl" :alt="`Image ${index + 1}`" 
                                         class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Details -->
                <div class="md:w-3/5 p-6 overflow-y-auto">
                    <h3 class="text-2xl lg:text-3xl leading-9 font-bold text-gray-900 mb-4" x-text="selectedProduct?.name || 'Product Name'"></h3>

                    <!-- Key Details Section - Card Style -->
                    <div class="bg-white border border-gray-200/80 rounded-lg shadow-sm p-4 mb-6 space-y-3">
                        <!-- Price & Discount -->
                        <div class="flex items-center justify-between">
                             <span class="text-sm font-medium text-gray-600 flex items-center">
                                 Price
                             </span>
                            <div class="text-lg font-semibold text-right">
                                <template x-if="selectedProduct?.original_price && parseFloat(selectedProduct?.original_price) > parseFloat(selectedProduct?.price)">
                                    <div class="flex items-baseline justify-end gap-2">
                                        <!-- Discount Badge -->
                                        <span class="order-1 bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full" 
                                              x-text="'-' + parseFloat(selectedProduct?.discount_percentage).toFixed(0) + '%'"></span>
                                        <!-- Prices -->
                                        <div class="inline-block order-2">
                                            <span class="text-sm text-gray-500 line-through" x-text="formatCurrency(selectedProduct?.original_price, currencySymbol)"></span>
                                            <span class="text-md font-medium text-blue-600 ml-1" x-text="formatCurrency(selectedProduct?.price, currencySymbol)"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="!selectedProduct?.original_price || parseFloat(selectedProduct?.original_price) <= parseFloat(selectedProduct?.price)">
                                    <span class="text-md font-medium text-blue-600" x-text="formatCurrency(selectedProduct?.price, currencySymbol)"></span>
                                </template>
                            </div>
                        </div>
                        <!-- Divider -->
                        <hr class="border-gray-100">
                        <!-- Category -->
                        <div class="flex items-center justify-between">
                             <span class="text-sm font-medium text-gray-600 flex items-center">
                                 Category
                             </span>
                            <span class="text-sm font-semibold text-gray-800" x-text="selectedProduct?.category_name || 'Uncategorized'"></span>
                        </div>
                         <!-- Divider -->
                         <hr class="border-gray-100">
                         <!-- Availability -->
                        <div class="flex items-center justify-between">
                             <span class="text-sm font-medium text-gray-600 flex items-center">
                                 Availability
                             </span>
                            <template x-if="selectedProduct">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold"
                                    :class="{
                                        'bg-green-100 text-green-800': selectedProduct?.stock > 0,
                                        'bg-yellow-500 text-white': selectedProduct?.stock <= 0 && selectedProduct?.backorder, 
                                        'bg-gray-500 text-white': selectedProduct?.stock <= 0 && !selectedProduct?.backorder   
                                    }"
                                    x-text="selectedProduct?.stock > 0 ? (selectedProduct.stock + ' In Stock') : (selectedProduct?.backorder ? 'Backorder' : 'Out of Stock')">
                                </span>
                            </template>
                             <template x-if="!selectedProduct">
                                 <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700">Checking...</span>
                             </template>
                        </div>
                    </div>
                     
                    <!-- Description -->
                    <div class="mb-6">
                         <h4 class="text-lg font-semibold text-gray-800 mb-2">Description</h4>
                         <div class="prose prose-sm max-w-none text-gray-600" x-html="selectedProduct?.description || '<p>No description available.</p>'">
                         </div>
                    </div>

                    <!-- Product Options Display -->
                    <template x-if="selectedProduct?.options && Object.keys(selectedProduct.options).length > 0">
                        <div class="space-y-3 mb-6 pt-4 border-t border-gray-200/60">
                             <h4 class="text-lg font-semibold text-gray-800">Select Options</h4>
                            <template x-for="(values, name) in selectedProduct.options" :key="name">
                                <div :id="'option-group-' + name.replace(/\s+/g, '-')">
                                    <label x-text="name" class="block text-sm font-medium text-gray-700 mb-1"></label>
                                    <select 
                                        :name="'options[' + name + ']'" 
                                        :data-option-name="name"
                                        x-ref="optionSelects"
                                        class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                        <template x-for="value in values" :key="value">
                                            <option :value="value" x-text="value"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                        </div>
                    </template>

                </div> <!-- End Right Side -->
            </div> <!-- End Modal Content Flex -->

            <!-- Footer with Buttons -->
            <div class="bg-gray-50/70 px-4 py-3 sm:px-6 flex justify-between items-center md:justify-end md:items-end gap-3 border-t border-gray-200">
                <button type="button" 
                        @click="isProductModalOpen = false"
                        class="w-full inline-flex justify-center gap-1 items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto">
                    <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                    Close
                </button>
                
                <!-- Combined Add/Added Button -->
                <button type="button" 
                        x-ref="addToCartButton" 
                        :data-product-id="selectedProduct?.id"
                        :disabled="viewedProductInCart"
                        @click="(event) => { 
                            try {
                                if (viewedProductInCart) return; // Don't add if already in cart

                                // Gather selected options using DOM traversal relative to the modal panel
                                let selectedOptions = {};
                                const modalPanel = event.target.closest('.inline-block.align-bottom'); // Find the main modal panel
                                if (modalPanel) {
                                    const selects = modalPanel.querySelectorAll('select[data-option-name]');
                                    console.log('[Product Modal] Found selects via querySelectorAll:', selects);
                                    if (selects && selects.length > 0) {
                                        selects.forEach(select => {
                                            const optionName = select.dataset.optionName;
                                            const optionValue = select.value;
                                            if (optionName && optionValue) {
                                                selectedOptions[optionName] = optionValue;
                                            } else {
                                                console.warn('[Product Modal] Select missing name or value:', select);
                                            }
                                        });
                                    } else {
                                        console.log('[Product Modal] No selects found with data-option-name in modal panel.');
                                    }
                                } else {
                                    console.error('[Product Modal] Could not find modal panel element.');
                                }
                                console.log('[Product Modal] Selected options gathered:', selectedOptions); 

                                const product = selectedProduct;
                                const success = cart.addItem(product.id, {
                                    id: product.id,
                                    name: product.name,
                                    price: product.price,
                                    image: product.image || '/assets/images/placeholder.png',
                                    options: selectedOptions 
                                });
                                
                                if (success) {
                                    toast?.success(`${product.name} added to cart!`);
                                    // Explicitly set state *before* dispatching event
                                    viewedProductInCart = true; 
                                    document.dispatchEvent(new CustomEvent('cart:updated', { detail: { productId: product.id } }));
                                    // Update icons AFTER state change allows DOM update
                                    $nextTick(() => { 
                                        console.log('Attempting icon refresh after add');
                                        if(typeof lucide !== 'undefined') lucide.createIcons(); 
                                    });
                                } else {
                                    toast?.error('Failed to add product to cart.');
                                }
                            } catch (error) {
                                console.error('Error adding to cart:', error);
                                toast?.error('An error occurred while adding to cart.');
                            }
                        }"
                        :class="{
                            'bg-green-600 hover:bg-green-700 text-white': !viewedProductInCart,
                            'bg-gray-300 text-gray-500 cursor-not-allowed': viewedProductInCart
                        }"
                        class="w-full inline-flex justify-center gap-1 items-center rounded-md border border-transparent shadow-sm px-4 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:w-auto transition-colors duration-200">
                    
                    <template x-if="!viewedProductInCart">
                        <span class="inline-flex items-center">
                            <i data-lucide="plus" class="w-4 h-4 inline-block mr-1"></i>
                            Add to Cart
                        </span>
                    </template>
                    <template x-if="viewedProductInCart">
                        <span class="inline-flex items-center">
                            <i data-lucide="check" class="w-4 h-4 inline-block mr-1"></i>
                            Added to Cart
                        </span>
                    </template>
                </button>
            </div>

        </div> 
        
        <!-- Image Lightbox Overlay -->
        <div x-show="isImageLightboxOpen" x-cloak
             class="fixed inset-0 z-[60] bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
             @keydown.escape.window="isImageLightboxOpen = false"
             @click.self="isImageLightboxOpen = false" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            
            <button @click="isImageLightboxOpen = false" class="absolute top-4 right-4 text-white/70 hover:text-white focus:outline-none z-10 bg-black/30 hover:bg-black/50 rounded-full p-2">
                <span class="sr-only">Close lightbox</span>
                <i data-lucide="x" class="h-6 w-6"></i>
            </button>

            <img x-bind:src="selectedProduct?.image && getProductImages(selectedProduct.image).length > 0 ? getProductImages(selectedProduct.image)[selectedProduct.currentImageIndex || 0] : '/assets/images/placeholder.png'" 
                 x-bind:alt="selectedProduct?.name + ' - Full size'" 
                 class="max-w-full max-h-[90vh] object-contain shadow-xl rounded-lg"
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 scale-95" 
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 scale-100" 
                 x-transition:leave-end="opacity-0 scale-95">
        </div>
        <!-- End Image Lightbox -->
        
    </div>
</div>
<!-- End Product Modal -->

<script>
    // Make the formatNumberWithCommas function available in the Alpine.js context
    document.addEventListener('DOMContentLoaded', function() {
        // Define currencySymbol in Alpine.js scope if not already set
        const currencySymbolElement = document.getElementById('product-grid');
        const currencySymbol = currencySymbolElement ? (currencySymbolElement.dataset.currencySymbol || '$') : '$';
        
        if (typeof Alpine !== 'undefined') {
            Alpine.store('productData', {
                currencySymbol: currencySymbol
            });
        }
        
    });
</script> 