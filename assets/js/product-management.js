/**
 * Product Management Script for products.php
 * Handles fetching, displaying, filtering, and interactions for products.
 * Includes logic for category/subcategory filters, product grid display,
 * product detail modal triggering, add-to-cart functionality,
 * and the integrated search modal.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log("Product Management Script Loaded");

    // Check if required PHP data is available
    if (typeof window.PHP_DATA === 'undefined') {
        console.error('PHP_DATA is not defined. Make sure it is set in products.php before including this script.');
        const container = document.getElementById('product-page-container');
        if (container) {
            container.innerHTML = '<p class="text-center text-red-500">Error: Could not load product page configuration.</p>';
        }
        return; // Stop execution if data is missing
    }

    // --- Access PHP Data --- 
    const allCategories = window.PHP_DATA.allCategories || [];
    const currencySymbol = window.PHP_DATA.currencySymbol || '₦';
    const initialCategoryId = window.PHP_DATA.initialCategoryId;
    const initialSubcategoryId = window.PHP_DATA.initialSubcategoryId;

    // --- Element References ---
    const categoryDropdownContainer = document.getElementById('categoryDropdownContainer');
    const categoryDropdownButton = document.getElementById('categoryDropdownButton');
    const categoryDropdownPanel = document.getElementById('categoryDropdownPanel');
    const categoryDropdownSelected = document.getElementById('categoryDropdownSelected');
    const categoryValueInput = document.getElementById('categoryValue'); // Hidden input
    const categoryOptions = categoryDropdownPanel ? categoryDropdownPanel.querySelectorAll('.category-option') : [];

    const subcategorySelect = document.getElementById('subcategory');
    const productContainer = document.getElementById('productContainer');
    const productsTitle = document.getElementById('productsTitle');

    // Mobile Filter Elements
    const mobileFilterTrigger = document.getElementById('mobileFilterTrigger');
    const filterControlsPanel = document.getElementById('filterControls'); // This is the panel
    const filterContainer = filterControlsPanel ? filterControlsPanel.parentElement : null; // The relative container for the panel

    // Reset Button
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');

    // SVGs (Make sure these are consistent with cart.js if needed there too)
    const cartIconSVG = `<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>`;
    const checkIconSVG = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;

    // --- State Variables ---
    let isFetching = false; // Prevents multiple simultaneous fetches

    // =========================================================================
    // --- Search Modal Logic (Integrated from previous search.js) ---
    // =========================================================================

    // Element references for the search modal
    const searchModal = document.getElementById('searchModal');
    const searchModalOverlay = document.getElementById('searchModalOverlay');
    const searchModalPanel = document.getElementById('searchModalPanel');
    const searchModalButton = document.getElementById('searchModalButton'); 
    const closeSearchModalButton = document.getElementById('closeSearchModalButton');
    const modalSearchInput = document.getElementById('modalSearchInput');

    // Function to open the search modal with animation
    function openSearchModal() {
        if (!searchModal || !searchModalOverlay || !searchModalPanel) return;
        searchModal.classList.remove('hidden');
        requestAnimationFrame(() => { 
            searchModalOverlay.classList.replace('opacity-0', 'opacity-100');
            searchModalPanel.classList.replace('opacity-0', 'opacity-100');
            searchModalPanel.classList.replace('translate-y-4', 'translate-y-0');
            searchModalPanel.classList.replace('sm:scale-95', 'sm:scale-100');
        });
        if (modalSearchInput) {
            setTimeout(() => modalSearchInput.focus(), 50); 
        }
    }

    // Function to close the search modal with animation
    function closeSearchModal() {
         if (!searchModal || !searchModalOverlay || !searchModalPanel || searchModal.classList.contains('hidden')) return;
         searchModalOverlay.classList.replace('opacity-100', 'opacity-0');
         searchModalPanel.classList.replace('opacity-100', 'opacity-0');
         searchModalPanel.classList.replace('translate-y-0', 'translate-y-4');
         searchModalPanel.classList.replace('sm:scale-100', 'sm:scale-95');
         setTimeout(() => {
             searchModal.classList.add('hidden');
         }, 300); 
    }
    // Search Modal Event Listeners
    if (searchModalButton) {
        searchModalButton.addEventListener('click', openSearchModal);
    } else {
        console.warn('Search modal trigger button (id="searchModalButton") not found.');
    }
    if (closeSearchModalButton) {
        closeSearchModalButton.addEventListener('click', closeSearchModal);
    }
    if (searchModalOverlay) {
        searchModalOverlay.addEventListener('click', closeSearchModal);
    }
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && searchModal && !searchModal.classList.contains('hidden')) {
            closeSearchModal();
        }
    });
    // --- End Search Modal Logic ---

    // =========================================================================
    // --- Helper Functions --- 
    // =========================================================================

    /**
     * Updates the subcategory dropdown options based on the currently selected main category.
     * Fetches subcategories from the `allCategories` data provided by PHP.
     * Disables the dropdown if no category is selected or if the selected category has no subcategories.
     */
    function updateSubcategoryOptions() {
        if (!subcategorySelect) return; // Element check
        const selectedCategoryId = categoryValueInput ? categoryValueInput.value : ''; 
        subcategorySelect.innerHTML = '<option value="">All Subcategories</option>'; // Reset

        if (selectedCategoryId) {
            const selectedCategory = allCategories.find(cat => cat.id == selectedCategoryId);
            if (selectedCategory && selectedCategory.subcategories && selectedCategory.subcategories.length > 0) {
                selectedCategory.subcategories.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.id;
                    option.textContent = sub.name;
                    // Restore selection if it belongs to the new parent category
                    if (option.value == subcategorySelect.value) {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });
                subcategorySelect.disabled = false;
            } else {
                subcategorySelect.disabled = true;
            }
        } else {
            subcategorySelect.disabled = true;
        }
        // Restore the "All Subcategories" selection if it was selected before
        // or if the previously selected subcategory is no longer valid
        if (!subcategorySelect.querySelector(`option[value="${subcategorySelect.value}"]`)) {
            subcategorySelect.value = '';
        }
    }

    /**
     * Escapes HTML special characters in a string to prevent XSS.
     * @param {string} str The string to escape.
     * @returns {string} The HTML-escaped string.
     */
    function escapeHTML(str) {
        if (typeof str !== 'string') return ''; // Handle non-string inputs
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // --- Skeleton Loader ---
    const gridSkeletonHTML = `
        <div class="product-card bg-white rounded-lg shadow overflow-hidden animate-pulse h-80">
            <div class="product-image-container h-48 bg-gray-300"></div>
            <div class="product-details p-4 flex flex-col justify-between flex-grow">
                 <div>
                     <div class="product-header">
                         <div class="h-4 bg-gray-300 rounded w-3/4 mb-2"></div>
                         <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                     </div>
                     <div class="h-3 bg-gray-300 rounded w-full mt-2"></div>
                     <div class="h-3 bg-gray-300 rounded w-5/6 mt-1"></div>
                 </div>
                 <div class="product-actions mt-3">
                     <div class="h-9 bg-gray-300 rounded w-full"></div>
                 </div>
             </div>
        </div>
    `;

    /**
     * Generates the HTML string for a single product card.
     * @param {object} product The product data object.
     * @returns {string} The HTML string for the product card.
     */
    function createProductCardHTML(product) {
        if (!product) return ''; // Basic check
        const safeName = escapeHTML(product.name);
        const safeImage = escapeHTML(product.image || '');
        const safeDesc = escapeHTML(product.description || '');
        const safeSlug = escapeHTML(product.slug || '');
        const safeCategoryName = escapeHTML(product.category_name || ''); // Get category name
        const safeOptions = escapeHTML(JSON.stringify(product.options || {})); 
        const price = parseFloat(product.price);
        const originalPrice = product.original_price ? parseFloat(product.original_price) : null;
        const discountPercentage = product.discount_percentage ? parseFloat(product.discount_percentage) : null;
        const stock = parseInt(product.stock || 0);
        const isActive = product.is_active === true || product.is_active === '1';
        const backorder = product.backorder === true || product.backorder === '1';

        // Check if product is already in cart using the global function from cart.js
        let isInCart = false;
        if (typeof getCartItems === 'function') {
            const cartItems = getCartItems(); 
            isInCart = cartItems.some(item => item.id == product.id);
        } else {
            console.warn('getCartItems function not found.');
        }

        // Status Logic
        let statusText = '';
        let statusClasses = '';
        let statusBadgeHTML = ''; 

        if (isActive) { 
            if (stock > 0) {
                statusText = `${stock} In Stock`;
                statusClasses = 'bg-green-100 text-green-800';
            } else if (backorder) {
                statusText = 'Backorder';
                statusClasses = 'bg-yellow-100 text-yellow-800';
            } else {
                statusText = 'Sold Out';
                statusClasses = 'bg-red-100 text-red-800';
            }
            statusBadgeHTML = `<span class="absolute top-2 right-2 inline-block px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClasses} z-10">${statusText}</span>`;
        }
        
        const formattedPrice = isNaN(price) ? 'N/A' : currencySymbol + price.toFixed(2);
        let priceHTML = `<p class="product-price text-lg font-medium text-primary flex-shrink-0">${formattedPrice}</p>`;
        let discountBadgeHTML = '';

        if (originalPrice && !isNaN(originalPrice) && originalPrice > price) {
            const formattedOriginalPrice = currencySymbol + originalPrice.toFixed(2);
            priceHTML = `
                <div class="flex items-baseline gap-2">
                    <p class="product-price text-lg font-medium text-red-600 flex-shrink-0">${formattedPrice}</p>
                    <p class="text-sm text-gray-500 line-through flex-shrink-0">${formattedOriginalPrice}</p>
                </div>
            `;
            if (discountPercentage && !isNaN(discountPercentage)) {
                 discountBadgeHTML = `<span class="absolute top-2 left-2 bg-rose-700 text-white text-xs font-semibold px-2 py-0.5 rounded z-10">-${discountPercentage.toFixed(0)}% OFF</span>`;
            }
        }
        
        const priceData = isNaN(price) ? '' : price; 

        return `
            <div class="product-card bg-white rounded-lg shadow overflow-hidden transition-shadow duration-300 hover:shadow-lg flex flex-col cursor-pointer"
                 data-product-id="${product.id}"
                 data-product-name="${safeName}"
                 data-product-price="${priceData}"
                 data-product-image="${safeImage}"
                 data-product-description="${safeDesc}" 
                 data-product-slug="${safeSlug}" 
                 data-category-name="${safeCategoryName}" 
                 data-stock="${stock}" 
                 data-is-active="${isActive}" 
                 data-backorder="${backorder}" 
                 data-original-price="${originalPrice || ''}" 
                 data-discount-percentage="${discountPercentage || ''}" 
                 data-product-options='${safeOptions}'> 
                <div class="product-image-container relative h-56 bg-gray-200"> 
                    ${discountBadgeHTML} 
                    ${statusBadgeHTML}   
                    <img src="${safeImage}" alt="${safeName}" class="product-image w-full h-full object-cover"> 
                    <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/50 to-transparent">
                         <h3 class="product-name text-md font-semibold text-white truncate pointer-events-none">${safeName}</h3> 
                    </div>
                </div>
                <div class="product-details px-4 pb-4 pt-2 flex flex-col flex-grow"> 
                     <div class="product-header flex justify-between items-center mt-1"> 
                         ${priceHTML} 
                         <button type="button" 
                                 class="add-to-cart-icon-btn p-1.5 rounded-full ${isInCart ? 'text-green-500 bg-green-100 cursor-not-allowed in-cart' : 'text-gray-500 hover:text-primary hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary'} ml-auto transition-colors duration-200" 
                                 title="${isInCart ? 'Added to Cart' : 'Add to Cart'}"
                                 data-product-id="${product.id}" 
                                 data-product-name="${safeName}" 
                                 data-product-price="${priceData}" 
                                 data-product-image="${safeImage}" 
                                 data-product-options='${safeOptions}'
                                 ${isInCart ? 'disabled' : ''}>
                             ${isInCart ? checkIconSVG : cartIconSVG}
                         </button>
                     </div>
                 </div>
            </div>`;
    }

    /**
     * Renders the provided list of products into the product container.
     * Clears previous content and displays a "No products found" message if the list is empty.
     * @param {Array<object>} products An array of product objects to render.
     */
    function renderProducts(products) {
        if (!productContainer) return; // Element check
        productContainer.innerHTML = ''; // Clear existing content

        if (!products || products.length === 0) {
            productContainer.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No products found.</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your filters.</p>
                    <div class="mt-6"><a href="/pages/products.php" class="text-primary hover:text-indigo-700">View all products</a></div>
                </div>`;
        } else {
            products.forEach(product => {
                productContainer.insertAdjacentHTML('beforeend', createProductCardHTML(product));
            });
        }
        productContainer.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 transition-opacity duration-300 ease-in-out';
    }

    /**
     * Fetches products from the server based on the current filter state (category, subcategory, search query).
     * Updates the product container with the fetched products or skeleton loaders.
     * Updates the browser URL and history state.
     * Updates the product title display.
     */
    async function fetchAndUpdateProducts() {
        if (isFetching || !productContainer) return;
        isFetching = true;

        const startTime = Date.now();
        // Show grid skeletons if container is empty
        if (productContainer.innerHTML.trim() === '') {
            productContainer.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 transition-opacity duration-300 ease-in-out';
            for (let i = 0; i < 12; i++) {
                productContainer.insertAdjacentHTML('beforeend', gridSkeletonHTML);
            }
        }

        const categoryId = categoryValueInput ? categoryValueInput.value : '';
        const subcategoryId = subcategorySelect ? subcategorySelect.value : '';
        // Get current search query from URL
        const currentUrlParams = new URLSearchParams(window.location.search);
        const searchQuery = currentUrlParams.get('search') || '';

        const params = new URLSearchParams({
            fetch: 'true'
        });
        if (categoryId) params.append('category', categoryId);
        if (subcategoryId) params.append('subcategory', subcategoryId);
        if (searchQuery) params.append('search', searchQuery); // Add search to fetch
        const fetchUrl = `/pages/products.php?${params.toString()}`;

        const stateParams = new URLSearchParams();
        if (categoryId) stateParams.append('category', categoryId);
        if (subcategoryId) stateParams.append('subcategory', subcategoryId);
        if (searchQuery) stateParams.append('search', searchQuery); // Add search to history state
        const stateUrl = `/pages/products.php${stateParams.toString() ? '?' + stateParams.toString() : ''}`;

        const fetchPromise = fetch(fetchUrl);
        const minDelayPromise = new Promise(resolve => setTimeout(resolve, 500)); // Shorter delay

        try {
            const [response] = await Promise.all([fetchPromise, minDelayPromise]);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();

            // Update URL only if it actually changed
            if (window.location.href !== window.location.origin + stateUrl) {
                 history.pushState({ 
                    category: categoryId, 
                    subcategory: subcategoryId, 
                    search: searchQuery // Include search in state object
                 }, '', stateUrl);
            }
            if (productsTitle) {
                // Update title based on search and category
                let titleText = 'Showing: ';
                if (searchQuery) {
                    titleText += `Results for "${escapeHTML(searchQuery)}"`;
                    if (data.categoryName && data.categoryName !== 'All Products') {
                        titleText += ` in ${escapeHTML(data.categoryName)}`;
                    }
                } else {
                    titleText += data.categoryName || 'Products';
                }
                productsTitle.innerHTML = titleText;
            }
            renderProducts(data.products);

        } catch (error) {
            console.error('Error fetching products:', error);
            const elapsedTime = Date.now() - startTime;
            if (elapsedTime < 500) { // Match min delay
                await new Promise(resolve => setTimeout(resolve, 500 - elapsedTime));
            }
            productContainer.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 transition-opacity duration-300 ease-in-out';
            productContainer.innerHTML = '<p class="text-center col-span-full py-10 text-red-600">Error loading products.</p>';
        } finally {
            isFetching = false;
            checkAndToggleResetButton(); // Update reset button state after fetch
        }
    }

    /**
     * Checks the current filter state (category, subcategory, search)
     * and shows or hides the 'Reset Filters' button accordingly.
     */
    function checkAndToggleResetButton() {
        if (!resetFiltersBtn) return;
        const categoryIsFiltered = categoryValueInput ? categoryValueInput.value !== '' : false;
        const subcategoryIsFiltered = subcategorySelect ? subcategorySelect.value !== '' : false;
        // Also consider search as a filter
        const currentUrlParams = new URLSearchParams(window.location.search);
        const searchIsActive = currentUrlParams.has('search') && currentUrlParams.get('search') !== '';
        
        if (categoryIsFiltered || subcategoryIsFiltered || searchIsActive) {
            resetFiltersBtn.classList.remove('hidden');
        } else {
            resetFiltersBtn.classList.add('hidden');
        }
    }

    // =========================================================================
    // --- Event Listeners --- 
    // =========================================================================

    // --- Filter Event Listeners ---

    // Custom Category Dropdown Logic
    if (categoryDropdownButton && categoryDropdownPanel) {
        categoryDropdownButton.addEventListener('click', () => {
            categoryDropdownPanel.classList.toggle('hidden');
            categoryDropdownButton.setAttribute('aria-expanded', !categoryDropdownPanel.classList.contains('hidden'));
        });

        categoryOptions.forEach(option => {
            option.addEventListener('click', () => {
                const value = option.getAttribute('data-value');
                const image = option.getAttribute('data-image');
                const nameElement = option.querySelector('span > span');
                const name = nameElement ? nameElement.textContent : 'All Categories';

                if (categoryValueInput) categoryValueInput.value = value;

                if (categoryDropdownSelected) {
                    let buttonHTML = '';
                    if (image) {
                        buttonHTML += `<img src="${escapeHTML(image)}" alt="" class="h-5 w-5 mr-2 flex-shrink-0 rounded-sm object-cover">`;
                    } else {
                        buttonHTML += '<svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM8.707 14.707a1 1 0 001.414 0L14 10.414V12a1 1 0 102 0V8a1 1 0 00-1-1h-4a1 1 0 100 2h1.586l-4.293 4.293a1 1 0 000 1.414z" clip-rule="evenodd" /></svg>';
                    }
                    buttonHTML += `<span>${escapeHTML(name)}</span>`;
                    categoryDropdownSelected.innerHTML = buttonHTML;
                }

                categoryDropdownPanel.classList.add('hidden');
                categoryDropdownButton.setAttribute('aria-expanded', 'false');

                updateSubcategoryOptions();
                
                // Clear subcategory selection when parent changes
                if (subcategorySelect) subcategorySelect.value = ''; 

                // Show skeletons and fetch
                if (productContainer) {
                    productContainer.innerHTML = '';
                    productContainer.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 transition-opacity duration-300 ease-in-out';
                    for (let i = 0; i < 12; i++) {
                        productContainer.insertAdjacentHTML('beforeend', gridSkeletonHTML);
                    }
                    fetchAndUpdateProducts();
                }
            });
        });
    }

    // Subcategory Select Listener
    if (subcategorySelect) {
        subcategorySelect.addEventListener('change', function() {
            if (productContainer) {
                productContainer.innerHTML = '';
                productContainer.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 transition-opacity duration-300 ease-in-out';
                for (let i = 0; i < 12; i++) {
                    productContainer.insertAdjacentHTML('beforeend', gridSkeletonHTML);
                }
                 fetchAndUpdateProducts();
            }
        });
    }

    // Close Dropdowns on Outside Click
    document.addEventListener('click', function(event) {
        if (categoryDropdownContainer && !categoryDropdownContainer.contains(event.target) && categoryDropdownPanel && !categoryDropdownPanel.classList.contains('hidden')) {
            categoryDropdownPanel.classList.add('hidden');
             if(categoryDropdownButton) categoryDropdownButton.setAttribute('aria-expanded', 'false');
        }
        if (filterContainer && !filterContainer.contains(event.target) && filterControlsPanel && !filterControlsPanel.classList.contains('hidden') && window.innerWidth < 768) {
            filterControlsPanel.classList.add('hidden');
        }
    });

    // --- Product Interaction Listeners ---

    // Product Container Click Delegation (Handles Modal Trigger & Add to Cart)
    if (productContainer) {
        productContainer.addEventListener('click', function(event) {
            const card = event.target.closest('.product-card');
            const addToCartButton = event.target.closest('.add-to-cart-icon-btn');

            // Add to Cart Button Clicked
            if (addToCartButton && !addToCartButton.disabled) {
                const productId = addToCartButton.dataset.productId;
                const productName = addToCartButton.dataset.productName;
                const productPrice = addToCartButton.dataset.productPrice;
                const productImage = addToCartButton.dataset.productImage;
                const productOptions = JSON.parse(addToCartButton.dataset.productOptions || '{}');
                const quantity = 1;

                // Call global addToCart function from cart.js
                if (typeof addToCart === 'function') {
                    addToCart(productId, quantity, productName, productPrice, productImage, productOptions);

                    // Visual Feedback
                    addToCartButton.disabled = true;
                    addToCartButton.classList.add('animate-bounce', 'cursor-not-allowed');

                    setTimeout(() => {
                        addToCartButton.innerHTML = checkIconSVG;
                        addToCartButton.classList.remove('animate-bounce', 'text-gray-500', 'hover:text-primary', 'hover:bg-gray-100');
                        addToCartButton.classList.add('text-green-500', 'bg-green-100', 'in-cart');
                        addToCartButton.title = 'Added to Cart';
                    }, 500);

                } else {
                    console.error("addToCart function is not defined.");
                    alert("Error: Could not add item to cart.");
                }
                return; // Prevent modal opening
            }

            // Product Card Clicked (Open Modal)
            if (card) {
                const productId = card.dataset.productId;
                const productName = card.dataset.productName;
                const productPrice = card.dataset.productPrice;
                const productOptions = JSON.parse(card.dataset.productOptions || '{}');
                const productDescription = card.dataset.productDescription || ''; 
                const productImage = card.dataset.productImage;
                const productSlug = card.dataset.productSlug || ''; 
                // Get additional data
                const categoryName = card.dataset.categoryName || 'Uncategorized';
                const stock = parseInt(card.dataset.stock || '0');
                const isActive = card.dataset.isActive === 'true';
                const backorder = card.dataset.backorder === 'true';
                const originalPrice = card.dataset.originalPrice ? parseFloat(card.dataset.originalPrice) : null;
                const discountPercentage = card.dataset.discountPercentage ? parseFloat(card.dataset.discountPercentage) : null;

                // Check if the global openProductModal function exists
                if (typeof openProductModal === 'function') {
                    // Pass all data to the modal function
                    openProductModal(productId, productName, productPrice, productImage, productDescription, productOptions, productSlug,
                                     categoryName, stock, isActive, backorder, originalPrice, discountPercentage);
                } else {
                    console.warn('openProductModal function not found.');
                    alert(`Product Clicked: ${productName}`); // Fallback
                }
            }
        });
    }

    // --- Other UI Listeners ---

    // Mobile Filter Dropdown Logic
    if (mobileFilterTrigger && filterControlsPanel) {
        mobileFilterTrigger.addEventListener('click', (event) => {
            event.stopPropagation();
            filterControlsPanel.classList.toggle('hidden');
        });
    }
    
    // Handle Browser Back/Forward Navigation (Popstate)
    window.addEventListener('popstate', function(event) {
        const state = event.state || { category: null, subcategory: null, search: null }; // Add search to default state
        const stateCategoryId = state.category || '';
        const stateSubcategoryId = state.subcategory || '';
        const stateSearchQuery = state.search || ''; // Get search from state

        console.log('Popstate triggered:', state);

        // Set the search input value if it exists in the modal
        const modalSearchInput = document.getElementById('modalSearchInput');
        if(modalSearchInput) {
            modalSearchInput.value = stateSearchQuery;
        }

        // Update hidden category input value FIRST
        if (categoryValueInput) {
            categoryValueInput.value = stateCategoryId;
        }

        // Find the corresponding option to update the button display
        const selectedOption = categoryDropdownPanel ? categoryDropdownPanel.querySelector(`.category-option[data-value="${stateCategoryId}"]`) : null;
        if (selectedOption && categoryDropdownSelected) {
            const image = selectedOption.getAttribute('data-image');
            const nameElement = selectedOption.querySelector('span > span');
            const name = nameElement ? nameElement.textContent : 'All Categories';
            let buttonHTML = '';
            if (image) {
                buttonHTML += `<img src="${escapeHTML(image)}" alt="" class="h-5 w-5 mr-2 flex-shrink-0 rounded-sm object-cover">`;
            } else {
                buttonHTML += '<svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM8.707 14.707a1 1 0 001.414 0L14 10.414V12a1 1 0 102 0V8a1 1 0 00-1-1h-4a1 1 0 100 2h1.586l-4.293 4.293a1 1 0 000 1.414z" clip-rule="evenodd" /></svg>';
            }
            buttonHTML += `<span>${escapeHTML(name)}</span>`;
            categoryDropdownSelected.innerHTML = buttonHTML;
        }

        updateSubcategoryOptions(); // Update subcategory options based on the new category
        
        // Set subcategory select value AFTER updating options
        if (subcategorySelect) {
            subcategorySelect.value = stateSubcategoryId;
        }

        // Fetch products for the restored state only if not currently fetching
        if (!isFetching) {
            if (productContainer) {
                productContainer.innerHTML = ''; // Clear current products/skeletons
                productContainer.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 transition-opacity duration-300 ease-in-out';
                for (let i = 0; i < 12; i++) {
                    productContainer.insertAdjacentHTML('beforeend', gridSkeletonHTML);
                }
            }    
            fetchAndUpdateProducts(); // Fetch data for the new state
        }
    });

    // =========================================================================
    // --- Initial Setup & Execution --- 
    // =========================================================================

    // Set initial category value from PHP_DATA
    if (categoryValueInput && initialCategoryId !== null) {
        categoryValueInput.value = initialCategoryId;
    }
    
    // Update subcategories based on initial category
    updateSubcategoryOptions();
    
    // Set initial subcategory selection from PHP_DATA
    if (subcategorySelect && initialSubcategoryId !== null && subcategorySelect.querySelector(`option[value="${initialSubcategoryId}"]`)) {
        subcategorySelect.value = initialSubcategoryId;
    }

    // Grid is default, PHP renders grid skeletons. Ensure classes are set.
    if (productContainer) {
        productContainer.className = 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 transition-opacity duration-300 ease-in-out';
    }

    // Trigger Initial Data Load
    fetchAndUpdateProducts(); 

}); 