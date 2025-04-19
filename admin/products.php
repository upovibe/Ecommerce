<?php
session_start();
require_once '../config/settings.php';
// Removed require_once '../utils/products.php'; as fetching is basic here

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Page specific variables
$pageTitle = "Manage Products";
$breadcrumbs = [
    ['name' => $pageTitle] // Current page - no URL needed
];

// Fetch initial data - Categories for modals, Products for the table
// Renamed function call
function getAllProductsAndCategories() {
    global $conn, $db_connected;
    $products = [];
    $categories = [];

    // Fetch Categories first (needed by products)
    if ($db_connected && $conn) {
        $sql_cat = "SELECT id, name FROM categories ORDER BY name ASC";
        $result_cat = $conn->query($sql_cat);
        if ($result_cat) {
            while ($row = $result_cat->fetch_assoc()) {
                $categories[] = $row;
            }
        }
    }

    // Fetch Products
    if ($db_connected && $conn) {
        $sql_prod = "SELECT 
                        p.id, p.name, p.slug, p.description, p.price, 
                        p.original_price, p.discount_percentage, 
                        p.image, p.category_id, p.stock, p.featured, 
                        p.is_active, p.backorder,
                        p.created_at, p.updated_at, 
                        c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.name ASC"; 
        $result_prod = $conn->query($sql_prod);
        if ($result_prod) {
            while ($row = $result_prod->fetch_assoc()) {
                // Ensure correct types
                $row['id'] = (int)$row['id'];
                $row['price'] = (float)$row['price'];
                $row['original_price'] = $row['original_price'] === null ? null : (float)$row['original_price'];
                $row['discount_percentage'] = $row['discount_percentage'] === null ? null : (float)$row['discount_percentage'];
                $row['stock'] = (int)$row['stock'];
                $row['featured'] = (bool)$row['featured'];
                $row['is_active'] = (bool)$row['is_active'];
                $row['backorder'] = (bool)$row['backorder'];
                $row['category_id'] = $row['category_id'] ? (int)$row['category_id'] : null;
                 // Calculate availability status
                 if ($row['stock'] > 0) {
                    $row['availability_status'] = 'in_stock';
                } elseif ($row['backorder']) {
                    $row['availability_status'] = 'backorder';
                 } else {
                    $row['availability_status'] = 'out_of_stock';
                 }
                $products[] = $row;
            }
        }
    }
    return ['products' => $products, 'categories' => $categories];
}

// Process actions (delete, etc.) - Placeholder
$message = '';
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];

    if ($action === 'delete' && $id > 0) {
        // TODO: Implement actual product deletion logic here
        // Example: deleteProduct($conn, $id); 
        $_SESSION['flash_message'] = ['type' => 'success', 'text' => "Product ID {$id} deletion triggered (implement actual deletion)."];
        header('Location: products.php'); // Redirect to avoid re-deletion on refresh
        exit;
    }
}
if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

// Get store settings
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store';
$currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '$';
// Call the correct function name
$initialData = getAllProductsAndCategories();
$initialCategories = $initialData['categories'];
$initialProducts = $initialData['products'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine Plugins -->
    <script defer src="https://unpkg.com/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>
    <!-- Include the external Alpine component script FIRST -->
    <script src="assets/js/productManager.js" defer></script>
    <!-- Alpine Core (Loads AFTER component definition) -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
        /* Add other styles as needed */
        tbody tr:nth-child(odd) { background-color: #f9fafb; /* gray-50 */ }
        tbody tr:hover { background-color: #f3f4f6; /* gray-100 */ }
    </style>
</head>

<body class="bg-gray-100 font-sans antialiased">
    <!-- Navigation -->
    <?php include_once 'includes/admin_navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
        x-data="productManager(<?= htmlspecialchars(json_encode($initialProducts, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG)) ?>, <?= htmlspecialchars(json_encode($initialCategories, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG)) ?>, '<?= htmlspecialchars($currencySymbol) ?>')" @keydown.escape.window="isModalOpen = false">

        <!-- Breadcrumbs -->
        <?php include_once 'includes/breadcrumbs.php'; ?>

        <div class="flex justify-between items-center mb-4 gap-4 ">
            <div class="shrink-0 space-y-0.5">
                <h1 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-2"><i data-lucide="package" class="h-6 w-6"></i> <?= htmlspecialchars($pageTitle) ?></h1>
                <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full mb-4"></div>
            </div>
            <button @click="openAddModal()" type="button" class="flex items-center inline-flex items-center justify-center gap-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 size-8 md:w-fit md:px-2">
                <i data-lucide="plus" class="size-4"></i> <span class="hidden md:inline">Add New Product</span>
            </button>
        </div>

        <?php if (isset($flashMessage)): ?>
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
                class="mb-6 p-4 rounded-md <?= $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($flashMessage['text']) ?>
            </div>
        <?php endif; ?>

        <!-- Filter and View Controls -->
        <div class="bg-white p-2 rounded-lg shadow-sm mb-6 flex gap-4 items-center relative">
            <?php include_once 'includes/product_filters.php'; ?>
        </div>

        <!-- Product Display Area -->
        <div class="transition-all duration-300 ease-in-out">
            <?php include_once 'includes/product_display.php'; ?>
        </div>

        <?php include_once 'modals/product_view_modal.php'; ?>
        <?php include_once 'modals/product_add_modal.php'; ?>
        <?php include_once 'modals/edit_product_modal.php'; ?>

        <?php 
        // Include change password modal for navbar trigger
        if (!defined('ALLOW_ACCESS')) {
            define('ALLOW_ACCESS', true); 
        }
        include_once 'modals/change_password_modal.php'; 
        ?>

    </div>

    <!-- Pass initial data to JavaScript -->
    <script id="product-manager-data" type="application/json">
        <?= json_encode([ 
            'products' => $initialProducts, 
            'categories' => $initialCategories, 
            'currencySymbol' => $currencySymbol 
        ]) ?>
    </script>

    <!-- Initial icon rendering (can stay here or move inside Alpine init) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    <?php include_once '../includes/toast.php'; // Ensure toast container and script are loaded 
    ?>
</body>

</html>