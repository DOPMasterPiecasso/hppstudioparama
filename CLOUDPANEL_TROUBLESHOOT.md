# 🔧 TROUBLESHOOT: Database Connection Error di CloudPanel

## Error: "Database connection failed"

Jika Anda mendapat error ini di server CloudPanel, ikuti langkah-langkah berikut:

---

## ✅ STEP 1: Check Debug Endpoint

Akses dari browser:
```
https://your-domain.com/api/debug.php
```

Lihat JSON output untuk mendapat info detail:

```json
{
  "database_connection": "✗ Failed",
  "database_error": "SQLSTATE[HY000] [2002] Can't connect to local MySQL server...",
  "database_config": {
    "DB_HOST": "localhost",
    "DB_PORT": 3306,
    "DB_USER": "root",
    "DB_PASS": "***",
    "DB_NAME": "parama_hpp"
  }
}
```

---

## 🔍 DIAGNOSA BERDASARKAN ERROR

### Case 1: "Can't connect to local MySQL server" atau "Connection refused"

**Sebab:** Host atau port salah

**Solusi:**

Di CloudPanel, MySQL biasanya:
- Host: `localhost` atau `127.0.0.1`
- Port: `3306` (default)

Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');  // atau coba '127.0.0.1'
define('DB_PORT', 3306);
define('DB_USER', 'root');
define('DB_PASS', 'your-password');
define('DB_NAME', 'parama_hpp');
```

---

### Case 2: "Access denied for user 'root'@'localhost'"

**Sebab:** Username atau password salah

**Solusi:**

1. SSH ke server CloudPanel:
```bash
ssh user@your-server.com
```

2. Login ke MySQL:
```bash
mysql -u root -p
```

3. Masukkan password root MySQL Anda

4. Verifikasi database ada:
```sql
SHOW DATABASES;
-- Lihat apakah 'parama_hpp' ada dalam list
```

5. Jika tidak ada, buat database:
```sql
CREATE DATABASE parama_hpp DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

6. Import schema:
```bash
mysql -u root -p parama_hpp < database/schema.sql
```

7. Update `config/db.php` dengan password yang benar:
```php
define('DB_USER', 'root');           // atau user yang Anda buat
define('DB_PASS', 'your-password');  // password yang benar
```

---

### Case 3: "Unknown database 'parama_hpp'"

**Sebab:** Database belum dibuat

**Solusi:**

```bash
# SSH ke server
ssh user@your-server.com

# Login MySQL
mysql -u root -p

# Buat database
mysql> CREATE DATABASE parama_hpp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import schema
mysql> USE parama_hpp;
mysql> SOURCE /path/to/database/schema.sql;

# Atau dari bash:
mysql -u root -p parama_hpp < /path/to/database/schema.sql

# Verifikasi berhasil
mysql> SHOW TABLES;
mysql> SELECT COUNT(*) FROM users;
```

---

### Case 4: "Too many connections"

**Sebab:** MySQL connection pool penuh

**Solusi:**

1. CloudPanel biasanya auto-handle, tapi cek:
```bash
mysql -u root -p -e "SHOW VARIABLES LIKE 'max_connections';"
```

2. Jika perlu, restart MySQL:
```bash
sudo systemctl restart mysql
# atau
sudo systemctl restart mariadb
```

---

## 📝 CHECKLIST TROUBLESHOOT

- [ ] Akses `/api/debug.php` dan catat error message
- [ ] Verifikasi `config/db.php` sudah sesuai dengan server Anda
- [ ] Cek MySQL adalah localhost atau 127.0.0.1
- [ ] Cek MySQL port adalah 3306
- [ ] SSH ke server dan test: `mysql -u root -p`
- [ ] Verifikasi database `parama_hpp` ada: `SHOW DATABASES;`
- [ ] Verifikasi tabel ada: `USE parama_hpp; SHOW TABLES;`
- [ ] Jalankan: `php init-auth.php` untuk create users
- [ ] Check error log: `tail -50 /var/log/mysql/error.log`
- [ ] Test login di browser

---

## 🆘 JIKA MASIH ERROR

Kumpulkan informasi ini:

### 1. Output debug endpoint
```bash
curl https://your-domain.com/api/debug.php
```

### 2. Cek error log MySQL
```bash
tail -50 /var/log/mysql/error.log
```

### 3. Cek error log PHP
```bash
tail -50 /var/log/apache2/error.log
# atau jika menggunakan Nginx:
tail -50 /var/log/nginx/error.log
```

### 4. Test manual connection
```bash
mysql -h localhost -u root -p parama_hpp -e "SELECT COUNT(*) FROM users;"
```

### 5. Cek config/db.php
```bash
cat config/db.php | grep define
```

Kirimkan output semua di atas untuk diagnosa lebih lanjut.

---

## 📞 QUICK FIXES

Jika tidak ada akses SSH, coba:

1. **Reset DB_HOST ke localhost**
   - Edit `config/db.php`
   - Ubah `DB_HOST` ke `localhost`

2. **Reset DB_PORT ke 3306**
   - Edit `config/db.php`
   - Set `DB_PORT` ke `3306`

3. **Pastikan password benar**
   - Login ke CloudPanel panel
   - Cari credentials database Anda
   - Update `config/db.php` dengan password yang benar

4. **Create missing database**
   - Di CloudPanel panel, cari "Database" atau "MySQL"
   - Create database dengan nama `parama_hpp`
   - Copy user/password ke `config/db.php`

---

## 🔐 SECURITY NOTE

Sebelum go production:
- [ ] Ubah `DB_USER` dari 'root' menjadi user khusus
- [ ] Hapus atau rename file `api/debug.php`
- [ ] Jangan commit `config/db.php` dengan password ke git
- [ ] Gunakan strong password untuk database user
