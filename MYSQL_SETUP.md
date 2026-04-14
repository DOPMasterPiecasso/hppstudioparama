# Parama HPP - MySQL Database Setup Guide

## Overview
Migrasi dari JSON-based storage ke MySQL database untuk better scalability dan performance.

## Prerequisites
- PHP 7.4+
- MySQL 5.7+ atau MariaDB 10.2+
- PDO MySQL extension

## Setup Steps

### 1. Create Database & Import Schema

```bash
# Connect ke MySQL sebagai root/admin
mysql -u root -p

# atau gunakan script berikut
mysql -u root -p < database/mysql_schema.sql
```

**Di dalam MySQL console:**
```sql
-- Create database
CREATE DATABASE parama_hpp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Import schema
USE parama_hpp;
SOURCE /path/to/database/mysql_schema.sql;

-- Verify
SHOW TABLES;
```

### 2. Configure Database Connection

**File: Create `.env` file di root project (atau edit di config/db.php)**

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=parama_hpp
DB_USER=parama_user
DB_PASS=your_strong_password
```

**atau edit langsung di `config/db.php` (lines 8-14):**
```php
$MySQL_Config = [
    'host' => 'localhost',
    'port' => 3306,
    'name' => 'parama_hpp',
    'user' => 'parama_user',
    'pass' => 'your_password',
    'charset' => 'utf8mb4'
];
```

### 3. Create MySQL User (Optional but Recommended)

```sql
CREATE USER 'parama_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON parama_hpp.* TO 'parama_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Migrate Data from JSON to MySQL

**Run migration script:**
```bash
# From project root
php migrate_json_to_mysql.php
```

**Output:**
```
=== Parama HPP - JSON → MySQL Migration ===

[1/6] Checking database tables...
✓ Database tables ready

[2/6] Migrating Overhead...
  ✓ designer: 20,000,000
  ✓ marketing: 15,000,000
  ...
  ✓ Total: 74,586,000

[3/6] Migrating Pricing Factors...
  ✓ cetak.handy = 1
  ✓ cetak.minimal = 0.95
  ...

[4/6] Migrating Full Service Pricing...
  ✓ Migrated 19 pricing tiers

[5/6] Migrating Graduation Packages...
  ✓ Package: Basic
  ...

[6/6] Migrating Add-ons...
  ✓ Hardcover Book: Rp 50,000
  ...

=== ✓ MIGRATION COMPLETE ===
Time: 0.42s
```

### 5. Verify Migration

**Check data in MySQL:**
```sql
SELECT * FROM overhead;
SELECT * FROM pricing_factors;
SELECT COUNT(*) as total FROM fullservice_pricing;
```

### 6. Update Application to Use MySQL

**Option A: Update existing `/api/master-data.php`**
- Replace calls to `$pdo->query()` untuk JSONDb
- Use `getMySQLMasterData($pdo)` instead

**Option B: Use new endpoint `/api/master-data-mysql.php`**
- Di `includes/header.php`, change:
  ```php
  const MASTER_API = '/api/master-data-mysql.php';
  ```

### 7. Update header.php for MySQL

**File: `includes/header.php` (around line 20-65)**

Change from:
```php
$settings = $db->getSettings();  // JSONDb
```

To:
```php
// Try MySQL first, fallback to JSON
$pdo = getMySQLConnection();
if ($pdo) {
    $masterData = getMySQLMasterData($pdo);
    $settings = [
        'overhead' => $masterData->getOverhead(),
        'pricing_factors' => $masterData->getPricingFactors(),
        'fullservice_pricing' => $masterData->getFullService(),
        'addons' => $masterData->getAddons(),
        'graduation' => $masterData->getGraduation(),
        'payment_terms' => $masterData->getPaymentTerms()
    ];
} else {
    // Fallback to JSON
    $settings = $db->getSettings();
}
```

## Testing

### 1. Test Database Connection
```bash
php -r "
require_once 'config/db.php';
\$pdo = getMySQLConnection();
echo \$pdo ? '✓ Connected to MySQL' : '✗ Connection failed';
"
```

