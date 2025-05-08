<?php
// This component requires the $db_connected variable to be set in the including page's scope.

// Show database connection notice if needed
if (!isset($db_connected) || !$db_connected):
?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4" data-aos="fade-down">
    <div class="flex items-center justify-center">
        <div class="flex-shrink-0">
            <i data-lucide="alert-triangle" class="h-5 w-5 text-yellow-400"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-yellow-700">
                <strong>Note:</strong> The site is running in demo mode because the database connection failed. Demo products and content are being displayed. To connect to a database, please <a href="/install.php" class="text-yellow-800 underline hover:text-yellow-900">run the installation script</a> or check your configuration in <code>config/db.php</code>.
            </p>
        </div>
    </div>
</div>
<?php endif; ?> 