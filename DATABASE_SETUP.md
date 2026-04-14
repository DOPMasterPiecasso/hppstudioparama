# Parama HPP - Database Migration & Integration Guide

## Overview
Dokumen ini menjelaskan bagaimana memisahkan data dari HTML ke database Parama HPP.

## ✓ Yang Sudah Siap

### 1. Database Schema (`database/schema.sql`)
- 11 tabel untuk menyimpan:
  - Overhead bulanan per kategori
  - Full Service packages (Handy, Minimal, Large)
  - À La Carte packages
  - Add-on items dengan tier pricing
  - Printing costs base
  - Graduation packages
  - Proposals/Offers

### 2. Migration Script (`migrate_to_database.php`)
Skrip yang akan:
- Membaca data dari hardcoded values di HTML
- Memasukkan ke database
- Generate seed data awal
- Run 1x saja

### 3. API Endpoints (`api/pricing.php`)
RESTful API untuk mengakses data:
- `?action=get_all` - Semua data (untuk init)
- `?action=get_fullservice` - Full Service packages
- `?action=get_alacarte` - À La Carte packages
- `?action=get_addons` - Add-on items
- `?action=get_graduation` - Graduation packages
- `?action=save_penawaran` - Simpan proposal
- `?action=get_penawarans` - Ambil daftar proposal

## ⚙️ Setup Instructions

### Langkah 1: Buat Database & Tabel
```bash
# Option A: Menggunakan setup script (recommended)
bash setup.sh

# Option B: Manual
mysql -h localhost -u root -p rahasia123 parama_hpp < database/schema.sql
```

### Langkah 2: Import Data Awal
```bash
php migrate_to_database.php
```

**Output:**
```
Memulai migrasi data Parama HPP...
============================================================

1. Migrasi Overhead...
   ✓ 7 kategori overhead tersimpan

2. Migrasi Full Service Packages...
   ✓ 12 paket full service tersimpan

... (seterusnya)

✓ MIGRASI DATABASE BERHASIL!
```

## 📱 Integration untuk HTML/JavaScript

### Option 1: Load Semua Data di Login (Recommended)
Setelah user login, fetch semua data sekaligus:

```javascript
let DB_DATA = {};

async function initializeAppWithDatabase() {
    try {
        const response = await fetch('api/pricing.php?action=get_all');
        const data = await response.json();
        
        // Convert database format to application format
        DB_DATA = {
            OH: {
                total: data.overhead.total,
                designer: data.overhead['Designer'],
                marketing: data.overhead['Marketing'],
                creative: data.overhead['Creative Prod.'],
                pm: data.overhead['Project Mgr'],
                sosmed: data.overhead['Social Media'],
                freelance: data.overhead['Freelance'],
                ops: data.overhead['Operasional'],
            },
            FS: data.fullservice, // {handy: [...], minimal: [...], large: [...]}
            ADDON_DATA: convertAddonsFormat(data.addons),
            GRAD: data.graduation,
            ALC_F: data.alacarte_factors,
            CETAK_BASE: data.cetak_base,
            CETAK_F: data.cetak_factors,
        };
        
        // Render UI dengan data dari database
        if (currentUser?.role === 'manager') {
            renderRingkasan();
            renderFS();
        }
        
        console.log('✓ Data dari database berhasil dimuat');
        return true;
        
    } catch (error) {
        console.error('❌ Gagal load data dari database:', error);
        // Fallback ke data hardcoded jika ada
        return false;
    }
}

// Panggil setelah login berhasil
function doLogin() {
    // ... existing login logic ...
    
    if (/*login berhasil*/) {
        setTimeout(() => {
            initializeAppWithDatabase();
        }, 500);
    }
}
```

### Option 2: Lazy Load Per Modul
Load data hanya saat dibutuhkan:

```javascript
async function getFullServiceData(packageType = null) {
    try {
        const url = packageType 
            ? `api/pricing.php?action=get_fullservice&package=${packageType}`
            : `api/pricing.php?action=get_fullservice`;
        
        const response = await fetch(url);
        return await response.json();
    } catch (error) {
        console.error('Error loading FS data:', error);
        return null;
    }
}

function renderFS() {
    getFullServiceData().then(fsData => {
        if (!fsData) return;
        // fsData format: {handy: [...], minimal: [...], large: [...]}
        FS = fsData;
        // ... render logic ...
    });
}
```

### Option 3: Convert Add-ons Format
```javascript
function convertAddonsFormat(dbAddons) {
    const result = {
        finishing: [],
        kertas: [],
        halaman: [],
        video: [],
        pkg1: [],
        pkg2: []
    };
    
    // Map database format ke format aplikasi
    dbAddons.forEach(addon => {
        const item = {
            id: addon.id,
            name: addon.name,
            type: addon.type,
            price: addon.price,
            tiers: addon.tiers
        };
        
        // Kategorisasi sesuai nama
        if (addon.name.includes('Finishing') || addon.name.includes('Hardcover')) {
            if (!result.finishing.includes(item)) result.finishing.push(item);
        } else if (addon.name.includes('Kertas') || addon.name.includes('Paper')) {
            if (!result.kertas.includes(item)) result.kertas.push(item);
        } else if (addon.name.includes('Halaman')) {
            if (!result.halaman.includes(item)) result.halaman.push(item);
        } else if (addon.name.includes('Video')) {
            if (!result.video.includes(item)) result.video.push(item);
        } else if (addon.name.includes('Slide')) {
            if (!result.pkg1.includes(item)) result.pkg1.push(item);
        } else if (addon.name.includes('Custom')) {
            if (!result.pkg2.includes(item)) result.pkg2.push(item);
        }
    });
    
    return result;
}
```

