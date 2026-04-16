<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
$user = requireAuth();
$pageTitle = 'Kalkulator Harga — Parama Studio';
$currentPage = 'kalkulator';
include __DIR__ . '/../includes/header.php';
?>
<body>
<!-- Mobile Navbar Fixed -->
<div class="mobile-navbar">
  <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
  <div class="mobile-navbar-title">Parama Studio</div>
</div>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeMobileMenu()"></div>
<!-- tetst -->
<div class="app">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<main class="main">
<!-- KALKULATOR -->
<div class="page active" id="page-kalkulator">
  <div class="ph"><div class="pt">Kalkulator Harga</div><div class="ps">Hitung penawaran — full service, à la carte, graduation, + add-on</div></div>
  <div class="g2">
    <div style="display:flex;flex-direction:column;gap:13px">
      <div class="card">
        <div class="ct">Parameter Utama</div>
        <div style="display:flex;flex-direction:column;gap:9px">
          <div class="fg"><label class="fl">Kategori</label>
            <select id="k-cat" onchange="kCatChange()">
              <option value="bukutahunan">Buku Tahunan</option>
              <option value="graduation">Graduation</option>
            </select>
          </div>
          <div class="fg" id="k-type-row"><label class="fl">Tipe Paket</label>
            <select id="k-type" onchange="kalcUpdate()">
              <optgroup label="Full Service">
                <option value="fs-handy">Full Service — Handy Book A4+</option>
                <option value="fs-minimal">Full Service — Minimal Book SQ</option>
                <option value="fs-large">Full Service — Large Book B4</option>
              </optgroup>
              <optgroup label="À La Carte">
                <option value="ac-ebook">À La Carte — E-Book Package</option>
                <option value="ac-editcetak">À La Carte — Edit+Desain+Cetak</option>
                <option value="ac-fotohalf">À La Carte — Foto Only (½ hari)</option>
                <option value="ac-fotofull">À La Carte — Foto Only (Full day)</option>
                <option value="ac-videod">À La Carte — Drone Video</option>
                <option value="ac-videodoc">À La Carte — Docudrama Video</option>
                <option value="ac-desain">À La Carte — Desain Only</option>
                <option value="ac-cetakonly">À La Carte — Cetak Only</option>
              </optgroup>
            </select>
          </div>
          <div class="fg" id="k-grad-row" style="display:none"><label class="fl">Paket Graduation</label><select id="k-grad-pkg" onchange="kalcUpdate()"></select></div>
          <div class="fg" id="k-siswa-row"><label class="fl">Jumlah Siswa / Order</label><input type="number" id="k-siswa" value="100" min="25" max="500" oninput="kalcUpdate()"></div>
          <div class="fg" id="k-hal-row"><label class="fl">Jumlah Halaman</label>
            <input type="number" id="k-hal" value="60" min="30" max="160" readonly style="background:var(--surface2);cursor:default">
            <span class="fh" id="k-hal-info">Otomatis dari pricelist</span>
          </div>
        </div>
      </div>
      <div class="card"><div class="ct">Add-on (Opsional)</div><div style="max-height:300px;overflow-y:auto" id="k-addon-list"></div></div>
    </div>
    <div style="display:flex;flex-direction:column;gap:13px">
      <div class="card">
        <div class="ct">Hasil Kalkulasi</div>
        <div id="k-result"></div>
        <hr style="border:none;border-top:1px solid var(--border);margin:12px 0">
        <div class="rr tot"><span class="rl">Total Penawaran</span><span class="rv" id="k-total">—</span></div>
        <div id="k-total-sub" style="font-size:11px;color:var(--text3);margin-top:3px;text-align:right"></div>
        <div class="diskon-box">
          <div style="font-size:11px;font-weight:600;color:var(--accent);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">Negosiasi Harga</div>
          <div style="font-size:12px;font-weight:500;color:var(--text2);margin-bottom:6px">Potongan Harga</div>
          <div class="dbt" style="margin-bottom:8px">
            <button class="dkbtn active" data-type="none" onclick="setDkType('none',this)">Tidak Ada</button>
            <button class="dkbtn" data-type="persen" onclick="setDkType('persen',this)">Diskon %</button>
            <button class="dkbtn" data-type="nominal" onclick="setDkType('nominal',this)">Diskon Nominal</button>
            <button class="dkbtn" data-type="cashback" onclick="setDkType('cashback',this)">Cashback %</button>
          </div>
          <div id="dk-val-section" style="display:none;margin-bottom:10px">
            <div style="display:flex;align-items:center;gap:6px">
              <input type="number" id="dk-value" value="5" min="0" max="100" step="0.5" style="width:85px" oninput="applyDiskon();updateNominalFmt()">
              <span style="font-size:13px;color:var(--text3)" id="dk-unit">%</span>
              <span id="dk-nominal-fmt" style="font-size:12px;color:var(--text3)"></span>
            </div>
          </div>
          <div style="font-size:12px;font-weight:500;color:var(--text2);margin-bottom:6px;margin-top:4px">Bonus Produk <span style="font-size:11px;font-weight:400;color:var(--text3)">(opsional)</span></div>
          <div style="display:flex;gap:6px;margin-bottom:6px">
            <input type="text" id="bns-input" placeholder="contoh: +10 halaman gratis..." style="flex:1;font-size:12px;padding:5px 10px" onkeydown="if(event.key==='Enter'){addBonusTag();event.preventDefault()}">
            <input type="number" id="bns-nominal" placeholder="Rp nilai" style="width:100px;font-size:12px;padding:5px 8px" oninput="applyDiskon()">
            <button class="btn bs bsm" onclick="addBonusTag()" style="flex-shrink:0;padding:5px 10px">+</button>
          </div>
          <div style="display:flex;gap:5px;flex-wrap:wrap;min-height:24px" id="bns-tags"></div>
          <div id="dk-result" style="margin-top:10px"></div>
          <div id="dk-warn" class="dk-warn" style="display:none"></div>
        </div>
      </div>
