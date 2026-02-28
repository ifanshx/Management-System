<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .grid-container { display: grid; grid-template-columns: 350px 1fr; gap: 25px; align-items: start; }
    @media (max-width: 900px) { .grid-container { grid-template-columns: 1fr; } }

    /* FORM PRODUKSI (LAYAR SENTUH / TABLET OPTIMIZED) */
    .factory-card { background: var(--bg-surface); border: 2px solid var(--border-subtle); border-radius: 20px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
    .card-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.5px;}

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; }
    
    .form-control-lg { width: 100%; background: var(--bg-base); border: 2px solid var(--border-subtle); padding: 16px 20px; border-radius: 12px; font-size: 16px; color: var(--text-main); font-weight: 700; transition: 0.2s;}
    .form-control-lg:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);}

    .btn-factory { width: 100%; background: #3b82f6; color: #fff; border: none; padding: 18px; border-radius: 12px; font-size: 16px; font-weight: 900; cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 10px; text-transform: uppercase; letter-spacing: 1px;}
    .btn-factory:hover { background: #2563eb; transform: translateY(-3px); box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);}

    /* TABEL RIWAYAT PRODUKSI */
    .history-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 25px; box-shadow: var(--shadow-card); }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; padding: 15px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base);}
    td { padding: 15px; border-bottom: 1px solid var(--border-subtle); font-size: 14px; font-weight: 600; color: var(--text-main);}
    
    .qty-badge { background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 12px; border-radius: 8px; font-weight: 900; display: inline-block;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-factory" style="color: #3b82f6;"></i> Modul Produksi Pabrik</h1>
        <p>Input hasil produksi harian. Sistem akan otomatis mensinkronkan penambahan stok ini ke gudang lokal dan toko Shopee.</p>
    </div>
</div>

<div class="grid-container">
    
    <div class="factory-card">
        <div class="card-title"><i class="ph ph-plus-circle"></i> Input Hasil Las/Poles</div>
        
        <form action="<?= base_url('/production/store_production') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Pilih Barang (SKU Gudang)</label>
                <select name="sku" class="form-control-lg" required>
                    <option value="">-- Ketik / Pilih Barang --</option>
                    <?php foreach($inventory as $inv): ?>
                        <option value="<?= esc($inv['sku']) ?>">
                            [ <?= esc($inv['sku']) ?> ] - <?= esc($inv['item_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Jumlah Selesai Diproduksi</label>
                <input type="number" name="qty_produced" class="form-control-lg" placeholder="Contoh: 50" required min="1">
            </div>

            <button type="submit" class="btn-factory">
                <i class="ph ph-check-square-offset"></i> Simpan & Sync
            </button>
        </form>
    </div>

    <div class="history-card">
        <div class="card-title" style="margin-bottom: 15px;"><i class="ph ph-clock-counter-clockwise"></i> Riwayat Produksi Hari Ini</div>
        
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Waktu Input</th>
                        <th>Kode SKU</th>
                        <th>Nama Knalpot</th>
                        <th>Qty Tambahan</th>
                        <th>Operator</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($todayLogs)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 40px; color: var(--text-muted); font-size: 13px;">
                                <i class="ph ph-coffee" style="font-size: 40px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                                Belum ada produksi yang dicatat hari ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($todayLogs as $log): ?>
                        <tr>
                            <td style="color: var(--text-muted); font-size: 12px;"><?= date('H:i', strtotime($log['production_date'])) ?> WIB</td>
                            <td style="font-family: monospace; font-weight: 800; color: var(--accent-main);"><?= esc($log['sku']) ?></td>
                            <td><?= esc($log['item_name']) ?></td>
                            <td><span class="qty-badge">+ <?= $log['qty_produced'] ?> Pcs</span></td>
                            <td style="font-size: 12px; color: var(--text-muted);"><i class="ph ph-user"></i> <?= esc($log['operator_name']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?= $this->endSection() ?>