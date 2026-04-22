// ============================================================
// Parama HPP Calculator — app.js
// Ported from parama_dashboard_v2.html → PHP integration
// Auth handled by PHP session, user info via PHP_USER global
// Data persistence via API (/api/settings.php & penawaran.php)
// ============================================================

const currentUser = typeof PHP_USER !== 'undefined' ? PHP_USER : null;

// ============================================================
// STATE — all prices live here, editable
// ============================================================
const DEF_OH = {total:73586000,marketing:15000000,creative:8000000,designer:20000000,pm:8000000,sosmed:7000000,freelance:4000000,operasional:11586000};
let OH = DB_SETTINGS['oh'] ? JSON.parse(DB_SETTINGS['oh']) : {...DEF_OH};

console.log('=== app.js OH INITIALIZATION ===');
console.log('Initial OH before cleanup:', JSON.parse(JSON.stringify(OH)));

// Ensure OH has proper structure and calculate total if missing
if (!OH.total || OH.total === 0) {
    OH.total = (OH.designer||0) + (OH.marketing||0) + (OH.creative||0) + (OH.pm||0) + 
               (OH.sosmed||0) + (OH.freelance||0) + (OH.operasional||0) + (OH.ops||0);
}
// Ensure all fields are numeric
Object.keys(OH).forEach(key => {
    OH[key] = typeof OH[key] === 'number' ? OH[key] : parseInt(OH[key]) || 0;
});

console.log('OH after numeric conversion:', JSON.parse(JSON.stringify(OH)));

// Ensure operasional has a value
if (!OH.operasional || OH.operasional === 0) {
    OH.operasional = OH.ops || DEF_OH.operasional || 0;
}

console.log('OH final:', JSON.parse(JSON.stringify(OH)));
console.log('OH.operasional final value:', OH.operasional, '(type:', typeof OH.operasional, ')');

let CETAK_F = DB_SETTINGS['cetak_f'] ? JSON.parse(DB_SETTINGS['cetak_f']) : {handy:1.0, minimal:0.95, large:1.15};
let ALC_F = DB_SETTINGS['alc_f'] ? JSON.parse(DB_SETTINGS['alc_f']) : {ebook:0.72, editcetak:0.62, desain:0.22, cetakonly:0.30};

let FS = DB_SETTINGS['fs'] ? JSON.parse(DB_SETTINGS['fs']) : {
  handy:[[30,50,465000,30],[51,75,415000,30],[76,100,370000,45],[101,125,350000,55],[126,150,335000,60],[151,175,315000,65],[176,200,295000,75],[201,225,260000,80],[226,250,250000,80],[251,275,240000,90],[276,300,230000,100],[300,325,220000,100],[326,350,210000,120],[351,375,200000,120],[376,400,190000,135],[401,425,185000,135],[426,450,165000,145],[451,475,175000,150],[476,500,150000,160]],
  minimal:[[30,50,450000,30],[51,75,400000,30],[76,100,355000,45],[101,125,335000,55],[126,150,320000,60],[151,175,300000,65],[176,200,280000,75],[201,225,245000,80],[226,250,235000,80],[251,275,240000,90],[276,300,215000,100],[300,325,205000,100],[326,350,195000,120],[351,375,185000,120],[376,400,180000,135],[401,425,170000,135],[426,450,160000,145],[451,475,150000,150],[476,500,140000,160]],
  large:[[30,50,480000,30],[51,75,430000,30],[76,100,405000,45],[101,125,365000,55],[126,150,350000,60],[151,175,330000,65],[176,200,310000,75],[201,225,275000,80],[226,250,265000,80],[251,275,255000,90],[276,300,245000,100],[300,325,235000,100],[326,350,225000,120],[351,375,215000,120],[376,400,205000,135],[401,425,195000,135],[426,450,175000,145],[451,475,165000,150],[476,500,155000,160]]
};

