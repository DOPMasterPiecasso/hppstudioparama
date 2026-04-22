// ============================================================
// PENAWARAN & PROYEK
// ============================================================
function updatePenawaranStatus(id, status){
  const p = penawaranList.find(x=>String(x.id)===String(id));
  if(p){ p.status=status; updatePenawaranAPI(id, {status}); renderProyek(); }
}

function removePenawaran(id){
  penawaranList = penawaranList.filter(x=>x.id!==id);
  deletePenawaranAPI(id); renderProyek();
}

function clearPenawaran(){
  penawaranList.forEach(p=>deletePenawaranAPI(p.id));
  penawaranList=[];
  renderProyek();
}

function exportPenawaran(){
  const data = JSON.stringify(penawaranList, null, 2);
  showDataModal(data, 'export');
}

function importPenawaran(event){
  const file = event.target.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = async e => {
    try {
      const imported = JSON.parse(e.target.result);
      if(!Array.isArray(imported)) throw new Error('format salah');
      const existingIds = new Set(penawaranList.map(p=>p.id));
      const newItems = imported.filter(p=>!existingIds.has(p.id));
      for(const item of newItems) {
        const res = await savePenawaranAPI(item);
        if(res.id) item.id = res.id;
      }
      penawaranList = [...penawaranList, ...newItems];
      renderProyek();
      showDataModal(null, 'import-ok', newItems.length);
    } catch(err){ showDataModal('', 'import'); }
  };
  reader.readAsText(file);
  event.target.value = '';
}

function showDataModal(data, mode, count){
  const old = document.getElementById('data-modal');
  if(old) old.remove();
  const modal = document.createElement('div');
  modal.id = 'data-modal';
  modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9998;display:flex;align-items:center;justify-content:center;padding:20px';
  let inner = '';
  if(mode === 'export'){
    inner = `<div style="background:var(--surface);border-radius:var(--rl);padding:24px;max-width:560px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3)">
      <div style="font-family:var(--font-d);font-size:18px;margin-bottom:6px">Export Data Penawaran</div>
      <textarea id="modal-ta" style="width:100%;height:200px;font-size:11px;font-family:monospace;padding:10px;border:1px solid var(--border2);border-radius:8px;resize:vertical" readonly>${data}</textarea>
      <div style="display:flex;gap:8px;margin-top:12px">
        <button class="btn bp" style="flex:1" onclick="document.getElementById('modal-ta').select();document.execCommand('copy');this.textContent='✓ Tersalin!'">Salin Semua</button>
        <button class="btn bs" onclick="document.getElementById('data-modal').remove()">Tutup</button>
      </div></div>`;
  } else if(mode === 'import-ok'){
    inner = `<div style="background:var(--surface);border-radius:var(--rl);padding:24px;max-width:400px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.3);text-align:center">
      <div style="font-size:32px;margin-bottom:10px">✅</div>
      <div style="font-family:var(--font-d);font-size:18px;margin-bottom:6px">Import Berhasil</div>
      <div style="font-size:13px;color:var(--text2);margin-bottom:16px">${count} penawaran berhasil diimport.</div>
      <button class="btn bp" style="width:100%" onclick="document.getElementById('data-modal').remove()">OK</button>
    </div>`;
  }
  modal.innerHTML = inner;
  modal.addEventListener('click', e=>{ if(e.target===modal) modal.remove(); });
  document.body.appendChild(modal);
}

