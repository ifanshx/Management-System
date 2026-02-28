<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 25px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 30px; box-shadow: var(--shadow-card); }
    .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px dashed var(--border-subtle); }
    .icon-wrapper { width: 40px; height: 40px; border-radius: 10px; background: var(--accent-light); color: var(--accent-main); display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .card-header h3 { font-size: 16px; font-weight: 800; color: var(--text-main); margin: 0; }

    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: all 0.3s; }
    .input-wrapper:focus-within { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }
    .input-wrapper input, .input-wrapper textarea { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 16px; font-size: 14px; font-weight: 600; outline: none; font-family: inherit; resize: none; width: 100%;}
    
    .btn-submit { background: var(--accent-main); color: #fff; padding: 14px 30px; border: none; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 12px var(--accent-light); display: inline-flex; align-items: center; gap: 8px;}
    .btn-submit:hover { transform: translateY(-3px); filter: brightness(1.1); }

    .logo-preview { width: 120px; height: 120px; border-radius: 16px; object-fit: contain; border: 2px dashed var(--border-subtle); padding: 5px; background: var(--bg-base); margin-bottom: 15px;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Identitas Perusahaan</h1>
        <p>Atur nama aplikasi, logo, dan profil perusahaan. Perubahan di sini akan mengubah seluruh kop surat, slip gaji, dan tampilan sistem secara global.</p>
    </div>
</div>

<form action="<?= base_url('/setting/update_company') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px; align-items: start;">
        
        <div class="bento-card" style="text-align: center;">
            <div class="card-header" style="justify-content: center;">
                <h3>Logo Sistem / Perusahaan</h3>
            </div>
            
            <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>" alt="Logo Perusahaan" class="logo-preview">
            
            <div class="form-group" style="text-align: left;">
                <label class="form-label">Upload Logo Baru</label>
                <div class="input-wrapper" style="padding: 5px;">
                    <input type="file" name="logo_file" accept="image/png, image/jpeg" style="font-size: 12px; padding: 8px;">
                </div>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Format disarankan: PNG Transparan. Maksimal 2MB.</p>
            </div>
        </div>

        <div class="bento-card">
            <div class="card-header">
                <div class="icon-wrapper"><i class="ph ph-buildings"></i></div>
                <h3>Informasi Perusahaan</h3>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Nama Aplikasi (Singkat)</label>
                    <div class="input-wrapper">
                        <input type="text" name="app_name" value="<?= esc($company['app_name'] ?? '') ?>" placeholder="Contoh: MajuJaya ERP" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Perusahaan Lengkap</label>
                    <div class="input-wrapper">
                        <input type="text" name="company_name" value="<?= esc($company['company_name'] ?? '') ?>" placeholder="Contoh: PT. Maju Jaya Sentosa" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Telepon / WhatsApp</label>
                <div class="input-wrapper">
                    <input type="text" name="phone" value="<?= esc($company['phone'] ?? '') ?>" placeholder="Contoh: 081234567890" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Lengkap Perusahaan (Untuk Kop Surat)</label>
                <div class="input-wrapper">
                    <textarea name="address" rows="3" required><?= esc($company['address'] ?? '') ?></textarea>
                </div>
            </div>

            <div style="text-align: right; margin-top: 30px;">
                <button type="submit" class="btn-submit">
                    <i class="ph ph-check-circle"></i> Simpan Identitas Global
                </button>
            </div>
        </div>

    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if(session()->getFlashdata('success')): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#38bdf8' });
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', text: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444' });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>