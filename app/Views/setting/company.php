<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. VARIABLES & TYPOGRAPHY
       ========================================================= */
    :root {
        --brand-orange: #f97316;
        --brand-dark: #ea580c;
        --brand-blue: #3b82f6;
        --bg-main: #f1f5f9;
        --bg-card: #ffffff;
        --bg-input: #f8fafc;
        --text-strong: #0f172a;
        --text-base: #334155;
        --text-muted: #64748b;
        --border-soft: #e2e8f0;
        --border-hard: #cbd5e1;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
        --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        --shadow-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        --radius-lg: 20px;
        --radius-md: 12px;
    }

    .header-section { margin-bottom: 30px; }
    .header-section h1 { font-size: 26px; font-weight: 800; color: var(--text-strong); margin-bottom: 4px; letter-spacing: -0.5px; }
    .header-section p { font-size: 14px; color: var(--text-muted); }

    .section-title { font-size: 18px; font-weight: 800; color: var(--text-strong); margin: 40px 0 20px; padding-bottom: 10px; border-bottom: 2px solid var(--border-soft); display: flex; align-items: center; gap: 10px;}
    .section-title i { color: var(--brand-orange); font-size: 22px;}

    /* =========================================================
       2. GRID LAYOUTS
       ========================================================= */
    .company-grid { display: grid; grid-template-columns: 320px 1fr; gap: 24px; align-items: stretch; }
    .catalog-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 24px; align-items: stretch; }
    
    @media (max-width: 1024px) { 
        .company-grid, .catalog-grid { grid-template-columns: 1fr; } 
    }

    .pro-card { background: var(--bg-card); border: 1px solid var(--border-soft); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow-card); transition: all 0.3s ease; display: flex; flex-direction: column;}
    .pro-card:hover { border-color: var(--border-hard); box-shadow: var(--shadow-hover); }

    /* =========================================================
       3. MODERN INPUT FIELDS
       ========================================================= */
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-base); margin-bottom: 6px; }
    .form-label span { color: var(--text-muted); font-weight: 500; font-size: 11px; margin-left: 4px; }
    
    .input-wrapper { display: flex; align-items: center; background: var(--bg-input); border: 1px solid var(--border-soft); border-radius: var(--radius-md); overflow: hidden; transition: 0.2s; }
    .input-wrapper:focus-within { background: var(--bg-card); border-color: var(--brand-blue); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    
    .input-icon { padding: 0 12px; color: var(--text-muted); font-size: 18px; display: flex; align-items: center; }
    .input-addon { padding: 0 14px; background: rgba(0,0,0,0.03); color: var(--text-muted); font-weight: 700; font-size: 13px; border-right: 1px solid var(--border-soft); display: flex; align-items: center;}
    
    .input-wrapper input, .input-wrapper textarea, .input-wrapper select { flex: 1; background: transparent; border: none; color: var(--text-strong); padding: 12px; font-size: 14px; font-weight: 500; outline: none; font-family: inherit; width: 100%;}
    .input-wrapper select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 35px;}

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    /* =========================================================
       4. UPLOAD ZONE (APPLE/STRIPE STYLE)
       ========================================================= */
    .upload-box { border: 2px dashed var(--border-hard); border-radius: var(--radius-md); padding: 20px; text-align: center; background: var(--bg-input); position: relative; transition: 0.3s; cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px;}
    .upload-box:hover { border-color: var(--brand-blue); background: rgba(59, 130, 246, 0.02); }
    .upload-box input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; z-index: 10; width: 100%; height: 100%;}
    
    .preview-img { max-width: 120px; max-height: 120px; object-fit: contain; z-index: 2; position: relative; margin-bottom: 10px; border-radius: 8px;}
    .upload-text { font-size: 13px; font-weight: 700; color: var(--brand-blue); margin-top: 10px; display: flex; align-items: center; gap: 6px;}
    .upload-hint { font-size: 11px; color: var(--text-muted); margin-top: 4px; line-height: 1.4;}

    /* =========================================================
       5. BUTTONS
       ========================================================= */
    .btn-solid { background: var(--brand-orange); color: #fff; border: none; padding: 12px 24px; border-radius: var(--radius-md); font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 10px rgba(249, 115, 22, 0.2); width: 100%;}
    .btn-solid:hover { background: var(--brand-dark); transform: translateY(-2px); box-shadow: 0 6px 15px rgba(249, 115, 22, 0.3); }
    
    .btn-outline { background: var(--bg-card); color: var(--text-strong); border: 1px solid var(--border-hard); padding: 12px 24px; border-radius: var(--radius-md); font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.3s; display: none; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 10px;}
    .btn-outline:hover { background: var(--bg-input); }

    /* =========================================================
       6. CATALOG LIST (CLEAN ROW DATA)
       ========================================================= */
    .list-container { display: flex; flex-direction: column; gap: 10px; max-height: 600px; overflow-y: auto; padding-right: 5px;}
    .list-container::-webkit-scrollbar { width: 6px; }
    .list-container::-webkit-scrollbar-thumb { background: var(--border-hard); border-radius: 10px; }
    
    .list-item { display: flex; align-items: center; gap: 16px; padding: 16px; border: 1px solid var(--border-soft); border-radius: var(--radius-md); background: var(--bg-card); transition: 0.2s;}
    .list-item:hover { border-color: var(--brand-orange); box-shadow: var(--shadow-sm); transform: scale(1.01);}
    
    .item-thumb { width: 60px; height: 60px; border-radius: 10px; background: var(--bg-input); display: flex; align-items: center; justify-content: center; font-size: 28px; color: var(--text-muted); border: 1px solid var(--border-soft); overflow: hidden; flex-shrink: 0;}
    .item-thumb img { width: 100%; height: 100%; object-fit: cover; }
    
    .item-info { flex: 1; min-width: 0; /* To allow text truncation */ }
    .item-cat { font-size: 10px; font-weight: 800; color: var(--brand-orange); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;}
    .item-title { font-size: 14px; font-weight: 700; color: var(--text-strong); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px;}
    .item-price { font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 700; color: #10b981; display: flex; align-items: center; gap: 6px;}
    .item-price del { color: var(--text-muted); font-size: 11px; font-weight: 500;}

    .item-actions { display: flex; gap: 6px; flex-shrink: 0;}
    .btn-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: 0.2s; border: none; cursor: pointer;}
    .btn-edit { background: rgba(59, 130, 246, 0.1); color: var(--brand-blue); }
    .btn-edit:hover { background: var(--brand-blue); color: #fff; }
    .btn-del { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .btn-del:hover { background: #ef4444; color: #fff; }
</style>

<div class="header-section">
    <h1>Sistem Induk & Identitas</h1>
    <p>Kelola profil utama perusahaan dan etalase produk untuk Landing Page publik Anda.</p>
</div>

<form action="<?= base_url('/setting/update_company') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="company-grid">
        
        <div class="pro-card">
            <div style="font-weight: 800; color: var(--text-strong); margin-bottom: 15px; font-size: 15px;">Logo Sistem</div>
            <div class="upload-box" style="flex: 1;">
                <img id="compLogoPreview" src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>" class="preview-img" alt="Logo">
                <input type="file" name="logo_file" accept="image/png, image/jpeg" onchange="previewImage(this, 'compLogoPreview', 'compUploadText')">
                <div class="upload-text" id="compUploadText"><i class="ph-bold ph-upload-simple"></i> Ganti Logo</div>
                <div class="upload-hint">PNG Transparan<br>Maks. 2MB (1:1)</div>
            </div>
        </div>

        <div class="pro-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div style="font-weight: 800; color: var(--text-strong); font-size: 15px;">Informasi Operasional</div>
                <button type="submit" class="btn-solid" style="width: auto; padding: 10px 20px;" onclick="this.innerHTML='<i class=\'ph-bold ph-spinner-gap ph-spin\'></i> Menyimpan...';">
                    <i class="ph-bold ph-check"></i> Simpan Profil
                </button>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Nama Aplikasi <span>(Singkat)</span></label>
                    <div class="input-wrapper">
                        <div class="input-icon"><i class="ph-duotone ph-app-window"></i></div>
                        <input type="text" name="app_name" value="<?= esc($company['app_name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Perusahaan <span>(Resmi)</span></label>
                    <div class="input-wrapper">
                        <div class="input-icon"><i class="ph-duotone ph-buildings"></i></div>
                        <input type="text" name="company_name" value="<?= esc($company['company_name'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">WhatsApp Resmi <span>(Tanpa spasi)</span></label>
                <div class="input-wrapper">
                    <div class="input-icon"><i class="ph-duotone ph-whatsapp-logo"></i></div>
                    <input type="text" name="phone" value="<?= esc($company['phone'] ?? '') ?>" required style="font-family: monospace;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Alamat Lengkap <span>(Tampil di Kop Cetak)</span></label>
                <div class="input-wrapper" style="align-items: flex-start;">
                    <div class="input-icon" style="padding-top: 12px;"><i class="ph-duotone ph-map-pin"></i></div>
                    <textarea name="address" rows="2" required><?= esc($company['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="section-title">
    <i class="ph-fill ph-storefront"></i> Etalase Landing Page
</div>

<div class="catalog-grid">
    
    <div class="pro-card" id="catalog-form-area">
        <div style="font-weight: 800; color: var(--text-strong); margin-bottom: 20px; font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph-fill ph-plus-circle" id="formCatalogIcon" style="color: #10b981; font-size: 20px;"></i>
            <span id="formCatalogTitle">Tambah Produk Baru</span>
        </div>

        <form action="<?= base_url('/setting/store_catalog') ?>" method="post" id="catalogForm" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label class="form-label">Nama Produk / Varian</label>
                <div class="input-wrapper">
                    <div class="input-icon"><i class="ph-duotone ph-package"></i></div>
                    <input type="text" name="product_name" id="in_product_name" required placeholder="Contoh: Noric WR 155 Standar">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <div class="input-wrapper"><input type="text" name="category" id="in_category" required placeholder="Sport 150cc" style="padding-left: 15px;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Label Promo</label>
                    <div class="input-wrapper"><input type="text" name="badge_text" id="in_badge_text" placeholder="NEW ARRIVAL" style="padding-left: 15px;"></div>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Harga Asli <span>(Coret)</span></label>
                    <div class="input-wrapper">
                        <div class="input-addon">Rp</div>
                        <input type="text" name="discount_price" id="in_discount_price" onkeyup="formatRupiah(this)" style="font-family: monospace;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual Valid</label>
                    <div class="input-wrapper" style="border-color: #10b981;">
                        <div class="input-addon" style="color: #10b981; background: rgba(16, 185, 129, 0.05);">Rp</div>
                        <input type="text" name="price" id="in_price" required onkeyup="formatRupiah(this)" style="font-weight: 800; color: #10b981; font-family: monospace;">
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Ikon Default</label>
                    <div class="input-wrapper">
                        <div class="input-icon"><i class="ph-duotone ph-motorcycle"></i></div>
                        <select name="icon_class" id="in_icon_class" required>
                            <option value="ph-motorcycle">Motor Sport</option>
                            <option value="ph-moped">Motor Bebek</option>
                            <option value="ph-scooter">Motor Matic</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Atau Foto Asli <span>(Opsional)</span></label>
                    <div class="input-wrapper" style="padding: 2px;">
                        <input type="file" name="product_image" id="in_product_image" accept="image/*" style="font-size: 12px; padding: 8px;">
                    </div>
                    <div id="editImageHint" style="font-size: 10px; color: var(--brand-blue); margin-top: 4px; display: none;">* Kosongkan jika tidak ubah foto.</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Spesifikasi Utama <span>(Pisahkan koma)</span></label>
                <div class="input-wrapper">
                    <div class="input-icon"><i class="ph-duotone ph-list-checks"></i></div>
                    <input type="text" name="specs" id="in_specs" placeholder="Las Argon, PNP, Suara Bass">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Link Shopee</label>
                    <div class="input-wrapper">
                        <div class="input-icon"><i class="ph-duotone ph-shopping-bag"></i></div>
                        <input type="url" name="shopee_link" id="in_shopee_link" required placeholder="https://shopee...">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">WA Grosir</label>
                    <div class="input-wrapper">
                        <div class="input-icon"><i class="ph-duotone ph-whatsapp-logo"></i></div>
                        <input type="text" name="wa_link" id="in_wa_link" required placeholder="wa.me/628...">
                    </div>
                </div>
            </div>

            <div style="margin-top: 10px;">
                <button type="submit" class="btn-solid" id="btnCatalogSubmit">
                    <i class="ph-bold ph-plus"></i> Simpan Produk
                </button>
                <button type="button" class="btn-outline" id="btnCancelEdit" onclick="resetForm()">
                    <i class="ph-bold ph-x"></i> Batal Edit
                </button>
            </div>
        </form>
    </div>

    <div class="pro-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="font-weight: 800; color: var(--text-strong); font-size: 15px;">Daftar Produk Tayang</div>
            <div style="font-size: 12px; font-weight: 700; color: var(--brand-orange); background: rgba(249, 115, 22, 0.1); padding: 4px 10px; border-radius: 6px;">
                <?= count($catalogs ?? []) ?> Item
            </div>
        </div>

        <div class="list-container">
            <?php if(empty($catalogs)): ?>
                <div style="text-align: center; padding: 50px 20px; color: var(--text-muted); border: 2px dashed var(--border-soft); border-radius: var(--radius-md);">
                    <i class="ph-duotone ph-package" style="font-size: 40px; margin-bottom: 10px; opacity: 0.5;"></i>
                    <div style="font-weight: 700; font-size: 14px;">Belum ada etalase.</div>
                </div>
            <?php else: ?>
                <?php foreach($catalogs as $c): ?>
                    <div class="list-item">
                        <div class="item-thumb">
                            <?php if(!empty($c['product_image'])): ?>
                                <img src="<?= base_url('uploads/catalogs/' . esc($c['product_image'])) ?>" alt="img">
                            <?php else: ?>
                                <i class="ph-duotone <?= esc($c['icon_class'] ?: 'ph-motorcycle') ?>"></i>
                            <?php endif; ?>
                        </div>
                        
                        <div class="item-info">
                            <div class="item-cat"><?= esc($c['category']) ?></div>
                            <div class="item-title" title="<?= esc($c['product_name']) ?>"><?= esc($c['product_name']) ?></div>
                            <div class="item-price">
                                Rp <?= number_format($c['price'], 0, ',', '.') ?>
                                <?php if($c['discount_price'] > 0): ?>
                                    <del>Rp <?= number_format($c['discount_price'], 0, ',', '.') ?></del>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="item-actions">
                            <button type="button" class="btn-icon btn-edit" onclick='startEdit(<?= json_encode($c) ?>)' title="Edit">
                                <i class="ph-bold ph-pencil-simple"></i>
                            </button>
                            <a href="<?= base_url('/setting/delete_catalog/'.$c['id']) ?>" onclick="return confirm('Hapus produk ini dari tayangan publik?')" class="btn-icon btn-del" title="Hapus">
                                <i class="ph-bold ph-trash"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // --- 1. DYNAMIC IMAGE PREVIEW ---
    function previewImage(input, previewId, textId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
                let fileName = input.files[0].name;
                if(fileName.length > 15) fileName = fileName.substring(0, 12) + '...';
                
                let textEl = document.getElementById(textId);
                textEl.innerHTML = `<i class="ph-bold ph-check-circle" style="color: #10b981;"></i> ${fileName}`;
                textEl.style.color = "#10b981";
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // --- 2. RUPIAH FORMATTER ---
    function formatRupiah(angka) {
        var val = angka.value.replace(/[^,\d]/g, '').toString();
        var split = val.split(','), sisa = split[0].length % 3, rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        angka.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    }

    function toCurrency(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    // --- 3. DYNAMIC EDIT FORM LOGIC ---
    function startEdit(data) {
        document.getElementById('catalog-form-area').scrollIntoView({ behavior: 'smooth', block: 'start' });

        const form = document.getElementById('catalogForm');
        form.action = "<?= base_url('/setting/update_catalog/') ?>" + data.id;
        
        document.getElementById('formCatalogTitle').innerText = "Edit: " + data.product_name;
        document.getElementById('formCatalogIcon').className = "ph-fill ph-pencil-simple";
        document.getElementById('formCatalogIcon').style.color = "#3b82f6";
        
        document.getElementById('in_product_name').value = data.product_name;
        document.getElementById('in_category').value = data.category;
        document.getElementById('in_badge_text').value = data.badge_text;
        document.getElementById('in_discount_price').value = data.discount_price > 0 ? toCurrency(data.discount_price) : '';
        document.getElementById('in_price').value = toCurrency(data.price);
        document.getElementById('in_specs').value = data.specs;
        document.getElementById('in_icon_class').value = data.icon_class || 'ph-motorcycle';
        document.getElementById('in_wa_link').value = data.wa_link;
        document.getElementById('in_shopee_link').value = data.shopee_link;

        document.getElementById('in_product_image').value = '';
        document.getElementById('editImageHint').style.display = 'block';

        const btnSubmit = document.getElementById('btnCatalogSubmit');
        btnSubmit.innerHTML = '<i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan';
        btnSubmit.style.background = "var(--brand-blue)";
        
        document.getElementById('btnCancelEdit').style.display = 'flex';
    }

    function resetForm() {
        const form = document.getElementById('catalogForm');
        form.action = "<?= base_url('/setting/store_catalog') ?>";
        form.reset();

        document.getElementById('formCatalogTitle').innerText = "Tambah Produk Baru";
        document.getElementById('formCatalogIcon').className = "ph-fill ph-plus-circle";
        document.getElementById('formCatalogIcon').style.color = "#10b981";

        document.getElementById('editImageHint').style.display = 'none';

        const btnSubmit = document.getElementById('btnCatalogSubmit');
        btnSubmit.innerHTML = '<i class="ph-bold ph-plus"></i> Simpan Produk';
        btnSubmit.style.background = "var(--brand-orange)";

        document.getElementById('btnCancelEdit').style.display = 'none';
    }
</script>

<?= $this->endSection() ?>