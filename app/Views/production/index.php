<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER
       ========================================================= */
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;}
    .page-title h1 { font-size: 24px; font-weight: 900; color: var(--text-main); margin-bottom: 4px; display: flex; align-items: center; gap: 10px; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); margin: 0; font-weight: 500;}
    
    .btn-primary { background: #8b5cf6; color: #fff; border: none; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4); transition: all 0.2s;}
    .btn-primary:hover { background: #7c3aed; transform: translateY(-2px);}

    /* =========================================================
       2. GRID LAYOUT & CARDS (COMPACT)
       ========================================================= */
    .grid-layout { display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: start;}
    @media (max-width: 1024px) { .grid-layout { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 20px; box-shadow: 0 8px 24px -8px rgba(0,0,0,0.05); }
    .card-title { font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 12px;}

    /* =========================================================
       3. FORM SPK
       ========================================================= */
    .form-group { margin-bottom: 12px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 10px 14px; border-radius: 10px; font-size: 13px; color: var(--text-main); font-weight: 600; outline: none; transition: 0.2s;}
    .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15); background: var(--bg-surface);}

    .btn-submit-spk { width: 100%; background: #2563eb; color: #fff; border: none; padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 800; cursor: pointer; transition: all 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 15px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);}
    .btn-submit-spk:hover { background: #1d4ed8; transform: translateY(-2px);}
    .btn-submit-spk:disabled { background: var(--border-subtle); color: var(--text-muted); box-shadow: none; cursor: not-allowed; transform: none;}

    /* =========================================================
       4. TABEL SPK (DENSE)
       ========================================================= */
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 14px 16px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 14px 16px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600; vertical-align: middle;}
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(37, 99, 235, 0.02); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }
    
    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 900; display: inline-flex; align-items: center; gap: 4px; text-transform: uppercase;}
    .s-draft { background: rgba(107, 114, 128, 0.1); color: #6b7280; border: 1px solid rgba(107, 114, 128, 0.2);}
    .s-progress { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2); animation: pulseStatus 2s infinite;}
    .s-completed { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}

    @keyframes pulseStatus { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }

    .btn-complete { background: var(--bg-surface); color: #10b981; border: 1px dashed #10b981; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;}
    .btn-complete:hover { background: #10b981; color: #fff; transform: translateY(-2px);}

    .empty-state { text-align: center; padding: 40px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 48px; color: var(--border-subtle); margin-bottom: 10px; display: block; }
    .empty-state p { font-size: 13px; font-weight: 500; margin: 0;}

    /* =========================================================
       5. MODAL KONFIRMASI SELESAI
       ========================================================= */
    .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; display: none; justify-content: center; align-items: center; opacity: 0; transition: opacity 0.3s;}
    .modal-overlay.active { display: flex; opacity: 1; }
    .modal-box { background: var(--bg-surface); border-radius: 20px; width: 100%; max-width: 400px; padding: 30px; text-align: center; transform: scale(0.95) translateY(10px); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.3); border: 1px solid var(--border-subtle);}
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>
            <div style="background: rgba(37, 99, 235, 0.1); color: #2563eb; padding: 6px; border-radius: 10px; display: flex;">
                <i class="ph-fill ph-factory"></i>
            </div>
            Pusat Eksekusi Manufaktur
        </h1>
        <p>Terbitkan Surat Perintah Kerja (SPK) dan pantau proses produksi.</p>
    </div>
    
    <a href="<?= base_url('/production/bom_builder') ?>" class="btn-primary">
        <i class="ph-bold ph-flask" style="font-size: 16px;"></i> Buat Resep (BoM) Baru
    </a>
</div>

