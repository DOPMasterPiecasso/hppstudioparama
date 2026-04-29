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

// Load addon data from JSON
function loadAddonsData() {
    $data = [];
    $addonsFile = __DIR__ . '/../data/addons.json';
    if (file_exists($addonsFile)) {
        $json = file_get_contents($addonsFile);
        $data = array_merge($data, json_decode($json, true) ?? []);
    }
    // Also load graduation data
    $gradFile = __DIR__ . '/../data/graduation.json';
    if (file_exists($gradFile)) {
        $json = file_get_contents($gradFile);
        $grad = json_decode($json, true) ?? [];
        if (isset($grad['addons'])) $data['grad_addons'] = $grad['addons'];
        if (isset($grad['cetak'])) $data['grad_cetak'] = $grad['cetak'];
    }
    return $data;
}

// Get addon price based on student count and tier
function getAddonPrice(array $addon, int $siswa = 1): int {
    // If it has a direct price and no complex type, it's a flat price (like Graduation addons)
    if (isset($addon['price']) && (!isset($addon['type']) || $addon['type'] === 'flat_video' || $addon['type'] === 'flat')) {
        return (int)$addon['price'];
    }
    
    // Tiers logic for Full Service addons
    if (isset($addon['tiers']) && is_array($addon['tiers'])) {
        foreach ($addon['tiers'] as $tier) {
            if ($siswa >= $tier[0] && $siswa <= $tier[1]) {
                return (int)$tier[2];
            }
        }
        // Fallback to last tier if outside range
        if (!empty($addon['tiers'])) {
            return (int)end($addon['tiers'])[2];
        }
    }
    
    return (int)($addon['price'] ?? 0);
}

