<?php
session_start();
require_once '../config/settings.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Page specific variables
$pageTitle = "Settings";
$breadcrumbs = [
    ['name' => $pageTitle] // Current page - no URL needed
];

// Get current store settings from constant
$storeName = STORE_SETTINGS['store_name'] ?? '';
$storeDescription = STORE_SETTINGS['store_description'] ?? '';
$whatsappNumber = STORE_SETTINGS['whatsapp_number'] ?? '';
$currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '';
$themeColor = STORE_SETTINGS['theme_color'] ?? '#3B82F6';
$logoPath = STORE_SETTINGS['store_logo'] ?? '';
$whatsappTemplate = STORE_SETTINGS['whatsapp_message_template'] ?? '';
$footerText = STORE_SETTINGS['footer_text'] ?? '© ' . date('Y') . ' ' . ($storeName ?: 'E-Commerce Store') . '. All rights reserved.';
$brandTextColor = STORE_SETTINGS['brand_text_color'] ?? '#FFFFFF';

// Get current content settings from database
$storeContent = [];
if ($db_connected && $conn) {
    $result = $conn->query("SELECT content_key, content_value FROM store_content");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $storeContent[$row['content_key']] = $row['content_value'];
        }
    }
}

// Assign content values with empty string fallbacks
$heroTitle = $storeContent['hero_title'] ?? '';
$heroSubtitle = $storeContent['hero_subtitle'] ?? '';
$aboutTitle = $storeContent['about_title'] ?? '';
$aboutContent = $storeContent['about_content'] ?? '';
$featuredTitle = $storeContent['featured_title'] ?? '';
$featuredSubtitle = $storeContent['featured_subtitle'] ?? '';
$heroImage = $storeContent['hero_image'] ?? '/assets/images/demo/hero-bg.jpg'; // Fetch hero image with fallback
$aboutImage = $storeContent['about_image'] ?? '/assets/images/demo/about-image.jpg'; // Fetch about image with fallback

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <?php include_once 'includes/admin_navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumbs -->
        <?php include_once 'includes/breadcrumbs.php'; ?>

        <div class="mb-8">
            <div class="shrink-0 space-y-0.5 w-fit">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-2"><i data-lucide="settings" class="h-6 w-6"></i> <?= htmlspecialchars($pageTitle) ?></h1>
                <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full mb-4"></div>
            </div>
            <p class="text-gray-600">Manage your store settings and appearance.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <?php include_once 'includes/settings_brand_identity.php'; ?>

            <div class="lg:col-span-2" x-data="{
                    tab: new URLSearchParams(window.location.search).get('tab') === 'content' ? 'content' : 'general',
                    isSavingGeneral: false,
                    isSavingContent: false,

                    async saveGeneralSettings(event) {
                        this.isSavingGeneral = true;
                        const formData = new FormData(event.target); 
                        
                        try {
                            const response = await fetch('utils/update_settings.php', {
                                method: 'POST',
                                body: formData
                            });
                            const result = await response.json();
                            if (result.success) {
                                toast.success(result.message || 'General settings updated successfully!');
                                // Maybe update displayed values if needed, or rely on refresh
                                setTimeout(() => { window.location.reload(); }, 1000); // Reload after 1 second to show toast
                            } else {
                                toast.error(result.message || 'Failed to update general settings.');
                            }
                        } catch (error) {
                            console.error('Error saving general settings:', error);
                            toast.error('An unexpected error occurred.');
                        } finally {
                            this.isSavingGeneral = false;
                        }
                    },

                    async saveStoreContent(event) {
                        this.isSavingContent = true;
                        const formData = new FormData(event.target);
                        
                        try {
                            const response = await fetch('utils/update_content.php', {
                                method: 'POST',
                                body: formData
                            });
                            const result = await response.json();
                            if (result.success) {
                                toast.success(result.message || 'Store content updated successfully!');
                            } else {
                                toast.error(result.message || 'Failed to update store content.');
                            }
                        } catch (error) {
                            console.error('Error saving store content:', error);
                            toast.error('An unexpected error occurred.');
                        } finally {
                            this.isSavingContent = false;
                        }
                    }
                }">
                <div class="border-b border-gray-200 mb-4">
                    <nav class="flex space-x-4">
                        <button
                            class="flex items-center px-4 py-2 text-sm font-medium rounded-t-lg focus:outline-none"
                            :class="tab === 'general' ? 'bg-white border-l border-t border-r text-blue-600 shadow-sm' : 'text-gray-500 hover:text-blue-600'"
                            @click="tab = 'general'">
                            <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                            General
                        </button>
                        <button
                            class="flex items-center px-4 py-2 text-sm font-medium rounded-t-lg focus:outline-none"
                            :class="tab === 'content' ? 'bg-white border-l border-t border-r text-blue-600 shadow-sm' : 'text-gray-500 hover:text-blue-600'"
                            @click="tab = 'content'">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                            Store
                        </button>
                    </nav>
                </div>

                <div class="tab-content relative">
                    <div x-show="tab === 'general'" x-cloak
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150 absolute w-full"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95">
                        <?php include_once 'includes/settings_general_form.php'; ?>
                    </div>
                    <div x-show="tab === 'content'" x-cloak
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-95"
                        x-transition:enter-end="opacity-100 transform scale-100"
                        x-transition:leave="transition ease-in duration-150 absolute w-full"
                        x-transition:leave-start="opacity-100 transform scale-100"
                        x-transition:leave-end="opacity-0 transform scale-95">
                        <?php include_once 'includes/settings_store_content.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once '../includes/toast.php'; ?>

    <!-- Include logo settings modal -->
    <?php include_once 'modals/logo_settings_modal.php'; ?>

    <?php
    // Include change password modal for navbar trigger
    if (!defined('ALLOW_ACCESS')) {
        define('ALLOW_ACCESS', true);
    }
    include_once 'modals/change_password_modal.php';
    ?>

    <!-- JavaScript for Modal Control -->
    <script>
        function openLogoModal() {
            const modal = document.getElementById('logoSettingsModal');
            if (modal) {
                modal.classList.remove('hidden');
                // Re-render icons if needed when modal opens
                if (typeof lucide !== 'undefined') {
                    setTimeout(() => lucide.createIcons(), 50); // Delay ensures elements are visible
                }
            } else {
                console.error('Logo settings modal not found!');
            }
        }

        function closeLogoModal() {
            const modal = document.getElementById('logoSettingsModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Attach close listener to the close button inside the modal
            const closeButton = document.getElementById('closeLogoModal');
            if (closeButton) {
                closeButton.addEventListener('click', closeLogoModal);
            }

            // Existing URL parameter handling logic...
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');

            if (tabParam === 'logo') {
                openLogoModal(); // Directly call the function to open
            }
        });
    </script>
</body>

</html>