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
    price DECIMAL(10, 2) NOT NULL,
    original_price DECIMAL(10, 2) NULL,
    discount_percentage DECIMAL(5, 2) NULL,
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
    full_name VARCHAR(100) NULL,
    gender VARCHAR(20) NULL,
    about TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Update the first admin user (assuming ID=1) with profile details
-- This requires an admin user with ID=1 to exist first.
-- You might need to create the admin user separately if your setup doesn't automatically create one.
UPDATE admin_users 
SET 
    full_name = 'Demo Admin', 
    gender = 'Prefer not to say',
    about = 'This is the default administrator account.'
WHERE id = 1;

-- Insert default store settings (broken into individual statements)
INSERT INTO store_settings (setting_key, setting_value) VALUES ('store_name', 'E-Commerce Store');
INSERT INTO store_settings (setting_key, setting_value) VALUES ('store_description', 'Your one-stop shop for all your needs');
INSERT INTO store_settings (setting_key, setting_value) VALUES ('store_logo', '/assets/images/logo.png');
INSERT INTO store_settings (setting_key, setting_value) VALUES ('whatsapp_number', '233542838165');
INSERT INTO store_settings (setting_key, setting_value) VALUES ('whatsapp_message_template', 'Hello, I want to inquire about:\n{ITEMS}\nTotal: {CURRENCY}{TOTAL}');
INSERT INTO store_settings (setting_key, setting_value) VALUES ('currency_symbol', '₵');
INSERT INTO store_settings (setting_key, setting_value) VALUES ('footer_text', '© 2023 E-Commerce Store. All rights reserved.');
INSERT INTO store_settings (setting_key, setting_value) VALUES ('theme_color', '#3B82F6');
INSERT INTO store_settings (setting_key, setting_value) VALUES ('brand_text_color', '#FFFFFF');
INSERT INTO store_settings (setting_key, setting_value) VALUES ('store_favicon', '/assets/images/favicon.ico');
-- INSERT INTO store_settings (setting_key, setting_value) VALUES ('facebook_username', 'example_store');
-- INSERT INTO store_settings (setting_key, setting_value) VALUES ('instagram_username', 'example_store');
-- INSERT INTO store_settings (setting_key, setting_value) VALUES ('twitter_username', 'example_store');
-- INSERT INTO store_settings (setting_key, setting_value) VALUES ('tiktok_username', 'example_store');
-- INSERT INTO store_settings (setting_key, setting_value) VALUES ('linkedin_username', 'example_store');
-- INSERT INTO store_settings (setting_key, setting_value) VALUES ('youtube_username', 'example_store');

-- Insert default content (broken into individual statements)
INSERT INTO store_content (content_key, content_value) VALUES ('hero_title', 'Welcome to our Online Store');
INSERT INTO store_content (content_key, content_value) VALUES ('hero_subtitle', 'Find everything you need, from essentials to luxuries.');
INSERT INTO store_content (content_key, content_value) VALUES ('about_title', 'About Our Store');
INSERT INTO store_content (content_key, content_value) VALUES ('about_content', '<p>We are dedicated to providing high-quality products at affordable prices. Our store features a wide range of items including bags, groceries, and shoes.</p><p>With a focus on customer satisfaction, we ensure that every purchase meets our high standards for quality and durability.</p>');
INSERT INTO store_content (content_key, content_value) VALUES ('about_image', '/assets/images/demo/about-image.png');
INSERT INTO store_content (content_key, content_value) VALUES ('featured_title', 'Shop by Category');
INSERT INTO store_content (content_key, content_value) VALUES ('featured_subtitle', 'Explore our popular categories and find exactly what you\'re looking for.');
INSERT INTO store_content (content_key, content_value) VALUES ('hero_image', '/assets/images/demo/hero-image.png');
INSERT INTO store_content (content_key, content_value) VALUES ('product_page_banner_image', '/assets/images/demo/product-banner.png');
INSERT INTO store_content (content_key, content_value) VALUES ('product_banner_title', 'Product Banner Title');
INSERT INTO store_content (content_key, content_value) VALUES ('product_banner_subtitle', 'Product subtile text goes here, edit it from settings in your admin panel');

