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
    if (searchTerm) {
        const pageTitle = document.querySelector('.max-w-7xl h2'); // Find the H2 title
        if (pageTitle) {
            pageTitle.textContent = `Search Results for "${searchTerm}"`;
        }
    }

    // Fetch products from the API
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                // Try to get error message from response body if available
                return response.text().then(text => {
                    throw new Error(`HTTP error! status: ${response.status}, message: ${text || 'No message'}`);
                });
            }
            return response.json();
        })
        .then(products => {
            loadingIndicator.remove(); // Remove loading indicator

            if (!Array.isArray(products)) {
                 console.error('Invalid data received from API:', products);
                 productGrid.innerHTML = '<p class="col-span-full text-red-600 text-center py-10">Error: Invalid data format received from server.</p>';
                 return;
            }

            if (products.length === 0) {
                productGrid.innerHTML = '<p class="col-span-full text-gray-500 text-center py-10">No products found.</p>';
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
                    productGrid.insertAdjacentHTML('beforeend', productCard); // More efficient than innerHTML +=
                });
                // Re-render Lucide icons if they were added dynamically
                 if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                 }
            }
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            if (loadingIndicator) loadingIndicator.remove(); // Ensure loading indicator is removed on error too
            productGrid.innerHTML = `<p class="col-span-full text-red-600 text-center py-10">Error loading products. Please try again later. (${escapeHTML(error.message)})</p>`;
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