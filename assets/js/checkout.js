/**
 * Formats order details and opens the WhatsApp chat window.
 * @param {object} finalOrderDetails - The finalized order details from Alpine data.
 * @param {string} whatsappNumber - The store's WhatsApp number.
 * @param {string} currencySymbol - The store's currency symbol.
 * @returns {object|null} Order data ({orderRef, total, items, timestamp}) on success, null on failure.
 */
function sendOrderToWhatsApp(finalOrderDetails, whatsappNumber, currencySymbol) {
    console.log("[sendOrderToWhatsApp] Called with:", { finalOrderDetails, whatsappNumber, currencySymbol });
    if (!finalOrderDetails || !finalOrderDetails.cart || Object.keys(finalOrderDetails.cart).length === 0) {
        console.error("[sendOrderToWhatsApp] Error: Final order details or cart is missing/empty.");
        toast?.error('Order details missing or cart is empty.');
        return null;
    }
    if (!whatsappNumber) {
        console.error("[sendOrderToWhatsApp] Error: WhatsApp number not configured.");
        toast?.error('WhatsApp number not configured.');
        return null;
    }

    const cartItems = Object.values(finalOrderDetails.cart);
    const lines = cartItems.map(item => {
        let line = `📦 ${item.details.name}`;
        if (item.details.options && Object.keys(item.details.options).length > 0) {
            const opts = Object.entries(item.details.options)
                .map(([key, value]) => `${key}: ${value}`)
                .join(', ');
            line += ` (${opts})`;
        }
        line += ` - ${item.quantity}x - ${currencySymbol}${parseFloat(item.details.price).toFixed(2)}`;
        return line;
    });

    let message = `Hi, I\'d like to buy these items:\n\n${lines.join('\n')}`;
    message += `\n\n🙍🏽‍♂️ Customer: ${finalOrderDetails.customer.name || 'N/A'}`;
    message += `\n\n*${finalOrderDetails.fulfillment.toUpperCase()} INFO:*`;
    message += `\nName: ${finalOrderDetails.customer.name || 'N/A'}`;
    message += `\nPhone: ${finalOrderDetails.customer.phone || 'N/A'}`;
    if (finalOrderDetails.fulfillment === 'delivery' && finalOrderDetails.customer.address) {
        message += `\nAddress: ${finalOrderDetails.customer.address}`;
    }
    if (finalOrderDetails.pickup) {
        message += `\nPickup Person: ${finalOrderDetails.pickup.name} (${finalOrderDetails.pickup.phone})`;
    }

    const total = cartItems.reduce((sum, item) => sum + (parseFloat(item.details.price) * item.quantity), 0);
    message += `\n\n💰 Total Price: *${currencySymbol}${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}*`;

    const orderRef = `REF-${Date.now().toString().slice(-6)}`;
    message += `\n\n🗒 Order Ref: *${orderRef}*`;

    const whatsappUrl = `https://api.whatsapp.com/send?phone=${whatsappNumber}&text=${encodeURIComponent(message)}`;
    console.log("[sendOrderToWhatsApp] Generated WhatsApp URL:", whatsappUrl);

    // Try opening WhatsApp link
    try {
        const newWindow = window.open(whatsappUrl, '_blank');
        if(!newWindow || newWindow.closed || typeof newWindow.closed=='undefined') {
             // Pop-up blocker likely intervened
             console.warn("[sendOrderToWhatsApp] WhatsApp window might have been blocked.");
             toast?.warning('Please allow pop-ups for WhatsApp to open.');
             // We still consider this a success in terms of *generating* the link
             // and returning data, as the user can copy the link or try again.
        }
        console.log("[sendOrderToWhatsApp] WhatsApp link opened (or attempted).");
    } catch (e) {
        console.error("[sendOrderToWhatsApp] Error opening WhatsApp link:", e);
        toast?.error('Could not open WhatsApp link.');
        return null; // Indicate failure
    }

    // Return order information
    const orderData = {
        orderRef: orderRef,
        total: total,
        items: cartItems.length,
        timestamp: new Date().toLocaleString()
    };
    console.log("[sendOrderToWhatsApp] Returning order data:", orderData);
    return orderData;
}


/**
 * Initiates the WhatsApp checkout process: sends order, clears cart, dispatches event.
 * Called directly from the checkout modal button's @click.
 * @param {object} finalOrderDetails - The finalized order details from Alpine data.
 * @param {string} whatsappNumber - The store's WhatsApp number.
 * @param {string} currencySymbol - The store's currency symbol.
 * @returns {boolean} True if the order data was generated and event dispatched, false otherwise.
 */
function initiateWhatsAppCheckout(finalOrderDetails, whatsappNumber, currencySymbol) {
    console.log('[initiateWhatsAppCheckout] Starting...');
    const orderData = sendOrderToWhatsApp(finalOrderDetails, whatsappNumber, currencySymbol);

    if (orderData) {
        console.log('[initiateWhatsAppCheckout] sendOrderToWhatsApp succeeded. Data:', orderData);

        // Clear the cart immediately after attempting to open WhatsApp
        if (typeof cart !== 'undefined' && typeof cart.clearCart === 'function') {
            cart.clearCart();
            console.log('[initiateWhatsAppCheckout] Cart cleared.');
        } else {
            console.error('[initiateWhatsAppCheckout] Could not clear cart. `cart` object or `clearCart` method not found.');
        }

        // Dispatch the success event with the order data
        window.dispatchEvent(new CustomEvent('checkout:success', { detail: orderData }));
        console.log('[initiateWhatsAppCheckout] checkout:success event dispatched.');
        // Don't show toast here, let the event listener handle UI changes

        return true; // Indicate the process was initiated successfully
    } else {
        console.error('[initiateWhatsAppCheckout] sendOrderToWhatsApp failed.');
        // Error toast is shown within sendOrderToWhatsApp
        return false; // Indicate failure
    }
}

// Make the initiation function globally available
window.initiateWhatsAppCheckout = initiateWhatsAppCheckout; 