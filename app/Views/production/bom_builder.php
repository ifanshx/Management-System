<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    /* =========================================================
       1. GLOBAL & ANIMATIONS
       ========================================================= */
    :root {
        --primary: #8b5cf6;
        --primary-light: #ddd6fe;
        --secondary: #f59e0b;
        --success: #10b981;
        --danger: #ef4444;
        --glass-bg: rgba(255, 255, 255, 0.95);
        --shadow-soft: 0 10px 40px -10px rgba(0,0,0,0.05);
        --shadow-hover: 0 20px 50px -15px rgba(139, 92, 246, 0.2);
    }
    
    @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

    /* LAYAR PENUH (FULL WIDTH) */
    .page-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 35px; 
        width: 100%;
        flex-wrap: wrap;
        gap: 15px;
    } 
    
    .page-title { display: flex; align-items: center; gap: 20px; animation: slideIn 0.5s ease-out forwards;}
    .title-icon { width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, var(--primary), #6d28d9); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.5);}
    .page-title h1 { font-size: 32px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0; line-height: 1.5;}

    .btn-back { 
        background: var(--glass-bg); color: var(--text-muted); border: 1px solid var(--border-subtle); 
        padding: 12px 24px; border-radius: 14px; font-size: 13px; font-weight: 800; 
        display: inline-flex; align-items: center; gap: 8px; text-decoration: none; 
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: var(--shadow-soft);
        margin: 0;
    }
    .btn-back:hover { color: var(--primary); border-color: var(--primary); transform: translateX(-5px); box-shadow: 0 8px 25px rgba(139, 92, 246, 0.15);}

    /* =========================================================
       2. BENTO BUILDER CARD (FULL WIDTH)
       ========================================================= */
    .builder-card { 
        background: var(--glass-bg); backdrop-filter: blur(10px);
        border: 1px solid var(--border-subtle); border-radius: 28px; padding: 45px; 
        box-shadow: var(--shadow-soft); 
        width: 100%;
        box-sizing: border-box;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); border-top: 8px solid var(--primary);
        animation: slideIn 0.6s ease-out 0.1s forwards; opacity: 0;
    }
    .builder-card:hover { box-shadow: var(--shadow-hover); border-color: var(--primary-light);}
    @media (max-width: 768px) { .builder-card { padding: 25px; border-radius: 20px; } }
    
    .target-box { background: linear-gradient(145deg, #f8fafc 0%, #ffffff 100%); border: 1px solid #e2e8f0; padding: 30px; border-radius: 20px; margin-bottom: 35px; position: relative; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.02);}
    .target-box::before { content: ''; position: absolute; top: 0; left: 0; width: 6px; height: 100%; background: var(--primary); border-radius: 6px 0 0 6px;}
    
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;}
    
    .form-control { width: 100%; background: #f8fafc; border: 2px solid #e2e8f0; padding: 16px 20px; border-radius: 14px; font-size: 14px; font-weight: 700; outline: none; color: var(--text-main); transition: all 0.3s;}
    .form-control:focus { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);}

    /* =========================================================
       3. SELECT2 MAGIC (SERAGAM & ELEGAN)
       ========================================================= */
    .select2-container--default .select2-selection--single { background: #f8fafc; border: 2px solid #e2e8f0; height: 54px; border-radius: 14px; display: flex; align-items: center; padding: 0 12px; transition: all 0.3s;}
    .select2-container--default.select2-container--focus .select2-selection--single { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15);}
    .select2-container--default .select2-selection--single .select2-selection__rendered { font-weight: 800; color: var(--text-main); font-size: 14px;}
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 52px; right: 18px;}
    
    .select2-dropdown { border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 20px 50px rgba(0,0,0,0.15); padding: 12px; border-top: none;}
    .select2-search__field { border-radius: 10px !important; padding: 12px 16px !important; border: 2px solid #e2e8f0 !important; outline: none; font-family: inherit; font-weight: 700;}
    .select2-search__field:focus { border-color: var(--primary) !important; }
    .select2-results__option { border-radius: 10px; margin-bottom: 4px; font-weight: 700; font-size: 13px; padding: 10px 15px;}
    .select2-results__option--highlighted[aria-selected] { background: var(--primary) !important; color: white !important; }
    
    /* Style untuk Badge Stok di dalam dropdown */
    .stok-badge { float: right; font-size: 11px; background: rgba(0,0,0,0.08); padding: 2px 8px; border-radius: 6px; font-family: 'Space Mono', monospace; font-weight: 900;}
    .select2-results__option--highlighted .stok-badge { background: rgba(255,255,255,0.2); color: #fff; }

    /* =========================================================
       4. DYNAMIC ROWS (THE CORE)
       ========================================================= */
    .section-title { font-size: 18px; font-weight: 900; color: var(--text-main); margin-top: 40px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; padding-bottom: 15px; border-bottom: 2px dashed var(--border-subtle);}
    .section-title i { background: rgba(139, 92, 246, 0.1); color: var(--primary); padding: 10px; border-radius: 12px; font-size: 22px;}

    .dynamic-row { display: grid; gap: 15px; align-items: center; background: #fff; padding: 18px 25px; border-radius: 18px; border: 1px solid var(--border-subtle); margin-bottom: 15px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 4px 6px rgba(0,0,0,0.02);}
    .dynamic-row:hover { border-color: var(--primary-light); box-shadow: 0 12px 30px -10px rgba(139, 92, 246, 0.2); transform: translateY(-3px);}
    
    /* Responsive Grid Adjustments untuk Full Width */
    .op-row { grid-template-columns: 35px 3fr 1.5fr auto; }
    .rm-row { grid-template-columns: 3fr 2fr auto; } 
    @media (max-width: 900px) { .op-row, .rm-row { grid-template-columns: 1fr; } .dynamic-row { padding: 15px; } .step-number{ display:none; } }

    .step-number { font-size: 16px; font-weight: 900; color: var(--text-muted); text-align: center; background: #f1f5f9; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 10px;}
    
    .btn-remove { background: rgba(239, 68, 68, 0.05); color: var(--danger); border: 2px solid transparent; width: 50px; height: 50px; border-radius: 14px; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.3s;}
    .btn-remove:hover { background: var(--danger); color: #fff; transform: scale(1.1) rotate(5deg); box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);}

    .btn-add { background: transparent; color: var(--primary); border: 2px dashed var(--primary-light); width: 100%; padding: 18px; border-radius: 18px; font-weight: 900; font-size: 15px; cursor: pointer; margin-bottom: 10px; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px; background: rgba(139, 92, 246, 0.02);}
    .btn-add:hover { background: rgba(139, 92, 246, 0.08); border-color: var(--primary); transform: translateY(-2px);}

    .btn-save { width: 100%; background: linear-gradient(135deg, var(--primary), #6d28d9); color: #fff; border: none; padding: 22px; border-radius: 20px; font-size: 18px; font-weight: 900; cursor: pointer; box-shadow: 0 15px 35px -5px rgba(139, 92, 246, 0.5); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); margin-top: 50px; display: flex; align-items: center; justify-content: center; gap: 12px; text-transform: uppercase; letter-spacing: 1px;}
    .btn-save:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -5px rgba(139, 92, 246, 0.6);}
    .btn-save:disabled { background: #cbd5e1; box-shadow: none; cursor: not-allowed; transform: none;}

    .input-wrapper { display: flex; align-items: stretch; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 14px; overflow: hidden; transition: all 0.3s ease; }
    .input-wrapper:focus-within { border-color: var(--secondary); background: #fff; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);}
    .input-wrapper input { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 16px; font-size: 15px; font-weight: 900; font-family: 'Space Mono', monospace; outline: none; width: 100%;}
    .prefix { background: rgba(245, 158, 11, 0.1); color: var(--secondary); font-size: 15px; font-weight: 900; padding: 0 18px; display: flex; align-items: center; border-right: 2px solid #e2e8f0; }

    .info-alert { background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); padding: 18px 20px; border-radius: 16px; margin-bottom: 25px; font-size: 13px; color: var(--text-muted); font-weight: 600; line-height: 1.6; display: flex; gap: 12px; align-items: flex-start;}
    .info-alert i { font-size: 22px; margin-top: 2px; }
    
    /* =========================================================
       5. SMART CONVERTER WIDGET
       ========================================================= */
    .converter-group { display: flex; border: 2px solid #e2e8f0; border-radius: 14px; overflow: hidden; background: #f8fafc; transition: all 0.3s;}
    .converter-group:focus-within { border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.15); }
    .converter-input { flex: 1; border: none; background: transparent; padding: 14px 18px; font-weight: 900; font-family: 'Space Mono', monospace; font-size: 16px; outline: none; color: var(--primary);}
    .converter-select { border: none; background: transparent; padding: 0 15px; font-weight: 800; font-size: 12px; color: var(--text-muted); border-left: 2px solid #e2e8f0; outline: none; cursor: pointer; transition: all 0.3s;}
    .converter-select:hover { background: rgba(139, 92, 246, 0.05); color: var(--primary);}
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-flask"></i></div>
        <div>
            <h1>BoM & Routing Studio</h1>
            <p>Arsitektur kebutuhan bahan mentah dan susunan alur kerja pabrikasi (Routing) untuk mencetak 1 Unit Produk.</p>
        </div>
    </div>
    <a href="<?= base_url('/production') ?>" class="btn-back">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Pusat Manufaktur
    </a>
</div>

<div class="builder-card">
    <form action="<?= base_url('/production/store_bom') ?>" method="post" id="formBOM">
        <?= csrf_field() ?>

        <div class="target-box">
            <div class="form-group" style="margin-bottom: 20px;">
                <label style="color: var(--primary);"><i class="ph-fill ph-target"></i> Target Produksi (Barang Jadi Masuk Gudang)</label>
                <select name="fg_sku" class="form-control select2-target" required>
                    <option value="">-- Cari & Pilih Produk Jualan (Cth: Leheran WR155) --</option>
                    <?php foreach($finishedGoods as $fg): ?>
                        <option value="<?= esc($fg['sku']) ?>">
                            [<?= esc($fg['sku']) ?>] <?= esc($fg['item_name']) ?> (Sisa Stok: <?= floatval($fg['physical_stock']) ?> Pcs)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if(empty($finishedGoods)): ?>
                    <div style="font-size: 12px; color: var(--danger); font-weight: 800; margin-top: 10px; display:flex; align-items:center; gap:5px;"><i class="ph-fill ph-warning-circle"></i> *Anda belum mendaftarkan Produk Jualan di Master Gudang.</div>
                <?php endif; ?>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label>Nama Resep / SOP Pembuatan</label>
                <input type="text" name="recipe_name" class="form-control" placeholder="Cth: Standar Perakitan Leheran Paten FU" required autocomplete="off">
            </div>
        </div>

        <div class="section-title">
            <i class="ph-fill ph-kanban" style="color: var(--secondary); background: rgba(245, 158, 11, 0.1);"></i> Tahapan Operasi & Upah Borongan
        </div>
        
        <div class="info-alert" style="background: #fffbeb; border-color: #fde68a;">
            <i class="ph-fill ph-warning-circle" style="color: #f59e0b;"></i>
            <div><b>ATURAN ROUTING:</b> Susun urutan kerja dari awal sampai akhir. <b>Tahapan paling bawah</b> akan dibaca oleh sistem sebagai <b>Tahap Final (Assembly Akhir)</b> yang bertugas memotong persediaan Material di gudang dan mengkalkulasi HPP.</div>
        </div>

        <div id="op-container">
            <div class="dynamic-row op-row">
                <div class="step-number">1</div>
                <input type="text" name="op_name[]" class="form-control" placeholder="Cth: Cetak Monel / Bending" required autocomplete="off" style="font-weight: 900;">
                <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.3);">
                    <div class="prefix">Rp</div>
                    <input type="text" name="op_wage[]" placeholder="Upah/Pcs" required onkeyup="formatRupiah(this)" autocomplete="off" style="color: var(--secondary);">
                </div>
                <button type="button" class="btn-remove" onclick="removeOpRow(this)" title="Hapus Tahap"><i class="ph-bold ph-trash"></i></button>
            </div>
            <div class="dynamic-row op-row" style="border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);">
                <div class="step-number" style="background: #3b82f6; color: #fff;">2</div>
                <input type="text" name="op_name[]" class="form-control" placeholder="Cth: Las Cacing / Perakitan Final" required autocomplete="off" style="font-weight: 900;">
                <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.3);">
                    <div class="prefix">Rp</div>
                    <input type="text" name="op_wage[]" placeholder="Upah/Pcs" required onkeyup="formatRupiah(this)" autocomplete="off" style="color: var(--secondary);">
                </div>
                <button type="button" class="btn-remove" onclick="removeOpRow(this)" title="Hapus Tahap"><i class="ph-bold ph-trash"></i></button>
            </div>
        </div>

        <button type="button" class="btn-add" onclick="addOpRow()" style="color: var(--secondary); border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.02);">
            <i class="ph-bold ph-plus-circle"></i> Tambah Tahapan Kerja Baru
        </button>

        <div class="section-title">
            <i class="ph-fill ph-cube"></i> Kebutuhan Material (BOM)
        </div>
        
        <div class="info-alert" style="background: #fdf4ff; border-color: #e879f9;">
            <i class="ph-fill ph-magic-wand" style="color: #c084fc;"></i> 
            <div><b style="color: #a855f7;">SMART CONVERTER AKTIF:</b> Jika material yang dibeli berupa Pipa Batangan (6 Meter), Bos cukup ketik kebutuhan dalam hitungan "CM", lalu ubah satuan dropdown ke "Potongan (CM)". Sistem otomatis membaginya!</div>
        </div>

        <div id="rm-container">
            <div class="dynamic-row rm-row">
                <select name="rm_sku[]" class="form-control select2-component" required>
                    <option value=""></option>
                    <optgroup label="Bahan Baku Mentah (Pipa, Kawat, dll)">
                        <?php foreach($rawMaterials as $rm): ?>
                            <option value="<?= esc($rm['sku_material']) ?>">
                                [<?= esc($rm['sku_material']) ?>] <?= esc($rm['material_name']) ?> (Sisa Stok: <?= floatval($rm['physical_stock']) ?> <?= esc($rm['unit']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Produk Sub-Assembly (Slincer, dll)">
                        <?php foreach($finishedGoods as $fg): ?>
                            <option value="<?= esc($fg['sku']) ?>">
                                [<?= esc($fg['sku']) ?>] <?= esc($fg['item_name']) ?> (Sisa Stok: <?= floatval($fg['physical_stock']) ?> Pcs)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
                
                <div class="converter-group">
                    <input type="text" class="converter-input qty-tampil" placeholder="Ketik Angka" required onkeyup="calculateRealQty(this)" onchange="calculateRealQty(this)">
                    <select class="converter-select" onchange="calculateRealQty(this)">
                        <option value="1">Satuan Gudang (Batang/Pcs)</option>
                        <option value="600">Potongan CM (1 Btg = 600 CM)</option>
                    </select>
                    <input type="hidden" name="qty[]" class="qty-real">
                </div>
                
                <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-trash"></i></button>
            </div>
        </div>

        <button type="button" class="btn-add" onclick="addRmRow()">
            <i class="ph-bold ph-plus-circle"></i> Tambah Kebutuhan Material
        </button>

        <button type="submit" id="btnSubmitBOM" class="btn-save" <?= (empty($finishedGoods) || empty($rawMaterials)) ? 'disabled' : '' ?>>
            <i class="ph-bold ph-check-square"></i> <span>Simpan Blueprint Produksi</span>
        </button>
    </form>
</div>

<script>
    const rmData = <?= json_encode($rawMaterials) ?>;
    const fgData = <?= json_encode($finishedGoods) ?>;

    // Kustomisasi Tampilan Options Select2 agar Badge Stok rapi di sebelah kanan
    function formatSelect2Options(item) {
        if (!item.id) return item.text;
        
        let text = item.text;
        // Deteksi pola "(Sisa Stok: XXX)" dan jadikan badge
        let badgeMatch = text.match(/\(Sisa Stok: (.*?)\)/);
        
        if (badgeMatch) {
            let pureText = text.replace(badgeMatch[0], '').trim();
            return $(`<span>${pureText} <span class="stok-badge"><i class="ph-bold ph-package"></i> Stok: ${badgeMatch[1]}</span></span>`);
        }
        return text;
    }

    $(document).ready(function() {
        // Inisialisasi Select Target dengan format rapi
        $('.select2-target').select2({ 
            width: '100%', 
            placeholder: "-- Cari & Pilih Target Produksi --",
            templateResult: formatSelect2Options,
            templateSelection: formatSelect2Options
        });
        
        initializeSelect2Component($('.select2-component'));

        // Anti Jeruk-Makan-Jeruk
        $('.select2-target').on('change', function() {
            let selectedTargetSku = $(this).val();
            $('.select2-component option').prop('disabled', false);
            if(selectedTargetSku) {
                $('.select2-component option[value="' + selectedTargetSku + '"]').prop('disabled', true);
            }
            initializeSelect2Component($('.select2-component')); // Refresh
        });
    });

    // FUNGSI AJAIB: SMART CONVERTER (Dengan Anti-Koma Indonesia)
    function calculateRealQty(element) {
        let row = $(element).closest('.converter-group');
        
        // Tangkap input, ubah koma (,) menjadi titik (.) agar bisa dihitung matematis oleh JS
        let rawInput = row.find('.qty-tampil').val().replace(',', '.');
        
        let nilaiTampil = parseFloat(rawInput) || 0;
        let pembagi = parseFloat(row.find('.converter-select').val()) || 1;
        
        let nilaiAsli = nilaiTampil / pembagi;
        
        // Simpan nilai asli ke hidden input dengan presisi 6 desimal
        row.find('.qty-real').val(nilaiAsli.toFixed(6)); 
        
        // Visual Feedback
        if(nilaiTampil > 0) {
            row.css('border-color', 'var(--primary)');
        } else {
            row.css('border-color', '#e2e8f0');
        }
    }

    function formatRupiah(angka) {
        if (!angka) return;
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(), split = number_string.split(','), sisa = split[0].length % 3, rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah; angka.value = rupiah;
    }

    function renumberOperations() {
        let rows = document.querySelectorAll('.op-row');
        rows.forEach((row, index) => {
            let numBox = row.querySelector('.step-number');
            numBox.innerText = index + 1;
            
            row.style.transition = 'all 0.4s ease';
            
            if(index === rows.length - 1) {
                row.style.borderColor = '#3b82f6'; 
                row.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.1)';
                numBox.style.background = '#3b82f6'; 
                numBox.style.color = '#fff';
            } else {
                row.style.borderColor = 'var(--border-subtle)'; 
                row.style.boxShadow = '0 4px 6px rgba(0,0,0,0.02)';
                numBox.style.background = '#f1f5f9'; 
                numBox.style.color = 'var(--text-muted)';
            }
        });
    }

    function addOpRow() {
        let container = document.getElementById('op-container');
        let row = document.createElement('div');
        row.className = 'dynamic-row op-row';
        row.innerHTML = `
            <div class="step-number"></div>
            <input type="text" name="op_name[]" class="form-control" placeholder="Cth: Tahapan Kerja Baru" required autocomplete="off" style="font-weight: 900;">
            <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.3);">
                <div class="prefix">Rp</div>
                <input type="text" name="op_wage[]" placeholder="Upah/Pcs" required onkeyup="formatRupiah(this)" autocomplete="off" style="color: var(--secondary);">
            </div>
            <button type="button" class="btn-remove" onclick="removeOpRow(this)" title="Hapus Tahap"><i class="ph-bold ph-trash"></i></button>
        `;
        row.style.opacity = 0; row.style.transform = "translateY(20px)";
        container.appendChild(row);
        
        setTimeout(() => { 
            row.style.opacity = 1; 
            row.style.transform = "translateY(0)"; 
            renumberOperations(); 
            row.querySelector('input[type="text"]').focus();
        }, 10);
    }

    function removeOpRow(btn) {
        let container = document.getElementById('op-container');
        if(container.children.length <= 1) { 
            btn.parentElement.animate([
                { transform: 'translateX(-5px)' }, { transform: 'translateX(5px)' }, 
                { transform: 'translateX(-5px)' }, { transform: 'translateX(5px)' }, 
                { transform: 'translateX(0)' }
            ], { duration: 400 });
            return; 
        }
        
        let row = btn.parentElement;
        row.style.opacity = 0;
        row.style.transform = "scale(0.95)";
        setTimeout(() => {
            row.remove();
            renumberOperations();
        }, 200);
    }

    function initializeSelect2Component($element) {
        $element.select2({ 
            width: '100%', 
            placeholder: "-- Cari Komponen / Material --",
            templateResult: formatSelect2Options,
            templateSelection: formatSelect2Options
        });
        
        let currentTarget = $('.select2-target').val();
        if(currentTarget) {
            $element.find('option[value="' + currentTarget + '"]').prop('disabled', true);
            // Refresh select2 to apply disabled state visually
            $element.select2({ 
                width: '100%', 
                placeholder: "-- Cari Komponen / Material --",
                templateResult: formatSelect2Options,
                templateSelection: formatSelect2Options
            });
        }
    }

    function addRmRow() {
        if(rmData.length === 0) return;
        let container = document.getElementById('rm-container');
        
        let optgroupRM = '<optgroup label="Bahan Baku Mentah (Pipa, Kawat, dll)">';
        rmData.forEach(rm => { 
            optgroupRM += `<option value="${rm.sku_material}">[${rm.sku_material}] ${rm.material_name} (Sisa Stok: ${parseFloat(rm.physical_stock)} ${rm.unit})</option>`; 
        });
        optgroupRM += '</optgroup>';
        
        let optgroupFG = '<optgroup label="Produk Sub-Assembly (Slincer, dll)">';
        fgData.forEach(fg => { 
            optgroupFG += `<option value="${fg.sku}">[${fg.sku}] ${fg.item_name} (Sisa Stok: ${parseFloat(fg.physical_stock)} Pcs)</option>`; 
        });
        optgroupFG += '</optgroup>';

        let row = document.createElement('div');
        row.className = 'dynamic-row rm-row';
        row.innerHTML = `
            <select name="rm_sku[]" class="form-control select2-component" required>
                <option value=""></option> ${optgroupRM} ${optgroupFG}
            </select>
            <div class="converter-group">
                <input type="text" class="converter-input qty-tampil" placeholder="Ketik Angka" required onkeyup="calculateRealQty(this)" onchange="calculateRealQty(this)">
                <select class="converter-select" onchange="calculateRealQty(this)">
                    <option value="1">Satuan Gudang (Batang/Pcs)</option>
                    <option value="600">Potongan CM (1 Btg = 600 CM)</option>
                </select>
                <input type="hidden" name="qty[]" class="qty-real">
            </div>
            <button type="button" class="btn-remove" onclick="this.parentElement.remove()" title="Hapus Baris"><i class="ph-bold ph-trash"></i></button>
        `;
        row.style.opacity = 0; row.style.transform = "translateY(20px)";
        container.appendChild(row);
        
        initializeSelect2Component($(row).find('.select2-component'));
        setTimeout(() => { row.style.opacity = 1; row.style.transform = "translateY(0)"; }, 10);
    }

    document.getElementById('formBOM').addEventListener('submit', function(e) {
        let isValid = true;
        $('.qty-tampil').each(function() {
            calculateRealQty(this); 
            let realValue = $(this).closest('.converter-group').find('.qty-real').val();
            if(!realValue || realValue <= 0) isValid = false;
        });

        if(!isValid) {
            e.preventDefault();
            alert("Harap isi kuantitas material dengan benar (Angka tidak boleh nol).");
            return;
        }

        const btn = document.getElementById('btnSubmitBOM');
        btn.style.transform = 'scale(0.98)';
        btn.style.opacity = '0.9'; 
        btn.style.pointerEvents = 'none';
        btn.querySelector('span').innerText = 'Sedang Menyimpan Blueprint...';
        btn.querySelector('i').className = 'ph-bold ph-spinner-gap ph-spin';
    });
</script>

<?= $this->endSection() ?>