/**
 * Parama HPP - Database Integration Layer
 * 
 * File ini berisi fungsi-fungsi helper untuk mengintegrasikan
 * aplikasi dengan database API.
 * 
 * Include file ini di parama_dashboard_v2.html sebelum closing </body>
 */

// ============================================================
// GLOBAL DATABASE CONNECTION
// ============================================================
const API_BASE = 'api/pricing.php';
let DATABASE_LOADED = false;

// Cache untuk data yang sudah di-fetch
let DATA_CACHE = {
    overhead: null,
    fullservice: null,
    alacarte: null,
    addons: null,
    graduation: null,
    proposals: null,
    lastFetch: {}
};

// ============================================================
// INITIALIZE - Jalankan saat login berhasil
// ============================================================
async function initializeDatabaseLayer() {
    console.log('Initializing database layer...');
    
    try {
        // Fetch semua data sekaligus
        const response = await fetch(`${API_BASE}?action=get_all`);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const data = await response.json();
        
        // Map ke struktur aplikasi yang sudah ada
        mapDatabaseToApplication(data);
        
        DATABASE_LOADED = true;
        console.log('✓ Database layer initialized successfully');
        
        return true;
    } catch (error) {
        console.error('❌ Failed to initialize database:', error);
        console.log('⚠ Falling back to local data');
        DATABASE_LOADED = false;
        return false;
    }
}

/**
 * Map data dari database ke struktur aplikasi yang sudah ada
 * Ini memastikan kompatibilitas 100% dengan kode existing
 */
function mapDatabaseToApplication(dbData) {
    // 1. OVERHEAD
    if (dbData.overhead) {
        OH = {
            designer: dbData.overhead['Designer'] || 8000000,
            marketing: dbData.overhead['Marketing'] || 3000000,
            creative: dbData.overhead['Creative Prod.'] || 5000000,
            pm: dbData.overhead['Project Mgr'] || 6000000,
            sosmed: dbData.overhead['Social Media'] || 2000000,
            freelance: dbData.overhead['Freelance'] || 1500000,
            ops: dbData.overhead['Operasional'] || 4500000,
        };
        OH.total = Object.values(OH).reduce((a, b) => a + b, 0);
    }
    
    // 2. FULL SERVICE PACKAGES
    if (dbData.fullservice) {
        FS = dbData.fullservice; // {handy: [...], minimal: [...], large: [...]}
    }
    
    // 3. À LA CARTE FACTORS
    if (dbData.alacarte_factors) {
        ALC_F = dbData.alacarte_factors;
    }
    
    // 4. ADD-ONS - Konversi format
    if (dbData.addons) {
        ADDON_DATA = convertAddonsFromDatabase(dbData.addons);
    }
    
    // 5. CETAK
    if (dbData.cetak_base) {
        CETAK_BASE = convertCetakBaseFromDatabase(dbData.cetak_base);
    }
    if (dbData.cetak_factors) {
        CETAK_F = dbData.cetak_factors;
    }
    
    // 6. GRADUATION
    if (dbData.graduation) {
        GRAD.packages = dbData.graduation.packages || [];
    }
    if (dbData.graduation_addons) {
        GRAD.addons = dbData.graduation_addons.addons || [];
        GRAD.cetak = dbData.graduation_addons.cetak || [];
    }
}

/**
 * Konversi format add-ons dari database ke aplikasi
 */
function convertAddonsFromDatabase(dbAddons) {
    const categories = {};
    
    // Group by kategori
    dbAddons.forEach(addon => {
        // Tentukan kategori berdasarkan nama/type
        let category = 'other';
        
        if (addon.name.includes('Finishing') || addon.name.includes('Hardcover') || addon.name.includes('Softcover')) {
            category = 'finishing';
        } else if (addon.name.includes('Kertas') || addon.name.includes('Paper') || addon.name.includes('Art') || addon.name.includes('Glossy')) {
            category = 'kertas';
        } else if (addon.name.includes('Halaman')) {
            category = 'halaman';
        } else if (addon.name.includes('Video') || addon.name.includes('Drone')) {
            category = 'video';
        } else if (addon.name.includes('Slide')) {
            category = 'pkg1';
        } else if (addon.name.includes('Custom')) {
            category = 'pkg2';
        }
        
        if (!categories[category]) {
            categories[category] = [];
        }
        
        // Format tiers ke array format yang diharapkan aplikasi
        const tiers = (addon.tiers || []).map(t => [
            t[0], // tier_label
            t[1], // min_quantity
            t[2]  // price
        ]);
        
        categories[category].push({
            id: addon.id,
            name: addon.name,
            type: addon.type,
            price: addon.price || 0,
            tiers: tiers
        });
    });
    
    return {
        finishing: categories.finishing || [],
        kertas: categories.kertas || [],
        halaman: categories.halaman || [],
        video: categories.video || [],
        pkg1: categories.pkg1 || [],
        pkg2: categories.pkg2 || [],
    };
}

