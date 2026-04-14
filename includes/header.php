<?php
// includes/header.php — dipanggil dari semua halaman
$user = $_SESSION['user'];
$role = $user['role'];
$isAdmin   = $role === 'admin';
$isManager = in_array($role, ['admin','manager']);
$isStaff   = $role === 'staff';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Parama Studio — HPP Calculator' ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<?php
$allSettings = [];
try {
    $db = getDB();
    
    // Load all pricing data from JSON files
    $settings = $db->getSettings();
    
    // Build OH structure for JavaScript
    $overhead = $settings['overhead'] ?? [];
    $ohSettings = $overhead;
    // Helper to get value case-insensitively and ignoring common delimiters
    $getOh = function($key) use ($ohSettings) {
        $cleanKey = str_replace([' ', '_', '.', '-'], '', strtolower($key));
        foreach ($ohSettings as $k => $v) {
            $cleanK = str_replace([' ', '_', '.', '-'], '', strtolower($k));
            if ($cleanK === $cleanKey) return (int)$v;
        }
        return null;
    };

    $oh = [];
    $sum = 0;
    foreach ($ohSettings as $k => $v) {
        $cleanK = str_replace([' ', '_', '.', '-'], '', strtolower($k));
        $val = (int)$v;
        $oh[$cleanK] = $val;
        if ($cleanK !== 'total' && $cleanK !== 'totaloverheadbulanan') {
            $sum += $val;
        }
    }

    // Ensure common keys are present for compatibility with legacy code
    $fixedKeys = ['marketing', 'creative', 'designer', 'pm', 'sosmed', 'freelance', 'ops', 'operasional'];
    foreach ($fixedKeys as $fk) {
        if (!isset($oh[$fk])) {
            $val = $getOh($fk);
            if ($val !== null) $oh[$fk] = $val;
        }
    }
    
    // Ensure all critical overhead keys exist
    $criticalKeys = ['designer', 'marketing', 'creative', 'pm', 'sosmed', 'freelance', 'operasional'];
    foreach ($criticalKeys as $ck) {
        if (!isset($oh[$ck]) || $oh[$ck] === null) {
            $val = $getOh($ck);
            $oh[$ck] = ($val !== null) ? $val : 0;
        }
    }
    
    $oh['total'] = $sum ?: ($getOh('total') ?? $getOh('totaloverheadbulanan') ?? 73586000);
    $allSettings['oh'] = json_encode($oh);
    
    // Load pricing factors
    $factors = $settings['pricing_factors'] ?? [];
    $allSettings['cetak_f'] = json_encode($factors['cetak'] ?? ['handy' => 1.0, 'minimal' => 0.95, 'large' => 1.15]);
    $allSettings['alc_f'] = json_encode($factors['alacarte'] ?? ['ebook' => 0.72, 'editcetak' => 0.62, 'desain' => 0.22, 'cetakonly' => 0.30]);
    
    // Load Full Service prices
    $fs = $settings['fullservice_pricing'] ?? [];
    $allSettings['fs'] = json_encode($fs);
    
    // Load Add-ons
    $addons = $db->getAddons();
    $allSettings['addon_data'] = json_encode($addons);
    
    // Load Cetak Base
    $cetakBase = $db->getCetakBase();
    $allSettings['cetak_base'] = json_encode($cetakBase);
    
    // Load Graduation
    $graduation = $db->getGraduation();
    $allSettings['grad_packages'] = json_encode($graduation);
    
    // Also extract for PHP use in pages like pengaturan.php
    $cetakF = json_decode($allSettings['cetak_f'], true);
    $alcF = json_decode($allSettings['alc_f'], true);
    
} catch (Exception $e) {
    // Fallback to default values if settings not available
    $allSettings['oh'] = json_encode(['designer' => 20000000, 'marketing' => 15000000, 'creative' => 8000000, 'pm' => 8000000, 'sosmed' => 7000000, 'freelance' => 4000000, 'operasional' => 11586000, 'total' => 73586000]);
    $allSettings['cetak_f'] = json_encode(['handy' => 1.0, 'minimal' => 0.95, 'large' => 1.15]);
    $allSettings['alc_f'] = json_encode(['ebook' => 0.72, 'editcetak' => 0.62, 'desain' => 0.22, 'cetakonly' => 0.30]);
    $allSettings['fs'] = json_encode(['handy' => [], 'minimal' => [], 'large' => []]);
    $allSettings['addon_data'] = json_encode(['finishing' => [], 'kertas' => [], 'halaman' => [], 'video' => [], 'pkg1' => [], 'pkg2' => []]);
    $allSettings['cetak_base'] = json_encode([]);
    $allSettings['grad_packages'] = json_encode(['packages' => [], 'addons' => [], 'cetak' => []]);
    
    // Set PHP variables for fallback
    $cetakF = ['handy' => 1.0, 'minimal' => 0.95, 'large' => 1.15];
    $alcF = ['ebook' => 0.72, 'editcetak' => 0.62, 'desain' => 0.22, 'cetakonly' => 0.30];
}
?>
<script>const DB_SETTINGS = <?= json_encode($allSettings) ?>;</script>
<script>
// Master Data API Helper — fetch dari master data terpusat
// Semua halaman lain bisa menggunakan API ini untuk data master
const MASTER_API = '/api/master-data.php';

