<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER & BUTTONS
       ========================================================= */
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;}
    
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(245, 158, 11, 0.05)); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(245, 158, 11, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 500; margin: 0;}

    .header-actions { display: flex; gap: 12px; flex-wrap: wrap; }

    .btn-create-asset { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; padding: 14px 24px; border-radius: 14px; font-size: 14px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.5); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);}
    .btn-create-asset:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(245, 158, 11, 0.6);}

    /* =========================================================
       2. ASSET GRID (FULL WIDTH)
       ========================================================= */
    .asset-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
    
    .asset-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); position: relative; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);}
    .asset-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -15px rgba(245, 158, 11, 0.15); border-color: rgba(245, 158, 11, 0.3); }
    
    .card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;}
    .a-code { font-size: 12px; font-family: 'Space Mono', monospace; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 6px 12px; border-radius: 8px; font-weight: 800; color: #f59e0b;}
    
    /* Tombol Hapus Aset */
    .btn-delete-card { background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1px solid transparent; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; transition: all 0.3s; text-decoration: none;}
    .btn-delete-card:hover { background: #ef4444; color: #fff; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transform: scale(1.05) rotate(5deg);}

    .a-name { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 10px; line-height: 1.3; letter-spacing: -0.5px;}
    
    .status-indicator { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 900; padding: 6px 12px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .st-ACTIVE { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .st-MAINTENANCE { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);}
    .st-BROKEN { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);}

    .btn-update { width: 100%; background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 12px; border-radius: 12px; font-weight: 800; font-size: 13px; cursor: pointer; margin-top: 10px; transition: 0.3s;}
    .btn-update:hover { background: var(--border-subtle); color: #f59e0b;}

    .empty-state { grid-column: 1 / -1; text-align: center; padding: 80px 20px; background: var(--bg-surface); border-radius: 24px; border: 2px dashed var(--border-subtle); color: var(--text-muted);}
    .empty-state i { font-size: 64px; opacity: 0.3; margin-bottom: 15px; display: block; color: #f59e0b;}
    .empty-state h3 { color: var(--text-main); font-weight: 900; margin-bottom: 8px; font-size: 18px;}

    /* =========================================================
       3. MODAL STYLES (PREMIUM)
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(6px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 24px; width: 100%; max-width: 480px; padding: 35px 40px; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }

    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
    .modal-title { font-size: 20px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 12px;}
    .btn-close { background: var(--bg-base); border: 1px solid var(--border-subtle); width: 36px; height: 36px; border-radius: 50%; font-size: 16px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;}
    .btn-close:hover { background: #ef4444; color: #fff; border-color: #ef4444; transform: rotate(90deg);}

    /* Form within Modal */
    .form-group { margin-bottom: 20px; text-align: left;}
    .form-group label { display: block; font-size: 12px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: 12px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none; transition: 0.3s;}
    .form-control:focus { border-color: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15); background: var(--bg-surface);}

    .btn-submit { width: 100%; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; padding: 18px; border-radius: 14px; font-size: 15px; font-weight: 900; cursor: pointer; transition: all 0.3s ease; display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 10px; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.5);}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(245, 158, 11, 0.6);}

    /* Modal Delete (Danger) */
    .icon-wrapper { width: 80px; height: 80px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; font-size: 40px; animation: pulseIcon 2s infinite; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 2px dashed rgba(239, 68, 68, 0.4);}
    @keyframes pulseIcon { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

    .btn-cancel-modal { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 14px; border-radius: 14px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s; width: 100%;}
    .btn-cancel-modal:hover { background: var(--border-subtle); }

    .btn-danger-modal { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; border: none; padding: 14px; border-radius: 14px; font-weight: 900; font-size: 14px; cursor: pointer; transition: 0.3s; width: 100%; text-decoration: none; box-shadow: 0 8px 20px -6px rgba(239, 68, 68, 0.5); display: flex; align-items: center; justify-content: center; gap: 8px;}
    .btn-danger-modal:hover { transform: translateY(-2px); box-shadow: 0 12px 25px -6px rgba(239, 68, 68, 0.6);}
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-wrench"></i></div>
        <div>
            <h1>Manajemen Aset Mesin</h1>
            <p>Kelola inventaris mesin pabrik dan perbarui status operasionalnya.</p>
        </div>
    </div>
    
    <div class="header-actions">
        <button onclick="openModal('modalCreateAsset')" class="btn-create-asset">
            <i class="ph-bold ph-plus-circle" style="font-size: 20px;"></i> Registrasi Mesin Baru
        </button>
    </div>
</div>

<div class="asset-grid">
    <?php if(empty($assets)): ?>
        <div class="empty-state">
            <i class="ph-fill ph-wrench"></i>
            <h3>Belum Ada Aset Mesin Terdaftar</h3>
            <p style="font-size: 14px;">Klik tombol "Registrasi Mesin Baru" di sudut kanan atas untuk mendata mesin pabrik Anda.</p>
        </div>
    <?php else: ?>
        <?php foreach($assets as $a): ?>
            <div class="asset-card">
                <div class="card-header">
                    <div class="a-code"><i class="ph-bold ph-barcode"></i> <?= esc($a['asset_code']) ?></div>
                    
                    <a href="#" onclick="openDeleteModal(event, '<?= base_url('/asset/delete/'.$a['id']) ?>')" class="btn-delete-card" title="Hapus Aset Permanen">
                        <i class="ph-bold ph-trash"></i>
                    </a>
                </div>

                <div class="a-name"><?= esc($a['asset_name']) ?></div>
                
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 20px; display: flex; align-items: center; gap: 6px; font-weight: 600;">
                    <i class="ph-bold ph-calendar-blank"></i> Dibeli: <?= date('d M Y', strtotime($a['purchase_date'])) ?>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <div class="status-indicator st-<?= $a['status'] ?>">
                        <?php 
                            if($a['status'] == 'ACTIVE') echo '<i class="ph-fill ph-check-circle"></i> Beroperasi Normal';
                            elseif($a['status'] == 'MAINTENANCE') echo '<i class="ph-fill ph-warning-circle"></i> Sedang Perawatan';
                            else echo '<i class="ph-fill ph-x-circle"></i> Mesin Rusak Total';
                        ?>
                    </div>
                </div>

                <form action="<?= base_url('/asset/update_status/'.$a['id']) ?>" method="post" style="border-top: 1px dashed var(--border-subtle); padding-top: 15px;">
                    <?= csrf_field() ?>
                    <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 0.5px;">Pembaruan Status Berkala:</label>
                    
                    <select name="status" class="form-control" style="padding: 10px 14px; font-size: 13px; margin-bottom: 10px; cursor: pointer;">
                        <option value="ACTIVE" <?= $a['status']=='ACTIVE'?'selected':'' ?>>🟢 Aktif Beroperasi</option>
                        <option value="MAINTENANCE" <?= $a['status']=='MAINTENANCE'?'selected':'' ?>>🟠 Maintenance / Servis</option>
                        <option value="BROKEN" <?= $a['status']=='BROKEN'?'selected':'' ?>>🔴 Rusak / Mati Total</option>
                    </select>
                    
                    <button type="submit" class="btn-update"><i class="ph-bold ph-arrows-clockwise"></i> Simpan Status</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="modalCreateAsset">
    <div class="modal-box" style="border-top: 6px solid #f59e0b;">
        <div class="modal-header">
            <div class="modal-title">
                <div style="background: rgba(245, 158, 11, 0.1); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #f59e0b;">
                    <i class="ph-fill ph-wrench"></i>
                </div>
                Registrasi Mesin Baru
            </div>
            <button class="btn-close" onclick="closeModal('modalCreateAsset')" type="button"><i class="ph-bold ph-x"></i></button>
        </div>
        
        <form action="<?= base_url('/asset/store') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Kode Aset (ID Unik Mesin)</label>
                <input type="text" name="asset_code" class="form-control" placeholder="Cth: AST-LAS-003" required style="font-family: 'Space Mono', monospace; text-transform: uppercase; font-size: 15px; color: #f59e0b;">
            </div>
            
            <div class="form-group">
                <label>Nama / Jenis Mesin</label>
                <input type="text" name="asset_name" class="form-control" placeholder="Cth: Mesin Las Argon TIG 200A" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>Tanggal Pembelian / Kedatangan</label>
                <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace;">
            </div>

            <button type="submit" class="btn-submit">
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> Tambahkan ke Database Aset
            </button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalDelete">
    <div class="modal-box" style="max-width: 400px; text-align: center;">
        <div class="icon-wrapper">
            <i class="ph-fill ph-trash"></i>
        </div>
        
        <h2 style="font-size: 22px; font-weight: 900; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.5px;">Hapus Aset Mesin?</h2>
        <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 30px; font-weight: 500;">
            Yakin ingin menghapus aset ini secara permanen? Data riwayat mesin yang telah dihapus <b>tidak dapat dikembalikan</b> ke dalam inventaris pabrik.
        </p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <button onclick="closeModal('modalDelete')" class="btn-cancel-modal">Batal</button>
            <a href="#" id="confirmBtnDelete" class="btn-danger-modal">
                <i class="ph-bold ph-trash"></i> Ya, Hapus Aset
            </a>
        </div>
    </div>
</div>

<script>
    // General Modal Functions
    function openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    }
    function closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }

    // Modal Delete Wrapper
    function openDeleteModal(event, actionUrl) {
        event.preventDefault(); 
        document.getElementById('confirmBtnDelete').href = actionUrl;
        openModal('modalDelete');
    }
    
    // Close modal if clicked outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
        }
    }
</script>

<?= $this->endSection() ?>