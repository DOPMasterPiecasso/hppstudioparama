# Master Data Architecture

## Konsep
Halaman **Edit Semua Harga** (`pengaturan.php`) adalah **DATA MASTER** untuk aplikasi. Semua halaman lain mengambil data dari master ini melalui `/api/master-data.php`.

## Struktur Data Master

### 1. **Overhead & Gaji Tim**
Data: `overhead` (array)
```json
{
  "Designer": 16700000,
  "Marketing": 12750000,
  "Creative Prod.": 7670000,
  "Project Mgr": 7200000,
  "Social Media": 6430000,
  "Freelance": 3204000,
  "Operasional": 11586000
}
```
**File**: `/data/settings.json` → `overhead`
**Editor**: `pengaturan.php` → Section "Overhead & Gaji Tim"

---

### 2. **Cetak Base (Harga Dasar per Buku)**
Data: `cetak_base` (array)
**File**: `/data/cetak_base.json`
**Editor**: `pengaturan.php` → Section "Biaya Cetak — Renjana Offset"

---

### 3. **Faktor Cetak per Paket**
Data: `cetak_f` (object)
```json
{
  "handy": 1.0,
  "minimal": 0.95,
  "large": 1.15
}
```
**File**: `/data/settings.json` → `pricing_factors.cetak`
**Editor**: `pengaturan.php` → Sub-section "Faktor per Paket"

---

### 4. **Faktor À La Carte**
Data: `alc_f` (object)
```json
{
  "ebook": 0.72,
  "editcetak": 0.62,
  "desain": 0.22,
  "cetakonly": 0.30
}
```
**File**: `/data/settings.json` → `pricing_factors.alacarte`
**Editor**: `pengaturan.php` → Section "Faktor Harga À La Carte"

---

### 5. **Full Service Pricing**
Data: `fs` (array berisi paket handy, minimal, large)
**Source**: Database table `packages_fullservice`
**Note**: Ini bisa ditambahkan form editor ke pengaturan.php di masa depan

---

### 6. **Add-ons (Finishing, Kertas, Video, Box)**
Data: `addon_data` (object kompleks)
**File**: `/data/addons.json`
**Editor**: `pengaturan.php` → Section "Add-on Buku Tahunan"

---

### 7. **Graduation Packages**
Data: `grad` (object)
```json
{
  "packages": [...],
  "addons": [...],
  "cetak": [...]
}
```
**File**: `/data/graduation.json`
**Editor**: `pengaturan.php` → Section "Harga Graduation" + "Add-on & Cetak Foto Graduation"

---

## API Endpoints

### GET Request

**Fetch Semua Data**
```bash
GET /api/master-data.php?action=get_all
```
Response:
```json
{
  "success": true,
  "data": {
    "overhead": {...},
    "cetak_f": {...},
    "cetak_base": [...],
    "alc_f": {...},
    "fs": {...},
    "addon_data": {...},
    "grad": {...},
    "timestamp": "2026-04-11 10:30:00"
  }
}
```

**Fetch Specific Data**
```bash
GET /api/master-data.php?action=get_overhead
GET /api/master-data.php?action=get_alacarte_factors
GET /api/master-data.php?action=get_graduation
(dll)
```

---

### POST Request

**Update Master Data**
```bash
POST /api/master-data.php
Content-Type: application/json

{
  "type": "overhead",
  "data": {
    "Designer": 17000000,
    "Marketing": 13000000,
    ...
  }
}
```

**Contoh update untuk berbagai type:**
- `type: "overhead"` → Update overhead & gaji
- `type: "cetak_factors"` → Update faktor cetak
- `type: "cetak_base"` → Update harga dasar cetak
- `type: "alacarte_factors"` → Update faktor à la carte
- `type: "addons"` → Update add-ons
- `type: "graduation"` → Update graduation packages

---

## Alur Data

```
pengaturan.php (Master Editor)
        ↓ (save via AJAX)
/api/master-data.php (update master)
        ↓
      Files: /data/settings.json, /data/graduation.json, dll
        ↑
pengaturan.php (display form)
        ↓
header.php (load initial data)
        ↓
app.js (global variables: OH, CETAK_F, FS, ADDON_DATA, GRAD)
        ↓
app-pages.js (render pages: fullservice, alacarte, addon, kalkulator, graduation)
```

---

## Implementasi di Halaman Lain

Untuk halaman lain yang ingin mengambil data terbaru dari master:

```javascript
// Di halaman fullservice.php / alacarte.php / dll
async function refreshMasterData() {
    const masterData = await loadMasterDataFromAPI();
    if (masterData) {
        // Update global variables
        OH = masterData.overhead;
        CETAK_F = masterData.cetak_f;
        FS = masterData.fs;
        ADDON_DATA = masterData.addon_data;
        GRAD = masterData.grad;
        ALC_F = masterData.alc_f;
        CETAK_BASE = masterData.cetak_base;
        
        // Re-render halaman
        renderFullService(); // atau fungsi render lainnya
    }
}
```

---

## Data Storage Locations

| Data Type | Storage | Load/Save |
|-----------|---------|-----------|
| Overhead | `/data/settings.json` | PHP getSettings() |
| Cetak Factors | `/data/settings.json` | PHP getSettings() |
| Cetak Base | `/data/cetak_base.json` | Direct JSON file |
| ALC Factors | `/data/settings.json` | PHP getSettings() |
| Full Service | Database `packages_fullservice` | PHP query |
| Add-ons | `/data/addons.json` | Direct JSON file |
| Graduation | `/data/graduation.json` | Direct JSON file |

---

## Files yang Terlibat

1. **`/api/master-data.php`** — API endpoint terpusat
2. **`/pages/pengaturan.php`** — Master editor halaman
3. **`/includes/header.php`** — Load data ke DB_SETTINGS
4. **`/assets/js/app.js`** — Global variables & helper functions
5. **`/assets/js/app-pages.js`** — Rendering functions
6. **`/data/settings.json`** — Settings master
7. **`/data/graduation.json`** — Graduation data
8. **`/data/addons.json`** — Add-ons data
9. **`/data/cetak_base.json`** — Cetak base pricing

---

## Catatan Penting

- Master data di-load saat halaman pertama kali dibuka (dari header.php → DB_SETTINGS)
- Ketika ada perubahan di pengaturan.php, langsung di-save ke master via API
- Halaman lain bisa merefresh data dengan memanggil `loadMasterDataFromAPI()`
- Semua perubahan harus melalui pengaturan.php sebagai single source of truth
