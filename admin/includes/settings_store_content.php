<div class="bg-white shadow-lg hover:shadow-xl transition-shadow duration-200 rounded-xl p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
        <i data-lucide="file-text" class="w-5 h-5 mr-2 text-blue-500"></i>
        Store Content
    </h2>
    <form id="contentForm" method="POST" @submit.prevent="saveStoreContent" class="space-y-6">
        
        <!-- Hero Section -->
        <fieldset class="space-y-4 border-t pt-4">
            <legend class="text-md font-medium text-gray-600 mb-2">Hero Section</legend>
            <div>
                <label for="hero_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="hero_title" id="hero_title" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    value="<?= htmlspecialchars($heroTitle) ?>">
            </div>
            <div>
                <label for="hero_subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                <textarea name="hero_subtitle" id="hero_subtitle" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    ><?= htmlspecialchars($heroSubtitle) ?></textarea>
            </div>
        </fieldset>

        <!-- Hero Image Section -->
        <fieldset class="space-y-4 border-t pt-4">
            <legend class="text-md font-medium text-gray-600 mb-2">Hero Background Image</legend>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Image (Optional)</label>
            <label for="hero_image" id="hero_image_dropzone"
                   class="relative mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200 min-h-[150px]">

                <!-- Placeholder Content -->
                <div id="hero_image_placeholder" class="space-y-1 text-center" <?= !empty($heroImage) ? 'style="display: none;"' : '' ?>>
                    <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400"></i>
                    <div class="flex text-sm text-gray-600">
                        <span class="relative bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                            <span>Upload a file</span>
                            <input type="file" name="hero_image" id="hero_image" accept="image/jpeg, image/png, image/webp, image/gif"
                                   onchange="previewSettingsImage('hero_image', 'hero_image_preview', 'hero_image_placeholder', 'hero_image_remove_button', '<?= htmlspecialchars($heroImage ?? '') ?>')"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        </span>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 2MB. Rec: 1920x1080</p>
                </div>

                <!-- Image Preview -->
                <div id="hero_image_preview_container" class="relative w-full h-full flex justify-center items-center" <?= empty($heroImage) ? 'style="display: none;"' : '' ?>>
                    <img id="hero_image_preview" src="<?= htmlspecialchars($heroImage ?? '') ?>" alt="Hero Image Preview"
                         class="max-h-48 max-w-full rounded-lg object-contain shadow-sm">
                    <button type="button" id="hero_image_remove_button" onclick="removeSettingsImage('hero_image', 'hero_image_preview', 'hero_image_placeholder', 'hero_image_remove_button', 'remove_hero_image', '<?= htmlspecialchars($heroImage ?? '') ?>')"
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 z-10"
                            title="Remove Image">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </div>
                <input type="hidden" name="remove_hero_image" id="remove_hero_image" value="0">
            </label>
            <p class="text-xs text-gray-500 mt-1">Current path (if set): <?= !empty($heroImage) ? htmlspecialchars($heroImage) : 'None' ?></p>
        </fieldset>

        <!-- Featured Section -->
        <fieldset class="space-y-4 border-t pt-4">
             <legend class="text-md font-medium text-gray-600 mb-2">Featured Products Section</legend>
             <div>
                <label for="featured_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="featured_title" id="featured_title" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    value="<?= htmlspecialchars($featuredTitle) ?>">
            </div>
            <div>
                <label for="featured_subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                <textarea name="featured_subtitle" id="featured_subtitle" rows="2" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    ><?= htmlspecialchars($featuredSubtitle) ?></textarea>
            </div>
        </fieldset>

        <!-- About Section -->
        <fieldset class="space-y-4 border-t pt-4">
            <legend class="text-md font-medium text-gray-600 mb-2">About Section</legend>
            <div>
                <label for="about_title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="about_title" id="about_title" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    value="<?= htmlspecialchars($aboutTitle) ?>">
            </div>
            <div>
                <label for="about_content" class="block text-sm font-medium text-gray-700 mb-1">Content (HTML allowed)</label>
                <textarea name="about_content" id="about_content" rows="6"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    ><?= htmlspecialchars($aboutContent) ?></textarea>
            </div>
        </fieldset>

        <!-- About Image Section -->
        <fieldset class="space-y-4 border-t pt-4">
            <legend class="text-md font-medium text-gray-600 mb-2">About Section Image</legend>
            
            <label class="block text-sm font-medium text-gray-700 mb-1">Image (Optional)</label>
            <label for="about_image" id="about_image_dropzone"
                   class="relative mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200 min-h-[150px]">

                <!-- Placeholder Content -->
                <div id="about_image_placeholder" class="space-y-1 text-center" <?= !empty($aboutImage) ? 'style="display: none;"' : '' ?>>
                    <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400"></i>
                    <div class="flex text-sm text-gray-600">
                        <span class="relative bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                            <span>Upload a file</span>
                            <input type="file" name="about_image" id="about_image" accept="image/jpeg, image/png, image/webp, image/gif"
                                   onchange="previewSettingsImage('about_image', 'about_image_preview', 'about_image_placeholder', 'about_image_remove_button', '<?= htmlspecialchars($aboutImage ?? '') ?>')"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        </span>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 2MB. Rec: 4:3 or 16:9</p>
                </div>

                <!-- Image Preview -->
                <div id="about_image_preview_container" class="relative w-full h-full flex justify-center items-center" <?= empty($aboutImage) ? 'style="display: none;"' : '' ?>>
                     <img id="about_image_preview" src="<?= htmlspecialchars($aboutImage ?? '') ?>" alt="About Image Preview"
                         class="max-h-48 max-w-full rounded-lg object-contain shadow-sm">
                    <button type="button" id="about_image_remove_button" onclick="removeSettingsImage('about_image', 'about_image_preview', 'about_image_placeholder', 'about_image_remove_button', 'remove_about_image', '<?= htmlspecialchars($aboutImage ?? '') ?>')"
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 z-10"
                            title="Remove Image">
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </div>
                 <input type="hidden" name="remove_about_image" id="remove_about_image" value="0">
            </label>
             <p class="text-xs text-gray-500 mt-1">Current path (if set): <?= !empty($aboutImage) ? htmlspecialchars($aboutImage) : 'None' ?></p>
        </fieldset>

        <!-- Product Page Banner Image Section -->
        <fieldset class="space-y-4 border-t pt-4">
            <legend class="text-md font-medium text-gray-600 mb-2">Product Page Banner</legend>
            
             <div>
                <label for="product_banner_title" class="block text-sm font-medium text-gray-700 mb-1">Banner Title</label>
                <input type="text" name="product_banner_title" id="product_banner_title" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    value="<?= htmlspecialchars($productBannerTitle) ?>">
            </div>
            <div>
                <label for="product_banner_subtitle" class="block text-sm font-medium text-gray-700 mb-1">Banner Subtitle</label>
                <textarea name="product_banner_subtitle" id="product_banner_subtitle" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                    ><?= htmlspecialchars($productBannerSubtitle) ?></textarea>
            </div>

            <label class="block text-sm font-medium text-gray-700 mb-1 pt-2">Banner Image (Optional)</label>
            <label for="product_page_banner_image" id="product_page_banner_image_dropzone"
                   class="relative mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200 min-h-[150px]">

                <!-- Placeholder Content -->
                <div id="product_page_banner_image_placeholder" class="space-y-1 text-center" <?= !empty($productPageBannerImage) ? 'style="display: none;"' : '' ?>>
                    <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400"></i>
                    <div class="flex text-sm text-gray-600">
                        <span class="relative bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                            <span>Upload a file</span>
                            <input type="file" name="product_page_banner_image" id="product_page_banner_image" accept="image/jpeg, image/png, image/webp, image/gif"
                                   onchange="previewSettingsImage('product_page_banner_image', 'product_page_banner_image_preview', 'product_page_banner_image_placeholder', 'product_page_banner_image_remove_button', '<?= htmlspecialchars($productPageBannerImage ?? '') ?>')"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        </span>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 2MB. Rec: 1920x300 (Wide Banner)</p>
                </div>

                <!-- Image Preview -->
                <div id="product_page_banner_image_preview_container" class="relative w-full h-full flex justify-center items-center" <?= empty($productPageBannerImage) ? 'style="display: none;"' : '' ?>>
                     <img id="product_page_banner_image_preview" src="<?= htmlspecialchars($productPageBannerImage ?? '') ?>" alt="Product Page Banner Preview"
                         class="max-h-48 max-w-full rounded-lg object-contain shadow-sm">
                    <button type="button" id="product_page_banner_image_remove_button" onclick="removeSettingsImage('product_page_banner_image', 'product_page_banner_image_preview', 'product_page_banner_image_placeholder', 'product_page_banner_image_remove_button', 'remove_product_page_banner_image', '<?= htmlspecialchars($productPageBannerImage ?? '') ?>')"
                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 z-10"
                            title="Remove Image" <?= empty($productPageBannerImage) ? 'style="display: none;"' : '' ?>>
                        <i data-lucide="x" class="w-3 h-3"></i>
                    </button>
                </div>
                 <input type="hidden" name="remove_product_page_banner_image" id="remove_product_page_banner_image" value="0">
            </label>
             <p class="text-xs text-gray-500 mt-1">Current path (if set): <?= !empty($productPageBannerImage) ? htmlspecialchars($productPageBannerImage) : 'None' ?></p>
        </fieldset>

        <div class="pt-6 border-t border-gray-100 flex justify-end">
            <button type="submit" id="contentSubmitButton" :disabled="isSavingContent"
                    class="flex items-center px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" :class="{'hidden': !isSavingContent}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <i data-lucide="save" class="w-5 h-5 mr-2" :class="{'hidden': isSavingContent}"></i>
                <span x-text="isSavingContent ? 'Saving...' : 'Save Content'"></span>
            </button>
        </div>
    </form>
