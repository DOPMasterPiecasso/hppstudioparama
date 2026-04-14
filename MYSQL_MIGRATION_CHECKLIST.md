# MySQL Migration Checklist

## ✅ Sudah Dibuat

### 1. Database Schema
- **File:** `database/mysql_schema.sql`
- **Berisi:** 10 tables + seed data awal
- **Tables:**
  - `overhead` - Biaya overhead
  - `overhead_total` - Total cached
  - `pricing_factors` - Cetak & à la carte factors
  - `fullservice_pricing` - Range pricing
  - `cetak_base` - Base cetak pricing  
  - `addons` - Add-on products
  - `graduation_packages` - Grad packages
  - `graduation_addons` - Grad add-ons
  - `graduation_cetak` - Grad cetak pricing
  - `payment_terms` - Payment terms

### 2. Migration Script
- **File:** `migrate_json_to_mysql.php`
- **Fungsi:** Otomatis transfer data dari JSON ke MySQL
- **Berjalan:** `php migrate_json_to_mysql.php`

### 3. Database Configuration
- **File:** `config/db.php`
- **Ditambah:** `getMySQLConnection()` function
- **Ditambah:** `MySQLMasterData` class dengan semua CRUD methods

### 4. New API Endpoint
- **File:** `api/master-data-mysql.php`
- **Fitur:** GET & POST master data dari MySQL
- **Kompatibel:** Sama dengan endpoint JSON lama

### 5. Documentation
- **File:** `MYSQL_SETUP.md` 
- **Isi:** Step-by-step setup, troubleshooting, backup procedures

---

## ⏳ Langkah Selanjutnya

### STEP 1: Siapkan MySQL Database
```bash
# Import schema ke MySQL
mysql -u root -p < database/mysql_schema.sql

# atau manual di MySQL console:
# CREATE DATABASE parama_hpp CHARACTER SET utf8mb4;
# USE parama_hpp;
# SOURCE database/mysql_schema.sql;
```

### STEP 2: Konfigurasi Credentials
Edit `config/db.php` baris 8-14:
```php
$MySQL_Config = [
    'host' => 'localhost',          // Sesuaikan
    'port' => 3306,                 // Sesuaikan
    'name' => 'parama_hpp',         // DB name
    'user' => 'root',               // MySQL user
    'pass' => 'password_here',      // MySQL password
    'charset' => 'utf8mb4'
];
```

**ATAU gunakan .env:**
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=parama_hpp
DB_USER=parama_user
DB_PASS=your_password
```

### STEP 3: Jalankan Migration
```bash
php migrate_json_to_mysql.php
```

**Expected Output:**
```
=== Parama HPP - JSON → MySQL Migration ===

[1/6] Checking database tables...
✓ Database tables ready

[2/6] Migrating Overhead...
  ✓ designer: 20,000,000
  ...

... (data import logs) ...

=== ✓ MIGRATION COMPLETE ===
Time: 0.XXs
```

### STEP 4: Verify Data Migration
```bash
# Check di MySQL:
mysql -u root -p parama_hpp
> SELECT COUNT(*) FROM overhead;
> SELECT SUM(amount) as total FROM overhead WHERE name != 'total';
> SELECT * FROM pricing_factors LIMIT 5;
```

### STEP 5: Update Header untuk MySQL (Optional)
**File:** `includes/header.php`

Current (JSON):
```php
$settings = $db->getSettings();
```

Update to (MySQL):
```php
$pdo = getMySQLConnection();
if ($pdo) {
    $masterData = getMySQLMasterData($pdo);
    // Use MySQL
} else {
    // Fallback to JSON
}
```

### STEP 6: Test Aplikasi
1. Buka halaman **Pengaturan**
2. Cek data overhead tampil dengan benar
3. Edit 1 item overhead → Simpan
4. Verify tersimpan di MySQL (bukan JSON)
5. Buka halaman **Ringkasan** → cek overhead breakdown

### STEP 7: Update API Endpoint (Jika Diperlukan)
**File:** `includes/header.php` baris 104

Current:
```javascript
const MASTER_API = '/api/master-data.php';
```

Update to (untuk MySQL):
```javascript
const MASTER_API = '/api/master-data-mysql.php';
```

---

## 📋 Configuration Files Modified

### config/db.php
- ✅ Added `getMySQLConnection()` function
- ✅ Added PDO configuration
- ✅ Added `MySQLMasterData` class
- ✅ Added `getMySQLMasterData()` helper

### Baru Dibuat:
- ✅ `database/mysql_schema.sql`
- ✅ `migrate_json_to_mysql.php`
- ✅ `api/master-data-mysql.php`
- ✅ `MYSQL_SETUP.md` (documentation)

---

## 🧪 Testing Commands

### Test MySQL Connection
```bash
php -r "require_once 'config/db.php'; \$p=getMySQLConnection(); echo \$p?'✓ OK':'✗ Failed';"
```

### Test API Response
```bash
# Dengan authentication, atau setup header dengan token/session
curl -b "PHPSESSID=your_session" http://localhost/api/master-data-mysql.php?action=get_overhead
```

### Test Overhead Data
```bash
mysql -u root -p parama_hpp -e "SELECT name, amount FROM overhead WHERE active=1;"
```

---

## ⚠️ Important Notes

1. **Backup JSON Files** - Jangan hapus `/data/*.json` sampai yakin semua OK
2. **Test di Local Dulu** - Sebelum production
3. **Database Credentials** - Secure & jangan hardcode sensitive data
4. **User Roles** - Only `admin` & `manager` bisa update master data
5. **Concurrent Updates** - MySQL handle better than JSON

---

## 🔄 Hybrid Mode (JSON + MySQL)

Jika ingin gradual migration (terus pakai JSON sambil migrasi):

1. Buat view di MySQL yang mirror ke JSON
2. Or sebaliknya, sync MySQL ke JSON files
3. API bisa fallback ke JSON jika MySQL down

---

## 📞 Troubleshooting Quick Links

| Problem | Solution |
|---------|----------|
| MySQL Connection Error | Verify credentials & server running |
| Migration Fails | Check JSON files exist & readable |
| Data Not Showing | Clear cache, check API endpoint |
| Update Not Saving | Check user role, verify API response |
| Slow Performance | Check indexes, enable query caching |

---

## ✨ Status

| Component | Status | Notes |
|-----------|--------|-------|
| Schema | ✅ Ready | 10 tables + indexes |
| Migration Script | ✅ Ready | Automatic JSON to MySQL |
| Config Updates | ✅ Done | PDO connection added |
| API Endpoint | ✅ Ready | Fully functional MySQL API |
| Documentation | ✅ Done | Complete setup guide |
| Testing | ⏳ Pending | User to test |
| Production | ⏳ Pending | After testing |

---

**Next:** Start from STEP 1 dalam "Langkah Selanjutnya" section! 🚀