// Find addon by ID or name across all categories
function findAddonInCategories(array $allAddons, string $addonId): ?array {
    foreach ($allAddons as $category => $items) {
        if (is_array($items)) {
            foreach ($items as $addon) {
                if (($addon['id'] ?? '') === $addonId || ($addon['name'] ?? '') === $addonId) {
                    return $addon;
                }
            }
        }
    }
    return null;
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

// Define package types for DB fetch
$dbPkgTypeJasa = 'fullservice';
$dbKategoriJasa = 'all';

if ($isFullService) {
    $dbPkgTypeJasa = 'fullservice';
    $dbKategoriJasa = 'fs-' . $pkgType; // fs-handy, fs-minimal, fs-large
} elseif ($isAlacarte) {
    $dbPkgTypeJasa = 'alacarte';
    if (strpos($paketLower, 'e-book') !== false || strpos($paketLower, 'ebook') !== false) {
        $dbKategoriJasa = 'ebook';
    } elseif (strpos($paketLower, 'edit') !== false && strpos($paketLower, 'cetak') !== false) {
        $dbKategoriJasa = 'editcetak';
    } elseif (strpos($paketLower, 'foto only') !== false) {
        if (strpos($paketLower, 'full day') !== false) $dbKategoriJasa = 'fotofull';
        else $dbKategoriJasa = 'fotohalf';
    } elseif (strpos($paketLower, 'drone') !== false) {
        $dbKategoriJasa = 'videod';
    } elseif (strpos($paketLower, 'docudrama') !== false || strpos($paketLower, 'video') !== false) {
        $dbKategoriJasa = 'videodoc';
    } elseif (strpos($paketLower, 'desain') !== false) {
        $dbKategoriJasa = 'desain';
    } elseif (strpos($paketLower, 'cetak only') !== false) {
        $dbKategoriJasa = 'cetakonly';
    }
} elseif ($isGraduation) {
    $dbPkgTypeJasa = 'graduation';
}

// ── Ambil Spesifikasi Produk dari Database ──────────────────
$masterDataHelper = new MySQLMasterData($pdo);
$spesifikasiList = $masterDataHelper->getMasterSpesifikasi($dbPkgTypeJasa, $dbKategoriJasa);

// Flag untuk menampilkan section spesifikasi (jika ada data di DB)
$showSpecs = !empty($spesifikasiList);

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

// ── Parse catatan untuk bonus/add-on/harga add-on ────────────
$addons = [];
$addonDetails = [];  // Menyimpan addon dengan harganya
$bonusExtra = [];
$diskonInfo = '';
$totalAddonPrice = 0;

// Load addon data
$allAddonsData = loadAddonsData();

if ($catatan) {
    foreach (explode('|', $catatan) as $part) {
        $part = trim($part);
        if (strpos($part, 'bonus:') === 0) {
            $bonusExtra[] = trim(substr($part, 6));
        } elseif (strpos($part, 'diskon ') === 0 || strpos($part, 'cashback ') === 0) {
            $diskonInfo = $part;
        } elseif (stripos($part, 'addons:') === 0) {
            $addonStr = trim(substr($part, 7));
            $addonEntries = explode(',', $addonStr);
            foreach ($addonEntries as $entry) {
                $entry = trim($entry);
                if (!$entry) continue;
                $colonIdx = strrpos($entry, ':');
                $addonId   = $colonIdx !== false ? trim(substr($entry, 0, $colonIdx)) : $entry;
                $extraVal  = $colonIdx !== false ? (int)trim(substr($entry, $colonIdx + 1)) : null;
                
                if (!$addonId) continue;
                $addonData = findAddonInCategories($allAddonsData, $addonId);
                if ($addonData) {
                    $unitPrice = getAddonPrice($addonData, $siswa);
                    $finalPrice = $unitPrice;
                    $calcLabel = "";
                    
                    // Multiplier logic (matches kalkulator.php / app-pages.js)
                    if (isset($addonData['type'])) {
                        if ($addonData['type'] === 'flat') {
                            $finalPrice = $unitPrice * $siswa;
                            $calcLabel = " ($siswa buku × " . rp($unitPrice) . ")";
                        } elseif ($addonData['type'] === 'per_hal') {
                            $hal = $jumlahHalaman ?? 0;
                            $finalPrice = $unitPrice * $hal * $siswa;
                            $calcLabel = " ($siswa buku × $hal hal × " . rp($unitPrice) . ")";
                        } elseif ($addonData['type'] === 'extra_hal') {
                            $extraQty = $extraVal ?? 0;
                            $finalPrice = $unitPrice * $extraQty * $siswa;
                            $calcLabel = " ($siswa buku × $extraQty hal tambahan × " . rp($unitPrice) . ")";
                        }
                    } elseif ($isGraduation && $extraVal > 0) {
                        // Graduation Cetak (qty * price)
                        // Cek apakah ini item cetak (g4r, g8r, etc)
                        $isCetak = false;
                        if (isset($allAddonsData['grad_cetak'])) {
                            foreach ($allAddonsData['grad_cetak'] as $gc) {
                                if ($gc['id'] === $addonId) { $isCetak = true; break; }
                            }
                        }
                        if ($isCetak) {
                            $finalPrice = $unitPrice * $extraVal;
                            $calcLabel = " ($extraVal lbr × " . rp($unitPrice) . ")";
                        }
                    }

                    $addonDetails[] = [
                        'name'  => ($addonData['name'] ?? $addonId) . $calcLabel,
                        'price' => $finalPrice,
                        'id'    => $addonId
                    ];
                    $totalAddonPrice += $finalPrice;
                } elseif ($extraVal > 0) {
                    $addonDetails[] = [
                        'name'  => ucfirst(str_replace('_', ' ', $addonId)),
                        'price' => $extraVal,
                        'id'    => $addonId
                    ];
                    $totalAddonPrice += $extraVal;
                }
            }
        } elseif ($part) {
            $addons[] = $part;
        }
    }
}

// Update harga total dengan addon
$hargaTotalBaru = $harga + $totalAddonPrice;

// ── Jasa Termasuk — dari tabel jasa_termasuk (DB) ────────────
$jasaTermasukList = [];

try {
    $stmtJasa = $pdo->prepare("SELECT label, detail FROM jasa_termasuk WHERE package_type = ? AND (kategori = ? OR kategori = 'all') AND active = 1 ORDER BY display_order ASC, id ASC");
    $stmtJasa->execute([$dbPkgTypeJasa, $dbKategoriJasa]);
    $jasaTermasukList = $stmtJasa->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $jasaTermasukList = [];
}

$syaratKetentuanList = [];
try {
    $stmtSyarat = $pdo->prepare("SELECT label, detail FROM syarat_ketentuan WHERE package_type = ? AND (kategori = ? OR kategori = 'all') AND active = 1 ORDER BY display_order ASC, id ASC");
    $stmtSyarat->execute([$dbPkgTypeJasa, $dbKategoriJasa]);
    $syaratRaw = $stmtSyarat->fetchAll(PDO::FETCH_ASSOC);
    
    // Process placeholders
    foreach ($syaratRaw as $s) {
        $label = str_replace(
            ['{siswa}', '{klien}', '{docId}', '{tglExp}'],
            [$siswa > 0 ? "<strong>$siswa buku</strong>" : "sesuai spesifikasi", "<strong>$namaKlien</strong>", "<strong>$docId</strong>", "<strong>$tglExp</strong>"],
            htmlspecialchars($s['label'] ?? '', ENT_QUOTES, 'UTF-8')
        );
        $detail = str_replace(
            ['{siswa}', '{klien}', '{docId}', '{tglExp}'],
            [$siswa > 0 ? "<strong>$siswa buku</strong>" : "sesuai spesifikasi", "<strong>$namaKlien</strong>", "<strong>$docId</strong>", "<strong>$tglExp</strong>"],
            $s['detail'] ?? ''
        );
        $syaratKetentuanList[] = ['label' => $label, 'detail' => $detail];
    }
} catch (Exception $e) {
    $syaratKetentuanList = [];
}

// ── Teks Penutup PDF — dari tabel settings ─────────────────────
$penutupDefault = 'Demikian penawaran yang kami sampaikan. Besar harapan kami untuk dapat berpartisipasi dalam project Anda. Hal–hal yang belum termasuk dan diatur di sini akan dibicarakan di kemudian hari apabila penawaran ini disetujui. Atas perhatian dan kerjasamanya kami sampaikan terima kasih.';
try {
    $stmtPenutup = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'pdf_penutup' LIMIT 1");
    $stmtPenutup->execute();
    $penutupRow = $stmtPenutup->fetch(PDO::FETCH_ASSOC);
    $penutupText = ($penutupRow && trim($penutupRow['setting_value']) !== '') ? $penutupRow['setting_value'] : $penutupDefault;
} catch (Exception $e) {
    $penutupText = $penutupDefault;
}

// ── Bonus & Fasilitas — dari tabel bonus_fasilitas (DB) ──────
$dbPkgType = 'fullservice'; // default
$dbKategori = 'all';

if ($isGraduation) {
    $dbPkgType = 'graduation';
    try {
        $stmtGrad = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'grad_packages'");
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
    } catch (Exception $e) {
        // Jika tabel settings tidak ada, gunakan default
        $dbKategori = 'all';
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
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">
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

        /* ══════════════ RESET & BASE ══════════════ */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* Screen styling */
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
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background 0.15s;
        }

        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.15);
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
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 6px;
        }

        .summary-row span:last-child {
            color: white;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            padding-top: 10px;
            margin-top: 4px;
        }

        .summary-total-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
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

        /* ══════════════ PDF SHEET ══════════════ */
        .pdf-sheet {
            width: 680px;
            background: white;
            border-radius: 4px;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.18);
            overflow: hidden;
            font-family: 'DM Sans', sans-serif;
            display: flex;
            flex-direction: column;
        }

        /* ── PDF HEADER ── */
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
            color: rgba(255, 255, 255, 0.5);
            margin-top: 2px;
            letter-spacing: 0.05em;
        }

        .pdf-brand-contact {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.4);
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
            color: rgba(255, 255, 255, 0.45);
            margin-top: 4px;
        }

        /* ── PDF BODY ── */
        .pdf-body {
            padding: 28px 36px;
            flex: 1;
        }

        /* ── PDF CLIENT SECTION ── */
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

        /* ── PDF SECTIONS ── */
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

        /* ── SPEC TABLE ── */
        .pdf-spec-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
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

        /* ── SERVICE GRID ── */
        .pdf-service-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px;
        }

        .pdf-service-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 12px;
            color: var(--navy-mid);
            padding: 6px 10px;
            background: var(--gray-light);
            border-radius: 6px;
        }

        .pdf-service-row .check {
            color: var(--orange);
            font-weight: 700;
            font-size: 12px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .pdf-service-name {
            font-weight: 500;
            font-size: 11px;
        }

        .pdf-service-detail {
            font-size: 10px;
            color: var(--gray);
            margin-top: 1px;
        }

        /* ── BONUS LIST ── */
        .pdf-bonus-list {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .pdf-bonus-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11.5px;
            color: var(--navy-mid);
        }

        .pdf-bonus-item::before {
            content: '✓';
            color: var(--orange);
            font-weight: 700;
            font-size: 12px;
            flex-shrink: 0;
        }

        /* ── PRICE TABLE ── */
        .pdf-price-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 4px;
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

        /* ── TERMS ── */
        .pdf-terms {
            margin-top: 20px;
            padding: 14px;
            background: var(--gray-light);
            border-radius: 8px;
            border-left: 3px solid var(--orange);
        }

        .pdf-terms-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--orange);
            margin-bottom: 8px;
        }

        .pdf-terms ol,
        .pdf-terms ul {
            padding-left: 16px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .pdf-terms li {
            font-size: 10.5px;
            color: var(--gray);
            line-height: 1.5;
        }

        .pdf-terms p {
            font-size: 10.5px;
            color: var(--gray);
            line-height: 1.7;
            margin-top: 8px;
        }

        /* ── TTD ── */
        .pdf-ttd {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .pdf-ttd-block {
            font-size: 11px;
            color: var(--gray);
        }

        .pdf-ttd-name {
            font-weight: 700;
            color: var(--navy);
            font-size: 12px;
            margin-top: 32px;
        }

        .pdf-ttd-jabatan {
            font-size: 10px;
            color: var(--gray);
        }

        .pdf-ttd-line {
            border-top: 1px solid var(--navy);
            margin-top: 2px;
        }

        .pdf-ttd-placeholder {
            margin-top: 48px;
            border-top: 1px solid var(--navy);
            padding-top: 4px;
            font-size: 10px;
            color: var(--gray);
        }

        /* ── PDF FOOTER ── */
        .pdf-footer {
            background: var(--navy);
            padding: 10px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pdf-footer-left {
            font-size: 9px;
            color: rgba(255, 255, 255, 0.35);
        }

        .pdf-footer-right {
            font-size: 9px;
            color: rgba(255, 255, 255, 0.35);
            text-align: right;
        }

        /* ══════════════ PRINT STYLES ══════════════ */
        @media print {

            /* Paksa browser cetak background color */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            @page {
                size: auto; /* Dinamis sesuai konten */
                margin: 0;
            }

            body {
                background: #fff;
                padding: 0;
                margin: 0;
                display: block !important;
                height: auto !important;
            }

            .pdf-sheet {
                width: 210mm !important;
                height: auto !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                display: block !important; /* Gunakan block agar tinggi fleksibel */
            }

            .topbar,
            .left-panel,
            .preview-label {
                display: none !important;
            }

            .layout {
                display: block;
            }

            .right-panel {
                padding: 0;
                background: transparent;
                display: block;
            }

            /* Pastikan header & footer navy tercetak */
            .pdf-header {
                background: var(--navy) !important;
            }

            .pdf-footer {
                background: var(--navy) !important;
            }

            /* Pastikan tabel total navy tercetak */
            .pdf-price-table tfoot tr td {
                background: var(--navy) !important;
            }

            /* Pastikan background tabel tercetak */
            .pdf-spec-table tr:nth-child(odd) td {
                background: var(--gray-light) !important;
            }

            .pdf-service-row {
                background: var(--gray-light) !important;
            }

            .pdf-body {
                display: block !important;
                height: auto !important;
            }

            a {
                text-decoration: none;
                color: inherit;
            }
        }
        .pdf-terms li p, 
        .pdf-service-name p,
        .pdf-bonus-item p { 
            margin: 0; 
            padding: 0; 
            display: inline; 
        }
        .pdf-terms li div p,
        .pdf-service-row div p {
            display: block;
            margin-bottom: 2px;
        }
    </style>
</head>

<body>

    <!-- ── NAVBAR ── -->
    <div class="topbar">
        <div class="topbar-brand">
            <div class="topbar-logo">P</div>
            Parama Studio <span>/ PDF Review</span>
        </div>
        <div class="topbar-actions">
            <button class="btn-ghost"
                onclick="if(window.history.length > 1) { history.back(); } else { window.close(); }">← Kembali</button>
            <button class="btn-primary" onclick="window.print()">⬇ Download PDF</button>
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

            <!-- ══════════════ PDF SHEET ══════════════ -->
            <div class="pdf-sheet">

                <!-- ── PDF HEADER ── -->
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
                        <div class="pdf-doc-date"
                            style="margin-top:8px; color: rgba(255,255,255,0.3); text-transform: uppercase; font-size: 9px;">
                            PENAWARAN HARGA
                        </div>
                    </div>
                </div>

                <!-- ── PDF BODY ── -->
                <div class="pdf-body">

                    <!-- CLIENT INFO -->
                    <div>
                        <div class="pdf-to-label">Ditujukan Kepada</div>
                        <div class="pdf-client-name"><?= e($namaKlien) ?></div>
                        <div class="pdf-paket-tag"><?= e($paket) ?><?php if ($siswa > 0 && !$isGraduation): ?> — <?= $siswa ?>
                            Siswa<?php endif; ?></div>
                    </div>

                    <!-- SPESIFIKASI BUKU (Dinamis dari Master Data) -->
                    <?php if ($showSpecs): ?>
                    <div class="pdf-section">
                        <div class="pdf-section-title">Spesifikasi Produk</div>
                        <table class="pdf-spec-table">
                            <?php if (!$isGraduation): ?>
                            <tr>
                                <td>Jumlah Pesanan</td>
                                <td><strong><?= $siswa > 0 ? $siswa . ($isFullService ? ' Buku' : '') : '—' ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($jumlahHalaman !== null): ?>
                            <tr>
                                <td>Jumlah Halaman</td>
                                <td><strong><?= $jumlahHalaman ?> Halaman</strong></td>
                            </tr>
                            <?php endif; ?>
                            <?php foreach ($spesifikasiList as $spec): ?>
                            <tr>
                                <td><?= e($spec['label']) ?></td>
                                <td><?= e($spec['value']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>
                    <?php endif; ?>

                    <!-- JASA TERMASUK (jika applicable) -->
                    <?php if (!empty($jasaTermasukList)): ?>
                    <div class="pdf-section">
                        <div class="pdf-section-title">Jasa Termasuk</div>
                        <div class="pdf-service-grid">
                            <?php foreach ($jasaTermasukList as $item): ?>
                            <div class="pdf-service-row">
                                <div class="check">✓</div>
                                <div>
                                    <div class="pdf-service-name"><?= e($item['label']) ?></div>
                                    <?php if (!empty($item['detail'])): ?>
                                    <div style="font-size: 10px; color: var(--gray); margin-top: 2px; line-height: 1.4;"><?= e($item['detail']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- BONUS & FASILITAS -->
                    <?php if (!empty($bonusStandar) || !empty($bonusExtra)): ?>
                    <div class="pdf-section">
                        <div class="pdf-section-title">Bonus &amp; Fasilitas</div>
                        <div class="pdf-bonus-list">
                            <?php foreach ($bonusStandar as $bs): ?>
                            <div class="pdf-bonus-item"><?= e($bs['label']) ?><?php if ($bs['detail']): ?>:
                                <?= e($bs['detail']) ?><?php endif; ?></div>
                            <?php endforeach; ?>
                            <?php foreach ($bonusExtra as $b): ?>
                            <div class="pdf-bonus-item"><?= e($b) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ADD-ON -->
                    <?php if (!empty($addons)): ?>
                    <div class="pdf-section">
                        <div class="pdf-section-title">Add-on</div>
                        <div class="pdf-bonus-list">
                            <?php foreach ($addons as $addon): ?>
                            <div class="pdf-bonus-item"><?= e($addon) ?></div>
                            <?php endforeach; ?>
                        </div>
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
                                    <td>
                                        <?php if ($isGraduation): ?>
                                        Harga Paket Graduation
                                        <?php elseif ($isAlacarte): ?>
                                        Harga Layanan
                                        <?php else: ?>
                                        Harga Buku Tahunan
                                        <?php if ($siswa > 0): ?>
                                        (<?= $siswa ?> buku × <?= rp($perBuku) ?>)
                                        <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="price-col"><?= rp($harga) ?></td>
                                </tr>
                                <?php foreach ($addonDetails as $addon): ?>
                                <tr>
                                    <td>
                                        <?= e(ucfirst($addon['name'])) ?>
                                    </td>
                                    <td class="price-col">
                                        <?= rp((int)$addon['price']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if ($hargaDP > 0 && $hargaDP !== $harga): ?>
                                <tr>
                                    <td>Diskon</td>
                                    <td class="price-col">− <?= rp($hargaDP - $harga) ?></td>
                                </tr>
                                <?php endif; ?>

                                
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>TOTAL HARGA PENAWARAN</td>
                                    <td class="price-col"><?= rp($hargaTotalBaru) ?></td>
                                </tr>
                            </tfoot>
                        </table>

                    </div>

                    <!-- KETERANGAN -->
                    <div class="pdf-terms">
                        <div class="pdf-terms-title">Keterangan</div>
                        <ul style="padding-left: 16px; display: flex; flex-direction: column; gap: 5px; list-style: disc;">
                            <?php foreach ($syaratKetentuanList as $item): ?>
                            <li style="font-size: 10.5px; color: var(--gray); line-height: 1.6;">
                                <?= $item['label'] ?>
                                <?php if (!empty($item['detail'])): ?>
                                <div style="font-size: 9.5px; color: #888; margin-top: 2px; line-height: 1.4;"><?= $item['detail'] ?></div>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <p style="font-size: 10.5px; color: var(--gray); line-height: 1.7; margin-top: 12px;">
                            <?= htmlspecialchars($penutupText, ENT_QUOTES, 'UTF-8') ?>
                        </p>
                    </div>

                    <!-- TANDA TANGAN -->
                    <div class="pdf-ttd">
                        <div class="pdf-ttd-block">
                            Hormat kami,
                            <div class="pdf-ttd-name"><?= e($addedBy) ?></div>
                            <div class="pdf-ttd-line"></div>
                            <div class="pdf-ttd-jabatan">Parama Studio</div>
                        </div>
                        <div class="pdf-ttd-block">
                            Disetujui oleh,
                            <div class="pdf-ttd-placeholder">(________________________)</div>
                        </div>
                    </div>

                </div><!-- /pdf-body -->

                <!-- ── PDF FOOTER ── -->
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