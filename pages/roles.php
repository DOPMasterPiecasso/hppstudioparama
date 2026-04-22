<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
$user = requireRole('admin','manager');
$pageTitle = 'Management Role — Parama Studio';
$currentPage = 'roles';
include __DIR__ . '/../includes/header.php';
$db = getDB();

// Get all roles from JSON
$roles = $db->getRoles();

// Count users per role
$allUsers = $db->getAllUsers();
$userCount = [];
foreach ($allUsers as $u) {
    $roleId = $u['role_id'];
    if (!isset($userCount[$roleId])) {
        $userCount[$roleId] = 0;
    }
    $userCount[$roleId]++;
}

// Add user_count to each role
foreach ($roles as &$r) {
    $r['user_count'] = $userCount[$r['id']] ?? 0;
}

$allFeatures = [
    'Dashboard Ringkasan',
    'Full Service / À La Carte / Add-on',
    'Graduation',
    'Kalkulator Harga',
    'Penawaran & Proyek',
    'Analisis Margin',
    'Edit Semua Harga',
    'Management User',
    'Management Role',
    'Download PDF'
];
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
<div class="page active" id="page-roles">
  <div class="ph">
    <div class="pt">Management Role</div>
    <div class="ps">Kelola role dan hak akses pengguna</div>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <div style="font-size:13px;color:var(--text3)"><?= count($roles) ?> role tersedia</div>
    <button class="btn bp" onclick="document.getElementById('modal-add').style.display='flex'">+ Tambah Role</button>
  </div>

  <div class="card">
    <div class="tw">
      <table>
        <thead><tr>
          <th>ID</th><th>Nama Role</th><th>Jumlah User</th><th>Permissions</th><th>Aksi</th>
        </tr></thead>
        <tbody>
        <?php foreach($roles as $r):
          $perms = json_decode($r['permissions'] ?? '[]', true) ?: [];
        ?>
          <tr>
            <td><?= $r['id'] ?></td>
            <td><b><?= ucfirst(htmlspecialchars($r['name'])) ?></b><br><span style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($r['label']??'') ?></span></td>
            <td><span class="badge binf"><?= $r['user_count'] ?> user</span></td>
            <td style="max-width:350px">
              <?php if(empty($perms)): ?>
                <span style="color:var(--text3);font-size:12px">—</span>
              <?php else: ?>
                <div style="display:flex;flex-wrap:wrap;gap:4px">
                <?php foreach($perms as $p): ?>
                  <span class="badge bsuc" style="font-weight:400;font-size:10px"><?= htmlspecialchars($p) ?></span>
                <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <button class="btn bs bsm" 
                data-id="<?= $r['id'] ?>"
                data-name="<?= htmlspecialchars($r['name'], ENT_QUOTES) ?>"
                data-label="<?= htmlspecialchars($r['label'] ?? '', ENT_QUOTES) ?>"
                data-perms="<?= htmlspecialchars(json_encode($perms), ENT_QUOTES) ?>"
                onclick="editRole(this.dataset.id, this.dataset.name, this.dataset.label, this.dataset.perms)">
                ✏ Edit
              </button>
              <?php if($r['name'] !== 'admin' && $r['name'] !== 'manager' && $r['user_count'] == 0): ?>
              <button class="btn bs bsm" style="color:var(--danger)" onclick="if(confirm('Hapus role <?= htmlspecialchars($r['name']) ?>?'))deleteRole(<?= $r['id'] ?>)">×</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</main>
</div>

<!-- Modal Add Role -->
<div id="modal-add" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-title">Tambah Role Baru</div>
    <form id="form-add" onsubmit="return submitAdd(event)">
      <div class="form-row"><label>Sistem Name (contoh: staff_magang)</label><input type="text" name="name" required placeholder="Gunakan huruf kecil tanpa spasi"></div>
      <div class="form-row"><label>Label Tampilan (contoh: Staff Magang)</label><input type="text" name="label" required></div>
      <div class="form-row"><label>Hak Akses (Permissions)</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;background:var(--surface2);padding:12px;border-radius:8px;border:1px solid var(--border)">
          <?php foreach($allFeatures as $f): ?>
            <label style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:400;color:var(--text);margin-bottom:0;cursor:pointer">
              <input type="checkbox" name="permissions[]" value="<?= htmlspecialchars($f) ?>"> <?= htmlspecialchars($f) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:16px">
        <button type="submit" class="btn bp" style="flex:1">Simpan</button>
        <button type="button" class="btn bs" onclick="document.getElementById('modal-add').style.display='none'">Batal</button>
      </div>
      <div id="add-msg" style="font-size:12px;margin-top:8px;display:none"></div>
    </form>
  </div>
