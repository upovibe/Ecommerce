<!-- Grid View -->
<div x-show="viewMode === 'grid' && filteredProducts.length > 0" x-cloak
    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <template x-for="product in filteredProducts" :key="product.id">
        <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-200 overflow-hidden flex flex-col">
            <div @click="openModal(product)" class="block h-48 overflow-hidden relative group cursor-pointer">
                <img :src="getProductThumbnail(product.image)"
                    alt=""
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">

                <!-- Full Image Overlay Container -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/50 to-transparent opacity-100 group-hover:opacity-0 transition-opacity duration-300 flex flex-col p-2 pointer-events-none">
                    <div class="flex justify-between items-start text-xs mb-auto">
                        <!-- Top-Left: Discount Info (Percentage + Original Price) -->
                        <div class="flex items-center space-x-1">
                            <template x-if="product.original_price && parseFloat(product.original_price) > parseFloat(product.price)">
                                <span class="bg-red-500/90 text-white px-1.5 py-0.5 rounded text-[10px] font-semibold shadow"
                                    x-text="'-' + parseFloat(product.discount_percentage).toFixed(0) + '%'">
                                </span>
                            </template>
                        </div>

                        <span class="px-1.5 py-0.5 rounded text-[10px] font-medium shadow"
                            :class="{
                                  'bg-green-100/90 text-green-900': product.availability_status === 'in_stock',
                                  'bg-yellow-100/90 text-yellow-900': product.availability_status === 'backorder',
                                  'bg-red-100/90 text-red-900': product.availability_status === 'sold_out',
                                  'bg-gray-100/90 text-gray-800': !product.is_active // Override if inactive
                              }"
                            x-text="product.is_active ? 
                                        (product.availability_status === 'in_stock' ? (product.stock + ' In Stock') : 
                                         (product.availability_status === 'backorder' ? 'Backorder' : 'Sold Out')) 
                                       : 'Inactive'">
                        </span>
                    </div>

                    <div class="flex justify-between items-end">
                        <div class="text-white">
                            <h4 class="text-white text-sm font-semibold truncate max-w-20 md:max-w-full" x-text="product.name"></h4>
                            <!-- Updated Price Display Logic (Grid) -->
                            <p class="text-xs text-blue-300 font-medium">
                                <template x-if="product.original_price && parseFloat(product.original_price) > parseFloat(product.price)">
                                    <span>
                                        <span x-text="formatCurrency(product.price)" class="mr-1"></span>
                                        <del class="text-gray-50 mr-1" x-text="formatCurrency(product.original_price)"></del>
                                    </span>
                                </template>
                                <template x-if="!product.original_price || parseFloat(product.original_price) <= parseFloat(product.price)">
                                    <span x-text="formatCurrency(product.price)"></span>
                                </template>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (Now always visible) -->
                <div class="absolute bottom-2 right-2 flex space-x-1 pointer-events-auto">
                    <button type="button" @click.stop="openEditModal(product.id)"
                        title="Edit Product"
                        class="text-indigo-200 hover:text-white bg-black/80 hover:bg-indigo-600 p-1.5 rounded-md shadow transition-colors">
                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                    </button>
                    <button type="button" @click.stop="confirmDelete(product.id)"
                        title="Delete Product"
                        class="text-red-300 hover:text-white bg-black/80 hover:bg-red-600 p-1.5 rounded-md shadow transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<!-- List View -->
