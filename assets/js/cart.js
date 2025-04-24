/**
 * Simple Client-Side Cart Management using localStorage
 */
const cart = {
    key: 'userCart',

    _getCart: function() {
        try {
            const cartData = localStorage.getItem(this.key);
            console.log('[cart.js] Raw cart data from localStorage:', cartData);
            const parsedCart = cartData ? JSON.parse(cartData) : {};
            console.log('[cart.js] Parsed cart data retrieved:', JSON.stringify(parsedCart, null, 2)); // Log the parsed structure
            return parsedCart; // Use object { productId: { details, quantity }, ... }
        } catch (e) {
            console.error("[cart.js] Error reading/parsing cart from localStorage:", e);
            return {};
        }
    },

    _saveCart: function(cartData) {
        try {
            localStorage.setItem(this.key, JSON.stringify(cartData));
            console.log('Cart saved to localStorage:', cartData);
            // Dispatch a custom event that components can listen to
            document.dispatchEvent(new CustomEvent('cart:updated', { 
                detail: { cart: cartData }
            }));
        } catch (e) {
            console.error("Error saving cart to localStorage:", e);
        }
    },

    // Adds an item or increments quantity
    addItem: function(productId, productDetails, quantity = 1) {
        console.log('Adding item to cart:', productId, productDetails, quantity);
        
        if (!productId || !productDetails) {
             console.error("addItem requires productId and productDetails");
             return false;
        }
        
        const cartData = this._getCart();
        
        if (cartData[productId]) {
            // Item exists, increment quantity
            cartData[productId].quantity += quantity;
            console.log('Updated quantity for existing item (options not updated):', cartData[productId]);
        } else {
             // Add new item
             const newItem = {
                 details: { // Store minimal needed details
                     id: productDetails.id,
                     name: productDetails.name,
                     price: productDetails.price,
                     image: productDetails.image,
                     options: productDetails.options || {} // Ensure options are stored
                 },
                 quantity: quantity 
             };
             cartData[productId] = newItem;
             console.log('Added new item to cart with details:', newItem);
        }
        
        this._saveCart(cartData);
        this.updateHeaderCount();
        return true;
    },

    // Increment item quantity by 1
    incrementQuantity: function(productId) {
        console.log('Incrementing quantity for item:', productId);
        const cartData = this._getCart();
        
        if (cartData[productId]) {
            cartData[productId].quantity += 1;
            this._saveCart(cartData);
            this.updateHeaderCount(); // Make sure header count updates
            return true;
        }
        return false;
    },

    // Decrement item quantity by 1, remove if quantity becomes 0
    decrementQuantity: function(productId) {
        console.log('Decrementing quantity for item:', productId);
        const cartData = this._getCart();
        
        if (cartData[productId]) {
            cartData[productId].quantity -= 1;
            
            if (cartData[productId].quantity <= 0) {
                delete cartData[productId];
                console.log('Removed item due to zero quantity:', productId);
            }
            
            this._saveCart(cartData);
            this.updateHeaderCount();
            return true;
        }
        return false;
    },

    // Removes an item completely
    removeItem: function(productId) {
        console.log('Removing item from cart:', productId);
        const cartData = this._getCart();
        if (cartData[productId]) {
            delete cartData[productId];
            this._saveCart(cartData);
            this.updateHeaderCount();
            return true;
        }
        return false;
    },
    
    // Check if an item is in the cart
    isInCart: function(productId) {
        const cartData = this._getCart();
        return !!cartData[productId]; // Check if the key exists
    },
    
    // Get item quantity from cart (0 if not in cart)
    getItemQuantity: function(productId) {
        const cartData = this._getCart();
        return cartData[productId] ? cartData[productId].quantity : 0;
    },

    // Get the total number of unique items in the cart
    getCount: function() {
        const cartData = this._getCart();
        return Object.keys(cartData).length;
    },

    // Update the cart count display in the header
    updateHeaderCount: function() {
        const count = this.getCount();
        console.log('Updating cart count in header:', count);
        const countElement = document.getElementById('cart-item-count');
        const mobileCountElement = document.getElementById('mobile-cart-item-count');
        
        if (countElement) {
            countElement.textContent = count;
            countElement.classList.toggle('hidden', count === 0); // Show/hide based on count
        }
        if (mobileCountElement) {
            mobileCountElement.textContent = count;
            mobileCountElement.classList.toggle('hidden', count === 0);
        }
    },

    // Get all items (useful for displaying cart later)
    getContents: function() {
        const contents = this._getCart();
        console.log('Cart contents retrieved:', contents);
        return contents;
    },

    // Clear the entire cart
    clearCart: function() {
        console.log('Clearing cart');
        this._saveCart({});
        this.updateHeaderCount();
        return true;
    }
};

// Initialize header count on page load
document.addEventListener('DOMContentLoaded', () => {
    console.log('Cart.js initialized');
    cart.updateHeaderCount();
    
    // Listen for click events on add-to-cart buttons in product cards
    document.querySelectorAll('.add-to-cart-icon-btn').forEach(button => {
        updateCartButtonUI(button);
    });
});

// Function to update any cart button UI based on cart state
function updateCartButtonUI(button) {
    if (!button) return;
    
    const productId = button.dataset.productId;
    if (!productId) return;
    
    const isInCart = cart.isInCart(productId);
    
    // Update button appearance
    if (isInCart) {
        button.innerHTML = `<i data-lucide="check" class="lucide-icon size-4 text-green-600"></i>`;
        button.classList.remove('text-gray-500', 'hover:text-primary', 'hover:bg-gray-100');
        button.classList.add('text-green-500', 'bg-green-100', 'cursor-not-allowed');
        button.disabled = true;
        button.title = 'Added to Cart';
    } else {
        button.innerHTML = `<i data-lucide="shopping-cart" class="lucide-icon size-4"></i>`;
        button.classList.add('text-gray-500', 'hover:text-primary', 'hover:bg-gray-100');
        button.classList.remove('text-green-500', 'bg-green-100', 'cursor-not-allowed');
        button.disabled = false;
        button.title = 'Add to Cart';
    }
    
    // Re-initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons({ nodes: [button] });
    }
} 