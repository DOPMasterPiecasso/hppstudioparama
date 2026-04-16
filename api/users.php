<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireRole('admin', 'manager');
header('Content-Type: application/json');
$db  = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// ── GET: daftar semua user ────────────────────────────────────
if ($method === 'GET') {
    $allUsers = $db->getAllUsers();
    echo json_encode(['data' => $allUsers]);
    exit;
}

// ── POST: tambah user baru ────────────────────────────────────
if ($method === 'POST') {
    $body     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $username = trim($body['username'] ?? '');
    $name     = trim($body['name']     ?? '');
    $email    = trim($body['email']    ?? '');
    $role_id  = (int)($body['role_id'] ?? 3);
    $password = $body['password'] ?? 'parama123';

    if (empty($username) || empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Username dan nama harus diisi']);
        exit;
    }

    // Cek duplikat username di database
    if ($db->usernameExists($username)) {
        http_response_code(409);
        echo json_encode(['error' => 'Username sudah terdaftar']);
        exit;
    }

    $hash   = password_hash($password, PASSWORD_DEFAULT);
    $userId = $db->addUser($username, $hash, $name, $email, $role_id);
    echo json_encode(['id' => $userId, 'ok' => true]);
    exit;
}

// ── PUT: update user ──────────────────────────────────────────
if ($method === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $id   = (int)($body['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'id required']);
        exit;
    }

    // Pastikan user ada
    $existing = $db->getUserById($id);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $fields = [];

    if (isset($body['username'])) {
        $newUsername = trim($body['username']);
        // Cek duplikat — kecuali user itu sendiri
        if ($db->usernameExists($newUsername, $id)) {
            http_response_code(409);
            echo json_encode(['error' => 'Username sudah terdaftar']);
            exit;
        }
        $fields['username'] = $newUsername;
    }
    if (isset($body['name']))      $fields['name']      = trim($body['name']);
    if (isset($body['role_id']))   $fields['role_id']   = (int)$body['role_id'];
    if (isset($body['is_active'])) $fields['is_active'] = $body['is_active'] ? 1 : 0;
    if (!empty($body['password'])) $fields['password']  = password_hash($body['password'], PASSWORD_DEFAULT);

    $db->updateUser($id, $fields);
    echo json_encode(['ok' => true]);
    exit;
}

// ── DELETE: nonaktifkan user ──────────────────────────────────
if ($method === 'DELETE') {
    $id = (int)($_GET['id'] ?? 0);

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'id required']);
        exit;
    }

    // Pastikan user ada
    $existing = $db->getUserById($id);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    // Soft-delete: set is_active = 0
    $db->deactivateUser($id);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
