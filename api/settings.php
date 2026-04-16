<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireAuth();
header('Content-Type: application/json; charset=utf-8');

try {
    // Get MySQL connection and master data handler
    $pdo = getMySQLConnection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed. Please check .env or DB credentials. Check PHP error logs for more info.']);
        exit;
    }
    $masterData = new MySQLMasterData($pdo);
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET — ambil semua settings dari database
    if ($method === 'GET') {
        $pricingFactors = $masterData->getPricingFactors();
        
        // Format response dengan struktur yang diharapkan app.js
        $response = [
            'overhead' => $masterData->getOverhead(),
            'cetak_f' => $pricingFactors['cetak'] ?? ['handy' => 1.0, 'minimal' => 0.95, 'large' => 1.15],
            'alc_f' => $pricingFactors['alacarte'] ?? ['ebook' => 0.72, 'editcetak' => 0.62, 'desain' => 0.22, 'cetakonly' => 0.30],
            'cetak_base' => $masterData->getCetakBase(),
            'fs_prices' => $masterData->getFullService(),
            'grad_packages' => $masterData->getGraduation()['packages'] ?? [],
            'grad_addons' => $masterData->getGraduation()['addons'] ?? [],
            'grad_cetak' => $masterData->getGraduation()['cetak'] ?? [],
            'addon_data' => $masterData->getAddons(),
            'payment_terms' => $masterData->getPaymentTerms(),
            'source' => 'MySQL Database'
        ];
        
        echo json_encode(['success' => true, 'data' => $response], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // POST — update settings dan save ke database
    if ($method === 'POST') {
        requireRole('admin', 'manager');
        
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $updates = [];
        
        // Update overhead
        if (isset($body['overhead'])) {
            $masterData->updateOverhead($body['overhead']);
            $updates[] = 'overhead';
        }
        
        // Update cetak factors
        if (isset($body['cetak_f'])) {
            $masterData->updateCetakFactors($body['cetak_f']);
            $updates[] = 'cetak_factors';
        }
        
        // Update alacarte factors
        if (isset($body['alc_f'])) {
            $masterData->updateAlaCarteFactors($body['alc_f']);
            $updates[] = 'alacarte_factors';
        }
        
        // Update cetak base
        if (isset($body['cetak_base'])) {
            $masterData->updateCetakBase($body['cetak_base']);
            $updates[] = 'cetak_base';
        }
        
        // Update full service pricing
        if (isset($body['fs_prices'])) {
            $masterData->updateFullService($body['fs_prices']);
            $updates[] = 'fullservice_prices';
        }
        
        // Update graduation packages
        if (isset($body['grad_packages']) || isset($body['grad_addons']) || isset($body['grad_cetak'])) {
            $graduationData = [];
            if (isset($body['grad_packages'])) $graduationData['packages'] = $body['grad_packages'];
            if (isset($body['grad_addons'])) $graduationData['addons'] = $body['grad_addons'];
            if (isset($body['grad_cetak'])) $graduationData['cetak'] = $body['grad_cetak'];
            
            $masterData->updateGraduation($graduationData);
            $updates[] = 'graduation';
        }
        
        // Update add-ons
        if (isset($body['addon_data'])) {
            $masterData->updateAddons($body['addon_data']);
            $updates[] = 'addons';
        }
        
        // Update payment terms
        if (isset($body['payment_terms'])) {
            $masterData->updatePaymentTerms($body['payment_terms']);
            $updates[] = 'payment_terms';
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Settings updated: ' . implode(', ', $updates),
            'updates' => $updates,
            'source' => 'MySQL Database'
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Method not allowed
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
