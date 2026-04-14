-- ============================================================
-- Parama Studio - MySQL Database Schema
-- Master Data untuk HPP Calculator
-- ============================================================

-- DROP DATABASE IF EXISTS parama_hpp;
-- CREATE DATABASE parama_hpp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE parama_hpp;

-- ============================================================
-- 1. OVERHEAD & GAJI TIM
-- ============================================================
CREATE TABLE IF NOT EXISTS overhead (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    amount BIGINT NOT NULL DEFAULT 0,
    description VARCHAR(255),
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active)
);

-- ============================================================
-- 2. OVERHEAD TOTAL (Cached)
-- ============================================================
CREATE TABLE IF NOT EXISTS overhead_total (
    id INT PRIMARY KEY AUTO_INCREMENT,
    total_amount BIGINT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================================
-- 3. PRICING FACTORS - Cetak, À La Carte
-- ============================================================
CREATE TABLE IF NOT EXISTS pricing_factors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category VARCHAR(50) NOT NULL,
    factor_name VARCHAR(50) NOT NULL,
    factor_value DECIMAL(10, 4) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_factor (category, factor_name),
    INDEX idx_category (category)
);

-- ============================================================
-- 4. FULL SERVICE PRICING - Range siswa & harga
-- ============================================================
CREATE TABLE IF NOT EXISTS fullservice_pricing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    package_type VARCHAR(50) NOT NULL,
    min_students INT NOT NULL,
    max_students INT NOT NULL,
    price_per_student BIGINT NOT NULL,
    pages INT DEFAULT 60,
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_package (package_type),
    INDEX idx_students (min_students, max_students),
    INDEX idx_active (active)
);

-- ============================================================
-- 5. CETAK BASE PRICING
-- ============================================================
CREATE TABLE IF NOT EXISTS cetak_base (
    id INT PRIMARY KEY AUTO_INCREMENT,
    min_students INT NOT NULL,
    max_students INT NOT NULL,
    price BIGINT NOT NULL,
    description VARCHAR(100),
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_range (min_students, max_students),
    INDEX idx_students (min_students, max_students),
    INDEX idx_active (active)
);

-- ============================================================
-- 6. ADD-ONS
-- ============================================================
CREATE TABLE IF NOT EXISTS addons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    price BIGINT NOT NULL,
    unit VARCHAR(50),
    category VARCHAR(50),
    description TEXT,
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active),
    INDEX idx_category (category)
);

-- ============================================================
-- 7. GRADUATION PACKAGES
-- ============================================================
CREATE TABLE IF NOT EXISTS graduation_packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    price BIGINT NOT NULL,
    includes_book VARCHAR(100),
    includes_tshirt VARCHAR(100),
    description TEXT,
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active)
);

-- ============================================================
-- 8. GRADUATION ADD-ONS
-- ============================================================
CREATE TABLE IF NOT EXISTS graduation_addons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    price BIGINT NOT NULL,
    item_type VARCHAR(50),
    description TEXT,
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active),
    INDEX idx_type (item_type)
);

-- ============================================================
-- 9. GRADUATION CETAK
-- ============================================================
CREATE TABLE IF NOT EXISTS graduation_cetak (
    id INT PRIMARY KEY AUTO_INCREMENT,
    min_qty INT NOT NULL,
    max_qty INT NOT NULL,
    price_per_unit BIGINT NOT NULL,
    description VARCHAR(100),
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_range (min_qty, max_qty),
    INDEX idx_active (active)
);

-- ============================================================
-- 10. PAYMENT TERMS
-- ============================================================
CREATE TABLE IF NOT EXISTS payment_terms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    term_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active)
);

-- ============================================================
-- Existing Tables (untuk reference & compatibility)
-- ============================================================

-- Users (already exists)
-- CREATE TABLE IF NOT EXISTS users (
--     id INT PRIMARY KEY AUTO_INCREMENT,
--     username VARCHAR(100) NOT NULL UNIQUE,
--     password VARCHAR(255) NOT NULL,
--     email VARCHAR(100) NOT NULL UNIQUE,
--     role ENUM('admin', 'manager', 'staff') DEFAULT 'staff',
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
-- );

-- ============================================================
-- SEED DATA (Starting Values - from settings.json)
-- ============================================================

-- Overhead
INSERT IGNORE INTO overhead (name, amount, description) VALUES
('designer', 20000000, 'Desainer grafis & layout'),
('marketing', 15000000, 'Tim marketing & branding'),
('creative', 8000000, 'Produksi kreatif & video'),
('pm', 8000000, 'Project manager'),
('sosmed', 7000000, 'Social media & content'),
('freelance', 4000000, 'Freelancer & contractor'),
('operasional', 12586000, 'Operasional & misc');

-- Overhead Total
DELETE FROM overhead_total;
INSERT INTO overhead_total (total_amount) VALUES (74586000);

-- Pricing Factors - Cetak
INSERT IGNORE INTO pricing_factors (category, factor_name, factor_value, description) VALUES
('cetak', 'handy', 1.0000, 'Full Service Handy'),
('cetak', 'minimal', 0.9500, 'Minimal Package'),
('cetak', 'large', 1.1500, 'Large Package');

-- Pricing Factors - À La Carte
INSERT IGNORE INTO pricing_factors (category, factor_name, factor_value, description) VALUES
('alacarte', 'ebook', 0.7200, 'E-Book dari Full Service'),
('alacarte', 'editcetak', 0.6200, 'Edit & Cetak dari Full Service'),
('alacarte', 'desain', 0.2200, 'Desain saja dari Full Service'),
('alacarte', 'cetakonly', 0.3000, 'Cetak saja dari Full Service');

-- Cetak Base Pricing (dari settings.json)
INSERT IGNORE INTO cetak_base (min_students, max_students, price, description) VALUES
(30, 50, 150000, '30-50 siswa'),
(51, 75, 135000, '51-75 siswa'),
(76, 100, 120000, '76-100 siswa'),
(101, 150, 115000, '101-150 siswa'),
(151, 200, 110000, '151-200 siswa'),
(201, 300, 105000, '201-300 siswa'),
(301, 500, 100000, '301-500 siswa');

-- Add-ons (contoh)
INSERT IGNORE INTO addons (name, price, unit, category, description) VALUES
('Hardcover Book', 50000, 'book', 'packaging', 'Hardcover binding untuk buku'),
('Premium Paper', 15000, 'per', 'paper', 'Premium glossy paper'),
('Dust Jacket', 25000, 'piece', 'print', 'Dust jacket protection');

-- Payment Terms
INSERT IGNORE INTO payment_terms (term_name, description) VALUES
('Cash', 'Pembayaran tunai di tempat'),
('Transfer 30%', '30% transfer, 70% sebelum delivery'),
('Transfer 50%', '50% transfer, 50% sebelum delivery'),
('Transfer Penuh', '100% transfer');

-- ============================================================
-- Helper Views (Optional - untuk convenience)
-- ============================================================

CREATE OR REPLACE VIEW v_overhead_summary AS
SELECT 
    'Designer' as category,
    SUM(amount) as total
FROM overhead
WHERE name IN ('designer');

-- ============================================================
-- End of Schema
-- ============================================================
