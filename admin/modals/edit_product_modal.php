<?php
// Edit Product Modal
// Assumes $categories is available from the parent PHP file (products.php)
?>
<div x-show="isEditModalOpen" x-cloak 
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="edit-modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="closeEditModal()"> 
    
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isEditModalOpen" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" 
             @click="closeEditModal()" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
         <div x-show="isEditModalOpen"
               x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" 
               x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" 
               class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                
                 <!-- Header -->
                 <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50">
                     <h3 class="text-xl font-semibold text-gray-900" id="edit-modal-title">Edit Product</h3>
                     <button type="button" @click="closeEditModal()" class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                        <span class="sr-only">Close</span>
                        <i data-lucide="x" class="h-5 w-5"></i>
                    </button>
                 </div>

                <!-- Form Content -->
                <!-- Add a check for editingProduct existence before rendering form -->
                <template x-if="editingProduct">
                    <div class="relative"> <!-- Added relative container for loader positioning -->
                        <!-- Loading Indicator (absolutely positioned overlay) -->
                        <div x-show="isLoadingEditDetails" 
                             x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                             x-transition:leave="transition-opacity ease-linear duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                             class="absolute inset-0 bg-white/70 backdrop-blur-sm flex flex-col items-center justify-center z-30 rounded-b-xl">
                             <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                             <p class="text-sm text-gray-500 mt-2">Loading product details...</p>
                        </div>

                        <!-- Form Content -->
                        <form id="editProductForm" @submit.prevent="handleProductUpdate" enctype="multipart/form-data">
                            <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto">
                                <div>
                                    <label for="edit_product_name" class="block text-sm font-medium text-gray-700 mb-1">Product Name <span class="text-red-500">*</span></label>
                                    <input type="text" name="product_name" id="edit_product_name" required 
                                           x-model="editingProduct.name" 
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                </div>

                                <div>
                                    <label for="edit_product_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea name="product_description" id="edit_product_description" rows="4"
                                              x-model="editingProduct.description"
                                              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"></textarea>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                    <div>
                                        <label for="edit_product_price" class="block text-sm font-medium text-gray-700 mb-1">Price <span class="text-red-500">*</span></label>
                                        <div class="relative mt-1 rounded-lg shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm"><?= htmlspecialchars($currencySymbol) ?></span>
                                            </div>
                                            <input type="number" name="product_price" id="edit_product_price" required step="0.01" min="0"
                                                   x-model.number="editingProduct.price"
                                                   class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="edit_product_stock" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                                        <input type="number" name="product_stock" id="edit_product_stock" min="0" 
                                                x-model.number="editingProduct.stock"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                    </div>
                                    <div>
                                        <label for="edit_category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                        
                                        <!-- Category Input with Dropdown Icon -->
                                        <div class="relative">
                                            <!-- Display Mode -->
                                            <div x-show="!showCategoryDropdown" class="relative">
                                                <div @click="showCategoryDropdown = true" 
                                                     class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-pointer hover:bg-gray-100 transition-colors">
                                                    <span x-text="editingProduct.categoryId ? categories.find(c => Number(c.id) == editingProduct.categoryId)?.name || 'Unknown Category' : 'Uncategorized'"></span>
                                                </div>
                                            </div>
                                            
                                            <!-- Select Mode -->
                                            <div x-show="showCategoryDropdown" x-cloak class="relative">
                                                <select name="category_id" id="edit_category_id"
                                                        x-model="editingProduct.categoryId"
                                                        @change="handleCategoryChange; showCategoryDropdown = false"
                                                        class="block w-full px-3 py-2 pr-10 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                                    <option :value="0">Uncategorized</option>
                                                    <template x-for="category in categories" :key="category.id">
                                                        <option :value="Number(category.id)" x-text="category.name"></option>
                                                    </template>
                                                </select>
                                                <button type="button" @click="showCategoryDropdown = false" 
                                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div x-show="editingProduct.categoryId && editingProduct.categoryId !== 0 && getSubcategoriesForCategory(editingProduct.categoryId).length > 0">
                                        <label for="edit_subcategory_id" class="block text-sm font-medium text-gray-700 mb-1">Subcategory</label>
                                        
                                        <!-- Subcategory Input with Dropdown Icon -->
                                        <div class="relative">
                                            <!-- Display Mode -->
                                            <div x-show="!showSubcategoryDropdown" class="relative">
                                                <div @click="showSubcategoryDropdown = true" 
                                                     class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 cursor-pointer hover:bg-gray-100 transition-colors">
                                                    <span x-text="editingProduct.subcategoryId ? getSubcategoriesForCategory(editingProduct.categoryId).find(s => Number(s.id) == editingProduct.subcategoryId)?.name || 'Unknown Subcategory' : 'Select Subcategory'"></span>
                                                </div>
                                            </div>
                                            
                                            <!-- Select Mode -->
                                            <div x-show="showSubcategoryDropdown" x-cloak class="relative">
                                                <select name="subcategory_id" id="edit_subcategory_id"
                                                        x-model="editingProduct.subcategoryId"
                                                        @change="showSubcategoryDropdown = false"
                                                        class="block w-full px-3 py-2 pr-10 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                                    <option :value="0">Select Subcategory</option>
                                                    <template x-for="subcategory in getSubcategoriesForCategory(editingProduct.categoryId)" :key="subcategory.id">
                                                        <option :value="Number(subcategory.id)" x-text="subcategory.name"></option>
                                                    </template>
                                                </select>
                                                <button type="button" @click="showSubcategoryDropdown = false" 
                                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 transition-colors">
                                                    <i data-lucide="x" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Discount Percentage Section -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Add Discount Percentage</span>
                                        <button type="button" @click="editingProduct.discountPercentageEnabled = !editingProduct.discountPercentageEnabled" 
                                                :class="{ 'bg-blue-600': editingProduct.discountPercentageEnabled, 'bg-gray-200': !editingProduct.discountPercentageEnabled }" 
                                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                role="switch" :aria-checked="editingProduct.discountPercentageEnabled.toString()">
                                            <span class="sr-only">Add Discount Percentage</span>
                                            <span aria-hidden="true" 
                                                  :class="{ 'translate-x-5': editingProduct.discountPercentageEnabled, 'translate-x-0': !editingProduct.discountPercentageEnabled }" 
                                                  class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </button>
                                    </div>
                                    <div x-show="editingProduct.discountPercentageEnabled" x-cloak class="border border-gray-200 rounded-lg p-4 bg-gray-50/50">
                                        <div>
                                            <label for="edit_discount_percentage" class="block text-sm font-medium text-gray-700 mb-1">Discount Percentage (Optional)</label>
                                            <div class="relative mt-1 rounded-lg shadow-sm">
                                                <input type="number" name="discount_percentage" id="edit_discount_percentage" 
                                                       x-model.number="editingProduct.discountPercentage"
                                                       min="0" max="100" step="0.01" 
                                                       placeholder="e.g., 15" 
                                                       :required="editingProduct.discountPercentageEnabled"
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
                                    <label for="edit_product_image" 
                                           class="relative mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200 min-h-[150px]"> 

                                        <!-- Preview -->
                                        <div class="absolute inset-0 flex justify-center items-center p-2"> <!-- Position preview absolutely -->
                                            <img :src="editingProduct.imageUrl || '../assets/images/placeholder.png'" 
                                                 alt="Image Preview" 
                                                 class="max-h-full max-w-full rounded-lg object-contain shadow-sm z-10">
                                        </div>
                                        
                                        <!-- Upload/Overlay Content -->
                                        <div class="relative z-20 space-y-1 text-center bg-gray-50/80 backdrop-blur-sm p-4 rounded-md"> <!-- Overlay content on top -->
                                            <i data-lucide="image" class="mx-auto h-10 w-10 text-gray-400"></i>
                                            <div class="flex text-sm text-gray-600">
                                                <span class="relative bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                    <span>Change file</span>
                                                    <input type="file" name="product_image" id="edit_product_image" accept="image/*"
                                                           @change="handleFileEditSelect($event)" 
                                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                                </span>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 2MB</p>
                                        </div>
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
                                        <button type="button" @click="editingProduct.isFeatured = !editingProduct.isFeatured" 
                                                :class="{ 'bg-blue-600': editingProduct.isFeatured, 'bg-gray-200': !editingProduct.isFeatured }" 
                                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                role="switch" :aria-checked="editingProduct.isFeatured.toString()">
                                            <span class="sr-only">Featured Product</span>
                                            <span aria-hidden="true" 
                                                  :class="{ 'translate-x-5': editingProduct.isFeatured, 'translate-x-0': !editingProduct.isFeatured }" 
                                                  class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </button>
                                    </div>

                                    <!-- Active Status Toggle -->
                                    <div class="flex items-center justify-between py-2 border border-gray-200 rounded-lg px-3 bg-white/50 shadow-sm">
                                        <span class="text-sm font-medium text-gray-700 flex items-center">
                                            <i data-lucide="zap" class="w-4 h-4 mr-2 text-green-500"></i>Active Status
                                        </span>
                                        <button type="button" 
                                                @click="editingProduct.is_active = !editingProduct.is_active; console.log('[Debug] Active toggle clicked. New editingProduct.is_active:', editingProduct.is_active)" 
                                                :class="{ 'bg-blue-600': editingProduct.is_active, 'bg-gray-200': !editingProduct.is_active }" 
                                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                role="switch" :aria-checked="editingProduct.is_active.toString()">
                                            <span class="sr-only">Active Status</span>
                                            <span aria-hidden="true" 
                                                  :class="{ 'translate-x-5': editingProduct.is_active, 'translate-x-0': !editingProduct.is_active }" 
                                                  class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </button>
                                    </div>

                                    <!-- Allow Backorder Toggle -->
                                    <div class="flex items-center justify-between py-2 border border-gray-200 rounded-lg px-3 bg-white/50 shadow-sm">
                                        <span class="text-sm font-medium text-gray-700 flex items-center">
                                            <i data-lucide="history" class="w-4 h-4 mr-2 text-cyan-500"></i>Allow Backorder
                                        </span>
                                        <button type="button" @click="editingProduct.backorder = !editingProduct.backorder" 
                                                :disabled="editingProduct.stock > 0" 
                                                :class="{
                                                    'bg-blue-600': editingProduct.backorder,
                                                    'bg-gray-200': !editingProduct.backorder,
                                                    'opacity-50 cursor-not-allowed': editingProduct.stock > 0
                                                }"
                                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                role="switch" :aria-checked="editingProduct.backorder.toString()">
                                            <span class="sr-only">Allow Backorder</span>
                                            <span aria-hidden="true" 
                                                  :class="{ 'translate-x-5': editingProduct.backorder, 'translate-x-0': !editingProduct.backorder }" 
                                                  class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </button>
                                    </div>
                                </div>

                                <hr class="border-gray-200/70 my-1">

                                <!-- Product Options Section -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Enable Product Options</span>
                                        <button type="button" @click="editingProduct.optionsEnabled = !editingProduct.optionsEnabled" 
                                                :class="{ 'bg-blue-600': editingProduct.optionsEnabled, 'bg-gray-200': !editingProduct.optionsEnabled }" 
                                                class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                role="switch" :aria-checked="editingProduct.optionsEnabled.toString()">
                                            <span class="sr-only">Enable Product Options</span>
                                            <span aria-hidden="true" 
                                                  :class="{ 'translate-x-5': editingProduct.optionsEnabled, 'translate-x-0': !editingProduct.optionsEnabled }" 
                                                  class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                        </button>
                                    </div>
                                    <div x-show="editingProduct.optionsEnabled" x-cloak class="space-y-4 border border-gray-200 rounded-lg p-4 bg-gray-50/50">
                                        <template x-for="(optionGroup, index) in editingProduct.productOptions" :key="index">
                                            <div class="flex items-start space-x-3 bg-white p-3 rounded-md shadow-sm border border-gray-100">
                                                <div class="flex-grow space-y-2">
                                                    <div>
                                                        <label :for="'edit_option_name_' + index" class="block text-xs font-medium text-gray-600 mb-1">Option Name</label>
                                                        <input type="text" :id="'edit_option_name_' + index" x-model="optionGroup.name" placeholder="Size"
                                                               class="block w-full px-2 py-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                                    </div>
                                                    <div x-data="{ currentEditInputValue: '' }">
                                                        <label :for="'edit_option_values_input_' + index" class="block text-xs font-medium text-gray-600 mb-1">Values (type, then comma)</label>
                                                        <div @click="$refs['edit_input_' + index].focus()" class="flex flex-wrap items-center gap-1 w-full px-2 py-1 min-h-[36px] border border-gray-300 rounded-md shadow-sm bg-white cursor-text">
                                                            <template x-for="(value, valueIndex) in optionGroup.values.split(',').map(v => v.trim()).filter(v => v !== '')" :key="value + index + '_badge_edit'">
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 shadow-sm">
                                                                    <span x-text="value"></span>
                                                                    <button type="button" @click.stop="optionGroup.values = optionGroup.values.split(',').map(v => v.trim()).filter(v => v !== '').filter((_, i) => i !== valueIndex).join(', ')" 
                                                                            class="ml-1.5 flex-shrink-0 text-blue-400 hover:text-blue-600 focus:outline-none">
                                                                        <i data-lucide="x" class="w-3 h-3"></i>
                                                                    </button>
                                                                </span>
                                                            </template>
                                                            <input type="text" :id="'edit_option_values_input_' + index" 
                                                                   :ref="'edit_input_' + index"
                                                                   x-model="currentEditInputValue"
                                                                   @keydown.comma.prevent="
                                                                       if (currentEditInputValue.trim() !== '') {
                                                                           const newValue = currentEditInputValue.trim().toLowerCase();
                                                                           let existingValues = optionGroup.values.split(',').map(v => v.trim()).filter(v => v !== '');
                                                                           if (!existingValues.includes(newValue)) { 
                                                                                optionGroup.values = [...existingValues, newValue].join(', ');
                                                                           }
                                                                           currentEditInputValue = '';
                                                                       }
                                                                   "
                                                                   @keydown.backspace="
                                                                       if (currentEditInputValue === '' && optionGroup.values.length > 0) {
                                                                           optionGroup.values = optionGroup.values.split(',').map(v => v.trim()).filter(v => v !== '').slice(0, -1).join(', ');
                                                                       }
                                                                   "
                                                                   placeholder="Type value, then comma..."
                                                                   class="flex-grow p-0 border-none focus:ring-0 text-sm focus:outline-none min-w-[100px]">
                                                        </div>
                                                        <p class="mt-1 text-xs text-gray-500">Type a value, press comma to add it. Backspace on empty input removes last value.</p>
                                                    </div>
                                                </div>
                                                <button type="button" @click="editingProduct.productOptions.splice(index, 1)" title="Remove Option Group"
                                                        class="mt-5 flex-shrink-0 text-red-500 hover:text-red-700 p-1 hover:bg-red-100 rounded-full transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    <span class="sr-only">Remove</span>
                                                </button>
                                            </div>
                                        </template>
                                        <button type="button" @click="editingProduct.productOptions.push({ name: '', values: '' })" 
                                                class="w-full inline-flex justify-center items-center px-3 py-1.5 border border-dashed border-gray-400 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add Option Group
                                        </button>
                                        <p x-show="!editingProduct.productOptions || editingProduct.productOptions.length === 0" class="text-center text-xs text-gray-500 py-2">No options defined yet. Click 'Add Option Group' to start.</p>
                                    </div>
                                </div>
                            </div> <!-- End form fields scrollable area -->

                            <!-- Footer with Buttons -->
                            <div class="bg-gray-50/70 px-4 py-3 sm:px-6 flex justify-between items-center md:justify-end md:items-end gap-2 border-t border-gray-200">
                                <button type="button" @click="closeEditModal()" 
                                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        :disabled="isUpdating" 
                                        class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition ease-in-out duration-150 min-w-[130px]" 
                                        :class="{ 'bg-blue-600 hover:bg-blue-700': !isUpdating, 'bg-blue-400 cursor-not-allowed': isUpdating }">
                                    <!-- Content Wrapper -->
                                    <span class="inline-flex items-center">
                                        <template x-if="isUpdating">
                                            <span>Updating...</span>
                                        </template>
                                        <template x-if="!isUpdating">
                                            <span>Update</span>
                                        </template>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div> <!-- End relative container -->
                </template>
             </div> 
         </div>
    </div>
</div> 