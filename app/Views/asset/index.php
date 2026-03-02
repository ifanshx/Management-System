<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .asset-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    .asset-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-card); position: relative;}
    
    .a-code { font-size: 11px; font-family: monospace; background: var(--bg-base); padding: 4px 8px; border-radius: 6px; font-weight: 800; display: inline-block; margin-bottom: 10px;}
    .a-name { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 15px;}
    
    .status-indicator { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 800; padding: 8px 12px; border-radius: 8px;}
    .st-ACTIVE { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .st-MAINTENANCE { background: rgba(245, 158, 11, 0.1); color: #d97706; }
    .st-BROKEN { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 8px; border-radius: 8px; font-size: 12px; font-weight: 600; outline: none; color: var(--text-main); margin-top: 15px;}
    .btn-update { width: 100%; background: var(--text-main); color: var(--bg-surface); border: none; padding: 8px; border-radius: 8px; font-weight: 800; cursor: pointer; margin-top: 5px; transition: 0.2s;}
    .btn-update:hover { opacity: 0.8;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-wrench" style="color: #f59e0b;"></i> Manajemen Aset & Perawatan</h1>
        <p>Pantau status kesehatan mesin produksi pabrik untuk meminimalisir waktu henti (downtime).</p>
    </div>
</div>

<div class="asset-grid">
    <?php foreach($assets as $a): ?>
        <div class="asset-card" style="border-top: 4px solid <?= ($a['status']=='ACTIVE')?'#10b981':(($a['status']=='MAINTENANCE')?'#f59e0b':'#ef4444') ?>;">
            <div class="a-code"><i class="ph ph-barcode"></i> <?= esc($a['asset_code']) ?></div>
            <div class="a-name"><?= esc($a['asset_name']) ?></div>
            
            <div class="status-indicator st-<?= $a['status'] ?>">
                <i class="ph ph-activity"></i> Status: <?= $a['status'] ?>
            </div>

            <form action="<?= base_url('/asset/update_status/'.$a['id']) ?>" method="post">
                <?= csrf_field() ?>
                <select name="status" class="form-control" required>
                    <option value="ACTIVE" <?= $a['status']=='ACTIVE'?'selected':'' ?>>Beroperasi Normal</option>
                    <option value="MAINTENANCE" <?= $a['status']=='MAINTENANCE'?'selected':'' ?>>Sedang Diperbaiki</option>
                    <option value="BROKEN" <?= $a['status']=='BROKEN'?'selected':'' ?>>Rusak / Nonaktif</option>
                </select>
                <button type="submit" class="btn-update">Perbarui Status</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<?= $this->endSection() ?>