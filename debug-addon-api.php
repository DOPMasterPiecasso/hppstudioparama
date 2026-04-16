<?php
/**
 * Addon API Debug Endpoint
 * Shows what /api/addons.php is actually returning
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== ADDON API DEBUG ===\n\n";

// First, let's check if we can connect to the database directly
echo "Test 0: Checking database connection\n";
echo str_repeat("-", 50) . "\n";

require_once __DIR__ . '/config/db.php';

try {
    $pdo = getMySQLConnection();
    if ($pdo) {
        echo "✓ Database connection OK\n";
        
        // Try to get addons
        $db = getDB();
        $addons = $db->getAddons();
        
        echo "✓ getAddons() worked\n";
        echo "Categories found: " . count($addons) . "\n";
        
        if (isset($addons['kertas'])) {
            echo "✓ Kertas category exists\n";
            $laminasiItems = array_filter($addons['kertas'], function($item) {
                return $item['id'] === 'laminasi';
            });
            if (!empty($laminasiItems)) {
                echo "✓ Laminasi Paper found\n";
                print_r(current($laminasiItems));
            }
        }
    } else {
        echo "✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n\n";
echo "Test 1: Testing /api/addons.php?action=get_all via HTTP\n";
echo str_repeat("-", 50) . "\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/api/addons.php?action=get_all");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "CURL ERROR: $error\n";
} else {
    echo "HTTP Status: $httpCode\n";
    echo "Response length: " . strlen($response) . " bytes\n";
    
    if ($httpCode == 302) {
        echo "⚠ Got redirect (302) - probably auth requiredIssue.\n";
        $lines = explode("\n", $response);
        echo "First few lines:\n";
        foreach (array_slice($lines, 0, 5) as $line) {
            echo "  " . trim($line) . "\n";
        }
    } else {
        echo "Response snippet:\n";
        echo substr($response, 0, 300) . "\n";
    }
}

?>

