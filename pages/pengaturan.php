<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
// Pengaturan is manager/admin only
$user = requireRole('admin', 'manager');
$pageTitle = 'Pengaturan Harga — Parama Studio';
$currentPage = 'pengaturan';

// Load master data dari database
try {
    // Koneksi ke database
    $pdo = getMySQLConnection();
    $masterData = new MySQLMasterData($pdo);
    
    // Load semua data dari database
    $overhead = $masterData->getOverhead();
    $pricingFactors = $masterData->getPricingFactors();
    $cetakF = $pricingFactors['cetak'] ?? ['handy' => 1.0, 'minimal' => 0.95, 'large' => 1.15];
    $alcF = $pricingFactors['alacarte'] ?? ['ebook' => 0.72, 'editcetak' => 0.62, 'desain' => 0.22, 'cetakonly' => 0.30];
    
    // Load graduation data dari database
    $graduationData = $masterData->getGraduation();
    $gradPackages = $graduationData['packages'] ?? [];
    $gradAddons = $graduationData['addons'] ?? [];
    $gradCetak = $graduationData['cetak'] ?? [];
    
    // Load addon data dari database
    $addonsData = $masterData->getAddons();
    
    // Load payment terms data dari database
    $paymentTermsData = $masterData->getPaymentTerms();
    $paymentTerms = $paymentTermsData['terms'] ?? [];
    
} catch (Exception $e) {
    // Fallback jika koneksi database gagal — tidak ada data hardcoded
    error_log('pengaturan.php DB error: ' . $e->getMessage());
    $overhead = [];
    $cetakF = ['handy' => 1.0, 'minimal' => 0.95, 'large' => 1.15];
    $alcF = ['ebook' => 0.72, 'editcetak' => 0.62, 'desain' => 0.22, 'cetakonly' => 0.30];
    $gradPackages = [];
    $gradAddons = [];
    $gradCetak = [];
    $addonsData = [];
    $paymentTerms = [];
}

include __DIR__ . '/../includes/header.php';
?>
<body>
<!-- Toast Container -->
<div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:2000;max-width:400px"></div>

<!-- Mobile Navbar Fixed -->
<div class="mobile-navbar">
  <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
  <div class="mobile-navbar-title">Parama Studio</div>
</div>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeMobileMenu()"></div>

