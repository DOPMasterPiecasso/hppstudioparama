// ============================================================
// À LA CARTE
// ============================================================
function renderAlacarte(){
  const pakets=[
    {num:'1',name:'E-Book Package',desc:'Foto+Editing+Desain, output file digital. Tanpa cetak fisik.',chips:['Foto','Fashion Stylist','Editing','Desain','E-Book'],no:['Cetak','Shipping'],key:'ebook',feat:true,margin:'62–68%'},
    {num:'2',name:'Edit+Desain+Cetak',desc:'Klien bawa foto sendiri. Parama handle editing, layout, cetak & kirim.',chips:['Editing','Desain','Cetak','Shipping'],no:['Foto','Fashion Stylist'],key:'editcetak',margin:'55–62%'},
    {num:'3a',name:'Foto Only (½ Hari)',desc:'Sesi foto max ~75 siswa. Fotografer + fashion stylist.',chips:['Foto','Fashion Stylist','Basic Edit'],no:['Desain','Cetak'],flatStr:'Rp3,5jt – 5jt',margin:'55–65%'},
    {num:'3b',name:'Foto Only (Full Day)',desc:'Sesi foto 76–150+ siswa. Full team seharian.',chips:['Foto','Fashion Stylist','Basic Edit'],no:['Desain','Cetak'],flatStr:'Rp6jt – 9jt',margin:'55–65%'},
    {num:'4a',name:'Drone Video',desc:'Video drone 1–2 menit.',chips:['Shooting','Edit Video'],no:['Foto','Desain','Cetak'],flatStr:'Rp1,5jt',margin:'—'},
    {num:'4b',name:'Docudrama Video',desc:'Video cerita angkatan 5–10 menit.',chips:['Shooting','Edit Video'],no:['Foto','Desain','Cetak'],flatStr:'Rp3jt',margin:'—'},
    {num:'5',name:'Desain Only',desc:'Klien bawa semua konten. Parama hanya layout buku.',chips:['Desain','E-Book'],no:['Foto','Editing','Cetak'],key:'desain',margin:'55–65%'},
    {num:'6',name:'Cetak Only',desc:'Klien sudah punya file siap cetak. Parama cetak & kirim saja.',chips:['Cetak','Shipping'],no:['Foto','Editing','Desain'],key:'cetakonly',margin:'30–45%'},
  ];
  document.getElementById('alc-grid').innerHTML=pakets.map(p=>{
    let price='';
    if(p.flatStr) price=`<div class="pp">${p.flatStr}</div><div class="pps">flat / per project</div>`;
    else{const f=ALC_F[p.key];const h=getFSPrice('handy',100).harga;const pb=Math.max(p.key==='desain'?50000:0,Math.round(h*f));price=`<div class="pp">${fmt(pb)}/siswa</div><div class="pps">100 siswa, Handy Book acuan</div>`}
    return `<div class="pkc ${p.feat?'feat':''}"><div class="pnum">Paket ${p.num}${p.feat?' — Populer':''}</div><div class="pname">${p.name}</div><div class="pdesc">${p.desc}</div><div style="margin-bottom:9px">${p.chips.map(c=>`<span class="chip cy">${c}</span>`).join('')}${p.no.map(c=>`<span class="chip cn">${c}</span>`).join('')}</div>${price}<div style="margin-top:7px"><span class="badge binf">Margin ~${p.margin}</span></div></div>`;
  }).join('');
  const h100=getFSPrice('handy',100).harga;
  const cmpData=[
    ['Full Service','✓','✓','✓','✓','✓',fmt(h100)+'/siswa','68–80%'],
    ['E-Book','✓','✓','✓','✓','—',fmt(Math.round(h100*ALC_F.ebook))+'/siswa','62–68%'],
    ['Edit+Desain+Cetak','—','✓','✓','✓','✓',fmt(Math.round(h100*ALC_F.editcetak))+'/siswa','55–62%'],
    ['Foto Only ½ Hari','✓','—','—','—','—','Rp3,5–5jt/sesi','55–65%'],
    ['Foto Only Full Day','✓','—','—','—','—','Rp6–9jt/sesi','55–65%'],
    ['Drone Video','—','—','—','—','—','Rp1,5jt/video','—'],
    ['Docudrama Video','—','—','—','—','—','Rp3jt/video','—'],
    ['Desain Only','—','—','✓','✓','—',fmt(Math.max(50000,Math.round(h100*ALC_F.desain)))+'/siswa','55–65%'],
    ['Cetak Only','—','—','—','—','✓',fmt(Math.max(30000,Math.round(h100*ALC_F.cetakonly)))+'/siswa','30–45%'],
  ];
  document.getElementById('alc-cmp').innerHTML=cmpData.map(r=>`<tr>${r.map((c,i)=>{let s=c;if(i>0&&i<6)s=c==='✓'?'<span style="color:var(--success);font-weight:600">✓</span>':'<span style="color:var(--border2)">—</span>';return`<td>${s}</td>`}).join('')}</tr>`).join('');
}

