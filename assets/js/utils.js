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

// Add other utility functions here in the future... 