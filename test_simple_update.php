<?php
// Test updateAddons dengan debugging sederhana

session_start();
$_SESSION['user'] = ['id' => 1, 'username' => 'admin', 'role' => 'admin'];

require_once 'config/db.php';

echo "Step 1: Getting DB\n";
flush();

$db = getDB();
echo "Step 2: DB obtained\n";
flush();

echo "Step 3: Getting addons\n";
flush();

$allAddons = $db->getAddons();
echo "Step 4: Addons obtained. Categories: " . implode(", ", array_keys($allAddons)) . "\n";
flush();

// Modify one
echo "Step 5: Modifying addon\n";
flush();

$allAddons['kertas'][0]['name'] = 'TEST NAME ' . time();
echo "Step 6: Addon modified\n";
flush();

echo "Step 7: About to call updateAddons...\n";
flush();

try {
    echo "Step 7a: Calling updateAddons\n";
    ob_flush();
    flush();
    
    $result = $db->updateAddons($allAddons);
    
    echo "Step 8: updateAddons returned\n";
    ob_flush();
    flush();
    
    echo "✅ SUCCESS!\n";
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
    ob_flush();
    flush();
}
?>
