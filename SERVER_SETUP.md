# 🔧 SERVER SETUP GUIDE - Parama HPP

## 🚨 MASALAH: Login Gagal di Server

Jika login bekerja di **lokal** tapi gagal di **server**, ini adalah root cause dan solusinya:

---

## ✅ SOLUSI LENGKAP (Step by Step)

### Step 1: Setup Database Schema

Database Anda **belum memiliki tabel `users` dan `roles`**! Itu sebabnya login gagal.

#### 1.1 Import Database Schema
```bash
# SSH ke server
ssh user@your-server.com

# Masuk ke direktori project
cd /path/to/parama_hpp

# Import schema yang sudah diperbaiki
mysql -u your-db-user -p < database/schema.sql
```

Masukkan password database Anda ketika diminta.

#### 1.2 Verifikasi Schema Berhasil
```bash
mysql -u your-db-user -p parama_hpp

# Di MySQL prompt:
SHOW TABLES;
# Harusnya menampilkan: users, roles, settings, overhead, dll
```

### Step 2: Update Konfigurasi Database (di `config/db.php`)

#### 2.1 Edit File Konfigurasi
```bash
# Edit file konfigurasi dengan kredensial server Anda
nano config/db.php
```

Sesuaikan nilai-nilai berikut dengan server Anda:
```php
define('DB_HOST', 'localhost');      // atau server.com atau IP server
define('DB_USER', 'your_db_user');   // jangan pakai 'root'!
define('DB_PASS', 'your_password');  // password yang benar
define('DB_NAME', 'parama_hpp');
```

**Contoh yang benar untuk server:**
```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'parama_user');
define('DB_PASS', 'SuperSecure123!@#');
define('DB_NAME', 'parama_hpp');
```

### Step 3: Initialize Default Users

Jalankan script untuk membuat default roles dan users:
```bash
php init-auth.php
```

Output yang benar:
```
🔐 Initializing Roles and Users...
✓ Role 'admin' created
✓ Role 'manager' created
✓ Role 'staff' created
✓ User 'admin' created
✓ User 'manager' created
✓ User 'staff' created

✅ Authentication setup complete!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Available Admin:
  Username: admin
  Password: admin2026
...
```

### Step 4: Verify Database Connection

Buka di browser:
```
http://your-server.com/api/debug.php
```

**Output yang benar:**
```json
{
  "database_connection": "Success ✓",
  "users_count": 3,
  "users_table_exists": true,
  "env_var": {
    "DB_HOST": "localhost",
    "DB_USER": "parama_user"
  }
}
```

**Jika error:**
- Cek kredensial di `config/db.php` - pastikan sudah sesuai dengan server
- Cek database `parama_hpp` sudah ada: `SHOW DATABASES;`
- Cek user punya akses: `mysql -u parama_user -p parama_hpp -e "SELECT 1;"`

### Step 5: Test Login

1. Buka: `http://your-server.com/` atau `http://your-server.com/auth/login.php`
2. Masukkan credentials:
   - **Username:** `admin`
   - **Password:** `admin2026`
3. Seharusnya berhasil login dan masuk ke dashboard

---

## 🔍 TROUBLESHOOTING

### ❌ Error: "Database connection failed"

**Cause:** Koneksi database gagal

**Solution:**
```bash
# 1. Verify credentials di config/db.php
nano config/db.php
# pastikan DB_HOST, DB_USER, DB_PASS, DB_NAME sudah benar

# 2. Test connection manual
mysql -h localhost -u root -prahasia123 parama_hpp -e "SELECT 1;"
# Catatan: tidak ada space sebelum password!

# 3. Jika MySQL di socket
mysql -u parama_user -p /var/run/mysqld/mysqld.sock -D parama_hpp -e "SELECT 1;"

# 4. Check MySQL status
systemctl status mysql
# atau
systemctl status mariadb
```

### ❌ Error: "Username atau password salah"

**Cause:** User tidak ada atau password salah

**Solution:**
```bash
# Jika belum jalankan init-auth.php
php init-auth.php

# Atau verify user sudah ada
mysql -u parama_user -p parama_hpp -e "SELECT username, name FROM users;"
# Output:
# username | name
# admin    | Administrator
# manager  | Manajer
# staff    | Staff Member
```

### ❌ Error: "Terjadi kesalahan"

**Cause:** Exception umum (gunakan debug untuk detail)

**Solution:**
```bash
# 1. Cek error log PHP
tail -50 /var/log/apache2/error.log

# 2. Atau cek debug endpoint
curl http://your-server.com/api/debug.php
```

### ❌ Session tidak tersimpan / Logout setelah refresh

**Cause:** Session save path tidak writable

**Solution:**
```bash
# 1. Check PHP session path
php -i | grep session.save_path

# 2. Make sure writable
sudo chmod 755 /var/lib/php/sessions/
# atau
sudo chown www-data:www-data /var/lib/php/sessions/

# 3. Test write
touch /var/lib/php/sessions/test.txt
rm /var/lib/php/sessions/test.txt
```

### ❌ ModRewrite error / 404 pada halaman

**Cause:** `.htaccess` tidak aktif

**Solution:**
```bash
# Enable rewrite module
sudo a2enmod rewrite

# Check httpd.conf memperbolehkan .htaccess
grep -i "AllowOverride" /etc/apache2/apache2.conf
# Harusnya tidak "None"

# Restart Apache
sudo systemctl restart apache2
```

---

## 📋 FULL CHECKLIST

- [ ] Database `parama_hpp` exists: `SHOW DATABASES; SHOW TABLES;`
- [ ] Schema imported dengan tabel `users` dan `roles`
- [ ] File `config/db.php` dikonfigurasi dengan kredensial yang benar
- [ ] Script `init-auth.php` sudah dijalankan
- [ ] Debug endpoint `/api/debug.php` menunjukkan "Success ✓"
- [ ] User `admin`, `manager`, `staff` ada di database
- [ ] Session path writable: `ls -la /var/lib/php/sessions/`
- [ ] ModRewrite enabled di Apache: `a2enmod rewrite`
- [ ] File `.htaccess` ada dan aktif
- [ ] ✅ Test login dengan `admin` / `admin2026` berhasil

---

## 🆘 Masih Gagal?

Kumpulkan informasi ini dan kirimkan:

```bash
# Export debug info
echo "=== CHECK DEBUG ===" && \
curl -s http://your-server.com/api/debug.php | python -m json.tool && \
echo -e "\n=== CHECK USERS ===" && \
mysql -u parama_user -p parama_hpp -e "SELECT username, is_active FROM users;" && \
echo -e "\n=== CHECK ERROR LOG ===" && \
tail -20 /var/log/apache2/error.log
```

Copy-paste output ke laporan masalah.
