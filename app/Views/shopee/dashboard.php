<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    .page-title p { color: var(--text-muted); font-size: 13px; margin: 0; }

    /* BENTO GRID LAYOUT UTAMA */
    .dashboard-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 20px;}
    @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .dashboard-grid { grid-template-columns: 1fr; } }

    /* METRIC CARDS */
    .metric-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 20px; box-shadow: var(--shadow-card); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: center;}
    .metric-icon { position: absolute; top: 20px; right: 20px; font-size: 32px; opacity: 0.2; }
    
    .metric-title { font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; z-index: 1;}
    .metric-value { font-size: 28px; font-weight: 900; color: var(--text-main); letter-spacing: -1px; z-index: 1;}
    
    /* Varian Warna Khusus */
    .card-warning { border-bottom: 4px solid #f59e0b; }
    .card-warning .metric-icon { color: #f59e0b; opacity: 0.5;}
    .card-success { border-bottom: 4px solid #10b981; }
    .card-success .metric-icon { color: #10b981; opacity: 0.5;}
    .card-primary { background: var(--accent-main); color: #fff; border: none;}
    .card-primary .metric-title, .card-primary .metric-value { color: #fff; }
    .card-primary .metric-icon { color: #fff; opacity: 0.3;}

    /* DUAL GRID UNTUK TABEL */
    .bottom-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    @media (max-width: 1024px) { .bottom-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 20px; box-shadow: var(--shadow-card); }
    .card-header { font-size: 15px; font-weight: 800; color: var(--text-main); margin-bottom: 15px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;}

    /* LIST TOP PRODUK */
    .top-product-list { list-style: none; padding: 0; margin: 0; }
    .top-product-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--border-subtle); }
    .top-product-item:last-child { border-bottom: none; }
    .prod-info { flex: 1; }
    .prod-name { font-size: 13px; font-weight: 700; color: var(--text-main); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;}
    .prod-var { font-size: 11px; color: var(--text-muted); }
    .prod-qty { background: rgba(249, 115, 22, 0.1); color: #ea580c; font-weight: 800; font-size: 14px; padding: 4px 10px; border-radius: 8px; margin-left: 15px;}

    /* TOKO STATUS */
    .shop-list { display: flex; flex-direction: column; gap: 10px; }
    .shop-item { display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px;}
    .shop-icon { width: 36px; height: 36px; background: #f97316; color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-presentation-chart"></i> Command Center</h1>
        <p>Ringkasan performa Omnichannel (Seluruh Cabang Shopee) bulan ini.</p>
    </div>
</div>

<div class="dashboard-grid">
    <div class="metric-card card-warning">
        <i class="ph ph-package metric-icon"></i>
        <div class="metric-title">Antrean Packing (Gudang)</div>
        <div class="metric-value"><?= $pendingOrders ?> <span style="font-size:14px; font-weight:700;">Pesanan</span></div>
    </div>
    
    <div class="metric-card">
        <i class="ph ph-shopping-cart metric-icon"></i>
        <div class="metric-title">Pesanan Masuk Hari Ini</div>
        <div class="metric-value"><?= $ordersToday ?> <span style="font-size:14px; font-weight:700;">Pesanan</span></div>
    </div>
    
    <div class="metric-card">
        <i class="ph ph-chart-line-up metric-icon"></i>
        <div class="metric-title">Omzet Kotor (Bulan Ini)</div>
        <div class="metric-value" style="font-size: 22px;">Rp <?= number_format($monthlyRevenue, 0, ',', '.') ?></div>
    </div>
    
    <div class="metric-card card-success card-primary">
        <i class="ph ph-wallet metric-icon"></i>
        <div class="metric-title">Uang Cair Bersih (Bulan Ini)</div>
        <div class="metric-value" style="font-size: 22px;">Rp <?= number_format($monthlyNetIncome, 0, ',', '.') ?></div>
    </div>
</div>

<div class="bottom-grid">
    
    <div class="bento-card">
        <div class="card-header"><i class="ph ph-trophy" style="color: #f59e0b;"></i> Top 5 Produk Terlaris</div>
        <ul class="top-product-list">
            <?php if(empty($bestSellers)): ?>
                <div style="padding: 30px; text-align: center; color: var(--text-muted); font-size: 13px;">Belum ada data penjualan.</div>
            <?php else: ?>
                <?php foreach($bestSellers as $prod): ?>
                <li class="top-product-item">
                    <div class="prod-info">
                        <div class="prod-name"><?= esc($prod['item_name']) ?></div>
                        <?php if(!empty($prod['variation_name'])): ?>
                            <div class="prod-var">Varian: <?= esc($prod['variation_name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="prod-qty"><?= $prod['total_sold'] ?> Terjual</div>
                </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
    </div>

    <div class="bento-card">
        <div class="card-header"><i class="ph ph-plugs-connected" style="color: #3b82f6;"></i> Koneksi API Toko</div>
        <div class="shop-list">
            <?php if(empty($activeShops)): ?>
                 <div style="font-size: 12px; color: var(--text-muted);">Tidak ada toko terhubung.</div>
            <?php else: ?>
                <?php foreach($activeShops as $shop): ?>
                <div class="shop-item">
                    <div class="shop-icon"><i class="ph ph-storefront"></i></div>
                    <div style="flex: 1;">
                        <div style="font-size: 13px; font-weight: 800; color: var(--text-main);"><?= esc($shop['shop_name']) ?></div>
                        <div style="font-size: 10px; color: #10b981; font-weight: 700;"><i class="ph ph-check-circle"></i> Sync Berjalan</div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <a href="<?= base_url('/shopee') ?>" style="display: block; text-align: center; margin-top: 10px; font-size: 12px; font-weight: 700; color: var(--accent-main); text-decoration: none;">
                Kelola Cabang Shopee &rarr;
            </a>
        </div>
    </div>

</div>

<?= $this->endSection() ?>