let ADDON_DATA = DB_SETTINGS['addon_data'] ? JSON.parse(DB_SETTINGS['addon_data']) : {
  finishing:[
    {id:'binding',name:'Binding Paku/Jepang/Spiral',type:'flat',tiers:[[25,75,50000],[76,150,35000],[151,9999,30000]]},
    {id:'popup',name:'Pop Up 2D',type:'flat',tiers:[[25,75,55000],[76,150,40000],[151,9999,35000]]},
    {id:'tunnel',name:'Cover Tunnel',type:'flat',tiers:[[25,75,75000],[76,150,60000],[151,9999,50000]]},
    {id:'klip',name:'Cover Klip/Cetekan',type:'flat',tiers:[[25,75,15000],[76,150,10000],[151,9999,8000]]},
    {id:'covbahan',name:'Cover Bahan',type:'flat',tiers:[[25,75,55000],[76,150,40000],[151,9999,35000]]},
  ],
  kertas:[
    {id:'ivory',name:'Ivory Paper',type:'per_hal',tiers:[[25,50,450],[51,100,250],[101,150,200],[151,9999,150]]},
    {id:'laminasi',name:'Laminasi Paper',type:'per_hal',tiers:[[25,50,600],[51,100,450],[101,150,400],[151,9999,350]]},
  ],
  halaman:[
    {id:'extrahal',name:'Halaman Tambahan',type:'extra_hal',tiers:[[25,50,3000],[51,100,2000],[101,150,1300],[151,9999,1000]]},
  ],
  video:[
    {id:'drone',name:'Drone Video (1–2 mnt)',type:'flat_video',price:1500000},
    {id:'docudrama',name:'Docudrama Video (5–10 mnt)',type:'flat_video',price:3000000},
  ],
  pkg1:[
    {id:'slidebox',name:'Slide Box',type:'flat',tiers:[[25,50,45000],[51,100,40000],[101,150,35000],[151,200,30000],[201,9999,25000]]},
    {id:'stdbox1',name:'Standart Box 1',type:'flat',tiers:[[25,50,150000],[51,100,95000],[101,150,80000],[151,200,70000],[201,9999,65000]]},
    {id:'stdbox2',name:'Standart Box 2',type:'flat',tiers:[[25,50,150000],[51,100,100000],[101,150,80000],[151,200,75000],[201,9999,70000]]},
    {id:'hardbox',name:'Hard Box 3 (Akrilik)',type:'flat',tiers:[[25,50,125000],[51,100,100000],[101,150,90000],[151,200,80000],[201,9999,75000]]},
  ],
  pkg2:[
    {id:'cbox1',name:'Custom Box 1',type:'flat',tiers:[[25,50,200000],[51,100,170000],[101,150,130000],[151,200,120000],[201,9999,110000]]},
    {id:'cbox2',name:'Custom Box 2',type:'flat',tiers:[[25,50,165000],[51,100,150000],[101,150,130000],[151,200,120000],[201,9999,110000]]},
    {id:'cbox3',name:'Custom Box 3',type:'flat',tiers:[[25,50,200000],[51,100,170000],[101,150,130000],[151,200,120000],[201,9999,110000]]},
    {id:'cbox4',name:'Custom Box 4',type:'flat',tiers:[[25,50,200000],[51,100,170000],[101,150,140000],[151,200,130000],[201,9999,120000]]},
    {id:'cbox5',name:'Custom Box 5',type:'flat',tiers:[[25,50,200000],[51,100,170000],[101,150,145000],[151,200,135000],[201,9999,130000]]},
  ],
};

let GRAD = DB_SETTINGS['grad_packages'] ? JSON.parse(DB_SETTINGS['grad_packages']) : {
  packages:[
    {id:'gphv',name:'Photo & Video',price:4500000,desc:'2 Fotografer + 1 Videografer, 50 foto edited, video cinematic 2–4 mnt, G-Drive, 4 jam coverage, transport jabodetabek',color:'acc'},
    {id:'gvideo',name:'Video Only',price:2000000,desc:'1 Videografer, video cinematic 2–5 mnt, G-Drive, 4 jam coverage, transport jabodetabek',color:''},
    {id:'gphoto',name:'Photo Only',price:2750000,desc:'2 Fotografer, 100 foto edited, G-Drive, 4 jam coverage, transport jabodetabek',color:''},
    {id:'gbooth',name:'Photo Booth',price:3850000,desc:'1–2 Crew profesional, backdrop wisuda, lighting studio, Selfiebox Machine, unlimited print 4R, max 3 jam, softcopy + QR Code realtime, transport jabodetabek',color:''},
    {id:'g360',name:'Glamation 360°',price:4100000,desc:'1–2 Crew profesional, MP4, LCD 50in preview, GoPro/iPhone 12 Pro, overlay design free, max 3 jam, QR Code realtime, transport jabodetabek',color:''},
    {id:'gcomplete',name:'Complete Package',price:7750000,desc:'Photo (2 foto, 100 edited) + Video (1 videografer, cinematic 2–4 mnt) + Photo Booth (unlimited print 4R, max 3 jam, QR Code), transport jabodetabek',color:'feat'},
  ],
  addons:[
    {id:'gvideo_add',name:'Tambah 1 Videografer',price:1500000},
    {id:'gphoto_add',name:'Tambah 1 Fotografer',price:1250000},
    {id:'gbooth_add',name:'Tambah 1 Jam Photobooth/360',price:500000},
    {id:'gwork_add',name:'Tambah 1 Jam Kerja/Orang',price:350000},
  ],
  cetak:[
    {id:'g4r',name:'Cetak Foto 4R',price:4000},
    {id:'g8r',name:'Cetak Foto 8R',price:8000},
    {id:'g10r',name:'Cetak Foto 10R',price:15000},
    {id:'g12r',name:'Cetak Foto 12R',price:20000},
  ]
};

const ALC_CFG = DB_SETTINGS['alc_cfg'] ? JSON.parse(DB_SETTINGS['alc_cfg']) : {
  'fs-handy':{label:'Full Service — Handy Book A4+',fs:true,pkg:'handy',bySiswa:true},
  'fs-minimal':{label:'Full Service — Minimal Book SQ',fs:true,pkg:'minimal',bySiswa:true},
  'fs-large':{label:'Full Service — Large Book B4',fs:true,pkg:'large',bySiswa:true},
  'ac-ebook':{label:'E-Book Package',bySiswa:true,factor:'ebook'},
  'ac-editcetak':{label:'Edit+Desain+Cetak',bySiswa:true,factor:'editcetak'},
  'ac-fotohalf':{label:'Foto Only (½ hari)',bySiswa:false,flat:[3500000,5000000]},
  'ac-fotofull':{label:'Foto Only (full day)',bySiswa:false,flat:[6000000,9000000]},
  'ac-videod':{label:'Drone Video',bySiswa:false,flat:[1500000,1500000]},
  'ac-videodoc':{label:'Docudrama Video',bySiswa:false,flat:[3000000,3000000]},
  'ac-desain':{label:'Desain Only',bySiswa:true,factor:'desain',minPerBuku:50000},
  'ac-cetakonly':{label:'Cetak Only',bySiswa:true,factor:'cetakonly',minPerBuku:30000},
};

