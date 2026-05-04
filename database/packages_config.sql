CREATE TABLE IF NOT EXISTS `packages_config` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `package_code`  VARCHAR(50) NOT NULL UNIQUE COMMENT 'Kode unik (cth: fs-handy, ac-fotohalf)',
  `label`         VARCHAR(100) NOT NULL COMMENT 'Nama Tampilan',
  `package_type`  ENUM('fs', 'alc_factor', 'alc_flat') NOT NULL DEFAULT 'alc_flat' COMMENT 'Jenis kalkulasi paket',
  `calc_key`      VARCHAR(50) DEFAULT NULL COMMENT 'Key untuk lookup di array FS atau ALC_F (handy, minimal, ebook, dll)',
  `by_siswa`      TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Apakah dihitung per jumlah siswa',
  `price_min`     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Harga min (hanya untuk alc_flat)',
  `price_max`     INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Harga maks (hanya untuk alc_flat)',
  `min_per_buku`  INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Min harga per buku (hanya untuk desain/cetakonly)',
  `description`   VARCHAR(255) DEFAULT NULL COMMENT 'Keterangan',
  `display_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `active`        TINYINT(1) NOT NULL DEFAULT 1,
  `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_active (`active`),
  INDEX idx_order  (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE `packages_config`;

INSERT INTO `packages_config`
  (`package_code`, `label`, `package_type`, `calc_key`, `by_siswa`, `price_min`, `price_max`, `min_per_buku`, `display_order`, `active`)
VALUES
  ('fs-handy',    'Full Service — Handy Book A4+', 'fs',         'handy',     1, 0, 0, 0,     1, 1),
  ('fs-minimal',  'Full Service — Minimal Book SQ', 'fs',        'minimal',   1, 0, 0, 0,     2, 1),
  ('fs-large',    'Full Service — Large Book B4',  'fs',         'large',     1, 0, 0, 0,     3, 1),
  ('ac-ebook',    'À La Carte — E-Book Package',   'alc_factor', 'ebook',     1, 0, 0, 0,     4, 1),
  ('ac-editcetak','À La Carte — Edit+Desain+Cetak','alc_factor', 'editcetak', 1, 0, 0, 0,     5, 1),
  ('ac-desain',   'À La Carte — Desain Only',      'alc_factor', 'desain',    1, 0, 0, 50000, 6, 1),
  ('ac-cetakonly','À La Carte — Cetak Only',       'alc_factor', 'cetakonly', 1, 0, 0, 30000, 7, 1),
  ('ac-fotohalf', 'À La Carte — Foto Only (½ Hari)','alc_flat',  NULL,        0, 3500000, 5000000, 0, 8, 1),
  ('ac-fotofull', 'À La Carte — Foto Only (Full Day)','alc_flat', NULL,       0, 6000000, 9000000, 0, 9, 1),
  ('ac-videod',   'À La Carte — Drone Video',      'alc_flat',   NULL,        0, 1500000, 1500000, 0, 10, 1),
  ('ac-videodoc', 'À La Carte — Docudrama Video',  'alc_flat',   NULL,        0, 3000000, 3000000, 0, 11, 1);
