<?php
/**
 * Parama HPP - Master Data API
 * 
 * API terpusat untuk semua data harga dan pengaturan
 * Halaman pengaturan.php adalah master editor, semua halaman lain mengambil data dari sini
 * 
 * Endpoints:
 * GET /api/master-data.php?action=get_all           — Semua data
 * GET /api/master-data.php?action=get_overhead      — Overhead saja
 * GET /api/master-data.php?action=get_fullservice   — Full Service pricing
 * GET /api/master-data.php?action=get_alacarte      — À La Carte packages
 * GET /api/master-data.php?action=get_addons        — Add-ons
 * GET /api/master-data.php?action=get_cetak         — Cetak base
 * GET /api/master-data.php?action=get_graduation    — Graduation packages
 * POST /api/master-data.php                         — Update master data
 */

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireAuth();
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getMySQLConnection();
    if (!$pdo) {
        throw new Exception('Database connection failed. Check DB configuration.');
    }
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'get_all';
    
    // GET — Fetch data dari master
    if ($method === 'GET') {
        $response = [];
        
        switch ($action) {
            case 'get_all':
                // Return ALL master data sekaligus
                $response = [
                    'overhead' => getMasterOverhead($pdo),
                    'cetak_f' => getMasterCetakFactors($pdo),
                    'cetak_base' => getMasterCetakBase($pdo),
                    'alc_f' => getMasterAlaCarteFactors($pdo),
                    'fs' => getMasterFullService($pdo),
                    'addon_data' => getMasterAddons($pdo),
                    'grad' => getMasterGraduation($pdo),
                    'payment_terms' => getMasterPaymentTerms($pdo),
                    'timestamp' => date('Y-m-d H:i:s'),
                ];
                break;
                
            case 'get_overhead':
                $response = getMasterOverhead($pdo);
                break;
                
            case 'get_cetak_factors':
                $response = getMasterCetakFactors($pdo);
                break;
                
            case 'get_cetak_base':
                $response = getMasterCetakBase($pdo);
                break;
                
            case 'get_alacarte_factors':
                $response = getMasterAlaCarteFactors($pdo);
                break;
                
            case 'get_fullservice':
                $response = getMasterFullService($pdo);
                break;
                
            case 'get_addons':
                $response = getMasterAddons($pdo);
                break;
                
            case 'get_graduation':
                $response = getMasterGraduation($pdo);
                break;
                
            case 'get_payment_terms':
                $response = getMasterPaymentTerms($pdo);
                break;
                
            default:
                throw new Exception('Action not recognized: ' . $action);
        }
        
        echo json_encode(['success' => true, 'data' => $response], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // POST — Update master data (manager/admin only)
    if ($method === 'POST') {
        error_log("=== POST REQUEST TO /api/master-data.php ===");
        
        requireRoleAPI('admin', 'manager');
        
        $rawBody = file_get_contents('php://input');
        error_log("Raw body: " . $rawBody);
        
        $body = json_decode($rawBody, true) ?? [];
        error_log("Decoded body: " . json_encode($body));
        
        $type = $body['type'] ?? null;
        error_log("Update type: " . $type);
        
        if (!$type) {
            throw new Exception('Type parameter required');
        }
        
        error_log("Calling updateMasterData with type '$type'");
        $response = updateMasterData($pdo, $type, $body);
        error_log("updateMasterData response: " . json_encode($response));
        
        echo json_encode(['success' => true, 'message' => 'Master data updated', 'data' => $response], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        error_log("=== POST REQUEST COMPLETE ===");
        exit;
    }
    
    // Method not allowed
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

// ============================================================
// GETTER FUNCTIONS - Master Data
// ============================================================

function getMasterOverhead($pdo) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->getOverhead();
}

function getMasterCetakFactors($pdo) {
    $masterData = new MySQLMasterData($pdo);
    $factors = $masterData->getPricingFactors();
    return $factors['cetak'] ?? ['handy' => 1.0, 'minimal' => 0.95, 'large' => 1.15];
}

function getMasterCetakBase($pdo) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->getCetakBase();
}

function getMasterAlaCarteFactors($pdo) {
    $masterData = new MySQLMasterData($pdo);
    $factors = $masterData->getPricingFactors();
    return $factors['alacarte'] ?? ['ebook' => 0.72, 'editcetak' => 0.62, 'desain' => 0.22, 'cetakonly' => 0.30];
}

function getMasterFullService($pdo) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->getFullService();
}

function getMasterAddons($pdo) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->getAddons();
}

function getMasterGraduation($pdo) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->getGraduation();
}

function getMasterPaymentTerms($pdo) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->getPaymentTerms();
}

// ============================================================
// UPDATE FUNCTION - Master Data
// ============================================================

function updateMasterData($pdo, $type, $body) {
    switch ($type) {
        case 'overhead':
            return updateOverhead($pdo, $body['data'] ?? []);
            
        case 'cetak_factors':
            return updateCetakFactors($pdo, $body['data'] ?? []);
            
        case 'cetak_base':
            return updateCetakBase($pdo, $body['data'] ?? []);
            
        case 'alacarte_factors':
            return updateAlaCarteFactors($pdo, $body['data'] ?? []);
            
        case 'fullservice':
            return updateFullService($pdo, $body['data'] ?? []);
            
        case 'addons':
            return updateAddons($pdo, $body['data'] ?? []);
            
        case 'graduation':
            return updateGraduation($pdo, $body['data'] ?? []);
        
        case 'payment_terms':
            return updatePaymentTerms($pdo, $body['data'] ?? []);
            
        default:
            throw new Exception('Unknown update type: ' . $type);
    }
}

function updateOverhead($pdo, $data) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->updateOverhead($data);
}

function updateCetakFactors($pdo, $data) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->updateCetakFactors($data);
}

function updateCetakBase($pdo, $data) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->updateCetakBase($data);
}

function updateAlaCarteFactors($pdo, $data) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->updateAlaCarteFactors($data);
}

function updateFullService($pdo, $data) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->updateFullService($data);
}

function updateAddons($pdo, $data) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->updateAddons($data);
}

function updateGraduation($pdo, $data) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->updateGraduation($data);
}

function updatePaymentTerms($pdo, $data) {
    $masterData = new MySQLMasterData($pdo);
    return $masterData->updatePaymentTerms($data);
}
?>
