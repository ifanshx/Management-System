<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. VARIABLES & PREMIUM TYPOGRAPHY
       ========================================================= */
    :root {
        --brand-orange: #f59e0b; 
        --brand-dark: #d97706;
        --brand-blue: #0ea5e9;
        --brand-blue-dark: #0284c7;
        --bg-input: rgba(0, 0, 0, 0.02);
        --radius-xl: 24px;
        --radius-lg: 16px;
        --radius-md: 12px;
        --shadow-card: 0 10px 40px -15px rgba(0,0,0,0.05);
        --shadow-hover: 0 20px 40px -10px rgba(0,0,0,0.08);
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    html.dark {
        --bg-input: rgba(255, 255, 255, 0.03);
    }

    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 800px; height: 500px; background: radial-gradient(circle, rgba(245, 158, 11, 0.08) 0%, transparent 70%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, transparent 70%); }

    /* =========================================================
       2. PAGE HEADER
       ========================================================= */
    .page-header { position: relative; z-index: 1; margin-bottom: 35px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;}
    .page-title { display: flex; align-items: center; gap: 18px;}
    .title-icon { width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, var(--brand-orange), var(--brand-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4);}
    .page-title h1 { font-size: 32px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; line-height: 1.1;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 6px 0 0 0; letter-spacing: -0.2px;}

    .section-divider { display: flex; align-items: center; gap: 15px; margin: 45px 0 25px; position: relative; z-index: 1;}
    .section-divider::after { content: ''; flex-grow: 1; height: 1px; background: var(--border-subtle); opacity: 0.6;}
    .section-title { font-size: 16px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 10px; background: var(--bg-surface); padding: 8px 16px; border-radius: 100px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); letter-spacing: -0.2px;}
    .section-title i { color: var(--brand-orange); font-size: 20px;}

    /* =========================================================
       3. BENTO CARDS & GRIDS
       ========================================================= */
    .company-grid { display: grid; grid-template-columns: 300px 1fr; gap: 24px; position: relative; z-index: 1;}
    .catalog-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 24px; position: relative; z-index: 1;}
    @media (max-width: 1024px) { .company-grid, .catalog-grid { grid-template-columns: 1fr; } }

    .pro-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-xl); padding: 30px; box-shadow: var(--shadow-card); transition: var(--transition-smooth); display: flex; flex-direction: column;}
    .pro-card:hover { border-color: rgba(245, 158, 11, 0.3); box-shadow: var(--shadow-hover); }
    
    .card-heading { font-weight: 900; color: var(--text-main); font-size: 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;}

    /* =========================================================
       4. MODERN INPUT FIELDS
       ========================================================= */
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-label span { color: var(--brand-orange); font-weight: 700; font-size: 10px; margin-left: 4px; background: rgba(245, 158, 11, 0.1); padding: 2px 6px; border-radius: 4px; text-transform: none; letter-spacing: 0;}
    
    .input-wrapper { display: flex; align-items: center; background: var(--bg-input); border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); overflow: hidden; transition: var(--transition-smooth); }
    .input-wrapper:focus-within { background: var(--bg-surface); border-color: var(--brand-blue); box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15); }
    
    .input-icon { padding: 0 16px; color: var(--text-muted); font-size: 20px; display: flex; align-items: center; transition: 0.3s;}
    .input-wrapper:focus-within .input-icon { color: var(--brand-blue); }
    
    .input-addon { padding: 0 16px; background: rgba(0,0,0,0.03); color: var(--text-muted); font-weight: 800; font-size: 13px; border-right: 1px solid var(--border-subtle); display: flex; align-items: center;}
    html.dark .input-addon { background: rgba(255,255,255,0.05); }
    
    .input-wrapper input, .input-wrapper textarea, .input-wrapper select { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 16px 16px 16px 0; font-size: 14px; font-weight: 600; outline: none; font-family: inherit; width: 100%;}
    .input-wrapper textarea { padding-left: 16px; }
    
    .input-wrapper select { cursor: pointer; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px;}

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    /* =========================================================
       5. PREMIUM UPLOAD ZONE
       ========================================================= */
    .upload-box { border: 2px dashed var(--border-subtle); border-radius: var(--radius-lg); padding: 30px 20px; text-align: center; background: var(--bg-input); position: relative; transition: var(--transition-smooth); cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 220px;}
    .upload-box:hover { border-color: var(--brand-blue); background: rgba(14, 165, 233, 0.03); }
    .upload-box input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; z-index: 10; width: 100%; height: 100%;}
    
    .preview-img { max-width: 140px; max-height: 140px; object-fit: contain; z-index: 2; position: relative; margin-bottom: 15px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.08); transition: 0.3s;}
    .upload-box:hover .preview-img { transform: scale(1.05); }
    
    .upload-text { font-size: 14px; font-weight: 800; color: var(--brand-blue); margin-top: 10px; display: flex; align-items: center; gap: 8px;}
    .upload-hint { font-size: 11px; font-weight: 600; color: var(--text-muted); margin-top: 6px; line-height: 1.5; background: var(--bg-surface); padding: 4px 10px; border-radius: 100px; border: 1px solid var(--border-subtle);}

    /* =========================================================
       6. SMART BUTTONS
       ========================================================= */
    .btn-solid { background: linear-gradient(135deg, var(--brand-orange), var(--brand-dark)); color: #fff; border: none; padding: 16px 24px; border-radius: var(--radius-lg); font-weight: 800; font-size: 15px; cursor: pointer; transition: var(--transition-smooth); display: inline-flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 8px 20px -6px rgba(245, 158, 11, 0.5); width: 100%;}
    .btn-solid:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(245, 158, 11, 0.6); }
    
    .btn-solid-blue { background: linear-gradient(135deg, var(--brand-blue), var(--brand-blue-dark)); box-shadow: 0 8px 20px -6px rgba(14, 165, 233, 0.5);}
    .btn-solid-blue:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -6px rgba(14, 165, 233, 0.6); }
    
    .btn-outline { background: var(--bg-surface); color: var(--text-main); border: 1px dashed var(--border-subtle); padding: 16px 24px; border-radius: var(--radius-lg); font-weight: 800; font-size: 15px; cursor: pointer; transition: var(--transition-smooth); display: none; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 12px;}
    .btn-outline:hover { background: rgba(239, 68, 68, 0.05); color: #ef4444; border-color: rgba(239, 68, 68, 0.3); border-style: solid;}

    /* =========================================================
       7. MODERN CATALOG LIST (APPLE MUSIC STYLE)
       ========================================================= */
    .list-container { display: flex; flex-direction: column; gap: 12px; max-height: 650px; overflow-y: auto; padding-right: 8px;}
    .list-container::-webkit-scrollbar { width: 4px; }
    .list-container::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 10px; }
    
    .list-item { display: flex; align-items: center; gap: 18px; padding: 16px; border: 1px solid var(--border-subtle); border-radius: var(--radius-lg); background: var(--bg-surface); transition: var(--transition-smooth); position: relative;}
    .list-item:hover { border-color: rgba(14, 165, 233, 0.3); box-shadow: 0 10px 25px -5px rgba(14, 165, 233, 0.1); transform: translateX(4px); z-index: 2;}
    
    .item-thumb { width: 64px; height: 64px; border-radius: 14px; background: var(--bg-input); display: flex; align-items: center; justify-content: center; font-size: 32px; color: var(--text-muted); border: 1px solid var(--border-subtle); overflow: hidden; flex-shrink: 0; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);}
    .item-thumb img { width: 100%; height: 100%; object-fit: cover; }
    
    .item-info { flex: 1; min-width: 0; }
    .item-cat { font-size: 10px; font-weight: 900; color: var(--brand-orange); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;}
    .item-title { font-size: 15px; font-weight: 800; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 6px; letter-spacing: -0.3px;}
    .item-price { font-family: 'Space Mono', monospace; font-size: 14px; font-weight: 900; color: #10b981; display: flex; align-items: center; gap: 8px;}
    .item-price del { color: var(--text-muted); font-size: 11px; font-weight: 600;}

    .item-actions { display: flex; gap: 8px; flex-shrink: 0; opacity: 0.5; transition: 0.3s;}
    .list-item:hover .item-actions { opacity: 1; }
    
    .btn-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: 0.2s; border: none; cursor: pointer;}
    .btn-edit { background: rgba(59, 130, 246, 0.1); color: var(--brand-blue); }
    .btn-edit:hover { background: var(--brand-blue); color: #fff; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);}
    .btn-del { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .btn-del:hover { background: #ef4444; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);}
</style>

<div class="ambient-glow"></div>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-buildings"></i></div>
        <div>
            <h1>Profil & Etalase Pabrik</h1>
            <p>Kelola identitas utama perusahaan dan susun katalog produk untuk publik.</p>
        </div>
    </div>
</div>

<form action="<?= base_url('/setting/update_company') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="company-grid">
        
        <div class="pro-card">
            <div class="card-heading">Logo Sistem <span>Identitas Visual</span></div>
            <div class="upload-box" style="flex: 1;">
                <img id="compLogoPreview" src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>" class="preview-img" alt="Logo">
                <input type="file" name="logo_file" accept="image/png, image/jpeg" onchange="previewImage(this, 'compLogoPreview', 'compUploadText')">
                <div class="upload-text" id="compUploadText"><i class="ph-bold ph-upload-simple"></i> Ganti Logo</div>
                <div class="upload-hint">PNG/JPG (Maks 2MB, 1:1)</div>
            </div>
        </div>

        <div class="pro-card">
            <div class="card-heading">
                Informasi Operasional Perusahaan
                <button type="submit" class="btn-solid" style="width: auto; padding: 10px 20px; font-size: 13px;" onclick="this.innerHTML='<i class=\'ph-bold ph-spinner-gap ph-spin\'></i> Menyimpan...';">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan Profil
                </button>
            </div>
            
            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Nama Aplikasi <span>Internal</span></label>
                    <div class="input-wrapper">
                        <div class="input-icon"><i class="ph-duotone ph-app-window"></i></div>
                        <input type="text" name="app_name" value="<?= esc($company['app_name'] ?? '') ?>" required placeholder="Contoh: Noric ERP">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Perusahaan <span>Kop Surat</span></label>
                    <div class="input-wrapper">
                        <div class="input-icon"><i class="ph-duotone ph-buildings"></i></div>
                        <input type="text" name="company_name" value="<?= esc($company['company_name'] ?? '') ?>" required placeholder="PT. Nama Perusahaan">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">WhatsApp CS Resmi <span>(Gunakan 628)</span></label>
                <div class="input-wrapper">
                    <div class="input-icon" style="color: #10b981;"><i class="ph-duotone ph-whatsapp-logo"></i></div>
                    <input type="text" name="phone" value="<?= esc($company['phone'] ?? '') ?>" required style="font-family: 'Space Mono', monospace;" placeholder="628123456789">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Alamat Lengkap Pabrik <span>Untuk Faktur & Surat Jalan</span></label>
                <div class="input-wrapper" style="align-items: flex-start;">
                    <div class="input-icon" style="padding-top: 16px;"><i class="ph-duotone ph-map-pin"></i></div>
                    <textarea name="address" rows="2" required placeholder="Jl. Raya Pabrik No. 123..."><?= esc($company['address'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="section-divider">
    <div class="section-title"><i class="ph-fill ph-storefront"></i> Manajemen Katalog Etalase</div>
</div>

<div class="catalog-grid">
    
    <div class="pro-card" id="catalog-form-area" style="position: sticky; top: 20px;">
        <div class="card-heading" style="color: #10b981; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="ph-fill ph-plus-circle" id="formCatalogIcon" style="font-size: 24px;"></i>
                <span id="formCatalogTitle" style="font-size: 18px;">Tambah Etalase Baru</span>
            </div>
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
                    <label class="form-label">Kategori Unit</label>
                    <div class="input-wrapper">
                        <div class="input-icon"><i class="ph-duotone ph-folders"></i></div>
                        <input type="text" name="category" id="in_category" required placeholder="Sport 150cc">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Label Promo <span>Sticker</span></label>
                    <div class="input-wrapper">
                        <div class="input-icon"><i class="ph-duotone ph-ticket"></i></div>
                        <input type="text" name="badge_text" id="in_badge_text" placeholder="NEW ARRIVAL">
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Harga Asli <span>(Coret)</span></label>
                    <div class="input-wrapper">
                        <div class="input-addon">Rp</div>
                        <input type="text" name="discount_price" id="in_discount_price" onkeyup="formatRupiah(this)" style="font-family: 'Space Mono', monospace;" placeholder="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Harga Jual Valid</label>
                    <div class="input-wrapper" style="border-color: #10b981; background: rgba(16, 185, 129, 0.03);">
                        <div class="input-addon" style="color: #10b981; background: transparent;">Rp</div>
                        <input type="text" name="price" id="in_price" required onkeyup="formatRupiah(this)" style="font-weight: 900; color: #10b981; font-family: 'Space Mono', monospace;" placeholder="0">
                    </div>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Ikon Avatar Alternatif</label>
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
                    <label class="form-label">Upload Foto Asli <span>(Opsional)</span></label>
                    <div class="input-wrapper" style="padding: 4px;">
                        <input type="file" name="product_image" id="in_product_image" accept="image/*" style="font-size: 12px; padding: 10px;">
                    </div>
                    <div id="editImageHint" style="font-size: 10px; color: var(--brand-blue); margin-top: 6px; display: none; font-weight: 700; background: rgba(14, 165, 233, 0.1); padding: 4px 8px; border-radius: 6px; width: fit-content;">* Kosongkan jika tidak mengubah foto.</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Spesifikasi Utama <span>(Pisahkan dengan koma)</span></label>
                <div class="input-wrapper">
                    <div class="input-icon"><i class="ph-duotone ph-list-checks"></i></div>
                    <input type="text" name="specs" id="in_specs" placeholder="Las Argon, PNP, Suara Bass Racing">
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Link Checkout Shopee</label>
                    <div class="input-wrapper" style="border-color: rgba(249, 115, 22, 0.4);">
                        <div class="input-icon" style="color: var(--brand-orange);"><i class="ph-duotone ph-shopping-bag"></i></div>
                        <input type="url" name="shopee_link" id="in_shopee_link" required placeholder="https://shopee...">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Link Tanya Grosir (WA)</label>
                    <div class="input-wrapper" style="border-color: rgba(16, 185, 129, 0.4);">
                        <div class="input-icon" style="color: #10b981;"><i class="ph-duotone ph-whatsapp-logo"></i></div>
                        <input type="text" name="wa_link" id="in_wa_link" required placeholder="wa.me/628...">
                    </div>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn-solid" id="btnCatalogSubmit">
                    <i class="ph-bold ph-upload-simple" style="font-size: 18px;"></i> <span>Unggah ke Landing Page</span>
                </button>
                <button type="button" class="btn-outline" id="btnCancelEdit" onclick="resetForm()">
                    <i class="ph-bold ph-x"></i> Batalkan Mode Edit
                </button>
            </div>
        </form>
    </div>

    <div class="pro-card">
        <div class="card-heading">
            Daftar Etalase Tayang
            <div style="font-size: 11px; font-weight: 800; color: var(--brand-blue); background: rgba(14, 165, 233, 0.1); padding: 6px 12px; border-radius: 100px; border: 1px solid rgba(14, 165, 233, 0.2);">
                <i class="ph-fill ph-check-circle"></i> <?= count($catalogs ?? []) ?> Item Aktif
            </div>
        </div>

        <div class="list-container">
            <?php if(empty($catalogs)): ?>
                <div style="text-align: center; padding: 60px 20px; color: var(--text-muted); border: 2px dashed var(--border-subtle); border-radius: var(--radius-md); background: var(--bg-input);">
                    <i class="ph-duotone ph-package" style="font-size: 48px; margin-bottom: 12px; opacity: 0.5;"></i>
                    <div style="font-weight: 800; font-size: 15px; color: var(--text-main);">Belum ada etalase.</div>
                    <p style="font-size: 13px; margin-top: 4px;">Produk yang Anda tambahkan di sebelah kiri akan muncul di sini.</p>
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
                            <button type="button" class="btn-icon btn-edit" onclick='startEdit(<?= json_encode($c) ?>)' title="Edit Konten">
                                <i class="ph-bold ph-pencil-simple"></i>
                            </button>
                            <a href="<?= base_url('/setting/delete_catalog/'.$c['id']) ?>" onclick="return confirm('Peringatan: Menghapus item ini akan menghilangkannya secara permanen dari Landing Page. Lanjutkan?')" class="btn-icon btn-del" title="Hapus Etalase">
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
        // Smooth scroll ke arah form
        document.getElementById('catalog-form-area').scrollIntoView({ behavior: 'smooth', block: 'center' });

        const form = document.getElementById('catalogForm');
        form.action = "<?= base_url('/setting/update_catalog/') ?>" + data.id;
        
        // Ganti Judul & Ikon (Warna Biru untuk mengindikasikan Mode Edit)
        const titleEl = document.getElementById('formCatalogTitle');
        const iconEl = document.getElementById('formCatalogIcon');
        titleEl.innerText = "Edit: " + data.product_name;
        titleEl.style.color = "var(--brand-blue)";
        iconEl.className = "ph-fill ph-pencil-simple";
        iconEl.style.color = "var(--brand-blue)";
        
        // Isi Data ke Form
        document.getElementById('in_product_name').value = data.product_name;
        document.getElementById('in_category').value = data.category;
        document.getElementById('in_badge_text').value = data.badge_text;
        document.getElementById('in_discount_price').value = data.discount_price > 0 ? toCurrency(data.discount_price) : '';
        document.getElementById('in_price').value = toCurrency(data.price);
        document.getElementById('in_specs').value = data.specs;
        document.getElementById('in_icon_class').value = data.icon_class || 'ph-motorcycle';
        document.getElementById('in_wa_link').value = data.wa_link;
        document.getElementById('in_shopee_link').value = data.shopee_link;

        // Reset file input & tampilkan hint
        document.getElementById('in_product_image').value = '';
        document.getElementById('editImageHint').style.display = 'block';

        // Ubah Tombol Submit
        const btnSubmit = document.getElementById('btnCatalogSubmit');
        btnSubmit.innerHTML = '<i class="ph-bold ph-check-circle" style="font-size: 18px;"></i> <span>Simpan Perubahan</span>';
        btnSubmit.className = "btn-solid btn-solid-blue";
        
        // Munculkan tombol Batal
        document.getElementById('btnCancelEdit').style.display = 'flex';
    }

    function resetForm() {
        const form = document.getElementById('catalogForm');
        form.action = "<?= base_url('/setting/store_catalog') ?>";
        form.reset();

        // Kembalikan Judul & Ikon ke Mode Tambah
        const titleEl = document.getElementById('formCatalogTitle');
        const iconEl = document.getElementById('formCatalogIcon');
        titleEl.innerText = "Tambah Etalase Baru";
        titleEl.style.color = "var(--text-main)";
        iconEl.className = "ph-fill ph-plus-circle";
        iconEl.style.color = "#10b981";

        document.getElementById('editImageHint').style.display = 'none';

        // Kembalikan Tombol Submit
        const btnSubmit = document.getElementById('btnCatalogSubmit');
        btnSubmit.innerHTML = '<i class="ph-bold ph-upload-simple" style="font-size: 18px;"></i> <span>Unggah ke Landing Page</span>';
        btnSubmit.className = "btn-solid";

        document.getElementById('btnCancelEdit').style.display = 'none';
    }
</script>

<?= $this->endSection() ?>