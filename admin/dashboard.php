<?php
session_start();
// --- DEBUGGING --- 
// echo "<div style='background: yellow; color: black; padding: 10px; position: absolute; top: 0; left: 0; z-index: 9999;'>DEBUG: password_needs_change = ";
// var_dump($_SESSION['password_needs_change'] ?? 'Not Set');
// echo "</div>";
// --- END DEBUGGING ---

require_once '../config/settings.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Check if password needs to be changed
$passwordNeedsChange = isset($_SESSION['password_changed']) && !$_SESSION['password_changed'];

// Get statistics
function getStats()
{
    global $conn, $db_connected;
    $stats = [
        'products_total' => 0,
        'products_active' => 0,
        'products_inactive' => 0,
        'categories' => 0,
        'parent_categories' => 0,
        'sub_categories' => 0,
        'inventory_total' => 0,
        'inventory_in_stock' => 0,
        'inventory_backorder' => 0,
        'inventory_sold_out' => 0,
        'avg_price' => 0,
        'min_price' => 0,
        'max_price' => 0,
        'total_inventory_value' => 0,
        'most_used_category_name' => 'N/A',
        'most_used_category_count' => 0,
        'least_used_category_name' => 'N/A',
        'least_used_category_count' => 0
        // Removed sample growth stats, can be added back if needed with real calculations
    ];

    if ($db_connected && $conn) {
        // Product Counts
        $result = $conn->query("SELECT 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                                FROM products");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['products_total'] = (int)$row['total'];
            $stats['products_active'] = (int)$row['active'];
            $stats['products_inactive'] = (int)$row['inactive'];
        }

        // Count categories (Total, Parent, Sub)
        $result = $conn->query("SELECT
                                    COUNT(*) as total,
                                    SUM(CASE WHEN parent_id IS NULL OR parent_id = 0 THEN 1 ELSE 0 END) as parent_count,
                                    SUM(CASE WHEN parent_id IS NOT NULL AND parent_id != 0 THEN 1 ELSE 0 END) as sub_count
                                FROM categories");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['categories'] = (int)$row['total'];
            $stats['parent_categories'] = (int)$row['parent_count'];
            $stats['sub_categories'] = (int)$row['sub_count'];
        }

        // Inventory Counts (Stock and Backorder Status)
        $result = $conn->query("SELECT 
                                    SUM(stock) as total_stock_units, 
                                    SUM(CASE WHEN stock > 0 THEN 1 ELSE 0 END) as in_stock_count, 
                                    SUM(CASE WHEN stock <= 0 AND backorder = 1 THEN 1 ELSE 0 END) as backorder_count, 
                                    SUM(CASE WHEN stock <= 0 AND backorder = 0 THEN 1 ELSE 0 END) as sold_out_count 
                                FROM products");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['inventory_total'] = (int)($row['total_stock_units'] ?? 0);
            $stats['inventory_in_stock'] = (int)($row['in_stock_count'] ?? 0);
            $stats['inventory_backorder'] = (int)($row['backorder_count'] ?? 0);
            $stats['inventory_sold_out'] = (int)($row['sold_out_count'] ?? 0);
        }

        // Price Stats & Inventory Value
        $result = $conn->query("SELECT 
                                    AVG(price) as avg_price, 
                                    MIN(price) as min_price, 
                                    MAX(price) as max_price,
                                    SUM(CASE WHEN stock > 0 THEN price * stock ELSE 0 END) as total_value
                                FROM products");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['avg_price'] = round($row['avg_price'] ?? 0, 2);
            $stats['min_price'] = round($row['min_price'] ?? 0, 2);
            $stats['max_price'] = round($row['max_price'] ?? 0, 2);
            $stats['total_inventory_value'] = round($row['total_value'] ?? 0, 2);
        }

        // Category Usage Stats (excluding uncategorized)
        $category_usage_sql = "SELECT c.name, COUNT(p.id) as product_count 
                               FROM categories c 
                               JOIN products p ON c.id = p.category_id 
                               WHERE p.category_id IS NOT NULL AND p.category_id != 0
                               GROUP BY c.id, c.name 
                               ORDER BY product_count DESC";
        $result = $conn->query($category_usage_sql);
        $category_counts = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $category_counts[] = $row;
            }
        }

        if (!empty($category_counts)) {
            // Most used (first row due to ORDER BY DESC)
            $stats['most_used_category_name'] = $category_counts[0]['name'];
            $stats['most_used_category_count'] = (int)$category_counts[0]['product_count'];

            // Least used (last row)
            $least_used_index = count($category_counts) - 1;
            $stats['least_used_category_name'] = $category_counts[$least_used_index]['name'];
            $stats['least_used_category_count'] = (int)$category_counts[$least_used_index]['product_count'];
        }
    }

    return $stats;
}

