<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
$user = requireAuth();
$pageTitle = 'Ringkasan — Parama Studio';
$currentPage = 'ringkasan';

// Determine user role
$isAdmin = $user['role'] === 'admin';
$isManager = $user['role'] === 'manager' || $isAdmin;

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
<!-- RINGKASAN -->
<div class="page active" id="page-ringkasan">
  <div class="ph"><div class="pt">Ringkasan</div><div class="ps">Overview bisnis Parama Studio</div></div>
  <div class="mg" id="metrics-ov"></div>
  <div class="g2 mb20">
    <div class="card"><div class="ct">Breakdown Overhead Bulanan</div><div id="oh-bars"></div></div>
    <div class="card"><div class="ct">Distribusi Komponen Full Service</div><div id="komp-bars"></div><div class="note mt10">Estimasi distribusi biaya → dasar harga à la carte.</div></div>
  </div>
  <div><div class="ct mb20">Skenario BEP Bulanan</div><div class="g3" id="bep-sc"></div></div>
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
<script>
const PHP_USER = <?= $jsUser ?>;
</script>
<script src="/assets/js/app.js?v=1.2"></script>
<script src="/assets/js/app-pages.js?v=1.2"></script>
<script src="/assets/js/app-proyek.js?v=1.2"></script>
<script>
// Debug mobile menu
setTimeout(() => {
  console.log('=== MOBILE MENU DEBUG ===');
  const btn = document.querySelector('.mobile-menu-btn');
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  
  if (btn) {
    const computed = window.getComputedStyle(btn);
    console.log('Button found:', {
      visibility: computed.visibility,
      opacity: computed.opacity,
      display: computed.display,
      zIndex: computed.zIndex,
      position: computed.position,
      top: computed.top,
      left: computed.left
    });
  } else {
    console.log('Button NOT found!');
  }
  
  console.log('Viewport width:', window.innerWidth);
  console.log('Media query should apply:', window.innerWidth <= 768);
  
  if (sidebar) {
    console.log('Sidebar found with id:', sidebar.id);
  } else {
    console.log('Sidebar NOT found!');
  }
  
  if (overlay) {
    console.log('Overlay found with id:', overlay.id);
  } else {
    console.log('Overlay NOT found!');
  }
}, 100);
</script>
</body>
</html>
