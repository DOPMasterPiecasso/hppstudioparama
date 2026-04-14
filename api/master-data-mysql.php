<?php
/**
 * Parama HPP - Master Data API (MySQL Version)
 * 
 * API terpusat untuk semua data harga dan pengaturan
 * Backend: MySQL Database
 * 
 * Endpoints:
 * GET /api/master-data-mysql.php?action=get_all           — Semua data
 * GET /api/master-data-mysql.php?action=get_overhead      — Overhead saja
 * GET /api/master-data-mysql.php?action=get_fullservice   — Full Service pricing
 * etc.
 * POST /api/master-data-mysql.php                         — Update master data
 */

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireAuth();
header('Content-Type: application/json; charset=utf-8');

try {
    // Get MySQL connection
    $pdo = getMySQLConnection();
    if (!$pdo) {
        throw new Exception('MySQL connection failed. Please check database configuration.');
    }
    
    // Get MySQL Master Data handler
    $masterData = getMySQLMasterData($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? 'get_all';
    
    // GET — Fetch data dari MySQL
    if ($method === 'GET') {
        $response = [];
        
        switch ($action) {
            case 'get_all':
                $response = [
                    'overhead' => $masterData->getOverhead(),
                    'cetak_f' => $masterData->getPricingFactors()['cetak'] ?? [],
                    'cetak_base' => $masterData->getCetakBase(),
                    'alc_f' => $masterData->getPricingFactors()['alacarte'] ?? [],
                    'fs' => $masterData->getFullService(),
                    'addon_data' => $masterData->getAddons(),
                    'grad' => $masterData->getGraduation(),
                    'payment_terms' => $masterData->getPaymentTerms(),
                    'timestamp' => date('Y-m-d H:i:s'),
                    'source' => 'MySQL'
                ];
                break;
                
            case 'get_overhead':
                $response = $masterData->getOverhead();
                break;
                
            case 'get_cetak_factors':
                $all = $masterData->getPricingFactors();
                $response = $all['cetak'] ?? [];
                break;
                
            case 'get_cetak_base':
                $response = $masterData->getCetakBase();
                break;
                
            case 'get_alacarte_factors':
                $all = $masterData->getPricingFactors();
                $response = $all['alacarte'] ?? [];
                break;
                
            case 'get_fullservice':
                $response = $masterData->getFullService();
                break;
                
            case 'get_addons':
                $response = $masterData->getAddons();
                break;
                
            case 'get_graduation':
                $response = $masterData->getGraduation();
                break;
                
            case 'get_payment_terms':
                $response = $masterData->getPaymentTerms();
                break;
                
            default:
                throw new Exception("Unknown action: $action");
        }
        
        echo json_encode(['success' => true, 'data' => $response, 'source' => 'MySQL'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // POST — Update master data (manager/admin only)
    if ($method === 'POST') {
        requireRoleAPI('admin', 'manager');
        
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $type = $body['type'] ?? null;
        
        if (!$type) {
            throw new Exception('Type parameter required');
        }
        
        // Route to appropriate update handler
        $response = match($type) {
            'overhead' => $masterData->updateOverhead($body['data'] ?? []),
            'cetak_factors' => $masterData->updateCetakFactors($body['data'] ?? []),
            'cetak_base' => $masterData->updateCetakBase($body['data'] ?? []),
            'alacarte_factors' => $masterData->updateAlaCarteFactors($body['data'] ?? []),
            'fullservice' => $masterData->updateFullService($body['data'] ?? []),
            'addons' => $masterData->updateAddons($body['data'] ?? []),
            'graduation' => $masterData->updateGraduation($body['data'] ?? []),
            'payment_terms' => $masterData->updatePaymentTerms($body['data'] ?? []),
            default => throw new Exception("Unknown update type: $type")
        };
        
        echo json_encode(
            ['success' => true, 'message' => 'Master data updated', 'data' => $response, 'source' => 'MySQL'],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
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
?>
