<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireAuth();
header('Content-Type: application/json');

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET — list penawaran
    if ($method === 'GET') {
        $penawarans = $db->getPenawaran();
        
        // Enrich with user names
        $users = $db->getUsers();
        $usersMap = array_reduce($users, function($acc, $u) {
            $acc[$u['id']] = $u['name'] ?? 'Unknown';
            return $acc;
        }, []);
        
        foreach ($penawarans as &$p) {
            $p['added_by_name'] = $usersMap[$p['added_by_id']] ?? $p['added_by'] ?? 'Unknown';
        }
        
        echo json_encode(['data' => $penawarans]);
        exit;
    }
    
    // POST — tambah penawaran
    if ($method === 'POST') {
        $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        
        $nextId = $db->addPenawaran([
            'nama_klien' => $body['nama_klien'] ?? '',
            'paket' => $body['paket'] ?? '',
            'harga' => (int)($body['harga'] ?? 0),
            'harga_sebelum_diskon' => (int)($body['harga_sebelum_diskon'] ?? 0),
            'jumlah_siswa' => (int)($body['jumlah_siswa'] ?? 0),
            'catatan' => $body['catatan'] ?? '',
            'status' => $body['status'] ?? 'pending',
            'added_by' => $user['username'],
            'added_by_id' => $user['id']
        ]);
        
        echo json_encode(['id' => $nextId, 'success' => true]);
        exit;
    }
    
    // PUT — update penawaran
    if ($method === 'PUT') {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = (int)($body['id'] ?? 0);
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'id required']);
            exit;
        }
        
        // Update penawaran by ID
        $penawarans = $db->getPenawaran();
        $updated = false;
        foreach ($penawarans as &$p) {
            if ($p['id'] == $id) {
                if (isset($body['nama_klien'])) $p['nama_klien'] = $body['nama_klien'];
                if (isset($body['paket'])) $p['paket'] = $body['paket'];
                if (isset($body['harga'])) $p['harga'] = (int)$body['harga'];
                if (isset($body['harga_sebelum_diskon'])) $p['harga_sebelum_diskon'] = (int)$body['harga_sebelum_diskon'];
                if (isset($body['jumlah_siswa'])) $p['jumlah_siswa'] = (int)$body['jumlah_siswa'];
                if (isset($body['catatan'])) $p['catatan'] = $body['catatan'];
                if (isset($body['status'])) $p['status'] = $body['status'];
                $p['updated_at'] = date('c');
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            // Save back to JSON
            $db->savePenawaran($penawarans);
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'penawaran not found']);
        }
        exit;
    }
    
    // DELETE
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'id required']);
            exit;
        }
        
        $penawarans = $db->getPenawaran();
        $penawarans = array_filter($penawarans, fn($p) => $p['id'] != $id);
        $db->savePenawaran($penawarans);
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
