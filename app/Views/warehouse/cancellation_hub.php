<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .alert-banner { background: rgba(239, 68, 68, 0.1); border: 1px dashed rgba(239, 68, 68, 0.4); padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; display: flex; gap: 15px; align-items: center; color: #ef4444; font-size: 13px; font-weight: 600;}
    .alert-banner i { font-size: 28px; }

    .cancel-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 20px; }
    
    .cancel-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-top: 4px solid #ef4444; border-radius: 16px; padding: 20px; box-shadow: var(--shadow-card); position: relative; overflow: hidden;}
    .cancel-card:hover { box-shadow: 0 10px 25px rgba(239, 68, 68, 0.1); }

    .c-header { display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 12px; margin-bottom: 12px;}
    .c-sn { font-family: 'Space Mono', monospace; font-size: 16px; font-weight: 900; color: var(--text-main); }
    .c-buyer { font-size: 12px; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 5px;}

    .c-reason-box { background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); padding: 10px; border-radius: 8px; margin-bottom: 15px;}
    .c-reason-title { font-size: 10px; font-weight: 800; color: #d97706; text-transform: uppercase; margin-bottom: 4px;}
    .c-reason-text { font-size: 13px; font-weight: 700; color: var(--text-main);}

    .c-items { font-size: 12px; color: var(--text-muted); max-height: 80px; overflow-y: auto; margin-bottom: 20px;}
    .c-item-row { display: flex; justify-content: space-between; margin-bottom: 6px; border-bottom: 1px solid rgba(0,0,0,0.02); padding-bottom: 4px;}

    .c-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    
    .btn-act { padding: 12px; border-radius: 10px; font-size: 13px; font-weight: 800; cursor: pointer; text-align: center; border: none; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px;}
    .btn-reject { background: #ef4444; color: #fff; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}
    .btn-reject:hover { background: #dc2626; transform: translateY(-2px);}
    
    .btn-accept { background: var(--bg-base); color: var(--text-muted); border: 1px solid var(--border-subtle);}
    .btn-accept:hover { background: #10b981; color: #fff; border-color: #10b981;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-warning-circle" style="color: #ef4444;"></i> Pusat Resolusi Batal</h1>
        <p>Tangani pembeli yang mengajukan pembatalan pesanan untuk toko <b><?= esc($shop['shop_name']) ?></b>.</p>
    </div>
</div>

<div class="alert-banner">
    <i class="ph ph-bell-ringing"></i>
    <div>
        <b>PERHATIAN GUDANG:</b> Jika paket belum Anda serahkan ke kurir, pertimbangkan untuk menyetujui pembatalan. Namun, jika resi sudah dicetak dan paket sudah di atas truk, silakan tekan <b>Tolak Pembatalan</b>.
    </div>
</div>

<?php if(empty($cancelOrders)): ?>
    <div style="background: var(--bg-surface); padding: 50px; text-align: center; border-radius: 16px; border: 1px dashed var(--border-subtle);">
        <i class="ph ph-check-circle" style="font-size: 64px; color: #10b981; margin-bottom: 15px; opacity: 0.5;"></i>
        <h3 style="color: var(--text-main); font-weight: 800;">Gudang Aman Terkendali</h3>
        <p style="color: var(--text-muted); font-size: 14px;">Saat ini tidak ada pembeli yang mengajukan pembatalan pesanan.</p>
    </div>
<?php else: ?>
    <div class="cancel-grid">
        <?php foreach($cancelOrders as $co): ?>
            <div class="cancel-card">
                <div class="c-header">
                    <div>
                        <div class="c-sn"><?= esc($co['order_sn']) ?></div>
                        <div style="font-size: 11px; color: #10b981; font-weight: 800; font-family: monospace;">Rp <?= number_format($co['total_amount'], 0, ',', '.') ?></div>
                    </div>
                    <div class="c-buyer"><i class="ph ph-user"></i> <?= esc($co['buyer_username']) ?></div>
                </div>

                <div class="c-reason-box">
                    <div class="c-reason-title">Alasan Pembatalan:</div>
                    <div class="c-reason-text">"<?= esc($co['cancel_reason'] ?? 'Tidak ada alasan spesifik') ?>"</div>
                    <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;">Dibatalkan oleh: <?= esc($co['cancel_by'] ?? 'Pembeli') ?></div>
                </div>

                <div class="c-items">
                    <div style="font-weight: 800; color: var(--text-main); margin-bottom: 5px;">Rincian Barang:</div>
                    <?php foreach($co['item_list'] as $item): ?>
                        <div class="c-item-row">
                            <span style="font-weight: 600;"><?= esc($item['item_name']) ?></span>
                            <span style="font-weight: 800; color: var(--accent-main);">x<?= $item['model_quantity_purchased'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form action="<?= base_url('/warehouse/process_cancellation') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="shop_id" value="<?= esc($shop['shop_id']) ?>">
                    <input type="hidden" name="order_sn" value="<?= esc($co['order_sn']) ?>">
                    
                    <div class="c-actions">
                        <button type="submit" name="operation" value="REJECT" class="btn-act btn-reject" onclick="return confirm('Paket sudah dikirim? Tolak pembatalan ini?')">
                            <i class="ph ph-prohibit"></i> Tolak Batal
                        </button>
                        <button type="submit" name="operation" value="ACCEPT" class="btn-act btn-accept" onclick="return confirm('Yakin ingin menyetujui pembatalan ini?')">
                            <i class="ph ph-check"></i> Setujui Batal
                        </button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>