let curFSPkg='handy', curAnPkg='handy';

// Bonus & Fasilitas — diisi dari DB via refreshMasterData()
let BONUS_FASILITAS = {
  fullservice: [],
  graduation:  [],
  alacarte:    [],
};

// ============================================================
// HELPERS
// ============================================================
const fmt = n => {
  const val = parseInt(n) || 0;
  return 'Rp'+Math.round(val).toLocaleString('id-ID');
};
const fmtM = n => {
  const val = parseInt(n) || 0;
  return val>=1000000?'Rp'+(val/1000000).toFixed(1)+'jt':fmt(val);
};
function getTier(tiers,qty){for(const[lo,hi,v]of tiers)if(qty>=lo&&qty<=hi)return v;return tiers[tiers.length-1][2]}
function getFSPrice(pkg,siswa){for(const[lo,hi,h,p]of FS[pkg])if(siswa>=lo&&siswa<=hi)return{harga:h,pages:p};return siswa<30?{harga:FS[pkg][0][2],pages:FS[pkg][0][3]}:{harga:FS[pkg][FS[pkg].length-1][2],pages:FS[pkg][FS[pkg].length-1][3]}}
function getFSPageForSiswa(pkg,siswa){return getFSPrice(pkg,siswa).pages}

// ============================================================
// BIAYA CETAK — Renjana Offset
// ============================================================
const DEF_CETAK_BASE = [
  {lo:30,hi:50,label:'30–50 siswa',pages:{30:92000,45:102000,60:115000,65:127000,75:140000,80:140000,90:152000,100:165000,110:176000,120:176000,135:176000,150:176000,160:176000}},
  {lo:51,hi:75,label:'51–75 siswa',pages:{30:80000,45:90000,60:100000,65:110000,75:122000,80:122000,90:134000,100:145000,110:158000,120:162000,135:165000,150:168000,160:170000}},
  {lo:76,hi:100,label:'76–100 siswa',pages:{30:70000,45:80000,60:90000,65:98000,75:108000,80:108000,90:118000,100:130000,110:140000,120:145000,135:150000,150:155000,160:160000}},
  {lo:101,hi:125,label:'101–125 siswa',pages:{30:62000,45:72000,60:82000,65:88000,75:97000,80:97000,90:106000,100:116000,110:126000,120:130000,135:135000,150:140000,160:145000}},
  {lo:126,hi:150,label:'126–150 siswa',pages:{30:58000,45:68000,60:76000,65:82000,75:90000,80:90000,90:98000,100:108000,110:118000,120:122000,135:127000,150:132000,160:137000}},
  {lo:151,hi:175,label:'151–175 siswa',pages:{30:54000,45:63000,60:71000,65:76000,75:84000,80:84000,90:91000,100:100000,110:109000,120:113000,135:118000,150:123000,160:128000}},
  {lo:176,hi:200,label:'176–200 siswa',pages:{30:50000,45:59000,60:66000,65:71000,75:78000,80:78000,90:85000,100:93000,110:101000,120:105000,135:110000,150:115000,160:119000}},
  {lo:201,hi:225,label:'201–225 siswa',pages:{30:47000,45:55000,60:62000,65:66000,75:73000,80:73000,90:79000,100:87000,110:95000,120:98000,135:103000,150:107000,160:111000}},
  {lo:226,hi:250,label:'226–250 siswa',pages:{30:44000,45:52000,60:58000,65:62000,75:68000,80:68000,90:74000,100:81000,110:88000,120:92000,135:96000,150:100000,160:104000}},
  {lo:251,hi:275,label:'251–275 siswa',pages:{30:41000,45:49000,60:55000,65:58000,75:64000,80:64000,90:70000,100:76000,110:83000,120:86000,135:90000,150:94000,160:98000}},
  {lo:276,hi:300,label:'276–300 siswa',pages:{30:39000,45:46000,60:52000,65:55000,75:60000,80:60000,90:66000,100:72000,110:78000,120:81000,135:85000,150:89000,160:92000}},
  {lo:301,hi:325,label:'301–325 siswa',pages:{30:37000,45:44000,60:49000,65:52000,75:57000,80:57000,90:62000,100:68000,110:74000,120:77000,135:80000,150:84000,160:87000}},
  {lo:326,hi:350,label:'326–350 siswa',pages:{30:35000,45:42000,60:47000,65:50000,75:54000,80:54000,90:59000,100:65000,110:70000,120:73000,135:76000,150:80000,160:83000}},
  {lo:351,hi:375,label:'351–375 siswa',pages:{30:33000,45:40000,60:45000,65:47000,75:52000,80:52000,90:56000,100:62000,110:67000,120:70000,135:73000,150:76000,160:79000}},
  {lo:376,hi:400,label:'376–400 siswa',pages:{30:31000,45:38000,60:42000,65:45000,75:49000,80:49000,90:53000,100:58000,110:63000,120:66000,135:69000,150:72000,160:75000}},
  {lo:401,hi:425,label:'401–425 siswa',pages:{30:30000,45:36000,60:40000,65:42000,75:46000,80:46000,90:50000,100:55000,110:60000,120:62000,135:65000,150:68000,160:71000}},
  {lo:426,hi:450,label:'426–450 siswa',pages:{30:28000,45:34000,60:38000,65:40000,75:44000,80:44000,90:48000,100:52000,110:57000,120:59000,135:62000,150:65000,160:67000}},
  {lo:451,hi:475,label:'451–475 siswa',pages:{30:27000,45:32000,60:36000,65:38000,75:42000,80:42000,90:45000,100:50000,110:54000,120:56000,135:59000,150:62000,160:64000}},
  {lo:476,hi:500,label:'476–500 siswa',pages:{30:26000,45:31000,60:34000,65:36000,75:40000,80:40000,90:43000,100:47000,110:51000,120:53000,135:56000,150:59000,160:61000}},
];

