-- ====================================
-- Parama HPP Database Schema v2.0
-- Created: 2026-03-06
-- ====================================

-- Table: roles
-- Sistem roles untuk authorization
CREATE TABLE IF NOT EXISTS `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `label` VARCHAR(100) NOT NULL,
  `permissions` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: users
-- Tabel user untuk login
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `email` VARCHAR(255),
  `role_id` INT NOT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `last_login` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  INDEX `idx_username` (`username`),
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: settings
-- Pengaturan aplikasi
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` LONGTEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: overhead
-- Menyimpan biaya overhead bulanan per kategori
CREATE TABLE IF NOT EXISTS `overhead` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category` VARCHAR(100) NOT NULL UNIQUE,
  `amount` INT NOT NULL DEFAULT 0,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: packages_fullservice
-- Full Service pakets dengan range siswa dan harga
CREATE TABLE IF NOT EXISTS `packages_fullservice` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `package_type` ENUM('handy', 'minimal', 'large') NOT NULL,
  `min_students` INT NOT NULL,
  `max_students` INT NOT NULL,
  `price_per_book` INT NOT NULL,
  `max_pages` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `type_range` (`package_type`, `min_students`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: packages_alacarte
-- À La Carte pakets (E-Book, Foto Only, Video, etc)
CREATE TABLE IF NOT EXISTS `packages_alacarte` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `price_type` ENUM('per_siswa', 'flat_range', 'flat_fixed') NOT NULL,
  `price_min` INT,
  `price_max` INT,
  `factor` DECIMAL(5, 3),
  `margin_target` VARCHAR(20),
  `includes` JSON,
  `excludes` JSON,
  `display_order` INT DEFAULT 0,
  `is_featured` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: addon_categories
-- Kategori add-on (Finishing, Kertas, Packaging, dll)
CREATE TABLE IF NOT EXISTS `addon_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_name` VARCHAR(100) NOT NULL UNIQUE,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: addon_items
-- Item add-on dengan tiers harga
CREATE TABLE IF NOT EXISTS `addon_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `addon_type` ENUM('flat', 'tiered', 'flat_video', 'per_hal', 'extra_hal') NOT NULL DEFAULT 'flat',
  `description` TEXT,
  `flat_price` INT,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `addon_categories`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `name_category` (`name`, `category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: addon_tiers
-- Tier harga untuk add-on berdasarkan range siswa
CREATE TABLE IF NOT EXISTS `addon_tiers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `addon_item_id` INT NOT NULL,
  `tier_label` VARCHAR(50),
  `min_quantity` INT NOT NULL,
  `max_quantity` INT,
  `price` INT NOT NULL,
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`addon_item_id`) REFERENCES `addon_items`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `tier_range` (`addon_item_id`, `min_quantity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cetak_base
-- Base printing costs per range siswa dan halaman
CREATE TABLE IF NOT EXISTS `cetak_base` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `range_label` VARCHAR(100) NOT NULL,
  `min_students` INT NOT NULL,
  `max_students` INT NOT NULL,
  `pages_count` INT NOT NULL,
  `base_price` INT NOT NULL,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `range_pages` (`min_students`, `pages_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cetak_factors
-- Faktor harga cetak per paket (handy, minimal, large)
CREATE TABLE IF NOT EXISTS `cetak_factors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `package_type` ENUM('handy', 'minimal', 'large') NOT NULL UNIQUE,
  `factor` DECIMAL(5, 3) NOT NULL,
  `description` VARCHAR(200),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: packages_graduation
-- Graduation packages (daftar paket wisuda)
CREATE TABLE IF NOT EXISTS `packages_graduation` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `package_key` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `price` INT NOT NULL,
  `color_scheme` VARCHAR(50),
  `is_featured` BOOLEAN DEFAULT FALSE,
  `transport_included` VARCHAR(100),
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: graduation_addons
-- Add-on khusus graduation (foto tambahan, cetak foto, dll)
CREATE TABLE IF NOT EXISTS `graduation_addons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `addon_key` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(150) NOT NULL,
  `price` INT NOT NULL,
  `addon_type` ENUM('addon', 'cetak', 'service') NOT NULL DEFAULT 'addon',
  `unit` VARCHAR(50),
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: alacarte_factors
-- Faktor harga untuk à la carte packages terhadap full service handy
CREATE TABLE IF NOT EXISTS `alacarte_factors` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `package_code` VARCHAR(50) NOT NULL UNIQUE,
  `factor` DECIMAL(5, 3) NOT NULL,
  `min_per_book` INT DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: penawaran (Proposals/Offers)
-- Daftar penawaran (proposals) yang dibuat dari kalkulator
CREATE TABLE IF NOT EXISTS `penawaran` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `client_name` VARCHAR(200) NOT NULL,
  `package` VARCHAR(100),
  `student_count` INT,
  `total_price` INT NOT NULL,
  `discount_type` VARCHAR(20),
  `discount_value` INT,
  `bonus_text` VARCHAR(255),
  `bonus_nominal` INT,
  `final_price` INT NOT NULL,
  `notes` TEXT,
  `status` ENUM('pending', 'nego', 'deal', 'gagal') DEFAULT 'pending',
  `created_by` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `month` VARCHAR(7),
  INDEX `status` (`status`),
  INDEX `month` (`month`),
  INDEX `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================
-- Index untuk performa query
-- ====================================
CREATE INDEX idx_fs_type ON packages_fullservice(package_type);
CREATE INDEX idx_addon_cat ON addon_items(category_id);
CREATE INDEX idx_addon_tier ON addon_tiers(addon_item_id);
CREATE INDEX idx_grad_pkg ON packages_graduation(package_key);
CREATE INDEX idx_cetak_range ON cetak_base(min_students, pages_count);
