/**
 * Utility Functions
 */

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @param {string} [currencySymbol='₵'] - Currency symbol (defaults to Cedi)
 * @returns {string} Formatted currency
 */
function formatCurrency(amount, currencySymbol = '₵') {
    // Ensure amount is treated as a number
    const numericAmount = parseFloat(amount);
    if (isNaN(numericAmount)) {
        console.warn('formatCurrency received a non-numeric amount:', amount);
        return currencySymbol + '0.00'; // Fallback for non-numeric input
    }
    
    // Use toLocaleString for formatting with thousand separators and 2 decimal places
    try {
        return currencySymbol + numericAmount.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    } catch (e) {
        console.error('Error formatting currency:', e);
        // Fallback to simple formatting if toLocaleString fails (highly unlikely for numbers)
        return currencySymbol + numericAmount.toFixed(2); 
    }
}

/**
 * Synchronizes a country code select dropdown with a hidden input field.
 * 
 * @param {string} selectId The ID of the <select> element for country codes.
 * @param {string} hiddenInputId The ID of the hidden <input> element to store the selected code.
 */
function setupCountryCodeSync(selectId, hiddenInputId) {
    const selectElement = document.getElementById(selectId);
    const hiddenInputElement = document.getElementById(hiddenInputId);

    if (!selectElement || !hiddenInputElement) {
        console.warn(`Phone input helper: Could not find elements for ${selectId} or ${hiddenInputId}`);
        return;
    }

    // Initial sync (in case the default selected option isn't the first one)
    hiddenInputElement.value = selectElement.value;

    // Update hidden input on change
    selectElement.addEventListener('change', (event) => {
        hiddenInputElement.value = event.target.value;
        // console.log(`Selected code for ${hiddenInputId}: ${event.target.value}`); // For debugging
    });
}

// Add other utility functions here in the future... 