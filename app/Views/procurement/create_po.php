<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER & NAVIGATION
       ========================================================= */
    .page-header { margin-bottom: 30px; display: flex; flex-direction: column; gap: 12px; }
    
    .btn-back { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 100px; width: fit-content; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.02);}
    .btn-back:hover { color: #4f46e5; border-color: #4f46e5; transform: translateX(-4px); box-shadow: 0 4px 10px rgba(79, 70, 229, 0.1);}

    .page-title h1 { font-size: 30px; font-weight: 900; color: var(--text-main); margin: 0; display: flex; align-items: center; gap: 12px; letter-spacing: -0.5px;}
    .page-title p { font-size: 15px; color: var(--text-muted); margin: 5px 0 0 0; font-weight: 500;}

    /* =========================================================
       2. MAIN BUILDER CARD
       ========================================================= */
    .builder-wrapper { max-width: 1050px; margin: 0 auto; padding-bottom: 50px; }
    
    .builder-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 40px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); transition: box-shadow 0.3s ease;}
    @media (max-width: 768px) { .builder-card { padding: 25px; } }

    .section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px dashed var(--border-subtle); }
    .section-header i { font-size: 24px; color: #4f46e5; background: rgba(79, 70, 229, 0.1); padding: 8px; border-radius: 10px; }
    .section-header h2 { font-size: 18px; font-weight: 900; color: var(--text-main); margin: 0; }

    /* =========================================================
       3. FORM ELEMENTS
       ========================================================= */
    .grid-2 { display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px; margin-bottom: 40px; }
    @media (max-width: 640px) { .grid-2 { grid-template-columns: 1fr; gap: 15px; } }

    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group label { font-size: 12px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;}
    
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 15px 18px; border-radius: 14px; font-size: 14px; font-weight: 600; color: var(--text-main); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); appearance: none;}
    .form-control:focus { border-color: #4f46e5; background: var(--bg-surface); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15); outline: none;}
    .form-control:disabled { opacity: 0.6; cursor: not-allowed; }

    /* =========================================================
       4. DYNAMIC ROWS (ENTERPRISE LIST)
       ========================================================= */
    .po-row { display: grid; grid-template-columns: 2.5fr 1fr 1.5fr 1fr auto; gap: 15px; align-items: center; background: var(--bg-surface); padding: 15px 20px; border-radius: 16px; border: 1px solid var(--border-subtle); margin-bottom: 12px; transition: all 0.3s ease; box-shadow: 0 2px 5px rgba(0,0,0,0.01);}
    .po-row:hover { border-color: #a5b4fc; box-shadow: 0 5px 15px rgba(79, 70, 229, 0.08); transform: translateY(-2px);}
    html.dark .po-row { background: var(--bg-base); }
    
    @media (max-width: 1024px) { 
        .po-row { grid-template-columns: 1fr 1fr; gap: 15px; padding: 20px; position: relative;} 
        .po-row > *:nth-child(1) { grid-column: 1 / -1; }
        .po-row > .btn-remove { position: absolute; top: 20px; right: 20px; }
    }

    /* Custom Input Money */
    .input-money { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; overflow: hidden; transition: 0.3s;}
    .input-money:focus-within { border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15); background: var(--bg-surface);}
    .input-money span { padding: 15px; background: rgba(0,0,0,0.02); font-size: 13px; font-weight: 800; color: var(--text-muted); border-right: 1px solid var(--border-subtle);}
    .input-money input { border: none; padding: 15px; font-size: 14px; font-weight: 700; width: 100%; outline: none; background: transparent; color: var(--text-main); font-family: 'Space Mono', monospace;}

    /* Live Subtotal Badge */
    .row-subtotal { background: rgba(16, 185, 129, 0.08); color: #10b981; padding: 15px; border-radius: 14px; font-family: 'Space Mono', monospace; font-weight: 800; font-size: 14px; text-align: right; border: 1px dashed rgba(16, 185, 129, 0.3);}

    .btn-remove { background: rgba(239, 68, 68, 0.08); color: #ef4444; border: 1px solid transparent; width: 48px; height: 48px; border-radius: 14px; font-size: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;}
    .btn-remove:hover { background: #ef4444; color: #fff; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); transform: scale(1.05) rotate(5deg);}

    .btn-add { background: var(--bg-base); color: #4f46e5; border: 2px dashed #a5b4fc; width: 100%; padding: 20px; border-radius: 16px; font-weight: 800; font-size: 15px; cursor: pointer; margin: 10px 0 35px 0; transition: all 0.3s ease; display: flex; justify-content: center; align-items: center; gap: 10px;}
    .btn-add:hover { background: rgba(79, 70, 229, 0.05); border-color: #4f46e5; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(79, 70, 229, 0.1);}

    /* =========================================================
       5. FOOTER & TOTAL
       ========================================================= */
    .grand-total-box { background: var(--text-main); color: var(--bg-base); padding: 30px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 15px 30px -10px rgba(0,0,0,0.2);}
    .total-title { font-size: 14px; font-weight: 800; text-transform: uppercase; color: var(--border-subtle); display: flex; align-items: center; gap: 12px; letter-spacing: 1px;}
    .total-val { font-size: 42px; font-weight: 900; font-family: 'Space Mono', monospace; line-height: 1; letter-spacing: -1px; color: #fff;}

    .btn-save { width: 100%; background: #4f46e5; color: #fff; border: none; padding: 24px; border-radius: 20px; font-size: 18px; font-weight: 900; cursor: pointer; box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); display: flex; justify-content: center; align-items: center; gap: 12px;}
    .btn-save:hover { background: #4338ca; transform: translateY(-5px); box-shadow: 0 20px 40px rgba(79, 70, 229, 0.4);}
    .btn-save:active { transform: translateY(0); }
</style>

<div class="builder-wrapper">
    
    <div class="page-header">
        <a href="<?= base_url('/procurement') ?>" class="btn-back">
            <i class="ph-bold ph-arrow-left"></i> Kembali ke Dasbor Logistik
        </a>
        <div class="page-title">
            <h1><i class="ph-fill ph-file-text" style="color: #4f46e5;"></i> Buat Purchase Order</h1>
            <p>Terbitkan dokumen pemesanan bahan baku resmi secara rapi dan profesional.</p>
        </div>
    </div>

    <?php if(empty($rawMaterials)): ?>
        <div style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 25px; border-radius: 20px; margin-bottom: 30px; display: flex; align-items: flex-start; gap: 18px; box-shadow: 0 4px 20px rgba(239, 68, 68, 0.1);">
            <i class="ph-fill ph-warning-circle" style="font-size: 32px;"></i>
            <div style="line-height: 1.6;">
                <span style="font-size: 16px; font-weight: 900; display: block; margin-bottom: 5px;">Akses Ditolak: Master Bahan Baku Kosong!</span>
                Sistem tidak mendeteksi adanya data bahan baku di gudang Anda. Silakan tambahkan data melalui menu <b>"Master Gudang Lokal"</b> terlebih dahulu.
            </div>
        </div>
    <?php endif; ?>

    <div class="builder-card">
        <form action="<?= base_url('/procurement/store_po') ?>" method="post">
            <?= csrf_field() ?>

            <div class="section-header">
                <i class="ph-fill ph-buildings"></i>
                <h2>Informasi Vendor & Waktu</h2>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Pilih Vendor / Supplier Tujuan</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">-- Pilih Supplier yang Terdaftar --</option>
                        <?php foreach($suppliers as $sup): ?>
                            <option value="<?= $sup['id'] ?>"><?= esc($sup['supplier_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Pemesanan (PO Date)</label>
                    <input type="date" name="po_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace;">
                </div>
            </div>

            <div class="section-header" style="margin-top: 20px;">
                <i class="ph-fill ph-package"></i>
                <h2>Rincian Barang yang Dipesan</h2>
            </div>

            <div style="display: grid; grid-template-columns: 2.5fr 1fr 1.5fr 1fr auto; gap: 15px; padding: 0 20px 10px 20px; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;" class="desktop-labels">
                <div>Nama Material</div>
                <div>Kuantitas</div>
                <div>Harga Satuan</div>
                <div style="text-align: right;">Subtotal Baris</div>
                <div style="width: 48px;"></div>
            </div>

            <div id="item-container">
                <div class="po-row">
                    <select name="rm_sku[]" class="form-control" required <?= empty($rawMaterials) ? 'disabled' : '' ?>>
                        <option value="">-- Cari Material di Gudang --</option>
                        <?php foreach($rawMaterials as $rm): ?>
                            <option value="<?= esc($rm['sku_material']) ?>">[<?= esc($rm['sku_material']) ?>] <?= esc($rm['material_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <input type="number" name="qty[]" class="form-control qty-input" placeholder="Qty" step="0.01" required oninput="calculateTotal()" <?= empty($rawMaterials) ? 'disabled' : '' ?>>
                    
                    <div class="input-money">
                        <span>Rp</span>
                        <input type="number" name="price[]" class="price-input" placeholder="0" required oninput="calculateTotal()" <?= empty($rawMaterials) ? 'disabled' : '' ?>>
                    </div>

                    <div class="row-subtotal subtotal-display">Rp 0</div>
                    
                    <button type="button" class="btn-remove" onclick="this.parentElement.remove(); calculateTotal();" title="Hapus Baris" <?= empty($rawMaterials) ? 'disabled' : '' ?>>
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-add" onclick="addItemRow()" <?= empty($rawMaterials) ? 'disabled' : '' ?>>
                <i class="ph-bold ph-plus-circle"></i> Tambah Material Lainnya
            </button>

            <div class="grand-total-box">
                <div class="total-title">
                    <div style="background: rgba(255,255,255,0.1); width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="ph-fill ph-calculator" style="color: #fff; font-size: 24px;"></i>
                    </div>
                    Total Estimasi Tagihan
                </div>
                <div class="total-val" id="displayTotal">Rp 0</div>
            </div>

            <button type="submit" class="btn-save" <?= empty($rawMaterials) || empty($suppliers) ? 'disabled' : '' ?>>
                <i class="ph-bold ph-paper-plane-tilt" style="font-size: 24px;"></i> Terbitkan Dokumen PO Sekarang
            </button>
        </form>
    </div>
</div>

<script>
    const rmData = <?= json_encode($rawMaterials) ?>;

    // Logika Penambahan Baris Baru
    function addItemRow() {
        if(rmData.length === 0) return;

        let container = document.getElementById('item-container');
        let options = '<option value="">-- Cari Material di Gudang --</option>';
        rmData.forEach(rm => { 
            options += `<option value="${rm.sku_material}">[${rm.sku_material}] ${rm.material_name}</option>`; 
        });

        let row = document.createElement('div');
        row.className = 'po-row';
        row.innerHTML = `
            <select name="rm_sku[]" class="form-control" required>${options}</select>
            <input type="number" name="qty[]" class="form-control qty-input" placeholder="Qty" step="0.01" required oninput="calculateTotal()">
            <div class="input-money">
                <span>Rp</span>
                <input type="number" name="price[]" class="price-input" placeholder="0" required oninput="calculateTotal()">
            </div>
            <div class="row-subtotal subtotal-display">Rp 0</div>
            <button type="button" class="btn-remove" onclick="this.parentElement.remove(); calculateTotal();" title="Hapus Baris">
                <i class="ph-bold ph-x"></i>
            </button>
        `;
        
        // Efek Animasi Meluncur Masuk
        row.style.opacity = 0;
        row.style.transform = "translateY(20px) scale(0.98)";
        container.appendChild(row);
        
        setTimeout(() => {
            row.style.opacity = 1;
            row.style.transform = "translateY(0) scale(1)";
        }, 10);
    }

    // Mesin Kalkulator Real-Time
    function calculateTotal() {
        let qtys = document.querySelectorAll('.qty-input');
        let prices = document.querySelectorAll('.price-input');
        let subtotals = document.querySelectorAll('.subtotal-display');
        let grandTotal = 0;

        for(let i=0; i<qtys.length; i++) {
            let q = parseFloat(qtys[i].value) || 0;
            let p = parseFloat(prices[i].value) || 0;
            let sub = q * p;
            
            // Tampilkan Live Subtotal per Baris
            subtotals[i].innerText = 'Rp ' + sub.toLocaleString('id-ID');
            
            grandTotal += sub;
        }

        // Tampilkan Live Grand Total dengan Format Rupiah
        document.getElementById('displayTotal').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
    }
</script>

<style>
    /* Sembunyikan label kolom di layar kecil agar tidak berantakan */
    @media (max-width: 1024px) {
        .desktop-labels { display: none !important; }
    }
</style>

<?= $this->endSection() ?>