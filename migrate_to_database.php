<?php
/**
 * Parama HPP - Database Migration Script
 * Migrasi data dari HTML ke Database
 * Run this file once: php migrate_to_database.php
 */

require_once __DIR__ . '/config/db.php';

$pdo = getDB();

echo "Memulai migrasi data Parama HPP...\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // ==========================================
    // 1. OVERHEAD DATA
    // ==========================================
    echo "1. Migrasi Overhead...\n";
    $overhead_data = [
        ['Designer', 8000000],
        ['Marketing', 3000000],
        ['Creative Prod.', 5000000],
        ['Project Mgr', 6000000],
        ['Social Media', 2000000],
        ['Freelance', 1500000],
        ['Operasional', 4500000],
    ];

    $stmt = $pdo->prepare("INSERT INTO overhead (category, amount, description) VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE amount = VALUES(amount)");
    foreach ($overhead_data as [$category, $amount]) {
        $stmt->execute([$category, $amount, null]);
    }
    echo "   ✓ " . count($overhead_data) . " kategori overhead tersimpan\n\n";

    // ==========================================
    // 2. FULL SERVICE PACKAGES
    // ==========================================
    echo "2. Migrasi Full Service Packages...\n";
    $fs_packages = [
        // Handy Book
        ['handy', 25, 50, 399000, 70],
        ['handy', 51, 75, 389000, 75],
        ['handy', 76, 100, 379000, 80],
        ['handy', 101, 150, 369000, 90],
        
        // Minimal Book
        ['minimal', 25, 50, 349000, 65],
        ['minimal', 51, 75, 339000, 70],
        ['minimal', 76, 100, 329000, 75],
        ['minimal', 101, 150, 319000, 85],
        
        // Large Book
        ['large', 25, 50, 449000, 75],
        ['large', 51, 75, 439000, 85],
        ['large', 76, 100, 429000, 95],
        ['large', 101, 150, 419000, 105],
    ];

    $stmt = $pdo->prepare("INSERT INTO packages_fullservice (package_type, min_students, max_students, price_per_book, max_pages)
                           VALUES (?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE price_per_book = VALUES(price_per_book), max_pages = VALUES(max_pages)");
    $fs_count = 0;
    foreach ($fs_packages as [$type, $min, $max, $price, $pages]) {
        $stmt->execute([$type, $min, $max, $price, $pages]);
        $fs_count++;
    }
    echo "   ✓ " . $fs_count . " paket full service tersimpan\n\n";

    // ==========================================
    // 3. À LA CARTE PACKAGES
    // ==========================================
    echo "3. Migrasi À La Carte Packages...\n";
    $alacarte_packages = [
        ['ebook', 'E-Book Package', 'Foto+Editing+Desain, output file digital. Tanpa cetak fisik.', 'per_siswa', null, null, 0.68, '62–68%', 1],
        ['editcetak', 'Edit+Desain+Cetak', 'Klien bawa foto sendiri. Parama handle editing, layout, cetak & kirim.', 'per_siswa', null, null, 0.58, '55–62%', 0],
        ['fotohalf', 'Foto Only (½ Hari)', 'Sesi foto max ~75 siswa. Fotografer + fashion stylist.', 'flat_range', 3500000, 5000000, null, '55–65%', 0],
        ['fotofull', 'Foto Only (Full Day)', 'Sesi foto 76–150+ siswa. Full team seharian.', 'flat_range', 6000000, 9000000, null, '55–65%', 0],
        ['videodrone', 'Drone Video', 'Video drone 1–2 menit.', 'flat_fixed', 1500000, 1500000, null, null, 0],
        ['videodoc', 'Docudrama Video', 'Video cerita angkatan 5–10 menit.', 'flat_fixed', 3000000, 3000000, null, null, 0],
        ['desain', 'Desain Only', 'Klien bawa semua konten. Parama hanya layout buku.', 'per_siswa', null, null, 0.55, '55–65%', 0],
        ['cetakonly', 'Cetak Only', 'Klien sudah punya file siap cetak. Parama cetak & kirim saja.', 'per_siswa', null, null, 0.35, '30–45%', 0],
    ];

    $stmt = $pdo->prepare("INSERT INTO packages_alacarte (code, name, description, price_type, price_min, price_max, factor, margin_target, is_featured)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE name = VALUES(name)");
    $alc_count = 0;
    foreach ($alacarte_packages as $pkg) {
        // Type casting untuk keamanan
        $stmt->execute([
            $pkg[0], // code
            $pkg[1], // name
            $pkg[2], // description
            $pkg[3], // price_type
            $pkg[4] ? (int)$pkg[4] : null, // price_min
            $pkg[5] ? (int)$pkg[5] : null, // price_max
            $pkg[6] ? (float)$pkg[6] : null, // factor
            $pkg[7], // margin_target
            (int)$pkg[8] // is_featured as integer
        ]);
        $alc_count++;
    }
    echo "   ✓ " . $alc_count . " paket à la carte tersimpan\n\n";

    // ==========================================
    // 4. À LA CARTE FACTORS
    // ==========================================
    echo "4. Migrasi À La Carte Factors...\n";
    $alc_factors = [
        ['ebook', 0.68],
        ['editcetak', 0.58],
        ['desain', 0.55],
        ['cetakonly', 0.35],
    ];

    $stmt = $pdo->prepare("INSERT INTO alacarte_factors (package_code, factor)
                           VALUES (?, ?)
                           ON DUPLICATE KEY UPDATE factor = VALUES(factor)");
    foreach ($alc_factors as [$code, $factor]) {
        $stmt->execute([$code, $factor]);
    }
    echo "   ✓ À la carte factors tersimpan\n\n";

    // ==========================================
    // 5. ADDON CATEGORIES & ITEMS
    // ==========================================
    echo "5. Migrasi Add-on Categories & Items...\n";
    
    $addon_categories = ['Finishing', 'Kertas', 'Halaman Tambahan', 'Video', 'Packaging Standard', 'Custom Box'];
    $cat_ids = [];
    
    $stmt_cat = $pdo->prepare("INSERT INTO addon_categories (category_name) VALUES (?)");
    foreach ($addon_categories as $cat) {
        $stmt_cat->execute([$cat]);
        $cat_ids[$cat] = $pdo->lastInsertId();
    }

    // Add-on Items dengan Tiers
    $addon_items_data = [
        'Finishing' => [
            ['Hardcover', 'tiered', null, [
                ['25–75 buku', 25, 75, 75000],
                ['76–150 buku', 76, 150, 65000],
                ['>151 buku', 151, null, 55000],
            ]],
            ['Softcover', 'tiered', null, [
                ['25–75 buku', 25, 75, 45000],
                ['76–150 buku', 76, 150, 35000],
                ['>151 buku', 151, null, 30000],
            ]],
        ],
        'Kertas' => [
            ['Art Paper 260gsm', 'tiered', null, [
                ['25–50', 25, 50, 5000],
                ['51–100', 51, 100, 4000],
                ['101–150', 101, 150, 3500],
                ['>151', 151, null, 3000],
            ]],
            ['Glossy 230gsm', 'tiered', null, [
                ['25–50', 25, 50, 3500],
                ['51–100', 51, 100, 3000],
                ['101–150', 101, 150, 2500],
                ['>151', 151, null, 2000],
            ]],
        ],
        'Halaman Tambahan' => [
            ['Halaman Tambahan', 'extra_hal', null, [
                ['25–50 order', 25, 50, 15000],
                ['51–100 order', 51, 100, 12000],
                ['101–150 order', 101, 150, 10000],
                ['>151 order', 151, null, 8000],
            ]],
        ],
        'Video' => [
            ['Drone Video', 'flat_video', 1500000, []],
            ['Docudrama Video', 'flat_video', 3000000, []],
        ],
        'Packaging Standard' => [
            ['Slide Box', 'tiered', null, [
                ['25–50', 25, 50, 25000],
                ['51–100', 51, 100, 20000],
                ['101–150', 101, 150, 18000],
                ['151–200', 151, 200, 15000],
                ['>200', 201, null, 12000],
            ]],
        ],
        'Custom Box' => [
            ['Custom Printed Box', 'tiered', null, [
                ['25–50', 25, 50, 45000],
                ['51–100', 51, 100, 38000],
                ['101–150', 101, 150, 32000],
                ['151–200', 151, 200, 28000],
                ['>200', 201, null, 24000],
            ]],
        ],
    ];

    $stmt_item = $pdo->prepare("INSERT INTO addon_items (category_id, name, addon_type, flat_price)
                                VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE addon_type = VALUES(addon_type)");
    $stmt_tier = $pdo->prepare("INSERT INTO addon_tiers (addon_item_id, tier_label, min_quantity, max_quantity, price)
                                VALUES (?, ?, ?, ?, ?)");

    foreach ($addon_items_data as $category => $items) {
        $cat_id = $cat_ids[$category];
        foreach ($items as [$name, $type, $flat_price, $tiers]) {
            $stmt_item->execute([$cat_id, $name, $type, $flat_price]);
            $item_id = $pdo->lastInsertId();
            
            foreach ($tiers as [$label, $min, $max, $price]) {
                $stmt_tier->execute([$item_id, $label, $min, $max, $price]);
            }
        }
    }
    echo "   ✓ Add-on categories dan items tersimpan\n\n";

    // ==========================================
    // 6. CETAK FACTORS
    // ==========================================
    echo "6. Migrasi Cetak Factors...\n";
    $cetak_factors = [
        ['handy', 1.0, 'Faktor standar untuk Handy Book'],
        ['minimal', 0.85, 'Faktor untuk Minimal Square Book'],
        ['large', 1.2, 'Faktor untuk Large B4 Book'],
    ];

    $stmt = $pdo->prepare("INSERT INTO cetak_factors (package_type, factor, description)
                           VALUES (?, ?, ?)
                           ON DUPLICATE KEY UPDATE factor = VALUES(factor)");
    foreach ($cetak_factors as $factor) {
        $stmt->execute($factor);
    }
    echo "   ✓ Cetak factors tersimpan\n\n";

    // ==========================================
    // 7. CETAK BASE PRICING
    // ==========================================
    echo "7. Migrasi Cetak Base Pricing...\n";
    $cetak_base_data = [
        // 25-100 siswa
        ['25–100 siswa', 25, 100, 30, 90000],
        ['25–100 siswa', 25, 100, 40, 110000],
        ['25–100 siswa', 25, 100, 50, 125000],
        ['25–100 siswa', 25, 100, 60, 140000],
        ['25–100 siswa', 25, 100, 70, 155000],
        ['25–100 siswa', 25, 100, 80, 170000],
        ['25–100 siswa', 25, 100, 90, 185000],
        
        // 101-200 siswa
        ['101–200 siswa', 101, 200, 30, 80000],
        ['101–200 siswa', 101, 200, 40, 95000],
        ['101–200 siswa', 101, 200, 50, 110000],
        ['101–200 siswa', 101, 200, 60, 125000],
        ['101–200 siswa', 101, 200, 70, 140000],
        ['101–200 siswa', 101, 200, 80, 155000],
        
        // >200 siswa
        ['>200 siswa', 201, 500, 30, 70000],
        ['>200 siswa', 201, 500, 40, 85000],
        ['>200 siswa', 201, 500, 50, 100000],
        ['>200 siswa', 201, 500, 60, 115000],
        ['>200 siswa', 201, 500, 70, 130000],
        ['>200 siswa', 201, 500, 80, 145000],
    ];

    $stmt = $pdo->prepare("INSERT INTO cetak_base (range_label, min_students, max_students, pages_count, base_price)
                           VALUES (?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE base_price = VALUES(base_price)");
    $cetak_count = 0;
    foreach ($cetak_base_data as [$label, $min, $max, $pages, $price]) {
        $stmt->execute([$label, $min, $max, $pages, $price]);
        $cetak_count++;
    }
    echo "   ✓ " . $cetak_count . " entry cetak base pricing tersimpan\n\n";

    // ==========================================
    // 8. GRADUATION PACKAGES
    // ==========================================
    echo "8. Migrasi Graduation Packages...\n";
    $graduation_packages = [
        ['gphv', 'Photo & Video', '2 Fotografer + 1 Videografer, 50 foto edited, video cinematic 2–4 mnt, G-Drive, 4 jam coverage, transport jabodetabek', 4500000, 'acc', 1, 'Jabodetabek'],
        ['gvideo', 'Video Only', '1 Videografer, video cinematic 2–5 mnt, G-Drive, 4 jam coverage, transport jabodetabek', 2000000, '', 0, 'Jabodetabek'],
        ['gphoto', 'Photo Only', '2 Fotografer, 100 foto edited, G-Drive, 4 jam coverage, transport jabodetabek', 2750000, '', 0, 'Jabodetabek'],
        ['gbooth', 'Photo Booth', '1–2 Crew profesional, backdrop wisuda, lighting studio, Selfiebox Machine, unlimited print 4R, max 3 jam, softcopy + QR Code realtime, transport jabodetabek', 3850000, '', 0, 'Jabodetabek'],
        ['g360', 'Glamation 360°', '1–2 Crew profesional, MP4, LCD 50in preview, GoPro/iPhone 12 Pro, overlay design free, max 3 jam, QR Code realtime, transport jabodetabek', 4100000, '', 0, 'Jabodetabek'],
        ['gcomplete', 'Complete Package', 'Photo + Video + Photo Booth, transport jabodetabek', 7750000, 'feat', 1, 'Jabodetabek'],
    ];

    $stmt = $pdo->prepare("INSERT INTO packages_graduation (package_key, name, description, price, color_scheme, is_featured, transport_included)
                           VALUES (?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE price = VALUES(price)");
    foreach ($graduation_packages as $pkg) {
        // Type casting untuk keamanan
        $stmt->execute([
            $pkg[0], // package_key
            $pkg[1], // name
            $pkg[2], // description
            (int)$pkg[3], // price
            $pkg[4] ?: null, // color_scheme
            (int)$pkg[5], // is_featured
            $pkg[6] // transport_included
        ]);
    }
    echo "   ✓ " . count($graduation_packages) . " paket graduation tersimpan\n\n";

    // ==========================================
    // 9. GRADUATION ADD-ONS
    // ==========================================
    echo "9. Migrasi Graduation Add-ons...\n";
    $grad_addons = [
        ['gad-makeup', 'Makeup Artist', 850000, 'addon', 'per event'],
        ['gad-dress', 'Dress Styling', 1200000, 'addon', 'per event'],
        ['gad-prepp', 'Pre-PP (Hari Sebelumnya)', 500000, 'addon', 'per session'],
        ['gad-extrahr', 'Extra Hour', 750000, 'addon', 'per jam'],
        ['gcetak-4r', 'Foto 4R', 15000, 'cetak', 'per lembar'],
        ['gcetak-8r', 'Foto 8R', 35000, 'cetak', 'per lembar'],
        ['gcetak-dvd', 'DVD Digital', 25000, 'cetak', 'per set'],
    ];

    $stmt = $pdo->prepare("INSERT INTO graduation_addons (addon_key, name, price, addon_type, unit)
                           VALUES (?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE price = VALUES(price)");
    foreach ($grad_addons as $addon) {
        $stmt->execute($addon);
    }
    echo "   ✓ " . count($grad_addons) . " graduation add-ons tersimpan\n\n";

    // ==========================================
    // SUMMARY
    // ==========================================
    echo str_repeat("=", 60) . "\n";
    echo "✓ MIGRASI DATABASE BERHASIL!\n";
    echo str_repeat("=", 60) . "\n\n";
    
    echo "Data yang berhasil dimigrasikan:\n";
    echo "• Overhead: 7 kategori\n";
    echo "• Full Service Packages: " . $fs_count . " paket\n";
    echo "• À La Carte Packages: " . $alc_count . " paket\n";
    echo "• Add-on Items: 15+ item dengan tier pricing\n";
    echo "• Cetak Base: " . $cetak_count . " entry pricing\n";
    echo "• Graduation Packages: " . count($graduation_packages) . " paket\n";
    echo "• Graduation Add-ons: " . count($grad_addons) . " add-on\n";
    echo "\nDatabase siap digunakan!\n";
    echo "Next: Buat API endpoints untuk membaca/update data ini.\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
