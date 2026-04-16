<?php
/**
 * Add-on Management API
 * Handles CRUD operations for all addon categories
 */

// Pastikan error ditangkap sebagai JSON, bukan output teks kosong
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering untuk mencegah whitespace merusak JSON
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Set timeout untuk operasi database yang kompleks
set_time_limit(30);

/**
 * Helper untuk mengirim response JSON yang bersih
 */
function sendJSON($data, $statusCode = 200) {
    if (ob_get_length()) {
        ob_end_clean();
    }
    
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 1. Cek Autentikasi
    if (empty($_SESSION['user'])) {
        sendJSON(['success' => false, 'error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu'], 401);
    }
    
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $user = $_SESSION['user'];
    $userRole = $user['role'] ?? '';

    // 2. Cek Otorisasi (Hanya admin/manager yang bisa POST/Update)
    if ($method === 'POST' && !in_array($userRole, ['admin', 'manager'])) {
        sendJSON(['success' => false, 'error' => 'Forbidden', 'message' => 'Akses ditolak'], 403);
    }

    // 3. Inisialisasi Database (Gunakan MySQLMasterData agar method updateAddons tersedia)
    $pdo = getMySQLConnection();
    if (!$pdo) {
        sendJSON(['success' => false, 'error' => 'Database connection failed'], 500);
    }
    $db = new MySQLMasterData($pdo);

    // ===== PROSES GET REQUEST =====
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'get_all';
        if ($action === 'get_all') {
            sendJSON(['success' => true, 'data' => $db->getAddons()]);
        } elseif ($action === 'get_category') {
            $category = $_GET['category'] ?? '';
            $all = $db->getAddons();
            sendJSON(['success' => true, 'data' => $all[$category] ?? []]);
        }
    }

    // ===== PROSES POST REQUEST =====
    if ($method === 'POST') {
        $input = file_get_contents('php://input');
        $body = json_decode($input, true);
        
        if (!$body) {
            sendJSON(['success' => false, 'error' => 'Invalid JSON input'], 400);
        }

        $operation = $body['operation'] ?? 'update_addon';
        $allAddons = $db->getAddons(); // Ambil data saat ini untuk dimodifikasi

        // --- Operasi: Update Single Addon ---
        if ($operation === 'update_addon') {
            $category = $body['category'] ?? null;
            $id = $body['id'] ?? null;

            if (!isset($allAddons[$category])) {
                sendJSON(['success' => false, 'error' => "Kategori '$category' tidak ditemukan"], 404);
            }

            $found = false;
            // PENTING: Gunakan &$addon (reference) agar perubahan tersimpan ke $allAddons
            foreach ($allAddons[$category] as &$addon) {
                if ($addon['id'] === $id) {
                    $addon['name'] = $body['name'] ?? $addon['name'];
                    
                    if ($body['type'] === 'flat_video') {
                        $addon['type'] = 'flat_video';
                        $addon['price'] = (int)($body['price'] ?? 0);
                        unset($addon['tiers']); // Hapus tiers jika berganti tipe
                    } else {
                        $addon['type'] = 'flat';
                        $addon['tiers'] = $body['tiers'] ?? [];
                        unset($addon['price']); // Hapus price jika berganti tipe
                    }
                    $found = true;
                    break;
                }
            }
            unset($addon); // Putus reference untuk keamanan

            if (!$found) {
                sendJSON(['success' => false, 'error' => "Addon ID '$id' tidak ditemukan"], 404);
            }

            // Simpan perubahan massal ke database
            $db->updateAddons($allAddons);

            sendJSON([
                'success' => true,
                'message' => "Addon berhasil diperbarui",
                'data' => $allAddons[$category]
            ]);
        }

        // --- Operasi: Delete Addon ---
        elseif ($operation === 'delete_addon') {
            $category = $body['category'] ?? null;
            $id = $body['id'] ?? null;

            if (isset($allAddons[$category])) {
                $allAddons[$category] = array_values(array_filter($allAddons[$category], function($a) use ($id) {
                    return $a['id'] !== $id;
                }));
                $db->updateAddons($allAddons);
                sendJSON(['success' => true, 'message' => 'Addon berhasil dihapus']);
            }
            sendJSON(['success' => false, 'error' => 'Kategori tidak ditemukan'], 404);
        }

        // --- Operasi: Tambah Addon Baru ---
        elseif ($operation === 'add_addon') {
            $category = $body['category'] ?? null;
            $newAddon = [
                'id' => $body['id'] ?? 'addon_' . uniqid(),
                'name' => $body['name'] ?? 'New Item',
                'type' => $body['type'] ?? 'flat'
            ];

            if ($newAddon['type'] === 'flat_video') {
                $newAddon['price'] = (int)($body['price'] ?? 0);
            } else {
                $newAddon['tiers'] = $body['tiers'] ?? [];
            }

            $allAddons[$category][] = $newAddon;
            $db->updateAddons($allAddons);
            
            sendJSON(['success' => true, 'message' => 'Addon berhasil ditambahkan', 'data' => $newAddon]);
        }

        // --- Operasi: Update Semua (Bulk) ---
        elseif ($operation === 'update_all_categories') {
            if (empty($body['data'])) {
                sendJSON(['success' => false, 'error' => 'Data kosong'], 400);
            }
            $db->updateAddons($body['data']);
            sendJSON(['success' => true, 'message' => 'Semua kategori berhasil disimpan']);
        }
        
        else {
            sendJSON(['success' => false, 'error' => 'Operasi tidak dikenal: ' . $operation], 400);
        }
    }

    sendJSON(['success' => false, 'error' => 'Method not allowed'], 405);

} catch (Throwable $e) {
    // Tangkap semua error/exception agar tidak return response kosong
    sendJSON([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ], 500);
}