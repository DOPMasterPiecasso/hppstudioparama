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
// COLOR PALETTE — Mengikuti template CSS
// Orange  : #c85b2a (212, 95, 42)
// Navy    : #1c2e3d (28, 46, 61)
// Beige   : #f7f5f0 (247, 245, 240)
// Green   : #2d7a4a (45, 122, 74), #e8f5ed (232, 245, 237)
// Brown   : #9c9890 (156, 152, 144)
// Dark brn: #5c5750 (92, 87, 80)
// Light b : #9db8c8 (157, 184, 200)
// ============================================================

class ParamaPDF extends FPDF {

    private $logoPath = '';
    private $pdfId = '';

    function __construct() {
        parent::__construct('P', 'mm', 'A4');
        $logoPath = __DIR__ . '/../assets/logopdf/logo.png';
        if (file_exists($logoPath)) {
            $this->logoPath = $logoPath;
        }
        $this->pdfId = 'PS-' . date('Ymd') . '-' . str_pad($GLOBALS['p']['id'] ?? 0, 3, '0', STR_PAD_LEFT);
    }

    function Header() {
        // Navy header background (no left bar)
        $this->SetFillColor(28, 46, 61);
        $this->Rect(0, 0, 210, 30, 'F');

        // Logo - larger
        if ($this->logoPath && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 8, 4, 0, 22);
        } else {
            $this->SetFillColor(212, 95, 42);
            $this->Rect(8, 4, 20, 22, 'F');
            $this->SetFont('Arial', 'B', 11);
            $this->SetTextColor(255, 255, 255);
            $this->SetXY(10, 12);
            $this->Cell(16, 8, 'PS', 0, 0, 'C');
        }

        // Company name
        $this->SetXY(32, 5);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(90, 6, 'Parama Studio', 0, 1, 'L');

        // Tagline
        $this->SetX(32);
        $this->SetFont('Arial', '', 7.5);
        $this->SetTextColor(157, 184, 200);
        $this->Cell(90, 3.5, 'Yearbook & Graduation Agency', 0, 1, 'L');

        // Contact
        $this->SetX(32);
        $this->SetFont('Arial', '', 6.5);
        $this->Cell(90, 3, 'studioparama.com' . ' - ' . '+62 822 9400 8994' . ' - ' . 'Tangerang Selatan', 0, 1, 'L');

        // Right: Document type
        $this->SetXY(130, 8);
        $this->SetFont('Arial', 'B', 7.5);
        $this->SetTextColor(157, 184, 200);
        $this->Cell(70, 3, 'PENAWARAN HARGA', 0, 1, 'R');

        // Invoice number
        $this->SetX(130);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(212, 95, 42);
        $this->Cell(70, 5, $this->pdfId, 0, 1, 'R');

        // Date
        $this->SetX(130);
        $this->SetFont('Arial', '', 6.5);
        $this->SetTextColor(157, 184, 200);
        $this->Cell(70, 3.5, date('d F Y'), 0, 1, 'R');

        $this->SetY(35);
        $this->SetTextColor(0, 0, 0);
    }

    function Footer() {
        $this->SetY(-14);
        $this->SetFont('Arial', '', 6.5);
        $this->SetTextColor(106, 138, 157);
        $this->Cell(0, 3, 'PT. Parama Kreatif Sukses - Rawa Buntu Utara Blok G1 No.12 - Serpong, Tangerang Selatan 15810', 0, 1, 'L');
        $this->SetX(120);
        $this->Cell(90, 3, $this->pdfId . ' - Berlaku s/d ' . date('d M Y', strtotime('+14 days')), 0, 1, 'R');
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
$pdf->SetAutoPageBreak(true, 22);
$pdf->SetMargins(8, 8, 8);

// ==== SECTION 1: DITUJUKAN KEPADA ====
$pdf->Ln(2);

// Beige background box
$pdf->SetFillColor(247, 245, 240);
$yStart = $pdf->GetY();
$pdf->Rect(8, $yStart, 194, 18, 'F');

// Content
$pdf->SetXY(12, $yStart + 1);
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->SetTextColor(156, 152, 144);
$pdf->Cell(0, 2.5, 'DITUJUKAN KEPADA', 0, 1, 'L');

$pdf->SetX(12);
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetTextColor(28, 46, 61);
$pdf->Cell(0, 5, decode($p['nama_klien'] ?? ''), 0, 1, 'L');

$pdf->SetX(12);
$pdf->SetFont('Arial', '', 7.5);
$pdf->SetTextColor(92, 87, 80);
$packageLabel = decode($p['paket'] ?? '');
$siswaLabel = ($p['jumlah_siswa'] > 0 ? $p['jumlah_siswa'] . ' siswa' : '');
$halamanLabel = (isset($p['halaman']) && $p['halaman'] > 0 ? $p['halaman'] . ' halaman' : '');
$subtitle = implode(' - ', array_filter([$packageLabel, $siswaLabel, $halamanLabel]));
$pdf->MultiCell(0, 3.5, $subtitle, 0, 'L');

$pdf->SetY($yStart + 18.5);
$pdf->Ln(1);

// ==== SECTION 2: SPESIFIKASI BUKU ====
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->SetTextColor(156, 152, 144);
$pdf->Cell(0, 3, 'SPESIFIKASI BUKU', 0, 1, 'L');

// Table header dengan navy
$pdf->SetFillColor(28, 46, 61);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 7.5);
$pdf->Cell(50, 4, 'Keterangan', 0, 0, 'L', true);
$pdf->Cell(150, 4, 'Spesifikasi', 0, 1, 'L', true);

