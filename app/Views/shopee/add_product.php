<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. CORE LAYOUT & TYPOGRAPHY
       ========================================================= */
    .page-header { margin-bottom: 25px; display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(234, 88, 12, 0.05)); color: #f97316; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid rgba(249, 115, 22, 0.2);}
    .page-title h1 { font-size: 24px; font-weight: 900; color: var(--text-main); margin: 0 0 2px 0; letter-spacing: -0.5px; }
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 500; margin: 0; display: flex; align-items: center; gap: 6px;}
    
    .publish-container { max-width: 900px; margin: 0 auto; padding-bottom: 120px; /* Ruang untuk sticky footer */ }
    
    /* =========================================================
       2. BENTO CARDS FOR FORM SECTIONS
       ========================================================= */
    .bs-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.02); overflow: hidden; transition: all 0.3s;}
    .bs-card:focus-within { border-color: rgba(249, 115, 22, 0.3); box-shadow: 0 10px 30px rgba(249, 115, 22, 0.05);}
    
    .bs-card-header { padding: 20px 25px; border-bottom: 1px dashed var(--border-subtle); font-weight: 900; color: var(--text-main); font-size: 15px; background: rgba(0,0,0,0.01); display: flex; align-items: center; gap: 10px;}
    html.dark .bs-card-header { background: rgba(255,255,255,0.01); }
    .bs-card-header i { color: #f97316; font-size: 18px; background: rgba(249, 115, 22, 0.1); padding: 4px; border-radius: 8px;}
    
    .bs-card-body { padding: 25px; }

    /* =========================================================
       3. FORM ELEMENTS & INPUTS
       ========================================================= */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    .form-group label span.req { color: #ef4444; margin-left: 2px; font-size: 14px;}
    
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 14px 18px; border-radius: 12px; font-size: 14px; font-weight: 600; color: var(--text-main); font-family: inherit; transition: 0.3s; outline: none; appearance: none;}
    .form-control:focus { border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15); background: var(--bg-surface);}
    
    textarea.form-control { min-height: 140px; resize: vertical; line-height: 1.6; }

    .input-group { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: 0.3s;}
    .input-group:focus-within { border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15); background: var(--bg-surface);}
    .input-group-text { background: rgba(0,0,0,0.02); padding: 0 18px; display: flex; align-items: center; font-weight: 800; color: var(--text-muted); font-size: 13px; border-right: 1px solid var(--border-subtle);}
    .input-group .form-control { border: none; border-radius: 0; box-shadow: none; background: transparent;}

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }

    /* =========================================================
       4. DRAG & DROP IMAGE UPLOAD
       ========================================================= */
    .image-upload-box { border: 2px dashed var(--border-subtle); border-radius: 16px; padding: 50px 20px; text-align: center; cursor: pointer; transition: all 0.3s ease; background: var(--bg-base); position: relative; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center;}
    .image-upload-box:hover { border-color: #f97316; background: rgba(249, 115, 22, 0.02); transform: translateY(-2px);}
    .image-upload-box input[type="file"] { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;}
    
    .upload-icon { font-size: 48px; color: var(--text-muted); margin-bottom: 12px; transition: 0.3s;}
    .image-upload-box:hover .upload-icon { color: #f97316; transform: scale(1.1);}
    
    .upload-text { font-size: 14px; font-weight: 800; color: var(--text-main); margin-bottom: 4px;}
    .upload-hint { font-size: 11px; font-weight: 600; color: var(--text-muted); }

    /* =========================================================
       5. STICKY FOOTER (ACTION BAR)
       ========================================================= */
    .sticky-footer { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); border-top: 1px solid var(--border-subtle); padding: 20px 40px; display: flex; justify-content: flex-end; gap: 15px; box-shadow: 0 -10px 30px rgba(0,0,0,0.05); z-index: 100;}
    html.dark .sticky-footer { background: rgba(24, 24, 27, 0.9); }
    
    .btn-cancel { background: var(--bg-base); color: var(--text-main); border: 1px dashed var(--border-subtle); padding: 14px 28px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; text-decoration: none; transition: 0.3s;}
    .btn-cancel:hover { background: var(--bg-surface); border-color: var(--text-muted); transform: translateY(-2px);}
    
    .btn-publish { background: linear-gradient(135deg, #f97316, #ea580c); color: #fff; border: none; padding: 14px 35px; border-radius: 12px; font-weight: 900; font-size: 15px; cursor: pointer; box-shadow: 0 8px 20px -5px rgba(249, 115, 22, 0.5); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); display: flex; align-items: center; gap: 10px;}
    .btn-publish:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -5px rgba(249, 115, 22, 0.6);}
    .btn-publish:disabled { background: var(--bg-base); color: var(--text-muted); box-shadow: none; cursor: not-allowed; transform: none;}
</style>

<div class="publish-container">
    
    <div class="page-header">
        <div class="title-icon"><i class="ph-fill ph-upload-simple"></i></div>
        <div class="page-title">
            <h1>Publish Produk Baru</h1>
            <p>Target Publikasi: <span style="background: rgba(249, 115, 22, 0.1); color: #f97316; padding: 2px 8px; border-radius: 6px; font-weight: 800; margin-left: 4px; border: 1px solid rgba(249, 115, 22, 0.2);"><i class="ph-fill ph-storefront"></i> <?= esc($shop['shop_name']) ?></span></p>
        </div>
    </div>

    <form id="formProduct" action="<?= base_url('/shopee/store_product/'.$shop['shop_id']) ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="bs-card">
            <div class="bs-card-header"><i class="ph-bold ph-info"></i> Informasi Dasar</div>
            <div class="bs-card-body">
                <div class="form-group">
                    <label>Nama Lengkap Produk <span class="req">*</span></label>
                    <input type="text" name="item_name" class="form-control" placeholder="Contoh: Knalpot Noric WR155 Standar Racing Full System" required maxlength="120" autocomplete="off">
                    <div style="font-size: 10px; font-weight: 700; color: var(--text-muted); text-align: right; margin-top: 6px;">Maksimal 120 karakter</div>
                </div>

                <div class="form-group">
                    <label>ID Kategori Shopee <span class="req">*</span></label>
                    <div class="input-group">
                        <div class="input-group-text"><i class="ph-bold ph-list-dashes"></i></div>
                        <input type="number" name="category_id" class="form-control" value="100115" required>
                    </div>
                    <div style="font-size: 10px; font-weight: 600; color: var(--text-muted); margin-top: 6px;"><i class="ph-bold ph-info"></i> 100115 adalah ID Kategori Otomotif > Aksesoris Motor.</div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Deskripsi Produk <span class="req">*</span></label>
                    <textarea name="description" class="form-control" placeholder="Jelaskan secara detail spesifikasi knalpot, material (stainless/galvanis), ukuran inlet, kelengkapan paket, dan jenis motor yang kompatibel..." required></textarea>
                </div>
            </div>
        </div>

        <div class="bs-card">
            <div class="bs-card-header"><i class="ph-bold ph-image"></i> Media & Foto Utama</div>
            <div class="bs-card-body">
                <div class="image-upload-box" id="uploadBox">
                    <input type="file" name="product_image" id="fileInput" accept="image/jpeg, image/png" required onchange="showFileName(this)">
                    <i class="ph-fill ph-image upload-icon" id="uploadIcon"></i>
                    <div class="upload-text" id="uploadText">Klik atau Tarik Foto Produk ke Sini</div>
                    <div class="upload-hint">Format JPG/PNG, maks 2MB. Resolusi terbaik 1024x1024.</div>
                </div>
            </div>
        </div>

        <div class="bs-card">
            <div class="bs-card-header"><i class="ph-bold ph-currency-circle-dollar"></i> Harga & Manajemen Stok</div>
            <div class="bs-card-body">
                <div class="grid-3">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>SKU (Kode Gudang) <span class="req">*</span></label>
                        <input type="text" name="item_sku" class="form-control" placeholder="Misal: NRC-WR155" required style="font-family: 'Space Mono', monospace; text-transform: uppercase;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Harga Jual Publik <span class="req">*</span></label>
                        <div class="input-group">
                            <div class="input-group-text">Rp</div>
                            <input type="number" name="price" class="form-control" placeholder="0" required min="1000" style="font-family: 'Space Mono', monospace; font-weight: 900;">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Stok Awal Alokasi <span class="req">*</span></label>
                        <input type="number" name="stock" class="form-control" placeholder="0" required min="0" style="font-family: 'Space Mono', monospace; font-weight: 900;">
                    </div>
                </div>
            </div>
        </div>

        <div class="bs-card">
            <div class="bs-card-header"><i class="ph-bold ph-truck"></i> Pengiriman & Logistik</div>
            <div class="bs-card-body">
                <div class="form-group">
                    <label>Berat Total Paket <span class="req">*</span></label>
                    <div class="input-group" style="max-width: 300px;">
                        <input type="number" step="0.1" name="weight" class="form-control" placeholder="Misal: 1.5" required>
                        <div class="input-group-text" style="border-right: none; border-left: 1px solid var(--border-subtle);">KG</div>
                    </div>
                </div>

                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; border-top: 1px dashed var(--border-subtle); padding-top: 20px;">
                    Dimensi Paket (Setelah Dibungkus Kardus)
                </label>
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
            <button type="submit" id="btnSubmit" class="btn-publish">
                <i class="ph-bold ph-rocket-launch" style="font-size: 20px;"></i> <span>Publish ke Shopee</span>
            </button>
        </div>
    </form>
</div>

<script>
    // --- MICRO-INTERACTION: NAMA FILE GAMBAR ---
    function showFileName(input) {
        const text = document.getElementById('uploadText');
        const icon = document.getElementById('uploadIcon');
        const box = document.getElementById('uploadBox');
        
        if (input.files && input.files[0]) {
            text.innerHTML = `<span style="color: #f97316;">${input.files[0].name}</span> siap diunggah.`;
            icon.className = "ph-fill ph-check-circle upload-icon";
            icon.style.color = "#f97316";
            box.style.borderColor = "#f97316";
            box.style.background = "rgba(249, 115, 22, 0.05)";
        }
    }

    // --- AJAX SUBMISSION UNTUK GLOBAL TOAST (OPSIONAL) ---
    // Jika backend controller 'store_product' Anda mengembalikan JSON, 
    // script ini akan mencegah reload dan menampilkan toast otomatis.
    document.getElementById('formProduct').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSubmit');
        const btnText = btn.querySelector('span');
        const btnIcon = btn.querySelector('i');
        
        // Cek apakah form valid secara HTML5
        if(!this.checkValidity()) return;

        // Tampilkan loading
        btn.disabled = true;
        btnText.innerText = "Mengirim Data API...";
        btnIcon.className = "ph-bold ph-spinner-gap ph-spin";

        /*
        // UNCOMMENT BLOK INI JIKA CONTROLLER MENGEMBALIKAN JSON AJAX
        e.preventDefault(); 
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Produk berhasil di-publish ke Shopee!");
                setTimeout(() => { window.location.href = "<?= base_url('/shopee/products/'.$shop['shop_id']) ?>"; }, 1500);
            } else {
                if(typeof window.showGlobalToast === 'function') window.showGlobalToast(data.message, true);
                btn.disabled = false; btnText.innerText = "Publish ke Shopee"; btnIcon.className = "ph-bold ph-rocket-launch";
            }
        }).catch(err => {
            if(typeof window.showGlobalToast === 'function') window.showGlobalToast("Koneksi gagal", true);
            btn.disabled = false; btnText.innerText = "Publish ke Shopee"; btnIcon.className = "ph-bold ph-rocket-launch";
        });
        */
    });
</script>

<?= $this->endSection() ?>