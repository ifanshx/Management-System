<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .btn-primary { background: #2563eb; color: #fff; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); transition: 0.2s;}
    .btn-primary:hover { background: #1d4ed8; transform: translateY(-2px);}

    .grid-layout { display: grid; grid-template-columns: 350px 1fr; gap: 20px; align-items: start;}
    @media (max-width: 1024px) { .grid-layout { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-card); }
    .card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;}

    /* FORM SPK */
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 10px; font-size: 13px; color: var(--text-main); font-weight: 600; outline: none;}
    .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);}

    /* TABEL SPK */
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base);}
    td { padding: 15px 20px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600;}
    
    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 900; display: inline-flex; align-items: center; gap: 4px; text-transform: uppercase;}
    .s-draft { background: rgba(107, 114, 128, 0.1); color: #6b7280; }
    .s-progress { background: rgba(245, 158, 11, 0.1); color: #d97706; animation: pulse 2s infinite;}
    .s-completed { background: rgba(16, 185, 129, 0.1); color: #10b981; }

    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }

    .btn-complete { background: #10b981; color: #fff; border: none; padding: 8px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; text-decoration: none;}
    .btn-complete:hover { background: #059669; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-factory" style="color: #2563eb;"></i> Pusat Eksekusi Manufaktur</h1>
        <p>Terbitkan Surat Perintah Kerja (SPK) untuk bengkel las dan otomatisasi pemotongan bahan baku.</p>
    </div>
    
    <a href="<?= base_url('/production/bom_builder') ?>" class="btn-primary" style="background: #8b5cf6; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);">
        <i class="ph ph-flask"></i> Buat Resep Baru (BoM)
    </a>
</div>

<div class="grid-layout">
    <div class="bento-card" style="border-top: 4px solid #2563eb;">
        <div class="card-title"><i class="ph ph-clipboard-text"></i> Terbitkan SPK Baru</div>
        
        <form action="<?= base_url('/production/create_spk') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Pilih Resep Produksi (BoM)</label>
                <select name="bom_id" class="form-control" required>
                    <option value="">-- Pilih Knalpot yang akan dibuat --</option>
                    <?php foreach($boms as $b): ?>
                        <option value="<?= $b['id'] ?>">[<?= esc($b['fg_sku']) ?>] <?= esc($b['recipe_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($boms)): ?>
                    <small style="color: #ef4444; font-size: 11px; margin-top: 5px; display: block;">*Anda belum membuat Resep (BoM) satupun. Klik tombol ungu di atas.</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Target Kuantitas (Pcs)</label>
                <input type="number" name="planned_qty" class="form-control" placeholder="Cth: 50" required min="1">
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;" <?= empty($boms) ? 'disabled' : '' ?>>
                <i class="ph ph-paper-plane-right"></i> Kirim SPK ke Bengkel
            </button>
        </form>
    </div>

    <div class="bento-card">
        <div class="card-title"><i class="ph ph-kanban"></i> Papan Surat Perintah Kerja (SPK)</div>
        
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>No. SPK</th>
                        <th>Target Produk (Knalpot)</th>
                        <th>Jumlah</th>
                        <th>Status Produksi</th>
                        <th style="text-align: right;">Aksi Pabrik</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($workOrders)): ?>
                        <tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">Belum ada SPK yang berjalan hari ini.</td></tr>
                    <?php else: ?>
                        <?php foreach($workOrders as $wo): ?>
                            <tr>
                                <td style="font-family: monospace; font-weight: 800; color: #2563eb;"><?= esc($wo['spk_number']) ?></td>
                                <td>
                                    <div style="font-size: 13px; font-weight: 800;"><?= esc($wo['recipe_name']) ?></div>
                                    <div style="font-size: 10px; color: var(--text-muted);">SKU: <?= esc($wo['fg_sku']) ?></div>
                                </td>
                                <td><span style="background: var(--bg-base); padding: 4px 8px; border-radius: 6px; font-weight: 800;"><?= $wo['planned_qty'] ?> Pcs</span></td>
                                <td>
                                    <?php 
                                        if($wo['status'] == 'IN_PROGRESS') echo '<span class="status-badge s-progress"><i class="ph ph-spinner-gap ph-spin"></i> Dikerjakan</span>';
                                        elseif($wo['status'] == 'COMPLETED') echo '<span class="status-badge s-completed"><i class="ph ph-check-circle"></i> Selesai</span>';
                                        else echo '<span class="status-badge s-draft">Draft</span>';
                                    ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if($wo['status'] == 'IN_PROGRESS'): ?>
                                        <a href="<?= base_url('/production/complete_spk/'.$wo['id']) ?>" class="btn-complete" onclick="return confirm('PENTING: Menekan Selesai akan OTOMATIS memotong Stok Bahan Baku dan menambah Stok Knalpot. Lanjutkan?')">
                                            <i class="ph ph-check-square-offset"></i> Tandai Selesai
                                        </a>
                                    <?php elseif($wo['status'] == 'COMPLETED'): ?>
                                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 700;"><i class="ph ph-clock"></i> <?= date('d M, H:i', strtotime($wo['completed_at'])) ?></span>
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

<?= $this->endSection() ?>