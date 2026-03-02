<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const bgColor = isDark ? '#18181b' : '#ffffff';
        const textColor = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({
                icon: 'success', title: 'Berhasil!', text: '<?= session()->getFlashdata('success') ?>',
                confirmButtonColor: '#f97316', background: bgColor, color: textColor,
            });
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({
                icon: 'error', title: 'Gagal', text: '<?= session()->getFlashdata('error') ?>',
                confirmButtonColor: '#ef4444', background: bgColor, color: textColor,
            });
        <?php endif; ?>
    });
</script>

<style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 6px; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;}
    .page-title p { color: var(--text-muted); font-size: 13px; margin: 0; }
    
    /* Tombol Utama Connect */
    .btn-shopee { background: #f97316; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); text-decoration: none; font-size: 14px;}
    .btn-shopee:hover { transform: translateY(-2px); background: #ea580c; box-shadow: 0 6px 15px rgba(249, 115, 22, 0.4);}
    
    /* --- SHOP CARD LAYOUT (BENTO STYLE) --- */
    .shop-grid { display: grid; grid-template-columns: 1fr; gap: 25px; }
    
    .shop-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; transition: 0.3s;}
    .shop-card:hover { border-color: #f97316; box-shadow: 0 10px 25px rgba(249, 115, 22, 0.1); }
    
    /* Header Card Toko */
    .shop-header { padding: 20px 25px; border-bottom: 1px dashed var(--border-subtle); background: var(--bg-base); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;}
    .shop-identity { display: flex; align-items: center; gap: 15px; }
    .shop-icon { width: 48px; height: 48px; background: rgba(249, 115, 22, 0.1); color: #f97316; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid rgba(249, 115, 22, 0.2);}
    .shop-name { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 2px;}
    .shop-id { font-family: 'Space Mono', monospace; font-size: 12px; color: var(--text-muted); font-weight: 700;}
    
    /* Meta & Status Info */
    .shop-meta { display: flex; align-items: center; gap: 20px; font-size: 12px; color: var(--text-muted); font-weight: 600;}
    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 5px; text-transform: uppercase;}
    .status-active { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .status-expired { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

    /* Body Card Toko (Menu Kategori) */
    .shop-body { padding: 25px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;}
    @media (max-width: 1024px) { .shop-body { grid-template-columns: 1fr; } }

    .menu-group { background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 15px; }
    .group-title { font-size: 12px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 15px; display: flex; align-items: center; gap: 8px;}
    
    .menu-list { display: flex; flex-direction: column; gap: 8px; }
    .btn-menu { width: 100%; display: flex; align-items: center; gap: 10px; padding: 10px 15px; border-radius: 10px; font-size: 13px; font-weight: 700; text-decoration: none; color: var(--text-main); border: 1px solid transparent; transition: 0.2s;}
    .btn-menu i { font-size: 18px; }
    
    /* Warna Kategori Menu */
    .btn-menu.op:hover { background: #fff; border-color: #3b82f6; color: #2563eb; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.1);}
    html.dark .btn-menu.op:hover { background: var(--bg-surface); color: #60a5fa;}

    .btn-menu.mkt:hover { background: #fff; border-color: #ec4899; color: #db2777; box-shadow: 0 4px 10px rgba(236, 72, 153, 0.1);}
    html.dark .btn-menu.mkt:hover { background: var(--bg-surface); color: #f472b6;}

    .btn-menu.cs:hover { background: #fff; border-color: #f59e0b; color: #d97706; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.1);}
    html.dark .btn-menu.cs:hover { background: var(--bg-surface); color: #fbbf24;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-shopping-bag-open" style="color: #f97316;"></i> Omnichannel Command Center</h1>
        <p>Pusat kendali operasional, pemasaran, dan layanan pelanggan untuk seluruh cabang toko Anda.</p>
    </div>
    
    <div>
        <a href="<?= esc($auth_url) ?>" class="btn-shopee">
            <i class="ph ph-plugs-connected" style="font-size: 18px;"></i> Hubungkan Toko Shopee
        </a>
    </div>
</div>

<div class="shop-grid">
    <?php if(empty($shops)): ?>
        <div class="shop-card" style="text-align:center; padding: 80px 20px; border-style: dashed;">
            <i class="ph ph-plugs" style="font-size: 56px; color: var(--border-subtle); display: block; margin-bottom: 15px;"></i>
            <div style="color: var(--text-main); font-weight: 800; font-size: 18px;">Belum Ada Toko Terhubung</div>
            <p style="color: var(--text-muted); font-size: 14px; font-weight: 500; margin-top: 5px;">Klik tombol "Hubungkan Toko Shopee" di pojok kanan atas untuk memulai sinkronisasi.</p>
        </div>
    <?php else: ?>
        <?php foreach($shops as $shop): ?>
            <div class="shop-card">
                
                <div class="shop-header">
                    <div class="shop-identity">
                        <div class="shop-icon"><i class="ph ph-storefront"></i></div>
                        <div>
                            <div class="shop-name"><?= esc($shop['shop_name']) ?></div>
                            <div class="shop-id">Platform ID: <?= esc($shop['shop_id']) ?></div>
                        </div>
                    </div>
                    
                    <div class="shop-meta">
                        <div>
                            <i class="ph ph-clock-counter-clockwise"></i> Sync: <?= date('d M Y, H:i', strtotime($shop['updated_at'])) ?>
                        </div>
                        <?php if($shop['status'] == 'Active' && time() < $shop['expire_at']): ?>
                            <span class="status-badge status-active"><i class="ph ph-check-circle"></i> Otorisasi Aktif</span>
                        <?php else: ?>
                            <span class="status-badge status-expired" title="Batas: <?= date('d M Y', $shop['expire_at']) ?>"><i class="ph ph-warning"></i> Kedaluwarsa</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="shop-body">
                    
                    <div class="menu-group">
                        <div class="group-title" style="color: #3b82f6;"><i class="ph ph-briefcase"></i> Operasional & Katalog</div>
                        <div class="menu-list">
                            <a href="<?= base_url('/shopee/sync_orders/' . $shop['shop_id']) ?>" class="btn-menu op">
                                <i class="ph ph-download-simple" style="color: #3b82f6;"></i> Tarik Pesanan Baru
                            </a>
                            <a href="<?= base_url('/shopee/products/' . $shop['shop_id']) ?>" class="btn-menu op">
                                <i class="ph ph-package" style="color: #3b82f6;"></i> Manajemen Katalog & Stok
                            </a>
                            <a href="<?= base_url('/shopee/mass_price/' . $shop['shop_id']) ?>" class="btn-menu op">
                                <i class="ph ph-currency-circle-dollar" style="color: #3b82f6;"></i> Update Harga Massal
                            </a>
                            <a href="<?= base_url('/shopee/finances/' . $shop['shop_id']) ?>" class="btn-menu op">
                                <i class="ph ph-wallet" style="color: #3b82f6;"></i> Rekonsiliasi Keuangan
                            </a>
                        </div>
                    </div>

                    <div class="menu-group">
                        <div class="group-title" style="color: #ec4899;"><i class="ph ph-megaphone"></i> Pusat Pemasaran</div>
                        <div class="menu-list">
                            <a href="<?= base_url('/shopee/boost/' . $shop['shop_id']) ?>" class="btn-menu mkt">
                                <i class="ph ph-rocket-launch" style="color: #ec4899;"></i> Auto-Boost Produk (Trafik)
                            </a>
                            <a href="<?= base_url('/marketing/shopee_discount/' . $shop['shop_id']) ?>" class="btn-menu mkt">
                                <i class="ph ph-tag" style="color: #ec4899;"></i> Harga Coret (Diskon)
                            </a>
                            <a href="<?= base_url('/shopee/bundle/' . $shop['shop_id']) ?>" class="btn-menu mkt">
                                <i class="ph ph-gift" style="color: #ec4899;"></i> Kombo Hemat (Bundle)
                            </a>
                            <a href="<?= base_url('/shopee/addon/' . $shop['shop_id']) ?>" class="btn-menu mkt">
                                <i class="ph ph-plugs-connected" style="color: #ec4899;"></i> Promo Tebus Murah (Add-on)
                            </a>
                        </div>
                    </div>

                    <div class="menu-group">
                        <div class="group-title" style="color: #f59e0b;"><i class="ph ph-headset"></i> Layanan & Resolusi</div>
                        <div class="menu-list">
                            <a href="<?= base_url('/customerservice/inbox/' . $shop['shop_id']) ?>" class="btn-menu cs">
                                <i class="ph ph-chats-circle" style="color: #f59e0b;"></i> Customer Service (Chat)
                            </a>
                            <a href="<?= base_url('/shopee/reviews/' . $shop['shop_id']) ?>" class="btn-menu cs">
                                <i class="ph ph-star" style="color: #f59e0b;"></i> Manajemen Ulasan Pembeli
                            </a>
                            <a href="<?= base_url('/shopee/sync_returns/' . $shop['shop_id']) ?>" class="btn-menu cs">
                                <i class="ph ph-arrow-u-down-left" style="color: #f59e0b;"></i> Audit Barang Batal/Retur
                            </a>
                            <a href="<?= base_url('/shopee/returns/' . $shop['shop_id']) ?>" class="btn-menu cs">
                                <i class="ph ph-shield-warning" style="color: #f59e0b;"></i> Sengketa & Terima Retur
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>