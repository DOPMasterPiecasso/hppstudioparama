# Environment Configuration Setup

## Overview

Database credentials dan configuration sekarang dimuat dari `.env` file, bukan hardcoded di PHP files. Ini adalah best practice untuk security.

## Files

### `.env` (SENSITIVE - Never commit!)
- Actual credentials untuk development/production
- Ignored by git (di `.gitignore`)
- **Do NOT share atau commit ke repository**

### `.env.example` (For documentation)
- Template tanpa sensitive data
- Safe untuk commit
- Dokumentasi struktur .env

### `config/db.php` (Reads from .env)
- Updated dengan `.env` parser
- Fallback ke default values jika `.env` tidak ada
- Also supports `getenv()` untuk environment variables

## Setup

### 1. Copy Template
```bash
cp .env.example .env
```

### 2. Configure Credentials
Edit `.env` dengan nilai sesuai environment:

**Development (Local):**
```env
DB_HOST="localhost"
DB_PORT="3306"
DB_NAME="parama_hpp"
DB_USER="root"
DB_PASS="your_password"
```

**Production:**
```env
DB_HOST="prod-db.example.com"
DB_PORT="3306"
DB_NAME="parama_hpp_prod"
DB_USER="parama_prod_user"
DB_PASS="SecurePassword123!@#"
```

### 3. Verify Setup
```bash
# Test connection
php -r "
require_once 'config/db.php';
global \$MySQL_Config;
echo 'Config loaded:' . PHP_EOL;
echo 'Host: ' . \$MySQL_Config['host'] . PHP_EOL;
echo 'User: ' . \$MySQL_Config['user'] . PHP_EOL;
"
```

### 4. Test MySQL Connection
```bash
php -r "
require_once 'config/db.php';
\$pdo = getMySQLConnection();
echo \$pdo ? '✓ Connected' : '✗ Failed';
"
```

## How It Works

### .env Parser (in config/db.php)
```php
function loadEnvFile($filePath = null) {
    // Baca .env file
    // Parse KEY="VALUE" format
    // Handle comments (lines starting with #)
    // Return array of variables
}
```

### Configuration Hierarchy
1. First: Check `.env` file values
2. Second: Check `getenv()` environment variables
3. Third: Use default values

```php
$MySQL_Config = [
    'host' => $envVars['DB_HOST'] 
              ?? getenv('DB_HOST') 
              ?: 'localhost',
];
```

## Security Best Practices

### ✅ DO
- ✅ Store `.env` in `.gitignore`
- ✅ Use strong passwords in production
- ✅ Different credentials for dev/prod
- ✅ Use `.env.example` untuk dokumentasi
- ✅ Rotate passwords regularly
- ✅ Keep PHP files without sensitive data

### ❌ DON'T
- ❌ Never commit `.env` to git
- ❌ Don't use simple passwords in production
- ❌ Don't share `.env` via unencrypted channels
- ❌ Don't hardcode credentials in PHP files
- ❌ Don't use same credentials for dev/prod

## Supported Formats

### Single & Double Quotes
```env
DB_HOST='localhost'
DB_HOST="localhost"
DB_HOST=localhost
```

### Comments
```env
# This is a comment
DB_HOST="localhost"  # Inline comments not supported
```

### Values with Spaces (use quotes)
```env
DB_USER="my user"    # Spaces - use quotes
DB_PASS='my@pass'    # Special chars - use quotes
```

## Additional Configuration Examples

### With Port Override
```env
DB_HOST="localhost"
DB_PORT="3307"       # Custom port
DB_NAME="parama_dev"
DB_USER="root"
DB_PASS="root"
```

### With Different User
```env
DB_HOST="localhost"
DB_USER="parama_user"
DB_PASS="user_password"
DB_NAME="parama_hpp"
```

### Production Remote Server
```env
DB_HOST="db.prod.internal.com"
DB_PORT="3306"
DB_USER="parama_app"
DB_PASS="Pr0d@ctionP@ss123"
DB_NAME="hpp_production"
```

## Testing Connection

### From Command Line
```bash
# Direct MySQL test
mysql -h localhost -u root -p -e "SELECT 1;"

# PHP MySQL connection test
php -r "
require_once 'config/db.php';
try {
    \$pdo = getMySQLConnection();
    \$result = \$pdo->query('SELECT 1')->fetch();
    echo '✓ MySQL Connected' . PHP_EOL;
} catch (Exception \$e) {
    echo '✗ Error: ' . \$e->getMessage() . PHP_EOL;
}
"
```

### From Application
```php
<?php
require_once 'config/db.php';

$pdo = getMySQLConnection();
if ($pdo) {
    echo "✓ Database connection OK";
    $master = getMySQLMasterData($pdo);
    $overhead = $master->getOverhead();
    var_dump($overhead);
} else {
    echo "✗ Database connection failed";
}
?>
```

## Troubleshooting

### "Connection refused"
- Check MySQL server running
- Verify host & port in `.env`
- Test direct connection: `mysql -h host -u user -p`

### "Access denied for user"
- Check username & password in `.env`
- Verify user exists in MySQL
- Test: `mysql -u user -p -e "SELECT 1;"`

### "Unknown database"
- Check database name in `.env`
- Verify database exists: `mysql -e "SHOW DATABASES;"`
- Create if needed: `mysql -e "CREATE DATABASE parama_hpp;"`

### ".env file not found"
- Create .env: `cp .env.example .env`
- Set credentials in .env
- Application will still work with defaults if .env missing

## Migration from Hardcoded Config

If you have old hardcoded config:

### Old (config/db.php)
```php
$MySQL_Config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => 'password123',
    'name' => 'parama_hpp',
];
```

### New (via .env)
```env
DB_HOST="localhost"
DB_USER="root"
DB_PASS="password123"
DB_NAME="parama_hpp"
```

No more credentials in PHP files! 🔐

## Files Structure

```
parama_hpp/
├── .env ...................... Credentials (git ignored) ⚠️
├── .env.example .............. Template (git tracked) ✅
├── .gitignore ................ Excludes .env
├── config/
│   └── db.php ................ Updated: reads from .env
├── migrate_json_to_mysql.php
├── api/
│   ├── master-data.php
│   └── master-data-mysql.php
└── ... (other files)
```

## Next Steps

1. ✅ Update `.env` dengan credentials Anda
2. ✅ Test connection: `php -r "require_once 'config/db.php'; echo getMySQLConnection()?'✓':'✗';"`
3. ✅ Run migration: `php migrate_json_to_mysql.php`
4. ✅ Test aplikasi
5. ✅ Push changes (`.env.example` yang di-commit, `.env` tidak)

---

**All credentials now safely managed via .env!** 🔐