/**
 * Konversi format cetak_base dari database
 */
function convertCetakBaseFromDatabase(cetakData) {
    return cetakData.map(range => ({
        label: range.label,
        pages: range.pages
    }));
}

// ============================================================
// PENAWARAN / PROPOSAL MANAGEMENT
// ============================================================

/**
 * Simpan penawaran ke database
 */
async function savePenawaranToDatabase(proposal) {
    try {
        const formData = new FormData();
        formData.append('action', 'save_penawaran');
        formData.append('client_name', proposal.nama);
        formData.append('package', proposal.paket || '');
        formData.append('student_count', proposal.siswa || 0);
        formData.append('total_price', proposal.harga || 0);
        formData.append('final_price', proposal.harga || 0);
        formData.append('notes', proposal.catatan || '');
        formData.append('status', proposal.status || 'pending');
        
        if (proposal.discount_type) {
            formData.append('discount_type', proposal.discount_type);
            formData.append('discount_value', proposal.discount_value || 0);
        }
        
        if (proposal.bonus_text) {
            formData.append('bonus_text', proposal.bonus_text);
            formData.append('bonus_nominal', proposal.bonus_nominal || 0);
        }
        
        const response = await fetch(API_BASE, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            console.log('✓ Proposal saved to DB:', result.id);
            return result;
        } else {
            throw new Error(result.message || 'Save failed');
        }
    } catch (error) {
        console.error('Error saving proposal to database:', error);
        throw error;
    }
}

/**
 * Ambil daftar penawaran dari database
 */
async function getPenawaransFromDatabase(month = null, status = null) {
    try {
        let url = `${API_BASE}?action=get_penawarans`;
        if (month) url += `&month=${month}`;
        if (status) url += `&status=${status}`;
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error fetching proposals:', error);
        return [];
    }
}

/**
 * Update status proposal di database
 */
