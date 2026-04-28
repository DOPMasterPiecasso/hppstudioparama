<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

// Only manager/admin can CRUD master data
$user = requireRole('admin', 'manager');

header('Content-Type: application/json');

$pdo = getMySQLConnection();
$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch specifications
if ($method === 'GET') {
    $type = $_GET['type'] ?? 'fullservice';
    try {
        $stmt = $pdo->prepare("SELECT * FROM spesifikasi_produk WHERE package_type = ? ORDER BY display_order ASC, id ASC");
        $stmt->execute([$type]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// POST: CRUD actions
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    try {
        if ($action === 'create') {
            $stmt = $pdo->prepare("INSERT INTO spesifikasi_produk (package_type, kategori, label, value, display_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $input['package_type'],
                $input['kategori'] ?? 'all',
                $input['label'],
                $input['value'] ?? '',
                $input['display_order'] ?? 0
            ]);
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
        } 
        elseif ($action === 'update') {
            $id = $input['id'];
            $stmt = $pdo->prepare("UPDATE spesifikasi_produk SET label = ?, value = ?, kategori = ?, display_order = ? WHERE id = ?");
            $stmt->execute([
                $input['label'],
                $input['value'],
                $input['kategori'] ?? 'all',
                $input['display_order'] ?? 0,
                $id
            ]);
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM spesifikasi_produk WHERE id = ?");
            $stmt->execute([$input['id']]);
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'reorder') {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE spesifikasi_produk SET display_order = ? WHERE id = ?");
            foreach ($input['orders'] as $item) {
                $stmt->execute([$item['order'], $item['id']]);
            }
            $pdo->commit();
            echo json_encode(['success' => true]);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
