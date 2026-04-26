-- Prime Home Hub Database Schema
-- Created: January 25, 2026
-- Optimized for Dependency Order

CREATE DATABASE IF NOT EXISTS if0_41592335_furm;
USE if0_41592335_furm;

-- ============================================
-- 1. INDEPENDENT TABLES (Parent Tables)
-- These have no foreign keys and must be created first.
-- ============================================

CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(191) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100),
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    role ENUM('customer', 'admin') DEFAULT 'customer',
    is_active BOOLEAN DEFAULT TRUE,
    avatar_url VARCHAR(500),
    session_token VARCHAR(64),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS backup_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    backup_file VARCHAR(255),
    backup_size BIGINT,
    status ENUM('success', 'failed') DEFAULT 'success',
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- ============================================
-- 2. FIRST-LEVEL DEPENDENT TABLES
-- Tables that depend on Categories or Users.
-- ============================================

CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    rating DECIMAL(2, 1) DEFAULT 4.0,
    image_url VARCHAR(500),
    description TEXT,
    stock INT DEFAULT 0,
    status ENUM('active', 'inactive', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_price (price),
    FULLTEXT INDEX ft_search (name, description)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method ENUM('credit_card', 'bank_transfer', 'cash_on_delivery') DEFAULT 'cash_on_delivery',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    shipping_address TEXT,
    shipping_cost DECIMAL(10, 2) DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, 
    type VARCHAR(50) NOT NULL, 
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS room_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    width INT,
    height INT,
    furniture_data LONGTEXT,
    preview_image LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_room_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mood_boards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    items_data LONGTEXT,
    preview_image LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mood_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- ============================================
-- 3. SECOND-LEVEL DEPENDENT TABLES
-- Tables that depend on Products, Orders, or multiple Parents.
-- ============================================

CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shipping_info (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_shipping_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating DECIMAL(2, 1) NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS analytics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_type VARCHAR(100),
    user_id INT,
    product_id INT,
    event_data JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event (event_type),
    INDEX idx_user (user_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- ============================================
-- 4. PERFORMANCE TUNING (Additional Indexes)
-- ============================================

CREATE INDEX idx_orders_user_date ON orders(user_id, created_at);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_products_category_price ON products(category_id, price);

-- ============================================
-- 5. VIEWS
-- Must be created after tables exist.
-- ============================================

CREATE OR REPLACE VIEW order_summary AS
SELECT 
    o.id,
    o.order_number,
    u.first_name,
    u.last_name,
    u.email,
    o.total_amount,
    o.status,
    COUNT(oi.id) as item_count,
    o.created_at
FROM orders o
JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id, o.order_number, u.first_name, u.last_name, u.email, o.total_amount, o.status, o.created_at;

CREATE OR REPLACE VIEW sales_by_category AS
SELECT 
    c.name,
    COUNT(oi.id) as items_sold,
    SUM(oi.subtotal) as total_revenue
FROM order_items oi
JOIN products p ON oi.product_id = p.id
JOIN categories c ON p.category_id = c.id
GROUP BY c.id, c.name;

CREATE OR REPLACE VIEW top_products AS
SELECT 
    p.id,
    p.name,
    COUNT(oi.id) as times_ordered,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.subtotal) as total_revenue,
    p.rating
FROM products p
LEFT JOIN order_items oi ON p.id = oi.product_id
GROUP BY p.id, p.name, p.rating
ORDER BY times_ordered DESC;

-- ============================================
-- 6. SEED DATA
-- Inserted last to ensure all constraints are met.
-- ============================================

INSERT INTO categories (name, slug, description) VALUES
('Living Room', 'living-room', 'Couches, coffee tables, and decor for the heart of the home.'),
('Bedroom', 'bedroom', 'Beds, dressers, and essentials for a restful night.'),
('Office', 'office', 'Desks, ergonomic chairs, and productivity tools.'),
('Decor', 'decor', 'Vases, art, mirrors, and final touches.'),
('Dining Room', 'dining-room', 'Tables, chairs, and sideboards for shared meals.'),
('Outdoor', 'outdoor', 'Patio furniture, lighting, and exterior accents.'),
('Storage', 'storage', 'Bookshelves, cabinets, and organizing solutions.'),
('Kitchen', 'kitchen', 'Dining accessories and pantry storage.'),
('Lighting', 'lighting', 'Floor lamps, pendants, and ambient light.');

INSERT INTO products (name, category_id, price, rating, image_url, description, stock) VALUES
('Modern Sofa', 1, 899.00, 4.5, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop', 'Elegant modern sofa perfect for contemporary living rooms', 15),
('Coffee Table', 1, 299.00, 4.2, 'https://images.unsplash.com/photo-1555939594-58d7cb561404?w=400&h=300&fit=crop', 'Stylish wood coffee table with storage', 20),
('Wall Art', 1, 199.00, 4.7, 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=400&h=300&fit=crop', 'Abstract wall art for modern spaces', 30),
('King Bed Frame', 2, 1299.00, 4.6, 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=400&h=300&fit=crop', 'Premium king-size bed with storage', 10),
('Nightstand', 2, 299.00, 4.3, 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=400&h=300&fit=crop', 'Modern nightstand with drawer', 25),
('Bedside Lamp', 2, 89.00, 4.4, 'https://images.unsplash.com/photo-1565636192335-14c6b42ce068?w=400&h=300&fit=crop', 'Adjustable bedside lamp with USB charging', 40),
('Office Desk', 3, 599.00, 4.5, 'https://images.unsplash.com/photo-1593062096033-9a26b09da705?w=400&h=300&fit=crop', 'Ergonomic office desk with monitor stand', 12),
('Office Chair', 3, 399.00, 4.6, 'https://images.unsplash.com/photo-1592078615290-033ee584e267?w=400&h=300&fit=crop', 'Comfortable ergonomic office chair', 18),
('Desk Organizer', 3, 49.00, 4.2, 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=300&fit=crop', 'Wooden desk organizer set', 50),
('Plant Pot', 4, 59.00, 4.3, 'https://images.unsplash.com/photo-1610055945828-949d8edd5d55?w=400&h=300&fit=crop', 'Modern ceramic plant pot', 35),
('Mirror', 4, 149.00, 4.5, 'https://images.unsplash.com/photo-1578926314433-b961e6a4d8e7?w=400&h=300&fit=crop', 'Decorative wall mirror', 28),
('Throw Pillow', 4, 39.00, 4.4, 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&h=300&fit=crop', 'Comfortable throw pillow set', 45);