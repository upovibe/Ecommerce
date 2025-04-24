<?php
session_start();
require_once '../config/settings.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Page specific variables
$pageTitle = "Account Settings";
$breadcrumbs = [
    ['name' => $pageTitle] // Current page - no URL needed
];

// Get current username from session
$currentUsername = $_SESSION['admin_username'] ?? 'Admin'; 
$adminId = $_SESSION['admin_id'] ?? 0; // Get admin ID

// Fetch current profile data
$profileData = [
    'full_name' => '',
    'gender' => '',
    'about' => ''
];
if ($db_connected && $conn && $adminId > 0) {
    $stmt = $conn->prepare("SELECT full_name, gender, about FROM admin_users WHERE id = ?");
    $stmt->bind_param('i', $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $profileData = $row; // Overwrite defaults with DB data
    }
    $stmt->close();
}

// Get store settings for the title
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Load Alpine core -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <?php include_once 'includes/admin_navbar.php'; ?>

    <!-- Main Content Area -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Breadcrumbs -->
        <?php include_once 'includes/breadcrumbs.php'; ?>

        <!-- Header -->
        <div class="mb-8">
             <div class="shrink-0 space-y-0.5 w-fit">
                 <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2"><i data-lucide="shield-user" class="h-6 w-6"></i> <?= htmlspecialchars($pageTitle) ?></h1>
             <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full mb-4"></div>
             </div>
        </div>

        <!-- Alpine Tab Component -->
        <div x-data="{
             activeTab: 'details',
             // Add profile data to Alpine state
             profile: {
                 full_name: '<?php echo htmlspecialchars(addslashes($profileData['full_name'] ?? '')); ?>',
                 gender: '<?php echo htmlspecialchars(addslashes($profileData['gender'] ?? '')); ?>',
                 about: '<?php echo htmlspecialchars(addslashes($profileData['about'] ?? '')); ?>'
             },
             profileLoading: false
         }" class="mt-6">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                    <button 
                        @click="activeTab = 'details'" 
                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'details', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'details' }"
                        class="flex items-center whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm focus:outline-none">
                        <i data-lucide="user-cog" class="w-4 h-4 mr-2"></i>
                        Details
                    </button>
                    <button 
                        @click="activeTab = 'username'" 
                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'username', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'username' }"
                        class="flex items-center whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm focus:outline-none">
                        <i data-lucide="user-check" class="w-4 h-4 mr-2"></i>
                        Username
                    </button>
                    <button 
                        @click="activeTab = 'security'" 
                        :class="{ 'border-blue-500 text-blue-600': activeTab === 'security', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'security' }"
                        class="flex items-center whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm focus:outline-none">
                        <i data-lucide="key" class="w-4 h-4 mr-2"></i>
                        Password
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Account Details Tab -->
                <div x-show="activeTab === 'details'" x-cloak x-transition>
                    <div class="bg-white shadow-md rounded-lg overflow-hidden p-6 border border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-3 flex items-center">
                            <i data-lucide="user" class="w-5 h-5 mr-2 text-gray-500"></i>
                             Details
                         </h2>
                         <div class="space-y-5">
                              <div>
                                 <label class="block text-sm font-medium text-gray-500 mb-1">Current Username</label>
                                 <div class="relative">
                                     <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                         <i data-lucide="at-sign" class="w-5 h-5 text-gray-400"></i>
                                     </span>
                                     <input type="text" value="<?= htmlspecialchars($currentUsername) ?>" 
                                            class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed focus:outline-none sm:text-sm" disabled>
                                 </div>
                                 <p class="text-xs text-gray-500 mt-1">Username displayed from current session.</p> 
                              </div>

                              <!-- Profile Fields Injected Here -->
                              <hr class="my-6 border-gray-200"> <!-- Divider -->
                              <h3 class="text-md font-semibold text-gray-700 mb-3 flex items-center">
                                 <i data-lucide="user-round-cog" class="w-4 h-4 mr-2 text-gray-500"></i>
                                 Profile Information
                              </h3>
                              <form id="profileUpdateForm" @submit.prevent="updateProfile($event)" class="space-y-5">
                                  <div>
                                      <label for="profile_full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                      <input type="text" id="profile_full_name" x-model="profile.full_name"
                                             class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                             placeholder="Enter your full name">
                                  </div>
                                   <div>
                                      <label for="profile_gender" class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                                      <select id="profile_gender" x-model="profile.gender"
                                              class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm appearance-none cursor-pointer">
                                          <option value="">-- Select Gender --</option>
                                          <option value="Male">Male</option>
                                          <option value="Female">Female</option>
                                          <option value="Other">Other</option>
                                          <option value="Prefer not to say">Prefer not to say</option>
                                      </select>
                                  </div>
                                  <div>
                                      <label for="profile_about" class="block text-sm font-medium text-gray-700 mb-1">About You</label>
                                      <textarea id="profile_about" rows="4" x-model="profile.about"
                                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"
                                                placeholder="Tell us a little about yourself (optional)"></textarea>
                                  </div>
                                  <div class="pt-3 text-right">
                                       <button type="submit" id="updateProfileBtn"
                                               class="inline-flex items-center justify-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50"
                                               :disabled="profileLoading">
                                           <span x-show="!profileLoading" class="flex items-center">
                                               <i data-lucide="save" class="w-5 h-5 mr-2"></i> Update Profile
                                           </span>
                                           <span x-show="profileLoading" class="flex items-center">
                                               <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                   <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                   <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                               </svg>
                                               Saving...
                                           </span>
                                       </button>
                                  </div>
                              </form>
                              <p class="text-xs text-gray-500 mt-3 text-right">This information helps personalize your admin experience.</p>
                               <!-- End Profile Fields -->
                         </div>
                     </div>
                 </div>

                <!-- Change Username Tab -->
                 <div x-show="activeTab === 'username'" x-cloak x-transition>
                     <div class="bg-white shadow-md rounded-lg overflow-hidden p-6 border border-gray-100">
                         <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-3 flex items-center">
                             <i data-lucide="user-check" class="w-5 h-5 mr-2 text-gray-500"></i>
                            Change Username
                        </h2>
                         <form id="usernameChangeForm" class="space-y-5">
                             <div>
                                 <label for="account_new_username" class="block text-sm font-medium text-gray-700 mb-1">New Username <span class="text-red-500">*</span></label>
                                 <div class="relative">
                                     <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                         <i data-lucide="user-plus" class="w-5 h-5 text-blue-500"></i>
                                     </span>
                                     <input type="text" id="account_new_username" name="new_username" required
                                            class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                            placeholder="Enter new username">
                                 </div>
                                 <p id="account-new-username-error" class="text-red-600 text-xs mt-1 hidden"></p>
                             </div>
                             <div>
                                 <label for="account_confirm_password_for_username" class="block text-sm font-medium text-gray-700 mb-1">Confirm with Current Password <span class="text-red-500">*</span></label>
                                 <div class="relative">
                                     <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                         <i data-lucide="key" class="w-5 h-5 text-blue-500"></i>
                                     </span>
                                     <input type="password" id="account_confirm_password_for_username" name="current_password" required
                                            class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200" 
                                            placeholder="Enter current password">
                                     <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                             onclick="togglePasswordVisibility('account_confirm_password_for_username')" tabindex="-1">
                                         <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                                     </button>
                                 </div>
                                 <p id="account-confirm-password-username-error" class="text-red-600 text-xs mt-1 hidden"></p>
                             </div>
                             <div class="pt-3 text-right">
                                  <button type="submit" id="changeUsernameBtn"
                                          class="inline-flex items-center justify-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50">
                                      <i data-lucide="save" class="w-5 h-5 mr-2"></i>
                                      Update Username
                                  </button>
                             </div>
                         </form>
                         <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                             <div class="flex items-start">
                                  <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0"></i>
                                  <p class="text-sm text-yellow-700">
                                     <strong class="font-medium">Important:</strong> Changing your username will log you out immediately. You will need to log back in with the new username and your current password.
                                  </p>
                             </div>
                         </div>
                    </div>
                 </div>

                <!-- Security Tab (Change Password) -->
                <div x-show="activeTab === 'security'" x-cloak x-transition>
                    <div class="bg-white shadow-md rounded-lg overflow-hidden p-6 border border-gray-100">
                         <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-3 flex items-center">
                             <i data-lucide="key" class="w-5 h-5 mr-2 text-gray-500"></i>
                            Change Password
                        </h2>
                         <form id="passwordChangeForm" class="space-y-5">
                             <input type="hidden" name="action" value="update_password_account_page"> 
                             <div>
                                 <label for="account_current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password <span class="text-red-500">*</span></label>
                                 <div class="relative">
                                     <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                         <i data-lucide="key" class="h-5 w-5 text-blue-500"></i>
                                     </div>
                                     <input type="password" name="current_password" id="account_current_password" required
                                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Enter your current password">
                                     <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                             onclick="togglePasswordVisibility('account_current_password')" tabindex="-1">
                                         <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                                     </button>
                                 </div>
                             </div>
                             <div>
                                 <label for="account_new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password <span class="text-red-500">*</span></label>
                                 <div class="relative">
                                     <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                         <i data-lucide="lock" class="h-5 w-5 text-blue-500"></i>
                                     </div>
                                     <input type="password" name="new_password" id="account_new_password" required
                                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Minimum 6 characters">
                                     <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                             onclick="togglePasswordVisibility('account_new_password')" tabindex="-1">
                                         <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                                     </button>
                                 </div>
                                 <p id="account-new-password-error" class="text-red-600 text-xs mt-1 hidden"></p> 
                             </div>
                             <div>
                                 <label for="account_confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password <span class="text-red-500">*</span></label>
                                 <div class="relative">
                                     <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                         <i data-lucide="shield-check" class="h-5 w-5 text-blue-500"></i>
                                     </div>
                                     <input type="password" name="confirm_password" id="account_confirm_password" required
                                            class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            placeholder="Confirm your new password">
                                     <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                             onclick="togglePasswordVisibility('account_confirm_password')" tabindex="-1">
                                         <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                                     </button>
                                 </div>
                                 <p id="account-confirm-password-error" class="text-red-600 text-xs mt-1 hidden"></p> 
                             </div>
                             <div class="pt-3 text-right">
                                 <button type="submit" id="changePasswordBtn"
                                         class="inline-flex items-center justify-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50">
                                     <i data-lucide="save" class="w-5 h-5 mr-2"></i>
                                     Update Password
                                 </button>
                             </div>
                         </form>
                         <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                             <div class="flex items-start">
                                  <i data-lucide="shield-alert" class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0"></i>
                                  <p class="text-sm text-yellow-700">
                                     <strong class="font-medium">Security Reminder:</strong> Use a strong, unique password that you don't use elsewhere. Never share your password. Changing your password will log you out immediately.
                                  </p>
                             </div>
                         </div>
                    </div>
                </div>
            </div> <!-- End Tab Content -->
        </div> <!-- End Alpine Tab Component -->

    </div> <!-- End Main Content Area -->

    <!-- Include toast notification component -->
    <?php include_once '../includes/toast.php'; ?>

<!-- Link the external JS file -->
<script src="assets/js/accountManager.js" defer></script>
</body>
</html> 