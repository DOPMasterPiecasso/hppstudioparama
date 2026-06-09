-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 09, 2026 at 05:57 AM
-- Server version: 8.0.45-0ubuntu0.22.04.1
-- PHP Version: 8.1.34
SET FOREIGN_KEY_CHECKS = 0;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Database: `hppprogram`
--
CREATE DATABASE IF NOT EXISTS `hppprogram` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE `hppprogram`;

-- --------------------------------------------------------

--
-- Table structure for table `addon_categories`
--

DROP TABLE IF EXISTS `addon_categories`;

CREATE TABLE `addon_categories` (
    `id` int NOT NULL,
    `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `display_order` int DEFAULT '0',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `addon_categories`
--

INSERT INTO
    `addon_categories` (
        `id`,
        `category_name`,
        `display_order`,
        `created_at`
    )
VALUES (
        1,
        'Finishing',
        0,
        '2026-04-06 08:54:43'
    ),
    (
        2,
        'Kertas',
        0,
        '2026-04-06 08:54:43'
    ),
    (
        3,
        'Halaman Tambahan',
        0,
        '2026-04-06 08:54:43'
    ),
    (
        4,
        'Video',
        0,
        '2026-04-06 08:54:43'
    ),
    (
        5,
        'Packaging Standard',
        0,
        '2026-04-06 08:54:43'
    ),
    (
        6,
        'Custom Box',
        0,
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `addon_items`
--

DROP TABLE IF EXISTS `addon_items`;

CREATE TABLE `addon_items` (
    `id` int NOT NULL,
    `category_id` int NOT NULL,
    `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `addon_type` enum(
        'flat',
        'tiered',
        'flat_video',
        'per_hal',
        'extra_hal'
    ) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'flat',
    `description` text COLLATE utf8mb4_unicode_ci,
    `flat_price` int DEFAULT NULL,
    `display_order` int DEFAULT '0',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `addon_items`
--

INSERT INTO
    `addon_items` (
        `id`,
        `category_id`,
        `name`,
        `addon_type`,
        `description`,
        `flat_price`,
        `display_order`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        1,
        'Hardcover',
        'tiered',
        NULL,
        NULL,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        2,
        1,
        'Softcover',
        'tiered',
        NULL,
        NULL,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        3,
        2,
        'Art Paper 260gsm',
        'tiered',
        NULL,
        NULL,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        4,
        2,
        'Glossy 230gsm',
        'tiered',
        NULL,
        NULL,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        5,
        3,
        'Halaman Tambahan',
        'extra_hal',
        NULL,
        NULL,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        6,
        4,
        'Drone Video',
        'flat_video',
        NULL,
        1500000,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        7,
        4,
        'Docudrama Video',
        'flat_video',
        NULL,
        3000000,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        8,
        5,
        'Slide Box',
        'tiered',
        NULL,
        NULL,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        9,
        6,
        'Custom Printed Box',
        'tiered',
        NULL,
        NULL,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `addon_tiers`
--

DROP TABLE IF EXISTS `addon_tiers`;

CREATE TABLE `addon_tiers` (
    `id` int NOT NULL,
    `addon_item_id` int NOT NULL,
    `tier_label` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `min_quantity` int NOT NULL,
    `max_quantity` int DEFAULT NULL,
    `price` int NOT NULL,
    `display_order` int DEFAULT '0',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `addon_tiers`
--

INSERT INTO
    `addon_tiers` (
        `id`,
        `addon_item_id`,
        `tier_label`,
        `min_quantity`,
        `max_quantity`,
        `price`,
        `display_order`,
        `created_at`
    )
VALUES (
        1,
        1,
        '25–75 buku',
        25,
        75,
        75000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        2,
        1,
        '76–150 buku',
        76,
        150,
        65000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        3,
        1,
        '>151 buku',
        151,
        NULL,
        55000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        4,
        2,
        '25–75 buku',
        25,
        75,
        45000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        5,
        2,
        '76–150 buku',
        76,
        150,
        35000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        6,
        2,
        '>151 buku',
        151,
        NULL,
        30000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        7,
        3,
        '25–50',
        25,
        50,
        5000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        8,
        3,
        '51–100',
        51,
        100,
        4000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        9,
        3,
        '101–150',
        101,
        150,
        3500,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        10,
        3,
        '>151',
        151,
        NULL,
        3000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        11,
        4,
        '25–50',
        25,
        50,
        3500,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        12,
        4,
        '51–100',
        51,
        100,
        3000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        13,
        4,
        '101–150',
        101,
        150,
        2500,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        14,
        4,
        '>151',
        151,
        NULL,
        2000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        15,
        5,
        '25–50 order',
        25,
        50,
        15000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        16,
        5,
        '51–100 order',
        51,
        100,
        12000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        17,
        5,
        '101–150 order',
        101,
        150,
        10000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        18,
        5,
        '>151 order',
        151,
        NULL,
        8000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        19,
        8,
        '25–50',
        25,
        50,
        25000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        20,
        8,
        '51–100',
        51,
        100,
        20000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        21,
        8,
        '101–150',
        101,
        150,
        18000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        22,
        8,
        '151–200',
        151,
        200,
        15000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        23,
        8,
        '>200',
        201,
        NULL,
        12000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        24,
        9,
        '25–50',
        25,
        50,
        45000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        25,
        9,
        '51–100',
        51,
        100,
        38000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        26,
        9,
        '101–150',
        101,
        150,
        32000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        27,
        9,
        '151–200',
        151,
        200,
        28000,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        28,
        9,
        '>200',
        201,
        NULL,
        24000,
        0,
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `alacarte_factors`
--

DROP TABLE IF EXISTS `alacarte_factors`;

CREATE TABLE `alacarte_factors` (
    `id` int NOT NULL,
    `package_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `factor` decimal(5, 3) NOT NULL,
    `min_per_book` int DEFAULT '0',
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `alacarte_factors`
--

INSERT INTO
    `alacarte_factors` (
        `id`,
        `package_code`,
        `factor`,
        `min_per_book`,
        `updated_at`
    )
VALUES (
        1,
        'ebook',
        0.680,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        2,
        'editcetak',
        0.580,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        3,
        'desain',
        0.550,
        0,
        '2026-04-06 08:54:43'
    ),
    (
        4,
        'cetakonly',
        0.350,
        0,
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `cetak_base`
--

DROP TABLE IF EXISTS `cetak_base`;

CREATE TABLE `cetak_base` (
    `id` int NOT NULL,
    `range_label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `min_students` int NOT NULL,
    `max_students` int NOT NULL,
    `pages_count` int NOT NULL,
    `base_price` int NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `cetak_base`
--

INSERT INTO
    `cetak_base` (
        `id`,
        `range_label`,
        `min_students`,
        `max_students`,
        `pages_count`,
        `base_price`,
        `description`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        '25–100 siswa',
        25,
        100,
        30,
        90000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        2,
        '25–100 siswa',
        25,
        100,
        40,
        110000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        3,
        '25–100 siswa',
        25,
        100,
        50,
        125000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        4,
        '25–100 siswa',
        25,
        100,
        60,
        140000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        5,
        '25–100 siswa',
        25,
        100,
        70,
        155000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        6,
        '25–100 siswa',
        25,
        100,
        80,
        170000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        7,
        '25–100 siswa',
        25,
        100,
        90,
        185000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        8,
        '101–200 siswa',
        101,
        200,
        30,
        80000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        9,
        '101–200 siswa',
        101,
        200,
        40,
        95000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        10,
        '101–200 siswa',
        101,
        200,
        50,
        110000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        11,
        '101–200 siswa',
        101,
        200,
        60,
        125000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        12,
        '101–200 siswa',
        101,
        200,
        70,
        140000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        13,
        '101–200 siswa',
        101,
        200,
        80,
        155000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        14,
        '>200 siswa',
        201,
        500,
        30,
        70000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        15,
        '>200 siswa',
        201,
        500,
        40,
        85000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        16,
        '>200 siswa',
        201,
        500,
        50,
        100000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        17,
        '>200 siswa',
        201,
        500,
        60,
        115000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        18,
        '>200 siswa',
        201,
        500,
        70,
        130000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        19,
        '>200 siswa',
        201,
        500,
        80,
        145000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `cetak_factors`
--

DROP TABLE IF EXISTS `cetak_factors`;

CREATE TABLE `cetak_factors` (
    `id` int NOT NULL,
    `package_type` enum('handy', 'minimal', 'large') COLLATE utf8mb4_unicode_ci NOT NULL,
    `factor` decimal(5, 3) NOT NULL,
    `description` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `cetak_factors`
--

INSERT INTO
    `cetak_factors` (
        `id`,
        `package_type`,
        `factor`,
        `description`,
        `updated_at`
    )
VALUES (
        1,
        'handy',
        1.000,
        'Faktor standar untuk Handy Book',
        '2026-04-06 08:54:43'
    ),
    (
        2,
        'minimal',
        0.850,
        'Faktor untuk Minimal Square Book',
        '2026-04-06 08:54:43'
    ),
    (
        3,
        'large',
        1.200,
        'Faktor untuk Large B4 Book',
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `graduation_addons`
--

DROP TABLE IF EXISTS `graduation_addons`;

CREATE TABLE `graduation_addons` (
    `id` int NOT NULL,
    `addon_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `price` int NOT NULL,
    `addon_type` enum('addon', 'cetak', 'service') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'addon',
    `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `description` text COLLATE utf8mb4_unicode_ci,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `graduation_addons`
--

INSERT INTO
    `graduation_addons` (
        `id`,
        `addon_key`,
        `name`,
        `price`,
        `addon_type`,
        `unit`,
        `description`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        'gad-makeup',
        'Makeup Artist',
        850000,
        'addon',
        'per event',
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        2,
        'gad-dress',
        'Dress Styling',
        1200000,
        'addon',
        'per event',
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        3,
        'gad-prepp',
        'Pre-PP (Hari Sebelumnya)',
        500000,
        'addon',
        'per session',
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        4,
        'gad-extrahr',
        'Extra Hour',
        750000,
        'addon',
        'per jam',
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        5,
        'gcetak-4r',
        'Foto 4R',
        15000,
        'cetak',
        'per lembar',
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        6,
        'gcetak-8r',
        'Foto 8R',
        35000,
        'cetak',
        'per lembar',
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        7,
        'gcetak-dvd',
        'DVD Digital',
        25000,
        'cetak',
        'per set',
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `overhead`
--

DROP TABLE IF EXISTS `overhead`;

CREATE TABLE `overhead` (
    `id` int NOT NULL,
    `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `amount` int NOT NULL DEFAULT '0',
    `description` text COLLATE utf8mb4_unicode_ci,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `overhead`
--

INSERT INTO
    `overhead` (
        `id`,
        `category`,
        `amount`,
        `description`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        'Designer',
        8000000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        2,
        'Marketing',
        3000000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        3,
        'Creative Prod.',
        5000000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        4,
        'Project Mgr',
        6000000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        5,
        'Social Media',
        2000000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        6,
        'Freelance',
        1500000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        7,
        'Operasional',
        4500000,
        NULL,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `packages_alacarte`
--

DROP TABLE IF EXISTS `packages_alacarte`;

CREATE TABLE `packages_alacarte` (
    `id` int NOT NULL AUTO_INCREMENT,
    `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci,
    `price_type` enum(
        'per_siswa',
        'flat_range',
        'flat_fixed'
    ) COLLATE utf8mb4_unicode_ci NOT NULL,
    `price_min` int DEFAULT NULL,
    `price_max` int DEFAULT NULL,
    `factor` decimal(5, 3) DEFAULT NULL,
    `margin_target` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `includes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
    `excludes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
    `display_order` int DEFAULT '0',
    `is_featured` tinyint(1) DEFAULT '0',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `packages_alacarte`
--

INSERT INTO
    `packages_alacarte` (
        `id`,
        `code`,
        `name`,
        `description`,
        `price_type`,
        `price_min`,
        `price_max`,
        `factor`,
        `margin_target`,
        `includes`,
        `excludes`,
        `display_order`,
        `is_featured`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        'ebook',
        'E-Book Package',
        'Foto+Editing+Desain, output file digital. Tanpa cetak fisik.',
        'per_siswa',
        NULL,
        NULL,
        0.680,
        '62–68%',
        NULL,
        NULL,
        0,
        1,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        2,
        'editcetak',
        'Edit+Desain+Cetak',
        'Klien bawa foto sendiri. Parama handle editing, layout, cetak & kirim.',
        'per_siswa',
        NULL,
        NULL,
        0.580,
        '55–62%',
        NULL,
        NULL,
        0,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        3,
        'fotohalf',
        'Foto Only (½ Hari)',
        'Sesi foto max ~75 siswa. Fotografer + fashion stylist.',
        'flat_range',
        3500000,
        5000000,
        NULL,
        '55–65%',
        NULL,
        NULL,
        0,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        4,
        'fotofull',
        'Foto Only (Full Day)',
        'Sesi foto 76–150+ siswa. Full team seharian.',
        'flat_range',
        6000000,
        9000000,
        NULL,
        '55–65%',
        NULL,
        NULL,
        0,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        5,
        'videodrone',
        'Drone Video',
        'Video drone 1–2 menit.',
        'flat_fixed',
        1500000,
        1500000,
        NULL,
        NULL,
        NULL,
        NULL,
        0,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        6,
        'videodoc',
        'Docudrama Video',
        'Video cerita angkatan 5–10 menit.',
        'flat_fixed',
        3000000,
        3000000,
        NULL,
        NULL,
        NULL,
        NULL,
        0,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        7,
        'desain',
        'Desain Only',
        'Klien bawa semua konten. Parama hanya layout buku.',
        'per_siswa',
        NULL,
        NULL,
        0.550,
        '55–65%',
        NULL,
        NULL,
        0,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        8,
        'cetakonly',
        'Cetak Only',
        'Klien sudah punya file siap cetak. Parama cetak & kirim saja.',
        'per_siswa',
        NULL,
        NULL,
        0.350,
        '30–45%',
        NULL,
        NULL,
        0,
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `packages_fullservice`
--

DROP TABLE IF EXISTS `packages_fullservice`;

CREATE TABLE `packages_fullservice` (
    `id` int NOT NULL,
    `package_type` enum('handy', 'minimal', 'large') COLLATE utf8mb4_unicode_ci NOT NULL,
    `min_students` int NOT NULL,
    `max_students` int NOT NULL,
    `price_per_book` int NOT NULL,
    `max_pages` int NOT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `packages_fullservice`
--

INSERT INTO
    `packages_fullservice` (
        `id`,
        `package_type`,
        `min_students`,
        `max_students`,
        `price_per_book`,
        `max_pages`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        'handy',
        25,
        50,
        399000,
        70,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        2,
        'handy',
        51,
        75,
        389000,
        75,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        3,
        'handy',
        76,
        100,
        379000,
        80,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        4,
        'handy',
        101,
        150,
        369000,
        90,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        5,
        'minimal',
        25,
        50,
        349000,
        65,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        6,
        'minimal',
        51,
        75,
        339000,
        70,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        7,
        'minimal',
        76,
        100,
        329000,
        75,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        8,
        'minimal',
        101,
        150,
        319000,
        85,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        9,
        'large',
        25,
        50,
        449000,
        75,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        10,
        'large',
        51,
        75,
        439000,
        85,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        11,
        'large',
        76,
        100,
        429000,
        95,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        12,
        'large',
        101,
        150,
        419000,
        105,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `packages_graduation`
--

DROP TABLE IF EXISTS `packages_graduation`;

CREATE TABLE `packages_graduation` (
    `id` int NOT NULL,
    `package_key` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci,
    `price` int NOT NULL,
    `color_scheme` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `is_featured` tinyint(1) DEFAULT '0',
    `transport_included` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `display_order` int DEFAULT '0',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `packages_graduation`
--

INSERT INTO
    `packages_graduation` (
        `id`,
        `package_key`,
        `name`,
        `description`,
        `price`,
        `color_scheme`,
        `is_featured`,
        `transport_included`,
        `display_order`,
        `created_at`,
        `updated_at`
    )
VALUES (
        1,
        'gphv',
        'Photo & Video',
        '2 Fotografer + 1 Videografer, 50 foto edited, video cinematic 2–4 mnt, G-Drive, 4 jam coverage, transport jabodetabek',
        4500000,
        'acc',
        1,
        'Jabodetabek',
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        2,
        'gvideo',
        'Video Only',
        '1 Videografer, video cinematic 2–5 mnt, G-Drive, 4 jam coverage, transport jabodetabek',
        2000000,
        NULL,
        0,
        'Jabodetabek',
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        3,
        'gphoto',
        'Photo Only',
        '2 Fotografer, 100 foto edited, G-Drive, 4 jam coverage, transport jabodetabek',
        2750000,
        NULL,
        0,
        'Jabodetabek',
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        4,
        'gbooth',
        'Photo Booth',
        '1–2 Crew profesional, backdrop wisuda, lighting studio, Selfiebox Machine, unlimited print 4R, max 3 jam, softcopy + QR Code realtime, transport jabodetabek',
        3850000,
        NULL,
        0,
        'Jabodetabek',
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        5,
        'g360',
        'Glamation 360°',
        '1–2 Crew profesional, MP4, LCD 50in preview, GoPro/iPhone 12 Pro, overlay design free, max 3 jam, QR Code realtime, transport jabodetabek',
        4100000,
        NULL,
        0,
        'Jabodetabek',
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    ),
    (
        6,
        'gcomplete',
        'Complete Package',
        'Photo + Video + Photo Booth, transport jabodetabek',
        7750000,
        'feat',
        1,
        'Jabodetabek',
        0,
        '2026-04-06 08:54:43',
        '2026-04-06 08:54:43'
    );

-- --------------------------------------------------------

--
-- Table structure for table `penawaran`
--

DROP TABLE IF EXISTS `penawaran`;

CREATE TABLE `penawaran` (
    `id` int NOT NULL AUTO_INCREMENT,
    `client_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
    `package` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `student_count` int DEFAULT NULL,
    `total_price` int NOT NULL,
    `discount_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `discount_value` int DEFAULT NULL,
    `bonus_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `bonus_nominal` int DEFAULT NULL,
    `final_price` int NOT NULL,
    `notes` text COLLATE utf8mb4_unicode_ci,
    `status` enum(
        'pending',
        'nego',
        'deal',
        'gagal'
    ) COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
    `created_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `month` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `status` (`status`),
    KEY `month` (`month`),
    KEY `created_at` (`created_at`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_addons`
--

DROP TABLE IF EXISTS `tbl_addons`;

CREATE TABLE `tbl_addons` (
    `id` int NOT NULL,
    `category` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `sub_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `price` int DEFAULT '0',
    `min_qty` int DEFAULT '0',
    `max_qty` int DEFAULT '9999'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `tbl_addons`
--

INSERT INTO
    `tbl_addons` (
        `id`,
        `category`,
        `sub_id`,
        `name`,
        `type`,
        `price`,
        `min_qty`,
        `max_qty`
    )
VALUES (
        1,
        'finishing',
        'binding',
        'Binding Paku/Jepang/Spiral',
        'flat',
        50000,
        25,
        75
    ),
    (
        2,
        'finishing',
        'binding',
        'Binding Paku/Jepang/Spiral',
        'flat',
        35000,
        76,
        150
    ),
    (
        3,
        'finishing',
        'binding',
        'Binding Paku/Jepang/Spiral',
        'flat',
        30000,
        151,
        9999
    ),
    (
        4,
        'finishing',
        'popup',
        'Pop Up 2D',
        'flat',
        55000,
        25,
        75
    ),
    (
        5,
        'finishing',
        'popup',
        'Pop Up 2D',
        'flat',
        40000,
        76,
        150
    ),
    (
        6,
        'finishing',
        'popup',
        'Pop Up 2D',
        'flat',
        35000,
        151,
        9999
    ),
    (
        7,
        'finishing',
        'tunnel',
        'Cover Tunnel',
        'flat',
        75000,
        25,
        75
    ),
    (
        8,
        'finishing',
        'tunnel',
        'Cover Tunnel',
        'flat',
        60000,
        76,
        150
    ),
    (
        9,
        'finishing',
        'tunnel',
        'Cover Tunnel',
        'flat',
        50000,
        151,
        9999
    ),
    (
        10,
        'finishing',
        'klip',
        'Cover Klip/Cetekan',
        'flat',
        15000,
        25,
        75
    ),
    (
        11,
        'finishing',
        'klip',
        'Cover Klip/Cetekan',
        'flat',
        10000,
        76,
        150
    ),
    (
        12,
        'finishing',
        'klip',
        'Cover Klip/Cetekan',
        'flat',
        8000,
        151,
        9999
    ),
    (
        13,
        'finishing',
        'covbahan',
        'Cover Bahan',
        'flat',
        55000,
        25,
        75
    ),
    (
        14,
        'finishing',
        'covbahan',
        'Cover Bahan',
        'flat',
        40000,
        76,
        150
    ),
    (
        15,
        'finishing',
        'covbahan',
        'Cover Bahan',
        'flat',
        35000,
        151,
        9999
    ),
    (
        16,
        'kertas',
        'ivory',
        'Ivory Paper',
        'per_hal',
        450,
        25,
        50
    ),
    (
        17,
        'kertas',
        'ivory',
        'Ivory Paper',
        'per_hal',
        250,
        51,
        100
    ),
    (
        18,
        'kertas',
        'ivory',
        'Ivory Paper',
        'per_hal',
        200,
        101,
        150
    ),
    (
        19,
        'kertas',
        'ivory',
        'Ivory Paper',
        'per_hal',
        150,
        151,
        9999
    ),
    (
        20,
        'kertas',
        'laminasi',
        'Laminasi Paper',
        'per_hal',
        600,
        25,
        50
    ),
    (
        21,
        'kertas',
        'laminasi',
        'Laminasi Paper',
        'per_hal',
        450,
        51,
        100
    ),
    (
        22,
        'kertas',
        'laminasi',
        'Laminasi Paper',
        'per_hal',
        400,
        101,
        150
    ),
    (
        23,
        'kertas',
        'laminasi',
        'Laminasi Paper',
        'per_hal',
        350,
        151,
        9999
    ),
    (
        24,
        'halaman',
        'extrahal',
        'Halaman Tambahan',
        'extra_hal',
        3000,
        25,
        50
    ),
    (
        25,
        'halaman',
        'extrahal',
        'Halaman Tambahan',
        'extra_hal',
        2000,
        51,
        100
    ),
    (
        26,
        'halaman',
        'extrahal',
        'Halaman Tambahan',
        'extra_hal',
        1300,
        101,
        150
    ),
    (
        27,
        'halaman',
        'extrahal',
        'Halaman Tambahan',
        'extra_hal',
        1000,
        151,
        9999
    ),
    (
        28,
        'video',
        'drone',
        'Drone Video (1-2 mnt)',
        'flat_video',
        1500000,
        0,
        9999
    ),
    (
        29,
        'video',
        'docudrama',
        'Docudrama Video (5-10 mnt)',
        'flat_video',
        3000000,
        0,
        9999
    ),
    (
        30,
        'pkg1',
        'slidebox',
        'Slide Box',
        'flat',
        45000,
        25,
        50
    ),
    (
        31,
        'pkg1',
        'slidebox',
        'Slide Box',
        'flat',
        40000,
        51,
        100
    ),
    (
        32,
        'pkg1',
        'slidebox',
        'Slide Box',
        'flat',
        35000,
        101,
        150
    ),
    (
        33,
        'pkg1',
        'slidebox',
        'Slide Box',
        'flat',
        30000,
        151,
        200
    ),
    (
        34,
        'pkg1',
        'slidebox',
        'Slide Box',
        'flat',
        25000,
        201,
        9999
    ),
    (
        35,
        'pkg1',
        'stdbox1',
        'Standart Box 1',
        'flat',
        150000,
        25,
        50
    ),
    (
        36,
        'pkg1',
        'stdbox1',
        'Standart Box 1',
        'flat',
        95000,
        51,
        100
    ),
    (
        37,
        'pkg1',
        'stdbox1',
        'Standart Box 1',
        'flat',
        80000,
        101,
        150
    ),
    (
        38,
        'pkg1',
        'stdbox1',
        'Standart Box 1',
        'flat',
        70000,
        151,
        200
    ),
    (
        39,
        'pkg1',
        'stdbox1',
        'Standart Box 1',
        'flat',
        65000,
        201,
        9999
    ),
    (
        40,
        'pkg1',
        'stdbox2',
        'Standart Box 2',
        'flat',
        150000,
        25,
        50
    ),
    (
        41,
        'pkg1',
        'stdbox2',
        'Standart Box 2',
        'flat',
        100000,
        51,
        100
    ),
    (
        42,
        'pkg1',
        'stdbox2',
        'Standart Box 2',
        'flat',
        80000,
        101,
        150
    ),
    (
        43,
        'pkg1',
        'stdbox2',
        'Standart Box 2',
        'flat',
        75000,
        151,
        200
    ),
    (
        44,
        'pkg1',
        'stdbox2',
        'Standart Box 2',
        'flat',
        70000,
        201,
        9999
    ),
    (
        45,
        'pkg1',
        'hardbox',
        'Hard Box 3 (Akrilik)',
        'flat',
        125000,
        25,
        50
    ),
    (
        46,
        'pkg1',
        'hardbox',
        'Hard Box 3 (Akrilik)',
        'flat',
        100000,
        51,
        100
    ),
    (
        47,
        'pkg1',
        'hardbox',
        'Hard Box 3 (Akrilik)',
        'flat',
        90000,
        101,
        150
    ),
    (
        48,
        'pkg1',
        'hardbox',
        'Hard Box 3 (Akrilik)',
        'flat',
        80000,
        151,
        200
    ),
    (
        49,
        'pkg1',
        'hardbox',
        'Hard Box 3 (Akrilik)',
        'flat',
        75000,
        201,
        9999
    ),
    (
        50,
        'pkg2',
        'cbox1',
        'Custom Box 1',
        'flat',
        200000,
        25,
        50
    ),
    (
        51,
        'pkg2',
        'cbox1',
        'Custom Box 1',
        'flat',
        170000,
        51,
        100
    ),
    (
        52,
        'pkg2',
        'cbox1',
        'Custom Box 1',
        'flat',
        130000,
        101,
        150
    ),
    (
        53,
        'pkg2',
        'cbox1',
        'Custom Box 1',
        'flat',
        120000,
        151,
        200
    ),
    (
        54,
        'pkg2',
        'cbox1',
        'Custom Box 1',
        'flat',
        110000,
        201,
        9999
    ),
    (
        55,
        'pkg2',
        'cbox2',
        'Custom Box 2',
        'flat',
        165000,
        25,
        50
    ),
    (
        56,
        'pkg2',
        'cbox2',
        'Custom Box 2',
        'flat',
        150000,
        51,
        100
    ),
    (
        57,
        'pkg2',
        'cbox2',
        'Custom Box 2',
        'flat',
        130000,
        101,
        150
    ),
    (
        58,
        'pkg2',
        'cbox2',
        'Custom Box 2',
        'flat',
        120000,
        151,
        200
    ),
    (
        59,
        'pkg2',
        'cbox2',
        'Custom Box 2',
        'flat',
        110000,
        201,
        9999
    ),
    (
        60,
        'pkg2',
        'cbox3',
        'Custom Box 3',
        'flat',
        200000,
        25,
        50
    ),
    (
        61,
        'pkg2',
        'cbox3',
        'Custom Box 3',
        'flat',
        170000,
        51,
        100
    ),
    (
        62,
        'pkg2',
        'cbox3',
        'Custom Box 3',
        'flat',
        130000,
        101,
        150
    ),
    (
        63,
        'pkg2',
        'cbox3',
        'Custom Box 3',
        'flat',
        120000,
        151,
        200
    ),
    (
        64,
        'pkg2',
        'cbox3',
        'Custom Box 3',
        'flat',
        110000,
        201,
        9999
    ),
    (
        65,
        'pkg2',
        'cbox4',
        'Custom Box 4',
        'flat',
        200000,
        25,
        50
    ),
    (
        66,
        'pkg2',
        'cbox4',
        'Custom Box 4',
        'flat',
        170000,
        51,
        100
    ),
    (
        67,
        'pkg2',
        'cbox4',
        'Custom Box 4',
        'flat',
        140000,
        101,
        150
    ),
    (
        68,
        'pkg2',
        'cbox4',
        'Custom Box 4',
        'flat',
        130000,
        151,
        200
    ),
    (
        69,
        'pkg2',
        'cbox4',
        'Custom Box 4',
        'flat',
        120000,
        201,
        9999
    ),
    (
        70,
        'pkg2',
        'cbox5',
        'Custom Box 5',
        'flat',
        200000,
        25,
        50
    ),
    (
        71,
        'pkg2',
        'cbox5',
        'Custom Box 5',
        'flat',
        170000,
        51,
        100
    ),
    (
        72,
        'pkg2',
        'cbox5',
        'Custom Box 5',
        'flat',
        145000,
        101,
        150
    ),
    (
        73,
        'pkg2',
        'cbox5',
        'Custom Box 5',
        'flat',
        135000,
        151,
        200
    ),
    (
        74,
        'pkg2',
        'cbox5',
        'Custom Box 5',
        'flat',
        130000,
        201,
        9999
    );

-- --------------------------------------------------------

--
-- Table structure for table `tbl_cetak_prices`
--

DROP TABLE IF EXISTS `tbl_cetak_prices`;

CREATE TABLE `tbl_cetak_prices` (
    `id` int NOT NULL,
    `range_idx` int DEFAULT NULL,
    `label` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `min_siswa` int DEFAULT NULL,
    `max_siswa` int DEFAULT NULL,
    `pages` int DEFAULT NULL,
    `harga` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `tbl_cetak_prices`
--

INSERT INTO
    `tbl_cetak_prices` (
        `id`,
        `range_idx`,
        `label`,
        `min_siswa`,
        `max_siswa`,
        `pages`,
        `harga`
    )
VALUES (
        1,
        0,
        '30-50 siswa',
        30,
        50,
        30,
        92000
    ),
    (
        2,
        0,
        '30-50 siswa',
        30,
        50,
        45,
        102000
    ),
    (
        3,
        0,
        '30-50 siswa',
        30,
        50,
        60,
        115000
    ),
    (
        4,
        0,
        '30-50 siswa',
        30,
        50,
        65,
        127000
    ),
    (
        5,
        0,
        '30-50 siswa',
        30,
        50,
        75,
        140000
    ),
    (
        6,
        0,
        '30-50 siswa',
        30,
        50,
        80,
        140000
    ),
    (
        7,
        0,
        '30-50 siswa',
        30,
        50,
        90,
        152000
    ),
    (
        8,
        0,
        '30-50 siswa',
        30,
        50,
        100,
        165000
    ),
    (
        9,
        0,
        '30-50 siswa',
        30,
        50,
        110,
        176000
    ),
    (
        10,
        0,
        '30-50 siswa',
        30,
        50,
        120,
        176000
    ),
    (
        11,
        0,
        '30-50 siswa',
        30,
        50,
        135,
        176000
    ),
    (
        12,
        0,
        '30-50 siswa',
        30,
        50,
        150,
        176000
    ),
    (
        13,
        0,
        '30-50 siswa',
        30,
        50,
        160,
        176000
    ),
    (
        14,
        1,
        '51-75 siswa',
        51,
        75,
        30,
        80000
    ),
    (
        15,
        1,
        '51-75 siswa',
        51,
        75,
        45,
        90000
    ),
    (
        16,
        1,
        '51-75 siswa',
        51,
        75,
        60,
        100000
    ),
    (
        17,
        1,
        '51-75 siswa',
        51,
        75,
        65,
        110000
    ),
    (
        18,
        1,
        '51-75 siswa',
        51,
        75,
        75,
        122000
    ),
    (
        19,
        1,
        '51-75 siswa',
        51,
        75,
        80,
        122000
    ),
    (
        20,
        1,
        '51-75 siswa',
        51,
        75,
        90,
        134000
    ),
    (
        21,
        1,
        '51-75 siswa',
        51,
        75,
        100,
        145000
    ),
    (
        22,
        1,
        '51-75 siswa',
        51,
        75,
        110,
        158000
    ),
    (
        23,
        1,
        '51-75 siswa',
        51,
        75,
        120,
        162000
    ),
    (
        24,
        1,
        '51-75 siswa',
        51,
        75,
        135,
        165000
    ),
    (
        25,
        1,
        '51-75 siswa',
        51,
        75,
        150,
        168000
    ),
    (
        26,
        1,
        '51-75 siswa',
        51,
        75,
        160,
        170000
    ),
    (
        27,
        2,
        '76-100 siswa',
        76,
        100,
        30,
        70000
    ),
    (
        28,
        2,
        '76-100 siswa',
        76,
        100,
        45,
        80000
    ),
    (
        29,
        2,
        '76-100 siswa',
        76,
        100,
        60,
        90000
    ),
    (
        30,
        2,
        '76-100 siswa',
        76,
        100,
        65,
        98000
    ),
    (
        31,
        2,
        '76-100 siswa',
        76,
        100,
        75,
        108000
    ),
    (
        32,
        2,
        '76-100 siswa',
        76,
        100,
        80,
        108000
    ),
    (
        33,
        2,
        '76-100 siswa',
        76,
        100,
        90,
        118000
    ),
    (
        34,
        2,
        '76-100 siswa',
        76,
        100,
        100,
        130000
    ),
    (
        35,
        2,
        '76-100 siswa',
        76,
        100,
        110,
        140000
    ),
    (
        36,
        2,
        '76-100 siswa',
        76,
        100,
        120,
        145000
    ),
    (
        37,
        2,
        '76-100 siswa',
        76,
        100,
        135,
        150000
    ),
    (
        38,
        2,
        '76-100 siswa',
        76,
        100,
        150,
        155000
    ),
    (
        39,
        2,
        '76-100 siswa',
        76,
        100,
        160,
        160000
    ),
    (
        40,
        3,
        '101-125 siswa',
        101,
        125,
        30,
        62000
    ),
    (
        41,
        3,
        '101-125 siswa',
        101,
        125,
        45,
        72000
    ),
    (
        42,
        3,
        '101-125 siswa',
        101,
        125,
        60,
        82000
    ),
    (
        43,
        3,
        '101-125 siswa',
        101,
        125,
        65,
        88000
    ),
    (
        44,
        3,
        '101-125 siswa',
        101,
        125,
        75,
        97000
    ),
    (
        45,
        3,
        '101-125 siswa',
        101,
        125,
        80,
        97000
    ),
    (
        46,
        3,
        '101-125 siswa',
        101,
        125,
        90,
        106000
    ),
    (
        47,
        3,
        '101-125 siswa',
        101,
        125,
        100,
        116000
    ),
    (
        48,
        3,
        '101-125 siswa',
        101,
        125,
        110,
        126000
    ),
    (
        49,
        3,
        '101-125 siswa',
        101,
        125,
        120,
        130000
    ),
    (
        50,
        3,
        '101-125 siswa',
        101,
        125,
        135,
        135000
    ),
    (
        51,
        3,
        '101-125 siswa',
        101,
        125,
        150,
        140000
    ),
    (
        52,
        3,
        '101-125 siswa',
        101,
        125,
        160,
        145000
    ),
    (
        53,
        4,
        '126-150 siswa',
        126,
        150,
        30,
        58000
    ),
    (
        54,
        4,
        '126-150 siswa',
        126,
        150,
        45,
        68000
    ),
    (
        55,
        4,
        '126-150 siswa',
        126,
        150,
        60,
        76000
    ),
    (
        56,
        4,
        '126-150 siswa',
        126,
        150,
        65,
        82000
    ),
    (
        57,
        4,
        '126-150 siswa',
        126,
        150,
        75,
        90000
    ),
    (
        58,
        4,
        '126-150 siswa',
        126,
        150,
        80,
        90000
    ),
    (
        59,
        4,
        '126-150 siswa',
        126,
        150,
        90,
        98000
    ),
    (
        60,
        4,
        '126-150 siswa',
        126,
        150,
        100,
        108000
    ),
    (
        61,
        4,
        '126-150 siswa',
        126,
        150,
        110,
        118000
    ),
    (
        62,
        4,
        '126-150 siswa',
        126,
        150,
        120,
        122000
    ),
    (
        63,
        4,
        '126-150 siswa',
        126,
        150,
        135,
        127000
    ),
    (
        64,
        4,
        '126-150 siswa',
        126,
        150,
        150,
        132000
    ),
    (
        65,
        4,
        '126-150 siswa',
        126,
        150,
        160,
        137000
    ),
    (
        66,
        5,
        '151-175 siswa',
        151,
        175,
        30,
        54000
    ),
    (
        67,
        5,
        '151-175 siswa',
        151,
        175,
        45,
        63000
    ),
    (
        68,
        5,
        '151-175 siswa',
        151,
        175,
        60,
        71000
    ),
    (
        69,
        5,
        '151-175 siswa',
        151,
        175,
        65,
        76000
    ),
    (
        70,
        5,
        '151-175 siswa',
        151,
        175,
        75,
        84000
    ),
    (
        71,
        5,
        '151-175 siswa',
        151,
        175,
        80,
        84000
    ),
    (
        72,
        5,
        '151-175 siswa',
        151,
        175,
        90,
        91000
    ),
    (
        73,
        5,
        '151-175 siswa',
        151,
        175,
        100,
        100000
    ),
    (
        74,
        5,
        '151-175 siswa',
        151,
        175,
        110,
        109000
    ),
    (
        75,
        5,
        '151-175 siswa',
        151,
        175,
        120,
        113000
    ),
    (
        76,
        5,
        '151-175 siswa',
        151,
        175,
        135,
        118000
    ),
    (
        77,
        5,
        '151-175 siswa',
        151,
        175,
        150,
        123000
    ),
    (
        78,
        5,
        '151-175 siswa',
        151,
        175,
        160,
        128000
    ),
    (
        79,
        6,
        '176-200 siswa',
        176,
        200,
        30,
        50000
    ),
    (
        80,
        6,
        '176-200 siswa',
        176,
        200,
        45,
        59000
    ),
    (
        81,
        6,
        '176-200 siswa',
        176,
        200,
        60,
        66000
    ),
    (
        82,
        6,
        '176-200 siswa',
        176,
        200,
        65,
        71000
    ),
    (
        83,
        6,
        '176-200 siswa',
        176,
        200,
        75,
        78000
    ),
    (
        84,
        6,
        '176-200 siswa',
        176,
        200,
        80,
        78000
    ),
    (
        85,
        6,
        '176-200 siswa',
        176,
        200,
        90,
        85000
    ),
    (
        86,
        6,
        '176-200 siswa',
        176,
        200,
        100,
        93000
    ),
    (
        87,
        6,
        '176-200 siswa',
        176,
        200,
        110,
        101000
    ),
    (
        88,
        6,
        '176-200 siswa',
        176,
        200,
        120,
        105000
    ),
    (
        89,
        6,
        '176-200 siswa',
        176,
        200,
        135,
        110000
    ),
    (
        90,
        6,
        '176-200 siswa',
        176,
        200,
        150,
        115000
    ),
    (
        91,
        6,
        '176-200 siswa',
        176,
        200,
        160,
        119000
    ),
    (
        92,
        7,
        '201-225 siswa',
        201,
        225,
        30,
        47000
    ),
    (
        93,
        7,
        '201-225 siswa',
        201,
        225,
        45,
        55000
    ),
    (
        94,
        7,
        '201-225 siswa',
        201,
        225,
        60,
        62000
    ),
    (
        95,
        7,
        '201-225 siswa',
        201,
        225,
        65,
        66000
    ),
    (
        96,
        7,
        '201-225 siswa',
        201,
        225,
        75,
        73000
    ),
    (
        97,
        7,
        '201-225 siswa',
        201,
        225,
        80,
        73000
    ),
    (
        98,
        7,
        '201-225 siswa',
        201,
        225,
        90,
        79000
    ),
    (
        99,
        7,
        '201-225 siswa',
        201,
        225,
        100,
        87000
    ),
    (
        100,
        7,
        '201-225 siswa',
        201,
        225,
        110,
        95000
    ),
    (
        101,
        7,
        '201-225 siswa',
        201,
        225,
        120,
        98000
    ),
    (
        102,
        7,
        '201-225 siswa',
        201,
        225,
        135,
        103000
    ),
    (
        103,
        7,
        '201-225 siswa',
        201,
        225,
        150,
        107000
    ),
    (
        104,
        7,
        '201-225 siswa',
        201,
        225,
        160,
        111000
    ),
    (
        105,
        8,
        '226-250 siswa',
        226,
        250,
        30,
        44000
    ),
    (
        106,
        8,
        '226-250 siswa',
        226,
        250,
        45,
        52000
    ),
    (
        107,
        8,
        '226-250 siswa',
        226,
        250,
        60,
        58000
    ),
    (
        108,
        8,
        '226-250 siswa',
        226,
        250,
        65,
        62000
    ),
    (
        109,
        8,
        '226-250 siswa',
        226,
        250,
        75,
        68000
    ),
    (
        110,
        8,
        '226-250 siswa',
        226,
        250,
        80,
        68000
    ),
    (
        111,
        8,
        '226-250 siswa',
        226,
        250,
        90,
        74000
    ),
    (
        112,
        8,
        '226-250 siswa',
        226,
        250,
        100,
        81000
    ),
    (
        113,
        8,
        '226-250 siswa',
        226,
        250,
        110,
        88000
    ),
    (
        114,
        8,
        '226-250 siswa',
        226,
        250,
        120,
        92000
    ),
    (
        115,
        8,
        '226-250 siswa',
        226,
        250,
        135,
        96000
    ),
    (
        116,
        8,
        '226-250 siswa',
        226,
        250,
        150,
        100000
    ),
    (
        117,
        8,
        '226-250 siswa',
        226,
        250,
        160,
        104000
    ),
    (
        118,
        9,
        '251-275 siswa',
        251,
        275,
        30,
        41000
    ),
    (
        119,
        9,
        '251-275 siswa',
        251,
        275,
        45,
        49000
    ),
    (
        120,
        9,
        '251-275 siswa',
        251,
        275,
        60,
        55000
    ),
    (
        121,
        9,
        '251-275 siswa',
        251,
        275,
        65,
        58000
    ),
    (
        122,
        9,
        '251-275 siswa',
        251,
        275,
        75,
        64000
    ),
    (
        123,
        9,
        '251-275 siswa',
        251,
        275,
        80,
        64000
    ),
    (
        124,
        9,
        '251-275 siswa',
        251,
        275,
        90,
        70000
    ),
    (
        125,
        9,
        '251-275 siswa',
        251,
        275,
        100,
        76000
    ),
    (
        126,
        9,
        '251-275 siswa',
        251,
        275,
        110,
        83000
    ),
    (
        127,
        9,
        '251-275 siswa',
        251,
        275,
        120,
        86000
    ),
    (
        128,
        9,
        '251-275 siswa',
        251,
        275,
        135,
        90000
    ),
    (
        129,
        9,
        '251-275 siswa',
        251,
        275,
        150,
        94000
    ),
    (
        130,
        9,
        '251-275 siswa',
        251,
        275,
        160,
        98000
    ),
    (
        131,
        10,
        '276-300 siswa',
        276,
        300,
        30,
        39000
    ),
    (
        132,
        10,
        '276-300 siswa',
        276,
        300,
        45,
        46000
    ),
    (
        133,
        10,
        '276-300 siswa',
        276,
        300,
        60,
        52000
    ),
    (
        134,
        10,
        '276-300 siswa',
        276,
        300,
        65,
        55000
    ),
    (
        135,
        10,
        '276-300 siswa',
        276,
        300,
        75,
        60000
    ),
    (
        136,
        10,
        '276-300 siswa',
        276,
        300,
        80,
        60000
    ),
    (
        137,
        10,
        '276-300 siswa',
        276,
        300,
        90,
        66000
    ),
    (
        138,
        10,
        '276-300 siswa',
        276,
        300,
        100,
        72000
    ),
    (
        139,
        10,
        '276-300 siswa',
        276,
        300,
        110,
        78000
    ),
    (
        140,
        10,
        '276-300 siswa',
        276,
        300,
        120,
        81000
    ),
    (
        141,
        10,
        '276-300 siswa',
        276,
        300,
        135,
        85000
    ),
    (
        142,
        10,
        '276-300 siswa',
        276,
        300,
        150,
        89000
    ),
    (
        143,
        10,
        '276-300 siswa',
        276,
        300,
        160,
        92000
    ),
    (
        144,
        11,
        '301-325 siswa',
        301,
        325,
        30,
        37000
    ),
    (
        145,
        11,
        '301-325 siswa',
        301,
        325,
        44,
        44000
    ),
    (
        146,
        11,
        '301-325 siswa',
        301,
        325,
        60,
        49000
    ),
    (
        147,
        11,
        '301-325 siswa',
        301,
        325,
        65,
        52000
    ),
    (
        148,
        11,
        '301-325 siswa',
        301,
        325,
        75,
        57000
    ),
    (
        149,
        11,
        '301-325 siswa',
        301,
        325,
        80,
        57000
    ),
    (
        150,
        11,
        '301-325 siswa',
        301,
        325,
        90,
        62000
    ),
    (
        151,
        11,
        '301-325 siswa',
        301,
        325,
        100,
        68000
    ),
    (
        152,
        11,
        '301-325 siswa',
        301,
        325,
        110,
        74000
    ),
    (
        153,
        11,
        '301-325 siswa',
        301,
        325,
        120,
        77000
    ),
    (
        154,
        11,
        '301-325 siswa',
        301,
        325,
        135,
        80000
    ),
    (
        155,
        11,
        '301-325 siswa',
        301,
        325,
        150,
        84000
    ),
    (
        156,
        11,
        '301-325 siswa',
        301,
        325,
        160,
        87000
    ),
    (
        157,
        12,
        '326-350 siswa',
        326,
        350,
        30,
        35000
    ),
    (
        158,
        12,
        '326-350 siswa',
        326,
        350,
        42,
        42000
    ),
    (
        159,
        12,
        '326-350 siswa',
        326,
        350,
        60,
        47000
    ),
    (
        160,
        12,
        '326-350 siswa',
        326,
        350,
        65,
        50000
    ),
    (
        161,
        12,
        '326-350 siswa',
        326,
        350,
        75,
        54000
    ),
    (
        162,
        12,
        '326-350 siswa',
        326,
        350,
        80,
        54000
    ),
    (
        163,
        12,
        '326-350 siswa',
        326,
        350,
        90,
        59000
    ),
    (
        164,
        12,
        '326-350 siswa',
        326,
        350,
        100,
        65000
    ),
    (
        165,
        12,
        '326-350 siswa',
        326,
        350,
        110,
        70000
    ),
    (
        166,
        12,
        '326-350 siswa',
        326,
        350,
        120,
        73000
    ),
    (
        167,
        12,
        '326-350 siswa',
        326,
        350,
        135,
        76000
    ),
    (
        168,
        12,
        '326-350 siswa',
        326,
        350,
        150,
        80000
    ),
    (
        169,
        12,
        '326-350 siswa',
        326,
        350,
        160,
        83000
    ),
    (
        170,
        13,
        '351-375 siswa',
        351,
        375,
        30,
        33000
    ),
    (
        171,
        13,
        '351-375 siswa',
        351,
        375,
        40,
        40000
    ),
    (
        172,
        13,
        '351-375 siswa',
        351,
        375,
        60,
        45000
    ),
    (
        173,
        13,
        '351-375 siswa',
        351,
        375,
        65,
        47000
    ),
    (
        174,
        13,
        '351-375 siswa',
        351,
        375,
        75,
        52000
    ),
    (
        175,
        13,
        '351-375 siswa',
        351,
        375,
        80,
        52000
    ),
    (
        176,
        13,
        '351-375 siswa',
        351,
        375,
        90,
        56000
    ),
    (
        177,
        13,
        '351-375 siswa',
        351,
        375,
        100,
        62000
    ),
    (
        178,
        13,
        '351-375 siswa',
        351,
        375,
        110,
        67000
    ),
    (
        179,
        13,
        '351-375 siswa',
        351,
        375,
        120,
        70000
    ),
    (
        180,
        13,
        '351-375 siswa',
        351,
        375,
        135,
        73000
    ),
    (
        181,
        13,
        '351-375 siswa',
        351,
        375,
        150,
        76000
    ),
    (
        182,
        13,
        '351-375 siswa',
        351,
        375,
        160,
        79000
    ),
    (
        183,
        14,
        '376-400 siswa',
        376,
        400,
        30,
        31000
    ),
    (
        184,
        14,
        '376-400 siswa',
        376,
        400,
        38,
        38000
    ),
    (
        185,
        14,
        '376-400 siswa',
        376,
        400,
        60,
        42000
    ),
    (
        186,
        14,
        '376-400 siswa',
        376,
        400,
        65,
        45000
    ),
    (
        187,
        14,
        '376-400 siswa',
        376,
        400,
        75,
        49000
    ),
    (
        188,
        14,
        '376-400 siswa',
        376,
        400,
        80,
        49000
    ),
    (
        189,
        14,
        '376-400 siswa',
        376,
        400,
        90,
        53000
    ),
    (
        190,
        14,
        '376-400 siswa',
        376,
        400,
        100,
        58000
    ),
    (
        191,
        14,
        '376-400 siswa',
        376,
        400,
        110,
        63000
    ),
    (
        192,
        14,
        '376-400 siswa',
        376,
        400,
        120,
        66000
    ),
    (
        193,
        14,
        '376-400 siswa',
        376,
        400,
        135,
        69000
    ),
    (
        194,
        14,
        '376-400 siswa',
        376,
        400,
        150,
        72000
    ),
    (
        195,
        14,
        '376-400 siswa',
        376,
        400,
        160,
        75000
    ),
    (
        196,
        15,
        '401-425 siswa',
        401,
        425,
        30,
        30000
    ),
    (
        197,
        15,
        '401-425 siswa',
        401,
        425,
        36,
        36000
    ),
    (
        198,
        15,
        '401-425 siswa',
        401,
        425,
        60,
        40000
    ),
    (
        199,
        15,
        '401-425 siswa',
        401,
        425,
        65,
        42000
    ),
    (
        200,
        15,
        '401-425 siswa',
        401,
        425,
        75,
        46000
    ),
    (
        201,
        15,
        '401-425 siswa',
        401,
        425,
        80,
        46000
    ),
    (
        202,
        15,
        '401-425 siswa',
        401,
        425,
        90,
        50000
    ),
    (
        203,
        15,
        '401-425 siswa',
        401,
        425,
        100,
        55000
    ),
    (
        204,
        15,
        '401-425 siswa',
        401,
        425,
        110,
        60000
    ),
    (
        205,
        15,
        '401-425 siswa',
        401,
        425,
        120,
        62000
    ),
    (
        206,
        15,
        '401-425 siswa',
        401,
        425,
        135,
        65000
    ),
    (
        207,
        15,
        '401-425 siswa',
        401,
        425,
        150,
        68000
    ),
    (
        208,
        15,
        '401-425 siswa',
        401,
        425,
        160,
        71000
    ),
    (
        209,
        16,
        '426-450 siswa',
        426,
        450,
        30,
        28000
    ),
    (
        210,
        16,
        '426-450 siswa',
        426,
        450,
        34,
        34000
    ),
    (
        211,
        16,
        '426-450 siswa',
        426,
        450,
        60,
        38000
    ),
    (
        212,
        16,
        '426-450 siswa',
        426,
        450,
        65,
        40000
    ),
    (
        213,
        16,
        '426-450 siswa',
        426,
        450,
        75,
        44000
    ),
    (
        214,
        16,
        '426-450 siswa',
        426,
        450,
        80,
        44000
    ),
    (
        215,
        16,
        '426-450 siswa',
        426,
        450,
        90,
        48000
    ),
    (
        216,
        16,
        '426-450 siswa',
        426,
        450,
        100,
        52000
    ),
    (
        217,
        16,
        '426-450 siswa',
        426,
        450,
        110,
        57000
    ),
    (
        218,
        16,
        '426-450 siswa',
        426,
        450,
        120,
        59000
    ),
    (
        219,
        16,
        '426-450 siswa',
        426,
        450,
        135,
        62000
    ),
    (
        220,
        16,
        '426-450 siswa',
        426,
        450,
        150,
        65000
    ),
    (
        221,
        16,
        '426-450 siswa',
        426,
        450,
        160,
        67000
    ),
    (
        222,
        17,
        '451-475 siswa',
        451,
        475,
        30,
        27000
    ),
    (
        223,
        17,
        '451-475 siswa',
        451,
        475,
        32,
        32000
    ),
    (
        224,
        17,
        '451-475 siswa',
        451,
        475,
        60,
        36000
    ),
    (
        225,
        17,
        '451-475 siswa',
        451,
        475,
        65,
        38000
    ),
    (
        226,
        17,
        '451-475 siswa',
        451,
        475,
        75,
        42000
    ),
    (
        227,
        17,
        '451-475 siswa',
        451,
        475,
        80,
        42000
    ),
    (
        228,
        17,
        '451-475 siswa',
        451,
        475,
        90,
        45000
    ),
    (
        229,
        17,
        '451-475 siswa',
        451,
        475,
        100,
        50000
    ),
    (
        230,
        17,
        '451-475 siswa',
        451,
        475,
        110,
        54000
    ),
    (
        231,
        17,
        '451-475 siswa',
        451,
        475,
        120,
        56000
    ),
    (
        232,
        17,
        '451-475 siswa',
        451,
        475,
        135,
        59000
    ),
    (
        233,
        17,
        '451-475 siswa',
        451,
        475,
        150,
        62000
    ),
    (
        234,
        17,
        '451-475 siswa',
        451,
        475,
        160,
        64000
    ),
    (
        235,
        18,
        '476-500 siswa',
        476,
        500,
        30,
        26000
    ),
    (
        236,
        18,
        '476-500 siswa',
        476,
        500,
        31,
        31000
    ),
    (
        237,
        18,
        '476-500 siswa',
        476,
        500,
        60,
        34000
    ),
    (
        238,
        18,
        '476-500 siswa',
        476,
        500,
        65,
        36000
    ),
    (
        239,
        18,
        '476-500 siswa',
        476,
        500,
        75,
        40000
    ),
    (
        240,
        18,
        '476-500 siswa',
        476,
        500,
        80,
        40000
    ),
    (
        241,
        18,
        '476-500 siswa',
        476,
        500,
        90,
        43000
    ),
    (
        242,
        18,
        '476-500 siswa',
        476,
        500,
        100,
        47000
    ),
    (
        243,
        18,
        '476-500 siswa',
        476,
        500,
        110,
        51000
    ),
    (
        244,
        18,
        '476-500 siswa',
        476,
        500,
        120,
        53000
    ),
    (
        245,
        18,
        '476-500 siswa',
        476,
        500,
        135,
        56000
    ),
    (
        246,
        18,
        '476-500 siswa',
        476,
        500,
        150,
        59000
    ),
    (
        247,
        18,
        '476-500 siswa',
        476,
        500,
        160,
        61000
    );

-- --------------------------------------------------------

--
-- Table structure for table `tbl_fs_prices`
--

DROP TABLE IF EXISTS `tbl_fs_prices`;

CREATE TABLE `tbl_fs_prices` (
    `id` int NOT NULL,
    `pkg` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `min_siswa` int DEFAULT NULL,
    `max_siswa` int DEFAULT NULL,
    `harga` int DEFAULT NULL,
    `pages` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `tbl_fs_prices`
--

INSERT INTO
    `tbl_fs_prices` (
        `id`,
        `pkg`,
        `min_siswa`,
        `max_siswa`,
        `harga`,
        `pages`
    )
VALUES (
        1,
        'handy',
        30,
        50,
        465000,
        30
    ),
    (
        2,
        'handy',
        51,
        75,
        415000,
        30
    ),
    (
        3,
        'handy',
        76,
        100,
        370000,
        45
    ),
    (
        4,
        'handy',
        101,
        125,
        350000,
        55
    ),
    (
        5,
        'handy',
        126,
        150,
        335000,
        60
    ),
    (
        6,
        'handy',
        151,
        175,
        315000,
        65
    ),
    (
        7,
        'handy',
        176,
        200,
        295000,
        75
    ),
    (
        8,
        'handy',
        201,
        225,
        260000,
        80
    ),
    (
        9,
        'handy',
        226,
        250,
        250000,
        80
    ),
    (
        10,
        'handy',
        251,
        275,
        240000,
        90
    ),
    (
        11,
        'handy',
        276,
        300,
        230000,
        100
    ),
    (
        12,
        'handy',
        300,
        325,
        220000,
        100
    ),
    (
        13,
        'handy',
        326,
        350,
        210000,
        120
    ),
    (
        14,
        'handy',
        351,
        375,
        200000,
        120
    ),
    (
        15,
        'handy',
        376,
        400,
        190000,
        135
    ),
    (
        16,
        'handy',
        401,
        425,
        185000,
        135
    ),
    (
        17,
        'handy',
        426,
        450,
        165000,
        145
    ),
    (
        18,
        'handy',
        451,
        475,
        175000,
        150
    ),
    (
        19,
        'handy',
        476,
        500,
        150000,
        160
    ),
    (
        20,
        'minimal',
        30,
        50,
        450000,
        30
    ),
    (
        21,
        'minimal',
        51,
        75,
        400000,
        30
    ),
    (
        22,
        'minimal',
        76,
        100,
        355000,
        45
    ),
    (
        23,
        'minimal',
        101,
        125,
        335000,
        55
    ),
    (
        24,
        'minimal',
        126,
        150,
        320000,
        60
    ),
    (
        25,
        'minimal',
        151,
        175,
        300000,
        65
    ),
    (
        26,
        'minimal',
        176,
        200,
        280000,
        75
    ),
    (
        27,
        'minimal',
        201,
        225,
        245000,
        80
    ),
    (
        28,
        'minimal',
        226,
        250,
        235000,
        80
    ),
    (
        29,
        'minimal',
        251,
        275,
        240000,
        90
    ),
    (
        30,
        'minimal',
        276,
        300,
        215000,
        100
    ),
    (
        31,
        'minimal',
        300,
        325,
        205000,
        100
    ),
    (
        32,
        'minimal',
        326,
        350,
        195000,
        120
    ),
    (
        33,
        'minimal',
        351,
        375,
        185000,
        120
    ),
    (
        34,
        'minimal',
        376,
        400,
        180000,
        135
    ),
    (
        35,
        'minimal',
        401,
        425,
        170000,
        135
    ),
    (
        36,
        'minimal',
        426,
        450,
        160000,
        145
    ),
    (
        37,
        'minimal',
        451,
        475,
        150000,
        150
    ),
    (
        38,
        'minimal',
        476,
        500,
        140000,
        160
    ),
    (
        39,
        'large',
        30,
        50,
        480000,
        30
    ),
    (
        40,
        'large',
        51,
        75,
        430000,
        30
    ),
    (
        41,
        'large',
        76,
        100,
        405000,
        45
    ),
    (
        42,
        'large',
        101,
        125,
        365000,
        55
    ),
    (
        43,
        'large',
        126,
        150,
        350000,
        60
    ),
    (
        44,
        'large',
        151,
        175,
        330000,
        65
    ),
    (
        45,
        'large',
        176,
        200,
        310000,
        75
    ),
    (
        46,
        'large',
        201,
        225,
        275000,
        80
    ),
    (
        47,
        'large',
        226,
        250,
        265000,
        80
    ),
    (
        48,
        'large',
        251,
        275,
        255000,
        90
    ),
    (
        49,
        'large',
        276,
        300,
        245000,
        100
    ),
    (
        50,
        'large',
        300,
        325,
        235000,
        100
    ),
    (
        51,
        'large',
        326,
        350,
        225000,
        120
    ),
    (
        52,
        'large',
        351,
        375,
        215000,
        120
    ),
    (
        53,
        'large',
        376,
        400,
        205000,
        135
    ),
    (
        54,
        'large',
        401,
        425,
        195000,
        135
    ),
    (
        55,
        'large',
        426,
        450,
        175000,
        145
    ),
    (
        56,
        'large',
        451,
        475,
        165000,
        150
    ),
    (
        57,
        'large',
        476,
        500,
        155000,
        160
    );

-- --------------------------------------------------------

--
-- Table structure for table `tbl_grad_addons`
--

DROP TABLE IF EXISTS `tbl_grad_addons`;

CREATE TABLE `tbl_grad_addons` (
    `id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    `category` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `price` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `tbl_grad_addons`
--

INSERT INTO
    `tbl_grad_addons` (
        `id`,
        `category`,
        `name`,
        `price`
    )
VALUES (
        'g10r',
        'cetak',
        'Cetak Foto 10R',
        15000
    ),
    (
        'g12r',
        'cetak',
        'Cetak Foto 12R',
        20000
    ),
    (
        'g4r',
        'cetak',
        'Cetak Foto 4R',
        4000
    ),
    (
        'g8r',
        'cetak',
        'Cetak Foto 8R',
        8000
    ),
    (
        'gbooth_add',
        'addons',
        'Tambah 1 Jam Photobooth/360',
        500000
    ),
    (
        'gphoto_add',
        'addons',
        'Tambah 1 Fotografer',
        1250000
    ),
    (
        'gvideo_add',
        'addons',
        'Tambah 1 Videografer',
        1500000
    ),
    (
        'gwork_add',
        'addons',
        'Tambah 1 Jam Kerja/Orang',
        350000
    );

-- --------------------------------------------------------

--
-- Table structure for table `tbl_grad_packages`
--

DROP TABLE IF EXISTS `tbl_grad_packages`;

CREATE TABLE `tbl_grad_packages` (
    `id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
    `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `price` int DEFAULT NULL,
    `description` text COLLATE utf8mb4_general_ci,
    `color` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `tbl_grad_packages`
--

INSERT INTO
    `tbl_grad_packages` (
        `id`,
        `name`,
        `price`,
        `description`,
        `color`
    )
VALUES (
        'g360',
        'Glamation 360',
        4100000,
        '1-2 Crew profesional',
        ''
    ),
    (
        'gbooth',
        'Photo Booth',
        3850000,
        '1-2 Crew profesional',
        ''
    ),
    (
        'gcomplete',
        'Complete Package',
        7750000,
        'Photo + Video',
        'feat'
    ),
    (
        'gphoto',
        'Photo Only',
        2750000,
        '2 Fotografer',
        ''
    ),
    (
        'gphv',
        'Photo & Video',
        4500000,
        '2 Fotografer + 1 Videografer',
        'acc'
    ),
    (
        'gvideo',
        'Video Only',
        2000000,
        '1 Videografer',
        ''
    );

-- --------------------------------------------------------

--
-- Table structure for table `tbl_multipliers`
--

DROP TABLE IF EXISTS `tbl_multipliers`;

CREATE TABLE `tbl_multipliers` (
    `id` int NOT NULL,
    `category` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `key_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `label` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `value` float DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `tbl_multipliers`
--

INSERT INTO
    `tbl_multipliers` (
        `id`,
        `category`,
        `key_name`,
        `label`,
        `value`
    )
VALUES (
        1,
        'cetak',
        'handy',
        'Handy Book A4+',
        1
    ),
    (
        2,
        'cetak',
        'minimal',
        'Minimal Book SQ',
        0.95
    ),
    (
        3,
        'cetak',
        'large',
        'Large Book B4',
        1.15
    ),
    (
        4,
        'alc',
        'ebook',
        'E-Book Package (%)',
        72
    ),
    (
        5,
        'alc',
        'editcetak',
        'Edit+Desain+Cetak (%)',
        62
    ),
    (
        6,
        'alc',
        'desain',
        'Desain Only (%)',
        22
    ),
    (
        7,
        'alc',
        'cetakonly',
        'Cetak Only (%)',
        30
    );

-- --------------------------------------------------------

--
-- Table structure for table `tbl_overhead`
--

DROP TABLE IF EXISTS `tbl_overhead`;

CREATE TABLE `tbl_overhead` (
    `id` int NOT NULL,
    `key_name` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `label` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
    `amount` int DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;

--
-- Dumping data for table `tbl_overhead`
--

INSERT INTO
    `tbl_overhead` (
        `id`,
        `key_name`,
        `label`,
        `amount`
    )
VALUES (
        1,
        'total',
        'Total Overhead',
        65540000
    ),
    (
        2,
        'marketing',
        'Marketing & Pemasaran',
        12750000
    ),
    (
        3,
        'creative',
        'Tim Kreatif (Desain, Video)',
        7670000
    ),
    (
        4,
        'designer',
        'Fotografer & Tim Lapangan',
        16700000
    ),
    (
        5,
        'pm',
        'Project Manager',
        7200000
    ),
    (
        6,
        'sosmed',
        'Sosmed & Konten',
        6430000
    ),
    (
        7,
        'freelance',
        'Cadangan Freelance',
        3204000
    ),
    (
        8,
        'ops',
        'Ops (Tools, Transport, Server)',
        11586000
    );

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addon_categories`
--
ALTER TABLE `addon_categories`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `addon_items`
--
ALTER TABLE `addon_items`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `name_category` (`name`, `category_id`),
ADD KEY `idx_addon_cat` (`category_id`);

--
-- Indexes for table `addon_tiers`
--
ALTER TABLE `addon_tiers`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `tier_range` (
    `addon_item_id`,
    `min_quantity`
),
ADD KEY `idx_addon_tier` (`addon_item_id`);

--
-- Indexes for table `alacarte_factors`
--
ALTER TABLE `alacarte_factors`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `package_code` (`package_code`);

--
-- Indexes for table `cetak_base`
--
ALTER TABLE `cetak_base`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `range_pages` (`min_students`, `pages_count`),
ADD KEY `idx_cetak_range` (`min_students`, `pages_count`);

--
-- Indexes for table `cetak_factors`
--
ALTER TABLE `cetak_factors`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `package_type` (`package_type`);

--
-- Indexes for table `graduation_addons`
--
ALTER TABLE `graduation_addons`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `addon_key` (`addon_key`);

--
-- Indexes for table `overhead`
--
ALTER TABLE `overhead`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `category` (`category`);

--
-- Indexes for table `packages_fullservice`
--
ALTER TABLE `packages_fullservice`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `type_range` (
    `package_type`,
    `min_students`
),
ADD KEY `idx_fs_type` (`package_type`);

--
-- Indexes for table `packages_graduation`
--
ALTER TABLE `packages_graduation`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `package_key` (`package_key`),
ADD KEY `idx_grad_pkg` (`package_key`);

--
-- Indexes for table `tbl_addons`
--
ALTER TABLE `tbl_addons` ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_cetak_prices`
--
ALTER TABLE `tbl_cetak_prices` ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_fs_prices`
--
ALTER TABLE `tbl_fs_prices` ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_grad_addons`
--
ALTER TABLE `tbl_grad_addons` ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_grad_packages`
--
ALTER TABLE `tbl_grad_packages` ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_multipliers`
--
ALTER TABLE `tbl_multipliers`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Indexes for table `tbl_overhead`
--
ALTER TABLE `tbl_overhead`
ADD PRIMARY KEY (`id`),
ADD UNIQUE KEY `key_name` (`key_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addon_categories`
--
ALTER TABLE `addon_categories`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 7;

--
-- AUTO_INCREMENT for table `addon_items`
--
ALTER TABLE `addon_items`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 10;

--
-- AUTO_INCREMENT for table `addon_tiers`
--
ALTER TABLE `addon_tiers`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 29;

--
-- AUTO_INCREMENT for table `alacarte_factors`
--
ALTER TABLE `alacarte_factors`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 5;

--
-- AUTO_INCREMENT for table `cetak_base`
--
ALTER TABLE `cetak_base`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 20;

--
-- AUTO_INCREMENT for table `cetak_factors`
--
ALTER TABLE `cetak_factors`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `graduation_addons`
--
ALTER TABLE `graduation_addons`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT for table `overhead`
--
ALTER TABLE `overhead`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT for table `packages_fullservice`
--
ALTER TABLE `packages_fullservice`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 13;

--
-- AUTO_INCREMENT for table `packages_graduation`
--
ALTER TABLE `packages_graduation`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 7;

--
-- AUTO_INCREMENT for table `tbl_addons`
--
ALTER TABLE `tbl_addons`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 75;

--
-- AUTO_INCREMENT for table `tbl_cetak_prices`
--
ALTER TABLE `tbl_cetak_prices`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 248;

--
-- AUTO_INCREMENT for table `tbl_fs_prices`
--
ALTER TABLE `tbl_fs_prices`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 58;

--
-- AUTO_INCREMENT for table `tbl_multipliers`
--
ALTER TABLE `tbl_multipliers`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 8;

--
-- AUTO_INCREMENT for table `tbl_overhead`
--
ALTER TABLE `tbl_overhead`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addon_items`
--
ALTER TABLE `addon_items`
ADD CONSTRAINT `addon_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `addon_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addon_tiers`
--
ALTER TABLE `addon_tiers`
ADD CONSTRAINT `addon_tiers_ibfk_1` FOREIGN KEY (`addon_item_id`) REFERENCES `addon_items` (`id`) ON DELETE CASCADE;

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;