let CETAK_BASE = DB_SETTINGS['cetak_base'] ? JSON.parse(DB_SETTINGS['cetak_base']) : DEF_CETAK_BASE.map(r=>({...r, pages:{...r.pages}}));
let curCetakRange = 0;

function getCetakRangeIdx(siswa){
  for(let i=0;i<CETAK_BASE.length;i++){
    if(siswa>=CETAK_BASE[i].lo && siswa<=CETAK_BASE[i].hi) return i;
  }
  return CETAK_BASE.length-1;
}

function setCetakRange(idx, btn){
  curCetakRange = idx;
  document.querySelectorAll('#cetak-range-btns button').forEach((b,i)=>{
    b.className = i===idx ? 'btn bp bsm' : 'btn bs bsm';
  });
  renderCetakTable();
}

function renderCetakTable(){
  const r = CETAK_BASE[curCetakRange];
  const pages = Object.keys(r.pages).map(Number).sort((a,b)=>a-b);
  const rows = pages.map(p=>`
    <tr>
      <td style="font-weight:500;white-space:nowrap">${p} hal</td>
      <td><div style="display:flex;align-items:center;gap:5px">
        <input type="number" value="${r.pages[p]}" style="width:95px;text-align:right"
          onchange="CETAK_BASE[${curCetakRange}].pages[${p}]=parseInt(this.value)||${r.pages[p]};kalcUpdate()">
        <span style="font-size:11px;color:var(--text3)">/buku</span>
      </div></td>
      <td style="font-size:11px;color:var(--text3)">
        Handy <b>${fmt(Math.round(r.pages[p]*CETAK_F.handy))}</b> ·
        Minimal <b>${fmt(Math.round(r.pages[p]*CETAK_F.minimal))}</b> ·
        Large <b>${fmt(Math.round(r.pages[p]*CETAK_F.large))}</b>
      </td>
    </tr>`).join('');
  const wrap = document.getElementById('cetak-table-wrap');
  if(!wrap) return;
  wrap.innerHTML=`
    <div style="font-size:12px;color:var(--accent);font-weight:600;margin-bottom:8px">${r.label}</div>
    <table style="width:100%">
      <thead><tr><th>Halaman</th><th>Harga Base/Buku</th><th>Efektif per paket (setelah faktor)</th></tr></thead>
      <tbody>${rows}</tbody>
    </table>`;
}

function saveCetakBase(){
  saveSettingsToAPI('cetak_base', CETAK_BASE).then(success => {
    if(success) kalcUpdate();
  });
}

function resetCetakBase(){
  CETAK_BASE = DEF_CETAK_BASE.map(r=>({...r, pages:{...r.pages}}));
  saveSettingsToAPI('cetak_base', null).then(success => {
    if(success) {
      renderCetakTable();
      kalcUpdate();
    }
  });
}

function estCetak(siswa, pages, pkg='handy'){
  try {
    const idx = getCetakRangeIdx(siswa);
    const r = CETAK_BASE[idx]?.pages;
    if(!r) return 0;
    const keys = Object.keys(r).map(Number).sort((a,b)=>a-b);
    if(!keys.length) return 0;
    const closest = keys.reduce((a,b)=>Math.abs(b-pages)<Math.abs(a-pages)?b:a);
    return Math.round((r[closest]||0) * (CETAK_F[pkg]||1));
  } catch(e){ return 0; }
}

function marginBadge(p){if(p>=70)return'<span class="badge bsuc">Excellent</span>';if(p>=55)return'<span class="badge binf">Baik</span>';if(p>=40)return'<span class="badge bwar">Cukup</span>';return'<span class="badge bdan">Tipis</span>'}
function bc(p){return p>=70?'#2D7A4A':p>=55?'#2A6B8A':p>=40?'#8A5F1A':'#A02020'}
function miniBar(p){const c=bc(p);return`<div style="display:flex;align-items:center;gap:5px"><div style="width:75px;height:6px;background:var(--border);border-radius:3px;overflow:hidden"><div style="width:${Math.min(p,100)}%;height:100%;background:${c};border-radius:3px"></div></div><span style="font-size:12px;color:${c};font-weight:500">${p.toFixed(0)}%</span></div>`}

// ============================================================
// API HELPERS — replace localStorage
// ============================================================
const API_BASE = '/api';