-- Insert demo categories
INSERT INTO categories (id, name, slug, description, parent_id, image, featured, display_order) VALUES (1, 'Bags', 'bags', 'All types of bags', NULL, '/assets/images/demo/bags-category.png', 1, 1);
INSERT INTO categories (id, name, slug, description, parent_id, image, featured, display_order) VALUES (2, 'Wig', 'wig', 'High quality wigs', NULL, '/assets/images/demo/wig-category.png', 1, 2);
INSERT INTO categories (id, name, slug, description, parent_id, image, featured, display_order) VALUES (3, 'Shoes', 'shoes', 'Footwear for all occasions', NULL, '/assets/images/demo/shoes-category.png', 1, 3);

-- Subcategories
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES (101, 'School Bags', 'school-bags', 'Bags for school and education', 1, 0, 1);
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES (102, 'Travel Bags', 'travel-bags', 'Bags for travel and adventure', 1, 0, 2);
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES (103, 'Girl\'s Bags', 'girls-bags', 'Fashionable bags for girls', 1, 0, 3);
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES (201, 'Lace Front Wigs', 'lace-front-wigs', 'Wigs with lace front for natural hairline', 2, 0, 1);
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES (202, 'Synthetic Wigs', 'synthetic-wigs', 'Affordable synthetic fiber wigs', 2, 0, 2);
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES (203, 'Human Hair Wigs', 'human-hair-wigs', 'Premium wigs made from human hair', 2, 0, 3);
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES (301, 'Sneakers', 'sneakers', 'Casual and sports sneakers', 3, 0, 1);
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES (302, 'Formal Shoes', 'formal-shoes', 'Business and formal footwear', 3, 0, 2);
INSERT INTO categories (id, name, slug, description, parent_id, featured, display_order) VALUES (303, 'Ladies\' Heels', 'ladies-heels', 'High heels and fashion footwear', 3, 0, 3);

-- Insert demo products
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES (1, 'School Backpack', 'school-backpack', 'Sturdy school backpack.', 150.00, 180.00, 16.67, '/assets/images/demo/backpack.png', 101, 25, 1, TRUE, FALSE);
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES (2, 'Travel Duffel Bag', 'travel-duffel-bag', 'Spacious travel duffel bag.', 300.00, NULL, NULL, '/assets/images/demo/duffel.png', 102, 15, 1, TRUE, FALSE);
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES (3, 'Pink Sequin Purse', 'pink-sequin-purse', 'Stylish pink sequin purse.', 80.00, NULL, NULL, '/assets/images/demo/purse.png', 103, 30, 0, TRUE, FALSE);
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES (4, 'Long Body Wave Lace Front Wig', 'long-body-wave-lace-front', 'Beautiful long body wave wig with realistic lace front.', 800.00, 900.00, 11.11, '/assets/images/demo/wig-lace-bodywave.png', 201, 15, 1, TRUE, FALSE);
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES (5, 'Short Bob Synthetic Wig - Black', 'short-bob-synthetic-black', 'Chic and easy-to-manage short black bob wig.', 150.00, NULL, NULL, '/assets/images/demo/wig-synth-bob.png', 202, 30, 0, TRUE, FALSE);
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES (6, 'Straight Human Hair Wig 18inch', 'straight-human-hair-18inch', 'Silky straight 18-inch human hair wig.', 1200.00, NULL, NULL, '/assets/images/demo/wig-human-straight.png', 203, 10, 1, TRUE, FALSE);
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES (7, 'Running Sneakers', 'running-sneakers', 'Comfortable running sneakers.', 250.00, 300.00, 16.67, '/assets/images/demo/sneakers.png', 301, 20, 1, TRUE, FALSE);
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES (8, 'Oxford Dress Shoes', 'oxford-dress-shoes', 'Classic Oxford dress shoes.', 350.00, NULL, NULL, '/assets/images/demo/oxford.png', 302, 15, 0, FALSE, FALSE);
INSERT INTO products (id, name, slug, description, price, original_price, discount_percentage, image, category_id, stock, featured, is_active, backorder) VALUES (9, 'Stiletto Heels', 'stiletto-heels', 'Elegant stiletto heels.', 180.00, 200.00, 10.00, '/assets/images/demo/heels.png', 303, 10, 0, TRUE, FALSE);