</div> 

<script>
function previewSettingsImage(inputId, previewId, placeholderId, removeButtonId, originalImagePath) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const previewContainer = preview.parentNode; // Get the container div
    const placeholder = document.getElementById(placeholderId);
    const removeButton = document.getElementById(removeButtonId);
    const file = input.files[0];
    const removeFlagInput = document.getElementById('remove_' + inputId); // Get the hidden input

    if (file) {
        // Check size (e.g., 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Image size exceeds 2MB limit.');
            input.value = null; // Clear the input
            // Optionally revert to original preview if exists
             if (originalImagePath) {
                preview.src = originalImagePath;
                previewContainer.style.display = 'flex';
                placeholder.style.display = 'none';
                removeButton.style.display = 'block';
                if(removeFlagInput) removeFlagInput.value = '0'; 
            } else {
                preview.src = '';
                previewContainer.style.display = 'none';
                placeholder.style.display = 'block';
                removeButton.style.display = 'none';
                 if(removeFlagInput) removeFlagInput.value = '0'; 
            }
            return;
        }
        
        // Check type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
             alert('Invalid image file type (PNG, JPG, GIF, WEBP allowed).');
             input.value = null; // Clear the input
             // Optionally revert preview
             if (originalImagePath) {
                preview.src = originalImagePath;
                previewContainer.style.display = 'flex';
                placeholder.style.display = 'none';
                removeButton.style.display = 'block';
                 if(removeFlagInput) removeFlagInput.value = '0'; 
            } else {
                preview.src = '';
                previewContainer.style.display = 'none';
                placeholder.style.display = 'block';
                removeButton.style.display = 'none';
                if(removeFlagInput) removeFlagInput.value = '0'; 
            }
            return;
        }

        // Use FileReader to preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'flex'; // Show preview container
            placeholder.style.display = 'none'; // Hide placeholder
            removeButton.style.display = 'block'; // Show remove button
             if(removeFlagInput) removeFlagInput.value = '0'; // File selected, so don't remove
        }
        reader.readAsDataURL(file);
    } else {
        // No file selected (e.g., user cancelled) - revert to original or empty state
        if (originalImagePath) {
            preview.src = originalImagePath;
            previewContainer.style.display = 'flex';
            placeholder.style.display = 'none';
            removeButton.style.display = 'block';
             if(removeFlagInput) removeFlagInput.value = '0'; 
        } else {
            preview.src = '';
            previewContainer.style.display = 'none';
            placeholder.style.display = 'block';
            removeButton.style.display = 'none';
            if(removeFlagInput) removeFlagInput.value = '0'; 
        }
    }
}

