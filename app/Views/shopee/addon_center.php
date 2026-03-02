<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 25px; box-shadow: var(--shadow-card); margin-bottom: 20px;}
    .card-title { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;}

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 10px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none;}
    .form-control:focus { border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);}

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } }

    .product-list-box { border: 2px dashed var(--border-subtle); border-radius: 12px; padding: 15px; background: rgba(0,0,0,0.01); height: 350px; overflow-y: auto;}
    html.dark .product-list-box { background: rgba(255,255,255,0.02); }
    
    .prod-item { display: flex; align-items: center; gap: 10px; background: var(--bg-surface); padding: 10px; border-radius: 8px; border: 1px solid var(--border-subtle); margin-bottom: 10px;}
    .prod-item:last-child { margin-bottom: 0; }

    .btn-submit { width: 100%; background: #8b5cf6; color: #fff; border: none; padding: 18px; border-radius: 12px; font-size: 16px; font-weight: 900; cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);}
    .btn-submit:hover { background: #7c3aed; transform: translateY(-3px);}
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-plugs-connected" style="color: #8b5cf6;"></i> Paket Diskon (Add-on Deals)</h1>
        <p>Atur penawaran khusus: "Beli Knalpot, dapatkan Aksesoris dengan harga miring".</p>
    </div>
</div>

<form action="<?= base_url('/shopee/create_addon/'.$shop['shop_id']) ?>" method="post" id="addonForm">
    <?= csrf_field() ?>
    <input type="hidden" name="main_items" id="mainItemsData">
    <input type="hidden" name="sub_items" id="subItemsData">

    <div class="bento-card">
        <div class="card-title"><i class="ph ph-faders"></i> 1. Pengaturan Waktu & Aturan</div>
        <div class="grid-2">
            <div>
                <div class="form-group">
                    <label>Nama Promosi (Internal)</label>
                    <input type="text" name="addon_name" class="form-control" placeholder="Cth: Promo Tebus Murah DB Killer" required maxlength="24">
                </div>
                <div class="form-group">
                    <label>Maks. Aksesoris yg boleh dibeli per Pesanan</label>
                    <input type="number" name="purchase_limit" class="form-control" value="2" required min="1">
                </div>
            </div>
            <div>
                <div class="form-group">
                    <label>Waktu Mulai</label>
                    <input type="datetime-local" name="start_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Waktu Berakhir</label>
                    <input type="datetime-local" name="end_time" class="form-control" required>
                </div>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="bento-card" style="border-top: 4px solid #3b82f6;">
            <div class="card-title"><i class="ph ph-motorcycle"></i> 2. Pilih Produk Utama</div>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 15px;">Pembeli harus memasukkan produk ini ke keranjang untuk memicu promo.</p>
            
            <div class="product-list-box">
                <?php foreach($products as $p): ?>
                <label class="prod-item">
                    <input type="checkbox" class="cb-main" value="<?= $p['item_id'] ?>" style="width: 18px; height: 18px; accent-color: #3b82f6;">
                    <div>
                        <div style="font-size: 13px; font-weight: 800;"><?= esc($p['item_name']) ?></div>
                        <div style="font-size: 11px; color: var(--text-muted);">Rp <?= number_format($p['price'],0,',','.') ?></div>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bento-card" style="border-top: 4px solid #8b5cf6;">
            <div class="card-title"><i class="ph ph-plus-circle"></i> 3. Pilih Produk Tambahan (Aksesoris)</div>
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 15px;">Aksesoris yang ditawarkan dengan harga diskon (Cth: DB Killer, Per).</p>
            
            <div class="product-list-box">
                <?php foreach($products as $p): ?>
                <div class="prod-item" style="flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px; width: 100%;">
                        <input type="checkbox" class="cb-sub" value="<?= $p['item_id'] ?>" style="width: 18px; height: 18px; accent-color: #8b5cf6;" onchange="toggleInputs(this, '<?= $p['item_id'] ?>')">
                        <div style="flex: 1;">
                            <div style="font-size: 13px; font-weight: 800;"><?= esc($p['item_name']) ?></div>
                            <div style="font-size: 11px; color: var(--text-muted);">Harga Asli: Rp <?= number_format($p['price'],0,',','.') ?></div>
                        </div>
                    </div>
                    
                    <div id="inputs_<?= $p['item_id'] ?>" style="display: none; width: 100%; margin-top: 10px; padding-top: 10px; border-top: 1px dashed var(--border-subtle); gap: 10px;">
                        <div style="flex: 1;">
                            <label style="font-size: 10px; font-weight: 700; color: var(--text-muted);">Harga Spesial (Add-on Rp)</label>
                            <input type="number" id="price_<?= $p['item_id'] ?>" class="form-control" style="padding: 6px; font-size: 12px;" placeholder="Cth: 10000">
                        </div>
                        <div style="width: 80px;">
                            <label style="font-size: 10px; font-weight: 700; color: var(--text-muted);">Maks. Beli</label>
                            <input type="number" id="limit_<?= $p['item_id'] ?>" class="form-control" style="padding: 6px; font-size: 12px;" value="1">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <button type="button" class="btn-submit" onclick="submitAddon()">
        <i class="ph ph-check-circle"></i> Terbitkan Paket Diskon ke Shopee
    </button>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function toggleInputs(checkbox, itemId) {
        let inputDiv = document.getElementById('inputs_' + itemId);
        if(checkbox.checked) {
            inputDiv.style.display = 'flex';
        } else {
            inputDiv.style.display = 'none';
        }
    }

    function submitAddon() {
        // Kumpulkan Produk Utama
        let mainItems = [];
        document.querySelectorAll('.cb-main:checked').forEach(cb => {
            mainItems.push(cb.value);
        });

        // Kumpulkan Produk Tambahan & Harganya
        let subItems = [];
        let isValid = true;

        document.querySelectorAll('.cb-sub:checked').forEach(cb => {
            let itemId = cb.value;
            let price = document.getElementById('price_' + itemId).value;
            let limit = document.getElementById('limit_' + itemId).value;

            if(!price || price <= 0) { isValid = false; }

            subItems.push({
                id: itemId,
                addon_price: price,
                addon_limit: limit
            });
        });

        if(mainItems.length === 0) {
            Swal.fire('Oops', 'Pilih minimal 1 Produk Utama (Kiri).', 'error'); return;
        }
        if(subItems.length === 0) {
            Swal.fire('Oops', 'Pilih minimal 1 Produk Tambahan (Kanan).', 'error'); return;
        }
        if(!isValid) {
            Swal.fire('Oops', 'Mohon isi Harga Spesial untuk semua Produk Tambahan yang dicentang.', 'error'); return;
        }

        // Masukkan ke Hidden Input sebagai JSON
        document.getElementById('mainItemsData').value = JSON.stringify(mainItems);
        document.getElementById('subItemsData').value = JSON.stringify(subItems);

        // Submit!
        document.getElementById('addonForm').submit();
    }
</script>

<?= $this->endSection() ?>