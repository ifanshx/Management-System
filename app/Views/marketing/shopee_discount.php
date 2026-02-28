<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .promo-container { display: grid; grid-template-columns: 1fr 350px; gap: 20px; align-items: start; }
    @media (max-width: 900px) { .promo-container { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 25px; box-shadow: var(--shadow-card); }
    .card-title { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;}

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 16px; border-radius: 12px; font-size: 14px; color: var(--text-main); font-family: inherit; font-weight: 600; transition: 0.2s;}
    .form-control:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1); outline: none;}

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

    .btn-submit { width: 100%; background: #ef4444; color: #fff; border: none; padding: 15px; border-radius: 12px; font-size: 15px; font-weight: 800; cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);}
    .btn-submit:hover { background: #dc2626; transform: translateY(-2px); }

    /* PREVIEW CARD (Simulasi Tampilan Shopee) */
    .preview-box { background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; overflow: hidden; font-family: sans-serif;}
    .preview-img { width: 100%; height: 200px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 40px;}
    .preview-body { padding: 15px; }
    .preview-tag { background: #ef4444; color: white; font-size: 10px; font-weight: bold; padding: 2px 6px; border-radius: 2px; display: inline-block; margin-bottom: 5px;}
    .preview-title { font-size: 13px; color: #1f2937; margin-bottom: 10px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;}
    .preview-price { display: flex; align-items: baseline; gap: 8px; }
    .price-strike { text-decoration: line-through; color: #9ca3af; font-size: 12px; }
    .price-new { color: #ef4444; font-size: 18px; font-weight: bold; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-tag" style="color: #ef4444;"></i> Marketing Engine</h1>
        <p>Atur Diskon & Harga Coret toko <b><?= esc($shop['shop_name']) ?></b> langsung dari sistem ERP.</p>
    </div>
</div>

<div class="promo-container">
    <div class="bento-card">
        <div class="card-title"><i class="ph ph-megaphone"></i> Buat Campaign Harga Coret</div>
        
        <form action="<?= base_url('/marketing/create_discount/'.$shop['shop_id']) ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label>Nama Promosi (Internal)</label>
                <input type="text" name="discount_name" class="form-control" placeholder="Cth: Flash Sale Knalpot Akhir Tahun" required maxlength="150">
            </div>

            <div class="form-group">
                <label>Pilih Knalpot yang Didiskon</label>
                <select name="item_id" class="form-control" required id="productSelect" onchange="updatePreview()">
                    <option value="">-- Pilih Produk dari Katalog --</option>
                    <?php foreach($products as $p): ?>
                        <option value="<?= $p['item_id'] ?>" data-price="<?= $p['price'] ?>" data-name="<?= esc($p['item_name']) ?>">
                            <?= esc($p['item_sku']) ?> | <?= esc($p['item_name']) ?> (Harga Normal: Rp <?= number_format($p['price'],0,',','.') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Harga Setelah Diskon (Harga Coret)</label>
                <input type="number" name="promo_price" id="promoPriceInput" class="form-control" placeholder="Cth: 1250000" required min="100" onkeyup="updatePreview()">
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 5px;"><i class="ph ph-info"></i> Harus lebih murah dari harga normal.</div>
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

            <button type="submit" class="btn-submit">
                <i class="ph ph-rocket-launch"></i> Tembak Promo ke Shopee
            </button>
        </form>
    </div>

    <div>
        <div style="font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 10px; text-align: center;">Simulasi Tampilan Aplikasi Pembeli</div>
        <div class="preview-box">
            <div class="preview-img"><i class="ph ph-image"></i></div>
            <div class="preview-body">
                <div class="preview-tag">Lebih Murah!</div>
                <div class="preview-title" id="prevName">Pilih produk di samping untuk melihat preview...</div>
                <div class="preview-price">
                    <div class="price-strike" id="prevOldPrice">Rp 0</div>
                    <div class="price-new" id="prevNewPrice">Rp 0</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Fitur JS Sederhana untuk mensimulasikan tampilan (Real-time Preview)
    function updatePreview() {
        const select = document.getElementById('productSelect');
        const priceInput = document.getElementById('promoPriceInput').value;
        
        if(select.selectedIndex > 0) {
            const opt = select.options[select.selectedIndex];
            const name = opt.getAttribute('data-name');
            const oldPrice = parseFloat(opt.getAttribute('data-price'));
            
            document.getElementById('prevName').innerText = name;
            document.getElementById('prevOldPrice').innerText = 'Rp ' + oldPrice.toLocaleString('id-ID');
            
            if(priceInput) {
                document.getElementById('prevNewPrice').innerText = 'Rp ' + parseFloat(priceInput).toLocaleString('id-ID');
            } else {
                document.getElementById('prevNewPrice').innerText = 'Rp 0';
            }
        }
    }
</script>

<?= $this->endSection() ?>