## 💾 Menyimpan Proposal ke Database

### Sebelumnya (localStorage)
```javascript
penawaranList.push({
    id: Date.now(),
    nama: "SMA Maju",
    harga: 8500000,
    ...
});
savePenawaranLS();
```

### Sesudahnya (Database)
```javascript
async function savePenawaranToDatabase(penawaran) {
    const formData = new FormData();
    formData.append('action', 'save_penawaran');
    formData.append('client_name', penawaran.nama);
    formData.append('package', penawaran.paket);
    formData.append('student_count', penawaran.siswa);
    formData.append('total_price', penawaran.harga);
    formData.append('final_price', penawaran.harga);
    formData.append('notes', penawaran.catatan);
    
    try {
        const response = await fetch('api/pricing.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            console.log('✓ Proposal saved:', result.id);
        }
    } catch (error) {
        console.error('Error saving proposal:', error);
    }
}

// Ganti di saveKalcToPenawaran():
penawaranList.push({
    id: Date.now(),
    nama, paket, siswa, harga, catatan, status,
    addedBy, ts, tsRaw
});

// Sebelumnya:
// savePenawaranLS();

// Sesudahnya tambahan:
savePenawaranToDatabase({nama, paket, siswa, harga, catatan});
```

## 🔄 Migration Strategy (Bertahap)

Jika website sudah live dengan data di localStorage, lakukan ini:

### Phase 1: Setup Database
1. Jalankan `migrate_to_database.php`
2. Database sekarang punya semua data pricing

### Phase 2: Read from Database
1. Update JavaScript untuk fetch data dari `api/pricing.php`
2. Application baca dari database untuk tampilan
3. Keep localStorage sebagai backup

### Phase 3: Hybrid Mode
```javascript
async function getDataWithFallback(action) {
    try {
        // Try database first
        const response = await fetch(`api/pricing.php?action=${action}`);
        if (response.ok) {
            return await response.json();
        }
    } catch (error) {
        console.warn('Database unavailable, using local data');
    }
    
    // Fallback ke data lokal jika database error
    return getLocalData(action);
}
```

### Phase 4: Migrate localStorage to Database
```javascript
async function migrateLocalOffers() {
    const localOffers = JSON.parse(
        localStorage.getItem('parama_penawaran_v1') || '[]'
    );
    
    for (const offer of localOffers) {
        await savePenawaranToDatabase({
            nama: offer.nama,
            paket: offer.paket,
            siswa: offer.siswa,
            harga: offer.harga,
            catatan: offer.catatan
        });
    }
    
    console.log('✓ Proposal berhasil dimigrasikan');
    // Clear localStorage or keep as backup
    // localStorage.removeItem('parama_penawaran_v1');
}
```

## 📊 Testing API

### Test dengan cURL
```bash
# Get semua data
curl http://localhost:8888/parama_hpp/api/pricing.php?action=get_all

# Get Full Service packages
curl http://localhost:8888/parama_hpp/api/pricing.php?action=get_fullservice

# Get dengan parameter package
curl http://localhost:8888/parama_hpp/api/pricing.php?action=get_fullservice&package=handy

# Get penawarans untuk bulan tertentu
curl http://localhost:8888/parama_hpp/api/pricing.php?action=get_penawarans&month=2026-03

# Save penawaran
curl -X POST http://localhost:8888/parama_hpp/api/pricing.php \
  -d "action=save_penawaran&client_name=SMA Maju&total_price=8500000&final_price=8500000"
```

## 🔐 Security Notes

1. **Proteksi API dengan Auth**
```php
// Di api/pricing.php, tambahkan di awal:
session_start();
if (empty($_SESSION['user'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}
```

2. **Validasi Input untuk POST**
```php
function savePenawaran($pdo, $data) {
    // Validate input
    $client_name = trim($data['client_name'] ?? '');
    if (empty($client_name) || strlen($client_name) > 200) {
        throw new Exception('Invalid client name');
    }
    // ... rest of validation
}
```

3. **SQL Injection Prevention**
- Sudah menggunakan prepared statements ✓
- Validasi semua input ✓

## ❓ Troubleshooting

### Error: Table doesn't exist
```bash
# Re-run schema creation
mysql -u root -p parama_hpp < database/schema.sql
```

### Error: Connection failed
- Check config/db.php
- Pastikan MySQL running
- Verify credentials

### API returns empty data
- Verify `migrate_to_database.php` sudah dijalankan
- Check database punya data: `mysql> SELECT COUNT(*) FROM packages_fullservice;`

## 📝 File Structure

```
parama_hpp/
├── database/
│   └── schema.sql           # Database schema (kena deploy)
├── api/
│   └── pricing.php          # API endpoints
├── config/
│   └── db.php              # Database connection
├── migrate_to_database.php  # Migration script (run sekali)
├── setup.sh                # Setup automation (run sekali)
└── parama_dashboard_v2.html # Frontend (akan diupdate)
```

## 🚀 Next Steps

1. ✅ Setup database & migration (sudah)
2. ⏳ Update HTML JavaScript untuk fetch dari API
3. ⏳ Test integration
4. ⏳ Deploy ke production
5. ⏳ Monitor database performance

---

**Questions?** Refer ke [api/pricing.php](api/pricing.php) untuk dokumentasi lengkap endpoint.
