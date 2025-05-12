<?php

// Check if installation appears complete
if (file_exists('config/db.php')) {
    // Include config to get credentials
    require_once 'config/db.php'; 
    
    // Check if DB connection was successful AND admin table exists
    if ($db_connected && $conn) {
        $result = $conn->query("SHOW TABLES LIKE 'admin_users'");
        if ($result && $result->num_rows > 0) {
            // Looks like a complete installation, redirect to login
            $conn->close(); // Close connection before redirect
            header('Location: admin/index.php');
            exit;
        } else {
            // Config exists, but DB setup seems incomplete (e.g., admin table missing)
            // Allow install.php to proceed, potentially overwriting existing partial setup.
             if ($conn) $conn->close();
        }
    } else {
    }
}

/**
 * E-Commerce Template Database Installation Script
 * 
 * Simple database setup script - creates database and tables with default values
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$error = '';
$success = '';
$dbInfo = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'dbname' => 'ecommerce_template',
];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbInfo['host'] = $_POST['db_host'] ?? 'localhost';
    $dbInfo['user'] = $_POST['db_user'] ?? '';
    $dbInfo['password'] = $_POST['db_password'] ?? '';
    $dbInfo['dbname'] = $_POST['db_name'] ?? 'ecommerce_template';

    try {
        // Test database connection
        $conn = new mysqli($dbInfo['host'], $dbInfo['user'], $dbInfo['password']);

        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Create database if doesn't exist
        if (!$conn->query("CREATE DATABASE IF NOT EXISTS `{$dbInfo['dbname']}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            throw new Exception("Error creating database: " . $conn->error);
        }

        // Select the database
        if (!$conn->select_db($dbInfo['dbname'])) {
            throw new Exception("Error selecting database: " . $conn->error);
        }

        // Create database configuration file
        $configContent = "<?php
define('DB_HOST', '{$dbInfo['host']}');
define('DB_USER', '{$dbInfo['user']}');
define('DB_PASSWORD', '{$dbInfo['password']}');
define('DB_NAME', '{$dbInfo['dbname']}');

\$conn = null;
\$db_connected = false;

try {
    \$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (!\$conn->connect_error) {
        \$db_connected = true;
        \$conn->set_charset('utf8mb4');
    }
} catch (Exception \$e) {
    error_log('Database connection error: ' . \$e->getMessage());
    \$conn = null;
    \$db_connected = false;
}";

        $configFile = 'config/db.php';
        if (file_put_contents($configFile, $configContent) === false) {
            throw new Exception("Could not write to config file. Please check file permissions.");
        }

        // Create tables
        $sqlFile = file_get_contents('config/database.sql');

        // Remove database creation commands
        $sqlFile = preg_replace('/DROP DATABASE.*?;/i', '', $sqlFile);
        $sqlFile = preg_replace('/CREATE DATABASE.*?;/i', '', $sqlFile);
        $sqlFile = preg_replace('/USE.*?;/i', '', $sqlFile);

        // Split and execute SQL statements
        $statements = array_filter(
            array_map('trim', explode(';', $sqlFile)),
            function ($sql) {
                return !empty($sql);
            }
        );

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                if (!$conn->query($statement)) {
                    throw new Exception("Error executing SQL: " . $conn->error);
                }
            }
        }

        // Set default admin credentials
        $default_username = 'admin';
        $default_password = 'admin123';
        $password_hash = password_hash($default_password, PASSWORD_DEFAULT);

        // Create admin_users table
        $adminTableSQL = "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            password_changed BOOLEAN NOT NULL DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        if (!$conn->query($adminTableSQL)) {
            throw new Exception("Error creating admin_users table: " . $conn->error);
        }

        // Insert default admin user
        $stmt = $conn->prepare("INSERT INTO admin_users (username, password_hash, password_changed) VALUES (?, ?, FALSE)");
        $stmt->bind_param('ss', $default_username, $password_hash);
        if (!$stmt->execute()) {
            throw new Exception("Error creating admin user: " . $stmt->error);
        }

        $success = "Database setup completed successfully! Log in to the admin dashboard to configure your store.";
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - E-Commerce Template</title>
    <script src="https://cdn.tailwindcss.com/"></script>
    <link href="assets/css/animations.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="min-h-screen relative">
    <!-- Background image with overlay -->
    <div class="fixed inset-0 z-0">
        <img src="assets/images/Ecommerce-bg.jpg" alt="" class="w-full h-full object-cover brightness-[0.15]">
    </div>

    <!-- Main content -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="max-w-lg w-full space-y-8">
            <!-- Logo Section - removed animation -->
            <div class="text-center">
                <img src="assets/images/PHIRMHOST LOGO FINAL ORG BK.png" alt="Phirmhost Logo" class="mx-auto h-20 mb-6 drop-shadow-2xl">
                <h1 class="text-3xl font-bold text-white mb-2 drop-shadow-lg">Database Setup</h1>
                <div class="h-1 w-24 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full mb-4"></div>
                <p class="text-blue-100">Configure your database connection to get started</p>
            </div>

            <?php if ($error): ?>
                <div class="rounded-xl bg-white shadow-xl p-4 animate-shake border-l-4 border-red-500">
                    <div class="flex items-center">
                        <i data-lucide="alert-circle" class="w-5 h-5 mr-2 text-red-500"></i>
                        <p class="text-gray-700"><?= htmlspecialchars($error) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-white shadow-2xl rounded-xl overflow-hidden">
                    <div class="p-6">
                        <div class="text-center">
                            <!-- Success Icon with animation -->
                            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4 animate-checkmark">
                                <i data-lucide="check" class="w-8 h-8 text-green-500"></i>
                            </div>

                            <!-- Success Message with animation -->
                            <h3 class="text-2xl font-bold text-gray-800 mb-1 animate-fade-in">Setup Complete!</h3>
                            <p class="text-gray-600 text-sm mb-6 animate-fade-in delay-100"><?= htmlspecialchars($success) ?></p>

                            <!-- Credentials Box -->
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100 mb-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="text-left">
                                        <div class="flex items-center space-x-2 text-gray-600">
                                            <i data-lucide="user" class="w-4 h-4"></i>
                                            <span class="text-sm">Username</span>
                                        </div>
                                        <p class="text-gray-900 font-medium ml-6">admin</p>
                                    </div>
                                    <div class="text-left">
                                        <div class="flex items-center space-x-2 text-gray-600">
                                            <i data-lucide="key" class="w-4 h-4"></i>
                                            <span class="text-sm">Password</span>
                                        </div>
                                        <p class="text-gray-900 font-medium ml-6">admin123</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Login Button -->
                            <a href="admin/index.php"
                                class="inline-flex items-center px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i data-lucide="log-in" class="w-5 h-5 mr-2"></i>
                                Go to Admin Login
                            </a>

                            <!-- Security Notice -->
                            <div class="mt-4 text-left">
                                <div class="bg-amber-50 rounded-lg p-3 border border-amber-100">
                                    <div class="flex items-start">
                                        <i data-lucide="shield-alert" class="w-4 h-4 mr-2 flex-shrink-0 text-amber-500 mt-0.5"></i>
                                        <div>
                                            <p class="font-medium text-amber-800 mb-1">Important Security Steps:</p>
                                            <ul class="space-y-0.5 text-amber-700 text-sm ml-4">
                                                <li class="list-disc">Change admin password after login</li>
                                                <li class="list-disc">Keep credentials secure</li>
                                                <li class="list-disc">Remove install.php file</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white shadow-2xl rounded-xl overflow-hidden">
                    <div class="p-6">
                        <form method="POST" action="">
                            <div class="space-y-5">
                                <div>
                                    <label for="db_host" class="block text-sm font-medium text-gray-700 mb-1">Database Host</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="database" class="h-5 w-5 text-blue-500"></i>
                                        </div>
                                        <input type="text" id="db_host" name="db_host"
                                            class="pl-10 w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            value="localhost" required>
                                    </div>
                                </div>

                                <div>
                                    <label for="db_user" class="block text-sm font-medium text-gray-700 mb-1">Database Username</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="user" class="h-5 w-5 text-blue-500"></i>
                                        </div>
                                        <input type="text" id="db_user" name="db_user"
                                            class="pl-10 w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            value="root" required>
                                    </div>
                                </div>

                                <div>
                                    <label for="db_password" class="block text-sm font-medium text-gray-700 mb-1">Database Password</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="key" class="h-5 w-5 text-blue-500"></i>
                                        </div>
                                        <input type="password" id="db_password" name="db_password"
                                            class="pl-10 w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                    </div>
                                </div>

                                <div>
                                    <label for="db_name" class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="folder" class="h-5 w-5 text-blue-500"></i>
                                        </div>
                                        <input type="text" id="db_name" name="db_name"
                                            class="pl-10 w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                            value="ecommerce_template" required>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-6 py-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transform hover:-translate-y-0.5 transition-all duration-200 shadow-lg hover:shadow-xl">
                                        <i data-lucide="database" class="w-5 h-5 mr-2"></i>
                                        Set Up Database
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>