// ============================================================
// ADD-ON TABLE (editable)
// ============================================================
function renderAddon(){
  function finRow(a){return`<tr><td>${a.name}</td>${a.tiers.map((t,ti)=>`<td><input class="edi" type="number" value="${t[2]}" onchange="ADDON_DATA.finishing[${ADDON_DATA.finishing.findIndex(x=>x.id===a.id)}].tiers[${ti}][2]=parseInt(this.value)" style="width:70px;text-align:right"></td>`).join('')}</tr>`}
  document.getElementById('adn-finishing').innerHTML=ADDON_DATA.finishing.map(a=>finRow(a)).join('');
  document.getElementById('adn-kertas').innerHTML=ADDON_DATA.kertas.map((a,ai)=>`<tr><td>${a.name}</td>${a.tiers.map((t,ti)=>`<td><input class="edi" type="number" value="${t[2]}" onchange="ADDON_DATA.kertas[${ai}].tiers[${ti}][2]=parseInt(this.value)" style="width:60px;text-align:right"><span style="font-size:11px;color:var(--text3)">/hal</span></td>`).join('')}</tr>`).join('');
  document.getElementById('adn-halaman').innerHTML=ADDON_DATA.halaman.map((a,ai)=>`<tr><td>25–50 order</td><td><input class="edi" type="number" value="${a.tiers[0][2]}" onchange="ADDON_DATA.halaman[0].tiers[0][2]=parseInt(this.value)" style="width:70px;text-align:right"></td></tr><tr><td>51–100 order</td><td><input class="edi" type="number" value="${a.tiers[1][2]}" onchange="ADDON_DATA.halaman[0].tiers[1][2]=parseInt(this.value)" style="width:70px;text-align:right"></td></tr><tr><td>101–150 order</td><td><input class="edi" type="number" value="${a.tiers[2][2]}" onchange="ADDON_DATA.halaman[0].tiers[2][2]=parseInt(this.value)" style="width:70px;text-align:right"></td></tr><tr><td>&gt;151 order</td><td><input class="edi" type="number" value="${a.tiers[3][2]}" onchange="ADDON_DATA.halaman[0].tiers[3][2]=parseInt(this.value)" style="width:70px;text-align:right"></td></tr>`).join('');
  document.getElementById('adn-video').innerHTML=ADDON_DATA.video.map((a,ai)=>`<tr><td>${a.name}</td><td>${a.id==='drone'?'1–2 menit':'5–10 menit'}</td><td><input class="edi" type="number" value="${a.price}" onchange="ADDON_DATA.video[${ai}].price=parseInt(this.value)" style="width:90px;text-align:right"></td></tr>`).join('');
  function pkgRow(arr,grp){return arr.map((a,i)=>`<tr><td>${a.name}</td>${a.tiers.map((t,ti)=>`<td><input class="edi" type="number" value="${t[2]}" onchange="ADDON_DATA.${grp}[${i}].tiers[${ti}][2]=parseInt(this.value)" style="width:60px;text-align:right"></td>`).join('')}</tr>`).join('')}
  document.getElementById('adn-pkg1').innerHTML=pkgRow(ADDON_DATA.pkg1,'pkg1');
  document.getElementById('adn-pkg2').innerHTML=pkgRow(ADDON_DATA.pkg2,'pkg2');
}

