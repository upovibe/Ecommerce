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

// Example Usage (You would call this from your main modal script)
// document.addEventListener('DOMContentLoaded', () => {
//     // Assuming your modal initialization happens here or after content is loaded
//     setupCountryCodeSync('customerWhatsapp_select', 'customerWhatsapp_code');
//     setupCountryCodeSync('senderWhatsapp_select', 'senderWhatsapp_code');
//     setupCountryCodeSync('receiverWhatsapp_select', 'receiverWhatsapp_code');
// }); 