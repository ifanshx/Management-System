<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* =========================================================
       1. PAGE HEADER
       ========================================================= */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;}
    
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.05)); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(245, 158, 11, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 500; margin: 0;}

    .btn-back { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 12px 20px; border-radius: 14px; font-weight: 800; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .btn-back:hover { color: #3b82f6; border-color: #3b82f6; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(59, 130, 246, 0.15);}

    /* =========================================================
       2. BENTO CARDS & GRID
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 35px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); transition: all 0.3s;}
    .bento-card:focus-within { border-color: rgba(59, 130, 246, 0.3); box-shadow: 0 15px 35px -10px rgba(59, 130, 246, 0.1);}
    
    .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px dashed var(--border-subtle); }
    .icon-wrapper { width: 40px; height: 40px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .card-header h3 { font-size: 16px; font-weight: 900; color: var(--text-main); margin: 0; }

    /* =========================================================
       3. FORM ELEMENTS (PREMIUM INPUTS)
       ========================================================= */
    .form-group { margin-bottom: 24px; }
    .form-label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; overflow: hidden; transition: all 0.3s; position: relative;}
    .input-wrapper:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); background: var(--bg-surface);}
    
    .input-wrapper input, .input-wrapper select { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 18px; font-size: 14px; font-weight: 700; outline: none; font-family: inherit; width: 100%; appearance: none; cursor: pointer;}
    .input-wrapper input[type="date"], .input-wrapper input[type="time"] { font-family: 'Space Mono', monospace; font-size: 15px; color: #3b82f6;}
    
    .input-loading { opacity: 0.5; pointer-events: none; }
    .input-filled { background-color: rgba(16, 185, 129, 0.05) !important; color: #10b981 !important; border-color: #10b981 !important; transition: 0.5s; }

    select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 18px center; padding-right: 40px !important; }
    
    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s;}
    input[type="date"]::-webkit-calendar-picker-indicator:hover,
    input[type="time"]::-webkit-calendar-picker-indicator:hover { opacity: 1;}
    html.dark input[type="date"]::-webkit-calendar-picker-indicator,
    html.dark input[type="time"]::-webkit-calendar-picker-indicator { filter: invert(0.8); }

    .btn-submit { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; padding: 18px 30px; border: none; border-radius: 16px; font-weight: 900; font-size: 16px; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 8px 20px -5px rgba(59, 130, 246, 0.5); display: flex; align-items: center; gap: 10px; width: 100%; justify-content: center; margin-top: 10px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -5px rgba(59, 130, 246, 0.6); }
    
    .alert-info { background: rgba(16, 185, 129, 0.1); border: 1px dashed rgba(16, 185, 129, 0.3); padding: 15px 20px; border-radius: 14px; color: #10b981; font-size: 13px; font-weight: 600; margin-bottom: 25px; display: flex; gap: 12px; align-items: flex-start; line-height: 1.5;}
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-pencil-simple-line"></i></div>
        <div>
            <h1>Koreksi Absensi Manual</h1>
            <p>Gunakan form ini jika karyawan lupa scan, mati lampu, atau mesin IoT mengalami gangguan.</p>
        </div>
    </div>
    <a href="<?= base_url('/attendance') ?>" class="btn-back">
        <i class="ph-bold ph-arrow-left"></i> Kembali ke Data Absensi
    </a>
</div>

<form action="<?= base_url('/attendance/store_manual') ?>" method="post">
    <?= csrf_field() ?>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 24px; align-items: start;">
        
        <div class="bento-card" style="border-top: 6px solid #f59e0b;">
            <div class="card-header">
                <div class="icon-wrapper"><i class="ph-bold ph-user-focus"></i></div>
                <h3>Data Karyawan & Tanggal</h3>
            </div>
            
            <div class="alert-info">
                <i class="ph-fill ph-info" style="font-size: 20px; flex-shrink: 0; margin-top: 2px;"></i>
                <div>Sistem akan otomatis menghitung denda keterlambatan dan durasi lembur berdasarkan jam yang Anda masukkan di bawah dan dicocokkan dengan Aturan Shift karyawan terkait.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Pilih Pekerja Terdaftar</label>
                <div class="input-wrapper">
                    <select name="employee_id" id="empSelect" required onchange="fetchExistingData()">
                        <option value="" disabled selected>-- Pilih Karyawan --</option>
                        <?php foreach($employees as $emp): ?>
                            <option value="<?= esc($emp['employee_id']) ?>"><?= esc($emp['name']) ?> (<?= esc($emp['employee_id']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Koreksi (Y-M-D)</label>
                <div class="input-wrapper">
                    <input type="date" name="date" id="dateSelect" value="<?= date('Y-m-d') ?>" required onchange="fetchExistingData()">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Status Kehadiran Akhir</label>
                <div class="input-wrapper">
                    <select name="status" id="statusSelect" required>
                        <option value="Hadir">Hadir (Kerja Normal)</option>
                        <option value="Sakit">Sakit (Lampirkan Surat di Menu Cuti)</option> 
                        <option value="Izin">Izin (Lampirkan Surat di Menu Cuti)</option>
                        <option value="Alpha">Alpha (Mangkir / Tanpa Kabar)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bento-card" style="border-top: 6px solid #3b82f6;">
            <div class="card-header">
                <div class="icon-wrapper" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;"><i class="ph-bold ph-clock"></i></div>
                <h3 style="flex: 1;">Rincian Jam (Fase Scan)</h3>
                <span id="autoFillBadge" style="font-size: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 8px; border-radius: 6px; font-weight: 800; display: none; border: 1px solid rgba(16, 185, 129, 0.3);"><i class="ph-bold ph-magic-wand"></i> DATA DITEMUKAN</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Jam Masuk</label>
                    <div class="input-wrapper" id="wrap-in">
                        <input type="time" name="time_in" id="time_in">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Jam Pulang</label>
                    <div class="input-wrapper" id="wrap-out">
                        <input type="time" name="time_out" id="time_out">
                    </div>
                </div>
            </div>

            <label style="display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin: 5px 0 15px 0; text-transform: uppercase; letter-spacing: 0.5px; border-top: 1px dashed var(--border-subtle); padding-top: 20px;">
                Waktu Istirahat (Opsional)
            </label>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Mulai Istirahat</label>
                    <div class="input-wrapper" id="wrap-bout">
                        <input type="time" name="break_out" id="break_out">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Selesai Istirahat</label>
                    <div class="input-wrapper" id="wrap-bin">
                        <input type="time" name="break_in" id="break_in">
                    </div>
                </div>
            </div>

            <div style="margin-top: 35px;">
                <button type="submit" class="btn-submit" onclick="this.innerHTML='<i class=\'ph-bold ph-spinner-gap ph-spin\' style=\'font-size:22px;\'></i> Menyimpan...';">
                    <i class="ph-bold ph-floppy-disk" style="font-size: 22px;"></i> Simpan & Kalkulasi Sistem
                </button>
            </div>
        </div>

    </div>
</form>

<script>
    function fetchExistingData() {
        const empId = document.getElementById('empSelect').value;
        const date  = document.getElementById('dateSelect').value;
        
        if (!empId || !date) return;

        const inputs = ['time_in', 'time_out', 'break_out', 'break_in', 'statusSelect'];
        inputs.forEach(id => document.getElementById(id).classList.add('input-loading'));

        fetch(`<?= base_url('/attendance/get_existing_log') ?>?employee_id=${empId}&date=${date}`)
            .then(response => response.json())
            .then(result => {
                inputs.forEach(id => document.getElementById(id).classList.remove('input-loading'));
                
                ['wrap-in', 'wrap-out', 'wrap-bout', 'wrap-bin'].forEach(id => document.getElementById(id).classList.remove('input-filled'));
                document.getElementById('autoFillBadge').style.display = 'none';

                if (result.success) {
                    document.getElementById('autoFillBadge').style.display = 'inline-flex';

                    document.getElementById('time_in').value = result.data.time_in;
                    document.getElementById('time_out').value = result.data.time_out;
                    document.getElementById('break_out').value = result.data.break_out;
                    document.getElementById('break_in').value = result.data.break_in;
                    
                    const statusDropdown = document.getElementById('statusSelect');
                    for(let i=0; i < statusDropdown.options.length; i++) {
                        if(statusDropdown.options[i].value === result.data.status) {
                            statusDropdown.selectedIndex = i;
                            break;
                        }
                    }

                    if(result.data.time_in) document.getElementById('wrap-in').classList.add('input-filled');
                    if(result.data.time_out) document.getElementById('wrap-out').classList.add('input-filled');
                    if(result.data.break_out) document.getElementById('wrap-bout').classList.add('input-filled');
                    if(result.data.break_in) document.getElementById('wrap-bin').classList.add('input-filled');
                } else {
                    document.getElementById('time_in').value = '';
                    document.getElementById('time_out').value = '';
                    document.getElementById('break_out').value = '';
                    document.getElementById('break_in').value = '';
                    document.getElementById('statusSelect').value = 'Hadir';
                }
            })
            .catch(error => {
                console.error("Gagal menarik data:", error);
                inputs.forEach(id => document.getElementById(id).classList.remove('input-loading'));
            });
    }
</script>

<?= $this->endSection() ?>