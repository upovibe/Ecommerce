<?php
session_start();
require_once '../config/settings.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Page specific variables
$pageTitle = "Manage Categories";
$breadcrumbs = [
    ['name' => $pageTitle] // Current page - no URL needed
];

// Fetch initial categories - This is now primarily for the JS data initialization
function getAllAdminCategoriesForJS() {
    global $conn, $db_connected;
    $categories = [];
    if ($db_connected && $conn) {
        $sql = "SELECT c.id, c.name, c.slug, c.parent_id, c.featured, c.description, c.image, COUNT(p.id) as product_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id 
                ORDER BY c.name ASC"; 
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Ensure correct types for JSON encoding / JS usage
                $row['id'] = (int)$row['id'];
                $row['featured'] = (bool)$row['featured'];
                $row['product_count'] = (int)$row['product_count'];
                $row['parent_id'] = $row['parent_id'] ? (int)$row['parent_id'] : null;
                $row['image'] = $row['image']; // Ensure image is included
                $categories[] = $row;
            }
        } else {
            // Add error logging if query fails
            error_log("Error fetching categories in getAllAdminCategoriesForJS: " . $conn->error);
        }
    }
    return $categories;
}

// Remove the renderCategoryRow function - it will be handled by Alpine

$initialCategories = getAllAdminCategoriesForJS();
$storeName = STORE_SETTINGS['store_name'] ?? 'E-Commerce Store';

