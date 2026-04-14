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
    $pdo = getDB();
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
                
            default:
                throw new Exception('Action not recognized: ' . $action);
        }
        
        echo json_encode(['success' => true, 'data' => $response], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
        
        $response = updateMasterData($pdo, $type, $body);
        
        echo json_encode(['success' => true, 'message' => 'Master data updated', 'data' => $response], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
    // Load dari settings.json
    $settingsPath = __DIR__ . '/../data/settings.json';
    if (file_exists($settingsPath)) {
        $data = json_decode(file_get_contents($settingsPath), true);
        if (isset($data['overhead'])) {
            return $data['overhead'];
        }
    }
    
    // Fallback ke database
    $stmt = $pdo->query("SELECT category, amount FROM overhead ORDER BY id");
    $result = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $result[$row['category']] = (int)$row['amount'];
    }
    return $result;
}

function getMasterCetakFactors($pdo) {
    $settingsPath = __DIR__ . '/../data/settings.json';
    if (file_exists($settingsPath)) {
        $data = json_decode(file_get_contents($settingsPath), true);
        if (isset($data['pricing_factors']['cetak'])) {
            return $data['pricing_factors']['cetak'];
        }
    }
    
    return [
        'handy' => 1.0,
        'minimal' => 0.95,
        'large' => 1.15
    ];
}

function getMasterCetakBase($pdo) {
    $path = __DIR__ . '/../data/cetak_base.json';
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }
    return [];
}

function getMasterAlaCarteFactors($pdo) {
    $settingsPath = __DIR__ . '/../data/settings.json';
    if (file_exists($settingsPath)) {
        $data = json_decode(file_get_contents($settingsPath), true);
        if (isset($data['pricing_factors']['alacarte'])) {
            return $data['pricing_factors']['alacarte'];
        }
    }
    
    return [
        'ebook' => 0.72,
        'editcetak' => 0.62,
        'desain' => 0.22,
        'cetakonly' => 0.30
    ];
}

function getMasterFullService($pdo) {
    // Try to load from settings.json first
    $settingsPath = __DIR__ . '/../data/settings.json';
    if (file_exists($settingsPath)) {
        $data = json_decode(file_get_contents($settingsPath), true);
        if (isset($data['fullservice_pricing'])) {
            return $data['fullservice_pricing'];
        }
    }
    
    // Fallback to database if not in JSON
    $stmt = $pdo->query("
        SELECT package_type, min_students, max_students, price_per_book, max_pages
        FROM packages_fullservice
        ORDER BY package_type, min_students
    ");
    
    $result = ['handy' => [], 'minimal' => [], 'large' => []];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $result[$row['package_type']][] = [
            'min' => (int)$row['min_students'],
            'max' => (int)$row['max_students'],
            'price' => (int)$row['price_per_book'],
            'pages' => (int)$row['max_pages']
        ];
    }
    
    return $result;
}

function getMasterAddons($pdo) {
    $path = __DIR__ . '/../data/addons.json';
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        return $data;
    }
    return [];
}

function getMasterGraduation($pdo) {
    $path = __DIR__ . '/../data/graduation.json';
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        return $data;
    }
    return ['packages' => [], 'addons' => [], 'cetak' => []];
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
    $settingsPath = __DIR__ . '/../data/settings.json';
    $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
    
    // Ensure all overhead values are integers
    $cleanedOverhead = [];
    $total = 0;
    foreach ($data as $key => $value) {
        $val = (int)$value;
        $cleanedOverhead[$key] = $val;
        // Don't include 'total' in the sum calculation
        if (strtolower($key) !== 'total') {
            $total += $val;
        }
    }
    
    // Add calculated total if not already present
    if (!isset($cleanedOverhead['total'])) {
        $cleanedOverhead['total'] = $total;
    } else if ($total > 0) {
        // Update total if there are actual items
        $cleanedOverhead['total'] = $total;
    }
    
    $settings['overhead'] = $cleanedOverhead;
    $result = file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('Gagal menulis ke settings.json. Periksa izin file.');
    }
    
    return $cleanedOverhead;
}

