<?php
// Product View Modal
?>
<div x-show="isModalOpen" x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" role="dialog" aria-modal="true"
     x-data="{ isImageLightboxOpen: false }"
     @keydown.escape.window="isModalOpen = false; isImageLightboxOpen = false">
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isModalOpen" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" 
             @click="isModalOpen = false" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div x-show="isModalOpen" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full"> <!-- Wider max-w -->
            
             <!-- Header with ID, Copy Link, Close -->
             <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50">
                 <div class="flex items-center space-x-2">
                    <i data-lucide="hash" class="size-4 text-gray-400 flex-shrink-0"></i>
                    <span class="text-sm font-medium text-gray-500">ID: <span x-text="viewingProduct?.id || 'N/A'"></span></span>
                    <span class="text-sm font-medium text-gray-500 pl-2 border-l border-gray-200/60" x-show="viewingProduct?.slug">
                        <span class="font-mono text-blue-600">@<span x-text="viewingProduct?.slug"></span></span>
                    </span>
                 </div>
                 <button type="button" @click="isModalOpen = false" class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
             </div>

            <!-- Modal Content -->
            <template x-if="viewingProduct">
                <div class="flex flex-col md:flex-row" style="max-height: 80vh;">
                    <!-- Left Side: Images -->
                    <div class="md:w-2/5 p-6 flex-shrink-0 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center border-r border-gray-200/60">
                        <div class="w-full max-w-xs mx-auto">
                            <!-- Main Image Display -->
                            <div class="aspect-w-1 aspect-h-1 mb-3">
                                <img x-show="viewingProduct.image && viewingProduct.image.length > 0" 
                                     :src="viewingProduct.currentImageIndex !== undefined ? viewingProduct.image[viewingProduct.currentImageIndex] : viewingProduct.image[0]" 
                                     :alt="viewingProduct.name" 
                                     @click="isImageLightboxOpen = true" 
                                     class="w-full h-48 md:h-full object-cover rounded-lg shadow-lg bg-white/50 backdrop-blur-sm cursor-pointer transition-transform hover:scale-105">
                                <img x-show="!viewingProduct.image || viewingProduct.image.length === 0" 
                                     src="../assets/images/placeholder.png" alt="Placeholder" 
                                     class="w-full h-48 md:h-full object-contain rounded-lg shadow-lg bg-white/50 backdrop-blur-sm">
                            </div>
                            
                            <!-- Thumbnail Navigation (if multiple images) -->
                            <div x-show="viewingProduct.image && viewingProduct.image.length > 1" class="flex gap-2 justify-center">
                                <template x-for="(imageUrl, index) in viewingProduct.image" :key="index">
                                    <button @click="viewingProduct.currentImageIndex = index"
                                            :class="{'ring-2 ring-blue-500': (viewingProduct.currentImageIndex || 0) === index}"
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
                        <h3 class="text-3xl leading-9 font-bold text-gray-900 mb-6" id="modal-title" x-text="viewingProduct.name"></h3>
                        
                        <!-- Key Details Section - Card Style -->
                        <div class="bg-white border border-gray-200/80 rounded-lg shadow-sm p-4 mb-6 space-y-3">
                            <!-- Price -->
                            <div class="flex items-center justify-between">
                                 <span class="text-sm font-medium text-gray-600 flex items-center"><i data-lucide="dollar-sign" class="size-4 mr-2 text-blue-500"></i>Price</span>
                                 <div class="text-lg font-semibold text-right">
                                     <template x-if="viewingProduct.original_price && parseFloat(viewingProduct.original_price) > parseFloat(viewingProduct.price)">
                                         <span class="text-gray-500 line-through mr-1.5 text-base font-normal" x-text="formatCurrency(viewingProduct.original_price)"></span>
                                     </template>
                                     <span class="text-gray-900" x-text="formatCurrency(viewingProduct.price)"></span>
                                     <template x-if="viewingProduct.discount_percentage && parseFloat(viewingProduct.discount_percentage) > 0">
                                         <span class="ml-1.5 inline-block bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full" 
                                                 x-text="'-' + parseFloat(viewingProduct.discount_percentage).toFixed(0) + '%'"></span>
                                     </template>
                                 </div>
                            </div>
                             <!-- Divider -->
                             <hr class="border-gray-100">
                            <!-- Category -->
                            <div class="flex items-center justify-between">
                                 <span class="text-sm font-medium text-gray-600 flex items-center"><i data-lucide="tag" class="size-4 mr-2 text-purple-500"></i>Category</span>
                                 <span class="text-sm font-semibold text-gray-800" x-text="viewingProduct.category_name || 'Uncategorized'"></span>
                            </div>
                             <!-- Divider -->
                             <hr class="border-gray-100">
                             <!-- Stock -->
                             <div class="flex items-center justify-between">
                                 <span class="text-sm font-medium text-gray-600 flex items-center"><i data-lucide="package" class="size-4 mr-2 text-orange-500"></i>Availability</span>
                                 <span class="px-2.5 py-1 rounded-full text-xs font-medium" 
                                       :class="{
                                            'bg-green-100 text-green-800': viewingProduct.availability_status === 'in_stock',
                                            'bg-yellow-100 text-yellow-800': viewingProduct.availability_status === 'backorder',
                                            'bg-red-100 text-red-800': viewingProduct.availability_status === 'sold_out',
                                            'bg-gray-100 text-gray-700': !viewingProduct.is_active // Override color if inactive
                                        }">
                                        <span x-text="viewingProduct.is_active ? 
                                                       (viewingProduct.availability_status === 'in_stock' ? (viewingProduct.stock + ' In Stock') : 
                                                        (viewingProduct.availability_status === 'backorder' ? 'Backorder' : 'Sold Out')) 
                                                      : 'Inactive'">
                                        </span>
                                 </span>
                             </div>
                             <!-- Backorder Status -->
                             <div class="flex items-center justify-between" x-show="viewingProduct.backorder">
                                 <hr class="border-gray-100 w-full my-1 sm:hidden"> <!-- Divider on small screens -->
                                 <span class="text-sm font-medium text-gray-600 flex items-center"><i data-lucide="history" class="size-4 mr-2 text-cyan-500"></i>Backorder</span>
                                 <span class="text-xs font-semibold text-cyan-800 bg-cyan-100 px-2.5 py-0.5 rounded-full inline-flex items-center">
                                     Allowed
                                 </span>
                             </div>
                             <!-- Featured Status -->
                             <div class="flex items-center justify-between" x-show="viewingProduct.featured == 1">
                                 <hr class="border-gray-100 w-full my-1 sm:hidden"> <!-- Divider on small screens -->
                                 <span class="text-sm font-medium text-gray-600 flex items-center"><i data-lucide="star" class="size-4 mr-2 text-yellow-500"></i>Status</span>
                                 <span class="text-xs font-semibold text-yellow-800 bg-yellow-100 px-2.5 py-0.5 rounded-full inline-flex items-center">
                                     Featured
                                 </span>
                            </div>
                        </div>

                         <!-- Description Section -->
                         <div class="mb-6">
                              <h4 class="text-lg font-semibold text-gray-800 mb-2">Description</h4>
                              <div class="prose prose-sm max-w-none text-gray-600 bg-gray-50/50 p-4 rounded-lg border border-gray-200/60 shadow-inner" 
                                    x-html="viewingProduct.description || '<p>No description available.</p>'">
                              </div>
                         </div>

                         <!-- Loading Indicator for Options -->
                         <div x-show="isLoadingDetails" class="text-center py-8">
                             <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                             <p class="text-sm text-gray-500 mt-2">Loading options...</p>
                         </div>

                         <!-- Product Options Display -->
                         <div x-show="!isLoadingDetails && viewingProductOptions.length > 0" x-cloak class="space-y-3 mb-6 pt-4 border-t border-gray-200/60">
                             <h4 class="text-lg font-semibold text-gray-800">Available Options</h4>
                             <template x-for="(optionGroup, index) in viewingProductOptions" :key="index">
                                 <div class="text-sm">
                                     <span class="font-medium text-gray-600 block mb-1.5" x-text="optionGroup.name"></span>
                                     <div class="flex flex-wrap gap-2 mt-1">
                                         <template x-for="value in optionGroup.values" :key="value + index + '_view'">
                                             <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white text-gray-800 border border-gray-300/70 shadow-sm">
                                                 <span x-text="value"></span>
                                             </span>
                                         </template>
                                     </div>
                                 </div>
                             </template>
                         </div>
                    </div>
                </div>
            </template>

            <!-- Footer with Buttons -->
            <div class="bg-gray-50/70 px-4 py-3 sm:px-6 flex justify-between items-center md:justify-end md:items-end gap-2 border-t border-gray-200">
                <button type="button" @click="isModalOpen = false" 
                        class="w-full inline-flex justify-center gap-1 items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    <i data-lucide="x" class="size-4"></i>Close
                </button>
                <!-- Delete Button -->
                <button type="button" 
                        @click="confirmDelete(viewingProduct.id); isModalOpen = false;" 
                        x-show="viewingProduct" 
                        class="w-full inline-flex justify-center gap-1 items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                     <i data-lucide="trash-2" class="size-4"></i>Delete
                </button>
                <!-- Edit Button -->
                <button type="button" 
                        @click="isModalOpen = false; openEditModal(viewingProduct.id)" 
                        x-show="viewingProduct" 
                        class="w-full inline-flex justify-center gap-1 items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    <i data-lucide="edit" class="size-4"></i>Edit
                </button>
            </div>

        </div> 
    </div>

    <!-- Image Lightbox Overlay (Copied from product_modal.php) -->
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

       <img x-bind:src="viewingProduct?.image && viewingProduct.image.length > 0 ? (viewingProduct.currentImageIndex !== undefined ? viewingProduct.image[viewingProduct.currentImageIndex] : viewingProduct.image[0]) : '../assets/images/placeholder.png'" 
            x-bind:alt="viewingProduct?.name + ' - Full size'" 
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

<!-- Product Display -->
<div class="bg-white rounded-lg shadow-sm">
    <!-- Loading State -->
    <div x-show="isLoading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-sm text-gray-500">Loading products...</p>
    </div>
</div> 