<div class="app">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<main class="main">
<!-- PENGATURAN -->
<div class="page active" id="page-pengaturan">
  <div class="ph"><div class="pt">Edit Semua Harga</div><div class="ps">Ubah semua harga &amp; asumsi biaya — berpengaruh ke seluruh dashboard</div></div>
  <div class="card mb16">
    <div class="ct">Overhead &amp; Gaji Tim (Rp/bulan)</div>
    <div class="note mb12">Kelola biaya overhead — edit, tambah item baru, atau hapus. Total overhead akan dihitung otomatis.</div>
    
    <!-- Control Buttons - AT TOP -->
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:16px;padding:12px;background:#f0f8ff;border-radius:4px;border:1px solid var(--border)">
      <button class="btn bp bsm" onclick="saveOH()" style="font-weight:600">💾 Simpan Overhead</button>
      <button class="btn bs bsm" onclick="resetOH()">↺ Reset ke Default</button>
      <span id="ov-status" style="font-size:12px;color:var(--success);display:none;margin-left:auto">✓ Tersimpan ke server</span>
    </div>

    <!-- Total Display -->
    <div style="padding:12px;background:#fff9f0;border-radius:4px;border:1px solid var(--border);margin-bottom:16px">
      <div style="font-size:12px;color:var(--text2)">Total Overhead Bulanan</div>
      <div id="oh-total" style="font-size:20px;font-weight:700;color:var(--accent)">Rp <?= isset($overhead['total']) ? number_format($overhead['total'], 0, ',', '.') : '0' ?></div>
    </div>
    
    <!-- Daftar Overhead Items -->
    <div id="overhead-items" style="margin-bottom:16px">
      <?php foreach ($overhead as $name => $value): ?>
        <?php if (strtolower($name) === 'total') continue; ?>
      <div class="overhead-item" data-name="<?= htmlspecialchars($name) ?>" data-value="<?= htmlspecialchars($value) ?>" style="display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px">
        <div style="font-size:13px;font-weight:500"><?= htmlspecialchars($name) ?></div>
        <div class="oh-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editOHItem('<?= htmlspecialchars($name) ?>')">Rp <?= number_format($value, 0, ',', '.') ?></div>
        <button class="btn bs bsm" onclick="editOHItem('<?= htmlspecialchars($name) ?>')" style="padding:6px 10px;font-size:11px;font-weight:600;background:#2196F3;color:white;border:none;cursor:pointer;border-radius:3px">✏️ Edit</button>
        <button class="btn bs bsm" onclick="deleteOHItem('<?= htmlspecialchars($name) ?>')" style="padding:6px 10px;font-size:11px;font-weight:600;background:#f44336;color:white;border:none;cursor:pointer;border-radius:3px">✕ Hapus</button>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Add New Overhead Item -->
    <div style="padding:12px;background:#f9f9f9;border-radius:4px;border:1px solid var(--border);margin-bottom:16px">
      <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:8px">Tambah Item Baru</div>
      <div style="display:grid;grid-template-columns:1fr 150px 80px;gap:8px;align-items:center">
        <input type="text" id="new-oh-name" placeholder="Nama cost (cth: Staffing, Asuransi)" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <input type="number" id="new-oh-value" placeholder="Nilai Rp" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <button class="btn bsm" style="background:#2a6b8a;color:white;border:none;cursor:pointer" onclick="addOHItem()">+ Tambah</button>
      </div>
    </div>
  </div>
  <!-- ===== BIAYA CETAK — RENJANA OFFSET (CRUD MASTER) ===== -->
  <div class="card mb16" id="cetak-master-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;margin-bottom:8px">
      <div>
        <div class="ct" style="margin-bottom:4px">Biaya Cetak — Renjana Offset</div>
        <div style="font-size:12px;color:var(--text2)">Master harga dasar per buku per range siswa. Edit langsung kalau harga vendor naik.</div>
      </div>
      <button class="btn bp bsm" onclick="showAddRangeModal()" style="flex-shrink:0">+ Tambah Range</button>
    </div>
    <div class="note mb12">Pilih range siswa untuk melihat/edit tabel harga. Klik angka untuk ubah. Perubahan tersimpan otomatis ke master data.</div>

    <!-- Range selector buttons — dirender PHP -->
    <div style="margin-bottom:12px">
      <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:6px">Pilih Range Siswa</div>
      <div style="display:flex;gap:5px;flex-wrap:wrap" id="cetak-range-btns">
        <?php
        $cetakBaseData = [];
        try {
            $db2 = getDB();
            $cetakBaseData = $db2->getCetakBase();
        } catch (Exception $e) { $cetakBaseData = []; }
        if (empty($cetakBaseData)) {
            $cetakBaseData = [
                ['lo'=>30,'hi'=>50,'label'=>'30–50 siswa'],['lo'=>51,'hi'=>75,'label'=>'51–75 siswa'],
                ['lo'=>76,'hi'=>100,'label'=>'76–100 siswa'],['lo'=>101,'hi'=>125,'label'=>'101–125 siswa'],
                ['lo'=>126,'hi'=>150,'label'=>'126–150 siswa'],['lo'=>151,'hi'=>175,'label'=>'151–175 siswa'],
                ['lo'=>176,'hi'=>200,'label'=>'176–200 siswa'],['lo'=>201,'hi'=>225,'label'=>'201–225 siswa'],
                ['lo'=>226,'hi'=>250,'label'=>'226–250 siswa'],['lo'=>251,'hi'=>275,'label'=>'251–275 siswa'],
                ['lo'=>276,'hi'=>300,'label'=>'276–300 siswa'],['lo'=>301,'hi'=>325,'label'=>'301–325 siswa'],
                ['lo'=>326,'hi'=>350,'label'=>'326–350 siswa'],['lo'=>351,'hi'=>375,'label'=>'351–375 siswa'],
                ['lo'=>376,'hi'=>400,'label'=>'376–400 siswa'],['lo'=>401,'hi'=>425,'label'=>'401–425 siswa'],
                ['lo'=>426,'hi'=>450,'label'=>'426–450 siswa'],['lo'=>451,'hi'=>475,'label'=>'451–475 siswa'],
                ['lo'=>476,'hi'=>500,'label'=>'476–500 siswa'],
            ];
        }
        foreach ($cetakBaseData as $idx => $range): ?>
        <button class="btn <?= $idx===0?'bp':'bs' ?> bsm cetak-range-btn" onclick="setCetakRange(<?= $idx ?>, this)" style="font-size:11px;position:relative">
          <?= htmlspecialchars($range['label']) ?>
          <span class="cetak-del-btn" onclick="deleteCetakRange(event,<?= $idx ?>)" title="Hapus range" style="margin-left:5px;color:var(--danger);font-weight:700;font-size:13px;line-height:1">×</span>
        </button>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Tabel harga per halaman untuk range yang dipilih -->
    <div class="tw" id="cetak-table-wrap" style="min-height:80px"></div>

    <!-- Add halaman baru ke range aktif -->
    <div id="cetak-add-hal-row" style="display:flex;gap:8px;align-items:center;margin-top:10px;flex-wrap:wrap;padding:10px;background:var(--surface2);border-radius:8px;border:1px dashed var(--border2)">
      <div style="font-size:12px;font-weight:600;color:var(--text2)">+ Tambah Tier Halaman:</div>
      <input type="number" id="new-hal-pages" placeholder="Jml halaman" min="1" max="500" style="width:110px;font-size:12px;padding:4px 8px">
      <input type="number" id="new-hal-price" placeholder="Harga/buku (Rp)" min="0" style="width:150px;font-size:12px;padding:4px 8px">
      <button class="btn bp bsm" onclick="addHalamanTier()">Tambah</button>
    </div>

    <!-- Status & Simpan -->
    <div style="display:flex;gap:8px;align-items:center;margin-top:12px;flex-wrap:wrap">
      <button class="btn bp bsm" onclick="saveCetakBase()">💾 Simpan Semua Perubahan</button>
      <button class="btn bs bsm" onclick="resetCetakBase()">↩ Reset ke Default Renjana</button>
      <span id="ov-cetak-status" style="font-size:12px;color:var(--success);display:none">✓ Tersimpan</span>
    </div>

    <!-- Faktor per paket -->
    <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border)">
      <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:8px">Faktor Multiplier per Paket <span style="font-weight:400;color:var(--text3)">(harga efektif = base × faktor)</span></div>
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:9px;margin-bottom:10px">
        <div class="fg"><label class="fl">Handy Book A4+</label><div style="display:flex;align-items:center;gap:5px"><input type="number" id="ov-cetak-handy" value="<?= htmlspecialchars($cetakF['handy'] ?? '1.00') ?>" step="0.01" min="0.5" max="3"><span style="font-size:12px;color:var(--text3)">×</span></div></div>
        <div class="fg"><label class="fl">Minimal Book SQ</label><div style="display:flex;align-items:center;gap:5px"><input type="number" id="ov-cetak-minimal" value="<?= htmlspecialchars($cetakF['minimal'] ?? '0.95') ?>" step="0.01" min="0.5" max="3"><span style="font-size:12px;color:var(--text3)">×</span></div></div>
        <div class="fg"><label class="fl">Large Book B4</label><div style="display:flex;align-items:center;gap:5px"><input type="number" id="ov-cetak-large" value="<?= htmlspecialchars($cetakF['large'] ?? '1.15') ?>" step="0.01" min="0.5" max="3"><span style="font-size:12px;color:var(--text3)">×</span></div></div>
      </div>
      <button class="btn bp bsm" onclick="saveCetak()">💾 Simpan Faktor Paket</button>
    </div>
  </div>

  <!-- Modal: Tambah Range Baru -->
  <div id="modal-add-range" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center">
    <div class="modal-box" style="max-width:420px">
      <div class="modal-title">Tambah Range Siswa Baru</div>
      <div class="form-row">
        <label>Jumlah Siswa Minimum</label>
        <input type="number" id="new-range-lo" placeholder="cth: 501" min="1" max="9999">
      </div>
      <div class="form-row">
        <label>Jumlah Siswa Maximum</label>
        <input type="number" id="new-range-hi" placeholder="cth: 525" min="1" max="9999">
      </div>
      <div class="form-row">
        <label>Label Tampilan <span style="color:var(--text3);font-size:11px">(opsional, otomatis)</span></label>
        <input type="text" id="new-range-label" placeholder="cth: 501–525 siswa">
      </div>
      <div class="note mb12" style="margin-top:4px">Range baru akan dibuat dengan harga default. Edit harga setelah range dibuat.</div>
      <div style="display:flex;gap:8px;margin-top:16px">
        <button class="btn bp" style="flex:1" onclick="confirmAddRange()">Tambah Range</button>
        <button class="btn bs" onclick="closeAddRangeModal()">Batal</button>
      </div>
    </div>
  </div>


  <div class="card mb16">
    <div class="ct">Faktor Harga À La Carte (% dari Full Service)</div>
    <div class="note mb12">Harga à la carte = harga full service × faktor ini.</div>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:9px;margin-bottom:12px">
      <div class="fg"><label class="fl">E-Book Package (%)</label><input type="number" id="ov-ac-ebook" value="<?= htmlspecialchars(round(($alcF['ebook'] ?? 0.72) * 100)) ?>" min="10" max="100"></div>
      <div class="fg"><label class="fl">Edit+Desain+Cetak (%)</label><input type="number" id="ov-ac-editcetak" value="<?= htmlspecialchars(round(($alcF['editcetak'] ?? 0.62) * 100)) ?>" min="10" max="100"></div>
      <div class="fg"><label class="fl">Desain Only (%)</label><input type="number" id="ov-ac-desain" value="<?= htmlspecialchars(round(($alcF['desain'] ?? 0.22) * 100)) ?>" min="10" max="100"></div>
      <div class="fg"><label class="fl">Cetak Only (%)</label><input type="number" id="ov-ac-cetakonly" value="<?= htmlspecialchars(round(($alcF['cetakonly'] ?? 0.30) * 100)) ?>" min="10" max="100"></div>
    </div>
    <button class="btn bp bsm" onclick="saveALC()">Simpan Faktor À La Carte</button>
  </div>

  <!-- ===== ADD-ON SECTION ===== -->
  <div style="margin-bottom:30px">
    <div class="ph"><div class="pt">Manajemen Add-on (Full Service)</div><div class="ps">Kelola harga finishing, kertas, halaman, video, dan packaging — pisah per kategori</div></div>
    
    <!-- FINISHING & BINDING -->
    <div class="card mb16">
      <div class="ph"><div class="pt" style="color:#2c3e50">Finishing &amp; Binding</div><div class="ps">Layanan finishing tambahan — binding, pop up, tunnel, klip, cover bahan</div></div>
      <div class="note mb12">Klik harga untuk edit. Tier: range qty (min-max) dengan harga masing-masing.</div>
      <div id="addon-finishing-items" style="margin-bottom:12px">
        <?php foreach ($addonsData['finishing'] ?? [] as $item): ?>
        <div class="addon-item" data-category="finishing" data-id="<?= htmlspecialchars($item['id']) ?>" style="margin-bottom:10px;padding:12px;background:#fafafa;border-radius:4px;border-left:3px solid #3498db">
          <div style="font-weight:600;font-size:13px;margin-bottom:6px"><?= htmlspecialchars($item['name']) ?></div>
          <div style="font-size:12px;color:var(--text2);margin-bottom:8px">
            <?php if ($item['type'] === 'flat'): ?>
              <span style="display:inline-block;background:#e3f2fd;padding:2px 6px;border-radius:3px">Flat</span>
            <?php else: ?>
              <span style="display:inline-block;background:#f3e5f5;padding:2px 6px;border-radius:3px"><?= htmlspecialchars($item['type']) ?></span>
            <?php endif; ?>
          </div>
          <!-- Tiers -->
          <div style="display:grid;gap:6px">
            <?php if ($item['type'] === 'flat' && isset($item['tiers'])): ?>
              <?php foreach ($item['tiers'] as $tier): ?>
              <div style="display:grid;grid-template-columns:auto auto auto 1fr;gap:8px;align-items:center;padding:6px;background:white;border-radius:3px;font-size:12px">
                <span style="color:var(--text2)"><?= $tier[0] ?></span>
                <span style="color:var(--text2)">–</span>
                <span style="color:var(--text2)"><?= $tier[1] ?></span>
                <div class="addon-price-display" style="text-align:right;color:var(--accent);font-weight:600;cursor:pointer" onclick="editAddonPrice(this)">Rp <?= number_format($tier[2], 0, ',', '.') ?></div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <div style="margin-top:8px;display:flex;gap:6px">
            <button class="btn bs bsm" onclick="editAddonItem('finishing','<?= htmlspecialchars($item['id']) ?>')">✏️ Edit</button>
            <button class="btn bs bsm" style="background:#f44336;color:white;border:none;cursor:pointer;padding:4px 8px;border-radius:3px;font-size:11px" onclick="deleteAddonItem('finishing','<?= htmlspecialchars($item['id']) ?>')">✕ Hapus</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="btn bp bsm" onclick="showAddAddonModal('finishing')">+ Tambah Item</button>
    </div>

    <!-- UPGRADE KERTAS -->
    <div class="card mb16">
      <div class="ph"><div class="pt" style="color:#2c3e50">Upgrade Kertas</div><div class="ps">Pilihan upgrade kertas — Ivory Paper, Laminasi Paper</div></div>
      <div class="note mb12">Per halaman. Tier: range qty (min-max) dengan harga per halaman masing-masing.</div>
      <div id="addon-kertas-items" style="margin-bottom:12px">
        <?php foreach ($addonsData['kertas'] ?? [] as $item): ?>
        <div class="addon-item" data-category="kertas" data-id="<?= htmlspecialchars($item['id']) ?>" style="margin-bottom:10px;padding:12px;background:#fafafa;border-radius:4px;border-left:3px solid #27ae60">
          <div style="font-weight:600;font-size:13px;margin-bottom:6px"><?= htmlspecialchars($item['name']) ?></div>
          <div style="font-size:12px;color:var(--text2);margin-bottom:8px">
            <span style="display:inline-block;background:#e8f5e9;padding:2px 6px;border-radius:3px">Per Halaman</span>
          </div>
          <!-- Tiers -->
          <div style="display:grid;gap:6px">
            <?php if (isset($item['tiers'])): ?>
              <?php foreach ($item['tiers'] as $tier): ?>
              <div style="display:grid;grid-template-columns:auto auto auto 1fr;gap:8px;align-items:center;padding:6px;background:white;border-radius:3px;font-size:12px">
                <span style="color:var(--text2)"><?= $tier[0] ?></span>
                <span style="color:var(--text2)">–</span>
                <span style="color:var(--text2)"><?= $tier[1] ?></span>
                <div class="addon-price-display" style="text-align:right;color:var(--accent);font-weight:600;cursor:pointer" onclick="editAddonPrice(this)">Rp <?= number_format($tier[2], 0, ',', '.') ?></div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <div style="margin-top:8px;display:flex;gap:6px">
            <button class="btn bs bsm" onclick="editAddonItem('kertas','<?= htmlspecialchars($item['id']) ?>')">✏️ Edit</button>
            <button class="btn bs bsm" style="background:#f44336;color:white;border:none;cursor:pointer;padding:4px 8px;border-radius:3px;font-size:11px" onclick="deleteAddonItem('kertas','<?= htmlspecialchars($item['id']) ?>')">✕ Hapus</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="btn bp bsm" onclick="showAddAddonModal('kertas')">+ Tambah Item</button>
    </div>

    <!-- HALAMAN TAMBAHAN -->
    <div class="card mb16">
      <div class="ph"><div class="pt" style="color:#2c3e50">Halaman Tambahan</div><div class="ps">Harga per halaman tambahan — berbeda per tier order</div></div>
      <div class="note mb12">Tier: range qty dengan harga/halaman masing-masing.</div>
      <div id="addon-halaman-items" style="margin-bottom:12px">
        <?php foreach ($addonsData['halaman'] ?? [] as $item): ?>
        <div class="addon-item" data-category="halaman" data-id="<?= htmlspecialchars($item['id']) ?>" style="margin-bottom:10px;padding:12px;background:#fafafa;border-radius:4px;border-left:3px solid #f39c12">
          <div style="font-weight:600;font-size:13px;margin-bottom:6px"><?= htmlspecialchars($item['name']) ?></div>
          <div style="font-size:12px;color:var(--text2);margin-bottom:8px">
            <span style="display:inline-block;background:#fff3e0;padding:2px 6px;border-radius:3px">Extra Halaman</span>
          </div>
          <!-- Tiers -->
          <div style="display:grid;gap:6px">
            <?php if (isset($item['tiers'])): ?>
              <?php foreach ($item['tiers'] as $tier): ?>
              <div style="display:grid;grid-template-columns:auto auto auto 1fr;gap:8px;align-items:center;padding:6px;background:white;border-radius:3px;font-size:12px">
                <span style="color:var(--text2)"><?= $tier[0] ?></span>
                <span style="color:var(--text2)">–</span>
                <span style="color:var(--text2)"><?= $tier[1] ?></span>
                <div class="addon-price-display" style="text-align:right;color:var(--accent);font-weight:600;cursor:pointer" onclick="editAddonPrice(this)">Rp <?= number_format($tier[2], 0, ',', '.') ?></div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <div style="margin-top:8px;display:flex;gap:6px">
            <button class="btn bs bsm" onclick="editAddonItem('halaman','<?= htmlspecialchars($item['id']) ?>')">✏️ Edit</button>
            <button class="btn bs bsm" style="background:#f44336;color:white;border:none;cursor:pointer;padding:4px 8px;border-radius:3px;font-size:11px" onclick="deleteAddonItem('halaman','<?= htmlspecialchars($item['id']) ?>')">✕ Hapus</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="btn bp bsm" onclick="showAddAddonModal('halaman')">+ Tambah Item</button>
    </div>

    <!-- VIDEO -->
    <div class="card mb16">
      <div class="ph"><div class="pt" style="color:#2c3e50">Video</div><div class="ps">Layanan video — Drone, Docudrama (flat price, tidak tier)</div></div>
      <div class="note mb12">Harga flat untuk seluruh project, tidak berdasar tier qty.</div>
      <div id="addon-video-items" style="margin-bottom:12px">
        <?php foreach ($addonsData['video'] ?? [] as $item): ?>
        <div class="addon-item" data-category="video" data-id="<?= htmlspecialchars($item['id']) ?>" style="margin-bottom:10px;padding:12px;background:#fafafa;border-radius:4px;border-left:3px solid #e74c3c">
          <div style="font-weight:600;font-size:13px;margin-bottom:6px"><?= htmlspecialchars($item['name']) ?></div>
          <div style="font-size:12px;color:var(--text2);margin-bottom:8px">
            <span style="display:inline-block;background:#ffebee;padding:2px 6px;border-radius:3px">Flat Video</span>
          </div>
          <div style="padding:6px;background:white;border-radius:3px;font-size:12px">
            <div class="addon-price-display" style="color:var(--accent);font-weight:600;cursor:pointer" onclick="editAddonPrice(this)">Rp <?= number_format($item['price'] ?? 0, 0, ',', '.') ?></div>
          </div>
          <div style="margin-top:8px;display:flex;gap:6px">
            <button class="btn bs bsm" onclick="editAddonItem('video','<?= htmlspecialchars($item['id']) ?>')">✏️ Edit</button>
            <button class="btn bs bsm" style="background:#f44336;color:white;border:none;cursor:pointer;padding:4px 8px;border-radius:3px;font-size:11px" onclick="deleteAddonItem('video','<?= htmlspecialchars($item['id']) ?>')">✕ Hapus</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="btn bp bsm" onclick="showAddAddonModal('video')">+ Tambah Item</button>
    </div>

    <!-- PACKAGING - SLIDE & STANDARD -->
    <div class="card mb16">
      <div class="ph"><div class="pt" style="color:#2c3e50">Packaging — Slide Box &amp; Standard</div><div class="ps">Packaging dengan tier berdasar jumlah order</div></div>
      <div class="note mb12">Tier: 25-50, 51-100, 101-150, 151-200, >200 buku.</div>
      <div id="addon-pkg1-items" style="margin-bottom:12px">
        <?php foreach ($addonsData['pkg1'] ?? [] as $item): ?>
        <div class="addon-item" data-category="pkg1" data-id="<?= htmlspecialchars($item['id']) ?>" style="margin-bottom:10px;padding:12px;background:#fafafa;border-radius:4px;border-left:3px solid #9c27b0">
          <div style="font-weight:600;font-size:13px;margin-bottom:6px"><?= htmlspecialchars($item['name']) ?></div>
          <div style="font-size:12px;color:var(--text2);margin-bottom:8px">
            <span style="display:inline-block;background:#f3e5f5;padding:2px 6px;border-radius:3px">Packaging Standar</span>
          </div>
          <!-- Tiers -->
          <div style="display:grid;gap:6px">
            <?php if (isset($item['tiers'])): ?>
              <?php foreach ($item['tiers'] as $tier): ?>
              <div style="display:grid;grid-template-columns:auto auto auto 1fr;gap:8px;align-items:center;padding:6px;background:white;border-radius:3px;font-size:12px">
                <span style="color:var(--text2)"><?= $tier[0] ?></span>
                <span style="color:var(--text2)">–</span>
                <span style="color:var(--text2)"><?= $tier[1] ?></span>
                <div class="addon-price-display" style="text-align:right;color:var(--accent);font-weight:600;cursor:pointer" onclick="editAddonPrice(this)">Rp <?= number_format($tier[2], 0, ',', '.') ?></div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <div style="margin-top:8px;display:flex;gap:6px">
            <button class="btn bs bsm" onclick="editAddonItem('pkg1','<?= htmlspecialchars($item['id']) ?>')">✏️ Edit</button>
            <button class="btn bs bsm" style="background:#f44336;color:white;border:none;cursor:pointer;padding:4px 8px;border-radius:3px;font-size:11px" onclick="deleteAddonItem('pkg1','<?= htmlspecialchars($item['id']) ?>')">✕ Hapus</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="btn bp bsm" onclick="showAddAddonModal('pkg1')">+ Tambah Item</button>
    </div>

    <!-- PACKAGING - CUSTOM BOX -->
    <div class="card mb16">
      <div class="ph"><div class="pt" style="color:#2c3e50">Packaging — Custom Box</div><div class="ps">Custom printed box dengan tier berdasar jumlah order</div></div>
      <div class="note mb12">Tier: 25-50, 51-100, 101-150, 151-200, >200 buku.</div>
      <div id="addon-pkg2-items" style="margin-bottom:12px">
        <?php foreach ($addonsData['pkg2'] ?? [] as $item): ?>
        <div class="addon-item" data-category="pkg2" data-id="<?= htmlspecialchars($item['id']) ?>" style="margin-bottom:10px;padding:12px;background:#fafafa;border-radius:4px;border-left:3px solid #00bcd4">
          <div style="font-weight:600;font-size:13px;margin-bottom:6px"><?= htmlspecialchars($item['name']) ?></div>
          <div style="font-size:12px;color:var(--text2);margin-bottom:8px">
            <span style="display:inline-block;background:#e0f2f1;padding:2px 6px;border-radius:3px">Custom Box</span>
          </div>
          <!-- Tiers -->
          <div style="display:grid;gap:6px">
            <?php if (isset($item['tiers'])): ?>
              <?php foreach ($item['tiers'] as $tier): ?>
              <div style="display:grid;grid-template-columns:auto auto auto 1fr;gap:8px;align-items:center;padding:6px;background:white;border-radius:3px;font-size:12px">
                <span style="color:var(--text2)"><?= $tier[0] ?></span>
                <span style="color:var(--text2)">–</span>
                <span style="color:var(--text2)"><?= $tier[1] ?></span>
                <div class="addon-price-display" style="text-align:right;color:var(--accent);font-weight:600;cursor:pointer" onclick="editAddonPrice(this)">Rp <?= number_format($tier[2], 0, ',', '.') ?></div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <div style="margin-top:8px;display:flex;gap:6px">
            <button class="btn bs bsm" onclick="editAddonItem('pkg2','<?= htmlspecialchars($item['id']) ?>')">✏️ Edit</button>
            <button class="btn bs bsm" style="background:#f44336;color:white;border:none;cursor:pointer;padding:4px 8px;border-radius:3px;font-size:11px" onclick="deleteAddonItem('pkg2','<?= htmlspecialchars($item['id']) ?>')">✕ Hapus</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <button class="btn bp bsm" onclick="showAddAddonModal('pkg2')">+ Tambah Item</button>
    </div>

    <!-- Save All Addons Button -->
    <div style="display:flex;gap:8px;margin-bottom:20px">
      <button class="btn bp bsm" onclick="saveAllAddons()" style="font-weight:600">💾 Simpan Semua Perubahan Add-on</button>
      <button class="btn bs bsm" onclick="resetAddons()">↩ Reset ke Default</button>
      <span id="addon-status" style="font-size:12px;color:var(--success);display:none">✓ Tersimpan ke database</span>
    </div>
  </div>

  <!-- ===== GRADUATION SECTION ===== -->
  <!-- Card 1: Edit Harga Paket Utama -->
  <div class="card mb16">
    <div class="ph"><div class="pt" style="color:var(--grad)">Graduation Package — Paket Utama</div><div class="ps">Dokumentasi wisuda — foto, video, photobooth &amp; glamation 360°</div></div>
    <div class="note grad mb16">✏️ Klik angka harga mana saja untuk edit langsung. Semua harga juga bisa diubah di menu <b>Edit Semua Harga</b>.</div>

    <div class="note mb12">Kelola paket utama — edit, tambah item baru, atau hapus. Perubahan langsung tersimpan ke database.</div>    
    <!-- Daftar Paket Items -->
    <div id="grad-pkg-items" style="margin-bottom:16px">
      <?php foreach ($gradPackages as $pkg): ?>
      <div class="grad-pkg-item" data-id="<?= htmlspecialchars($pkg['id']) ?>" data-name="<?= htmlspecialchars($pkg['name']) ?>" data-price="<?= htmlspecialchars($pkg['price']) ?>" data-desc="<?= htmlspecialchars($pkg['desc'] ?? '') ?>" style="margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px;border:1px solid var(--border);display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center">
        <div style="font-size:13px;font-weight:500"><?= htmlspecialchars($pkg['name']) ?></div>
        <div class="gp-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editGradPkg('<?= htmlspecialchars($pkg['id']) ?>')">Rp <?= number_format($pkg['price'], 0, ',', '.') ?></div>
        <button class="btn bs bsm btn-edit-price" data-id="<?= htmlspecialchars($pkg['id']) ?>" style="padding:4px 8px;font-size:11px;cursor:pointer">✏️</button>
        <button class="btn bs bsm btn-delete" data-id="<?= htmlspecialchars($pkg['id']) ?>" style="padding:4px 8px;font-size:11px;cursor:pointer">✕</button>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Add New Package Item -->
    <div style="padding:12px;background:#f9f9f9;border-radius:4px;border:1px solid var(--border);margin-bottom:16px">
      <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:8px">Tambah Paket Baru</div>
      <div style="display:grid;grid-template-columns:1fr 150px 80px;gap:8px;margin-bottom:8px;align-items:center">
        <input type="text" id="new-grad-pkg-name" placeholder="Nama paket (cth: Paket Promo, Paket VIP)" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <input type="number" id="new-grad-pkg-price" placeholder="Harga Rp" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <button class="btn bsm" style="background:#2a6b8a;color:white;border:none;cursor:pointer" onclick="addGradPkg()">+ Tambah</button>
      </div>
      <div style="margin-bottom:8px">
        <label style="font-size:11px;font-weight:600;color:var(--text3);display:block;margin-bottom:4px">Deskripsi (opsional)</label>
        <textarea id="new-grad-pkg-desc" placeholder="Deskripsi singkat paket..." style="width:100%;min-height:50px;border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px;font-family:inherit"></textarea>
      </div>
    </div>

    <!-- Control Buttons -->
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <button class="btn bgrad bsm" onclick="saveGrad()">Simpan Harga Paket</button>
      <button class="btn bs bsm" onclick="resetGrad()">Reset ke Default</button>
      <span id="grad-pkg-status" style="font-size:12px;color:var(--success);display:none">✓ Tersimpan</span>
    </div>
  </div>

  <!-- Card 2: Edit Add-on & Cetak Foto -->
  <div class="card mb16">
    <div class="ph"><div class="pt" style="color:var(--grad)">Edit Add-on &amp; Cetak Foto</div><div class="ps">Kelola add-on ekstra dan harga cetak foto satuan</div></div>
    <div class="note mb12">Kelola add-on dan cetak foto — edit harga, tambah item baru, atau hapus.</div>
    
    <!-- Daftar Add-on Items -->
    <div style="margin-bottom:16px">
      <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:8px">Add-on Graduation</div>
      <div id="grad-addon-items" style="margin-bottom:12px">
        <?php foreach ($gradAddons as $addon): ?>
        <div class="grad-addon-item" data-id="<?= htmlspecialchars($addon['id']) ?>" data-name="<?= htmlspecialchars($addon['name']) ?>" data-price="<?= htmlspecialchars($addon['price']) ?>" style="display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px">
          <div style="font-size:13px;font-weight:500"><?= htmlspecialchars($addon['name']) ?></div>
          <div class="ga-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editGradAddon('<?= htmlspecialchars($addon['id']) ?>')">Rp <?= number_format($addon['price'], 0, ',', '.') ?></div>
          <button class="btn bs bsm" onclick="editGradAddon('<?= htmlspecialchars($addon['id']) ?>')" style="padding:4px 8px;font-size:11px">✏️</button>
          <button class="btn bs bsm" onclick="deleteGradAddon('<?= htmlspecialchars($addon['id']) ?>')" style="padding:4px 8px;font-size:11px">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Add New Add-on Item -->
    <div style="padding:12px;background:#f9f9f9;border-radius:4px;border:1px solid var(--border);margin-bottom:16px">
      <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:8px">Tambah Add-on Baru</div>
      <div style="display:grid;grid-template-columns:1fr 150px 80px;gap:8px;align-items:center">
        <input type="text" id="new-grad-addon-name" placeholder="Nama add-on (cth: Tambah 2 Jam)" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <input type="number" id="new-grad-addon-price" placeholder="Harga Rp" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <button class="btn bsm" style="background:#2a6b8a;color:white;border:none;cursor:pointer" onclick="addGradAddon()">+ Tambah</button>
      </div>
    </div>

    <!-- Daftar Cetak Items -->
    <div style="margin-bottom:16px">
      <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:8px">Cetak Foto Tambahan</div>
      <div id="grad-cetak-items" style="margin-bottom:12px">
        <?php foreach ($gradCetak as $cetak): ?>
        <div class="grad-cetak-item" data-id="<?= htmlspecialchars($cetak['id']) ?>" data-name="<?= htmlspecialchars($cetak['name']) ?>" data-price="<?= htmlspecialchars($cetak['price']) ?>" style="display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px">
          <div style="font-size:13px;font-weight:500"><?= htmlspecialchars($cetak['name']) ?></div>
          <div class="gc-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editGradCetak('<?= htmlspecialchars($cetak['id']) ?>')">Rp <?= number_format($cetak['price'], 0, ',', '.') ?></div>
          <button class="btn bs bsm" onclick="editGradCetak('<?= htmlspecialchars($cetak['id']) ?>')" style="padding:4px 8px;font-size:11px">✏️</button>
          <button class="btn bs bsm" onclick="deleteGradCetak('<?= htmlspecialchars($cetak['id']) ?>')" style="padding:4px 8px;font-size:11px">✕</button>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Add New Cetak Item -->
    <div style="padding:12px;background:#f9f9f9;border-radius:4px;border:1px solid var(--border);margin-bottom:16px">
      <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:8px">Tambah Cetak Baru</div>
      <div style="display:grid;grid-template-columns:1fr 150px 80px;gap:8px;align-items:center">
        <input type="text" id="new-grad-cetak-name" placeholder="Nama (cth: Cetak Foto A5)" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <input type="number" id="new-grad-cetak-price" placeholder="Harga Rp" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <button class="btn bsm" style="background:#2a6b8a;color:white;border:none;cursor:pointer" onclick="addGradCetak()">+ Tambah</button>
      </div>
    </div>

    <!-- Control Buttons -->
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <button class="btn bgrad bsm" onclick="saveGradAddon()">Simpan Add-on &amp; Cetak</button>
      <span id="grad-addon-status" style="font-size:12px;color:var(--success);display:none">✓ Tersimpan</span>
    </div>
  </div>

  <!-- Card 3: Preview Paket Utama & Add-On -->
  <div class="card mb16">
    <div class="ph"><div class="pt" style="color:var(--grad)">Preview Paket &amp; Add-on</div><div class="ps">Tampilan data sebagaimana akan dilihat oleh client</div></div>

    <div class="pkgrid mb20" id="grad-grid"></div>

    <div class="g2 mb20">
      <div>
        <div class="sec" style="margin-bottom:8px">Add-on Graduation</div>
        <div style="border:1px solid var(--border);border-radius:6px;overflow:hidden"><div class="tw" style="margin:0"><table><thead><tr><th>Item</th><th>Harga</th></tr></thead><tbody id="grad-addon"></tbody></table></div></div>
      </div>
      <div>
        <div class="sec" style="margin-bottom:8px">Cetak Foto Tambahan</div>
        <div style="border:1px solid var(--border);border-radius:6px;overflow:hidden"><div class="tw" style="margin:0"><table><thead><tr><th>Ukuran</th><th>Harga/lembar</th></tr></thead><tbody id="grad-cetak"></tbody></table></div></div>
      </div>
    </div>
  </div>

  <!-- Card 4: Kalkulator Graduation -->
  <div class="card mb16">
    <div class="ph"><div class="pt" style="color:var(--grad)">Kalkulator Graduation</div><div class="ps">Simulasi perhitungan interaktif untuk client</div></div>
    <div class="g2">
      <div>
        <div class="fg mb12">
          <label class="fl">Pilih Paket</label>
          <select id="gc-pkg" onchange="gcUpdate()"><option value="">— Pilih paket —</option></select>
        </div>
        <div id="gc-addons"></div>
      </div>
      <div>
        <div class="ct">Ringkasan</div>
        <div id="gc-result"></div>
        <div style="margin-top:10px;padding-top:8px;border-top:1px solid var(--border)">
          <div class="rr tot"><span class="rl">Total</span><span class="rv" id="gc-total" style="color:var(--grad)">—</span></div>
        </div>
        <div id="gc-note" class="note grad mt10"></div>
      </div>
    </div>
  </div>

  <!-- ===== BONUS & FASILITAS MASTER DATA ===== -->
  <div class="card mb16" id="bonus-fasilitas-card">
    <div class="ph">
      <div class="pt" style="color:var(--success)">🎁 Bonus &amp; Fasilitas</div>
      <div class="ps">Master data bonus per tipe paket — tampil di kalkulator &amp; tercetak di PDF penawaran</div>
    </div>
    <div class="note mb12">Setiap tipe paket memiliki daftar bonus standar tersendiri. Perubahan langsung berlaku di kalkulator dan PDF.</div>

    <!-- Tab Switcher -->
    <div style="display:flex;gap:6px;margin-bottom:16px;border-bottom:2px solid var(--border);padding-bottom:0">
      <button class="bf-tab-btn active" data-pkg="fullservice"
        onclick="bfSwitchTab('fullservice',this)"
        style="padding:8px 16px;font-size:13px;font-weight:600;border:none;border-bottom:2px solid transparent;background:none;cursor:pointer;color:var(--text2);margin-bottom:-2px;border-radius:4px 4px 0 0;transition:.15s">
        📚 Full Service
      </button>
      <button class="bf-tab-btn" data-pkg="graduation"
        onclick="bfSwitchTab('graduation',this)"
        style="padding:8px 16px;font-size:13px;font-weight:600;border:none;border-bottom:2px solid transparent;background:none;cursor:pointer;color:var(--text2);margin-bottom:-2px;border-radius:4px 4px 0 0;transition:.15s">
        🎓 Graduation
      </button>
      <button class="bf-tab-btn" data-pkg="alacarte"
        onclick="bfSwitchTab('alacarte',this)"
        style="padding:8px 16px;font-size:13px;font-weight:600;border:none;border-bottom:2px solid transparent;background:none;cursor:pointer;color:var(--text2);margin-bottom:-2px;border-radius:4px 4px 0 0;transition:.15s">
        🛒 À La Carte
      </button>
    </div>

    <!-- Deskripsi Paket per Tab -->
    <div id="bf-tab-desc" style="font-size:11px;color:var(--text3);margin-bottom:12px;padding:6px 10px;background:var(--surface2);border-radius:6px"></div>

    <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
      <label style="font-size:12px;font-weight:600;color:var(--text2)">Kategori:</label>
      <select id="bf-kategori-select" onchange="bfFilterKategori()" style="padding:6px; font-size:12px; border-radius:4px; border:1px solid var(--border); min-width:200px">
        <option value="all">Berlaku Semua (All)</option>
      </select>
    </div>

    <!-- Daftar Bonus Items -->
    <div id="bf-list" style="margin-bottom:16px;min-height:60px">
      <div style="color:var(--text3);font-size:13px;text-align:center;padding:20px 0">⏳ Memuat data...</div>
    </div>

    <!-- Separator -->
    <div style="border-top:1px dashed var(--border2);margin-bottom:14px"></div>

    <!-- Form Tambah Bonus Baru -->
    <div style="background:var(--surface2);border-radius:8px;padding:14px">
      <div style="font-size:11px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">+ Tambah Bonus Baru</div>
      <div style="display:grid;grid-template-columns:1fr 2fr;gap:8px;margin-bottom:8px">
        <div>
          <label style="font-size:11px;color:var(--text3);display:block;margin-bottom:3px">Judul Bonus <span style="color:var(--danger)">*</span></label>
          <input type="text" id="bf-new-label" placeholder="cth: Studio Foto, Buku Gratis..."
            style="width:100%;font-size:13px;padding:7px 10px;border:1px solid var(--border);border-radius:6px;background:var(--surface)">
        </div>
        <div>
          <label style="font-size:11px;color:var(--text3);display:block;margin-bottom:3px">Deskripsi Detail <span style="color:var(--danger)">*</span></label>
          <input type="text" id="bf-new-detail" placeholder="cth: Free portable studio, Fashion Stylist, Properti sesuai tema"
            style="width:100%;font-size:13px;padding:7px 10px;border:1px solid var(--border);border-radius:6px;background:var(--surface)"
            onkeydown="if(event.key==='Enter'){bfAddItem();event.preventDefault()}">
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <button class="btn bp bsm" onclick="bfAddItem()" style="padding:7px 18px;font-size:13px;font-weight:600">+ Tambah</button>
        <span id="bf-add-status" style="font-size:12px;display:none"></span>
      </div>
    </div>
  </div>



  <!-- ===== PAYMENT TERMS SECTION ===== -->
  <div class="card mb16" style="display:none">
    <div class="ph"><div class="pt" style="color:#9B59B6">Syarat Pembayaran</div><div class="ps">Kelola opsi pembayaran untuk penawaran dan proyek — DP, cicilan, atau pembayaran penuh</div></div>
    <div class="note grad mb16">💳 Kelola syarat pembayaran yang tersedia untuk penawaran proyek Anda. Setiap syarat mencakup persentase DP, deskripsi, dan warna penanda.</div>

    <div class="sec">Kelola Syarat Pembayaran</div>
    <div class="note mb12">Edit, tambah, atau hapus opsi pembayaran — perubahan tersimpan otomatis.</div>
    
    <!-- Daftar Payment Terms Items -->
    <div id="pt-items" style="margin-bottom:16px">
      <?php foreach ($paymentTerms as $term): ?>
      <div class="pt-item" data-id="<?= htmlspecialchars($term['id']) ?>" data-name="<?= htmlspecialchars($term['name']) ?>" data-deposit="<?= htmlspecialchars($term['deposit']) ?>" data-desc="<?= htmlspecialchars($term['desc'] ?? '') ?>" data-color="<?= htmlspecialchars($term['color'] ?? '#9B59B6') ?>" style="margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px;border:1px solid var(--border);display:grid;grid-template-columns:auto 1fr auto auto auto;gap:12px;align-items:center">
        <div style="width:20px;height:20px;border-radius:3px;background:<?= htmlspecialchars($term['color'] ?? '#9B59B6') ?>;border:1px solid rgba(0,0,0,0.1)"></div>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($term['name']) ?></div>
          <div style="font-size:11px;color:var(--text3);margin-top:2px">DP: <?= htmlspecialchars($term['deposit']) ?>%</div>
        </div>
        <div style="font-size:12px;color:var(--text2);min-width:200px"><?= htmlspecialchars($term['desc'] ?? '—') ?></div>
        <button class="btn bs bsm btn-edit-pt" data-id="<?= htmlspecialchars($term['id']) ?>" style="padding:4px 8px;font-size:11px;cursor:pointer">✏️</button>
        <button class="btn bs bsm btn-delete-pt" data-id="<?= htmlspecialchars($term['id']) ?>" style="padding:4px 8px;font-size:11px;cursor:pointer">✕</button>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Add New Payment Term Item -->
    <div style="padding:12px;background:#f9f9f9;border-radius:4px;border:1px solid var(--border);margin-bottom:16px">
      <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:8px">Tambah Syarat Pembayaran Baru</div>
      <div style="display:grid;grid-template-columns:1fr 80px 80px 80px;gap:8px;margin-bottom:8px;align-items:center">
        <input type="text" id="new-pt-name" placeholder="Nama (cth: DP 50%)" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <input type="number" id="new-pt-deposit" placeholder="DP %" min="0" max="100" style="border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px">
        <input type="color" id="new-pt-color" value="#9B59B6" style="border:1px solid var(--border);padding:4px;border-radius:3px;height:32px;cursor:pointer">
        <button class="btn bsm" style="background:#2a6b8a;color:white;border:none;cursor:pointer" onclick="addPaymentTerm()">+ Tambah</button>
      </div>
      <div style="margin-bottom:0">
        <label style="font-size:11px;font-weight:600;color:var(--text3);display:block;margin-bottom:4px">Deskripsi (opsional)</label>
        <textarea id="new-pt-desc" placeholder="Contoh: Pembayaran 50% di awal, 50% saat selesai..." style="width:100%;min-height:50px;border:1px solid var(--border);padding:6px 8px;border-radius:3px;font-size:12px;font-family:inherit"></textarea>
      </div>
    </div>

    <!-- Control Buttons -->
    <div style="display:none">
      <button class="btn bs bsm" onclick="resetPaymentTerms()">Reset ke Default</button>
    </div>
  </div>
