<?php
// Simple Admin Navbar
// Ensure settings are available if needed for logo/name
if (file_exists(__DIR__ . '/../../config/settings.php')) {
    require_once __DIR__ . '/../../config/settings.php';
}
$adminThemeColor = defined('STORE_SETTINGS') && isset(STORE_SETTINGS['theme_color']) ? STORE_SETTINGS['theme_color'] : '#1F2937'; // Default bg
$brandTextColor = defined('STORE_SETTINGS') && isset(STORE_SETTINGS['brand_text_color']) ? STORE_SETTINGS['brand_text_color'] : '#FFFFFF'; // Default text
?>
<nav class="shadow-md" style="background-color: <?= htmlspecialchars($adminThemeColor) ?>;">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo/Store Name -->
            <a href="dashboard.php" class="flex items-center font-bold text-lg" style="color: <?= htmlspecialchars($brandTextColor) ?>;">
                <?php 
                    $logoPathSimple = defined('STORE_SETTINGS') && isset(STORE_SETTINGS['store_logo']) ? STORE_SETTINGS['store_logo'] : null;
                    $storeNameSimple = defined('STORE_SETTINGS') && isset(STORE_SETTINGS['store_name']) ? STORE_SETTINGS['store_name'] : 'Admin';
                    if ($logoPathSimple && !empty($logoPathSimple)):
                ?>
                    <img src="<?= htmlspecialchars($logoPathSimple) ?>" alt="Logo" class="h-8 max-h-8 mr-2 object-contain">
                <?php else: ?>
                    <i data-lucide="shield" class="w-5 h-5 mr-2" style="color: <?= htmlspecialchars($brandTextColor) ?>;"></i>
                    <span><?= htmlspecialchars($storeNameSimple) ?></span>
                <?php endif; ?>
            </a>

            <!-- Right Links -->
            <div class="flex items-center space-x-4">
                <!-- View Store Link -->
                <a href="../index.php" target="_blank" 
                   class="hidden sm:flex items-center hover:opacity-80 text-sm" 
                   style="color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.8;" 
                   title="View Store">
                    <i data-lucide="external-link" class="w-5 h-5 mr-1" style="color: <?= htmlspecialchars($brandTextColor) ?>;"></i>
                    <span>View Store</span>
                </a>

                <!-- Logout Link -->
                 <a href="logout.php" 
                    class="hidden sm:flex items-center hover:opacity-80 text-sm" 
                    style="color: <?= htmlspecialchars($brandTextColor) ?>; opacity: 0.8;" 
                    title="Logout">
                     <i data-lucide="log-out" class="w-5 h-5 mr-1" style="color: <?= htmlspecialchars($brandTextColor) ?>;"></i>
                     <span>Logout</span>
                 </a>
            </div>
        </div>
    </div>
</nav>

<!-- Simple Navbar - No Alpine/JS needed specifically for this basic version -->
<!-- Ensure Lucide script is loaded on the parent page --> 