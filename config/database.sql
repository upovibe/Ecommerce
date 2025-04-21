-- E-Commerce Template Database Structure

-- Drop database if exists
DROP DATABASE IF EXISTS ecommerce_template;

-- Create database
CREATE DATABASE ecommerce_template DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use database
USE ecommerce_template;

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(150) UNIQUE,
    description TEXT,
    parent_id INT NULL,
    image VARCHAR(255) NULL,
    featured BOOLEAN DEFAULT 0,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category_slug (slug)
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL, -- This is the SELLING price
    original_price DECIMAL(10, 2) NULL, -- Price before discount, calculated if percentage is set
    discount_percentage DECIMAL(5, 2) NULL, -- Optional discount percentage (e.g., 15.00 for 15%)
    image VARCHAR(255),
    category_id INT,
    stock INT DEFAULT 0,
    featured BOOLEAN DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    backorder BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_product_slug (slug)
);

-- Product options table
CREATE TABLE product_options (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    option_name VARCHAR(50) NOT NULL,
    option_values JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY `idx_product_option_name` (`product_id`,`option_name`)
);

-- Store settings table
CREATE TABLE store_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Store content table
CREATE TABLE store_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content_key VARCHAR(50) NOT NULL UNIQUE,
    content_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    password_changed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default store settings
INSERT INTO store_settings (setting_key, setting_value) VALUES
('store_name', 'E-Commerce Store'),
('store_description', 'Your one-stop shop for all your needs'),
('store_logo', '/assets/images/logo.png'),
('whatsapp_number', '233542838165'),
('whatsapp_message_template', 'Hello, I want to inquire about:\n{ITEMS}\nTotal: {CURRENCY}{TOTAL}'),
('currency_symbol', '₵'),
('footer_text', '© 2023 E-Commerce Store. All rights reserved.'),
('theme_color', '#3B82F6'),
('brand_text_color', '#FFFFFF');

-- Insert default content
INSERT INTO store_content (content_key, content_value) VALUES
('hero_title', 'Welcome to our Online Store'),
('hero_subtitle', 'Find everything you need, from essentials to luxuries.'),
('about_title', 'About Our Store'),
('about_content', '<p>We are dedicated to providing high-quality products at affordable prices. Our store features a wide range of items including bags, groceries, and shoes.</p><p>With a focus on customer satisfaction, we ensure that every purchase meets our high standards for quality and durability.</p>'),
('about_image', '/assets/images/demo/about-image.png'),
('featured_title', 'Shop by Category'),
('featured_subtitle', 'Explore our popular categories and find exactly what you\'re looking for.'),
('hero_image', '/assets/images/demo/hero-bg.png'),
('product_page_banner_image', '/assets/images/demo/product-banner.png'),
('product_banner_title', 'Product Banner Title'),
('product_banner_subtitle', 'Product subtile text goes here, edit it from settings in your admin panel');

-- Insert demo categories
INSERT INTO categories (id, name, slug, description, parent_id, image, featured, display_order) VALUES
(1, 'Bags', 'bags', 'All types of bags', NULL, '/assets/images/demo/bags-category.png', 1, 1),
(2, 'Wig', 'wig', 'High quality wigs', NULL, '/assets/images/demo/wig-category.png', 1, 2),
(3, 'Shoes', 'shoes', 'Footwear for all occasions', NULL, '/assets/images/demo/shoes-category.png', 1, 3);

-- Subcategories
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES
(101, 'School Bags', 'school-bags', 'Bags for school and education', 1, 0, 1),
(102, 'Travel Bags', 'travel-bags', 'Bags for travel and adventure', 1, 0, 2),
(103, 'Girl\'s Bags', 'girls-bags', 'Fashionable bags for girls', 1, 0, 3),
(201, 'Lace Front Wigs', 'lace-front-wigs', 'Wigs with lace front for natural hairline', 2, 0, 1),
(202, 'Synthetic Wigs', 'synthetic-wigs', 'Affordable synthetic fiber wigs', 2, 0, 2),
(203, 'Human Hair Wigs', 'human-hair-wigs', 'Premium wigs made from human hair', 2, 0, 3),
(301, 'Sneakers', 'sneakers', 'Casual and sports sneakers', 3, 0, 1),
(302, 'Formal Shoes', 'formal-shoes', 'Business and formal footwear', 3, 0, 2),
(303, 'Ladies\' Heels', 'ladies-heels', 'High heels and fashion footwear', 3, 0, 3);

-- Insert demo products
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES
(1, 'School Backpack', 'school-backpack', 'Sturdy school backpack.', 150.00, 180.00, 16.67, '/assets/images/demo/backpack.png', 101, 25, 1, TRUE, FALSE),
(2, 'Travel Duffel Bag', 'travel-duffel-bag', 'Spacious travel duffel bag.', 300.00, NULL, NULL, '/assets/images/demo/duffel.png', 102, 15, 1, TRUE, FALSE),
(3, 'Pink Sequin Purse', 'pink-sequin-purse', 'Stylish pink sequin purse.', 80.00, NULL, NULL, '/assets/images/demo/purse.png', 103, 30, 0, TRUE, FALSE),
(4, 'Long Body Wave Lace Front Wig', 'long-body-wave-lace-front', 'Beautiful long body wave wig with realistic lace front.', 800.00, 900.00, 11.11, '/assets/images/demo/wig-lace-bodywave.png', 201, 15, 1, TRUE, FALSE),
(5, 'Short Bob Synthetic Wig - Black', 'short-bob-synthetic-black', 'Chic and easy-to-manage short black bob wig.', 150.00, NULL, NULL, '/assets/images/demo/wig-synth-bob.png', 202, 30, 0, TRUE, FALSE),
(6, 'Straight Human Hair Wig 18inch', 'straight-human-hair-18inch', 'Silky straight 18-inch human hair wig.', 1200.00, NULL, NULL, '/assets/images/demo/wig-human-straight.png', 203, 10, 1, TRUE, FALSE),
(7, 'Running Sneakers', 'running-sneakers', 'Comfortable running sneakers.', 250.00, 300.00, 16.67, '/assets/images/demo/sneakers.png', 301, 20, 1, TRUE, FALSE),
(8, 'Oxford Dress Shoes', 'oxford-dress-shoes', 'Classic Oxford dress shoes.', 350.00, NULL, NULL, '/assets/images/demo/oxford.png', 302, 15, 0, FALSE, FALSE),
(9, 'Stiletto Heels', 'stiletto-heels', 'Elegant stiletto heels.', 180.00, 200.00, 10.00, '/assets/images/demo/heels.png', 303, 10, 0, TRUE, FALSE);

-- Insert demo product options
INSERT INTO product_options (product_id, option_name, option_values) VALUES
(1, 'color', '["Black", "Blue", "Red"]'),
(2, 'color', '["Black", "Navy", "Grey"]'),
(2, 'size', '["Medium", "Large"]'),
(3, 'color', '["Pink", "Purple", "Gold"]'),
(4, 'color', '["Natural Black", "Brown", "Blonde"]'),
(4, 'length', '["18 inch", "22 inch", "26 inch"]'),
(5, 'color', '["Black", "Red", "Blue"]'),
(6, 'color', '["Natural Black"]'),
(6, 'length', '["18 inch", "20 inch", "22 inch"]'),
(7, 'color', '["White", "Black", "Blue"]'),
(7, 'size', '["40", "41", "42", "43", "44", "45"]'),
(8, 'color', '["Black", "Brown"]'),
(9, 'color', '["Black", "Red", "Nude"]'),
(9, 'size', '["36", "37", "38", "39", "40"]');