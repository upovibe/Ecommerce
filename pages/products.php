<?php
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../utils/cart.php';

// Function to get store content (copied from index.php)
function getStoreContent($key)
{
    global $conn, $db_connected;

    $content = '';

    // Try to get content from database
    if ($db_connected && $conn) {
        $sql = "SELECT content_value FROM store_content WHERE content_key = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $content = $row['content_value'];
        }
    }

    // Return demo content if not found in database
    // Note: The demo content array might need adjustment if specific keys are needed only on product page
    if (empty($content)) {
        $demoContent = [
            'hero_title' => 'Welcome to our Online Store',
            'hero_subtitle' => 'Find everything you need, from essentials to luxuries.',
            'about_title' => 'About Our Store',
            'about_content' => '<p>...</p>', // Truncated for brevity
            'featured_title' => 'Shop by Category',
            'featured_subtitle' => 'Explore our popular categories...',
            'hero_image' => '/assets/images/demo/hero-bg.png',
            'about_image' => '/assets/images/demo/about-image.png',
            'product_page_banner_image' => '/assets/images/demo/product-banner.png', // Ensure this key exists here
            'product_banner_title' => 'Benguy Fashion', // Added demo title
            'product_banner_subtitle' => 'Your go-to destination for high-quality ladies\' bags, heels and apparel. Elevate your style with our exquisite collection.' // Added demo subtitle
        ];

        return $demoContent[$key] ?? '';
    }

    return $content;
}

// Function to load demo data from JSON file
function loadDemoData() {
    $jsonPath = __DIR__ . '/../config/demo_data.json';
    if (file_exists($jsonPath)) {
        $jsonContent = file_get_contents($jsonPath);
        $data = json_decode($jsonContent, true); // Decode as associative array
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
    }
    // Return empty structure if file not found or JSON error
    return ['categories' => [], 'products' => []]; 
}

// Get categories from database (fallback to demo categories if database not set up)
function getCategories()
{
    global $conn, $db_connected;
    
    $categories = [];
    
    $sql = "SELECT id, name, parent_id, image FROM categories WHERE parent_id IS NULL ORDER BY name";
    
    // Only query database if connection is available
    if ($db_connected && $conn) {
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $category = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'image' => $row['image'],
                    'subcategories' => []
                ];
                
                // Get subcategories
                $subSql = "SELECT id, name FROM categories WHERE parent_id = ? ORDER BY name";
                $stmt = $conn->prepare($subSql);
                $stmt->bind_param('i', $row['id']);
                $stmt->execute();
                $subResult = $stmt->get_result();
                
                if ($subResult && $subResult->num_rows > 0) {
                    while ($subRow = $subResult->fetch_assoc()) {
                        $category['subcategories'][] = [
                            'id' => $subRow['id'],
                            'name' => $subRow['name']
                        ];
                    }
                }
                
                $categories[] = $category;
            }
        }
    }
    
    // If no categories found in database or connection failed, use demo categories from JSON
    if (empty($categories)) {
        $demoData = loadDemoData();
        $categories = $demoData['categories'] ?? [];
        // If using demo data, ensure 'subcategories' key exists even if empty
        foreach ($categories as &$cat) { // Use reference to modify array directly
            if (!isset($cat['subcategories'])) {
                $cat['subcategories'] = [];
            }
        }
        unset($cat); // Unset reference after loop
    }
    
    return $categories;
}