<div x-show="viewMode === 'list' && filteredProducts.length > 0" x-cloak class="space-y-4">
    <template x-for="product in filteredProducts" :key="product.id">
        <!-- Individual Product Card -->
        <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 shadow-gray-50 overflow-hidden flex items-center p-2 space-x-3">
            <!-- Image Container -->
            <div class="relative flex-shrink-0">
                <img :src="getProductThumbnail(product.image)" alt=""
                    class="h-16 w-16 rounded-md object-cover"
                    :class="{ 'opacity-50 grayscale': !product.is_active }">
                <!-- Stock Badge Overlay -->
                <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded text-[10px] font-medium shadow"
                    :class="{
                           'bg-green-100/90 text-green-900': product.availability_status === 'in_stock',
                           'bg-yellow-100/90 text-yellow-900': product.availability_status === 'backorder',
                           'bg-red-100/90 text-red-900': product.availability_status === 'sold_out'
                           // We show inactive state elsewhere
                       }"
                    x-text="product.availability_status === 'in_stock' ? product.stock 
                               : (product.availability_status === 'backorder' ? 'B/O' : 'Sold')">
                </span>
                <!-- Inactive Indicator -->
                <span x-show="!product.is_active" x-cloak class="absolute bottom-1 right-1 bg-gray-500 text-white p-0.5 rounded-full">
                    <i data-lucide="zap-off" class="w-3 h-3"></i>
                </span>
            </div>

            <!-- Main Info Area (Grows) -->
            <div class="flex-grow min-w-0">
                <h3 class="text-md font-semibold text-gray-900 truncate" x-text="product.name"></h3>
                <p class="text-sm text-gray-500 truncate" x-text="product.category_name || 'Uncategorized'"></p>
                <!-- Corrected Price Display Logic (List) -->
                <div class="mt-1">
                    <template x-if="product.original_price && parseFloat(product.original_price) > parseFloat(product.price)">
                        <span>
                            <span class="text-sm text-gray-500 line-through mr-1" x-text="formatCurrency(product.original_price)"></span>
                            <span class="text-md font-medium text-blue-600 mr-1" x-text="formatCurrency(product.price)"></span>
                            <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-1.5 py-0.5 rounded-md"
                                x-text="'-' + parseFloat(product.discount_percentage).toFixed(0) + '%'"></span>
                        </span>
                    </template>
                    <template x-if="!product.original_price || parseFloat(product.original_price) <= parseFloat(product.price)">
                        <span class="text-md font-medium text-blue-600" x-text="formatCurrency(product.price)"></span>
                    </template>
                </div>
            </div>

            <!-- Actions Area (Fixed Width, Pushed Right) -->
            <div class="flex flex-shrink-0 space-x-1 ml-auto">
                <button type="button" @click.stop="openModal(product)"
                    title="View Details"
                    class="text-blue-600 hover:text-blue-900 p-1 hover:bg-blue-100 rounded transition-colors">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </button>
                <button type="button" @click.stop="openEditModal(product.id)"
                    title="Edit Product"
                    class="text-indigo-600 hover:text-indigo-900 p-1 hover:bg-indigo-100 rounded transition-colors">
                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                </button>
                <button type="button" @click.stop="confirmDelete(product.id)"
                    title="Delete Product"
                    class="text-red-600 hover:text-red-900 p-1 hover:bg-red-100 rounded transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </template>
</div>

<!-- No Results Messages -->
<div x-show="!isLoading && allProducts.length === 0" x-cloak class="text-center py-10 px-6 bg-white rounded-lg shadow-sm">
    <i data-lucide="package-x" class="w-16 h-16 mx-auto text-gray-300"></i>
    <p class="mt-4 text-lg font-medium text-gray-600">No Products Available</p>
    <p class="mt-1 text-sm text-gray-500">There are no products in the database yet.</p>
    <button @click="openAddModal()" 
        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
        Add New Product
    </button>
</div>

<div x-show="!isLoading && allProducts.length > 0 && filteredProducts.length === 0" x-cloak class="text-center py-10 px-6 bg-white rounded-lg shadow-sm">
    <i data-lucide="search-x" class="w-16 h-16 mx-auto text-gray-300"></i>
    <p class="mt-4 text-lg font-medium text-gray-600">No Matching Products</p>
    <p class="mt-1 text-sm text-gray-500">No products match your current filters or search terms.</p>
    <button @click="searchTerm = ''; minPrice = null; maxPrice = null; selectedCategoryId = ''; selectedSubcategoryId = ''; selectedStockStatus = ''; selectedActiveStatus = ''; isFilterDropdownOpen = false" 
        class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <i data-lucide="x" class="w-4 h-4 mr-2"></i>
        Clear All Filters
    </button>
</div> 