-- Insert demo product options
INSERT INTO product_options (product_id, option_name, option_values) VALUES (1, 'color', '["Black", "Blue", "Red"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (2, 'color', '["Black", "Navy", "Grey"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (2, 'size', '["Medium", "Large"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (3, 'color', '["Pink", "Purple", "Gold"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (4, 'color', '["Natural Black", "Brown", "Blonde"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (4, 'length', '["18 inch", "22 inch", "26 inch"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (5, 'color', '["Black", "Red", "Blue"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (6, 'color', '["Natural Black"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (6, 'length', '["18 inch", "20 inch", "22 inch"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (7, 'color', '["White", "Black", "Blue"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (7, 'size', '["40", "41", "42", "43", "44", "45"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (8, 'color', '["Black", "Brown"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (9, 'color', '["Black", "Red", "Nude"]');
INSERT INTO product_options (product_id, option_name, option_values) VALUES (9, 'size', '["36", "37", "38", "39", "40"]');

-- Note: The admin user update statement should be executed after creating an admin user
-- This is typically done in the PHP installation script

-- Contact settings table
CREATE TABLE contact_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- FAQs table
CREATE TABLE faqs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    page_location VARCHAR(50) DEFAULT 'contact',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Store maps table
CREATE TABLE store_maps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    location_name VARCHAR(100) NOT NULL,
    address TEXT,
    map_url TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default contact settings
INSERT INTO contact_settings (setting_key, setting_value) VALUES ('business_hours_weekdays', '9am - 6pm');
INSERT INTO contact_settings (setting_key, setting_value) VALUES ('business_hours_saturday', '10am - 4pm');
INSERT INTO contact_settings (setting_key, setting_value) VALUES ('business_hours_sunday', 'Closed');
INSERT INTO contact_settings (setting_key, setting_value) VALUES ('contact_email', 'contact@example.com');
INSERT INTO contact_settings (setting_key, setting_value) VALUES ('contact_phone', '+1234567890');
INSERT INTO contact_settings (setting_key, setting_value) VALUES ('contact_form_enabled', 'true');
INSERT INTO contact_settings (setting_key, setting_value) VALUES ('contact_page_title', 'Contact Us');
INSERT INTO contact_settings (setting_key, setting_value) VALUES ('contact_page_subtitle', 'We\'d love to hear from you! Send us a message and we\'ll respond as soon as possible.');
INSERT INTO contact_settings (setting_key, setting_value) VALUES ('contact_banner_image', '/assets/images/Ecommerce-bg.jpg');

-- Add email configuration settings
INSERT INTO contact_settings (setting_key, setting_value) VALUES 
('smtp_host', 'smtp.example.com'),
('smtp_port', '587'),
('smtp_username', 'user@example.com'),
('smtp_auth_key', 'sample_password_123'),
('smtp_from_email', 'noreply@example.com'),
('smtp_from_name', 'Your Store Name'),
('email_enabled', 'true');

-- Insert default FAQs
INSERT INTO faqs (question, answer, display_order, page_location) VALUES
('What payment methods do you accept?', 'We accept various payment methods including credit/debit cards, mobile money, and bank transfers. All payments are secure and encrypted.', 1, 'contact'),
('How long does shipping take?', 'Shipping times vary depending on your location. Typically, local deliveries take 1-3 business days, while international shipping can take 7-14 business days.', 2, 'contact'),
('What is your return policy?', 'We offer a 30-day return policy. If you\'re not satisfied with your purchase, you can return it within 30 days for a full refund or exchange. Items must be unused and in their original packaging.', 3, 'contact'),
('Do you ship internationally?', 'Yes, we ship to most countries worldwide. International shipping rates and delivery times vary by location. Please contact us for specific information about shipping to your country.', 4, 'contact');

-- Insert a demo store map
INSERT INTO store_maps (location_name, address, map_url, display_order) VALUES
('Main Store', '123 Commerce St, Example City', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.1!2d-73.9!3d40.7!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQyJzAwLjAiTiA3M8KwNTQnMDAuMCJX!5e0!3m2!1sen!2sus!4v1600000000000!5m2!1sen!2sus', 1);