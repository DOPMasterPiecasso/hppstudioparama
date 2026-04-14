<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireRole('admin', 'manager');
header('Content-Type: application/json');
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $allUsers = $db->getAllUsers();
    echo json_encode(['data' => $allUsers]);
    exit;
}

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $username = $body['username'] ?? '';
    $name = $body['name'] ?? '';
    $email = $body['email'] ?? '';
    $role_id = (int)($body['role_id'] ?? 3);
    $password = $body['password'] ?? 'parama123';
    
    if (empty($username) || empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Username dan nama harus diisi']);
        exit;
    }
    
    // Check duplicate username
    $data = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
    foreach ($data['users'] as $u) {
        if ($u['username'] === $username) {
            http_response_code(409);
            echo json_encode(['error' => 'Username sudah terdaftar']);
            exit;
        }
    }
    
    $pass = password_hash($password, PASSWORD_DEFAULT);
    $userId = $db->addUser($username, $pass, $name, $email, $role_id);
    echo json_encode(['id' => $userId, 'ok' => true]);
    exit;
}

if ($method === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $id = (int)($body['id'] ?? 0);
    if (!$id) { 
        http_response_code(400); 
        echo json_encode(['error'=>'id required']); 
        exit; 
    }
    
    // Get current user data
    $data = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
    $userIdx = null;
    foreach ($data['users'] as $i => $u) {
        if ($u['id'] == $id) {
            $userIdx = $i;
            break;
        }
    }
    
    if ($userIdx === null) {
        http_response_code(404);
        echo json_encode(['error'=>'User not found']);
        exit;
    }
    
    // Update fields
    if (isset($body['username'])) {
        $newUsername = $body['username'];
        // Check if new username already exists (and is not the same user)
        foreach ($data['users'] as $u) {
            if ($u['username'] === $newUsername && $u['id'] != $id) {
                http_response_code(409);
                echo json_encode(['error' => 'Username sudah terdaftar']);
                exit;
            }
        }
        $data['users'][$userIdx]['username'] = $newUsername;
    }
    if (isset($body['name'])) $data['users'][$userIdx]['name'] = $body['name'];
    if (isset($body['role_id'])) $data['users'][$userIdx]['role_id'] = (int)$body['role_id'];
    if (isset($body['is_active'])) $data['users'][$userIdx]['is_active'] = (bool)$body['is_active'];
    if (!empty($body['password'])) $data['users'][$userIdx]['password'] = password_hash($body['password'], PASSWORD_DEFAULT);
    
    $data['users'][$userIdx]['updated_at'] = date('c');
    
    file_put_contents(__DIR__ . '/../data/users.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok' => true]);
    exit;
}

if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { 
        http_response_code(400); 
        echo json_encode(['error'=>'id required']); 
        exit; 
    }
    
    // Get current user data
    $data = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true);
    $userIdx = null;
    foreach ($data['users'] as $i => $u) {
        if ($u['id'] == $id) {
            $userIdx = $i;
            break;
        }
    }
    
    if ($userIdx === null) {
        http_response_code(404);
        echo json_encode(['error'=>'User not found']);
        exit;
    }
    
    // Set inactive instead of deleting
    $data['users'][$userIdx]['is_active'] = false;
    $data['users'][$userIdx]['updated_at'] = date('c');
    
    file_put_contents(__DIR__ . '/../data/users.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

