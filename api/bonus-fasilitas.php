<?php
/**
 * Parama HPP — Bonus & Fasilitas API
 *
 * CRUD master data bonus & fasilitas per tipe paket.
 * Data ini menggantikan hardcode di pdf.php dan app-pages.js.
 *
 * GET    /api/bonus-fasilitas.php                     — list semua (grouped by package_type)
 * GET    /api/bonus-fasilitas.php?type=fullservice     — list per tipe
 * POST   /api/bonus-fasilitas.php  {action:'create', label, detail, package_type, display_order?}
 * POST   /api/bonus-fasilitas.php  {action:'update', id, label?, detail?, display_order?, active?}
 * POST   /api/bonus-fasilitas.php  {action:'delete', id}
 * POST   /api/bonus-fasilitas.php  {action:'reorder', items:[{id, display_order},...]}
 */

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireAuth();
header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getMySQLConnection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];

    // ── GET ───────────────────────────────────────────────────
    if ($method === 'GET') {
        $type = $_GET['type'] ?? null;

        if ($type) {
            $stmt = $pdo->prepare(
                "SELECT id, package_type, kategori, label, detail, display_order, active
                 FROM bonus_fasilitas
                 WHERE package_type = ? AND active = 1
                 ORDER BY display_order ASC, id ASC"
            );
            $stmt->execute([$type]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
        } else {
            // Grouped by package_type
            $stmt = $pdo->query(
                "SELECT id, package_type, kategori, label, detail, display_order, active
                 FROM bonus_fasilitas
                 ORDER BY package_type, kategori, display_order ASC, id ASC"
            );
            $stmt->execute();
            $rows  = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $grouped = [];
            foreach ($rows as $row) {
                $grouped[$row['package_type']][] = $row;
            }
            echo json_encode(['success' => true, 'data' => $grouped], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // ── POST (create / update / delete / reorder) ─────────────
    if ($method === 'POST') {
        requireRoleAPI('admin', 'manager');

        $body   = json_decode(file_get_contents('php://input'), true) ?? [];
        $action = $body['action'] ?? 'create';

        switch ($action) {

            // ─ CREATE ─────────────────────────────────────────
            case 'create': {
                $pkgType  = trim($body['package_type'] ?? '');
                $kategori = trim($body['kategori'] ?? 'all');
                $label    = trim($body['label']        ?? '');
                $detail   = trim($body['detail']       ?? '');
                $order    = (int)($body['display_order'] ?? 99);

                if (!$pkgType || !$label || !$detail) {
                    throw new Exception('package_type, label, dan detail wajib diisi.');
                }
                $allowed = ['fullservice', 'graduation', 'alacarte'];
                if (!in_array($pkgType, $allowed)) {
                    throw new Exception('package_type tidak valid. Gunakan: ' . implode(', ', $allowed));
                }

                $stmt = $pdo->prepare(
                    "INSERT INTO bonus_fasilitas (package_type, kategori, label, detail, display_order)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([$pkgType, $kategori, $label, $detail, $order]);
                $newId = $pdo->lastInsertId();

                echo json_encode([
                    'success' => true,
                    'message' => 'Bonus berhasil ditambahkan.',
                    'id'      => $newId,
                ], JSON_UNESCAPED_UNICODE);
                break;
            }

            // ─ UPDATE ─────────────────────────────────────────
            case 'update': {
                $id = (int)($body['id'] ?? 0);
                if (!$id) throw new Exception('ID tidak valid.');

                $fields = [];
                $params = [];

                if (isset($body['kategori'])) {
                    $fields[] = 'kategori = ?';
                    $params[] = trim($body['kategori']);
                }
                if (isset($body['label'])) {
                    $fields[] = 'label = ?';
                    $params[] = trim($body['label']);
                }
                if (isset($body['detail'])) {
                    $fields[] = 'detail = ?';
                    $params[] = trim($body['detail']);
                }
                if (isset($body['display_order'])) {
                    $fields[] = 'display_order = ?';
                    $params[] = (int)$body['display_order'];
                }
                if (isset($body['active'])) {
                    $fields[] = 'active = ?';
                    $params[] = (int)(bool)$body['active'];
                }
                if (empty($fields)) {
                    throw new Exception('Tidak ada field yang diupdate.');
                }

                $params[] = $id;
                $sql = 'UPDATE bonus_fasilitas SET ' . implode(', ', $fields) . ' WHERE id = ?';
                $pdo->prepare($sql)->execute($params);

                echo json_encode(['success' => true, 'message' => 'Bonus berhasil diupdate.'], JSON_UNESCAPED_UNICODE);
                break;
            }

            // ─ DELETE ─────────────────────────────────────────
            case 'delete': {
                $id = (int)($body['id'] ?? 0);
                if (!$id) throw new Exception('ID tidak valid.');

                $pdo->prepare("DELETE FROM bonus_fasilitas WHERE id = ?")->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Bonus dihapus.'], JSON_UNESCAPED_UNICODE);
                break;
            }

            // ─ REORDER ────────────────────────────────────────
            case 'reorder': {
                $items = $body['items'] ?? [];
                if (empty($items)) throw new Exception('items[] wajib diisi.');

                $stmt = $pdo->prepare("UPDATE bonus_fasilitas SET display_order = ? WHERE id = ?");
                $pdo->beginTransaction();
                foreach ($items as $item) {
                    $stmt->execute([(int)$item['display_order'], (int)$item['id']]);
                }
                $pdo->commit();

                echo json_encode(['success' => true, 'message' => 'Urutan disimpan.'], JSON_UNESCAPED_UNICODE);
                break;
            }

            default:
                throw new Exception('Action tidak dikenal: ' . $action);
        }
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