// Flash messages (can still be useful for non-JS feedback or initial load errors)
$flashMessage = null;
if (isset($_SESSION['flash_message'])) {
    $flashMessage = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= htmlspecialchars($storeName) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Load component definition FIRST -->
    <script src="assets/js/category-manager.js" defer></script>
    <!-- Load Alpine core AFTER component definition -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        tbody tr:nth-child(odd) { background-color: #f9fafb; }
        tbody tr:hover { background-color: #f3f4f6; }
        .table-section-header {
            background-color: #e5e7eb; /* gray-200 */
            font-weight: 600; /* semibold */
            color: #374151; /* gray-700 */
            padding: 0.75rem 1.5rem; /* py-3 px-6 */
            text-align: left;
            font-size: 0.875rem; /* text-sm */
        }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased">
    <!-- Navigation -->
    <?php include_once 'includes/admin_navbar.php'; ?>

    <!-- Main Content Area -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="categoryManager()">
        
        <!-- Breadcrumbs -->
        <?php include_once 'includes/breadcrumbs.php'; ?>

        <!-- Header & Add Button -->
        <div class="flex justify-between items-center mb-6 gap-4">
            <div class="shrink-0 space-y-0.5">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2"><i data-lucide="folder-tree" class="h-6 w-6"></i> <?= htmlspecialchars($pageTitle) ?></h1>
            <div class="h-1 bg-gradient-to-r from-blue-500 to-blue-600 mx-auto rounded-full mb-4"></div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($flashMessage)): ?>
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition
            class="mb-4 p-4 rounded-md <?= $flashMessage['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= htmlspecialchars($flashMessage['text']) ?>
        </div>
        <?php endif; ?>

        <!-- Tab Navigation & Content Area -->
        <div x-data="{
                tab: 'parents', // Default tab
                init() {
                    // Optional: Check URL for tab parameter if needed
                    // const urlParams = new URLSearchParams(window.location.search);
                    // this.tab = urlParams.get('tab') === 'subcategories' ? 'subcategories' : 'parents';
                }
             }">
            <!-- Tab Buttons -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="flex -mb-px space-x-6" aria-label="Tabs">
                    <button 
                        class="flex items-center whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm focus:outline-none"
                        :class="tab === 'parents' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
                        @click="tab = 'parents'"
                    >
                        <i data-lucide="folder-tree" class="w-4 h-4 mr-2"></i>
                        Parent Categories
                    </button>
                    <button 
                        class="flex items-center whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm focus:outline-none"
                        :class="tab === 'subcategories' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" 
                        @click="tab = 'subcategories'"
                    >
                        <i data-lucide="folder-symlink" class="w-4 h-4 mr-2"></i>
                        Subcategories
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="tab-content relative">
                <!-- Parent Categories Table -->
                <div x-show="tab === 'parents'" x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150 absolute w-full"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95">
                    
                    <!-- Filter Controls Row -->
                    <div class="mb-4 flex flex-col sm:flex-row justify-end items-stretch gap-3">
                         <!-- Search Input (Moved back out) -->
                        <div class="relative flex-grow w-full sm:w-auto">
                            <label for="parent-search" class="sr-only">Search</label>
                             <input type="text" id="parent-search" placeholder="Search parent categories..."
                                   x-model.debounce.300ms="parentSearchTerm"
                                   class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                            </div>
                        </div>
                        
                         <!-- Filter Trigger Button & Dropdown -->
                        <div class="relative flex-shrink-0" x-data="{ isOpen: false }">
                            <button @click="isOpen = !isOpen" title="Filters"
                                    class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i data-lucide="sliders-horizontal" class="size-4 text-gray-500"></i>
                                <!-- Optional: Add indicator dot if filters are active -->
                                <span x-show="parentSearchTerm || parentFeaturedFilter !== 'all'" x-cloak class="ml-1.5 w-2 h-2 bg-blue-500 rounded-full"></span>
                            </button>
                            
                            <div x-show="isOpen" 
                                 @click.outside="isOpen = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute top-full right-0 mt-2 w-72 z-20 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none p-4 space-y-4" 
                                 x-cloak>
                                
                                <h4 class="text-sm font-medium text-gray-500 border-b pb-2 mb-4">Filter Options</h4>
                                
                                <!-- Featured Filter -->
                                <div>
                                    <label for="parent-featured" class="block text-sm font-medium text-gray-700 mb-1">Featured Status</label>
                                     <select id="parent-featured" x-model="parentFeaturedFilter"
                                            class="w-full h-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent sm:text-sm appearance-none cursor-pointer">
                                        <option value="all">All</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                
                                <!-- Clear Filters Button -->
                                <div class="border-t pt-3 mt-3 flex justify-end">
                                    <button @click="resetParentFilters(); isOpen = false;" class="text-sm text-blue-600 hover:underline">Clear Filters</button>
                                </div>
                            </div>
                        </div>
                        
                         <!-- Refresh Button -->
                        <div class="flex-shrink-0">
                            <button @click="resetParentFilters()" title="Reset Filters"
                                    class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i data-lucide="rotate-cw" class="h-4 w-4"></i>
                            </button>
                        </div>
                         <!-- Add Button -->
                         <div class="flex-shrink-0">
                             <button @click="openAddParentModal()" type="button" class="h-full w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150 shadow hover:shadow-md transform hover:-translate-y-0.5">
                                <i data-lucide="plus" class="h-4 w-4"></i> <span class="hidden sm:inline">Add Parent</span>
                            </button>
                         </div>
                    </div>

                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">In Use</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-if="parentCategories.length === 0">
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <i data-lucide="folder-search" class="w-12 h-12 mb-3 text-gray-400"></i>
                                                    <p class="font-semibold mb-1">No Parent Categories Found</p>
                                                    <p class="text-sm">Try adjusting your search/filters or add a new parent category.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="category in parentCategories" :key="category.id">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <template x-if="category.image">
                                                    <img :src="getImageUrl(category.image)" :alt="category.name" class="h-10 w-10 rounded-md object-cover shadow-sm border border-gray-200">
                                                </template>
                                                <template x-if="!category.image">
                                                    <div class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center">
                                                        <i data-lucide="image" class="h-5 w-5 text-gray-400"></i>
                                                    </div>
                                                </template>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="category.name"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="category.slug || 'N/A'"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                      :class="category.product_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                                      x-text="category.product_count > 0 ? 'Yes' : 'No'"></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                      :class="category.featured ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                                      x-text="category.featured ? 'Yes' : 'No'"></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button @click="openEditModal(category)" title="Edit" class="text-indigo-600 hover:text-indigo-900 mr-3 transition duration-150 ease-in-out"><i data-lucide="edit" class="h-4 w-4"></i></button>
                                                <button @click="confirmDelete(category.id)" title="Delete" class="text-red-600 hover:text-red-900 transition duration-150 ease-in-out"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Subcategories Table -->
                <div x-show="tab === 'subcategories'" x-cloak
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-150 absolute w-full"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95">
                    
                    <!-- Filter Controls Row -->
                    <div class="mb-4 flex flex-col sm:flex-row justify-end items-stretch gap-3">
                          <!-- Search Input (Moved back out) -->
                        <div class="relative flex-grow w-full sm:w-auto">
                             <label for="sub-search" class="sr-only">Search</label>
                            <input type="text" id="sub-search" placeholder="Search subcategories..."
                                   x-model.debounce.300ms="subSearchTerm"
                                   class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent sm:text-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
                            </div>
                        </div>
                        
                         <!-- Filter Trigger Button & Dropdown -->
                        <div class="relative flex-shrink-0" x-data="{ isOpen: false }">
                            <button @click="isOpen = !isOpen" title="Filters"
                                    class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i data-lucide="sliders-horizontal" class="size-4 text-gray-500"></i>
                                <!-- Optional: Add indicator dot if filters are active -->
                                <span x-show="subSearchTerm || subParentFilter !== 'all' || subFeaturedFilter !== 'all'" x-cloak class="ml-1.5 w-2 h-2 bg-blue-500 rounded-full"></span>
                            </button>
                            
                            <div x-show="isOpen" 
                                 @click.outside="isOpen = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute top-full right-0 mt-2 w-72 z-20 origin-top-right bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none p-4 space-y-4" 
                                 x-cloak>
                                
                                <h4 class="text-sm font-medium text-gray-500 border-b pb-2 mb-4">Filter Options</h4>
                                
                                <!-- Parent Filter -->
                                <div>
                                     <label for="sub-parent" class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
                                     <select id="sub-parent" x-model="subParentFilter"
                                             class="w-full h-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent sm:text-sm appearance-none cursor-pointer">
                                        <option value="all">All</option>
                                        <template x-for="parent in parentCategoriesForFilter" :key="parent.id">
                                             <option :value="parent.id" x-text="parent.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <!-- Featured Filter -->
                                <div>
                                     <label for="sub-featured" class="block text-sm font-medium text-gray-700 mb-1">Featured Status</label>
                                     <select id="sub-featured" x-model="subFeaturedFilter"
                                            class="w-full h-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-transparent sm:text-sm appearance-none cursor-pointer">
                                        <option value="all">All</option>
                                        <option value="yes">Yes</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                
                                 <!-- Clear Filters Button -->
                                <div class="border-t pt-3 mt-3 flex justify-end">
                                    <button @click="resetSubFilters(); isOpen = false;" class="text-sm text-blue-600 hover:underline">Clear Filters</button>
                                </div>
                            </div>
                        </div>
                        
                         <!-- Refresh Button -->
                        <div class="flex-shrink-0">
                            <button @click="resetSubFilters()" title="Reset Filters"
                                    class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                <i data-lucide="rotate-cw" class="h-4 w-4"></i>
                            </button>
                        </div>
                         <!-- Add Button -->
                         <div class="flex-shrink-0">
                            <button @click="openAddSubcategoryModal()" type="button" class="h-full w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150 shadow hover:shadow-md transform hover:-translate-y-0.5">
                                <i data-lucide="plus" class="h-4 w-4"></i> <span class="hidden sm:inline">Add Subcategory</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parent</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">In Use</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Featured</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-if="subCategories.length === 0">
                                        <tr>
                                             <td colspan="6" class="px-6 py-12 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <i data-lucide="folder-symlink" class="w-12 h-12 mb-3 text-gray-400"></i>
                                                    <p class="font-semibold mb-1">No Subcategories Found</p>
                                                    <p class="text-sm">Try adjusting your search/filters or add a new subcategory.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                     <template x-for="category in subCategories" :key="category.id">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <template x-if="category.image">
                                                    <img :src="getImageUrl(category.image)" :alt="category.name" class="h-10 w-10 rounded-md object-cover shadow-sm border border-gray-200">
                                                </template>
                                                <template x-if="!category.image">
                                                    <div class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center">
                                                        <i data-lucide="image" class="h-5 w-5 text-gray-400"></i>
                                                    </div>
                                                </template>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="category.name"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="category.slug || 'N/A'"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="getParentName(category.parent_id)"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                 <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                      :class="category.product_count > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                                      x-text="category.product_count > 0 ? 'Yes' : 'No'"></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                      :class="category.featured ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                                      x-text="category.featured ? 'Yes' : 'No'"></span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button @click="openEditModal(category)" title="Edit" class="text-indigo-600 hover:text-indigo-900 mr-3 transition duration-150 ease-in-out"><i data-lucide="edit" class="h-4 w-4"></i></button>
                                                <button @click="confirmDelete(category.id)" title="Delete" class="text-red-600 hover:text-red-900 transition duration-150 ease-in-out"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!-- End Tab Content -->
        </div> <!-- End Alpine Tab Component -->
        
        <!-- Category Add/Edit Modal -->
        <div x-show="isModalOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             aria-labelledby="category-modal-title" role="dialog" aria-modal="true"
             @keydown.escape.window="closeModal()">

            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div x-show="isModalOpen"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm transition-opacity"
                     @click="closeModal()" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div x-show="isModalOpen"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white/95 backdrop-blur-xl rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <!-- Header -->
                    <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200/50">
                        <h3 class="text-xl font-semibold text-gray-900" id="category-modal-title" x-text="modalTitle"></h3>
                        <button type="button" @click="closeModal()" class="bg-white/50 rounded-lg text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 backdrop-blur-xl">
                            <span class="sr-only">Close</span>
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <!-- Form Content -->
                    <form id="categoryForm" @submit.prevent="saveCategory()" enctype="multipart/form-data">
                        <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto">
                            
                            <!-- Only show Parent Selector when editing a subcategory or adding a new subcategory -->
                             <div x-show="(modalMode === 'edit' && currentCategory.parent_id !== null) || modalMode === 'addSub'">
                                <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-1">Parent Category <span class="text-red-500">*</span></label>
                                <select name="parent_id" id="parent_id"
                                        x-model="currentCategory.parent_id"
                                        :required="(modalMode === 'edit' && currentCategory.parent_id !== null) || modalMode === 'addSub'" 
                                        :disabled="modalMode === 'addParent'" 
                                        class="block w-full px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed">
                                     <option value="">-- No Parent (Top Level) --</option>
                                     <template x-for="parent in categories.filter(c => c.parent_id === null && String(c.id) !== String(currentCategory.id))" :key="parent.id">
                                         <option :value="parent.id" x-text="parent.name"></option>
                                     </template>
                                </select>
                                <p class="text-xs text-gray-500 mt-1" x-show="modalMode === 'edit' && currentCategory.parent_id !== null">Changing parent reclassifies the category.</p>
                            </div>

                            <!-- Category Image (Visible for Parent Add/Edit only) -->
                             <div x-show="modalMode === 'addParent' || (modalMode === 'edit' && currentCategory.parent_id === null)">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Image (Optional)</label>
                                <label for="category_image"
                                    class="relative mt-1 flex justify-center items-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors duration-200 min-h-[150px]">

                                    <!-- Placeholder Content (Icon & Text) -->
                                    <div x-show="!categoryImageUrl" class="space-y-1 text-center">
                                        <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400"></i>
                                        <div class="flex text-sm text-gray-600">
                                            <span class="relative bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload a file</span>
                                                <input type="file" name="category_image" id="category_image" accept="image/*"
                                                    @change="handleCategoryImageSelect($event)"
                                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                            </span>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 2MB</p>
                                    </div>

                                    <!-- Image Preview -->
                                    <div x-show="categoryImageUrl" class="relative w-full h-full flex justify-center items-center" x-cloak>
                                        <img :src="categoryImageUrl" alt="Category Image Preview"
                                            class="max-h-48 max-w-full rounded-lg object-contain shadow-sm">
                                        <button type="button" @click.prevent.stop="removeCategoryImage()"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 z-10">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                        </button>
                                    </div>

                                    <!-- Fallback for browsers without JS or if Alpine fails -->
                                    <input type="file" name="category_image_fallback" id="category_image_input_fallback" accept="image/*"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer sr-only">
                                </label>
                            </div>
                            <!-- End Category Image Upload -->

                            <div>
                                <label for="category_name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" id="category_name" required
                                       x-model="currentCategory.name"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                            </div>

                            <div>
                                <label for="category_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" id="category_description" rows="3"
                                          x-model="currentCategory.description"
                                          class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"></textarea>
                            </div>

                            <!-- Featured Toggle -->
                            <div class="flex items-center justify-between py-2 border border-gray-200 rounded-lg px-3 bg-white/50 shadow-sm">
                                <span class="text-sm font-medium text-gray-700 flex items-center">
                                    <i data-lucide="star" class="w-4 h-4 mr-2 text-yellow-500"></i>Featured Category
                                </span>
                                <button type="button" @click="currentCategory.is_featured = !currentCategory.is_featured"
                                        :class="{ 'bg-blue-600': currentCategory.is_featured, 'bg-gray-200': !currentCategory.is_featured }"
                                        class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                        role="switch" :aria-checked="currentCategory.is_featured.toString()">
                                    <span class="sr-only">Featured Category</span>
                                    <span aria-hidden="true"
                                          :class="{ 'translate-x-5': currentCategory.is_featured, 'translate-x-0': !currentCategory.is_featured }"
                                          class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"></span>
                                </button>
                            </div>

                        </div> <!-- End Form Fields -->

                        <!-- Footer Actions -->
                        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-200/50 flex justify-end items-center space-x-3">
                            <button type="button" @click="closeModal()"
                                    class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 min-w-[120px]"
                                    :disabled="isLoading">
                                <!-- Use separate spans for clarity -->
                                <span x-show="!isLoading && modalMode === 'edit'">Save Changes</span>
                                <span x-show="!isLoading && (modalMode === 'addParent' || modalMode === 'addSub' || modalMode === 'addSubcategory')">Create Category</span>
                                <span x-show="isLoading" class="flex items-center">
                                    <i data-lucide="loader-2" class="animate-spin h-4 w-4 mr-2"></i> Processing...
                                </span>
                            </button>
                        </div>
                    </form> <!-- End Form -->
                </div> <!-- End Modal Panel -->
            </div>
        </div> <!-- End Modal Container -->

    </div> <!-- End Main Content Area / Alpine Scope -->

    <!-- Include toast notification component -->
        <?php include_once '../includes/toast.php'; ?>
        
    <!-- Pass initial category data to Alpine -->
    <script id="category-manager-data" type="application/json">
        <?= json_encode(['categories' => $initialCategories]) ?>
    </script>

    <!-- Lucide Icons (Ensure it's loaded if not part of navbar include) -->
    <!-- <script> lucide.createIcons(); </script> Removed - handled in JS -->
</body>
</html> 