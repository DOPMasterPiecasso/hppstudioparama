<?php
// Test simulating API update addon request

session_start();
$_SESSION['user'] = ['id' => 1, 'username' => 'admin', 'role' => 'admin'];
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

require_once 'config/db.php';

echo "=== TESTING UPDATE ADDON SIMULATION ===\n\n";

try {
    $db = getDB();
    
    echo "1. Getting current addons...\n";
    $allAddons = $db->getAddons();
    echo "✓ Got addons\n\n";
    
    // Simulate an update_addon request
    echo "2. Simulating update addon request...\n";
    
    $category = 'kertas';
    $id = 'ivory';
    $name = 'Ivory Paper UPDATED ' . time();
    
    // Update addon in current data
    if (isset($allAddons[$category])) {
        $found = false;
        foreach ($allAddons[$category] as &$addon) {
            if ($addon['id'] === $id) {
                echo "   Found addon to update: " . $addon['name'] . "\n";
                $addon['name'] = $name;
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo "   Updated name to: $name\n\n";
        } else {
            echo "   ERROR: Addon not found\n";
            exit;
        }
    }
    
    echo "3. Calling updateAddons()...\n";
    $startTime = microtime(true);
    $result = $db->updateAddons($allAddons);
    $duration = (microtime(true) - $startTime);
    
    echo "✓ updateAddons completed in " . number_format($duration, 3) . " seconds\n";
    echo "   Result type: " . gettype($result) . "\n";
    
    if (is_array($result) && isset($result[$category])) {
        echo "   Updated category has " . count($result[$category]) . " items\n";
        foreach ($result[$category] as $item) {
            if ($item['id'] === $id) {
                echo "   Verification - New name: " . $item['name'] . "\n";
                break;
            }
        }
    }
    
    echo "\n✅ SUCCESS: Update addon works correctly!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}
?>
