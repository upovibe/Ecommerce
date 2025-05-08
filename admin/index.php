<?php
session_start();
require_once '../config/settings.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Check if installation is needed
if (!$db_connected) {
    // Check if install.php exists
    if (file_exists('../install.php')) {
        header('Location: ../install.php');
        exit;
    }
}

$error = '';
$installSuccess = false;
$installMessage = '';

// Get installation success message if it exists
if (isset($_SESSION['install_success']) && $_SESSION['install_success']) {
    $installSuccess = true;
    $installMessage = $_SESSION['install_message'] ?? 'Installation completed successfully!';
    // Clear the installation messages
    unset($_SESSION['install_success'], $_SESSION['install_message']);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($db_connected && $conn) {
        // Fetch id along with other fields
        $stmt = $conn->prepare("SELECT id, username, password_hash, password_changed FROM admin_users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                // Set admin session
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = (int)$row['id'];
                $_SESSION['admin_username'] = $username;
                $_SESSION['password_changed'] = (bool)$row['password_changed'];
                $_SESSION['password_needs_change'] = !(bool)$row['password_changed'];
                
                // Write session data and close the session before redirecting
                session_write_close();
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'Invalid username or password';
    } else {
        $error = 'Database connection error';
    }
}

// Get store settings for the title
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com/"></script>
    <link href="../assets/css/animations.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen relative">
    <!-- Background image with overlay -->
    <div class="fixed inset-0 z-0"> 
        <img src="../assets/images/phirmhostImg.png" alt="" class="w-screen h-screen object-cover object-top ">
    </div> 
     <!-- logo -->
     <div class="text-center mr-[10em]  ">
                <img src="../assets/images/phirmhostLogo.png" alt="Store Logo" class="mx-auto h-20 mb-6 drop-shadow-2xl w-[395px] h-[151px]">
     </div>  
    <!-- Main content -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10 ">
        <div class="max-w-md w-full h-full space-y-8 ml-auto mr-[10em] mb-auto mt-[10em]">
            <!-- Logo Section - removed animation -->
            
                <!-- <div class="h-1 w-24 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full mb-4"></div> -->
               
            

            <?php if ($installSuccess): ?>
            <div class="mb-6 rounded-lg bg-green-50 p-4 border-l-4 border-green-500 animate-fade-in">
                <div class="flex">
                    <i data-lucide="check-circle" class="h-5 w-5 text-green-500 mr-2"></i>
                    <div>
                        <p class="font-medium text-green-800">Installation Successful!</p>
                        <p class="text-sm text-green-700 mt-1"><?= htmlspecialchars($installMessage) ?></p>
                        <div class="mt-2 bg-white bg-opacity-50 rounded p-2">
                            <p class="text-sm font-medium text-gray-700">Default Credentials:</p>
                            <div class="grid grid-cols-2 gap-2 mt-1 text-sm">
                                <div>Username: <span class="font-medium">admin</span></div>
                                <div>Password: <span class="font-medium">admin123</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-white shadow-2xl rounded-xl overflow-hidden  h-[400px]">
            <h1 class="text-3xl font-bold text-white mb-2 drop-shadow-lg">Admin Login</h1>
            <p class="text-blue-100"><?= htmlspecialchars($storeName) ?> Dashboard</p>
                <div class="p-8">
                    <?php if ($error): ?>
                        <div class="mb-6 rounded-lg bg-red-50 p-4 border-l-4 border-red-500 animate-shake">
                            <div class="flex">
                                <i data-lucide="alert-circle" class="h-5 w-5 text-red-500 mr-2"></i>
                                <p class="text-red-700"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-6" id="loginForm">
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="user" class="h-5 w-5 text-blue-500"></i>
                                </div>
                                <input type="text" id="username" name="username" required
                                    class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter your username">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i data-lucide="key" class="h-5 w-5 text-blue-500"></i>
                                </div>
                                <input type="password" id="password" name="password" required
                                    class="pl-10 w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter your password">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-600 hover:text-blue-500 transition-colors"
                                        onclick="togglePasswordVisibility('password')"
                                        tabindex="-1">
                                    <i data-lucide="eye" class="h-5 w-5" data-visible="false"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" id="loginButton"
                            class="w-full flex items-center justify-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" id="loadingSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <i data-lucide="log-in" class="w-5 h-5 mr-2" id="loginIcon"></i>
                            <span id="buttonText">Login to Dashboard</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center">
                <a href="../index.php" class="inline-flex items-center text-blue-300 hover:text-blue-200 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Back to Store
                </a>
            </div>

            <?php if (!$db_connected): ?>
            <div class="bg-amber-50 rounded-lg p-4 border border-amber-100 animate-fade-in">
                <div class="flex items-start">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500 mt-0.5 mr-2"></i>
                    <div>
                        <p class="font-medium text-amber-800">Setup Required</p>
                        <p class="text-sm text-amber-700 mt-1">Database connection is not configured.</p>
                        <?php if (file_exists('../install.php')): ?>
                            <a href="../install.php" class="inline-block mt-2 text-blue-600 hover:text-blue-700 font-medium text-sm">
                                Run the installer →
                            </a>
                        <?php else: ?>
                            <p class="text-sm text-amber-700 mt-1">Please check your database configuration in config/db.php</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('[data-lucide]');
        const isVisible = icon.getAttribute('data-visible') === 'true';
        
        // Toggle password visibility
        input.type = isVisible ? 'password' : 'text';
        icon.setAttribute('data-visible', !isVisible);
        
        // Update icon
        icon.setAttribute('data-lucide', isVisible ? 'eye' : 'eye-off');
        
        // Update Lucide icons
        lucide.createIcons();
    }
    lucide.createIcons();

    // Add form submission handling
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const button = document.getElementById('loginButton');
        const spinner = document.getElementById('loadingSpinner');
        const loginIcon = document.getElementById('loginIcon');
        const buttonText = document.getElementById('buttonText');

        // Disable the button and show loading state
        button.disabled = true;
        spinner.classList.remove('hidden');
        loginIcon.classList.add('hidden');
        buttonText.textContent = 'Logging in...';
    });
    </script>
</body>
</html>