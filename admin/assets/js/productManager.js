document.addEventListener('alpine:init', () => {
    // Get initial data passed from PHP
    const initialDataElement = document.getElementById('product-manager-data');
    const initialData = initialDataElement ? JSON.parse(initialDataElement.textContent) : { products: [], categories: [], currencySymbol: '$' };

    Alpine.data('productManager', () => ({
        // State
        viewMode: Alpine.$persist('grid').as('product_view_mode'),
        searchTerm: '',
        minPrice: null,
        maxPrice: null,
        allProducts: initialData.products || [],
        isModalOpen: false,
        viewingProduct: null,
        isLoading: false, // Start as false, data is loaded initially
        isAddModalOpen: false, // State for Add Product modal
        // State for View Modal details
        viewingProductOptions: [],
        isLoadingDetails: false,
        // State for Edit Modal
        isEditModalOpen: false,
        isLoadingEditDetails: false,
        editingProduct: null, // Will hold product data for editing
        isUpdating: false, // Separate loading state for update operation
        newEditImageFile: null, // To hold the new image file if selected
        categories: initialData.categories || [], // Pass categories to Alpine
        productName: '',
        productSlug: '',
        newProductImage: null,
        imageUrl: '../assets/images/placeholder.png', // For image preview
        // New state for toggles and options
        isFeatured: false, // For the featured toggle
        isActive: true, // Default to active for new products
        allowBackorder: false, // Default to no backorder for new products
        optionsEnabled: false, // For the options toggle
        productOptions: [], // Array to hold option groups like [{ name: 'Size', values: 'S, M, L' }, ...]
        // New state for discounts
        discountPercentageEnabled: false, // Toggle for discount percentage input
        discountPercentage: null, // Optional discount percentage value
        // State for other form fields
        productDescription: '',
        productPrice: null,
        productStock: 0,
        categoryId: '', // Use empty string for default "Uncategorized"
        subcategoryId: '', // Add subcategory state
        selectedCategoryId: '', // Added for category filtering
        selectedSubcategoryId: '', // Add subcategory state
        selectedStockStatus: '', // Added for stock status filtering
        selectedActiveStatus: '', // Added for active status filtering
        productToDeleteId: null,
        productToDeleteName: '',
        isRefreshing: false, // State for refresh button
        isFilterDropdownOpen: false, // State for filter dropdown panel
        // State for the new Manage Account Modal
        isManageAccountModalOpen: false,

        // Getters
        getSubcategoriesForCategory(categoryId) {
            if (!categoryId || categoryId === '') return [];
            const category = this.categories.find(c => c.id === parseInt(categoryId));
            return category ? (category.subcategories || []) : [];
        },
        get filteredProducts() {
            let filtered = [...this.allProducts];

            // Search Term Filter
            if (this.searchTerm.trim() !== '') {
                const lowerSearch = this.searchTerm.toLowerCase();
                filtered = filtered.filter(p =>
                    (p.name && p.name.toLowerCase().includes(lowerSearch)) ||
                    (p.category_name && p.category_name.toLowerCase().includes(lowerSearch)) ||
                    (p.id.toString().includes(lowerSearch))
                );
            }

            // Price Filter
            const min = parseFloat(this.minPrice);
            const max = parseFloat(this.maxPrice);

            if (!isNaN(min)) {
                filtered = filtered.filter(p => p.price >= min);
            }
            if (!isNaN(max) && max > 0) {
                filtered = filtered.filter(p => p.price <= max);
            }

            // Category and Subcategory Filter
            if (this.selectedCategoryId && this.selectedCategoryId !== '') {
                if (this.selectedCategoryId === 'uncategorized') {
                    filtered = filtered.filter(p => !p.category_id || p.category_id === '' || p.category_id === 0);
                } else {
                    if (this.selectedSubcategoryId && this.selectedSubcategoryId !== '') {
                        // If subcategory is selected, filter by subcategory
                        filtered = filtered.filter(p => Number(p.category_id) === Number(this.selectedSubcategoryId));
                    } else {
                        // If only category is selected, show all products in that category and its subcategories
                        const category = this.categories.find(c => c.id === Number(this.selectedCategoryId));
                        if (category) {
                            const subcategoryIds = category.subcategories.map(sub => sub.id);
                            filtered = filtered.filter(p => 
                                Number(p.category_id) === Number(this.selectedCategoryId) || 
                                subcategoryIds.includes(Number(p.category_id))
                            );
                        }
                    }
                }
            }

            // Stock Filter
            if (this.selectedStockStatus && this.selectedStockStatus !== '') {
                if (this.selectedStockStatus === 'in_stock') {
                    filtered = filtered.filter(p => p.stock > 0);
                } else if (this.selectedStockStatus === 'out_of_stock') {
                    filtered = filtered.filter(p => !p.stock || p.stock <= 0);
                }
            }

            // Active Status Filter
            if (this.selectedActiveStatus && this.selectedActiveStatus !== '') {
                if (this.selectedActiveStatus === 'active') {
                    filtered = filtered.filter(p => p.is_active);
                } else if (this.selectedActiveStatus === 'inactive') {
                    filtered = filtered.filter(p => !p.is_active);
                }
            }

            // Add cache-busting parameter to all filtered product images
            filtered = filtered.map(product => {
                if (product.image) {
                    // Clone the product to avoid modifying the original
                    const newProduct = {...product};
                    // Add timestamp parameter if not already present
                    if (newProduct.image.indexOf('?') === -1) {
                        newProduct.image = newProduct.image + '?v=' + new Date().getTime();
                    } else if (newProduct.image.indexOf('v=') === -1) {
                        newProduct.image = newProduct.image + '&v=' + new Date().getTime();
                    }
                    return newProduct;
                }
                return product;
            });

            return filtered;
        },

        // Methods
        formatCurrency(amount) {
            const symbol = initialData.currencySymbol || '$'; // Use symbol from initial data
            try {
                // Ensure the symbol is properly decoded if it's a Unicode escape sequence
                const decodedSymbol = symbol.startsWith('\\u') ? JSON.parse('"' + symbol + '"') : symbol;
                return decodedSymbol + parseFloat(amount).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            } catch (e) {
                return symbol + '0.00'; // Fallback
            }
        },
        confirmDelete(productId) {
            if (!productId) return;
            const productName = this.allProducts.find(p => p.id === productId)?.name || 'this product'; // Find name for confirmation message

            if (confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                fetch('utils/delete_product.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `product_id=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            toast.success(data.message || 'Product deleted successfully!');
                            this.allProducts = this.allProducts.filter(p => p.id !== productId);
                        } else {
                            toast.error(data.message || 'Failed to delete product.');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting product:', error);
                        toast.error('An error occurred during deletion.');
                    });
            }
        },
        copyLink(productId) {
            if (!productId) return;
            const url = `${window.location.origin}/product.php?id=${productId}`;
            navigator.clipboard.writeText(url).then(() => {
                console.log('Product link copied:', url);
                toast.info('Product link copied to clipboard!'); // Added toast feedback
            }).catch(err => {
                console.error('Failed to copy product link:', err);
                toast.error('Failed to copy link.');
            });
        },
        openModal(product) {
            this.viewingProduct = product;
            
            // Add cache-busting to image URL for view modal
            if (this.viewingProduct && this.viewingProduct.image) {
                const timestamp = new Date().getTime();
                if (this.viewingProduct.image.indexOf('?') === -1) {
                    this.viewingProduct.image += `?v=${timestamp}`;
                } else {
                    this.viewingProduct.image += `&v=${timestamp}`;
                }
            }
            
            this.viewingProductOptions = []; // Clear previous options
            this.isLoadingDetails = true; // Show loading indicator
            this.isModalOpen = true;

            fetch(`utils/get_product_details.php?id=${product.id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.options) {
                        this.viewingProductOptions = data.options;
                    } else {
                        console.error('Failed to load product options:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching product details:', error);
                })
                .finally(() => {
                    this.isLoadingDetails = false;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                });
        },
        openAddModal() {
            this.isLoading = false;
            this.isAddModalOpen = true;
            // Reset form fields via state
            this.productName = '';
            this.productSlug = '';
            this.newProductImage = null;
            this.imageUrl = '../assets/images/placeholder.png';
            this.isFeatured = false;
            this.isActive = true;
            this.allowBackorder = false;
            this.optionsEnabled = false;
            this.productOptions = [];
            this.discountPercentageEnabled = false;
            this.discountPercentage = null;
            this.productDescription = '';
            this.productPrice = null;
            this.productStock = 0;
            this.categoryId = '';
            this.subcategoryId = '';

            const fileInput = document.getElementById('product_image');
            if (fileInput) fileInput.value = null;

            const form = document.getElementById("addProductForm");
            if (form) form.reset();
        },
        closeAddModal() {
            this.isAddModalOpen = false;
            // Optionally reset feedback when closing
            // this.addProductFeedback = { message: '', type: '' }; 
        },
        handleProductAdd() {
            this.isLoading = true;
            // Reset feedback before submit
            // this.addProductFeedback = { message: '', type: '' }; 

            const formData = new FormData();
            formData.append('product_name', this.productName);
            formData.append('product_slug', this.productSlug);
            formData.append('product_description', this.productDescription);
            formData.append('product_price', this.productPrice);
            formData.append('product_stock', this.productStock);
            formData.append('category_id', this.subcategoryId || this.categoryId || ''); // Use subcategory if selected, otherwise use category
            if (this.newProductImage) {
                formData.append('product_image', this.newProductImage, this.newProductImage.name);
            }
            if (this.isFeatured) formData.append('featured', '1');
            if (this.isActive) formData.append('is_active', '1');
            if (this.allowBackorder) formData.append('backorder', '1');

            if (this.optionsEnabled && this.productOptions.length > 0) {
                const validOptions = this.productOptions.filter(opt => opt.name.trim() !== '' && opt.values.trim() !== '');
                if (validOptions.length > 0) {
                    formData.append('product_options', JSON.stringify(validOptions));
                }
            }

            if (this.discountPercentageEnabled && this.discountPercentage !== null && this.discountPercentage > 0 && this.discountPercentage <= 100) {
                formData.append('discount_percentage', this.discountPercentage);
            }

            fetch('utils/add_product.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toast.success(data.message || 'Product added successfully!');
                        if (data.product) {
                            this.allProducts.unshift(data.product);
                        }
                        // Reset happens in openAddModal or closeAddModal depending on flow
                        // Optionally close modal after success
                        setTimeout(() => this.closeAddModal(), 1000);
                    } else {
                        toast.error(data.message || 'Failed to add product.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toast.error('An unexpected error occurred.');
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },
        // Removed initComponent - fetchProducts not needed as data is passed initially
        // $watch for slug generation moved to init()
        generateSlug(text) {
            if (!text) return '';
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^\w\-]+/g, '')
                .replace(/\-\-+/g, '-')
                .replace(/^-+/, '')
                .replace(/-+$/, '');
        },
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    toast.error('Image size exceeds 2MB limit.');
                    event.target.value = null;
                    return;
                }
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    toast.error('Invalid image file type.');
                    event.target.value = null;
                    return;
                }
                this.newProductImage = file;
                this.imageUrl = URL.createObjectURL(file);
            } else {
                this.newProductImage = null;
                this.imageUrl = '../assets/images/placeholder.png';
            }
        },
        // Removed fetchCategories - assuming loaded initially
        // Methods for managing product options
        addOptionGroup() {
            if (this.editingProduct) {
                this.editingProduct.productOptions.push({ name: '', values: '' });
            } else {
                 this.productOptions.push({ name: '', values: '' });
            }
        },
        removeOptionGroup(index) {
             if (this.editingProduct) {
                 this.editingProduct.productOptions.splice(index, 1);
             } else {
                this.productOptions.splice(index, 1);
             }
        },
        // --- Edit Modal Functions ---
        openEditModal(productId) {
            if (!productId) return;
            this.isLoadingEditDetails = true;
            this.editingProduct = null; 
            this.newEditImageFile = null;
            this.isEditModalOpen = true;
            // Assuming categories already loaded

            fetch(`utils/get_product_details.php?id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.product) {
                        const product = data.product;
                        
                        // Add cache-busting parameter to image URL
                        let imageUrl = product.image || '../assets/images/placeholder.png';
                        if (imageUrl.indexOf('?') === -1) {
                            imageUrl = imageUrl + '?v=' + new Date().getTime();
                        } else {
                            imageUrl = imageUrl + '&v=' + new Date().getTime();
                        }
                        
                        this.editingProduct = {
                            id: product.id,
                            name: product.name,
                            slug: product.slug || '',
                            description: product.description || '',
                            price: parseFloat(product.price) || 0,
                            stock: parseInt(product.stock) || 0,
                            categoryId: product.category_id || '',
                            isFeatured: Boolean(product.featured),
                            is_active: Boolean(product.is_active),
                            imageUrl: imageUrl,
                            productOptions: data.options ? data.options.map(opt => ({ ...opt, values: Array.isArray(opt.values) ? opt.values.join(', ') : opt.values })) : [],
                            discountPercentageEnabled: product.discount_percentage !== null,
                            discountPercentage: product.discount_percentage,
                            backorder: Boolean(product.backorder),
                            optionsEnabled: data.options && data.options.length > 0,
                        };
                    } else {
                        console.error('Failed to load product details:', data.message);
                        toast.error('Could not load product details.');
                        this.isEditModalOpen = false;
                    }
                })
                .catch(error => {
                    console.error('Error fetching product details:', error);
                    toast.error('Error fetching product details.');
                    this.isEditModalOpen = false;
                })
                .finally(() => {
                    this.isLoadingEditDetails = false;
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    });
                });
        },
        closeEditModal() {
            this.isEditModalOpen = false;
            this.editingProduct = null;
            this.newEditImageFile = null;
            // Reset feedback? 
            // this.editProductFeedback = { message: '', type: '' };
        },
        handleFileEditSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    toast.error('Image size exceeds 2MB limit.');
                    event.target.value = null;
                    return;
                }
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    toast.error('Invalid image file type.');
                    event.target.value = null;
                    return;
                }
                this.newEditImageFile = file;
                if (this.editingProduct) {
                    this.editingProduct.imageUrl = URL.createObjectURL(file);
                }
            } else {
                this.newEditImageFile = null;
                // Don't reset to placeholder - keep original image if selection cancelled
            }
        },
        handleProductUpdate() {
            if (!this.editingProduct) return;
            this.isUpdating = true;
            // Reset feedback
            // this.editProductFeedback = { message: '', type: '' };

            const formData = new FormData();
            formData.append('product_id', this.editingProduct.id);
            formData.append('product_name', this.editingProduct.name);
            // Auto-generate slug on backend or send if editable
            // formData.append('product_slug', this.editingProduct.slug);
            formData.append('product_description', this.editingProduct.description || '');
            formData.append('product_price', this.editingProduct.price || 0);
            formData.append('product_stock', this.editingProduct.stock || 0);
            formData.append('category_id', this.editingProduct.subcategoryId || this.editingProduct.categoryId || ''); // Use subcategory if selected, otherwise use category
            formData.append('featured', this.editingProduct.isFeatured ? '1' : '0');
            formData.append('is_active', this.editingProduct.is_active ? '1' : '0');
            formData.append('backorder', this.editingProduct.backorder ? '1' : '0');
            
            // Discount handling
            if (this.editingProduct.discountPercentageEnabled && this.editingProduct.discountPercentage !== null && 
                this.editingProduct.discountPercentage > 0 && this.editingProduct.discountPercentage <= 100) {
                formData.append('discount_percentage', this.editingProduct.discountPercentage);
            } else if (this.editingProduct.discountPercentageEnabled === false) {
                formData.append('remove_discount', '1'); // Signal to remove discount
            }
            
            // Options handling - send as JSON string
            if (this.editingProduct.optionsEnabled && this.editingProduct.productOptions.length > 0) {
                const validOptions = this.editingProduct.productOptions.filter(opt => 
                    opt.name && opt.name.trim() !== '' && opt.values && opt.values.trim() !== '');
                
                if (validOptions.length > 0) {
                    formData.append('product_options', JSON.stringify(validOptions));
                }
            } else if (this.editingProduct.optionsEnabled === false) {
                formData.append('remove_options', '1'); // Signal to remove all options
            }
            
            // Image upload - only if a new file was selected
            if (this.newEditImageFile) {
                formData.append('product_image', this.newEditImageFile, this.newEditImageFile.name);
            }
            
            fetch('utils/update_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toast.success(data.message || 'Product updated successfully!');
                    // Update the product in the allProducts array
                    if (data.product) {
                        // Add a cache-busting parameter to the image URL
                        if (data.product.image) {
                            const timestamp = new Date().getTime();
                            if (data.product.image.indexOf('?') === -1) {
                                data.product.image += `?v=${timestamp}`;
                            } else {
                                data.product.image += `&v=${timestamp}`;
                            }
                        }
                        
                        // Find and update the product in the array
                        const index = this.allProducts.findIndex(p => p.id === data.product.id);
                        if (index !== -1) {
                            this.allProducts[index] = data.product;
                        }
                    }
                    this.closeEditModal();
                } else {
                    toast.error(data.message || 'Failed to update product.');
                }
            })
            .catch(error => {
                console.error('Error updating product:', error);
                toast.error('An error occurred while updating.');
            })
            .finally(() => {
                this.isUpdating = false;
            });
        },
        // --- End Edit Modal Functions ---

        // --- Refresh Function ---
        refreshProducts() {
            console.log('[Refresh] Starting product data refresh...');
            this.isRefreshing = true;
            fetch('utils/get_all_products.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Ensure boolean conversion for status flags
                        this.allProducts = (data.products || []).map(p => ({
                            ...p,
                            is_active: Boolean(p.is_active),
                            featured: Boolean(p.featured),
                            backorder: Boolean(p.backorder)
                        }));
                        console.log('[Refresh] Data refreshed successfully.', this.allProducts.length, 'products loaded.');
                        toast.success('Product data refreshed!');
                         this.$nextTick(() => { // Ensure icons render after data loads/refreshes
                            if (typeof lucide !== 'undefined') {
                                lucide.createIcons();
                            }
                        });
                    } else {
                        console.error('[Refresh] Failed to refresh products:', data.message);
                        toast.error(data.message || 'Failed to refresh product data.');
                    }
                })
                .catch(error => {
                    console.error('[Refresh] Error fetching product data:', error);
                    toast.error('An error occurred while refreshing data.');
                })
                .finally(() => {
                    this.isRefreshing = false;
                });
        },
        // --- End Refresh Function ---

        // Function to open the Manage Account modal
        openManageAccountModal() {
            console.log('Opening Manage Account Modal');
            this.isManageAccountModalOpen = true;
            // No need to fetch data here usually, 
            // modal should have current username from PHP session
            this.$nextTick(() => {
                 lucide.createIcons(); // Ensure icons in the modal are rendered
                 // Optional: focus the input field
                 const input = document.getElementById('manage-account-username');
                 if (input) input.focus();
             });
        },

        // Lifecycle hook if needed (e.g., initial data fetch)
        init() {
            this.$watch('productName', (newName) => {
                if (this.isAddModalOpen) { // Only auto-slug for add modal
                    this.productSlug = this.generateSlug(newName);
                }
             });
            // Initial data load is handled by PHP rendering
            // this.isLoading = false; // Set loading false after initial setup
             console.log('Product Manager Initialized');
             console.log('Initial allProducts:', this.allProducts);
        },

        handleCategoryChange() {
            // Reset subcategory when category changes
            this.subcategoryId = '';
            if (this.editingProduct) {
                this.editingProduct.subcategoryId = '';
            }
        }
    }));
});

// Global function to open the change password modal
function openChangePasswordModal() {
    const modal = document.getElementById('changePasswordModal'); // Assuming this ID exists on the modal
    if (modal) {
        // You might need to trigger Alpine state if the modal uses x-data internally,
        // or simply manipulate classes/styles if it doesn't.
        // Simple example: remove a 'hidden' class
        modal.classList.remove('hidden'); 
        // If it uses Alpine x-data="{ isOpen: false }", you might need:
        // const alpineComponent = Alpine.$data(modal); // Or find the component root
        // if (alpineComponent) alpineComponent.isOpen = true;
    } else {
        console.error('Change Password Modal element not found!');
    }
}

// Note: Initial lucide.createIcons() call might still be needed 
// in the main HTML after Alpine is ready, or within init().
// Let's keep the one in products.php for now. 