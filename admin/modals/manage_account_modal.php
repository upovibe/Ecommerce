<?php
// Ensure this file is included by a script that starts the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define ALLOW_ACCESS if not already defined
// This is a security measure to prevent direct access to modal files
if (!defined('ALLOW_ACCESS')) {
    define('ALLOW_ACCESS', true); 
}

// Get current username from session
$currentUsername = $_SESSION['admin_username'] ?? 'Admin'; 
?>
<!-- Manage Account Modal with Accordion & Glassy Overlay -->
<div 
    id="manageAccountModal"
    x-show="isManageAccountModalOpen" 
    x-data="{ 
        // Accordion state
        openSection: '', // 'username' or 'password'

        // Form fields
        currentUsername: '<?php echo htmlspecialchars($currentUsername); ?>',
        newUsername: '', 
        confirmUsernamePassword: '', // Password to confirm username change
        currentPassword: '', // For password change
        newPassword: '',
        confirmPassword: '',

        // Feedback and loading
        isLoadingUsername: false,
        isLoadingPassword: false,

        // Methods
        toggleSection(section) {
            this.openSection = this.openSection === section ? '' : section;
        },

        async submitUsernameChange() {
            if (!this.newUsername || !this.confirmUsernamePassword) {
                toast.error('Please enter the new username and your current password.');
                return;
            }

            this.isLoadingUsername = true;
            const body = {
                new_username: this.newUsername,
                current_password: this.confirmUsernamePassword
            };

            try {
                const response = await fetch('utils/manage_username.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }, 
                    body: JSON.stringify(body)
                });

                const data = await response.json();

                if (data.success) {
                    toast.success(data.message || 'Username updated successfully!');
                    this.newUsername = '';
                    this.confirmUsernamePassword = '';
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
                    toast.error(data.message || 'Failed to update username. Check password?');
                }
            } catch (error) {
                console.error('Error updating username:', error);
                toast.error('An error occurred while updating username. Please try again.');
            } finally {
                this.isLoadingUsername = false;
            }
        },

        async submitPasswordChange() {
            if (this.newPassword !== this.confirmPassword) {
                toast.error('New passwords do not match.');
                return;
            }
             if (!this.currentPassword || !this.newPassword) {
                toast.error('Please fill in all password fields.');
                return;
            }

            this.isLoadingPassword = true;
            const body = {
                current_password: this.currentPassword,
                new_password: this.newPassword
            };

            try {
                const response = await fetch('utils/manage_password.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }, 
                    body: JSON.stringify(body)
                });

                const data = await response.json();

                if (data.success) {
                    toast.success(data.message || 'Password changed successfully!');
                    this.currentPassword = '';
                    this.newPassword = '';
                    this.confirmPassword = '';
                    this.openSection = '';
                } else {
                    toast.error(data.message || 'Failed to change password.');
                }
            } catch (error) {
                console.error('Error changing password:', error);
                toast.error('An error occurred while changing password. Please try again.');
            } finally {
                this.isLoadingPassword = false;
            }
        },

        closeModal() {
             isManageAccountModalOpen = false; // Access parent scope variable
             // Reset form state on close
             this.openSection = '';
             this.newUsername = '';
             this.confirmUsernamePassword = '';
             this.currentPassword = '';
             this.newPassword = '';
             this.confirmPassword = '';
             this.isLoadingUsername = false;
             this.isLoadingPassword = false;
        }
    }"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 bg-gray-900 bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center p-4"
    @keydown.escape.window="closeModal()"
    style="display: none;" x-cloak>
    
    <!-- Modal Content -->
    <div 
        class="relative mx-auto p-6 border w-full max-w-lg shadow-2xl rounded-xl bg-white/95 backdrop-blur-xl"
        @click.outside="closeModal()">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6 border-b pb-4">
            <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                <i data-lucide="user-cog" class="w-6 h-6 mr-2 text-blue-600"></i>
                Manage Account
            </h2>
            <button @click="closeModal()" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="space-y-4">

            <!-- Accordion Section: Change Username -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" @click="toggleSection('username')" class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 focus:outline-none">
                    <span class="font-semibold text-gray-700">Change Username</span>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" :class="{ '-rotate-180': openSection === 'username' }"></i>
                </button>
                <div x-show="openSection === 'username'" x-collapse x-cloak class="overflow-hidden transition-[max-height] duration-300 ease-in-out">
                    <form @submit.prevent="submitUsernameChange()" class="p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Username</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i data-lucide="user" class="w-5 h-5 text-gray-400"></i>
                                </span>
                                <input type="text" x-bind:value="currentUsername" 
                                       class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed focus:outline-none sm:text-sm" disabled>
                            </div>
                        </div>
                        <div>
                            <label for="manage-account-new-username" class="block text-sm font-medium text-gray-700 mb-1">New Username</label>
                            <div class="relative">
                                 <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i data-lucide="user-plus" class="w-5 h-5 text-blue-500"></i>
                                </span>
                                <input type="text" id="manage-account-new-username" x-model="newUsername" 
                                       class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                       placeholder="Enter new username" required>
                            </div>
                        </div>
                         <div>
                            <label for="manage-account-confirm-password-for-username" class="block text-sm font-medium text-gray-700 mb-1">Confirm with Current Password</label>
                            <div class="relative">
                                 <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i data-lucide="key" class="w-5 h-5 text-blue-500"></i>
                                </span>
                                <input type="password" id="manage-account-confirm-password-for-username" x-model="confirmUsernamePassword" 
                                       class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                       placeholder="Enter current password" required>
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                        onclick="togglePasswordVisibility('manage-account-confirm-password-for-username')"
                                        tabindex="-1">
                                    <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                             <button type="submit" :disabled="isLoadingUsername" 
                                    class="w-full flex items-center justify-center px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50">
                                <span x-show="!isLoadingUsername" class="flex items-center">
                                    <i data-lucide="save" class="w-5 h-5 mr-2"></i> Save New Username
                                </span>
                                 <span x-show="isLoadingUsername" class="flex items-center">
                                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Accordion Section: Change Password -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <button type="button" @click="toggleSection('password')" class="w-full flex justify-between items-center p-4 bg-gray-50 hover:bg-gray-100 focus:outline-none">
                    <span class="font-semibold text-gray-700">Change Password</span>
                     <i data-lucide="chevron-down" class="w-5 h-5 text-gray-500 transform transition-transform duration-200" :class="{ '-rotate-180': openSection === 'password' }"></i>
                </button>
                <div x-show="openSection === 'password'" x-collapse x-cloak class="overflow-hidden transition-[max-height] duration-300 ease-in-out">
                    <form @submit.prevent="submitPasswordChange()" class="p-4 space-y-4">
                         <div>
                             <label for="manage-account-current-password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                             <div class="relative">
                                 <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i data-lucide="key" class="w-5 h-5 text-blue-500"></i>
                                </span>
                                <input type="password" id="manage-account-current-password" x-model="currentPassword" 
                                       class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" placeholder="Current Password" required>
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                        onclick="togglePasswordVisibility('manage-account-current-password')"
                                        tabindex="-1">
                                    <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                                </button>
                            </div>
                        </div>
                         <div>
                             <label for="manage-account-new-password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                             <div class="relative">
                                 <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i data-lucide="lock" class="w-5 h-5 text-blue-500"></i>
                                </span>
                                 <input type="password" id="manage-account-new-password" x-model="newPassword" 
                                       class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" placeholder="New Password" required>
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                        onclick="togglePasswordVisibility('manage-account-new-password')"
                                        tabindex="-1">
                                    <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                             <label for="manage-account-confirm-password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <div class="relative">
                                 <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i data-lucide="shield-check" class="w-5 h-5 text-blue-500"></i>
                                </span>
                                 <input type="password" id="manage-account-confirm-password" x-model="confirmPassword" 
                                       class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" placeholder="Confirm New Password" required>
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                        onclick="togglePasswordVisibility('manage-account-confirm-password')"
                                        tabindex="-1">
                                    <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                                </button>
                            </div>
                        </div>
                         <div>
                             <button type="submit" :disabled="isLoadingPassword" 
                                    class="w-full flex items-center justify-center px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transform hover:-translate-y-0.5 transition-all duration-200 shadow-md hover:shadow-lg disabled:opacity-50">
                                <span x-show="!isLoadingPassword" class="flex items-center">
                                    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> Update Password
                                </span>
                                <span x-show="isLoadingPassword" class="flex items-center">
                                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Updating...
                                </span>
                             </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Footer (Optional - can keep simple close button) -->
        <div class="flex justify-end items-center pt-6 mt-6 border-t border-gray-200">
            <button type="button" @click="closeModal()" class="px-5 py-2 bg-gray-100 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Close
            </button>
             <!-- Removed specific save buttons from footer, handled within accordions -->
        </div>
    </div>
</div>
<!-- End Manage Account Modal --> 

<script>
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    // Find the button by searching for the specific onclick attribute value for this inputId
    const button = input.closest('.relative').querySelector(`button[onclick="togglePasswordVisibility('${inputId}')"]`); 
    if (!button) {
        console.error('Could not find toggle button for input:', inputId);
        return; 
    }
    const icon = button.querySelector('[data-lucide]');
    if (!icon) return;

    const isVisible = icon.getAttribute('data-visible') === 'true';

    // Toggle password visibility
    input.type = isVisible ? 'password' : 'text';
    icon.setAttribute('data-visible', !isVisible);

    // Update icon
    icon.setAttribute('data-lucide', isVisible ? 'eye' : 'eye-off');

    // Update Lucide icons (ensure lucide library is available)
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
}
</script> 