<?php
/**
 * Parama HPP — Penutup PDF API
 *
 * Menyimpan & mengambil teks paragraf penutup di PDF penawaran.
 * Data disimpan di tbl_settings dengan key 'pdf_penutup'.
 *
 * GET  /api/penutup-pdf.php          — ambil teks penutup
 * POST /api/penutup-pdf.php  {text}  — simpan teks penutup
 */

session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireAuth();
header('Content-Type: application/json; charset=utf-8');

const DEFAULT_PENUTUP = 'Demikian penawaran yang kami sampaikan. Besar harapan kami untuk dapat berpartisipasi dalam project Anda. Hal–hal yang belum termasuk dan diatur di sini akan dibicarakan di kemudian hari apabila penawaran ini disetujui. Atas perhatian dan kerjasamanya kami sampaikan terima kasih.';

try {
    $pdo = getMySQLConnection();
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
        exit;
    }

    // Pastikan tabel tbl_settings punya kolom yang benar
    // (sudah ada di sistem, cukup gunakan langsung)

    $method = $_SERVER['REQUEST_METHOD'];

    // ── GET ───────────────────────────────────────────────────
    if ($method === 'GET') {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'pdf_penutup' LIMIT 1");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $text = $row ? $row['setting_value'] : DEFAULT_PENUTUP;

        echo json_encode([
            'success' => true,
            'text'    => $text,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ── POST ──────────────────────────────────────────────────
    if ($method === 'POST') {
        requireRoleAPI('admin', 'manager');

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $text = trim($body['text'] ?? '');

        if ($text === '') {
            throw new Exception('Teks penutup tidak boleh kosong.');
        }

        // UPSERT — insert atau update jika sudah ada
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value)
            VALUES ('pdf_penutup', ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$text]);

        echo json_encode([
            'success' => true,
            'message' => 'Teks penutup berhasil disimpan.',
        ], JSON_UNESCAPED_UNICODE);
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
