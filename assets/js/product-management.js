document.addEventListener('DOMContentLoaded', function() {
    const productGrid = document.getElementById('product-grid');
    const loadingIndicator = document.getElementById('loading-products');
    // We need to get the currency symbol from the HTML, as PHP won't be available here.
    // Let's assume it's stored in a data attribute on the grid or passed via a global JS variable.
    // Alternative: Pass it via a data attribute on the script tag itself.
    const currencySymbolElement = document.getElementById('product-grid'); // Example: Reading from grid
    const currencySymbol = currencySymbolElement ? (currencySymbolElement.dataset.currencySymbol || '$') : '$';

    // Check if elements exist
    if (!productGrid || !loadingIndicator) {
        console.error('Required elements (product-grid or loading-products) not found.');
        if(loadingIndicator) loadingIndicator.textContent = 'Error: Page structure incorrect.';
        return; // Stop execution if essential elements are missing
    }

    // Store the original title
    const pageTitleElement = document.querySelector('.max-w-7xl h2'); // Find the H2 title
    const originalPageTitle = pageTitleElement ? pageTitleElement.textContent : 'Our Products';

    // Get category filter from URL query parameter 'category' (which is the PARENT slug)
    const urlParams = new URLSearchParams(window.location.search);
    const parentCategorySlug = urlParams.get('category'); 
    const searchTerm = urlParams.get('search'); // Get search term

    // Construct API URL
    let apiUrl = '/api/product_api.php';
    const queryParams = [];
    if (parentCategorySlug) {
        queryParams.push(`parent_category_slug=${encodeURIComponent(parentCategorySlug)}`); 
    }
    if (searchTerm) {
        queryParams.push(`search=${encodeURIComponent(searchTerm)}`);
    }
    if (queryParams.length > 0) {
        apiUrl += `?${queryParams.join('&')}`;
    }

    // Update page title if searching
    if (searchTerm && pageTitleElement) {
        pageTitleElement.textContent = `Search Results for "${searchTerm}"`;
    }

    // Function to fetch and display products - Make it global
    window.fetchAndDisplayProducts = function(url) { // Attach to window
        // Show loading indicator using skeleton cards
        if (productGrid) { // Ensure productGrid exists
            const skeletonCardHTML = `
                <div class="product-card bg-white rounded-lg shadow overflow-hidden animate-pulse flex flex-col w-full min-w-56">
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
            // Generate 8 skeleton cards
            let skeletonHTML = '';
            for (let i = 0; i < 8; i++) {
                skeletonHTML += skeletonCardHTML;
            }
            // Apply the skeleton loaders to the grid
            productGrid.innerHTML = skeletonHTML;
        }
        
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP error! status: ${response.status}, message: ${text || 'No message'}`);
                    });
                }
                return response.json();
            })
            .then(products => {
                // Note: The existing logic already removes the loading indicator/skeletons 
                // by setting productGrid.innerHTML before adding actual products or the 'No products found' message.
                // const currentLoadingIndicator = document.getElementById('loading-products'); // This ID is no longer used for loading
                // if (currentLoadingIndicator) currentLoadingIndicator.remove(); 

                if (!Array.isArray(products)) {
                     console.error('Invalid data received from API:', products);
                     productGrid.innerHTML = '<p class="col-span-full text-red-600 text-center py-10">Error: Invalid data format received from server.</p>';
                     return;
                }

                if (products.length === 0) {
                     productGrid.innerHTML = `
                        <div class="col-span-full text-center py-16 px-6 bg-gray-50 rounded-lg border border-gray-200">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-2 text-xl font-semibold text-gray-800">No Products Found</h3>
                            <p class="mt-1 text-sm text-gray-500">We couldn't find any products matching your criteria. Try adjusting your search or filters.</p>
                            <div class="mt-6">
                                <button id="reset-products-btn" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 hover:bg-blue-50 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <i data-lucide="refresh-cw" class="mr-1.5 h-4 w-4"></i>
                                    Clear search
                                </button>
                            </div>
                        </div>
                    `;
                    // Render Lucide icon for the button
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                } else {
                    productGrid.innerHTML = ''; // Clear the grid before adding products
                    products.forEach(product => {
                        const productCard = `
                            <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition duration-300 hover:shadow-xl hover:-translate-y-1">
                                <a href="/product_detail.php?id=${product.id}" class="block">
                                    <img src="${escapeHTML(product.image || '/assets/images/product-placeholder.png')}" alt="${escapeHTML(product.name)}" class="w-full h-48 object-cover">
                                </a>
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2 truncate" title="${escapeHTML(product.name)}">
                                        <a href="/product_detail.php?id=${product.id}" class="hover:text-blue-600">${escapeHTML(product.name)}</a>
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-3">${escapeHTML(product.category_name || 'Uncategorized')}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xl font-bold text-gray-900">${currencySymbol}${parseFloat(product.price).toFixed(2)}</span>
                                        <button 
                                            class="add-to-cart-btn bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full transition duration-200"
                                            data-product-id="${product.id}"
                                            data-product-name="${escapeHTML(product.name)}"
                                            data-product-price="${product.price}"
                                            data-product-image="${escapeHTML(product.image)}"
                                            aria-label="Add ${escapeHTML(product.name)} to cart">
                                            <i data-lucide="shopping-cart" class="h-5 w-5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                        productGrid.insertAdjacentHTML('beforeend', productCard);
                    });
                     if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                     }
                }
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                // Note: The existing logic already clears the grid/skeletons on error.
                // const currentLoadingIndicator = document.getElementById('loading-products'); // This ID is no longer used
                // if (currentLoadingIndicator) currentLoadingIndicator.remove();
                productGrid.innerHTML = `<p class="col-span-full text-red-600 text-center py-10">Error loading products. Please try again later. (${escapeHTML(error.message)})</p>`;
            });
    }

    // Initial fetch
    fetchAndDisplayProducts(apiUrl);

    // Event listener for the reset button (using delegation)
    productGrid.addEventListener('click', function(event) {
        const resetButton = event.target.closest('#reset-products-btn');
        if (resetButton) {
            // Restore original title
            if (pageTitleElement) {
                pageTitleElement.textContent = originalPageTitle;
            }
            // Clear URL query parameters
            history.pushState({}, '', window.location.pathname);
            // Fetch all products
            window.fetchAndDisplayProducts('/api/product_api.php'); // Use global function
        }
    });

    // Basic HTML escaping function
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Add to Cart functionality delegation (if needed)
    // Ensure your main.js or similar handles clicks on .add-to-cart-btn
    // If not, uncomment and implement this:
    // productGrid.addEventListener('click', function(event) {
    //     const button = event.target.closest('.add-to-cart-btn');
    //     if (button) {
    //         console.log('Add to cart clicked:', button.dataset.productId);
    //         // Implement actual add to cart logic here
    //     }
    // });
}); 