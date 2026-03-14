<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
// --- FUNGSI PHP CERDAS UNTUK MENYINGKAT ANGKA BESAR ---
if (!function_exists('format_compact')) {
    function format_compact($angka) {
        $angka = (float)$angka;
        if ($angka >= 1000000000) {
            return number_format($angka / 1000000000, 2, ',', '.') . ' M'; // Miliar
        } elseif ($angka >= 1000000) {
            return number_format($angka / 1000000, 2, ',', '.') . ' Jt'; // Juta
        } else {
            return number_format($angka, 0, ',', '.'); // Ribuan
        }
    }
}
?>

<style>
    /* =========================================================
       1. PAGE HEADER & TYPOGRAPHY
       ========================================================= */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;}
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(234, 88, 12, 0.05)); color: #f97316; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(249, 115, 22, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { color: var(--text-muted); font-size: 13px; font-weight: 500; margin: 0; }

    /* =========================================================
       2. BENTO GRID SYSTEM
       ========================================================= */
    .dashboard-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 24px;}
    @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .dashboard-grid { grid-template-columns: 1fr; } }

    .bottom-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
    @media (max-width: 1024px) { .bottom-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 25px 30px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);}
    .card-header { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px;}

    /* =========================================================
       3. METRIC CARDS (SMART KPI)
       ========================================================= */
    .metric-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 25px; box-shadow: 0 10px 25px -10px rgba(0,0,0,0.05); position: relative; overflow: hidden; display: flex; flex-direction: column; justify-content: center; transition: 0.3s;}
    .metric-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px -10px rgba(0,0,0,0.08);}
    
    .metric-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;}
    .metric-icon-wrap { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
    .metric-title { font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.4;}
    
    .metric-value-compact { font-size: 32px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; letter-spacing: -1.5px; line-height: 1;}
    .metric-value-exact { font-size: 11px; font-weight: 800; color: var(--text-muted); font-family: 'Space Mono', monospace; margin-top: 8px; display: inline-flex; align-items: center; gap: 6px; background: var(--bg-base); padding: 4px 10px; border-radius: 6px; border: 1px dashed var(--border-subtle);}

    /* Varian Warna Khusus */
    .card-warning { border-bottom: 4px solid #f59e0b; }
    .card-warning .metric-icon-wrap { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff;}
    
    .card-info { border-bottom: 4px solid #3b82f6; }
    .card-info .metric-icon-wrap { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff;}

    .card-purple { border-bottom: 4px solid #8b5cf6; }
    .card-purple .metric-icon-wrap { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: #fff;}

    .card-success { background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none;}
    .card-success .metric-title, .card-success .metric-value-compact, .card-success .metric-value-exact { color: #fff; }
    .card-success .metric-value-exact { background: rgba(0,0,0,0.1); border-color: rgba(255,255,255,0.2);}
    .card-success .metric-icon-wrap { background: rgba(255,255,255,0.2); color: #fff;}

    /* =========================================================
       4. LIST TOP PRODUK (RANKING STYLE)
       ========================================================= */
    .top-product-list { display: flex; flex-direction: column; gap: 12px; }
    .top-product-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: var(--bg-base); border: 1px solid transparent; border-radius: 16px; transition: 0.2s;}
    .top-product-item:hover { background: var(--bg-surface); border-color: var(--border-subtle); transform: translateX(5px); box-shadow: 0 4px 15px rgba(0,0,0,0.03);}
    
    .rank-info-wrap { display: flex; align-items: center; gap: 15px; flex: 1;}
    
    /* Rank Badges (Medali) */
    .rank-badge { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 900; font-family: 'Space Mono', monospace; flex-shrink: 0;}
    .rank-1 { background: linear-gradient(135deg, #fcd34d, #f59e0b); color: #fff; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);}
    .rank-2 { background: linear-gradient(135deg, #e4e4e7, #a1a1aa); color: #fff; box-shadow: 0 4px 10px rgba(161, 161, 170, 0.3);}
    .rank-3 { background: linear-gradient(135deg, #fca5a5, #d946ef); color: #fff; box-shadow: 0 4px 10px rgba(217, 70, 239, 0.3);}
    .rank-other { background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-muted);}

    .prod-info { display: flex; flex-direction: column; gap: 4px; }
    .prod-name { font-size: 14px; font-weight: 900; color: var(--text-main); line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;}
    .prod-var { font-size: 11px; color: #8b5cf6; font-weight: 800; background: rgba(139, 92, 246, 0.1); padding: 2px 8px; border-radius: 6px; display: inline-block; width: fit-content;}

    .prod-qty { background: rgba(249, 115, 22, 0.1); color: #ea580c; font-weight: 900; font-size: 16px; font-family: 'Space Mono', monospace; padding: 6px 14px; border-radius: 10px; margin-left: 15px; border: 1px solid rgba(249, 115, 22, 0.2); white-space: nowrap;}
    .prod-qty span { font-size: 10px; font-family: 'Plus Jakarta Sans', sans-serif; text-transform: uppercase; font-weight: 800; color: #ea580c;}

    /* =========================================================
       5. TOKO API STATUS (ACTIVE NODE STYLE)
       ========================================================= */
    .shop-list { display: flex; flex-direction: column; gap: 15px; }
    .shop-item { display: flex; justify-content: space-between; align-items: center; padding: 20px; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 16px; transition: 0.3s;}
    .shop-item:hover { border-color: #f97316; box-shadow: 0 4px 15px rgba(249, 115, 22, 0.05);}
    
    .shop-left { display: flex; align-items: center; gap: 15px; }
    .shop-icon { width: 44px; height: 44px; background: rgba(249, 115, 22, 0.1); color: #f97316; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px dashed rgba(249, 115, 22, 0.3);}
    
    .shop-info h4 { margin: 0 0 4px 0; font-size: 14px; font-weight: 900; color: var(--text-main);}
    .shop-info p { margin: 0; font-size: 11px; font-weight: 700; color: var(--text-muted); font-family: 'Space Mono', monospace;}

    /* Glowing Dot Animation */
    .sync-status { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 6px 12px; border-radius: 8px; border: 1px solid rgba(16, 185, 129, 0.2);}
    .dot { width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; animation: pulseDot 1.5s infinite;}
    @keyframes pulseDot { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

    .btn-manage-shops { display: flex; align-items: center; justify-content: center; gap: 8px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 14px; border-radius: 14px; font-weight: 900; font-size: 13px; text-decoration: none; margin-top: 15px; transition: 0.3s; border: 1px solid rgba(59, 130, 246, 0.2);}
    .btn-manage-shops:hover { background: #3b82f6; color: #fff; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(59, 130, 246, 0.3);}
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-presentation-chart"></i></div>
        <div>
            <h1>Command Center</h1>
            <p>Ringkasan performa Omnichannel (Seluruh Cabang Shopee) bulan ini.</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="metric-card card-warning">
        <div class="metric-header">
            <div class="metric-label">Antrean Packing<br><span style="font-size:10px; font-weight:600; text-transform:none;">(Gudang Pusat)</span></div>
            <div class="metric-icon-wrap"><i class="ph-bold ph-package"></i></div>
        </div>
        <div class="metric-value-compact"><?= $pendingOrders ?></div>
        <div class="metric-value-exact"><i class="ph-fill ph-warning-circle" style="color: #f59e0b;"></i> Perlu Segera Diproses</div>
    </div>
    
    <div class="metric-card card-info">
        <div class="metric-header">
            <div class="metric-label">Pesanan Masuk<br><span style="font-size:10px; font-weight:600; text-transform:none;">(Hari Ini)</span></div>
            <div class="metric-icon-wrap"><i class="ph-bold ph-shopping-cart"></i></div>
        </div>
        <div class="metric-value-compact"><?= $ordersToday ?></div>
        <div class="metric-value-exact"><i class="ph-fill ph-clock-counter-clockwise"></i> Terakhir ditarik hari ini</div>
    </div>
    
    <div class="metric-card card-purple">
        <div class="metric-header">
            <div class="metric-label">Omzet Kotor<br><span style="font-size:10px; font-weight:600; text-transform:none;">(Bulan Ini)</span></div>
            <div class="metric-icon-wrap"><i class="ph-bold ph-chart-line-up"></i></div>
        </div>
        <div class="metric-value-compact">Rp <?= format_compact($monthlyRevenue) ?></div>
        <div class="metric-value-exact" title="Nominal Detail">
            <i class="ph-fill ph-info"></i> Rp <?= number_format($monthlyRevenue, 0, ',', '.') ?>
        </div>
    </div>
    
    <div class="metric-card card-success">
        <div class="metric-header">
            <div class="metric-label">Uang Cair Bersih<br><span style="font-size:10px; font-weight:600; text-transform:none;">(Bulan Ini)</span></div>
            <div class="metric-icon-wrap"><i class="ph-bold ph-wallet"></i></div>
        </div>
        <div class="metric-value-compact">Rp <?= format_compact($monthlyNetIncome) ?></div>
        <div class="metric-value-exact" title="Nominal Detail">
            <i class="ph-fill ph-check-circle"></i> Rp <?= number_format($monthlyNetIncome, 0, ',', '.') ?>
        </div>
    </div>
</div>

<div class="bottom-grid">
    
    <div class="bento-card">
        <div class="card-header">
            <i class="ph-fill ph-trophy" style="color: #f59e0b; font-size: 22px;"></i> Top 5 Produk Terlaris
        </div>
        <div class="top-product-list">
            <?php if(empty($bestSellers)): ?>
                <div style="padding: 40px; text-align: center; color: var(--text-muted); border: 2px dashed var(--border-subtle); border-radius: 16px;">
                    <i class="ph-fill ph-package-x" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                    <div style="font-weight: 800; font-size: 14px;">Data Penjualan Kosong</div>
                    <div style="font-size: 12px; margin-top: 4px;">Belum ada pesanan yang tersinkronisasi bulan ini.</div>
                </div>
            <?php else: ?>
                <?php $rank = 1; foreach($bestSellers as $prod): ?>
                <?php 
                    $rankClass = 'rank-other';
                    if($rank == 1) $rankClass = 'rank-1';
                    if($rank == 2) $rankClass = 'rank-2';
                    if($rank == 3) $rankClass = 'rank-3';
                ?>
                <div class="top-product-item">
                    <div class="rank-info-wrap">
                        <div class="rank-badge <?= $rankClass ?>">#<?= $rank ?></div>
                        <div class="prod-info">
                            <div class="prod-name" title="<?= esc($prod['item_name']) ?>"><?= esc($prod['item_name']) ?></div>
                            <?php if(!empty($prod['variation_name'])): ?>
                                <div class="prod-var"><i class="ph-fill ph-tag"></i> <?= esc($prod['variation_name']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="prod-qty">
                        <?= $prod['total_sold'] ?> <span>Terjual</span>
                    </div>
                </div>
                <?php $rank++; endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="bento-card">
        <div class="card-header">
            <i class="ph-fill ph-plugs-connected" style="color: #3b82f6; font-size: 22px;"></i> Koneksi API Toko
        </div>
        
        <div class="shop-list">
            <?php if(empty($activeShops)): ?>
                <div style="padding: 40px 20px; text-align: center; color: var(--text-muted); background: var(--bg-base); border-radius: 16px; border: 1px solid var(--border-subtle);">
                    <i class="ph-fill ph-storefront" style="font-size: 32px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                    <div style="font-size: 13px; font-weight: 800;">Tidak ada toko aktif.</div>
                </div>
            <?php else: ?>
                <?php foreach($activeShops as $shop): ?>
                <div class="shop-item">
                    <div class="shop-left">
                        <div class="shop-icon"><i class="ph-fill ph-storefront"></i></div>
                        <div class="shop-info">
                            <h4><?= esc($shop['shop_name']) ?></h4>
                            <p>ID: <?= esc($shop['shop_id']) ?></p>
                        </div>
                    </div>
                    <div class="sync-status" title="Koneksi Real-time Shopee API Berjalan">
                        <div class="dot"></div> Sinkronisasi
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <a href="<?= base_url('/shopee') ?>" class="btn-manage-shops">
            <i class="ph-bold ph-faders"></i> Kelola Cabang Shopee
        </a>
    </div>

</div>

<?= $this->endSection() ?>