function updateCetakFactors($pdo, $data) {
    $settingsPath = __DIR__ . '/../data/settings.json';
    $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
    
    if (!isset($settings['pricing_factors'])) {
        $settings['pricing_factors'] = [];
    }
    
    // Ensure data is numeric and properly formatted
    $cleanedFactors = [];
    foreach ($data as $k => $v) {
        $cleanedFactors[$k] = (float)$v;
    }
    
    $settings['pricing_factors']['cetak'] = $cleanedFactors;
    $result = file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('Gagal menulis faktor cetak ke settings.json.');
    }
    
    return $cleanedFactors;
}

function updateCetakBase($pdo, $data) {
    $path = __DIR__ . '/../data/cetak_base.json';
    
    // Ensure data directory exists
    $dataDir = dirname($path);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Validate and clean cetak_base data
    $cleanedData = [];
    if (is_array($data)) {
        foreach ($data as $range) {
            if (isset($range['lo']) && isset($range['hi']) && isset($range['pages'])) {
                $cleanedData[] = [
                    'lo' => (int)$range['lo'],
                    'hi' => (int)$range['hi'],
                    'label' => (string)($range['label'] ?? $range['lo'] . '–' . $range['hi'] . ' siswa'),
                    'pages' => is_array($range['pages']) ? array_map(function($v) { return (int)$v; }, $range['pages']) : []
                ];
            }
        }
    }
    
    $result = file_put_contents($path, json_encode($cleanedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('Gagal menulis ke cetak_base.json.');
    }
    
    return $cleanedData;
}

function updateAlaCarteFactors($pdo, $data) {
    $settingsPath = __DIR__ . '/../data/settings.json';
    $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
    
    if (!isset($settings['pricing_factors'])) {
        $settings['pricing_factors'] = [];
    }
    
    // Ensure data is numeric and properly formatted as decimals (0.0 to 1.0)
    $cleanedFactors = [];
    foreach ($data as $key => $val) {
        $num = (float)$val;
        
        // If value > 1, assume it was sent as percentage and convert to decimal
        // Otherwise, assume it's already a decimal and keep as-is
        if ($num > 1) {
            $num = $num / 100;
        }
        
        // Ensure value is between 0.01 and 1.0
        if ($num < 0.01 || $num > 1.0) {
            throw new Exception("Faktor À La Carte harus antara 1% dan 100% ($key: $num)");
        }
        
        $cleanedFactors[$key] = $num;
    }
    
    $settings['pricing_factors']['alacarte'] = $cleanedFactors;
    $result = file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('Gagal menulis faktor alacarte ke settings.json.');
    }
    
    return $cleanedFactors;
}

function updateFullService($pdo, $data) {
    // Save full service pricing to settings.json (under fullservice_pricing key)
    $settingsPath = __DIR__ . '/../data/settings.json';
    $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];
    
    $settings['fullservice_pricing'] = $data;
    $result = file_put_contents($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('Gagal menulis fullservice pricing ke settings.json.');
    }
    
    return $data;
}