<?php if($isManager || $isAdmin): ?>
      <div class="card" id="k-profit-card">
        <div class="ct">Estimasi Profitabilitas</div>
        <div id="k-profit"></div>
        <div id="k-verdict" style="margin-top:9px"></div>
      </div>
      <div class="note" id="k-note"></div>
<?php endif; ?>
      <div style="padding:12px;background:var(--surface2);border-radius:var(--r);border:1px dashed var(--border2)">
        <div style="font-size:11px;font-weight:600;color:var(--text2);margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em">💾 Simpan ke Daftar Penawaran</div>
        <div style="display:flex;gap:6px">
          <input type="text" id="k-save-nama" placeholder="Nama sekolah / klien..." style="flex:1;font-size:13px;padding:7px 10px">
          <button class="btn bp" style="flex-shrink:0;padding:7px 14px" onclick="saveKalcToPenawaran()">Simpan</button>
          <button id="btn-hapus-pw" class="btn bsm" style="flex-shrink:0;padding:7px 14px;color:var(--danger);display:none;background:var(--danger-bg);border:1px solid var(--danger)" onclick="deleteCurrentPenawaran()">Hapus</button>
        </div>
        <div id="k-save-msg" style="font-size:11px;margin-top:5px;display:none"></div>
      </div>
    </div>
  </div>
</div>
</main>
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
<script>const PHP_USER = <?= $jsUser ?>;</script>
<script src="/assets/js/app.js"></script>
<script src="/assets/js/app-pages.js"></script>
<script src="/assets/js/app-proyek.js"></script>
<script>
// Handle init specifically if there's an edit_id param
window.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit_id');
    if (editId) {
        // Need a slight delay to allow settings and penawaran to load
        setTimeout(() => {
            if (typeof editPenawaran === 'function') {
                editPenawaran(parseInt(editId));
            }
        }, 300);
    }
});
</script>
</body>
</html>
