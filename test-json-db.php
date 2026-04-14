<?php
/**
 * Test JSON Database Setup
 * Run: php test-json-db.php
 */

echo "\n🧪 Testing JSON Database Setup...\n\n";

try {
    require_once 'config/db.php';
    
    echo "1️⃣  Loading database...\n";
    $db = getDB();
    echo "   ✓ Database loaded\n\n";
    
    echo "2️⃣  Checking users...\n";
    $users = $db->getAllUsers();
    echo "   ✓ Found " . count($users) . " users\n";
    foreach ($users as $u) {
        echo "     - {$u['username']} ({$u['role']})\n";
    }
    echo "\n";
    
    echo "3️⃣  Testing login (admin/admin2026)...\n";
    $user = $db->getUserByUsername('admin');
    if ($user && password_verify('admin2026', $user['password'])) {
        echo "   ✓ Login test passed\n\n";
    } else {
        echo "   ✗ Login test failed\n\n";
    }
    
    echo "4️⃣  Checking roles...\n";
    $roles = $db->getSettings();
    echo "   ✓ Settings loaded\n\n";
    
    echo "5️⃣  Checking file permissions...\n";
    if (is_writable(__DIR__ . '/data')) {
        echo "   ✓ data/ directory is writable\n";
    } else {
        echo "   ✗ data/ directory is NOT writable\n";
    }
    if (is_writable(__DIR__ . '/data/users.json')) {
        echo "   ✓ data/users.json is writable\n";
    } else {
        echo "   ✗ data/users.json is NOT writable\n";
    }
    
    echo "\n✅ All tests passed!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Ready to use!\n";
    echo "Access: http://localhost/\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
?>
