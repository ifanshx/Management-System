<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .btn-primary { background: #4f46e5; color: #fff; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); transition: 0.2s;}
    .btn-primary:hover { background: #4338ca; transform: translateY(-2px);}

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; }
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 18px 25px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 18px 25px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600; vertical-align: middle;}
    tr:hover td { background: rgba(0,0,0,0.01); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }
    
    .po-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase;}
    .bg-ordered { background: rgba(245, 158, 11, 0.1); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.2);}
    .bg-received { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}

    .btn-action { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 800; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;}
    .btn-action.receive:hover { background: #10b981; color: #fff; border-color: #10b981; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-truck" style="color: #4f46e5;"></i> Procurement & Pembelian</h1>
        <p>Kelola pemesanan bahan baku ke Supplier dan penerimaan barang gudang.</p>
    </div>
    
    <a href="<?= base_url('/procurement/create_po') ?>" class="btn-primary">
        <i class="ph ph-plus-circle"></i> Buat Purchase Order (PO)
    </a>
</div>

<div class="bento-card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Nomor PO</th>
                    <th>Supplier / Vendor</th>
                    <th>Tanggal Pesan</th>
                    <th>Total Nilai (Rp)</th>
                    <th style="text-align: center;">Status Pengiriman</th>
                    <th style="text-align: right;">Aksi Gudang</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($purchaseOrders)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 60px; color: var(--text-muted);"><i class="ph ph-receipt" style="font-size:48px; opacity:0.5; display:block; margin-bottom:10px;"></i> Belum ada riwayat pemesanan.</td></tr>
                <?php else: ?>
                    <?php foreach($purchaseOrders as $po): ?>
                        <tr>
                            <td style="font-family: monospace; font-weight: 900; color: #4f46e5; font-size: 14px;"><?= esc($po['po_number']) ?></td>
                            <td>
                                <div style="font-weight: 800; display:flex; align-items:center; gap:6px;"><i class="ph ph-buildings" style="color:var(--text-muted);"></i> <?= esc($po['supplier_name']) ?></div>
                            </td>
                            <td><?= date('d M Y', strtotime($po['po_date'])) ?></td>
                            <td style="font-weight: 900; color: var(--text-main); font-family: monospace;">
                                Rp <?= number_format($po['total_amount'], 0, ',', '.') ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($po['status'] == 'ORDERED'): ?>
                                    <span class="po-badge bg-ordered"><i class="ph ph-truck"></i> Sedang Dikirim</span>
                                <?php else: ?>
                                    <span class="po-badge bg-received"><i class="ph ph-package"></i> Telah Diterima</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if($po['status'] == 'ORDERED'): ?>
                                    <a href="<?= base_url('/procurement/receive_goods/'.$po['id']) ?>" class="btn-action receive" onclick="return confirm('Truk Supplier sudah datang? Menekan OK akan otomatis menambah stok fisik Bahan Baku di gudang.')">
                                        <i class="ph ph-check-square-offset"></i> Terima Barang
                                    </a>
                                <?php else: ?>
                                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 700;"><i class="ph ph-check-circle" style="color: #10b981;"></i> Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>