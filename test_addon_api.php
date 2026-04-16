<?php
// Simple test to diagnose addon API issues

// Start session and set fake auth
session_start();
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'admin',
    'role' => 'admin'
];

// Test the getDB and getAddons functions
require_once 'config/db.php';

echo "=== ADDON API DIAGNOSTIC ===\n\n";

echo "1. Testing MySQL Connection...\n";
$pdo = getMySQLConnection();
if ($pdo) {
    echo "✓ MySQL connection successful\n";
} else {
    echo "✗ MySQL connection failed\n";
}

echo "\n2. Testing getDB() function...\n";
try {
    $db = getDB();
    echo "✓ getDB() successful - Class: " . get_class($db) . "\n";
} catch (Exception $e) {
    echo "✗ getDB() failed: " . $e->getMessage() . "\n";
    exit;
}

echo "\n3. Testing getAddons() function...\n";
try {
    $addons = $db->getAddons();
    echo "✓ getAddons() successful\n";
    echo "   Structure: " . json_encode(array_keys($addons ?? []), JSON_UNESCAPED_UNICODE) . "\n";
    
    // Check if it has data
    $totalItems = 0;
    if (is_array($addons)) {
        foreach ($addons as $category => $items) {
            $count = is_array($items) ? count($items) : 0;
            $totalItems += $count;
            echo "   - $category: $count items\n";
        }
    }
    
    echo "   Total items: $totalItems\n";
    
    if ($totalItems === 0) {
        echo "   ⚠ WARNING: No addon items found!\n";
    }
    
} catch (Exception $e) {
    echo "✗ getAddons() failed: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit;
}

echo "\n4. Testing JSON encoding of response...\n";
$response = [
    'success' => true,
    'data' => $addons
];
$json = json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
echo "✓ JSON encoded successfully\n";
echo "   Length: " . strlen($json) . " bytes\n";
echo "   Valid JSON: " . (json_decode($json) !== null ? "Yes" : "No") . "\n";

echo "\n5. Simulating full API response...\n";
header('Content-Type: application/json; charset=utf-8');
http_response_code(200);
echo $json;
?>
