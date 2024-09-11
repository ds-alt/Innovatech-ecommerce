-- Create the database
CREATE DATABASE ecommerce;
USE ecommerce;

-- Create the categories table
CREATE TABLE categories (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    UNIQUE KEY (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create the products table with category_name
CREATE TABLE products (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT(11) NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    for_sale TINYINT(1) NOT NULL DEFAULT 1,
    image VARCHAR(255) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Create cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;


-- Insert categories
INSERT INTO categories (name, description) VALUES 
('Laptops', 'Portable computers for various uses.'),
('Mobile Phones', 'Smartphones and cell phones.'),
('Tablets', 'Touchscreen devices larger than phones but smaller than laptops.'),
('Smartwatches', 'Wearable devices with smart features.'),
('Computers', 'Desktop computers and workstations.'),
('Accessories', 'Various accessories for electronic devices.');


-- Insert products for Laptops
INSERT INTO products (name, category_id, category_name, description, price, image) VALUES 
('Dell XPS 13', 1, 'Laptops', 'A high-performance laptop with a sleek design.', 999.99, 'dell_xps_13.jpg'),
('MacBook Pro', 1, 'Laptops', 'A premium laptop with advanced features.', 1299.99, 'macbook_pro.jpg'),
('HP Spectre x360', 1, 'Laptops', 'A convertible laptop with a stunning display.', 1199.99, 'hp_spectre_x360.jpg'),
('Lenovo ThinkPad X1 Carbon', 1, 'Laptops', 'A business laptop with durable build and high performance.', 1099.99, 'lenovo_thinkpad_x1_carbon.jpg'),
('Acer Swift 3', 1, 'Laptops', 'An affordable laptop with decent specs.', 749.99, 'acer_swift_3.jpg'),
('Asus ZenBook 14', 1, 'Laptops', 'A lightweight and powerful ultrabook.', 899.99, 'asus_zenbook_14.jpg');

-- Insert products for Mobile Phones
INSERT INTO products (name, category_id, category_name, description, price, image) VALUES 
('iPhone 14', 2, 'Mobile Phones', 'Latest model of Apple iPhone with cutting-edge features.', 799.99, 'iphone_14.jpg'),
('Samsung Galaxy S23', 2, 'Mobile Phones', 'High-end smartphone with excellent camera and performance.', 899.99, 'samsung_galaxy_s23.jpg'),
('Google Pixel 7', 2, 'Mobile Phones', 'Smartphone with a focus on photography and AI features.', 699.99, 'google_pixel_7.jpg'),
('OnePlus 11', 2, 'Mobile Phones', 'Performance-oriented phone with fast charging.', 749.99, 'oneplus_11.jpg'),
('Sony Xperia 10 IV', 2, 'Mobile Phones', 'Mid-range smartphone with a sleek design.', 549.99, 'sony_xperia_10_iv.jpg'),
('Xiaomi Mi 13', 2, 'Mobile Phones', 'A high-value phone with great specs for the price.', 699.99, 'xiaomi_mi_13.jpg');

-- Insert products for Tablets
INSERT INTO products (name, category_id, category_name, description, price, image) VALUES 
('iPad Air', 3, 'Tablets', 'A versatile tablet with great performance and battery life.', 599.99, 'ipad_air.jpg'),
('Samsung Galaxy Tab S8', 3, 'Tablets', 'An Android tablet with high-resolution display.', 699.99, 'galaxy_tab_s8.jpg'),
('Microsoft Surface Pro 9', 3, 'Tablets', 'A powerful 2-in-1 tablet with detachable keyboard.', 999.99, 'surface_pro_9.jpg'),
('Lenovo Tab P12 Pro', 3, 'Tablets', 'A high-end Android tablet with a large screen.', 799.99, 'lenovo_tab_p12_pro.jpg'),
('Huawei MatePad Pro', 3, 'Tablets', 'A premium tablet with stylus support and powerful specs.', 849.99, 'huawei_matepad_pro.jpg'),
('Amazon Fire HD 10', 3, 'Tablets', 'An affordable tablet for media consumption and productivity.', 149.99, 'amazon_fire_hd_10.jpg');

-- Insert products for Smartwatches
INSERT INTO products (name, category_id, category_name, description, price, image) VALUES 
('Apple Watch Series 8', 4, 'Smartwatches', 'A smartwatch with health monitoring and connectivity features.', 399.99, 'apple_watch_series_8.jpg'),
('Samsung Galaxy Watch 5', 4, 'Smartwatches', 'Smartwatch with advanced fitness tracking capabilities.', 349.99, 'galaxy_watch_5.jpg'),
('Garmin Venu 3', 4, 'Smartwatches', 'Fitness-focused smartwatch with detailed health metrics.', 399.99, 'garmin_venu_3.jpg'),
('Fitbit Sense 2', 4, 'Smartwatches', 'Health-centric smartwatch with ECG and stress management.', 299.99, 'fitbit_sense_2.jpg'),
('Fossil Gen 6', 4, 'Smartwatches', 'Stylish smartwatch with Wear OS and fitness tracking.', 279.99, 'fossil_gen_6.jpg'),
('Amazfit GTR 4', 4, 'Smartwatches', 'Affordable smartwatch with long battery life and health features.', 229.99, 'amazfit_gtr_4.jpg');

-- Insert products for Computers
INSERT INTO products (name, category_id, category_name, description, price, image) VALUES 
('HP Pavilion Desktop', 5, 'Computers', 'A powerful desktop computer for everyday use.', 699.99, 'hp_pavilion_desktop.jpg'),
('iMac 24"', 5, 'Computers', 'All-in-one computer with stunning Retina display.', 1499.99, 'imac_24.jpg'),
('Dell Inspiron 3880', 5, 'Computers', 'A budget-friendly desktop for basic computing needs.', 549.99, 'dell_inspiron_3880.jpg'),
('ASUS ROG Strix G15', 5, 'Computers', 'Gaming desktop with high-performance components.', 1299.99, 'asus_rog_strix_g15.jpg'),
('Acer Aspire TC', 5, 'Computers', 'A versatile desktop for home and office use.', 629.99, 'acer_aspire_tc.jpg'),
('Lenovo Legion Tower 5', 5, 'Computers', 'Gaming tower with powerful hardware and sleek design.', 1399.99, 'lenovo_legion_tower_5.jpg');

-- Insert products for Accessories
INSERT INTO products (name, category_id, category_name, description, price, image) VALUES 
('Wireless Mouse', 6, 'Accessories', 'Ergonomic wireless mouse with long battery life.', 29.99, 'wireless_mouse.jpg'),
('Bluetooth Headphones', 6, 'Accessories', 'Noise-cancelling headphones with Bluetooth connectivity.', 79.99, 'bluetooth_headphones.jpg'),
('USB-C Hub', 6, 'Accessories', 'Multi-port USB-C hub for extended connectivity.', 39.99, 'usb_c_hub.jpg'),
('External SSD 1TB', 6, 'Accessories', 'Portable 1TB external SSD for fast data storage.', 129.99, 'external_ssd_1tb.jpg'),
('Laptop Stand', 6, 'Accessories', 'Adjustable stand for comfortable laptop use.', 49.99, 'laptop_stand.jpg'),
('Webcam HD 1080p', 6, 'Accessories', 'High-definition webcam for video calls and streaming.', 59.99, 'webcam_hd_1080p.jpg');
