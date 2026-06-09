<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
$user = requireAuth();
$pageTitle = 'Add-on — Parama Studio';
$currentPage = 'addon';
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
<!-- ADD-ON -->
<div class="page active" id="page-addon">
  <div class="ph"><div class="pt">Pricelist Add-on</div><div class="ps">Layanan tambahan — bisa diedit langsung di tabel</div></div>
  <div class="note mb16">💡 Klik angka mana saja di tabel untuk edit harga.</div>
  <div class="g2">
    <div>
      <div class="sec">Finishing &amp; Binding</div>
      <div class="card mb16"><div class="tw"><table><thead id="hdr-finishing"><tr><th>Jenis</th></tr></thead><tbody id="adn-finishing"></tbody></table></div></div>
      <div class="sec">Upgrade Kertas</div>
      <div class="card mb16"><div class="tw"><table><thead id="hdr-kertas"><tr><th>Jenis</th></tr></thead><tbody id="adn-kertas"></tbody></table></div></div>
      <div class="sec">Halaman Tambahan (per halaman)</div>
      <div class="card mb16"><div class="tw"><table><thead id="hdr-halaman"><tr><th>Jumlah Order</th><th>Harga/Hal</th></tr></thead><tbody id="adn-halaman"></tbody></table></div></div>
      <div class="sec">Video</div>
      <div class="card"><div class="tw"><table><thead id="hdr-video"><tr><th>Jenis</th><th>Durasi</th><th>Harga</th></tr></thead><tbody id="adn-video"></tbody></table></div></div>
    </div>
    <div>
      <div class="sec">Packaging — Slide &amp; Standard</div>
      <div class="card mb16"><div class="tw"><table><thead id="hdr-pkg1"><tr><th>Tipe</th></tr></thead><tbody id="adn-pkg1"></tbody></table></div></div>
      <div class="sec">Packaging — Custom Box</div>
      <div class="card"><div class="tw"><table><thead id="hdr-pkg2"><tr><th>Tipe</th></tr></thead><tbody id="adn-pkg2"></tbody></table></div></div>
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
<script src="/assets/js/app.js?v=1.9"></script>
<script src="/assets/js/app-pages.js?v=1.9"></script>
<script src="/assets/js/app-proyek.js?v=1.9"></script>
</body>
</html>
