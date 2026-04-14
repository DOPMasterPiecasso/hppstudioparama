<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

// Only admin can manage roles
$user = requireRole('admin', 'manager');
header('Content-Type: application/json');
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Return all roles from JSON
    $roles = $db->getRoles();
    
    // Count users per role
    $allUsers = $db->getAllUsers();
    $userCount = [];
    foreach ($allUsers as $u) {
        $roleId = $u['role_id'];
        if (!isset($userCount[$roleId])) {
            $userCount[$roleId] = 0;
        }
        $userCount[$roleId]++;
    }
    
    // Add user_count to each role and ensure permissions is array
    foreach ($roles as &$r) {
        $r['user_count'] = $userCount[$r['id']] ?? 0;
        if (is_string($r['permissions'] ?? '')) {
            $r['permissions'] = json_decode($r['permissions'], true) ?: [];
        }
    }
    
    echo json_encode(['data' => $roles]);
    exit;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $name = $body['name'] ?? '';
    $label = $body['label'] ?? '';
    $perms = $body['permissions'] ?? [];
    
    if (!$name || !$label) {
        http_response_code(400);
        echo json_encode(['error' => 'name and label required']);
        exit;
    }
    
    $roleId = $db->addRole($name, $label, $perms);
    showToast('Role berhasil ditambahkan', 'success');
    echo json_encode(['id' => $roleId, 'ok' => true]);
    exit;
}

if ($method === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($body['id'] ?? 0);
    if (!$id) { 
        http_response_code(400); 
        echo json_encode(['error' => 'id required']); 
        exit; 
    }
    
    // Get current roles data
    $data = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
    $roleIdx = null;
    foreach ($data['roles'] as $i => $r) {
        if ($r['id'] == $id) {
            $roleIdx = $i;
            break;
        }
    }
    
    if ($roleIdx === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Role not found']);
        exit;
    }
    
    // Update fields
    if (isset($body['name'])) $data['roles'][$roleIdx]['name'] = $body['name'];
    if (isset($body['label'])) $data['roles'][$roleIdx]['label'] = $body['label'];
    if (isset($body['permissions'])) $data['roles'][$roleIdx]['permissions'] = $body['permissions'];
    
    file_put_contents(__DIR__ . '/../data/users.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    showToast('Role berhasil diperbarui', 'success');
    echo json_encode(['ok' => true]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { 
        http_response_code(400); 
        echo json_encode(['error' => 'id required']); 
        exit; 
    }
    
    // Get current roles data
    $data = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
    $roleIdx = null;
    foreach ($data['roles'] as $i => $r) {
        if ($r['id'] == $id) {
            $roleIdx = $i;
            break;
        }
    }
    
    if ($roleIdx === null) {
        http_response_code(404);
        echo json_encode(['error' => 'Role not found']);
        exit;
    }
    
    // Delete by removing from array
    array_splice($data['roles'], $roleIdx, 1);
    file_put_contents(__DIR__ . '/../data/users.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    showToast('Role berhasil dihapus', 'success');
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