<div class="grid-layout">
    <div class="bento-card" style="border-top: 4px solid #2563eb; position: sticky; top: 20px;">
        <div class="card-title"><i class="ph-fill ph-clipboard-text" style="color: #2563eb; font-size: 20px;"></i> Terbitkan SPK Baru</div>
        
        <form action="<?= base_url('/production/create_spk') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Pilih Resep Produksi (BoM)</label>
                <select name="bom_id" class="form-control" required>
                    <option value="">-- Pilih Target Produk --</option>
                    <?php foreach($boms as $b): ?>
                        <option value="<?= $b['id'] ?>">[<?= esc($b['fg_sku']) ?>] <?= esc($b['recipe_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($boms)): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px dashed rgba(239, 68, 68, 0.3); color: #ef4444; font-size: 11px; padding: 8px 10px; border-radius: 8px; margin-top: 10px; font-weight: 700; display: flex; gap: 6px; align-items: flex-start;">
                        <i class="ph-fill ph-warning-circle" style="font-size: 16px;"></i> 
                        Sistem mendeteksi Anda belum membuat Resep (BoM). Klik tombol ungu di atas.
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Target Kuantitas (Pcs)</label>
                <input type="number" name="planned_qty" class="form-control" placeholder="Cth: 50" required min="1">
            </div>

            <button type="submit" class="btn-submit-spk" <?= empty($boms) ? 'disabled' : '' ?>>
                <i class="ph-bold ph-paper-plane-tilt" style="font-size: 16px;"></i> Kirim SPK ke Bengkel
            </button>
        </form>
    </div>

    <div class="bento-card">
        <div class="card-title"><i class="ph-fill ph-kanban" style="color: var(--text-muted); font-size: 20px;"></i> Papan Pemantauan SPK</div>
        
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Dokumen SPK</th>
                        <th>Target Knalpot (BoM)</th>
                        <th style="text-align: center;">Jumlah Target</th>
                        <th>Status Produksi</th>
                        <th style="text-align: right;">Aksi Pabrik</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($workOrders)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="ph-fill ph-clipboard-text"></i>
                                    <h3 style="margin:0 0 5px 0; color: var(--text-main); font-weight: 900; font-size: 15px;">Papan SPK Kosong</h3>
                                    <p>Belum ada perintah produksi yang berjalan.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($workOrders as $wo): ?>
                            <tr>
                                <td>
                                    <div style="font-family: 'Space Mono', monospace; font-weight: 900; color: #2563eb; background: rgba(37, 99, 235, 0.1); padding: 4px 8px; border-radius: 6px; display: inline-block;">
                                        <?= esc($wo['spk_number']) ?>
                                    </div>
                                    <div style="font-size: 10px; color: var(--text-muted); font-weight: 700; margin-top: 6px; display: flex; align-items: center; gap: 4px;">
                                        <i class="ph-bold ph-calendar-blank"></i> <?= date('d M Y', strtotime($wo['start_date'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 12px; font-weight: 800; color: var(--text-main); margin-bottom: 4px;"><?= esc($wo['recipe_name']) ?></div>
                                    <div style="font-size: 10px; color: var(--text-muted); font-family: 'Space Mono', monospace; background: var(--bg-base); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border-subtle); display: inline-block;">
                                        <?= esc($wo['fg_sku']) ?>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <span style="font-family: 'Space Mono', monospace; font-size: 15px; font-weight: 900; color: var(--text-main);">
                                        <?= $wo['planned_qty'] ?> <span style="font-size: 10px; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif;">Pcs</span>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        if($wo['status'] == 'IN_PROGRESS') echo '<span class="status-badge s-progress"><i class="ph-bold ph-spinner-gap ph-spin"></i> Dikerjakan</span>';
                                        elseif($wo['status'] == 'COMPLETED') echo '<span class="status-badge s-completed"><i class="ph-fill ph-check-circle"></i> Selesai Diproduksi</span>';
                                        else echo '<span class="status-badge s-draft">Draft</span>';
                                    ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if($wo['status'] == 'IN_PROGRESS'): ?>
                                        <a href="#" onclick="openConfirmModal(event, '<?= base_url('/production/complete_spk/'.$wo['id']) ?>')" class="btn-complete" title="Konversi Material ke Produk Jadi">
                                            <i class="ph-bold ph-check-square-offset"></i> Tandai Selesai
                                        </a>
                                    <?php elseif($wo['status'] == 'COMPLETED'): ?>
                                        <div style="font-size: 10px; color: var(--text-muted); font-weight: 700; display: inline-flex; align-items: center; gap: 4px; background: var(--bg-base); padding: 4px 8px; border-radius: 6px;">
                                            <i class="ph-bold ph-clock"></i> Selesai: <?= date('d M, H:i', strtotime($wo['completed_at'])) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalConfirm">
    <div class="modal-box">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(16, 185, 129, 0.15); color: #10b981; border: 2px solid rgba(16, 185, 129, 0.3); display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 15px auto;">
            <i class="ph-fill ph-check-circle"></i>
        </div>
        <h2 style="margin: 0 0 8px 0; font-size: 18px; font-weight: 900; color: var(--text-main);">Tandai SPK Selesai?</h2>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; margin-bottom: 25px;">
            Aksi ini akan otomatis <b>memotong stok Material (MAT)</b> sesuai resep, dan <b>menambah stok Produk Jadi (PRD)</b> di gudang Anda. Lanjutkan?
        </p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <button onclick="closeModal('modalConfirm')" style="background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.2s;">Batal</button>
            <a href="#" id="confirmBtnYes" style="background: #10b981; color: white; text-decoration: none; padding: 12px; border-radius: 10px; font-weight: 800; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 6px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                Ya, Konversi Stok
            </a>
        </div>
    </div>
</div>

<script>
    function openConfirmModal(event, url) {
        event.preventDefault();
        document.getElementById('confirmBtnYes').href = url;
        document.getElementById('modalConfirm').classList.add('active');
    }
    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }
    window.onclick = function(e) {
        if (e.target.classList.contains('modal-overlay')) closeModal('modalConfirm');
    }
</script>

<?= $this->endSection() ?>