// ============================================================
// GRADUATION
// ============================================================
function renderGraduation(){
  const sel=[document.getElementById('gc-pkg'),document.getElementById('k-grad-pkg')];
  const opts='<option value="">— Pilih paket —</option>'+GRAD.packages.map(p=>`<option value="${p.id}">${p.name} — ${fmt(p.price)}</option>`).join('');
  sel.forEach(s=>{if(s)s.innerHTML=opts});

  document.getElementById('grad-grid').innerHTML=GRAD.packages.map((p,pi)=>`
    <div class="pkc ${p.color==='feat'?'feat-grad':''} ${p.color==='acc'?'feat':''}">
      <div class="pnum">${p.color==='feat'?'Complete Package':''}</div>
      <div class="pname">${p.name}</div>
      <div class="pdesc" style="font-size:11px">${p.desc}</div>
      <div class="pp grad" style="margin-top:10px">
        <input class="edi" type="number" value="${p.price}" onchange="GRAD.packages[${pi}].price=parseInt(this.value);renderGraduation()" style="width:100px;font-size:18px;font-family:var(--font-d);color:var(--grad);text-align:right;border-color:var(--grad-light)">
      </div>
      <div class="pps">flat per event • incl. transport jabodetabek</div>
    </div>`).join('');

  document.getElementById('grad-addon').innerHTML=GRAD.addons.map((a,ai)=>`
    <tr><td>${a.name}</td><td><input class="edi" type="number" value="${a.price}" onchange="GRAD.addons[${ai}].price=parseInt(this.value)" style="width:90px;text-align:right"></td></tr>`).join('');
  document.getElementById('grad-cetak').innerHTML=GRAD.cetak.map((c,ci)=>`
    <tr><td>${c.name}</td><td><input class="edi" type="number" value="${c.price}" onchange="GRAD.cetak[${ci}].price=parseInt(this.value)" style="width:70px;text-align:right"><span style="font-size:11px;color:var(--text3)">/lembar</span></td></tr>`).join('');

  document.getElementById('gc-addons').innerHTML=`
    <div class="ct">Add-on Graduation</div>
    ${GRAD.addons.map(a=>`<div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid var(--border);font-size:13px"><input type="checkbox" id="gchk-${a.id}" onchange="gcUpdate()"><span style="flex:1">${a.name}</span><span style="color:var(--text3);font-size:12px">${fmt(a.price)}</span></div>`).join('')}
    <div class="ct mt10">Cetak Foto Tambahan</div>
    ${GRAD.cetak.map(c=>`<div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px"><input type="checkbox" id="gchk-${c.id}" onchange="gcUpdate()"><span style="flex:1">${c.name}</span><span style="color:var(--text3);font-size:12px">${fmt(c.price)}/lembar</span><input type="number" id="gqty-${c.id}" value="1" min="1" style="width:50px;display:none" oninput="gcUpdate()"></div>`).join('')}`;
  GRAD.cetak.forEach(c=>{
    const chk=document.getElementById('gchk-'+c.id);
    if(chk) chk.addEventListener('change',()=>{const qi=document.getElementById('gqty-'+c.id);if(qi)qi.style.display=chk.checked?'':'none'});
  });
  gcUpdate();
}

function gcUpdate(){
  const pkgId=document.getElementById('gc-pkg')?.value;
  if(!pkgId){document.getElementById('gc-result').innerHTML='<div style="color:var(--text3);font-size:13px">Pilih paket terlebih dahulu.</div>';document.getElementById('gc-total').textContent='—';document.getElementById('gc-note').textContent='';return}
  const pkg=GRAD.packages.find(p=>p.id===pkgId);
  if(!pkg) return;
  let rows=`<div class="rr"><span class="rl">Paket ${pkg.name}</span><span class="rv">${fmt(pkg.price)}</span></div>`;
  let total=pkg.price;
  GRAD.addons.forEach(a=>{const chk=document.getElementById('gchk-'+a.id);if(chk&&chk.checked){rows+=`<div class="rr"><span class="rl">+ ${a.name}</span><span class="rv gr">+${fmt(a.price)}</span></div>`;total+=a.price}});
  GRAD.cetak.forEach(c=>{const chk=document.getElementById('gchk-'+c.id);if(chk&&chk.checked){const qty=parseInt(document.getElementById('gqty-'+c.id)?.value)||1;const sub=c.price*qty;rows+=`<div class="rr"><span class="rl">+ ${c.name} ×${qty}</span><span class="rv gr">+${fmt(sub)}</span></div>`;total+=sub}});
  document.getElementById('gc-result').innerHTML=rows;
  document.getElementById('gc-total').textContent=fmt(total);
  document.getElementById('gc-note').textContent='Harga sudah termasuk transport Jabodetabek. Luar Jabodetabek + biaya transport.';
}

// ============================================================
// KALKULATOR
// ============================================================
function kCatChange(){
  const cat=document.getElementById('k-cat').value;
  document.getElementById('k-type-row').style.display=cat==='bukutahunan'?'':'none';
  document.getElementById('k-grad-row').style.display=cat==='graduation'?'':'none';
  document.getElementById('k-siswa-row').style.display=cat==='bukutahunan'?'':'none';
  document.getElementById('k-hal-row').style.display=cat==='bukutahunan'?'':'none';
  kalcUpdate();
}

function buildAddonList(){
  const gs=document.getElementById('k-grad-pkg');
  if(gs) gs.innerHTML='<option value="">— Pilih paket —</option>'+GRAD.packages.map(p=>`<option value="${p.id}">${p.name} — ${fmt(p.price)}</option>`).join('');
  const grps=[['Finishing',ADDON_DATA.finishing],['Kertas',ADDON_DATA.kertas],['Halaman Tambahan',ADDON_DATA.halaman],['Video',ADDON_DATA.video],['Packaging Standard',ADDON_DATA.pkg1],['Custom Box',ADDON_DATA.pkg2]];
  let html='';
  grps.forEach(([g,arr])=>{
    html+=`<div style="font-size:10px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.06em;padding:9px 0 3px">${g}</div>`;
    arr.forEach(a=>{
      let sub='';
      if(a.type==='extra_hal') sub=`<div style="padding-left:22px;margin-top:3px;padding-bottom:4px"><input type="number" id="xhal-${a.id}" value="10" min="1" max="100" style="width:55px" oninput="kalcUpdate()"> halaman</div>`;
      html+=`<div style="display:flex;align-items:center;gap:7px;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px"><input type="checkbox" id="chk-${a.id}" onchange="kalcUpdate()"><span style="flex:1">${a.name}</span><span style="color:var(--text3);font-size:11px" id="acp-${a.id}">—</span></div>${sub}`;
    });
  });
  html+=`<div style="font-size:10px;font-weight:600;color:var(--grad);text-transform:uppercase;letter-spacing:.06em;padding:9px 0 3px">Add-on Graduation</div>`;
  GRAD.addons.forEach(a=>{html+=`<div style="display:flex;align-items:center;gap:7px;padding:6px 0;border-bottom:1px solid var(--border);font-size:13px"><input type="checkbox" id="chk-${a.id}" onchange="kalcUpdate()"><span style="flex:1">${a.name}</span><span style="color:var(--text3);font-size:11px">${fmt(a.price)}</span></div>`});
  document.getElementById('k-addon-list').innerHTML=html;
}

function kalcUpdateCore(){
  const cat=document.getElementById('k-cat')?.value||'bukutahunan';
  const siswa=parseInt(document.getElementById('k-siswa')?.value)||100;

  if(cat==='graduation'){
    const pkgId=document.getElementById('k-grad-pkg')?.value;
    const pkg=GRAD.packages.find(p=>p.id===pkgId);
    let rows='',total=0,addonRows='';
    if(pkg){total=pkg.price;rows=`<div class="rr"><span class="rl">Paket ${pkg.name}</span><span class="rv">${fmt(pkg.price)}</span></div>`}
    GRAD.addons.forEach(a=>{const c=document.getElementById('chk-'+a.id);if(c&&c.checked){total+=a.price;addonRows+=`<div class="rr"><span class="rl">+ ${a.name}</span><span class="rv gr">+${fmt(a.price)}</span></div>`}});
    document.getElementById('k-result').innerHTML=rows+addonRows;
    document.getElementById('k-total').textContent=total>0?fmt(total):'—';
    document.getElementById('k-total').style.color='var(--grad)';
    document.getElementById('k-profit').innerHTML=`<div class="rr"><span class="rl">Tipe harga</span><span class="rv">Flat per event</span></div><div class="rr"><span class="rl">Transport</span><span class="rv">Included (Jabodetabek)</span></div>`;
    document.getElementById('k-verdict').innerHTML='';
    document.getElementById('k-note').textContent='Harga graduation flat per event, bukan per siswa.';
    return;
  }

  const type=document.getElementById('k-type').value;
  const cfg=ALC_CFG[type];
  document.getElementById('k-total').style.color='var(--accent)';

  const halInput=document.getElementById('k-hal');
  const showHal=(cfg?.fs)||(cfg?.bySiswa);
  if(cfg&&cfg.fs){
    const {pages}=getFSPrice(cfg.pkg,siswa);
    halInput.value=pages;
    document.getElementById('k-hal-info').textContent=`Otomatis: ${pages} halaman untuk ${siswa} siswa (dari pricelist)`;
  } else if(cfg&&cfg.bySiswa){
    const {pages}=getFSPrice('handy',siswa);
    halInput.value=pages;
    document.getElementById('k-hal-info').textContent=`Acuan: ${pages} halaman untuk ${siswa} siswa (ref. pricelist handy)`;
  } else {
    halInput.value='—';
    document.getElementById('k-hal-info').textContent='Halaman menyesuaikan paket';
  }
  const hal=cfg&&cfg.fs?getFSPrice(cfg.pkg,siswa).pages:(cfg&&cfg.bySiswa?getFSPrice('handy',siswa).pages:60);
  const isSiswa=cfg?.bySiswa;
  document.getElementById('k-siswa-row').style.display=isSiswa?'':'none';
  document.getElementById('k-hal-row').style.display=showHal?'':'none';

  const allAddons=[...ADDON_DATA.finishing,...ADDON_DATA.kertas,...ADDON_DATA.halaman,...ADDON_DATA.video,...ADDON_DATA.pkg1,...ADDON_DATA.pkg2];
  allAddons.forEach(a=>{
    const el=document.getElementById('acp-'+a.id);if(!el)return;
    if(a.type==='flat'||a.type==='flat_video') el.textContent=a.type==='flat_video'?fmtM(a.price):fmt(getTier(a.tiers,siswa))+'/buku';
    else if(a.type==='per_hal') el.textContent=getTier(a.tiers,siswa).toLocaleString('id-ID')+'/hal';
    else if(a.type==='extra_hal') el.textContent=getTier(a.tiers,siswa).toLocaleString('id-ID')+'/hal ekstra';
  });

  let basePB=0,baseFlat=0,baseLabel='';
  if(cfg?.fs){const{harga}=getFSPrice(cfg.pkg,siswa);basePB=harga;baseLabel=`${fmt(harga)}/buku (${siswa} siswa) • ${hal} hal`}
  else if(cfg?.bySiswa){const hB=getFSPrice('handy',siswa).harga;basePB=Math.max(cfg.minPerBuku||0,Math.round(hB*ALC_F[cfg.factor]));baseLabel=`${fmt(basePB)}/buku (${siswa} siswa) • ${hal} hal`}
  else if(cfg?.flat){baseFlat=(cfg.flat[0]+cfg.flat[1])/2;baseLabel=`${fmtM(cfg.flat[0])}${cfg.flat[0]!==cfg.flat[1]?' – '+fmtM(cfg.flat[1]):''} (flat/proyek)`}

  let addonPB=0,addonVid=0,addonRows='';
  allAddons.forEach(a=>{
    const chk=document.getElementById('chk-'+a.id);if(!chk||!chk.checked)return;
    if(a.type==='flat'){const v=getTier(a.tiers,siswa);addonPB+=v;addonRows+=`<div class="rr"><span class="rl">+ ${a.name}</span><span class="rv gr">+${fmt(v)}/buku</span></div>`}
    else if(a.type==='per_hal'){const v=getTier(a.tiers,siswa)*hal;addonPB+=v;addonRows+=`<div class="rr"><span class="rl">+ ${a.name} (${hal}hal)</span><span class="rv gr">+${fmt(v)}/buku</span></div>`}
    else if(a.type==='extra_hal'){const eh=parseInt(document.getElementById('xhal-'+a.id)?.value)||0;const v=getTier(a.tiers,siswa)*eh;addonPB+=v;addonRows+=`<div class="rr"><span class="rl">+ ${eh} hal tambahan</span><span class="rv gr">+${fmt(v)}/buku</span></div>`}
    else if(a.type==='flat_video'){addonVid+=a.price;addonRows+=`<div class="rr"><span class="rl">+ ${a.name}</span><span class="rv gr">+${fmtM(a.price)} (flat)</span></div>`}
  });

  const totPB=basePB+addonPB;
  const totAll=isSiswa?totPB*siswa+addonVid:baseFlat+addonVid;
  document.getElementById('k-result').innerHTML=`<div class="rr"><span class="rl">Harga dasar</span><span class="rv">${baseLabel}</span></div>`+addonRows;
  document.getElementById('k-total').textContent=isSiswa?fmt(totAll):cfg?.flat?fmtM(cfg.flat[0])+(cfg.flat[0]!==cfg.flat[1]?' – '+fmtM(cfg.flat[1]):''):'—';
  document.getElementById('k-total-sub').textContent=addonVid>0?`*termasuk video ${fmtM(addonVid)} flat`:'';

  let profHTML='';
  if(cfg?.fs&&isSiswa){
    const cetak=estCetak(siswa,hal,cfg.pkg);
    const gB=basePB-cetak+addonPB;
    const gT=gB*siswa+addonVid;
    const net=gT-OH.total;
    const pct=gB/totPB*100;
    let cetakEl=document.getElementById('k-cetak-est');
    if(!cetakEl){cetakEl=document.createElement('span');cetakEl.id='k-cetak-est';cetakEl.style.display='none';document.body.appendChild(cetakEl)}
    cetakEl.dataset.cetak=cetak*siswa;
    profHTML=`<div class="rr"><span class="rl">Est. biaya cetak/buku</span><span class="rv rd">−${fmt(cetak)}</span></div><div class="rr"><span class="rl">Gross margin/buku</span><span class="rv gr">${fmt(gB)} (${pct.toFixed(0)}%)</span></div><div class="rr"><span class="rl">Gross total proyek</span><span class="rv gr">${fmt(gT)}</span></div><div class="rr"><span class="rl">Overhead bulanan</span><span class="rv rd">−${fmtM(OH.total)}</span></div><div class="rr tot"><span class="rl">Net (1 proyek/bln)</span><span class="rv ${net>=0?'gr':'rd'}">${net>=0?'':'-'}${fmtM(Math.abs(net))}</span></div>`;
    document.getElementById('k-verdict').innerHTML=pct>=60?`<div class="note suc">✓ Margin ${pct.toFixed(0)}% sehat.</div>`:pct>=40?`<div class="note warn">⚠ Margin ${pct.toFixed(0)}% tipis, perhatikan efisiensi.</div>`:`<div class="note dan">✗ Margin ${pct.toFixed(0)}% terlalu rendah.</div>`;
  } else {document.getElementById('k-verdict').innerHTML='';profHTML=`<div class="rr"><span class="rl">Tipe harga</span><span class="rv">${isSiswa?'Per siswa':'Flat per proyek'}</span></div>`}
  document.getElementById('k-profit').innerHTML=profHTML;
  const notes={'fs-handy':'Full service: foto, editing, desain, e-book, cetak & kirim.','fs-minimal':'Paket buku square 24×24cm, semua layanan.','fs-large':'Buku besar B4 25×35cm, semua layanan.','ac-ebook':'Tanpa cetak fisik. Klien terima file digital.','ac-editcetak':'⚠ Klien bawa foto sendiri — wajib SOP kualitas file.','ac-desain':'⚠ Klien bawa semua konten siap pakai.','ac-cetakonly':'⚠ Klien bawa file print-ready.','ac-fotohalf':'Sesi ½ hari, max ~75 siswa.','ac-fotofull':'Full day, 76–150+ siswa.','ac-videod':'Flat per video, bukan per siswa.','ac-videodoc':'Flat per video.'};
  document.getElementById('k-note').textContent=notes[type]||'';
}

function kalcUpdate(){ kalcUpdateCore(); applyDiskon(); }

// ============================================================
// ANALISIS
// ============================================================
function setAnTab(id,el){document.querySelectorAll('.tp').forEach(p=>p.classList.remove('active'));document.querySelectorAll('.tb').forEach(b=>b.classList.remove('active'));document.getElementById('an-'+id).classList.add('active');el.classList.add('active')}
function setAnPkg(p){curAnPkg=p;['handy','minimal','large'].forEach(x=>document.getElementById('abtn-'+x).className='btn bsm '+(x===p?'bp':'bs'));renderAnalisis()}

function renderAnalisis(){
  const oh=OH.total;
  document.getElementById('an-body').innerHTML=FS[curAnPkg].map(([lo,hi,harga,pages])=>{
    const mid=Math.round((lo+hi)/2);
    const cetak=estCetak(mid,pages,curAnPkg);
    const ohPB=oh/(mid*3);
    const gp=(harga-cetak)/harga*100;
    const np=Math.max(0,(harga-cetak-ohPB)/harga*100);
    return`<tr class="${gp<45?'dim':''}"><td><b>${lo}–${hi}</b></td><td><b>${fmt(harga)}</b></td><td style="color:var(--danger)">${fmt(cetak)}</td><td style="color:var(--text3)">${fmt(Math.round(ohPB))}</td><td>${miniBar(gp)}</td><td>${miniBar(np)}</td><td>${marginBadge(gp)}</td></tr>`;
  }).join('');
}

// ============================================================
// PENGATURAN
// ============================================================
function renderPengaturan(){
  const ohDefs=[['total','Total Overhead Bulanan'],['marketing','Marketing'],['creative','Creative Production'],['designer','Designer'],['pm','Project Manager'],['sosmed','Social Media'],['freelance','Freelance'],['ops','Operasional']];
  document.getElementById('ov-oh').innerHTML=ohDefs.map(([k,l])=>`<div class="fg"><label class="fl">${l}</label><div style="display:flex;align-items:center;gap:4px"><span style="font-size:12px;color:var(--text3)">Rp</span><input type="number" id="ov-${k}" value="${OH[k]}"></div></div>`).join('');
  document.getElementById('ov-grad').innerHTML=GRAD.packages.map(p=>`<div class="fg"><label class="fl" style="color:var(--grad)">${p.name}</label><div style="display:flex;align-items:center;gap:4px"><span style="font-size:12px;color:var(--text3)">Rp</span><input type="number" id="ovg-${p.id}" value="${p.price}"></div></div>`).join('');
  document.getElementById('ov-grad-addon').innerHTML=[...GRAD.addons,...GRAD.cetak].map(a=>`<div class="fg"><label class="fl">${a.name}</label><div style="display:flex;align-items:center;gap:4px"><span style="font-size:12px;color:var(--text3)">Rp</span><input type="number" id="ovga-${a.id}" value="${a.price}"></div></div>`).join('');
  const btnWrap = document.getElementById('cetak-range-btns');
  if(btnWrap) btnWrap.innerHTML = CETAK_BASE.map((r,i)=>`<button class="btn ${i===curCetakRange?'bp':'bs'} bsm" onclick="setCetakRange(${i},this)">${r.label}</button>`).join('');
  renderCetakTable();
  document.getElementById('ov-cetak-handy').value = CETAK_F.handy.toFixed(2);
  document.getElementById('ov-cetak-minimal').value = CETAK_F.minimal.toFixed(2);
  document.getElementById('ov-cetak-large').value = CETAK_F.large.toFixed(2);
  const acEl = document.getElementById('ov-ac-cetakonly');
  if(acEl) acEl.value = Math.round(ALC_F.cetakonly * 100);
}

function showStatus(){const s=document.getElementById('ov-status');s.style.display='block';setTimeout(()=>s.style.display='none',2000)}
function saveOH(){['total','marketing','creative','designer','pm','sosmed','freelance','ops'].forEach(k=>{const el=document.getElementById('ov-'+k);if(el)OH[k]=parseInt(el.value)||OH[k]});saveSettingsToAPI('overhead',OH);showStatus()}
function resetOH(){Object.assign(OH,{...DEF_OH});renderPengaturan();saveSettingsToAPI('overhead',null);showStatus()}
function saveCetak(){['handy','minimal','large'].forEach(k=>{const el=document.getElementById('ov-cetak-'+k);if(el)CETAK_F[k]=parseFloat(el.value)||1});saveSettingsToAPI('cetak_f',CETAK_F);showStatus()}
function saveALC(){['ebook','editcetak','desain','cetakonly'].forEach(k=>{const el=document.getElementById('ov-ac-'+k);if(el)ALC_F[k]=parseInt(el.value)/100});saveSettingsToAPI('alc_f',ALC_F);renderAlacarte();showStatus()}
function saveGrad(){GRAD.packages.forEach(p=>{const el=document.getElementById('ovg-'+p.id);if(el)p.price=parseInt(el.value)||p.price});saveSettingsToAPI('grad_packages',GRAD.packages);renderGraduation();showStatus()}
function resetGrad(){GRAD.packages=[{id:'gphv',name:'Photo & Video',price:4500000,desc:'2 Fotografer + 1 Videografer, 50 foto edited, video cinematic 2–4 mnt, G-Drive, 4 jam coverage, transport jabodetabek',color:'acc'},{id:'gvideo',name:'Video Only',price:2000000,desc:'1 Videografer, video cinematic 2–5 mnt, G-Drive, 4 jam coverage, transport jabodetabek',color:''},{id:'gphoto',name:'Photo Only',price:2750000,desc:'2 Fotografer, 100 foto edited, G-Drive, 4 jam coverage, transport jabodetabek',color:''},{id:'gbooth',name:'Photo Booth',price:3850000,desc:'1–2 Crew profesional, backdrop wisuda, lighting studio, Selfiebox Machine, unlimited print 4R, max 3 jam, softcopy + QR Code realtime, transport jabodetabek',color:''},{id:'g360',name:'Glamation 360°',price:4100000,desc:'1–2 Crew profesional, MP4, LCD 50in preview, GoPro/iPhone 12 Pro, overlay design free, max 3 jam, QR Code realtime, transport jabodetabek',color:''},{id:'gcomplete',name:'Complete Package',price:7750000,desc:'Photo + Video + Photo Booth, transport jabodetabek',color:'feat'}];saveSettingsToAPI('grad_packages',null);renderPengaturan();showStatus()}
function saveGradAddon(){[...GRAD.addons,...GRAD.cetak].forEach(a=>{const el=document.getElementById('ovga-'+a.id);if(el)a.price=parseInt(el.value)||a.price});saveSettingsToAPI('grad_addons',GRAD.addons);saveSettingsToAPI('grad_cetak',GRAD.cetak);showStatus()}
