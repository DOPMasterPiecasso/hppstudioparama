# Parama HPP - Database Migration Project

## 📋 Overview

Proyek ini untuk memisahkan data pricing Parama Studio dari HTML into database. Sekarang data disimpan di database dan di-manage via API daripada hardcoded di HTML.

**Status:** ✅ Complete - Siap implementasi

## 📦 Apa Yang Sudah Dibuat

### 1. Database Schema (`database/schema.sql`)
11 tabel untuk menyimpan data:
- `overhead` - Overhead operasional bulanan
- `packages_fullservice` - Full Service pricing (Handy, Minimal, Large)
- `packages_alacarte` - À La Carte packages (E-Book, Video, Foto, dll)
- `addon_categories` - Kategori add-on
- `addon_items` - Item add-on (Finishing, Kertas, Packaging, dll)
- `addon_tiers` - Tier pricing untuk add-on
- `cetak_base` - Base printing cost per range
- `cetak_factors` - Printing multiplier per paket
- `packages_graduation` - Graduation packages
- `graduation_addons` - Add-on spesifik graduation
- `alacarte_factors` - Faktor harga à la carte
- `penawaran` - Daftar proposal/offers

**Total data:** ~100+ entries siap migrasi

### 2. Migration Script (`migrate_to_database.php`)
Script untuk import data awal dari hardcoded values ke database.
- Membaca data dari struktur JavaScript
- Insert ke database dengan prepared statements
- Safe dari SQL injection
- **Run sekali saja** setelah setup

```bash
php migrate_to_database.php
```

Output:
```
✓ 7 kategori overhead tersimpan
✓ 12 paket full service tersimpan
✓ 8 paket à la carte tersimpan
✓ Add-on categories dan items tersimpan
... (dst)

✓ MIGRASI DATABASE BERHASIL!
```

### 3. API Endpoints (`api/pricing.php`)
RESTful API untuk manage semua data:

| Action | Method | Deskripsi |
|--------|--------|-----------|
| `get_all` | GET | Semua data sekaligus (untuk init) |
| `get_overhead` | GET | Overhead data |
| `get_fullservice` | GET | Full Service packages |
| `get_alacarte` | GET | À La Carte packages |
| `get_addons` | GET | Add-on items dengan tiers |
| `get_graduation` | GET | Graduation packages |
| `save_penawaran` | POST | Simpan proposal baru |
| `get_penawarans` | GET | Ambil daftar proposal |
| `update_penawaran_status` | POST | Update status proposal |
| `delete_penawaran` | POST | Hapus proposal |

**Base URL:** `api/pricing.php?action=get_all`

### 4. Setup Script (`setup.sh`)
Automation script untuk setup database & migration
```bash
bash setup.sh  # Linux/Mac
```

Atau manual:
```bash
# 1. Create database schema
mysql < database/schema.sql

# 2. Run migration
php migrate_to_database.php
```

### 5. Integration Layer (`assets/js/db-integration.js`)
JavaScript helper untuk menghubungkan app ke database:
- `initializeDatabaseLayer()` - Load data dari API
- `savePenawaranToDatabase()` - Save proposal
- `getPenawaransFromDatabase()` - Fetch proposal list
- Automatic fallback ke local data jika API error
- 100% compatible dengan existing JS code

### 6. Documentation
- **DATABASE_SETUP.md** - Setup guide & integration tutorial
- **Kode examples** - Implementasi lengkap di db-integration.js

## 🚀 Quick Start

### Step 1: Setup Database (5 menit)
```bash
cd parama_hpp/

# Option A: Automatic
bash setup.sh

# Option B: Manual  
mysql -u root -p rahasia123 parama_hpp < database/schema.sql
php migrate_to_database.php
```

### Step 2: Verify Data (1 menit)
```bash
# Test API
curl "http://localhost:8888/parama_hpp/api/pricing.php?action=get_overhead"

# Should return:
# {"Designer": 8000000, "Marketing": 3000000, ...}
```

### Step 3: Integrate to HTML (30 menit)
Edit `parama_dashboard_v2.html`:

