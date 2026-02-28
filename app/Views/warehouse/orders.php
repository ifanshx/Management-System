<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- HEADER --- */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px;}
    .page-title p { color: var(--text-muted); font-size: 13px; margin: 0; }
    
    /* --- KANBAN / CARD GRID --- */
    .order-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 20px; align-items: start; }
    
    /* --- ORDER CARD DESIGN --- */
    .order-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; box-shadow: var(--shadow-card); overflow: hidden; transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column;}
    .order-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.08); border-color: var(--accent-light); }
    
    .card-header { padding: 15px 20px; border-bottom: 1px dashed var(--border-subtle); display: flex; justify-content: space-between; align-items: flex-start; background: rgba(0,0,0,0.01); }
    html.dark .card-header { background: rgba(255,255,255,0.02); }
    
    .order-sn { font-family: 'Space Mono', monospace; font-size: 16px; font-weight: 800; color: var(--accent-main); margin-bottom: 4px; }
    .order-date { font-size: 11px; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 4px;}
    
    .courier-badge { background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; color: var(--text-main); text-transform: uppercase; display: flex; align-items: center; gap: 4px;}
    
    /* --- BUYER INFO --- */
    .buyer-info { padding: 15px 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid var(--border-subtle); }
    .buyer-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--border-subtle); color: var(--text-main); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; }
    .buyer-name { font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 2px;}
    .payment-method { font-size: 11px; color: var(--text-muted); display: inline-block; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 2px 6px; border-radius: 4px; font-weight: 700;}
    
    /* --- ITEM LIST (BAGIAN PALING PENTING UNTUK GUDANG) --- */
    .item-list { padding: 0; margin: 0; list-style: none; flex-grow: 1;}
    .item-row { padding: 15px 20px; display: flex; gap: 15px; border-bottom: 1px solid var(--border-subtle); align-items: flex-start;}
    .item-row:last-child { border-bottom: none; }
    
    .qty-box { background: rgba(249, 115, 22, 0.1); border: 1px solid rgba(249, 115, 22, 0.2); color: #ea580c; min-width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; flex-shrink: 0;}
    .item-detail h4 { margin: 0 0 4px 0; font-size: 13px; font-weight: 800; color: var(--text-main); line-height: 1.4;}
    .item-var { font-size: 11px; color: #6366f1; background: rgba(99, 102, 241, 0.1); padding: 2px 8px; border-radius: 4px; display: inline-block; font-weight: 700; border: 1px dashed rgba(99, 102, 241, 0.3);}
    
    /* --- CARD FOOTER (ACTIONS) --- */
    .card-footer { padding: 15px 20px; background: var(--bg-base); display: flex; gap: 10px; border-top: 1px solid var(--border-subtle);}
    .btn-action { flex: 1; padding: 10px 0; border-radius: 8px; font-size: 13px; font-weight: 700; border: none; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 6px; transition: 0.2s; text-decoration: none;}
    .btn-print { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); }
    .btn-print:hover { background: var(--border-subtle); }
    .btn-pack { background: #10b981; color: #fff; }
    .btn-pack:hover { background: #059669; }

    /* Empty State */
    .empty-state { grid-column: 1 / -1; text-align: center; padding: 80px 20px; background: var(--bg-surface); border-radius: 20px; border: 1px dashed var(--border-subtle); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-package" style="color: #ea580c;"></i> Antrean Packing Gudang</h1>
        <p>Daftar pesanan dari Shopee yang sudah dibayar dan menunggu proses pengemasan (First In First Out).</p>
    </div>
    
    <div>
        <a href="<?= base_url('/shopee') ?>" style="background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
            <i class="ph ph-arrows-clockwise"></i> Tarik Data Terbaru
        </a>
    </div>
</div>

<div class="order-grid">
    <?php if(empty($orders)): ?>
        <div class="empty-state">
            <i class="ph ph-check-circle" style="font-size: 64px; color: #10b981; margin-bottom: 15px; display: block;"></i>
            <h3 style="color: var(--text-main); font-weight: 800; font-size: 18px; margin-bottom: 5px;">Gudang Bersih!</h3>
            <p style="color: var(--text-muted); font-size: 14px;">Tidak ada antrean pesanan yang perlu dipacking saat ini.</p>
        </div>
    <?php else: ?>
        <?php foreach($orders as $order): ?>
            <div class="order-card">
                
                <div class="card-header">
                    <div>
                        <div class="order-sn"><?= esc($order['order_sn']) ?></div>
                        <div class="order-date"><i class="ph ph-clock"></i> Masuk: <?= date('d M Y, H:i', strtotime($order['order_date'])) ?></div>
                    </div>
                    <div class="courier-badge">
                        <i class="ph ph-truck"></i> <?= esc($order['shipping_carrier']) ?>
                    </div>
                </div>

                <div class="buyer-info">
                    <div class="buyer-avatar">
                        <i class="ph ph-user"></i>
                    </div>
                    <div>
                        <div class="buyer-name"><?= esc($order['buyer_username']) ?></div>
                        <div class="payment-method"><i class="ph ph-wallet"></i> <?= esc($order['payment_method']) ?></div>
                    </div>
                </div>

                <ul class="item-list">
                    <?php if(!empty($order['items'])): ?>
                        <?php foreach($order['items'] as $item): ?>
                        <li class="item-row">
                            <div class="qty-box">x<?= $item['model_qty'] ?></div>
                            <div class="item-detail">
                                <h4><?= esc($item['item_name']) ?></h4>
                                <?php if(!empty($item['variation_name'])): ?>
                                    <div class="item-var">Tipe: <?= esc($item['variation_name']) ?></div>
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="item-row" style="color: var(--text-muted); font-size: 12px; font-style: italic;">Gagal memuat rincian barang.</li>
                    <?php endif; ?>
                </ul>

                <div class="card-footer">
                    <a href="<?= base_url('/warehouse/print_shopee_awb/' . $order['order_sn']) ?>" target="_blank" class="btn-action btn-print">
                        <i class="ph ph-printer"></i> Cetak Resi
                    </a>
                    <a href="<?= base_url('/warehouse/ship_shopee_order/' . $order['order_sn']) ?>" onclick="return confirm('Tandai pesanan ini selesai di-packing dan minta kurir menjemput?')" class="btn-action btn-pack">
                        <i class="ph ph-check-square-offset"></i> Selesai Packing
                    </a>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>