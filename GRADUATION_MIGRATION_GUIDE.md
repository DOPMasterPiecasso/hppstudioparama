# Graduation Data Migration - Summary

## 🔍 Masalah yang Ditemukan

### 1. **Table Names Mismatch**
- Schema yang dibuat menggunakan nama table: `graduation_packages`
- Tetapi code di `api/master-data.php` mencari table: `packages_graduation`
- Ini menyebabkan data tidak ditemukan meskipun JSON sudah ada

### 2. **Data Source: JSON, Bukan Database**
Data graduation yang terlihat di UI berasal dari:
- **File**: `data/graduation.json`
- **Contains**:
  - 6 packages (Photo & Video, Video Only, Photo Only, Photo Booth, 360° Glamation, Complete)
  - 4 add-ons (Tambah Videografer, Fotografer, Jam Photobooth, Jam Kerja)
  - 4 cetak items (4R, 8R, 10R, 12R printing sizes)

### 3. **Column Names Mismatch**
| Aspek | Schema Awal | Expected | Fix |
|-------|-------------|----------|-----|
| Table Name | `graduation_packages` | `packages_graduation` | ✅ Updated |
| Package ID | `id` | `package_key` | ✅ Updated |
| Addon Type Col | `item_type` | `addon_type` | ✅ Updated |
| Addon ID | `id` | `addon_key` | ✅ Updated |
| Cetak Table | Old format | Individual items | ✅ Updated |

---

## ✅ Solusi yang Diimplementasikan

### 1. **Update Database Schema** 
   File: `database/mysql_schema.sql`

   **packages_graduation** (tabel untuk paket graduation):
   ```sql
   CREATE TABLE IF NOT EXISTS packages_graduation (
       id INT PRIMARY KEY AUTO_INCREMENT,
       package_key VARCHAR(50) NOT NULL UNIQUE,
       name VARCHAR(100) NOT NULL,
       price BIGINT NOT NULL,
       description TEXT,
       color VARCHAR(50),
       display_order INT DEFAULT 0,
       active TINYINT DEFAULT 1,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   );
   ```

   **graduation_addons** (tabel untuk add-ons):
   ```sql
   CREATE TABLE IF NOT EXISTS graduation_addons (
       id INT PRIMARY KEY AUTO_INCREMENT,
       addon_key VARCHAR(50) NOT NULL UNIQUE,
       name VARCHAR(100) NOT NULL,
       price BIGINT NOT NULL,
       addon_type VARCHAR(50),
       description TEXT,
       active TINYINT DEFAULT 1,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   );
   ```

   **graduation_cetak** (tabel untuk harga cetak):
   ```sql
   CREATE TABLE IF NOT EXISTS graduation_cetak (
       id INT PRIMARY KEY AUTO_INCREMENT,
       cetak_key VARCHAR(50) NOT NULL UNIQUE,
       name VARCHAR(100) NOT NULL,
       price_per_unit BIGINT NOT NULL,
       description VARCHAR(255),
       active TINYINT DEFAULT 1,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   );
   ```

### 2. **Migration Script**
   File: `migrate_graduation_data.php`
   
   Script ini akan:
   ✅ Membaca `data/graduation.json`
   ✅ Mengimport packages ke `packages_graduation`
   ✅ Mengimport add-ons ke `graduation_addons`
   ✅ Mengimport cetak items ke `graduation_cetak`
   ✅ Menampilkan hasil migrasi

---

## 🚀 Cara Menjalankan Migrasi

### Step 1: Initialize Database (jika belum)
```bash
php init_database.php
```

### Step 2: Run Graduation Migration
```bash
php migrate_graduation_data.php
```

**Output yang diharapkan:**
```
========================================
Graduation Data Migration
JSON → MySQL
========================================

📂 Membaca graduation.json...
✓ JSON dimuat berhasil

📦 Migrasi Graduation Packages...
✓ 6 paket tersimpan
  - [gphv] Photo & Video (Rp 4,700,000)
  - [gvideo] Video Only (Rp 2,000,000)
  - [gphoto] Photo Only (Rp 2,750,000)
  - [gbooth] Photo Booth (Rp 3,850,000)
  - [g360] Glamation 360° (Rp 4,100,000)
  - [gcomplete] Complete Package (Rp 7,750,000)

📎 Migrasi Graduation Add-ons...
✓ 4 add-on tersimpan
  - [gvideo_add] Tambah 1 Videografer (Rp 1,100,000)
  - [gphoto_add] Tambah 1 Fotografer (Rp 1,250,000)
  - [gbooth_add] Tambah 1 Jam Photobooth/360 (Rp 500,000)
  - [gwork_add] Tambah 1 Jam Kerja/Orang (Rp 350,000)

🖨️  Migrasi Graduation Cetak...
✓ 4 cetak item tersimpan
  - [g4r] Cetak Foto 4R (Rp 4,000)
  - [g8r] Cetak Foto 8R (Rp 8,000)
  - [g10r] Cetak Foto 10R (Rp 15,000)
  - [g12r] Cetak Foto 12R (Rp 20,000)

✅ Verification:
  • packages_graduation: 6 rows
  • graduation_addons: 4 rows
  • graduation_cetak: 4 rows

========================================
✅ Migrasi selesai!
========================================
```

### Step 3: Verify in API
```bash
# GET graduation data dari API
curl "http://localhost:8000/api/master-data.php?action=get_graduation"
```

---

## 📊 Data Structure Mapping

### Packages
```json
{
  "id": "gphv",                                  → package_key
  "name": "Photo & Video",                       → name
  "price": 4700000,                              → price
  "color": "acc",                                → color
  "desc": "2 Fotografer + 1 Videografer..."      → description
}
```

### Add-ons
```json
{
  "id": "gvideo_add",                            → addon_key
  "name": "Tambah 1 Videografer",                → name
  "price": 1100000                               → price
}
```

### Cetak Items
```json
{
  "id": "g4r",                                   → cetak_key
  "name": "Cetak Foto 4R",                       → name
  "price": 4000                                  → price_per_unit
}
```

---

## ✨ Hasil

Setelah migrasi:
- ✅ Data graduation tersimpan di database (bukan JSON)
- ✅ API `getMasterGraduation()` akan membaca dari table MySQL
- ✅ CRUD operations bisa dilakukan via `updateGraduation()`
- ✅ Table structure sesuai dengan code expectations

---

## 📝 Files yang Diupdate/Dibuat

| File | Status | Keterangan |
|------|--------|-----------|
| `database/mysql_schema.sql` | ✏️ Updated | Fixed table names & columns |
| `migrate_graduation_data.php` | 🆕 Created | Migration script dari JSON → MySQL |
| `data/graduation.json` | ℹ️ Reference | Source data (tetap dipertahankan) |

---

## 🔗 Related Files

- `api/master-data.php` - Main API that uses `MySQLMasterData`
- `api/master-data-mysql.php` - Alternative MySQL API
- `config/db.php` - Database configuration & `MySQLMasterData` class
- `pages/pengaturan.php` - Settings editor page

---

## 📌 Notes

- JSON file `data/graduation.json` tetap disimpan sebagai backup
- Script menggunakan `INSERT IGNORE` untuk mencegah duplicate
- All timestamps menggunakan `CURRENT_TIMESTAMP`
- Data sudah include dengan color, deskripsi, dan metadata lainnya