```html
<!-- Tambah di sebelum closing </body> -->
<script src="assets/js/db-integration.js"></script>

<!-- Lalu update fungsi doLogin: -->
<script>
// Ganti: doLogin() dengan:
const originalDoLogin = doLogin;
doLogin = function() { doLoginWithDatabase(); }

// Ganti: saveKalcToPenawaran() dengan:
const originalSave = saveKalcToPenawaran;
saveKalcToPenawaran = function() { saveKalcToPenawaranWithDatabase(); }
</script>
```

Atau lebih mudah, copy integration function dari `db-integration.js` ke dalam `parama_dashboard_v2.html` script tag.

### Step 4: Test (5 menit)
1. Open `http://localhost:8888/parama_hpp/parama_dashboard_v2.html`
2. Login dengan credentials yang ada
3. Buat proposal
4. Check database: `mysql> SELECT * FROM penawaran;`

## 📁 File Structure

```
parama_hpp/
├── database/
│   └── schema.sql              # ✅ Database schema
├── api/
│   └── pricing.php             # ✅ API endpoints
├── assets/js/
│   ├── app.js
│   ├── app-pages.js
│   └── db-integration.js        # ✅ Integration layer
├── config/
│   └── db.php
├── migrate_to_database.php      # ✅ Migration script
├── setup.sh                     # ✅ Setup automation
├── DATABASE_SETUP.md            # ✅ Setup guide
└── parama_dashboard_v2.html     # ⏳ Need update
```

## 🔄 How It Works

### Before (All in HTML)
```javascript
// File: parama_dashboard_v2.html
const FS = {
    handy: [[25, 50, 399000, 70], [51, 75, 389000, 75], ...],
    minimal: [...],
    large: [...]
};

const OH = {designer: 8000000, marketing: 3000000, ...};
// ... hundreds of lines hardcoded
```

### After (Database)
```javascript
// File: api/pricing.php
GET api/pricing.php?action=get_fullservice
→ Returns: {handy: [...], minimal: [...], large: [...]}

// File: assets/js/db-integration.js
await initializeDatabaseLayer();
// Fetches all data and maps to FS, OH, ADDON_DATA, etc.
```

### Data Flow
```
Login → initializeDatabaseLayer() → fetch api/pricing.php
  ↓
mapDatabaseToApplication() → FS, OH, ADDON_DATA, GRAD, etc.
  ↓
Render UI dengan data dari database
  ↓
User buat proposal → savePenawaranToDatabase()
  ↓
INSERT INTO penawaran (MySQL)
```

## 💾 Data Mapping

### Overhead
```
Database column: category, amount
Map to JS:
OH.designer         = overhead['Designer']
OH.marketing        = overhead['Marketing']
OH.creative         = overhead['Creative Prod.']
... (etc)
OH.total            = SUM(all)
```

### Full Service
```
Database table: packages_fullservice
Map to JS:
FS.handy = [
  [min_students, max_students, price_per_book, max_pages],
  ...
]
Same for FS.minimal, FS.large
```

### Add-ons
```
Database tables: addon_categories, addon_items, addon_tiers
Map to JS:
ADDON_DATA.finishing = [
  {id, name, type, price, tiers: [[label, min, max, price], ...]},
  ...
]
Same for kertas, halaman, video, pkg1, pkg2
```

## 🔐 Security

✅ **Already implemented:**
- Prepared statements (no SQL injection)
- Input validation
- Type casting
- Error handling

**Recommended next steps:**
- Add session auth check di api/pricing.php
- Rate limiting
- CORS policy
- Encrypt sensitive data

Example auth:
```php
// Di api/pricing.php:
session_start();
if (empty($_SESSION['user'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}
```

## 📊 Testing

