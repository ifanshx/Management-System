<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px;}
    
    .builder-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 30px; box-shadow: var(--shadow-card); max-width: 900px; margin: 0 auto;}
    
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 15px; border-radius: 12px; font-size: 14px; font-weight: 600; outline: none; color: var(--text-main); transition: 0.2s;}
    .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);}

    /* Dynamic Row PO */
    .po-row { display: grid; grid-template-columns: 2fr 1fr 1.5fr auto; gap: 15px; align-items: center; margin-bottom: 12px; background: rgba(0,0,0,0.01); padding: 12px; border-radius: 12px; border: 1px dashed var(--border-subtle);}
    html.dark .po-row { background: rgba(255,255,255,0.02); }
    
    .input-money { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 10px; overflow: hidden; focus-within: border-color: #4f46e5;}
    .input-money span { padding: 12px; background: rgba(0,0,0,0.03); font-size: 13px; font-weight: 800; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    .input-money input { border: none; padding: 12px; font-size: 14px; font-weight: 700; width: 100%; outline: none; background: transparent; color: var(--text-main);}

    .btn-remove { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; width: 44px; height: 44px; border-radius: 10px; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s;}
    .btn-remove:hover { background: #ef4444; color: #fff;}

    .btn-add { background: var(--bg-base); color: #4f46e5; border: 2px dashed #4f46e5; width: 100%; padding: 15px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; margin-bottom: 25px; transition: 0.2s; display: flex; justify-content: center; gap: 8px;}
    .btn-add:hover { background: rgba(79, 70, 229, 0.1); }

    .grand-total { background: rgba(16, 185, 129, 0.05); border: 2px dashed #10b981; padding: 20px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;}
    .total-title { font-size: 14px; font-weight: 900; color: var(--text-muted); text-transform: uppercase;}
    .total-val { font-size: 32px; font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace;}

    .btn-save { width: 100%; background: #4f46e5; color: #fff; border: none; padding: 20px; border-radius: 14px; font-size: 16px; font-weight: 900; cursor: pointer; box-shadow: 0 4px 20px rgba(79, 70, 229, 0.3); transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 10px;}
    .btn-save:hover { background: #4338ca; transform: translateY(-3px);}
</style>

<div class="page-header" style="max-width: 900px; margin: 0 auto 25px auto;">
    <a href="<?= base_url('/procurement') ?>" style="color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 10px;"><i class="ph ph-arrow-left"></i> Kembali</a>
    <div class="page-title">
        <h1><i class="ph ph-file-text" style="color: #4f46e5;"></i> Buat Purchase Order</h1>
        <p>Pesan bahan baku ke Supplier untuk operasional pabrik.</p>
    </div>
</div>

<div class="builder-card">
    <form action="<?= base_url('/procurement/store_po') ?>" method="post">
        <?= csrf_field() ?>

        <div class="grid-2">
            <div class="form-group">
                <label><i class="ph ph-buildings"></i> Vendor / Supplier</label>
                <select name="supplier_id" class="form-control" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= esc($sup['supplier_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><i class="ph ph-calendar"></i> Tanggal Pemesanan</label>
                <input type="date" name="po_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
        </div>

        <div style="font-size: 14px; font-weight: 900; color: var(--text-main); margin-bottom: 15px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 10px;">
            <i class="ph ph-package"></i> Rincian Barang yang Dipesan
        </div>

        <div id="item-container">
            <div class="po-row">
                <select name="rm_sku[]" class="form-control" required>
                    <option value="">-- Pilih Bahan Baku Gudang --</option>
                    <?php foreach($rawMaterials as $rm): ?>
                        <option value="<?= esc($rm['sku']) ?>">[<?= esc($rm['sku']) ?>] <?= esc($rm['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="qty[]" class="form-control qty-input" placeholder="Jumlah" step="0.01" required oninput="calculateTotal()">
                <div class="input-money">
                    <span>Rp</span>
                    <input type="number" name="price[]" class="price-input" placeholder="Harga Satuan" required oninput="calculateTotal()">
                </div>
                <button type="button" class="btn-remove" onclick="this.parentElement.remove(); calculateTotal();"><i class="ph ph-trash"></i></button>
            </div>
        </div>

        <button type="button" class="btn-add" onclick="addItemRow()"><i class="ph ph-plus-circle"></i> Tambah Baris Barang</button>

        <div class="grand-total">
            <div class="total-title">Estimasi Total Tagihan</div>
            <div class="total-val" id="displayTotal">Rp 0</div>
        </div>

        <button type="submit" class="btn-save"><i class="ph ph-paper-plane-right"></i> Terbitkan Purchase Order (PO)</button>
    </form>
</div>

<script>
    const rmData = <?= json_encode($rawMaterials) ?>;

    function addItemRow() {
        let container = document.getElementById('item-container');
        let options = '<option value="">-- Pilih Bahan Baku Gudang --</option>';
        rmData.forEach(rm => { options += `<option value="${rm.sku}">[${rm.sku}] ${rm.item_name}</option>`; });

        let row = document.createElement('div');
        row.className = 'po-row';
        row.innerHTML = `
            <select name="rm_sku[]" class="form-control" required>${options}</select>
            <input type="number" name="qty[]" class="form-control qty-input" placeholder="Jumlah" step="0.01" required oninput="calculateTotal()">
            <div class="input-money">
                <span>Rp</span>
                <input type="number" name="price[]" class="price-input" placeholder="Harga Satuan" required oninput="calculateTotal()">
            </div>
            <button type="button" class="btn-remove" onclick="this.parentElement.remove(); calculateTotal();"><i class="ph ph-trash"></i></button>
        `;
        container.appendChild(row);
    }

    function calculateTotal() {
        let qtys = document.querySelectorAll('.qty-input');
        let prices = document.querySelectorAll('.price-input');
        let total = 0;

        for(let i=0; i<qtys.length; i++) {
            let q = parseFloat(qtys[i].value) || 0;
            let p = parseFloat(prices[i].value) || 0;
            total += (q * p);
        }

        document.getElementById('displayTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }
</script>

<?= $this->endSection() ?>