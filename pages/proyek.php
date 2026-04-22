<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
$user = requireAuth();
$pageTitle = 'Penawaran & Proyek — Parama Studio';
$currentPage = 'proyek';
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
<!-- PROYEK -->
<div class="page active" id="page-proyek">
  <div class="ph"><div class="pt">Penawaran &amp; Proyek</div><div class="ps">Semua penawaran — update status kapanpun</div></div>
  <div id="pw-comparison" style="display:none;margin-bottom:16px"></div>
  <div class="mg" id="sim-metrics" style="grid-template-columns:repeat(5,1fr);margin-bottom:16px"></div>
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:14px">
    <div style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
      <select id="pw-bulan" onchange="renderProyek()" style="font-size:12px;padding:5px 9px;border-radius:8px;border:1px solid var(--border2);background:var(--surface)"></select>
      <select id="pw-filter" onchange="renderProyek()" style="font-size:12px;padding:5px 9px;border-radius:8px;border:1px solid var(--border2);background:var(--surface)">
        <option value="all">Semua Status</option>
        <option value="pending">🕐 Pending</option>
        <option value="nego">🔄 Nego</option>
        <option value="deal">✅ Deal</option>
        <option value="gagal">❌ Tidak Jadi</option>
      </select>
      <select id="pw-sort" onchange="renderProyek()" style="font-size:12px;padding:5px 9px;border-radius:8px;border:1px solid var(--border2);background:var(--surface)">
        <option value="newest">Terbaru dulu</option>
        <option value="oldest">Terlama dulu</option>
        <option value="highest">Harga tertinggi</option>
        <option value="lowest">Harga terendah</option>
      </select>
    </div>
  </div>
  <div id="pw-summary" style="margin-bottom:16px"></div>
  <div id="pw-list"></div>
  <div class="g2 mt16" id="proyek-breakdown" style="display:none">
    <div class="card"><div class="ct">Breakdown per Status</div><div id="sim-by-status"></div></div>
    <div class="card"><div class="ct">Breakdown per Paket</div><div id="sim-by-paket"></div></div>
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
