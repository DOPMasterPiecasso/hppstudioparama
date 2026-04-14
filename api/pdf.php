<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
require_once __DIR__ . '/../vendor/autoload.php';

$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { http_response_code(400); die('ID penawaran diperlukan'); }

// Get penawaran from DB
$penawarans = $db->getPenawaran();
$p = null;
foreach ($penawarans as $penawaran) {
    if ($penawaran['id'] == $id) {
        $p = $penawaran;
        break;
    }
}
if (!$p) { http_response_code(404); die('Penawaran tidak ditemukan'); }

// Get user name
$users = $db->getUsers();
$userName = 'Parama Studio';
foreach ($users as $user) {
    if ($user['id'] == $p['added_by_id']) {
        $userName = $user['name'];
        break;
    }
}
$p['added_by_name'] = $userName;

// ============================================================
// COLOR PALETTE — Mengikuti template penawaran Parama Studio
// Navy  : 26, 46, 80
// Orange: 212, 95, 38
// Light : 245, 243, 240
// Dark  : 40, 40, 40
// Mid   : 100, 100, 100
// White : 255, 255, 255
// ============================================================

class ParamaPDF extends FPDF {

    // Logo path — isi dengan path logo jika sudah ada
    private $logoPath = '';

    function __construct() {
        parent::__construct('P', 'mm', 'A4');
        // Coba load logo jika ada
        $possiblePaths = [
            __DIR__ . '/../assets/images/logo.png',
            __DIR__ . '/../assets/images/logo.jpg',
            __DIR__ . '/../assets/logo.png',
            __DIR__ . '/../assets/logo.jpg',
        ];
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $this->logoPath = $path;
                break;
            }
        }
    }

    function Header() {
        // ---- HEADER ATAS: Navy background ----
        $this->SetFillColor(26, 46, 80);
        $this->Rect(0, 0, 210, 28, 'F');

        // ---- Accent bar: Orange strip bawah header ----
        $this->SetFillColor(212, 95, 38);
        $this->Rect(0, 28, 210, 4, 'F');

        // Logo atau teks nama perusahaan
        if ($this->logoPath && file_exists($this->logoPath)) {
            // Tampilkan logo jika ada
            $this->Image($this->logoPath, 12, 4, 0, 20);
            // Teks di samping logo
            $this->SetXY(45, 5);
            $this->SetFont('Arial', 'B', 16);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(0, 8, 'PARAMA STUDIO', 0, 1, 'L');
            $this->SetX(45);
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(180, 200, 230);
            $this->Cell(0, 5, 'Photography & Yearbook Production', 0, 1, 'L');
        } else {
            // Placeholder jika logo belum ada
            // Kotak logo placeholder
            $this->SetFillColor(212, 95, 38);
            $this->Rect(10, 5, 18, 18, 'F');
            $this->SetFont('Arial', 'B', 11);
            $this->SetTextColor(255, 255, 255);
            $this->SetXY(10, 9);
            $this->Cell(18, 10, 'PS', 0, 0, 'C');

            // Nama perusahaan
            $this->SetXY(32, 5);
            $this->SetFont('Arial', 'B', 17);
            $this->SetTextColor(255, 255, 255);
            $this->Cell(100, 9, 'PARAMA STUDIO', 0, 1, 'L');
            $this->SetX(32);
            $this->SetFont('Arial', '', 8);
            $this->SetTextColor(180, 200, 230);
            $this->Cell(100, 5, 'Photography & Yearbook Production', 0, 1, 'L');
            $this->SetX(32);
            $this->SetFont('Arial', '', 7);
            $this->SetTextColor(150, 175, 210);
            $this->Cell(100, 4, 'Instagram: @paramastudio  |  WA: 0812-XXXX-XXXX', 0, 1, 'L');
        }

        // Kanan: label dokumen
        $docLabel = 'SURAT PENAWARAN HARGA';
        $this->SetXY(120, 6);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(80, 6, $docLabel, 0, 1, 'R');

        $this->SetX(120);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(180, 200, 230);
        $this->Cell(80, 5, 'No: SP-' . str_pad($GLOBALS['p']['id'] ?? 0, 4, '0', STR_PAD_LEFT) . '/' . date('m/Y'), 0, 1, 'R');
        $this->SetX(120);
        $this->SetFont('Arial', '', 7.5);
        $this->Cell(80, 5, 'Tanggal: ' . date('d F Y'), 0, 1, 'R');

        // Reset posisi setelah header (32mm dari atas)
        $this->SetY(38);
        $this->SetTextColor(40, 40, 40);
    }

    function Footer() {
        $this->SetY(-18);
        // Garis footer
        $this->SetDrawColor(212, 95, 38);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Ln(3);
        $this->SetFont('Arial', 'I', 7.5);
        $this->SetTextColor(130, 130, 130);
        $this->Cell(95, 5, 'Parama Studio — Dokumen ini dibuat secara digital pada ' . date('d/m/Y H:i'), 0, 0, 'L');
        $this->Cell(95, 5, 'Halaman ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }

    // Fungsi helper: baris info 2 kolom
    function InfoRow($label, $value) {
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(48, 6, $label, 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(40, 40, 40);
        $this->Cell(2, 6, ':', 0, 0);
        $this->MultiCell(0, 6, $value, 0, 'L');
    }

    // Fungsi helper: header section
    function SectionTitle($title) {
        $this->Ln(5);
        $this->SetFillColor(26, 46, 80);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 7, '  ' . strtoupper($title), 0, 1, 'L', true);
        $this->SetTextColor(40, 40, 40);
        $this->Ln(3);
    }

    // Fungsi helper: baris tabel harga
    function PriceRow($label, $value, $bold = false, $color = null) {
        if ($color) {
            $this->SetTextColor($color[0], $color[1], $color[2]);
        } else {
            $this->SetTextColor(60, 60, 60);
        }
        if ($bold) {
            $this->SetFont('Arial', 'B', 10);
        } else {
            $this->SetFont('Arial', '', 9.5);
        }
        $this->Cell(120, 7, $label, 0, 0, 'L');
        $this->Cell(0, 7, $value, 0, 1, 'R');
        $this->SetTextColor(40, 40, 40);
    }
}

// ============================================================
// Helper
// ============================================================
function rupiah(int $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function decode($str): string {
    return html_entity_decode(htmlspecialchars_decode($str ?? ''), ENT_QUOTES, 'UTF-8');
}

// Status labels & warna
$statLabels = ['deal' => 'DEAL', 'nego' => 'NEGOSIASI', 'pending' => 'PENDING', 'gagal' => 'TIDAK JADI'];
$statColors = [
    'deal'    => [45, 140, 90],
    'nego'    => [26, 46, 80],
    'pending' => [200, 140, 30],
    'gagal'   => [180, 40, 40],
];
$sc = $statColors[$p['status']] ?? [100, 100, 100];
$statusLabel = $statLabels[$p['status']] ?? strtoupper($p['status']);

// ============================================================
// BUILD PDF
// ============================================================
$pdf = new ParamaPDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 25);
$pdf->SetMargins(10, 10, 10);

// ---- STATUS BADGE ----
$pdf->SetFillColor($sc[0], $sc[1], $sc[2]);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Rect(10, $pdf->GetY(), 190, 7, 'F');
$pdf->SetXY(10, $pdf->GetY());
$pdf->Cell(190, 7, '  STATUS: ' . $statusLabel, 0, 1, 'L');
$pdf->Ln(5);

// ---- SECTION: DATA KLIEN ----
$pdf->SectionTitle('Data Klien');

$pdf->InfoRow('Nama Klien / Sekolah', decode($p['nama_klien'] ?? ''));
$pdf->InfoRow('Paket Layanan', decode($p['paket'] ?? ''));
$pdf->InfoRow('Jumlah Siswa', ($p['jumlah_siswa'] > 0 ? $p['jumlah_siswa'] . ' siswa' : '—'));
$pdf->InfoRow('Tanggal Penawaran', date('d F Y', strtotime($p['created_at'] ?? 'now')));
$pdf->InfoRow('Staf Parama', decode($p['added_by_name'] ?? 'Parama Studio'));

// ---- SECTION: RINCIAN HARGA ----
$pdf->SectionTitle('Rincian Harga');

// Background box harga
$yStart = $pdf->GetY();
$pdf->SetFillColor(247, 246, 243);
$pdf->Rect(10, $yStart, 190, 1, 'F'); // placeholder — diperluas nanti

$hasDiskon = ($p['harga_sebelum_diskon'] > 0 && $p['harga_sebelum_diskon'] != $p['harga']);

if ($hasDiskon) {
    $selisih = (int)$p['harga_sebelum_diskon'] - (int)$p['harga'];
    $pct = round($selisih / $p['harga_sebelum_diskon'] * 100);
    $pdf->PriceRow('Harga Paket (sebelum diskon)', rupiah((int)$p['harga_sebelum_diskon']));
    $pdf->PriceRow('Diskon (' . $pct . '%)', '- ' . rupiah($selisih), false, [180, 40, 40]);
    // Garis pemisah
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
    $pdf->Ln(3);
}

// Total harga — besar dan mencolok
$pdf->SetFillColor(26, 46, 80);
$pdf->Rect(10, $pdf->GetY(), 190, 12, 'F');
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(255, 255, 255);
$yPriceBox = $pdf->GetY();
$pdf->SetXY(10, $yPriceBox);
$pdf->Cell(110, 12, '  TOTAL HARGA', 0, 0, 'L');
$pdf->SetTextColor(212, 95, 38); // orange untuk angka total
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(90, 12, rupiah((int)($p['harga'] ?? 0)) . '  ', 0, 1, 'R');
$pdf->SetTextColor(40, 40, 40);
$pdf->Ln(4);

// ---- CATATAN / NEGOSIASI ----
if (!empty($p['catatan'])) {
    $catatanList = explode(' | ', decode($p['catatan']));
    $pdf->SectionTitle('Catatan & Negosiasi');

    foreach ($catatanList as $item) {
        $item = trim($item);
        if (!$item) continue;
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(60, 60, 60);
        $pdf->SetX(14);
        $pdf->MultiCell(180, 5.5, chr(183) . '  ' . $item, 0, 'L');
    }
    $pdf->Ln(2);
}

// ---- SYARAT & KETENTUAN ----
$pdf->SectionTitle('Syarat & Ketentuan');
$terms = [
    'Penawaran ini berlaku selama 14 (empat belas) hari sejak tanggal diterbitkan.',
    'Pembayaran DP minimal 50% dari total harga untuk konfirmasi proyek.',
    'Pelunasan dilakukan maksimal 7 hari setelah serah terima hasil pekerjaan.',
    'Revisi desain buku tahunan maksimal 3 (tiga) kali sesuai kontrak.',
    'Pembatalan proyek setelah konfirmasi DP dikenakan biaya pembatalan sesuai kebijakan.',
    'Harga dapat berubah jika ada perubahan spesifikasi yang disepakati bersama.',
];
foreach ($terms as $i => $term) {
    $pdf->SetFont('Arial', '', 8.5);
    $pdf->SetTextColor(80, 80, 80);
    $pdf->SetX(14);
    $pdf->MultiCell(180, 5, ($i + 1) . '. ' . $term, 0, 'L');
}

// ---- AREA TANDA TANGAN ----
$pdf->Ln(8);
$pdf->SetDrawColor(212, 95, 38);
$pdf->SetLineWidth(0.4);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->SetLineWidth(0.2);
$pdf->Ln(6);

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(80, 80, 80);

// Kiri: Hormat kami
$pdf->Cell(95, 5, 'Hormat kami,', 0, 0, 'C');
// Kanan: Disetujui oleh
$pdf->Cell(95, 5, 'Disetujui oleh,', 0, 1, 'C');

$pdf->Ln(18); // ruang tanda tangan

// Nama & jabatan Parama
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(26, 46, 80);
$pdf->Cell(95, 5, 'Parama Studio', 0, 0, 'C');
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(95, 5, '(  ________________________________  )', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(130, 130, 130);
$pdf->Cell(95, 4, decode($p['added_by_name'] ?? 'Tim Parama Studio'), 0, 0, 'C');
$pdf->Cell(95, 4, 'Nama & Stempel Klien', 0, 1, 'C');

$pdf->Ln(3);

// ---- PESAN PENUTUP ----
$pdf->Ln(4);
$pdf->SetFillColor(245, 243, 238);
$pdf->SetDrawColor(212, 95, 38);
$pdf->SetLineWidth(0.4);
$closingY = $pdf->GetY();
$pdf->Rect(10, $closingY, 190, 12, 'FD');
$pdf->SetXY(10, $closingY);
$pdf->SetFont('Arial', 'I', 8.5);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(190, 12, '  Terima kasih atas kepercayaan Anda kepada Parama Studio. Kami siap memberikan layanan terbaik untuk kenangan tak terlupakan.', 0, 1, 'L');

// Output PDF
$filename = 'Penawaran_SP' . str_pad($p['id'], 4, '0', STR_PAD_LEFT) . '_' . preg_replace('/[^a-z0-9]/i', '_', decode($p['nama_klien'] ?? 'klien')) . '.pdf';
$pdf->Output('D', $filename);