// Toast notification system
let toastTimeout = null;
function showToast(message, type = 'success', duration = 3000) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:300px';
    document.body.appendChild(container);
  }
  
  const toast = document.createElement('div');
  const bgColor = type === 'success' ? '#2D7A4A' : type === 'error' ? '#A02020' : '#2A6B8A';
  const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
  
  toast.style.cssText = `
    background: ${bgColor};
    color: white;
    padding: 14px 16px;
    border-radius: 8px;
    margin-bottom: 8px;
    font-size: 13px;
    animation: slideInRight 0.3s ease-out;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    display: flex;
    align-items: center;
    gap: 8px;
  `;
  toast.innerHTML = `<span style="font-weight:600;font-size:16px">${icon}</span><span>${message}</span>`;
  
  container.appendChild(toast);
  
  if (toastTimeout) clearTimeout(toastTimeout);
  toastTimeout = setTimeout(() => {
    toast.style.animation = 'slideOutRight 0.3s ease-out';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

async function saveSettingsToAPI(key, value) {
  try {
    const res = await fetch(API_BASE + '/settings.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({[key]: value})
    });
    const json = await res.json();
    if (json.success) {
      showToast('Data berhasil disimpan', 'success');
      return true;
    } else {
      showToast(json.message || 'Gagal menyimpan data', 'error');
      return false;
    }
  } catch(e) { 
    console.warn('Settings save failed:', e);
    showToast('Error: ' + e.message, 'error');
    return false;
  }
}

async function loadSettingsFromAPI() {
  try {
    const res = await fetch(API_BASE + '/settings.php');
    const json = await res.json();
    if (json.data) {
      if (json.data.overhead) Object.assign(OH, json.data.overhead);
      if (json.data.cetak_f) Object.assign(CETAK_F, json.data.cetak_f);
      if (json.data.alc_f) Object.assign(ALC_F, json.data.alc_f);
      if (json.data.cetak_base && Array.isArray(json.data.cetak_base) && json.data.cetak_base[0]?.pages) {
        CETAK_BASE = json.data.cetak_base;
      }
      if (json.data.fs_prices) Object.assign(FS, json.data.fs_prices);
      if (json.data.grad_packages) GRAD.packages = json.data.grad_packages;
      if (json.data.grad_addons) GRAD.addons = json.data.grad_addons;
      if (json.data.grad_cetak) GRAD.cetak = json.data.grad_cetak;
    }
  } catch(e) { console.warn('Settings load failed:', e); }
}

// Penawaran API
let penawaranList = [];

async function loadPenawaranFromAPI() {
  try {
    const res = await fetch(API_BASE + '/penawaran.php');
    const json = await res.json();
    penawaranList = (json.data || []).map(p => ({
      id: p.id, nama: p.nama_klien, paket: p.paket,
      harga: parseInt(p.harga)||0, hargaSebelumDiskon: parseInt(p.harga_sebelum_diskon)||0,
      siswa: parseInt(p.jumlah_siswa)||0, catatan: p.catatan||'',
      status: p.status||'pending', addedBy: p.added_by_name||'',
      ts: new Date(p.created_at).toLocaleDateString('id-ID'),
      tsRaw: new Date(p.created_at).getTime()
    }));
  } catch(e) { console.warn('Penawaran load failed:', e); }
}

async function savePenawaranAPI(data) {
  try {
    const res = await fetch(API_BASE + '/penawaran.php', {
      method: 'POST', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        nama_klien: data.nama, paket: data.paket, harga: data.harga,
        harga_sebelum_diskon: data.hargaSebelumDiskon||0,
        jumlah_siswa: data.siswa, catatan: data.catatan, status: data.status||'pending'
      })
    });
    const result = await res.json();
    if (result.success || result.id) {
      showToast('Penawaran berhasil disimpan', 'success');
    } else {
      showToast(result.error || 'Gagal menyimpan penawaran', 'error');
    }
    return result;
  } catch(e) { 
    console.warn('Save failed:', e);
    showToast('Error: ' + e.message, 'error');
    return {}; 
  }
}

async function updatePenawaranAPI(id, data) {
  try {
    const res = await fetch(API_BASE + '/penawaran.php', {
      method: 'PUT', headers: {'Content-Type':'application/json'},
      body: JSON.stringify({id, ...data})
    });
    const result = await res.json();
    if (result.success || res.ok) {
      showToast('Penawaran berhasil diperbarui', 'success');
    } else {
      showToast(result.error || 'Gagal memperbarui penawaran', 'error');
    }
  } catch(e) { 
    console.warn('Update failed:', e);
    showToast('Error: ' + e.message, 'error');
  }
}

async function deletePenawaranAPI(id) {
  try {
    const res = await fetch(API_BASE + '/penawaran.php?id=' + id, {method:'DELETE'});
    const result = await res.json();
    if (result.success || res.ok) {
      showToast('Penawaran berhasil dihapus', 'success');
    } else {
      showToast(result.error || 'Gagal menghapus penawaran', 'error');
    }
  } catch(e) { 
    console.warn('Delete failed:', e);
    showToast('Error: ' + e.message, 'error');
  }
}

// ============================================================
// NAVIGATION
// ============================================================
function goPageCore(id, el){
  window.location.href = '/pages/' + id + '.php';
}

const isManager = currentUser?.isManager;
const isAdmin = currentUser?.isAdmin;
const STAFF_PAGES = ['kalkulator','proyek'];

function goPage(id, el){
  if(!currentUser) return;
  if(!isManager && !STAFF_PAGES.includes(id)) return;
  goPageCore(id, el);
}

