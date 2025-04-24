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
    let subcategorySwiperInstance = null; // Variable to hold the Swiper instance

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

    // --- Product Fetching and Display ---
    window.fetchAndDisplayProducts = function(url) { 
        if (!productGrid) return; // Exit if grid doesn't exist
        
        // Use skeleton loader - SIMPLIFIED DESIGN
        const skeletonCardHTML = `
            <div class="product-card bg-white rounded-lg shadow overflow-hidden animate-pulse transition-shadow duration-300 hover:shadow-lg flex flex-col cursor-pointer  w-full">
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
        `;
        let skeletonHTML = '';
        loadingIndicator?.remove(); // Remove static loader if present
        for (let i = 0; i < 8; i++) skeletonHTML += skeletonCardHTML;
        productGrid.innerHTML = skeletonHTML;

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(`HTTP error! status: ${response.status}, message: ${text || 'No message'}`); });
                }
                return response.json();
            })
            .then(products => {
                if (!productGrid) return;
                productGrid.innerHTML = ''; // Clear skeletons

                if (!Array.isArray(products)) {
                     console.error('Invalid data received from API:', products);
                     productGrid.innerHTML = '<p class="col-span-full text-red-600 text-center py-10">Error: Invalid data format received from server.</p>';
                     return;
                }

                if (products.length === 0) {
                     productGrid.innerHTML = `
                        <div class="col-span-full text-center py-16 px-6 bg-gray-50 rounded-lg border border-gray-200">
                            <i data-lucide="frown" class="mx-auto h-12 w-12 text-gray-400"></i>
                            <h3 class="mt-2 text-xl font-semibold text-gray-800">No Products Found</h3>
                            <p class="mt-1 text-sm text-gray-500">We couldn't find any products matching your current filters or search term.</p>
                            <div class="mt-6">
                                <button id="reset-products-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <i data-lucide="refresh-cw" class="mr-1.5 h-4 w-4"></i> Clear Filters / Search
                                </button>
                            </div>
                        </div>
                    `;
                     if (typeof lucide !== 'undefined') lucide.createIcons();
                } else {
                    products.forEach(product => {
                        // Prepare variables for the template
                        const safeName = escapeHTML(product.name);
                        const safeImage = escapeHTML(product.image || '/assets/images/product-placeholder.png');
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
                        
                        // Generate Price HTML (Handles discounts)
                        let priceHTML = `<span class="product-price font-semibold text-gray-900">${currencySymbol}${formatNumberWithCommas(product.price)}</span>`;
                        if (originalPrice && parseFloat(originalPrice) > parseFloat(product.price)) {
                            priceHTML = `
                                <div class="price-container flex flex-col md:flex-row items-baseline gap-0 md:gap-1">
                                    <span class="product-price text-base md:text-lg font-bold text-red-600">${currencySymbol}${formatNumberWithCommas(product.price)}</span>
                                    <span class="product-original-price text-sm font-semibold text-gray-500 line-through">${currencySymbol}${formatNumberWithCommas(originalPrice)}</span>
                                </div>
                            `;
                        }
                        
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
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                if (productGrid) productGrid.innerHTML = `<p class="col-span-full text-red-600 text-center py-10">Error loading products. Please try again later. (${escapeHTML(error.message)})</p>`;
            });
    }

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
                if (!swiperWrapper) return; // Should not happen

                // 3. Add slides with buttons
                data.subcategories.forEach(sub => {
                    const slide = document.createElement('div');
                    slide.className = 'swiper-slide !w-auto'; // Important: !w-auto for auto width
                    
                    const subLink = document.createElement('button');
                    subLink.className = 'subcategory-link block px-3 py-1 text-sm rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 hover:border-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-colors';
                    subLink.textContent = sub.name;
                    subLink.dataset.subcategorySlug = sub.slug;
                    
                    slide.appendChild(subLink);
                    swiperWrapper.appendChild(slide);
                });

                // 4. Initialize Swiper
                subcategorySwiperInstance = new Swiper('.subcategory-swiper', {
                    slidesPerView: 'auto',
                    spaceBetween: 8,
                    loop: true,
                    centeredSlides: false,
                    slidesOffsetBefore: 0,
                    // navigation: { // Removed navigation configuration
                    //     nextEl: '.subcategory-swiper-button-next',
                    //     prevEl: '.subcategory-swiper-button-prev',
                    // },
                    // freeMode: true, // Optional: Add freeMode for smoother scrolling
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
        const { category = 'all', subcategory_slug = null, search = null } = params;
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

        // Build parameters for API call (using 'parent_category_slug')
        if (parentSlugForApi) apiParams.push(`parent_category_slug=${encodeURIComponent(parentSlugForApi)}`);
        if (subcategory_slug) apiParams.push(`subcategory_slug=${encodeURIComponent(subcategory_slug)}`);
        if (search) apiParams.push(`search=${encodeURIComponent(search)}`);
        
        // Construct URLs
        let newUrl = window.location.pathname + (queryParams.length > 0 ? `?${queryParams.join('&')}` : '');
        let apiUrl = '/api/product_api.php' + (apiParams.length > 0 ? `?${apiParams.join('&')}` : '');
        
        // Update Browser History and Title
        // Use replaceState for back/forward nav to work better with filters
        history.replaceState(params, pageTitle, newUrl);
        if (pageTitleElement) pageTitleElement.textContent = pageTitle;
        document.title = pageTitle; // Update actual document title

        // Show/Hide Title Reset Button
        if (titleResetButton) {
            const filtersActive = (category && category !== 'all') || subcategory_slug || search;
            titleResetButton.classList.toggle('hidden', !filtersActive);
            titleResetButton.classList.toggle('flex', filtersActive); // Use inline-flex to show
        }

        // Fetch Products
        console.log('[updateProductView] Fetching products with API URL:', apiUrl);
        window.fetchAndDisplayProducts(apiUrl);
        
        // Update active states after fetching products
        if (parentTabsContainer) {
             const activeParentTab = parentTabsContainer.querySelector(`.category-tab[data-category-slug="${category}"]`);
             setActiveClass(parentTabsContainer, activeParentTab, 'active');
        }
         // Ensure subcategory active state is correct AFTER products load
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
        
        let initialParams = { category, subcategory_slug: subSlug, search };
        Object.keys(initialParams).forEach(key => initialParams[key] == null && delete initialParams[key]);
        if (!initialParams.category) initialParams.category = 'all';

        console.log("Initial Params:", initialParams);

        // Fetch initial subcategories based on URL
        fetchAndDisplaySubcategories(category === 'all' ? null : category).then(() => {
            console.log("Subcategories fetched/displayed for initial load.");
            // Once subcategories are rendered, update the main view
            // This call will also set the active classes correctly
            updateProductView(initialParams);
        });
    }

    initializeProductView(); // Run initial setup

    // --- Auto Refresh Logic --- 
    function autoRefreshProducts() {
        console.log('[Auto Refresh] Checking for updates...');
        // Construct the API URL based on current browser URL params
        const currentParams = new URLSearchParams(window.location.search);
        const apiParams = [];
        const parentSlug = currentParams.get('category');
        const subSlug = currentParams.get('subcategory_slug');
        const search = currentParams.get('search');

        // Build API params similarly to updateProductView
        if (parentSlug && parentSlug !== 'all') apiParams.push(`parent_category_slug=${encodeURIComponent(parentSlug)}`);
        if (subSlug) apiParams.push(`subcategory_slug=${encodeURIComponent(subSlug)}`);
        if (search) apiParams.push(`search=${encodeURIComponent(search)}`);

        let apiUrl = '/api/product_api.php' + (apiParams.length > 0 ? `?${apiParams.join('&')}` : '');
        console.log('[Auto Refresh] Fetching with API URL:', apiUrl);

        // Fetch and display, but DO NOT update history or title
        window.fetchAndDisplayProducts(apiUrl);
    }

    // Set interval to run the refresh function every 30 seconds
    setInterval(autoRefreshProducts, 30000); 

}); 