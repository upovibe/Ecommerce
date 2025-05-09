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
<body class="relative min-h-screen">
    <!-- Background image with dark overlay -->
    <div class="fixed inset-0 z-0">
        <img src="../assets/images/phirmhostImg.png" alt="" class="object-cover object-top w-full h-full brightness-75">
        <div class="absolute inset-0 bg-black/30"></div>
    </div>
    <!-- Logo and Title -->
    <div class="absolute z-10 flex flex-col items-center transform -translate-x-1/2 top-10 left-1/2">
        <img src="../assets/images/phirmhostLogo.png" alt="Store Logo" class="mx-auto h-20 mb-2 drop-shadow-2xl w-[220px]">
    </div>
    <!-- Main content -->
    <div class="relative z-10 flex items-center justify-end min-h-screen px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md mr-24">
            <div class="overflow-hidden bg-white shadow-2xl rounded-2xl">
                <div class="p-10">
                    <h1 class="mb-1 text-3xl font-bold text-center text-gray-900">Admin Login</h1>
                    <p class="mb-8 text-base text-center text-gray-600">E-commerce Store Dashboard</p>
                    <?php if ($error): ?>
                        <div class="p-4 mb-6 border-l-4 border-red-500 rounded-lg bg-red-50 animate-shake">
                            <div class="flex">
                                <i data-lucide="alert-circle" class="w-5 h-5 mr-2 text-red-500"></i>
                                <p class="text-red-700"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="" class="space-y-6" id="loginForm">
                        <div>
                            <label for="username" class="block mb-1 text-sm font-bold text-gray-700">Username</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i data-lucide="user" class="w-5 h-5 text-blue-500"></i>
                                </div>
                                <input type="text" id="username" name="username" required
                                    class="w-full px-3 py-2 pl-10 transition-all duration-200 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter your username">
                            </div>
                        </div>
                        <div>
                            <label for="password" class="block mb-1 text-sm font-bold text-gray-700">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i data-lucide="lock" class="w-5 h-5 text-blue-500"></i>
                                </div>
                                <input type="password" id="password" name="password" required
                                    class="w-full px-3 py-2 pl-10 transition-all duration-200 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="Enter your password">
                                <button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-600 transition-colors hover:text-blue-500"
                                        onclick="togglePasswordVisibility('password')"
                                        tabindex="-1">
                                    <i data-lucide="eye" class="w-5 h-5" data-visible="false"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" id="loginButton"
                            class="w-full flex items-center justify-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed text-lg font-semibold">
                            <i data-lucide="log-in" class="w-5 h-5 mr-2" id="loginIcon"></i>
                            <span id="buttonText">Login to dashboard</span>
                        </button>
                        </div>
                       
                    </form>
                    <div class="mt-8 text-center">
                        <a href="../index.php" class="inline-flex items-center text-base font-medium text-gray-500 transition-colors hover:text-blue-600">
                            <i data-lucide="arrow-left" class="w-5 h-5 mr-2"></i>
                            Back to store
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('[data-lucide]');
        const isVisible = icon.getAttribute('data-visible') === 'true';
        input.type = isVisible ? 'password' : 'text';
        icon.setAttribute('data-visible', !isVisible);
        icon.setAttribute('data-lucide', isVisible ? 'eye' : 'eye-off');
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
    </script>
</body>
</html>