// Table rows (alternating beige)
$altColor = true;
$specs = [
    'Jumlah Pesanan' => ($p['jumlah_siswa'] ?? 0) . ' Buku',
    'Ukuran Buku' => 'Full Service — Sesuai paket',
    'Halaman' => (isset($p['halaman']) ? $p['halaman'] . ' halaman' : '—'),
    'Jenis Kertas' => 'Matte Paper 150gsm',
    'Cover' => 'Hard Cover, AC 190gsm, Laminasi Doff',
    'Packaging' => 'Slongsong',
    'Finishing' => 'Binding Jahit',
    'Jasa Termasuk' => 'Foto • Editing • Desain • Layout • E-Book',
];

foreach ($specs as $label => $value) {
    if ($altColor) {
        $pdf->SetFillColor(247, 245, 240);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }
    $pdf->SetTextColor(60, 60, 60);
    $pdf->SetFont('Arial', '', 7.5);
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->Cell(50, 5, $label, 1, 0, 'L', true);
    $pdf->MultiCell(150, 5, $value, 1, 'L', $altColor);
    $altColor = !$altColor;
}

$pdf->Ln(1);

// ==== SECTION 3: BONUS & FASILITAS ====
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->SetTextColor(156, 152, 144);
$pdf->Cell(0, 3, 'BONUS ' . chr(38) . ' FASILITAS', 0, 1, 'L');

// Green background
$pdf->SetFillColor(232, 245, 237);
$yStart = $pdf->GetY();
$pdf->Rect(8, $yStart, 194, 18, 'F');

$pdf->SetXY(12, $yStart + 0.5);
$pdf->SetFont('Arial', 'B', 7.5);
$pdf->SetTextColor(45, 122, 74);

$bonusItems = [
    'Studio Foto: Free portable studio delivery, Fashion Stylist, Properti sesuai tema',
    'Buku Gratis: 4 pcs Buku Tahunan',
    'Fotografi: Free Photoshoot Graduation (2 Fotografer)',
    'Pengiriman: Gratis pengiriman area Jabodetabek',
];

foreach ($bonusItems as $item) {
    $pdf->SetX(12);
    $pdf->SetFont('Arial', 'B', 7.5);
    $pdf->SetTextColor(45, 122, 74);
    $pdf->Cell(3, 4, chr(10003), 0, 0, 'L');
    $pdf->SetX(16);
    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(26, 50, 40);
    $pdf->MultiCell(178, 4, $item, 0, 'L');
}

$pdf->SetY($yStart + 18.5);
$pdf->Ln(1);

// ==== SECTION 4: RINCIAN HARGA ====
$pdf->SetFont('Arial', 'B', 6.5);
$pdf->SetTextColor(156, 152, 144);
$pdf->Cell(0, 3, 'RINCIAN HARGA', 0, 1, 'L');

// Price table
$pdf->SetFont('Arial', '', 7.5);
$pdf->SetTextColor(60, 60, 60);
$basePrice = (int)($p['harga'] ?? 0);
$perBuku = round($basePrice / max(1, ($p['jumlah_siswa'] ?? 1)));

// Row 1: Base price
$pdf->SetFillColor(255, 255, 255);
$pdf->SetX(8);
$pdf->Cell(132, 5, 'Harga Paket (' . ($p['jumlah_siswa'] ?? 0) . ' buku × Rp ' . number_format($perBuku, 0, ',', '.') . ')', 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(60, 5, rupiah($basePrice), 1, 1, 'R');

// Row 2: Total box (navy + white text)
$pdf->SetFillColor(28, 46, 61);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 7.5);
$pdf->SetX(8);
$pdf->Cell(132, 6, 'TOTAL HARGA PENAWARAN', 1, 0, 'L', true);
$pdf->SetTextColor(212, 95, 42);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 6, rupiah($basePrice), 1, 1, 'R', true);

$pdf->Ln(1);

// ==== KETENTUAN / NOTES ====
// Beige background
$pdf->SetFillColor(240, 237, 230);
$yStart = $pdf->GetY();
$pdf->Rect(8, $yStart, 194, 8, 'F');

$pdf->SetXY(12, $yStart + 0.5);
$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(92, 87, 80);
$pdf->MultiCell(186, 3.5, 'Harga berlaku untuk minimal ' . ($p['jumlah_siswa'] ?? 0) . ' pemesanan Buku Tahunan.' . "\n" . 
                            'Harga bersifat penawaran dan dapat berubah sesuai kesepakatan.', 0, 'L');

$pdf->SetY($yStart + 8.5);
$pdf->Ln(2);

// ==== SIGNATURE SECTION ====
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(60, 60, 60);

$pdf->Cell(98, 4, 'Hormat kami,', 0, 0, 'C');
$pdf->Cell(94, 4, 'Disetujui oleh,', 0, 1, 'C');

$pdf->Ln(12);

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetTextColor(28, 46, 61);
$pdf->Cell(98, 3.5, 'Parama Studio', 0, 0, 'C');

$pdf->SetTextColor(60, 60, 60);
$pdf->SetFont('Arial', '', 7.5);
$pdf->Cell(94, 3.5, '(  _______________________  )', 0, 1, 'C');

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(156, 152, 144);
$pdf->Cell(98, 3, decode($p['added_by_name'] ?? 'Parama Studio'), 0, 0, 'C');
$pdf->Cell(94, 3, 'Nama & Stempel Klien', 0, 1, 'C');

// Output PDF
$filename = 'Penawaran_' . preg_replace('/[^a-z0-9]/i', '_', decode($p['nama_klien'] ?? 'klien')) . '.pdf';
$pdf->Output('D', $filename);