// Get products with optional filters
function getProducts($search = '', $categoryId = null, $subcategoryId = null)
{
    global $conn, $db_connected;
    
    $products = [];
    $relevantCategoryIds = []; // Store IDs to filter by

    // Determine relevant category IDs
    if (!empty($subcategoryId)) {
        // If a specific subcategory is selected, only use that ID
        $relevantCategoryIds = [(int)$subcategoryId];
    } elseif (!empty($categoryId)) {
        // If a parent category is selected, get its ID and all its subcategory IDs
        $relevantCategoryIds = [(int)$categoryId]; // Start with the parent ID
        if ($db_connected && $conn) {
            $subSql = "SELECT id FROM categories WHERE parent_id = ?";
            $stmtSub = $conn->prepare($subSql);
            if ($stmtSub) {
                $stmtSub->bind_param('i', $categoryId);
                $stmtSub->execute();
                $subResult = $stmtSub->get_result();
                while ($subRow = $subResult->fetch_assoc()) {
                    $relevantCategoryIds[] = (int)$subRow['id'];
                }
                $stmtSub->close();
            }
        }
    }
    // If neither categoryId nor subcategoryId is set, $relevantCategoryIds remains empty, showing all products (unless searched).
    
    // Check if database connection exists and try to get products
    if ($db_connected && $conn) {
        $sql = "SELECT p.id, p.name, p.slug, p.price, p.original_price, p.discount_percentage, p.description, p.image, p.category_id, c.name as category_name, p.stock, p.is_active, p.backorder
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = TRUE";
        $params = [];
        $types = '';
        
        // Add search filter
        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'ss';
        }
        
        // Add category filter using the relevant IDs
        if (!empty($relevantCategoryIds)) {
            // Create placeholders for IN clause (?, ?, ?)
            $placeholders = implode(',', array_fill(0, count($relevantCategoryIds), '?'));
            $sql .= " AND p.category_id IN ({$placeholders})";
            // Add each ID to the params array
            foreach ($relevantCategoryIds as $id) {
                $params[] = $id;
            }
            // Add corresponding types ('i' for each integer ID)
            $types .= str_repeat('i', count($relevantCategoryIds));
        }
        
        $sql .= " ORDER BY p.name";
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Get product options
                $options = [];
                $optionSql = "SELECT option_name, option_values FROM product_options WHERE product_id = ?";
                $optStmt = $conn->prepare($optionSql);
                $optStmt->bind_param('i', $row['id']);
                $optStmt->execute();
                $optResult = $optStmt->get_result();
                
                if ($optResult && $optResult->num_rows > 0) {
                    while ($optRow = $optResult->fetch_assoc()) {
                        $options[$optRow['option_name']] = json_decode($optRow['option_values'], true);
                    }
                }
                
                $products[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'price' => $row['price'],
                    'original_price' => $row['original_price'],
                    'discount_percentage' => $row['discount_percentage'],
                    'description' => $row['description'],
                    'image' => $row['image'],
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'options' => $options,
                    'stock' => $row['stock'],
                    'is_active' => (bool)$row['is_active'],
                    'backorder' => (bool)$row['backorder']
                ];
            }
        }
    }
    
    // Use demo products if database connection failed or no products found
    if (empty($products)) {
        $demoData = loadDemoData();
        $demoProducts = $demoData['products'] ?? [];
        
        // Apply filters to demo products
        $filteredDemoProducts = []; // Use a new array for filtered results
        foreach ($demoProducts as $product) {
            // *** Skip inactive products first ***
            if (!($product['is_active'] ?? true)) { // Default to true if key missing, skip if false
                continue;
            }
            
            $keep = true; // Assume we keep the product initially

            // Apply search filter
            if (!empty($search)) {
                $nameMatch = stripos($product['name'], $search) !== false;
                $descMatch = stripos($product['description'], $search) !== false;
                if (!$nameMatch && !$descMatch) {
                    $keep = false; // Mark for removal
                }
            }

            // Apply category/subcategory filter (only if search didn't already exclude it)
            if ($keep) {
                if (!empty($relevantCategoryIds)) {
                    // Check if the product's category ID is in the list of relevant IDs
                    if (!in_array($product['category_id'], $relevantCategoryIds)) {
                        $keep = false; // Mark for removal
                    }
                }
            }

            if ($keep) {
                $filteredDemoProducts[] = $product; // Add product if it passed filters
            }
        }
        $products = $filteredDemoProducts; // Assign filtered demo products
    }
    
    return $products;
}

// Function to get the name of the currently selected category or subcategory
function getCategoryName($categoryId = null, $subcategoryId = null, $categories = [])
{
    if (!empty($subcategoryId)) {
        foreach ($categories as $parentCategory) {
            foreach ($parentCategory['subcategories'] as $sub) {
                if ($sub['id'] == $subcategoryId) {
                    return $sub['name'];
                }
            }
        }
    } elseif (!empty($categoryId)) {
        foreach ($categories as $parentCategory) {
            if ($parentCategory['id'] == $categoryId) {
                return $parentCategory['name'];
            }
        }
    }
    return 'All Products'; // Default if no specific category/subcategory is selected
}

