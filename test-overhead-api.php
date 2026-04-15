<?php
/**
 * Test Script: Simulate frontend overhead save operation
 * Tests the complete flow: collect data -> POST to API -> reload
 */

session_start();
// Mock user session
$_SESSION['user'] = ['id' => 1, 'name' => 'Test', 'role' => 'admin'];

require_once __DIR__ . '/config/db.php';

echo "=== OVERHEAD API TEST ===\n\n";

// Step 1: Get current overhead from database
echo "STEP 1: Fetch current overhead from database\n";
$pdo = getMySQLConnection();
$md = new MySQLMasterData($pdo);
$currentOverhead = $md->getOverhead();
echo "Current overhead in database:\n";
print_r($currentOverhead);
echo "\n";

// Step 2: Simulate user adding new item and deleting one
echo "STEP 2: Simulate user changes\n";
$modifiedOverhead = $currentOverhead;
unset($modifiedOverhead['total']);
unset($modifiedOverhead['freelance']); // Delete this
$modifiedOverhead['newtestitem'] = 3000000; // Add new
echo "Modified overhead (freelance deleted, newtestitem added):\n";
print_r($modifiedOverhead);
echo "\n";

// Step 3: Simulate POST request to API
echo "STEP 3: POST to /api/master-data.php\n";
$postData = json_encode([
    'type' => 'overhead',
    'data' => $modifiedOverhead
]);
echo "POST payload:\n";
echo $postData . "\n";
echo "\n";

// Step 4: Call the API update function directly
echo "STEP 4: Call updateMasterData directly\n";
try {
    $response = updateMasterData($pdo, 'overhead', json_decode($postData, true));
    echo "API Response:\n";
    print_r($response);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "\n";

// Step 5: Verify database was updated
echo "STEP 5: Verify database after update\n";
$verifyOverhead = $md->getOverhead();
echo "Overhead in database after update:\n";
print_r($verifyOverhead);
echo "\n";

// Check if update persisted
echo "VERIFICATION:\n";
echo "- freelance exists? " . (isset($verifyOverhead['freelance']) ? 'YES (BAD!)' : 'NO (GOOD!)') . "\n";
echo "- newtestitem exists? " . (isset($verifyOverhead['newtestitem']) ? 'YES (GOOD!)' : 'NO (BAD!)') . "\n";
echo "- Total: Rp " . number_format($verifyOverhead['total'], 0, ',', '.') . "\n";

// Helper function from api/master-data.php
function updateMasterData($pdo, $type, $body) {
    switch ($type) {
        case 'overhead':
            return updateOverhead($pdo, $body['data'] ?? []);
        default:
            throw new Exception('Unknown update type: ' . $type);
    }
}

function updateOverhead($pdo, $data) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->updateOverhead($data);
}

?>
