console.log("category-manager.js: Script loaded"); // DEBUG: File loaded

// Placeholder for Category Manager Alpine.js component
document.addEventListener('alpine:init', () => {
    console.log("category-manager.js: alpine:init event fired"); // DEBUG: Alpine ready
    
    Alpine.data('categoryManager', () => {
        console.log("category-manager.js: Alpine.data('categoryManager') called"); // DEBUG: Component registration attempted
        
        return { // Return the component object
            isModalOpen: false,
            modalMode: 'edit', // 'addParent', 'addSubcategory', 'edit'
            isLoading: false, // Explicitly initialize loading state
            currentCategory: {
                id: null,
                name: '',
                // slug: '', // Removed - Handled by backend
                parent_id: null,
                is_featured: 0, // Use boolean/int here, convert on send
                // image: null, // REMOVED image property
                description: '' 
            },
            categories: [], // Main array holding ALL categories
            
            // Filter state properties
            parentSearchTerm: '',
            parentFeaturedFilter: 'all', // 'all', 'yes', 'no'
            subSearchTerm: '',
            subParentFilter: 'all', // 'all' or parent_id
            subFeaturedFilter: 'all', // 'all', 'yes', 'no'
            // imageUrl: null, // REMOVED image preview URL state
            // selectedImageFile: null, // REMOVED selected file state

            init() {
                console.log("category-manager.js: categoryManager component init() started"); // DEBUG: Component init
                const dataElement = document.getElementById('category-manager-data');
                if (dataElement) {
                    try {
                        const initialData = JSON.parse(dataElement.textContent);
                        this.categories = initialData.categories || [];
                        console.log("Initial categories loaded:", this.categories.length);
                    } catch (e) {
                        console.error('Error parsing category data:', e);
                        this.categories = [];
                    }
                } else {
                    console.warn('Category data script tag not found, fetching...');
                    this.fetchCategories(); // Fetch if initial data is missing
                }
                
                // Initial icon render needs to happen after Alpine initializes and renders the table
                 this.$nextTick(() => {
                      if (typeof lucide !== 'undefined') {
                           console.log('Lucide: Refreshing icons after init/nextTick...'); // DEBUG
                           lucide.createIcons(); 
                      } else {
                           console.warn('Lucide not found during init/nextTick'); // DEBUG
                      }
                 });
                 
                // Watch filter properties to re-render icons after Alpine updates the DOM
                const refreshIconsOnFilterChange = () => {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            console.log('Lucide: Refreshing icons after filter change/nextTick...'); // DEBUG
                            lucide.createIcons(); 
                        } else {
                             console.warn('Lucide not found during filter change/nextTick'); // DEBUG
                        }
                    });
                };
                
                this.$watch('parentSearchTerm', refreshIconsOnFilterChange);
                this.$watch('parentFeaturedFilter', refreshIconsOnFilterChange);
                this.$watch('subSearchTerm', refreshIconsOnFilterChange);
                this.$watch('subParentFilter', refreshIconsOnFilterChange);
                this.$watch('subFeaturedFilter', refreshIconsOnFilterChange);
                 
                console.log('Category Manager initialized.');
            },

            // Computed property for modal title
            get modalTitle() {
                if (this.modalMode === 'addParent') return 'Add New Parent Category';
                if (this.modalMode === 'addSubcategory') return 'Add New Subcategory';
                if (this.modalMode === 'edit') return 'Edit Category';
                return 'Manage Category'; // Default fallback
            },

            resetForm() {
                this.currentCategory = { 
                    id: null, name: '', 
                    // slug: '', // Removed
                    parent_id: null, is_featured: 0, /* image: null, */ description: '' // Removed image
                }; 
                this.errors = {};
                // REMOVED Image preview/file reset
                // this.imageUrl = null; 
                // this.selectedImageFile = null; 
                // const fileInput = document.getElementById('category_image');
                // if (fileInput) {
                //     fileInput.value = ''; 
                // }
            },

            openAddParentModal() {
                this.resetForm();
                // Removed this.imageUrl = null;
                this.modalMode = 'addParent';
                this.currentCategory.parent_id = null; 
                this.isModalOpen = true;
            },

            openAddSubcategoryModal(parentId) {
                this.resetForm();
                // Removed this.imageUrl = null;
                this.modalMode = 'addSub';
                this.currentCategory.parent_id = parentId;
                this.isModalOpen = true;
            },

            openEditModal(category) {
                this.resetForm();
                this.modalTitle = `Edit Category: ${category.name}`;
                this.modalMode = 'edit';
                // Make a deep copy & ensure all needed fields exist
                this.currentCategory = JSON.parse(JSON.stringify({
                    id: category.id,
                    name: category.name || '',
                    // slug: category.slug || '', // Removed
                    parent_id: category.parent_id || null,
                    is_featured: category.featured || 0, // Ensure boolean/int 0/1
                    // image: category.image || null, // REMOVED
                    description: category.description || ''
                }));
                
                // REMOVED Image preview logic
                // this.imageUrl = null;
                // this.selectedImageFile = null;
                this.isModalOpen = true;
            },

            closeModal() {
                this.isModalOpen = false;
                // Reset mode after closing animation (optional)
                // setTimeout(() => { this.modalMode = 'edit'; }, 300);
            },

            // --- REMOVED Image Handling Functions ---
            // handleImagePreview(event) { ... }
            // removeImagePreview() { ... }
            // --- End REMOVED Image Handling ---

            // --- Removed Slug Generation ---
            
            // --- End Slug Generation ---

            async saveCategory() {
                // Client-side validation for parent selection when adding/editing a subcategory
                const requiresParent = (this.modalMode === 'edit' && this.categories.find(c => c.id === this.currentCategory.id)?.parent_id !== null) || this.modalMode === 'addSub';
                if (requiresParent && !this.currentCategory.parent_id) {
                    if (typeof toast !== 'undefined') { 
                        toast.error('Please select a Parent Category for this subcategory.'); 
                    } else {
                        alert('Please select a Parent Category for this subcategory.');
                    }
                    return; // Stop submission
                }
                
                this.isLoading = true;
                this.errors = {};
                const isEditMode = this.modalMode === 'edit';
                const url = isEditMode ? './utils/update_category.php' : './utils/add_category.php';

                const formData = new FormData();

                // Append all scalar fields from currentCategory, handling special cases
                for (const key in this.currentCategory) {
                    // Skip image path and slug
                    if (key === 'image' || key === 'slug') continue; // Belt and braces
                    
                    // Handle featured status explicitly: send as '1' or '0' string
                    if (key === 'is_featured') {
                        formData.append('featured', this.currentCategory.is_featured ? '1' : '0');
                        continue; // Don't append the original 'is_featured' key
                    }
                    
                    // Ensure null parent_id is sent correctly as empty string
                    const value = this.currentCategory[key] === null ? '' : this.currentCategory[key];
                    formData.append(key, value);
                }
                
                // --- REMOVED Image Handling for Form Data ---
                // No need to append category_image or remove_image flag
                // --- End REMOVED Image Handling for Form Data ---

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        body: formData // Send as FormData
                    });

                    const result = await response.json();
                    console.log('API Response:', result);
                    if (result.success) {
                        if (typeof toast !== 'undefined') { toast.success(result.message || 'Operation successful!'); }
                        else { alert(result.message || 'Operation successful!'); }
                        
                        // Refresh data instead of reloading page
                        this.fetchCategories(); 

                        this.closeModal();
                    } else {
                        if (typeof toast !== 'undefined') { toast.error(result.message || 'An error occurred.'); }
                        else { alert(result.message || 'An error occurred.'); }
                        console.error('API Error:', result.message);
                        // Don't close modal on error
                    }
                } catch (error) {
                    console.error('Save Category Error:', error);
                    toast.error(`An error occurred: ${error.message}`);
                } finally {
                    this.isLoading = false;
                }
            },

            confirmDelete(categoryId) {
                console.log('Requesting delete confirmation for category ID:', categoryId);
                if (confirm('Are you sure you want to delete this category? This may affect associated products and cannot be undone.')) {
                    this.deleteCategory(categoryId);
                }
            },

            deleteCategory(categoryId) {
                console.log('Attempting to delete category ID:', categoryId);
                const apiUrl = 'utils/delete_category.php';
                
                fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ id: categoryId })
                })
                .then(response => response.json())
                .then(result => {
                     console.log('API Response:', result);
                     if (result.success) {
                        if (typeof toast !== 'undefined') { toast.success(result.message || 'Category deleted!'); }
                        else { alert(result.message || 'Category deleted!'); }
                        
                        // Refresh data instead of reloading page
                         this.fetchCategories(); 
                        
                    } else {
                         if (typeof toast !== 'undefined') { toast.error(result.message || 'Failed to delete category.'); }
                         else { alert(result.message || 'Failed to delete category.'); }
                         console.error('API Error:', result.message);
                    }
                })
                 .catch(error => {
                    console.error('Fetch Error:', error);
                     if (typeof toast !== 'undefined') { toast.error('An unexpected network error occurred.'); }
                     else { alert('An unexpected network error occurred.'); }
                });
            },

            // --- Computed Properties for Tables and Filters ---
            get parentCategoriesForFilter() {
                 // Used to populate the parent filter dropdown in the subcategory tab
                return this.categories.filter(c => c.parent_id === null).sort((a, b) => a.name.localeCompare(b.name));
            },

            get parentCategories() {
                const searchTerm = this.parentSearchTerm.toLowerCase();
                return this.categories.filter(c => {
                    // Basic parent check
                    if (c.parent_id !== null) return false;
                    
                    // Featured filter
                    if (this.parentFeaturedFilter === 'yes' && !c.featured) return false;
                    if (this.parentFeaturedFilter === 'no' && c.featured) return false;
                    
                    // Search filter
                    if (searchTerm && !c.name.toLowerCase().includes(searchTerm)) return false;
                    
                    return true; // Passed all filters
                }).sort((a, b) => a.name.localeCompare(b.name));
            },
            
            get subCategories() {
                const searchTerm = this.subSearchTerm.toLowerCase();
                const parentFilterId = this.subParentFilter === 'all' ? null : parseInt(this.subParentFilter);
                
                const filtered = this.categories.filter(c => {
                     // Basic subcategory check
                    if (c.parent_id === null) return false;
                    
                     // Featured filter
                    if (this.subFeaturedFilter === 'yes' && !c.featured) return false;
                    if (this.subFeaturedFilter === 'no' && c.featured) return false;
                    
                    // Parent filter
                    if (parentFilterId !== null && c.parent_id !== parentFilterId) return false;
                    
                    // Search filter
                    if (searchTerm && !c.name.toLowerCase().includes(searchTerm)) return false;
                    
                    return true; // Passed all filters
                });

                // Sort by parent name first, then subcategory name
                return filtered.sort((a, b) => {
                    const parentA = this.getParentName(a.parent_id);
                    const parentB = this.getParentName(b.parent_id);
                    if (parentA !== parentB) {
                        return parentA.localeCompare(parentB);
                    }
                    return a.name.localeCompare(b.name);
                });
            },

            // Helper to get parent name for display in subcategory table
            getParentName(parentId) {
                if (!parentId) return '-';
                const parent = this.categories.find(c => c.id === parentId);
                return parent ? parent.name : 'Unknown';
            },
            
            // --- Methods ---
             async fetchCategories() {
                console.log('Fetching categories...');
                try {
                    const response = await fetch('utils/get_categories.php');
                    const result = await response.json();
                    if (result.success) {
                        this.categories = result.categories;
                        console.log('Categories refreshed:', this.categories.length);
                         // Refresh icons after data update using nextTick
                         this.$nextTick(() => {
                             if (typeof lucide !== 'undefined') {
                                 console.log('Lucide: Refreshing icons after fetch/nextTick...'); // DEBUG
                                 lucide.createIcons(); 
                            } else {
                                 console.warn('Lucide not found during fetch/nextTick'); // DEBUG
                             }
                        });
                    } else {
                        console.error('Failed to fetch categories:', result.message);
                        if (typeof toast !== 'undefined') { toast.error(result.message || 'Could not load categories.'); }
                    }
                } catch (error) {
                    console.error('Error fetching categories:', error);
                    if (typeof toast !== 'undefined') { toast.error('Network error loading categories.'); }
                }
            },
            
            // --- Filter Reset Methods ---
            resetParentFilters() {
                console.log('Resetting parent filters...');
                this.parentSearchTerm = '';
                this.parentFeaturedFilter = 'all';
                // Fetch fresh data after resetting filters
                this.fetchCategories(); 
                if (typeof toast !== 'undefined' && typeof toast.success === 'function') {
                    // Using success level as info might not exist
                    toast.success('Parent category filters reset and data refreshed.'); 
                } else {
                    console.warn('Toast function not available for filter reset message.');
                }
            },
            
            resetSubFilters() {
                console.log('Resetting subcategory filters...');
                this.subSearchTerm = '';
                this.subParentFilter = 'all';
                this.subFeaturedFilter = 'all';
                // Fetch fresh data after resetting filters
                this.fetchCategories(); 
                 if (typeof toast !== 'undefined' && typeof toast.success === 'function') {
                     // Using success level as info might not exist
                    toast.success('Subcategory filters reset and data refreshed.'); 
                } else {
                    console.warn('Toast function not available for filter reset message.');
                }
            }
        }; // End of returned component object
    });
}); 