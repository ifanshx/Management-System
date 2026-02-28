<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 30px; box-shadow: var(--shadow-card); }
    .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px dashed var(--border-subtle); }
    .icon-wrapper { width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .card-header h3 { font-size: 16px; font-weight: 800; color: var(--text-main); margin: 0; }

    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: all 0.3s; }
    .input-wrapper:focus-within { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }
    .input-wrapper input, .input-wrapper select { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 16px; font-size: 14px; font-weight: 600; outline: none; font-family: inherit; width: 100%;}
    
    select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px !important; }
    input[type="time"]::-webkit-calendar-picker-indicator { cursor: pointer; filter: invert(0.5); }

    .btn-submit { background: var(--accent-main); color: #fff; padding: 14px 30px; border: none; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 12px var(--accent-light); display: inline-flex; align-items: center; gap: 8px; width: 100%; justify-content: center;}
    .btn-submit:hover { transform: translateY(-3px); filter: brightness(1.1); }
    
    .alert-info { background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); padding: 15px; border-radius: 12px; color: #0284c7; font-size: 13px; font-weight: 600; margin-bottom: 25px; display: flex; gap: 10px; align-items: flex-start;}
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Koreksi Absensi Manual</h1>
        <p>Gunakan fitur ini jika karyawan lupa scan jari, mati lampu, atau mesin IoT mengalami gangguan.</p>
    </div>
    <a href="<?= base_url('/attendance') ?>" style="background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 13px;">
        &larr; Kembali ke Data Absensi
    </a>
</div>

<form action="<?= base_url('/attendance/store_manual') ?>" method="post">
    <?= csrf_field() ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; align-items: start;">
        
        <div class="bento-card">
            <div class="card-header">
                <div class="icon-wrapper"><i class="ph ph-user-focus"></i></div>
                <h3>Data Karyawan & Tanggal</h3>
            </div>
            
            <div class="alert-info">
                <i class="ph ph-info" style="font-size: 20px; flex-shrink: 0;"></i>
                <div>Sistem akan otomatis menghitung denda keterlambatan dan durasi lembur berdasarkan jam yang Anda masukkan di bawah dan dicocokkan dengan Aturan Shift karyawan terkait.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Pilih Karyawan</label>
                <div class="input-wrapper">
                    <select name="employee_id" required>
                        <option value="" disabled selected>-- Pilih Karyawan --</option>
                        <?php foreach($employees as $emp): ?>
                            <option value="<?= esc($emp['employee_id']) ?>"><?= esc($emp['name']) ?> (<?= esc($emp['employee_id']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Koreksi</label>
                <div class="input-wrapper">
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Status Kehadiran</label>
                <div class="input-wrapper">
                    <select name="status" required>
                        <option value="Hadir">Hadir (Normal)</option>
                        <option value="Sakit">Sakit (Lampirkan Surat di Menu Cuti)</option>
                        <option value="Izin">Izin (Lampirkan Surat di Menu Cuti)</option>
                        <option value="Alpha">Alpha (Mangkir)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bento-card">
            <div class="card-header">
                <div class="icon-wrapper" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph ph-clock"></i></div>
                <h3>Rincian Jam (Opsional)</h3>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Jam Masuk</label>
                    <div class="input-wrapper">
                        <input type="time" name="time_in">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Jam Pulang</label>
                    <div class="input-wrapper">
                        <input type="time" name="time_out">
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px dashed var(--border-subtle); margin: 10px 0 25px 0;">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Mulai Istirahat</label>
                    <div class="input-wrapper">
                        <input type="time" name="break_out">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Selesai Istirahat</label>
                    <div class="input-wrapper">
                        <input type="time" name="break_in">
                    </div>
                </div>
            </div>

            <div style="margin-top: 25px;">
                <button type="submit" class="btn-submit">
                    <i class="ph ph-floppy-disk"></i> Simpan & Kalkulasi Sistem
                </button>
            </div>
        </div>

    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if(session()->getFlashdata('error')): ?>
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            icon: 'error', title: 'Gagal!', text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonColor: '#ef4444', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b',
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>