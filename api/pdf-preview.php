<?php
/**
 * api/pdf-preview.php — PDF Preview dengan Sidebar & Navbar
 * Tampilan sama persis dengan mockup termasuk left panel, navbar, dan right panel preview
 */
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';

$user = requireAuth();
$pdo = getMySQLConnection();

if (!$pdo) {
    http_response_code(500);
    die('Database error');
}

// Ambil data penawaran
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    die('ID penawaran diperlukan');
}

$stmt = $pdo->prepare("
    SELECT p.*, u.name AS added_by_name
    FROM penawaran p
    LEFT JOIN users u ON p.added_by = u.id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$penawaran = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$penawaran) {
    http_response_code(404);
    die('Penawaran tidak ditemukan');
}

// Helper functions
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

// Prepare data
$docId = 'PS-' . date('Ymd', strtotime($penawaran['created_at'])) . '-' . str_pad($penawaran['id'], 3, '0', STR_PAD_LEFT);
$namaKlien = $penawaran['nama_klien'] ?? '';
$paket = $penawaran['paket'] ?? '';
$siswa = (int)($penawaran['jumlah_siswa'] ?? 0);
$harga = (int)($penawaran['harga'] ?? 0);
$hargaDP = (int)($penawaran['harga_sebelum_diskon'] ?? 0);
$addedBy = $penawaran['added_by_name'] ?? 'Parama Studio';
$tglDoc = tanggal($penawaran['created_at']);
$tglExp = tanggal(date('Y-m-d', strtotime($penawaran['created_at'] . ' +14 days')));
$perBuku = $siswa > 0 ? round($harga / $siswa) : $harga;

// Paket info
$paketLower = strtolower($paket);
$isFullService = (strpos($paketLower, 'full service') !== false);
$isEBook = (strpos($paketLower, 'e-book') !== false || strpos($paketLower, 'ebook') !== false);
$isEditCetak = (strpos($paketLower, 'edit') !== false && strpos($paketLower, 'cetak') !== false);
$isFotoA = (strpos($paketLower, 'foto') !== false && (strpos($paketLower, '½') !== false || strpos($paketLower, 'half') !== false) && strpos($paketLower, 'studio') === false);
$isFotoB = (strpos($paketLower, 'foto') !== false && (strpos($paketLower, '½') !== false || strpos($paketLower, 'half') !== false) && strpos($paketLower, 'studio') !== false);
$isFotoFull = (strpos($paketLower, 'full day') !== false);
$isVideoDrone = (strpos($paketLower, 'drone') !== false);
$isVideoMovie = (strpos($paketLower, 'docudrama') !== false || (strpos($paketLower, 'video') !== false && strpos($paketLower, 'drone') === false));
$isDesain = (strpos($paketLower, 'desain') !== false && strpos($paketLower, 'cetak') === false);
$isCetak = (strpos($paketLower, 'cetak only') !== false);
$isGraduation = (strpos($paketLower, 'graduation') !== false);

// Service mapping berdasarkan klasifikasi
$services = [];
$bonus = [];

