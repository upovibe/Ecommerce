<?php
// Prevent direct access
if (!defined('ALLOW_ACCESS')) {
    exit('Direct access not permitted');
}
?>
<!-- Force Password Change Modal with Glassy Overlay -->
<!-- DEBUG: password_needs_change = <?php var_dump($_SESSION['password_needs_change'] ?? 'Not Set'); ?> -->
<div id="changePasswordModal" 
     class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center <?php echo (isset($_SESSION['password_needs_change']) && $_SESSION['password_needs_change']) ? '' : 'hidden'; ?>">
    <div class="relative mx-auto p-8 border w-[480px] shadow-2xl rounded-xl bg-white/95 backdrop-blur-xl">
        <div>
            <div class="text-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Change Your Password</h3>
                <div class="h-1 w-24 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full"></div>
            </div>
            
            <div>
                <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-md flex items-start">
                    <i data-lucide="alert-triangle" class="h-6 w-6 text-yellow-500 mr-3 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <h4 class="font-bold text-yellow-700 mb-1">IMPORTANT SECURITY NOTICE</h4>
                        <p class="text-yellow-700">
                            For the security of your account and store data, you must change your default password before continuing.
                        </p>
                    </div>
                </div>

                <form id="passwordChangeForm" class="space-y-6">
                    <input type="hidden" name="action" value="change_password">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="key" class="h-5 w-5 text-blue-500"></i>
                            </div>
                            <input type="password" name="current_password" id="current_password" required
                                   class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                   placeholder="Enter your current password">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                    onclick="togglePasswordVisibility('current_password')"
                                    tabindex="-1">
                                <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="h-5 w-5 text-blue-500"></i>
                            </div>
                            <input type="password" name="new_password" id="new_password" required
                                   class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                   placeholder="Enter your new password">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                    onclick="togglePasswordVisibility('new_password')"
                                    tabindex="-1">
                                <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                            </button>
                        </div>
                        <p id="new-password-error" class="text-red-600 text-xs mt-1 hidden"></p>
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="shield-check" class="h-5 w-5 text-blue-500"></i>
                            </div>
                            <input type="password" name="confirm_password" id="confirm_password" required
                                   class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                   placeholder="Confirm your new password">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                    onclick="togglePasswordVisibility('confirm_password')"
                                    tabindex="-1">
                                <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                            </button>
                        </div>
                        <p id="confirm-password-error" class="text-red-600 text-xs mt-1 hidden"></p>
                    </div>
                    <div class="flex items-center gap-4">
                        <button type="button" onclick="window.location.href='logout.php'" 
                                class="w-full flex items-center justify-center px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transform hover:-translate-y-0.5 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i data-lucide="log-out" class="w-5 h-5 mr-2"></i>
                            Logout
                        </button>
                        <button type="submit" id="changePasswordBtn"
                                class="w-full flex items-center justify-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl">
                            <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/passwordModalHandler.js" defer></script>

<?php
// The closing tags should be handled by the including file (e.g., dashboard.php)
// Do not add closing </body> or </html> here.
?>