</div>

<!-- Modal Edit Role -->
<div id="modal-edit" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-title">Edit Role</div>
    <form id="form-edit" onsubmit="return submitEdit(event)">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-row"><label>Sistem Name <span style="font-weight:400;color:var(--text3)">(tidak disarankan diubah)</span></label><input type="text" name="name" id="edit-name" required></div>
      <div class="form-row"><label>Label Tampilan</label><input type="text" name="label" id="edit-label" required></div>
      <div class="form-row"><label>Hak Akses (Permissions)</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;background:var(--surface2);padding:12px;border-radius:8px;border:1px solid var(--border)">
          <?php foreach($allFeatures as $f): ?>
            <label style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:400;color:var(--text);margin-bottom:0;cursor:pointer">
              <input type="checkbox" name="permissions[]" class="edit-perm" value="<?= htmlspecialchars($f) ?>"> <?= htmlspecialchars($f) ?>
            </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:16px">
        <button type="submit" class="btn bp" style="flex:1">Update</button>
        <button type="button" class="btn bs" onclick="document.getElementById('modal-edit').style.display='none'">Batal</button>
      </div>
      <div id="edit-msg" style="font-size:12px;margin-top:8px;display:none"></div>
    </form>
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
<script>const PHP_USER = <?= $jsUser ?>;</script>
<script src="/assets/js/app.js?v=1.2"></script>

<script>
const API = '/api/roles.php';

function editRole(id, name, label, permsJson){
  document.getElementById('edit-id').value = id;
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-label').value = label;
  
  let perms = [];
  try { perms = JSON.parse(permsJson); } catch(e) {}
  
  document.querySelectorAll('.edit-perm').forEach(cb => {
    cb.checked = perms.includes(cb.value);
  });
  
  document.getElementById('modal-edit').style.display = 'flex';
}

async function submitAdd(e){
  e.preventDefault();
  const fd = new FormData(e.target);
  // Get all checked permissions
  const perms = [];
  fd.getAll('permissions[]').forEach(val => perms.push(val));
  const body = {
      name: fd.get('name'),
      label: fd.get('label'),
      permissions: perms
  };
  
  try {
    const res = await fetch(API, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)});
    const json = await res.json();
    if(json.ok){
       showToast('Role berhasil ditambahkan', 'success');
       setTimeout(()=>location.reload(),800);
    } else {
       showToast(json.error || 'Gagal menyimpan role', 'error');
    }
  } catch(e) {
    showToast('Error: ' + e.message, 'error');
  }
  return false;
}

async function submitEdit(e){
  e.preventDefault();
  const fd = new FormData(e.target);
  const perms = [];
  fd.getAll('permissions[]').forEach(val => perms.push(val));
  const body = {
      id: fd.get('id'),
      name: fd.get('name'),
      label: fd.get('label'),
      permissions: perms
  };
  
  try {
    const res = await fetch(API, {method:'PUT', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)});
    const json = await res.json();
    if(json.ok){
       showToast('Role berhasil diperbarui', 'success');
       setTimeout(()=>location.reload(),800);
    } else {
       showToast(json.error || 'Gagal memperbarui role', 'error');
    }
  } catch(e) {
    showToast('Error: ' + e.message, 'error');
  }
  return false;
}

async function deleteRole(id){
  try {
    const res = await fetch(API+'?id='+id, {method:'DELETE'});
    const json = await res.json();
    if(json.ok || res.ok) {
      showToast('Role berhasil dihapus', 'success');
      setTimeout(()=>location.reload(),800);
    } else {
      showToast(json.error || 'Gagal menghapus role', 'error');
    }
  } catch(e) {
    showToast('Error: ' + e.message, 'error');
  }
}
</script>
</body></html>