### 2. Test API Endpoints
```bash
# Get all master data
curl http://localhost/api/master-data-mysql.php?action=get_all

# Get overhead only
curl http://localhost/api/master-data-mysql.php?action=get_overhead

# POST update
curl -X POST http://localhost/api/master-data-mysql.php \
  -H "Content-Type: application/json" \
  -d '{"type":"overhead","data":{"designer":21000000}}'
```

### 3. Test Halaman Aplikasi
- Login ke aplikasi
- Buka halaman **Pengaturan** → cek data overhead, pricing, dsb
- Buka **Ringkasan** → cek overhead breakdown
- Edit data → Simpan → Verify tersimpan ke MySQL

## Database Schema

### Tables
- `overhead` - Biaya overhead & gaji tim
- `overhead_total` - Total overhead (cached)
- `pricing_factors` - Faktor harga cetak & à la carte
- `fullservice_pricing` - Pricing tiers per range siswa
- `cetak_base` - Base pricing untuk cetak
- `addons` - Add-on produk
- `graduation_packages` - Paket graduation
- `graduation_addons` - Add-on graduation
- `graduation_cetak` - Harga cetak graduation
- `payment_terms` - Istilah pembayaran

## Troubleshooting

### MySQL Connection Failed
1. Verify MySQL server running: `mysql -u root -p`
2. Check credentials di `config/db.php`
3. Verify database exists: `SHOW DATABASES;`
4. Check user permissions: `SHOW GRANTS FOR 'parama_user'@'localhost';`

### Migration Script Errors
1. Check database tables created: `SHOW TABLES;`
2. Verify JSON files exist in `/data/` folder
3. Check file permissions: `ls -la data/`
4. Run with verbose: Add `echo` di migration script

### Data Not Showing in App
1. Clear browser cache (Ctrl+Shift+Del)
2. Check API endpoint returns data: `curl /api/master-data-mysql.php?action=get_overhead`
3. Check browser DevTools Console for errors
4. Verify `MASTER_API` constant uses correct endpoint

### Update Not Saving
1. Check user role is `admin` or `manager`
2. Verify API response success: Check Network tab in DevTools
3. Check MySQL error log: `/var/log/mysql/error.log`
4. Ensure `active = 1` in table rows

## Backup & Recovery

### Backup MySQL Data
```bash
# Full database backup
mysqldump -u parama_user -p parama_hpp > backup_parama_hpp_2026-04-14.sql

# Compressed backup
mysqldump -u parama_user -p parama_hpp | gzip > backup_parama_hpp_2026-04-14.sql.gz
```

### Restore from Backup
```bash
# Create fresh database
mysql -u root -p < database/mysql_schema.sql

# Restore data
mysql -u parama_user -p parama_hpp < backup_parama_hpp_2026-04-14.sql
```

## JSON to MySQL Differences

| Aspect | JSON | MySQL |
|--------|------|-------|
| Performance | Slower for large data | Faster queries |
| Consistency | Manual file locks | ACID transactions |
| Concurrent Access | Limited | Full support |
| Data Validation | Code-level | Schema-level + indexes |
| Backups | File copy | mysqldump |
| Scaling | Limited | Horizontal possible |

## Gradual Migration (Optional)

Jika ingin keep both JSON & MySQL:

1. **Keep JSON as fallback:**
```php
// In includes/header.php
if ($pdo) {
    // Use MySQL
} else {
    // Fallback to JSON
    $db->getSettings();
}
```

2. **API should support both:**
```php
// In api/master-data.php
// Try MySQL first
// IF fail, use JSON
```

## Performance Tips

1. **Add Indexes** - Already in schema (active, category, students range)
2. **Connection Pooling** - Use persistent connections if available
3. **Query Caching** - Enable in MySQL config
4. **Backup Strategy** - scheduled daily mysqldump

## Next Steps

1. ✅ Run `php migrate_json_to_mysql.php`
2. ✅ Test halaman aplikasi
3. ✅ Verify all data sync correctly
4. ✅ Update MASTER_API endpoint if needed
5. ✅ Archive JSON files (keep as backup)
6. ✅ Document changes di team

## Support

Jika ada issues:
1. Check error logs: `tail -f /var/log/mysql/error.log`
2. Check application logs
3. Verify database schema: `SHOW CREATE TABLE overhead;`
4. Test connection: `php -r "require_once 'config/db.php'; var_dump(getMySQLConnection());"`
