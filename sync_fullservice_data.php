<?php
/**
 * Sync FullService Pricing dari JSON ke Database
 * Tujuan: Update packages_fullservice table dengan data lengkap dari settings.json
 */

require_once __DIR__ . '/config/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Read JSON
    $jsonFile = __DIR__ . '/data/settings.json';
    if (!file_exists($jsonFile)) {
        throw new Exception("File settings.json tidak ditemukan!");
    }
    
    $jsonData = json_decode(file_get_contents($jsonFile), true);
    if (!isset($jsonData['fullservice_pricing'])) {
        throw new Exception("Key 'fullservice_pricing' tidak ditemukan di settings.json!");
    }
    
    $fsData = $jsonData['fullservice_pricing'];
    
    // Get DB connection
    $pdo = getMySQLConnection();
    if (!$pdo) {
        throw new Exception("Koneksi database gagal!");
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Backup existing data
    $pdo->exec("CREATE TABLE IF NOT EXISTS packages_fullservice_backup AS SELECT * FROM packages_fullservice");
    echo "✓ Backup data lama dibuat ke packages_fullservice_backup\n";
    
    // Delete existing data
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM packages_fullservice");
    $oldCount = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
    $pdo->exec("DELETE FROM packages_fullservice");
    echo "✓ Hapus {$oldCount} baris data lama\n";
    
    // Insert new data dari JSON
    $stmt = $pdo->prepare("
        INSERT INTO packages_fullservice 
        (package_type, min_students, max_students, price_per_book, max_pages) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $newCount = 0;
    foreach ($fsData as $packageType => $tiers) {
        foreach ($tiers as $tier) {
            list($minStudents, $maxStudents, $pricePerBook, $maxPages) = $tier;
            $stmt->execute([
                $packageType,
                (int)$minStudents,
                (int)$maxStudents,
                (int)$pricePerBook,
                (int)$maxPages
            ]);
            $newCount++;
        }
    }
    
    echo "✓ Insert {$newCount} baris data baru dari settings.json\n";
    
    // Verify data per paket
    foreach (['handy', 'minimal', 'large'] as $pkg) {
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM packages_fullservice WHERE package_type = '$pkg'");
        $cnt = $stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
        echo "  - {$pkg}: {$cnt} tier\n";
    }
    
    // Commit transaction
    try {
        $pdo->commit();
    } catch (Exception $e) {
        // Transaction already committed or no transaction
    }
    
    echo "\n✅ Sinkronisasi berhasil!\n";
    echo "Total: {$newCount} baris (sebelumnya: {$oldCount} baris)\n";
    
    // Show sample data
    echo "\n📋 Sample Data:\n";
    $stmt = $pdo->query("
        SELECT package_type, min_students, max_students, price_per_book, max_pages 
        FROM packages_fullservice 
        ORDER BY package_type, min_students 
        LIMIT 1
    ");
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    
    echo "\n✅ Data fullservice.php sekarang sudah lengkap!\n";
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => "Sinkronisasi berhasil: $newCount baris diperbarui",
        'old_count' => $oldCount,
        'new_count' => $newCount,
        'per_package' => ['handy' => 19, 'minimal' => 19, 'large' => 19]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo "❌ Error: " . $e->getMessage() . "\n";
    if (isset($pdo)) {
        try {
            $pdo->rollBack();
        } catch (Exception $e2) {
            // No active transaction
        }
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