async function loadMasterDataFromAPI() {
    try {
        const response = await fetch(MASTER_API + '?action=get_all');
        const result = await response.json();
        if (result.success) {
            return result.data;
        }
    } catch (error) {
        console.error('Failed to load master data from API:', error);
    }
    return null;
}

async function updateMasterData(type, data) {
    try {
        console.log('updateMasterData called:', type, data);
        const response = await fetch(MASTER_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type, data: data })
        });
        
        const result = await response.json();
        console.log('updateMasterData response:', result);
        
        if (result.success) {
            console.log('✓ Master data updated:', type);
            return true;
        } else {
            console.error('API error:', result.error || result.message);
            return false;
        }
    } catch (error) {
        console.error('Failed to update master data:', error);
        return false;
    }
}
</script>
<style>
:root{--bg:#F7F5F0;--surface:#FFF;--surface2:#F0EDE6;--border:#E2DDD5;--border2:#CCC8C0;--text:#1A1714;--text2:#5C5750;--text3:#9C9890;--accent:#C85B2A;--accent-light:#F5EAE3;--accent2:#2A6B8A;--accent2-light:#E3EFF5;--success:#2D7A4A;--success-bg:#E8F5ED;--warning:#8A5F1A;--warning-bg:#FDF3E3;--danger:#A02020;--danger-bg:#FAEAEA;--navy:#1C2E3D;--grad:#1A5C3A;--grad-light:#E5F2EC;--font-d:'DM Serif Display',Georgia,serif;--font-b:'DM Sans',sans-serif;--r:10px;--rl:16px;--sh:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04)}
*{box-sizing:border-box;margin:0;padding:0}
html{font-size:14px}
body{background:var(--bg);font-family:var(--font-b);color:var(--text);min-height:100vh;padding-top:0}
.app{display:grid;grid-template-columns:224px 1fr;min-height:100vh}
.sidebar{background:var(--navy);position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column}
.sb-logo{padding:22px 20px 14px;border-bottom:1px solid rgba(255,255,255,.08)}
.sb-logo .brand{font-family:var(--font-d);font-size:19px;color:#fff}
.sb-logo .sub{font-size:10px;color:rgba(255,255,255,.35);margin-top:2px;letter-spacing:.06em;text-transform:uppercase}
.nav{padding:10px 10px;flex:1}
.nav-sec{font-size:10px;color:rgba(255,255,255,.3);letter-spacing:.08em;text-transform:uppercase;padding:12px 10px 5px}
.nav-item{display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:8px;cursor:pointer;font-size:13px;color:rgba(255,255,255,.6);transition:all .15s;margin-bottom:1px;border:none;background:none;width:100%;text-align:left;text-decoration:none}
.nav-item:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.9)}
.nav-item.active{background:rgba(200,91,42,.25);color:#fff}
.nav-item.active-grad{background:rgba(26,92,58,.35);color:#fff}
.nav-item .ico{width:16px;text-align:center;font-size:13px;opacity:.7}
.nav-item.active .ico,.nav-item.active-grad .ico{opacity:1}
.sb-footer{padding:10px 20px 18px;border-top:1px solid rgba(255,255,255,.08)}
.sb-footer .ver{font-size:10px;color:rgba(255,255,255,.25)}
.sb-user{padding:10px 20px 14px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:space-between;gap:8px}
.sb-user-info{display:flex;flex-direction:column;gap:2px}
.sb-user-name{font-size:12px;color:rgba(255,255,255,.8);font-weight:500}
.sb-user-role{font-size:10px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.06em}
.sb-logout{font-size:11px;color:rgba(255,255,255,.4);padding:3px 7px;border-radius:5px;border:1px solid rgba(255,255,255,.15);background:none;text-decoration:none;transition:all .15s;flex-shrink:0}
.sb-logout:hover{color:#fff;border-color:rgba(255,255,255,.4)}
.main{overflow:hidden}
.page{display:none;padding:26px 30px;max-width:1120px}
.page.active{display:block}
.ph{margin-bottom:22px}
.pt{font-family:var(--font-d);font-size:26px;letter-spacing:-.5px}
.ps{font-size:13px;color:var(--text3);margin-top:3px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);padding:18px;box-shadow:var(--sh)}
.ct{font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px}
.mg{display:grid;grid-template-columns:repeat(4,1fr);gap:11px;margin-bottom:18px}
.m{background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:14px}
.ml{font-size:10px;color:var(--text3);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:5px}
.mv{font-family:var(--font-d);font-size:21px}
.ms{font-size:11px;color:var(--text3);margin-top:2px}
.mv.acc{color:var(--accent)}.mv.suc{color:var(--success)}.mv.war{color:var(--warning)}
.tw{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:13px}
th{background:var(--surface2);padding:8px 11px;text-align:left;font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--border);white-space:nowrap}
td{padding:8px 11px;border-bottom:1px solid var(--border);color:var(--text);vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--surface2)}
input,select,textarea{font-family:var(--font-b);font-size:13px;padding:7px 10px;border:1px solid var(--border2);border-radius:8px;background:var(--surface);color:var(--text);outline:none;transition:border-color .15s;width:100%}
input:focus,select:focus,textarea:focus{border-color:var(--accent)}
input[type=number]{width:auto}
input[type=checkbox]{width:auto}
.btn{display:inline-flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:none;transition:all .15s;font-family:var(--font-b);text-decoration:none}
.bp{background:var(--accent);color:#fff}.bp:hover{background:#b04d22}
.bs{background:var(--surface2);color:var(--text);border:1px solid var(--border2)}.bs:hover{background:var(--border)}
.bg_{background:transparent;color:var(--accent);border:1px solid var(--accent)}.bg_:hover{background:var(--accent-light)}
.bgrad{background:var(--grad);color:#fff}.bgrad:hover{background:#154d30}
.bsm{padding:5px 11px;font-size:12px}
.bdanger{background:var(--danger-bg);color:var(--danger);border:1px solid var(--danger)}.bdanger:hover{background:var(--danger);color:#fff}
.badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500}
.bsuc{background:var(--success-bg);color:var(--success)}.bwar{background:var(--warning-bg);color:var(--warning)}.bdan{background:var(--danger-bg);color:var(--danger)}.binf{background:var(--accent2-light);color:var(--accent2)}.bgra{background:var(--surface2);color:var(--text3)}.bgreen{background:var(--grad-light);color:var(--grad)}
.chip{display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;margin:2px}
.cy{background:var(--success-bg);color:var(--success)}.cn{background:var(--surface2);color:var(--text3);text-decoration:line-through}
.note{font-size:12px;color:var(--text2);padding:9px 13px;background:var(--accent2-light);border-radius:8px;border-left:3px solid var(--accent2);line-height:1.6;margin-top:8px}
.note.warn{background:var(--warning-bg);border-left-color:var(--warning);color:var(--warning)}
.note.suc{background:var(--success-bg);border-left-color:var(--success);color:var(--success)}
.note.dan{background:var(--danger-bg);border-left-color:var(--danger);color:var(--danger)}
.note.grad{background:var(--grad-light);border-left-color:var(--grad);color:var(--grad)}
.rr{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px}
.rr:last-child{border-bottom:none}
.rr .rl{color:var(--text2)}.rr .rv{font-weight:500;color:var(--text)}
.rr.tot{padding-top:10px}.rr.tot .rl{font-weight:600;color:var(--text);font-size:14px}
.rr.tot .rv{font-family:var(--font-d);font-size:20px;color:var(--accent)}
.rr .rv.gr{color:var(--success)}.rr .rv.rd{color:var(--danger)}
.tn{display:flex;gap:3px;border-bottom:1px solid var(--border);margin-bottom:18px}
.tb{padding:7px 14px;font-size:13px;font-weight:500;cursor:pointer;border:none;background:none;color:var(--text3);border-bottom:2px solid transparent;margin-bottom:-1px;transition:all .15s;font-family:var(--font-b)}
.tb.active{color:var(--accent);border-bottom-color:var(--accent)}
.tp{display:none}.tp.active{display:block}
.g2{display:grid;grid-template-columns:1fr 1fr;gap:15px}
.g3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:11px}
.full-width{position:relative;width:100%;padding-left:30px;padding-right:30px;margin-left:-26px;margin-right:-26px;padding-top:0;padding-bottom:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);box-shadow:var(--sh)}
.fg{display:flex;flex-direction:column;gap:5px}
.fl{font-size:12px;font-weight:500;color:var(--text2)}
.fh{font-size:11px;color:var(--text3)}
.br{display:flex;align-items:center;gap:9px;margin-bottom:7px;font-size:12px}
.bl{width:140px;color:var(--text2);flex-shrink:0}
.bt{flex:1;height:7px;background:var(--border);border-radius:3px;overflow:hidden}
.bf{height:100%;border-radius:3px;transition:width .4s}
.bv{width:42px;text-align:right;color:var(--text2);font-weight:500}
.pkgrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(255px,1fr));gap:13px}
.pkc{background:var(--surface);border:1px solid var(--border);border-radius:var(--rl);padding:16px;transition:box-shadow .15s}
.pkc:hover{box-shadow:0 4px 20px rgba(0,0,0,.08)}
.pkc.feat{border-color:var(--accent);border-width:2px}
.pkc.feat-grad{border-color:var(--grad);border-width:2px}
.pnum{font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px}
.pname{font-family:var(--font-d);font-size:16px;color:var(--text);margin-bottom:5px}
.pdesc{font-size:12px;color:var(--text2);line-height:1.55;margin-bottom:11px}
.pp{font-family:var(--font-d);font-size:19px;color:var(--accent)}
.pp.grad{color:var(--grad)}
.pps{font-size:11px;color:var(--text3);margin-top:2px}
.si{background:var(--surface2);border:1px solid var(--border);border-radius:var(--r);padding:13px;margin-bottom:8px}
.sih{display:flex;justify-content:space-between;align-items:center;margin-bottom:7px}
.sin{font-weight:500;font-size:14px}.sis{font-size:12px;color:var(--text3);margin-top:1px}
.sim{display:grid;grid-template-columns:repeat(4,1fr);gap:7px;margin-top:9px}
.sitem{background:var(--surface);border-radius:7px;padding:9px;border:1px solid var(--border)}
.siml{font-size:10px;color:var(--text3);font-weight:500;text-transform:uppercase;margin-bottom:2px}
.simv{font-size:13px;font-weight:600}
.fb{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.mb12{margin-bottom:12px}.mb16{margin-bottom:16px}.mb20{margin-bottom:20px}
.mt10{margin-top:10px}.mt16{margin-top:16px}
.sec{font-size:12px;font-weight:600;color:var(--text2);margin-bottom:10px;display:flex;align-items:center;gap:8px}
.sec::after{content:'';flex:1;height:1px;background:var(--border)}
.dim td{opacity:.5}
.edi{padding:3px 6px;border:1px solid transparent;border-radius:5px;width:100%;font-size:13px;background:transparent;cursor:text;min-width:60px}
.edi:hover{border-color:var(--border2);background:var(--surface)}
.edi:focus{border-color:var(--accent);background:var(--surface);outline:none}
.edit-hint{font-size:10px;color:var(--text3);font-style:italic;padding:4px 0}
.grad-accent{color:var(--grad)}
.grad-card-header{background:var(--grad);color:#fff;border-radius:var(--r) var(--r) 0 0;padding:10px 14px;margin:-18px -18px 14px;font-weight:600;font-size:13px}
.bns-tag{display:inline-flex;align-items:center;gap:5px;padding:3px 10px 3px 12px;background:var(--accent-light);color:var(--accent);border-radius:20px;font-size:12px;font-weight:500}
.bns-tag button{background:none;border:none;cursor:pointer;color:var(--accent);font-size:14px;line-height:1;padding:0;opacity:.7}
.bns-tag button:hover{opacity:1}
.diskon-box{background:var(--surface2);border:1px dashed var(--accent);border-radius:var(--r);padding:13px;margin-top:12px}
.diskon-box .dbt{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px}
.dkbtn{padding:4px 12px;border-radius:20px;font-size:12px;cursor:pointer;border:1px solid var(--border2);background:transparent;color:var(--text2);transition:all .15s;font-family:var(--font-b)}
.dkbtn.active{background:var(--accent);color:#fff;border-color:var(--accent)}
.dk-warn{font-size:11px;padding:6px 10px;border-radius:6px;margin-top:8px}
.dw-ok{background:var(--success-bg);color:var(--success)}.dw-warn{background:var(--warning-bg);color:var(--warning)}.dw-bad{background:var(--danger-bg);color:var(--danger)}
@keyframes slideInRight{from{transform:translateX(400px);opacity:0}to{transform:translateX(0);opacity:1}}
@keyframes slideOutRight{from{transform:translateX(0);opacity:1}to{transform:translateX(400px);opacity:0}}
/* Modal */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9998;display:flex;align-items:center;justify-content:center;padding:20px}

/* Full Service Controls - Desktop */
.fs-controls{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
.fs-paket-section{flex:0 0 auto;display:flex;flex-direction:column;gap:8px}
.fs-paket-buttons{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-start}
.fs-oh-section{display:flex;flex-wrap:wrap;align-items:center;justify-content:flex-end;gap:6px}
.modal-box{background:var(--surface);border-radius:var(--rl);padding:28px;max-width:520px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3)}
.modal-title{font-family:var(--font-d);font-size:20px;margin-bottom:16px}
.form-row{margin-bottom:12px}
.form-row label{display:block;font-size:12px;font-weight:500;color:var(--text2);margin-bottom:4px}

/* Mobile Navbar */
.mobile-navbar{display:none;position:fixed;top:0;left:0;right:0;height:50px;background:var(--navy);color:#fff;z-index:1000;align-items:center;padding:0 12px;gap:12px;border-bottom:1px solid rgba(0,0,0,.1)}
.mobile-navbar-title{flex:1;font-size:14px;font-weight:500}

/* Mobile Menu Button */
.mobile-menu-btn{background:none;border:none;color:#fff;font-size:24px;cursor:pointer;padding:8px;border-radius:4px;transition:all .2s;display:flex;align-items:center;justify-content:center}
.mobile-menu-btn:hover{background:rgba(255,255,255,.1)}

.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999}
.sidebar-overlay.active{display:block}

/* Responsive Design */
@media (max-width: 768px) {
  html{font-size:13px}
  body{padding-top:50px}
  .mobile-navbar{display:flex}
  .app{grid-template-columns:1fr}
  .sidebar{position:fixed;left:0;top:50px;height:calc(100vh - 50px);width:224px;transform:translateX(-100%);transition:transform .3s ease-out;z-index:9999}
  .sidebar.active{transform:translateX(0)}
  .main{padding-top:0}
  
  .page{padding:14px 16px}
  .ph{margin-bottom:16px}
  .pt{font-size:20px}
  .ps{font-size:12px}
  
  .mg{grid-template-columns:repeat(2,1fr);gap:8px}
  .m{padding:12px;border-radius:8px}
  .ml{font-size:9px}
  .mv{font-size:18px}
  .ms{font-size:10px}
  
  .card{padding:12px}
  .ct{font-size:10px;margin-bottom:10px}
  
  table{font-size:12px}
  th{padding:6px 8px;font-size:10px}
  td{padding:6px 8px}
  
  input,select,textarea{font-size:13px;padding:6px 8px}
  .btn{padding:6px 12px;font-size:12px}
  .bsm{padding:4px 10px;font-size:11px}
  
  .modal-box{max-width:95vw;padding:16px;max-height:85vh}
  .modal-title{font-size:16px;margin-bottom:12px}
  
  .note{font-size:11px;padding:8px 12px}
  .badge{font-size:10px}
  
  .g2{grid-template-columns:1fr}
  .g3{grid-template-columns:1fr}
  .full-width{margin-left:-14px;margin-right:-14px;padding-left:14px;padding-right:14px}
  
  .br{gap:8px;margin-bottom:10px}
  .bl{width:100px}
}

@media (max-width: 480px) {
  html{font-size:12px}
  .mg{grid-template-columns:1fr}
  .m{padding:10px}
  .ml{font-size:8px}
  .mv{font-size:16px}
  .ms{font-size:9px}
  
  .page{padding:10px 12px;margin-top:10px}
  .pt{font-size:18px}
  
  .btn{padding:5px 10px;font-size:11px}
  table{font-size:11px}
  th{padding:5px 6px;font-size:9px}
  td{padding:5px 6px}
  
  input,select,textarea{font-size:12px;padding:5px 6px}
  .modal-box{padding:12px}
  .modal-title{font-size:14px}
  
  .card{padding:10px}
  .ct{font-size:9px}
  
  .g2{grid-template-columns:1fr}
  .g3{grid-template-columns:1fr}
  .full-width{margin-left:-10px;margin-right:-10px;padding-left:10px;padding-right:10px}
  
  /* Full Service Controls - Mobile */
  .fs-controls{flex-direction:column;align-items:flex-start}
  .fs-paket-section{width:100%}
  .fs-paket-buttons{width:100%;justify-content:flex-start}
  .fs-oh-section{width:100%;justify-content:flex-start}
  
  .fb{flex-direction:column;gap:8px !important}
  .br{gap:6px;margin-bottom:8px}
  .bl{width:80px;font-size:11px}
  .bv{font-size:11px}
}
</style>