async function updateProposalStatusInDatabase(id, status) {
    try {
        const formData = new FormData();
        formData.append('action', 'update_penawaran_status');
        formData.append('id', id);
        formData.append('status', status);
        
        const response = await fetch(API_BASE, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error updating proposal status:', error);
        throw error;
    }
}

/**
 * Hapus proposal dari database
 */
async function deleteProposalFromDatabase(id) {
    try {
        const formData = new FormData();
        formData.append('action', 'delete_penawaran');
        formData.append('id', id);
        
        const response = await fetch(API_BASE, {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error deleting proposal:', error);
        throw error;
    }
}

// ============================================================
// UTILITY FUNCTIONS
// ============================================================

/**
 * Refresh data dari database (jika ada perubahan)
 */
async function refreshDatabaseData() {
    DATABASE_LOADED = false;
    DATA_CACHE = {};
    return initializeDatabaseLayer();
}

/**
 * Check apakah database tersedia
 */
async function isDatabaseAvailable() {
    try {
        const response = await fetch(`${API_BASE}?action=get_all`);
        return response.ok;
    } catch (error) {
        return false;
    }
}

/**
 * Fallback: Gunakan data lokal jika database tidak tersedia
 */
async function getDataWithFallback(action) {
    if (DATABASE_LOADED) {
        // Use existing global data
        switch(action) {
            case 'full_service':
                return FS;
            case 'overhead':
                return OH;
            case 'addons':
                return ADDON_DATA;
            case 'graduation':
                return GRAD;
            default:
                return null;
        }
    }
    
    // Try to fetch from database
    try {
        const response = await fetch(`${API_BASE}?action=get_${action.replace(/_/g, '')}`);
        if (response.ok) {
            return await response.json();
        }
    } catch (error) {
        console.warn(`Fallback for ${action}:`, error);
    }
    
    return null;
}

// ============================================================
// INTEGRATION HOOKS
// ============================================================

/**
 * Modified doLogin untuk mengintegrasikan database
 * Replace fungsi doLogin yang ada dengan ini
 */
const originalDoLogin = doLogin;
function doLoginWithDatabase() {
    // ... existing login checks ...
    const u = document.getElementById('li-user').value.trim().toLowerCase();
    const p = document.getElementById('li-pass').value;
    const acc = ACCOUNTS[u];
    const errEl = document.getElementById('li-err');
    
    if (!acc || acc.pass !== p) {
        errEl.style.display = 'block';
        document.getElementById('li-pass').value = '';
        return;
    }
    
    // Login successful - Initialize with database
    errel.style.display = 'none';
    currentUser = { username: u, ...acc };
    
    // Set UI
    document.getElementById('login-overlay').style.display = 'none';
    document.getElementById('main-app').style.display = 'grid';
    document.getElementById('sb-uname').textContent = acc.name;
    document.getElementById('sb-urole').textContent = acc.role === 'manager' ? 'Manager — Akses Penuh' : 'Staff Penjualan';
    
    // Initialize database
    setTimeout(() => {
        initializeDatabaseLayer().then(success => {
            if (success) {
                applyRoleUI();
                if (acc.role === 'manager') {
                    renderRingkasan();
                    renderFS();
                    const opts = '<option value="">— Pilih paket —</option>' +
                        GRAD.packages.map(p => `<option value="${p.id}">${p.name} — ${fmt(p.price)}</option>`).join('');
                    ['gc-pkg', 'k-grad-pkg', 's-grad-pkg'].forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.innerHTML = opts;
                    });
                } else {
                    // Staff mode
                    document.querySelectorAll('.page').forEach(pg => pg.classList.remove('active'));
                    document.getElementById('page-kalkulator').classList.add('active');
                    buildAddonList();
                    kalcUpdate();
                }
            } else {
                alert('⚠ Database tidak tersedia, menggunakan data lokal');
                // Continue dengan fallback data yang ada di HTML
                applyRoleUI();
            }
        });
    }, 500);
}

/**
 * Override saveKalcToPenawaran untuk menyimpan ke database
 */
const originalSaveKalc = saveKalcToPenawaran;
async function saveKalcToPenawaranWithDatabase() {
    // ... existing validation ...
    const totalEl = document.getElementById('k-total');
    const total = parseInt((totalEl?.textContent || '').replace(/[^0-9]/g, '')) || 0;
    const msgEl = document.getElementById('k-save-msg');
    
    if (!total) {
        if (msgEl) {
            msgEl.style.display = 'block';
            msgEl.style.color = 'var(--danger)';
            msgEl.textContent = '⚠ Hitung dulu harga di kalkulator sebelum simpan.';
        }
        return;
    }
    
    const namaEl = document.getElementById('k-save-nama');
    const nama = namaEl?.value?.trim();
    if (!nama) {
        if (msgEl) {
            msgEl.style.display = 'block';
            msgEl.style.color = 'var(--danger)';
            msgEl.textContent = '⚠ Isi nama sekolah / klien dulu.';
        }
        namaEl?.focus();
        return;
    }
    
    // Build proposal object
    const siswa = parseInt(document.getElementById('k-siswa')?.value) || 0;
    const paket = getSelectedPackage();
    const dType = document.querySelector('.dkbtn.active')?.dataset?.type || 'none';
    const dVal = parseFloat(document.getElementById('dk-value')?.value) || 0;
    
    const proposal = {
        nama,
        paket,
        siswa,
        harga: total,
        status: 'pending'
    };
    
    if (dType !== 'none' && dVal > 0) {
        proposal.discount_type = dType;
        proposal.discount_value = dVal;
    }
    
    // Save ke database
    try {
        if (DATABASE_LOADED) {
            await savePenawaranToDatabase(proposal);
        } else {
            // Fallback ke localStorage
            penawaranList.push({
                ...proposal,
                id: Date.now(),
                addedBy: currentUser?.name || 'Staff',
                ts: new Date().toLocaleDateString('id-ID')
            });
            savePenawaranLS();
        }
        
        if (msgEl) {
            msgEl.style.display = 'block';
            msgEl.style.color = 'var(--success)';
            msgEl.textContent = `✓ Penawaran "${nama}" disimpan!`;
            setTimeout(() => msgEl.style.display = 'none', 3000);
        }
        
        namaEl.value = '';
        renderProyek();
    } catch (error) {
        if (msgEl) {
            msgEl.style.display = 'block';
            msgEl.style.color = 'var(--danger)';
            msgEl.textContent = '❌ Gagal menyimpan penawaran: ' + error.message;
        }
    }
}

function getSelectedPackage() {
    const cat = document.getElementById('k-cat')?.value || 'bukutahunan';
    if (cat === 'graduation') {
        const gp = document.getElementById('k-grad-pkg');
        return 'Graduation — ' + (gp?.options[gp?.selectedIndex]?.text?.split('—')[0]?.trim() || '');
    } else {
        const typeEl = document.getElementById('k-type');
        return typeEl?.options[typeEl?.selectedIndex]?.text || '';
    }
}