</div>
</main>
</div>

<!-- Modal untuk Full Service Editor -->
<div id="fs-modal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center">
  <div style="background:white;padding:30px;border-radius:10px;max-width:600px;max-height:80vh;overflow-y:auto;width:90%">
    <div style="font-size:18px;font-weight:600;margin-bottom:20px">Edit Full Service Pricing</div>
    <div id="fs-modal-content"></div>
    <div style="margin-top:20px;display:flex;gap:10px">
      <button class="btn bp" onclick="saveFSModal()">Simpan</button>
      <button class="btn bs" onclick="closeFSModal()">Batal</button>
    </div>
  </div>
</div>

<?php
$jsUser = json_encode([
    'id'       => $user['id'],
    'name'     => $user['name'],
    'role'     => $user['role'],
    'isManager'=> $isManager,
    'isAdmin'  => $isAdmin,
]);
?>
<script src="/assets/js/app.js?v=1.2"></script>
<script src="/assets/js/app-pages.js?v=1.2"></script>
<script>
const PHP_USER = <?= $jsUser ?>;
// MASTER_API sudah didefinisikan di header.php — jangan redeclare
</script>
<script>
// ============================================================
// MASTER DATA EDITOR — pengaturan.php
// Semua perubahan disimpan via /api/master-data.php
// ============================================================

