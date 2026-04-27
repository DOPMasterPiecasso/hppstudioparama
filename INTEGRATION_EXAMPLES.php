<?php
/**
 * INTEGRATION GUIDE — Cara Mengintegrasikan pdf-preview.php ke Aplikasi
 * 
 * File ini berisi contoh-contoh kode untuk mengintegrasikan fitur preview PDF
 * ke halaman penawaran yang sudah ada.
 */

/**
 * ============================================
 * 1. LINK DARI DAFTAR PENAWARAN
 * ============================================
 * 
 * Di halaman yang menampilkan list penawaran, tambahkan tombol:
 * 
 */
?>

<!-- CONTOH 1: Button Preview di Tabel Penawaran -->
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Klien</th>
            <th>Paket</th>
            <th>Harga</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($penawaranList as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= $p['nama_klien'] ?></td>
            <td><?= $p['paket'] ?></td>
            <td><?= number_format($p['harga'], 0, ',', '.') ?></td>
            <td>
                <!-- LINK KE PREVIEW -->
                <a href="/api/pdf-preview.php?id=<?= $p['id'] ?>" 
                   target="_blank" 
                   class="btn btn-info">
                   👁 Preview
                </a>
                
                <!-- ATAU LINK KE PDF PRINT -->
                <a href="/api/pdf.php?id=<?= $p['id'] ?>" 
                   target="_blank" 
                   class="btn btn-primary">
                   📄 PDF
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
/**
 * ============================================
 * 2. MODAL/LIGHTBOX PREVIEW
 * ============================================
 * 
 * Tampilkan preview dalam modal popup:
 */
?>

<!-- HTML -->
<div class="modal fade" id="pdfPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview PDF Penawaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <iframe id="pdfPreviewFrame" 
                        style="width:100%; height:600px; border:none;"
                        src="">
                </iframe>
            </div>
            <div class="modal-footer">
                <a href="#" id="pdfDownloadBtn" class="btn btn-primary" target="_blank">
                    ⬇ Download PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function openPdfPreview(id) {
    const previewUrl = `/api/pdf-preview.php?id=${id}`;
    const downloadUrl = `/api/pdf.php?id=${id}`;
    
    document.getElementById('pdfPreviewFrame').src = previewUrl;
    document.getElementById('pdfDownloadBtn').href = downloadUrl;
    
    const modal = new bootstrap.Modal(document.getElementById('pdfPreviewModal'));
    modal.show();
}
</script>

<!-- Tombol untuk trigger -->
<button class="btn btn-info" onclick="openPdfPreview(<?= $penawaran['id'] ?>)">
    👁 Preview PDF
</button>

<?php
/**
 * ============================================
 * 3. FULL PAGE NAVIGATION
 * ============================================
 * 
 * Jika ingin full page dengan nav ke penawaran lain:
 */
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-6">
            <h4>Penawaran yang Tersedia</h4>
            <div class="list-group">
                <?php foreach ($penawaranList as $p): ?>
                <a href="/api/pdf-preview.php?id=<?= $p['id'] ?>" 
                   class="list-group-item list-group-item-action <?= $p['id'] == $selectedId ? 'active' : '' ?>">
                    <div class="d-flex justify-content-between">
                        <strong><?= htmlspecialchars($p['nama_klien']) ?></strong>
                        <span class="badge bg-primary"><?= htmlspecialchars($p['paket']) ?></span>
                    </div>
                    <small class="text-muted"><?= date('d M Y', strtotime($p['created_at'])) ?></small>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="col-md-6">
            <iframe src="/api/pdf-preview.php?id=<?= $selectedId ?>" 
                    style="width:100%; height:800px; border:1px solid #ddd;">
            </iframe>
        </div>
    </div>
</div>

<?php
/**
 * ============================================
 * 4. ADD BUTTON KE FORM PENAWARAN
 * ============================================
 * 
 * Di halaman form edit penawaran, tambahkan preview button:
 */
?>

<!-- Form Penawaran -->
<form method="POST" action="/penawaran/save">
    <div class="form-group">
        <label for="nama_klien">Nama Klien</label>
        <input type="text" class="form-control" id="nama_klien" name="nama_klien" required>
    </div>
    
    <div class="form-group">
        <label for="paket">Paket</label>
        <select class="form-control" id="paket" name="paket" required>
            <option value="">Pilih Paket...</option>
            <option value="Full Service">Full Service</option>
            <option value="E-Book Only">E-Book Only</option>
            <option value="Edit, Desain & Cetak">Edit, Desain & Cetak</option>
            <option value="Foto Only (½ Hari)">Foto Only (½ Hari)</option>
            <option value="Full Day">Full Day (8 Jam)</option>
            <option value="Video Drone">Video Drone</option>
            <option value="Short Movie">Short Movie</option>
            <option value="Desain Only">Desain Only</option>
            <option value="Cetak Only">Cetak Only</option>
        </select>
    </div>
    
    <div class="form-group">
        <label for="jumlah_siswa">Jumlah Siswa</label>
        <input type="number" class="form-control" id="jumlah_siswa" name="jumlah_siswa" required>
    </div>
    
    <div class="form-group">
        <label for="harga">Harga</label>
        <input type="number" class="form-control" id="harga" name="harga" required>
    </div>
    
    <!-- BUTTONS -->
    <div class="form-group">
        <button type="submit" class="btn btn-primary">💾 Simpan Penawaran</button>
        
        <!-- LINK PREVIEW - hanya bisa diakses jika sudah tersimpan di DB -->
        <?php if (!empty($penawaran['id'])): ?>
        <a href="/api/pdf-preview.php?id=<?= $penawaran['id'] ?>" 
           target="_blank" 
           class="btn btn-info">
           👁 Preview PDF
        </a>
        <a href="/api/pdf.php?id=<?= $penawaran['id'] ?>" 
           target="_blank" 
           class="btn btn-secondary">
           📄 Download PDF
        </a>
        <?php else: ?>
        <button type="button" class="btn btn-info" disabled title="Simpan dulu untuk preview">
           👁 Preview (Simpan dulu)
        </button>
        <?php endif; ?>
    </div>
</form>

<?php
/**
 * ============================================
 * 5. NAVIGATION ANTAR PENAWARAN
 * ============================================
 * 
 * Di dalam pdf-preview.php, tambahkan next/prev navigation:
 * (Edit file api/pdf-preview.php)
 */
?>

<!-- Tambahkan di NAVBAR pdf-preview.php -->
<div class="topbar">
    <div class="topbar-brand">
        <div class="topbar-logo">P</div>
        Parama Studio <span>/ PDF Preview</span>
        
        <!-- Navigation -->
        <select class="form-control form-control-sm" 
                style="max-width: 300px; margin-left: 30px;"
                onchange="if(this.value) window.location.href = '/api/pdf-preview.php?id=' + this.value">
            <option value="">Pilih penawaran lain...</option>
            <?php 
            $allPenawarans = $pdo->query("SELECT id, nama_klien, paket FROM penawaran ORDER BY id DESC LIMIT 10");
            foreach ($allPenawarans as $p):
                $selected = ($p['id'] == $id) ? 'selected' : '';
            ?>
            <option value="<?= $p['id'] ?>" <?= $selected ?>>
                PS-<?= $p['id'] ?> • <?= $p['nama_klien'] ?> • <?= $p['paket'] ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="topbar-actions">
        <button class="btn-ghost" onclick="window.print()">🖨 Cetak PDF</button>
        <button class="btn-primary" onclick="window.history.back()">⬅ Kembali</button>
    </div>
</div>

<?php
/**
 * ============================================
 * 6. EMAIL/SHARE LINKS
 * ============================================
 * 
 * Kirim link preview ke email klien:
 */
?>

<?php
// Di backend setelah membuat penawaran baru:
function sendPdfPreviewEmail($penawaran_id, $email_klien) {
    $previewUrl = "https://domain.com/api/pdf-preview.php?id=" . $penawaran_id;
    $downloadUrl = "https://domain.com/api/pdf.php?id=" . $penawaran_id;
    
    $subject = "Penawaran Parama Studio - Preview PDF";
    $body = "
        <h2>Penawaran Baru Tersedia</h2>
        <p>Penawaran Anda sudah siap untuk direview.</p>
        
        <p>
            <a href='$previewUrl' target='_blank' class='btn btn-primary'>
                👁 Lihat Preview PDF
            </a>
        </p>
        
        <p>
            <a href='$downloadUrl' target='_blank'>
                📄 Atau download PDF langsung
            </a>
        </p>
        
        <p>Terima kasih,<br>Parama Studio</p>
    ";
    
    // Send email using mail() atau PHPMailer
    // mail($email_klien, $subject, $body, "Content-Type: text/html");
}
?>

<?php
/**
 * ============================================
 * 7. PERMISSION CHECKING
 * ============================================
 * 
 * Pastikan ada authentication di pdf-preview.php:
 */
?>

<?php // Di awal pdf-preview.php
session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Unauthorized: Silakan login dulu');
}

