<?php
/**
 * Initialize Default Roles and Users (JSON Based)
 * Run: php init-auth.php
 */

require_once 'config/db.php';

try {
    $db = getDB();
    
    echo "\n🔐 Initializing Roles and Users...\n";
    
    // 1. Add default roles
    $roles = [
        ['admin', 'Administrator', ['create_user', 'edit_user', 'delete_user', 'view_reports', 'manage_settings']],
        ['manager', 'Manager', ['create_penawaran', 'edit_penawaran', 'view_reports']],
        ['staff', 'Staff', ['create_penawaran', 'view_penawaran']]
    ];
    
    foreach ($roles as $role) {
        $roleExists = $db->getRoleByName($role[0]);
        if (!$roleExists) {
            $db->addRole($role[0], $role[1], $role[2]);
            echo "✓ Role '{$role[0]}' created\n";
        } else {
            echo "✓ Role '{$role[0]}' already exists\n";
        }
    }
    
    // 2. Add default users
    $users = [
        ['admin', password_hash('admin2026', PASSWORD_BCRYPT), 'Administrator', 'admin@parama.studio', 1],
        ['manager', password_hash('parama2026', PASSWORD_BCRYPT), 'Manajer', 'manager@parama.studio', 2],
        ['staff', password_hash('staff123', PASSWORD_BCRYPT), 'Staff Member', 'staff@parama.studio', 3]
    ];
    
    foreach ($users as $user) {
        $userExists = $db->getUserByUsername($user[0]);
        if (!$userExists) {
            $db->addUser($user[0], $user[1], $user[2], $user[3], $user[4]);
            echo "✓ User '{$user[0]}' created\n";
        } else {
            echo "✓ User '{$user[0]}' already exists\n";
        }
    }
    
    echo "\n✅ Authentication setup complete!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Available Credentials:\n";
    echo "  Admin: admin / admin2026\n";
    echo "  Manager: manager / parama2026\n";
    echo "  Staff: staff / staff123\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