// Toast Notification System
function showToast(message, type = 'success', duration = 3000) {
    const container = document.getElementById('toast-container');
    
    // Color mapping
    const colors = {
        'success': { bg: '#e8f5e9', border: '#4caf50', icon: '✓', color: '#2e7d32' },
        'error': { bg: '#ffebee', border: '#f44336', icon: '✕', color: '#c62828' },
        'info': { bg: '#e3f2fd', border: '#2196f3', icon: 'ℹ', color: '#1565c0' },
        'warning': { bg: '#fff3e0', border: '#ff9800', icon: '⚠', color: '#e65100' }
    };
    
    const style = colors[type] || colors.info;
    
    const toast = document.createElement('div');
    toast.style.cssText = `
        background: ${style.bg};
        border-left: 4px solid ${style.border};
        padding: 14px 16px;
        border-radius: 4px;
        margin-bottom: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        color: ${style.color};
        font-weight: 500;
        animation: slideInRight 0.3s ease-out;
    `;
    
    toast.innerHTML = `
        <span style="font-size: 16px; font-weight: 600;">${style.icon}</span>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    // Auto remove
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// Add CSS animations if not already present
if (!document.getElementById('toast-animations')) {
    const style = document.createElement('style');
    style.id = 'toast-animations';
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Save functions untuk setiap section
async function refreshMasterData() {
    try {
        const response = await fetch(MASTER_API + '?action=get_all');
        const result = await response.json();
        if (result.success && result.data) {
            const data = result.data;
            
            // Update semua global variables
            if (data.overhead) {
                // Clear and re-populate OH to handle custom items
                for (let k in OH) delete OH[k];
                Object.assign(OH, flattenObject(data.overhead));
            }
            if (data.cetak_f) CETAK_F = {...data.cetak_f};
        }
    } catch (e) { console.error('Failed to refresh master data', e); }
}

async function saveOH() {
    try {
        console.log('=== saveOH() STARTED ===');
        
        // Show loading indicator
        const savedEl = document.getElementById('ov-status');
        if (savedEl) savedEl.textContent = '⏳ Menyimpan...';
        if (savedEl) savedEl.style.display = 'block';
        
        // 1. Force save any open inputs to dataset
        console.log('Step 1: Checking for open edit inputs...');
        document.querySelectorAll('.oh-edit-input').forEach(input => {
            const item = input.closest('.overhead-item');
            if (item) {
                const name = item.dataset.name;
                const newVal = parseInt(input.value || 0);
                console.log('  Found open input:', name, '=', newVal);
                item.dataset.value = newVal;
            }
        });

        // 2. Collect all overhead items from dataset
        console.log('Step 2: Collecting overhead items...');
        const overhead = {};
        document.querySelectorAll('.overhead-item').forEach(item => {
            const name = item.dataset.name;
            const value = parseInt(item.dataset.value || 0);
            // SKIP 'total' - jangan disimpan ke database
            if (name && name.toLowerCase() !== 'total') {
                overhead[name] = value;
                console.log('  Collected:', name, '=', value);
            }
        });
        
        console.log('Step 3: Validation...');
        if (Object.keys(overhead).length === 0) {
            console.warn('Validation failed: No items');
            showToast('Minimal harus ada 1 item overhead', 'error');
            if (savedEl) savedEl.style.display = 'none';
            return;
        }
        
        console.log('Complete overhead object:', overhead);
        
        console.log('Step 4: Calling updateMasterData...');
        const success = await updateMasterData('overhead', overhead);
        console.log('Step 5: updateMasterData response:', success);
        
        if (success) {
            console.log('✓ SUCCESS - Data saved to server');
            showToast('✓ Overhead berhasil tersimpan', 'success');
            
            // Update status indicator
            if (savedEl) {
                savedEl.textContent = '✓ Tersimpan ke server';
                savedEl.style.color = 'var(--success)';
                savedEl.style.display = 'block';
            }
            
            // Update local JS state if available
            if (typeof OH !== 'undefined') {
                console.log('Step 6: Updating OH object');
                Object.keys(overhead).forEach(k => {
                    const cleanK = k.toLowerCase().replace(/[. _-]/g, '');
                    OH[cleanK] = overhead[k];
                });
                OH.total = Object.values(overhead).reduce((a, b) => a + b, 0);
                console.log('OH updated:', OH);
            }
            
            // Refresh page after 1.5 seconds to show fresh data
            console.log('Refreshing page in 1.5 seconds...');
            setTimeout(() => {
                console.log('Page refresh triggered');
                location.reload();
            }, 1500);
        } else {
            console.error('✗ FAILED - Check console for error details');
            showToast('✕ Gagal menyimpan overhead. Cek console untuk detail error.', 'error');
            if (savedEl) {
                savedEl.textContent = '✗ Gagal menyimpan';
                savedEl.style.color = 'var(--error)';
                savedEl.style.display = 'block';
            }
        }
        console.log('=== saveOH() COMPLETED ===\n');
    } catch (err) {
        console.error('=== saveOH() ERROR ===', err);
        showToast('✕ Error: ' + err.message, 'error');
        const savedEl = document.getElementById('ov-status');
        if (savedEl) {
            savedEl.textContent = '✗ Error';
            savedEl.style.color = 'var(--error)';
            savedEl.style.display = 'block';
        }
    }
}

function updateOHTotal() {
    console.log('updateOHTotal() called');
    let total = 0;
    document.querySelectorAll('.overhead-item').forEach(item => {
        const name = item.dataset.name;
        const value = parseInt(item.dataset.value || 0);
        // Only sum items yang bukan 'total'
        if (name && name.toLowerCase() !== 'total') {
            total += value;
            console.log('  Adding to total:', name, '=', value, ', running total =', total);
        }
    });
    
    const totalEl = document.getElementById('oh-total');
    if (totalEl) {
        totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
        console.log('Updated oh-total display:', total);
    }
    
    // Hide saved status indicator
    const savedEl = document.getElementById('ov-status');
    if (savedEl) {
        savedEl.style.display = 'none';
    }
    console.log('Total updated:', total);
}

function editOHItem(name) {
    console.log('editOHItem() called for:', name);
    const item = document.querySelector(`.overhead-item[data-name="${name}"]`);
    if (!item) {
        console.warn('Item not found:', name);
        return;
    }
    
    const value = item.dataset.value;
    const display = item.querySelector('.oh-display');
    const buttons = item.querySelectorAll('button');
    if (buttons.length < 2) {
        console.warn('Buttons not found for item:', name);
        return;
    }
    
    const editBtn = buttons[0];  // ✏️ button
    const deleteBtn = buttons[1];  // ✕ button
    
    console.log('Entering edit mode for:', name, 'current value:', value);
    
    // Create input
    const input = document.createElement('input');
    input.type = 'number';
    input.className = 'oh-edit-input';
    input.value = value;
    input.style.cssText = 'border:2px solid var(--accent);padding:8px 12px;border-radius:3px;font-size:13px;font-weight:600;width:140px;text-align:right';
    
    // Update on input change - show unsaved indicator
    input.addEventListener('input', () => {
        console.log('Input changed for', name, ':', input.value);
    });
    
    // Handle Enter key to close edit
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            console.log('Enter pressed, closing edit for:', name);
            cancelOHItemEdit(name);
        }
        if (e.key === 'Escape') {
            console.log('Escape pressed, closing edit for:', name);
            cancelOHItemEdit(name);
        }
    });
    
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn bs bsm';
    cancelBtn.style.cssText = 'padding:6px 10px;font-size:11px;font-weight:600;background:#f44336;color:white;border:none;cursor:pointer;border-radius:3px';
    cancelBtn.textContent = '✕ Batal';
    cancelBtn.onclick = (e) => {
        e.preventDefault();
        console.log('Cancel button clicked for:', name);
        // Save pending value to dataset
        const newVal = parseInt(input.value || 0);
        if (newVal > 0) {
            item.dataset.value = newVal;
            console.log('Saved pending value:', name, '=', newVal);
        }
        cancelOHItemEdit(name);
    };
    
    // Replace display with input
    display.replaceWith(input);
    
    // Update edit button to show it's in edit mode
    editBtn.textContent = '📝 Editing...';
    editBtn.className = 'btn bsm oh-edit-mode';
    editBtn.style.cssText = 'padding:6px 10px;font-size:11px;font-weight:600;background:#9e9e9e;color:white;border:none;cursor:default;border-radius:3px';
    editBtn.disabled = true;
    editBtn.onclick = null;
    
    // Replace delete button with cancel
    deleteBtn.replaceWith(cancelBtn);
    
    console.log('Edit mode activated for:', name);
    input.focus();
    input.select();
}

function saveOHItemEdit(name) {
    console.log('saveOHItemEdit() called for:', name);
    const item = document.querySelector(`.overhead-item[data-name="${name}"]`);
    if (!item) {
        console.warn('Item not found:', name);
        return;
    }
    
    const input = item.querySelector('.oh-edit-input');
    if (!input) {
        console.warn('Edit input not found for:', name);
        return;
    }
    
    const newValue = parseInt(input.value || 0);
    console.log('Saving edit:', name, 'new value:', newValue);
    
    if (newValue <= 0) {
        showToast('Nilai harus lebih dari 0', 'warning');
        input.focus();
        console.warn('Invalid value:', newValue);
        return;
    }
    
    // Update data attribute
    item.dataset.value = newValue;
    
    // Create display
    const display = document.createElement('div');
    display.className = 'oh-display';
    display.style.cssText = 'font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0';
    display.textContent = 'Rp ' + newValue.toLocaleString('id-ID');
    display.onclick = () => editOHItem(name);
    
    // Replace input with display
    input.replaceWith(display);
    
    // Restore buttons
    const buttons = item.querySelectorAll('button');
    if (buttons.length >= 2) {
        const editBtn = buttons[0];
        const cancelBtn = buttons[1];
        
        // Restore edit button
        editBtn.textContent = '✏️';
        editBtn.onclick = () => editOHItem(name);
        editBtn.style.cssText = 'padding:4px 8px;font-size:11px';
        editBtn.className = 'btn bs bsm';
        
        // Replace cancel button with delete button
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn bs bsm';
        deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px';
        deleteBtn.textContent = '✕';
        deleteBtn.onclick = () => deleteOHItem(name);
        cancelBtn.replaceWith(deleteBtn);
    }
    
    updateOHTotal();
    console.log('Item updated. Total recalculated');
    showToast('✓ Item "' + name + '" diperbarui', 'success');
}

function cancelOHItemEdit(name) {
    console.log('cancelOHItemEdit() called for:', name);
    const item = document.querySelector(`.overhead-item[data-name="${name}"]`);
    if (!item) {
        console.warn('Item not found:', name);
        return;
    }
    
    const value = item.dataset.value;
    console.log('Canceling edit for:', name, 'value:', value);
    
    // Create display
    const display = document.createElement('div');
    display.className = 'oh-display';
    display.style.cssText = 'font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0';
    display.textContent = 'Rp ' + parseInt(value).toLocaleString('id-ID');
    display.onclick = () => editOHItem(name);
    
    // Find and replace input with display
    const input = item.querySelector('.oh-edit-input');
    if (input) {
        input.replaceWith(display);
    }
    
    // Restore buttons
    const buttons = item.querySelectorAll('button');
    if (buttons.length >= 2) {
        const editBtn = buttons[0];
        const cancelBtn = buttons[1];
        
        // Restore edit button
        editBtn.textContent = '✏️ Edit';
        editBtn.onclick = () => editOHItem(name);
        editBtn.style.cssText = 'padding:6px 10px;font-size:11px;font-weight:600;background:#2196F3;color:white;border:none;cursor:pointer;border-radius:3px';
        editBtn.className = 'btn bs bsm';
        editBtn.disabled = false;
        
        // Replace cancel button with delete button
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'btn bs bsm';
        deleteBtn.style.cssText = 'padding:6px 10px;font-size:11px;font-weight:600;background:#f44336;color:white;border:none;cursor:pointer;border-radius:3px';
        deleteBtn.textContent = '✕ Hapus';
        deleteBtn.onclick = () => deleteOHItem(name);
        cancelBtn.replaceWith(deleteBtn);
    }
    
    console.log('Edit mode canceled for:', name);
}

function addOHItem() {
    const name = document.getElementById('new-oh-name').value.trim();
    const value = parseInt(document.getElementById('new-oh-value').value || 0);
    
    if (!name) {
        showToast('Nama item tidak boleh kosong', 'warning');
        return;
    }
    
    if (value <= 0) {
        showToast('Nilai harus lebih dari 0', 'warning');
        return;
    }
    
    // Check if item already exists
    const existing = document.querySelector(`.overhead-item[data-name="${name}"]`);
    if (existing) {
        showToast('Item "' + name + '" sudah ada', 'warning');
        return;
    }
    
    // Create new item element with display text + edit button
    const newItem = document.createElement('div');
    newItem.className = 'overhead-item';
    newItem.dataset.name = name;
    newItem.dataset.value = value;
    newItem.style.cssText = 'display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px';
    
    newItem.innerHTML = `
        <div style="font-size:13px;font-weight:500">${escapeHtml(name)}</div>
        <div class="oh-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editOHItem('${escapeHtml(name)}')">Rp ${value.toLocaleString('id-ID')}</div>
        <button class="btn bs bsm" onclick="editOHItem('${escapeHtml(name)}')" style="padding:4px 8px;font-size:11px">✏️</button>
        <button class="btn bs bsm" onclick="deleteOHItem('${escapeHtml(name)}')" style="padding:4px 8px;font-size:11px">✕</button>
    `;
    
    document.getElementById('overhead-items').appendChild(newItem);
    
    // Clear form
    document.getElementById('new-oh-name').value = '';
    document.getElementById('new-oh-value').value = '';
    
    updateOHTotal();
    showToast('✓ Item "' + name + '" ditambahkan', 'success');
}

function deleteOHItem(name) {
    const item = document.querySelector(`.overhead-item[data-name="${name}"]`);
    if (!item) return;
    
    // Animate removal
    item.style.opacity = '0.5';
    item.style.textDecoration = 'line-through';
    
    setTimeout(() => {
        item.remove();
        updateOHTotal();
        showToast('✓ Item "' + name + '" dihapus', 'success');
        // Auto-save after delete
        saveOH();
    }, 300);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text ?? '').replace(/[&<>"']/g, m => map[m]);
}

async function resetOH() {
    showToast('⏳ Memproses reset...', 'info');
    const defaults = {
        'Designer': 16700000,
        'Marketing': 12750000,
        'Creative Prod.': 7670000,
        'Project Mgr': 7200000,
        'Social Media': 6430000,
        'Freelance': 3204000,
        'Operasional': 11586000,
    };
    
    const success = await updateMasterData('overhead', defaults);
    if (success) {
        showToast('✓ Data overhead direset ke default', 'success');
        setTimeout(() => location.reload(), 1500);
    } else {
        showToast('✕ Gagal mereset overhead', 'error');
    }
}

// saveCetakBase is now defined globally at the end of the file for better consistency

async function resetCetakBase() {
    if (!confirm('Reset ke nilai default Renjana?')) return;
    // Reset logic here
}

async function saveCetak() {
    const cetakF = {
        'handy': parseFloat(document.getElementById('ov-cetak-handy')?.value || 1.0),
        'minimal': parseFloat(document.getElementById('ov-cetak-minimal')?.value || 0.95),
        'large': parseFloat(document.getElementById('ov-cetak-large')?.value || 1.15),
    };
    
    const success = await updateMasterData('cetak_factors', cetakF);
    if (success) {
        CETAK_F = {...cetakF};
        showToast('✓ Faktor cetak berhasil tersimpan', 'success');
    } else {
        showToast('✕ Gagal menyimpan faktor cetak', 'error');
    }
}

async function saveALC() {
    // Collect values from form (in percentages) and convert to decimals
    const ebookVal = parseFloat(document.getElementById('ov-ac-ebook')?.value || 72);
    const editcetakVal = parseFloat(document.getElementById('ov-ac-editcetak')?.value || 62);
    const desainVal = parseFloat(document.getElementById('ov-ac-desain')?.value || 22);
    const cetakonlyVal = parseFloat(document.getElementById('ov-ac-cetakonly')?.value || 30);
    
    // Validate all values
    if (ebookVal <= 0 || editcetakVal <= 0 || desainVal <= 0 || cetakonlyVal <= 0) {
        showToast('✕ Semua faktor harus lebih besar dari 0', 'error');
        return;
    }
    
    const alcF = {
        'ebook': ebookVal / 100,
        'editcetak': editcetakVal / 100,
        'desain': desainVal / 100,
        'cetakonly': cetakonlyVal / 100,
    };
    
    const success = await updateMasterData('alacarte_factors', alcF);
    if (success) {
        showToast('✓ Faktor À La Carte berhasil tersimpan', 'success');
        if (typeof ALC_F !== 'undefined') Object.assign(ALC_F, alcF);
        if (typeof kalcUpdate === 'function') kalcUpdate();
    } else {
        showToast('✕ Gagal menyimpan faktor À La Carte', 'error');
    }
}

async function saveGrad() {
    // Collect graduation package prices from data attributes
    const packages = [];
    document.querySelectorAll('.grad-pkg-item').forEach(item => {
        packages.push({
            id: item.dataset.id,
            name: item.dataset.name,
            price: parseInt(item.dataset.price || 0),
            desc: item.dataset.desc || ''
        });
    });
    
    // Update GRAD object and save
    GRAD.packages = packages;
    
    const success = await updateMasterData('graduation', GRAD);
    if (success) {
        renderGraduation();
        showToast('✓ Harga Paket berhasil tersimpan', 'success');
    } else {
        showToast('✕ Gagal menyimpan harga paket', 'error');
    }
}

function editGradPkg(id) {
    const item = document.querySelector(`.grad-pkg-item[data-id="${id}"]`);
    if (!item) return;
    
    // Check if already in edit mode
    const existingContainer = item.querySelector('.gp-edit-container');
    if (existingContainer) {
        // Already in edit mode - cancel instead
        cancelGradPkgEdit(id);
        return;
    }
    
    const price = item.dataset.price;
    const desc = item.dataset.desc || '';
    const display = item.querySelector('.gp-display');
    const editBtn = item.querySelector('.btn-edit-price');
    const deleteBtn = item.querySelector('.btn-delete');
    
    // Create container for price and description editing - wrap both vertically
    const editContainer = document.createElement('div');
    editContainer.className = 'gp-edit-container';
    editContainer.style.cssText = 'display:flex;flex-direction:column;gap:8px;grid-column:2/3';
    
    // Create price input
    const priceInput = document.createElement('input');
    priceInput.type = 'number';
    priceInput.className = 'gp-edit-input';
    priceInput.value = price;
    priceInput.step = '50000';
    priceInput.style.cssText = 'border:2px solid var(--accent);padding:6px 8px;border-radius:3px;font-size:13px;font-weight:600;min-width:100px;text-align:right';
    priceInput.placeholder = 'Harga...';
    
    // Create description input
    const descInput = document.createElement('textarea');
    descInput.className = 'gp-edit-desc';
    descInput.value = desc;
    descInput.style.cssText = 'border:2px solid var(--accent);padding:6px 8px;border-radius:3px;font-size:12px;min-width:220px;min-height:135px;font-family:inherit;resize:vertical';
    descInput.placeholder = 'Deskripsi paket (opsional)...';
    
    editContainer.appendChild(priceInput);
    editContainer.appendChild(descInput);
    
    // Replace display with container
    display.replaceWith(editContainer);
    
    // Update button
    editBtn.textContent = '✓';
    editBtn.dataset.editState = 'save';
    editBtn.style.cssText = 'padding:4px 8px;font-size:11px;background:#4caf50;color:white;border:none;cursor:pointer;align-self:flex-start';
    editBtn.onclick = () => saveGradPkgEdit(id);
    
    // Replace delete button onclick with cancel (keep the button, just change behavior)
    deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px;cursor:pointer;align-self:flex-start;background:#f44336;color:white';
    deleteBtn.textContent = '✕';
    deleteBtn.onclick = () => cancelGradPkgEdit(id);
    
    // Focus price input
    priceInput.focus();
    priceInput.select();
}

function saveGradPkgEdit(id) {
    const item = document.querySelector(`.grad-pkg-item[data-id="${id}"]`);
    if (!item) {
        showToast('Error: tidak bisa menemukan paket', 'error');
        return;
    }
    
    // Get container (always exists when in edit mode)
    const container = item.querySelector('.gp-edit-container');
    if (!container) {
        showToast('Error: tidak dalam mode edit', 'error');
        return;
    }
    
    const priceInput = container.querySelector('.gp-edit-input');
    const descInput = container.querySelector('.gp-edit-desc');
    const newPrice = parseInt(priceInput.value || 0);
    const newDesc = descInput.value.trim();
    
    if (newPrice <= 0) {
        showToast('Harga harus lebih dari 0', 'warning');
        priceInput.focus();
        return;
    }
    
    // Update data attributes
    item.dataset.price = newPrice;
    item.dataset.desc = newDesc;
    
    // Update GRAD object
    const pkg = GRAD.packages.find(p => p.id === id);
    if (pkg) {
        pkg.price = newPrice;
        pkg.desc = newDesc;
    }
    
    // Call saveGrad and refresh page setelah save berhasil
    saveGrad().then(() => {
        showToast('✓ Harga & deskripsi paket diperbarui', 'success');
        // No longer reloading, renderGraduation() inside saveGrad handles it
    }).catch(err => {
        showToast('Error saat menyimpan: ' + err, 'error');
        // Tetap restore UI walaupun error
        const display = document.createElement('div');
        display.className = 'gp-display';
        display.style.cssText = 'font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0';
        display.textContent = 'Rp ' + newPrice.toLocaleString('id-ID');
        display.onclick = () => editGradPkg(id);
        
        container.replaceWith(display);
        
        const editBtn = item.querySelector('.btn-edit-price');
        const deleteBtn = item.querySelector('.btn-delete');
        
        if (editBtn) {
            editBtn.textContent = '✏️';
            editBtn.style.cssText = 'padding:4px 8px;font-size:11px;cursor:pointer';
            editBtn.onclick = null;
        }
        
        if (deleteBtn) {
            deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px;cursor:pointer';
            deleteBtn.onclick = () => deleteGradPkg(id);
        }
    });
}

function toggleGradPkgDesc(id) {
    const item = document.querySelector(`.grad-pkg-item[data-id="${id}"]`);
    if (!item) return;
    
    const descArea = item.querySelector('.gp-desc-area');
    const toggleBtn = item.querySelector('.gp-toggle-desc');
    
    if (descArea.style.display === 'none') {
        descArea.style.display = 'block';
        if (toggleBtn) toggleBtn.style.display = 'none';
    } else {
        descArea.style.display = 'none';
        if (toggleBtn) toggleBtn.style.display = 'block';
    }
}

function editDescGradPkg(id) {
    const item = document.querySelector(`.grad-pkg-item[data-id="${id}"]`);
    if (!item) return;
    
    const name = item.dataset.name;
    const currentDesc = item.dataset.desc || '';
    
    // Create modal for editing
    const modal = document.createElement('div');
    modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:2000;display:flex;align-items:center;justify-content:center';
    modal.id = 'desc-edit-modal';
    
    const content = document.createElement('div');
    content.style.cssText = 'background:white;padding:20px;border-radius:8px;max-width:600px;width:90%;max-height:80vh;overflow-y:auto;box-shadow:0 4px 20px rgba(0,0,0,0.2)';
    
    content.innerHTML = `
        <div style="font-size:16px;font-weight:600;margin-bottom:12px">Edit Deskripsi: ${escapeHtml(name)}</div>
        <div style="margin-bottom:12px">
            <label style="font-size:12px;font-weight:600;color:var(--text2);display:block;margin-bottom:6px">Deskripsi Paket</label>
            <textarea id="modal-desc-text" style="width:100%;min-height:100px;border:1px solid var(--border);padding:8px;border-radius:3px;font-size:13px;font-family:inherit;resize:vertical">${escapeHtml(currentDesc)}</textarea>
        </div>
        <div style="display:flex;gap:8px;justify-content:flex-end">
            <button class="btn bs" style="padding:8px 16px;cursor:pointer" onclick="closeDescModal()">Batal</button>
            <button class="btn bp" style="padding:8px 16px;cursor:pointer;background:#2a6b8a;color:white;border:none" onclick="saveDescModal('${escapeHtml(id)}')">💾 Simpan</button>
        </div>
    `;
    
    modal.appendChild(content);
    document.body.appendChild(modal);
    
    // Focus textarea
    document.getElementById('modal-desc-text').focus();
}

function closeDescModal() {
    const modal = document.getElementById('desc-edit-modal');
    if (modal) modal.remove();
}

function saveDescModal(id) {
    const item = document.querySelector(`.grad-pkg-item[data-id="${id}"]`);
    if (!item) return closeDescModal();
    
    const textarea = document.getElementById('modal-desc-text');
    const newDesc = textarea.value.trim();
    
    // Update data attribute
    item.dataset.desc = newDesc;
    
    // Update/rebuild description area
    let descAreaHtml = '';
    if (newDesc) {
        descAreaHtml = `
            <div class="gp-desc-area" style="padding:0 10px 10px 10px;border-top:1px solid var(--border)">
              <div style="font-size:11px;font-weight:600;color:var(--text3);margin-bottom:6px">Deskripsi Paket</div>
              <div style="font-size:12px;line-height:1.4;color:var(--text2);margin-bottom:8px;padding:8px;background:white;border-radius:3px;border:1px solid var(--border)">${escapeHtml(newDesc)}</div>
              <button class="gp-edit-desc-btn" style="width:100%;padding:6px;background:transparent;border:none;border-top:1px solid var(--border);color:var(--text3);font-size:11px;cursor:pointer;text-align:center;margin-top:0" onclick="editDescGradPkg('${escapeHtml(id)}')">✏️ Edit Deskripsi</button>
            </div>
        `;
    } else {
        descAreaHtml = `<button class="gp-add-desc-btn" style="width:100%;padding:6px;background:transparent;border:none;border-top:1px solid var(--border);color:var(--text3);font-size:11px;cursor:pointer;text-align:center;margin-top:0" onclick="editDescGradPkg('${escapeHtml(id)}')">+ Tambah Deskripsi</button>`;
    }
    
    // Remove old description area
    const oldDescArea = item.querySelector('.gp-desc-area');
    const oldAddBtn = item.querySelector('.gp-add-desc-btn');
    const oldEditBtn = item.querySelector('.gp-edit-desc-btn');
    const oldToggleBtn = item.querySelector('.gp-toggle-desc');
    
    if (oldDescArea) oldDescArea.remove();
    if (oldAddBtn) oldAddBtn.remove();
    if (oldEditBtn) oldEditBtn.remove();
    if (oldToggleBtn) oldToggleBtn.remove();
    
    // Add new description area
    const newDescElement = document.createElement('div');
    newDescElement.innerHTML = descAreaHtml;
    item.appendChild(newDescElement.firstElementChild || newDescElement);
    
    showToast('✓ Deskripsi paket disimpan', 'success');
    closeDescModal();
}

function cancelGradPkgEdit(id) {
    const item = document.querySelector(`.grad-pkg-item[data-id="${id}"]`);
    if (!item) return;
    
    const price = item.dataset.price;
    
    const display = document.createElement('div');
    display.className = 'gp-display';
    display.style.cssText = 'font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0';
    display.textContent = 'Rp ' + parseInt(price).toLocaleString('id-ID');
    display.onclick = () => editGradPkg(id);
    
    const container = item.querySelector('.gp-edit-container');
    container.replaceWith(display);
    
    const editBtn = item.querySelector('.btn-edit-price');
    if (editBtn) {
        editBtn.textContent = '✏️';
        editBtn.style.cssText = 'padding:4px 8px;font-size:11px;cursor:pointer';
        editBtn.dataset.editState = '';
        editBtn.onclick = null;
    }
    
    const deleteBtn = item.querySelector('.btn-delete');
    if (deleteBtn) {
        deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px;cursor:pointer';
        deleteBtn.textContent = '✕';
        deleteBtn.onclick = () => deleteGradPkg(id);
    }
}

function addGradPkg() {
    const name = document.getElementById('new-grad-pkg-name').value.trim();
    const price = parseInt(document.getElementById('new-grad-pkg-price').value || 0);
    const desc = document.getElementById('new-grad-pkg-desc').value.trim();
    
    if (!name) {
        showToast('Nama paket tidak boleh kosong', 'warning');
        return;
    }
    
    if (price <= 0) {
        showToast('Harga harus lebih dari 0', 'warning');
        return;
    }
    
    // Generate ID from timestamp
    const id = 'gp_' + Date.now();
    
    // Check if package already exists
    const existing = document.querySelector(`.grad-pkg-item[data-name="${name}"]`);
    if (existing) {
        showToast('Paket "' + name + '" sudah ada', 'warning');
        return;
    }
    
    // Create new item element
    const newItem = document.createElement('div');
    newItem.className = 'grad-pkg-item';
    newItem.dataset.id = id;
    newItem.dataset.name = name;
    newItem.dataset.price = price;
    newItem.dataset.desc = desc;
    newItem.style.cssText = 'margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px;border:1px solid var(--border);display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center';
    
    // Create grid content
    newItem.innerHTML = `
        <div style="font-size:13px;font-weight:500">${escapeHtml(name)}</div>
        <div class="gp-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editGradPkg('${escapeHtml(id)}')">Rp ${price.toLocaleString('id-ID')}</div>
        <button class="btn bs bsm btn-edit-price" data-id="${escapeHtml(id)}" style="padding:4px 8px;font-size:11px;cursor:pointer">✏️</button>
        <button class="btn bs bsm btn-delete" data-id="${escapeHtml(id)}" style="padding:4px 8px;font-size:11px;cursor:pointer">✕</button>
    `;
    
    document.getElementById('grad-pkg-items').appendChild(newItem);
    
    // Attach event listeners to new buttons
    const editBtn = newItem.querySelector('.btn-edit-price');
    const deleteBtn = newItem.querySelector('.btn-delete');
    
    editBtn.addEventListener('click', function() {
        editGradPkg(this.dataset.id);
    });
    deleteBtn.addEventListener('click', function() {
        deleteGradPkg(this.dataset.id);
    });
    
    // Clear form
    document.getElementById('new-grad-pkg-name').value = '';
    document.getElementById('new-grad-pkg-price').value = '';
    document.getElementById('new-grad-pkg-desc').value = '';
    
    // Add to GRAD object
    GRAD.packages.push({
        id: id,
        name: name,
        price: price,
        desc: desc,
        color: '#A9D6E5'
    });
    
    showToast('✓ Paket "' + name + '" ditambahkan', 'success');
}

function deleteGradPkg(id) {
    const item = document.querySelector(`.grad-pkg-item[data-id="${id}"]`);
    if (!item) return;
    
    const name = item.dataset.name;
    
    // Animate removal
    item.style.opacity = '0.5';
    item.style.textDecoration = 'line-through';
    
    setTimeout(() => {
        item.remove();
        showToast('✓ Paket "' + name + '" dihapus', 'success');
        // Auto-save after delete
        saveGrad();
    }, 300);
}

// ===== GRADIENT ADD-ON CRUD =====

async function saveGradAddon() {
    // Collect addon and cetak prices from data attributes
    const addons = [];
    document.querySelectorAll('.grad-addon-item').forEach(item => {
        addons.push({
            id: item.dataset.id,
            name: item.dataset.name,
            price: parseInt(item.dataset.price || 0)
        });
    });
    
    const cetak = [];
    document.querySelectorAll('.grad-cetak-item').forEach(item => {
        cetak.push({
            id: item.dataset.id,
            name: item.dataset.name,
            price: parseInt(item.dataset.price || 0)
        });
    });
    
    GRAD.addons = addons;
    GRAD.cetak = cetak;
    
    const success = await updateMasterData('graduation', GRAD);
    if (success) {
        renderGraduation();
        showToast('✓ Add-on & Cetak berhasil tersimpan', 'success');
    } else {
        showToast('✕ Gagal menyimpan add-on & cetak', 'error');
    }
}

function editGradAddon(id) {
    const item = document.querySelector(`.grad-addon-item[data-id="${id}"]`);
    if (!item) return;
    
    const price = item.dataset.price;
    const display = item.querySelector('.ga-display');
    const editBtn = item.querySelector('button:nth-child(3)');
    
    // Hide display, show edit mode
    const input = document.createElement('input');
    input.type = 'number';
    input.className = 'ga-edit-input';
    input.value = price;
    input.step = '50000';
    input.style.cssText = 'border:2px solid var(--accent);padding:6px 8px;border-radius:3px;font-size:13px;font-weight:600;width:120px;text-align:right';
    
    // Replace display with input
    display.replaceWith(input);
    editBtn.textContent = '✓';
    editBtn.onclick = () => saveGradAddonEdit(id);
    editBtn.style.cssText = 'padding:4px 8px;font-size:11px;background:#4caf50;color:white;border:none;cursor:pointer';
    
    // Replace delete button with cancel
    const deleteBtn = item.querySelector('button:nth-child(4)');
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn bs bsm';
    cancelBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    cancelBtn.textContent = '✕';
    cancelBtn.onclick = () => cancelGradAddonEdit(id);
    deleteBtn.replaceWith(cancelBtn);
    
    // Focus input
    input.focus();
    input.select();
}

function saveGradAddonEdit(id) {
    const item = document.querySelector(`.grad-addon-item[data-id="${id}"]`);
    if (!item) return;
    
    const input = item.querySelector('.ga-edit-input');
    const newPrice = parseInt(input.value || 0);
    
    if (newPrice <= 0) {
        showToast('Harga harus lebih dari 0', 'warning');
        input.focus();
        return;
    }
    
    // Update data and display
    item.dataset.price = newPrice;
    
    const display = document.createElement('div');
    display.className = 'ga-display';
    display.style.cssText = 'font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0';
    display.textContent = 'Rp ' + newPrice.toLocaleString('id-ID');
    display.onclick = () => editGradAddon(id);
    
    const editBtn = item.querySelector('button:nth-child(3)');
    const cancelBtn = item.querySelector('button:nth-child(4)');
    
    // Restore buttons
    input.replaceWith(display);
    editBtn.textContent = '✏️';
    editBtn.onclick = () => editGradAddon(id);
    editBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'btn bs bsm';
    deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    deleteBtn.textContent = '✕';
    deleteBtn.onclick = () => deleteGradAddon(id);
    cancelBtn.replaceWith(deleteBtn);
    
    showToast('✓ Add-on diperbarui', 'success');
}

function cancelGradAddonEdit(id) {
    const item = document.querySelector(`.grad-addon-item[data-id="${id}"]`);
    if (!item) return;
    
    const price = item.dataset.price;
    
    const display = document.createElement('div');
    display.className = 'ga-display';
    display.style.cssText = 'font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0';
    display.textContent = 'Rp ' + parseInt(price).toLocaleString('id-ID');
    display.onclick = () => editGradAddon(id);
    
    const input = item.querySelector('.ga-edit-input');
    input.replaceWith(display);
    
    const editBtn = item.querySelector('button:nth-child(3)');
    editBtn.textContent = '✏️';
    editBtn.onclick = () => editGradAddon(id);
    editBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    
    const cancelBtn = item.querySelector('button:nth-child(4)');
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'btn bs bsm';
    deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    deleteBtn.textContent = '✕';
    deleteBtn.onclick = () => deleteGradAddon(id);
    cancelBtn.replaceWith(deleteBtn);
}

function addGradAddon() {
    const name = document.getElementById('new-grad-addon-name').value.trim();
    const price = parseInt(document.getElementById('new-grad-addon-price').value || 0);
    
    if (!name) {
        showToast('Nama add-on tidak boleh kosong', 'warning');
        return;
    }
    
    if (price <= 0) {
        showToast('Harga harus lebih dari 0', 'warning');
        return;
    }
    
    // Generate ID from name
    const id = 'ga_' + Date.now();
    
    // Check if item already exists
    const existing = document.querySelector(`.grad-addon-item[data-name="${name}"]`);
    if (existing) {
        showToast('Add-on "' + name + '" sudah ada', 'warning');
        return;
    }
    
    // Create new item element
    const newItem = document.createElement('div');
    newItem.className = 'grad-addon-item';
    newItem.dataset.id = id;
    newItem.dataset.name = name;
    newItem.dataset.price = price;
    newItem.style.cssText = 'display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px';
    
    newItem.innerHTML = `
        <div style="font-size:13px;font-weight:500">${escapeHtml(name)}</div>
        <div class="ga-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editGradAddon('${escapeHtml(id)}')">Rp ${price.toLocaleString('id-ID')}</div>
        <button class="btn bs bsm" onclick="editGradAddon('${escapeHtml(id)}')" style="padding:4px 8px;font-size:11px">✏️</button>
        <button class="btn bs bsm" onclick="deleteGradAddon('${escapeHtml(id)}')" style="padding:4px 8px;font-size:11px">✕</button>
    `;
    
    document.getElementById('grad-addon-items').appendChild(newItem);
    
    // Clear form
    document.getElementById('new-grad-addon-name').value = '';
    document.getElementById('new-grad-addon-price').value = '';
    
    showToast('✓ Add-on "' + name + '" ditambahkan', 'success');
}

function deleteGradAddon(id) {
    const item = document.querySelector(`.grad-addon-item[data-id="${id}"]`);
    if (!item) return;
    
    const name = item.dataset.name;
    
    // Animate removal
    item.style.opacity = '0.5';
    item.style.textDecoration = 'line-through';
    
    setTimeout(() => {
        item.remove();
        showToast('✓ Add-on "' + name + '" dihapus', 'success');
        // Auto-save after delete
        saveGradAddon();
    }, 300);
}

// ===== GRADUATION CETAK CRUD =====

function editGradCetak(id) {
    const item = document.querySelector(`.grad-cetak-item[data-id="${id}"]`);
    if (!item) return;
    
    const price = item.dataset.price;
    const display = item.querySelector('.gc-display');
    const editBtn = item.querySelector('button:nth-child(3)');
    
    // Hide display, show edit mode
    const input = document.createElement('input');
    input.type = 'number';
    input.className = 'gc-edit-input';
    input.value = price;
    input.step = '500';
    input.style.cssText = 'border:2px solid var(--accent);padding:6px 8px;border-radius:3px;font-size:13px;font-weight:600;width:120px;text-align:right';
    
    // Replace display with input
    display.replaceWith(input);
    editBtn.textContent = '✓';
    editBtn.onclick = () => saveGradCetakEdit(id);
    editBtn.style.cssText = 'padding:4px 8px;font-size:11px;background:#4caf50;color:white;border:none;cursor:pointer';
    
    // Replace delete button with cancel
    const deleteBtn = item.querySelector('button:nth-child(4)');
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn bs bsm';
    cancelBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    cancelBtn.textContent = '✕';
    cancelBtn.onclick = () => cancelGradCetakEdit(id);
    deleteBtn.replaceWith(cancelBtn);
    
    // Focus input
    input.focus();
    input.select();
}

function saveGradCetakEdit(id) {
    const item = document.querySelector(`.grad-cetak-item[data-id="${id}"]`);
    if (!item) return;
    
    const input = item.querySelector('.gc-edit-input');
    const newPrice = parseInt(input.value || 0);
    
    if (newPrice <= 0) {
        showToast('Harga harus lebih dari 0', 'warning');
        input.focus();
        return;
    }
    
    // Update data and display
    item.dataset.price = newPrice;
    
    const display = document.createElement('div');
    display.className = 'gc-display';
    display.style.cssText = 'font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0';
    display.textContent = 'Rp ' + newPrice.toLocaleString('id-ID');
    display.onclick = () => editGradCetak(id);
    
    const editBtn = item.querySelector('button:nth-child(3)');
    const cancelBtn = item.querySelector('button:nth-child(4)');
    
    // Restore buttons
    input.replaceWith(display);
    editBtn.textContent = '✏️';
    editBtn.onclick = () => editGradCetak(id);
    editBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'btn bs bsm';
    deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    deleteBtn.textContent = '✕';
    deleteBtn.onclick = () => deleteGradCetak(id);
    cancelBtn.replaceWith(deleteBtn);
    
    showToast('✓ Cetak foto diperbarui', 'success');
}

function cancelGradCetakEdit(id) {
    const item = document.querySelector(`.grad-cetak-item[data-id="${id}"]`);
    if (!item) return;
    
    const price = item.dataset.price;
    
    const display = document.createElement('div');
    display.className = 'gc-display';
    display.style.cssText = 'font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0';
    display.textContent = 'Rp ' + parseInt(price).toLocaleString('id-ID');
    display.onclick = () => editGradCetak(id);
    
    const input = item.querySelector('.gc-edit-input');
    input.replaceWith(display);
    
    const editBtn = item.querySelector('button:nth-child(3)');
    editBtn.textContent = '✏️';
    editBtn.onclick = () => editGradCetak(id);
    editBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    
    const cancelBtn = item.querySelector('button:nth-child(4)');
    const deleteBtn = document.createElement('button');
    deleteBtn.className = 'btn bs bsm';
    deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px';
    deleteBtn.textContent = '✕';
    deleteBtn.onclick = () => deleteGradCetak(id);
    cancelBtn.replaceWith(deleteBtn);
}

function addGradCetak() {
    const name = document.getElementById('new-grad-cetak-name').value.trim();
    const price = parseInt(document.getElementById('new-grad-cetak-price').value || 0);
    
    if (!name) {
        showToast('Nama cetak tidak boleh kosong', 'warning');
        return;
    }
    
    if (price <= 0) {
        showToast('Harga harus lebih dari 0', 'warning');
        return;
    }
    
    // Generate ID from name
    const id = 'gc_' + Date.now();
    
    // Check if item already exists
    const existing = document.querySelector(`.grad-cetak-item[data-name="${name}"]`);
    if (existing) {
        showToast('Cetak "' + name + '" sudah ada', 'warning');
        return;
    }
    
    // Create new item element
    const newItem = document.createElement('div');
    newItem.className = 'grad-cetak-item';
    newItem.dataset.id = id;
    newItem.dataset.name = name;
    newItem.dataset.price = price;
    newItem.style.cssText = 'display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px';
    
    newItem.innerHTML = `
        <div style="font-size:13px;font-weight:500">${escapeHtml(name)}</div>
        <div class="gc-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editGradCetak('${escapeHtml(id)}')">Rp ${price.toLocaleString('id-ID')}</div>
        <button class="btn bs bsm" onclick="editGradCetak('${escapeHtml(id)}')" style="padding:4px 8px;font-size:11px">✏️</button>
        <button class="btn bs bsm" onclick="deleteGradCetak('${escapeHtml(id)}')" style="padding:4px 8px;font-size:11px">✕</button>
    `;
    
    document.getElementById('grad-cetak-items').appendChild(newItem);
    
    // Clear form
    document.getElementById('new-grad-cetak-name').value = '';
    document.getElementById('new-grad-cetak-price').value = '';
    
    showToast('✓ Cetak "' + name + '" ditambahkan', 'success');
}

function deleteGradCetak(id) {
    const item = document.querySelector(`.grad-cetak-item[data-id="${id}"]`);
    if (!item) return;
    
    const name = item.dataset.name;
    
    // Animate removal
    item.style.opacity = '0.5';
    item.style.textDecoration = 'line-through';
    
    setTimeout(() => {
        item.remove();
        showToast('✓ Cetak "' + name + '" dihapus', 'success');
        // Auto-save after delete
        saveGradAddon();
    }, 300);
}

async function resetGrad() {
    showToast('⏳ Memproses reset...', 'info');
    GRAD = {
        packages: [
            {id:'gphv',name:'Photo & Video',price:4500000,desc:'2 Fotografer + 1 Videografer, 50 foto edited, video cinematic 2–4 mnt, G-Drive, 4 jam coverage, transport jabodetabek',color:'acc'},
            {id:'gvideo',name:'Video Only',price:2000000,desc:'1 Videografer, video cinematic 2–5 mnt, G-Drive, 4 jam coverage, transport jabodetabek',color:''},
            {id:'gphoto',name:'Photo Only',price:2750000,desc:'2 Fotografer, 100 foto edited, G-Drive, 4 jam coverage, transport jabodetabek',color:''},
            {id:'gbooth',name:'Photo Booth',price:3850000,desc:'1–2 Crew profesional, backdrop wisuda, lighting studio, Selfiebox Machine, unlimited print 4R, max 3 jam, softcopy + QR Code realtime, transport jabodetabek',color:''},
            {id:'g360',name:'Glamation 360°',price:4100000,desc:'1–2 Crew profesional, MP4, LCD 50in preview, GoPro/iPhone 12 Pro, overlay design free, max 3 jam, QR Code realtime, transport jabodetabek',color:''},
            {id:'gcomplete',name:'Complete Package',price:7750000,desc:'Photo (2 foto, 100 edited) + Video (1 videografer, cinematic 2–4 mnt) + Photo Booth (unlimited print 4R, max 3 jam, QR Code), transport jabodetabek',color:'feat'},
        ],
        addons: [
            {id:'gvideo_add',name:'Tambah 1 Videografer',price:1500000},
            {id:'gphoto_add',name:'Tambah 1 Fotografer',price:1250000},
            {id:'gbooth_add',name:'Tambah 1 Jam Photobooth/360',price:500000},
            {id:'gwork_add',name:'Tambah 1 Jam Kerja/Orang',price:350000},
        ],
        cetak: [
            {id:'g4r',name:'Cetak Foto 4R',price:4000},
            {id:'g8r',name:'Cetak Foto 8R',price:8000},
            {id:'g10r',name:'Cetak Foto 10R',price:15000},
            {id:'g12r',name:'Cetak Foto 12R',price:20000},
        ]
    };
    
    // Update form fields with reset values - packages
    document.getElementById('grad-pkg-items').innerHTML = '';
    GRAD.packages.forEach(pkg => {
        const newItem = document.createElement('div');
        newItem.className = 'grad-pkg-item';
        newItem.dataset.id = pkg.id;
        newItem.dataset.name = pkg.name;
        newItem.dataset.price = pkg.price;
        newItem.dataset.desc = pkg.desc || '';
        newItem.style.cssText = 'margin-bottom:12px;padding:0;background:#fafafa;border-radius:4px;border:1px solid var(--border)';
        
        let descBottom = '';
        if (pkg.desc) {
            descBottom = `
                <div class="gp-desc-area" style="padding:0 10px 10px 10px;border-top:1px solid var(--border)">
                  <div style="font-size:11px;font-weight:600;color:var(--text3);margin-bottom:6px">Deskripsi Paket</div>
                  <div style="font-size:12px;line-height:1.4;color:var(--text2);margin-bottom:8px;padding:8px;background:white;border-radius:3px;border:1px solid var(--border)">${escapeHtml(pkg.desc)}</div>
                  <button class="gp-edit-desc-btn" style="width:100%;padding:6px;background:transparent;border:none;border-top:1px solid var(--border);color:var(--text3);font-size:11px;cursor:pointer;text-align:center;margin-top:0" onclick="editDescGradPkg('${escapeHtml(pkg.id)}')">✏️ Edit Deskripsi</button>
                </div>
            `;
        } else {
            descBottom = `<button class="gp-add-desc-btn" style="width:100%;padding:6px;background:transparent;border:none;border-top:1px solid var(--border);color:var(--text3);font-size:11px;cursor:pointer;text-align:center;margin-top:0" onclick="editDescGradPkg('${escapeHtml(pkg.id)}')">+ Tambah Deskripsi</button>`;
        }
        
        newItem.innerHTML = `
            <div style="display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;padding:10px">
              <div style="font-size:13px;font-weight:500">${escapeHtml(pkg.name)}</div>
              <div class="gp-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editGradPkg('${escapeHtml(pkg.id)}')">Rp ${pkg.price.toLocaleString('id-ID')}</div>
              <button class="btn bs bsm" onclick="editGradPkg('${escapeHtml(pkg.id)}')" style="padding:4px 8px;font-size:11px">✏️</button>
              <button class="btn bs bsm" onclick="deleteGradPkg('${escapeHtml(pkg.id)}')" style="padding:4px 8px;font-size:11px">✕</button>
            </div>
            ${descBottom}
        `;
        
        document.getElementById('grad-pkg-items').appendChild(newItem);
    });
    
    // Update form fields with reset values - addons
    document.getElementById('grad-addon-items').innerHTML = '';
    GRAD.addons.forEach(addon => {
        const newItem = document.createElement('div');
        newItem.className = 'grad-addon-item';
        newItem.dataset.id = addon.id;
        newItem.dataset.name = addon.name;
        newItem.dataset.price = addon.price;
        newItem.style.cssText = 'display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px';
        
        newItem.innerHTML = `
            <div style="font-size:13px;font-weight:500">${escapeHtml(addon.name)}</div>
            <div class="ga-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editGradAddon('${escapeHtml(addon.id)}')">Rp ${addon.price.toLocaleString('id-ID')}</div>
            <button class="btn bs bsm" onclick="editGradAddon('${escapeHtml(addon.id)}')" style="padding:4px 8px;font-size:11px">✏️</button>
            <button class="btn bs bsm" onclick="deleteGradAddon('${escapeHtml(addon.id)}')" style="padding:4px 8px;font-size:11px">✕</button>
        `;
        
        document.getElementById('grad-addon-items').appendChild(newItem);
    });
    
    // Update form fields with reset values - cetak
    document.getElementById('grad-cetak-items').innerHTML = '';
    GRAD.cetak.forEach(cetak => {
        const newItem = document.createElement('div');
        newItem.className = 'grad-cetak-item';
        newItem.dataset.id = cetak.id;
        newItem.dataset.name = cetak.name;
        newItem.dataset.price = cetak.price;
        newItem.style.cssText = 'display:grid;grid-template-columns:1fr auto auto auto;gap:8px;align-items:center;margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px';
        
        newItem.innerHTML = `
            <div style="font-size:13px;font-weight:500">${escapeHtml(cetak.name)}</div>
            <div class="gc-display" style="font-size:13px;font-weight:600;color:var(--accent);min-width:120px;text-align:right;cursor:pointer;padding:4px 0" onclick="editGradCetak('${escapeHtml(cetak.id)}')">Rp ${cetak.price.toLocaleString('id-ID')}</div>
            <button class="btn bs bsm" onclick="editGradCetak('${escapeHtml(cetak.id)}')" style="padding:4px 8px;font-size:11px">✏️</button>
            <button class="btn bs bsm" onclick="deleteGradCetak('${escapeHtml(cetak.id)}')" style="padding:4px 8px;font-size:11px">✕</button>
        `;
        
        document.getElementById('grad-cetak-items').appendChild(newItem);
    });
    
    renderGraduation();
    await saveGradAddon();
}

// ============================================================
// PAYMENT TERMS CRUD FUNCTIONS
// ============================================================

// Global Payment Terms object
let PT = <?= json_encode(['terms' => $paymentTerms]) ?>;

async function savePaymentTerms() {
    try {
        // Collect payment terms from data attributes
        const terms = [];
        document.querySelectorAll('.pt-item').forEach(item => {
            terms.push({
                id: item.dataset.id,
                name: item.dataset.name,
                deposit: parseInt(item.dataset.deposit || 0),
                desc: item.dataset.desc || '',
                color: item.dataset.color || '#9B59B6'
            });
        });
        
        console.log('Collected terms:', terms);
        console.log('MASTER_API defined?', typeof MASTER_API !== 'undefined', MASTER_API);
        
        // Update PT object and save
        PT.terms = terms;
        
        console.log('PT object to be saved:', PT);
        console.log('updateMasterData function exists?', typeof updateMasterData === 'function');
        
        const success = await updateMasterData('payment_terms', PT);
        console.log('updateMasterData result:', success);
        
        if (success) {
            showToast('✓ Syarat Pembayaran berhasil tersimpan', 'success');
            console.log('Reloading page in 500ms...');
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            showToast('✕ Gagal menyimpan syarat pembayaran', 'error');
        }
    } catch (err) {
        console.error('Error in savePaymentTerms:', err);
        console.error('Stack:', err.stack);
        showToast('✕ Error: ' + err.message, 'error');
    }
}

function editPaymentTerm(id) {
    try {
        const item = document.querySelector(`.pt-item[data-id="${id}"]`);
        if (!item) {
            showToast('✕ Error: Item tidak ditemukan', 'error');
            console.error('Item not found for PT id:', id);
            return;
        }
        
        // Check if already in edit mode
        const existingContainer = item.querySelector('.pt-edit-container');
        if (existingContainer) {
            cancelPaymentTermEdit(id);
            return;
        }
        
        const name = item.dataset.name;
        const deposit = item.dataset.deposit || '';
        const desc = item.dataset.desc || '';
        const color = item.dataset.color || '#9B59B6';
        
        console.log('editPaymentTerm started for id:', id, { name, deposit, desc, color });
        
        // Find the details div - it contains name (font-weight:600) and DP info (text3 color)
        let detailsDiv = null;
        const allDivs = item.querySelectorAll('div');
        console.log('Total divs in item:', allDivs.length);
        
        for (let el of allDivs) {
            const hasName = el.querySelector('[style*="font-weight:600"]');
            const hasDP = el.textContent.includes('DP:');
            if (hasName && hasDP) {
                detailsDiv = el;
                console.log('Found details div');
                break;
            }
        }
        
        if (!detailsDiv) {
            showToast('✕ Error: Detail area tidak ditemukan', 'error');
            console.error('Details div not found');
            return;
        }
        
        // Create container for editing
        const editContainer = document.createElement('div');
        editContainer.className = 'pt-edit-container';
        editContainer.style.cssText = 'display:flex;flex-direction:column;gap:8px';
        
        // Create inputs
        const nameInput = document.createElement('input');
        nameInput.type = 'text';
        nameInput.className = 'pt-edit-name';
        nameInput.value = name;
        nameInput.placeholder = 'Nama';
        nameInput.style.cssText = 'border:2px solid #9B59B6;padding:6px 8px;border-radius:3px;font-size:12px;font-weight:600';
        
        const depositInput = document.createElement('input');
        depositInput.type = 'number';
        depositInput.className = 'pt-edit-deposit';
        depositInput.value = deposit;
        depositInput.min = 0;
        depositInput.max = 100;
        depositInput.placeholder = 'DP %';
        depositInput.style.cssText = 'border:2px solid #9B59B6;padding:6px 8px;border-radius:3px;font-size:12px;text-align:center;width:60px';
        
        const descInput = document.createElement('textarea');
        descInput.className = 'pt-edit-desc';
        descInput.value = desc;
        descInput.placeholder = 'Deskripsi...';
        descInput.style.cssText = 'border:2px solid #9B59B6;padding:6px 8px;border-radius:3px;font-size:12px;font-family:inherit;min-height:60px;resize:vertical';
        
        const colorInput = document.createElement('input');
        colorInput.type = 'color';
        colorInput.className = 'pt-edit-color';
        colorInput.value = color;
        colorInput.style.cssText = 'border:2px solid #9B59B6;padding:2px;border-radius:3px;height:32px;cursor:pointer;width:60px';
        
        // Create wrapper for Name + Color in one row
        const nameColorWrapper = document.createElement('div');
        nameColorWrapper.style.cssText = 'display:grid;grid-template-columns:1fr 60px;gap:8px;align-items:flex-start';
        nameColorWrapper.appendChild(nameInput);
        nameColorWrapper.appendChild(colorInput);
        
        editContainer.appendChild(nameColorWrapper);
        editContainer.appendChild(descInput);
        editContainer.appendChild(depositInput);
        
        // Replace details display with edit container
        detailsDiv.replaceWith(editContainer);
        console.log('Replaced detailsDiv with editContainer');
        
        // Update buttons
        const editBtn = item.querySelector('.btn-edit-pt');
        const deleteBtn = item.querySelector('.btn-delete-pt');
        
        console.log('Found buttons - edit:', !!editBtn, 'delete:', !!deleteBtn);
        
        if (editBtn) {
            editBtn.textContent = '✓';
            editBtn.style.cssText = 'padding:4px 8px;font-size:11px;background:#2ECC71;color:white;border:none;cursor:pointer';
            editBtn.onclick = function() { savePaymentTermEdit(id); };
        }
        
        if (deleteBtn) {
            deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px;background:#E74C3C;color:white;border:none;cursor:pointer';
            deleteBtn.onclick = function() { cancelPaymentTermEdit(id); };
        }
        
        // Focus name input
        nameInput.focus();
        nameInput.select();
        console.log('editPaymentTerm completed successfully');
    } catch (err) {
        console.error('Error in editPaymentTerm:', err);
        showToast('✕ Error: ' + err.message, 'error');
    }
}

async function savePaymentTermEdit(id) {
    try {
        console.log('savePaymentTermEdit called for id:', id);
        
        const item = document.querySelector(`.pt-item[data-id="${id}"]`);
        if (!item) {
            console.error('Item not found');
            showToast('✕ Error: Item tidak ditemukan', 'error');
            return;
        }
        
        const container = item.querySelector('.pt-edit-container');
        if (!container) {
            console.error('Edit container not found');
            showToast('✕ Error: Edit mode tidak aktif', 'error');
            return;
        }
        
        const nameInput = container.querySelector('.pt-edit-name');
        const depositInput = container.querySelector('.pt-edit-deposit');
        const descInput = container.querySelector('.pt-edit-desc');
        const colorInput = container.querySelector('.pt-edit-color');
        
        const newName = nameInput.value.trim();
        const newDeposit = parseInt(depositInput.value.trim());
        const newDesc = descInput.value.trim();
        const newColor = colorInput.value;
        
        console.log('New values:', { newName, newDeposit, newDesc, newColor });
        
        if (!newName) {
            showToast('✕ Nama tidak boleh kosong', 'error');
            nameInput.focus();
            return;
        }
        if (isNaN(newDeposit) || newDeposit < 0 || newDeposit > 100) {
            showToast('✕ Deposit harus berupa angka antara 0-100', 'error');
            depositInput.focus();
            return;
        }
        
        // Update data attributes
        item.dataset.name = newName;
        item.dataset.deposit = newDeposit;
        item.dataset.desc = newDesc;
        item.dataset.color = newColor;
        
        console.log('Data attributes updated');
        
        // Recreate the entire item HTML to restore clean state
        const newItem = document.createElement('div');
        newItem.className = 'pt-item';
        newItem.dataset.id = id;
        newItem.dataset.name = newName;
        newItem.dataset.deposit = newDeposit;
        newItem.dataset.desc = newDesc;
        newItem.dataset.color = newColor;
        newItem.style.cssText = 'margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px;border:1px solid var(--border);display:grid;grid-template-columns:auto 1fr auto auto auto;gap:12px;align-items:center';
        
        newItem.innerHTML = `
            <div style="width:20px;height:20px;border-radius:3px;background:${escapeHtml(newColor)};border:1px solid rgba(0,0,0,0.1)"></div>
            <div style="flex:1">
                <div style="font-size:13px;font-weight:600">${escapeHtml(newName)}</div>
                <div style="font-size:11px;color:var(--text3);margin-top:2px">DP: ${escapeHtml(newDeposit)}%</div>
            </div>
            <div style="font-size:12px;color:var(--text2);min-width:200px">${escapeHtml(newDesc) || '—'}</div>
            <button class="btn bs bsm btn-edit-pt" data-id="${escapeHtml(id)}" style="padding:4px 8px;font-size:11px;cursor:pointer">✏️</button>
            <button class="btn bs bsm btn-delete-pt" data-id="${escapeHtml(id)}" style="padding:4px 8px;font-size:11px;cursor:pointer">✕</button>
        `;
        
        // Replace entire item
        item.replaceWith(newItem);
        
        console.log('Item replaced with clean state');
        console.log('savePaymentTermEdit completed');
        
        // Auto-save langsung setelah edit
        await savePaymentTerms();
    } catch (err) {
        console.error('Error in savePaymentTermEdit:', err);
        console.error('Stack:', err.stack);
        showToast('✕ Error: ' + err.message, 'error');
    }
}

function cancelPaymentTermEdit(id) {
    const item = document.querySelector(`.pt-item[data-id="${id}"]`);
    if (!item) return;
    
    const container = item.querySelector('.pt-edit-container');
    if (!container) return;
    
    const name = item.dataset.name;
    const deposit = item.dataset.deposit;
    const desc = item.dataset.desc || '';
    
    // Create new details display div
    const newDetailsDiv = document.createElement('div');
    newDetailsDiv.style.cssText = 'flex:1';
    newDetailsDiv.innerHTML = `
        <div style="font-size:13px;font-weight:600">${escapeHtml(name)}</div>
        <div style="font-size:11px;color:var(--text3);margin-top:2px">DP: ${escapeHtml(deposit)}%</div>
    `;
    
    // Replace edit container with details display
    container.replaceWith(newDetailsDiv);
    
    // Restore buttons
    const editBtn = item.querySelector('.btn-edit-pt');
    const deleteBtn = item.querySelector('.btn-delete-pt');
    
    if (editBtn) {
        editBtn.textContent = '✏️';
        editBtn.style.cssText = 'padding:4px 8px;font-size:11px;cursor:pointer';
        editBtn.onclick = function() { editPaymentTerm(id); };
    }
    
    if (deleteBtn) {
        deleteBtn.style.cssText = 'padding:4px 8px;font-size:11px;cursor:pointer';
        deleteBtn.onclick = function() { deletePaymentTerm(id); };
    }
}

async function deletePaymentTerm(id) {
    if (!confirm('Yakin ingin menghapus syarat pembayaran ini?')) return;
    
    const item = document.querySelector(`.pt-item[data-id="${id}"]`);
    if (item) {
        item.remove();
        await savePaymentTerms();
    }
}

async function addPaymentTerm() {
    const nameInput = document.getElementById('new-pt-name');
    const depositInput = document.getElementById('new-pt-deposit');
    const descInput = document.getElementById('new-pt-desc');
    const colorInput = document.getElementById('new-pt-color');
    
    const name = nameInput.value.trim();
    const deposit = depositInput.value.trim();
    const desc = descInput.value.trim();
    const color = colorInput.value || '#9B59B6';
    
    if (!name) {
        showToast('✕ Nama tidak boleh kosong', 'error');
        return;
    }
    if (!deposit || isNaN(deposit)) {
        showToast('✕ Deposit harus berupa angka', 'error');
        return;
    }
    
    // Generate unique ID
    const id = 'pt_' + Date.now();
    
    // Create new item element
    const newItem = document.createElement('div');
    newItem.className = 'pt-item';
    newItem.dataset.id = id;
    newItem.dataset.name = name;
    newItem.dataset.deposit = deposit;
    newItem.dataset.desc = desc;
    newItem.dataset.color = color;
    newItem.style.cssText = 'margin-bottom:8px;padding:10px;background:#fafafa;border-radius:4px;border:1px solid var(--border);display:grid;grid-template-columns:auto 1fr auto auto auto;gap:12px;align-items:center';
    
    newItem.innerHTML = `
        <div style="width:20px;height:20px;border-radius:3px;background:${escapeHtml(color)};border:1px solid rgba(0,0,0,0.1)"></div>
        <div style="flex:1">
            <div style="font-size:13px;font-weight:600">${escapeHtml(name)}</div>
            <div style="font-size:11px;color:var(--text3);margin-top:2px">DP: ${escapeHtml(deposit)}%</div>
        </div>
        <div style="font-size:12px;color:var(--text2);min-width:200px">${escapeHtml(desc) || '—'}</div>
        <button class="btn bs bsm btn-edit-pt" data-id="${escapeHtml(id)}" style="padding:4px 8px;font-size:11px;cursor:pointer">✏️</button>
        <button class="btn bs bsm btn-delete-pt" data-id="${escapeHtml(id)}" style="padding:4px 8px;font-size:11px;cursor:pointer">✕</button>
    `;
    
    document.getElementById('pt-items').appendChild(newItem);
    
    // Clear form
    nameInput.value = '';
    depositInput.value = '';
    descInput.value = '';
    colorInput.value = '#9B59B6';
    
    // Auto-save setelah tambah
    await savePaymentTerms();
}

function resetPaymentTerms() {
    if (!confirm('Yakin ingin reset ke default? Semua perubahan akan hilang.')) return;
    
    // Reload page to restore defaults
    location.reload();
}

// Initialize Payment Terms - DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - Initializing pengaturan page');
    console.log('DB_SETTINGS:', DB_SETTINGS);
    console.log('GRAD:', GRAD);
    console.log('PT:', PT);
    
    // Initialize Overhead total display
    updateOHTotal();
    
    // Render tabel Biaya Cetak (JANGAN panggil renderPengaturan() dari app-pages.js
    // karena ia mengakses elemen ov-oh yang tidak ada di halaman ini)
    if (typeof renderCetakTable === 'function') {
        renderCetakTable();
    }
    
    // Event delegation for package items container
    const pkgItemsContainer = document.getElementById('grad-pkg-items');
    if (pkgItemsContainer) {
        pkgItemsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-edit-price')) {
                const id = e.target.dataset.id;
                if (id) {
                    editGradPkg(id);
                }
            } else if (e.target.classList.contains('btn-delete')) {
                const id = e.target.dataset.id;
                if (id) {
                    deleteGradPkg(id);
                }
            }
        });
    }
    
    // Event delegation for payment terms items container
    const ptItemsContainer = document.getElementById('pt-items');
    if (ptItemsContainer) {
        ptItemsContainer.addEventListener('click', function(e) {
            console.log('PT Container clicked:', e.target.className, e.target.dataset);
            try {
                if (e.target.classList.contains('btn-edit-pt')) {
                    const id = e.target.dataset.id;
                    console.log('Edit PT clicked, id:', id);
                    if (id) {
                        editPaymentTerm(id);
                    }
                } else if (e.target.classList.contains('btn-delete-pt')) {
                    const id = e.target.dataset.id;
                    console.log('Delete PT clicked, id:', id);
                    if (id) {
                        deletePaymentTerm(id);
                    }
                }
            } catch (err) {
                console.error('Error handling PT button click:', err);
                showToast('✕ Error: ' + err.message, 'error');
            }
        });
    }
    
    // Render graduation preview
    if (document.getElementById('grad-grid')) {
        console.log('Rendering graduation preview...');
        if (typeof renderGraduation === 'function') {
            renderGraduation();
        } else {
            console.error('renderGraduation function not found');
        }
    }

    // Render tabel Biaya Cetak — dipanggil terakhir setelah semua script ready
    if (typeof renderCetakTable === 'function') {
        renderCetakTable();
    }

    // ── Init Bonus & Fasilitas ditangani di script Bonus & Fasilitas sendiri
});
</script>

<script>
// ============================================================
// BONUS & FASILITAS CRUD — pengaturan.php
// API: /api/bonus-fasilitas.php
// ============================================================

const BF_API = '/api/bonus-fasilitas.php';
let bfCurrentPkg  = 'fullservice';
let bfData        = {};    // cache { fullservice:[], graduation:[], alacarte:[] }
let bfEditingId   = null;  // id item yang sedang diedit

const BF_PKG_META = {
  fullservice: {
    label : '📚 Full Service',
    desc  : 'Berlaku untuk semua sub-paket Full Service (Handy Book A4+, Minimal Book SQ, Large Book B4).',
    color : 'var(--success)',
  },
  graduation: {
    label : '🎓 Graduation',
    desc  : 'Berlaku untuk semua paket Graduation (Photo & Video, Video Only, Photo Only, dll.).',
    color : 'var(--info, #2A6B8A)',
  },
  alacarte: {
    label : '🛒 À La Carte',
    desc  : 'Berlaku untuk semua sub-paket À La Carte (E-Book, Edit+Desain+Cetak, Foto Only, dll.).',
    color : '#8A5F1A',
  },
};

// ── Tab switcher ─────────────────────────────────────────
function bfSwitchTab(pkg, btn) {
  bfCurrentPkg = pkg;

  // Update tab button styles
  document.querySelectorAll('.bf-tab-btn').forEach(b => {
    const isActive = b === btn;
    b.style.color       = isActive ? BF_PKG_META[pkg].color : 'var(--text2)';
    b.style.borderBottom = isActive ? `2px solid ${BF_PKG_META[pkg].color}` : '2px solid transparent';
    b.style.background  = isActive ? 'var(--surface2)' : 'none';
  });

  // Description
  const descEl = document.getElementById('bf-tab-desc');
  if (descEl) descEl.textContent = BF_PKG_META[pkg]?.desc || '';

  // Update Kategori Options
  bfUpdateKategoriOptions(pkg);

  bfLoadItems(pkg);
}

let bfCurrentKategori = 'all';

function bfUpdateKategoriOptions(pkg) {
  const sel = document.getElementById('bf-kategori-select');
  if (!sel) return;
  
  let html = '<option value="all">Berlaku Semua (All)</option>';
  
  if (pkg === 'fullservice') {
    html += `
      <option value="fs-handy">Handy Book A4+</option>
      <option value="fs-minimal">Minimal Book SQ</option>
      <option value="fs-large">Large Book B4</option>
    `;
  } else if (pkg === 'alacarte') {
    html += `
      <option value="ac-ebook">E-Book Package</option>
      <option value="ac-editcetak">Edit+Desain+Cetak</option>
      <option value="ac-fotohalf">Foto Only (½ Hari)</option>
      <option value="ac-fotofull">Foto Only (Full Day)</option>
      <option value="ac-videod">Drone Video</option>
      <option value="ac-videodoc">Docudrama Video</option>
      <option value="ac-desain">Desain Only</option>
      <option value="ac-cetakonly">Cetak Only</option>
    `;
  } else if (pkg === 'graduation') {
    if (typeof GRAD !== 'undefined' && GRAD.packages) {
      GRAD.packages.forEach(p => {
        html += `<option value="${p.id}">${p.name}</option>`;
      });
    }
  }
  
  sel.innerHTML = html;
  sel.value = 'all';
  bfCurrentKategori = 'all';
}

function bfFilterKategori() {
  const sel = document.getElementById('bf-kategori-select');
  if (sel) {
    bfCurrentKategori = sel.value;
    bfRenderList(bfCurrentPkg);
  }
}

// ── Load from API ─────────────────────────────────────────
async function bfLoadItems(pkg) {
  pkg = pkg || bfCurrentPkg;
  bfCurrentPkg = pkg;

  const listEl = document.getElementById('bf-list');
  if (listEl) listEl.innerHTML = '<div style="color:var(--text3);font-size:13px;text-align:center;padding:20px 0">⏳ Memuat...</div>';

  // Set tab desc immediately
  const descEl = document.getElementById('bf-tab-desc');
  if (descEl) descEl.textContent = BF_PKG_META[pkg]?.desc || '';

  try {
    const res  = await fetch(`${BF_API}?type=${pkg}`);
    const json = await res.json();
    if (!json.success) throw new Error(json.error || 'Gagal memuat');

    bfData[pkg] = json.data || [];
    bfRenderList(pkg);
  } catch (e) {
    if (listEl) listEl.innerHTML = `<div style="color:var(--danger);font-size:13px;padding:12px">✕ Error: ${e.message}</div>`;
  }
}

// ── Render list ───────────────────────────────────────────
function bfRenderList(pkg) {
  const listEl = document.getElementById('bf-list');
  if (!listEl) return;

  const allItems = bfData[pkg] || [];
  const items = allItems.filter(i => i.kategori === bfCurrentKategori || (bfCurrentKategori !== 'all' && i.kategori === 'all'));

  if (!items.length) {
    listEl.innerHTML = `
      <div style="text-align:center;padding:24px 0;color:var(--text3)">
        <div style="font-size:28px;margin-bottom:8px">🎁</div>
        <div style="font-size:13px">Belum ada bonus untuk kategori ini.<br>Tambahkan menggunakan form di bawah.</div>
      </div>`;
    return;
  }

  const color = BF_PKG_META[pkg]?.color || 'var(--accent)';

  listEl.innerHTML = items.map((item, idx) => `
    <div id="bf-item-${item.id}" style="
      display:grid;grid-template-columns:28px 1fr auto auto;gap:10px;align-items:center;
      padding:10px 12px;margin-bottom:8px;border-radius:8px;
      background:var(--surface);border:1px solid var(--border);
      transition:box-shadow .15s">

      <!-- Rank badge -->
      <div style="width:24px;height:24px;border-radius:50%;background:${color};color:white;
        font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0">
        ${idx + 1}
      </div>

      <!-- Content (view mode) -->
      <div id="bf-content-${item.id}">
        <div style="font-size:13px;font-weight:600;color:var(--text)">
          ${escHtml(item.label)} 
          ${item.kategori === 'all' && bfCurrentKategori !== 'all' ? '<span style="font-size:10px;background:var(--surface2);color:var(--text3);padding:2px 6px;border-radius:4px;margin-left:4px">All</span>' : ''}
        </div>
        <div style="font-size:12px;color:var(--text2);margin-top:2px">${escHtml(item.detail)}</div>
      </div>

      <!-- Edit button -->
      <button class="btn bs bsm" onclick="bfStartEdit(${item.id})"
        style="padding:5px 10px;font-size:12px" title="Edit">✏️</button>

      <!-- Delete button -->
      <button class="btn bs bsm" onclick="bfDeleteItem(${item.id}, '${escHtml(item.label)}')"
        style="padding:5px 10px;font-size:12px;color:var(--danger);border-color:var(--danger)" title="Hapus">✕</button>
    </div>
  `).join('');
}

// ── Start inline edit ─────────────────────────────────────
function bfStartEdit(id) {
  // Close previous edit if any
  if (bfEditingId && bfEditingId !== id) bfCancelEdit(bfEditingId);
  bfEditingId = id;

  const items = bfData[bfCurrentPkg] || [];
  const item  = items.find(i => i.id === id);
  if (!item) return;

  const contentEl = document.getElementById(`bf-content-${id}`);
  if (!contentEl) return;

  contentEl.innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 2fr auto;gap:6px;align-items:center">
      <input id="bf-edit-label-${id}" type="text" value="${escHtml(item.label)}"
        placeholder="Judul bonus"
        style="font-size:13px;padding:5px 8px;border:1px solid var(--accent);border-radius:5px">
      <input id="bf-edit-detail-${id}" type="text" value="${escHtml(item.detail)}"
        placeholder="Deskripsi detail"
        style="font-size:13px;padding:5px 8px;border:1px solid var(--accent);border-radius:5px"
        onkeydown="if(event.key==='Enter'){bfSaveEdit(${id});event.preventDefault()}">
      <div style="display:flex;gap:5px">
        <button class="btn bp bsm" onclick="bfSaveEdit(${id})" style="padding:5px 10px;font-size:12px">✓</button>
        <button class="btn bs bsm" onclick="bfCancelEdit(${id})" style="padding:5px 10px;font-size:12px">✕</button>
      </div>
    </div>`;

  document.getElementById(`bf-edit-label-${id}`)?.focus();
}

