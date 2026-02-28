<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- BIGSELLER STYLE LAYOUT --- */
    .page-header { margin-bottom: 20px; }
    .page-title h1 { font-size: 22px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }
    
    .publish-container { max-width: 900px; margin: 0 auto; padding-bottom: 100px; /* Ruang untuk sticky footer */ }
    
    .bs-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 12px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
    .bs-card-header { padding: 15px 20px; border-bottom: 1px solid var(--border-subtle); font-weight: 800; color: var(--text-main); font-size: 15px; background: rgba(0,0,0,0.01); display: flex; align-items: center; gap: 8px;}
    .bs-card-body { padding: 20px; }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
    .form-group label span.req { color: #ef4444; margin-left: 2px;}
    
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 8px; font-size: 13px; color: var(--text-main); font-family: inherit; transition: 0.2s;}
    .form-control:focus { border-color: #f97316; box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1); outline: none;}
    textarea.form-control { min-height: 120px; resize: vertical; }

    .input-group { display: flex; align-items: stretch; }
    .input-group-text { background: var(--bg-base); border: 1px solid var(--border-subtle); border-right: none; padding: 0 15px; display: flex; align-items: center; border-radius: 8px 0 0 8px; font-weight: 700; color: var(--text-muted); font-size: 13px;}
    .input-group .form-control { border-radius: 0 8px 8px 0; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }

    /* Image Upload Area */
    .image-upload-box { border: 2px dashed var(--border-subtle); border-radius: 12px; padding: 40px 20px; text-align: center; cursor: pointer; transition: 0.2s; background: var(--bg-base); position: relative; overflow: hidden;}
    .image-upload-box:hover { border-color: #f97316; background: rgba(249, 115, 22, 0.02);}
    .image-upload-box input[type="file"] { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .upload-icon { font-size: 40px; color: var(--text-muted); margin-bottom: 10px;}
    .upload-text { font-size: 13px; font-weight: 700; color: var(--text-main); }
    .upload-hint { font-size: 11px; color: var(--text-muted); margin-top: 5px; }

    /* STICKY FOOTER (Ciri Khas BigSeller) */
    .sticky-footer { position: fixed; bottom: 0; left: 0; right: 0; background: var(--bg-surface); border-top: 1px solid var(--border-subtle); padding: 15px 40px; display: flex; justify-content: flex-end; gap: 15px; box-shadow: 0 -4px 20px rgba(0,0,0,0.05); z-index: 100;}
    
    .btn-cancel { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 12px 24px; border-radius: 8px; font-weight: 700; cursor: pointer; text-decoration: none;}
    .btn-publish { background: #f97316; color: #fff; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 800; cursor: pointer; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); transition: 0.2s; display: flex; align-items: center; gap: 8px;}
    .btn-publish:hover { background: #ea580c; transform: translateY(-2px);}
</style>

<div class="publish-container">
    <div class="page-header">
        <div class="page-title">
            <h1><i class="ph ph-upload-simple" style="color: #f97316;"></i> Publish Produk Baru</h1>
            <p>Toko Target: <b style="color: #f97316;"><?= esc($shop['shop_name']) ?></b></p>
        </div>
    </div>

    <form action="<?= base_url('/shopee/store_product/'.$shop['shop_id']) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="bs-card">
            <div class="bs-card-header"><i class="ph ph-info"></i> Informasi Dasar</div>
            <div class="bs-card-body">
                <div class="form-group">
                    <label>Nama Produk <span class="req">*</span></label>
                    <input type="text" name="item_name" class="form-control" placeholder="Contoh: Knalpot Noric WR155 Standar Racing" required maxlength="120">
                    <div style="font-size: 10px; color: var(--text-muted); text-align: right; margin-top: 4px;">Max 120 karakter</div>
                </div>

                <div class="form-group">
                    <label>Kategori Produk (ID Shopee) <span class="req">*</span></label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="ph ph-list-dashes"></i></div>
                        <input type="number" name="category_id" class="form-control" value="100115" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Deskripsi Produk <span class="req">*</span></label>
                    <textarea name="description" class="form-control" placeholder="Jelaskan spesifikasi knalpot, bahan (stainless/galvanis), inlet, dan kelengkapan paket..." required></textarea>
                </div>
            </div>
        </div>

        <div class="bs-card">
            <div class="bs-card-header"><i class="ph ph-image"></i> Media Produk</div>
            <div class="bs-card-body">
                <div class="image-upload-box">
                    <input type="file" name="product_image" accept="image/jpeg, image/png" required>
                    <i class="ph ph-image upload-icon"></i>
                    <div class="upload-text">Klik atau Tarik Foto Knalpot ke sini</div>
                    <div class="upload-hint">Format JPG/PNG, maks 2MB. Resolusi disarankan 1024x1024.</div>
                </div>
            </div>
        </div>

        <div class="bs-card">
            <div class="bs-card-header"><i class="ph ph-currency-circle-dollar"></i> Harga & Manajemen Stok</div>
            <div class="bs-card-body">
                <div class="grid-3">
                    <div class="form-group">
                        <label>SKU Induk (Kode Gudang) <span class="req">*</span></label>
                        <input type="text" name="item_sku" class="form-control" placeholder="Misal: NRC-WR155-SR" required style="font-family: monospace; text-transform: uppercase;">
                    </div>
                    <div class="form-group">
                        <label>Harga Jual <span class="req">*</span></label>
                        <div class="input-group">
                            <div class="input-group-text">Rp</div>
                            <input type="number" name="price" class="form-control" placeholder="0" required min="1000">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Stok Awal <span class="req">*</span></label>
                        <input type="number" name="stock" class="form-control" placeholder="0" required min="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="bs-card">
            <div class="bs-card-header"><i class="ph ph-truck"></i> Pengiriman (Berat & Dimensi)</div>
            <div class="bs-card-body">
                <div class="form-group">
                    <label>Berat Paket <span class="req">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.1" name="weight" class="form-control" placeholder="Misal: 1.5" required>
                        <div class="input-group-text" style="border-radius: 0 8px 8px 0; border-left: none; border-right: 1px solid var(--border-subtle);">KG</div>
                    </div>
                </div>

                <label style="font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; display: block;">Ukuran Paket setelah dibungkus kardus</label>
                <div class="grid-3">
                    <div class="input-group">
                        <div class="input-group-text">Panjang</div>
                        <input type="number" name="length" class="form-control" placeholder="CM" required>
                    </div>
                    <div class="input-group">
                        <div class="input-group-text">Lebar</div>
                        <input type="number" name="width" class="form-control" placeholder="CM" required>
                    </div>
                    <div class="input-group">
                        <div class="input-group-text">Tinggi</div>
                        <input type="number" name="height" class="form-control" placeholder="CM" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky-footer">
            <a href="<?= base_url('/shopee/products/'.$shop['shop_id']) ?>" class="btn-cancel">Batal</a>
            <button type="submit" class="btn-publish">
                <i class="ph ph-paper-plane-tilt"></i> Simpan & Publish ke Shopee
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>