function initBulanSelector(){
  const sel=document.getElementById('pw-bulan');
  if(!sel) return;
  const now=new Date();
  const months=[];
  for(let i=0;i<12;i++){
    const d=new Date(now.getFullYear(),now.getMonth()-i,1);
    const val=`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
    const label=d.toLocaleDateString('id-ID',{month:'long',year:'numeric'});
    months.push({val,label});
  }
  const cur=`${now.getFullYear()}-${String(now.getMonth()+1).padStart(2,'0')}`;
  if(sel.options.length<=1) sel.innerHTML='<option value="all">Semua Waktu</option>'+months.map(m=>`<option value="${m.val}"${m.val===cur?' selected':''}>${m.label}</option>`).join('');
}

function getPenawaranByMonth(mk){
  if(mk==='all') return penawaranList;
  return penawaranList.filter(p=>{
    const d=p.tsRaw?new Date(p.tsRaw):new Date((p.ts||'').split('/').reverse().join('-')||Date.now());
    const k=`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}`;
    return k===mk;
  });
}

function renderProyek(){
  initBulanSelector();
  const filter=document.getElementById('pw-filter')?.value||'all';
  const sort=document.getElementById('pw-sort')?.value||'newest';
  const bulan=document.getElementById('pw-bulan')?.value||'all';
  const allFiltered=getPenawaranByMonth(bulan);
  const stats=(list)=>({
    deal:list.filter(p=>p.status==='deal'),nego:list.filter(p=>p.status==='nego'),
    pending:list.filter(p=>p.status==='pending'),gagal:list.filter(p=>p.status==='gagal'),
    total:list.length,
    revDeal:list.filter(p=>p.status==='deal').reduce((s,p)=>s+p.harga,0),
    revNego:list.filter(p=>p.status==='nego').reduce((s,p)=>s+p.harga,0),
  });
  const cur=stats(allFiltered);

  // Comparison panel
  const compEl=document.getElementById('pw-comparison');
  if(compEl&&isManager&&bulan!=='all'){
    const [y,m]=bulan.split('-').map(Number);
    const pd=new Date(y,m-2,1);
    const pk=`${pd.getFullYear()}-${String(pd.getMonth()+1).padStart(2,'0')}`;
    const prev=stats(getPenawaranByMonth(pk));
    compEl.style.display='';
    const arrow=n=>n>0?`<span style="color:var(--success)">▲${Math.abs(n)}</span>`:n<0?`<span style="color:var(--danger)">▼${Math.abs(n)}</span>`:`<span style="color:var(--text3)">—</span>`;
    const arrowM=n=>n>0?`<span style="color:var(--success)">▲${fmtM(Math.abs(n))}</span>`:n<0?`<span style="color:var(--danger)">▼${fmtM(Math.abs(n))}</span>`:`<span style="color:var(--text3)">—</span>`;
    compEl.innerHTML=`<div class="card" style="padding:14px 16px;border-left:3px solid var(--accent2)">
      <div style="font-size:11px;font-weight:600;color:var(--text3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px">vs Bulan Sebelumnya</div>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:8px;font-size:13px">
        <div style="background:var(--surface2);border-radius:var(--r);padding:10px"><div style="color:var(--text3);font-size:11px;margin-bottom:2px">Total Leads</div><b>${cur.total}</b> ${arrow(cur.total-prev.total)}</div>
        <div style="background:var(--surface2);border-radius:var(--r);padding:10px"><div style="color:var(--text3);font-size:11px;margin-bottom:2px">✅ Deal</div><b>${cur.deal.length}</b> ${arrow(cur.deal.length-prev.deal.length)}</div>
        <div style="background:var(--surface2);border-radius:var(--r);padding:10px"><div style="color:var(--text3);font-size:11px;margin-bottom:2px">Revenue Deal</div><b>${fmtM(cur.revDeal)}</b> ${arrowM(cur.revDeal-prev.revDeal)}</div>
      </div></div>`;
  } else if(compEl){ compEl.style.display='none'; }

  // Metrics
  const metricsEl=document.getElementById('sim-metrics');
  if(metricsEl){
    if(isManager){
      const net=(cur.revDeal-Math.round(cur.revDeal*0.25))-OH.total;
      metricsEl.style.display='';
      metricsEl.innerHTML=`
        <div class="m"><div class="ml">Total Leads</div><div class="mv">${cur.total}</div><div class="ms">${bulan==='all'?'semua waktu':'bulan ini'}</div></div>
        <div class="m"><div class="ml">✅ Deal</div><div class="mv suc">${cur.deal.length}</div><div class="ms">${fmtM(cur.revDeal)}</div></div>
        <div class="m"><div class="ml">🔄 Nego</div><div class="mv" style="color:var(--accent2)">${cur.nego.length}</div><div class="ms">${fmtM(cur.revNego)}</div></div>
        <div class="m"><div class="ml">🕐 Pending</div><div class="mv war">${cur.pending.length}</div><div class="ms">&nbsp;</div></div>
        <div class="m"><div class="ml">Est. Net</div><div class="mv ${net>=0?'suc':'acc'}">${net>=0?'':'-'}${fmtM(Math.abs(net))}</div><div class="ms">setelah COGS+OH</div></div>`;
    } else { metricsEl.style.display='none'; }
  }

  // Summary
  const sumEl=document.getElementById('pw-summary');
  if(sumEl){
    const row=(l,v,c)=>`<div><span style="color:var(--text3)">${l}</span><br><b ${c?`style="color:${c}"`:``}>${v}</b></div>`;
    sumEl.innerHTML=`<div class="card mb16" style="padding:11px 16px"><div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(105px,1fr));gap:8px;font-size:13px">
      ${row('Total',cur.total+' penawaran')}${row('✅ Deal',cur.deal.length,'var(--success)')}${row('🔄 Nego',cur.nego.length,'var(--accent2)')}${row('🕐 Pending',cur.pending.length,'var(--warning)')}${row('❌ Gagal',cur.gagal.length,'var(--text3)')}${isManager?row('Revenue',fmtM(cur.revDeal),'var(--success)'):''}
    </div></div>`;
  }

  // List
  let list=filter==='all'?[...allFiltered]:allFiltered.filter(p=>p.status===filter);
  if(sort==='newest') list.sort((a,b)=>b.id-a.id);
  if(sort==='oldest') list.sort((a,b)=>a.id-b.id);
  if(sort==='highest') list.sort((a,b)=>b.harga-a.harga);
  if(sort==='lowest') list.sort((a,b)=>a.harga-b.harga);

  const statMap={deal:{label:'✅ Deal',cls:'bsuc',color:'var(--success)'},nego:{label:'🔄 Nego',cls:'binf',color:'var(--accent2)'},pending:{label:'🕐 Pending',cls:'bwar',color:'var(--warning)'},gagal:{label:'❌ Gagal',cls:'bdan',color:'var(--danger)'}};
  const listEl=document.getElementById('pw-list');
  if(!listEl) return;
  if(!list.length){
    listEl.innerHTML=`<div style="color:var(--text3);font-size:13px;padding:28px;text-align:center;background:var(--surface2);border-radius:var(--r)">${penawaranList.length===0?'Belum ada penawaran. Hitung di <b>Kalkulator</b> lalu klik Simpan.':'Tidak ada penawaran dengan filter ini.'}</div>`;
  } else {
    listEl.innerHTML=list.map(p=>{
      const st=statMap[p.status]||statMap.pending;
      return `<div class="si" style="border-left:3px solid ${st.color};margin-bottom:8px">
        <div class="sih">
          <div style="flex:1;min-width:0">
            <div class="sin">${p.nama} <span class="badge ${st.cls}" style="margin-left:5px;font-size:10px">${st.label}</span></div>
            <div class="sis">${p.paket}${p.siswa?' · '+p.siswa+' siswa':''} · ${p.ts}${isManager?' · '+p.addedBy:''}</div>
            ${p.catatan?`<div style="font-size:11px;color:var(--text3);margin-top:2px">📝 ${p.catatan}</div>`:''}
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0;margin-left:12px">
            <div style="text-align:right"><span style="font-weight:600;font-size:14px">${fmtM(p.harga)}</span></div>
            <select id="stat-${p.id}" style="font-size:11px;padding:2px 5px;border-radius:6px;border:1px solid var(--border2)">
              <option value="pending" ${p.status==='pending'?'selected':''}>🕐 Pending</option>
              <option value="nego" ${p.status==='nego'?'selected':''}>🔄 Nego</option>
              <option value="deal" ${p.status==='deal'?'selected':''}>✅ Deal</option>
              <option value="gagal" ${p.status==='gagal'?'selected':''}>❌ Gagal</option>
            </select>
            <div style="display:flex;gap:4px">
              <a class="btn bs bsm" href="/api/pdf.php?id=${p.id}" target="_blank" style="padding:1px 9px;font-size:12px;text-decoration:none">📄 PDF</a>
              <button class="btn bs bsm" onclick="updatePenawaranStatus(${p.id}, document.getElementById('stat-${p.id}').value)" style="padding:1px 9px;font-size:12px;color:var(--success)">💾 Simpan</button>
              <button class="btn bs bsm" onclick="editPenawaran(${p.id})" style="padding:1px 9px;font-size:12px">✏ Edit</button>
            </div>
          </div>
        </div>
      </div>`;
    }).join('');
  }

  // Breakdown
  const bdEl=document.getElementById('proyek-breakdown');
  if(bdEl) bdEl.style.display=isManager&&allFiltered.length>0?'':'none';
  if(isManager){
    const bs=document.getElementById('sim-by-status');
    if(bs) bs.innerHTML=[['deal','✅ Deal'],['nego','🔄 Nego'],['pending','🕐 Pending'],['gagal','❌ Gagal']].map(([s,l])=>{
      const items=allFiltered.filter(p=>p.status===s);
      return `<div class="rr"><span class="rl">${l} (${items.length})</span><span class="rv">${items.length?fmtM(items.reduce((a,p)=>a+p.harga,0)):'—'}</span></div>`;
    }).join('');
    const bp=document.getElementById('sim-by-paket');
    if(bp){const pc={};allFiltered.forEach(p=>{const k=p.paket.split('—')[0].trim();pc[k]=(pc[k]||0)+1});bp.innerHTML=Object.entries(pc).sort((a,b)=>b[1]-a[1]).map(([k,v])=>`<div class="rr"><span class="rl">${k}</span><span class="rv">${v}</span></div>`).join('');}
  }
}

// ============================================================
// EDIT PENAWARAN & SAVE dari KALKULATOR
// ============================================================
let editingPenawaranId = null;

function editPenawaran(id){
  const p = penawaranList.find(x=>String(x.id)===String(id));
  if(!p) return;
  if(window.location.pathname !== '/pages/kalkulator.php'){
    window.location.href = '/pages/kalkulator.php?edit_id=' + id;
    return;
  }
  editingPenawaranId = id;
  setTimeout(()=>{
    const isGrad = p.paket.toLowerCase().includes('graduation');
    const catEl = document.getElementById('k-cat');
    if(catEl){ catEl.value = isGrad ? 'graduation' : 'bukutahunan'; kCatChange(); }
    if(!isGrad){
      const typeEl = document.getElementById('k-type');
      if(typeEl){
        const paketLower = p.paket.toLowerCase();
        for(let i=0;i<typeEl.options.length;i++){
          if(typeEl.options[i].text.toLowerCase().includes(paketLower.split('—')[0].trim().toLowerCase().slice(0,10))){
            typeEl.selectedIndex=i; break;
          }
        }
      }
      const siswaEl = document.getElementById('k-siswa');
      if(siswaEl&&p.siswa) siswaEl.value = p.siswa;
    }
    kalcUpdate();
    const namaEl=document.getElementById('k-save-nama'); if(namaEl) namaEl.value=p.nama;
    const hbtn = document.getElementById('btn-hapus-pw');
    if(hbtn) hbtn.style.display = 'block';
  }, 150);
}

function deleteCurrentPenawaran(){
  if(!editingPenawaranId) return;
  if(confirm('Hapus penawaran/proyek ini secara permanen?')){
    removePenawaran(editingPenawaranId);
    window.location.href = '/pages/proyek.php';
  }
}

async function saveKalcToPenawaran(){
  const totalEl = document.getElementById('k-total');
  const total = parseInt((totalEl?.textContent||'').replace(/[^0-9]/g,''))||0;
  const msgEl = document.getElementById('k-save-msg');
  if(!total){
    if(msgEl){msgEl.style.display='block';msgEl.style.color='var(--danger)';msgEl.textContent='⚠ Hitung dulu harga di kalkulator sebelum simpan.';}
    return;
  }
  const namaEl = document.getElementById('k-save-nama');
  const nama = namaEl?.value?.trim();
  if(!nama){
    if(msgEl){msgEl.style.display='block';msgEl.style.color='var(--danger)';msgEl.textContent='⚠ Isi nama sekolah / klien dulu.';}
    namaEl?.focus(); return;
  }
  const catEl = document.getElementById('k-cat');
  const cat = catEl?.value||'bukutahunan';
  let paketLabel='';
  if(cat==='graduation'){
    const gp=document.getElementById('k-grad-pkg');
    paketLabel='Graduation — '+(gp?.options[gp?.selectedIndex]?.text?.split('—')[0]?.trim()||'');
  } else {
    const typeEl=document.getElementById('k-type');
    paketLabel=typeEl?.options[typeEl?.selectedIndex]?.text||'';
  }
  const siswa=parseInt(document.getElementById('k-siswa')?.value)||0;
  const tags=[...(document.querySelectorAll('.bns-tag')||[])].map(t=>t.dataset.label);
  const dType=document.querySelector('.dkbtn.active')?.dataset?.type||'none';
  const dVal=parseFloat(document.getElementById('dk-value')?.value)||0;
  let nego=[];
  if(dType==='persen'&&dVal>0) nego.push(`diskon ${dVal}%`);
  else if(dType==='nominal'&&dVal>0) nego.push(`diskon Rp${dVal.toLocaleString('id-ID')}`);
  else if(dType==='cashback'&&dVal>0) nego.push(`cashback ${dVal}%`);
  if(tags.length) nego.push(...tags.map(t=>`bonus: ${t}`));
  const catatan=nego.join(' | ');
  let hargaFinal=total;
  if(dType==='persen'&&dVal>0) hargaFinal=Math.round(total*(1-dVal/100));
  else if(dType==='nominal'&&dVal>0) hargaFinal=Math.max(0,total-dVal);

  if(editingPenawaranId){
    const p=penawaranList.find(x=>x.id===editingPenawaranId);
    if(p){
      p.nama=nama; p.paket=paketLabel; p.harga=hargaFinal;
      p.hargaSebelumDiskon=total!==hargaFinal?total:0;
      p.siswa=siswa; p.catatan=catatan;
      await updatePenawaranAPI(editingPenawaranId, {nama_klien:nama, paket:paketLabel, harga:hargaFinal, harga_sebelum_diskon:total!==hargaFinal?total:0, jumlah_siswa:siswa, catatan});
    }
    editingPenawaranId=null;
    if(msgEl){msgEl.style.display='block';msgEl.style.color='var(--success)';msgEl.textContent=`✓ Penawaran "${nama}" diperbarui!`;setTimeout(()=>msgEl.style.display='none',3000);}
  } else {
    const res = await savePenawaranAPI({nama, paket:paketLabel, harga:hargaFinal, hargaSebelumDiskon:total!==hargaFinal?total:0, siswa, catatan, status:'pending'});
    penawaranList.push({id:res.id||Date.now(),nama,paket:paketLabel,harga:hargaFinal,hargaSebelumDiskon:total!==hargaFinal?total:0,siswa,catatan,status:'pending',addedBy:currentUser?.name||'Staff',ts:new Date().toLocaleDateString('id-ID'),tsRaw:Date.now()});
    if(msgEl){msgEl.style.display='block';msgEl.style.color='var(--success)';msgEl.textContent=`✓ Penawaran "${nama}" disimpan!`;setTimeout(()=>msgEl.style.display='none',3000);}
  }
  namaEl.value='';
  renderProyek();
  setTimeout(()=>{
    window.location.href = '/pages/proyek.php';
  }, 800);
}

// ============================================================
// DISKON / BONUS
// ============================================================
function applyDiskon(){
  const dType = document.querySelector('.dkbtn.active')?.dataset?.type || 'none';
  const dVal  = parseFloat(document.getElementById('dk-value')?.value)||0;
  const totalEl = document.getElementById('k-total');
  const totalStr = totalEl?.textContent?.replace(/[^0-9]/g,'')||'0';
  const total = parseInt(totalStr)||0;
  const resEl = document.getElementById('dk-result');
  const warnEl = document.getElementById('dk-warn');
  const tags = [...(document.querySelectorAll('.bns-tag')||[])];
  const bonusItems = tags.map(t=>t.dataset.label);
  const bonusNominal = tags.reduce((s,t)=>s+(parseInt(t.dataset.nominal)||0),0);
  let html = '', potongan = 0, warnMsg = '', warnCls = 'dw-ok';

  if(dType!=='none' && dVal>0 && total>0){
    if(dType==='persen') potongan = Math.round(total * dVal/100);
    else if(dType==='nominal') potongan = Math.min(dVal, total);
    const finalPrice = dType==='cashback' ? total : total - potongan;
    const cashback = dType==='cashback' ? Math.round(total * dVal/100) : 0;

    if(dType==='persen'||dType==='nominal'){
      html+=`<div class="rr"><span class="rl">Harga sebelum potongan</span><span class="rv">${fmt(total)}</span></div>`;
      html+=`<div class="rr"><span class="rl">Diskon ${dType==='persen'?dVal+'%':fmt(dVal)}</span><span class="rv rd">−${fmt(potongan)}</span></div>`;
      html+=`<div class="rr" style="padding:6px 0;font-weight:600"><span style="color:var(--text2)">Harga Final Klien</span><span style="font-size:18px;font-family:var(--font-d);color:var(--accent)">${fmt(finalPrice)}</span></div>`;
      if(isManager){
        const cetakEl = document.getElementById('k-cetak-est');
        const cetakEst = cetakEl ? parseInt(cetakEl.dataset.cetak||0) : 0;
        if(cetakEst>0){const grossAfter=finalPrice-cetakEst;const mAfter=finalPrice>0?grossAfter/finalPrice*100:0;
          html+=`<div class="rr"><span class="rl" style="font-size:11px;color:var(--text3)">Est. gross setelah diskon</span><span class="rv" style="font-size:11px;color:${mAfter>=50?'var(--success)':'var(--danger)'}"> ${fmt(grossAfter)} (${mAfter.toFixed(0)}%)</span></div>`;}
      }
      const pct=potongan/total*100;
      if(pct<=5){warnCls='dw-ok';warnMsg=`✓ Diskon ${pct.toFixed(1)}% aman.`}
      else if(pct<=10){warnCls='dw-warn';warnMsg=`⚠ Diskon ${pct.toFixed(1)}% — pertimbangkan bonus produk.`}
      else{warnCls='dw-bad';warnMsg=`✗ Diskon ${pct.toFixed(1)}% terlalu besar.`}
    } else if(dType==='cashback'){
      html+=`<div class="rr"><span class="rl">Harga ke klien (tetap)</span><span class="rv">${fmt(total)}</span></div>`;
      html+=`<div class="rr"><span class="rl">Cashback ${dVal}%</span><span class="rv gr">${fmt(cashback)}</span></div>`;
      if(dVal<=5){warnCls='dw-ok';warnMsg=`✓ Cashback ${dVal}% wajar.`}
      else if(dVal<=10){warnCls='dw-warn';warnMsg=`⚠ Cashback ${dVal}% — pastikan margin cukup.`}
      else{warnCls='dw-bad';warnMsg=`✗ Cashback ${dVal}% terlalu besar.`}
    }
  }
  if(bonusItems.length>0){
    html+=`<div style="margin-top:8px;padding-top:8px;border-top:1px solid var(--border)"><div style="font-size:11px;font-weight:600;color:var(--text2);margin-bottom:4px">Bonus Produk:</div><ul style="margin:0 0 0 14px;font-size:12px;line-height:1.9;color:var(--text)">${bonusItems.map(b=>`<li>${b}</li>`).join('')}</ul>${bonusNominal>0?`<div style="font-size:11px;color:var(--text3);margin-top:3px">Total nilai bonus ≈ ${fmtM(bonusNominal)}</div>`:''}</div>`;
    if(!warnMsg){warnCls='dw-ok';warnMsg='✓ Bonus produk = strategi terbaik.';}
  }
  if(resEl) resEl.innerHTML = html;
  if(warnEl){
    if(warnMsg){warnEl.className='dk-warn '+warnCls;warnEl.textContent=warnMsg;warnEl.style.display='block';}
    else warnEl.style.display='none';
  }
}

function addBonusTag(){
  const inp = document.getElementById('bns-input');
  const nomEl = document.getElementById('bns-nominal');
  const val = inp?.value?.trim();
  if(!val) return;
  const nom = parseInt(nomEl?.value)||0;
  const container = document.getElementById('bns-tags');
  if(!container) return;
  const id = 'bns-'+Date.now();
  const tag = document.createElement('span');
  tag.className = 'bns-tag';
  const label = nom>0 ? `${val} (≈${fmtM(nom)})` : val;
  tag.dataset.label = label;
  tag.dataset.nominal = nom;
  tag.id = id;
  tag.innerHTML = `${label}<button onclick="removeBonusTag('${id}')" title="Hapus">×</button>`;
  container.appendChild(tag);
  inp.value=''; if(nomEl) nomEl.value='';
  inp.focus();
  applyDiskon();
}

function removeBonusTag(id){
  const el=document.getElementById(id);
  if(el) el.remove();
  applyDiskon();
}

function setDkType(type, el){
  document.querySelectorAll('.dkbtn').forEach(b=>b.classList.remove('active'));
  el.classList.add('active');
  const unit=document.getElementById('dk-unit');
  if(unit) unit.textContent = type==='nominal'?'Rp':'%';
  const valSection=document.getElementById('dk-val-section');
  if(valSection) valSection.style.display = type==='none'?'none':'';
  const dkVal=document.getElementById('dk-value');
  if(dkVal){
    if(type==='nominal'){dkVal.placeholder='contoh: 500000';dkVal.max=99999999;}
    else{dkVal.placeholder='contoh: 5';dkVal.max=50;}
  }
  applyDiskon();
}

function updateNominalFmt(){
  const type=document.querySelector('.dkbtn.active')?.dataset?.type||'none';
  const val=parseFloat(document.getElementById('dk-value')?.value)||0;
  const totalEl=document.getElementById('k-total');
  const total=parseInt((totalEl?.textContent||'').replace(/[^0-9]/g,''))||0;
  const el=document.getElementById('dk-nominal-fmt');
  if(!el) return;
  if(type==='persen'&&total>0) el.textContent=`= ${fmtM(Math.round(total*val/100))}`;
  else if(type==='cashback'&&total>0) el.textContent=`klien terima ${fmtM(Math.round(total*val/100))} cashback`;
  else el.textContent='';
}

// ============================================================
// INIT
// ============================================================
window.addEventListener('DOMContentLoaded', async ()=>{
  await loadSettingsFromAPI();
  if (typeof refreshMasterData === 'function') {
    await refreshMasterData();
  }
  await loadPenawaranFromAPI();
  
  const pg = document.querySelector('.page');
  const activePageId = pg ? pg.id : '';

  if(activePageId === 'page-ringkasan' && typeof renderRingkasan === 'function') renderRingkasan();
  if(activePageId === 'page-fullservice' && typeof renderFS === 'function') renderFS();
  if(activePageId === 'page-alacarte' && typeof renderAlacarte === 'function') renderAlacarte();
  if(activePageId === 'page-addon' && typeof renderAddon === 'function') renderAddon();
  if(activePageId === 'page-graduation' && typeof renderGraduation === 'function') renderGraduation();
  if(activePageId === 'page-kalkulator' && typeof kalcUpdateCore === 'function') {
    buildAddonList(); 
    kalcUpdateCore();
  }
  if(activePageId === 'page-proyek' && typeof renderProyek === 'function') renderProyek();
  if(activePageId === 'page-analisis' && typeof renderAnalisis === 'function') renderAnalisis();
  if(activePageId === 'page-pengaturan' && typeof renderPengaturan === 'function') renderPengaturan();

  // Hide profit card for non-manager
  const profCard = document.getElementById('k-profit-card');
  if(profCard && typeof isManager !== 'undefined') profCard.style.display = isManager ? '' : 'none';
});