// ============================================================
// RINGKASAN
// ============================================================
function renderRingkasan(){
  console.log('=== renderRingkasan called ===');
  console.log('OH.operasional:', OH.operasional, '(type:', typeof OH.operasional, ')');
  const oh=OH.total;
  const {harga:h150}=getFSPrice('handy',150);
  const c150=estCetak(150,60,'handy');
  const mb=h150-c150;
  const bepP=oh/(mb*150);
  document.getElementById('metrics-ov').innerHTML=`
    <div class="m"><div class="ml">Overhead Bulanan</div><div class="mv acc">${fmtM(oh)}</div><div class="ms">total pengeluaran tim & ops</div></div>
    <div class="m"><div class="ml">BEP (150 siswa)</div><div class="mv war">${bepP.toFixed(1)} proyek</div><div class="ms">min. proyek/bulan</div></div>
    <div class="m"><div class="ml">Gross Margin Avg</div><div class="mv suc">68–80%</div><div class="ms">full service, 30–150 siswa</div></div>
    <div class="m"><div class="ml">Net (3 proyek)</div><div class="mv suc">${fmtM(3*150*h150-3*150*c150-oh)}</div><div class="ms">estimasi @150 siswa</div></div>`;
  const ohItems=[['Designer',OH.designer||0],['Marketing',OH.marketing||0],['Creative Prod.',OH.creative||0],['Project Mgr',OH.pm||0],['Social Media',OH.sosmed||0],['Freelance',OH.freelance||0],['Operasional',OH.operasional||0]];
  console.log('ohItems:', ohItems);
  const mx=Math.max(...ohItems.map(x=>x[1]));
  const cols=['#2A6B8A','#C85B2A','#2D7A4A','#8A5F1A','#6B3A8A','#4A7A6B','#888'];
  document.getElementById('oh-bars').innerHTML=ohItems.map(([l,v],i)=>{
    const val=parseInt(v)||0;
    console.log(`Rendering ${l}: value=${v}, parsed=${val}, formatted=${fmtM(val)}`);
    return `<div class="br"><div class="bl">${l}</div><div class="bt"><div class="bf" style="width:${mx>0?val/mx*100:0}%;background:${cols[i]}"></div></div><div class="bv">${fmtM(val)}</div></div>`;
  }).join('');
  const komps=[['Foto+Stylist+Prop',30,'#C85B2A'],['Cetak+Shipping',25,'#2A6B8A'],['Desain Layout',20,'#2D7A4A'],['Editing Foto',15,'#8A5F1A'],['PM+Overhead',7,'#6B3A8A'],['E-Book',3,'#888']];
  document.getElementById('komp-bars').innerHTML=komps.map(([l,p,c])=>`<div class="br"><div class="bl">${l}</div><div class="bt"><div class="bf" style="width:${p}%;background:${c}"></div></div><div class="bv">${p}%</div></div>`).join('');
  let bepHTML='';
  for(const[lbl,np,ns,hp]of[['Konservatif',3,150,335000],['Moderat',5,150,335000],['Optimis',8,150,335000]]){
    const rev=np*ns*hp,cogs=np*ns*estCetak(ns,60,'handy'),gp=rev-cogs,net=gp-oh,ok=net>=0;
    bepHTML+=`<div style="border:1px solid var(--border);border-radius:var(--r);padding:13px"><div style="font-size:12px;font-weight:600;color:var(--text3);margin-bottom:6px">${lbl}</div><div style="font-size:11px;color:var(--text3)">${np} proyek × ${ns} siswa</div><div style="margin-top:7px;display:flex;flex-direction:column;gap:3px;font-size:12px"><div style="display:flex;justify-content:space-between"><span style="color:var(--text2)">Revenue</span><span>${fmtM(rev)}</span></div><div style="display:flex;justify-content:space-between"><span style="color:var(--text2)">COGS</span><span style="color:var(--danger)">-${fmtM(cogs)}</span></div><div style="display:flex;justify-content:space-between"><span style="color:var(--text2)">Gross</span><span>${fmtM(gp)}</span></div><div style="display:flex;justify-content:space-between"><span style="color:var(--text2)">Overhead</span><span style="color:var(--danger)">-${fmtM(oh)}</span></div><div style="display:flex;justify-content:space-between;font-weight:600;margin-top:4px;padding-top:4px;border-top:1px solid var(--border)"><span>Net</span><span style="color:${ok?'var(--success)':'var(--danger)'}">${ok?'':'-'}${fmtM(Math.abs(net))}</span></div></div></div>`;
  }
  document.getElementById('bep-sc').innerHTML=bepHTML;
}

// ============================================================
// FULL SERVICE
// ============================================================
function setPkg(p){
  curFSPkg=p;
  ['handy','minimal','large'].forEach(x=>document.getElementById('btn-'+x).className='btn bsm '+(x===p?'bp':'bs'));
  renderFS();
}

function suggestedPrice(siswa, cetak, grossTotal, ohPerProyek) {
  const minFromNet = cetak + (ohPerProyek / siswa) * 1.3;
  const minFromMargin = cetak / 0.45;
  const raw = Math.max(minFromNet, minFromMargin);
  return Math.ceil(raw / 5000) * 5000;
}

// Save fullservice pricing data to API
function saveFS() {
  const body = { fs_prices: FS };
  fetch(API_BASE + '/settings.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(body)
  })
  .then(res => res.json())
  .then(json => {
    if(json.success) {
      showToast('Harga fullservice berhasil disimpan', 'success');
    } else {
      showToast(json.message || 'Gagal menyimpan harga', 'error');
    }
  })
  .catch(e => {
    console.error('Save failed:', e);
    showToast('Error: ' + e.message, 'error');
  });
}

