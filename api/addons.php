<?php
/**
 * Add-on Management API
 * Handles CRUD operations for all addon categories
 */

// Aktifkan error reporting untuk menangkap Fatal Error agar tidak return kosong
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Start output buffering
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Set timeout for database operations
set_time_limit(30);

/**
 * Helper function to send JSON and stop execution safely
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
    // 1. Authentication Check
    if (empty($_SESSION['user'])) {
        sendJSON([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => 'Silakan login terlebih dahulu'
        ], 401);
    }
    
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $user = $_SESSION['user'];
    $userRole = $user['role'] ?? '';

    // 2. Authorization Check (POST only)
    if ($method === 'POST') {
        if (!in_array($userRole, ['admin', 'manager'])) {
            sendJSON([
                'success' => false,
                'error' => 'Forbidden',
                'message' => 'Akses ditolak - diperlukan role admin atau manager'
            ], 403);
        }
    }

    // 3. Database Connection Check
    $pdo = getMySQLConnection();
    if (!$pdo) {
        sendJSON(['success' => false, 'error' => 'Database connection failed'], 500);
    }

   $pdo = getMySQLConnection();
    $db = new MySQLMasterData($pdo);
    if (!$db) {
        sendJSON(['success' => false, 'error' => 'Database wrapper (getDB) not initialized'], 500);
    }
    
    $action = $_GET['action'] ?? 'get_all';
    
    // ===== GET Requests =====
    if ($method === 'GET') {
        if ($action === 'get_all') {
            $addons = $db->getAddons();
            sendJSON(['success' => true, 'data' => $addons], 200);
        } elseif ($action === 'get_category') {
            $category = $_GET['category'] ?? 'finishing';
            $allAddons = $db->getAddons();
            $result = $allAddons[$category] ?? [];
            sendJSON(['success' => true, 'data' => $result], 200);
        } else {
            sendJSON(['error' => 'Unknown action: ' . $action], 400);
        }
    }
    
    // ===== POST Requests =====
    if ($method === 'POST') {
        $input = file_get_contents('php://input');
        $body = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendJSON(['success' => false, 'error' => 'Invalid JSON input'], 400);
        }

        $allAddons = $db->getAddons();
        $operation = $body['operation'] ?? 'update_addon';
        
        // ===== Update Single Addon =====
        if ($operation === 'update_addon') {
            $category = $body['category'] ?? null;
            $id = $body['id'] ?? null;
            $name = $body['name'] ?? null;
            
            if (!$category || !$id || !$name) {
                sendJSON(['success' => false, 'error' => 'Missing required fields: category, id, name'], 400);
            }
            
            if (isset($allAddons[$category])) {
                $found = false;
                foreach ($allAddons[$category] as &$addon) {
                    if ($addon['id'] === $id) {
                        $addon['name'] = $name;
                        if ($body['type'] === 'flat_video') {
                            $addon['price'] = (int)($body['price'] ?? 0);
                            $addon['type'] = 'flat_video';
                        } else {
                            $addon['tiers'] = $body['tiers'] ?? [];
                            $addon['type'] = 'flat';
                        }
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    sendJSON(['success' => false, 'error' => 'Addon not found: ' . $id], 404);
                }
            } else {
                sendJSON(['success' => false, 'error' => 'Category not found: ' . $category], 404);
            }
            
            if (!method_exists($db, 'updateAddons')) {
                throw new Exception("Method updateAddons tidak tersedia di class " . get_class($db));
            }
            
            $db->updateAddons($allAddons);
            
            sendJSON([
                'success' => true,
                'message' => "Addon '$name' berhasil diperbarui",
                'data' => $allAddons[$category] ?? []
            ], 200);
        }
        
        // ===== Delete Addon =====
        elseif ($operation === 'delete_addon') {
            $category = $body['category'] ?? null;
            $id = $body['id'] ?? null;
            
            if (!$category || !$id) {
                sendJSON(['success' => false, 'error' => 'Missing category or id'], 400);
            }
            
            $found = false;
            if (isset($allAddons[$category])) {
                $allAddons[$category] = array_filter(
                    $allAddons[$category],
                    function($addon) use ($id, &$found) {
                        if ($addon['id'] === $id) {
                            $found = true;
                            return false;
                        }
                        return true;
                    }
                );
                $allAddons[$category] = array_values($allAddons[$category]);
            }
            
            if (!$found) {
                sendJSON(['success' => false, 'error' => 'Addon not found'], 404);
            }
            
            $db->updateAddons($allAddons);
            
            sendJSON([
                'success' => true,
                'message' => 'Addon berhasil dihapus',
                'data' => $allAddons[$category] ?? []
            ], 200);
        }
        
        // ===== Add New Addon =====
        elseif ($operation === 'add_addon') {
            $category = $body['category'] ?? null;
            $name = $body['name'] ?? null;
            
            if (!$category || !$name) {
                sendJSON(['success' => false, 'error' => 'Missing category or name'], 400);
            }
            
            $newAddon = [
                'id' => $body['id'] ?? 'addon_' . uniqid(),
                'name' => $name,
                'type' => $body['type'] ?? 'flat'
            ];
            
            if ($newAddon['type'] === 'flat_video') {
                $newAddon['price'] = (int)($body['price'] ?? 0);
            } else {
                $newAddon['tiers'] = $body['tiers'] ?? [];
            }
            
            if (!isset($allAddons[$category])) {
                $allAddons[$category] = [];
            }
            
            $allAddons[$category][] = $newAddon;
            $db->updateAddons($allAddons);
            
            sendJSON([
                'success' => true,
                'message' => "Add-on '$name' berhasil ditambahkan",
                'data' => $newAddon
            ], 201);
        }
        
        // ===== Update All Categories at Once =====
        elseif ($operation === 'update_all_categories') {
            $newData = $body['data'] ?? [];
            if (empty($newData)) {
                sendJSON(['success' => false, 'error' => 'No data provided'], 400);
            }
            
            foreach ($newData as $cat => $items) {
                $allAddons[$cat] = $items;
            }
            
            $db->updateAddons($allAddons);
            sendJSON(['success' => true, 'message' => 'Semua kategori berhasil diperbarui'], 200);
        }
        
        // ===== Reset Addons to Default =====
        elseif ($operation === 'reset_addons') {
            $defaultFile = __DIR__ . '/../data/addons.json';
            if (!file_exists($defaultFile)) {
                sendJSON(['success' => false, 'error' => 'Default addons file not found'], 500);
            }
            
            $defaultData = json_decode(file_get_contents($defaultFile), true);
            if (!$defaultData) {
                sendJSON(['success' => false, 'error' => 'Failed to parse default addons file'], 500);
            }
            
            $db->updateAddons($defaultData);
            sendJSON(['success' => true, 'message' => 'Semua addons direset ke default', 'data' => $defaultData], 200);
        }
        
        else {
            sendJSON(['success' => false, 'error' => 'Unknown operation: ' . $operation], 400);
        }
    }
    
    // Fallback if no condition met
    sendJSON(['success' => false, 'error' => 'Invalid Request Method'], 405);

} catch (Throwable $e) {
    // Tangkap Throwable (termasuk Error dan Exception) agar tidak return kosong
    sendJSON([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine()
    ], 500);
}