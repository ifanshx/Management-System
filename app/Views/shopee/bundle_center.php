<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-card); }
    .card-title { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;}

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 10px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none;}
    .form-control:focus { border-color: #ec4899; box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.1);}

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }

    .product-select-list { max-height: 250px; overflow-y: auto; border: 1px solid var(--border-subtle); border-radius: 10px; padding: 10px; background: var(--bg-base); }
    .checkbox-item { display: flex; align-items: center; gap: 10px; padding: 10px; border-bottom: 1px solid var(--border-subtle); }
    .checkbox-item:last-child { border-bottom: none; }
    
    .btn-submit { width: 100%; background: #ec4899; color: #fff; border: none; padding: 15px; border-radius: 10px; font-size: 15px; font-weight: 800; cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 20px;}
    .btn-submit:hover { background: #db2777; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(236, 72, 153, 0.2);}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-package" style="color: #ec4899;"></i> Kombo Hemat (Bundle Deals)</h1>
        <p>Tingkatkan rata-rata belanja pembeli (AOV) dengan membuat paket bundling di toko <b><?= esc($shop['shop_name']) ?></b>.</p>
    </div>
</div>

<div class="bento-card">
    <div class="card-title"><i class="ph ph-gift"></i> Setup Promo Kombo</div>
    
    <form action="<?= base_url('/shopee/create_bundle/'.$shop['shop_id']) ?>" method="post">
        <?= csrf_field() ?>
        
        <div class="grid-2">
            <div class="form-group">
                <label>Nama Kombo (Tampil di Aplikasi)</label>
                <input type="text" name="bundle_title" class="form-control" placeholder="Cth: Paket Komplit Silencer + Leher" required maxlength="24">
            </div>
            <div class="form-group">
                <label>Batas Pembelian per Akun</label>
                <input type="number" name="purchase_limit" class="form-control" value="0" placeholder="0 = Tanpa Batas" required min="0">
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>Syarat: Harus Beli Berapa Barang?</label>
                <input type="number" name="item_count" class="form-control" placeholder="Cth: Beli 2" required min="2">
            </div>
            <div class="form-group">
                <label>Tipe Diskon</label>
                <select name="rule_type" id="ruleType" class="form-control" onchange="updatePlaceholder()">
                    <option value="2">Diskon Persentase (%)</option>
                    <option value="3">Potongan Nominal (Rp)</option>
                    <option value="1">Harga Spesial Jadi (Rp)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nilai Diskon</label>
                <input type="number" name="discount_value" id="discountValue" class="form-control" placeholder="Cth: 15 (Untuk 15%)" required min="1">
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label>Waktu Mulai</label>
                <input type="datetime-local" name="start_time" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Waktu Berakhir</label>
                <input type="datetime-local" name="end_time" class="form-control" required>
            </div>
        </div>

        <div class="form-group" style="margin-top: 10px;">
            <label>Pilih Barang yang Masuk ke Paket Kombo (Min. 2 Barang)</label>
            <div class="product-select-list">
                <?php foreach($products as $p): ?>
                <label class="checkbox-item">
                    <input type="checkbox" name="item_ids[]" value="<?= $p['item_id'] ?>" style="width: 16px; height: 16px; cursor: pointer;">
                    <div style="flex: 1;">
                        <div style="font-size: 13px; font-weight: 800; color: var(--text-main);"><?= esc($p['item_name']) ?></div>
                        <div style="font-size: 11px; color: var(--text-muted);">Rp <?= number_format($p['price'],0,',','.') ?> | Stok: <?= $p['stock'] ?></div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="btn-submit">
            <i class="ph ph-rocket-launch"></i> Terbitkan Kombo Hemat ke Shopee
        </button>
    </form>
</div>

<script>
function updatePlaceholder() {
    let type = document.getElementById('ruleType').value;
    let input = document.getElementById('discountValue');
    if(type == "2") { input.placeholder = "Cth: 15 (Untuk diskon 15%)"; }
    else if(type == "3") { input.placeholder = "Cth: 20000 (Untuk potongan Rp 20.000)"; }
    else { input.placeholder = "Cth: 150000 (Semua barang jadi harga Rp 150.000)"; }
}
</script>

<?= $this->endSection() ?>