<?php
// Get current favicon path
$faviconPath = STORE_SETTINGS['store_favicon'] ?? '/assets/images/favicon.ico';
?>

<div id="faviconSettingsModal" class="fixed inset-0 overflow-y-auto z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeFaviconModal()"></div>
        
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Update Favicon
                        </h3>
                        
                        <form action="utils/update_favicon.php" method="POST" enctype="multipart/form-data" class="space-y-6 mt-4" id="faviconSettingsForm">
                            <div class="flex flex-col items-center bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-200">
                                <div class="w-full h-32 flex items-center justify-center mb-4">
                                    <?php 
                                    $faviconVersion = isset($_SESSION['favicon_version']) ? "?v=" . $_SESSION['favicon_version'] : '';
                                    if ($faviconPath && file_exists(__DIR__ . '/../../' . ltrim($faviconPath, '/'))): 
                                    ?>
                                        <img src="<?= htmlspecialchars('../' . ltrim($faviconPath, '/') . $faviconVersion) ?>" alt="Store Favicon" class="max-h-32 max-w-full object-contain" id="faviconPreview">
                                    <?php else: ?>
                                        <div class="text-gray-300 flex flex-col items-center" id="faviconPreview">
                                            <i data-lucide="image" class="w-12 h-12 mb-2"></i>
                                            <span class="text-sm">No favicon uploaded</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="w-full">
                                    <label class="w-full flex flex-col items-center px-4 py-4 bg-white rounded-lg border-2 border-gray-300 border-dashed cursor-pointer hover:bg-gray-50 transition-colors">
                                        <i data-lucide="upload-cloud" class="w-8 h-8 text-blue-500 mb-2"></i>
                                        <span class="text-sm font-medium text-gray-700">Click to upload favicon</span>
                                        <span class="text-xs text-gray-500 mt-1">ICO, PNG, JPG up to 1MB</span>
                                        <input type="file" name="favicon" class="hidden" accept=".ico,.png,.jpg,.jpeg" required onchange="previewFavicon(this)">
                                    </label>
                                    <p class="mt-2 text-xs text-center text-gray-500">Recommended size: 32x32 pixels for ICO, 192x192 pixels for PNG/JPG</p>
                                </div>
                            </div>
                            
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button type="submit" id="faviconSubmitButton" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    <svg id="faviconLoadingSpinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <i data-lucide="save" id="faviconSaveIcon" class="w-5 h-5 mr-2"></i>
                                    <span id="faviconButtonText">Save Changes</span>
                                </button>
                                <button type="button" onclick="closeFaviconModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewFavicon(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const previewContainer = document.getElementById('faviconPreview');
            
            if (previewContainer.tagName === 'IMG') {
                previewContainer.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.id = 'faviconPreview';
                img.className = 'max-h-32 max-w-full object-contain';
                img.alt = 'Store Favicon';
                previewContainer.parentNode.replaceChild(img, previewContainer);
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Add form submission handling
document.getElementById('faviconSettingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const button = document.getElementById('faviconSubmitButton');
    const spinner = document.getElementById('faviconLoadingSpinner');
    const saveIcon = document.getElementById('faviconSaveIcon');
    const buttonText = document.getElementById('faviconButtonText');

    // Prevent multiple submissions
    if (button.disabled) {
        return;
    }

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
            toast.success(result.message || 'Favicon updated successfully!');
            // Update the preview image in the Brand Identity section if it exists
            const brandIdentityPreview = document.querySelector('#brandIdentityFaviconPreview');
            if (brandIdentityPreview) {
                brandIdentityPreview.src = result.path + '?v=' + new Date().getTime(); // Add cache buster
            }
            // Update the preview in the modal as well
            const modalPreview = document.getElementById('faviconPreview');
            if (modalPreview.tagName === 'IMG') {
                modalPreview.src = result.path + '?v=' + new Date().getTime();
            }
            
            // Close the modal after a short delay
            setTimeout(() => {
                closeFaviconModal();
                window.location.reload(); // Reload the page to show updated favicon
            }, 1500);
        } else {
            toast.error(result.message || 'Failed to update favicon.');
            // Reset button state
            button.disabled = false;
            spinner.classList.add('hidden');
            saveIcon.classList.remove('hidden');
            buttonText.textContent = 'Save Changes';
        }
    } catch (error) {
        console.error('Error:', error);
        toast.error('An unexpected error occurred during upload.');
        // Reset button state
        button.disabled = false;
        spinner.classList.add('hidden');
        saveIcon.classList.remove('hidden');
        buttonText.textContent = 'Save Changes';
    }
});
</script> 