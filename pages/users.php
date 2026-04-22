<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../auth/AuthMiddleware.php';
$user = requireRole('admin','manager');
$pageTitle = 'Management User — Parama Studio';
$currentPage = 'users';
include __DIR__ . '/../includes/header.php';
$db = getDB();

// Get all users with role information
$allUsers = $db->getAllUsers();

// Get all roles dari database
$roles = $db->getRoles();
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
<div class="page active" id="page-users">
  <div class="ph">
    <div class="pt">Management User</div>
    <div class="ps">Kelola akun pengguna — hanya admin yang dapat mengakses halaman ini</div>
  </div>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
    <div style="font-size:13px;color:var(--text3)"><?= count($allUsers) ?> user terdaftar</div>
    <button class="btn bp" onclick="document.getElementById('modal-add').style.display='flex'">+ Tambah User</button>
  </div>

  <div class="card">
    <div class="tw">
      <table>
        <thead><tr>
          <th>ID</th><th>Username</th><th>Nama</th><th>Role</th><th>Status</th><th>Aksi</th>
        </tr></thead>
        <tbody>
        <?php foreach($allUsers as $u): ?>
          <tr>
            <td><?= $u['id'] ?></td>
            <td><b><?= htmlspecialchars($u['username']) ?></b></td>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><span class="badge <?= $u['role']==='admin'?'bdan':($u['role']==='manager'?'binf':'bgra') ?>"><?= ucfirst($u['role']) ?></span></td>
            <td><?= $u['is_active'] ? '<span class="badge bsuc">Aktif</span>' : '<span class="badge bgra">Nonaktif</span>' ?></td>
            <td>
              <button class="btn bs bsm" onclick="editUser(<?= $u['id'] ?>,'<?= htmlspecialchars($u['username']) ?>','<?= htmlspecialchars($u['name']) ?>',<?= $u['role_id'] ?>,<?= $u['is_active'] ?>)">✏ Edit</button>
              <?php if($u['id'] != $user['id']): ?>
              <button class="btn bs bsm" style="color:var(--danger)" onclick="if(confirm('Hapus user <?= htmlspecialchars($u['username']) ?>?'))deleteUser(<?= $u['id'] ?>)">×</button>
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

<!-- Modal Add User -->
<div id="modal-add" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-title">Tambah User Baru</div>
    <form id="form-add" onsubmit="return submitAdd(event)">
      <div class="form-row"><label>Username</label><input type="text" name="username" required placeholder="Tidak boleh ada spasi"></div>
      <div class="form-row"><label>Nama Lengkap</label><input type="text" name="name" required></div>
      <div class="form-row"><label>Password</label><input type="password" name="password" required minlength="6"></div>
      <div class="form-row"><label>Role</label>
        <select name="role_id"><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= ucfirst($r['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div style="display:flex;gap:8px;margin-top:16px">
        <button type="submit" class="btn bp" style="flex:1">Simpan</button>
        <button type="button" class="btn bs" onclick="document.getElementById('modal-add').style.display='none'">Batal</button>
      </div>
      <div id="add-msg" style="font-size:12px;margin-top:8px;display:none"></div>
    </form>
  </div>
</div>

<!-- Modal Edit User -->
<div id="modal-edit" class="modal-overlay" style="display:none">
  <div class="modal-box">
    <div class="modal-title">Edit User</div>
    <form id="form-edit" onsubmit="return submitEdit(event)">
      <input type="hidden" name="id" id="edit-id">
      <div class="form-row"><label>Username</label><input type="text" name="username" id="edit-username" required placeholder="Tidak boleh ada spasi"></div>
      <div class="form-row"><label>Nama Lengkap</label><input type="text" name="name" id="edit-name" required></div>
      <div class="form-row"><label>Password Baru <span style="font-weight:400;color:var(--text3)">(kosongkan jika tidak diubah)</span></label><input type="password" name="password" id="edit-password" minlength="6"></div>
      <div class="form-row"><label>Role</label>
        <select name="role_id" id="edit-role"><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= ucfirst($r['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="form-row"><label>Status</label>
        <select name="is_active" id="edit-active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
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
const API = '/api/users.php';

function editUser(id,username,name,role,active){
  document.getElementById('edit-id').value=id;
  document.getElementById('edit-username').value=username;
  document.getElementById('edit-name').value=name;
  document.getElementById('edit-role').value=role;
  document.getElementById('edit-active').value=active;
  document.getElementById('edit-password').value='';
  document.getElementById('modal-edit').style.display='flex';
}

async function submitAdd(e){
  e.preventDefault();
  const fd=new FormData(e.target);
  const body=Object.fromEntries(fd);
  try {
    const res=await fetch(API,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const json=await res.json();
    if(json.ok){
      showToast('User berhasil ditambahkan', 'success');
      setTimeout(()=>location.reload(),1000);
    }
    else{
      showToast(json.error||'Gagal menambah user', 'error');
    }
  } catch(e) {
    showToast('Error: ' + e.message, 'error');
  }
  return false;
}

async function submitEdit(e){
  e.preventDefault();
  const fd=new FormData(e.target);
  const body=Object.fromEntries(fd);
  if(!body.password) delete body.password;
  try {
    const res=await fetch(API,{method:'PUT',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});
    const json=await res.json();
    if(json.ok){
      showToast('User berhasil diperbarui', 'success');
      setTimeout(()=>location.reload(),1000);
    }
    else{
      showToast(json.error||'Gagal memperbarui user', 'error');
    }
  } catch(e) {
    showToast('Error: ' + e.message, 'error');
  }
  return false;
}

async function deleteUser(id){
  try {
    const res = await fetch(API+'?id='+id,{method:'DELETE'});
    const json = await res.json();
    if(json.ok || res.ok) {
      showToast('User berhasil dihapus', 'success');
      setTimeout(()=>location.reload(),1000);
    } else {
      showToast(json.error || 'Gagal menghapus user', 'error');
    }
  } catch(e) {
    showToast('Error: ' + e.message, 'error');
  }
}
</script>
</body></html>
