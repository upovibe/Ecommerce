<?php
require_once 'config/settings.php';
require_once __DIR__ . '/api/whatsapp.php'; // Load WhatsApp utilities

// Include header
require_once __DIR__ . '/includes/header.php';

// Get settings for contact information
$whatsappNumber = STORE_SETTINGS['whatsapp_number'] ?? '';
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store';
$storeDescription = STORE_SETTINGS['store_description'] ?? 'Your one-stop shop for all your needs';
$themeColor = STORE_SETTINGS['theme_color'] ?? '#3B82F6';

// Social media links
$facebookUsername = STORE_SETTINGS['facebook_username'] ?? '';
$instagramUsername = STORE_SETTINGS['instagram_username'] ?? '';
$twitterUsername = STORE_SETTINGS['twitter_username'] ?? '';
$linkedinUsername = STORE_SETTINGS['linkedin_username'] ?? '';

// Get contact settings from database
$contactSettings = [];
if ($db_connected && $conn) {
    $result = $conn->query("SELECT setting_key, setting_value FROM contact_settings");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contactSettings[$row['setting_key']] = $row['setting_value'];
        }
    }
}

// Get FAQs from database
$faqs = [];
if ($db_connected && $conn) {
    $result = $conn->query("SELECT * FROM faqs WHERE page_location = 'contact' AND is_active = 1 ORDER BY display_order ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
    }
}

// Get store maps from database
$maps = [];
if ($db_connected && $conn) {
    $result = $conn->query("SELECT * FROM store_maps WHERE is_active = 1 ORDER BY display_order ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $maps[] = $row;
        }
    }
}

// Set defaults if empty
$businessHoursWeekdays = $contactSettings['business_hours_weekdays'] ?? '9am - 6pm';
$businessHoursSaturday = $contactSettings['business_hours_saturday'] ?? '10am - 4pm';
$businessHoursSunday = $contactSettings['business_hours_sunday'] ?? 'Closed';
$contactEmail = $contactSettings['contact_email'] ?? 'contact@example.com';
$contactPhone = $contactSettings['contact_phone'] ?? '';
$contactFormEnabled = $contactSettings['contact_form_enabled'] ?? 'true';
$contactPageTitle = $contactSettings['contact_page_title'] ?? 'Contact Us';
$contactPageSubtitle = $contactSettings['contact_page_subtitle'] ?? 'We\'d love to hear from you! Send us a message and we\'ll respond as soon as possible.';
$contactBannerImage = $contactSettings['contact_banner_image'] ?? '/assets/images/Ecommerce-bg.jpg';

// Generate WhatsApp link
$whatsappLink = generateWhatsAppLink($whatsappNumber, 'Hello! I have a question about your products.');

// Contact form processing
$formSubmitted = false;
$formError = false;
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form']) && $contactFormEnabled === 'true') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Contact Form Inquiry');
    
    // Simple validation
    if (empty($name) || empty($email) || empty($message)) {
        $formError = true;
        $errorMessage = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = true;
        $errorMessage = 'Please enter a valid email address.';
    } else {
        // Check if email sending is enabled
        $emailEnabled = $contactSettings['email_enabled'] ?? 'false';
        
        if ($emailEnabled === 'true') {
            // Load PHPMailer
            require 'vendor/autoload.php';
            
            // Create a new PHPMailer instance
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = $contactSettings['smtp_host'] ?? 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = $contactSettings['smtp_username'] ?? '';
                $mail->Password = $contactSettings['smtp_password'] ?? '';
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $contactSettings['smtp_port'] ?? 587;
                
                // Recipients
                $mail->setFrom($contactSettings['smtp_from_email'] ?? '', $contactSettings['smtp_from_name'] ?? '');
                $mail->addAddress($contactEmail, $storeName);
                $mail->addReplyTo($email, $name);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = "[Contact Form] " . $subject;
                
                // Email body
                $emailBody = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background: #f8f9fa; padding: 20px; border-radius: 5px; }
                        .content { padding: 20px 0; }
                        .footer { font-size: 12px; color: #666; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>New Contact Form Submission</h2>
                        </div>
                        <div class='content'>
                            <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                            <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                            <p><strong>Message:</strong></p>
                            <p>" . nl2br(htmlspecialchars($message)) . "</p>
                        </div>
                        <div class='footer'>
                            <p>This email was sent from the contact form on " . htmlspecialchars($storeName) . "</p>
                        </div>
                    </div>
                </body>
                </html>";
                
                $mail->Body = $emailBody;
                $mail->AltBody = strip_tags($emailBody); // Plain text version
                
                // Send email
                $mail->send();
                
                $formSubmitted = true;
                $successMessage = 'Thank you for your message! We will get back to you as soon as possible.';
                
                // Reset form fields after successful submission
                $name = $email = $message = $subject = '';
                
            } catch (Exception $e) {
                $formError = true;
                $errorMessage = 'Sorry, there was an error sending your message. Please try again later or contact us directly.';
                // Log the error for debugging
                error_log('Contact form error: ' . $mail->ErrorInfo);
            }
        } else {
            // Email sending is disabled
            $formError = true;
            $errorMessage = 'Email sending is currently disabled. Please contact us directly using the provided contact information.';
        }
    }
}
?>

