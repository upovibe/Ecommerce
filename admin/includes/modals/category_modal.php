<!-- Category Add/Edit Modal -->
<div x-show="isModalOpen" x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="category-modal-title" role="dialog" aria-modal="true"
     @keydown.escape.window="closeModal()">

    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="isModalOpen"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" 
            @click="closeModal()" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div x-show="isModalOpen"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full"> 

            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50"> 
                <h3 class="text-lg font-medium text-gray-900" x-text="modalTitle" id="category-modal-title"></h3>
                <button type="button" @click="closeModal()" class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl"> 
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="saveCategory()">
                <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto"> 

                    <!-- Category Name -->
                    <div>
                        <label for="category-name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" id="category-name" x-model="currentCategory.name" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">The main display name for the category.</p>
                    </div>

                    <!-- Category Slug (Removed) -->
                    <!-- 
                    <div>
                        <label for="category-slug" class="block text-sm font-medium text-gray-700">Slug (Optional)</label>
                        <input type="text" id="category-slug" x-model="currentCategory.slug" placeholder="e.g., electronics-accessories"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">URL-friendly version of the name. Leave blank to auto-generate.</p>
                    </div> 
                    -->

                    <!-- Category Description (Always Visible, Optional) -->
                    <div> 
                        <label for="category-description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                        <textarea id="category-description" x-model="currentCategory.description" rows="3"
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        <p class="text-xs text-gray-500 mt-1">A short description for the category, potentially shown on category pages.</p>
                    </div>

                    <!-- Parent Category (Visible for Sub Add/Edit only) -->
                    <div x-show="modalMode === 'addSubcategory' || (modalMode === 'edit' && currentCategory.parent_id !== null)">
                        <label for="category-parent" class="block text-sm font-medium text-gray-700">
                            Parent Category 
                            <span x-show="modalMode === 'addSubcategory'" class="text-red-500">*</span>
                        </label>
                        <select id="category-parent" x-model="currentCategory.parent_id"
                                :required="modalMode === 'addSubcategory'" 
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md shadow-sm appearance-none bg-white hover:border-gray-400 cursor-pointer">
                            <option value="">-- Select Parent --</option>
                            <!-- Populate only with parent categories -->
                            <template x-for="cat in parentCategories.filter(c => c.id !== currentCategory.id)" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                         <p x-show="modalMode === 'addSubcategory'" class="text-xs text-gray-500 mt-1">Required for subcategories. Choose the main category this belongs to.</p>
                         <p x-show="modalMode === 'edit' && currentCategory.parent_id" class="text-xs text-gray-500 mt-1">Change the parent category if needed.</p>
                         <p x-show="modalMode === 'edit' && !currentCategory.parent_id" class="text-xs text-gray-500 mt-1">Assign a parent to make this a subcategory.</p>
                    </div>

                    <!-- Category Image (Visible for Parent Add/Edit only) -->
                    <div x-show="modalMode === 'addParent' || (modalMode === 'edit' && currentCategory.parent_id === null)">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image (Optional)</label>
                        <input type="file" name="category_image" id="category_image" accept="image/*"
                               @change="handleImagePreview($event)"
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent">
                         <!-- Image Preview -->
                         <div class="mt-2" x-show="imageUrl">
                            <img :src="imageUrl" alt="Preview" class="h-20 w-20 object-cover rounded-md border border-gray-200 bg-gray-50">
                            <button type="button" @click="removeImagePreview()" class="mt-1 text-xs text-red-600 hover:text-red-800">Remove</button>
                         </div>
                         <p class="text-xs text-gray-500 mt-1">Upload an image for this parent category.</p>
                    </div>
                    
                    <!-- Featured Status Toggle (Always Visible) -->
                    <div class="flex items-center justify-between"> 
                        <label for="category-featured-toggle" class="text-sm font-medium text-gray-900">Mark as Featured</label>
                        <button 
                            type="button"
                            id="category-featured-toggle"
                            @click="currentCategory.featured = !currentCategory.featured"
                            :class="currentCategory.featured ? 'bg-blue-600' : 'bg-gray-200'"
                            class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            role="switch"
                            :aria-checked="currentCategory.featured.toString()">
                            <span class="sr-only">Use setting</span>
                            <span 
                                aria-hidden="true"
                                :class="currentCategory.featured ? 'translate-x-5' : 'translate-x-0'"
                                class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                        </button>
                    </div>
                     <p class="text-xs text-gray-500 mt-1">Featured categories may be highlighted on the homepage or main navigation.</p>
                
                 </div> <!-- End Scrollable Content Area -->

                <!-- Footer with Buttons -->
                <div class="bg-gray-50/70 px-4 py-3 sm:px-6 flex justify-end space-x-3 border-t border-gray-200"> 
                    <button type="button" @click="closeModal()"
                            class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"> 
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition ease-in-out duration-150">
                        <span x-text="modalMode === 'edit' ? 'Save Changes' : (modalMode === 'addParent' ? 'Add Parent' : 'Add Subcategory')"></span>
                    </button>
                </div>
            </form>
        </div> <!-- End Modal Panel -->
    </div>
</div> 