<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* PAGE HEADER */
    .page-header { margin-bottom: 30px; }
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 500; margin: 0;}
    
    .layout-grid { display: grid; grid-template-columns: 1fr 2.5fr; gap: 25px; align-items: start;}
    @media (max-width: 1024px) { .layout-grid { grid-template-columns: 1fr; } }

    /* FORM CARD (KIRI) */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; box-shadow: 0 15px 35px -10px rgba(0,0,0,0.05); padding: 30px;}
    .card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px;}

    .form-group { margin-bottom: 18px;}
    .form-group label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: 12px; font-size: 14px; font-weight: 600; outline: none; color: var(--text-main); transition: 0.3s;}
    .form-control:focus { border-color: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1); background: var(--bg-surface);}

    .btn-submit { width: 100%; background: #f59e0b; color: #fff; border: none; padding: 16px; border-radius: 14px; font-size: 15px; font-weight: 800; cursor: pointer; transition: all 0.3s; display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 10px; box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.5);}
    .btn-submit:hover { background: #d97706; transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(245, 158, 11, 0.6);}

    /* ASSET GRID (KANAN) */
    .asset-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    .asset-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); position: relative; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);}
    .asset-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -10px rgba(0,0,0,0.1); border-color: #f59e0b; }
    
    .card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;}
    .a-code { font-size: 12px; font-family: 'Space Mono', monospace; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 6px 12px; border-radius: 8px; font-weight: 800; display: inline-block;}
    
    /* Tombol Hapus Aset */
    .btn-delete-card { background: rgba(239, 68, 68, 0.05); color: #ef4444; border: 1px solid transparent; width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; cursor: pointer; transition: all 0.3s; text-decoration: none;}
    .btn-delete-card:hover { background: #ef4444; color: #fff; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3); transform: scale(1.05) rotate(5deg);}

    .a-name { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 10px; line-height: 1.3; letter-spacing: -0.5px;}
    
    .status-indicator { display: inline-flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 900; padding: 6px 12px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .st-ACTIVE { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .st-MAINTENANCE { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);}
    .st-BROKEN { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);}

    .btn-update { width: 100%; background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 12px; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; margin-top: 10px; transition: 0.2s;}
    .btn-update:hover { background: var(--border-subtle); color: #f59e0b;}

    /* =========================================================
       MODAL KONFIRMASI HAPUS (PREMIUM)
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    
    .modal-box { background: var(--bg-surface); border-radius: 28px; width: 100%; max-width: 420px; padding: 45px 35px; text-align: center; transform: scale(0.95) translateY(20px); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 30px 60px -15px rgba(0,0,0,0.4); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }

    .icon-wrapper { width: 88px; height: 88px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 25px auto; font-size: 44px; animation: pulseIcon 2s infinite; background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 2px solid rgba(239, 68, 68, 0.3);}
    @keyframes pulseIcon { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

    .btn-cancel-modal { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 14px; border-radius: 14px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s; width: 100%;}
    .btn-cancel-modal:hover { background: var(--border-subtle); }

    .btn-danger-modal { background: #ef4444; color: #fff; border: none; padding: 14px; border-radius: 14px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s; width: 100%; text-decoration: none; box-shadow: 0 8px 20px -6px rgba(239, 68, 68, 0.5);}
    .btn-danger-modal:hover { background: #dc2626; transform: translateY(-2px); box-shadow: 0 12px 25px -6px rgba(239, 68, 68, 0.6);}
</style>

<div class="page-header">
    <div class="page-title">
        <h1>
            <div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 10px; border-radius: 14px; display: flex;">
                <i class="ph-fill ph-wrench"></i>
            </div>
            Manajemen Aset Mesin
        </h1>
        <p>Data inventaris mesin pabrik, ubah status perawatan, atau hapus aset yang sudah tidak digunakan.</p>
    </div>
</div>

<div class="layout-grid">
    <div class="bento-card" style="border-top: 6px solid #f59e0b; height: fit-content; position: sticky; top: 20px;">
        <div class="card-title"><i class="ph-bold ph-plus-circle" style="color: #f59e0b; font-size: 20px;"></i> Registrasi Mesin Baru</div>
        <form action="<?= base_url('/asset/store') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Kode Aset (ID Unik)</label>
                <input type="text" name="asset_code" class="form-control" placeholder="Cth: AST-LAS-003" required style="font-family: 'Space Mono', monospace; text-transform: uppercase;">
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
                <i class="ph-bold ph-floppy-disk" style="font-size: 20px;"></i> Simpan ke Database
            </button>
        </form>
    </div>

    <div>
        <div class="asset-grid">
            <?php if(empty($assets)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: var(--bg-surface); border-radius: 24px; border: 1px dashed var(--border-subtle); color: var(--text-muted);">
                    <i class="ph-fill ph-wrench" style="font-size: 64px; opacity: 0.3; margin-bottom: 15px; display: block;"></i>
                    <h3 style="color: var(--text-main); font-weight: 900; margin-bottom: 5px;">Belum Ada Aset Terdaftar</h3>
                    <p style="font-size: 14px;">Silakan gunakan form di sebelah kiri untuk mendata mesin pabrik Anda.</p>
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
                            <label style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; display: block; letter-spacing: 0.5px;">Ubah Status Mesin:</label>
                            
                            <select name="status" class="form-control" style="padding: 10px 15px; font-size: 13px; margin-bottom: 10px; cursor: pointer;">
                                <option value="ACTIVE" <?= $a['status']=='ACTIVE'?'selected':'' ?>>🟢 Aktif Beroperasi</option>
                                <option value="MAINTENANCE" <?= $a['status']=='MAINTENANCE'?'selected':'' ?>>🟠 Maintenance / Servis</option>
                                <option value="BROKEN" <?= $a['status']=='BROKEN'?'selected':'' ?>>🔴 Rusak / Mati Total</option>
                            </select>
                            
                            <button type="submit" class="btn-update">Perbarui Status</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalDelete">
    <div class="modal-box">
        <div class="icon-wrapper">
            <i class="ph-fill ph-trash"></i>
        </div>
        
        <h2 style="font-size: 24px; font-weight: 900; color: var(--text-main); margin-bottom: 12px; letter-spacing: -0.5px;">Hapus Aset Mesin?</h2>
        <p style="font-size: 15px; color: var(--text-muted); line-height: 1.6; margin-bottom: 35px; font-weight: 500;">
            Yakin ingin menghapus aset ini secara permanen? Data yang telah dihapus tidak dapat dikembalikan ke dalam inventaris pabrik.
        </p>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <button onclick="closeDeleteModal()" class="btn-cancel-modal">Batal</button>
            <a href="#" id="confirmBtnDelete" class="btn-danger-modal" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                Ya, Hapus
            </a>
        </div>
    </div>
</div>

<script>
    // --- Logika Modal Hapus ---
    function openDeleteModal(event, actionUrl) {
        event.preventDefault(); // Cegah klik langsung berpindah halaman
        
        const modal = document.getElementById('modalDelete');
        const btnYes = document.getElementById('confirmBtnDelete');
        
        // Pasang URL eksekusi ke tombol Ya, Hapus
        btnYes.href = actionUrl;
        
        // Tampilkan Modal dengan animasi
        modal.classList.add('active');
    }

    function closeDeleteModal() { 
        document.getElementById('modalDelete').classList.remove('active'); 
    }
    
    // Tutup modal jika klik area gelap di luarnya
    window.onclick = function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
        }
    }
</script>

<?= $this->endSection() ?>