// Check permission - hanya bisa lihat penawaran milik sendiri atau dari tim
$user_id = $_SESSION['user_id'];
$penawaran_id = (int)($_GET['id'] ?? 0);

// Query dengan permission check
$stmt = $pdo->prepare("
    SELECT p.* FROM penawaran p
    JOIN users u ON p.added_by = u.id
    WHERE p.id = ? AND (p.added_by = ? OR u.team_id = ?)
");
$stmt->execute([$penawaran_id, $user_id, $_SESSION['team_id']]);
$penawaran = $stmt->fetch();

if (!$penawaran) {
    http_response_code(403);
    die('Forbidden: Anda tidak memiliki akses ke penawaran ini');
}
?>

<?php
/**
 * ============================================
 * 8. USAGE EXAMPLE — FULL FLOW
 * ============================================
 * 
 * Contoh lengkap from create to preview to download:
 */
?>

<!-- STEP 1: Form Create Penawaran -->
<form method="POST" action="/penawaran/store">
    <input type="text" name="nama_klien" placeholder="Nama Klien" required>
    <select name="paket" required>
        <option value="">Pilih Paket</option>
        <option value="Full Service">Full Service</option>
        <!-- ... dst -->
    </select>
    <input type="number" name="jumlah_siswa" placeholder="Jumlah Siswa" required>
    <input type="number" name="harga" placeholder="Harga" required>
    <button type="submit">Buat Penawaran</button>
</form>

<!-- STEP 2: Backend save & redirect -->
<?php
if ($_POST) {
    // Save ke database
    $stmt = $pdo->prepare("INSERT INTO penawaran (nama_klien, paket, jumlah_siswa, harga, added_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $result = $stmt->execute([$_POST['nama_klien'], $_POST['paket'], $_POST['jumlah_siswa'], $_POST['harga'], $_SESSION['user_id']]);
    
    if ($result) {
        $newId = $pdo->lastInsertId();
        // Redirect ke preview
        header("Location: /api/pdf-preview.php?id=$newId");
        exit;
    }
}
?>

<!-- STEP 3: User lihat preview, review, cetak/download -->
<!-- (Already handled di pdf-preview.php) -->

<?php
/**
 * ============================================
 * SUMMARY
 * ============================================
 * 
 * Pilih salah satu cara integrasi di atas:
 * 
 * 1. Simple: Link langsung dari list penawaran
 * 2. Modal: Preview dalam popup modal
 * 3. Split View: List + preview side-by-side
 * 4. Form Integration: Add button di form
 * 5. Email: Share preview link via email
 * 6. Full Flow: Create → Auto Preview → Manage
 * 
 * Rekomendasi:
 * - Gunakan Simple atau Modal untuk quick preview
 * - Gunakan Split View untuk management page
 * - Gunakan Full Flow untuk workflow automation
 */
?>
