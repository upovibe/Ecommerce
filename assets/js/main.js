/**
 * Main JavaScript for E-Commerce Template
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize product view buttons
    initProductViewButtons();
});

/**
 * Initialize "View Details" buttons for products
 */
function initProductViewButtons() {
    const viewButtons = document.querySelectorAll('.view-product-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get product data from data attributes
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const productPrice = this.dataset.productPrice;
            const productImage = this.dataset.productImage;
            const productDescription = this.dataset.productDescription || '';
            
            // Parse options if they exist
            let productOptions = {};
            if (this.dataset.productOptions) {
                try {
                    productOptions = JSON.parse(this.dataset.productOptions);
                } catch (error) {
                    console.error('Error parsing product options:', error);
                }
            }
            
            // Open product modal
            openProductModal({
                id: productId,
                name: productName,
                price: productPrice,
                image: productImage,
                description: productDescription,
                options: productOptions
            });
        });
    });
}

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @param {string} currencySymbol - Currency symbol
 * @returns {string} Formatted currency
 */
function formatCurrency(amount, currencySymbol = '₦') {
    return currencySymbol + parseFloat(amount).toFixed(2);
}

/**
 * Handle AJAX errors
 * @param {Error} error - Error object
 */
function handleAjaxError(error) {
    console.error('AJAX Error:', error);
    alert('An error occurred. Please try again later.');
} 