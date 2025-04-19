<?php
/**
 * WhatsApp Utility Functions
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/cart.php';

/**
 * Build a WhatsApp message URL for cart inquiry
 * 
 * @return string WhatsApp URL
 */
function buildCartInquiryUrl() {
    $whatsappNumber = STORE_SETTINGS['whatsapp_number'] ?? '2348012345678';
    $cartItems = getCartItemsText();
    $total = getCartTotal();
    $currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '₦';
    
    $messageTemplate = STORE_SETTINGS['whatsapp_message_template'] ?? "Hello, I want to inquire about:\n{ITEMS}\nTotal: {CURRENCY}{TOTAL}";
    
    // Replace placeholders
    $message = str_replace(
        ['{ITEMS}', '{CURRENCY}', '{TOTAL}'],
        [$cartItems, $currencySymbol, number_format($total, 2)],
        $messageTemplate
    );
    
    // Encode message for URL
    $encodedMessage = urlencode($message);
    
    return "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";
}

/**
 * Build a WhatsApp message URL for order placement
 * 
 * @param array $orderDetails Order details (name, phone, address, delivery type, etc.)
 * @return string WhatsApp URL
 */
function buildOrderPlacementUrl($orderDetails) {
    $whatsappNumber = STORE_SETTINGS['whatsapp_number'] ?? '2348012345678';
    $cartItems = getCartItemsText();
    $total = getCartTotal();
    $currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '₦';
    
    $message = "New Order:\n";
    $message .= $cartItems;
    $message .= "Total: {$currencySymbol}" . number_format($total, 2) . "\n\n";
    
    // Add delivery details
    $message .= "Delivery: " . ($orderDetails['delivery_type'] ?? 'Not specified') . "\n";
    $message .= "Receiver: " . ($orderDetails['name'] ?? 'Not provided') . "\n";
    $message .= "Phone: " . ($orderDetails['phone'] ?? 'Not provided') . "\n";
    
    // Add address if delivery is selected
    if (isset($orderDetails['delivery_type']) && strtolower($orderDetails['delivery_type']) === 'delivery') {
        $message .= "Address: " . ($orderDetails['address'] ?? 'Not provided') . "\n";
    }
    
    // Add extra note if provided
    if (!empty($orderDetails['note'])) {
        $message .= "Note: " . $orderDetails['note'] . "\n";
    }
    
    // Encode message for URL
    $encodedMessage = urlencode($message);
    
    return "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";
} 