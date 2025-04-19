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

        // Count categories
        $result = $conn->query("SELECT COUNT(*) as count FROM categories");
        if ($result && $row = $result->fetch_assoc()) {
            $stats['categories'] = (int)$row['count'];
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
    </style>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <?php include_once 'includes/admin_navbar.php'; ?>

    <!-- Include toast notification component -->
    <?php include_once '../includes/toast.php'; ?>

    <?php
    // Always include the modal, it will be hidden by default
    // The condition ($passwordNeedsChange) can be checked in JS if needed to *auto-show* on load
    define('ALLOW_ACCESS', true); // Ensure the modal include doesn't block itself
    include_once 'modals/change_password_modal.php';
    ?>

    <!-- Main container with Alpine data scope -->
    <div class="container mx-auto px-4 py-8" x-data="productManager()">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div class="flex-grow">
                <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-600">Overview of your store performance</p>
            </div>
            <!-- Button Group -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <button @click="openAddModal()" type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg">
                    <i data-lucide="plus" class="h-4 w-4"></i> Add Product
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
                            <i data-lucide="trending-up" class="h-4 w-4"></i>
                            <span class="text-sm font-medium truncate" title="<?= htmlspecialchars($stats['most_used_category_name']) ?>">
                                <?= htmlspecialchars($stats['most_used_category_name']) ?>
                                (<?= number_format($stats['most_used_category_count']) ?>)
                            </span>
                        </div>
                        <div class="stat-item text-red-800">
                            <i data-lucide="trending-down" class="h-4 w-4"></i>
                            <span class="text-sm font-medium truncate" title="<?= htmlspecialchars($stats['least_used_category_name']) ?>">
                                <?= htmlspecialchars($stats['least_used_category_name']) ?>
                                (<?= number_format($stats['least_used_category_count']) ?>)
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
                    <a href="#" @click.prevent="openManageAccountModal()" class="flex items-center p-3 bg-cyan-50 text-cyan-700 rounded-lg hover:bg-cyan-100 transition">
                        <i data-lucide="user-cog" class="h-5 w-5 mr-2"></i>
                        <span>Manage Account</span>
                    </a>
                    <a href="settings.php?tab=general" class="flex items-center p-3 bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition">
                        <i data-lucide="settings" class="h-5 w-5 mr-2"></i>
                        <span>General Settings</span>
                    </a>
                    <a href="settings.php?tab=content" class="flex items-center p-3 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition">
                        <i data-lucide="file-text" class="h-5 w-5 mr-2"></i>
                        <span>Store Content Settings</span>
                    </a>
                    <a href="settings.php?tab=logo" class="flex items-center p-3 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition">
                        <i data-lucide="image" class="h-5 w-5 mr-2"></i>
                        <span>Change Logo</span>
                    </a>
                </div>
            </div>

            <!-- Chart -->
            <div class="bg-white flex flex-col gap-4 justify-between rounded-xl shadow-sm p-6 lg:col-span-2 border border-gray-100">
                <div class="flex items-center">
                    <i data-lucide="bar-chart-2" class="h-5 w-5 text-blue-500 mr-2"></i>
                    <h2 class="text-lg font-semibold text-gray-800">Product Statistics</h2>
                </div>
                <div class="h-80 mt-auto">
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
                        <div class="flex items-center justify-center w-full mb-3">
                            <img src="../assets/images/phirmhost.png" alt="Phirmhost" class="h-12 object-contain mb-2">
                        </div>
                        <p class="text-sm text-center text-gray-600 mb-3">Your e-commerce store is powered by Phirmhost, a leading hosting provider for online businesses.</p>
                        <a href="https://phirmhost.com" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                            <i data-lucide="external-link" class="h-3.5 w-3.5 mr-1"></i>
                            Visit Phirmhost.com
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Include the Add Product Modal -->
        <?php include_once 'modals/product_add_modal.php'; ?>

        <!-- Include the Manage Account Modal -->
        <?php include_once 'modals/manage_account_modal.php'; ?>

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

        // Initialize chart
        document.addEventListener('DOMContentLoaded', function() {
            // Sample data for chart
            const ctx = document.getElementById('productsChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Products Added',
                        data: [5, 8, 12, 7, 10, <?= $stats['products_total'] ?>],
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