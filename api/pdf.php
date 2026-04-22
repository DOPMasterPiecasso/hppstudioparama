<?php
/**
 * api/pdf.php — Penawaran HTML Print View
 * Tampilan sama persis dengan penawaran_parama_template.html
 * Gunakan browser Print → Save as PDF
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireAuth();

// ── Ambil data penawaran dari MySQL ──────────────────────────
$pdo = getMySQLConnection();
if (!$pdo) { http_response_code(500); die('Database error'); }

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); die('ID penawaran diperlukan'); }

$stmt = $pdo->prepare("
    SELECT p.*, u.name AS added_by_name
    FROM penawaran p
    LEFT JOIN users u ON p.added_by = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) { http_response_code(404); die('Penawaran tidak ditemukan'); }

// ── Helper functions ─────────────────────────────────────────
function rp(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function tanggal(string $dateStr = ''): string {
    $ts = $dateStr ? strtotime($dateStr) : time();
    $bulan = ['Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
    return date('j', $ts) . ' ' . $bulan[date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

// ── Compose data ─────────────────────────────────────────────
$docId     = 'PS-' . date('Ymd', strtotime($p['created_at'])) . '-' . str_pad($p['id'], 3, '0', STR_PAD_LEFT);
$namaKlien = $p['nama_klien'] ?? '';
$paket     = $p['paket'] ?? '';
$siswa     = (int)($p['jumlah_siswa'] ?? 0);
$harga     = (int)($p['harga'] ?? 0);
$hargaDP   = (int)($p['harga_sebelum_diskon'] ?? 0);
$catatan   = $p['catatan'] ?? '';
$addedBy   = $p['added_by_name'] ?? 'Parama Studio';
$tglDoc    = tanggal($p['created_at']);
$tglExp    = tanggal(date('Y-m-d', strtotime($p['created_at'] . ' +14 days')));
$perBuku   = $siswa > 0 ? round($harga / $siswa) : $harga;

// Logo sebagai base64 agar bisa di-print tanpa path issue
$logoPath = __DIR__ . '/../assets/logopdf/logo.png';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
}

// Parse catatan untuk bonus/add-on
$addons = [];
$bonusExtra = [];
$diskonInfo = '';
if ($catatan) {
    foreach (explode('|', $catatan) as $part) {
        $part = trim($part);
        if (strpos($part, 'bonus:') === 0) {
            $bonusExtra[] = trim(substr($part, 6));
        } elseif (strpos($part, 'diskon ') === 0 || strpos($part, 'cashback ') === 0) {
            $diskonInfo = $part;
        } elseif ($part) {
            $addons[] = $part;
        }
    }
}

// ── Deteksi tipe paket ────────────────────────────────────────
$paketLower    = strtolower($paket);
$isFullService = (strpos($paketLower, 'full service') !== false);
$isAlacarte    = (strpos($paketLower, 'à la carte') !== false
               || strpos($paketLower, 'a la carte') !== false
               || strpos($paketLower, 'la carte') !== false);
$isGraduation  = (strpos($paketLower, 'graduation') !== false);

// ── Tipe buku (Handy / Minimal / Large) ──────────────────────
$pkgType = 'handy';
if (strpos($paketLower, 'minimal') !== false) $pkgType = 'minimal';
elseif (strpos($paketLower, 'large') !== false) $pkgType = 'large';

// ── Spesifikasi Buku sesuai tipe ─────────────────────────────
$ukuranBuku     = 'A4+ (22 × 30 cm)';
$hasTipe        = 'Handy Book A4+';
$jenisCover     = 'Hard Cover, AC 190gsm, Laminasi Doff';
$jenisFinishing = 'Binding Jahit';
$jenisKertas    = 'Art Paper 150gsm';
$jenisPackaging = 'Slongsong';

if ($pkgType === 'minimal') {
    $ukuranBuku     = 'SQ (25 × 25 cm)';
    $hasTipe        = 'Minimal Book SQ';
    $jenisCover     = 'Soft Cover, AC 150gsm, Laminasi Doff';
    $jenisFinishing = 'Binding Jahit / Staples';
    $jenisKertas    = 'Art Paper 120gsm';
    $jenisPackaging = 'Plastik Wrap';
} elseif ($pkgType === 'large') {
    $ukuranBuku     = 'B4 (25 × 35 cm)';
    $hasTipe        = 'Large Book B4';
    $jenisCover     = 'Hard Cover, AC 210gsm, Laminasi Doff';
    $jenisFinishing = 'Binding Jahit';
    $jenisKertas    = 'Art Paper 170gsm';
    $jenisPackaging = 'Slongsong';
}

// ── Ambil jumlah halaman dari database (tbl_fs_prices) ───────
$jumlahHalaman = null;
if ($isFullService && $siswa > 0) {
    $stmtPages = $pdo->prepare("
        SELECT pages FROM tbl_fs_prices
        WHERE pkg = ? AND min_siswa <= ? AND max_siswa >= ?
        LIMIT 1
    ");
    $stmtPages->execute([$pkgType, $siswa, $siswa]);
    $rowPages = $stmtPages->fetch(PDO::FETCH_ASSOC);
    if ($rowPages) {
        $jumlahHalaman = (int)$rowPages['pages'];
    }
}

// ── Jasa Termasuk sesuai tipe paket ──────────────────────────
if ($isFullService) {
    $jasaTermasuk = 'Foto Produksi (Personal, Konsep, Konten) &nbsp;·&nbsp; Desain Cover &amp; Layout &nbsp;·&nbsp; Editing Foto Terpilih';
} elseif ($isAlacarte) {
    if (strpos($paketLower, 'e-book') !== false || strpos($paketLower, 'ebook') !== false) {
        $jasaTermasuk = 'Foto Produksi &nbsp;·&nbsp; Editing &nbsp;·&nbsp; Desain &amp; Layout (Output: File Digital)';
    } elseif (strpos($paketLower, 'edit') !== false && strpos($paketLower, 'cetak') !== false) {
        $jasaTermasuk = 'Editing Foto Terpilih &nbsp;·&nbsp; Desain &amp; Layout &nbsp;·&nbsp; Cetak &amp; Kirim';
    } elseif (strpos($paketLower, 'foto only') !== false) {
        $jasaTermasuk = 'Sesi Foto Produksi &nbsp;·&nbsp; Fashion Stylist';
    } elseif (strpos($paketLower, 'drone') !== false) {
        $jasaTermasuk = 'Video Drone 1–2 menit';
    } elseif (strpos($paketLower, 'docudrama') !== false || strpos($paketLower, 'video') !== false) {
        $jasaTermasuk = 'Video Cerita Angkatan 5–10 menit';
    } elseif (strpos($paketLower, 'desain') !== false) {
        $jasaTermasuk = 'Layout &amp; Desain Buku (Klien sediakan konten)';
    } elseif (strpos($paketLower, 'cetak only') !== false) {
        $jasaTermasuk = 'Cetak &amp; Pengiriman (File siap cetak dari klien)';
    } else {
        $jasaTermasuk = 'Sesuai paket yang dipilih';
    }
} elseif ($isGraduation) {
    $jasaTermasuk = 'Dokumentasi Wisuda (Foto &amp; Video sesuai paket)';
} else {
    $jasaTermasuk = 'Sesuai paket yang dipilih';
}

// ── Bonus & Fasilitas — dari tabel bonus_fasilitas (DB) ──────
$dbPkgType = 'fullservice'; // default
$dbKategori = 'all';

if ($isGraduation) {
    $dbPkgType = 'graduation';
    $stmtGrad = $pdo->query("SELECT setting_value FROM tbl_settings WHERE setting_key = 'grad_packages'");
    $gradPkgJson = $stmtGrad->fetchColumn();
    if ($gradPkgJson) {
        $gradPkgs = json_decode($gradPkgJson, true);
        if (is_array($gradPkgs)) {
            foreach ($gradPkgs as $gpkg) {
                if (stripos($paket, $gpkg['name']) !== false) {
                    $dbKategori = $gpkg['id'];
                    break;
                }
            }
        }
    }
} elseif ($isAlacarte) {
    $dbPkgType = 'alacarte';
    if (strpos($paketLower, 'e-book') !== false) $dbKategori = 'ac-ebook';
    elseif (strpos($paketLower, 'edit') !== false && strpos($paketLower, 'cetak') !== false) $dbKategori = 'ac-editcetak';
    elseif (strpos($paketLower, 'foto only') !== false && strpos($paketLower, '½ hari') !== false) $dbKategori = 'ac-fotohalf';
    elseif (strpos($paketLower, 'foto only') !== false) $dbKategori = 'ac-fotofull';
    elseif (strpos($paketLower, 'drone') !== false) $dbKategori = 'ac-videod';
    elseif (strpos($paketLower, 'docudrama') !== false || strpos($paketLower, 'video') !== false) $dbKategori = 'ac-videodoc';
    elseif (strpos($paketLower, 'desain') !== false) $dbKategori = 'ac-desain';
    elseif (strpos($paketLower, 'cetak only') !== false) $dbKategori = 'ac-cetakonly';
} else {
    $dbPkgType = 'fullservice';
    if ($pkgType === 'minimal') $dbKategori = 'fs-minimal';
    elseif ($pkgType === 'large') $dbKategori = 'fs-large';
    else $dbKategori = 'fs-handy';
}

$bonusStandar = [];
try {
    $stmtBonus = $pdo->prepare(
        "SELECT label, detail FROM bonus_fasilitas
         WHERE package_type = ? AND active = 1 AND (kategori = 'all' OR kategori = ? OR kategori = '' OR kategori IS NULL)
         ORDER BY display_order ASC, id ASC"
    );
    $stmtBonus->execute([$dbPkgType, $dbKategori]);
    $bonusStandar = $stmtBonus->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Jika tabel belum ada atau query gagal, biarkan kosong
    $bonusStandar = [];
}





$subtitle = implode('  ·  ', array_filter([$paket, $siswa > 0 ? $siswa . ' siswa' : '']));

$filename = 'Penawaran_' . preg_replace('/[^a-z0-9]/i', '_', $namaKlien) . '_' . $docId . '.pdf';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($docId) ?> — Penawaran Parama Studio</title>
<style>
/* ══════════════ RESET & BASE ══════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* Screen styling */
body {
    font-family: Arial, sans-serif;
    background: #e9e9e9;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 24px 16px 60px;
    min-height: 100vh;
    color: #1a1714;
}

