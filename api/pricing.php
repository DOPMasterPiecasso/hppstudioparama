<?php
/**
 * Parama HPP - Pricing Data API
 * API untuk fetch semua data pricing dari database
 */

require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'get_all';

try {
    $pdo = getDB();
    
    switch($action) {
        case 'get_all':
            // Return semua data sekaligus (untuk init aplikasi)
            $response = [
                'overhead' => getOverhead($pdo),
                'fullservice' => getFullService($pdo),
                'alacarte' => getAlaCarte($pdo),
                'alacarte_factors' => getAlaCarteFactors($pdo),
                'addons' => getAddons($pdo),
                'cetak_base' => getCetakBase($pdo),
                'cetak_factors' => getCetakFactors($pdo),
                'graduation' => getGraduation($pdo),
                'graduation_addons' => getGraduationAddons($pdo),
            ];
            break;
            
        case 'get_overhead':
            $response = getOverhead($pdo);
            break;
            
        case 'get_fullservice':
            $package = $_GET['package'] ?? null;
            $response = getFullService($pdo, $package);
            break;
            
        case 'get_alacarte':
            $response = getAlaCarte($pdo);
            break;
            
        case 'get_addons':
            $response = getAddons($pdo);
            break;
            
        case 'get_graduation':
            $response = getGraduation($pdo);
            break;
            
        case 'save_penawaran':
            $response = savePenawaran($pdo, $_POST);
            break;
            
        case 'get_penawarans':
            $month = $_GET['month'] ?? null;
            $status = $_GET['status'] ?? null;
            $response = getPenawarans($pdo, $month, $status);
            break;
            
        case 'update_penawaran_status':
            $response = updatePenawaranStatus($pdo, $_POST);
            break;
            
        case 'delete_penawaran':
            $response = deletePenawaran($pdo, $_POST);
            break;
            
        default:
            throw new Exception('Action tidak dikenali: ' . $action);
    }
    
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

// ===== GETTER FUNCTIONS =====

function getOverhead($pdo) {
    $stmt = $pdo->query("
        SELECT category, amount 
        FROM overhead 
        ORDER BY id
    ");
    $result = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $result[$row['category']] = (int)$row['amount'];
    }
    // Calculate total
    $result['total'] = array_sum($result);
    return $result;
}

function getFullService($pdo, $package = null) {
    $query = "
        SELECT package_type, min_students, max_students, price_per_book, max_pages
        FROM packages_fullservice
        ORDER BY package_type, min_students
    ";
    if ($package) {
        $query = "
            SELECT package_type, min_students, max_students, price_per_book, max_pages
            FROM packages_fullservice
            WHERE package_type = ?
            ORDER BY min_students
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$package]);
    } else {
        $stmt = $pdo->query($query);
    }
    
    $result = ['handy' => [], 'minimal' => [], 'large' => []];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $result[$row['package_type']][] = [
            (int)$row['min_students'],
            (int)$row['max_students'],
            (int)$row['price_per_book'],
            (int)$row['max_pages']
        ];
    }
    
    return $package ? $result[$package] : $result;
}

