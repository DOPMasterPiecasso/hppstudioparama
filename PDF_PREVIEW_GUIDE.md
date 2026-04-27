# PDF Preview & Generator — Parama Studio
## Dokumentasi Penggunaan

---

## 📋 Files yang Ada

### 1. **api/pdf-preview.php** ✅ (NEW - PREVIEW DENGAN SIDEBAR)
Preview PDF dengan tampilan lengkap seperti mockup:
- **Top Bar (Navbar)**: Branding + Tombol Cetak/Kembali
- **Left Panel (Sidebar)**: Informasi penawaran + Summary harga
- **Right Panel**: Preview PDF sheet dengan semua detail
- **Layout Responsive**: Support print ke PDF langsung dari browser

**Akses:**
```
GET /api/pdf-preview.php?id=1
```

**Yang Ditampilkan:**
- Informasi penawaran (nomor dokumen, klien, paket, siswa, PIC)
- Service yang didapat (berdasarkan tipe paket)
- Bonus & Fasilitas
- Rincian harga dengan detail breakdown
- Syarat & Ketentuan (Template A atau B berdasarkan paket)

---

### 2. **api/pdf.php** (EXISTING - PRINT/DOWNLOAD PDF)
File PDF untuk print langsung atau save as PDF
- Menggunakan tampilan modern dengan DM Fonts
- Support semua tipe paket dari database
- Automatic layout adjustment berdasarkan paket

**Akses:**
```
GET /api/pdf.php?id=1
```

---

## 🎯 Paket yang Didukung

Semua 10 paket dari KLASIFIKASI_PAKET_YEARBOOK_Parama.md.docx.txt sudah ada:

| No | Paket | Database | Service Template |
|----|-------|----------|------------------|
| 1 | Full Service | packages_fullservice | A (Keterangan) |
| 2 | E-Book Only | packages_alacarte (ebook) | A (Keterangan) |
| 3 | Edit, Desain & Cetak | packages_alacarte (editcetak) | A (Keterangan) |
| 4 | Foto Only A (½ Hari) | packages_alacarte (fotohalf) | B (Syarat & Ketentuan) |
| 5 | Foto Only B (½ Hari + Studio) | packages_alacarte (fotohalf) | B (Syarat & Ketentuan) |
| 6 | Full Day (8 Jam) | packages_alacarte (fotofull) | B (Syarat & Ketentuan) |
| 7 | Video Drone | packages_alacarte (videodrone) | B (Syarat & Ketentuan) |
| 8 | Short Movie / Docudrama | packages_alacarte (videodoc) | B (Syarat & Ketentuan) |
| 9 | Desain Only | packages_alacarte (desain) | A (Keterangan) |
| 10 | Cetak Only | packages_alacarte (cetakonly) | A (Keterangan) |

---

## 🔄 Logika Template

### Template A: Keterangan + Penutup (Produk Fisik & Digital)
**Digunakan untuk:**
- Full Service
- E-Book Only
- Edit, Desain & Cetak
- Desain Only
- Cetak Only

**Isi:**
- Bullet points keterangan harga
- Paragraf penutup formal

---

### Template B: Syarat & Ketentuan (Jasa Only)
**Digunakan untuk:**
- Foto Only A & B
- Full Day
- Video Drone
- Short Movie / Docudrama

**Isi:**
- Numbered syarat & ketentuan
- Paragraf penutup dengan penekanan jasa

---

## 🎨 Service Mapping

Setiap paket memiliki list service yang spesifik sesuai klasifikasi:

**Full Service (11 services):**
- Creative Brief
- Photography
- Studio Photo Delivery
- Property
- Fashion Stylist
- Editing
- Design
- E-Book
- Project Report
- Shipping
- Guarantee

**E-Book (10 services):**
- Creative Brief
- Photography
- Studio Photo Delivery (opsional)
- Property (opsional)
- Fashion Stylist (opsional)
- Editing
- Design
- E-Book
- Project Report
- Guarantee

**Foto Only A/B (6-8 services):**
- Photography
- Studio Photo Delivery (B only)
- Property (B only)
- Fashion Stylist
- Editing (45 foto)
- Project Report
- Crew
- Guarantee

**Dst... (sesuai klasifikasi dokumen)**

---

## 🖨️ Print/Export

### Dari Preview (pdf-preview.php):
1. Klik tombol "Cetak PDF" di navbar
2. Pilih "Save as PDF" di browser print dialog
3. File akan tersimpan lokally

### Dari PDF Page (pdf.php):
1. Browser print → Save as PDF
2. Atau gunakan tool PDF extension di browser

---

## 📝 Catatan Implementasi

1. **Database**: Semua paket sudah ada di `packages_alacarte` dan `packages_fullservice`
2. **Font**: Menggunakan Google Fonts (DM Serif Display + DM Sans)
3. **Warna**: Konsisten dengan brand Parama Studio
   - Navy: #1A2236 (header/footer)
   - Orange: #C0602A (accent)
   - Gray: #6B7280 (text supporting)
4. **Responsive**: Layout sidebar + preview beradaptasi dengan ukuran window
5. **Print-Ready**: Semua background color tercetak dengan proper color-adjust

---

## 🚀 Cara Menggunakan

### Untuk Pengguna (Sales/Manager):
1. Buka kalkulator penawaran
2. Isi data klien, paket, jumlah siswa
3. Klik tombol "Preview PDF"
4. Akan membuka `api/pdf-preview.php?id=...`
5. Review di sidebar (left panel) + preview PDF (right panel)
6. Klik "Cetak PDF" untuk save/print

### Untuk Developer:
- Modifikasi service & bonus di kondisi `if ($isFullService)` dst di `api/pdf-preview.php`
- Tambah paket baru ke database, update kondisi di PHP
- Update template keterangan/terms sesuai kebutuhan

---

## ✨ Feature Highlights

✅ **Same as Mockup Layout**
- Top bar dengan branding
- Left panel sidebar dengan info & summary
- Right panel dengan PDF preview
- Responsive & printable

✅ **All 10 Packages Supported**
- Mapping lengkap dari klasifikasi dokumen
- Service & bonus per paket
- Template Terms otomatis

✅ **Modern Design**
- DM Fonts untuk typography premium
- Color scheme profesional
- Proper spacing & hierarchy
- Print-optimized styling

✅ **Mobile & Desktop Ready**
- Layout grid responsive
- Print stylesheet included
- Cross-browser compatible

---

## 📞 Support

Jika ada pertanyaan atau perlu modifikasi:
1. Cek file `KLASIFIKASI_PAKET_YEARBOOK_Parama.md.docx.txt` untuk reference
2. Update database `packages_alacarte` jika ada perubahan paket
3. Modifikasi kondisi PHP di `api/pdf-preview.php` atau `api/pdf.php`

**Last Updated: April 27, 2026**
**Version: 1.0 - Full Implementation**
