document.addEventListener('DOMContentLoaded', function() {
    const productGrid = document.getElementById('product-grid');
    // Keep reference to original loading indicator if it exists
    const loadingIndicator = document.getElementById('loading-products'); 
    const currencySymbolElement = document.getElementById('product-grid'); 
    const currencySymbol = currencySymbolElement ? (currencySymbolElement.dataset.currencySymbol || '$') : '$';
    const pageTitleElement = document.getElementById('product-list-title');
    const originalPageTitle = pageTitleElement ? pageTitleElement.textContent : 'Products';
    const parentTabsContainer = document.getElementById('parent-category-tabs');
    const subcategoryDisplay = document.getElementById('subcategory-display');
    const titleResetButton = document.getElementById('reset-filters-title-btn'); // Get reference to the new button
    const searchInput = document.getElementById('product-search');
    let subcategorySwiperInstance = null; // Variable to hold the Swiper instance
    let allProducts = []; // Store all fetched products
    let currentMinPrice = null;
    let currentMaxPrice = null;
    let currentSearchTerm = '';
    let currentCategory = '';
    let currentSubcategory = '';

    // Get initial products and demo mode status
    const isDemoMode = productGrid?.dataset.isDemo === 'true';
    try {
        const initialProductsData = productGrid.dataset.initialProducts;
        if (initialProductsData) {
            allProducts = JSON.parse(initialProductsData);
            // Remove the data attribute to free up memory
            delete productGrid.dataset.initialProducts;
        }
    } catch (error) {
        console.error('Error parsing initial products:', error);
    }

    // Initialize Lucide icons for the pre-rendered content
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // --- Helper Functions ---

    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatNumberWithCommas(number) {
         if (number === null || number === undefined) return '0.00';
         const parsed = parseFloat(number);
         if (isNaN(parsed)) return '0.00'; 
         return parsed.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // Utility function to get the first image from product image data
    function getProductThumbnail(imageData) {
        if (!imageData) {
            return '/assets/images/product-placeholder.png';
        }
        
        // Handle JSON array format (new format)
        if (typeof imageData === 'string' && imageData.startsWith('[')) {
            try {
                const imageArray = JSON.parse(imageData);
                if (Array.isArray(imageArray) && imageArray.length > 0) {
                    return imageArray[0]; // Return first image
                }
            } catch (e) {
                console.error('Error parsing image JSON:', e);
            }
        }
        
        // Handle array format (if already parsed)
        if (Array.isArray(imageData) && imageData.length > 0) {
            return imageData[0]; // Return first image
        }
        
        // Handle single string format (legacy format)
        if (typeof imageData === 'string' && imageData.trim() !== '') {
            return imageData;
        }
        
        // Fallback to placeholder
        return '/assets/images/product-placeholder.png';
    }

    // Function to set active class on tabs/links within a container
    // Ensures only one item has the active class
    function setActiveClass(container, clickedElement, activeClass = 'active') {
        if (!container) return;
        // Find all potential elements to deactivate based on a common class or selector
        const elements = container.querySelectorAll('.category-tab, .subcategory-link');
        elements.forEach(el => {
            // Ensure we are comparing with the correct active class for the element type
            const currentActiveClass = el.classList.contains('category-tab') ? 'active' : 'active-sub';
            el.classList.remove(currentActiveClass);
        });

        // Add active class to the newly clicked element if provided
        if (clickedElement) {
            const newActiveClass = clickedElement.classList.contains('category-tab') ? 'active' : 'active-sub';
            clickedElement.classList.add(newActiveClass);
        }
    }
    
    // Specific function to set active subcategory link
    function setActiveSubcategory(subSlug) {
         if (!subcategoryDisplay) return;
         subcategoryDisplay.querySelectorAll('.subcategory-link').forEach(el => el.classList.remove('active-sub'));
         if (subSlug) {
             const activeSubLink = subcategoryDisplay.querySelector(`.subcategory-link[data-subcategory-slug="${subSlug}"]`);
             if (activeSubLink) activeSubLink.classList.add('active-sub');
         }
    }

    // Function to show empty state message
    function showEmptyState(message, submessage = '') {
        if (!productGrid) return;
        
        productGrid.innerHTML = `
            <div class="col-span-full text-center py-16 px-6">
                <div class="max-w-lg mx-auto">
                    <i data-lucide="package-x" class="mx-auto h-16 w-16 text-gray-400 mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">${message}</h3>
                    <p class="text-gray-500 mb-6">${submessage}</p>
                </div>
            </div>
        `;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // --- Product Fetching and Display ---
    window.fetchAndDisplayProducts = function(url) {
        if (!productGrid || isDemoMode) return; // Don't fetch in demo mode

        // Only show loading state if we don't have products yet
        if (!allProducts.length) {
            productGrid.innerHTML = `
                <div class="col-span-full grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    ${Array(8).fill().map(() => `
                        <div class="product-card bg-white rounded-lg shadow overflow-hidden animate-pulse transition-shadow duration-300 hover:shadow-lg flex flex-col cursor-pointer w-full">
                            <div class="product-image-container relative h-56 bg-gray-200 w-full min-w-max">
                                <div class="absolute bg-gray-300 top-2 right-2 rounded h-5 w-12"></div>
                                <div class="absolute bg-gray-300 top-2 left-2 rounded h-5 w-14"></div>
                                <div class="absolute bg-gray-200 bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/20 to-transparent">
                                    <div class="h-5 bg-gray-300 rounded w-2/3"></div>
                                </div>
                            </div>
                            <div class="product-details px-4 pb-4 pt-2 flex flex-col flex-grow gap-2">
                                <div class="product-header flex justify-between items-center mt-1">
                                    <div class="h-6 bg-gray-300 rounded w-1/2"></div>
                                    <div class="bg-gray-300 rounded h-6 w-6 ml-auto"></div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Convert the old API URL to the new v2 format
        const oldUrl = new URL(url, window.location.origin);
        const newUrl = new URL('/api/products_v2.php', window.location.origin);
        // Copy over the search parameters
        oldUrl.searchParams.forEach((value, key) => {
            newUrl.searchParams.set(key, value);
        });

        // Add cache busting only when necessary
        if (window.productCacheNeedsUpdate) {
            newUrl.searchParams.set('_t', Date.now());
            window.productCacheNeedsUpdate = false;
        }

        fetch(newUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!productGrid) return;

                if (!data.success || !Array.isArray(data.products)) {
                    if (!allProducts.length) {
                        showEmptyState(
                            'Error Loading Products',
                            'There was a problem fetching the product data. Please try again later.'
                        );
                    }
                    return;
                }

                // Store the products
                allProducts = data.products;
                
                // Apply current filters and display immediately
                filterAndDisplayProducts();

                // Preload images for better user experience
                requestIdleCallback(() => {
                    data.products.forEach(product => {
                        if (product.image) {
                            const img = new Image();
                            img.src = product.image;
                        }
                    });
                });
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                if (!allProducts.length) {
                    showEmptyState(
                        'Error Loading Products',
                        'There was a problem connecting to the store. Please check your connection and try again.'
                    );
                }
            });
    };

    // --- Subcategory Fetching and Display ---
    async function fetchAndDisplaySubcategories(parentSlug) {
        if (!subcategoryDisplay) return;

        // 1. Destroy previous Swiper instance if it exists
        if (subcategorySwiperInstance) {
            subcategorySwiperInstance.destroy(true, true);
            subcategorySwiperInstance = null;
        }

        // Skeleton Loader HTML
        const skeletonSubcategoryHTML = `<div class="flex space-x-2 overflow-hidden"><div class="px-3 py-1 h-7 w-24 bg-gray-200 rounded-full animate-pulse flex-shrink-0"></div> <div class="px-3 py-1 h-7 w-28 bg-gray-200 rounded-full animate-pulse flex-shrink-0"></div> <div class="px-3 py-1 h-7 w-20 bg-gray-200 rounded-full animate-pulse flex-shrink-0"></div></div>`;
        subcategoryDisplay.innerHTML = skeletonSubcategoryHTML;

        const fetchAll = (!parentSlug || parentSlug === 'all');
        let apiUrl = '/api/subcategory_api.php';
        if (!fetchAll) apiUrl += `?parent_slug=${encodeURIComponent(parentSlug)}`;
        
        try {
            const response = await fetch(apiUrl);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const data = await response.json();
            
            // Clear skeleton/previous content
            subcategoryDisplay.innerHTML = ''; 

            if (data.success && data.subcategories.length > 0) {
                // 2. Inject Swiper HTML structure
                subcategoryDisplay.innerHTML = `
                    <div class="swiper subcategory-swiper relative group"> 
                        <div class="swiper-wrapper"> 
                            <!-- Slides will be added here -->
                        </div>
                    </div>
                `;

                const swiperWrapper = subcategoryDisplay.querySelector('.swiper-wrapper');
                if (!swiperWrapper) return;

                // 3. Add slides with buttons
                data.subcategories.forEach(sub => {
                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide !w-auto';
                    
                    const subLink = document.createElement('button');
                    subLink.className = 'subcategory-link block px-3 py-1 text-sm rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 hover:border-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors';
                    subLink.textContent = sub.name;
                    // Convert category name to slug for data attribute
                    subLink.dataset.subcategorySlug = sub.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                    
                    slide.appendChild(subLink);
                    swiperWrapper.appendChild(slide);
                });

                // 4. Initialize Swiper
                subcategorySwiperInstance = new Swiper('.subcategory-swiper', {
                    slidesPerView: 'auto',
                    spaceBetween: 8,
                    loop: true,
                    centeredSlides: false,
                    slidesOffsetBefore: 0
                });
            } else if (data.success && data.subcategories.length === 0 && !fetchAll) {
                subcategoryDisplay.innerHTML = '<p class="text-gray-500 text-sm italic">No subcategories found.</p>';
            } else if (!data.success) {
                 subcategoryDisplay.innerHTML = '<p class="text-red-500 text-sm">Error loading subcategories.</p>';
                 console.error('API error fetching subcategories:', data.message);
            }
             // If fetchAll and no subcategories, display remains empty
        } catch (error) {
            console.error('Fetch error for subcategories:', error);
            subcategoryDisplay.innerHTML = '<p class="text-red-500 text-sm">Could not fetch subcategories.</p>';
        }
    }

    // --- Update View (URL, Title, Products) ---
    function updateProductView(params = {}) {
        const { category = 'all', subcategory_slug = null, search = null, price_min = null, price_max = null } = params;
        const queryParams = []; // For browser URL
        const apiParams = [];   // For API call
        let pageTitle = originalPageTitle;
        let parentSlugForApi = null;

        // Build parameters for Browser URL (using 'category')
        if (category !== 'all') {
            queryParams.push(`category=${encodeURIComponent(category)}`);
            parentSlugForApi = category; 
            const parentTab = parentTabsContainer?.querySelector(`.category-tab[data-category-slug="${category}"]`);
            if (parentTab) pageTitle = parentTab.textContent.trim();
        }
        if (subcategory_slug) {
             queryParams.push(`subcategory_slug=${encodeURIComponent(subcategory_slug)}`);
             const subLink = subcategoryDisplay?.querySelector(`.subcategory-link[data-subcategory-slug="${subcategory_slug}"]`);
             if(subLink) pageTitle += ` > ${subLink.textContent.trim()}`;
        }
        if (search) {
             queryParams.push(`search=${encodeURIComponent(search)}`);
             pageTitle = `Search Results for "${search}"`;
        }
        if (price_min !== null) {
            queryParams.push(`price_min=${encodeURIComponent(price_min)}`);
            pageTitle += ` (Min: ${currencySymbol}${price_min})`;
        }
        if (price_max !== null) {
            queryParams.push(`price_max=${encodeURIComponent(price_max)}`);
            pageTitle += ` (Max: ${currencySymbol}${price_max})`;
        }

        // Build parameters for API call (using 'parent_category_slug')
        if (parentSlugForApi) apiParams.push(`parent_category_slug=${encodeURIComponent(parentSlugForApi)}`);
        if (subcategory_slug) apiParams.push(`subcategory_slug=${encodeURIComponent(subcategory_slug)}`);
        if (search) apiParams.push(`search=${encodeURIComponent(search)}`);
        if (price_min !== null) apiParams.push(`price_min=${encodeURIComponent(price_min)}`);
        if (price_max !== null) apiParams.push(`price_max=${encodeURIComponent(price_max)}`);
        
        // Construct URLs
        let newUrl = window.location.pathname + (queryParams.length > 0 ? `?${queryParams.join('&')}` : '');
        let apiUrl = '/api/product_api.php' + (apiParams.length > 0 ? `?${apiParams.join('&')}` : '');
        
        // Update Browser History and Title
        history.replaceState(params, pageTitle, newUrl);
        if (pageTitleElement) pageTitleElement.textContent = pageTitle;
        document.title = pageTitle;

        // Show/Hide Title Reset Button
        if (titleResetButton) {
            const filtersActive = (category && category !== 'all') || subcategory_slug || search || price_min !== null || price_max !== null;
            titleResetButton.classList.toggle('hidden', !filtersActive);
            titleResetButton.classList.toggle('flex', filtersActive);
        }

        // Fetch Products
        console.log('[updateProductView] Fetching products with API URL:', apiUrl);
        window.fetchAndDisplayProducts(apiUrl);
        
        // Update active states
        if (parentTabsContainer) {
             const activeParentTab = parentTabsContainer.querySelector(`.category-tab[data-category-slug="${category}"]`);
             setActiveClass(parentTabsContainer, activeParentTab, 'active');
        }
         setActiveSubcategory(subcategory_slug);
    }

    // --- Event Listeners ---

    // Parent Category Tab Clicks
    if (parentTabsContainer) {
        parentTabsContainer.addEventListener('click', async function(event) { // Keep async if fetchAndDisplaySubcategories is async
            const button = event.target.closest('.category-tab');
            if (!button || button.classList.contains('active')) return;

            const categorySlug = button.dataset.categorySlug;
            const currentParams = new URLSearchParams(window.location.search);
            const search = currentParams.get('search') || null;
            
            // Update active tab & clear subcategory active state immediately
            setActiveClass(parentTabsContainer, button, 'active');
            if (subcategoryDisplay) {
                subcategoryDisplay.querySelectorAll('.subcategory-link').forEach(el => el.classList.remove('active-sub'));
            }

            // Start fetching subcategories (don't await)
            fetchAndDisplaySubcategories(categorySlug === 'all' ? null : categorySlug);
            
            // Immediately update the product view (products, URL, title)
            updateProductView({ category: categorySlug, subcategory_slug: null, search: search });
        });
    }

    // Subcategory Link Clicks
    if (subcategoryDisplay) {
        subcategoryDisplay.addEventListener('click', function(event) {
            const button = event.target.closest('.subcategory-link');
             if (!button || button.classList.contains('active-sub')) return;

            const subSlug = button.dataset.subcategorySlug;
            const currentParams = new URLSearchParams(window.location.search);
            const parentSlug = currentParams.get('category') || 'all'; // Need the current parent
            const search = currentParams.get('search') || null;

            if (parentSlug === 'all') {
                 console.warn('Subcategory clicked while "All Products" selected. Parent might be ambiguous.');
                 // Decide behavior? Maybe find parent via API? For now, filter using sub + search.
                  updateProductView({ subcategory_slug: subSlug, search: search });
                 return;
            }
            
            // Update view (this will handle active classes)
            updateProductView({ category: parentSlug, subcategory_slug: subSlug, search: search });
        });
    }

    // Product Card Clicks & Add to Cart
    if (productGrid) {
        productGrid.addEventListener('click', function(event) {
            // Check if Add to Cart button was clicked
            const addToCartBtn = event.target.closest('.add-to-cart-icon-btn');
            if (addToCartBtn && !addToCartBtn.disabled) {
                event.stopPropagation(); // Prevent product card click/modal open
                
                const productId = addToCartBtn.dataset.productId;
                const productName = addToCartBtn.dataset.productName;
                const productPrice = addToCartBtn.dataset.productPrice;
                const productImage = addToCartBtn.dataset.productImage;
                // Minimal details needed for the cart item
                const productDetails = {
                    id: productId,
                    name: productName,
                    price: productPrice,
                    image: productImage
                };

                // Add item to cart using cart.js
                const added = cart.addItem(productId, productDetails);

                if (added) {
                    // Update button UI
                    addToCartBtn.innerHTML = `<i data-lucide="check" class="lucide-icon size-4 text-green-600"></i>`;
                    addToCartBtn.classList.remove('text-gray-500', 'hover:text-primary', 'hover:bg-gray-100');
                    addToCartBtn.classList.add('text-green-500', 'bg-green-100', 'cursor-not-allowed');
                    addToCartBtn.disabled = true;
                    addToCartBtn.title = 'Added to Cart';
                    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [addToCartBtn] });

                    // Also update any other buttons for the same product
                    document.querySelectorAll(`.add-to-cart-icon-btn[data-product-id="${productId}"]`).forEach(btn => {
                        if (btn !== addToCartBtn) {
                            btn.innerHTML = `<i data-lucide="check" class="lucide-icon size-4 text-green-600"></i>`;
                            btn.classList.remove('text-gray-500', 'hover:text-primary', 'hover:bg-gray-100');
                            btn.classList.add('text-green-500', 'bg-green-100', 'cursor-not-allowed');
                            btn.disabled = true;
                            btn.title = 'Added to Cart';
                            if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [btn] });
                        }
                    });

                    // Show toast notification (ensure toast object is available)
                    if (typeof toast !== 'undefined') {
                        toast.success(`'${productName}' added to cart!`);
                    } else {
                        console.warn('Toast notification system not found.');
                    }

                    // Header count is updated by cart.addItem
                } else {
                     if (typeof toast !== 'undefined') {
                        toast.error(`Could not add '${productName}' to cart.`);
                    } else {
                        console.error('Toast notification system not found.');
                    }
                }
                return; // Stop further processing
            }
            
            // Reset Button Click (Ensure it's handled if not an add-to-cart click)
            const resetButton = event.target.closest('#reset-products-btn');
            if (resetButton) {
                fetchAndDisplaySubcategories(null); // Fetch all subcategories
                updateProductView({}); // Reset view to all products, no filters
                return;
            }

            // Product Card Click (If not Add to Cart or Reset)
            const productCard = event.target.closest('.product-card');
            if (productCard && !addToCartBtn) { // Ensure add-to-cart wasn't clicked
                // Collect product data from data attributes
                const product = {
                    id: productCard.dataset.productId,
                    name: productCard.dataset.productName,
                    price: productCard.dataset.productPrice,
                    image: productCard.dataset.productImage,
                    description: productCard.dataset.productDescription,
                    slug: productCard.dataset.productSlug,
                    category_name: productCard.dataset.categoryName,
                    stock: productCard.dataset.stock,
                    is_active: productCard.dataset.isActive === 'true',
                    backorder: productCard.dataset.backorder === 'true',
                    original_price: productCard.dataset.originalPrice,
                    discount_percentage: productCard.dataset.discountPercentage,
                    // Parse the options directly from the attribute
                    options: JSON.parse(productCard.dataset.productOptions || '{}')
                };
                
                // Open the product modal using Alpine.js
                const body = document.querySelector('body');
                if (body && body._x_dataStack) {
                    const alpineData = body._x_dataStack[0]; // Assuming global Alpine data is the first element
                    if (alpineData) {
                        // Initialize currentImageIndex for multiple image navigation
                        product.currentImageIndex = 0;
                        alpineData.selectedProduct = product;
                        alpineData.isProductModalOpen = true;
                        // Re-initialize icons in the modal if needed after content changes
                        setTimeout(() => { 
                            if (typeof lucide !== 'undefined') {
                                const modalPanel = document.querySelector('#productModalPanel'); // Adjust if your modal panel ID is different
                                if (modalPanel) lucide.createIcons({ nodes: [modalPanel] });
                            }
                        }, 50); // Delay slightly for Alpine rendering
                    }
                }
            }
        });
    }
    
    // Title Reset Button Click
    if (titleResetButton) {
        titleResetButton.addEventListener('click', function() {
            fetchAndDisplaySubcategories(null); // Fetch all subcategories
            updateProductView({}); // Reset view to all products, no filters
            // The updateProductView call will automatically hide this button again
        });
    }
    
    // Handle Browser Back/Forward Navigation
    window.addEventListener('popstate', function(event) {
        if (event.state) {
             // State object might contain our category/subcat/search params
             console.log('Popstate triggered:', event.state);
             fetchAndDisplaySubcategories(event.state.category === 'all' ? null : event.state.category).then(() => {
                 updateProductView(event.state); 
             });
        } else {
            // No state, likely initial page load or manual URL change - re-init
             initializeProductView(); 
        }
    });

    // --- Initial Page Load Logic ---
    function initializeProductView() {
        if (!parentTabsContainer || !subcategoryDisplay || !productGrid) {
             console.error("Essential elements for product view initialization missing.");
             return;
        }
        console.log("Initializing Product View...");
        const urlParams = new URLSearchParams(window.location.search);
        const category = urlParams.get('category') || 'all';
        const subSlug = urlParams.get('subcategory_slug');
        const search = urlParams.get('search');
        
        // Apply any URL parameters immediately
        if (search) {
            currentSearchTerm = search;
            searchInput.value = search;
        }
        
        if (category !== 'all') {
            currentCategory = category;
            if (subSlug) {
                currentSubcategory = subSlug;
            }
            // Fetch subcategories if needed
            fetchAndDisplaySubcategories(category);
        }

        // Apply filters if any parameters exist
        if (search || category !== 'all' || subSlug) {
            filterAndDisplayProducts();
        }
    }

    initializeProductView(); // Run initial setup

    // --- Auto Refresh Logic --- 
    function autoRefreshProducts() {
        if (isDemoMode) return; // Don't auto-refresh in demo mode
        
        const currentParams = new URLSearchParams(window.location.search);
        const apiParams = [];
        const parentSlug = currentParams.get('category');
        const subSlug = currentParams.get('subcategory_slug');
        const search = currentParams.get('search');

        if (parentSlug && parentSlug !== 'all') apiParams.push(`parent_category_slug=${encodeURIComponent(parentSlug)}`);
        if (subSlug) apiParams.push(`subcategory_slug=${encodeURIComponent(subSlug)}`);
        if (search) apiParams.push(`search=${encodeURIComponent(search)}`);

        let apiUrl = '/api/product_api.php' + (apiParams.length > 0 ? `?${apiParams.join('&')}` : '');
        console.log('[Auto Refresh] Fetching with API URL:', apiUrl);

        // Fetch and display, but DO NOT update history or title
        window.fetchAndDisplayProducts(apiUrl);
    }

    // Only set auto-refresh if not in demo mode
    if (!isDemoMode) {
        setInterval(autoRefreshProducts, 300000);
    }

    // Search input handler
    if (searchInput) {
        let debounceTimeout;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                currentSearchTerm = e.target.value;
                
                // Update URL
                const urlParams = new URLSearchParams(window.location.search);
                if (currentSearchTerm) urlParams.set('search', currentSearchTerm);
                else urlParams.delete('search');
                const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
                history.pushState({}, '', newUrl);

                // Apply filters
                filterAndDisplayProducts();
            }, 300);
        });
    }

    // Price filter handler
    window.handlePriceFilter = function(min, max) {
        currentMinPrice = min ? parseFloat(min) : null;
        currentMaxPrice = max ? parseFloat(max) : null;
        
        const urlParams = new URLSearchParams(window.location.search);
        if (currentMinPrice !== null) urlParams.set('price_min', currentMinPrice);
        else urlParams.delete('price_min');
        if (currentMaxPrice !== null) urlParams.set('price_max', currentMaxPrice);
        else urlParams.delete('price_max');
        
        const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
        history.pushState({}, '', newUrl);

        filterAndDisplayProducts();
    };

    // Function to filter and display products
    function filterAndDisplayProducts() {
        if (!productGrid || !allProducts.length) return;

        let filteredProducts = [...allProducts];
        console.log('Starting filter with products:', filteredProducts.length);

        // Apply category filter
        if (currentCategory && currentCategory !== 'all') {
            console.log('Filtering by category:', currentCategory);
            if (currentCategory === 'uncategorized') {
                // Filter for uncategorized products
                filteredProducts = filteredProducts.filter(product => {
                    return !product.category_slug && !product.parent_category_slug;
                });
            } else if (currentSubcategory) {
                console.log('Filtering by subcategory:', currentSubcategory);
                filteredProducts = filteredProducts.filter(product => {
                    console.log('Product category data:', {
                        productId: product.id,
                        name: product.name,
                        categorySlug: product.category_slug,
                        parentCategorySlug: product.parent_category_slug,
                        currentCategory,
                        currentSubcategory
                    });
                    
                    return product.category_slug === currentSubcategory;
                });
            } else {
                // Filter by parent category
                filteredProducts = filteredProducts.filter(product => {
                    console.log('Product category data:', {
                        productId: product.id,
                        name: product.name,
                        categorySlug: product.category_slug,
                        parentCategorySlug: product.parent_category_slug,
                        currentCategory
                    });

                    return product.parent_category_slug === currentCategory || 
                           product.category_slug === currentCategory;
                });
            }
            console.log('After category filtering:', filteredProducts.length);
        }

        // Apply price filter
        if (currentMinPrice !== null) {
            filteredProducts = filteredProducts.filter(product => {
                const price = parseFloat(product.price);
                return !isNaN(price) && price >= currentMinPrice;
            });
        }
        if (currentMaxPrice !== null) {
            filteredProducts = filteredProducts.filter(product => {
                const price = parseFloat(product.price);
                return !isNaN(price) && price <= currentMaxPrice;
            });
        }

        // Apply search filter
        if (currentSearchTerm) {
            const searchLower = currentSearchTerm.toLowerCase();
            filteredProducts = filteredProducts.filter(product => 
                (product.name && product.name.toLowerCase().includes(searchLower)) ||
                (product.description && product.description.toLowerCase().includes(searchLower))
            );
        }

        console.log('Final filtered products:', filteredProducts.length);
        displayProducts(filteredProducts);
        
        // Show/Hide Reset Button
        if (titleResetButton) {
            const filtersActive = currentMinPrice !== null || currentMaxPrice !== null || 
                                currentSearchTerm || currentCategory || currentSubcategory;
            titleResetButton.classList.toggle('hidden', !filtersActive);
            titleResetButton.classList.toggle('flex', filtersActive);
        }

        // Update page title
        updatePageTitle(filteredProducts.length);
    }

    // Function to update page title based on filters
    function updatePageTitle(resultCount) {
        let title = originalPageTitle;
        const filters = [];

        if (currentCategory) {
            const categoryElement = document.querySelector(`option[value="${currentCategory}"]`);
            if (categoryElement) {
                filters.push(categoryElement.textContent);
                if (currentSubcategory) {
                    const subcategoryElement = document.querySelector(`option[value="${currentSubcategory}"]`);
                    if (subcategoryElement) {
                        filters.push(subcategoryElement.textContent);
                    }
                }
            }
        }
        if (currentSearchTerm) filters.push(`Search: "${currentSearchTerm}"`);
        if (currentMinPrice !== null) filters.push(`Min: ${currencySymbol}${currentMinPrice}`);
        if (currentMaxPrice !== null) filters.push(`Max: ${currencySymbol}${currentMaxPrice}`);

        if (filters.length > 0) {
            title = `${title} (${filters.join(' > ')})`;
        }

        if (pageTitleElement) pageTitleElement.textContent = title;
        document.title = title;
    }

    // Reset all filters
    window.resetAllFilters = function() {
        currentMinPrice = null;
        currentMaxPrice = null;
        currentSearchTerm = '';
        currentCategory = '';
        currentSubcategory = '';
        
        // Reset URL
        history.pushState({}, '', window.location.pathname);
        
        // Reset search input
        if (searchInput) searchInput.value = '';
        
        // Reset Alpine.js filter panel state
        const filterPanel = document.querySelector('[x-data]');
        if (filterPanel && filterPanel.__x) {
            filterPanel.__x.updateData({
                minPrice: null,
                maxPrice: null,
                selectedCategory: '',
                selectedSubcategory: '',
                isFilterOpen: false
            });
        }
        
        // Fetch all products (no filters)
        const apiUrl = '/api/product_api.php';
        window.fetchAndDisplayProducts(apiUrl);
    };

    // Reset button click handler
    if (titleResetButton) {
        titleResetButton.addEventListener('click', window.resetAllFilters);
    }

    // Category filter handler
    window.handleCategoryFilter = function(category, subcategory) {
        currentCategory = category || '';
        currentSubcategory = subcategory || '';
        
        // Update URL
        const urlParams = new URLSearchParams(window.location.search);
        if (currentCategory) urlParams.set('category', currentCategory);
        else urlParams.delete('category');
        if (currentSubcategory) urlParams.set('subcategory_slug', currentSubcategory);
        else urlParams.delete('subcategory_slug');
        
        const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
        history.pushState({}, '', newUrl);

        // Fetch products with new filters
        const apiUrl = `/api/product_api.php?${urlParams.toString()}`;
        window.fetchAndDisplayProducts(apiUrl);
    };

    // Function to display products
    function displayProducts(products) {
        if (!productGrid) return;

        if (products.length === 0) {
            const message = currentSearchTerm ? 
                `No products found matching "${currentSearchTerm}"` :
                'No products found matching your filters';
            
            showEmptyState(
                message,
                'Try adjusting your filters or search terms'
            );
            return;
        }

        productGrid.innerHTML = '';
        products.forEach(product => {
            // Prepare variables for the template
            const safeName = escapeHTML(product.name);
            const safeImage = escapeHTML(getProductThumbnail(product.image));
            const safeDesc = escapeHTML(product.description || '');
            const safeSlug = escapeHTML(product.slug || ''); // Assuming slug is available
            const safeCategoryName = escapeHTML(product.category_name || 'Uncategorized');
            const priceData = product.price;
            const stock = product.stock !== undefined ? product.stock : null; // Assuming stock is available
            const isActive = product.is_active !== undefined ? product.is_active : true; // Assuming is_active is available
            const backorder = product.backorder !== undefined ? product.backorder : false; // Assuming backorder is available
            const originalPrice = product.original_price; // Assuming original_price is available
            const discountPercentage = product.discount_percentage; // Assuming discount_percentage is available
            // Directly stringify options without HTML escaping for the data attribute
            const productOptionsJSON = JSON.stringify(product.options || {}); 
            
            // Generate Discount Badge HTML
            let discountBadgeHTML = '';
            if (discountPercentage && parseFloat(discountPercentage) > 0) {
                 discountBadgeHTML = `<span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-semibold px-2 py-0.5 rounded-full">${parseFloat(discountPercentage).toFixed(0)}%<span class="hidden md:inline"> OFF</span></span>`;
            }
            
            // Generate Status Badge HTML (Handles Inactive > Out of Stock > Backorder > In Stock)
            let statusBadgeHTML = '';
            if (!isActive) {
                // Should not happen anymore due to backend filtering, but kept as fallback
                statusBadgeHTML = `<span class="absolute top-2 left-2 bg-gray-700 text-white text-xs font-semibold px-2 py-0.5 rounded">Unavailable</span>`; 
            } else if (stock !== null && stock <= 0) {
                if (backorder) {
                    statusBadgeHTML = `<span class="absolute top-2 left-2 bg-yellow-500 text-white text-xs font-semibold px-2 py-0.5 rounded">Backorder</span>`;
                } else {
                    statusBadgeHTML = `<span class="absolute top-2 left-2 bg-gray-500 text-white text-xs font-semibold px-2 py-0.5 rounded">Out of Stock</span>`;
                }
            } else if (stock !== null && stock > 0) {
                 // Display stock count if active and in stock
                 statusBadgeHTML = `<span class="absolute top-2 left-2 bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded">${stock} in Stock</span>`;
            } // No badge if stock is null or undefined
            
            // Generate Price HTML (Always show the final price)
            let priceHTML = `<span class="product-price font-semibold text-gray-900">${currencySymbol}${formatNumberWithCommas(product.price)}</span>`;
            
            // Cart Status Placeholder & Icons (using Lucide)
            const isInCart = cart.isInCart(product.id); // Check if product is in cart
            const cartIconHTML = `<i data-lucide="shopping-cart" class="lucide-icon size-4"></i>`; // Lucide cart icon tag
            const checkIconHTML = `<i data-lucide="check" class="lucide-icon size-4 text-green-600"></i>`; // Lucide check icon tag (added green color)
            
            // The product card template literal
            const productCard = `
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
                     data-product-options='${productOptionsJSON}'> 
                    <div class="product-image-container relative h-56 bg-gray-200"> 
                        ${discountBadgeHTML} 
                        ${statusBadgeHTML}   
                        <img src="${safeImage}" alt="${safeName}" class="product-image w-full h-full object-cover"> 
                        <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black/50 to-transparent">
                             <h3 class="product-name font-semibold text-sm md:text-base text-white truncate pointer-events-none">${safeName}</h3> 
                        </div>
                    </div>
                    <div class="product-details p-2 flex flex-col flex-grow"> 
                         <div class="product-header flex justify-between items-center mt-1"> 
                             <span class="product-price text-gray-900">${priceHTML}</span>
                             <button type="button" 
                                     class="add-to-cart-icon-btn p-1.5 rounded-full ${isInCart ? 'text-green-500 bg-green-100 cursor-not-allowed' : 'text-gray-500 hover:text-primary hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary'} ml-auto transition-colors duration-200" 
                                     title="${isInCart ? 'Added to Cart' : 'Add to Cart'}"
                                     data-product-id="${product.id}" 
                                     data-product-name="${safeName}" 
                                     data-product-price="${priceData}" 
                                     data-product-image="${safeImage}" 
                                     data-product-options='${productOptionsJSON}'
                                     ${isInCart ? 'disabled' : ''}>
                                     ${isInCart ? checkIconHTML : cartIconHTML}
                                 </button>
                         </div>
                     </div>
                </div>`;

            productGrid.insertAdjacentHTML('beforeend', productCard);
        });
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

});