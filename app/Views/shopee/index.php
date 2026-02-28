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
    
    /* Tombol Khas Shopee (Orange) */
    .btn-shopee { background: #f97316; color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.3s; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); text-decoration: none; font-size: 14px;}
    .btn-shopee:hover { transform: translateY(-2px); background: #ea580c; box-shadow: 0 6px 15px rgba(249, 115, 22, 0.4);}
    
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; padding: 0;}
    .card-header { padding: 20px 25px; border-bottom: 1px dashed var(--border-subtle); background: var(--bg-base); font-weight: 800; color: var(--text-main); font-size: 15px; display: flex; align-items: center; gap: 10px;}
    
    .table-responsive { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 25px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-surface); letter-spacing: 0.5px;}
    td { padding: 16px 25px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600; vertical-align: middle;}
    
    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 5px;}
    .status-active { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
    .status-expired { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

    .action-group { display: flex; gap: 8px; }
    .btn-act { padding: 8px 12px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: 0.2s; border: 1px solid transparent;}
    .btn-sync-order { background: rgba(56, 189, 248, 0.1); color: #0284c7; border-color: rgba(56, 189, 248, 0.2); }
    .btn-sync-order:hover { background: #38bdf8; color: #fff; }
    .btn-sync-stock { background: rgba(168, 85, 247, 0.1); color: #7e22ce; border-color: rgba(168, 85, 247, 0.2); }
    .btn-sync-stock:hover { background: #a855f7; color: #fff; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-shopping-bag-open" style="color: #f97316;"></i> Omnichannel Shopee</h1>
        <p>Pusat integrasi Multi-Toko untuk sinkronisasi pesanan, stok barang, dan resi pengiriman.</p>
    </div>
    
    <div>
        <a href="<?= esc($auth_url) ?>" class="btn-shopee">
            <i class="ph ph-plugs-connected" style="font-size: 18px;"></i> Hubungkan Toko Shopee
        </a>
    </div>
</div>

<div class="bento-card">
    <div class="card-header">
        <i class="ph ph-storefront"></i> Daftar Toko Shopee Terhubung
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Informasi Toko</th>
                    <th>ID Platform</th>
                    <th style="text-align: center;">Status Integrasi</th>
                    <th>Terakhir Sinkronisasi</th>
                    <th style="text-align: right;">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($shops)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 60px 20px;">
                            <i class="ph ph-plugs" style="font-size: 48px; color: var(--border-subtle); display: block; margin-bottom: 10px;"></i>
                            <div style="color: var(--text-main); font-weight: 800; font-size: 15px;">Belum Ada Toko Terhubung</div>
                            <p style="color: var(--text-muted); font-size: 13px; font-weight: 500;">Klik tombol "Hubungkan Toko Shopee" di pojok kanan atas untuk memulai.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($shops as $shop): ?>
                        <tr>
                            <td>
                                <div style="font-size: 14px; font-weight: 800; color: #f97316; margin-bottom: 4px;">
                                    <?= esc($shop['shop_name']) ?>
                                </div>
                                <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">
                                    Token Kedaluwarsa: <?= date('d M Y, H:i', $shop['expire_at']) ?>
                                </div>
                            </td>
                            <td style="font-family: monospace; font-weight: 700; color: var(--text-main);">
                                #<?= esc($shop['shop_id']) ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($shop['status'] == 'Active' && time() < $shop['expire_at']): ?>
                                    <span class="status-badge status-active"><i class="ph ph-check-circle"></i> AKTIF</span>
                                <?php else: ?>
                                    <span class="status-badge status-expired"><i class="ph ph-warning"></i> KEDALUWARSA</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                <?= date('d M Y, H:i', strtotime($shop['updated_at'])) ?>
                            </td>
                           <td style="text-align: right;">
                                <div class="action-group" style="justify-content: flex-end; gap: 6px;">
                                    
                                    <a href="<?= base_url('/customerservice/inbox/' . $shop['shop_id']) ?>" class="btn-act" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; border-color: rgba(139, 92, 246, 0.2);" title="Pusat Pesan Pelanggan (Inbox)">
                                        <i class="ph ph-chats-circle" style="font-size: 16px;"></i> CS Chat
                                    </a>

                                    <a href="<?= base_url('/shopee/sync_orders/' . $shop['shop_id']) ?>" class="btn-act btn-sync-order" title="Tarik Pesanan Baru dari Shopee">
                                        <i class="ph ph-download-simple" style="font-size: 16px;"></i> Order
                                    </a>

                                    <a href="<?= base_url('/shopee/finances/' . $shop['shop_id']) ?>" class="btn-act" style="background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2);" title="Laporan Buku Kas Shopee">
                                        <i class="ph ph-wallet" style="font-size: 16px;"></i> Keuangan
                                    </a>

                                    <a href="<?= base_url('/shopee/products/' . $shop['shop_id']) ?>" class="btn-act" style="background: var(--bg-base); color: var(--text-main); border-color: var(--border-subtle);" title="Manajemen Katalog & Sinkronisasi Stok">
                                        <i class="ph ph-package" style="font-size: 16px;"></i> Katalog
                                    </a>
                                    
                                    <a href="<?= base_url('/shopee/sync_returns/' . $shop['shop_id']) ?>" class="btn-act" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2);" title="Tarik Data Pembatalan & Retur Barang">
                                        <i class="ph ph-arrow-u-down-left" style="font-size: 16px;"></i> Audit Retur
                                    </a>

                                    <a href="<?= base_url('/marketing/shopee_discount/' . $shop['shop_id']) ?>" class="btn-act" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2);" title="Atur Harga Coret">
                                        <i class="ph ph-tag" style="font-size: 16px;"></i> Promo
                                    </a>       
                                    
                                    <a href="<?= base_url('/shopee/boost/' . $shop['shop_id']) ?>" class="btn-act" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2);" title="Naikkan Produk (Gratis Trafik)">
                                        <i class="ph ph-rocket-launch" style="font-size: 16px;"></i> Boost
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>