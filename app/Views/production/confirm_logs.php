<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    :root {
        --prod-primary: #2563eb; --prod-primary-dark: #1d4ed8; --prod-primary-soft: rgba(37, 99, 235, 0.1);
        --prod-success: #10b981; --prod-success-soft: rgba(16, 185, 129, 0.1);
        --prod-warning: #f59e0b; --prod-warning-dark: #d97706; --prod-warning-soft: rgba(245, 158, 11, 0.1);
        --prod-danger: #ef4444; --prod-danger-soft: rgba(239, 68, 68, 0.1);
        --bg-surface: #ffffff; --bg-input: #f8fafc; --border-subtle: #e2e8f0;
        --text-main: #0f172a; --text-muted: #64748b;
    }
    html.dark { --bg-surface: #1e293b; --bg-input: #0f172a; --border-subtle: #334155; --text-main: #f8fafc; --text-muted: #94a3b8; }
    
    @keyframes slideInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    .prod-page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; width: 100%; flex-wrap: wrap; gap: 20px;} 
    .prod-page-title { display: flex; align-items: center; gap: 15px; animation: slideInUp 0.5s ease-out forwards; flex-wrap: wrap;}
    .prod-title-icon { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, var(--prod-warning), var(--prod-warning-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4); flex-shrink: 0;}
    .prod-title-text { display: flex; flex-direction: column; gap: 4px; }
    .prod-title-text h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -0.5px; line-height: 1.2;}
    .prod-title-text p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 0; line-height: 1.5;}

    .btn-back { background: var(--bg-surface); color: var(--text-muted); border: 1px solid var(--border-subtle); padding: 12px 20px; border-radius: 14px; font-size: 13px; font-weight: 800; text-decoration: none; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px;}
    .btn-back:hover { border-color: var(--prod-primary); color: var(--prod-primary); transform: translateY(-2px);}

    .prod-bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); animation: slideInUp 0.7s ease-out forwards; overflow: hidden; border-top: 6px solid var(--prod-warning);}
    .prod-table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border-radius: 14px; border: 1px solid var(--border-subtle); background: var(--bg-surface);}
    .prod-table { width: 100%; border-collapse: collapse; white-space: nowrap; min-width: 800px; }
    .prod-table th { text-align: left; padding: 14px 18px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-input); border-bottom: 2px solid var(--border-subtle);}
    .prod-table td { padding: 14px 18px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 700; vertical-align: middle;}
    .prod-table tr:hover td { background: rgba(0,0,0,0.02); }
    
    .spk-badge { font-family: 'Space Mono', monospace; font-size: 10px; color: var(--text-muted); background: var(--bg-input); padding: 4px 8px; border-radius: 6px; font-weight: 900; border: 1px solid var(--border-subtle); display: inline-block;}
    
    .btn-action-sm { padding: 10px 14px; border-radius: 10px; font-size: 12px; font-weight: 900; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; transition: 0.3s; border: none;}
    .btn-action-sm:hover { transform: translateY(-2px);}
    .btn-acc { background: var(--prod-success); color: #fff; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); }
    .btn-rej { background: var(--prod-danger-soft); color: var(--prod-danger); }
    .btn-rej:hover { background: var(--prod-danger); color: #fff; }

    @media (max-width: 768px) {
        .prod-page-header { flex-direction: column; align-items: flex-start; gap: 15px;}
        .btn-back { width: 100%; }
        .prod-bento-card { padding: 15px; border-radius: 16px; }
    }
</style>

<div class="prod-page-header">
    <div class="prod-page-title">
        <div class="prod-title-icon"><i class="ph-bold ph-bell-ringing"></i></div>
        <div class="prod-title-text">
            <h1>Antrean Konfirmasi Setoran</h1>
            <p>Terima untuk memotong stok bahan dan mencatat hutang upah pekerja.</p>
        </div>
    </div>
    <div class="prod-actions">
        <a href="<?= base_url('/production') ?>" class="btn-back"><i class="ph-bold ph-arrow-left"></i> Kembali ke Dashboard Produksi</a>
    </div>
</div>

<div class="prod-bento-card">
    <div class="prod-table-responsive">
        <table class="prod-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Waktu & Pekerja</th>
                    <th style="width: 30%;">SPK & Produk Target</th>
                    <th style="width: 20%;">Tahapan / Spesialisasi</th>
                    <th style="text-align:center; width: 15%;">Jumlah Setor</th>
                    <th style="text-align:center; width: 15%;">Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($pendingLogs)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:50px 20px; color:var(--text-muted);">
                            <i class="ph-duotone ph-check-circle" style="font-size: 48px; color: var(--prod-success); opacity: 0.5; margin-bottom: 10px; display: block;"></i>
                            <div style="font-weight: 800; font-size: 14px;">Tidak ada setoran yang menunggu</div>
                            <span style="font-size: 12px;">Semua setoran pekerja telah dikonfirmasi.</span>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($pendingLogs as $pl): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 900; font-size:13px; color: var(--text-main); margin-bottom: 4px;"><?= esc($pl['employee_name']) ?></div>
                            <div style="font-size:11px; font-weight:700; color: var(--text-muted);"><i class="ph-bold ph-clock"></i> <?= date('d M Y, H:i', strtotime($pl['production_date'])) ?></div>
                        </td>
                        <td>
                            <div style="font-size:12px; font-weight:800; color: var(--text-main); margin-bottom: 6px; white-space: normal; line-height: 1.3;"><?= esc($pl['item_name']) ?></div>
                            <div class="spk-badge"><?= esc($pl['spk_number']) ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 800; color: var(--prod-primary); font-size:13px; margin-bottom: 4px;"><?= esc($pl['operation_name']) ?></div>
                            <?php if($pl['notes']): ?>
                                <div style="font-size: 10px; color: var(--prod-warning-dark); font-weight: 800;"><i class="ph-fill ph-warning-circle"></i> Info: <?= esc($pl['notes']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <div style="font-family: 'Space Mono', monospace; font-weight: 900; font-size:22px; color: var(--prod-success);">
                                <?= $pl['qty_produced'] ?> <span style="font-size:12px;">Pcs</span>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                                <a href="<?= base_url('production/approve_log/'.$pl['id']) ?>" class="btn-action-sm btn-acc" onclick="return confirm('PENTING!\n\nMenerima setoran ini akan:\n1. Memotong bahan baku dari gudang.\n2. Mencatat upah ke gaji karyawan.\n3. Menambah stok produk (jika tahap final).\n\nLanjutkan?')"><i class="ph-bold ph-check"></i> Terima</a>
                                <a href="<?= base_url('production/reject_log/'.$pl['id']) ?>" class="btn-action-sm btn-rej" onclick="return confirm('Tolak setoran ini?\nSetoran akan dibatalkan dan tidak dihitung ke gaji pekerja.')"><i class="ph-bold ph-x"></i> Tolak</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>