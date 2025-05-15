<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/settings.php';

function getProducts($filters = []) {
    global $conn, $db_connected;
    $products = [];
    $using_demo_data = !$db_connected || !$conn;

    if ($using_demo_data) {
        // Load demo data
        $demo_file = __DIR__ . '/../config/demo_data.json';
        if (file_exists($demo_file)) {
            $demo_data = json_decode(file_get_contents($demo_file), true);
            if ($demo_data && isset($demo_data['products']) && isset($demo_data['categories'])) {
                $products = processDemoData($demo_data, $filters);
            }
        }
        return [
            'success' => true,
            'products' => $products,
            'using_demo_data' => true
        ];
    }

    // Database query
    try {
        // Base query with all necessary joins
        $sql = "SELECT 
                p.id, 
                p.name, 
                p.slug as product_slug,
                p.description, 
                p.price, 
                p.image,
                p.category_id,
                p.stock,
                p.is_active,
                p.backorder,
                p.original_price,
                p.discount_percentage,
                c.name as category_name,
                c.slug as category_slug,
                parent.id as parent_category_id,
                parent.name as parent_category_name,
                parent.slug as parent_category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN categories parent ON c.parent_id = parent.id
            WHERE p.is_active = 1";

        $params = [];
        $types = '';

        // Apply filters
        if (!empty($filters['category'])) {
            // If filtering by parent category
            $sql .= " AND (parent.slug = ? OR (parent.id IS NULL AND c.slug = ?))";
            $types .= 'ss';
            $params[] = $filters['category'];
            $params[] = $filters['category'];
        }

        if (!empty($filters['subcategory'])) {
            // If filtering by specific subcategory
            $sql .= " AND c.slug = ?";
            $types .= 's';
            $params[] = $filters['subcategory'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $search_term = "%" . $filters['search'] . "%";
            $types .= 'ss';
            $params[] = $search_term;
            $params[] = $search_term;
        }

        if (isset($filters['min_price']) && is_numeric($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $types .= 'd';
            $params[] = $filters['min_price'];
        }

        if (isset($filters['max_price']) && is_numeric($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $types .= 'd';
            $params[] = $filters['max_price'];
        }

        $sql .= " ORDER BY p.name ASC";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                // Format the product data
                $product = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                    'slug' => $row['product_slug'],
                    'description' => $row['description'],
                    'price' => (float)$row['price'],
                    'image' => $row['image'] ?: '/assets/images/product-placeholder.png',
                    'category_id' => (int)$row['category_id'],
                    'category_name' => $row['category_name'],
                    'category_slug' => $row['category_slug'],
                    'parent_category_name' => $row['parent_category_name'],
                    'parent_category_slug' => $row['parent_category_slug'],
                    'stock' => (int)$row['stock'],
                    'is_active' => (bool)$row['is_active'],
                    'backorder' => (bool)$row['backorder'],
                    'original_price' => $row['original_price'] ? (float)$row['original_price'] : null,
                    'discount_percentage' => $row['discount_percentage'] ? (float)$row['discount_percentage'] : null
                ];

                // Fetch product options
                $options = [];
                $opt_sql = "SELECT option_name, option_values FROM product_options WHERE product_id = ?";
                $opt_stmt = $conn->prepare($opt_sql);
                if ($opt_stmt) {
                    $opt_stmt->bind_param('i', $row['id']);
                    $opt_stmt->execute();
                    $opt_result = $opt_stmt->get_result();
                    while ($opt_row = $opt_result->fetch_assoc()) {
                        $options[$opt_row['option_name']] = json_decode($opt_row['option_values'], true);
                    }
                    $opt_stmt->close();
                }
                $product['options'] = $options;

                $products[] = $product;
            }
            $stmt->close();
        }

        return [
            'success' => true,
            'products' => $products,
            'using_demo_data' => false
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'using_demo_data' => false
        ];
    }
}

function processDemoData($demo_data, $filters) {
    $products = [];
    $categories = $demo_data['categories'];
    
    foreach ($demo_data['products'] as $product) {
        // Find category information
        $category_info = findCategoryInfo($product['category_id'], $categories);
        
        if ($category_info) {
            $product['category_name'] = $category_info['name'];
            $product['category_slug'] = $category_info['slug'];
            $product['parent_category_name'] = $category_info['parent_name'];
            $product['parent_category_slug'] = $category_info['parent_slug'];
        }

        // Apply filters
        if (!empty($filters['category']) && 
            $product['parent_category_slug'] !== $filters['category'] && 
            $product['category_slug'] !== $filters['category']) {
            continue;
        }

        if (!empty($filters['subcategory']) && $product['category_slug'] !== $filters['subcategory']) {
            continue;
        }

        if (!empty($filters['search'])) {
            $search_term = strtolower($filters['search']);
            if (strpos(strtolower($product['name']), $search_term) === false && 
                strpos(strtolower($product['description']), $search_term) === false) {
                continue;
            }
        }

        if (isset($filters['min_price']) && $product['price'] < $filters['min_price']) {
            continue;
        }

        if (isset($filters['max_price']) && $product['price'] > $filters['max_price']) {
            continue;
        }

        $products[] = $product;
    }

    return $products;
}

function findCategoryInfo($category_id, $categories) {
    foreach ($categories as $parent) {
        if (!empty($parent['subcategories'])) {
            foreach ($parent['subcategories'] as $sub) {
                if ((int)$sub['id'] === (int)$category_id) {
                    return [
                        'name' => $sub['name'],
                        'slug' => $sub['slug'],
                        'parent_name' => $parent['name'],
                        'parent_slug' => $parent['slug']
                    ];
                }
            }
        }
        // Check if it's a parent category
        if ((int)$parent['id'] === (int)$category_id) {
            return [
                'name' => $parent['name'],
                'slug' => $parent['slug'],
                'parent_name' => null,
                'parent_slug' => null
            ];
        }
    }
    return null;
}

// Get filter parameters from request
$filters = [
    'category' => $_GET['category'] ?? null,
    'subcategory' => $_GET['subcategory_slug'] ?? null,
    'search' => $_GET['search'] ?? null,
    'min_price' => isset($_GET['price_min']) ? floatval($_GET['price_min']) : null,
    'max_price' => isset($_GET['price_max']) ? floatval($_GET['price_max']) : null
];

// Get and return products
$result = getProducts($filters);
echo json_encode($result); 