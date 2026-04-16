<?php
/**
 * Migration Script: Graduation Data (JSON → MySQL)
 * 
 * Memigrasikan data dari data/graduation.json ke tabel MySQL:
 * - packages_graduation
 * - graduation_addons
 * - graduation_cetak
 */

require_once 'config/db.php';

$db = getMySQLConnection();
if (!$db) {
    die("❌ Error: Koneksi database gagal\n");
}

echo "========================================\n";
echo "Graduation Data Migration\n";
echo "JSON → MySQL\n";
echo "========================================\n\n";

// ============================================================
// 1. LOAD JSON FILE
// ============================================================
echo "📂 Membaca graduation.json...\n";

$jsonFile = __DIR__ . '/data/graduation.json';
if (!file_exists($jsonFile)) {
    die("❌ Error: File $jsonFile tidak ditemukan\n");
}

$jsonData = json_decode(file_get_contents($jsonFile), true);
if (!$jsonData) {
    die("❌ Error: Gagal membaca JSON file\n");
}

echo "✓ JSON dimuat berhasil\n\n";

// ============================================================
// 2. MIGRATE GRADUATION PACKAGES
// ============================================================
echo "📦 Migrasi Graduation Packages...\n";

try {
    // Clear existing data
    $db->exec("DELETE FROM packages_graduation");
    
    $stmt = $db->prepare("
        INSERT INTO packages_graduation 
        (package_key, name, price, description, color, display_order) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $order = 0;
    foreach ($jsonData['packages'] as $pkg) {
        $stmt->execute([
            $pkg['id'] ?? '',
            $pkg['name'] ?? '',
            (int)($pkg['price'] ?? 0),
            $pkg['desc'] ?? '',
            $pkg['color'] ?? '',
            $order++
        ]);
    }
    
    echo "✓ " . count($jsonData['packages']) . " paket tersimpan\n";
    
    // Show list
    $result = $db->query("SELECT id, package_key, name, price FROM packages_graduation ORDER BY display_order");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - [{$row['package_key']}] {$row['name']} (Rp " . number_format($row['price'], 0, ',', '.') . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================
// 3. MIGRATE GRADUATION ADD-ONS
// ============================================================
echo "\n📎 Migrasi Graduation Add-ons...\n";

try {
    // Clear existing data
    $db->exec("DELETE FROM graduation_addons");
    
    $stmt = $db->prepare("
        INSERT INTO graduation_addons 
        (addon_key, name, price, addon_type) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($jsonData['addons'] as $addon) {
        $stmt->execute([
            $addon['id'] ?? '',
            $addon['name'] ?? '',
            (int)($addon['price'] ?? 0),
            'addon'
        ]);
    }
    
    echo "✓ " . count($jsonData['addons']) . " add-on tersimpan\n";
    
    // Show list
    $result = $db->query("SELECT id, addon_key, name, price FROM graduation_addons ORDER BY addon_key");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - [{$row['addon_key']}] {$row['name']} (Rp " . number_format($row['price'], 0, ',', '.') . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================
// 4. MIGRATE GRADUATION CETAK
// ============================================================
echo "\n🖨️  Migrasi Graduation Cetak...\n";

try {
    // Clear existing data
    $db->exec("DELETE FROM graduation_cetak");
    
    $stmt = $db->prepare("
        INSERT INTO graduation_cetak 
        (cetak_key, name, price_per_unit, description) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($jsonData['cetak'] as $cetak) {
        $stmt->execute([
            $cetak['id'] ?? '',
            $cetak['name'] ?? '',
            (int)($cetak['price'] ?? 0),
            ''
        ]);
    }
    
    echo "✓ " . count($jsonData['cetak']) . " cetak item tersimpan\n";
    
    // Show list
    $result = $db->query("SELECT id, cetak_key, name, price_per_unit FROM graduation_cetak ORDER BY cetak_key");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "  - [{$row['cetak_key']}] {$row['name']} (Rp " . number_format($row['price_per_unit'], 0, ',', '.') . ")\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================
// 5. VERIFICATION
// ============================================================
echo "\n✅ Verification:\n";

$pkgCount = $db->query("SELECT COUNT(*) FROM packages_graduation")->fetchColumn();
$addonCount = $db->query("SELECT COUNT(*) FROM graduation_addons")->fetchColumn();
$cetakCount = $db->query("SELECT COUNT(*) FROM graduation_cetak")->fetchColumn();

echo "  • packages_graduation: $pkgCount rows\n";
echo "  • graduation_addons: $addonCount rows\n";
echo "  • graduation_cetak: $cetakCount rows\n";

echo "\n" . str_repeat("=", 40) . "\n";
echo "✅ Migrasi selesai!\n";
echo str_repeat("=", 40) . "\n";
?>