// Get all categories for dropdown
function getAllCategories()
{
    global $conn, $db_connected;
    $categories = [];
    if ($db_connected && $conn) {
        $sql = "SELECT id, name FROM categories ORDER BY name ASC";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
    }
    return $categories;
}

// Get store settings for the title
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store';
$currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '$';
$stats = getStats();
$categories = getAllCategories(); // Fetch categories

// --- Dynamic Product Stats for Chart ---
$monthlyStats = [];
if ($db_connected && $conn) {
    $result = $conn->query("
        SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count
        FROM products
        WHERE created_at IS NOT NULL
        GROUP BY month
        ORDER BY MIN(created_at) ASC
        LIMIT 12
    ");
    while ($row = $result && $result->fetch_assoc()) {
        $monthlyStats[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com/"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Alpine Plugins (if needed by productManager or its dependencies) -->
    <script defer src="https://unpkg.com/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <!-- Include the external Alpine component script FIRST -->
    <script src="assets/js/productManager.js" defer></script>
    <!-- Alpine Core (Loads AFTER component definition) -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .stat-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            /* space between icon and text */
            padding: 0.5rem;
            border-radius: 0.375rem;
            /* rounded-md */
            background-color: rgba(255, 255, 255, 0.5);
            /* slight white background */
        }

        /* Animation for the Powered By section */
        @keyframes glow {
            0%, 100% { filter: drop-shadow(0 0 0.5rem rgba(74, 222, 128, 0.2)); }
            50% { filter: drop-shadow(0 0 1rem rgba(74, 222, 128, 0.6)); }
        }
        
        @keyframes textGlow {
            0%, 100% { text-shadow: 0 0 0.2rem rgba(96, 165, 250, 0.2); }
            50% { text-shadow: 0 0 0.5rem rgba(96, 165, 250, 0.6); }
        }
        
        @keyframes arrowGlow {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(0.25rem); }
        }
        
        .animate-glow {
            animation: glow 2s ease-in-out infinite;
        }
        
        .animate-text-glow {
            animation: textGlow 2s ease-in-out infinite;
        }
        
        .animate-arrow-glow {
            animation: arrowGlow 1.5s ease-in-out infinite;
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <?php include_once 'includes/admin_navbar.php'; ?>

    <!-- Include toast notification component -->
    <?php include_once '../includes/toast.php'; ?>

    <!-- Force Password Change Modal (if needed) -->
    <?php 
    define('ALLOW_ACCESS', true); // Define flag before including
    include_once 'modals/change_password_modal.php'; 
    ?>

    <!-- Main container with Alpine data scope -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="productManager()">
        <div class="flex justify-between items-start md:items-center mb-8 gap-4">
            <div class="flex-grow">
                <div class="shrink-0 space-y-0.5 w-fit">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2"><i data-lucide="home" class="h-6 w-6"></i> Dashboard </h1>
                <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full mb-4"></div>
                </div>
                <p class="text-gray-600">Overview of your store performance</p>
            </div>
            <!-- Button Group -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <button @click="openAddModal()" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                    <i data-lucide="plus" class="h-4 w-4"></i> <span class="hidden sm:inline">Add Product</span>
                </button>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Store Snapshot</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Products Card (Enhanced) -->
                <div class="bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl shadow-md p-6 border border-blue-200 text-blue-900">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">Products</h3>
                        <div class="p-2 rounded-full bg-blue-500/20">
                            <i data-lucide="box" class="h-5 w-5 text-blue-700"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mb-3"><?= number_format($stats['products_total']) ?></p>
                    <div class="stat-card-grid">
                        <div class="stat-item text-green-700">
                            <i data-lucide="check-circle" class="h-4 w-4"></i>
                            <span class="text-sm font-medium"><?= number_format($stats['products_active']) ?> Active</span>
                        </div>
                        <div class="stat-item text-red-700">
                            <i data-lucide="x-circle" class="h-4 w-4"></i>
                            <span class="text-sm font-medium"><?= number_format($stats['products_inactive']) ?> Inactive</span>
                        </div>
                    </div>
                </div>

                <!-- Inventory Card (Enhanced) -->
                <div class="bg-gradient-to-br from-purple-100 to-purple-200 rounded-xl shadow-md p-6 border border-purple-200 text-purple-900">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">Inventory Status</h3>
                        <div class="p-2 rounded-full bg-purple-500/20">
                            <i data-lucide="package" class="h-5 w-5 text-purple-700"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mb-3"><?= number_format($stats['inventory_total']) ?> <span class="text-lg font-normal">Units</span></p>
                    <div class="stat-card-grid">
                        <div class="stat-item text-green-700">
                            <i data-lucide="package-check" class="h-4 w-4"></i>
                            <span class="text-sm font-medium"><?= number_format($stats['inventory_in_stock']) ?> In Stock</span>
                        </div>
                        <div class="stat-item text-yellow-700">
                            <i data-lucide="history" class="h-4 w-4"></i>
                            <span class="text-sm font-medium"><?= number_format($stats['inventory_backorder']) ?> Backorder</span>
                        </div>
                        <div class="stat-item text-red-700">
                            <i data-lucide="package-x" class="h-4 w-4"></i>
                            <span class="text-sm font-medium"><?= number_format($stats['inventory_sold_out']) ?> Sold Out</span>
                        </div>
                    </div>
                </div>

                <!-- Categories Card (Enhanced) -->
                <div class="bg-gradient-to-br from-green-100 to-green-200 rounded-xl shadow-md p-6 border border-green-200 text-green-900">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">Categories</h3>
                        <div class="p-2 rounded-full bg-green-500/20">
                            <i data-lucide="tag" class="h-5 w-5 text-green-700"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mb-3"><?= number_format($stats['categories']) ?> <span class="text-lg font-normal">Total</span></p>
                    <div class="stat-card-grid mt-2">
                        <div class="stat-item text-green-800">
                            <i data-lucide="folder" class="h-4 w-4"></i>
                            <span class="text-sm font-medium">
                                <?= number_format($stats['parent_categories']) ?> Parent
                            </span>
                        </div>
                        <div class="stat-item text-blue-800">
                            <i data-lucide="folder-symlink" class="h-4 w-4"></i>
                            <span class="text-sm font-medium">
                                <?= number_format($stats['sub_categories']) ?> Sub
                            </span>
                        </div>
                        <div class="stat-item text-green-800">
                            <i data-lucide="trending-up" class="h-4 w-4"></i>
                            <span class="text-sm font-medium truncate" title="<?= htmlspecialchars($stats['most_used_category_name']) ?>">
                                Most: <?= htmlspecialchars($stats['most_used_category_name']) ?> <span class="text-xs text-gray-500 bg-blue-100 px-1 rounded-full font-normal">
                                    <?= number_format($stats['most_used_category_count']) ?>
                                </span>
                            </span>
                        </div>
                        <div class="stat-item text-red-800">
                            <i data-lucide="trending-down" class="h-4 w-4"></i>
                            <span class="text-sm font-medium truncate" title="<?= htmlspecialchars($stats['least_used_category_name']) ?>">
                                Least: <?= htmlspecialchars($stats['least_used_category_name']) ?> <span class="text-xs text-gray-500 bg-red-100 px-1 rounded-full font-normal">
                                    <?= number_format($stats['least_used_category_count']) ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Price Stats Card (Enhanced) -->
                <div class="bg-gradient-to-br from-amber-100 to-amber-200 rounded-xl shadow-md p-6 border border-amber-200 text-amber-900">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">Pricing & Value</h3>
                        <div class="p-2 rounded-full bg-amber-500/20">
                            <i data-lucide="dollar-sign" class="h-5 w-5 text-amber-700"></i>
                        </div>
                    </div>
                    <p class="text-xl font-bold mb-3">Avg: <?= htmlspecialchars($currencySymbol) ?><?= number_format($stats['avg_price'], 2) ?></p>
                    <div class="stat-card-grid mt-2">
                        <div class="stat-item text-green-800">
                            <i data-lucide="arrow-up-circle" class="h-4 w-4"></i>
                            <span class="text-sm font-medium">Max: <?= htmlspecialchars($currencySymbol) ?><?= number_format($stats['max_price'], 2) ?></span>
                        </div>
                        <div class="stat-item text-red-800">
                            <i data-lucide="arrow-down-circle" class="h-4 w-4"></i>
                            <span class="text-sm font-medium">Min: <?= htmlspecialchars($currencySymbol) ?><?= number_format($stats['min_price'], 2) ?></span>
                        </div>
                        <div class="stat-item text-blue-800 col-span-full"><!-- Span full width -->
                            <i data-lucide="coins" class="h-4 w-4"></i>
                            <span class="text-sm font-medium">Inv. Value: <?= htmlspecialchars($currencySymbol) ?><?= number_format($stats['total_inventory_value'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center mb-5">
                    <i data-lucide="zap" class="h-5 w-5 text-blue-500 mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-800">Quick Actions</h2>
                </div>
                <div class="space-y-3">
                    <a href="products.php" class="flex items-center p-3 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition">
                        <i data-lucide="list" class="h-5 w-5 mr-2"></i>
                        <span>Manage Products</span>
                    </a>
                    <a href="categories.php" class="flex items-center p-3 bg-teal-50 text-teal-700 rounded-lg hover:bg-teal-100 transition">
                        <i data-lucide="tag" class="h-5 w-5 mr-2"></i>
                        <span>Manage Categories</span>
                    </a>
                    <a href="account.php" class="flex items-center p-3 bg-cyan-50 text-cyan-700 rounded-lg hover:bg-cyan-100 transition">
                        <i data-lucide="user-cog" class="h-5 w-5 mr-2"></i>
                        <span>Account Settings</span>
                    </a>
                    <a href="settings.php" class="flex items-center p-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition">
                        <i data-lucide="settings" class="h-5 w-5 mr-2"></i>
                        <span>Settings Page</span>
                    </a>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white flex flex-col gap-4 justify-between rounded-xl shadow-sm p-6 lg:col-span-2 border border-gray-100">
                <div class="flex items-center">
                    <i data-lucide="bar-chart-2" class="h-5 w-5 text-blue-500 mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-800">Product Statistics</h2>
                </div>
                <div class="h-64 mt-auto">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Database Status -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center mb-5">
                <i data-lucide="activity" class="h-5 w-5 text-blue-500 mr-2"></i>
                <h2 class="text-lg font-semibold text-gray-800">System Status</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Database Connection</h3>
                    <?php if (DB_CONNECTED): ?>
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                            <div class="flex">
                                <i data-lucide="check-circle" class="h-5 w-5 text-green-500 mr-2"></i>
                                <div>
                                    <p class="text-sm font-medium text-green-800">Connected</p>
                                    <p class="text-sm text-green-700 mt-1">Database connection is active and working properly.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                            <div class="flex">
                                <i data-lucide="alert-circle" class="h-5 w-5 text-red-500 mr-2"></i>
                                <div>
                                    <p class="text-sm font-medium text-red-800">Connection Failed</p>
                                    <p class="text-sm text-red-700 mt-1">Database connection failed. The site is running in demo mode.</p>
                                    <div class="mt-3 flex items-center">
                                        <i data-lucide="tool" class="h-4 w-4 text-red-500 mr-1"></i>
                                        <p class="text-xs text-red-700">To fix this, edit your database configuration in <code class="bg-red-100 px-1 py-0.5 rounded">config/db.php</code></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Powered By</h3>
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 flex flex-col items-center">
                        <div class="flex items-center justify-center mb-3 px-2 py-1 bg-gray-800/80 w-fit rounded-lg">
                            <img src="../assets/images/phirmhost-ads.png" alt="Phirmhost" class="h-12 object-contain mb-2">
                        </div>
                        <p class="text-sm text-center text-gray-600 mb-3">Your e-commerce store is powered by Phirmhost, a leading hosting provider for online businesses.</p>
                        <!-- <div class="group relative">
                            <a href="https://wa.me/233542838165?text=Hello!%20I%20saw%20your%20amazing%20website%20and%20I%20would%20love%20to%20get%20one%20for%20my%20business.%20Could%20you%20please%20tell%20me%20more%20about%20your%20web%20development%20services%3F" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                Let's build you one
                            </a>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-4 py-2 bg-gray-900 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 w-64 text-center">
                                Want a stunning website like this? 
                                <a href="https://wa.me/233542838165?text=Hello!%20I%20saw%20your%20amazing%20website%20and%20I%20would%20love%20to%20get%20one%20for%20my%20business.%20Could%20you%20please%20tell%20me%20more%20about%20your%20web%20development%20services%3F" target="_blank" class="block mt-1 text-blue-400 hover:text-blue-300 hover:underline animate-text-glow">
                                    <span class="animate-text-glow">Let's build you one</span>
                                    <span class="ml-1 inline-block animate-arrow-glow text-green-500 text-xl">→</span>
                                </a>
                                <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 translate-y-1/2 rotate-45 w-2 h-2 bg-gray-900"></div>
                            </div>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Include the Add Product Modal -->
        <?php include_once 'modals/product_add_modal.php'; ?>

    </div>

    <!-- Pass initial data to JavaScript -->
    <script id="product-manager-data" type="application/json">
        <?= json_encode([
            // We don't need products here, but modals need categories and currency
            'products' => [],
            'categories' => $categories,
            'currencySymbol' => $currencySymbol
        ]) ?>
    </script>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Pass PHP monthly stats to JS
        const productMonthlyStats = <?= json_encode($monthlyStats) ?>;

        // Initialize chart
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('productsChart').getContext('2d');
            // Use dynamic data
            const labels = productMonthlyStats.map(item => item.month);
            const data = productMonthlyStats.map(item => parseInt(item.count));
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [{
                        label: 'Products Added',
                        data: data.length ? data : [0],
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });

            <?php if (isset($showToast) && $showToast): ?>
                // Show toast notification after password change
                toast.<?= $toastType ?>('<?= $toastMessage ?>');
            <?php endif; ?>
        });
    </script>
</body>

</html>