<?php
// This component requires the $db_connected variable to be set in the including page's scope.

// Show database connection notice if needed
if (!isset($db_connected) || !$db_connected):
?>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4" data-aos="fade-down">
    <div class="flex">
        <div class="flex-shrink-0">
            <!-- Assuming lucide icons are loaded globally or you have a way to load them -->
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle text-yellow-400"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
        </div>
        <div class="ml-3">
            <p class="text-sm text-yellow-700">
                <strong>Note:</strong> The site is running in demo mode because the database connection failed. Demo products and content are being displayed. To connect to a database, please check your configuration in <code>config/db.php</code>.
            </p>
        </div>
    </div>
</div>
<?php endif; ?> 