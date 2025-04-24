<?php
/**
 * WhatsApp Utility Functions
 */

require_once __DIR__ . '/../config/settings.php';

/**
 * Generates a wa.me link for WhatsApp, cleaning the number and optionally adding a message.
 *
 * @param string|null $rawNumber The raw phone number from settings or input.
 * @param string|null $message Optional pre-filled message.
 * @param string $defaultCountryCode The country code to prefix if none is detected (defaults to '233' for Ghana).
 * @return string The formatted wa.me link, or '#' if the number is invalid.
 */
function generateWhatsAppLink($rawNumber, $message = null, $defaultCountryCode = '233') {
    if (empty($rawNumber)) {
        return '#';
    }
    
    // 1. Remove all non-digit characters
    $cleanedNumber = preg_replace('/[^0-9]/', '', $rawNumber);
    
    if (empty($cleanedNumber)) {
        return '#'; // Return anchor if cleaning results in empty string
    }
    
    // 2. Check for existing country code (simple check for common lengths/prefixes)
    // This logic might need refinement based on expected number formats.
    $hasCountryCode = false;
    if (strlen($cleanedNumber) > 10) { // Basic check: if longer than typical local num, might have CC
       // Add more specific checks if needed, e.g., based on STORE_SETTINGS['country']?
       // For now, we assume numbers starting with common long codes have one.
       // Example: Check if it starts with the $defaultCountryCode
       if (substr($cleanedNumber, 0, strlen($defaultCountryCode)) === $defaultCountryCode) {
          $hasCountryCode = true;
       } 
       // Add other common country codes if necessary, e.g., '1' for NANP
       // elseif (substr($cleanedNumber, 0, 1) === '1') { $hasCountryCode = true; }
    }
    
    // 3. Add default country code if none seems present
    if (!$hasCountryCode) {
        // Remove leading 0 if present (common for local dialing)
        $localNumber = ltrim($cleanedNumber, '0');
        $cleanedNumber = $defaultCountryCode . $localNumber;
    }

    // 4. Construct the base link
    $link = "https://wa.me/{$cleanedNumber}";

    // 5. Add optional message
    if (!empty($message)) {
        $link .= "?text=" . urlencode($message);
    }

    return $link;
}
