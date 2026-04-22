<?php
// ============================================================
// API: PDF Ketentuan Master Data
// Endpoint: /api/pdf-ketentuan.php
// ============================================================
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/auth_middleware.php';

AuthMiddleware::requireLogin();
$user = AuthMiddleware::getUser();

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    // GET: Fetch PDF Ketentuan
    if ($method === 'GET') {
        $type = $_GET['type'] ?? '';
        $validTypes = ['fullservice', 'graduation', 'alacarte'];
        
        $sql = "SELECT id, package_type, text_content as text, display_order, active 
                FROM pdf_ketentuan WHERE active = 1 ";
        $params = [];
        
        if (in_array($type, $validTypes)) {
            $sql .= "AND package_type = ? ";
            $params[] = $type;
        }
        
        $sql .= "ORDER BY display_order ASC, id ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // POST: Manage CRUD for PDF Ketentuan
    if ($method === 'POST') {
        AuthMiddleware::requireRole(['admin', 'manager']);
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['action'])) {
            throw new Exception("Invalid payload or missing action.");
        }
        
        $action = $input['action'];
        
        if ($action === 'create') {
            $pkg = $input['package_type'] ?? '';
            $text = $input['text'] ?? '';
            $order = (int)($input['display_order'] ?? 0);
            
            if (!in_array($pkg, ['fullservice', 'graduation', 'alacarte']) || empty($text)) {
                throw new Exception("Package type and text are required.");
            }
            
            $stmt = $pdo->prepare("INSERT INTO pdf_ketentuan (package_type, text_content, display_order) VALUES (?, ?, ?)");
            $stmt->execute([$pkg, $text, $order]);
            
            echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
            exit;
        }
        
        if ($action === 'update') {
            $id = $input['id'] ?? null;
            $text = $input['text'] ?? '';
            
            if (!$id || empty($text)) {
                throw new Exception("ID and text are required.");
            }
            
            $stmt = $pdo->prepare("UPDATE pdf_ketentuan SET text_content = ? WHERE id = ?");
            $stmt->execute([$text, $id]);
            
            echo json_encode(['success' => true]);
            exit;
        }
        
        if ($action === 'delete') {
            $id = $input['id'] ?? null;
            if (!$id) {
                throw new Exception("ID is required.");
            }
            
            $stmt = $pdo->prepare("DELETE FROM pdf_ketentuan WHERE id = ?");
            $stmt->execute([$id]);
            
            echo json_encode(['success' => true]);
            exit;
        }
        
        if ($action === 'reorder') {
            $items = $input['items'] ?? []; // [{id: 1, order: 1}, ...]
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("UPDATE pdf_ketentuan SET display_order = ? WHERE id = ?");
            foreach ($items as $item) {
                $stmt->execute([$item['order'], $item['id']]);
            }
            $pdo->commit();
            echo json_encode(['success' => true]);
            exit;
        }
        
        throw new Exception("Unknown action.");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