function updateAddons($pdo, $data) {
    $path = __DIR__ . '/../data/addons.json';
    
    // Ensure data directory exists
    $dataDir = dirname($path);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Validate addon data structure
    $cleanedData = [];
    if (is_array($data)) {
        foreach ($data as $category => $items) {
            if (is_array($items)) {
                $cleanedData[$category] = [];
                foreach ($items as $item) {
                    if (isset($item['name']) && isset($item['id'])) {
                        $cleanedItem = [
                            'id' => (string)$item['id'],
                            'name' => (string)$item['name'],
                        ];
                        // Handle different addon types
                        if (isset($item['type'])) $cleanedItem['type'] = (string)$item['type'];
                        if (isset($item['tiers'])) $cleanedItem['tiers'] = $item['tiers'];
                        if (isset($item['price'])) $cleanedItem['price'] = (int)$item['price'];
                        
                        $cleanedData[$category][] = $cleanedItem;
                    }
                }
            }
        }
    }
    
    $result = file_put_contents($path, json_encode($cleanedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('Gagal menulis ke addons.json.');
    }
    
    return $cleanedData;
}

function updateGraduation($pdo, $data) {
    $path = __DIR__ . '/../data/graduation.json';
    
    // Ensure data directory exists
    $dataDir = dirname($path);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Ensure proper structure with default keys
    if (!isset($data['packages'])) $data['packages'] = [];
    if (!isset($data['addons'])) $data['addons'] = [];
    if (!isset($data['cetak'])) $data['cetak'] = [];
    
    // Validate and clean data
    $cleanedData = [
        'packages' => [],
        'addons' => [],
        'cetak' => []
    ];
    
    // Clean packages
    if (is_array($data['packages'])) {
        foreach ($data['packages'] as $pkg) {
            if (isset($pkg['name']) && isset($pkg['price'])) {
                $cleanedData['packages'][] = [
                    'id' => $pkg['id'] ?? 'pkg_' . substr(md5(microtime()), 0, 8),
                    'name' => (string)$pkg['name'],
                    'price' => (int)$pkg['price'],
                    'desc' => isset($pkg['desc']) ? (string)$pkg['desc'] : '',
                    'color' => isset($pkg['color']) ? (string)$pkg['color'] : ''
                ];
            }
        }
    }
    
    // Clean addons
    if (is_array($data['addons'])) {
        foreach ($data['addons'] as $addon) {
            if (isset($addon['name']) && isset($addon['price'])) {
                $cleanedData['addons'][] = [
                    'id' => $addon['id'] ?? 'addon_' . substr(md5(microtime()), 0, 8),
                    'name' => (string)$addon['name'],
                    'price' => (int)$addon['price']
                ];
            }
        }
    }
    
    // Clean cetak
    if (is_array($data['cetak'])) {
        foreach ($data['cetak'] as $cetak) {
            if (isset($cetak['name']) && isset($cetak['price'])) {
                $cleanedData['cetak'][] = [
                    'id' => $cetak['id'] ?? 'cetak_' . substr(md5(microtime()), 0, 8),
                    'name' => (string)$cetak['name'],
                    'price' => (int)$cetak['price']
                ];
            }
        }
    }
    
    $result = file_put_contents($path, json_encode($cleanedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('Gagal menulis ke graduation.json.');
    }
    
    return $cleanedData;
}

function updatePaymentTerms($pdo, $data) {
    $path = __DIR__ . '/../data/payment_terms.json';
    
    // Ensure data directory exists
    $dataDir = dirname($path);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    
    // Ensure proper data structure
    $paymentTermsData = $data;
    
    // If $data is an array of terms directly, wrap it in 'terms' key
    if (is_array($data) && !isset($data['terms'])) {
        // Check if data has 'terms' key or is just an array of term objects
        $firstKey = array_key_first($data);
        if (is_int($firstKey) || (is_string($firstKey) && !isset($data['id']))) {
            // It's an indexed array, wrap it
            $paymentTermsData = ['terms' => $data];
        }
    }
    
    // Validate each term has required fields
    if (isset($paymentTermsData['terms']) && is_array($paymentTermsData['terms'])) {
        foreach ($paymentTermsData['terms'] as &$term) {
            // Ensure all required fields
            if (!isset($term['id'])) $term['id'] = 'pt_' . substr(md5(microtime()), 0, 8);
            if (!isset($term['name'])) $term['name'] = 'Payment Term';
            if (!isset($term['deposit'])) $term['deposit'] = 0;
            if (!isset($term['desc'])) $term['desc'] = '';
            if (!isset($term['color'])) $term['color'] = '#9B59B6';
            
            // Ensure numeric deposit
            $term['deposit'] = (int)$term['deposit'];
        }
    }
    
    // Try to write file
    $result = file_put_contents($path, json_encode($paymentTermsData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    if ($result === false) {
        throw new Exception('Failed to write payment_terms.json - check file permissions');
    }
    
    return $paymentTermsData;
}
?>