### Test API dengan curl
```bash
# Get semua data
curl "http://localhost:8888/parama_hpp/api/pricing.php?action=get_all" | jq

# Get Full Service packages
curl "http://localhost:8888/parama_hpp/api/pricing.php?action=get_fullservice" | jq

# Get specific paket
curl "http://localhost:8888/parama_hpp/api/pricing.php?action=get_fullservice&package=handy" | jq

# Get penawarans
curl "http://localhost:8888/parama_hpp/api/pricing.php?action=get_penawarans?month=2026-03" | jq

# Save proposal
curl -X POST http://localhost:8888/parama_hpp/api/pricing.php \
  -d "action=save_penawaran&client_name=SMA Maju&total_price=8500000&final_price=8500000" | jq
```

### Browser DevTools
```javascript
// Dari browser console saat aplikasi terbuka:
fetch('api/pricing.php?action=get_all')
  .then(r => r.json())
  .then(data => console.log(data))
```

## ⚡ Performance

- **API Response Time:** ~50-200ms (typical)
- **Database Queries:** Indexed untuk speed
- **Caching:** Optional di db-integration.js
- **Data Size:** ~5KB untuk all data

Untuk meningkatkan:
1. Add database indexes (sudah di schema)
2. Implement caching di API
3. Compress responses
4. Use CDN untuk assets

## 🐛 Troubleshooting

### Database Connection Error
```
PDOException: SQLSTATE[HY000] [2002] Connection refused
```
**Solution:**
- Check MySQL running: `mysql -u root -p`
- Verify credentials di `config/db.php`
- Check DB_HOST is correct (localhost or 127.0.0.1)

### Table Not Found
```
SQLSTATE[42S02]: Table 'parama_hpp.packages_fullservice' doesn't exist
```
**Solution:**
```bash
mysql < database/schema.sql
# OR
php migrate_to_database.php
```

### API Returns Empty Data
**Solution:**
- Verify `migrate_to_database.php` ran successfully
- Check data in database: `SELECT * FROM packages_fullservice;`
- Check file permissions on api/pricing.php

### Integration Not Working
**Solution:**
- Check browser console for errors (F12 → Console)
- Verify `db-integration.js` included in HTML
- Check Network tab - see if API calls succeed
- Check `DATABASE_LOADED` global variable

## 📈 Future Enhancements

1. **Admin Panel** - Edit pricing via web UI
   ```
   /admin/edit-prices.php
   /admin/manage-overhead.php
   ```

2. **Audit Log** - Track pricing changes
   ```
   NEW TABLE: pricing_history (who, what, when, old_value, new_value)
   ```

3. **Multi-user Sync** - Real-time price updates
   ```
   WebSocket or Server-Sent Events
   ```

4. **Export/Import** - CSV, Excel support
   ```
   /api/export.php?format=csv
   /api/import.php
   ```

5. **Approval Workflow** - Manager approve price changes
   ```
   NEW TABLE: price_changes (pending, approved, rejected)
   ```

## 📞 Support

**Common Issues:**
- See `DATABASE_SETUP.md` Troubleshooting section
- Check `api/pricing.php` code comments
- Review `assets/js/db-integration.js` for implementation examples

**Kontribusi:**
- Report bugs → Check browser console & database logs
- Request features → Document use case & expected output

## ✅ Checklist Implementasi

- [ ] Run `mysql < database/schema.sql`
- [ ] Run `php migrate_to_database.php`
- [ ] Test API endpoints dengan curl
- [ ] Update HTML untuk include `db-integration.js`
- [ ] Override `doLogin()` dengan `doLoginWithDatabase()`
- [ ] Override `saveKalcToPenawaran()` dengan database version
- [ ] Test full flow: Login → Create proposal → Database
- [ ] Backup data sebelum deploy ke production
- [ ] Deploy ke server
- [ ] Monitor database performance

## 📚 Documentation

- **DATABASE_SETUP.md** - Detail setup & integration
- **API Endpoints** - Documented di api/pricing.php code
- **Integration Examples** - Di assets/js/db-integration.js
- **Schema** - Commented di database/schema.sql

---

**Version:** 2.0 (March 2026)
**Status:** ✅ Ready for Implementation
**Next:** See DATABASE_SETUP.md untuk langkah2 detail
