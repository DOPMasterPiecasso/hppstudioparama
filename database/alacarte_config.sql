-- ============================================================
-- Tabel: alacarte_config
-- Menyimpan konfigurasi paket À La Carte yang sebelumnya hardcode
-- di ALC_CFG dalam app.js (khusus untuk paket dengan harga flat).
-- ============================================================

CREATE TABLE IF NOT EXISTS `alacarte_config` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `package_code`  VARCHAR(30)  NOT NULL UNIQUE COMMENT 'Kode unik paket, cth: ac-fotohalf',
  `label`         VARCHAR(100) NOT NULL COMMENT 'Nama tampilan paket',
  `price_min`     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Harga minimum (Rp)',
  `price_max`     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Harga maksimum (Rp). Sama dgn price_min jika harga tunggal.',
  `description`   VARCHAR(255) DEFAULT NULL COMMENT 'Keterangan singkat untuk tampilan kalkulator',
  `display_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `active`        TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active (`active`),
  INDEX idx_order  (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Konfigurasi harga flat À La Carte (foto, video, dll)';

-- ============================================================
-- Seed Data — sesuai dengan hardcode di ALC_CFG (app.js)
-- Hanya paket yang menggunakan harga flat (bukan faktor % dari FS)
-- ============================================================

INSERT INTO `alacarte_config`
  (`package_code`, `label`, `price_min`, `price_max`, `description`, `display_order`, `active`)
VALUES
  ('ac-fotohalf', 'Foto Only (½ Hari)',  3500000, 5000000, 'Sesi foto max ~75 siswa. Fotografer + fashion stylist.',      1, 1),
  ('ac-fotofull', 'Foto Only (Full Day)', 6000000, 9000000, 'Sesi foto 76–150+ siswa. Full team seharian.',               2, 1),
  ('ac-videod',   'Drone Video',          1500000, 1500000, 'Video drone 1–2 menit.',                                      3, 1),
  ('ac-videodoc', 'Docudrama Video',      3000000, 3000000, 'Video cerita angkatan 5–10 menit.',                           4, 1)
ON DUPLICATE KEY UPDATE
  `label`         = VALUES(`label`),
  `price_min`     = VALUES(`price_min`),
  `price_max`     = VALUES(`price_max`),
  `description`   = VALUES(`description`),
  `display_order` = VALUES(`display_order`);
