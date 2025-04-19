<?php
// Logo Settings Modal
?>
<div id="logoSettingsModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Glassmorphism overlay -->
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>

        <!-- This element centers the modal content -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="absolute top-0 right-0 pt-4 pr-4">
                <button type="button" id="closeLogoModal" class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                    <span class="sr-only">Close</span>
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <div class="p-6">
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Update Store Logo</h3>
                    <div class="h-1 w-24 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full"></div>
                </div>

                <form action="utils/update_logo.php" method="POST" enctype="multipart/form-data" class="space-y-6" id="logoSettingsForm">
                    <div class="flex flex-col items-center bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-200">
                        <div class="w-full h-40 flex items-center justify-center mb-4">
                            <?php 
                            $logoVersion = isset($_SESSION['logo_version']) ? "?v=" . $_SESSION['logo_version'] : '';
                            // Use correct relative path for admin area
                            $logoDisplayPath = ($logoPath && file_exists(__DIR__ . '/../../' . ltrim($logoPath, '/'))) ? '../' . ltrim($logoPath, '/') : null; 
                            if ($logoDisplayPath): 
                            ?>
                                <img src="<?= htmlspecialchars($logoDisplayPath . $logoVersion) ?>" alt="Store Logo" class="max-h-40 max-w-full object-contain" id="logoPreview">
                            <?php else: ?>
                                <div class="text-gray-300 flex flex-col items-center" id="logoPreview">
                                    <i data-lucide="image" class="w-16 h-16 mb-2"></i>
                                    <span class="text-sm">No logo uploaded</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="w-full">
                            <label class="w-full flex flex-col items-center px-4 py-4 bg-white rounded-lg border-2 border-gray-300 border-dashed cursor-pointer hover:bg-gray-50 transition-colors">
                                <i data-lucide="upload-cloud" class="w-8 h-8 text-blue-500 mb-2"></i>
                                <span class="text-sm font-medium text-gray-700">Click to upload logo</span>
                                <span class="text-xs text-gray-500 mt-1">PNG, JPG up to 2MB</span>
                                <input type="file" name="logo" class="hidden" accept="image/*" required onchange="previewImage(this)">
                            </label>
                            <p class="mt-2 text-xs text-center text-gray-500">Recommended size: 200x200 pixels</p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6 border-t border-gray-100">
                        <button type="button" onclick="document.getElementById('logoSettingsModal').classList.add('hidden')" 
                            class="mr-3 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </button>
                        <button type="submit" id="logoSubmitButton" class="px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl inline-flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" id="logoLoadingSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <i data-lucide="save" class="w-4 h-4 mr-2" id="logoSaveIcon"></i>
                            <span id="logoButtonText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const previewContainer = document.getElementById('logoPreview');
            
            if (previewContainer.tagName === 'IMG') {
                previewContainer.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.id = 'logoPreview';
                img.className = 'max-h-40 max-w-full object-contain';
                img.alt = 'Store Logo';
                previewContainer.parentNode.replaceChild(img, previewContainer);
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Add form submission handling
document.getElementById('logoSettingsForm').addEventListener('submit', async function(e) {
    e.preventDefault(); // Prevent default form submission

    const form = e.target;
    const formData = new FormData(form);
    const button = document.getElementById('logoSubmitButton');
    const spinner = document.getElementById('logoLoadingSpinner');
    const saveIcon = document.getElementById('logoSaveIcon');
    const buttonText = document.getElementById('logoButtonText');

    // Disable the button and show loading state
    button.disabled = true;
    spinner.classList.remove('hidden');
    saveIcon.classList.add('hidden');
    buttonText.textContent = 'Saving...';

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            toast.success(result.message || 'Logo updated successfully!');
            // Update the preview image in the Brand Identity section if it exists
            const brandIdentityPreview = document.querySelector('#brandIdentityLogoPreview'); // Need to add this ID
            if (brandIdentityPreview) {
                brandIdentityPreview.src = result.newLogoPath + '?v=' + new Date().getTime(); // Add cache buster
            }
            // Update the preview in the modal as well
            const modalPreview = document.getElementById('logoPreview');
             if (modalPreview.tagName === 'IMG') {
                modalPreview.src = result.newLogoPath + '?v=' + new Date().getTime();
            } // Handle case where it was placeholder
            
            // Close the modal after a short delay
            setTimeout(() => {
                document.getElementById('logoSettingsModal').classList.add('hidden');
            }, 1500);
        } else {
            toast.error(result.message || 'Failed to update logo.');
        }
    } catch (error) {
        console.error('Error uploading logo:', error);
        toast.error('An unexpected error occurred during upload.');
    } finally {
        // Re-enable button and reset state
        button.disabled = false;
        spinner.classList.add('hidden');
        saveIcon.classList.remove('hidden');
        buttonText.textContent = 'Save Changes';
    }
});

// Initialize Lucide icons
lucide.createIcons();
</script>