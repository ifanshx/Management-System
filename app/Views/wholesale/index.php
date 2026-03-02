<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; margin-bottom: 25px; padding: 25px;}
    
    .form-group { margin-bottom: 15px;}
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 10px; font-size: 13px; font-weight: 600; outline: none; color: var(--text-main);}
    .form-control:focus { border-color: #10b981; }

    .btn-submit { background: #10b981; color: #fff; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 800; cursor: pointer; transition: 0.2s; width: 100%;}
    .btn-submit:hover { background: #059669; transform: translateY(-2px);}

    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base);}
    td { padding: 15px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600;}

    /* Progress Bar Piutang */
    .progress-wrapper { width: 100%; background: var(--border-subtle); border-radius: 100px; height: 8px; overflow: hidden; margin-top: 5px;}
    .progress-bar { height: 100%; background: #10b981; border-radius: 100px;}
    
    .status-badge { padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 900;}
    .s-pending { background: rgba(239, 68, 68, 0.1); color: #ef4444;}
    .s-partial { background: rgba(245, 158, 11, 0.1); color: #d97706;}
    .s-paid { background: rgba(16, 185, 129, 0.1); color: #10b981;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-handshake" style="color: #10b981;"></i> B2B Wholesale & Piutang</h1>
        <p>Kelola pesanan partai besar (Grosir/Reseller) dan pantau pelunasan piutang jatuh tempo.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2.5fr; gap: 20px;">
    <div class="bento-card" style="border-top: 4px solid #10b981;">
        <div style="font-weight: 900; font-size: 15px; margin-bottom: 15px;"><i class="ph ph-plus-circle"></i> Buat Pesanan Grosir</div>
        <form action="<?= base_url('/wholesale/store_so') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Pelanggan (Reseller)</label>
                <select name="customer_id" class="form-control" required>
                    <?php foreach($customers as $c): ?><option value="<?= $c['id'] ?>"><?= esc($c['company_name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Barang Jadi (Knalpot)</label>
                <select name="fg_sku" class="form-control" required>
                    <?php foreach($products as $p): ?><option value="<?= $p['sku'] ?>">[<?= $p['physical_stock'] ?> Pcs] <?= esc($p['item_name']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 10px;">
                <div class="form-group"><label>Jumlah (Qty)</label><input type="number" name="qty" class="form-control" required></div>
                <div class="form-group"><label>Harga Grosir (Rp)</label><input type="number" name="unit_price" class="form-control" required></div>
            </div>
            <div class="form-group">
                <label>Uang Muka / DP (Rp) - Isi 0 jika Bon</label>
                <input type="number" name="dp_amount" class="form-control" value="0" required>
            </div>
            <div class="form-group">
                <label>Jatuh Tempo Pembayaran</label>
                <input type="date" name="due_date" class="form-control" required>
            </div>
            <button type="submit" class="btn-submit">Terbitkan Sales Order</button>
        </form>
    </div>

    <div class="bento-card">
        <div style="font-weight: 900; font-size: 15px; margin-bottom: 15px;"><i class="ph ph-list-dashes"></i> Daftar Piutang & Pesanan</div>
        <div style="overflow-x: auto;">
            <table>
                <thead><tr><th>No. SO</th><th>Reseller</th><th>Jatuh Tempo</th><th>Progres Pelunasan</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach($salesOrders as $so): 
                        $percent = ($so['total_amount'] > 0) ? ($so['paid_amount'] / $so['total_amount']) * 100 : 0;
                    ?>
                    <tr>
                        <td style="font-family: monospace; font-weight: 800; color: #10b981;"><?= esc($so['so_number']) ?></td>
                        <td><?= esc($so['company_name']) ?></td>
                        <td style="color: <?= (strtotime($so['due_date']) < time() && $so['status'] != 'PAID') ? '#ef4444' : 'inherit' ?>; font-weight:700;">
                            <?= date('d M Y', strtotime($so['due_date'])) ?>
                        </td>
                        <td>
                            <div style="display: flex; justify-content: space-between; font-size: 10px; font-weight: 800;">
                                <span>Rp <?= number_format($so['paid_amount'],0,',','.') ?></span>
                                <span>Rp <?= number_format($so['total_amount'],0,',','.') ?></span>
                            </div>
                            <div class="progress-wrapper"><div class="progress-bar" style="width: <?= $percent ?>%;"></div></div>
                            <div class="status-badge s-<?= strtolower($so['status']) ?>" style="margin-top: 5px; display: inline-block;"><?= $so['status'] ?></div>
                        </td>
                        <td>
                            <?php if($so['status'] != 'PAID'): ?>
                            <form action="<?= base_url('/wholesale/pay_installment/'.$so['id']) ?>" method="post" style="display:flex; gap:5px;">
                                <?= csrf_field() ?>
                                <input type="number" name="amount" class="form-control" style="padding: 6px; width: 100px; font-size: 11px;" placeholder="Nominal" required>
                                <button type="submit" class="btn-submit" style="padding: 6px 10px; width: auto; font-size: 11px;">Bayar</button>
                            </form>
                            <?php else: ?>
                                <span style="color: #10b981; font-weight: 800; font-size: 12px;"><i class="ph ph-check-circle"></i> LUNAS</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>