// --- Start of AJAX Handling ---
if (isset($_GET['fetch']) && $_GET['fetch'] == 'true') {
    // This is an AJAX request for products

    // Get filter parameters from the request
$search = $_GET['search'] ?? '';
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
$subcategoryId = isset($_GET['subcategory']) ? (int) $_GET['subcategory'] : null;

    // Get categories (needed for getCategoryName) and products
    $categories = getCategories(); // Fetch categories to determine the name
$products = getProducts($search, $categoryId, $subcategoryId);
$categoryName = getCategoryName($categoryId, $subcategoryId, $categories);

// Get currency symbol
$currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '₦';

    // Prepare the response data
    $responseData = [
        'products' => $products,
        'categoryName' => htmlspecialchars($categoryName), // Ensure name is safe
        'searchQuery' => htmlspecialchars($search), // Send back the search query used
        'currencySymbol' => $currencySymbol
    ];

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($responseData);
    exit; // Stop script execution after sending JSON
}
// --- End of AJAX Handling ---

// --- Regular Page Load Logic ---

// Get filter parameters for initial page load
$search = $_GET['search'] ?? '';
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
$subcategoryId = isset($_GET['subcategory']) ? (int) $_GET['subcategory'] : null;

// Get categories and products for initial page load
$categories = getCategories();
$products = getProducts($search, $categoryId, $subcategoryId);
$categoryName = getCategoryName($categoryId, $subcategoryId, $categories);

// Get currency symbol for initial page load
$currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '₦';

// Get WhatsApp Number for banner button
$whatsappNumber = STORE_SETTINGS['whatsapp_number'] ?? ''; // Use your setting key
$whatsappMessage = "Hello! I'm interested in your products."; // Default message
$whatsappURL = '';
if (!empty($whatsappNumber)) {
    // Basic number cleaning (remove non-digits)
    $cleanedNumber = preg_replace('/[^0-9]/', '', $whatsappNumber);
    // Construct URL
    $whatsappURL = 'https://wa.me/' . $cleanedNumber . '?text=' . urlencode($whatsappMessage);
}

// Include header
include_once __DIR__ . '/../includes/header.php';

// Include the database connection notice component
include __DIR__ . '/../includes/db_notice.php'; 
?>