if ($isFullService) {
    $services = [
        'Creative Brief' => 'Konsultasi konsep & tema buku tahunan',
        'Photography' => 'Foto produksi personal, konsep kelas, dan konten',
        'Studio Photo Delivery' => 'Studio foto portable ke lokasi sekolah',
        'Property' => 'Properti foto sesuai tema kelas',
        'Fashion Stylist' => 'Pengarah gaya dan konsultasi kostum/tema',
        'Editing' => 'Editing foto terpilih',
        'Design' => 'Desain cover, packaging, dan layout halaman',
        'E-Book' => 'Versi digital buku tahunan (PDF/Flipbook)',
        'Project Report' => 'Laporan progres proyek berkala',
        'Shipping' => 'Gratis pengiriman buku ke lokasi (Jabodetabek)',
        'Guarantee' => 'Garansi revisi desain 3x sebelum cetak'
    ];
    $bonus = [
        'Buku gratis sesuai kuantitas pesanan',
        'Studio foto portable delivery ke lokasi',
        'Fashion Stylist & Properti sesuai tema'
    ];
} elseif ($isEBook) {
    $services = [
        'Creative Brief' => 'Konsultasi konsep & tema buku tahunan',
        'Photography' => 'Foto produksi personal, konsep kelas, dan konten',
        'Studio Photo Delivery' => 'Studio foto portable ke lokasi (opsional)',
        'Property' => 'Properti foto sesuai tema (opsional)',
        'Fashion Stylist' => 'Pengarah gaya (opsional)',
        'Editing' => 'Editing foto terpilih',
        'Design' => 'Desain cover dan layout halaman',
        'E-Book' => 'File digital siap distribusi (PDF/Flipbook)',
        'Project Report' => 'Laporan progres proyek berkala',
        'Guarantee' => 'Garansi revisi desain 3x'
    ];
    $bonus = ['File digital siap distribusi ke seluruh siswa'];
} elseif ($isEditCetak) {
    $services = [
        'Creative Brief' => 'Konsultasi konsep & tema desain',
        'Editing' => 'Editing dan seleksi foto dari klien',
        'Design' => 'Desain cover, packaging, dan layout isi',
        'E-Book' => 'Versi digital buku tahunan (PDF)',
        'Project Report' => 'Laporan progres edit, desain dan cetak',
        'Shipping' => 'Pengiriman buku ke lokasi klien',
        'Guarantee' => 'Garansi revisi desain 3x sebelum cetak'
    ];
    $bonus = ['File digital PDF hasil desain'];
} elseif ($isFotoA) {
    $services = [
        'Photography' => '1 Fotografer + Unlimited Shoot',
        'Fashion Stylist' => '1 Fashion Stylist (pengarah gaya)',
        'Editing' => '45 Foto Edit Terpilih',
        'Project Report' => 'Laporan dan pengiriman file via G-Drive',
        'Crew' => '1 Crew pendukung produksi',
        'Guarantee' => 'Revisi foto 1x · File maksimal 14 hari kerja'
    ];
    $bonus = [
        '45 foto hasil edit terpilih',
        'Pengiriman via Google Drive',
        'Lokasi foto disiapkan oleh sekolah'
    ];
} elseif ($isFotoB) {
    $services = [
        'Photography' => '1 Fotografer + Unlimited Shoot',
        'Studio Photo Delivery' => 'Studio foto portable ke lokasi klien',
        'Property' => 'Properti sesuai tema kelas (1 tema)',
        'Fashion Stylist' => '1 Fashion Stylist (pengarah gaya)',
        'Editing' => '45 Foto Edit Terpilih',
        'Project Report' => 'Laporan dan pengiriman file via G-Drive',
        'Crew' => '1 Crew pendukung produksi',
        'Guarantee' => 'File maksimal 14 hari kerja setelah produksi'
    ];
    $bonus = [
        '45 foto hasil edit terpilih',
        'Studio portable & properti diantar ke lokasi',
        'Pengiriman via Google Drive'
    ];
} elseif ($isFotoFull) {
    $services = [
        'Photography' => '1 Fotografer + Unlimited Shoot (8 jam)',
        'Fashion Stylist' => '1 Fashion Stylist',
        'Editing' => '45 Foto Edit Terpilih per kelas',
        'Project Report' => 'Laporan dan pengiriman file via G-Drive',
        'Crew' => '1 Crew pendukung produksi',
        'Guarantee' => 'File maksimal 14 hari kerja setelah produksi'
    ];
    $bonus = [
        '90 foto hasil edit (45 per kelas)',
        'Sesi foto selama 8 jam kerja',
        'Pengiriman via Google Drive'
    ];
} elseif ($isVideoDrone) {
    $services = [
        'Videography (Drone)' => '1 Pilot Drone + 1 Videographer + 1 Crew',
        'Creative Brief' => 'Briefing konsep formasi dan script drone',
        'Project Report' => 'Pengiriman file via G-Drive',
        'Guarantee' => 'File maksimal 14 hari kerja setelah produksi'
    ];
    $bonus = [
        'Video durasi 1–3 menit',
        'Format MP4 HD/4K',
        'Pengiriman via Google Drive'
    ];
} elseif ($isVideoMovie) {
    $services = [
        'Videography' => '1–2 Videographer + 1 Crew',
        'Creative Brief' => 'Penulisan & briefing script bersama klien',
        'Editing Video' => 'Full editing + color grading + scoring musik',
        'Project Report' => 'Pengiriman file via G-Drive',
        'Guarantee' => 'File maksimal 14 hari kerja setelah produksi'
    ];
    $bonus = [
        'Video durasi 5–10 menit',
        'Full editing dengan color grading',
        'Scoring musik & pengiriman via Google Drive'
    ];
} elseif ($isDesain) {
    $services = [
        'Creative Brief' => 'Konsultasi konsep & arah visual desain',
        'Design' => 'Desain cover, packaging, pop-up, layout halaman',
        'E-Book' => 'File PDF digital siap distribusi',
        'Project Report' => 'Update progres desain berkala',
        'Guarantee' => 'Garansi revisi desain 3x'
    ];
    $bonus = ['File PDF digital siap distribusi'];
} elseif ($isCetak) {
    $services = [
        'Printing' => 'Cetak buku dengan spesifikasi sesuai permintaan',
        'Setting & Prepress' => 'Setting file sebelum cetak (cek bleed/margin, dll)',
        'Project Report' => 'Update status cetak dan estimasi selesai',
        'Shipping' => 'Pengiriman buku ke lokasi klien',
        'Guarantee' => 'Quality control & garansi buku cacat/reject'
    ];
    $bonus = ['Estimasi cetak: 21–45 hari kerja'];
}

