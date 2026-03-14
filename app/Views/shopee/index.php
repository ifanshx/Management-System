<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER
       ========================================================= */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 35px; flex-wrap: wrap; gap: 20px; }
    
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(234, 88, 12, 0.05)); color: #f97316; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(249, 115, 22, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { color: var(--text-muted); font-size: 13px; font-weight: 500; margin: 0; }
    
    /* Tombol Utama Connect */
    .btn-shopee { background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; border: none; padding: 14px 24px; border-radius: 14px; font-weight: 900; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 8px 20px -5px rgba(249, 115, 22, 0.5); text-decoration: none; font-size: 14px;}
    .btn-shopee:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(249, 115, 22, 0.6);}
    
    /* =========================================================
       2. SHOP CARD LAYOUT (PREMIUM BENTO)
       ========================================================= */
    .shop-grid { display: grid; grid-template-columns: 1fr; gap: 30px; animation: fadeUp 0.4s ease-out;}
    @keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .shop-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); overflow: hidden; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);}
    .shop-card:hover { border-color: rgba(249, 115, 22, 0.4); box-shadow: 0 20px 40px -10px rgba(249, 115, 22, 0.12); transform: translateY(-4px);}
    
    /* Header Card Toko */
    .shop-header { padding: 25px 30px; border-bottom: 2px dashed var(--border-subtle); background: rgba(0,0,0,0.01); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;}
    html.dark .shop-header { background: rgba(255,255,255,0.01); }
    
    .shop-identity { display: flex; align-items: center; gap: 15px; }
    .shop-icon { width: 56px; height: 56px; background: rgba(249, 115, 22, 0.1); color: #f97316; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; border: 1px dashed rgba(249, 115, 22, 0.3);}
    .shop-name { font-size: 20px; font-weight: 900; color: var(--text-main); margin-bottom: 4px; letter-spacing: -0.5px;}
    .shop-id { font-family: 'Space Mono', monospace; font-size: 12px; color: var(--text-muted); font-weight: 800;}
    
    /* Meta & Status Info */
    .shop-meta { display: flex; align-items: center; gap: 20px; font-size: 12px; color: var(--text-muted); font-weight: 700;}
    .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    .status-active { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .status-expired { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); animation: pulseDanger 2s infinite;}
    @keyframes pulseDanger { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); } 70% { box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }

    /* =========================================================
       3. BODY CARD (MENU KATEGORI)
       ========================================================= */
    .shop-body { padding: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;}

    .menu-group { background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 20px; transition: 0.3s;}
    .menu-group:hover { background: var(--bg-surface); border-color: rgba(0,0,0,0.1); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.03);}
    html.dark .menu-group:hover { border-color: rgba(255,255,255,0.1); }

    .group-title { font-size: 12px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;}
    
    .menu-list { display: flex; flex-direction: column; gap: 8px; }
    .btn-menu { width: 100%; display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 14px; font-size: 13px; font-weight: 700; text-decoration: none; color: var(--text-main); border: 1px solid transparent; transition: all 0.2s ease;}
    .btn-menu i { font-size: 20px; transition: transform 0.2s;}
    
    /* Warna Kategori Menu yang Elegan */
    .btn-menu.op:hover { background: #fff; border-color: #3b82f6; color: #2563eb; box-shadow: 0 6px 15px rgba(59, 130, 246, 0.15); padding-left: 20px;}
    .btn-menu.op:hover i { transform: scale(1.1); color: #2563eb !important;}
    html.dark .btn-menu.op:hover { background: var(--bg-surface); color: #60a5fa;}

    .btn-menu.mkt:hover { background: #fff; border-color: #ec4899; color: #db2777; box-shadow: 0 6px 15px rgba(236, 72, 153, 0.15); padding-left: 20px;}
    .btn-menu.mkt:hover i { transform: scale(1.1); color: #db2777 !important;}
    html.dark .btn-menu.mkt:hover { background: var(--bg-surface); color: #f472b6;}

    .btn-menu.cs:hover { background: #fff; border-color: #f59e0b; color: #d97706; box-shadow: 0 6px 15px rgba(245, 158, 11, 0.15); padding-left: 20px;}
    .btn-menu.cs:hover i { transform: scale(1.1); color: #d97706 !important;}
    html.dark .btn-menu.cs:hover { background: var(--bg-surface); color: #fbbf24;}

    /* Empty State */
    .empty-state { text-align: center; padding: 100px 20px; border: 2px dashed var(--border-subtle); border-radius: 24px; background: var(--bg-base);}
    .empty-state i { font-size: 72px; color: var(--border-subtle); display: block; margin-bottom: 20px; }
    .empty-state h3 { color: var(--text-main); font-weight: 900; font-size: 20px; margin-bottom: 8px;}
    .empty-state p { color: var(--text-muted); font-size: 14px; font-weight: 600; margin: 0;}
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-shopping-bag-open"></i></div>
        <div>
            <h1>Omnichannel Command Center</h1>
            <p>Pusat kendali operasional, pemasaran, dan layanan pelanggan untuk seluruh cabang toko online.</p>
        </div>
    </div>
    
    <div>
        <a href="<?= esc($auth_url) ?>" class="btn-shopee">
            <i class="ph-bold ph-plugs-connected" style="font-size: 20px;"></i> Hubungkan Toko Shopee Baru
        </a>
    </div>
</div>

<div class="shop-grid">
    <?php if(empty($shops)): ?>
        <div class="empty-state">
            <i class="ph-fill ph-plugs"></i>
            <h3>Belum Ada Toko Terhubung</h3>
            <p>Klik tombol "Hubungkan Toko Shopee Baru" di pojok kanan atas untuk memulai sinkronisasi dengan platform e-commerce Anda.</p>
        </div>
    <?php else: ?>
        <?php foreach($shops as $shop): ?>
            <div class="shop-card">
                
                <div class="shop-header">
                    <div class="shop-identity">
                        <div class="shop-icon"><i class="ph-fill ph-storefront"></i></div>
                        <div>
                            <div class="shop-name"><?= esc($shop['shop_name']) ?></div>
                            <div class="shop-id">Platform ID: <?= esc($shop['shop_id']) ?></div>
                        </div>
                    </div>
                    
                    <div class="shop-meta">
                        <div style="background: var(--bg-surface); padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-subtle); box-shadow: 0 2px 5px rgba(0,0,0,0.02);">
                            <i class="ph-bold ph-clock-counter-clockwise"></i> Terakhir Sync: <?= date('d M Y, H:i', strtotime($shop['updated_at'])) ?>
                        </div>
                        <?php if($shop['status'] == 'Active' && time() < $shop['expire_at']): ?>
                            <span class="status-badge status-active"><i class="ph-bold ph-check-circle" style="font-size: 14px;"></i> Otorisasi Aktif</span>
                        <?php else: ?>
                            <span class="status-badge status-expired" title="Batas: <?= date('d M Y', $shop['expire_at']) ?>"><i class="ph-bold ph-warning" style="font-size: 14px;"></i> Kedaluwarsa</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="shop-body">
                    
                    <div class="menu-group">
                        <div class="group-title" style="color: #3b82f6;"><i class="ph-fill ph-briefcase" style="font-size: 16px;"></i> Operasional & Katalog</div>
                        <div class="menu-list">
                            <a href="<?= base_url('/shopee/sync_orders/' . $shop['shop_id']) ?>" class="btn-menu op">
                                <i class="ph-bold ph-download-simple" style="color: #3b82f6;"></i> Tarik Pesanan Baru
                            </a>
                            <a href="<?= base_url('/shopee/products/' . $shop['shop_id']) ?>" class="btn-menu op">
                                <i class="ph-bold ph-package" style="color: #3b82f6;"></i> Manajemen Katalog & Stok
                            </a>
                            <a href="<?= base_url('/shopee/mass_price/' . $shop['shop_id']) ?>" class="btn-menu op">
                                <i class="ph-bold ph-currency-circle-dollar" style="color: #3b82f6;"></i> Update Harga Massal
                            </a>
                            <a href="<?= base_url('/shopee/finances/' . $shop['shop_id']) ?>" class="btn-menu op">
                                <i class="ph-bold ph-wallet" style="color: #3b82f6;"></i> Rekonsiliasi Keuangan
                            </a>
                        </div>
                    </div>

                    <div class="menu-group">
                        <div class="group-title" style="color: #ec4899;"><i class="ph-fill ph-megaphone" style="font-size: 16px;"></i> Pusat Pemasaran</div>
                        <div class="menu-list">
                            <a href="<?= base_url('/shopee/boost/' . $shop['shop_id']) ?>" class="btn-menu mkt">
                                <i class="ph-bold ph-rocket-launch" style="color: #ec4899;"></i> Auto-Boost Produk (Trafik)
                            </a>
                            <a href="<?= base_url('/marketing/shopee_discount/' . $shop['shop_id']) ?>" class="btn-menu mkt">
                                <i class="ph-bold ph-tag" style="color: #ec4899;"></i> Harga Coret (Diskon)
                            </a>
                            <a href="<?= base_url('/shopee/bundle/' . $shop['shop_id']) ?>" class="btn-menu mkt">
                                <i class="ph-bold ph-gift" style="color: #ec4899;"></i> Kombo Hemat (Bundle)
                            </a>
                            <a href="<?= base_url('/shopee/addon/' . $shop['shop_id']) ?>" class="btn-menu mkt">
                                <i class="ph-bold ph-plugs-connected" style="color: #ec4899;"></i> Promo Tebus Murah (Add-on)
                            </a>
                        </div>
                    </div>

                    <div class="menu-group">
                        <div class="group-title" style="color: #f59e0b;"><i class="ph-fill ph-headset" style="font-size: 16px;"></i> Layanan & Resolusi</div>
                        <div class="menu-list">
                            <a href="<?= base_url('/customerservice/inbox/' . $shop['shop_id']) ?>" class="btn-menu cs">
                                <i class="ph-bold ph-chats-circle" style="color: #f59e0b;"></i> Customer Service (Chat)
                            </a>
                            <a href="<?= base_url('/shopee/reviews/' . $shop['shop_id']) ?>" class="btn-menu cs">
                                <i class="ph-bold ph-star" style="color: #f59e0b;"></i> Manajemen Ulasan Pembeli
                            </a>
                            <a href="<?= base_url('/shopee/sync_returns/' . $shop['shop_id']) ?>" class="btn-menu cs">
                                <i class="ph-bold ph-arrow-u-down-left" style="color: #f59e0b;"></i> Audit Barang Batal/Retur
                            </a>
                            <a href="<?= base_url('/shopee/returns/' . $shop['shop_id']) ?>" class="btn-menu cs">
                                <i class="ph-bold ph-shield-warning" style="color: #f59e0b;"></i> Sengketa & Terima Retur
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>