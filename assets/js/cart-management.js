/**
 * Cart Management Functions using localStorage
 * Provides functions to interact with the shopping cart stored in the browser's localStorage.
 * Includes adding, removing, updating quantities, and retrieving cart items.
 * Also handles basic UI updates like the cart icon count.
 */

// =========================================================================
// --- Core Cart Data Functions --- 
// =========================================================================

/**
 * Retrieves the shopping cart items from localStorage.
 * @returns {Array<object>} An array of cart item objects, or an empty array if the cart is empty or not found.
 */
function getCartItems() {
    const cart = localStorage.getItem('shoppingCart');
    return cart ? JSON.parse(cart) : [];
}

/**
 * Saves the provided cart array to localStorage.
 * Also triggers a UI update.
 * @param {Array<object>} cart The array of cart items to save.
 */
function saveCartItems(cart) {
    localStorage.setItem('shoppingCart', JSON.stringify(cart));
    updateCartUI(); // Update UI whenever cart changes
}

// =========================================================================
// --- Cart Modification Functions --- 
// =========================================================================

// SVG Icons used for button state changes within cart functions
const checkIconSVG_cart = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
const cartIconSVG_cart = `<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>`;

/**
 * Adds an item to the shopping cart or potentially updates its quantity if it already exists.
 * Currently, it only adds new items and doesn't update quantity for existing ones.
 * Updates the UI and the state of the corresponding product card button (if found).
 * 
 * @param {number|string} productId The unique ID of the product.
 * @param {number} [quantity=1] The quantity to add (defaults to 1).
 * @param {string} productName The name of the product.
 * @param {number|string} productPrice The price of the product.
 * @param {string} productImage The URL of the product image.
 * @param {object} [productOptions={}] An object containing selected product options (e.g., size, color).
 */
function addToCart(productId, quantity = 1, productName, productPrice, productImage, productOptions = {}) {
    let cart = getCartItems();
    const existingItemIndex = cart.findIndex(item => item.id == productId);

    let itemAddedOrUpdated = false;

    if (existingItemIndex > -1) {
        console.log("Item already in cart:", productId);
        // Optionally update quantity here if needed in the future
        // cart[existingItemIndex].quantity += quantity;
        // itemAddedOrUpdated = true; 
    } else {
        const newItem = {
            id: productId,
            name: productName,
            price: parseFloat(productPrice),
            image: productImage,
            options: productOptions,
            quantity: quantity
        };
        cart.push(newItem);
        itemAddedOrUpdated = true;
        console.log("Added new item to cart:", newItem);
    }

    if (itemAddedOrUpdated) {
        saveCartItems(cart);

        // --- Update corresponding product card button UI (if it exists on the page) ---
        try {
            const productCardButton = document.querySelector(`.add-to-cart-icon-btn[data-product-id="${productId}"]`);
            if (productCardButton) {
                productCardButton.innerHTML = checkIconSVG_cart; // Change icon to checkmark
                productCardButton.disabled = true;
                productCardButton.title = 'Added to Cart';
                // Remove old classes, add new ones
                productCardButton.classList.remove('text-gray-500', 'hover:text-primary', 'hover:bg-gray-100');
                productCardButton.classList.add('text-green-500', 'bg-green-100', 'cursor-not-allowed', 'in-cart');
                 // Remove any lingering animation classes (like animate-bounce if added elsewhere)
                productCardButton.classList.remove('animate-bounce'); 
            }
        } catch (e) {
            console.error("Error updating product card button UI:", e);
        }
        // --- End button UI update ---
    }
}

/**
 * Removes an item completely from the shopping cart based on its product ID.
 * Updates the UI and reverts the state of the corresponding product card button (if found).
 * 
 * @param {number|string} productId The unique ID of the product to remove.
 */
