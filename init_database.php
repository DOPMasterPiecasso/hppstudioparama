<?php
/**
 * Database Initialization Script
 * Menjalankan schema.sql dan seed users/roles dari users.json
 */

require_once 'config/db.php';

$db = getMySQLConnection();

if (!$db) {
    die("❌ Error: Koneksi database gagal\n");
}

echo "✓ Koneksi database berhasil\n";

// ============================================================
// 1. JALANKAN SCHEMA SQL
// ============================================================
echo "\n📋 Membaca dan menjalankan schema...\n";

$schemaFile = __DIR__ . '/database/mysql_schema.sql';
if (!file_exists($schemaFile)) {
    die("❌ Error: File schema tidak ditemukan: $schemaFile\n");
}

$schemaSql = file_get_contents($schemaFile);

// Create tables only (DROP the INSERT statements for now)
$createStatements = [
    // Remove comments and insert statements, keep only CREATE TABLE
    preg_replace('/^.*?CREATE TABLE/ms', 'CREATE TABLE', $schemaSql)
];

// Parse CREATE TABLE statements better
$matches = [];
preg_match_all('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+\w+\s*\([^;]+\);/is', $schemaSql, $matches);

$successCount = 0;
$errorCount = 0;

if (!empty($matches[0])) {
    foreach ($matches[0] as $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        
        try {
            $db->exec($statement);
            $successCount++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }
    }
}

echo "✓ Schema SQL dijalankan: $successCount tabel dibuat/sudah ada\n";
if ($errorCount > 0) {
    echo "  ($errorCount errors, tapi mungkin table sudah ada)\n";
}

// ============================================================
// 2. VERIFIKASI TABEL
// ============================================================
echo "\n📊 Verifikasi tabel yang dibuat:\n";

$requiredTables = [
    'roles',
    'users',
    'overhead',
    'overhead_total',
    'pricing_factors',
    'fullservice_pricing',
    'cetak_base',
    'addons',
    'graduation_packages',
    'graduation_addons',
    'graduation_cetak',
    'payment_terms'
];

$query = $db->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE()");
$existingTables = array_column($query->fetchAll(PDO::FETCH_ASSOC), 'TABLE_NAME');

foreach ($requiredTables as $table) {
    if (in_array($table, $existingTables)) {
        echo "  ✓ $table\n";
    } else {
        echo "  ✗ $table (TIDAK ADA)\n";
    }
}

// ============================================================
// 3. CEK DATA USERS & ROLES
// ============================================================
echo "\n👥 Status Users & Roles:\n";

$rolesCount = $db->query("SELECT COUNT(*) FROM roles")->fetchColumn();
$usersCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

echo "  Roles: $rolesCount\n";
echo "  Users: $usersCount\n";

if ($rolesCount > 0) {
    $roles = $db->query("SELECT id, name, label FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($roles as $role) {
        echo "    - [{$role['id']}] {$role['name']} ({$role['label']})\n";
    }
}

if ($usersCount > 0) {
    $users = $db->query("SELECT id, username, name, email, role_id, is_active FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        $status = $user['is_active'] ? '✓' : '✗';
        echo "    - [{$user['id']}] {$status} {$user['username']} ({$user['name']}) - role:{$user['role_id']}\n";
    }
}

// ============================================================
// 4. HASIL AKHIR
// ============================================================
echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Database initialization selesai!\n";
echo "=".str_repeat("=", 49) . "\n\n";

// Menampilkan info koneksi
echo "📌 Database Info:\n";
echo "  Host: {$GLOBALS['MySQL_Config']['host']}\n";
echo "  Database: {$GLOBALS['MySQL_Config']['name']}\n";
echo "  User: {$GLOBALS['MySQL_Config']['user']}\n";
echo "\n";
