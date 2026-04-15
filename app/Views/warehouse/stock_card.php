<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .card-box {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 10px 35px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 25px;
    }
    .page-head h1 {
        margin: 0;
        font-size: 30px;
        font-weight: 900;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
    }
    @media (max-width: 900px) {
        .filter-grid { grid-template-columns: 1fr; }
    }
    .form-control {
        width: 100%;
        padding: 13px 15px;
        border-radius: 14px;
        border: 1px solid var(--border-subtle);
        background: var(--bg-input);
        font-weight: 700;
    }
    .btn-filter {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: white;
        border: none;
        padding: 14px 18px;
        border-radius: 14px;
        font-weight: 900;
        cursor: pointer;
    }
    .badge-type {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        display: inline-block;
    }
    .b-raw { background: rgba(245,158,11,.12); color: #d97706; }
    .b-fg  { background: rgba(16,185,129,.12); color: #059669; }
    .b-in  { color: #10b981; font-weight: 900; }
    .b-out { color: #ef4444; font-weight: 900; }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 14px 12px;
        border-bottom: 1px dashed var(--border-subtle);
        text-align: left;
        font-size: 13px;
    }
    th {
        font-size: 11px;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 900;
    }
</style>

<div class="page-head">
    <div>
        <h1>📦 Kartu Stok</h1>
        <p style="margin: 6px 0 0 0; color: var(--text-muted); font-weight: 600;">
            Audit semua mutasi stok bahan baku dan barang jadi.
        </p>
    </div>
</div>

<div class="card-box">
    <form method="get">
        <div class="filter-grid">
            <div>
                <label style="font-size:11px;font-weight:900;">Tipe Barang</label>
                <select name="item_type" class="form-control">
                    <option value="">Semua</option>
                    <option value="RAW" <?= $itemType == 'RAW' ? 'selected' : '' ?>>Bahan Baku</option>
                    <option value="FG" <?= $itemType == 'FG' ? 'selected' : '' ?>>Barang Jadi / Semi FG</option>
                </select>
            </div>

            <div>
                <label style="font-size:11px;font-weight:900;">SKU</label>
                <select name="item_sku" class="form-control">
                    <option value="">Semua SKU</option>

                    <optgroup label="Bahan Baku">
                        <?php foreach($rawMaterials as $rm): ?>
                            <option value="<?= esc($rm['sku']) ?>" <?= $itemSku == $rm['sku'] ? 'selected' : '' ?>>
                                [<?= esc($rm['sku']) ?>] <?= esc($rm['item_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>

                    <optgroup label="Barang Jadi / Semi FG">
                        <?php foreach($fgItems as $fg): ?>
                            <option value="<?= esc($fg['sku']) ?>" <?= $itemSku == $fg['sku'] ? 'selected' : '' ?>>
                                [<?= esc($fg['sku']) ?>] <?= esc($fg['item_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <div>
                <label style="font-size:11px;font-weight:900;">Dari Tanggal</label>
                <input type="date" name="date_from" value="<?= esc($dateFrom) ?>" class="form-control">
            </div>

            <div>
                <label style="font-size:11px;font-weight:900;">Sampai Tanggal</label>
                <input type="date" name="date_to" value="<?= esc($dateTo) ?>" class="form-control">
            </div>

            <div>
                <button type="submit" class="btn-filter">Filter</button>
            </div>
        </div>
    </form>
</div>

<div class="card-box">
    <div style="overflow:auto;">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>SKU</th>
                    <th>Nama Barang</th>
                    <th>Mutasi</th>
                    <th>Masuk</th>
                    <th>Keluar</th>
                    <th>Saldo Akhir</th>
                    <th>HPP</th>
                    <th>Nilai</th>
                    <th>Ref</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($movements)): ?>
                    <tr>
                        <td colspan="12" style="text-align:center; padding:40px; color:var(--text-muted);">
                            Belum ada data mutasi stok.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($movements as $m): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($m['movement_date'])) ?></td>
                            <td>
                                <span class="badge-type <?= $m['item_type'] === 'RAW' ? 'b-raw' : 'b-fg' ?>">
                                    <?= esc($m['item_type']) ?>
                                </span>
                            </td>
                            <td><b><?= esc($m['item_sku']) ?></b></td>
                            <td><?= esc($m['item_name']) ?></td>
                            <td><?= esc($m['movement_type']) ?></td>
                            <td class="b-in"><?= $m['qty_in'] > 0 ? number_format($m['qty_in'], 2) . ' ' . esc($m['uom']) : '-' ?></td>
                            <td class="b-out"><?= $m['qty_out'] > 0 ? number_format($m['qty_out'], 2) . ' ' . esc($m['uom']) : '-' ?></td>
                            <td><b><?= number_format($m['balance_after'], 2) . ' ' . esc($m['uom']) ?></b></td>
                            <td>Rp <?= number_format($m['unit_cost'], 0, ',', '.') ?></td>
                            <td>Rp <?= number_format($m['total_value'], 0, ',', '.') ?></td>
                            <td><?= esc($m['reference_no']) ?></td>
                            <td><?= esc($m['notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>