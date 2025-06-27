<?php
// Add Product Modal
// Assumes $categories is available from the parent PHP file (products.php)
?>
<div x-show="isAddModalOpen" x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="add-modal-title" role="dialog" aria-modal="true"
    @keydown.escape.window="closeAddModal()">

    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isAddModalOpen"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"
            @click="closeAddModal()" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div x-show="isAddModalOpen"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">

            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50">
                <h3 class="text-xl font-semibold text-gray-900" id="add-modal-title">Add New Product</h3>
                <button type="button" @click="closeAddModal()" class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <!-- Form Content -->
            <form id="addProductForm" @submit.prevent="handleProductAdd" enctype="multipart/form-data">
                <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto">

                    <div>
                        <label for="product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                        <input type="text" name="product_name" id="product_name" required
                            x-model="productName"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                    </div>

                    <div>
                        <label for="product_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="product_description" id="product_description" rows="4"
                            x-model="productDescription"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label for="product_price" class="block text-sm font-medium text-gray-700 mb-1">Price <span class="text-red-500">*</span></label>
                            <div class="relative mt-1 rounded-lg shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm"><?= htmlspecialchars($currencySymbol) ?></span>
                                </div>
                                <input type="number" name="product_price" id="product_price" required step="0.01" min="0"
                                    x-model.number="productPrice"
                                    class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"
                                    placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label for="product_stock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                            <input type="number" name="product_stock" id="product_stock" min="0" value="0"
                                x-model.number="productStock"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                        </div>
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select name="category_id" id="category_id"
                                x-model="categoryId"
                                @change="handleCategoryChange"
                                class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                <option value="">Uncategorized</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="category.id" x-text="category.name"></option>
                                </template>
                            </select>
                        </div>
                        <div x-show="categoryId && categoryId !== '' && getSubcategoriesForCategory(categoryId).length > 0">
                            <label for="subcategory_id" class="block text-sm font-medium text-gray-700 mb-1">Subcategory</label>
                            <select name="subcategory_id" id="subcategory_id"
                                x-model="subcategoryId"
                                class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                <option value="">Select Subcategory</option>
                                <template x-for="subcategory in getSubcategoriesForCategory(categoryId)" :key="subcategory.id">
                                    <option :value="subcategory.id" x-text="subcategory.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <!-- Add Discount Percentage input next -->
                    <div class="space-y-4">
                        <!-- Discount Percentage Toggle -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Add Discount Percentage</span>
                            <button type="button" @click="discountPercentageEnabled = !discountPercentageEnabled"
                                :class="{ 'bg-blue-600': discountPercentageEnabled, 'bg-gray-200': !discountPercentageEnabled }"
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                role="switch" :aria-checked="discountPercentageEnabled.toString()">
                                <span class="sr-only">Add Discount Percentage</span>
                                <span aria-hidden="true"
                                    :class="{ 'translate-x-5': discountPercentageEnabled, 'translate-x-0': !discountPercentageEnabled }"
                                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </button>
                        </div>

                        <!-- Percentage Input (Conditional) -->
                        <div x-show="discountPercentageEnabled" x-cloak class="border border-gray-200 rounded-lg p-4 bg-gray-50/50">
                            <div>
                                <label for="discount_percentage" class="block text-sm font-medium text-gray-700 mb-1">Discount Percentage (Optional)</label>
                                <div class="relative mt-1 rounded-lg shadow-sm">
                                    <input type="number" name="discount_percentage" id="discount_percentage"
                                        x-model.number="discountPercentage"
                                        min="0" max="100" step="0.01"
                                        placeholder="e.g., 15"
                                        :required="discountPercentageEnabled"
                                        class="block w-full pr-8 pl-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 sm:text-sm">%</span>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">If set, an "original price" will be calculated based on the Selling Price.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modern Image Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                        <label for="product_image"
                            class="relative mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200 min-h-[150px]">

                            <!-- Placeholder Content (Icon & Text) -->
                            <div x-show="imageUrl === '../assets/images/placeholder.png'" class="space-y-1 text-center">
                                <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400"></i>
                                <div class="flex text-sm text-gray-600">
                                    <span class="relative bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload a file</span>
                                        <!-- Input is now part of the Alpine component -->
                                        <input type="file" name="product_image" id="product_image" accept="image/*"
                                            @change="handleFileSelect($event)"
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    </span>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 4MB</p>
                            </div>

                            <!-- Image Preview -->
                            <div x-show="imageUrl !== '../assets/images/placeholder.png'" class="relative w-full h-full flex justify-center items-center" x-cloak>
                                <img :src="imageUrl" alt="Image Preview"
                                    class="max-h-48 max-w-full rounded-lg object-contain shadow-sm">
                                <!-- Optional: Add a button to remove/change image -->
                                <button type="button" @click="imageUrl = '../assets/images/placeholder.png'; newProductImage = null; document.getElementById('product_image').value = null;"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </div>

                            <!-- Hidden actual file input, triggered by label -->
                            <input type="file" name="product_image_fallback" id="product_image" accept="image/*"
                                @change="handleFileSelect($event)"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer sr-only">
                        </label>
                    </div>
                    <!-- End Modern Image Upload -->

                    <!-- Status Toggles -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Featured Toggle -->
                        <div class="flex items-center justify-between py-2 border border-gray-200 rounded-lg px-3 bg-white/50 shadow-sm">
                            <span class="text-sm font-medium text-gray-700 flex items-center">
                                <i data-lucide="star" class="w-4 h-4 mr-2 text-yellow-500"></i>Featured
                            </span>
                            <button type="button" @click="isFeatured = !isFeatured"
                                :class="{ 'bg-blue-600': isFeatured, 'bg-gray-200': !isFeatured }"
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                role="switch" :aria-checked="isFeatured.toString()">
                                <span class="sr-only">Featured Product</span>
                                <span aria-hidden="true"
                                    :class="{ 'translate-x-5': isFeatured, 'translate-x-0': !isFeatured }"
                                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </button>
                        </div>

                        <!-- Active Status Toggle -->
                        <div class="flex items-center justify-between py-2 border border-gray-200 rounded-lg px-3 bg-white/50 shadow-sm">
                            <span class="text-sm font-medium text-gray-700 flex items-center">
                                <i data-lucide="zap" class="w-4 h-4 mr-2 text-green-500"></i>Active Status
                            </span>
                            <button type="button" @click="isActive = !isActive"
                                :class="{ 'bg-blue-600': isActive, 'bg-gray-200': !isActive }"
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                role="switch" :aria-checked="isActive.toString()">
                                <span class="sr-only">Active Status</span>
                                <span aria-hidden="true"
                                    :class="{ 'translate-x-5': isActive, 'translate-x-0': !isActive }"
                                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </button>
                        </div>

                        <!-- Allow Backorder Toggle -->
                        <div class="flex items-center justify-between py-2 border border-gray-200 rounded-lg px-3 bg-white/50 shadow-sm">
                            <span class="text-sm font-medium text-gray-700 flex items-center">
                                <i data-lucide="history" class="w-4 h-4 mr-2 text-cyan-500"></i>Allow Backorder
                            </span>
                            <button type="button" @click="allowBackorder = !allowBackorder"
                                :disabled="productStock > 0"
                                :class="{
                                          'bg-blue-600': allowBackorder,
                                          'bg-gray-200': !allowBackorder,
                                          'opacity-50 cursor-not-allowed': productStock > 0
                                      }"
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                role="switch" :aria-checked="allowBackorder.toString()">
                                <span class="sr-only">Allow Backorder</span>
                                <span aria-hidden="true"
                                    :class="{ 'translate-x-5': allowBackorder, 'translate-x-0': !allowBackorder }"
                                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </button>
                        </div>
                    </div>

                    <hr class="border-gray-200/70 my-1">

                    <!-- Product Options Section -->
                    <div class="space-y-4">
                        <!-- Options Toggle -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">Enable Product Options (e.g., Size, Color)</span>
                            <button type="button" @click="optionsEnabled = !optionsEnabled"
                                :class="{ 'bg-blue-600': optionsEnabled, 'bg-gray-200': !optionsEnabled }"
                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                role="switch" :aria-checked="optionsEnabled.toString()">
                                <span class="sr-only">Enable Product Options</span>
                                <span aria-hidden="true"
                                    :class="{ 'translate-x-5': optionsEnabled, 'translate-x-0': !optionsEnabled }"
                                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                            </button>
                        </div>

                        <!-- Options Management Area (Conditional) -->
                        <div x-show="optionsEnabled" x-cloak class="space-y-4 border border-gray-200 rounded-lg p-4 bg-gray-50/50">
                            <template x-for="(optionGroup, index) in productOptions" :key="index">
                                <div class="flex items-start space-x-3 bg-white p-3 rounded-md shadow-sm border border-gray-100">
                                    <div class="flex-grow space-y-2">
                                        <!-- Option Name -->
                                        <div>
                                            <label :for="'option_name_' + index" class="block text-xs font-medium text-gray-600 mb-1">Option Name (e.g., Size)</label>
                                            <input type="text" :id="'option_name_' + index" x-model="optionGroup.name" placeholder="Size"
                                                class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                        </div>
                                        <!-- Option Values (Tags Input Simulation) -->
                                        <div x-data="{ currentInputValue: '' }">
                                            <label :for="'option_values_input_' + index" class="block text-xs font-medium text-gray-600 mb-1">Values (type, then comma)</label>
                                            <!-- Container styled like input -->
                                            <div @click="$refs['input_' + index].focus()" class="flex flex-wrap items-center gap-1 w-full px-2 py-1 min-h-[36px] border border-gray-300 rounded-md shadow-sm bg-white cursor-text">
                                                <!-- Badges -->
                                                <template x-for="(value, valueIndex) in optionGroup.values.split(',').map(v => v.trim()).filter(v => v !== '')" :key="value + index + '_badge'">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 shadow-sm">
                                                        <span x-text="value"></span>
                                                        <!-- Remove badge button -->
                                                        <button type="button" @click.stop="optionGroup.values = optionGroup.values.split(',').map(v => v.trim()).filter(v => v !== '').filter((_, i) => i !== valueIndex).join(', ')"
                                                            class="ml-1.5 flex-shrink-0 text-blue-400 hover:text-blue-600 focus:outline-none">
                                                            <i data-lucide="x" class="w-3 h-3"></i>
                                                        </button>
                                                    </span>
                                                </template>
                                                <!-- Actual Input (Borderless) -->
                                                <input type="text" :id="'option_values_input_' + index"
                                                    :ref="'input_' + index"
                                                    x-model="currentInputValue"
                                                    @keydown.comma.prevent="
                                                            if (currentInputValue.trim() !== '') {
                                                                const newValue = currentInputValue.trim().toLowerCase();
                                                                let existingValues = optionGroup.values.split(',').map(v => v.trim()).filter(v => v !== '');
                                                                if (!existingValues.includes(newValue)) { // Prevent duplicates
                                                                     optionGroup.values = [...existingValues, newValue].join(', ');
                                                                }
                                                                currentInputValue = '';
                                                            }
                                                        "
                                                    @keydown.backspace="
                                                            if (currentInputValue === '' && optionGroup.values.length > 0) {
                                                                optionGroup.values = optionGroup.values.split(',').map(v => v.trim()).filter(v => v !== '').slice(0, -1).join(', ');
                                                            }
                                                        "
                                                    placeholder="Type value, then comma..."
                                                    class="flex-grow p-0 border-none focus:ring-0 text-sm focus:outline-none min-w-[100px]">
                                            </div>
                                            <!-- Hidden input to store the actual comma-separated string for the form (optional, as x-model on optionGroup.values already holds it) -->
                                            <!-- <input type="hidden" :name="'options[' + index + '][values]'" x-model="optionGroup.values"> -->
                                            <p class="mt-1 text-xs text-gray-500">Type a value, press comma to add it. Backspace on empty input removes last value.</p>
                                        </div>
                                    </div>
                                    <!-- Remove Button -->
                                    <button type="button" @click="removeOptionGroup(index)" title="Remove Option Group"
                                        class="mt-5 flex-shrink-0 text-red-500 hover:text-red-700 p-1 hover:bg-red-100 rounded-full transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        <span class="sr-only">Remove</span>
                                    </button>
                                </div>
                            </template>

                            <!-- Add Option Button -->
                            <button type="button" @click="addOptionGroup()"
                                class="w-full inline-flex justify-center items-center px-3 py-1.5 border border-dashed border-gray-400 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add Option Group
                            </button>

                            <p x-show="productOptions.length === 0" class="text-center text-xs text-gray-500 py-2">No options defined yet. Click 'Add Option Group' to start.</p>
                        </div>
                    </div>

                </div>

                <!-- Footer with Buttons -->
                <div class="bg-gray-50/70 px-4 py-3 sm:px-6 flex justify-between items-center  md:justify-end md:items-end gap-2 border-t border-gray-200">
                    <button type="button" @click="closeAddModal()"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                    <button type="submit"
                        :disabled="isLoading"
                        class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition ease-in-out duration-150 min-w-[130px]"
                        :class="{ 'bg-blue-600 hover:bg-blue-700': !isLoading, 'bg-blue-400 cursor-not-allowed': isLoading }">
                        <!-- Content Wrapper -->
                        <span class="inline-flex items-center">
                            <template x-if="isLoading">
                                <!-- Loading State: Text Only -->
                                <span>Saving...</span>
                            </template>
                            <template x-if="!isLoading">
                                <!-- Normal State: Icon + Text -->
                                <span class="inline-flex items-center">
                                    Save Product
                                </span>
                            </template>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>