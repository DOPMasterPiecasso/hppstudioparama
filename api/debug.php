<?php
/**
 * DEBUG ENDPOINT - Troubleshoot JSON database
 * Access: /api/debug.php
 * DELETE this in production!
 */

header('Content-Type: application/json');

$debug = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'database_type' => 'JSON File Based',
];

// Check JSON files
$debug['json_files'] = [
    'users.json' => file_exists(__DIR__ . '/../data/users.json') ? '✓ Exists' : '✗ Missing',
    'settings.json' => file_exists(__DIR__ . '/../data/settings.json') ? '✓ Exists' : '✗ Missing',
    'penawaran.json' => file_exists(__DIR__ . '/../data/penawaran.json') ? '✓ Exists' : '✗ Missing',
];

// Try loading JSON files
try {
    require_once __DIR__ . '/../config/db.php';
    $db = getDB();
    $debug['database_connection'] = '✓ Success';
    
    // Get users count
    $users = $db->getAllUsers();
    $debug['users'] = [
        'count' => count($users),
        'list' => array_map(fn($u) => [
            'id' => $u['id'],
            'username' => $u['username'],
            'name' => $u['name'],
            'role' => $u['role'],
            'is_active' => $u['is_active']
        ], $users)
    ];
    
    // Get settings
    $settings = $db->getSettings();
    $debug['settings'] = $settings;
    
} catch (Exception $e) {
    $debug['database_connection'] = '✗ Failed';
    $debug['database_error'] = $e->getMessage();
}

// Check file permissions
$debug['permissions'] = [
    'data_dir_writable' => is_writable(__DIR__ . '/../data') ? '✓ Yes' : '✗ No',
    'users_writable' => is_writable(__DIR__ . '/../data/users.json') ? '✓ Yes' : '✗ No',
];

echo json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
