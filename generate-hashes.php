<?php
/**
 * Generate password hashes for users.json
 * Run: php generate-hashes.php
 */

$passwords = [
    'admin' => 'admin2026',
    'manager' => 'parama2026',
    'staff' => 'staff123'
];

echo "\n🔐 Password Hashes for users.json:\n\n";

foreach ($passwords as $user => $pass) {
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    echo "User: $user\n";
    echo "Password: $pass\n";
    echo "Hash: $hash\n";
    echo "\n---\n\n";
}
?>
