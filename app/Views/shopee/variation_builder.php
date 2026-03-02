<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; display: flex; align-items: center; gap: 10px;}
    
    .builder-container { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; box-shadow: var(--shadow-card); overflow: hidden;}
    .product-info { background: rgba(59, 130, 246, 0.05); padding: 20px; border-bottom: 1px solid rgba(59, 130, 246, 0.2); display: flex; align-items: center; gap: 15px;}
    
    .b-section { padding: 25px; border-bottom: 1px dashed var(--border-subtle); }
    .section-title { font-size: 15px; font-weight: 800; color: var(--text-main); margin-bottom: 15px; display: flex; align-items: center; gap: 8px;}

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 10px; font-size: 14px; color: var(--text-main); font-weight: 600; outline: none; transition: 0.2s;}
    .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);}

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    /* TABLE KOMBINASI */
    table { width: 100%; border-collapse: collapse; margin-top: 10px;}
    th { padding: 12px 15px; font-size: 12px; font-weight: 800; color: var(--text-muted); background: var(--bg-base); border-bottom: 2px solid var(--border-subtle); text-align: left;}
    td { padding: 12px 15px; border-bottom: 1px solid var(--border-subtle); vertical-align: middle;}
    .td-input { width: 100%; border: 1px solid var(--border-subtle); background: var(--bg-base); padding: 8px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; color: var(--text-main); outline: none;}
    .td-input:focus { border-color: #3b82f6; }

    .btn-generate { background: var(--text-main); color: var(--bg-base); border: none; padding: 12px 20px; border-radius: 10px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; margin-top: 10px;}
    
    .btn-save { width: 100%; background: #10b981; color: #fff; border: none; padding: 18px; border-radius: 0 0 16px 16px; font-size: 16px; font-weight: 900; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.2s;}
    .btn-save:hover { background: #059669; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1><i class="ph ph-tree-structure" style="color: #3b82f6;"></i> Variasi Produk</h1>
        <p>Atur pilihan warna, ukuran, dan harga khusus untuk setiap varian.</p>
    </div>
</div>

<div class="builder-container">
    <div class="product-info">
        <?php if(!empty($product['image_url'])): ?>
            <img src="<?= esc($product['image_url']) ?>" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
        <?php else: ?>
            <div style="width: 50px; height: 50px; background: #fff; border-radius: 8px; display:flex; align-items:center; justify-content:center;"><i class="ph ph-image"></i></div>
        <?php endif; ?>
        <div>
            <div style="font-weight: 800; font-size: 16px; color: #1d4ed8;"><?= esc($product['item_name']) ?></div>
            <div style="font-size: 12px; color: var(--text-muted); font-family: monospace;">Item ID: <?= esc($product['item_id']) ?> | SKU: <?= esc($product['item_sku']) ?></div>
        </div>
    </div>

    <div class="b-section">
        <div class="section-title"><i class="ph ph-list-numbers"></i> 1. Tentukan Kategori Varian</div>
        
        <div class="grid-2">
            <div style="background: rgba(0,0,0,0.02); padding: 15px; border-radius: 12px; border: 1px solid var(--border-subtle);">
                <div class="form-group">
                    <label>Varian 1 (Contoh: Warna)</label>
                    <input type="text" id="t1_name" class="form-control" placeholder="Ketik nama varian..." value="Warna">
                </div>
                <div class="form-group">
                    <label>Pilihan (Pisahkan dengan koma)</label>
                    <input type="text" id="t1_options" class="form-control" placeholder="Contoh: Sandblast, Half Blue, Carbon" value="Sandblast, Carbon">
                </div>
            </div>

            <div style="background: rgba(0,0,0,0.02); padding: 15px; border-radius: 12px; border: 1px solid var(--border-subtle);">
                <div class="form-group">
                    <label>Varian 2 (Opsional - Contoh: Ukuran)</label>
                    <input type="text" id="t2_name" class="form-control" placeholder="Ketik nama varian..." value="Inlet">
                </div>
                <div class="form-group">
                    <label>Pilihan (Pisahkan dengan koma)</label>
                    <input type="text" id="t2_options" class="form-control" placeholder="Contoh: 38mm, 50mm" value="38mm, 50mm">
                </div>
            </div>
        </div>
        
        <button type="button" class="btn-generate" onclick="generateTable()">
            <i class="ph ph-arrows-clockwise"></i> Buat Tabel Kombinasi
        </button>
    </div>

    <div class="b-section" id="tableSection" style="display: none;">
        <div class="section-title"><i class="ph ph-table"></i> 2. Atur Harga & Stok per Spesifikasi</div>
        <div style="overflow-x: auto;">
            <table id="variationTable">
                <thead>
                    <tr id="tableHead">
                        </tr>
                </thead>
                <tbody id="tableBody">
                    </tbody>
            </table>
        </div>
    </div>

    <form action="<?= base_url('/shopee/save_variation/'.$shop['shop_id'].'/'.$product['item_id']) ?>" method="post" id="formSubmit" style="display: none;">
        <?= csrf_field() ?>
        <input type="hidden" name="variation_payload" id="payloadJson">
        <button type="button" class="btn-save" onclick="submitVariation()">
            <i class="ph ph-cloud-arrow-up"></i> Simpan Varian ke Shopee
        </button>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentCombinations = [];

    function generateTable() {
        let t1_name = document.getElementById('t1_name').value.trim();
        let t1_opts = document.getElementById('t1_options').value.split(',').map(s => s.trim()).filter(s => s);
        
        let t2_name = document.getElementById('t2_name').value.trim();
        let t2_opts = document.getElementById('t2_options').value.split(',').map(s => s.trim()).filter(s => s);

        if(t1_opts.length === 0) {
            Swal.fire('Oops', 'Varian 1 wajib diisi minimal 1 pilihan.', 'error');
            return;
        }

        // Render Table Head
        let thead = document.getElementById('tableHead');
        let htmlHead = `<th>${t1_name}</th>`;
        if(t2_opts.length > 0 && t2_name) {
            htmlHead += `<th>${t2_name}</th>`;
        }
        htmlHead += `<th>Harga Modal / Asli (Rp)</th><th>Stok Fisik</th><th>Kode SKU Gudang</th>`;
        thead.innerHTML = htmlHead;

        // Generate Combinations
        currentCombinations = [];
        let tbody = document.getElementById('tableBody');
        let htmlBody = '';

        for (let i = 0; i < t1_opts.length; i++) {
            if (t2_opts.length > 0 && t2_name) {
                for (let j = 0; j < t2_opts.length; j++) {
                    currentCombinations.push({ t1: i, t2: j, name1: t1_opts[i], name2: t2_opts[j] });
                    htmlBody += createRowHTML(t1_opts[i], t2_opts[j], i, j);
                }
            } else {
                currentCombinations.push({ t1: i, t2: null, name1: t1_opts[i], name2: null });
                htmlBody += createRowHTML(t1_opts[i], null, i, null);
            }
        }

        tbody.innerHTML = htmlBody;
        document.getElementById('tableSection').style.display = 'block';
        document.getElementById('formSubmit').style.display = 'block';
    }

    function createRowHTML(val1, val2, idx1, idx2) {
        let indexStr = val2 ? `${idx1}_${idx2}` : `${idx1}`;
        let tr = `<tr><td><b>${val1}</b></td>`;
        if (val2) tr += `<td><b>${val2}</b></td>`;
        
        tr += `
            <td><input type="number" class="td-input v-price" data-idx="${indexStr}" placeholder="150000" required min="100"></td>
            <td><input type="number" class="td-input v-stock" data-idx="${indexStr}" placeholder="10" required min="0"></td>
            <td><input type="text" class="td-input v-sku" data-idx="${indexStr}" placeholder="FG-XXX-XXX" style="text-transform:uppercase;"></td>
        </tr>`;
        return tr;
    }

    function submitVariation() {
        let t1_name = document.getElementById('t1_name').value.trim();
        let t1_opts = document.getElementById('t1_options').value.split(',').map(s => s.trim()).filter(s => s);
        
        let t2_name = document.getElementById('t2_name').value.trim();
        let t2_opts = document.getElementById('t2_options').value.split(',').map(s => s.trim()).filter(s => s);

        // 1. Susun Tier Variation Array
        let tier_variation = [];
        tier_variation.push({
            name: t1_name,
            option_list: t1_opts.map(opt => ({ option: opt }))
        });

        if (t2_opts.length > 0 && t2_name) {
            tier_variation.push({
                name: t2_name,
                option_list: t2_opts.map(opt => ({ option: opt }))
            });
        }

        // 2. Susun Model Array
        let model = [];
        let isValid = true;

        currentCombinations.forEach(combo => {
            let indexStr = combo.t2 !== null ? `${combo.t1}_${combo.t2}` : `${combo.t1}`;
            
            let price = document.querySelector(`.v-price[data-idx="${indexStr}"]`).value;
            let stock = document.querySelector(`.v-stock[data-idx="${indexStr}"]`).value;
            let sku   = document.querySelector(`.v-sku[data-idx="${indexStr}"]`).value;

            if (!price || !stock) isValid = false;

            let tier_index = combo.t2 !== null ? [combo.t1, combo.t2] : [combo.t1];

            model.push({
                tier_index: tier_index,
                normal_price: parseFloat(price),
                original_price: parseFloat(price),
                stock_setting: { normal_stock: parseInt(stock) },
                model_sku: sku
            });
        });

        if (!isValid) {
            Swal.fire('Oops', 'Mohon lengkapi semua Harga dan Stok.', 'error');
            return;
        }

        // 3. Masukkan ke input tersembunyi sebagai string JSON
        let finalPayload = {
            tier_variation: tier_variation,
            model: model
        };

        document.getElementById('payloadJson').value = JSON.stringify(finalPayload);

        // Submit form
        document.getElementById('formSubmit').submit();
    }
</script>

<?= $this->endSection() ?>