// ── Cancel edit ───────────────────────────────────────────
function bfCancelEdit(id) {
  bfEditingId = null;
  const items = bfData[bfCurrentPkg] || [];
  const item  = items.find(i => i.id === id);
  const contentEl = document.getElementById(`bf-content-${id}`);
  if (!item || !contentEl) return;

  contentEl.innerHTML = `
    <div style="font-size:13px;font-weight:600;color:var(--text)">${escHtml(item.label)}</div>
    <div style="font-size:12px;color:var(--text2);margin-top:2px">${escHtml(item.detail)}</div>`;
}

// ── Save edit ─────────────────────────────────────────────
async function bfSaveEdit(id) {
  const label  = document.getElementById(`bf-edit-label-${id}`)?.value.trim();
  const detail = document.getElementById(`bf-edit-detail-${id}`)?.value.trim();
  if (!label || !detail) { showToast('Judul dan deskripsi wajib diisi.', 'warning'); return; }

  try {
    const res  = await fetch(BF_API, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ action:'update', id, label, detail })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.error || 'Gagal menyimpan');

    // Update cache
    const items = bfData[bfCurrentPkg] || [];
    const item  = items.find(i => i.id === id);
    if (item) { item.label = label; item.detail = detail; }

    bfEditingId = null;
    bfRenderList(bfCurrentPkg);
    showToast('✓ Bonus berhasil diperbarui.', 'success');
  } catch (e) {
    showToast(`✕ ${e.message}`, 'error');
  }
}

