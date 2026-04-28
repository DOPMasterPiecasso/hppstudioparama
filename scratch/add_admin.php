<?php
require_once __DIR__ . '/../config/db.php';

try {
    $pdo = getMySQLConnection();
    if (!$pdo) {
        die("Connection failed\n");
    }

    $username = 'admin';
    $password = 'admin123';
    $name = 'Admin';
    $role_id = 1; // Administrator

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        // Update existing admin password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, name = ?, role_id = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $name, $role_id, $username]);
        echo "User 'admin' already existed. Password updated to 'admin123'.\n";
    } else {
        // Create new admin
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, name, role_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $name, $role_id]);
        echo "User 'admin' created successfully with password 'admin123'.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