<!-- Page Header -->
<header class="relative bg-cover bg-center py-16 md:py-24" style="background-image: url('<?= htmlspecialchars($contactBannerImage) ?>');">
    <div class="absolute inset-0 bg-black/60"></div> <!-- Dark overlay -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10">
        <div class="text-center">
            <h1 class="text-3xl font-extrabold text-white sm:text-4xl">
                <?= htmlspecialchars($contactPageTitle) ?>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-white/90 sm:text-lg md:mt-5 md:text-xl">
                <?= htmlspecialchars($contactPageSubtitle) ?>
            </p>
        </div>
    </div>
</header>

<!-- Contact Section -->
<section class="py-12 md:py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:gap-10">
            <!-- Contact Information -->
            <div class="mb-12 lg:mb-0" data-aos="fade-right">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Get in Touch</h2>
                    <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 w-24 mt-2 rounded-full"></div>
                    <p class="mt-4 text-lg text-gray-500">
                        Have questions about our products or services? Reach out to us through any of these channels.
                    </p>
                </div>
                
                <dl class="grid grid-cols-1 gap-4">
                    <?php if (!empty($whatsappNumber)): ?>
                    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-green-50 to-green-100/50 p-4 transition-all duration-300 hover:shadow-lg hover:shadow-green-100/50">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-green-500 to-green-600 text-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                                    <i data-lucide="message-circle" class="h-6 w-6"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <dt class="text-base font-medium text-gray-900">WhatsApp</dt>
                                <dd class="mt-1">
                                    <a href="<?= $whatsappLink ?>" target="_blank" class="text-green-600 hover:text-green-800 transition-colors duration-200">
                                        <?= formatPhoneNumber($whatsappNumber) ?>
                                    </a>
                                </dd>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($contactEmail)): ?>
                    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 transition-all duration-300 hover:shadow-lg hover:shadow-blue-100/50">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                                    <i data-lucide="mail" class="h-6 w-6"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <dt class="text-base font-medium text-gray-900">Email</dt>
                                <dd class="mt-1">
                                    <a href="mailto:<?= htmlspecialchars($contactEmail) ?>" class="text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                        <?= htmlspecialchars($contactEmail) ?>
                                    </a>
                                </dd>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($contactPhone)): ?>
                    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100/50 p-4 transition-all duration-300 hover:shadow-lg hover:shadow-indigo-100/50">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                                    <i data-lucide="phone" class="h-6 w-6"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <dt class="text-base font-medium text-gray-900">Phone</dt>
                                <dd class="mt-1">
                                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contactPhone) ?>" class="text-indigo-600 hover:text-indigo-800 transition-colors duration-200">
                                        <?= htmlspecialchars($contactPhone) ?>
                                    </a>
                                </dd>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-4 transition-all duration-300 hover:shadow-lg hover:shadow-gray-100/50">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 mt-1">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-gray-600 to-gray-700 text-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                                    <i data-lucide="clock" class="h-6 w-6"></i>
                                </div>
                            </div>
                            <div class="flex-1 ">
                                <dt class="text-base font-medium text-gray-900">Business Hours</dt>
                                <dd class="mt-2">
                                    <ul class="space-y-2">
                                        <li class="flex items-center text-sm text-gray-600">
                                            <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                                <i data-lucide="calendar" class="h-3 w-3"></i>
                                            </span>
                                            <span class="font-medium">Mon - Fri:</span>
                                            <span class="ml-2 bg-white/50 px-2 py-0.5 rounded"><?= htmlspecialchars($businessHoursWeekdays) ?></span>
                                        </li>
                                        <li class="flex items-center text-sm text-gray-600">
                                            <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                                <i data-lucide="calendar" class="h-3 w-3"></i>
                                            </span>
                                            <span class="font-medium">Sat:</span>
                                            <span class="ml-2 bg-white/50 px-2 py-0.5 rounded"><?= htmlspecialchars($businessHoursSaturday) ?></span>
                                        </li>
                                        <li class="flex items-center text-sm text-gray-600">
                                            <span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-purple-100 text-purple-600">
                                                <i data-lucide="calendar" class="h-3 w-3"></i>
                                            </span>
                                            <span class="font-medium">Sun:</span>
                                            <span class="ml-2 bg-white/50 px-2 py-0.5 rounded"><?= htmlspecialchars($businessHoursSunday) ?></span>
                                        </li>
                                    </ul>
                                </dd>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($facebookUsername) || !empty($instagramUsername) || !empty($twitterUsername) || !empty($linkedinUsername)): ?>
                    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-gray-50 to-gray-100/50 p-4 transition-all duration-300 hover:shadow-lg hover:shadow-gray-100/50">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-gray-600 to-gray-700 text-white shadow-sm transition-transform duration-300 group-hover:scale-110">
                                    <i data-lucide="share-2" class="h-6 w-6"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <dt class="text-base font-medium text-gray-900">Follow Us</dt>
                                <dd class="mt-2">
                                    <div class="flex space-x-4">
                                        <?php if (!empty($facebookUsername)): ?>
                                        <a href="https://facebook.com/<?= htmlspecialchars(ltrim($facebookUsername, '@')) ?>" target="_blank" 
                                           class="text-blue-500 hover:text-blue-700 transition-all duration-200 hover:scale-110">
                                            <i data-lucide="facebook" class="h-5 w-5"></i>
                                            <span class="sr-only">Facebook</span>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($instagramUsername)): ?>
                                        <a href="https://instagram.com/<?= htmlspecialchars(ltrim($instagramUsername, '@')) ?>" target="_blank" 
                                           class="text-pink-500 hover:text-pink-700 transition-all duration-200 hover:scale-110">
                                            <i data-lucide="instagram" class="h-5 w-5"></i>
                                            <span class="sr-only">Instagram</span>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($twitterUsername)): ?>
                                        <a href="https://twitter.com/<?= htmlspecialchars(ltrim($twitterUsername, '@')) ?>" target="_blank" 
                                           class="text-blue-400 hover:text-blue-600 transition-all duration-200 hover:scale-110">
                                            <i data-lucide="twitter" class="h-5 w-5"></i>
                                            <span class="sr-only">Twitter</span>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($linkedinUsername)): ?>
                                        <a href="https://linkedin.com/company/<?= htmlspecialchars(ltrim($linkedinUsername, '@')) ?>" target="_blank" 
                                           class="text-blue-700 hover:text-blue-900 transition-all duration-200 hover:scale-110">
                                            <i data-lucide="linkedin" class="h-5 w-5"></i>
                                            <span class="sr-only">LinkedIn</span>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </dd>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </dl>
            </div>
            
            <!-- Contact Form -->
            <?php if ($contactFormEnabled === 'true'): ?>
            <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200" data-aos="fade-left">
                <div class="mb-5">
                    <h3 class="text-xl font-semibold text-gray-900">Send us a message</h3>
                    <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 w-16 mt-2 rounded-full"></div>
                </div>
                
                <!-- Success Message -->
                <?php if ($formSubmitted && !$formError): ?>
                <div id="success-message" class="rounded-md bg-green-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">
                                Message Sent!
                            </h3>
                            <div class="mt-2 text-sm text-green-700">
                                <p><?= htmlspecialchars($successMessage) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Error Message -->
                <?php if ($formError): ?>
                <div id="error-message" class="rounded-md bg-red-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="alert-circle" class="h-5 w-5 text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                There was an error with your submission
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p><?= htmlspecialchars($errorMessage) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" class="space-y-5">
                    <input type="hidden" name="contact_form" value="1">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required 
                                   class="py-2 px-3 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border border-gray-300 rounded-md bg-gray-50"
                                   value="<?= htmlspecialchars($name ?? '') ?>">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" id="email" required
                                   class="py-2 px-3 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border border-gray-300 rounded-md bg-gray-50"
                                   value="<?= htmlspecialchars($email ?? '') ?>">
                        </div>
                    </div>
                    
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                            Subject
                        </label>
                        <input type="text" name="subject" id="subject"
                               class="py-2 px-3 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border border-gray-300 rounded-md bg-gray-50"
                               value="<?= htmlspecialchars($subject ?? '') ?>">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                            Message <span class="text-red-500">*</span>
                        </label>
                        <textarea id="message" name="message" rows="4" required
                                 class="py-2 px-3 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 border border-gray-300 rounded-md bg-gray-50"><?= htmlspecialchars($message ?? '') ?></textarea>
                    </div>
                    
                    <div>
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-5 py-2 border border-transparent rounded-md shadow-md text-base font-medium text-white focus:outline-none transition-all duration-300 hover:shadow-lg transform hover:-translate-y-0.5" 
                                style="background-color: <?= htmlspecialchars($themeColor) ?>;">
                            <i data-lucide="send" class="h-4 w-4 mr-2"></i>
                            Send Message
                        </button>
                    </div>
                </form>
                
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 mb-3">Alternatively, contact us directly via WhatsApp</p>
                    <a href="<?= $whatsappLink ?>" target="_blank"
                       class="inline-flex items-center px-5 py-2 border border-transparent text-base font-medium rounded-md shadow-md text-white bg-green-600 hover:bg-green-700 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-0.5">
                        <i data-lucide="message-circle" class="h-4 w-4 mr-2"></i>
                        WhatsApp Chat
                    </a>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200" data-aos="fade-left">
                <div class="mb-5">
                    <h3 class="text-xl font-semibold text-gray-900">Contact Us</h3>
                    <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 w-16 mt-2 rounded-full"></div>
                </div>
                
                <p class="text-gray-500 mb-6">
                    Please use the contact information provided to get in touch with us. We look forward to hearing from you!
                </p>
                
                <div class="text-center space-y-3">
                    <a href="<?= $whatsappLink ?>" target="_blank"
                       class="inline-flex items-center px-5 py-2 border border-transparent text-base font-medium rounded-md shadow-md text-white bg-green-600 hover:bg-green-700 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-0.5 w-full justify-center">
                        <i data-lucide="message-circle" class="h-4 w-4 mr-2"></i>
                        WhatsApp Chat
                    </a>
                    
                    <?php if (!empty($contactEmail)): ?>
                    <a href="mailto:<?= htmlspecialchars($contactEmail) ?>"
                       class="inline-flex items-center px-5 py-2 border border-transparent text-base font-medium rounded-md shadow-md text-white bg-blue-600 hover:bg-blue-700 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-0.5 w-full justify-center">
                        <i data-lucide="mail" class="h-4 w-4 mr-2"></i>
                        Send Email
                    </a>
                    <?php endif; ?>
                    
                    <?php if (!empty($contactPhone)): ?>
                    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contactPhone) ?>"
                       class="inline-flex items-center px-5 py-2 border border-transparent text-base font-medium rounded-md shadow-md text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-300 hover:shadow-lg transform hover:-translate-y-0.5 w-full justify-center">
                        <i data-lucide="phone" class="h-4 w-4 mr-2"></i>
                        Call Us
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (!empty($maps)): ?>
<!-- Store Locations Section -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-10">
            <h2 class="text-3xl font-extrabold text-gray-900">Our Locations</h2>
            <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 w-24 mx-auto mt-2 rounded-full"></div>
            <p class="mt-4 text-lg text-gray-500">
                Visit us at one of our convenient locations.
            </p>
        </div>
        
        <div class="grid grid-cols-1 <?= count($maps) > 1 ? 'lg:grid-cols-2' : '' ?> gap-8">
            <?php foreach ($maps as $map): ?>
            <div class="overflow-hidden rounded-lg shadow-lg" data-aos="fade-up">
                <div class="aspect-w-16 aspect-h-9">
                    <iframe src="<?= htmlspecialchars($map['map_url']) ?>" 
                            width="100%" height="450" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade"
                            class="w-full h-full"></iframe>
                </div>
                <div class="p-4 bg-white">
                    <h3 class="text-lg font-medium text-gray-900"><?= htmlspecialchars($map['location_name']) ?></h3>
                    <?php if (!empty($map['address'])): ?>
                    <p class="mt-1 text-gray-500"><?= htmlspecialchars($map['address']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($faqs)): ?>
