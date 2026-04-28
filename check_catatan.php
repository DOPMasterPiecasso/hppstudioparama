<?php
require_once 'config/db.php';
$pdo = getMySQLConnection();

echo "=== Checking penawaran table structure ===\n\n";

// Get table structure
$stmt = $pdo->query("DESCRIBE penawaran");
echo "Table columns:\n";
foreach ($stmt as $col) {
    echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
}

echo "\n=== Sample data from penawaran table ===\n\n";

// Get sample data
$stmt = $pdo->query("SELECT id, nama_klien, paket, catatan FROM penawaran LIMIT 3");
$data = $stmt->fetchAll();

if (!empty($data)) {
    foreach ($data as $idx => $row) {
        echo "--- Record " . ($idx + 1) . " ---\n";
        echo "ID: " . $row['id'] . "\n";
        echo "Klien: " . $row['nama_klien'] . "\n";
        echo "Paket: " . $row['paket'] . "\n";
        echo "Catatan: " . $row['catatan'] . "\n\n";
    }
} else {
    echo "Tidak ada data di table penawaran\n";
}
?>