<!-- Product Page Banner -->
<?php
$bannerImageUrl = getStoreContent('product_page_banner_image');
if (!empty($bannerImageUrl)):
?>
    <section class="relative w-full mb-8 rounded-b-lg shadow overflow-hidden">
        <!-- Image -->
        <img src="<?= htmlspecialchars($bannerImageUrl) ?>"
            alt="Products Banner"
            class="w-full h-auto object-cover max-h-64 md:max-h-80 lg:max-h-96">
        <!-- Dark Overlay -->
        <div class="absolute inset-0 bg-black/60"></div>
        <!-- Text Content -->
        <div class="absolute inset-0 flex flex-col gap-2 md:gap-4 items-center justify-center text-center p-4">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight shadow-text">
                <?= htmlspecialchars(getStoreContent('product_banner_title')) // Use dynamic title 
                ?>
            </h1>
            <p class="text-base md:text-lg text-gray-200 max-w-xl shadow-text">
                <?= htmlspecialchars(getStoreContent('product_banner_subtitle')) // Use dynamic subtitle 
                ?>
            </p>
            <!-- WhatsApp Button -->
            <?php if ($whatsappURL): ?>
                <a href="<?= htmlspecialchars($whatsappURL) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center px-6 h-10 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    Chat on WhatsApp
                </a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<div class="container mx-auto px-4 py-8" id="product-page-container" data-currency-symbol="<?= htmlspecialchars($currencySymbol) ?>"> <!-- Added ID and data attribute -->

    <!-- Filter/View Controls Bar -->
    <div class="bg-white p-3 rounded-lg shadow-sm mb-6 flex items-center justify-between gap-4">

        <!-- "Showing: ..." Title -->
        <div class="flex items-center gap-2 flex-row-reverse">
            <h1 id="productsTitle" class="text-lg font-semibold text-gray-700 order-1 flex-shrink-0">
                Showing: <?= htmlspecialchars($categoryName) ?>
            </h1>
            <!-- Reset Filters Button (Initially Hidden) -->
            <button id="resetFiltersBtn" type="button" title="Reset Filters"
                class="hidden text-sm bg-red-600 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 rounded-md px-2 py-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 inline mr-0.5">
                    <path d="M15 2H9a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1Z" />
                    <path d="M19 5H5a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1Z" />
                    <path d="M15 9.5 9 15.5" />
                    <path d="m9 9.5 6 6" />
                </svg>
                Reset
                            </button>
                    </div>
                    
        <!-- Filters Container (Includes Button and Panel) -->
        <div class="order-2 relative flex items-center gap-2"> <!-- Added flex/gap, kept relative -->
            <!-- Mobile Filter Trigger Button -->
            <button id="mobileFilterTrigger" type="button"
                class="md:hidden inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 mr-1">
                    <path d="M21 4H3" />
                    <path d="M17 4v6" />
                    <path d="M13 4v10" />
                    <path d="M9 4v14" />
                    <path d="M5 4v16" />
                </svg>
                Filters
            </button>



            <!-- Category Filters Controls / Panel -->
            <div id="filterControls" class="hidden md:flex items-center gap-3 justify-end absolute md:static top-full right-0 md:right-auto mt-1 md:mt-0 bg-white md:bg-transparent shadow-lg md:shadow-none rounded-md md:rounded-none p-4 md:p-0 z-20 md:z-auto w-64 md:w-auto">
                <!-- Custom Category Dropdown -->
                <div class="relative inline-block text-left w-full md:w-auto" id="categoryDropdownContainer">
                        <div>
                        <button type="button" class="inline-flex justify-between w-56 rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-primary" id="categoryDropdownButton" aria-haspopup="true" aria-expanded="true">
                            <span class="flex items-center overflow-hidden whitespace-nowrap" id="categoryDropdownSelected">
                                <!-- Initially set based on $categoryId -->
                                <?php
                                $selectedCatImage = '';
                                $selectedCatName = 'All Categories';
                                if ($categoryId) {
                                    foreach ($categories as $category) {
                                        if ($category['id'] == $categoryId) {
                                            $selectedCatImage = $category['image'];
                                            $selectedCatName = $category['name'];
                                            break;
                                        }
                                    }
                                }
                                if ($selectedCatImage) {
                                    echo '<img src="' . htmlspecialchars($selectedCatImage) . '" alt="" class="h-5 w-5 mr-2 flex-shrink-0 rounded-sm object-cover">';
                                } else {
                                    // Optional: Placeholder icon if no image or "All Categories"
                                    echo '<svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM8.707 14.707a1 1 0 001.414 0L14 10.414V12a1 1 0 102 0V8a1 1 0 00-1-1h-4a1 1 0 100 2h1.586l-4.293 4.293a1 1 0 000 1.414z" clip-rule="evenodd" /></svg>';
                                }
                                echo '<span>' . htmlspecialchars($selectedCatName) . '</span>';
                                ?>
                            </span>
                            <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <!-- Hidden Input -->
                    <input type="hidden" id="categoryValue" name="category" value="<?= htmlspecialchars($categoryId ?? '') ?>">

                    <!-- Dropdown panel -->
                    <div id="categoryDropdownPanel" class="origin-top-right absolute right-0 mt-2 w-full rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10 hidden min-w-max" role="menu" aria-orientation="vertical" aria-labelledby="categoryDropdownButton">
                        <ul class="py-1" role="none">
                            <!-- All Categories Option -->
                            <li class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer category-option" role="menuitem" data-value="" data-image="">
                                <span class="flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M10 2a8 8 0 100 16 8 8 0 000-16zM8.707 14.707a1 1 0 001.414 0L14 10.414V12a1 1 0 102 0V8a1 1 0 00-1-1h-4a1 1 0 100 2h1.586l-4.293 4.293a1 1 0 000 1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <span>All Categories</span>
                                </span>
                            </li>
                            <!-- PHP Loop for Categories -->
                                <?php foreach ($categories as $category): ?>
                                <li class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-100 cursor-pointer category-option" role="menuitem" data-value="<?= $category['id'] ?>" data-image="<?= htmlspecialchars($category['image'] ?? '') ?>">
                                    <span class="flex items-center">
                                        <?php if (!empty($category['image'])): ?>
                                            <img src="<?= htmlspecialchars($category['image']) ?>" alt="" class="h-5 w-5 mr-2 flex-shrink-0 rounded-sm object-cover">
                                        <?php else: ?>
                                            <!-- Optional: Placeholder if a specific category lacks an image -->
                                            <span class="h-5 w-5 mr-2 flex-shrink-0 rounded-sm bg-gray-200"></span>
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($category['name']) ?></span>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                        </ul>
                    </div>
                        </div>
                        
                <!-- Subcategory Select -->
                <div class="w-full md:w-auto">
                    <label for="subcategory" class="sr-only">Subcategory</label>
                    <select id="subcategory" name="subcategory" class="block w-full py-2 pl-3 pr-8 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" <?= empty($categoryId) ? 'disabled' : '' ?>>
                                <option value="">All Subcategories</option>
                        <?php // Options populated by JS or initially if category is set
                                if (!empty($categoryId)) {
                                    foreach ($categories as $category) {
                                        if ($category['id'] == $categoryId) {
                                    if (!empty($category['subcategories'])) { // Check if subcategories exist
                                            foreach ($category['subcategories'] as $subcategory) {
                                                $selected = $subcategoryId == $subcategory['id'] ? 'selected' : '';
                                                echo "<option value=\"{$subcategory['id']}\" {$selected}>" . htmlspecialchars($subcategory['name']) . "</option>";
                                        }
                                            }
                                            break;
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
            </div>
        </div>
        
    <!-- Product Grid/List Container -->
    <div id="productContainer"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 transition-opacity duration-300 ease-in-out"
        style="opacity: 1;"> <!-- Make visible initially -->
        <!-- Render Grid Skeletons by Default in PHP -->
        <?php
        $gridSkeletonHTML_php = '
        <div class="product-card bg-white rounded-lg shadow overflow-hidden animate-pulse h-80">
            <div class="product-image-container h-48 bg-gray-300"></div>
            <div class="product-details p-4 flex flex-col justify-between flex-grow">
                 <div>
                     <div class="product-header">
                         <div class="h-4 bg-gray-300 rounded w-3/4 mb-2"></div>
                         <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                        </div>
                     <div class="h-3 bg-gray-300 rounded w-full mt-2"></div>
                     <div class="h-3 bg-gray-300 rounded w-5/6 mt-1"></div>
                    </div>
                 <div class="product-actions mt-3">
                     <div class="h-9 bg-gray-300 rounded w-full"></div>
                            </div>
                                </div>
                                </div>
    ';
        for ($i = 0; $i < 12; $i++) {
            echo $gridSkeletonHTML_php;
        }
        ?>
    </div>
</div>

<!-- Include modals -->
<?php include_once __DIR__ . '/../modals/productModal.php'; ?>
<?php include_once __DIR__ . '/../modals/cartModal.php'; ?>
<?php include_once __DIR__ . '/../modals/searchModal.php'; // Keep this one
?>

<style>
    #productContainer .animate-pulse {
        animation-duration: 3s !important;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    #product-page-container {
        /* Make this container grow */
        flex-grow: 1;
    }
</style>

<script>
  // Pass PHP data to JavaScript
  window.PHP_DATA = {
    allCategories: <?= json_encode($categories) ?>,
    // Use json_encode for null/empty string safety
    initialCategoryId: <?= json_encode($categoryId ?? null) ?>, 
    initialSubcategoryId: <?= json_encode($subcategoryId ?? null) ?>, 
    currencySymbol: <?= json_encode($currencySymbol) ?>
  };
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?> 