<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER & TYPOGRAPHY
       ========================================================= */
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;}
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(79, 70, 229, 0.15), rgba(67, 56, 202, 0.05)); color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(79, 70, 229, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); margin: 0; font-weight: 500;}
    
    /* =========================================================
       2. PREMIUM TAB NAVIGATION & BUTTONS
       ========================================================= */
    .header-actions { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
    
    .tab-nav { display: inline-flex; background: var(--bg-surface); padding: 6px; border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 2px 10px rgba(0,0,0,0.02);}
    .tab-btn { padding: 10px 20px; font-size: 13px; font-weight: 800; border-radius: 12px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { background: var(--bg-base); color: #4f46e5; box-shadow: 0 2px 8px rgba(0,0,0,0.05); border: 1px solid rgba(79, 70, 229, 0.1); }
    
    .tab-content { display: none; animation: slideFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .tab-content.active { display: block; }
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .btn-primary { background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff; border: none; padding: 12px 20px; border-radius: 14px; font-weight: 900; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 6px 15px -4px rgba(79, 70, 229, 0.5); transition: 0.3s; text-decoration: none;}
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -4px rgba(79, 70, 229, 0.6);}
    
    .btn-secondary { background: var(--bg-surface); color: var(--text-main); border: 1px dashed var(--border-subtle); padding: 12px 20px; border-radius: 14px; font-weight: 800; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s;}
    .btn-secondary:hover { border-color: #4f46e5; color: #4f46e5; background: rgba(79, 70, 229, 0.05); transform: translateY(-2px);}

    /* =========================================================
       3. BENTO TABLE CARDS (ANALYTICAL STRICT BORDERS)
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); overflow: hidden; margin-top: 20px;}
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 16px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { text-align: center; padding: 16px 20px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600; vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:last-child, td:last-child { border-right: none; text-align: center; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(79, 70, 229, 0.02); }
    html.dark tr:hover td { background: rgba(79, 70, 229, 0.05); }

    /* Modern Badges inside Table */
    .po-badge { font-family: 'Space Mono', monospace; font-weight: 900; font-size: 13px; color: #4f46e5; background: rgba(79, 70, 229, 0.1); padding: 4px 10px; border-radius: 8px; display: inline-block; border: 1px dashed rgba(79, 70, 229, 0.3);}
    .vendor-badge { font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;}
    .vendor-badge i { color: var(--text-muted); background: var(--bg-base); padding: 4px; border-radius: 6px; border: 1px solid var(--border-subtle);}
    
    .status-badge { padding: 5px 12px; border-radius: 8px; font-size: 10px; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    .bg-ordered { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); animation: pulseWait 2s infinite;}
    .bg-received { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    @keyframes pulseWait { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }

    /* Action Buttons in Table */
    .table-actions { display: flex; justify-content: center; align-items: center; gap: 8px; }
    
    .btn-detail { background: rgba(59, 130, 246, 0.05); color: #3b82f6; border: 1px solid transparent; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; transition: 0.3s; text-decoration: none;}
    .btn-detail:hover { background: #3b82f6; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);}
    
    .btn-receive { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); padding: 0 12px; height: 34px; border-radius: 10px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; transition: 0.3s; text-decoration: none;}
    .btn-receive:hover { background: #10b981; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);}

    .btn-delete { background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1px solid transparent; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: 0.3s;}
    .btn-delete:hover { background: #ef4444; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 56px; color: var(--border-subtle); margin-bottom: 15px; display: block; }
    .empty-state h3 { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 5px; }
    .empty-state p { font-size: 13px; font-weight: 500; }

    /* =========================================================
       4. MODALS (FORM & CONFIRMATION)
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 520px; padding: 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .modal-title { font-size: 20px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px; margin: 0;}
    
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 36px; height: 36px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}

    /* Form Elements inside Modal */
    .form-group { margin-bottom: 20px; text-align: left;}
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: 12px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none; transition: all 0.3s;}
    .form-control:focus { border-color: #4f46e5; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);}
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px;}

    .btn-submit-modal { width: 100%; background: linear-gradient(135deg, #4f46e5, #4338ca); color: #fff; border: none; padding: 18px; border-radius: 16px; font-weight: 900; font-size: 15px; cursor: pointer; transition: 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 10px; box-shadow: 0 8px 25px -5px rgba(79, 70, 229, 0.5);}
    .btn-submit-modal:hover { transform: translateY(-3px); box-shadow: 0 12px 30px -5px rgba(79, 70, 229, 0.6);}

    /* Alert Modal Custom Icon */
    .icon-wrapper { width: 80px; height: 80px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; font-size: 38px; animation: pulseIcon 2s infinite;}
    @keyframes pulseIcon { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-truck"></i></div>
        <div>
            <h1>Procurement & Pembelian</h1>
            <p>Kelola pemesanan bahan baku ke Supplier dan lacak penerimaan barang.</p>
        </div>
    </div>
    
    <div class="header-actions">
        <div class="tab-nav">
            <button class="tab-btn active" onclick="switchTab('po')"><i class="ph-bold ph-receipt"></i> Dokumen PO</button>
            <button class="tab-btn" onclick="switchTab('supplier')"><i class="ph-bold ph-buildings"></i> Data Vendor</button>
        </div>
    </div>
</div>

<div id="tab-po" class="tab-content active">
    <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
        <a href="<?= base_url('/procurement/create_po') ?>" class="btn-primary">
            <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Terbitkan PO Baru
        </a>
    </div>

    <div class="bento-card" style="padding: 20px;">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Dokumen PO</th>
                        <th style="text-align: left;">Vendor Tujuan</th>
                        <th>Tanggal Pesan</th>
                        <th style="text-align: right;">Total Nilai (Rp)</th>
                        <th>Status Kedatangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($purchaseOrders)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="ph-fill ph-receipt"></i>
                                    <h3>Belum Ada Dokumen PO</h3>
                                    <p>Klik tombol "Terbitkan PO Baru" di kanan atas untuk memulai pemesanan material.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($purchaseOrders as $po): ?>
                            <tr>
                                <td>
                                    <span class="po-badge"><?= esc($po['po_number']) ?></span>
                                </td>
                                <td style="text-align: left;">
                                    <div class="vendor-badge">
                                        <i class="ph-fill ph-buildings"></i> <?= esc($po['supplier_name']) ?>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted); font-size: 12px; font-weight: 800;">
                                    <i class="ph-bold ph-calendar-blank"></i> <?= date('d M Y', strtotime($po['po_date'])) ?>
                                </td>
                                <td style="text-align: right; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; font-size: 15px;">
                                    <?= number_format($po['total_amount'], 0, ',', '.') ?>
                                </td>
                                <td>
                                    <?php if($po['status'] == 'ORDERED'): ?>
                                        <span class="status-badge bg-ordered"><i class="ph-bold ph-truck"></i> Menunggu Truk</span>
                                    <?php else: ?>
                                        <span class="status-badge bg-received"><i class="ph-bold ph-check-circle"></i> Selesai Masuk</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= base_url('/procurement/detail/'.$po['id']) ?>" class="btn-detail" title="Lihat Detail PO">
                                            <i class="ph-bold ph-eye"></i>
                                        </a>

                                        <?php if($po['status'] == 'ORDERED'): ?>
                                            <a href="#" class="btn-receive" onclick="openConfirmModal(event, '<?= base_url('/procurement/receive_goods/'.$po['id']) ?>', 'receive')" title="Tandai Barang Tiba & Masuk Gudang">
                                                <i class="ph-bold ph-package"></i> Terima
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="tab-supplier" class="tab-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 10px;">
        <div style="font-size: 12px; font-weight: 800; color: var(--text-muted); background: var(--bg-surface); padding: 12px 20px; border-radius: 14px; border: 1px solid var(--border-subtle); box-shadow: 0 4px 10px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 10px;">
            <i class="ph-fill ph-users-three" style="font-size: 18px; color: #4f46e5;"></i>
            Total Vendor Aktif: <span style="color: var(--text-main); font-size: 16px; font-weight: 900; font-family: 'Space Mono', monospace;"><?= count($suppliers) ?></span>
        </div>
        <button class="btn-secondary" onclick="openModal('modalSupplier')">
            <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Vendor Baru
        </button>
    </div>

    <div class="bento-card" style="padding: 20px;">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">Nama Perusahaan / Vendor</th>
                        <th style="text-align: left;">Kontak PIC</th>
                        <th>Telepon Resmi</th>
                        <th style="text-align: left;">Alamat Pengiriman</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($suppliers)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="ph-fill ph-buildings"></i>
                                    <h3>Belum Ada Data Vendor</h3>
                                    <p>Klik tombol "Tambah Vendor Baru" untuk mendaftarkan mitra suplai bahan baku Anda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($suppliers as $sup): ?>
                            <tr>
                                <td style="font-weight: 900; color: var(--text-main); font-size: 14px; text-align: left;">
                                    <div class="vendor-badge">
                                        <i class="ph-fill ph-buildings" style="color: #4f46e5; background: rgba(79, 70, 229, 0.1); border-color: transparent;"></i> 
                                        <?= esc($sup['supplier_name']) ?>
                                    </div>
                                </td>
                                <td style="text-align: left;">
                                    <div style="font-weight: 800; display:flex; align-items:center; gap:6px; font-size: 12px; color: var(--text-muted);">
                                        <i class="ph-fill ph-user-circle" style="font-size: 16px;"></i> <?= esc($sup['contact_person'] ?? '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-family: 'Space Mono', monospace; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.08); padding: 4px 10px; border-radius: 6px; display: inline-block; border: 1px dashed rgba(16, 185, 129, 0.3);">
                                        <?= esc($sup['phone'] ?? '-') ?>
                                    </div>
                                </td>
                                <td style="white-space: normal; line-height: 1.4; font-size: 12px; color: var(--text-muted); max-width: 250px; text-align: left;">
                                    <?= esc($sup['address'] ?? '-') ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="#" onclick="openConfirmModal(event, '<?= base_url('/procurement/delete_supplier/'.$sup['id']) ?>', 'delete')" class="btn-delete" title="Hapus Vendor">
                                            <i class="ph-bold ph-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalSupplier">
    <div class="modal-box" style="border-top: 8px solid #4f46e5;">
        <div class="modal-header">
            <div class="modal-title">
                <div style="background: rgba(79, 70, 229, 0.1); width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #4f46e5;">
                    <i class="ph-fill ph-buildings" style="font-size: 24px;"></i>
                </div>
                Registrasi Vendor Baru
            </div>
            <button class="btn-close" onclick="closeModal('modalSupplier')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        
        <form id="formSupplier" action="<?= base_url('/procurement/store_supplier') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Nama Perusahaan / Vendor</label>
                <input type="text" name="supplier_name" class="form-control" placeholder="Cth: PT Baja Stainless Indonesia" required autocomplete="off">
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label>Nama Kontak Utama (PIC)</label>
                    <input type="text" name="contact_person" class="form-control" placeholder="Cth: Bpk. Hendra" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Nomor Telepon / WhatsApp</label>
                    <input type="text" name="phone" class="form-control" placeholder="Cth: 0812..." autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label>Alamat Lengkap Operasional</label>
                <textarea name="address" class="form-control" rows="3" placeholder="Tuliskan alamat gudang atau kantor supplier..." style="resize: none; line-height: 1.5;"></textarea>
            </div>
            
            <button type="submit" id="btnSubmitSupplier" class="btn-submit-modal">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> <span>Simpan Data Vendor</span>
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalConfirm">
    <div class="modal-box" style="max-width: 400px; text-align: center; padding: 40px 30px;">
        <div id="confirmIconWrapper" class="icon-wrapper"></div>
        
        <h2 id="confirmTitle" style="font-size: 22px; font-weight: 900; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.5px;">Konfirmasi</h2>
        <p id="confirmText" style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 30px; font-weight: 500;"></p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <button onclick="closeModal('modalConfirm')" class="btn-secondary" style="justify-content: center; font-size: 14px;">Batal</button>
            <a href="#" id="confirmBtnYes" class="btn-primary" style="justify-content: center; font-size: 14px; text-decoration: none;">Ya, Lanjutkan</a>
        </div>
    </div>
</div>

<script>
    // --- 1. Tab Navigation Logic (Smooth Switching) ---
    function switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
        
        event.currentTarget.classList.add('active');
        document.getElementById('tab-' + tabName).classList.add('active');
    }

    // Auto-Buka Tab jika ada parameter URL
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'supplier') {
            document.querySelector('[onclick="switchTab(\'supplier\')"]').click();
        }
    });

    // --- 2. Modal Logic ---
    function openModal(modalId) { document.getElementById(modalId).classList.add('active'); }
    function closeModal(modalId) { document.getElementById(modalId).classList.remove('active'); }
    window.onclick = function(event) { if (event.target.classList.contains('modal-overlay')) { event.target.classList.remove('active'); } }

    // --- 3. AJAX: SIMPAN VENDOR BARU (Terintegrasi ke Global Toast) ---
    document.getElementById('formSupplier').addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const form = this;
        const btn = document.getElementById('btnSubmitSupplier');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');
        
        btn.disabled = true;
        btnText.innerText = "Menyimpan...";
        btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message);
                form.reset();
                closeModal('modalSupplier');
                // Pindah tab secara halus dengan me-replace URL
                setTimeout(() => { window.location.replace("<?= base_url('/procurement') ?>?tab=supplier"); }, 1200);
            } else {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message, true);
                btn.disabled = false; btnText.innerText = "Simpan Data Vendor"; btnIcon.className = "ph-bold ph-floppy-disk";
            }
        })
        .catch(err => {
            if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Koneksi Server Gagal", true);
            btn.disabled = false; btnText.innerText = "Simpan Data Vendor"; btnIcon.className = "ph-bold ph-floppy-disk";
        })
    });

    // --- 4. Custom Alert Confirmation Logic (Terima Barang & Hapus) ---
    function openConfirmModal(event, actionUrl, type) {
        event.preventDefault();
        const modal = document.getElementById('modalConfirm');
        const title = document.getElementById('confirmTitle');
        const text = document.getElementById('confirmText');
        const iconWrap = document.getElementById('confirmIconWrapper');
        const btnYes = document.getElementById('confirmBtnYes');
        
        if(type === 'receive') {
            iconWrap.style.background = 'rgba(16, 185, 129, 0.15)';
            iconWrap.style.color = '#10b981';
            iconWrap.style.border = '2px dashed rgba(16, 185, 129, 0.4)';
            iconWrap.innerHTML = '<i class="ph-fill ph-package" style="font-size: 38px;"></i>';
            
            title.innerText = 'Terima Barang Ini?';
            text.innerText = 'Truk Supplier sudah datang? Menekan "Terima" akan otomatis menjurnal transaksi ke Akuntansi dan menambah stok fisik Bahan Baku di Master Gudang.';
            
            btnYes.style.background = 'linear-gradient(135deg, #10b981, #059669)';
            btnYes.style.boxShadow = '0 8px 20px -6px rgba(16, 185, 129, 0.5)';
            btnYes.innerHTML = '<i class="ph-bold ph-check-square-offset"></i> Ya, Terima Barang';
            
        } else if(type === 'delete') {
            iconWrap.style.background = 'rgba(239, 68, 68, 0.15)';
            iconWrap.style.color = '#ef4444';
            iconWrap.style.border = '2px dashed rgba(239, 68, 68, 0.4)';
            iconWrap.innerHTML = '<i class="ph-fill ph-trash" style="font-size: 38px;"></i>';
            
            title.innerText = 'Hapus Vendor?';
            text.innerText = 'Yakin ingin menghapus Vendor ini secara permanen? Data yang telah dihapus tidak dapat dikembalikan lagi.';
            
            btnYes.style.background = 'linear-gradient(135deg, #ef4444, #dc2626)';
            btnYes.style.boxShadow = '0 8px 20px -6px rgba(239, 68, 68, 0.5)';
            btnYes.innerHTML = '<i class="ph-bold ph-trash"></i> Hapus Permanen';
        }
        
        btnYes.href = actionUrl;
        modal.classList.add('active');
    }
</script>

<?= $this->endSection() ?>