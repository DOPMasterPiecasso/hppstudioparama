<?php
// includes/sidebar.php
$currentPage = $currentPage ?? '';
$isAdmin   = $_SESSION['user']['role'] === 'admin';
$isManager = in_array($_SESSION['user']['role'], ['admin','manager']);
$isStaff   = $_SESSION['user']['role'] === 'staff';

function navItem(string $id, string $ico, string $label, string $current, bool $show = true, string $extra = ''): string {
    if (!$show) return '';
    $active = $current === $id ? 'active' . ($id === 'graduation' ? '-grad' : '') : '';
    return "<a href=\"/pages/$id.php\" class=\"nav-item $active $extra\"><span class=\"ico\">$ico</span>$label</a>";
}
?>
<aside class="sidebar" id="sidebar">
  <div class="sb-logo">
    <div class="brand">Parama Studio</div>
    <div class="sub"><?= $isManager ? 'Dashboard Harga 2026' : 'Kalkulator Harga' ?></div>
  </div>
  <div class="sb-user">
    <div class="sb-user-info">
      <div class="sb-user-name"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
      <div class="sb-user-role"><?= htmlspecialchars($_SESSION['user']['role_label'] ?? ucfirst($_SESSION['user']['role'])) ?></div>
    </div>
    <a href="/auth/logout.php" class="sb-logout">Keluar</a>
  </div>
  <nav class="nav">
    <?php if ($isManager): ?>
    <div class="nav-sec">Overview</div>
    <?= navItem('ringkasan','◈','Ringkasan',$currentPage,$isManager) ?>
    <div class="nav-sec">Buku Tahunan</div>
    <?= navItem('fullservice','◉','Full Service',$currentPage,$isManager) ?>
    <?= navItem('alacarte','◎','À La Carte',$currentPage,$isManager) ?>
    <?= navItem('addon','◫','Add-on',$currentPage,$isManager) ?>
    <?php endif; ?>
    <div class="nav-sec">Tools</div>
    <?= navItem('kalkulator','⊟','Kalkulator',$currentPage,true) ?>
    <?= navItem('proyek','◉','Penawaran & Proyek',$currentPage,true) ?>
    <?php if ($isManager): ?>
    <?= navItem('analisis','⊕','Analisis Margin',$currentPage,$isManager) ?>
    <div class="nav-sec">Pengaturan</div>
    <?= navItem('pengaturan','⊙','Edit Semua Harga',$currentPage,$isManager) ?>
    <?php endif; ?>
    <?php if ($isManager): ?>
    <div class="nav-sec">Management</div>
    <a class="nav-item <?= $currentPage==='users'?'active':'' ?>" href="/pages/users.php"><span class="ico">👤</span>Management User</a>
    <?php endif; ?>
  </nav>
  <div class="sb-footer"><div class="ver">v3.0 PHP — 2026</div></div>
</aside>
<script>
function toggleMobileMenu(){const sb=document.getElementById('sidebar');const ov=document.getElementById('sidebar-overlay');if(sb)sb.classList.toggle('active');if(ov)ov.classList.toggle('active')}
function closeMobileMenu(){const sb=document.getElementById('sidebar');const ov=document.getElementById('sidebar-overlay');if(sb)sb.classList.remove('active');if(ov)ov.classList.remove('active')}
// Close mobile menu when clicking navigation items
document.querySelectorAll('.nav-item').forEach(item=>{item.addEventListener('click',()=>{if(window.innerWidth<=768)closeMobileMenu()})})
</script>