function renderFS(){
  const rows = FS[curFSPkg];
  const nProyek = parseInt(document.getElementById('fs-nproyek')?.value) || 4;
  const ohPerProyek = OH.total / nProyek;
  let insightList = [];
  let prevHarga = 9999999;

  document.getElementById('fs-body').innerHTML = rows.map(([lo,hi,harga,pages],i) => {
    const mid = Math.round((lo+hi)/2);
    const cetak = estCetak(mid, pages, curFSPkg);
    const grossBuku = harga - cetak;
    const pct = grossBuku / harga * 100;
    const grossTotal = grossBuku * mid;
    const netProyek = grossTotal - ohPerProyek;
    const netOk = netProyek > 0;
    const isAnomaly = harga > prevHarga;
    prevHarga = harga;

    let statusBadge, rowClass = '', saranCell = '';
    if (pct >= 60 && netOk) statusBadge = '<span class="badge bsuc">✅ Bagus</span>';
    else if (pct >= 50 && netOk) statusBadge = '<span class="badge binf">🟡 Cukup</span>';
    else if (netOk && pct >= 40) {
      statusBadge = '<span class="badge bwar">🟠 Perlu Cek</span>';
      const saran = suggestedPrice(mid, cetak, grossTotal, ohPerProyek);
      if (saran > harga) saranCell = `<span style="color:var(--accent);font-weight:600">${fmt(saran)}</span><div style="font-size:10px;color:var(--text3)">+${fmt(saran-harga)}/buku</div>`;
    } else {
      statusBadge = '<span class="badge bdan">🔴 Kritis</span>';
      rowClass = 'highlight-row';
      const saran = suggestedPrice(mid, cetak, grossTotal, ohPerProyek);
      saranCell = `<span style="color:var(--danger);font-weight:600">${fmt(saran)}</span><div style="font-size:10px;color:var(--text3)">+${fmt(saran-harga)}/buku</div>`;
      insightList.push({lo,hi,harga,saran,pct,grossTotal,netProyek,reason:pct<30?'margin sangat rendah':'net proyek negatif'});
    }
    if (isAnomaly && i > 0) {
      insightList.push({lo,hi,harga,anomaly:true,prevLo:rows[i-1][0],prevHi:rows[i-1][1],prevHarga:rows[i-1][2]});
      rowClass = 'highlight-row';
    }
    const netColor = netOk ? 'var(--success)' : 'var(--danger)';
    return `<tr class="${rowClass}">
      <td><b>${lo}–${hi}</b>${isAnomaly&&i>0?'<span style="font-size:10px;color:var(--danger);margin-left:4px">⚠ anomali</span>':''}</td>
      <td><input class="edi" type="number" value="${harga}" onchange="FS['${curFSPkg}'][${i}][2]=parseInt(this.value);saveFS();renderFS()" style="width:90px;text-align:right;font-weight:600"></td>
      <td><input class="edi" type="number" value="${pages}" onchange="FS['${curFSPkg}'][${i}][3]=parseInt(this.value);saveFS();renderFS()" style="width:55px;text-align:center"> hal</td>
      <td style="color:var(--danger)">${fmt(cetak)}</td>
      <td style="color:${grossBuku>0?'var(--success)':'var(--danger)'}">${fmt(grossBuku)}</td>
      <td>${miniBar(pct)}</td>
      <td style="font-weight:500">${fmtM(grossTotal)}</td>
      <td style="color:${netColor};font-weight:500">${netOk?'+':''}${fmtM(netProyek)}</td>
      <td>${statusBadge}</td>
      <td>${saranCell || '<span style="color:var(--text3);font-size:12px">—</span>'}</td>
    </tr>`;
  }).join('');

  const insightBox = document.getElementById('fs-insights');
  if (insightList.length === 0) {
    insightBox.innerHTML = '<div style="font-size:13px;color:var(--success)">✓ Semua range sudah dalam kondisi baik berdasarkan asumsi ' + nProyek + ' proyek aktif/bulan.</div>';
    return;
  }
  insightBox.innerHTML = insightList.map(ins => {
    if (ins.anomaly) return `<div style="margin-bottom:10px;padding:10px 12px;background:var(--danger-bg);border-radius:8px;font-size:13px"><b style="color:var(--danger)">⚠ Anomali Harga: ${ins.lo}–${ins.hi} siswa</b><br><span style="color:var(--text2)">Harga <b>${fmt(ins.harga)}</b> lebih <b>mahal</b> dari range sebelumnya (${ins.prevLo}–${ins.prevHi}: ${fmt(ins.prevHarga)})</span></div>`;
    return `<div style="margin-bottom:10px;padding:10px 12px;background:var(--warning-bg);border-radius:8px;font-size:13px"><b style="color:var(--warning)">${ins.lo}–${ins.hi} siswa — ${ins.reason}</b><br><span style="color:var(--text2)">Gross margin ${ins.pct.toFixed(0)}%, net per proyek ${ins.netProyek>=0?'+':''}${fmtM(ins.netProyek)}. Saran harga minimal: <b>${fmt(ins.saran)}</b>.</span></div>`;
  }).join('');
}

