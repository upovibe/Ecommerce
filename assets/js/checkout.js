function sendOrderToWhatsApp(finalOrderDetails, whatsappNumber, currencySymbol) {
    if (!finalOrderDetails) {
        toast?.error('Order details missing.');
        return;
    }
    if (!whatsappNumber) {
        toast?.error('WhatsApp number not configured.');
        return;
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
    window.open(whatsappUrl, '_blank');
}

window.sendOrderToWhatsApp = sendOrderToWhatsApp; 