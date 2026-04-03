<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ 
                icon: 'success', title: 'Berhasil', text: '<?= session()->getFlashdata('success') ?>', 
                confirmButtonColor: '#10b981', 
                background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b',
                customClass: { popup: 'swal2-custom-radius' }
            });
        <?php endif; ?>
    });
</script>

<style>
    .swal2-custom-radius { border-radius: 24px !important; font-family: 'Plus Jakarta Sans', sans-serif; }
    
    .page-header { margin-bottom: 30px; display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.05)); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(245, 158, 11, 0.2);}
    .page-header h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0; }
    
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 40px; box-shadow: 0 15px 35px -10px rgba(0,0,0,0.05); max-width: 800px; margin: 0 auto; }
    
    .form-group { margin-bottom: 25px; }
    .form-group label { display: block; font-size: 13px; font-weight: 800; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .input-wrapper { display: flex; align-items: center; background: var(--bg-base); border: 2px solid var(--border-subtle); border-radius: 16px; overflow: hidden; transition: 0.3s; }
    .input-wrapper:focus-within { border-color: #f59e0b; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15); background: var(--bg-surface);}
    .input-wrapper i { padding: 0 0 0 20px; color: var(--text-muted); font-size: 20px;}
    .input-wrapper input { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 18px 20px; font-size: 15px; font-weight: 700; outline: none; font-family: 'Space Mono', monospace;}
    
    .btn-submit { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; padding: 18px 30px; border-radius: 16px; font-weight: 900; font-size: 16px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: 0.3s; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.5); width: 100%; margin-top: 15px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -5px rgba(245, 158, 11, 0.6); }
</style>

<div class="page-header">
    <div class="title-icon"><i class="ph-fill ph-plugs-connected"></i></div>
    <div>
        <h1>Konfigurasi API Fingerspot</h1>
        <p style="color: var(--text-muted); margin: 4px 0 0 0; font-size: 14px; font-weight: 500;">Ubah tautan Cloud ID dan Token Akses Mesin Pabrik Anda di sini.</p>
    </div>
</div>

<div class="bento-card">
    <form action="<?= base_url('/device/update_settings') ?>" method="post">
        <?= csrf_field() ?>

        <div class="form-group">
            <label>API URL (Endpoint Server)</label>
            <div class="input-wrapper">
                <i class="ph-bold ph-link"></i>
                <input type="url" name="api_url" value="<?= esc($fsConfig['api_url'] ?? 'https://developer.fingerspot.io/api/') ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Cloud ID Mesin (Tercetak di Mesin Fisik)</label>
            <div class="input-wrapper" style="border-color: #3b82f6;">
                <i class="ph-bold ph-hard-drives" style="color: #3b82f6;"></i>
                <input type="text" name="cloud_id" value="<?= esc($fsConfig['cloud_id'] ?? '') ?>" placeholder="Misal: FZ1096818" required style="color: #3b82f6;">
            </div>
        </div>

        <div class="form-group">
            <label>API Token (Rahasia)</label>
            <div class="input-wrapper" style="border-color: #10b981;">
                <i class="ph-bold ph-key" style="color: #10b981;"></i>
                <input type="text" name="api_token" value="<?= esc($fsConfig['api_token'] ?? '') ?>" placeholder="Misal: BATIXAM74RGEG2XS" required style="color: #10b981;">
            </div>
            <p style="font-size: 12px; color: var(--text-muted); margin-top: 8px;"><i class="ph-fill ph-warning-circle"></i> Pastikan token dimasukkan dengan benar. Token yang salah akan membuat semua perintah sinkronisasi ditolak.</p>
        </div>

        <button type="submit" class="btn-submit" onclick="this.innerHTML='<i class=\'ph-bold ph-spinner-gap ph-spin\' style=\'font-size:24px;\'></i> Menyimpan Konfigurasi...';">
            <i class="ph-bold ph-floppy-disk"></i> Simpan Konfigurasi Mesin
        </button>
    </form>
</div>

<?= $this->endSection() ?>