function getAlaCarte($pdo) {
    $stmt = $pdo->query("
        SELECT code, name, description, price_type, price_min, price_max, factor, 
               margin_target, includes, excludes, is_featured
        FROM packages_alacarte
        ORDER BY display_order, code
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAlaCarteFactors($pdo) {
    $stmt = $pdo->query("
        SELECT package_code, factor
        FROM alacarte_factors
    ");
    $result = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $result[$row['package_code']] = (float)$row['factor'];
    }
    return $result;
}

function getAddons($pdo) {
    $stmt = $pdo->query("
        SELECT 
            c.category_name,
            a.id,
            a.name,
            a.addon_type,
            a.flat_price,
            GROUP_CONCAT(
                JSON_OBJECT(
                    'tier_label', t.tier_label,
                    'min_quantity', t.min_quantity,
                    'max_quantity', t.max_quantity,
                    'price', t.price
                )
                ORDER BY t.min_quantity
            ) as tiers
        FROM addon_categories c
        LEFT JOIN addon_items a ON c.id = a.category_id
        LEFT JOIN addon_tiers t ON a.id = t.addon_item_id
        WHERE a.id IS NOT NULL
        GROUP BY a.id
        ORDER BY c.display_order, a.display_order
    ");
    
    $result = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $tiers = [];
        if ($row['tiers']) {
            $tier_objects = json_decode('[' . $row['tiers'] . ']', true);
            foreach ($tier_objects as $t) {
                $tiers[] = [
                    $t['tier_label'],
                    (int)$t['min_quantity'],
                    (int)($t['max_quantity'] ?? 999999),
                    (int)$t['price']
                ];
            }
        }
        
        $result[] = [
            'id' => $row['name'],
            'name' => $row['name'],
            'type' => $row['addon_type'],
            'price' => (int)($row['flat_price'] ?? 0),
            'tiers' => $tiers
        ];
    }
    return $result;
}

function getCetakBase($pdo) {
    $stmt = $pdo->query("
        SELECT DISTINCT min_students, max_students, pages_count, base_price
        FROM cetak_base
        ORDER BY min_students, pages_count
    ");
    
    $result = [
        'data' => [], // flattened
        'ranges' => [] // grouped by range
    ];
    
    $current_range = null;
    $pages_obj = [];
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $range_key = $row['min_students'] . '-' . $row['max_students'];
        
        if ($current_range !== $range_key && $current_range !== null) {
            $result['ranges'][] = [
                'label' => $current_range,
                'pages' => $pages_obj
            ];
            $pages_obj = [];
        }
        
        $current_range = $range_key;
        $pages_obj[(int)$row['pages_count']] = (int)$row['base_price'];
    }
    
    if ($current_range && !empty($pages_obj)) {
        $result['ranges'][] = [
            'label' => $current_range,
            'pages' => $pages_obj
        ];
    }
    
    return $result['ranges'] ?? [];
}

function getCetakFactors($pdo) {
    $stmt = $pdo->query("
        SELECT package_type, factor
        FROM cetak_factors
        ORDER BY package_type
    ");
    $result = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $result[$row['package_type']] = (float)$row['factor'];
    }
    return $result;
}

function getGraduation($pdo) {
    $stmt = $pdo->query("
        SELECT package_key, name, description, price, color_scheme, is_featured, transport_included
        FROM packages_graduation
        ORDER BY display_order, price DESC
    ");
    
    $result = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $result[] = [
            'id' => $row['package_key'],
            'name' => $row['name'],
            'desc' => $row['description'],
            'price' => (int)$row['price'],
            'color' => $row['color_scheme'] ?? '',
            'transport' => $row['transport_included'] ?? ''
        ];
    }
    return ['packages' => $result];
}

function getGraduationAddons($pdo) {
    $stmt = $pdo->query("
        SELECT addon_key, name, price, addon_type, unit
        FROM graduation_addons
        ORDER BY addon_type, name
    ");
    
    $addons = [];
    $cetak = [];
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $item = ['id' => $row['addon_key'], 'name' => $row['name'], 'price' => (int)$row['price']];
        
        if ($row['addon_type'] === 'cetak') {
            $cetak[] = $item;
        } else {
            $addons[] = $item;
        }
    }
    
    return [
        'addons' => $addons,
        'cetak' => $cetak
    ];
}

function savePenawaran($pdo, $data) {
    $required = ['client_name', 'total_price', 'final_price'];
    foreach ($required as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Field $field diperlukan");
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO penawaran (
            client_name, package, student_count, total_price,
            discount_type, discount_value, bonus_text, bonus_nominal,
            final_price, notes, status, created_by, month
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $month = date('Y-m');
    $created_by = $_SESSION['user'] ?? 'Staff';
    
    $stmt->execute([
        $data['client_name'],
        $data['package'] ?? null,
        $data['student_count'] ?? null,
        $data['total_price'],
        $data['discount_type'] ?? null,
        $data['discount_value'] ?? null,
        $data['bonus_text'] ?? null,
        $data['bonus_nominal'] ?? null,
        $data['final_price'],
        $data['notes'] ?? null,
        $data['status'] ?? 'pending',
        $created_by,
        $month
    ]);
    
    return [
        'success' => true,
        'id' => (int)$pdo->lastInsertId(),
        'message' => 'Penawaran berhasil disimpan'
    ];
}

function getPenawarans($pdo, $month = null, $status = null) {
    $query = "SELECT * FROM penawaran WHERE 1=1";
    $params = [];
    
    if ($month && $month !== 'all') {
        $query .= " AND month = ?";
        $params[] = $month;
    }
    
    if ($status && $status !== 'all') {
        $query .= " AND status = ?";
        $params[] = $status;
    }
    
    $query .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $result = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $row['id'] = (int)$row['id'];
        $row['student_count'] = (int)$row['student_count'];
        $row['total_price'] = (int)$row['total_price'];
        $row['final_price'] = (int)$row['final_price'];
        $result[] = $row;
    }
    
    return $result;
}

function updatePenawaranStatus($pdo, $data) {
    if (!isset($data['id']) || !isset($data['status'])) {
        throw new Exception("ID dan status diperlukan");
    }
    
    $stmt = $pdo->prepare("UPDATE penawaran SET status = ? WHERE id = ?");
    $stmt->execute([$data['status'], $data['id']]);
    
    return ['success' => true, 'message' => 'Status updated'];
}

function deletePenawaran($pdo, $data) {
    if (!isset($data['id'])) {
        throw new Exception("ID diperlukan");
    }
    
    $stmt = $pdo->prepare("DELETE FROM penawaran WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    return ['success' => true, 'message' => 'Penawaran deleted'];
}
?>
