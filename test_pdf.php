<?php
session_start();
$_SESSION['user'] = ['id' => 1, 'username' => 'admin'];

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/vendor/autoload.php';

$db = getDB();
$id = 2;

// Get penawaran
$penawarans = $db->getPenawaran();
$p = null;
foreach ($penawarans as $penawaran) {
    if ($penawaran['id'] == $id) {
        $p = $penawaran;
        break;
    }
}

if (!$p) {
    die('Penawaran tidak ditemukan');
}

// Get user name
$users = $db->getUsers();
$userName = 'Unknown';
foreach ($users as $user) {
    if ($user['id'] == $p['added_by_id']) {
        $userName = $user['name'];
        break;
    }
}
$p['added_by_name'] = $userName;

echo "Data siap untuk PDF:\n";
echo "- ID: " . $p['id'] . "\n";
echo "- Klien: " . $p['nama_klien'] . "\n";
echo "- Paket: " . $p['paket'] . "\n";
echo "- Harga: " . $p['harga'] . "\n";
echo "- Added By: " . $p['added_by_name'] . "\n";
?>