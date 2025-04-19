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
                        <img src="<?= htmlspecialchars('../' . ltrim($logoPath, '/') . $logoVersion) ?>" alt="Store Logo" class="max-h-40 max-w-full object-contain">
                    <?php else: ?>
                        <div class="text-gray-300 flex flex-col items-center">
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
            
            <div>
                <label for="theme_color" class="block text-sm font-medium text-gray-700 mb-1">Brand Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="theme_color" id="theme_color" 
                        class="h-[42px] w-[60px] rounded-lg border border-gray-300 p-1 cursor-pointer bg-white"
                        value="<?= htmlspecialchars($themeColor) ?>" form="settingsForm">
                    <div class="flex-1">
                        <input type="text" id="color_text"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 uppercase font-mono text-sm bg-white"
                            value="<?= htmlspecialchars($themeColor) ?>" readonly>
                        
                    </div>
                </div>
                <p class="mt-1.5 text-xs text-gray-500">This color will be used throughout the store</p>
            </div>
        </div>
    </div>
</div> 