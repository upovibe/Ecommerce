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

    // Define Lucide Icons used in this script
    const modalAddIconHTML = `<i data-lucide="plus" class="w-4 h-4 mr-1"></i>`;
    const modalCheckIconHTML = `<i data-lucide="check" class="w-5 h-5 mr-1"></i>`;

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
    const cartModalClearButton = document.getElementById('cartModalClearButton'); // Added Clear Cart Button

    // --- Complete Order Modal Elements ---
    const completeOrderModal = document.getElementById('completeOrderModal');
    const completeOrderModalOverlay = document.getElementById('completeOrderModalOverlay');
    const completeOrderModalPanel = document.getElementById('completeOrderModalPanel');
    const closeCompleteOrderModalButton = document.getElementById('closeCompleteOrderModalButton');
    const cancelCompleteOrderButton = document.getElementById('cancelCompleteOrderButton'); // Added cancel button

    // --- Complete Order Modal Form Elements ---
    const deliveryMethodRadios = document.querySelectorAll('input[name="deliveryMethod"]');
    const recipientTypeRadios = document.querySelectorAll('input[name="recipientType"]');
    const myselfFields = document.getElementById('myselfFields');
    const senderFieldsContainer = document.getElementById('senderFieldsContainer'); // New container for sender info
    const recipientAccordionContainer = document.getElementById('recipientAccordionContainer'); // New accordion container
    // Input fields that need requirement toggling
    const customerAddressInput = document.getElementById('customerAddress'); // Now handled by Alpine :required
    const receiverAddressInput = document.getElementById('receiverAddress'); // Now handled by Alpine :required
    const primaryDetailsTitle = document.getElementById('primaryDetailsTitle'); // Title span for first accordion
    const senderFields = senderFieldsContainer ? senderFieldsContainer.querySelectorAll('input[id^="sender"]') : [];
    const receiverFields = recipientAccordionContainer ? recipientAccordionContainer.querySelectorAll('input[id^="receiver"], textarea[id^="receiver"]') : [];
    const customerFields = myselfFields ? myselfFields.querySelectorAll('input[id^="customer"], textarea[id^="customer"]') : [];

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
            const format = (num) => typeof formatCurrency === 'function' ? formatCurrency(num) : `₵${num.toFixed(2)}`;

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
        
        // Update Add to Cart button's product ID and initial state
        if(productModalAddToCartButton) {
            productModalAddToCartButton.dataset.productId = productId;

            let isInCart = false;
            if (typeof getCartItems === 'function') {
                const cartItems = getCartItems(); 
                // Simple check by ID. Refine if options need to be checked.
                isInCart = cartItems.some(item => item.id == productId); 
            } else {
                console.warn('getCartItems function not found for initial modal button check.');
            }

            if (isInCart) {
                 // Set to "Added" state
                 productModalAddToCartButton.disabled = true;
                 productModalAddToCartButton.innerHTML = `${modalCheckIconHTML} Added to Cart`;
                 productModalAddToCartButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                 productModalAddToCartButton.classList.add('bg-green-600', 'hover:bg-green-700', 'cursor-not-allowed');
            } else {
                 // Reset to default "Add" state
                 productModalAddToCartButton.disabled = false;
                 productModalAddToCartButton.innerHTML = `${modalAddIconHTML} Add to Cart`;
                 productModalAddToCartButton.classList.remove('bg-green-600', 'hover:bg-green-700', 'cursor-not-allowed');
                 productModalAddToCartButton.classList.add('bg-blue-600', 'hover:bg-blue-700');
            }
            // Render the icon in the button
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
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
                
                // --- Update button to permanent "Added" state --- 
                productModalAddToCartButton.disabled = true;
                productModalAddToCartButton.innerHTML = `${modalCheckIconHTML} Added to Cart`;
                productModalAddToCartButton.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                productModalAddToCartButton.classList.add('bg-green-600', 'hover:bg-green-700', 'cursor-not-allowed');
                // Render the icon in the button
                if (typeof lucide !== 'undefined') {
                    lucide.createIcons();
                }
                
                 // --- REMOVED previous temporary feedback logic ---

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
        if (!cartModalItemsContainer || !cartModalSubtotal || !cartModalProceedButton || !cartModalEmptyMsg || !cartModalLoadingMsg || !cartModalClearButton) {
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
            if (cartModalClearButton) cartModalClearButton.disabled = true;
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
            if (cartModalClearButton) cartModalClearButton.disabled = true; // Disable clear button when empty
        } else {
            console.log("Cart has items, showing items."); 
            cartModalEmptyMsg.style.display = 'none'; 
            cartItems.forEach(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                const trashIconHTML = `<i data-lucide="trash-2" class="w-4 h-4"></i>`;
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
                                     <i data-lucide="minus" class="w-3 h-3"></i>
                                </button>
                                <span class="px-3 font-medium">${item.quantity}</span>
                                <button type="button" aria-label="Increase quantity" class="cart-quantity-btn cart-increase-btn p-1 border rounded-md text-gray-600 hover:bg-gray-100" data-product-id="${item.id}">
                                     <i data-lucide="plus" class="w-3 h-3"></i>
                                </button>
                            </div>
                        </div>
                        <div class="text-right ml-4 flex flex-col items-end justify-between">
                            <p class="font-medium text-gray-900 text-sm mb-1">${formatCurrency(itemTotal)}</p>
                             <button type="button" aria-label="Remove item" class="p-1 rounded-md text-red-600 hover:bg-red-100 mt-auto cart-remove-item-btn" data-product-id="${item.id}">
                                ${trashIconHTML}
                             </button>
                        </div>
                    </div>
                `;
                cartModalItemsContainer.insertAdjacentHTML('beforeend', itemHTML);
            });
            // Render icons in the cart items
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
             if (cartModalProceedButton) cartModalProceedButton.disabled = false;
             if (cartModalClearButton) cartModalClearButton.disabled = false; // Enable clear button when not empty
        }

        cartModalSubtotal.textContent = formatCurrency(subtotal);
    }

    // ======================================
    //          COMPLETE ORDER MODAL LOGIC
    // ======================================

    // --- Function to Open Complete Order Modal ---
    window.openCompleteOrderModal = function() {
        if (!completeOrderModal) return;
        console.log("Opening Complete Order Modal");
        
        // Reset to default selections if desired (optional)
        // document.getElementById('deliveryMethodDelivery').checked = true;
        // document.getElementById('recipientTypeMyself').checked = true;

        updateCompleteOrderForm(); // Update visibility based on current/default selections
        
        // Show Modal with transitions
        completeOrderModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            if(completeOrderModalOverlay) completeOrderModalOverlay.classList.replace('opacity-0', 'opacity-100');
            if(completeOrderModalPanel) completeOrderModalPanel.classList.replace('opacity-0', 'opacity-100');
            if(completeOrderModalPanel) completeOrderModalPanel.classList.replace('translate-y-4', 'translate-y-0');
            if(completeOrderModalPanel) completeOrderModalPanel.classList.replace('sm:scale-95', 'sm:scale-100');
        });
    }

    // --- Function to Close Complete Order Modal ---
    window.closeCompleteOrderModal = function() {
        if (!completeOrderModal || completeOrderModal.classList.contains('hidden')) return;

        // Start fade-out transitions
        if(completeOrderModalOverlay) completeOrderModalOverlay.classList.replace('opacity-100', 'opacity-0');
        if(completeOrderModalPanel) completeOrderModalPanel.classList.replace('opacity-100', 'opacity-0');
        if(completeOrderModalPanel) completeOrderModalPanel.classList.replace('translate-y-0', 'translate-y-4');
        if(completeOrderModalPanel) completeOrderModalPanel.classList.replace('sm:scale-100', 'sm:scale-95');

        // Hide modal after transition duration (e.g., 300ms)
        setTimeout(() => {
            completeOrderModal.classList.add('hidden');
        }, 300);
    }

    // --- Function to Update Complete Order Form Visibility ---
    function updateCompleteOrderForm() {
        // const isDelivery = document.querySelector('input[name="deliveryMethod"]:checked')?.value === 'delivery'; // Address handled by Alpine now
        const isMyself = document.querySelector('input[name="recipientType"]:checked')?.value === 'myself';

        if (myselfFields && senderFieldsContainer && recipientAccordionContainer && primaryDetailsTitle) {
            // Toggle fields *within* the first accordion
            myselfFields.classList.toggle('hidden', !isMyself);
            senderFieldsContainer.classList.toggle('hidden', isMyself);
            
            // Update the title of the first accordion
            primaryDetailsTitle.textContent = isMyself ? 'Your Details' : 'Sender Details';

            // Toggle the *entire* Recipient accordion item
            recipientAccordionContainer.classList.toggle('hidden', isMyself);

            // Delivery Address Containers and their required attributes are now handled by Alpine.js x-data/x-show/:required
            
            // Toggle required attributes on sender/receiver/customer fields based on visibility
            customerFields.forEach(input => {
                // Exclude address input as Alpine handles its requirement via :required="show"
                if (input.id !== 'customerAddress') {
                   input.required = isMyself;
                }
            });
            senderFields.forEach(input => input.required = !isMyself);
            receiverFields.forEach(input => {
                 // Exclude address input as Alpine handles its requirement via :required="show"
                if (input.id !== 'receiverAddress') {
                   input.required = !isMyself;
                }
            });
            
        } else {
            console.warn('One or more elements for complete order form conditional logic not found (myselfFields, senderFieldsContainer, recipientAccordionContainer, primaryDetailsTitle).');
        }
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
        if (event.key === 'Escape') {
            if (productModal && !productModal.classList.contains('hidden')) {
                closeProductModal();
            }
            if (cartModal && !cartModal.classList.contains('hidden')) {
                closeCartModal();
            }
            if (completeOrderModal && !completeOrderModal.classList.contains('hidden')) {
                closeCompleteOrderModal();
            }
            // Add other modals here if needed
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

     // Proceed button listener (Now opens Complete Order Modal)
     if (cartModalProceedButton) {
         cartModalProceedButton.addEventListener('click', () => {
             console.log('Cart Proceed button clicked. Opening Complete Order modal.');
             // Close Cart Modal first (optional, but cleaner)
             closeCartModal(); 
             // Open the new modal after a short delay to allow cart modal to start closing
             setTimeout(openCompleteOrderModal, 150); 
             // Previous action (e.g., redirect) is removed/replaced by this listener.
             // If it was redirecting via href, ensure that href is removed or '#'.
         });
     }

     // --- Complete Order Modal Listeners ---
     if (closeCompleteOrderModalButton) {
         closeCompleteOrderModalButton.addEventListener('click', closeCompleteOrderModal);
     }
     if (cancelCompleteOrderButton) { // Also close on cancel button click
         cancelCompleteOrderButton.addEventListener('click', closeCompleteOrderModal);
     }
     if (completeOrderModalOverlay) {
         completeOrderModalOverlay.addEventListener('click', closeCompleteOrderModal);
     }
     // Escape key handled by the shared listener above

    // Add listeners for the radio buttons in Complete Order Modal
    recipientTypeRadios.forEach(radio => {
        radio.addEventListener('change', updateCompleteOrderForm);
    });

    // Initial form state update when modal opens (might be better in openCompleteOrderModal)
    // For now, we assume defaults are correct on load, or call it once
    // updateCompleteOrderForm(); // Call once on load if needed, but might run before elements are ready
    // Better to call it when the modal opens. Let's modify openCompleteOrderModal
    
    // --- Modified Function to Open Complete Order Modal (Calls form update) ---
     window.openCompleteOrderModal = function() {
        if (!completeOrderModal) return;
        console.log("Opening Complete Order Modal");
        
        // Reset to default selections if desired (optional)
        // document.getElementById('deliveryMethodDelivery').checked = true;
        // document.getElementById('recipientTypeMyself').checked = true;

        updateCompleteOrderForm(); // Update visibility based on current/default selections
        
        // Show Modal with transitions
        completeOrderModal.classList.remove('hidden');
        requestAnimationFrame(() => {
            if(completeOrderModalOverlay) completeOrderModalOverlay.classList.replace('opacity-0', 'opacity-100');
            if(completeOrderModalPanel) completeOrderModalPanel.classList.replace('opacity-0', 'opacity-100');
            if(completeOrderModalPanel) completeOrderModalPanel.classList.replace('translate-y-4', 'translate-y-0');
            if(completeOrderModalPanel) completeOrderModalPanel.classList.replace('sm:scale-95', 'sm:scale-100');
        });
    }

    // Clear Cart Button Listener (Added)
    if (cartModalClearButton) {
        cartModalClearButton.addEventListener('click', () => {
            if (typeof clearCart === 'function') {
                if (confirm('Are you sure you want to remove all items from your cart?')) {
                    console.log('Clear Cart button clicked and confirmed.');
                    clearCart(); 
                } else {
                    console.log('Clear Cart action cancelled.');
                }
            } else {
                console.error('clearCart function is not defined.');
                alert('Error: Could not clear the cart.');
            }
        });
    }

}); 