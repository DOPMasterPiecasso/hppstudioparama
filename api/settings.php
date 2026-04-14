<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireAuth();
header('Content-Type: application/json');

try {
    $db = getDB();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET — ambil semua settings
    if ($method === 'GET') {
        $settings = $db->getSettings();
        $addons = $db->getAddons();
        $cetakBase = $db->getCetakBase();
        $graduation = $db->getGraduation();
        
        // Format response dengan struktur yang diharapkan app.js
        $response = [
            'overhead' => $settings['overhead'] ?? [],
            'cetak_f' => $settings['pricing_factors']['cetak'] ?? ['handy' => 1.0, 'minimal' => 0.95, 'large' => 1.15],
            'alc_f' => $settings['pricing_factors']['alacarte'] ?? ['ebook' => 0.72, 'editcetak' => 0.62, 'desain' => 0.22, 'cetakonly' => 0.30],
            'cetak_base' => is_array($cetakBase) ? array_values($cetakBase) : [],
            'fs_prices' => $settings['fullservice_pricing'] ?? [],
            'grad_packages' => $graduation['packages'] ?? [],
            'grad_addons' => $graduation['addons'] ?? [],
            'grad_cetak' => $graduation['cetak'] ?? [],
            'addon_data' => $addons,
        ];
        
        echo json_encode(['data' => $response]);
        exit;
    }
    
    // POST — update settings
    if ($method === 'POST') {
        requireRole('admin', 'manager');
        
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $settings = $db->getSettings();
        
        // Handle each setting type
        if (isset($body['overhead'])) {
            $settings['overhead'] = $body['overhead'];
        }
        
        if (isset($body['cetak_f'])) {
            $settings['pricing_factors']['cetak'] = $body['cetak_f'];
        }
        
        if (isset($body['alc_f'])) {
            $settings['pricing_factors']['alacarte'] = $body['alc_f'];
        }
        
        if (isset($body['cetak_base'])) {
            $cetakData = $body['cetak_base'];
            if (is_array($cetakData) && !empty($cetakData)) {
                // Re-index the cetak base data and save to cetak_base.json
                $formattedCetak = [];
                foreach ($cetakData as $item) {
                    $formattedCetak[] = [
                        'lo' => $item['lo'] ?? 0,
                        'hi' => $item['hi'] ?? 0,
                        'label' => $item['label'] ?? '',
                        'pages' => $item['pages'] ?? []
                    ];
                }
                $db->saveFile('cetak_base.json', $formattedCetak);
            } elseif ($body['cetak_base'] === null) {
                // Reset to default
                $db->saveFile('cetak_base.json', []);
            }
        }
        
        if (isset($body['fs_prices'])) {
            $settings['fullservice_pricing'] = $body['fs_prices'];
        }
        
        if (isset($body['grad_packages']) || isset($body['grad_addons']) || isset($body['grad_cetak'])) {
            $graduation = $db->getGraduation();
            if (isset($body['grad_packages'])) $graduation['packages'] = $body['grad_packages'];
            if (isset($body['grad_addons'])) $graduation['addons'] = $body['grad_addons'];
            if (isset($body['grad_cetak'])) $graduation['cetak'] = $body['grad_cetak'];
            
            $db->saveFile('graduation.json', $graduation);
        }
        
        if (isset($body['addon_data'])) {
            $db->saveFile('addons.json', $body['addon_data']);
        }
        
        // Save main settings.json
        $db->saveSettings($settings);
        
        echo json_encode(['success' => true, 'message' => 'Settings updated']);
        exit;
    }
    
    // Method not allowed
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
