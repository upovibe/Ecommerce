<!-- Logo Section -->
<div class="lg:col-span-1">
    <div class="bg-white shadow-lg hover:shadow-xl transition-shadow duration-200 rounded-xl p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i data-lucide="image" class="w-5 h-5 mr-2 text-blue-500"></i>
                Brand Identity
            </h2>
        </div>
        
        <div class="space-y-6">
            <div class="flex flex-col items-center bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-200">
                <div class="w-full h-40 flex items-center justify-center mb-4">
                    <?php 
                    $logoVersion = isset($_SESSION['logo_version']) ? "?v=" . $_SESSION['logo_version'] : '';
                    if ($logoPath && file_exists(__DIR__ . '/../../' . ltrim($logoPath, '/'))): 
                    ?>
                        <img src="<?= htmlspecialchars('../' . ltrim($logoPath, '/') . $logoVersion) ?>" alt="Store Logo" class="max-h-40 max-w-full object-contain" id="brandIdentityLogoPreview">
                    <?php else: ?>
                        <div class="text-gray-300 flex flex-col items-center" id="brandIdentityLogoPreview">
                            <i data-lucide="shopping-bag" class="w-16 h-16 mb-2"></i>
                            <span class="text-sm">No logo uploaded</span>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="openLogoModal()" 
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2.5 px-4 rounded-lg text-sm inline-flex items-center justify-center transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i data-lucide="pen" class="w-4 h-4 mr-2"></i>
                    Change Logo
                </button>
                <p class="text-xs text-gray-400 text-center mt-2 truncate w-full"><?= htmlspecialchars($logoPath) ?></p>
            </div>
            
            <!-- Favicon Upload -->
            <div class="flex flex-col items-center bg-gray-50 rounded-xl p-6 border-2 border-dashed border-gray-200">
                <div class="w-full h-32 flex items-center justify-center mb-4">
                    <?php 
                    $faviconVersion = isset($_SESSION['favicon_version']) ? "?v=" . $_SESSION['favicon_version'] : '';
                    $faviconPath = STORE_SETTINGS['store_favicon'] ?? '/assets/images/favicon.ico';
                    if ($faviconPath && file_exists(__DIR__ . '/../../' . ltrim($faviconPath, '/'))): 
                    ?>
                        <img src="<?= htmlspecialchars('../' . ltrim($faviconPath, '/') . $faviconVersion) ?>" alt="Store Favicon" class="max-h-32 max-w-full object-contain" id="brandIdentityFaviconPreview">
                    <?php else: ?>
                        <div class="text-gray-300 flex flex-col items-center" id="brandIdentityFaviconPreview">
                            <i data-lucide="image" class="w-12 h-12 mb-2"></i>
                            <span class="text-sm">No favicon uploaded</span>
                        </div>
                    <?php endif; ?>
                </div>
                <button type="button" onclick="openFaviconModal()" 
                    class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2.5 px-4 rounded-lg text-sm inline-flex items-center justify-center transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i data-lucide="pen" class="w-4 h-4 mr-2"></i>
                    Change Favicon
                </button>
                <p class="text-xs text-gray-400 text-center mt-2 truncate w-full"><?= htmlspecialchars($faviconPath) ?></p>
            </div>

        </div>
    </div>
</div>

<script>
function openFaviconModal() {
    document.getElementById('faviconSettingsModal').classList.remove('hidden');
}

function closeFaviconModal() {
    document.getElementById('faviconSettingsModal').classList.add('hidden');
}
</script>

<?php
// Include the favicon settings modal
require_once __DIR__ . '/../modals/favicon_settings_modal.php';
?>