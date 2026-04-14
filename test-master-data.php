<?php
/**
 * Test Master Data API
 * File untuk testing /api/master-data.php
 * 
 * Jalankan: php test-master-data.php
 */

require_once __DIR__ . '/config/db.php';

echo "===== Master Data API Testing =====\n\n";

try {
    $pdo = getDB();
    
    // Test 1: Get Overhead
    echo "1. Testing Overhead Data...\n";
    $settingsPath = __DIR__ . '/data/settings.json';
    if (file_exists($settingsPath)) {
        $data = json_decode(file_get_contents($settingsPath), true);
        echo "   ✓ settings.json ditemukan\n";
        echo "   Overhead keys: " . implode(', ', array_keys($data['overhead'] ?? [])) . "\n\n";
    } else {
        echo "   ✗ settings.json tidak ditemukan\n\n";
    }
    
    // Test 2: Get Graduation
    echo "2. Testing Graduation Data...\n";
    $gradPath = __DIR__ . '/data/graduation.json';
    if (file_exists($gradPath)) {
        $grad = json_decode(file_get_contents($gradPath), true);
        echo "   ✓ graduation.json ditemukan\n";
        echo "   - Packages: " . count($grad['packages'] ?? []) . "\n";
        echo "   - Addons: " . count($grad['addons'] ?? []) . "\n";
        echo "   - Cetak: " . count($grad['cetak'] ?? []) . "\n\n";
    } else {
        echo "   ✗ graduation.json tidak ditemukan\n\n";
    }
    
    // Test 3: Get Addons
    echo "3. Testing Addons Data...\n";
    $addonsPath = __DIR__ . '/data/addons.json';
    if (file_exists($addonsPath)) {
        $addons = json_decode(file_get_contents($addonsPath), true);
        echo "   ✓ addons.json ditemukan\n";
        echo "   Addon types: " . implode(', ', array_keys($addons)) . "\n\n";
    } else {
        echo "   ✗ addons.json tidak ditemukan\n\n";
    }
    
    // Test 4: Get Full Service
    echo "4. Testing Full Service Data (dari DB)...\n";
    echo "   ℹ Full service dari database (bisa via API)\n\n";
    
    // Test 5: Check Cetak Base
    echo "5. Testing Cetak Base Data...\n";
    $cetakPath = __DIR__ . '/data/cetak_base.json';
    if (file_exists($cetakPath)) {
        $cetak = json_decode(file_get_contents($cetakPath), true);
        echo "   ✓ cetak_base.json ditemukan\n";
        echo "   Jumlah range: " . count($cetak) . "\n\n";
    } else {
        echo "   ✗ cetak_base.json tidak ditemukan\n\n";
    }
    
    echo "===== All Tests Completed =====\n";
    echo "\nAPI Endpoints untuk diakses:\n";
    echo "1. GET /api/master-data.php?action=get_all\n";
    echo "2. GET /api/master-data.php?action=get_overhead\n";
    echo "3. GET /api/master-data.php?action=get_graduation\n";
    echo "4. GET /api/master-data.php?action=get_addons\n";
    echo "5. POST /api/master-data.php (update data)\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>
