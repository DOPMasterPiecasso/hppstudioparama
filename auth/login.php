<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!empty($_SESSION['user'])) {
    header('Location: /pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        try {
            $db = getDB();
            $user = $db->getUserByUsername($username);

            if ($user && $user['is_active'] && password_verify($password, $user['password'])) {
                $_SESSION['user'] = [
                    'id'         => $user['id'],
                    'username'   => $user['username'],
                    'name'       => $user['name'],
                    'role'       => $user['role'],
                    'role_label' => $user['role_label'],
                    'permissions'=> $user['permissions'],
                ];
                session_regenerate_id(true);
                header('Location: /pages/dashboard.php');
                exit;
            } else {
                $error = 'Username atau password salah.';
            }
        } catch (Exception $e) {
            error_log('Login Error: ' . $e->getMessage());
            $error = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    } else {
        $error = 'Username dan password wajib diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Parama Studio</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--bg:#F7F5F0;--surface:#FFF;--surface2:#F0EDE6;--border:#E2DDD5;--border2:#CCC8C0;--text:#1A1714;--text2:#5C5750;--text3:#9C9890;--accent:#C85B2A;--accent-light:#F5EAE3;--danger:#A02020;--danger-bg:#FAEAEA;--navy:#1C2E3D;--font-d:'DM Serif Display',Georgia,serif;--font-b:'DM Sans',sans-serif;--r:10px;--rl:16px}
*{box-sizing:border-box;margin:0;padding:0}
body{background:var(--navy);font-family:var(--font-b);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center}
.login-box{background:var(--surface);border-radius:var(--rl);padding:40px 36px;width:360px;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.login-logo{font-family:var(--font-d);font-size:22px;color:var(--text);margin-bottom:4px}
.login-sub{font-size:12px;color:var(--text3);margin-bottom:28px;line-height:1.5}
.login-field{margin-bottom:14px}
.login-field label{display:block;font-size:12px;font-weight:500;color:var(--text2);margin-bottom:5px}
.login-field input{width:100%;padding:9px 12px;border:1px solid var(--border2);border-radius:8px;font-size:13px;font-family:var(--font-b);background:var(--surface);color:var(--text);outline:none;transition:border-color .15s}
.login-field input:focus{border-color:var(--accent)}
.login-btn{width:100%;padding:10px;background:var(--accent);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:500;cursor:pointer;font-family:var(--font-b);transition:background .15s;margin-top:4px}
.login-btn:hover{background:#b04d22}
.login-err{font-size:12px;color:var(--danger);background:var(--danger-bg);padding:8px 12px;border-radius:8px;margin-top:10px;text-align:center}
.demo-hint{margin-top:18px;padding-top:14px;border-top:1px solid var(--border);font-size:11px;color:var(--text3);line-height:1.8}
code{background:var(--surface2);padding:1px 5px;border-radius:4px;font-size:11px}
</style>
</head>
<body>
<div class="login-box">
  <div class="login-logo">Parama Studio</div>
  <div class="login-sub">HPP Calculator — Masuk untuk melanjutkan</div>
  <form method="POST">
    <div class="login-field">
      <label>Username</label>
      <input type="text" name="username" placeholder="contoh: manager" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autofocus>
    </div>
    <div class="login-field">
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••">
    </div>
    <button type="submit" class="login-btn">Masuk</button>
    <?php if ($error): ?>
    <div class="login-err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
  </form>
  <div class="demo-hint">
    <!-- <b>Akun yang tersedia:</b><br>
    Admin: <code>admin</code> / <code>admin2026</code><br>
    Manager: <code>manager</code> / <code>parama2026</code><br>
    Staff: <code>staff</code> / <code>staff123</code> -->
  </div>
</div>
</body>
</html>