// ============================================================
// PENGATURAN PAGE — render & save functions
// ============================================================
function renderPengaturan(){
  // Render overhead fields
  const ovEl = document.getElementById('ov-oh');
  if(ovEl){
    const fields = ['designer', 'marketing', 'creative', 'pm', 'sosmed', 'freelance', 'ops'];
    const labels = ['Designer', 'Marketing', 'Creative', 'PM', 'Social Media', 'Freelance', 'Ops'];
    ovEl.innerHTML = fields.map((f, i) => `
      <div class="fg">
        <label class="fl">${labels[i]}</label>
        <input type="number" id="ov-${f}" value="${OH[f] || 0}" placeholder="0" style="text-align:right">
      </div>
    `).join('');
  }

  // Jika CETAK_BASE kosong (DB belum punya data), pakai default
  if (!CETAK_BASE || CETAK_BASE.length === 0) {
    CETAK_BASE = DEF_CETAK_BASE.map(r => ({...r, pages: {...r.pages}}));
  }

  // Render cetak range buttons
  const btnsEl = document.getElementById('cetak-range-btns');
  if(btnsEl && CETAK_BASE.length > 0){
    btnsEl.innerHTML = CETAK_BASE.map((r, i) => `
      <button class="btn ${i===curCetakRange?'bp':'bs'} bsm" onclick="setCetakRange(${i}, this)" style="font-size:12px">${r.label}</button>
    `).join('');
    renderCetakTable();
  }
}

function saveOH(){
  const fields = ['designer', 'marketing', 'creative', 'pm', 'sosmed', 'freelance', 'ops'];
  fields.forEach(f => {
    const val = parseInt(document.getElementById(`ov-${f}`)?.value) || 0;
    OH[f] = val;
  });
  OH.total = fields.reduce((s, f) => s + OH[f], 0);
  saveSettingsToAPI('overhead', OH).then(success => {
    if(success) kalcUpdate();
  });
}

function resetOH(){
  const defOH = {designer:0, marketing:0, creative:0, pm:0, sosmed:0, freelance:0, ops:0, total:0};
  Object.assign(OH, defOH);
  saveSettingsToAPI('overhead', OH).then(success => {
    if(success) renderPengaturan();
  });
}

function saveCetak(){
  CETAK_F.handy = parseFloat(document.getElementById('ov-cetak-handy')?.value) || 1.0;
  CETAK_F.minimal = parseFloat(document.getElementById('ov-cetak-minimal')?.value) || 0.95;
  CETAK_F.large = parseFloat(document.getElementById('ov-cetak-large')?.value) || 1.15;
  saveSettingsToAPI('cetak_f', CETAK_F).then(success => {
    if(success) kalcUpdate();
  });
}

function saveALC(){
  ALC_F.ebook = parseFloat(document.getElementById('ov-ac-ebook')?.value) / 100 || 0.72;
  ALC_F.editcetak = parseFloat(document.getElementById('ov-ac-editcetak')?.value) / 100 || 0.62;
  ALC_F.desain = parseFloat(document.getElementById('ov-ac-desain')?.value) / 100 || 0.22;
  ALC_F.cetakonly = parseFloat(document.getElementById('ov-ac-cetakonly')?.value) / 100 || 0.30;
  saveSettingsToAPI('alc_f', ALC_F);
}

function saveGrad(){
  saveSettingsToAPI('grad_packages', GRAD.packages);
}

function resetGrad(){
  saveSettingsToAPI('grad_packages', null);
}

function saveGradAddon(){
  saveSettingsToAPI('grad_addons', GRAD.addons);
}

// ============================================================
// MASTER DATA FUNCTIONS — Update via /api/master-data.php
// ============================================================

// Fungsi universal untuk update master data
async function updateMasterData(type, data) {
    try {
        console.log('updateMasterData called with type:', type);
        console.log('Data to send:', data);
        console.log('MASTER_API URL:', MASTER_API);
        
        const payload = { type: type, data: data };
        console.log('Full payload:', JSON.stringify(payload));
        
        const response = await fetch(MASTER_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        const responseText = await response.text();
        console.log('Raw response text:', responseText);
        
        const result = JSON.parse(responseText);
        console.log('Parsed response:', result);
        
        if (result.success) {
            console.log('✓ Master data updated:', type);
            return true;
        } else {
            console.error('Failed to update master data:', result.error);
            showToast('✕ Server error: ' + (result.error || 'Unknown error'), 'error');
            return false;
        }
    } catch (error) {
        console.error('Error updating master data:', error);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        showToast('✕ Network error: ' + error.message, 'error');
        return false;
    }
}

// Fungsi untuk refresh data dari master API
async function refreshMasterData() {
    try {
        const response = await fetch(MASTER_API + '?action=get_all');
        const result = await response.json();
        if (result.success && result.data) {
            const data = result.data;
            
            // Update semua global variables
            if (data.overhead)    OH = {...OH, ...flattenObject(data.overhead)};
            if (data.cetak_f)    CETAK_F = {...data.cetak_f};
            if (data.cetak_base) CETAK_BASE = data.cetak_base;
            if (data.alc_f)      ALC_F = {...data.alc_f};
            if (data.fs)         FS = {...data.fs};
            if (data.addon_data) ADDON_DATA = {...data.addon_data};
            if (data.grad)       GRAD = {...data.grad};
            if (data.bonus_fasilitas) BONUS_FASILITAS = {...data.bonus_fasilitas};
            if (data.payment_terms)   PT = data.payment_terms;
            
            console.log('✓ Master data refreshed from API');
            return true;
        }
    } catch (error) {
        console.error('Error refreshing master data:', error);
    }
    return false;
}

// Helper untuk flatten object (convery nested object to flat)
function flattenObject(obj) {
    const result = {};
    for (const key in obj) {
        const val = obj[key];
        if (typeof val === 'object' && !Array.isArray(val)) {
            Object.assign(result, flattenObject(val));
        } else {
            result[key.toLowerCase().replace(/[. ]/g, '')] = val;
        }
    }
    return result;
}