/* Toolbar */
.toolbar {
    width: 100%;
    max-width: 595pt;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding: 12px 16px;
    background: #1c2e3d;
    border-radius: 8px;
    gap: 12px;
    flex-wrap: wrap;
}
.toolbar-left { font-size: 13px; color: rgba(255,255,255,0.7); }
.toolbar-left b { color: #fff; font-size: 14px; }
.toolbar-btns { display: flex; gap: 8px; }
.btn-print {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 6px;
    font-size: 13px; font-weight: 600;
    cursor: pointer; border: none;
    font-family: Arial, sans-serif;
    transition: all 0.15s;
}
.btn-primary { background: #c85b2a; color: #fff; }
.btn-primary:hover { background: #a84820; }
.btn-secondary { background: rgba(255,255,255,0.12); color: #fff; border: 1px solid rgba(255,255,255,0.25); }
.btn-secondary:hover { background: rgba(255,255,255,0.2); }

/* ══════════════ DOCUMENT (A4 proportion) ══════════════ */
.doc {
    width: 595pt;
    min-height: 842pt;
    background: #ffffff;
    box-shadow: 0 4px 40px rgba(0,0,0,0.2);
    border-radius: 2px;
    display: flex;
    flex-direction: column;
    padding: 0;
    overflow: hidden;
}
/* Body konten mengisi sisa ruang agar footer selalu di bawah */
.doc-body { flex: 1; }

/* ── HEADER ── */
.doc-header {
    background: #1c2e3d;
    padding: 16px 36px 14px;
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 0 20px;
    align-items: center;
}
.hd-logo img { width: 52px; height: 52px; object-fit: contain; display: block; }
.hd-logo-placeholder {
    width: 52px; height: 52px;
    background: #c85b2a;
    border-radius: 4px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 16px;
}
.hd-company { padding-left: 4px; }
.hd-company .co-name { font-size: 18pt; font-weight: 700; color: #ffffff; line-height: 1.2; }
.hd-company .co-tag { font-size: 8pt; color: #9db8c8; margin-top: 1px; }
.hd-company .co-contact { font-size: 7pt; color: #6a8a9d; margin-top: 2px; }
.hd-docinfo { text-align: right; }
.hd-docinfo .di-label { font-size: 7pt; font-weight: 700; color: #9db8c8; letter-spacing: 0.06em; }
.hd-docinfo .di-id { font-size: 12pt; font-weight: 700; color: #c85b2a; line-height: 1.3; }
.hd-docinfo .di-date { font-size: 7.5pt; color: #9db8c8; }

/* ── BODY ── */
.doc-body { padding: 22px 36px 0; flex: 1; }

/* Ditujukan kepada */
.to-block {
    display: flex;
    margin-bottom: 18px;
}
.to-bar { width: 5px; background: #c85b2a; flex-shrink: 0; border-radius: 1px; }
.to-content { background: #f7f5f0; flex: 1; padding: 10px 14px; }
.to-label { font-size: 7pt; font-weight: 700; color: #9c9890; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 3px; }
.to-name { font-size: 15pt; font-weight: 700; color: #1c2e3d; line-height: 1.2; margin-bottom: 3px; }
.to-sub { font-size: 8pt; color: #5c5750; }

/* Section label */
.sec-label {
    font-size: 7pt; font-weight: 700; color: #9c9890;
    letter-spacing: 0.08em; text-transform: uppercase;
    margin-bottom: 6px;
}

/* Tabel spesifikasi */
.spec-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 8pt; }
.spec-table td { padding: 5px 8px; border: 0.5pt solid #dddddd; vertical-align: middle; }
.spec-table tr:nth-child(odd) td { background: #f7f5f0; }
.spec-table tr:nth-child(even) td { background: #ffffff; }
.spec-table .spec-key { width: 33%; color: #5c5750; font-weight: 400; }
.spec-table .spec-val { color: #1a1714; font-weight: 700; }

/* Bonus block */
.bonus-block {
    display: flex;
    margin-bottom: 18px;
}
.bonus-bar { width: 5px; background: #2d7a4a; flex-shrink: 0; border-radius: 1px; }
.bonus-content { background: #e8f5ed; flex: 1; padding: 10px 14px; }
.bonus-item { display: flex; align-items: flex-start; gap: 6px; margin-bottom: 4px; font-size: 8pt; line-height: 1.5; }
.bonus-item:last-child { margin-bottom: 0; }
.bonus-check { color: #2d7a4a; font-weight: 700; font-size: 9pt; flex-shrink: 0; margin-top: 1px; }
.bonus-text { color: #1a4a2e; }
.bonus-text b { color: #2d7a4a; }

/* Rincian harga */
.price-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; font-size: 8.5pt; }
.price-table td { padding: 6px 10px; border: 0.5pt solid #dddddd; }
.price-table .pt-label { background: #ffffff; color: #5c5750; }
.price-table .pt-val { background: #ffffff; color: #1a1714; font-weight: 700; text-align: right; width: 30%; }
.price-total td { background: #1c2e3d !important; }
.price-total .pt-label { color: #9db8c8 !important; font-weight: 700; font-size: 7.5pt; letter-spacing: 0.04em; }
.price-total .pt-val { color: #ffffff !important; font-size: 12pt; font-weight: 700; }
.price-sub { width: 30%; font-size: 7.5pt; text-align: right; }
.price-sub td { background: #f0ede6; color: #5c5750; border-color: #dddddd; font-size: 7.5pt; }

/* Notes */
.notes-block {
    display: flex;
    margin-bottom: 20px;
}
.notes-bar { width: 5px; background: #9c9890; flex-shrink: 0; border-radius: 1px; }
.notes-content { background: #f0ede6; flex: 1; padding: 8px 14px; font-size: 7.5pt; color: #5c5750; line-height: 1.7; }
.notes-content li { list-style: disc; margin-left: 14px; }

/* TTD / Signature */
.sign-section { display: grid; grid-template-columns: 1fr 1fr; gap: 0; margin-bottom: 8px; }
.sign-col { padding: 8px 10px; }
.sign-label { font-size: 8pt; color: #5c5750; margin-bottom: 70px; }
.sign-line { border-top: 0.5pt solid #5c5750; padding-top: 4px; }
.sign-name { font-size: 9pt; font-weight: 700; color: #1a1714; }
.sign-role { font-size: 7.5pt; color: #9c9890; }
.sign-col-right { text-align: right; }
.sign-col-right .sign-label { text-align: right; }
.sign-slot { font-size: 9pt; color: #5c5750; font-family: Arial, sans-serif; }
.sign-slot-role { font-size: 7.5pt; color: #9c9890; text-align: right; }

/* ── FOOTER ── */
.doc-footer {
    background: #1c2e3d;
    padding: 10px 36px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: auto;
}
.ft-address { font-size: 6.5pt; color: #6a8a9d; }
.ft-validity { font-size: 6.5pt; color: #6a8a9d; text-align: right; }

/* ══════════════ PRINT STYLES ══════════════ */
@media print {
    /* Paksa browser cetak background color */
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    body {
        background: #fff;
        padding: 0;
        margin: 0;
        display: block;
    }

    .toolbar { display: none !important; }

    .doc {
        width: 210mm;
        height: 297mm;
        min-height: 297mm;
        max-height: 297mm;
        box-shadow: none;
        border-radius: 0;
        margin: 0;
        overflow: hidden;
    }

    .doc-body { flex: 1; }

    /* Pastikan header & footer navy tercetak */
    .doc-header {
        background: #1c2e3d !important;
    }
    .doc-footer {
        background: #1c2e3d !important;
    }

    /* Pastikan tabel total navy tercetak */
    .price-total td {
        background: #1c2e3d !important;
    }

    /* Pastikan bonus hijau tercetak */
    .bonus-content {
        background: #e8f5ed !important;
    }

    /* Pastikan notes tercetak */
    .notes-content {
        background: #f0ede6 !important;
    }

    /* Pastikan spek tabel tercetak */
    .spec-table tr:nth-child(odd) td {
        background: #f7f5f0 !important;
    }

    /* to-content & bar */
    .to-content {
        background: #f7f5f0 !important;
    }
    .to-bar { background: #c85b2a !important; }
    .bonus-bar { background: #2d7a4a !important; }
    .notes-bar { background: #9c9890 !important; }

    @page {
        size: A4;
        margin: 0;
    }

    a { text-decoration: none; color: inherit; }
}
</style>
</head>
<body>

<!-- ── TOOLBAR (screen only) ── -->
<div class="toolbar">
    <div class="toolbar-left">
        <b><?= e($docId) ?></b><br>
        <?= e($namaKlien) ?>
    </div>
    <div class="toolbar-btns">
        <button class="btn-print btn-secondary" onclick="if(window.history.length > 1) { history.back(); } else { window.close(); }">← Kembali</button>
        <button class="btn-print btn-primary" onclick="window.print()">🖨 Print / Save PDF</button>
    </div>
</div>

<!-- ══════════════ DOCUMENT ══════════════ -->
<div class="doc">

    <!-- ── HEADER ── -->
    <div class="doc-header">
        <div class="hd-logo">
            <?php if ($logoBase64): ?>
                <img src="<?= $logoBase64 ?>" alt="Parama Studio Logo">
            <?php else: ?>
                <div class="hd-logo-placeholder">PS</div>
            <?php endif; ?>
        </div>
        <div class="hd-company">
            <div class="co-name">Parama Studio</div>
            <div class="co-tag">Yearbook &amp; Graduation Agency</div>
            <div class="co-contact">studioparama.com &nbsp;·&nbsp; +62 822 9400 8994 &nbsp;·&nbsp; Tangerang Selatan</div>
        </div>
        <div class="hd-docinfo">
            <div class="di-label">PENAWARAN HARGA</div>
            <div class="di-id"><?= e($docId) ?></div>
            <div class="di-date"><?= e($tglDoc) ?></div>
        </div>
    </div>

    <!-- ── BODY ── -->
    <div class="doc-body">

        <!-- Ditujukan Kepada -->
        <div class="to-block">
            <div class="to-bar"></div>
            <div class="to-content">
                <div class="to-label">Ditujukan Kepada</div>
                <div class="to-name"><?= e($namaKlien) ?></div>
                <div class="to-sub"><?= e($subtitle) ?></div>
            </div>
        </div>

        <!-- Spesifikasi Buku -->
        <div class="sec-label">Spesifikasi Buku</div>
        <table class="spec-table">
            <tr><td class="spec-key">Jumlah Pesanan</td><td class="spec-val"><?= $siswa > 0 ? $siswa . ' Buku' : '—' ?></td></tr>
            <?php if ($jumlahHalaman !== null): ?>
            <tr><td class="spec-key">Jumlah Halaman</td><td class="spec-val"><?= $jumlahHalaman ?> Halaman</td></tr>
            <?php endif; ?>
            <?php if ($isFullService || $isAlacarte): ?>
            <tr><td class="spec-key">Ukuran Buku</td><td class="spec-val"><?= e($ukuranBuku) ?></td></tr>
            <tr><td class="spec-key">Jenis Kertas</td><td class="spec-val"><?= e($jenisKertas) ?></td></tr>
            <tr><td class="spec-key">Cover</td><td class="spec-val"><?= e($jenisCover) ?></td></tr>
            <tr><td class="spec-key">Packaging</td><td class="spec-val"><?= e($jenisPackaging) ?></td></tr>
            <tr><td class="spec-key">Finishing</td><td class="spec-val"><?= e($jenisFinishing) ?></td></tr>
            <?php endif; ?>
            <tr><td class="spec-key">Jasa Termasuk</td><td class="spec-val"><?= $jasaTermasuk ?></td></tr>
        </table>

        <!-- Bonus & Fasilitas -->
        <?php if (!empty($bonusStandar) || !empty($bonusExtra)): ?>
        <div class="sec-label">Bonus &amp; Fasilitas</div>
        <div class="bonus-block">
            <div class="bonus-bar"></div>
            <div class="bonus-content">
                <?php foreach ($bonusStandar as $bs): ?>
                <div class="bonus-item"><span class="bonus-check">✓</span><span class="bonus-text"><b><?= e($bs['label']) ?>:</b> <?= $bs['detail'] ?></span></div>
                <?php endforeach; ?>
                <?php foreach ($bonusExtra as $b): ?>
                <div class="bonus-item"><span class="bonus-check">✓</span><span class="bonus-text"><?= e($b) ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Rincian Harga -->
        <div class="sec-label">Rincian Harga</div>
        <table class="price-table">
            <!-- Baris base price -->
            <tr>
                <td class="pt-label">
                    <?php if ($isGraduation): ?>
                        Harga Paket Graduation
                    <?php elseif ($isAlacarte): ?>
                        Harga Layanan
                    <?php else: ?>
                        Harga Buku Tahunan
                        <?php if ($siswa > 0): ?>
                            &nbsp;(<?= $siswa ?> buku × <?= rp($perBuku) ?>)
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td class="pt-val"><?= rp($harga) ?></td>
            </tr>

            <!-- Add-on dari catatan -->
            <?php foreach ($addons as $addon): ?>
            <tr>
                <td class="pt-label"><?= e(ucfirst($addon)) ?></td>
                <td class="pt-val">—</td>
            </tr>
            <?php endforeach; ?>

            <!-- Diskon jika ada -->
            <?php if ($hargaDP > 0 && $hargaDP !== $harga): ?>
            <tr>
                <td class="pt-label" style="color:#a02020">Diskon</td>
                <td class="pt-val" style="color:#a02020">− <?= rp($hargaDP - $harga) ?></td>
            </tr>
            <?php endif; ?>

            <!-- Total baris navy -->
            <tr class="price-total">
                <td class="pt-label">TOTAL HARGA PENAWARAN</td>
                <td class="pt-val"><?= rp($harga) ?></td>
            </tr>
        </table>



        <!-- Ketentuan -->
        <div class="notes-block" style="margin-top:12px">
            <div class="notes-bar"></div>
            <div class="notes-content">
                <ul>
                    <?php if ($isFullService && $siswa > 0): ?>
                    <li>Harga berlaku untuk minimal <?= $siswa ?> pemesan Buku Tahunan.</li>
                    <?php elseif ($isGraduation): ?>
                    <li>Harga berlaku untuk event yang telah disepakati.</li>
                    <?php else: ?>
                    <li>Harga berlaku sesuai spesifikasi yang tercantum.</li>
                    <?php endif; ?>
                    <li>Harga bersifat penawaran dan dapat berubah sesuai kesepakatan.</li>
                    <?php if ($diskonInfo): ?>
                    <li>Telah diperhitungkan: <?= e($diskonInfo) ?>.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Tanda Tangan -->
        <div class="sign-section">
            <div class="sign-col">
                <div class="sign-label">Hormat kami,</div>
                <div class="sign-line">
                    <div class="sign-name">Dhamar Singgih Wicaksono</div>
                    <div class="sign-role">Marketing — Parama Studio</div>
                </div>
            </div>
            <div class="sign-col sign-col-right">
                <div class="sign-label">Disetujui oleh,</div>
                <div class="sign-line">
                    <div class="sign-slot">(________________________)</div>
                    <div class="sign-slot-role">Nama &amp; Jabatan</div>
                </div>
            </div>
        </div>

    </div><!-- /doc-body -->

    <!-- ── FOOTER ── -->
    <div class="doc-footer">
        <div class="ft-address">
            PT. Parama Kreatif Sukses &nbsp;·&nbsp; Rawa Buntu Utara Blok G1 No.12, Serpong, Tangerang Selatan 15810
        </div>
        <div class="ft-validity">
            <?= e($docId) ?> &nbsp;·&nbsp; Berlaku s/d <?= e($tglExp) ?>
        </div>
    </div>

</div><!-- /doc -->

</body>
</html>
