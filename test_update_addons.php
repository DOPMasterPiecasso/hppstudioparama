<?php
// Test updateAddons to find the 500 error

session_start();
$_SESSION['user'] = ['id' => 1, 'username' => 'admin', 'role' => 'admin'];

require_once 'config/db.php';

echo "=== TESTING UPDATE ADDONS ===\n\n";

try {
    $db = getDB();
    
    echo "1. Getting current addons...\n";
    $allAddons = $db->getAddons();
    echo "✓ Got " . count($allAddons) . " categories\n\n";
    
    echo "2. Testing single addon update...\n";
    // Try to update a simple addon
    $testData = $allAddons;
    
    // Modify one addon name
    if (isset($testData['kertas']) && count($testData['kertas']) > 0) {
        $testData['kertas'][0]['name'] = 'Test Updated Name ' . time();
        echo "   Modified: " . $testData['kertas'][0]['name'] . "\n";
    }
    
    echo "\n3. Calling updateAddons()...\n";
    $result = $db->updateAddons($testData);
    
    echo "✓ updateAddons executed\n";
    echo "   Result type: " . gettype($result) . "\n";
    
    if (is_array($result)) {
        echo "   Result categories: " . count($result) . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
}
?>
