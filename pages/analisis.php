<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
// Analisis is usually manager/admin only, let's enforce it
$user = requireRole('admin', 'manager');
$pageTitle = 'Analisis Margin — Parama Studio';
$currentPage = 'analisis';
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
<!-- ANALISIS -->
<div class="page active" id="page-analisis">
  <div class="ph"><div class="pt">Analisis Margin</div><div class="ps">Evaluasi profitabilitas &amp; rekomendasi penyesuaian harga</div></div>
  <div class="card mb20">
    <div class="tn">
      <button class="tb active" onclick="setAnTab('margin',this)">Margin per Segmen</button>
      <button class="tb" onclick="setAnTab('kompetitor',this)">vs Kompetitor</button>
      <button class="tb" onclick="setAnTab('rekomendasi',this)">Rekomendasi</button>
    </div>
    <div class="tp active" id="an-margin">
      <div class="fb mb16">
        <span style="font-size:13px;color:var(--text2)">Paket:</span>
        <button class="btn bp bsm" onclick="setAnPkg('handy')" id="abtn-handy">Handy</button>
        <button class="btn bs bsm" onclick="setAnPkg('minimal')" id="abtn-minimal">Minimal</button>
        <button class="btn bs bsm" onclick="setAnPkg('large')" id="abtn-large">Large</button>
      </div>
      <div class="tw"><table><thead><tr><th>Range Siswa</th><th>Harga Jual</th><th>Est. Cetak</th><th>Overhead/Buku*</th><th>Gross Margin</th><th>Net Margin</th><th>Status</th></tr></thead><tbody id="an-body"></tbody></table></div>
    </div>
    <div class="tp" id="an-kompetitor">
      <div class="tw"><table><thead><tr><th>Range Siswa</th><th>Parama (Handy)</th><th>Parama (Large)</th><th>Est. Kompetitor</th><th>Posisi</th><th>Catatan</th></tr></thead>
      <tbody>
        <tr><td>30–50</td><td>Rp465.000</td><td>Rp480.000</td><td>Rp400rb–520rb</td><td><span class="badge bsuc">Kompetitif</span></td><td>Margin tinggi, harga wajar</td></tr>
        <tr><td>51–100</td><td>Rp370–415rb</td><td>Rp405–430rb</td><td>Rp350rb–450rb</td><td><span class="badge bsuc">Kompetitif</span></td><td>Sweet spot pasar</td></tr>
        <tr><td>101–150</td><td>Rp320–350rb</td><td>Rp350–365rb</td><td>Rp300rb–380rb</td><td><span class="badge bsuc">Kompetitif</span></td><td>Range paling sering order</td></tr>
        <tr><td>151–250</td><td>Rp235–315rb</td><td>Rp265–330rb</td><td>Rp220rb–320rb</td><td><span class="badge binf">Sedikit premium</span></td><td>Wajar karena include e-book</td></tr>
        <tr><td>251–375</td><td>Rp185–240rb</td><td>Rp215–255rb</td><td>Rp170rb–240rb</td><td><span class="badge bwar">Perlu dicek</span></td><td>Margin mulai menipis</td></tr>
        <tr><td>376–500</td><td>Rp140–190rb</td><td>Rp155–205rb</td><td>Rp130rb–190rb</td><td><span class="badge bwar">Margin tipis</span></td><td>Risiko jika banyak revisi</td></tr>
      </tbody></table></div>
    </div>
    <div class="tp" id="an-rekomendasi">
      <div class="tw"><table><thead><tr><th>Range / Isu</th><th>Kondisi Saat Ini</th><th>Rekomendasi</th><th>Prioritas</th></tr></thead>
      <tbody>
        <tr><td><b>426–475 siswa (Handy)</b></td><td>Rp165–175rb — margin ~41–45%</td><td>Naikkan ke <b>Rp185–195rb</b></td><td><span class="badge bdan">Tinggi</span></td></tr>
        <tr><td><b>476–500 siswa (Handy)</b></td><td>Rp150rb — margin ~36%</td><td>Naikkan ke <b>Rp165–170rb</b></td><td><span class="badge bdan">Tinggi</span></td></tr>
        <tr><td><b>Halaman tambahan</b></td><td>Tidak ada harga eksplisit</td><td>Gunakan pricelist add-on halaman</td><td><span class="badge bwar">Sedang</span></td></tr>
        <tr><td><b>Paket À La Carte</b></td><td>Belum ada pricing resmi</td><td>Formalkan paket E-Book &amp; Edit+Cetak</td><td><span class="badge bwar">Sedang</span></td></tr>
        <tr><td><b>BEP rendah</b></td><td>BEP hanya 1.6 proyek/bulan</td><td>Sudah bagus — fokus ke upsell add-on</td><td><span class="badge bsuc">Positif</span></td></tr>
      </tbody></table></div>
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
</body>
</html>
