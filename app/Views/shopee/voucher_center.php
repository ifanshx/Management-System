<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; display: flex; justify-content: space-between; align-items: flex-end;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .grid-container { display: grid; grid-template-columns: 350px 1fr; gap: 20px; align-items: start;}
    @media (max-width: 1024px) { .grid-container { grid-template-columns: 1fr; } }

    /* FORM CARD */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-card); }
    .card-title { font-size: 15px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;}

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 10px; font-size: 13px; color: var(--text-main); font-weight: 600; outline: none; transition: 0.2s;}
    .form-control:focus { border-color: #f97316; box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);}

    .input-group { position: relative; display: flex; align-items: center; }
    .input-group span { position: absolute; left: 15px; color: var(--text-muted); font-size: 13px; font-weight: 700;}
    .input-group input { padding-left: 45px; }

    .btn-submit { width: 100%; background: #f97316; color: #fff; border: none; padding: 14px; border-radius: 10px; font-size: 14px; font-weight: 800; cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);}
    .btn-submit:hover { background: #ea580c; transform: translateY(-2px); }

    /* TIKET VOUCHER STYLE */
    .voucher-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; }
    
    .ticket { display: flex; background: var(--bg-base); border: 1px dashed #f97316; border-radius: 12px; overflow: hidden; position: relative; transition: 0.2s;}
    .ticket:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(249, 115, 22, 0.15);}
    
    .ticket-left { background: rgba(249, 115, 22, 0.1); padding: 15px; display: flex; flex-direction: column; justify-content: center; align-items: center; border-right: 2px dashed #f97316; width: 100px; text-align: center;}
    .ticket-left i { font-size: 32px; color: #f97316; margin-bottom: 5px;}
    .ticket-left span { font-size: 10px; font-weight: 800; color: #ea580c; text-transform: uppercase;}
    
    .ticket-right { padding: 15px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;}
    .v-code { font-family: 'Space Mono', monospace; font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 4px;}
    .v-name { font-size: 11px; color: var(--text-muted); font-weight: 600; margin-bottom: 8px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
    .v-rules { font-size: 12px; font-weight: 800; color: #10b981; }
    
    .v-footer { margin-top: 10px; display: flex; justify-content: space-between; align-items: flex-end; border-top: 1px solid var(--border-subtle); padding-top: 10px;}
    .v-date { font-size: 10px; color: var(--text-muted); font-weight: 500;}
    
    .btn-stop { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; cursor: pointer; text-decoration: none;}
    .btn-stop:hover { background: #ef4444; color: #fff;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-ticket" style="color: #f97316;"></i> Voucher Engine</h1>
        <p>Cetak kupon promosi toko <b><?= esc($shop['shop_name']) ?></b> untuk mendongkrak penjualan harian.</p>
    </div>
</div>

<div class="grid-container">
    <div class="bento-card">
        <div class="card-title"><i class="ph ph-plus-circle"></i> Buat Kupon Diskon Baru</div>
        
        <form action="<?= base_url('/shopee/create_voucher/'.$shop['shop_id']) ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Nama Voucher (Internal)</label>
                <input type="text" name="voucher_name" class="form-control" placeholder="Cth: Diskon Gajian Knalpot" required maxlength="100">
            </div>

            <div class="form-group">
                <label>Kode Voucher (Tampil ke Pembeli)</label>
                <input type="text" name="voucher_code" class="form-control" placeholder="Cth: NORICGAJIAN (Maks 11 Karakter)" required maxlength="11" style="text-transform: uppercase; font-family: monospace; font-weight: 800; color: #f97316;">
            </div>

            <div class="form-group">
                <label>Nominal Potongan Harga</label>
                <div class="input-group">
                    <span>Rp</span>
                    <input type="number" name="discount_amount" class="form-control" placeholder="50000" required min="1000">
                </div>
            </div>

            <div class="form-group">
                <label>Minimal Belanja</label>
                <div class="input-group">
                    <span>Rp</span>
                    <input type="number" name="min_basket_price" class="form-control" placeholder="150000" required min="0">
                </div>
            </div>

            <div class="form-group">
                <label>Batas Kuota Pemakaian (Orang)</label>
                <input type="number" name="usage_quantity" class="form-control" placeholder="Cth: 100" required min="1">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <div class="form-group">
                    <label>Berlaku Mulai</label>
                    <input type="datetime-local" name="start_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Berakhir Pada</label>
                    <input type="datetime-local" name="end_time" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="ph ph-magic-wand"></i> Terbitkan ke Shopee
            </button>
        </form>
    </div>

    <div>
        <div class="bento-card" style="margin-bottom: 20px;">
            <div class="card-title" style="color: #10b981; border-color: rgba(16, 185, 129, 0.2);"><i class="ph ph-broadcast"></i> Sedang Berjalan (Bisa Diklaim)</div>
            
            <?php if(empty($ongoingVouchers)): ?>
                <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">Tidak ada voucher yang sedang aktif saat ini.</div>
            <?php else: ?>
                <div class="voucher-grid">
                    <?php foreach($ongoingVouchers as $v): ?>
                        <div class="ticket">
                            <div class="ticket-left">
                                <i class="ph ph-ticket"></i>
                                <span>Toko</span>
                            </div>
                            <div class="ticket-right">
                                <div>
                                    <div class="v-code"><?= esc($v['voucher_code']) ?></div>
                                    <div class="v-name"><?= esc($v['voucher_name']) ?></div>
                                    <div class="v-rules">Diskon Rp <?= number_format($v['reward_info']['discount_amount'] ?? 0, 0, ',', '.') ?></div>
                                    <div style="font-size: 10px; color: var(--text-muted);">Min. Belanja: Rp <?= number_format($v['min_basket_price'] ?? 0, 0, ',', '.') ?></div>
                                </div>
                                <div class="v-footer">
                                    <div class="v-date">S/d: <?= date('d M Y, H:i', $v['end_time']) ?></div>
                                    <a href="<?= base_url('/shopee/end_voucher/' . $shop['shop_id'] . '/' . $v['voucher_id']) ?>" onclick="return confirm('Yakin ingin menyetop voucher ini?')" class="btn-stop">Hentikan</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bento-card">
            <div class="card-title" style="color: #3b82f6; border-color: rgba(59, 130, 246, 0.2);"><i class="ph ph-calendar-blank"></i> Terjadwal (Akan Datang)</div>
            
            <?php if(empty($upcomingVouchers)): ?>
                <div style="padding: 20px; text-align: center; color: var(--text-muted); font-size: 13px;">Belum ada voucher yang dijadwalkkan.</div>
            <?php else: ?>
                <div class="voucher-grid">
                    <?php foreach($upcomingVouchers as $v): ?>
                        <div class="ticket" style="border-color: #3b82f6; opacity: 0.8;">
                            <div class="ticket-left" style="background: rgba(59, 130, 246, 0.1); border-color: #3b82f6;">
                                <i class="ph ph-clock-countdown" style="color: #3b82f6;"></i>
                                <span style="color: #2563eb;">Menunggu</span>
                            </div>
                            <div class="ticket-right">
                                <div>
                                    <div class="v-code"><?= esc($v['voucher_code']) ?></div>
                                    <div class="v-name"><?= esc($v['voucher_name']) ?></div>
                                </div>
                                <div class="v-footer">
                                    <div class="v-date">Aktif: <?= date('d M Y, H:i', $v['start_time']) ?></div>
                                    <a href="<?= base_url('/shopee/end_voucher/' . $shop['shop_id'] . '/' . $v['voucher_id']) ?>" onclick="return confirm('Hapus jadwal ini?')" class="btn-stop">Batalkan</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>