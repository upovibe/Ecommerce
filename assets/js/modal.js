/**
 * Modal Interaction Logic
 */
document.addEventListener('DOMContentLoaded', () => {
    const productModal = document.getElementById('productModal');
    const productModalOverlay = document.getElementById('productModalOverlay');
    const productModalPanel = document.getElementById('productModalPanel');
    const closeProductModalButton = document.getElementById('closeProductModalButton');
    const productModalCloseButtonFooter = document.getElementById('productModalCloseButtonFooter');
    const productModalAddToCartButton = document.getElementById('productModalAddToCartButton');

    // Modal elements to populate
    const modalImage = document.getElementById('productModalImage');
    const modalName = document.getElementById('productModalName');
    const modalPriceDisplay = document.getElementById('productModalPriceDisplay');
    const modalDescription = document.getElementById('productModalDescription');
    const modalOptionsContainer = document.getElementById('productModalOptionsContainer');
    const modalSlugText = document.getElementById('productModalSlugText');
    const modalSlugDisplay = document.getElementById('productModalSlugDisplay');
    const modalCopyLinkButton = document.getElementById('productModalCopyLinkButton');
    const modalCopyFeedback = document.getElementById('productModalCopyFeedback');
    // New elements
    const modalCategory = document.getElementById('productModalCategory');
    const modalAvailability = document.getElementById('productModalAvailability');
    const modalDiscountBadge = document.getElementById('productModalDiscountBadge'); // Assumes an ID for the badge span

    // --- Cart Modal Elements ---
    const cartModal = document.getElementById('cartModal');
    const cartModalOverlay = document.getElementById('cartModalOverlay');
    const cartModalPanel = document.getElementById('cartModalPanel');
    const closeCartModalButton = document.getElementById('closeCartModalButton');
    const cartModalItemsContainer = document.getElementById('cartModalItemsContainer');
    const cartModalSubtotal = document.getElementById('cartModalSubtotal');
    const cartModalProceedButton = document.getElementById('cartModalProceedButton');
    const cartModalEmptyMsg = document.getElementById('cartModalEmptyMsg');
    const cartModalLoadingMsg = document.getElementById('cartModalLoadingMsg');

    // --- Header Button Elements ---
    const headerCartButton = document.getElementById('cartButton'); // Desktop
    const mobileCartIconButton = document.getElementById('mobileCartIcon'); // New ID (icon button in header bar)

    let currentProductData = null; // Store data for Add to Cart
    let copyTimeout = null; // Timeout for copy feedback

    // --- Function to Open Product Modal ---
    window.openProductModal = function(productId, productName, productPrice, productImage, productDescription, productOptions, productSlug,
                                     categoryName, stock, isActive, backorder, originalPrice, discountPercentage) {
        if (!productModal) return;

        // Store data for later use
        currentProductData = { 
            id: productId, 
            name: productName, 
            price: parseFloat(productPrice || 0),
            original_price: originalPrice ? parseFloat(originalPrice) : null, 
            discount_percentage: discountPercentage ? parseFloat(discountPercentage) : null,
            image: productImage, 
            slug: productSlug || '', 
            options: productOptions || {},
            category_name: categoryName || 'Uncategorized',
            stock: parseInt(stock || 0),
            is_active: isActive,
            backorder: backorder
        };

        // Populate Modal Content
        if (modalName) modalName.textContent = productName || 'Product';
        if (modalImage) {
            modalImage.src = productImage || '../assets/images/placeholder.png';
            modalImage.alt = productName || 'Product Image';
        }
        
        // Price Display (Handle original price & discount badge)
        if (modalPriceDisplay) {
            const price = currentProductData.price;
            const origPrice = currentProductData.original_price;
            const discount = currentProductData.discount_percentage;
            let priceHTML = '';
            const format = (num) => typeof formatCurrency === 'function' ? formatCurrency(num) : `₦${num.toFixed(2)}`;

            if (origPrice && !isNaN(origPrice) && origPrice > price) {
                 priceHTML = `
                    <span class="text-base text-gray-500 line-through mr-2">${format(origPrice)}</span>
                    <span class="text-red-600">${format(price)}</span>
                 `;
            } else {
                priceHTML = `<span>${format(price)}</span>`;
            }
            modalPriceDisplay.innerHTML = priceHTML;
            
            // Discount Badge (Assuming badge element exists)
             if (modalDiscountBadge) {
                if (discount && !isNaN(discount) && discount > 0) {
                    modalDiscountBadge.textContent = `-${discount.toFixed(0)}%`;
                    modalDiscountBadge.classList.remove('hidden');
                } else {
                    modalDiscountBadge.classList.add('hidden');
                }
            }
        }
        
        // Category
        if (modalCategory) {
            modalCategory.textContent = currentProductData.category_name;
        }
        
        // Availability Status
        if (modalAvailability) {
            let statusText = '';
            let statusClasses = '';
            if (!currentProductData.is_active) {
                statusText = 'Inactive';
                statusClasses = 'bg-gray-100 text-gray-700';
            } else if (currentProductData.stock > 0) {
                statusText = `${currentProductData.stock} In Stock`;
                statusClasses = 'bg-green-100 text-green-800';
            } else if (currentProductData.backorder) {
                statusText = 'Backorder';
                statusClasses = 'bg-yellow-100 text-yellow-800';
            } else {
                statusText = 'Sold Out';
                statusClasses = 'bg-red-100 text-red-800';
            }
            modalAvailability.textContent = statusText;
            // Reset classes and apply new ones
            modalAvailability.className = 'px-2.5 py-1 rounded-full text-xs font-medium '; // Base classes
            modalAvailability.classList.add(...statusClasses.split(' '));
        }

        if (modalDescription) {
            modalDescription.innerHTML = productDescription || '<p>No description available.</p>'; 
        }
        
        // Slug & Copy Link
        if (modalSlugText && modalSlugDisplay && modalCopyLinkButton) {
            if (currentProductData.slug) {
                modalSlugText.textContent = currentProductData.slug;
                modalSlugDisplay.classList.remove('hidden');
                modalCopyLinkButton.classList.remove('hidden');
            } else {
                modalSlugDisplay.classList.add('hidden');
                modalCopyLinkButton.classList.add('hidden');
            }
        }
         if (modalCopyFeedback) modalCopyFeedback.classList.add('hidden'); // Hide feedback initially

        // Populate Options
        if (modalOptionsContainer) {
            modalOptionsContainer.innerHTML = ''; // Clear previous options
            const options = productOptions || {};
            const optionKeys = Object.keys(options);

            if (optionKeys.length > 0) {
                modalOptionsContainer.classList.remove('hidden');
                optionKeys.forEach((optionName, index) => {
                    const optionValues = options[optionName];
                    if (Array.isArray(optionValues) && optionValues.length > 0) {
                        const optionIdPrefix = `product-option-${index}`;
                        let optionHTML = `<div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-1.5">${escapeHTML(optionName)}</label>
                                            <select id="${optionIdPrefix}" name="options[${escapeHTML(optionName)}]" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border">
                                        `;
                        optionValues.forEach(value => {
                            optionHTML += `<option value="${escapeHTML(value)}">${escapeHTML(value)}</option>`;
                        });
                        optionHTML += `   </select>
                                      </div>`;
                        modalOptionsContainer.insertAdjacentHTML('beforeend', optionHTML);
                    }
                });
            } else {
                modalOptionsContainer.classList.add('hidden');
            }
        }
        
        // Update Add to Cart button's product ID
        if(productModalAddToCartButton) {
            productModalAddToCartButton.dataset.productId = productId;
        }

        // Show Modal with transitions
        productModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            if(productModalOverlay) productModalOverlay.classList.replace('opacity-0', 'opacity-100');
            if(productModalPanel) productModalPanel.classList.replace('opacity-0', 'opacity-100');
            if(productModalPanel) productModalPanel.classList.replace('translate-y-4', 'translate-y-0');
            if(productModalPanel) productModalPanel.classList.replace('sm:scale-95', 'sm:scale-100');
        });
    }

    // --- Function to Close Product Modal ---
    window.closeProductModal = function() {
        if (!productModal || productModal.classList.contains('hidden')) return;

        // Start fade-out transitions
         if(productModalOverlay) productModalOverlay.classList.replace('opacity-100', 'opacity-0');
         if(productModalPanel) productModalPanel.classList.replace('opacity-100', 'opacity-0');
         if(productModalPanel) productModalPanel.classList.replace('translate-y-0', 'translate-y-4');
         if(productModalPanel) productModalPanel.classList.replace('sm:scale-100', 'sm:scale-95');

        // Hide modal after transition duration (e.g., 300ms)
        setTimeout(() => {
            productModal.classList.add('hidden');
            currentProductData = null; // Clear stored data
        }, 300); 
    }

    // --- Event Listeners ---
    if (closeProductModalButton) {
        closeProductModalButton.addEventListener('click', closeProductModal);
    }
    if (productModalCloseButtonFooter) {
        productModalCloseButtonFooter.addEventListener('click', closeProductModal);
    }
    if (productModalOverlay) {
        productModalOverlay.addEventListener('click', closeProductModal);
    }
    // Close on Escape key
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && productModal && !productModal.classList.contains('hidden')) {
            closeProductModal();
        }
    });

    // Copy Link Button Listener
    if (modalCopyLinkButton) {
        modalCopyLinkButton.addEventListener('click', () => {
            if (!currentProductData || !currentProductData.slug) return;

            // Construct the URL (adjust path and parameter as needed)
            const productUrl = `${window.location.origin}/pages/product.php?slug=${currentProductData.slug}`;

            navigator.clipboard.writeText(productUrl).then(() => {
                // Success feedback
                if (modalCopyFeedback) {
                    modalCopyFeedback.classList.remove('hidden');
                    // Clear previous timeout if exists
                    if (copyTimeout) clearTimeout(copyTimeout);
                    // Hide feedback after 2 seconds
                    copyTimeout = setTimeout(() => {
                         modalCopyFeedback.classList.add('hidden');
                    }, 2000);
                }
            }).catch(err => {
                console.error('Failed to copy link: ', err);
                // Optional: Show an error message to the user
                alert('Failed to copy link.'); 
            });
        });
    }

    // Add to Cart Button Listener
    if (productModalAddToCartButton) {
        productModalAddToCartButton.addEventListener('click', () => {
            if (!currentProductData) {
                console.error("No product data available for adding to cart.");
                return;
            }

            const productId = currentProductData.id;
            const quantity = 1; // Always add quantity 1 from modal
            
            // Collect selected options
            let selectedOptions = {};
            if (modalOptionsContainer) {
                const selects = modalOptionsContainer.querySelectorAll('select');
                selects.forEach(select => {
                    const optionName = select.name.match(/options\[(.*?)\]/)[1]; // Extract name from options[Name]
                    if (optionName) {
                        selectedOptions[optionName] = select.value;
                    }
                });
            }

            console.log('Adding to cart from modal:', productId, quantity, selectedOptions);

            // Call global addToCart function (from cart.js)
            if (typeof addToCart === 'function') {
                // Pass selected options along with other details
                addToCart(
                    productId,
                    quantity,
                    currentProductData.name,
                    currentProductData.price,
                    currentProductData.image,
                    selectedOptions // Pass the collected options
                );
                
                // Optionally provide feedback (e.g., change button text briefly)
                const originalText = productModalAddToCartButton.innerHTML;
                productModalAddToCartButton.innerHTML = 
                    `<svg class="w-5 h-5 mr-1 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Adding...`;
                productModalAddToCartButton.disabled = true;

                setTimeout(() => {
                    productModalAddToCartButton.innerHTML = 
                         `<svg class="w-5 h-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>Added!`;
                    setTimeout(() => {
                         productModalAddToCartButton.innerHTML = originalText;
                         productModalAddToCartButton.disabled = false;
                         closeProductModal(); // Close modal after adding
                    }, 1200); // Reset button text and close
                }, 800); // Show 'Added!' message

            } else {
                console.error("addToCart function is not defined.");
                alert("Error: Could not add item to cart.");
            }
        });
    }

    // Helper to escape HTML (if not already global)
    function escapeHTML(str) {
        if (typeof str !== 'string') return '';
        const p = document.createElement('p');
        p.textContent = str;
        return p.innerHTML;
    }

    // ======================================
    //          CART MODAL LOGIC
    // ======================================

    // --- Function to Open Cart Modal ---
    window.openCartModal = function() {
        if (!cartModal) return;
        console.log("Opening Cart Modal");
        updateCartModal(); // Populate with current cart items

        // Show Modal with transitions
        cartModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            if(cartModalOverlay) cartModalOverlay.classList.replace('opacity-0', 'opacity-100');
            if(cartModalPanel) cartModalPanel.classList.replace('opacity-0', 'opacity-100');
            if(cartModalPanel) cartModalPanel.classList.replace('translate-y-4', 'translate-y-0');
            if(cartModalPanel) cartModalPanel.classList.replace('sm:scale-95', 'sm:scale-100');
        });
    }

    // --- Function to Close Cart Modal ---
    window.closeCartModal = function() {
        if (!cartModal || cartModal.classList.contains('hidden')) return;

        // Start fade-out transitions
         if(cartModalOverlay) cartModalOverlay.classList.replace('opacity-100', 'opacity-0');
         if(cartModalPanel) cartModalPanel.classList.replace('opacity-100', 'opacity-0');
         if(cartModalPanel) cartModalPanel.classList.replace('translate-y-0', 'translate-y-4');
         if(cartModalPanel) cartModalPanel.classList.replace('sm:scale-100', 'sm:scale-95');

        // Hide modal after transition duration (e.g., 300ms)
        setTimeout(() => {
            cartModal.classList.add('hidden');
        }, 300); 
    }

    // --- Function to Update Cart Modal Content ---
    window.updateCartModal = function() {
        // Ensure elements exist
        if (!cartModalItemsContainer || !cartModalSubtotal || !cartModalProceedButton || !cartModalEmptyMsg || !cartModalLoadingMsg) {
            console.warn("Cart modal elements not found, cannot update.");
            return; 
        }

        // Ensure functions exist
        if (typeof getCartItems !== 'function' || typeof formatCurrency !== 'function') {
            console.error("Required functions (getCartItems, formatCurrency) not found.");
            cartModalItemsContainer.innerHTML = '<p class="text-red-500 p-4">Error loading cart data.</p>';
            cartModalLoadingMsg.style.display = 'none';
            cartModalEmptyMsg.style.display = 'none';
            if (cartModalProceedButton) cartModalProceedButton.disabled = true;
            return;
        }

        // Hide messages initially using direct styles
        cartModalLoadingMsg.style.display = 'none';
        cartModalEmptyMsg.style.display = 'none';
        cartModalItemsContainer.innerHTML = ''; 
        let subtotal = 0;

        const cartItems = getCartItems();
        console.log("Updating Cart Modal. Item Count:", cartItems.length); 

        if (cartItems.length === 0) {
            console.log("Cart is empty, showing empty message."); 
            cartModalItemsContainer.innerHTML = ''; 
            cartModalEmptyMsg.style.display = 'block'; 
            if (cartModalProceedButton) cartModalProceedButton.disabled = true;
        } else {
            console.log("Cart has items, showing items."); 
            cartModalEmptyMsg.style.display = 'none'; 
            cartItems.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                const trashIconSVG = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>`;
                const itemHTML = `
                    <div class="flex items-center py-4" data-cart-item-id="${item.id}">
                        <img src="${escapeHTML(item.image || '../assets/images/placeholder.png')}" alt="${escapeHTML(item.name)}" class="h-16 w-16 rounded object-cover mr-4 flex-shrink-0">
                        <div class="flex-grow">
                            <h4 class="font-medium text-gray-900 text-sm mb-1">${escapeHTML(item.name)}</h4>
                            <!-- Optional: Display selected options -->
                            ${Object.entries(item.options || {}).map(([key, value]) => `<p class="text-xs text-gray-500">${escapeHTML(key)}: ${escapeHTML(value)}</p>`).join('')}
                             <!-- Quantity Controls -->
                             <div class="flex items-center mt-2 text-sm">
                                <button type="button" aria-label="Decrease quantity" class="cart-quantity-btn cart-decrease-btn p-1 border rounded-md text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed" data-product-id="${item.id}" ${item.quantity <= 1 ? 'disabled' : ''}>
                                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                </button>
                                <span class="px-3 font-medium">${item.quantity}</span>
                                <button type="button" aria-label="Increase quantity" class="cart-quantity-btn cart-increase-btn p-1 border rounded-md text-gray-600 hover:bg-gray-100" data-product-id="${item.id}">
                                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                </button>
                            </div>
                        </div>
                        <div class="text-right ml-4 flex flex-col items-end justify-between">
                            <p class="font-medium text-gray-900 text-sm mb-1">${formatCurrency(itemTotal)}</p>
                             <button type="button" aria-label="Remove item" class="p-1 rounded-md text-red-600 hover:bg-red-100 mt-auto cart-remove-item-btn" data-product-id="${item.id}">
                                ${trashIconSVG}
                             </button>
                        </div>
                    </div>
                `;
                cartModalItemsContainer.insertAdjacentHTML('beforeend', itemHTML);
            });
             if (cartModalProceedButton) cartModalProceedButton.disabled = false;
        }

        cartModalSubtotal.textContent = formatCurrency(subtotal);
    }

    // ======================================
    //          EVENT LISTENERS
    // ======================================

    // --- Product Modal Listeners (Existing) ---
    if (closeProductModalButton) {
        closeProductModalButton.addEventListener('click', closeProductModal);
    }
    if (productModalCloseButtonFooter) {
        productModalCloseButtonFooter.addEventListener('click', closeProductModal);
    }
    if (productModalOverlay) {
        productModalOverlay.addEventListener('click', closeProductModal);
    }
    // Close on Escape key (shared handler)
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && cartModal && !cartModal.classList.contains('hidden')) {
            closeCartModal();
        }
    });

    // --- Cart Modal Listeners ---
    if (headerCartButton) {
        headerCartButton.addEventListener('click', openCartModal);
    }
    if (mobileCartIconButton) {
        mobileCartIconButton.addEventListener('click', openCartModal);
    }
    if (closeCartModalButton) {
        closeCartModalButton.addEventListener('click', closeCartModal);
    }
    if (cartModalOverlay) {
        cartModalOverlay.addEventListener('click', closeCartModal);
    }
    // Remove item listener (using event delegation)
    if (cartModalItemsContainer) {
        cartModalItemsContainer.addEventListener('click', (event) => {
            const target = event.target;
            const button = target.closest('.cart-quantity-btn, .cart-remove-item-btn'); // Check for quantity or remove buttons
            
            if (!button) return; // Exit if click wasn't on a relevant button
            
            const productId = button.dataset.productId;
            if (!productId) return;

            if (button.classList.contains('cart-remove-item-btn')) {
                 if (typeof removeFromCart === 'function') {
                    console.log("Removing item from cart modal:", productId);
                     removeFromCart(productId);
                } else {
                    console.error('removeFromCart function missing.');
                 }
            } else if (button.classList.contains('cart-increase-btn')) {
                if (typeof increaseCartItemQuantity === 'function') {
                     console.log("Increasing quantity from cart modal:", productId);
                    increaseCartItemQuantity(productId);
                } else {
                     console.error('increaseCartItemQuantity function missing.');
                 }
            } else if (button.classList.contains('cart-decrease-btn')) {
                 if (typeof decreaseCartItemQuantity === 'function') {
                     console.log("Decreasing quantity from cart modal:", productId);
                     decreaseCartItemQuantity(productId);
                 } else {
                     console.error('decreaseCartItemQuantity function missing.');
                 }
            }
        });
    }

     // Proceed button listener (Replaces Checkout)
     if (cartModalProceedButton) {
         cartModalProceedButton.addEventListener('click', () => {
             alert('Proceeding! (Next step TBD)');
             // TODO: Implement redirect to checkout page or order placement page
             closeCartModal();
         });
     }
}); 