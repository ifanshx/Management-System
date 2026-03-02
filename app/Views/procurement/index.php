<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER & TYPOGRAPHY
       ========================================================= */
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0 0 8px 0; display: flex; align-items: center; gap: 12px; letter-spacing: -0.5px;}
    .page-title p { font-size: 14px; color: var(--text-muted); margin: 0; font-weight: 500;}
    
    /* =========================================================
       2. PREMIUM TAB NAVIGATION
       ========================================================= */
    .tab-nav { display: inline-flex; background: var(--bg-surface); padding: 8px; border-radius: 100px; border: 1px solid var(--border-subtle); margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);}
    .tab-btn { padding: 12px 24px; font-size: 14px; font-weight: 800; border-radius: 100px; border: none; background: transparent; color: var(--text-muted); cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 8px;}
    .tab-btn:hover { color: var(--text-main); }
    .tab-btn.active { background: var(--text-main); color: var(--bg-base); box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    html.dark .tab-btn.active { background: #4f46e5; color: #ffffff;}

    .tab-content { display: none; animation: slideFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .tab-content.active { display: block; }
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    /* =========================================================
       3. BUTTONS & COMPONENTS
       ========================================================= */
    .btn-primary { background: #4f46e5; color: #fff; border: none; padding: 12px 24px; border-radius: 14px; font-weight: 800; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 8px 20px -6px rgba(79, 70, 229, 0.5); transition: all 0.3s ease; text-decoration: none;}
    .btn-primary:hover { background: #4338ca; transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(79, 70, 229, 0.6);}
    
    .btn-secondary { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 12px 24px; border-radius: 14px; font-weight: 800; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); transition: all 0.3s ease;}
    .btn-secondary:hover { border-color: #4f46e5; color: #4f46e5; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(79, 70, 229, 0.08);}

    /* =========================================================
       4. BENTO TABLE CARDS
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; box-shadow: 0 15px 35px -10px rgba(0,0,0,0.05); overflow: hidden; margin-top: 10px; transition: 0.3s;}
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 20px 25px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 20px 25px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 600; vertical-align: middle; transition: background 0.2s;}
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(79, 70, 229, 0.02); }
    html.dark tr:hover td { background: rgba(79, 70, 229, 0.05); }

    /* Modern Badges */
    .po-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    .bg-ordered { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);}
    .bg-received { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}

    /* Action Buttons in Table */
    .table-actions { display: flex; justify-content: flex-end; gap: 8px; }
    .btn-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 800; transition: all 0.3s; text-decoration: none;}
    
    .btn-detail { background: rgba(59, 130, 246, 0.1); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.2); }
    .btn-detail:hover { background: #3b82f6; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);}
    
    .btn-receive { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); width: auto; padding: 0 14px; font-size: 12px; gap: 6px;}
    .btn-receive:hover { background: #10b981; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);}

    .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
    .btn-delete:hover { background: #ef4444; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}

    /* =========================================================
       5. MODALS (FORM & CONFIRMATION)
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 550px; padding: 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .modal-title { font-size: 22px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px; margin: 0;}
    
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 40px; height: 40px; border-radius: 50%; font-size: 18px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}

    /* Form Elements inside Modal */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 15px 20px; border-radius: 14px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none; transition: all 0.3s;}
    .form-control:focus { border-color: #4f46e5; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);}
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px;}

    /* Alert Modal Custom Icon */
    .icon-wrapper { width: 88px; height: 88px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 25px auto; font-size: 44px; animation: pulseIcon 2s infinite;}
    @keyframes pulseIcon { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
    
    /* Empty State */
    .empty-state { text-align: center; padding: 80px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 64px; color: var(--border-subtle); margin-bottom: 15px; display: block; }
    .empty-state h3 { font-size: 18px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }
    .empty-state p { font-size: 14px; font-weight: 500; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>
            <div style="background: rgba(79, 70, 229, 0.1); color: #4f46e5; padding: 10px; border-radius: 14px; display: flex;">
                <i class="ph-fill ph-truck"></i>
            </div>
            Procurement & Pembelian
        </h1>
        <p>Kelola pemesanan bahan baku ke Supplier dan lacak penerimaan barang gudang.</p>
    </div>
</div>

<div class="tab-nav">
    <button class="tab-btn active" onclick="switchTab('po')"><i class="ph-bold ph-receipt"></i> Riwayat PO</button>
    <button class="tab-btn" onclick="switchTab('supplier')"><i class="ph-bold ph-buildings"></i> Master Vendor</button>
</div>

<div id="tab-po" class="tab-content active">
    <div style="display: flex; justify-content: flex-end; margin-bottom: 15px;">
        <a href="<?= base_url('/procurement/create_po') ?>" class="btn-primary">
            <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Buat PO Baru
        </a>
    </div>

    <div class="bento-card">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th>Tanggal</th>
                        <th style="text-align: right;">Total (Rp)</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($purchaseOrders)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="ph-fill ph-receipt"></i>
                                    <h3>Belum Ada Dokumen PO</h3>
                                    <p>Klik tombol "Buat PO Baru" di kanan atas untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($purchaseOrders as $po): ?>
                            <tr>
                                <td>
                                    <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: #4f46e5; font-size: 14px; background: rgba(79, 70, 229, 0.08); padding: 6px 12px; border-radius: 8px; display: inline-block;">
                                        <?= esc($po['po_number']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 800; display:flex; align-items:center; gap:8px;">
                                        <div style="background: var(--bg-base); padding: 6px; border-radius: 8px; border: 1px solid var(--border-subtle); display: flex;"><i class="ph-fill ph-buildings" style="color: var(--text-muted);"></i></div>
                                        <?= esc($po['supplier_name']) ?>
                                    </div>
                                </td>
                                <td style="color: var(--text-muted); font-size: 13px; font-weight: 700;">
                                    <?= date('d M Y', strtotime($po['po_date'])) ?>
                                </td>
                                <td style="text-align: right; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; font-size: 15px;">
                                    <?= number_format($po['total_amount'], 0, ',', '.') ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if($po['status'] == 'ORDERED'): ?>
                                        <span class="po-badge bg-ordered">Menunggu</span>
                                    <?php else: ?>
                                        <span class="po-badge bg-received">Selesai</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= base_url('/procurement/detail/'.$po['id']) ?>" class="btn-icon btn-detail" title="Lihat Detail">
                                            <i class="ph-bold ph-eye"></i>
                                        </a>

                                        <?php if($po['status'] == 'ORDERED'): ?>
                                            <a href="#" class="btn-icon btn-receive" onclick="openConfirmModal(event, '<?= base_url('/procurement/receive_goods/'.$po['id']) ?>', 'receive')">
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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <div style="font-size: 13px; font-weight: 800; color: var(--text-muted); background: var(--bg-surface); padding: 12px 24px; border-radius: 14px; border: 1px solid var(--border-subtle); box-shadow: 0 4px 10px rgba(0,0,0,0.02);">
            Total Vendor: <span style="color: var(--text-main); font-size: 16px; margin-left: 5px; font-weight: 900; font-family: 'Space Mono', monospace;"><?= count($suppliers) ?></span>
        </div>
        <button class="btn-secondary" onclick="openModal('modalSupplier')">
            <i class="ph-bold ph-plus-circle" style="font-size: 18px;"></i> Tambah Vendor
        </button>
    </div>

    <div class="bento-card">
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nama Vendor</th>
                        <th>PIC</th>
                        <th>Telepon</th>
                        <th>Alamat</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($suppliers)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="ph-fill ph-buildings"></i>
                                    <h3>Belum Ada Data Vendor</h3>
                                    <p>Klik tombol "Tambah Vendor" untuk mendaftarkan mitra suplai bahan baku Anda.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($suppliers as $sup): ?>
                            <tr>
                                <td style="font-weight: 900; color: var(--text-main); font-size: 14px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(79, 70, 229, 0.1); color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                            <i class="ph-fill ph-buildings"></i>
                                        </div>
                                        <?= esc($sup['supplier_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; display:flex; align-items:center; gap:6px;"><i class="ph-fill ph-user-circle" style="color: var(--border-subtle); font-size: 20px;"></i> <?= esc($sup['contact_person'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <div style="font-family: 'Space Mono', monospace; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 6px 12px; border-radius: 8px; display: inline-block; border: 1px dashed rgba(16, 185, 129, 0.3);">
                                        <?= esc($sup['phone'] ?? '-') ?>
                                    </div>
                                </td>
                                <td style="white-space: normal; line-height: 1.5; font-size: 13px; color: var(--text-muted); max-width: 250px;">
                                    <?= esc($sup['address'] ?? '-') ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="#" onclick="openConfirmModal(event, '<?= base_url('/procurement/delete_supplier/'.$sup['id']) ?>', 'delete')" class="btn-icon btn-delete" title="Hapus Vendor">
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
                    <i class="ph-fill ph-buildings"></i>
                </div>
                Registrasi Vendor
            </div>
            <button class="btn-close" onclick="closeModal('modalSupplier')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        <form action="<?= base_url('/procurement/store_supplier') ?>" method="post">
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
            
            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 20px; margin-top: 15px; font-size: 16px;">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> Simpan Data Vendor
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalConfirm">
    <div class="modal-box" style="max-width: 420px; text-align: center; padding: 45px 35px;">
        <div id="confirmIconWrapper" class="icon-wrapper"></div>
        
        <h2 id="confirmTitle" style="font-size: 24px; font-weight: 900; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.5px;">Konfirmasi</h2>
        <p id="confirmText" style="font-size: 15px; color: var(--text-muted); line-height: 1.6; margin-bottom: 35px; font-weight: 500;"></p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <button onclick="closeModal('modalConfirm')" class="btn-secondary" style="justify-content: center; font-size: 15px;">Batal</button>
            <a href="#" id="confirmBtnYes" class="btn-primary" style="justify-content: center; text-decoration: none; font-size: 15px;">Ya, Lanjutkan</a>
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

    // --- 2. Standard Modal Logic (Bounce In/Out) ---
    function openModal(modalId) { 
        document.getElementById(modalId).classList.add('active'); 
    }
    
    function closeModal(modalId) { 
        document.getElementById(modalId).classList.remove('active'); 
    }
    
    // --- 3. Custom Alert Confirmation Logic (Dynamic Theming) ---
    function openConfirmModal(event, actionUrl, type) {
        event.preventDefault(); // Mencegah pindah halaman seketika
        
        const modal = document.getElementById('modalConfirm');
        const title = document.getElementById('confirmTitle');
        const text = document.getElementById('confirmText');
        const iconWrap = document.getElementById('confirmIconWrapper');
        const btnYes = document.getElementById('confirmBtnYes');
        
        // Atur gaya berdasarkan tipe aksi
        if(type === 'receive') {
            iconWrap.style.background = 'rgba(16, 185, 129, 0.15)';
            iconWrap.style.color = '#10b981';
            iconWrap.style.border = '2px solid rgba(16, 185, 129, 0.3)';
            iconWrap.innerHTML = '<i class="ph-fill ph-package"></i>';
            
            title.innerText = 'Terima Barang Ini?';
            text.innerText = 'Truk Supplier sudah datang? Menekan "Terima" akan otomatis mencatat dan menambah stok fisik Bahan Baku di Master Gudang Anda.';
            
            btnYes.style.background = '#10b981';
            btnYes.style.boxShadow = '0 8px 20px -6px rgba(16, 185, 129, 0.5)';
            btnYes.innerText = 'Ya, Terima Barang';
            
        } else if(type === 'delete') {
            iconWrap.style.background = 'rgba(239, 68, 68, 0.15)';
            iconWrap.style.color = '#ef4444';
            iconWrap.style.border = '2px solid rgba(239, 68, 68, 0.3)';
            iconWrap.innerHTML = '<i class="ph-fill ph-trash"></i>';
            
            title.innerText = 'Hapus Vendor?';
            text.innerText = 'Yakin ingin menghapus Vendor ini secara permanen? Data yang telah dihapus tidak dapat dikembalikan lagi.';
            
            btnYes.style.background = '#ef4444';
            btnYes.style.boxShadow = '0 8px 20px -6px rgba(239, 68, 68, 0.5)';
            btnYes.innerText = 'Hapus Permanen';
        }
        
        // Pasang URL eksekusi ke tombol Yes
        btnYes.href = actionUrl;
        
        // Tampilkan Modal
        modal.classList.add('active');
    }

    // --- 4. Close Modal if clicking outside the box ---
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
        }
    }
</script>

<?= $this->endSection() ?>