// ── Delete ────────────────────────────────────────────────
async function bfDeleteItem(id, label) {
  if (!confirm(`Hapus bonus "${label}"?\nBonus ini akan hilang dari kalkulator dan PDF.`)) return;

  try {
    const res  = await fetch(BF_API, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ action:'delete', id })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.error || 'Gagal menghapus');

    // Remove from cache
    bfData[bfCurrentPkg] = (bfData[bfCurrentPkg] || []).filter(i => i.id !== id);
    bfRenderList(bfCurrentPkg);
    showToast(`✓ Bonus "${label}" dihapus.`, 'success');
  } catch (e) {
    showToast(`✕ ${e.message}`, 'error');
  }
}

// ── Add new item ──────────────────────────────────────────
async function bfAddItem() {
  const label    = document.getElementById('bf-new-label')?.value.trim();
  const detail   = document.getElementById('bf-new-detail')?.value.trim();
  const statusEl = document.getElementById('bf-add-status');

  if (!label || !detail) {
    showToast('Judul dan deskripsi wajib diisi.', 'warning');
    return;
  }

  if (statusEl) { statusEl.textContent = '⏳ Menyimpan...'; statusEl.style.display = ''; statusEl.style.color = 'var(--text3)'; }

  try {
    const currentItems = bfData[bfCurrentPkg] || [];
    const newOrder = currentItems.length + 1;

    const res  = await fetch(BF_API, {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ action:'create', package_type: bfCurrentPkg, kategori: bfCurrentKategori, label, detail, display_order: newOrder })
    });
    const json = await res.json();
    if (!json.success) throw new Error(json.error || 'Gagal menambah');

    // Clear inputs
    document.getElementById('bf-new-label').value  = '';
    document.getElementById('bf-new-detail').value = '';

    if (statusEl) { statusEl.textContent = '✓ Ditambahkan!'; statusEl.style.color = 'var(--success)'; }
    setTimeout(() => { if (statusEl) statusEl.style.display = 'none'; }, 2500);

    // Reload list to get fresh data (including new id)
    await bfLoadItems(bfCurrentPkg);
    showToast(`✓ Bonus "${label}" ditambahkan ke paket ${BF_PKG_META[bfCurrentPkg]?.label}.`, 'success');
  } catch (e) {
    if (statusEl) { statusEl.textContent = `✕ ${e.message}`; statusEl.style.color = 'var(--danger)'; statusEl.style.display = ''; }
    showToast(`✕ ${e.message}`, 'error');
  }
}

