<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    :root { --brand: #4f46e5; --brand-dark: #4338ca; --bg-soft: rgba(79, 70, 229, 0.05); }

    .page-header { margin-bottom: 25px; display: flex; flex-direction: column; gap: 12px; }
    .btn-back { color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 999px; width: fit-content; transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .btn-back:hover { color: var(--brand); border-color: var(--brand); transform: translateX(-4px); box-shadow: 0 8px 15px var(--bg-soft);}

    .page-title h1 { font-size: 32px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; display: flex; align-items: center; gap: 12px;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 6px 0 0 0;}

    .builder-layout { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 25px; align-items: start; padding-bottom: 50px;}
    @media (max-width: 1024px) { .builder-layout { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 30px; box-shadow: 0 15px 35px -10px rgba(0,0,0,0.05); }
    .sticky-card { position: sticky; top: 25px; }
    
    .section-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin: 0 0 20px 0; padding-bottom: 15px; border-bottom: 1px dashed var(--border-subtle); display: flex; align-items: center; gap: 10px;}
    .section-title i { background: var(--bg-soft); color: var(--brand); padding: 8px; border-radius: 10px; font-size: 20px;}

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 20px;}
    .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 16px;}
    .form-group label { font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 16px; border-radius: 14px; font-size: 13px; font-weight: 700; color: var(--text-main); outline: none; transition: 0.3s; }
    .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 4px var(--bg-soft); }
    select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px; cursor: pointer;}

    .item-row { display: grid; grid-template-columns: 2.5fr 1.2fr 1.5fr auto; gap: 15px; background: var(--bg-base); padding: 15px; border-radius: 18px; border: 1px solid var(--border-subtle); margin-bottom: 15px; align-items: center; transition: 0.3s;}
    .item-row:hover { border-color: var(--brand); box-shadow: 0 8px 20px -8px var(--bg-soft); transform: translateY(-2px);}
    @media (max-width: 640px) { .item-row { grid-template-columns: 1fr; position: relative; padding-top: 35px;} .item-row .btn-del { position: absolute; top: 10px; right: 10px; } }

    .input-grp { display: flex; align-items: stretch; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: 0.3s;}
    .input-grp:focus-within { border-color: var(--brand); box-shadow: 0 0 0 3px var(--bg-soft); }
    .input-grp span { padding: 12px 14px; background: rgba(0,0,0,0.02); font-size: 12px; font-weight: 900; color: var(--text-muted); border-right: 1px solid var(--border-subtle); display: flex; align-items: center;}
    .input-grp input { flex: 1; border: none; padding: 12px 14px; font-size: 13px; font-weight: 800; background: transparent; outline: none; font-family: 'Space Mono', monospace; text-align: right;}

    .btn-add { width: 100%; background: transparent; border: 2px dashed var(--brand); color: var(--brand); padding: 16px; border-radius: 16px; font-size: 13px; font-weight: 900; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.3s;}
    .btn-add:hover { background: var(--bg-soft); transform: translateY(-2px); }
    .btn-del { width: 44px; height: 44px; border-radius: 12px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; font-size: 20px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center;}
    .btn-del:hover { background: #ef4444; color: #fff; transform: scale(1.05) rotate(5deg); }

    .sum-line { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px dashed var(--border-subtle); font-size: 13px; font-weight: 800; color: var(--text-muted);}
    .sum-line.grand { border-top: 2px solid var(--border-subtle); border-bottom: none; margin-top: 10px; padding-top: 15px; font-size: 18px; color: var(--text-main); font-weight: 900;}
    .sum-val { font-family: 'Space Mono', monospace; color: var(--text-main); font-weight: 900;}
    .sum-line.grand .sum-val { font-size: 24px; color: var(--brand); letter-spacing: -1px;}

    .btn-submit { width: 100%; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; border: none; padding: 20px; border-radius: 18px; font-size: 15px; font-weight: 900; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 25px -8px rgba(79, 70, 229, 0.6); display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 25px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -8px rgba(79, 70, 229, 0.8); }

    .desktop-labels { display: grid; grid-template-columns: 2.5fr 1.2fr 1.5fr auto; gap: 15px; padding: 0 15px 10px 15px; font-size: 11px; font-weight: 900; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    @media (max-width: 1024px) { .desktop-labels { display: none !important; } }
    
    .unit-label { background: rgba(37,99,235,0.08) !important; color: var(--brand) !important; border-left: 1px solid var(--border-subtle); border-right: none !important; font-weight: 900 !important;}
</style>

<div class="page-header">
    <a href="<?= base_url('procurement') ?>" class="btn-back"><i class="ph-bold ph-arrow-left"></i> Kembali</a>
    <div class="page-title">
        <h1><i class="ph-fill ph-file-text" style="color: var(--brand);"></i> Buat Purchase Order</h1>
        <p>Terbitkan dokumen pemesanan bahan baku resmi secara rapi dan otomatis ke WhatsApp Supplier.</p>
    </div>
</div>

<form id="formPO" action="<?= base_url('procurement/store_po') ?>" method="post">
    <?= csrf_field() ?>
    
    <div class="builder-layout">
        <div class="left-col">
            <div class="bento-card" style="margin-bottom: 25px;">
                <div class="section-title"><i class="ph-fill ph-buildings"></i> Informasi Vendor & Waktu</div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Vendor / Supplier Tujuan</label>
                        <select name="supplier_id" class="form-control" required>
                            <option value="" disabled selected>-- Pilih Vendor --</option>
                            <?php foreach($suppliers as $sup): ?>
                                <?php $hasPhone = !empty(preg_replace('/[^0-9]/', '', $sup['phone'])); ?>
                                <option value="<?= $sup['id'] ?>">
                                    <?= esc($sup['supplier_name']) ?> <?= $hasPhone ? '' : '(WA Kosong)' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Dokumen</label>
                        <input type="date" name="po_date" class="form-control" value="<?= date('Y-m-d') ?>" required style="font-family: 'Space Mono', monospace;">
                    </div>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Metode & Termin Pembayaran</label>
                    <select name="payment_term" class="form-control" required>
                        <option value="Cash / Tunai">Cash / Tunai Keras</option>
                        <option value="Bank Transfer (IDR)" selected>Bank Transfer (IDR)</option>
                        <option value="Tempo 7 Hari">Hutang - Tempo 7 Hari</option>
                        <option value="Tempo 14 Hari">Hutang - Tempo 14 Hari</option>
                        <option value="Tempo 30 Hari">Hutang - Tempo 30 Hari</option>
                    </select>
                </div>
            </div>

            <div class="bento-card">
                <div class="section-title"><i class="ph-fill ph-package"></i> Rincian Material / Barang Penolong</div>
                
                <div class="item-builder">
                    <div class="desktop-labels">
                        <div>SKU / Material</div>
                        <div style="text-align: center;">Kuantitas (Qty)</div>
                        <div style="text-align: center;">Harga Beli Satuan</div>
                        <div style="width: 44px;"></div>
                    </div>

                    <div id="item-container"></div>

                    <button type="button" class="btn-add" onclick="addRow()"><i class="ph-bold ph-plus-square"></i> Tambah Baris Pembelian</button>
                </div>
            </div>
        </div>

        <div class="right-col">
            <div class="bento-card sticky-card">
                <div class="section-title"><i class="ph-fill ph-calculator"></i> Kalkulasi Finansial</div>
                
                <div class="form-group">
                    <label>PPN / Pajak Tambahan (Rp)</label>
                    <div class="input-grp">
                        <span>Rp</span>
                        <input type="text" name="tax_amount" id="valTax" value="0" onkeyup="formatRupiah(this); calcTotal();" style="color:var(--brand);">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label>Biaya Ongkir / Ekspedisi (Rp)</label>
                    <div class="input-grp">
                        <span>Rp</span>
                        <input type="text" name="shipping_cost" id="valShip" value="0" onkeyup="formatRupiah(this); calcTotal();" style="color:var(--brand);">
                    </div>
                </div>

                <div style="background: var(--bg-base); padding: 20px; border-radius: 18px; border: 1px dashed var(--border-subtle);">
                    <div class="sum-line"><span>Subtotal Barang</span> <span class="sum-val" id="dispSub">Rp 0</span></div>
                    <div class="sum-line"><span>Pajak (Tax)</span> <span class="sum-val" id="dispTax">Rp 0</span></div>
                    <div class="sum-line"><span>Ongkir</span> <span class="sum-val" id="dispShip">Rp 0</span></div>
                    <div class="sum-line grand"><span>TOTAL PO</span> <span class="sum-val" id="dispGrand">Rp 0</span></div>
                </div>

                <button type="submit" id="btnSubmitPO" class="btn-submit">
                    <i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> Terbitkan PO
                </button>
            </div>
        </div>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const rmData = <?= json_encode($rawMaterials, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ?>;

    function formatRupiah(angka) {
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
            
        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        angka.value = rupiah;
    }

    function parseRupiah(rupiahString) {
        if (!rupiahString) return 0;
        let cleanString = rupiahString.replace(/\./g, '');
        return parseFloat(cleanString) || 0;
    }

    function materialOptions() {
        let html = `<option value="">-- Pilih Material --</option>`;
        rmData.forEach(item => {
            let uom = item.purchase_uom || item.unit || 'PCS';
            html += `<option value="${item.sku_material}" data-unit="${uom}">[${item.sku_material}] ${item.material_name}</option>`;
        });
        return html;
    }

    function updateUnitLabel(selElement) {
        let selectedOption = selElement.options[selElement.selectedIndex];
        let unit = selectedOption.getAttribute('data-unit') || 'Unit';
        let row = selElement.closest('.item-row');
        row.querySelector('.unit-label').innerText = unit;
    }

    function addRow() {
        const row = document.createElement('div');
        row.className = 'item-row';
        row.innerHTML = `
            <div style="margin:0;">
                <select name="rm_sku[]" class="form-control material-select" required onchange="updateUnitLabel(this)">
                    ${materialOptions()}
                </select>
            </div>
            <div style="margin:0;">
                <div class="input-grp" style="border: 1px solid var(--border-subtle);">
                    <input type="number" name="qty[]" class="qty-input" step="0.01" value="1" required oninput="calcTotal()" style="text-align: center; font-size: 16px;">
                    <span class="unit-label">Unit</span>
                </div>
            </div>
            <div style="margin:0;">
                <div class="input-grp" style="border: 1px solid var(--border-subtle);">
                    <span>Rp</span>
                    <input type="text" name="unit_price[]" class="price-input" value="0" required onkeyup="formatRupiah(this); calcTotal();">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; align-items:center;">
                <button type="button" class="btn-del" onclick="this.parentElement.parentElement.remove(); calcTotal();" title="Hapus Baris"><i class="ph-bold ph-trash"></i></button>
            </div>
        `;
        row.style.opacity = 0; row.style.transform = "translateY(15px)";
        document.getElementById('item-container').appendChild(row);
        setTimeout(() => { row.style.opacity = 1; row.style.transform = "translateY(0)"; }, 10);
    }

    function calcTotal() {
        let subtotal = 0;
        const qtys = document.querySelectorAll('.qty-input');
        const prices = document.querySelectorAll('.price-input');
        
        for(let i=0; i<qtys.length; i++) {
            let q = parseFloat(qtys[i].value) || 0;
            let p = parseRupiah(prices[i].value);
            subtotal += (q * p);
        }

        const tax = parseRupiah(document.getElementById('valTax').value);
        const ship = parseRupiah(document.getElementById('valShip').value);
        const grand = subtotal + tax + ship;

        document.getElementById('dispSub').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        document.getElementById('dispTax').innerText = 'Rp ' + tax.toLocaleString('id-ID');
        document.getElementById('dispShip').innerText = 'Rp ' + ship.toLocaleString('id-ID');
        document.getElementById('dispGrand').innerText = 'Rp ' + grand.toLocaleString('id-ID');
    }

    document.getElementById('formPO').addEventListener('submit', function(e) {
        e.preventDefault(); 
        
        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Kosong!', text: 'Tambahkan minimal 1 item material.', customClass: { popup: 'swal2-custom-radius' }});
            return;
        }

        document.querySelectorAll('.price-input, #valTax, #valShip').forEach(input => {
            input.value = input.value.replace(/\./g, '');
        });

        const btn = document.getElementById('btnSubmitPO');
        btn.disabled = true; btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin"></i> Menerbitkan PO...';
        
        fetch(this.action, { method: 'POST', body: new FormData(this), headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                if(data.wa_link) {
                    Swal.fire({
                        title: 'PO Berhasil Diterbitkan!',
                        text: 'Pemesanan sudah tercatat di ERP. Apakah Anda ingin langsung mengirim pesanan ini ke WhatsApp Supplier?',
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: '<i class="ph-bold ph-whatsapp-logo" style="font-size:18px;"></i> Buka WhatsApp',
                        cancelButtonText: 'Tutup Saja',
                        confirmButtonColor: '#25D366', 
                        cancelButtonColor: '#64748b',
                        customClass: { popup: 'swal2-custom-radius' }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(data.wa_link, '_blank');
                        }
                        window.location.href = "<?= base_url('procurement') ?>"; 
                    });
                } else {
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Berhasil!', 
                        html: data.message + '<br><br><span style="font-size:12px; color:#ef4444;"><i class="ph-fill ph-warning-circle"></i> Info: Vendor ini tidak memiliki nomor HP valid. Tidak bisa kirim WA otomatis.</span>', 
                        customClass: { popup: 'swal2-custom-radius' }
                    });
                    setTimeout(() => { window.location.href = "<?= base_url('procurement') ?>"; }, 2500);
                }
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message, customClass: { popup: 'swal2-custom-radius' }});
                btn.disabled = false; btn.innerHTML = '<i class="ph-bold ph-paper-plane-tilt"></i> Terbitkan PO';
                document.querySelectorAll('.price-input, #valTax, #valShip').forEach(input => formatRupiah(input));
            }
        }).catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: "Koneksi terputus.", customClass: { popup: 'swal2-custom-radius' }});
            btn.disabled = false; btn.innerHTML = '<i class="ph-bold ph-paper-plane-tilt"></i> Terbitkan PO';
            document.querySelectorAll('.price-input, #valTax, #valShip').forEach(input => formatRupiah(input));
        });
    });

    window.onload = function() { addRow(); };
</script>

<?= $this->endSection() ?>