// Template Terms berdasarkan tipe paket
$templateTerms = 'A'; // Default
if ($isFotoA || $isFotoB || $isFotoFull || $isVideoDrone || $isVideoMovie) {
    $templateTerms = 'B'; // Syarat & Ketentuan bernomor
}

$diskon = $hargaDP > $harga ? ($hargaDP - $harga) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($docId) ?> — PDF Preview Penawaran</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --orange: #C0602A;
            --orange-light: #F4EBE4;
            --orange-mid: #E08050;
            --navy: #1A2236;
            --navy-mid: #2C3A55;
            --gray: #6B7280;
            --gray-light: #F5F5F3;
            --border: #E5E0D8;
            --white: #FDFCFA;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: #ECEAE5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP BAR ── */
        .topbar {
            background: var(--navy);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            height: 56px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .topbar-brand span {
            color: var(--orange-mid);
        }

        .topbar-logo {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid var(--orange-mid);
            display: grid;
            place-items: center;
            font-size: 13px;
        }

        .topbar-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn-ghost {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background 0.15s;
        }

        .btn-ghost:hover {
            background: rgba(255,255,255,0.15);
        }

        .btn-primary {
            background: var(--orange);
            border: none;
            color: white;
            padding: 7px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
        }

        .btn-primary:hover {
            background: #A85424;
        }

        /* ── LAYOUT ── */
        .layout {
            display: grid;
            grid-template-columns: 340px 1fr;
            min-height: calc(100vh - 56px);
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            background: var(--white);
            border-right: 1px solid var(--border);
            overflow-y: auto;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .panel-title {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--gray);
            margin-bottom: 4px;
            margin-top: 8px;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 8px 0;
        }

        .info-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-bottom: 8px;
        }

        .info-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            font-size: 13px;
            font-weight: 500;
            color: var(--navy);
        }

        .summary-box {
            background: var(--navy);
            border-radius: 12px;
            padding: 16px;
            margin-top: 8px;
            color: white;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: rgba(255,255,255,0.6);
            margin-bottom: 6px;
        }

        .summary-row span:last-child {
            color: white;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid rgba(255,255,255,0.15);
            padding-top: 10px;
            margin-top: 4px;
        }

        .summary-total-label {
            font-size: 12px;
            color: rgba(255,255,255,0.7);
        }

        .summary-total-value {
            font-size: 18px;
            font-weight: 700;
            color: var(--orange-mid);
        }

        /* ── RIGHT PANEL ── */
        .right-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px;
            overflow-y: auto;
            background: #DEDAD4;
            gap: 16px;
        }

        .preview-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #9C9589;
            align-self: flex-start;
            margin-bottom: -8px;
        }

        /* PDF SHEET */
        .pdf-sheet {
            width: 680px;
            background: white;
            border-radius: 4px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.18);
            overflow: hidden;
            font-family: 'DM Sans', sans-serif;
        }

        .pdf-header {
            background: var(--navy);
            padding: 28px 36px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .pdf-brand {
            color: white;
        }

        .pdf-brand-name {
            font-family: 'DM Serif Display', serif;
            font-size: 22px;
            letter-spacing: 0.01em;
            font-weight: 700;
        }

        .pdf-brand-tagline {
            font-size: 10px;
            color: rgba(255,255,255,0.5);
            margin-top: 2px;
            letter-spacing: 0.05em;
        }

        .pdf-brand-contact {
            font-size: 10px;
            color: rgba(255,255,255,0.4);
            margin-top: 8px;
            line-height: 1.7;
        }

        .pdf-doc-info {
            text-align: right;
        }

        .pdf-doc-number {
            color: var(--orange-mid);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }

        .pdf-doc-date {
            font-size: 11px;
            color: rgba(255,255,255,0.45);
            margin-top: 4px;
        }

        .pdf-body {
            padding: 28px 36px;
        }

        .pdf-to-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--gray);
            margin-bottom: 4px;
        }

        .pdf-client-name {
            font-family: 'DM Serif Display', serif;
            font-size: 26px;
            color: var(--navy);
            line-height: 1.1;
            font-weight: 700;
        }

        .pdf-paket-tag {
            display: inline-block;
            background: var(--orange-light);
            color: var(--orange);
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            margin-top: 6px;
        }

        .pdf-section {
            margin-top: 24px;
        }

        .pdf-section-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--gray);
            border-bottom: 1px solid var(--border);
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .pdf-spec-table,
        .pdf-price-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .pdf-spec-table td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .pdf-spec-table td:first-child {
            color: var(--gray);
            width: 38%;
            font-size: 11px;
        }

        .pdf-spec-table td:last-child {
            color: var(--navy);
            font-weight: 500;
        }

        .pdf-spec-table tr:nth-child(odd) td {
            background: var(--gray-light);
        }

        .pdf-price-table thead tr {
            background: var(--navy-mid);
            color: white;
        }

        .pdf-price-table thead td {
            padding: 8px 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .pdf-price-table tbody tr td {
            padding: 9px 12px;
            color: var(--navy-mid);
            border-bottom: 1px solid var(--border);
        }

        .pdf-price-table tbody tr:nth-child(odd) td {
            background: var(--gray-light);
        }

        .pdf-price-table .price-col {
            text-align: right;
            font-weight: 500;
        }

        .pdf-price-table tfoot tr td {
            background: var(--navy);
            color: white;
            padding: 12px 12px;
            font-weight: 700;
            font-size: 13px;
        }

        .pdf-price-table tfoot .price-col {
            color: var(--orange-mid);
            font-size: 16px;
        }

        .pdf-footer {
            background: var(--navy);
            padding: 10px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pdf-footer-left {
            font-size: 9px;
            color: rgba(255,255,255,0.35);
        }

        .pdf-footer-right {
            font-size: 9px;
            color: rgba(255,255,255,0.35);
            text-align: right;
        }

        @media print {
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            body {
                background: #fff;
                padding: 0;
                margin: 0;
            }

            /* Keep topbar and left-panel visible in printed preview so
               the on-screen layout (navbar + sidebar) is shown in exported PDF.
               Only hide the small preview label when printing. */
            .preview-label {
                display: none !important;
            }

            .layout {
                grid-template-columns: 1fr;
                min-height: 100%;
            }

            .right-panel {
                padding: 0;
                background: white;
            }

            .pdf-sheet {
                width: 210mm;
                height: 297mm;
                box-shadow: none;
                border-radius: 0;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>

<!-- ── NAVBAR ── -->
<div class="topbar">
    <div class="topbar-brand">
        <div class="topbar-logo">P</div>
        Parama Studio <span>/ PDF Preview</span>
    </div>
    <div class="topbar-actions">
        <button class="btn-ghost" onclick="window.print()">← Cetak PDF</button>
        <button class="btn-primary" onclick="window.history.back()">⬅ Kembali</button>
    </div>
</div>

<div class="layout">

    <!-- ── LEFT PANEL ── -->
    <div class="left-panel">

        <div class="panel-title">Informasi Penawaran</div>

        <div class="info-row">
            <div class="info-label">Nomor Dokumen</div>
            <div class="info-value"><?= e($docId) ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">Klien</div>
            <div class="info-value"><?= e($namaKlien) ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">Paket</div>
            <div class="info-value"><?= e($paket) ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">Jumlah Siswa</div>
            <div class="info-value"><?= $siswa ?> Siswa</div>
        </div>

        <div class="info-row">
            <div class="info-label">PIC / Sales</div>
            <div class="info-value"><?= e($addedBy) ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">Tanggal Dokumen</div>
            <div class="info-value"><?= e($tglDoc) ?></div>
        </div>

        <div class="info-row">
            <div class="info-label">Berlaku Hingga</div>
            <div class="info-value"><?= e($tglExp) ?></div>
        </div>

        <div class="divider"></div>

        <div class="panel-title">Summary Harga</div>

        <div class="summary-box">
            <div class="summary-row">
                <span>Harga Per Unit</span>
                <span><?= rp($perBuku) ?></span>
            </div>
            <div class="summary-row">
                <span><?= $siswa ?> unit × <?= rp($perBuku) ?></span>
                <span><?= rp($siswa > 0 ? $siswa * $perBuku : $harga) ?></span>
            </div>
            <?php if ($diskon > 0): ?>
            <div class="summary-row">
                <span>Diskon</span>
                <span>− <?= rp($diskon) ?></span>
            </div>
            <?php endif; ?>
            <div class="summary-total">
                <span class="summary-total-label">TOTAL PENAWARAN</span>
                <span class="summary-total-value"><?= rp($harga) ?></span>
            </div>
        </div>

    </div>

    <!-- ── RIGHT PANEL ── -->
    <div class="right-panel">

        <div class="preview-label">📋 Preview PDF — Siap Unduh atau Cetak</div>

        <!-- ── PDF SHEET ── -->
        <div class="pdf-sheet">

            <!-- PDF HEADER -->
            <div class="pdf-header">
                <div class="pdf-brand">
                    <div class="pdf-brand-name">Parama Studio</div>
                    <div class="pdf-brand-tagline">Yearbook &amp; Graduation Agency</div>
                    <div class="pdf-brand-contact">
                        studioparama.com · +62 822 9400 8994<br>
                        Tangerang Selatan
                    </div>
                </div>
                <div class="pdf-doc-info">
                    <div class="pdf-doc-number"><?= e($docId) ?></div>
                    <div class="pdf-doc-date"><?= e($tglDoc) ?></div>
                    <div class="pdf-doc-date" style="margin-top:8px; color: rgba(255,255,255,0.3); text-transform: uppercase; font-size: 9px;">
                        PENAWARAN HARGA
                    </div>
                </div>
            </div>

            <!-- PDF BODY -->
            <div class="pdf-body">

                <!-- CLIENT INFO -->
                <div>
                    <div class="pdf-to-label">Ditujukan Kepada</div>
                    <div class="pdf-client-name"><?= e($namaKlien) ?></div>
                    <div class="pdf-paket-tag"><?= e($paket) ?><?php if ($siswa > 0 && !$isGraduation): ?> — <?= $siswa ?> Siswa<?php endif; ?></div>
                </div>

                <!-- SPESIFIKASI BUKU -->
                <div class="pdf-section">
                    <div class="pdf-section-title">Informasi Penawaran</div>
                    <table class="pdf-spec-table">
                        <tr><td>Tipe Paket</td><td><strong><?= e($paket) ?></strong></td></tr>
                        <?php if (!$isGraduation): ?>
                        <tr><td>Jumlah Siswa/Unit</td><td><strong><?= $siswa ?></strong></td></tr>
                        <?php endif; ?>
                        <tr><td>Harga Per Unit</td><td><?= rp($perBuku) ?></td></tr>
                        <tr><td>Periode Berlaku</td><td><?= e($tglExp) ?></td></tr>
                    </table>
                </div>

                <!-- SERVICE YANG DIDAPAT -->
                <?php if (!empty($services)): ?>
                <div class="pdf-section">
                    <div class="pdf-section-title">Service yang Didapat</div>
                    <table class="pdf-spec-table">
                        <?php foreach ($services as $serviceNama => $serviceDeskripsi): ?>
                        <tr>
                            <td>✓ <?= e($serviceNama) ?></td>
                            <td><?= e($serviceDeskripsi) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>

                <!-- BONUS & FASILITAS -->
                <?php if (!empty($bonus)): ?>
                <div class="pdf-section">
                    <div class="pdf-section-title">Bonus &amp; Fasilitas</div>
                    <ul style="padding-left: 16px; font-size: 11px; color: var(--navy); line-height: 1.8; list-style: disc;">
                        <?php foreach ($bonus as $bonusItem): ?>
                        <li><?= e($bonusItem) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- RINCIAN HARGA -->
                <div class="pdf-section">
                    <div class="pdf-section-title">Rincian Harga</div>
                    <table class="pdf-price-table">
                        <thead>
                            <tr>
                                <td>Deskripsi</td>
                                <td class="price-col">Harga</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= e($paket) ?><?php if ($siswa > 0): ?> (<?= $siswa ?> unit × <?= rp($perBuku) ?>)<?php endif; ?></td>
                                <td class="price-col"><?= rp($hargaDP > 0 ? $hargaDP : $harga) ?></td>
                            </tr>
                            <?php if ($diskon > 0): ?>
                            <tr>
                                <td><strong>Diskon</strong></td>
                                <td class="price-col"><strong>− <?= rp($diskon) ?></strong></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>TOTAL HARGA PENAWARAN</td>
                                <td class="price-col"><?= rp($harga) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- KETERANGAN -->
                <div style="margin-top: 20px; padding: 14px; background: var(--gray-light); border-radius: 8px; border-left: 3px solid var(--orange);">
                    <?php if ($templateTerms === 'A'): ?>
                        <!-- TEMPLATE A: Keterangan + Penutup untuk produk fisik/digital -->
                        <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--orange); margin-bottom: 8px;">Keterangan</div>
                        <ul style="padding-left: 16px; list-style: disc; font-size: 10.5px; color: var(--gray); line-height: 1.6;">
                            <?php if ($isFullService && $siswa > 0): ?>
                            <li>Harga berlaku untuk minimal <?= $siswa ?> pemesan Buku Tahunan.</li>
                            <?php elseif ($isEBook): ?>
                            <li>Harga berlaku untuk output file digital (PDF/Flipbook).</li>
                            <?php elseif ($isEditCetak): ?>
                            <li>Harga berlaku jika klien menyediakan foto berkualitas.</li>
                            <?php elseif ($isDesain): ?>
                            <li>Harga berlaku jika klien menyediakan semua konten dan foto.</li>
                            <?php elseif ($isCetak): ?>
                            <li>Harga berlaku untuk file yang sudah final dan print-ready.</li>
                            <?php else: ?>
                            <li>Harga berlaku sesuai spesifikasi dan kesepakatan.</li>
                            <?php endif; ?>
                            <li>Harga bersifat penawaran dan dapat berubah sesuai kesepakatan lebih lanjut.</li>
                            <li>Penawaran berlaku hingga <?= e($tglExp) ?>.</li>
                        </ul>
                        <p style="font-size: 10.5px; color: var(--gray); line-height: 1.7; margin-top: 8px;">
                            Demikian penawaran yang kami sampaikan. Besar harapan kami untuk dapat berpartisipasi dalam project <?php if ($isFullService) echo 'katalog / buku tahunan'; elseif ($isEBook) echo 'buku tahunan digital'; elseif ($isEditCetak) echo 'buku tahunan'; else echo 'kreatif'; ?> Anda. Hal–hal yang belum termasuk dan diatur di sini akan dibicarakan di kemudian hari apabila penawaran ini disetujui. Atas perhatian dan kerjasamanya kami sampaikan terima kasih.
                        </p>

                    <?php else: ?>
                        <!-- TEMPLATE B: Syarat & Ketentuan bernomor untuk jasa only -->
                        <div style="font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--orange); margin-bottom: 8px;">Syarat &amp; Ketentuan</div>
                        <ol style="padding-left: 16px; list-style: decimal; font-size: 10.5px; color: var(--gray); line-height: 1.6;">
                            <li>Pilihan paket yang telah diambil tidak dapat diubah setelah dimulainya pekerjaan, kecuali ada kesepakatan tertulis.</li>
                            <?php if ($isFotoA || $isFotoB || $isFotoFull): ?>
                            <li>Waktu satu hari maksimal 8 jam kerja untuk produksi foto.</li>
                            <?php elseif ($isVideoDrone || $isVideoMovie): ?>
                            <li>Waktu produksi sesuai dengan durasi dan kompleksitas video yang disepakati.</li>
                            <?php endif; ?>
                            <li>Harga di atas masih bersifat tentative (dapat berubah) sesuai kesepakatan nantinya.</li>
                        </ol>
                        <p style="font-size: 10.5px; color: var(--gray); line-height: 1.7; margin-top: 8px;">
                            Demikian penawaran yang kami sampaikan. Besar harapan kami untuk dapat berpartisipasi dalam project tahunan {{instansi_name}} Anda. Hal–hal yang belum termasuk dan diatur di sini akan dibicarakan di kemudian hari apabila penawaran ini disetujui. Atas perhatian dan kerjasamanya kami sampaikan terima kasih.
                        </p>
                    <?php endif; ?>
                </div>

            </div>

            <!-- PDF FOOTER -->
            <div class="pdf-footer">
                <div class="pdf-footer-left">
                    PT. Parama Kreatif Sukses · Rawa Buntu Utara Blok G1 No.12, Serpong, Tangerang Selatan 15810
                </div>
                <div class="pdf-footer-right">
                    <?= e($docId) ?> · Berlaku s/d <?= e($tglExp) ?>
                </div>
            </div>

        </div><!-- /pdf-sheet -->

    </div>

</div>

</body>
</html>
