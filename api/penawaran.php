<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user   = requireAuth();
header('Content-Type: application/json');

try {
    $db     = getDB();
    $method = $_SERVER['REQUEST_METHOD'];

    // ── GET: daftar semua penawaran ──────────────────────────────
    if ($method === 'GET') {
        $penawarans = $db->getPenawaran();
        echo json_encode(['data' => $penawarans]);
        exit;
    }

    // ── POST: tambah penawaran baru ──────────────────────────────
    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if (empty($body['nama_klien'])) {
            http_response_code(400);
            echo json_encode(['error' => 'nama_klien wajib diisi']);
            exit;
        }

        $newId = $db->addPenawaran([
            'nama_klien'           => trim($body['nama_klien'] ?? ''),
            'paket'                => $body['paket']           ?? '',
            'harga'                => (int)($body['harga']     ?? 0),
            'harga_sebelum_diskon' => (int)($body['harga_sebelum_diskon'] ?? 0),
            'jumlah_siswa'         => (int)($body['jumlah_siswa'] ?? 0),
            'catatan'              => $body['catatan']         ?? '',
            'status'               => $body['status']         ?? 'pending',
            'added_by_id'          => $user['id'],
        ]);

        echo json_encode(['id' => $newId, 'success' => true]);
        exit;
    }

    // ── PUT: update penawaran ────────────────────────────────────
    if ($method === 'PUT') {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id   = (int)($body['id'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'id required']);
            exit;
        }

        // Pastikan penawaran ada
        if (!$db->getPenawaranById($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'penawaran not found']);
            exit;
        }

        $fields = [];
        $allowed = ['nama_klien','paket','harga','harga_sebelum_diskon','jumlah_siswa','catatan','status'];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $body)) {
                $fields[$key] = $body[$key];
            }
        }

        if (!empty($fields)) {
            $db->updatePenawaran($id, $fields);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // ── DELETE: hapus penawaran ──────────────────────────────────
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'id required']);
            exit;
        }

        if (!$db->getPenawaranById($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'penawaran not found']);
            exit;
        }

        $db->deletePenawaran($id);
        echo json_encode(['success' => true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Exception $e) {
    error_log('penawaran.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
