<?php
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../utils/cart.php';

// Get categories from database (fallback to demo categories if database not set up)
function getCategories() {
    global $conn, $db_connected;
    
    $categories = [];
    
    $sql = "SELECT id, name, parent_id FROM categories WHERE parent_id IS NULL ORDER BY name";
    
    // Only query database if connection is available
    if ($db_connected && $conn) {
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $category = [
                    'id' => $row['id'],
                    'name' => $row['name'],
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
    
    // If no categories found in database or connection failed, use demo categories
    if (empty($categories)) {
        $categories = [
            [
                'id' => 1,
                'name' => 'Bags',
                'subcategories' => [
                    ['id' => 101, 'name' => 'School Bags'],
                    ['id' => 102, 'name' => 'Travel Bags'],
                    ['id' => 103, 'name' => "Girl's Bags"]
                ]
            ],
            [
                'id' => 2,
                'name' => 'Groceries',
                'subcategories' => [
                    ['id' => 201, 'name' => 'Fresh Produce'],
                    ['id' => 202, 'name' => 'Canned Goods'],
                    ['id' => 203, 'name' => 'Dairy']
                ]
            ],
            [
                'id' => 3,
                'name' => 'Shoes',
                'subcategories' => [
                    ['id' => 301, 'name' => 'Sneakers'],
                    ['id' => 302, 'name' => 'Formal Shoes'],
                    ['id' => 303, 'name' => "Ladies' Heels"]
                ]
            ]
        ];
    }
    
    return $categories;
}

// Get products with optional filters
function getProducts($search = '', $categoryId = null, $subcategoryId = null) {
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
        $sql = "SELECT p.id, p.name, p.price, p.description, p.image, p.category_id, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
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
                    'price' => $row['price'],
                    'description' => $row['description'],
                    'image' => $row['image'],
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'options' => $options
                ];
            }
        }
    }
    
    // Use demo products if database connection failed or no products found
    if (empty($products)) {
        // Demo products if database is not set up
        $demoProducts = [
            [
                'id' => 1,
                'name' => 'School Backpack',
                'price' => 4500,
                'description' => 'Sturdy school backpack with multiple compartments and padded shoulder straps.',
                'image' => '/assets/images/demo/backpack.png',
                'category_id' => 101,
                'category_name' => 'School Bags',
                'options' => [
                    'color' => ['Black', 'Blue', 'Red']
                ]
            ],
            [
                'id' => 2,
                'name' => 'Travel Duffel Bag',
                'price' => 8500,
                'description' => 'Spacious travel duffel bag with wheels and telescopic handle.',
                'image' => '/assets/images/demo/duffel.png',
                'category_id' => 102,
                'category_name' => 'Travel Bags',
                'options' => [
                    'color' => ['Black', 'Navy', 'Grey'],
                    'size' => ['Medium', 'Large']
                ]
            ],
            [
                'id' => 3,
                'name' => 'Pink Sequin Purse',
                'price' => 3200,
                'description' => 'Stylish pink sequin purse for girls, perfect for special occasions.',
                'image' => '/assets/images/demo/purse.png',
                'category_id' => 103,
                'category_name' => "Girl's Bags",
                'options' => [
                    'color' => ['Pink', 'Purple', 'Gold']
                ]
            ],
            [
                'id' => 4,
                'name' => 'Long Body Wave Lace Front Wig',
                'price' => 25000.00,
                'description' => 'Beautiful long body wave wig with realistic lace front.',
                'image' => '/assets/images/demo/wig-lace-bodywave.png',
                'category_id' => 201,
                'category_name' => 'Lace Front Wigs',
                'options' => [
                     'color' => ['Natural Black', 'Brown', 'Blonde'],
                     'length' => ['18 inch', '22 inch', '26 inch']
                ]
            ],
            [
                'id' => 5,
                'name' => 'Short Bob Synthetic Wig - Black',
                'price' => 7500.00,
                'description' => 'Chic and easy-to-manage short black bob wig.',
                'image' => '/assets/images/demo/wig-synth-bob.png',
                'category_id' => 202,
                'category_name' => 'Synthetic Wigs',
                'options' => [
                     'color' => ['Black', 'Red', 'Blue']
                ]
            ],
            [
                'id' => 6,
                'name' => 'Straight Human Hair Wig 18inch',
                'price' => 45000.00,
                'description' => 'Silky straight 18-inch human hair wig.',
                'image' => '/assets/images/demo/wig-human-straight.png',
                'category_id' => 203,
                'category_name' => 'Human Hair Wigs',
                'options' => [
                    'color' => ['Natural Black'], 
                    'length' => ['18 inch', '20 inch', '22 inch']
                ]
            ],
            [
                'id' => 7,
                'name' => 'Running Sneakers',
                'price' => 12000,
                'description' => 'Comfortable running sneakers with cushioned soles for maximum comfort.',
                'image' => '/assets/images/demo/sneakers.png',
                'category_id' => 301,
                'category_name' => 'Sneakers',
                'options' => [
                    'color' => ['White', 'Black', 'Blue'],
                    'size' => ['40', '41', '42', '43', '44', '45']
                ]
            ],
            [
                'id' => 8,
                'name' => 'Oxford Dress Shoes',
                'price' => 15000,
                'description' => 'Classic Oxford dress shoes made from genuine leather.',
                'image' => '/assets/images/demo/oxford.png',
                'category_id' => 302,
                'category_name' => 'Formal Shoes',
                'options' => [
                    'color' => ['Black', 'Brown'],
                    'size' => ['40', '41', '42', '43', '44', '45']
                ]
            ],
            [
                'id' => 9,
                'name' => 'Stiletto Heels',
                'price' => 9500,
                'description' => 'Elegant stiletto heels for special occasions and formal events.',
                'image' => '/assets/images/demo/heels.png',
                'category_id' => 303,
                'category_name' => "Ladies' Heels",
                'options' => [
                    'color' => ['Black', 'Red', 'Nude'],
                    'size' => ['36', '37', '38', '39', '40']
                ]
            ],
        ];
        
        // Apply filters to demo products
        foreach ($demoProducts as $product) {
            // Apply search filter
            if (!empty($search)) {
                $nameMatch = stripos($product['name'], $search) !== false;
                $descMatch = stripos($product['description'], $search) !== false;
                
                if (!$nameMatch && !$descMatch) {
                    continue; // Skip this product
                }
            }
            
            // Apply category filter
            if (!empty($subcategoryId) && $product['category_id'] != $subcategoryId) {
                continue; // Skip this product
            } elseif (!empty($categoryId)) {
                // Check if product category ID matches category ID or is a subcategory of it
                $categoryMatches = false;
                $allDemoCategories = getCategories(); // Get all categories for checking

                if ($product['category_id'] == $categoryId) {
                    $categoryMatches = true;
                } else {
                    // Check if product category is a subcategory of the selected category
                    foreach ($allDemoCategories as $parentCategory) {
                        if ($parentCategory['id'] == $categoryId) {
                            foreach ($parentCategory['subcategories'] as $sub) {
                                if ($sub['id'] == $product['category_id']) {
                                    $categoryMatches = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }
                
                if (!$categoryMatches) {
                    continue; // Skip this product
                }
            }
            
            $products[] = $product;
        }
    }
    
    return $products;
}

// Function to get the name of the currently selected category or subcategory
function getCategoryName($categoryId = null, $subcategoryId = null, $categories = []) {
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

// Get filter parameters
$search = $_GET['search'] ?? '';
$categoryId = isset($_GET['category']) ? (int) $_GET['category'] : null;
$subcategoryId = isset($_GET['subcategory']) ? (int) $_GET['subcategory'] : null;

// Get categories and products
$categories = getCategories();
$products = getProducts($search, $categoryId, $subcategoryId);
$categoryName = getCategoryName($categoryId, $subcategoryId, $categories);

// Get currency symbol
$currencySymbol = STORE_SETTINGS['currency_symbol'] ?? '₦';

// Include header
include_once __DIR__ . '/../includes/header.php';

// Include the database connection notice component right after the header
include __DIR__ . '/../includes/components/db_notice.php'; 

?>

<div class="container mx-auto px-4 py-8">

    <div class="flex flex-col md:flex-row gap-8">
        <!-- Sidebar -->
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <form action="/pages/products.php" method="GET" class="space-y-6">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700">Search Products</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="search" id="search" class="focus:ring-primary focus:border-primary flex-1 block w-full rounded-md sm:text-sm border-gray-300" placeholder="Enter product name or keyword" value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                                Search
                            </button>
                        </div>
                    </div>
                    
                    <!-- Category Filters -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                            <select id="category" name="category" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id'] ?>" <?= $categoryId == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label for="subcategory" class="block text-sm font-medium text-gray-700">Subcategory</label>
                            <select id="subcategory" name="subcategory" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-primary focus:border-primary sm:text-sm" <?= empty($categoryId) ? 'disabled' : '' ?>>
                                <option value="">All Subcategories</option>
                                <?php
                                if (!empty($categoryId)) {
                                    foreach ($categories as $category) {
                                        if ($category['id'] == $categoryId) {
                                            foreach ($category['subcategories'] as $subcategory) {
                                                $selected = $subcategoryId == $subcategory['id'] ? 'selected' : '';
                                                echo "<option value=\"{$subcategory['id']}\" {$selected}>" . htmlspecialchars($subcategory['name']) . "</option>";
                                            }
                                            break;
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="w-full md:w-3/4">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-6">Products</h1>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (empty($products)): ?>
                    <div class="col-span-full text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="mt-2 text-lg font-medium text-gray-900">No products found for your selection.</h3>
                        <p class="mt-1 text-sm text-gray-500">Try changing your search or filter criteria.</p>
                        <div class="mt-6">
                            <a href="/pages/products.php" class="text-primary hover:text-indigo-700">
                                View all products
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="bg-white rounded-lg shadow overflow-hidden transition-shadow duration-300 hover:shadow-lg">
                            <div class="aspect-ratio bg-gray-200">
                                <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="w-full h-full object-center object-cover">
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900 truncate"><?= htmlspecialchars($product['name']) ?></h3>
                                    <p class="text-lg font-medium text-primary"><?= $currencySymbol . number_format($product['price'], 2) ?></p>
                                </div>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2"><?= htmlspecialchars($product['description']) ?></p>
                                <div class="mt-3">
                                    <button type="button" 
                                            class="view-product-btn w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-indigo-700"
                                            data-product-id="<?= $product['id'] ?>"
                                            data-product-name="<?= htmlspecialchars($product['name']) ?>"
                                            data-product-price="<?= $product['price'] ?>"
                                            data-product-image="<?= htmlspecialchars($product['image']) ?>"
                                            data-product-description="<?= htmlspecialchars($product['description']) ?>"
                                            data-product-options='<?= htmlspecialchars(json_encode($product['options'])) ?>'>
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Include modals -->
<?php include_once __DIR__ . '/../modals/productModal.php'; ?>
<?php include_once __DIR__ . '/../modals/cartModal.php'; ?>
<?php include_once __DIR__ . '/../modals/orderModal.php'; ?>

<script>
    // Category-Subcategory filter relationship
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category');
        const subcategorySelect = document.getElementById('subcategory');
        
        if (categorySelect && subcategorySelect) {
            // Category change event
            categorySelect.addEventListener('change', function() {
                const categoryId = this.value;
                
                // Clear subcategory options
                subcategorySelect.innerHTML = '<option value="">All Subcategories</option>';
                
                if (categoryId) {
                    // Enable subcategory select
                    subcategorySelect.disabled = false;
                    
                    // Get subcategories for selected category
                    const subcategories = getSubcategoriesForCategory(categoryId);
                    
                    // Add subcategory options
                    subcategories.forEach(function(subcategory) {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        subcategorySelect.appendChild(option);
                    });
                } else {
                    // Disable subcategory select if no category selected
                    subcategorySelect.disabled = true;
                }
                
                // Submit form to apply filter
                // document.querySelector('form').submit();
            });
        }
        
        // Helper function to get subcategories for a category
        function getSubcategoriesForCategory(categoryId) {
            const categories = <?= json_encode($categories) ?>;
            let subcategories = [];
            
            categories.forEach(function(category) {
                if (category.id == categoryId) {
                    subcategories = category.subcategories;
                }
            });
            
            return subcategories;
        }
    });
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?> 