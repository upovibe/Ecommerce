<?php
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
    $result = $conn->query("SELECT * FROM faqs WHERE page_location = 'contact' ORDER BY display_order ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
    }
}

// Get store maps from database
$maps = [];
if ($db_connected && $conn) {
    $result = $conn->query("SELECT * FROM store_maps ORDER BY display_order ASC");
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
?>

<div class="bg-white rounded-lg shadow">
    <form @submit.prevent="saveContactSettings($event)" method="POST" enctype="multipart/form-data" class="p-6">
        <div class="flex items-center justify-between mb-4 ">
            <h3 class="text-lg font-medium text-gray-900">Contact Settings</h3>
            <button type="submit" class="flex items-center p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" :class="{'hidden': !isSavingContact}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <i data-lucide="save" class="w-5 h-5 mr-2" :class="{'hidden': isSavingContact}"></i>
                <span x-text="isSavingContact ? 'Saving...' : 'Save Changes'"></span>
            </button>
        </div>

        <!-- Page Header Settings -->
        <fieldset class="space-y-4 mb-6">
            <legend class="text-base font-medium text-gray-700 mb-2">Page Header</legend>
            <div class="space-y-4">
                <div>
                    <label for="contact_page_title" class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                    <input type="text" name="contact_page_title" id="contact_page_title" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= htmlspecialchars($contactPageTitle) ?>">
                </div>
                <div>
                    <label for="contact_page_subtitle" class="block text-sm font-medium text-gray-700 mb-1">Page Subtitle</label>
                    <textarea name="contact_page_subtitle" id="contact_page_subtitle" rows="2" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($contactPageSubtitle) ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Banner Image</label>
                    <label for="contact_banner_image" id="contact_banner_image_dropzone"
                           class="relative mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200 min-h-[150px]">

                        <!-- Placeholder Content -->
                        <div id="contact_banner_image_placeholder" class="space-y-1 text-center" <?= !empty($contactSettings['contact_banner_image']) && $contactSettings['contact_banner_image'] != '/assets/images/Ecommerce-bg.jpg' ? 'style="display: none;"' : '' ?>>
                            <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400"></i>
                            <div class="flex text-sm text-gray-600">
                                <span class="relative bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Upload a file</span>
                                    <input type="file" name="contact_banner_image_file" id="contact_banner_image_file" accept="image/jpeg, image/png, image/webp, image/gif"
                                           onchange="previewContactBannerImage()"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                </span>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 2MB. Recommended: 1920x400</p>
                        </div>

                        <!-- Image Preview -->
                        <div id="contact_banner_image_preview_container" class="relative w-full h-full flex justify-center items-center" <?= empty($contactSettings['contact_banner_image']) || $contactSettings['contact_banner_image'] == '/assets/images/Ecommerce-bg.jpg' ? 'style="display: none;"' : '' ?>>
                             <img id="contact_banner_image_preview" src="<?= htmlspecialchars($contactSettings['contact_banner_image'] ?? '/assets/images/Ecommerce-bg.jpg') ?>" alt="Banner Preview"
                                 class="max-h-48 max-w-full rounded-lg object-contain shadow-sm">
                            <button type="button" id="contact_banner_image_remove_button" onclick="removeContactBannerImage()"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 z-10"
                                    title="Remove Image" <?= empty($contactSettings['contact_banner_image']) || $contactSettings['contact_banner_image'] == '/assets/images/Ecommerce-bg.jpg' ? 'style="display: none;"' : '' ?>>
                                <i data-lucide="x" class="w-3 h-3"></i>
                            </button>
                        </div>
                         <input type="hidden" name="contact_banner_image" id="contact_banner_image" value="<?= htmlspecialchars($contactSettings['contact_banner_image'] ?? '/assets/images/Ecommerce-bg.jpg') ?>">
                         <input type="hidden" name="remove_contact_banner_image" id="remove_contact_banner_image" value="0">
                    </label>
                     <p class="text-xs text-gray-500 mt-1">Current path: <?= htmlspecialchars($contactSettings['contact_banner_image'] ?? '/assets/images/Ecommerce-bg.jpg') ?></p>
                </div>
            </div>
        </fieldset>

        <!-- Contact Information -->
        <fieldset class="space-y-4 mb-6 border-t pt-4">
            <legend class="text-base font-medium text-gray-700 mb-2">Contact Information</legend>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
                    <input type="email" name="contact_email" id="contact_email" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= htmlspecialchars($contactEmail) ?>">
                </div>
                <div>
                    <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-1">Contact Phone</label>
                    <input type="text" name="contact_phone" id="contact_phone" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= htmlspecialchars($contactPhone) ?>">
                </div>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="contact_form_enabled" value="true"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                           <?= $contactFormEnabled === 'true' ? 'checked' : '' ?>>
                    <span class="ml-2 text-sm text-gray-700">Enable contact form on contact page</span>
                </label>
            </div>
        </fieldset>

        <!-- Business Hours -->
        <fieldset class="space-y-4 mb-6 border-t pt-4">
            <legend class="text-base font-medium text-gray-700 mb-2">Business Hours</legend>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="business_hours_weekdays" class="block text-sm font-medium text-gray-700 mb-1">Weekdays (Mon-Fri)</label>
                    <input type="text" name="business_hours_weekdays" id="business_hours_weekdays" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= htmlspecialchars($businessHoursWeekdays) ?>">
                </div>
                <div>
                    <label for="business_hours_saturday" class="block text-sm font-medium text-gray-700 mb-1">Saturday</label>
                    <input type="text" name="business_hours_saturday" id="business_hours_saturday" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= htmlspecialchars($businessHoursSaturday) ?>">
                </div>
                <div>
                    <label for="business_hours_sunday" class="block text-sm font-medium text-gray-700 mb-1">Sunday</label>
                    <input type="text" name="business_hours_sunday" id="business_hours_sunday" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= htmlspecialchars($businessHoursSunday) ?>">
                </div>
            </div>
        </fieldset>

        <!-- Email Configuration -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <h3 class="text-lg font-medium text-gray-900">Email Configuration</h3>
                <div class="flex items-center">
                    <label for="email_enabled" class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="email_enabled" id="email_enabled" value="true" 
                               class="sr-only peer" <?= isset($contactSettings['email_enabled']) && $contactSettings['email_enabled'] === 'true' ? 'checked' : '' ?>>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Enable Email Sending</span>
                    </label>
                </div>
            </div>

            <div class="space-y-6" id="email-settings" x-data="{ showPassword: false }">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="relative">
                        <label for="smtp_host" class="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="server" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text" name="smtp_host" id="smtp_host" 
                                   value="<?= htmlspecialchars($contactSettings['smtp_host'] ?? '') ?>" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., smtp.gmail.com">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Your SMTP server hostname</p>
                    </div>

                    <div class="relative">
                        <label for="smtp_port" class="block text-sm font-medium text-gray-700 mb-1">SMTP Port</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="hash" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="number" name="smtp_port" id="smtp_port" 
                                   value="<?= htmlspecialchars($contactSettings['smtp_port'] ?? '') ?>" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., 587">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Common ports: 587 (TLS), 465 (SSL), 25</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="relative">
                        <label for="smtp_username" class="block text-sm font-medium text-gray-700 mb-1">SMTP Username</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="user" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text" name="smtp_username" id="smtp_username" 
                                   value="<?= htmlspecialchars($contactSettings['smtp_username'] ?? '') ?>" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Your email or username">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Your email address or SMTP username</p>
                    </div>

                    <div class="relative">
                        <label for="smtp_password" class="block text-sm font-medium text-gray-700 mb-1">SMTP Password</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" name="smtp_password" id="smtp_password" 
                                   value="<?= htmlspecialchars($contactSettings['smtp_password'] ?? '') ?>" 
                                   class="block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Your password">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button type="button" @click="showPassword = !showPassword" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                    <i data-lucide="eye" x-show="!showPassword" class="h-5 w-5"></i>
                                    <i data-lucide="eye-off" x-show="showPassword" class="h-5 w-5"></i>
                                </button>
                            </div>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">Your email password or app-specific password</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="relative">
                        <label for="smtp_from_email" class="block text-sm font-medium text-gray-700 mb-1">From Email</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="email" name="smtp_from_email" id="smtp_from_email" 
                                   value="<?= htmlspecialchars($contactSettings['smtp_from_email'] ?? '') ?>" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="sender@example.com">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">The email address that will appear as the sender</p>
                    </div>

                    <div class="relative">
                        <label for="smtp_from_name" class="block text-sm font-medium text-gray-700 mb-1">From Name</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="user-circle" class="h-5 w-5 text-gray-400"></i>
                            </div>
                            <input type="text" name="smtp_from_name" id="smtp_from_name" 
                                   value="<?= htmlspecialchars($contactSettings['smtp_from_name'] ?? '') ?>" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Your Store Name">
                        </div>
                        <p class="mt-1 text-sm text-gray-500">The name that will appear as the sender</p>
                    </div>
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-md">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-shrink-0">
                            <i data-lucide="info" class="h-5 w-5 text-blue-400"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-blue-800">Email Provider Setup Instructions</h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <p class="mb-2">Common email provider settings:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-white/50 p-3 rounded-md">
                                        <h4 class="font-medium text-blue-900">Gmail</h4>
                                        <ul class="mt-1 space-y-1 text-blue-800">
                                            <li>Host: smtp.gmail.com</li>
                                            <li>Port: 587</li>
                                            <li>Requires App Password</li>
                                        </ul>
                                    </div>
                                    <div class="bg-white/50 p-3 rounded-md">
                                        <h4 class="font-medium text-blue-900">Outlook/Hotmail</h4>
                                        <ul class="mt-1 space-y-1 text-blue-800">
                                            <li>Host: smtp.office365.com</li>
                                            <li>Port: 587</li>
                                        </ul>
                                    </div>
                                    <div class="bg-white/50 p-3 rounded-md">
                                        <h4 class="font-medium text-blue-900">Yahoo</h4>
                                        <ul class="mt-1 space-y-1 text-blue-800">
                                            <li>Host: smtp.mail.yahoo.com</li>
                                            <li>Port: 587</li>
                                        </ul>
                                    </div>
                                    <div class="bg-white/50 p-3 rounded-md">
                                        <h4 class="font-medium text-blue-900">Custom Domain</h4>
                                        <ul class="mt-1 space-y-1 text-blue-800">
                                            <li>Use your hosting provider's SMTP settings</li>
                                            <li>Check your hosting control panel</li>
                                        </ul>
                                    </div>
                                </div>
                                <p class="mt-4 text-blue-800">
                                    <strong>For Gmail users:</strong> Enable 2-Step Verification and generate an App Password in your Google Account settings.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Store Maps Section -->
    <div class="border-t p-6" x-data="{ showAddMap: false, mapIdToEdit: null, editingMap: null }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Store Locations</h3>
            <button type="button" @click="showAddMap = true; mapIdToEdit = null; editingMap = null" 
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i data-lucide="plus" class="h-4 w-4 mr-1"></i> Add Location
            </button>
        </div>
        
        <div class="space-y-4">
            <?php if (empty($maps)): ?>
                <div class="text-center py-4 text-gray-500">
                    No store locations have been added yet.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($maps as $map): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($map['location_name']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($map['address']) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($map['is_active']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <div class="flex items-center space-x-2">
                                            <button type="button" @click="mapIdToEdit = <?= $map['id'] ?>; editingMap = <?= htmlspecialchars(json_encode($map)) ?>; showAddMap = true;" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                <i data-lucide="edit" class="h-4 w-4"></i>
                                                <span class="sr-only">Edit</span>
                                            </button>
                                            <form action="utils/delete_map.php" method="POST" class="inline-block" 
                                                  onsubmit="return confirm('Are you sure you want to delete this location?');">
                                                <input type="hidden" name="map_id" value="<?= $map['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                    <span class="sr-only">Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Add/Edit Map Modal -->
        <div x-show="showAddMap" x-cloak class="fixed inset-0 overflow-y-auto z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showAddMap = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="utils/save_map.php" method="POST">
                        <input type="hidden" name="map_id" :value="mapIdToEdit">
                        
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        <span x-show="mapIdToEdit === null">Add New Location</span>
                                        <span x-show="mapIdToEdit !== null">Edit Location</span>
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label for="location_name" class="block text-sm font-medium text-gray-700">Location Name</label>
                                            <input type="text" name="location_name" id="location_name" required
                                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                   x-bind:value="editingMap ? editingMap.location_name : ''">
                                        </div>
                                        <div>
                                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                            <textarea name="address" id="address" rows="2" 
                                                      class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                      x-text="editingMap ? editingMap.address : ''"></textarea>
                                        </div>
                                        <div>
                                            <label for="map_url" class="block text-sm font-medium text-gray-700">Google Maps Embed URL</label>
                                            <input type="text" name="map_url" id="map_url" required 
                                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                   x-bind:value="editingMap ? editingMap.map_url : ''">
                                            <p class="mt-1 text-xs text-gray-500">
                                                Get this from Google Maps by selecting a location, clicking "Share," choosing "Embed a map," and copying the iframe's src attribute.
                                            </p>
                                        </div>
                                        <div>
                                            <label for="display_order" class="block text-sm font-medium text-gray-700">Display Order</label>
                                            <input type="number" name="display_order" id="display_order" min="0" value="0"
                                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                   x-bind:value="editingMap ? editingMap.display_order : 0">
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" name="is_active" id="is_active" value="1" checked
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                   x-bind:checked="editingMap ? editingMap.is_active == 1 : true">
                                            <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save
                            </button>
                            <button type="button" @click="showAddMap = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FAQs Section -->
    <div class="border-t p-6" x-data="{ showAddFaq: false, faqIdToEdit: null, editingFaq: null }">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Frequently Asked Questions</h3>
            <button type="button" @click="showAddFaq = true; faqIdToEdit = null; editingFaq = null"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i data-lucide="plus" class="h-4 w-4 mr-1"></i> Add
            </button>
        </div>
        
        <div class="space-y-4">
            <?php if (empty($faqs)): ?>
                <div class="text-center py-4 text-gray-500">
                    No FAQs have been added yet.
                </div>
            <?php else: ?>
                <div class="overflow-hidden">
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($faqs as $faq): ?>
                            <li class="py-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?= htmlspecialchars($faq['question']) ?>
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?= htmlspecialchars($faq['answer']) ?>
                                        </p>
                                    </div>
                                    <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                                        <button type="button" @click="faqIdToEdit = <?= $faq['id'] ?>; editingFaq = <?= htmlspecialchars(json_encode($faq)) ?>; showAddFaq = true;"
                                                class="text-blue-600 hover:text-blue-900">
                                            <i data-lucide="edit" class="h-4 w-4"></i>
                                            <span class="sr-only">Edit</span>
                                        </button>
                                        <form action="utils/delete_faq.php" method="POST" class="inline-block"
                                              onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                                            <input type="hidden" name="faq_id" value="<?= $faq['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                                <span class="sr-only">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Add/Edit FAQ Modal -->
        <div x-show="showAddFaq" x-cloak class="fixed inset-0 overflow-y-auto z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showAddFaq = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="utils/save_faq.php" method="POST">
                        <input type="hidden" name="faq_id" :value="faqIdToEdit">
                        <input type="hidden" name="page_location" value="contact">
                        
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        <span x-show="faqIdToEdit === null">Add New FAQ</span>
                                        <span x-show="faqIdToEdit !== null">Edit FAQ</span>
                                    </h3>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label for="question" class="block text-sm font-medium text-gray-700">Question</label>
                                            <input type="text" name="question" id="question" required
                                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                   x-bind:value="editingFaq ? editingFaq.question : ''">
                                        </div>
                                        <div>
                                            <label for="answer" class="block text-sm font-medium text-gray-700">Answer</label>
                                            <textarea name="answer" id="answer" rows="4" required
                                                      class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                      x-text="editingFaq ? editingFaq.answer : ''"></textarea>
                                        </div>
                                        <div>
                                            <label for="display_order" class="block text-sm font-medium text-gray-700">Display Order</label>
                                            <input type="number" name="display_order" id="display_order" min="0" value="0"
                                                   class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                   x-bind:value="editingFaq ? editingFaq.display_order : 0">
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" name="is_active" id="is_active" value="1" checked
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                   x-bind:checked="editingFaq ? editingFaq.is_active == 1 : true">
                                            <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Save
                            </button>
                            <button type="button" @click="showAddFaq = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        
        // Image preview for contact banner
        const contactBannerInput = document.getElementById('contact_banner_image');
        if (contactBannerInput && '<?= !empty($contactSettings['contact_banner_image']) && $contactSettings['contact_banner_image'] != '/assets/images/Ecommerce-bg.jpg' ?>') {
            document.getElementById('contact_banner_image_placeholder').style.display = 'none';
            document.getElementById('contact_banner_image_preview_container').style.display = 'flex';
            document.getElementById('contact_banner_image_remove_button').style.display = 'block';
        }
        
        // Initialize contact banner image dropzone for drag and drop
        const dropzone = document.getElementById('contact_banner_image_dropzone');
        if (dropzone) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropzone.classList.add('border-blue-400', 'bg-blue-50');
            }
            
            function unhighlight() {
                dropzone.classList.remove('border-blue-400', 'bg-blue-50');
            }
            
            dropzone.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const files = e.dataTransfer.files;
                if (files.length) {
                    document.getElementById('contact_banner_image_file').files = files;
                    previewContactBannerImage();
                }
            }
        }

        // Toggle email settings visibility based on enable/disable toggle
        const emailEnabled = document.getElementById('email_enabled');
        const emailSettings = document.getElementById('email-settings');

        function updateEmailSettingsVisibility() {
            if (emailSettings) {
                emailSettings.style.opacity = emailEnabled.checked ? '1' : '0.5';
                emailSettings.style.pointerEvents = emailEnabled.checked ? 'auto' : 'none';
            }
        }

        if (emailEnabled) {
            emailEnabled.addEventListener('change', updateEmailSettingsVisibility);
            updateEmailSettingsVisibility();
        }
    });

    // Function to preview the contact banner image
    function previewContactBannerImage() {
        const input = document.getElementById('contact_banner_image_file');
        const preview = document.getElementById('contact_banner_image_preview');
        const previewContainer = document.getElementById('contact_banner_image_preview_container');
        const placeholder = document.getElementById('contact_banner_image_placeholder');
        const removeButton = document.getElementById('contact_banner_image_remove_button');
        const hiddenInput = document.getElementById('contact_banner_image');
        const removeFlagInput = document.getElementById('remove_contact_banner_image');
        const file = input.files[0];
        
        if (file) {
            // Check file size (2MB limit)
            if (file.size > 2 * 1024 * 1024) {
                alert('Image size exceeds 2MB limit.');
                input.value = null; // Clear the input
                return;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid image file type. Only PNG, JPG, GIF, WEBP are allowed.');
                input.value = null; // Clear the input
                return;
            }
            
            // Use FileReader to preview the image
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                previewContainer.style.display = 'flex';
                placeholder.style.display = 'none';
                removeButton.style.display = 'block';
                hiddenInput.value = 'UPLOADED_FILE_PENDING'; // Mark for backend to process uploaded file
                removeFlagInput.value = '0';
            };
            reader.readAsDataURL(file);
        }
    }

    // Function to remove the contact banner image
    function removeContactBannerImage() {
        const input = document.getElementById('contact_banner_image_file');
        const preview = document.getElementById('contact_banner_image_preview');
        const previewContainer = document.getElementById('contact_banner_image_preview_container');
        const placeholder = document.getElementById('contact_banner_image_placeholder');
        const removeButton = document.getElementById('contact_banner_image_remove_button');
        const hiddenInput = document.getElementById('contact_banner_image');
        const removeFlagInput = document.getElementById('remove_contact_banner_image');
        
        input.value = null; // Clear the file input
        preview.src = '';
        previewContainer.style.display = 'none';
        placeholder.style.display = 'block';
        removeButton.style.display = 'none';
        hiddenInput.value = '/assets/images/Ecommerce-bg.jpg'; // Reset to default
        removeFlagInput.value = '1'; // Mark that we want to remove/reset the image
    }
</script> 