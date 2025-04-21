<!-- Product Detail Modal -->
<div id="productModal" 
     class="fixed inset-0 z-40 overflow-y-auto hidden" 
     aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div id="productModalOverlay"
             class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity ease-out duration-300 opacity-0"
             aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div id="productModalPanel"
             class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all ease-out duration-300 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 sm:my-8 sm:align-middle sm:max-w-3xl w-full">
            
             <!-- Header with Slug, Copy Link, Close -->
             <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50">
                 <div class="flex items-center space-x-2">
                     <!-- Slug Display (populated by JS) -->
                     <span id="productModalSlugDisplay" class="text-sm font-medium text-gray-500 hidden">
                         <span class="font-mono text-blue-600">@<span id="productModalSlugText"></span></span>
                     </span>
                     <!-- Copy Link Button -->
                     <button type="button" id="productModalCopyLinkButton" title="Copy product link" class="text-gray-400 hover:text-blue-600 focus:outline-none hidden p-1 rounded-md hover:bg-gray-100">
                        <span class="sr-only">Copy link</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </button>
                    <span id="productModalCopyFeedback" class="text-xs text-green-600 hidden ml-1">Copied!</span>
                 </div>
                 <button type="button" id="closeProductModalButton" class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                    <span class="sr-only">Close</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
             </div>

            <!-- Modal Content -->
            <div class="flex flex-col md:flex-row" style="max-height: 80vh;">
                <!-- Left Side: Image -->
                <div class="md:w-2/5 p-6 flex-shrink-0 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center border-r border-gray-200/60">
                    <div class="aspect-w-1 aspect-h-1 w-full max-w-xs mx-auto">
                        <img id="productModalImage" src="../assets/images/placeholder.png" alt="Product Image"
                             class="w-full h-full object-contain rounded-lg shadow-lg bg-white/50 backdrop-blur-sm">
                    </div>
                </div>

                <!-- Right Side: Details -->
                <div class="md:w-3/5 p-6 overflow-y-auto">
                    <h3 class="text-3xl leading-9 font-bold text-gray-900 mb-4" id="productModalName">Product Name</h3>

                    <!-- Key Details Section - Card Style -->
                    <div class="bg-white border border-gray-200/80 rounded-lg shadow-sm p-4 mb-6 space-y-3">
                        <!-- Price & Discount -->
                        <div class="flex items-center justify-between">
                             <span class="text-sm font-medium text-gray-600 flex items-center">
                                 <!-- Optional: Icon <i data-lucide="dollar-sign" class="size-4 mr-2 text-blue-500"></i> -->
                                 Price
                             </span>
                            <div class="text-lg font-semibold text-right flex items-baseline gap-2">
                                <!-- Discount Badge -->
                                <span id="productModalDiscountBadge" class="hidden order-1 bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full"></span>
                                <!-- Price Display -->
                                <div id="productModalPriceDisplay" class="inline-block order-2">
                                    <!-- Price content injected by JS -->
                                </div>
                            </div>
                        </div>
                        <!-- Divider -->
                        <hr class="border-gray-100">
                        <!-- Category -->
                        <div class="flex items-center justify-between">
                             <span class="text-sm font-medium text-gray-600 flex items-center">
                                <!-- Optional: Icon <i data-lucide="tag" class="size-4 mr-2 text-purple-500"></i> -->
                                 Category
                             </span>
                            <span id="productModalCategory" class="text-sm font-semibold text-gray-800">Uncategorized</span>
                        </div>
                         <!-- Divider -->
                         <hr class="border-gray-100">
                         <!-- Availability -->
                        <div class="flex items-center justify-between">
                             <span class="text-sm font-medium text-gray-600 flex items-center">
                                <!-- Optional: Icon <i data-lucide="package" class="size-4 mr-2 text-orange-500"></i> -->
                                 Availability
                             </span>
                            <span id="productModalAvailability" class="px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Checking...</span>
                        </div>
                    </div>
                     
                    <!-- Description -->
                    <div class="mb-6">
                         <h4 class="text-lg font-semibold text-gray-800 mb-2">Description</h4>
                         <!-- Removed border/bg from description div for cleaner look -->
                         <div id="productModalDescription" class="prose prose-sm max-w-none text-gray-600">
                              <p>No description available.</p>
                         </div>
                    </div>

                    <!-- Product Options Display -->
                    <div id="productModalOptionsContainer" class="space-y-3 mb-6 pt-4 border-t border-gray-200/60 hidden">
                         <h4 class="text-lg font-semibold text-gray-800">Select Options</h4>
                    </div>

                </div> <!-- End Right Side -->
            </div> <!-- End Modal Content Flex -->

            <!-- Footer with Buttons -->
            <div class="bg-gray-50/70 px-4 py-3 sm:px-6 flex justify-between items-center md:justify-end md:items-end gap-2 border-t border-gray-200">
                <button type="button" id="productModalCloseButtonFooter" 
                        class="w-full inline-flex justify-center gap-1 items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto">
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    Close
                </button>
                 <button type="button" id="productModalAddToCartButton" 
                        data-product-id=""
                        class="w-full inline-flex justify-center gap-1 items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto">
                    <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Add to Cart
                </button>
            </div>

        </div> 
    </div>
</div> 