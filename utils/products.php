<?php
/**
 * Product related functions
 */

/**
 * Get a list of products with optional filtering
 * 
 * @param array $filters Optional array of filters
 * @param int $limit Number of products to return
 * @param int $offset Starting position for pagination
 * @return array List of products
 */
function getProducts($filters = [], $limit = 12, $offset = 0) {
    global $db;
    
    $query = "SELECT p.*, c.name AS category 
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($filters['category_id'])) {
        $query .= " AND p.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    if (!empty($filters['search'])) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (isset($filters['in_stock']) && $filters['in_stock'] !== null) {
        $query .= " AND p.in_stock = ?";
        $params[] = $filters['in_stock'] ? 1 : 0;
    }
    
    // Add sorting
    $query .= " ORDER BY p.created_at DESC";
    
    // Add pagination
    $query .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        return [];
    }
    
    // Bind parameters using references
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Format prices from cents to dollars for display
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Get product details by ID
 * 
 * @param int $productId Product ID
 * @return array|null Product details or null if not found
 */
function getProductDetails($productId) {
    global $db;
    
    $query = "SELECT p.*, c.name AS category 
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.id = ?";
              
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return null;
    }
    
    $product = $result->fetch_assoc();
    
    // Get product options if any
    $product['options'] = getProductOptions($productId);
    
    return $product;
}

/**
 * Get product options
 * 
 * @param int $productId Product ID
 * @return array Product options
 */
function getProductOptions($productId) {
    global $db;
    
    $query = "SELECT option_name, option_values
              FROM product_options
              WHERE product_id = ?";
              
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $options = [];
    
    while ($row = $result->fetch_assoc()) {
        // Option values are stored as comma-separated values
        $values = explode(',', $row['option_values']);
        $options[$row['option_name']] = array_map('trim', $values);
    }
    
    return $options;
}

/**
 * Count total products based on filters
 * 
 * @param array $filters Optional array of filters
 * @return int Total number of products
 */
function countProducts($filters = []) {
    global $db;
    
    $query = "SELECT COUNT(*) as total 
              FROM products p
              WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($filters['category_id'])) {
        $query .= " AND p.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    if (!empty($filters['search'])) {
        $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if (isset($filters['in_stock']) && $filters['in_stock'] !== null) {
        $query .= " AND p.in_stock = ?";
        $params[] = $filters['in_stock'] ? 1 : 0;
    }
    
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        return 0;
    }
    
    // Bind parameters using references
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'] ?? 0;
}

/**
 * Get list of categories
 * 
 * @return array List of categories
 */
function getCategories() {
    global $db;
    
    $query = "SELECT id, name, slug 
              FROM categories 
              ORDER BY name ASC";
              
    $result = $db->query($query);
    
    if (!$result) {
        return [];
    }
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

/**
 * Get related products
 * 
 * @param int $productId Current product ID
 * @param int $categoryId Category ID for finding related products
 * @param int $limit Number of related products to return
 * @return array List of related products
 */
function getRelatedProducts($productId, $categoryId, $limit = 4) {
    global $db;
    
    $query = "SELECT p.*, c.name AS category 
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.category_id = ? AND p.id != ?
              ORDER BY RAND()
              LIMIT ?";
              
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param('iii', $categoryId, $productId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
} 