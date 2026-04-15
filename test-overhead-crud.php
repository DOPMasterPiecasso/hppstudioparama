#!/usr/bin/env php
<?php
/**
 * Overhead CRUD Test Script
 * Test: Add, Edit, Delete, Verify Persistence
 */

require_once __DIR__ . '/config/db.php';

echo "\n========================================\n";
echo "OVERHEAD CRUD TEST\n";
echo "========================================\n\n";

$pdo = getMySQLConnection();
$md = new MySQLMasterData($pdo);

// TEST 1: Load current data
echo "TEST 1: Load Current Data\n";
echo "---------------------------------\n";
$before = $md->getOverhead();
echo "Items in database:\n";
$itemCount = 0;
foreach ($before as $key => $val) {
    if ($key !== 'total') {
        echo "  - $key: Rp " . number_format($val, 0, ',', '.') . "\n";
        $itemCount++;
    }
}
echo "Total items: $itemCount\n";
echo "Total value: Rp " . number_format($before['total'], 0, ',', '.') . "\n";
echo "\n";

// TEST 2: Add new item
echo "TEST 2: Add New Item - TestAddItem2024\n";
echo "---------------------------------\n";
$newData = $before;
unset($newData['total']);
$newData['TestAddItem2024'] = 2000000;

echo "Sending to API:\n";
print_r(array_slice($newData, -3)); // Show last 3 items

$result = $md->updateOverhead($newData);
echo "\nResult from updateOverhead():\n";
echo "  Items returned: " . count(array_filter($result, function($k) { return $k !== 'total'; }, ARRAY_FILTER_USE_KEY)) . "\n";
echo "  New total: Rp " . number_format($result['total'], 0, ',', '.') . "\n";

// Verify persistence
$after = $md->getOverhead();
if (isset($after['TestAddItem2024']) && $after['TestAddItem2024'] == 2000000) {
    echo "✓ PASSED: Item added and persisted in database\n";
} else {
    echo "✗ FAILED: Item not found in database!\n";
}
echo "\n";

// TEST 3: Delete item
echo "TEST 3: Delete Item - marketing\n";
echo "---------------------------------\n";
$deleteData = $after;
unset($deleteData['total']);
unset($deleteData['marketing']); // DELETE this

echo "Deleting 'marketing' from data...\n";
echo "Total items before delete: " . count($deleteData) . "\n";

$result2 = $md->updateOverhead($deleteData);
echo "Result from updateOverhead():\n";
echo "  Items returned: " . count(array_filter($result2, function($k) { return $k !== 'total'; }, ARRAY_FILTER_USE_KEY)) . "\n";
echo "  New total: Rp " . number_format($result2['total'], 0, ',', '.') . "\n";

// Verify persistence
$after2 = $md->getOverhead();
if (!isset($after2['marketing'])) {
    echo "✓ PASSED: Item deleted and does NOT reappear\n";
} else {
    echo "✗ FAILED: Item was deleted but REAPPEARED!\n";
    echo "  Value: " . $after2['marketing'] . "\n";
}
echo "\n";

// TEST 4: Verify 'total' not in results
echo "TEST 4: Verify 'total' is NOT saved as item\n";
echo "---------------------------------\n";
$final = $md->getOverhead();
if (count($final) > 0) {
    $keys = array_keys($final);
    $hasTotalAsItem = false;
    foreach ($keys as $k) {
        if ($k !== 'total' && strtolower($k) === 'total') {
            $hasTotalAsItem = true;
            break;
        }
    }
    
    if ($hasTotalAsItem) {
        echo "✗ FAILED: 'total' found as separate item in database!\n";
    } else {
        echo "✓ PASSED: 'total' is only the sum, not a separate item\n";
    }
}

// TEST 5: Show final state
echo "\nTEST 5: Final Database State\n";
echo "---------------------------------\n";
echo "Items in database:\n";
$totalItems = 0;
$totalValue = 0;
foreach ($final as $key => $val) {
    if ($key !== 'total') {
        echo sprintf("  - %-25s: Rp %12s\n", $key, number_format($val, 0, ',', '.'));
        $totalItems++;
        $totalValue += $val;
    }
}
echo "---\n";
echo sprintf("  %-25s: Rp %12s\n", "TOTAL", number_format($totalValue, 0, ',', '.'));
echo "Total items: $totalItems\n\n";

echo "========================================\n";
echo "TEST COMPLETE\n";
echo "========================================\n\n";
?>
