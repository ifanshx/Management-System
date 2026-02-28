<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;}
    .page-title p { color: var(--text-muted); font-size: 13px; margin: 0; }
    
    .btn-secondary { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 13px; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px;}
    .btn-secondary:hover { background: var(--bg-base); border-color: var(--accent-main); color: var(--accent-main);}

    /* --- FINANCIAL STATS CARDS --- */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; }
    .stat-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-card); display: flex; align-items: center; gap: 20px;}
    
    .stat-icon { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; flex-shrink: 0;}
    .icon-blue { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
    .icon-red { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .icon-green { background: rgba(16, 185, 129, 0.1); color: #10b981; }

    .stat-data h4 { margin: 0 0 5px 0; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px;}
    .stat-data .amount { font-size: 24px; font-weight: 800; color: var(--text-main); font-family: 'Space Mono', monospace; letter-spacing: -1px;}

    /* --- TABLE STYLES --- */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; }
    .card-header { padding: 20px 25px; border-bottom: 1px dashed var(--border-subtle); font-weight: 800; color: var(--text-main); font-size: 15px; display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.01);}
    html.dark .card-header { background: rgba(255,255,255,0.02); }

    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 25px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 16px 25px; border-bottom: 1px solid var(--border-subtle); font-size: 13px; font-weight: 600; vertical-align: middle; font-family: 'Space Mono', monospace;}
    
    .text-gross { color: #3b82f6; }
    .text-fees { color: #ef4444; }
    .text-net { color: #10b981; font-weight: 800; font-size: 14px;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-wallet"></i> Buku Kas Shopee</h1>
        <p>Laporan pencairan dana (Escrow) dan pemotongan biaya layanan untuk toko <b><?= esc($shop['shop_name']) ?></b>.</p>
    </div>
    
    <div>
        <a href="<?= base_url('/shopee/sync_finance/'.$shop['shop_id']) ?>" class="btn-secondary" style="background: #10b981; color: white; border-color: #10b981;">
            <i class="ph ph-arrows-clockwise"></i> Tarik Dana Cair Terbaru
        </a>
        <a href="<?= base_url('/shopee') ?>" class="btn-secondary" style="margin-left: 8px;">
            <i class="ph ph-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon icon-blue"><i class="ph ph-receipt"></i></div>
        <div class="stat-data">
            <h4>Total Omzet (Kotor)</h4>
            <div class="amount">Rp <?= number_format($statGross, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon icon-red"><i class="ph ph-scissors"></i></div>
        <div class="stat-data">
            <h4>Potongan Shopee</h4>
            <div class="amount">Rp <?= number_format($statFees, 0, ',', '.') ?></div>
        </div>
    </div>
    <div class="stat-card" style="border-color: rgba(16, 185, 129, 0.4); box-shadow: 0 8px 24px rgba(16, 185, 129, 0.1);">
        <div class="stat-icon icon-green"><i class="ph ph-money"></i></div>
        <div class="stat-data">
            <h4 style="color: #10b981;">Pendapatan Bersih</h4>
            <div class="amount" style="color: #10b981;">Rp <?= number_format($statNet, 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<div class="bento-card">
    <div class="card-header">
        <i class="ph ph-list-numbers"></i> Rincian Pencairan per Pesanan
    </div>
    <div style="width: 100%; overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th style="font-family: inherit;">No. Pesanan / Tanggal</th>
                    <th style="text-align: right;">Penjualan Kotor</th>
                    <th style="text-align: right;">Biaya Admin & Layanan</th>
                    <th style="text-align: right;">Selisih Ongkir</th>
                    <th style="text-align: right; background: rgba(16, 185, 129, 0.05); color: #10b981;">Dana Cair (Net)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($finances)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 60px 20px; font-family: inherit;">
                            <i class="ph ph-empty" style="font-size: 48px; color: var(--border-subtle); display: block; margin-bottom: 10px;"></i>
                            <div style="color: var(--text-main); font-weight: 800; font-size: 15px;">Belum Ada Riwayat Pendapatan</div>
                            <p style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Klik "Tarik Dana Cair Terbaru" jika pesanan sudah berstatus Selesai.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($finances as $fin): ?>
                    <tr>
                        <td>
                            <div style="color: var(--text-main); font-weight: 800; margin-bottom: 4px;"><?= esc($fin['order_sn']) ?></div>
                            <div style="color: var(--text-muted); font-size: 11px; font-family: inherit;">
                                Pembeli: <span style="font-weight: 700;"><?= esc($fin['buyer_username']) ?></span><br>
                                <?= date('d M Y', strtotime($fin['payout_time'])) ?>
                            </div>
                        </td>
                        <td class="text-gross" style="text-align: right;">
                            Rp <?= number_format($fin['original_price'], 0, ',', '.') ?>
                        </td>
                        <td class="text-fees" style="text-align: right;">
                            - Rp <?= number_format($fin['admin_fee'], 0, ',', '.') ?>
                        </td>
                        <td style="text-align: right; color: var(--text-muted);">
                            - Rp <?= number_format($fin['shipping_fee_paid_by_seller'], 0, ',', '.') ?>
                        </td>
                        <td class="text-net" style="text-align: right; background: rgba(16, 185, 129, 0.02);">
                            Rp <?= number_format($fin['escrow_amount'], 0, ',', '.') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>