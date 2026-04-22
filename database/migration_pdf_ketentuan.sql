CREATE TABLE IF NOT EXISTS `pdf_ketentuan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `package_type` ENUM('fullservice', 'graduation', 'alacarte') NOT NULL,
  `text_content` TEXT NOT NULL,
  `display_order` INT DEFAULT 0,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_pkg` (`package_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default values if the table is empty
INSERT INTO `pdf_ketentuan` (`package_type`, `text_content`, `display_order`)
SELECT 'fullservice', 'Harga berlaku untuk minimal {siswa} pemesan Buku Tahunan.', 1
WHERE NOT EXISTS (SELECT 1 FROM `pdf_ketentuan` WHERE `package_type` = 'fullservice');

INSERT INTO `pdf_ketentuan` (`package_type`, `text_content`, `display_order`)
SELECT 'fullservice', 'Harga bersifat penawaran dan dapat berubah sesuai kesepakatan.', 2
WHERE NOT EXISTS (SELECT 1 FROM `pdf_ketentuan` WHERE `package_type` = 'fullservice' AND `display_order` = 2);

INSERT INTO `pdf_ketentuan` (`package_type`, `text_content`, `display_order`)
SELECT 'graduation', 'Harga berlaku untuk event yang telah disepakati.', 1
WHERE NOT EXISTS (SELECT 1 FROM `pdf_ketentuan` WHERE `package_type` = 'graduation');

INSERT INTO `pdf_ketentuan` (`package_type`, `text_content`, `display_order`)
SELECT 'graduation', 'Harga bersifat penawaran dan dapat berubah sesuai kesepakatan.', 2
WHERE NOT EXISTS (SELECT 1 FROM `pdf_ketentuan` WHERE `package_type` = 'graduation' AND `display_order` = 2);

INSERT INTO `pdf_ketentuan` (`package_type`, `text_content`, `display_order`)
SELECT 'alacarte', 'Harga berlaku sesuai spesifikasi yang tercantum.', 1
WHERE NOT EXISTS (SELECT 1 FROM `pdf_ketentuan` WHERE `package_type` = 'alacarte');

INSERT INTO `pdf_ketentuan` (`package_type`, `text_content`, `display_order`)
SELECT 'alacarte', 'Harga bersifat penawaran dan dapat berubah sesuai kesepakatan.', 2
WHERE NOT EXISTS (SELECT 1 FROM `pdf_ketentuan` WHERE `package_type` = 'alacarte' AND `display_order` = 2);
