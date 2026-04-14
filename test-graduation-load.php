<?php
/**
 * Test script to verify graduation data loading
 */
session_start();
require_once __DIR__ . '/config/db.php';

echo "=== GRADUATION DATA LOADING TEST ===\n\n";

try {
    $db = getDB();
    
    // Test 1: Load graduation directly
    echo "1. Loading graduation from database:\n";
    $graduation = $db->getGraduation();
    echo "   - Packages: " . count($graduation['packages'] ?? []) . " items\n";
    echo "   - Addons: " . count($graduation['addons'] ?? []) . " items\n";
    echo "   - Cetak: " . count($graduation['cetak'] ?? []) . " items\n";
    echo "\n";
    
    // Test 2: Verify price values
    echo "2. Verification of package prices:\n";
    foreach ($graduation['packages'] as $pkg) {
        echo "   - {$pkg['id']}: {$pkg['name']} = Rp " . number_format($pkg['price'], 0, ',', '.') . "\n";
    }
    echo "\n";
    
    // Test 3: Check JSON file directly
    $path = __DIR__ . '/data/graduation.json';
    echo "3. Direct JSON file check:\n";
    echo "   - File exists: " . (file_exists($path) ? "YES" : "NO") . "\n";
    if (file_exists($path)) {
        $content = json_decode(file_get_contents($path), true);
        echo "   - JSON valid: " . ($content !== null ? "YES" : "NO") . "\n";
        echo "   - Total keys: " . count($content) . "\n";
    }
    echo "\n";
    
    // Test 4: Verify settings loading (as used in header.php)
    echo "4. Settings loading (for header.php):\n";
    $settings = $db->getSettings();
    echo "   - Settings keys: " . count($settings) . "\n";
    
    // Test 5: Format as would be in DB_SETTINGS
    echo "5. DB_SETTINGS format check:\n";
    $allSettings = [];
    $graduation = $db->getGraduation();
    $allSettings['grad_packages'] = json_encode($graduation);
    $decoded = json_decode($allSettings['grad_packages'], true);
    echo "   - Encoded/Decoded successfully: " . ($decoded === $graduation ? "YES" : "NO") . "\n";
    echo "   - Package count in DB_SETTINGS: " . count($decoded['packages'] ?? []) . "\n";
    
    echo "\n✓ All tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
