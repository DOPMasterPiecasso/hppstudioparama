# MySQL Migration — Quick Reference

## File Structure

```
parama_hpp/
├── database/
│   └── mysql_schema.sql ..................... Schema & seed data
├── config/
│   └── db.php ............................. Updated: Added MySQL support
├── api/
│   ├── master-data.php ..................... Old JSON endpoint
│   └── master-data-mysql.php ✨ ........... New MySQL endpoint
├── migrate_json_to_mysql.php ✨ ........... Migration script
├── MYSQL_SETUP.md ✨ ....................... Detailed setup guide
├── MYSQL_MIGRATION_CHECKLIST.md ✨ ....... Quick checklist
└── data/
    ├── settings.json ....................... Old data (backup)
    ├── graduation.json ..................... Old data (backup)
    ├── addons.json ......................... Old data (backup)
    └── payment_terms.json .................. Old data (backup)
```

✨ = New files created

---

## Quick Setup (3 Steps)

### 1️⃣ Create Database
```bash
mysql -u root -p < database/mysql_schema.sql
```

Or in MySQL console:
```sql
CREATE DATABASE parama_hpp CHARACTER SET utf8mb4;
USE parama_hpp;
SOURCE database/mysql_schema.sql;
```

### 2️⃣ Configure Credentials
Edit `config/db.php` lines 8-14:
```php
'host' => 'localhost',
'name' => 'parama_hpp',
'user' => 'your_mysql_user',
'pass' => 'your_password',
```

### 3️⃣ Migrate Data
```bash
php migrate_json_to_mysql.php
```

Done! ✅

---

## Verify Setup

```bash
# Check connection
php -r "require_once 'config/db.php'; echo getMySQLConnection()?'✓':'✗';"

# Check tables
mysql -u root -p parama_hpp -e "SHOW TABLES;"

# Check data
mysql -u root -p parama_hpp -e "SELECT COUNT(*) FROM overhead;"
```

---

## API Endpoints

### Get All Data
```
GET /api/master-data-mysql.php?action=get_all
```
Returns: Semua master data

### Get Specific Data
```
GET /api/master-data-mysql.php?action=get_overhead
GET /api/master-data-mysql.php?action=get_fullservice
GET /api/master-data-mysql.php?action=get_addons
GET /api/master-data-mysql.php?action=get_graduation
```

### Update Data (POST)
```
POST /api/master-data-mysql.php
Content-Type: application/json

{
  "type": "overhead",
  "data": {
    "designer": 21000000,
    "marketing": 15000000
  }
}
```

---

## Database Tables

| Table | Purpose | Rows |
|-------|---------|------|
| `overhead` | Biaya overhead | ~7 |
| `overhead_total` | Total cached | 1 |
| `pricing_factors` | Cetak & à la carte | ~7 |
| `fullservice_pricing` | Pricing tiers | 19+ |
| `cetak_base` | Cetak base pricing | 7 |
| `addons` | Add-ons | 3+ |
| `graduation_packages` | Grad packages | ? |
| `graduation_addons` | Grad add-ons | ? |
| `graduation_cetak` | Grad cetak | ? |
| `payment_terms` | Payment terms | 4 |

---

## Common Tasks

### Backup Database
```bash
mysqldump -u root -p parama_hpp > backup.sql
```

### Restore Database  
```bash
mysql -u root -p parama_hpp < backup.sql
```

### Reset Database
```bash
mysql -u root -p -e "DROP DATABASE parama_hpp;"
mysql -u root -p < database/mysql_schema.sql
php migrate_json_to_mysql.php
```

### Check Total Overhead
```bash
mysql -u root -p parama_hpp -e \
  "SELECT SUM(amount) as total FROM overhead WHERE name != 'total';"
```

### List All Overhead Items
```bash
mysql -u root -p parama_hpp -e \
  "SELECT name, amount FROM overhead WHERE active=1 ORDER BY id;"
```

### Check Pricing Factors
```bash
mysql -u root -p parama_hpp -e \
  "SELECT category, factor_name, factor_value FROM pricing_factors;"
```

---

## Environment Variables (Optional)

Create `.env` file:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=parama_hpp
DB_USER=parama_user
DB_PASS=secure_password
```

Then in `config/db.php`:
```php
$MySQL_Config = [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') ?: '',
    ...
];
```

---

## Code Examples

### Get Master Data in Code
```php
<?php
require_once 'config/db.php';

$pdo = getMySQLConnection();
$master = getMySQLMasterData($pdo);

// Get overhead
$overhead = $master->getOverhead();
echo $overhead['designer']; // 20000000

// Get full service pricing
$fs = $master->getFullService();
foreach ($fs['handy'] as $tier) {
    list($lo, $hi, $price, $pages) = $tier;
    echo "$lo-$hi siswa: Rp $price\n";
}
?>
```

### Update Master Data
```php
<?php
require_once 'config/db.php';

$pdo = getMySQLConnection();
$master = getMySQLMasterData($pdo);

// Update overhead
$newOverhead = [
    'designer' => 21000000,
    'marketing' => 15000000,
];
$result = $master->updateOverhead($newOverhead);
var_dump($result);  // Shows updated data with new total
?>
```

---

## Troubleshooting

### "MySQL connection failed"
```bash
# Check MySQL running
sudo systemctl status mysql

# Or
ps aux | grep mysql

# Or connect directly
mysql -u root -p
```

### "Unknown database 'parama_hpp'"
```bash
# Import schema
mysql -u root -p < database/mysql_schema.sql

# Verify
mysql -u root -p -e "SHOW DATABASES;" | grep parama
```

### "Access denied for user"
```bash
# Verify credentials in config/db.php
# or test connection directly:
mysql -u parama_user -p parama_hpp -e "SELECT 1;"
```

### "SQLSTATE[00000]: Success: 1286"
- Means MySQL extension not loaded
- Check: `php -m | grep mysql`
- Install: `apt-get install php-mysql`

---

## Next Actions

1. ✅ Read this file (done!)
2. ⏳ See [MYSQL_SETUP.md](MYSQL_SETUP.md) for detailed steps
3. ⏳ See [MYSQL_MIGRATION_CHECKLIST.md](MYSQL_MIGRATION_CHECKLIST.md) for full checklist
4. ⏳ Follow 7-step setup in MYSQL_SETUP.md
5. ⏳ Test application
6. ⏳ Backup & archive JSON files

---

## Status

- ✅ Schema created
- ✅ Migration script created  
- ✅ API endpoint created
- ✅ Documentation written
- ⏳ **Waiting for you to setup!**

**Start:** `php migrate_json_to_mysql.php` 🚀

---

## Further Help

- **Setup Details:** See [MYSQL_SETUP.md](MYSQL_SETUP.md)
- **Checklist:** See [MYSQL_MIGRATION_CHECKLIST.md](MYSQL_MIGRATION_CHECKLIST.md)
- **Code Issues:** Check `/api/master-data-mysql.php`
- **Database:** Check `database/mysql_schema.sql`
