<!-- Search -->
<div class="relative flex-grow w-full md:w-auto">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <i data-lucide="search" class="w-5 h-5 text-gray-400"></i>
    </div>
    <input type="search" x-model.debounce.300ms="searchTerm" placeholder="Search products..."
        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
</div>

<!-- Refresh Button -->
<div class="flex-shrink-0">
    <button @click="refreshProducts()" :disabled="isRefreshing"
        class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
        <i data-lucide="refresh-cw" class="h-4 w-4" :class="{ 'animate-spin': isRefreshing }"></i>
    </button>
</div>

<!-- View Toggle -->
<div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-md flex-shrink-0">
    <button @click="viewMode = 'grid'"
        :class="{ 'bg-white text-blue-600 shadow-sm': viewMode === 'grid', 'text-gray-500 hover:text-gray-700': viewMode !== 'grid' }"
        class="p-1.5 rounded-md focus:outline-none transition-colors">
        <i data-lucide="layout-grid" class="w-5 h-5"></i>
    </button>
    <button @click="viewMode = 'list'"
        :class="{ 'bg-white text-blue-600 shadow-sm': viewMode === 'list', 'text-gray-500 hover:text-gray-700': viewMode !== 'list' }"
        class="p-1.5 rounded-md focus:outline-none transition-colors">
        <i data-lucide="list" class="w-5 h-5"></i>
    </button>
</div>

<div class="flex-shrink-0">
    <!-- Filter Trigger Button -->
    <div class="flex-shrink-0">
        <button @click="isFilterDropdownOpen = !isFilterDropdownOpen" type="button"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <i data-lucide="sliders-horizontal" class="size-4 text-gray-500"></i>
            <span x-show="minPrice || maxPrice || selectedCategoryId || selectedStockStatus || selectedActiveStatus" x-cloak class="ml-1.5 w-2 h-2 bg-blue-500 rounded-full"></span>
        </button>
    </div>

    <!-- Filter Dropdown Panel -->
    <div x-show="isFilterDropdownOpen"
        @click.outside="isFilterDropdownOpen = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute top-full right-0 mt-2 w-full md:w-auto md:max-w-xl z-20 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none p-4 space-y-4"
        x-cloak>

        <h4 class="text-sm font-medium text-gray-500 border-b pb-2 mb-4">Filter Options</h4>

        <!-- Price Filter -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
            <span class="text-sm text-gray-700 font-medium flex-shrink-0 w-20">Price Range:</span>
            <div class="flex items-center space-x-2 flex-grow">
                <input type="number" x-model.number="minPrice" placeholder="Min <?php echo htmlspecialchars($currencySymbol ?? '$ '); ?>" min="0"
                    class="w-full sm:w-24 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                <span class="text-gray-400">-</span>
                <input type="number" x-model.number="maxPrice" placeholder="Max <?php echo htmlspecialchars($currencySymbol ?? '$ '); ?>" min="0"
                    class="w-full sm:w-24 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Category Filter -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
            <label for="filter-category" class="text-sm text-gray-700 font-medium flex-shrink-0 w-20">Category:</label>
            <select id="filter-category" x-model="selectedCategoryId"
                class="block w-full pl-3 pr-8 py-1.5 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">All Categories</option>
                <template x-for="category in categories" :key="category.id">
                    <option :value="category.id" x-text="category.name"></option>
                </template>
                <option value="uncategorized">Uncategorized</option>
            </select>
        </div>

        <!-- Stock Filter -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
            <label for="filter-stock" class="text-sm text-gray-700 font-medium flex-shrink-0 w-20">Stock Status:</label>
            <select id="filter-stock" x-model="selectedStockStatus"
                class="block w-full pl-3 pr-8 py-1.5 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">All Stock Statuses</option>
                <option value="in_stock">In Stock</option>
                <option value="out_of_stock">Out of Stock</option>
            </select>
        </div>

        <!-- Active Status Filter -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
            <label for="filter-active" class="text-sm text-gray-700 font-medium flex-shrink-0 w-20">Active Status:</label>
            <select id="filter-active" x-model="selectedActiveStatus"
                class="block w-full pl-3 pr-8 py-1.5 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                <option value="">All Statuses</option>
                <option value="active">Active Only</option>
                <option value="inactive">Inactive Only</option>
            </select>
        </div>

        <!-- Clear Filters Button -->
        <div class="border-t pt-3 mt-3 flex justify-end">
            <button @click="minPrice = null; maxPrice = null; selectedCategoryId = ''; selectedStockStatus = ''; selectedActiveStatus = ''; isFilterDropdownOpen = false"
                class="text-sm text-blue-600 hover:underline">
                Clear All Filters
            </button>
        </div>

    </div>
</div> 