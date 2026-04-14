<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Check if user is logged in
if (empty($_SESSION['user'])) {
    // Simulate login for testing
    $db = getDB();
    $user = $db->getUserByUsername('admin');
    if ($user && password_verify('admin2026', $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'name' => $user['name'],
            'role' => $user['role'],
            'permissions' => $user['permissions'],
        ];
    }
}

// If still not logged in, redirect
if (empty($_SESSION['user'])) {
    header('Location: /auth/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Test — Parama Studio</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: #F7F5F0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; }
        .section h2 { margin-bottom: 10px; color: #333; }
        .debug-info { background: #f5f5f5; padding: 10px; border-left: 3px solid #C85B2A; margin: 10px 0; font-family: monospace; font-size: 12px; }
        .success { color: #2D7A4A; }
        .error { color: #A02020; }
        .warning { color: #8A5F1A; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Ringkasan Page Debug Test</h1>
    
    <div class="section">
        <h2>Session Info</h2>
        <div class="debug-info">
            <div><strong>User:</strong> <?= htmlspecialchars($_SESSION['user']['name']) ?></div>
            <div><strong>Role:</strong> <?= htmlspecialchars($_SESSION['user']['role']) ?></div>
            <div><strong>ID:</strong> <?= $_SESSION['user']['id'] ?></div>
        </div>
    </div>
    
    <div class="section">
        <h2>JavaScript Global Variables Test</h2>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Loaded');
            
            // Test if DB_SETTINGS is available
            const dbSettingsCheck = typeof window.DB_SETTINGS !== 'undefined' 
                ? '<span class="success">✓ DB_SETTINGS exists</span>'
                : '<span class="error">✗ DB_SETTINGS is undefined</span>';
            
            // Test if functions exist
            const renderRingkasanCheck = typeof window.renderRingkasan === 'function'
                ? '<span class="success">✓ renderRingkasan() exists</span>'
                : '<span class="error">✗ renderRingkasan() not found</span>';
            
            const fmtCheck = typeof window.fmt === 'function'
                ? '<span class="success">✓ fmt() exists</span>'
                : '<span class="error">✗ fmt() not found</span>';
            
            const getOHCheck = typeof window.getOH === 'function'
                ? '<span class="success">✓ getOH() exists</span>'
                : '<span class="warning">⚠ getOH() not found (may not be needed)</span>';
            
            const getFSPriceCheck = typeof window.getFSPrice === 'function'
                ? '<span class="success">✓ getFSPrice() exists</span>'
                : '<span class="error">✗ getFSPrice() not found</span>';
            
            // Try to show DB data
            let dbData = '<span class="warning">? DB_SETTINGS not loaded yet</span>';
            if (typeof window.DB_SETTINGS !== 'undefined') {
                try {
                    const oh = JSON.parse(window.DB_SETTINGS.oh || '{}');
                    dbData = `<span class="success">✓ OH Total: Rp${oh.total?.toLocaleString('id-ID') || '?'}</span>`;
                } catch(e) {
                    dbData = '<span class="error">✗ Error parsing OH data: ' + e.message + '</span>';
                }
            }
            
            document.getElementById('results').innerHTML = `
                <div class="debug-info">
                    <div>${dbSettingsCheck}</div>
                    <div>${renderRingkasanCheck}</div>
                    <div>${fmtCheck}</div>
                    <div>${getOHCheck}</div>
                    <div>${getFSPriceCheck}</div>
                    <div>${dbData}</div>
                </div>
                <p><strong>Note:</strong> If renderRingkasan or other functions are missing, they may not be defined until app.js loads.</p>
            `;
        });
        </script>
        <div id="results" class="debug-info" style="color: #999;">Loading...</div>
    </div>
    
    <div class="section">
        <h2>Ringkasan Page Content Will Load Here</h2>
        <div id="metrics-ov" style="background: #f9f9f9; padding: 10px; border-radius: 5px; margin: 10px 0;">
            Metrics will appear here if page loads correctly...
        </div>
    </div>
    
    <div class="section">
        <h2>Errors Console</h2>
        <p>Open browser's Developer Tools (F12) → Console tab to see any JavaScript errors.</p>
    </div>
</div>

<?php
$pageTitle = 'Test';
$currentPage = 'ringkasan';
?>
<script>const PHP_USER = <?php echo json_encode([
    'id' => $_SESSION['user']['id'],
    'name' => $_SESSION['user']['name'],
    'role' => $_SESSION['user']['role'],
]); ?>;</script>
<script src="/assets/js/app.js"></script>
<script>
    window.addEventListener('load', function() {
        console.log('Page fully loaded');
        console.log('DB_SETTINGS:', typeof window.DB_SETTINGS !== 'undefined' ? window.DB_SETTINGS : 'NOT FOUND');
        console.log('renderRingkasan:', typeof window.renderRingkasan);
        console.log('fmt:', typeof window.fmt);
        
        // Try to render
        if (typeof window.renderRingkasan === 'function') {
            try {
                console.log('Attempting to call renderRingkasan()...');
                window.renderRingkasan();
                console.log('✓ renderRingkasan() called successfully');
            } catch(e) {
                console.error('✗ Error calling renderRingkasan():', e);
            }
        }
    });
</script>
</body>
</html>