function removeSettingsImage(inputId, previewId, placeholderId, removeButtonId, removeFlagInputId, originalImagePath) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const previewContainer = preview.parentNode;
    const placeholder = document.getElementById(placeholderId);
    const removeButton = document.getElementById(removeButtonId);
    const removeFlagInput = document.getElementById(removeFlagInputId);

    input.value = null; // Clear the file input
    preview.src = ''; // Clear the preview image source
    previewContainer.style.display = 'none'; // Hide preview container
    placeholder.style.display = 'block'; // Show placeholder
    removeButton.style.display = 'none'; // Hide remove button

    if (originalImagePath) {
        // If there was an original image, set the flag to remove it
        if(removeFlagInput) removeFlagInput.value = '1';
    } else {
         // If there was no original image, ensure flag is 0
        if(removeFlagInput) removeFlagInput.value = '0';
    }
}

// Ensure Lucide icons are rendered if this include is loaded dynamically
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    // Initial setup in case there's already an image on load
    const heroImageInput = document.getElementById('hero_image');
    if (heroImageInput && '<?= !empty($heroImage) ?>') {
         document.getElementById('hero_image_placeholder').style.display = 'none';
         document.getElementById('hero_image_preview_container').style.display = 'flex';
         document.getElementById('hero_image_remove_button').style.display = 'block';
    }
    const aboutImageInput = document.getElementById('about_image');
     if (aboutImageInput && '<?= !empty($aboutImage) ?>') {
         document.getElementById('about_image_placeholder').style.display = 'none';
         document.getElementById('about_image_preview_container').style.display = 'flex';
         document.getElementById('about_image_remove_button').style.display = 'block';
    }
    const productBannerInput = document.getElementById('product_page_banner_image');
     if (productBannerInput && '<?= !empty($productPageBannerImage) ?>') {
         document.getElementById('product_page_banner_image_placeholder').style.display = 'none';
         document.getElementById('product_page_banner_image_preview_container').style.display = 'flex';
         document.getElementById('product_page_banner_image_remove_button').style.display = 'block';
    }
});

</script> 