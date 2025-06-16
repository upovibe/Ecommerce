<?php
global $featuredCategories;
?>
<!-- Filter Button and Panel -->
<div class="relative flex-shrink-0" x-data="{ 
    isFilterOpen: false,
    minPrice: null,
    maxPrice: null,
    selectedCategory: '',
    selectedSubcategory: '',
    clearFilters() {
        this.minPrice = null;
        this.maxPrice = null;
        this.selectedCategory = '';
        this.selectedSubcategory = '';
        this.isFilterOpen = false;
        window.resetAllFilters();
    }
}">
    <!-- Filter Button -->
    <button @click="isFilterOpen = !isFilterOpen" type="button"
        class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
        <i data-lucide="sliders-horizontal" class="size-4 text-gray-500"></i>
        <span x-show="minPrice || maxPrice || selectedCategory || selectedSubcategory" x-cloak 
              class="ml-1.5 w-2 h-2 bg-blue-500 rounded-full"></span>
    </button>

    <!-- Filter Panel -->
    <div x-show="isFilterOpen" @click.outside="isFilterOpen = false"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 p-4 space-y-4 z-50"
        x-cloak>

        <h4 class="text-sm font-medium text-gray-500 border-b pb-2">Filter Products</h4>

        <!-- Price Range Filter -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Price Range</label>
            <div class="flex items-center space-x-2">
                <input type="number" x-model.number="minPrice" placeholder="Min <?= htmlspecialchars(STORE_SETTINGS['currency_symbol'] ?? '$') ?>"
                    @input="window.handlePriceFilter($event.target.value, maxPrice)"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                <span class="text-gray-400">-</span>
                <input type="number" x-model.number="maxPrice" placeholder="Max <?= htmlspecialchars(STORE_SETTINGS['currency_symbol'] ?? '$') ?>"
                    @input="window.handlePriceFilter(minPrice, $event.target.value)"
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Category Filter -->
        <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Category</label>
            <select x-model="selectedCategory"
                @change="selectedSubcategory = ''; window.handleCategoryFilter($event.target.value, '')"
                class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Categories</option>
                <?php foreach ($featuredCategories as $category): ?>
                    <?php if (!is_array($category) || empty($category['slug'])) continue; ?>
                    <option value="<?= htmlspecialchars($category['slug']) ?>" 
                            data-has-subcategories="<?= !empty($category['subcategories']) ? 'true' : 'false' ?>"
                            <?= isset($_GET['category']) && $_GET['category'] === $category['slug'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
                <option value="uncategorized" <?= isset($_GET['category']) && $_GET['category'] === 'uncategorized' ? 'selected' : '' ?>>Uncategorized</option>
            </select>
        </div>

        <!-- Subcategory Filter (Dynamic) -->
        <div class="space-y-2" x-show="selectedCategory && document.querySelector(`option[value='${selectedCategory}']`)?.dataset?.hasSubcategories === 'true'">
            <label class="text-sm font-medium text-gray-700">Subcategory</label>
            <select x-model="selectedSubcategory"
                @change="window.handleCategoryFilter(selectedCategory, $event.target.value)"
                class="block w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Subcategories</option>
                <?php foreach ($featuredCategories as $category): ?>
                    <?php if (!empty($category['subcategories']) && !empty($category['slug'])): ?>
                        <template x-if="selectedCategory === '<?= htmlspecialchars($category['slug']) ?>'">
                            <?php foreach ($category['subcategories'] as $subcategory): ?>
                                <option value="<?= htmlspecialchars($subcategory['slug']) ?>"
                                        <?= isset($_GET['subcategory_slug']) && $_GET['subcategory_slug'] === $subcategory['slug'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($subcategory['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </template>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Clear Filters -->
        <div class="pt-3 border-t flex justify-end">
            <button @click="clearFilters()"
                class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                Clear All Filters
            </button>
        </div>
    </div>
</div> 