<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
$user = requireAuth();
$pageTitle = 'Full Service — Parama Studio';
$currentPage = 'fullservice';
include __DIR__ . '/../includes/header.php';
?>
<body>
<!-- Mobile Navbar Fixed -->
<div class="mobile-navbar">
  <button class="mobile-menu-btn" onclick="toggleMobileMenu()">☰</button>
  <div class="mobile-navbar-title">Parama Studio</div>
</div>
<div class="sidebar-overlay" id="sidebar-overlay" onclick="closeMobileMenu()"></div>

<div class="app">
<?php include __DIR__ . '/../includes/sidebar.php'; ?>
<main class="main">
<!-- FULL SERVICE -->
<div class="page active" id="page-fullservice">
  <div class="ph"><div class="pt">Pricelist Full Service</div><div class="ps">Termasuk photography, fashion stylist, editing, desain, e-book, cetak &amp; shipping</div></div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px">
    <div class="note" style="margin:0">💡 <b>Edit langsung:</b> klik angka Harga/Buku atau Maks Halaman untuk mengubahnya. Perubahan langsung tersimpan otomatis.</div>
    <div class="note warn" style="margin:0">⚠️ <b>Logika:</b> margin % per buku turun untuk qty besar — <b>normal</b>. Yang penting <b>Total Gross per proyek</b> cukup nutup OH.</div>
  </div>
  <div class="fs-controls mb16">
    <div class="fs-paket-section">
      <span style="font-size:13px;color:var(--text2);display:block;margin-bottom:8px">Paket:</span>
      <div class="fs-paket-buttons">
        <button class="btn bp bsm" onclick="setPkg('handy')" id="btn-handy">Handy Book A4+</button>
        <button class="btn bs bsm" onclick="setPkg('minimal')" id="btn-minimal">Minimal Book SQ</button>
        <button class="btn bs bsm" onclick="setPkg('large')" id="btn-large">Large Book B4</button>
      </div>
    </div>
    <div class="fs-oh-section" style="gap:6px;align-items:center;font-size:12px;color:var(--text3)">
      <span>OH per proyek (asumsi</span>
      <input type="number" id="fs-nproyek" value="4" min="1" max="20" style="width:42px;padding:3px 6px;font-size:12px" oninput="renderFS()">
      <span>proyek aktif/bln)</span>
      <button class="btn bs bsm" onclick="saveFS()" style="margin-left:12px;font-size:12px">💾 Simpan Semua</button>
    </div>
  </div>
  <div class="card">
    <div class="edit-hint mb12">✏️ Klik angka untuk edit. Perubahan otomatis tersimpan. Saran Harga = harga minimum agar proyek tetap profitable.</div>
    <div class="tw"><table id="fs-table">
      <thead><tr><th>Range Siswa</th><th>Harga/Buku</th><th>Maks Halaman</th><th>Est. Cetak/Buku</th><th>Gross/Buku</th><th>Gross %</th><th>Total Gross</th><th>Net/Proyek*</th><th>Rating</th><th>Saran Harga</th></tr></thead>
      <tbody id="fs-body"></tbody>
    </table></div>
    <div style="font-size:11px;color:var(--text3);margin-top:8px">*Net/Proyek = Total Gross dikurangi alokasi overhead.</div>
  </div>
  <div class="card mt16" id="fs-insight-box" style="border-left:3px solid var(--accent)">
    <div class="ct">Insight &amp; Anomali</div><div id="fs-insights"></div>
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
<script src="/assets/js/app.js?v=1.2"></script>
<script src="/assets/js/app-pages.js?v=1.2"></script>
<script src="/assets/js/app-proyek.js?v=1.2"></script>
</body>
</html>