function removeFromCart(productId) {
    let cart = getCartItems();
    const initialLength = cart.length;
    cart = cart.filter(item => item.id != productId);

    // Only save and update UI if an item was actually removed
    if (cart.length < initialLength) {
        console.log("Removed item from cart logic:", productId);
        saveCartItems(cart);

        // --- Update corresponding product card button UI (if it exists on the page) ---
        try {
            const productCardButton = document.querySelector(`.add-to-cart-icon-btn[data-product-id="${productId}"]`);
            if (productCardButton) {
                productCardButton.innerHTML = cartIconSVG_cart; // Change icon back to cart
                productCardButton.disabled = false;
                productCardButton.title = 'Add to Cart';
                // Remove added styles, add back default/hover styles
                productCardButton.classList.remove('text-green-500', 'bg-green-100', 'cursor-not-allowed', 'in-cart');
                productCardButton.classList.add('text-gray-500', 'hover:text-primary', 'hover:bg-gray-100');
            }
        } catch (e) {
            console.error("Error reverting product card button UI:", e);
        }
        // --- End button UI update ---
    } else {
         console.warn("Attempted to remove item not found in cart:", productId);
    }
}

/**
 * Increases the quantity of a specific item in the cart by 1.
 * 
 * @param {number|string} productId The unique ID of the product whose quantity should be increased.
 */
function increaseCartItemQuantity(productId) {
    let cart = getCartItems();
    const itemIndex = cart.findIndex(item => item.id == productId);
    if (itemIndex > -1) {
        cart[itemIndex].quantity += 1;
        saveCartItems(cart);
        console.log("Increased quantity for:", productId);
    } else {
        console.warn("Attempted to increase quantity for item not in cart:", productId);
    }
}

/**
 * Decreases the quantity of a specific item in the cart by 1.
 * If the quantity drops to 0 or below, the item is removed from the cart.
 * 
 * @param {number|string} productId The unique ID of the product whose quantity should be decreased.
 */
function decreaseCartItemQuantity(productId) {
    let cart = getCartItems();
    const itemIndex = cart.findIndex(item => item.id == productId);
    if (itemIndex > -1) {
        cart[itemIndex].quantity -= 1;
        if (cart[itemIndex].quantity <= 0) {
            // Remove item if quantity is 0 or less
            console.log("Quantity zero, removing item:", productId);
            cart.splice(itemIndex, 1); // Use splice to remove item by index
        } else {
             console.log("Decreased quantity for:", productId);
        }
        saveCartItems(cart);
    } else {
         console.warn("Attempted to decrease quantity for item not in cart:", productId);
    }
}

// =========================================================================
// --- UI Update Functions --- 
// =========================================================================

/**
 * Updates various parts of the UI to reflect the current state of the cart.
 * Specifically updates the cart item count in the header (desktop and mobile).
 * Calls `updateCartModal()` if that function exists (defined in cartModal.js).
 */
function updateCartUI() {
    const cart = getCartItems();
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);

    // Update header cart count (desktop)
    const cartCountElement = document.getElementById('cart-item-count');
    if (cartCountElement) {
        cartCountElement.textContent = totalItems;
        cartCountElement.classList.toggle('hidden', totalItems === 0); // Show/hide based on count
    }
    
    // Update header cart count (mobile)
    const mobileCartCountElement = document.getElementById('mobile-cart-item-count');
    if (mobileCartCountElement) {
        mobileCartCountElement.textContent = totalItems;
        mobileCartCountElement.classList.toggle('hidden', totalItems === 0); // Show/hide based on count
    }

    // Update cart modal if it's open and the function exists
    if (typeof updateCartModal === 'function') {
         updateCartModal();
    }

    // You might want to update other UI elements here
    console.log("Cart UI updated. Total items:", totalItems);
}

// =========================================================================
// --- Initialization --- 
// =========================================================================

// Initialize cart UI on page load
document.addEventListener('DOMContentLoaded', () => {
    updateCartUI();
});

// --- Example Usage (can be removed) ---
// addToCart(1, 1, 'Test Product', 99.99, '/path/to/image.jpg', {color: 'Red'});
// console.log(getCartItems());
// removeFromCart(1);
// console.log(getCartItems());
// ------------------------------------- 