<!-- FAQ Section -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl font-extrabold text-gray-900">Frequently Asked Questions</h2>
            <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 w-32 mx-auto mt-2 rounded-full"></div>
            <p class="mt-4 text-lg text-gray-500">
                Answers to some common questions our customers ask.
            </p>
        </div>
        
        <div class="mt-10 space-y-4">
            <!-- FAQ Items -->
            <?php foreach ($faqs as $index => $faq): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" 
                 x-data="{ open: <?= $index === 0 ? 'true' : 'false' ?> }">
                <button @click="open = !open" 
                        class="flex justify-between items-center w-full px-5 py-4 text-base font-medium text-left text-gray-900 hover:bg-gray-50 transition-colors duration-200">
                    <span><?= htmlspecialchars($faq['question']) ?></span>
                    <i data-lucide="chevron-down" class="h-5 w-5 text-gray-500 transition-transform duration-300 ease-in-out" 
                       x-bind:class="{'transform rotate-180': open}"></i>
                </button>
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform -translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 transform translate-y-0"
                     x-transition:leave-end="opacity-0 transform -translate-y-4"
                     class="px-5 pb-4">
                    <p class="text-base text-gray-500">
                        <?= htmlspecialchars($faq['answer']) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
// Function to remove messages after a delay
function removeMessages() {
    const successMessage = document.getElementById('success-message');
    const errorMessage = document.getElementById('error-message');
    
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.transition = 'opacity 0.5s ease-out';
            successMessage.style.opacity = '0';
            setTimeout(() => {
                successMessage.remove();
            }, 500);
        }, 5000); // Remove after 5 seconds
    }
    
    if (errorMessage) {
        setTimeout(() => {
            errorMessage.style.transition = 'opacity 0.5s ease-out';
            errorMessage.style.opacity = '0';
            setTimeout(() => {
                errorMessage.remove();
            }, 500);
        }, 5000); // Remove after 5 seconds
    }
}

// Call the function when the page loads
document.addEventListener('DOMContentLoaded', removeMessages);
</script>

<?php
// Helper function to adjust color brightness
function adjustBrightness($hex, $steps) {
    // Remove # if present
    $hex = ltrim($hex, '#');
    
    // Parse the hex color
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Adjust brightness
    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));
    
    // Convert back to hex
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

// Helper function to format phone number for display
function formatPhoneNumber($number) {
    // Remove any non-numeric characters
    $number = preg_replace('/[^0-9]/', '', $number);
    
    // Format based on common patterns - simplified example
    if (strlen($number) >= 10) {
        return '+' . substr($number, 0, 3) . ' ' . substr($number, 3, 3) . ' ' . substr($number, 6);
    }
    
    return '+' . $number;
}

// Include footer
require_once __DIR__ . '/includes/footer.php';
?>
