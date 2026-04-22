-- ============================================================
-- MIGRATION: Tabel bonus_fasilitas
-- Menggantikan data hardcoded di pdf.php dan app-pages.js
-- ============================================================

CREATE TABLE IF NOT EXISTS `bonus_fasilitas` (
  `id`           INT(11) NOT NULL AUTO_INCREMENT,
  `package_type` ENUM('fullservice','graduation','alacarte') NOT NULL COMMENT 'Tipe paket induk',
  `label`        VARCHAR(100) NOT NULL                        COMMENT 'Judul bonus, mis: Studio Foto',
  `detail`       TEXT NOT NULL                                COMMENT 'Deskripsi lengkap bonus',
  `display_order` INT(11) NOT NULL DEFAULT 0                  COMMENT 'Urutan tampil',
  `active`       TINYINT(1) NOT NULL DEFAULT 1                COMMENT '1=aktif, 0=nonaktif',
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pkg_active` (`package_type`, `active`, `display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Master data Bonus & Fasilitas per tipe paket';

-- ============================================================
-- SEED DATA — Sesuai dengan yang sebelumnya hardcoded
-- ============================================================

-- Kosongkan dulu kalau ada (idempotent run)
TRUNCATE TABLE `bonus_fasilitas`;

-- Full Service (handy / minimal / large — semua dapat bonus yang sama)
INSERT INTO `bonus_fasilitas` (`package_type`, `label`, `detail`, `display_order`) VALUES
('fullservice', 'Studio Foto',  'Free portable studio delivery, Fashion Stylist, Properti sesuai tema', 1),
('fullservice', 'Buku Gratis',  '4 pcs Buku Tahunan',                                                  2),
('fullservice', 'Fotografi',    'Free Photoshoot Graduation (2 Fotografer)',                            3),
('fullservice', 'Pengiriman',   'Gratis biaya pengiriman area Jabodetabek',                             4);

-- Graduation
INSERT INTO `bonus_fasilitas` (`package_type`, `label`, `detail`, `display_order`) VALUES
('graduation',  'Transportasi', 'Gratis biaya transportasi area Jabodetabek',           1),
('graduation',  'G-Drive',      'File foto/video dikirim via Google Drive',              2),
('graduation',  'Coverage',     'Maksimal 4 jam liputan',                                3);

-- À La Carte
INSERT INTO `bonus_fasilitas` (`package_type`, `label`, `detail`, `display_order`) VALUES
('alacarte',    'Konsultasi',   'Konsultasi desain & konten gratis',                     1),
('alacarte',    'Revisi',       'Revisi desain hingga 2 kali',                           2);