// ── Helper ────────────────────────────────────────────────
function escHtml(str) {
  return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Init on DOM ready ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
  bfLoadItems('fullservice');
});
</script>

<script>
// Override renderPengaturan() — versi app-pages.js crash karena akses 'ov-oh'
// yang tidak ada di pengaturan.php. Override ini aman dan hanya render yang perlu.
function renderPengaturan() {
    if (typeof renderCetakTable === 'function') renderCetakTable();
    if (typeof renderGraduation === 'function') renderGraduation();
}

// Override renderGraduation() agar bekerja di pengaturan.php
// (app-pages.js versinya crash karena coba akses elemen halaman lain)
function renderGraduation() {
    // Pastikan GRAD terisi penuh
    if (!GRAD || !Array.isArray(GRAD.packages)) return;

    // 1. Isi dropdown gc-pkg (ada di halaman ini) dan k-grad-pkg (mungkin tidak ada)
    const opts = '<option value="">— Pilih paket —</option>' +
        GRAD.packages.map(p => `<option value="${p.id}">${p.name} — ${fmt(p.price)}</option>`).join('');
    ['gc-pkg', 'k-grad-pkg'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = opts;
    });

    // 2. Render preview kartu Paket Utama
    const gridEl = document.getElementById('grad-grid');
    if (gridEl) {
        gridEl.innerHTML = GRAD.packages.map((p, pi) => {
            const isFeat = p.color === 'feat';
            const isAcc  = p.color === 'acc';
            return `<div class="pkc ${isFeat ? 'feat-grad' : ''} ${isAcc ? 'feat' : ''}">
                <div class="pnum grad-accent" style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em">${isFeat ? '⭐ Complete Package' : isAcc ? '📸 Unggulan' : ''}</div>
                <div class="pname">${p.name}</div>
                <div class="pdesc" style="font-size:11px">${p.desc || ''}</div>
                <div class="pp grad" style="margin-top:10px">
                    <input class="edi" type="number" value="${p.price}"
                        onchange="GRAD.packages[${pi}].price=parseInt(this.value)||${p.price};saveGrad()"
                        style="width:110px;font-size:18px;font-family:var(--font-d);color:var(--grad);text-align:right;border-color:var(--grad-light)">
                </div>
                <div class="pps">flat per event • incl. transport jabodetabek</div>
            </div>`;
        }).join('');
    }

    // 3. Render tabel Add-on (preview bawah)
    const addonEl = document.getElementById('grad-addon');
    if (addonEl) {
        addonEl.innerHTML = GRAD.addons.map((a, ai) =>
            `<tr><td>${a.name}</td><td>
                <input class="edi" type="number" value="${a.price}"
                    onchange="GRAD.addons[${ai}].price=parseInt(this.value)"
                    style="width:90px;text-align:right">
            </td></tr>`
        ).join('');
    }

    // 4. Render tabel Cetak Foto (preview bawah)
    const cetakEl = document.getElementById('grad-cetak');
    if (cetakEl) {
        cetakEl.innerHTML = GRAD.cetak.map((c, ci) =>
            `<tr><td>${c.name}</td><td>
                <input class="edi" type="number" value="${c.price}"
                    onchange="GRAD.cetak[${ci}].price=parseInt(this.value)"
                    style="width:70px;text-align:right">
                <span style="font-size:11px;color:var(--text3)">/lembar</span>
            </td></tr>`
        ).join('');
    }

    // 5. Render Kalkulator Graduation add-ons & cetak
    const gcAddonsEl = document.getElementById('gc-addons');
    if (gcAddonsEl) {
        gcAddonsEl.innerHTML = `
            <div class="ct">Add-on Graduation</div>
            ${GRAD.addons.map(a => `
                <div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px">
                    <input type="checkbox" id="gchk-${a.id}" onchange="gcUpdate()">
                    <span style="flex:1">${a.name}</span>
                    <span style="color:var(--text3);font-size:12px">${fmt(a.price)}</span>
                </div>`).join('')}
            <div class="ct mt10">Cetak Foto Tambahan</div>
            ${GRAD.cetak.map(c => `
                <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px">
                    <input type="checkbox" id="gchk-${c.id}" onchange="gcUpdate()">
                    <span style="flex:1">${c.name}</span>
                    <span style="color:var(--text3);font-size:12px">${fmt(c.price)}/lembar</span>
                    <input type="number" id="gqty-${c.id}" value="1" min="1" style="width:50px;display:none" oninput="gcUpdate()">
                </div>`).join('')}`;
        // attach cetak qty toggle
        GRAD.cetak.forEach(c => {
            const chk = document.getElementById('gchk-' + c.id);
            if (chk) chk.addEventListener('change', () => {
                const qi = document.getElementById('gqty-' + c.id);
                if (qi) qi.style.display = chk.checked ? '' : 'none';
            });
        });
    }

    gcUpdate();
}
</script>

<!-- ============================================================ -->
<!-- CETAK CRUD FUNCTIONS — Biaya Cetak Renjana Offset            -->
<!-- ============================================================ -->
<script>
// Override renderCetakTable dari app.js dengan versi CRUD
function renderCetakTable() {
    if (!CETAK_BASE || CETAK_BASE.length === 0) {
        CETAK_BASE = DEF_CETAK_BASE.map(r => ({...r, pages: {...r.pages}}));
    }
    const r = CETAK_BASE[curCetakRange];
    if (!r) return;
    const wrap = document.getElementById('cetak-table-wrap');
    if (!wrap) return;

    const pages = Object.keys(r.pages).map(Number).sort((a, b) => a - b);

    const rows = pages.map(p => `
        <tr>
            <td style="font-weight:500;white-space:nowrap">${p} hal</td>
            <td>
                <div style="display:flex;align-items:center;gap:5px">
                    <input type="number" value="${r.pages[p]}" min="0" step="1000"
                        style="width:105px;text-align:right;font-size:13px"
                        onchange="CETAK_BASE[${curCetakRange}].pages[${p}]=parseInt(this.value)||${r.pages[p]};autoSaveCetakBase()"
                        title="Edit harga untuk ${p} halaman">
                    <span style="font-size:11px;color:var(--text3)">/buku</span>
                </div>
            </td>
            <td style="font-size:11px;color:var(--text3)">
                Handy <b>${fmt(Math.round(r.pages[p] * CETAK_F.handy))}</b> ·
                Minimal <b>${fmt(Math.round(r.pages[p] * CETAK_F.minimal))}</b> ·
                Large <b>${fmt(Math.round(r.pages[p] * CETAK_F.large))}</b>
            </td>
            <td>
                <button class="btn bs bsm" onclick="deleteHalamanTier(${p})"
                    title="Hapus tier ${p} halaman"
                    style="padding:2px 8px;font-size:12px;color:var(--danger);border-color:var(--danger)">✕</button>
            </td>
        </tr>`).join('');

    wrap.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <div style="font-size:12px;color:var(--accent);font-weight:600">${r.label}</div>
            <div style="font-size:11px;color:var(--text3)">${pages.length} tier halaman</div>
        </div>
        <table style="width:100%">
            <thead><tr>
                <th>Halaman</th>
                <th>Harga Base/Buku</th>
                <th>Efektif per Paket (setelah faktor)</th>
                <th style="width:40px"></th>
            </tr></thead>
            <tbody>${rows || '<tr><td colspan="4" style="text-align:center;color:var(--text3);padding:16px">Belum ada tier halaman. Tambah di bawah.</td></tr>'}</tbody>
        </table>`;
}

// Hapus satu tier halaman dari range aktif
function deleteHalamanTier(pages) {
    const r = CETAK_BASE[curCetakRange];
    if (!r || !confirm(`Hapus tier ${pages} halaman dari ${r.label}?`)) return;
    delete r.pages[pages];
    renderCetakTable();
    autoSaveCetakBase();
}

// Tambah tier halaman baru ke range aktif
function addHalamanTier() {
    const pagesEl = document.getElementById('new-hal-pages');
    const priceEl = document.getElementById('new-hal-price');
    const pages = parseInt(pagesEl?.value);
    const price = parseInt(priceEl?.value);
    if (!pages || pages < 1) { showToast('Masukkan jumlah halaman yang valid', 'error'); pagesEl?.focus(); return; }
    if (!price || price < 0) { showToast('Masukkan harga yang valid', 'error'); priceEl?.focus(); return; }
    const r = CETAK_BASE[curCetakRange];
    if (!r) return;
    r.pages[pages] = price;
    pagesEl.value = '';
    priceEl.value = '';
    renderCetakTable();
    autoSaveCetakBase();
}

// Hapus range dari CETAK_BASE
function deleteCetakRange(e, idx) {
    e.stopPropagation();
    const r = CETAK_BASE[idx];
    if (!r || !confirm(`Hapus range "${r.label}" secara permanen?\n\nSemua data harga dalam range ini akan hilang.`)) return;
    CETAK_BASE.splice(idx, 1);
    curCetakRange = Math.max(0, Math.min(curCetakRange, CETAK_BASE.length - 1));
    rebuildRangeBtns();
    renderCetakTable();
    autoSaveCetakBase();
}

// Rebuild range buttons di DOM setelah add/delete
function rebuildRangeBtns() {
    const btnsEl = document.getElementById('cetak-range-btns');
    if (!btnsEl) return;
    btnsEl.innerHTML = CETAK_BASE.map((r, i) => `
        <button class="btn ${i === curCetakRange ? 'bp' : 'bs'} bsm cetak-range-btn"
            onclick="setCetakRange(${i}, this)" style="font-size:11px;position:relative">
            ${r.label}
            <span class="cetak-del-btn" onclick="deleteCetakRange(event,${i})"
                title="Hapus range"
                style="margin-left:5px;color:${i===curCetakRange?'rgba(255,255,255,.7)':'var(--danger)'};font-weight:700;font-size:13px;line-height:1">×</span>
        </button>`).join('');
}

// Modal: Tambah Range Baru
function showAddRangeModal() {
    const el = document.getElementById('modal-add-range');
    if (el) { el.style.display = 'flex'; }
}
function closeAddRangeModal() {
    const el = document.getElementById('modal-add-range');
    if (el) { el.style.display = 'none'; }
}
function confirmAddRange() {
    const lo = parseInt(document.getElementById('new-range-lo')?.value);
    const hi = parseInt(document.getElementById('new-range-hi')?.value);
    const labelInp = document.getElementById('new-range-label')?.value?.trim();
    if (!lo || !hi || lo >= hi) {
        showToast('Nilai min/max tidak valid (min harus < max)', 'error'); return;
    }
    // Cek overlap dengan range yang ada
    const overlap = CETAK_BASE.some(r => !(hi < r.lo || lo > r.hi));
    if (overlap) {
        showToast('Range ini tumpang tindih dengan range yang sudah ada', 'error'); return;
    }
    const label = labelInp || `${lo}–${hi} siswa`;
    // Default pages dari range terdekat
    const closest = CETAK_BASE.reduce((a, b) => Math.abs((a.lo+a.hi)/2 - (lo+hi)/2) < Math.abs((b.lo+b.hi)/2 - (lo+hi)/2) ? a : b);
    const newRange = { lo, hi, label, pages: {...closest.pages} };
    CETAK_BASE.push(newRange);
    CETAK_BASE.sort((a, b) => a.lo - b.lo);
    curCetakRange = CETAK_BASE.findIndex(r => r.lo === lo && r.hi === hi);
    closeAddRangeModal();
    document.getElementById('new-range-lo').value = '';
    document.getElementById('new-range-hi').value = '';
    document.getElementById('new-range-label').value = '';
    rebuildRangeBtns();
    renderCetakTable();
    autoSaveCetakBase();
    showToast(`Range "${label}" berhasil ditambahkan`, 'success');
}

// Close modal on backdrop click
document.getElementById('modal-add-range')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddRangeModal();
});

async function saveCetakBase() {
    const success = await updateMasterData('cetak_base', CETAK_BASE);
    if (success) {
        showToast('✓ Biaya cetak base berhasil tersimpan', 'success');
        if (typeof kalcUpdate === 'function') kalcUpdate();
    } else {
        showToast('✕ Gagal menyimpan biaya cetak base', 'error');
    }
}

async function autoSaveCetakBase() {
    try {
        const success = await updateMasterData('cetak_base', CETAK_BASE);
        if (success) {
            const s = document.getElementById('ov-cetak-status');
            if (s) { 
                s.style.display = 'inline'; 
                s.textContent = '✓ Tersimpan otomatis'; 
                setTimeout(() => s.style.display = 'none', 2500); 
            }
        }
    } catch(e) { console.warn('Auto-save cetak failed:', e); }
}

// Override resetCetakBase agar juga rebuild buttons
function resetCetakBase() {
    if (!confirm('Reset semua data Biaya Cetak ke default Renjana Offset?\n\nPerubahan manual akan hilang.')) return;
    CETAK_BASE = DEF_CETAK_BASE.map(r => ({...r, pages: {...r.pages}}));
    curCetakRange = 0;
    if (typeof rebuildRangeBtns === 'function') rebuildRangeBtns();
    renderCetakTable();
    autoSaveCetakBase();
}

// Override setCetakRange agar update button colors dengan benar
function setCetakRange(idx, btn) {
    curCetakRange = idx;
    document.querySelectorAll('#cetak-range-btns button').forEach((b, i) => {
        b.className = `btn ${i === idx ? 'bp' : 'bs'} bsm cetak-range-btn`;
        const delSpan = b.querySelector('.cetak-del-btn');
        if (delSpan) delSpan.style.color = i === idx ? 'rgba(255,255,255,.7)' : 'var(--danger)';
    });
    renderCetakTable();
}

// ============================================================
// ADD-ON MANAGEMENT FUNCTIONS
// ============================================================

async function showAddAddonModal(category) {
    const modalHtml = `
        <div id="addon-modal" style="display:flex;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center">
            <div style="background:white;padding:30px;border-radius:10px;max-width:600px;width:90%;max-height:80vh;overflow-y:auto">
                <div style="font-size:18px;font-weight:600;margin-bottom:20px">Tambah Add-on Baru — ${category.toUpperCase()}</div>
                <div class="form-row" style="margin-bottom:15px">
                    <label style="font-weight:600;margin-bottom:5px">Nama Item</label>
                    <input type="text" id="new-addon-name" placeholder="Nama add-on baru" style="border:1px solid var(--border);padding:8px;border-radius:3px;width:100%;box-sizing:border-box;font-size:13px">
                </div>
                <div id="addon-tiers-section" style="margin-bottom:15px">
                    <label style="font-weight:600;margin-bottom:8px;display:block">Tier Harga</label>
                    <div id="addon-tiers-list"></div>
                    <button type="button" class="btn bs bsm" onclick="addAddonTierRow()" style="margin-top:8px">+ Tambah Tier</button>
                </div>
                <div style="display:flex;gap:10px;margin-top:20px">
                    <button class="btn bp" onclick="confirmAddAddon('${category}')" style="flex:1">Tambah Item</button>
                    <button class="btn bs" onclick="closeAddonModal()" style="flex:1">Batal</button>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Render default tiers based on category
    const tiersList = document.getElementById('addon-tiers-list');
    tiersList.innerHTML = '';
    
    const defaultTiers = {
        finishing: [[25, 75, 50000], [76, 150, 35000], [151, 9999, 30000]],
        kertas: [[25, 50, 450], [51, 100, 250], [101, 150, 200], [151, 9999, 150]],
        halaman: [[25, 50, 3000], [51, 100, 2000], [101, 150, 1300], [151, 9999, 1000]],
        pkg1: [[25, 50, 45000], [51, 100, 40000], [101, 150, 35000], [151, 200, 30000], [201, 9999, 25000]],
        pkg2: [[25, 50, 200000], [51, 100, 170000], [101, 150, 130000], [151, 200, 120000], [201, 9999, 110000]]
    };
    
    const tiers = defaultTiers[category] || [[0, 9999, 0]];
    tiers.forEach((tier, idx) => {
        const tierRow = document.createElement('div');
        tierRow.style.cssText = 'display:grid;grid-template-columns:80px 80px 120px auto;gap:8px;margin-bottom:8px;align-items:center';
        tierRow.innerHTML = `
            <input type="number" class="tier-min" value="${tier[0]}" placeholder="Min" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
            <input type="number" class="tier-max" value="${tier[1]}" placeholder="Max" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
            <input type="number" class="tier-price" value="${tier[2]}" placeholder="Harga" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
            <button type="button" class="btn bs bsm" onclick="this.parentElement.remove()" style="padding:4px 8px;font-size:11px">✕</button>
        `;
        tiersList.appendChild(tierRow);
    });
}

