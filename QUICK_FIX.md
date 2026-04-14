# 🚀 QUICK ACTION PLAN - Fix Database Connection Error

## Step 1: Cek Debug Info (PALING PENTING!)

Buka di browser:
```
https://your-domain.com/api/debug.php
```

**Catat error message yang muncul** - ini akan memberitahu root cause!

---

## Step 2: Perbaiki `config/db.php`

Edit file: `/config/db.php`

```php
<?php
// UBAH SESUAI SERVER ANDA:
define('DB_HOST', 'localhost');      // Ubah jika perlu
define('DB_PORT', 3306);             // Default MySQL port
define('DB_USER', 'root');           // Ubah jika berbeda
define('DB_PASS', 'password-anda');  // UBAH INI!
define('DB_NAME', 'parama_hpp');     // Ubah jika nama DB berbeda
```

**Untuk CloudPanel, check di:**
- CloudPanel Dashboard → Database / MySQL
- Catat username, password, database name
- Update di `config/db.php`

---

## Step 3: Verify Database

SSH ke server:
```bash
# Login MySQL
mysql -u root -p

# Di MySQL prompt:
SHOW DATABASES;  # Lihat apakah 'parama_hpp' ada

# Jika tidak ada, create:
CREATE DATABASE parama_hpp CHARACTER SET utf8mb4;

# Import schema:
EXIT;
mysql -u root -p parama_hpp < database/schema.sql
```

---

## Step 4: Initialize Users

```bash
php init-auth.php
```

Output yang benar:
```
✓ Role 'admin' created
✓ Role 'manager' created
✓ Role 'staff' created
✓ User 'admin' created
✓ User 'manager' created
✓ User 'staff' created
```

---

## Step 5: Test Login

1. Buka: `https://your-domain.com/`
2. Username: `admin`
3. Password: `admin2026`

---

## ⚠️ JIKA MASIH ERROR

Kirimkan output:

```bash
# 1. Debug endpoint output
curl https://your-domain.com/api/debug.php

# 2. Config file (jangan kirim password!)
grep define config/db.php

# 3. MySQL error log
tail -50 /var/log/mysql/error.log

# 4. Test manual
mysql -u root -p parama_hpp -e "SHOW TABLES;"
```

**Lalu baca:** [CLOUDPANEL_TROUBLESHOOT.md](CLOUDPANEL_TROUBLESHOOT.md) untuk solusi detail.
