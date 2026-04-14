<?php
/**
 * Quick Login Test - Verify system is working
 * Access: /test-login.php
 */

require_once __DIR__ . '/config/db.php';

echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test - Parama HPP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .section { margin-bottom: 20px; }
        .success { color: #27ae60; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #34495e; color: white; }
        .test-form { background: #ecf0f1; padding: 15px; border-radius: 5px; margin-top: 10px; }
        input { padding: 8px; margin: 5px 0; width: 100%; box-sizing: border-box; }
        button { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #2980b9; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Login Verification Test</h1>
        
        <div class="section">
            <h2>System Status</h2>';

try {
    $db = getDB();
    echo '<p class="success">✓ Database loaded successfully</p>';
    
    $users = $db->getAllUsers();
    echo '<p>Found ' . count($users) . ' users:</p>';
    echo '<table>
            <tr>
                <th>Username</th>
                <th>Name</th>
                <th>Role</th>
                <th>Status</th>
            </tr>';
    
    foreach ($users as $user) {
        $status = $user['is_active'] ? '<span class="success">Active</span>' : '<span class="error">Inactive</span>';
        echo '<tr>
                <td>' . htmlspecialchars($user['username']) . '</td>
                <td>' . htmlspecialchars($user['name']) . '</td>
                <td>' . htmlspecialchars($user['role']) . '</td>
                <td>' . $status . '</td>
            </tr>';
    }
    echo '</table>';
    
} catch (Exception $e) {
    echo '<p class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div>
        
        <div class="section">
            <h2>Test Login Credentials</h2>
            <table>
                <tr>
                    <th>Username</th>
                    <th>Password</th>
                    <th>Test</th>
                </tr>
                <tr>
                    <td>admin</td>
                    <td>admin2026</td>
                    <td><a href="/auth/login.php" style="color: #3498db; text-decoration: none;">Try Login</a></td>
                </tr>
                <tr>
                    <td>manager</td>
                    <td>parama2026</td>
                    <td><a href="/auth/login.php" style="color: #3498db; text-decoration: none;">Try Login</a></td>
                </tr>
                <tr>
                    <td>staff</td>
                    <td>staff123</td>
                    <td><a href="/auth/login.php" style="color: #3498db; text-decoration: none;">Try Login</a></td>
                </tr>
            </table>
        </div>
        
        <div class="section">
            <h2>Manual Test</h2>
            <p>Or test with custom credentials below:</p>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    try {
        $db = getDB();
        $user = $db->getUserByUsername($username);
        
        if ($user && $user['is_active']) {
            if (password_verify($password, $user['password'])) {
                echo '<p class="success">✓ Login OK! User: ' . htmlspecialchars($user['name']) . ' (' . htmlspecialchars($user['role']) . ')</p>';
            } else {
                echo '<p class="error">✗ Wrong password for user ' . htmlspecialchars($username) . '</p>';
            }
        } else {
            echo '<p class="error">✗ User ' . htmlspecialchars($username) . ' not found or inactive</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
}

echo '<form method="POST" class="test-form">
                <input type="text" name="username" placeholder="Username" value="admin" required>
                <input type="password" name="password" placeholder="Password" value="admin2026" required>
                <button type="submit">Test Login</button>
            </form>
        </div>
        
        <div class="section">
            <h2>Other Tools</h2>
            <ul>
                <li><a href="/api/debug.php">Debug Endpoint</a></li>
                <li><a href="/auth/login.php">Login Page</a></li>
            </ul>
        </div>
    </div>
</body>
</html>';
?>