function closeAddonModal() {
    const modal = document.getElementById('addon-modal');
    if (modal) modal.remove();
}

function addAddonTierRow() {
    const tiersList = document.getElementById('addon-tiers-list');
    const tierRow = document.createElement('div');
    tierRow.style.cssText = 'display:grid;grid-template-columns:80px 80px 120px auto;gap:8px;margin-bottom:8px;align-items:center';
    tierRow.innerHTML = `
        <input type="number" class="tier-min" placeholder="Min" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
        <input type="number" class="tier-max" placeholder="Max" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
        <input type="number" class="tier-price" placeholder="Harga" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
        <button type="button" class="btn bs bsm" onclick="this.parentElement.remove()" style="padding:4px 8px;font-size:11px">✕</button>
    `;
    tiersList.appendChild(tierRow);
}

async function confirmAddAddon(category) {
    const name = document.getElementById('new-addon-name')?.value?.trim();
    if (!name) {
        showToast('Masukkan nama item', 'error');
        return;
    }
    
    const tierRows = document.querySelectorAll('#addon-tiers-list > div');
    const tiers = [];
    
    tierRows.forEach(row => {
        const min = parseInt(row.querySelector('.tier-min')?.value || 0);
        const max = parseInt(row.querySelector('.tier-max')?.value || 9999);
        const price = parseInt(row.querySelector('.tier-price')?.value || 0);
        if (price > 0) tiers.push([min, max, price]);
    });
    
    if (tiers.length === 0) {
        showToast('Tambahkan minimal 1 tier dengan harga > 0', 'error');
        return;
    }
    
    // Send to server
    const newAddon = {
        id: 'addon_' + Date.now(),
        name: name,
        type: category === 'video' ? 'flat_video' : 'flat',
        tiers: tiers,
        price: tiers[0][2] // For flat_video
    };
    
    try {
        const response = await fetch('/api/addons.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                operation: 'add_addon',
                category: category,
                name: name,
                type: category === 'video' ? 'flat_video' : 'flat',
                tiers: tiers,
                price: tiers[0][2]
            })
        });
        
        console.log('Add Addon Response - Status:', response.status);
        const text = await response.text();
        console.log('Add Addon Response Body:', text.substring(0, 500));
        
        if (!text || text.trim() === '') {
            showToast('✕ Server tidak memberi respons. Status: ' + response.status, 'error');
            return;
        }
        
        const data = JSON.parse(text);
        if (data.success) {
            showToast(`✓ Add-on "${name}" ditambahkan`, 'success');
            closeAddonModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('✕ ' + (data.message || 'Gagal menambahkan add-on'), 'error');
        }
    } catch (err) {
        console.error('Add Addon Error:', err);
        showToast('✕ Error: ' + err.message, 'error');
    }
}

async function editAddonItem(category, id) {
    // Find the item element to get all data
    const item = document.querySelector(`.addon-item[data-category="${category}"][data-id="${id}"]`);
    if (!item) return;
    
    const itemName = item.querySelector('div:nth-child(1)').textContent;
    const priceElements = item.querySelectorAll('.addon-price-display');
    
    // Build modal HTML
    let tierHtml = '';
    if (category === 'video') {
        // Video: single flat price
        const price = parseInt(priceElements[0].textContent.replace(/\D/g, '') || 0);
        tierHtml = `
            <div class="form-row" style="margin-bottom:15px">
                <label style="font-weight:600;margin-bottom:5px">Harga (Rp)</label>
                <input type="number" id="edit-video-price" value="${price}" style="border:1px solid var(--border);padding:8px;border-radius:3px;width:100%;box-sizing:border-box;font-size:13px">
            </div>
        `;
    } else {
        // Tiered: multiple price ranges
        const tiers = [];
        priceElements.forEach(el => {
            const row = el.parentElement;
            const spans = row.querySelectorAll('span');
            if (spans.length >= 3) {
                const min = parseInt(spans[0].textContent);
                const max = parseInt(spans[2].textContent);
                const price = parseInt(el.textContent.replace(/\D/g, ''));
                tiers.push({min, max, price});
            }
        });
        
        tierHtml = `
            <div style="margin-bottom:15px">
                <label style="font-weight:600;margin-bottom:8px;display:block">Tier Harga</label>
                <div id="edit-addon-tiers">
                    ${tiers.map((tier, idx) => `
                        <div style="display:grid;grid-template-columns:80px 80px 120px auto;gap:8px;margin-bottom:8px;align-items:center" class="tier-row">
                            <input type="number" class="tier-min" value="${tier.min}" placeholder="Min" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
                            <input type="number" class="tier-max" value="${tier.max}" placeholder="Max" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
                            <input type="number" class="tier-price" value="${tier.price}" placeholder="Harga" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
                            <button type="button" class="btn bs bsm" onclick="this.parentElement.remove()" style="padding:4px 8px;font-size:11px">✕</button>
                        </div>
                    `).join('')}
                </div>
                <button type="button" class="btn bs bsm" onclick="addEditAddonTierRow()" style="margin-top:8px;font-size:11px">+ Tambah Tier</button>
            </div>
        `;
    }
    
    const modalHtml = `
        <div id="edit-addon-modal" style="display:flex;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center">
            <div style="background:white;padding:30px;border-radius:10px;max-width:600px;width:90%;max-height:80vh;overflow-y:auto">
                <div style="font-size:18px;font-weight:600;margin-bottom:20px">
                    Edit Add-on: <span style="color:var(--accent)">${itemName}</span>
                </div>
                
                <div class="form-row" style="margin-bottom:15px">
                    <label style="font-weight:600;margin-bottom:5px">Nama Item</label>
                    <input type="text" id="edit-addon-name" value="${itemName}" style="border:1px solid var(--border);padding:8px;border-radius:3px;width:100%;box-sizing:border-box;font-size:13px">
                </div>
                
                ${tierHtml}
                
                <div style="display:flex;gap:10px;margin-top:20px">
                    <button class="btn bp" onclick="saveEditAddonItem('${category}', '${id}')" style="flex:1;font-weight:600">💾 Simpan Perubahan</button>
                    <button class="btn bs" onclick="closeEditAddonModal()" style="flex:1">Batal</button>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeEditAddonModal() {
    const modal = document.getElementById('edit-addon-modal');
    if (modal) modal.remove();
}

function addEditAddonTierRow() {
    const tiersList = document.getElementById('edit-addon-tiers');
    if (!tiersList) return;
    
    const tierRow = document.createElement('div');
    tierRow.style.cssText = 'display:grid;grid-template-columns:80px 80px 120px auto;gap:8px;margin-bottom:8px;align-items:center';
    tierRow.className = 'tier-row';
    tierRow.innerHTML = `
        <input type="number" class="tier-min" placeholder="Min" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
        <input type="number" class="tier-max" placeholder="Max" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
        <input type="number" class="tier-price" placeholder="Harga" style="border:1px solid var(--border);padding:6px;border-radius:3px;font-size:12px">
        <button type="button" class="btn bs bsm" onclick="this.parentElement.remove()" style="padding:4px 8px;font-size:11px">✕</button>
    `;
    tiersList.appendChild(tierRow);
}

async function saveEditAddonItem(category, id) {
    const newName = document.getElementById('edit-addon-name')?.value?.trim();
    if (!newName) {
        showToast('Nama item tidak boleh kosong', 'error');
        return;
    }
    
    let updatedAddon = {
        operation: 'update_addon',
        id: id,
        name: newName,
        type: category === 'video' ? 'flat_video' : 'flat',
        category: category
    };
    
    if (category === 'video') {
        // Video: flat price
        const price = parseInt(document.getElementById('edit-video-price')?.value || 0);
        if (price <= 0) {
            showToast('Harga harus lebih dari 0', 'error');
            return;
        }
        updatedAddon.price = price;
    } else {
        // Tiered addons
        const tierRows = document.querySelectorAll('.tier-row');
        const tiers = [];
        
        tierRows.forEach(row => {
            const min = parseInt(row.querySelector('.tier-min')?.value || 0);
            const max = parseInt(row.querySelector('.tier-max')?.value || 9999);
            const price = parseInt(row.querySelector('.tier-price')?.value || 0);
            
            if (price > 0) {
                tiers.push([min, max, price]);
            }
        });
        
        if (tiers.length === 0) {
            showToast('Tambahkan minimal 1 tier dengan harga > 0', 'error');
            return;
        }
        
        updatedAddon.tiers = tiers;
    }
    
    try {
        console.log('Sending update request:', JSON.stringify(updatedAddon));
        const response = await fetch('/api/addons.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(updatedAddon)
        });
        
        console.log('Response received - Status:', response.status, 'OK:', response.ok);
        console.log('Response Headers:', {
            'Content-Type': response.headers.get('Content-Type'),
            'Content-Length': response.headers.get('Content-Length')
        });
        
        const text = await response.text();
        console.log('Response Body Length:', text.length);
        console.log('API Response Text:', text.substring(0, 500));
        
        if (!text || text.trim() === '') {
            console.error('Empty response body!');
            showToast('✕ Server tidak memberi respons (response kosong). Status: ' + response.status, 'error');
            return;
        }
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Raw Response (full):', text);
            showToast('✕ Respons tidak valid dari server: ' + text.substring(0, 100), 'error');
            return;
        }
        
        if (data.success) {
            showToast(`✓ "${newName}" berhasil diperbarui`, 'success');
            closeEditAddonModal();
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('✕ ' + (data.message || data.error || 'Gagal menyimpan'), 'error');
        }
    } catch (err) {
        console.error('Fetch Error:', err);
        console.error('Error Stack:', err.stack);
        showToast('✕ Fetch Error: ' + err.message, 'error');
    }
}

async function deleteAddonItem(category, id) {
    if (!confirm('Hapus add-on ini?')) return;
    
    try {
        const response = await fetch('/api/addons.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                operation: 'delete_addon',
                category: category,
                id: id
            })
        });
        
        console.log('Delete Addon Response - Status:', response.status);
        const text = await response.text();
        console.log('Delete Addon Response Body:', text.substring(0, 500));
        
        if (!text || text.trim() === '') {
            showToast('✕ Server tidak memberi respons. Status: ' + response.status, 'error');
            return;
        }
        
        const data = JSON.parse(text);
        if (data.success) {
            showToast('✓ Add-on dihapus', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('✕ ' + (data.message || 'Gagal menghapus add-on'), 'error');
        }
    } catch (err) {
        console.error('Delete Addon Error:', err);
        showToast('✕ Error menghapus: ' + err.message, 'error');
    }
}

function editAddonPrice(priceElement) {
    // Enable inline price editing
    const currentPrice = priceElement.textContent.replace(/\D/g, '');
    const input = document.createElement('input');
    input.type = 'number';
    input.value = currentPrice;
    input.style.cssText = 'border:2px solid var(--accent);padding:4px;border-radius:3px;width:150px;font-size:12px;font-weight:600';
    
    input.onblur = () => {
        const newPrice = parseInt(input.value || 0);
        priceElement.textContent = 'Rp ' + newPrice.toLocaleString('id-ID');
        input.replaceWith(priceElement);
    };
    
    input.onkeypress = (e) => {
        if (e.key === 'Enter') input.blur();
    };
    
    priceElement.replaceWith(input);
    input.focus();
    input.select();
}

async function saveAllAddons() {
    showToast('💾 Menyimpan semua perubahan add-on...', 'info');
    
    // Collect all addon data from DOM
    const categories = ['finishing', 'kertas', 'halaman', 'video', 'pkg1', 'pkg2'];
    const allAddonsData = {};
    
    for (let cat of categories) {
        allAddonsData[cat] = collectAddonsByCategory(cat);
    }
    
    try {
        // Prepare payload with all category data
        const payload = {
            operation: 'update_all_categories',
            data: allAddonsData
        };
        
        const response = await fetch('/api/addons.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(payload)
        });
        
        const text = await response.text();
        const data = JSON.parse(text);
        if (data.success) {
            const statusEl = document.getElementById('addon-status');
            if (statusEl) {
                statusEl.style.display = 'inline';
                setTimeout(() => statusEl.style.display = 'none', 3000);
            }
            showToast('✓ Semua perubahan add-on tersimpan ke database', 'success');
        } else {
            showToast('✕ ' + (data.message || 'Gagal menyimpan add-on'), 'error');
        }
    } catch (err) {
        console.error('Error:', err);
        showToast('✕ Error: ' + err.message, 'error');
    }
}

function collectAddonsByCategory(category) {
    const items = document.querySelectorAll(`.addon-item[data-category="${category}"]`);
    const result = [];
    
    items.forEach(item => {
        const priceElements = item.querySelectorAll('.addon-price-display');
        if (priceElements.length === 0) return;
        
        // Single price (for video)
        if (category === 'video') {
            const price = parseInt(priceElements[0].textContent.replace(/\D/g, '') || 0);
            result.push({
                id: item.dataset.id,
                name: item.querySelector('div:nth-child(1)').textContent,
                type: 'flat_video',
                price: price
            });
        } else {
            // Multiple tiers
            const tiers = [];
            priceElements.forEach(el => {
                const row = el.parentElement;
                const spans = row.querySelectorAll('span');
                if (spans.length >= 3) {
                    const min = parseInt(spans[0].textContent);
                    const max = parseInt(spans[2].textContent);
                    const price = parseInt(el.textContent.replace(/\D/g, ''));
                    tiers.push([min, max, price]);
                }
            });
            
            if (tiers.length > 0) {
                result.push({
                    id: item.dataset.id,
                    name: item.querySelector('div:nth-child(1)').textContent,
                    type: 'flat',
                    tiers: tiers
                });
            }
        }
    });
    
    return result;
}

async function resetAddons() {
    if (!confirm('Reset semua add-on ke default?\n\nPerubahan manual akan hilang.')) return;
    
    showToast('💾 Mereset add-on ke default...', 'info');
    
    try {
        const response = await fetch('/api/addons.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify({
                operation: 'reset_addons'
            })
        });
        
        const text = await response.text();
        const data = JSON.parse(text);
        if (data.success) {
            showToast('✓ Add-on direset ke default', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('✕ ' + (data.message || 'Gagal mereset add-on'), 'error');
        }
    } catch (err) {
        console.error('Error:', err);
        showToast('✕ Error: ' + err.message, 'error');
    }
}
</script>
</body>
</html>
