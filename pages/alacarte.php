<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
$user = requireAuth();
$pageTitle = 'Paket À La Carte — Parama Studio';
$currentPage = 'alacarte';
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
<!-- A LA CARTE -->
<div class="page active" id="page-alacarte">
  <div class="ph"><div class="pt">Paket À La Carte</div><div class="ps">Paket parsial untuk klien yang tidak ambil full service</div></div>
  <div class="note mb16">💡 Harga à la carte dihitung otomatis dari faktor % full service.</div>
  <div class="pkgrid" id="alc-grid"></div>
  <div class="mt16">
    <div class="sec">Tabel Perbandingan Paket</div>
    <div class="card"><div class="tw"><table><thead><tr><th>Paket</th><th>Foto</th><th>Editing</th><th>Desain</th><th>E-Book</th><th>Cetak</th><th>Harga (100 siswa)</th><th>Target Margin</th></tr></thead><tbody id="alc-cmp"></tbody></table></div>
    <div class="note warn mt10">⚠️ Paket Edit+Cetak &amp; Desain Only: wajib kasih SOP standar file foto ke klien.</div></div>
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
