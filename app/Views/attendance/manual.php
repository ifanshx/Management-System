<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --brand: #3b82f6; --brand-dark: #2563eb; --brand-soft: rgba(59, 130, 246, 0.1);
        --success: #10b981; --warning: #f59e0b; --danger: #ef4444; --info: #0ea5e9;
    }

    /* AMBIENT GLOW */
    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 800px; height: 500px; background: radial-gradient(circle, rgba(245, 158, 11, 0.08) 0%, transparent 70%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, transparent 70%); }

    .page-wrapper { position: relative; z-index: 1; }

    /* =========================================================
       1. PAGE HEADER
       ========================================================= */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;}
    
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.05)); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 28px; border: 1px solid rgba(245, 158, 11, 0.2); box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4);}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 0;}

    .btn-back { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 12px 20px; border-radius: 14px; font-weight: 800; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .btn-back:hover { color: var(--brand); border-color: var(--brand); transform: translateX(-4px); box-shadow: 0 8px 15px var(--brand-soft);}

    /* =========================================================
       2. BENTO CARDS & GRID
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; padding: 35px; box-shadow: 0 15px 40px -10px rgba(0,0,0,0.05); transition: all 0.3s;}
    .bento-card:focus-within { border-color: rgba(59, 130, 246, 0.3); box-shadow: 0 20px 40px -10px rgba(59, 130, 246, 0.15);}
    @media (max-width: 768px) { .bento-card { padding: 25px; border-radius: 20px; } }
    
    .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px dashed var(--border-subtle); }
    .icon-wrapper { width: 44px; height: 44px; border-radius: 14px; background: rgba(59, 130, 246, 0.1); color: var(--brand); display: flex; align-items: center; justify-content: center; font-size: 22px; }
    .card-header h3 { font-size: 18px; font-weight: 900; color: var(--text-main); margin: 0; }

    /* =========================================================
       3. FORM ELEMENTS (PREMIUM INPUTS)
       ========================================================= */
    .form-group { margin-bottom: 24px; }
    .form-label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; overflow: hidden; transition: all 0.3s; position: relative;}
    .input-wrapper:focus-within { border-color: var(--brand); box-shadow: 0 0 0 4px var(--brand-soft); background: var(--bg-surface);}
    
    .input-wrapper input, .input-wrapper select { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 18px; font-size: 14px; font-weight: 700; outline: none; font-family: inherit; width: 100%; appearance: none; cursor: pointer;}
    .input-wrapper input[type="date"], .input-wrapper input[type="time"] { font-family: 'Space Mono', monospace; font-size: 15px; color: var(--brand);}
    
    .input-loading { opacity: 0.5; pointer-events: none; }
    .input-filled { background-color: rgba(16, 185, 129, 0.05) !important; color: #10b981 !important; border-color: #10b981 !important; transition: 0.5s; }

    /* Custom Standard Select */
    select.standard-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 18px center; padding-right: 40px !important; }
    
    input[type="date"]::-webkit-calendar-picker-indicator,
    input[type="time"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s;}
    input[type="date"]::-webkit-calendar-picker-indicator:hover,
    input[type="time"]::-webkit-calendar-picker-indicator:hover { opacity: 1;}
    html.dark input[type="date"]::-webkit-calendar-picker-indicator,
    html.dark input[type="time"]::-webkit-calendar-picker-indicator { filter: invert(0.8); }

    /* =========================================================
       4. SELECT2 CUSTOMIZATION (SEARCHABLE DROPDOWN)
       ========================================================= */
    .select2-container--default .select2-selection--single { background: var(--bg-base); border: 1px solid var(--border-subtle); height: auto; min-height: 48px; border-radius: 14px; display: flex; align-items: center; padding: 0 8px; transition: all 0.3s;}
    .select2-container--default.select2-container--focus .select2-selection--single,
    .select2-container--default.select2-container--open .select2-selection--single { border-color: var(--brand); background: var(--bg-surface); box-shadow: 0 0 0 4px var(--brand-soft);}
    .select2-container--default .select2-selection--single .select2-selection__rendered { font-weight: 800; font-size: 14px; color: var(--text-main); padding-left: 8px;}
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 46px; right: 12px;}
    .select2-dropdown { border-radius: 16px; border: 1px solid var(--border-subtle); box-shadow: 0 20px 50px rgba(0,0,0,0.15); padding: 10px; background: var(--bg-surface); overflow: hidden;}
    .select2-search__field { border-radius: 10px !important; padding: 10px 14px !important; border: 1px solid var(--border-subtle) !important; outline: none; font-family: inherit; font-weight: 700; background: var(--bg-base); color: var(--text-main);}
    .select2-search__field:focus { border-color: var(--brand) !important; box-shadow: 0 0 0 3px var(--brand-soft) !important; }
    .select2-results__option { border-radius: 10px; margin-bottom: 4px; font-weight: 700; font-size: 13px; padding: 10px 14px; color: var(--text-main); transition: 0.2s;}
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background: var(--brand) !important; color: white !important; }
    
    /* Tombol & Alert */
    .btn-submit { background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; padding: 20px 30px; border: none; border-radius: 16px; font-weight: 900; font-size: 16px; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5); display: flex; align-items: center; gap: 10px; width: 100%; justify-content: center; margin-top: 15px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 35px -5px rgba(59, 130, 246, 0.6); }
    
    .alert-info { background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); padding: 18px 24px; border-radius: 16px; color: #d97706; font-size: 13px; font-weight: 600; margin-bottom: 25px; display: flex; gap: 12px; align-items: flex-start; line-height: 1.6;}
</style>

<div class="ambient-glow"></div>

<div class="page-wrapper">
    <div class="page-header">
        <div class="page-title">
            <div class="title-icon"><i class="ph-fill ph-pencil-simple-line"></i></div>
            <div>
                <h1>Koreksi Absensi Manual</h1>
                <p>Gunakan form ini jika karyawan lupa scan, mati lampu, atau mesin IoT mengalami gangguan.</p>
            </div>
        </div>
        <a href="<?= base_url('/attendance') ?>" class="btn-back">
            <i class="ph-bold ph-arrow-left"></i> Kembali ke Log Absensi
        </a>
    </div>

    <form action="<?= base_url('/attendance/store_manual') ?>" method="post">
        <?= csrf_field() ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 24px; align-items: start;">
            
            <div class="bento-card" style="border-top: 6px solid #f59e0b;">
                <div class="card-header">
                    <div class="icon-wrapper" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;"><i class="ph-bold ph-user-focus"></i></div>
                    <h3>Data Karyawan & Tanggal</h3>
                </div>
                
                <div class="alert-info">
                    <i class="ph-fill ph-info" style="font-size: 22px; flex-shrink: 0; margin-top: 2px;"></i>
                    <div>Sistem akan otomatis menghitung denda keterlambatan dan durasi lembur berdasarkan jam yang Anda masukkan di bawah, lalu dicocokkan dengan Aturan Shift master.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Pilih Pekerja Terdaftar</label>
                    <select name="employee_id" id="empSelect" required>
                        <option value="" disabled selected>-- Ketik / Cari Nama Karyawan --</option>
                        <?php foreach($employees as $emp): ?>
                            <option value="<?= esc($emp['employee_id']) ?>">
                                <?= esc($emp['name']) ?> (<?= esc($emp['employee_id']) ?>) - <?= esc($emp['position']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                        <select name="status" id="statusSelect" class="standard-select" required>
                            <option value="Hadir">✅ Hadir (Kerja Normal)</option>
                            <option value="Sakit">⚕️ Sakit (Lampirkan Surat di Menu Cuti)</option> 
                            <option value="Izin">📄 Izin (Lampirkan Surat di Menu Cuti)</option>
                            <option value="Alpha">❌ Alpha (Mangkir / Tanpa Kabar)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="bento-card" style="border-top: 6px solid var(--brand);">
                <div class="card-header">
                    <div class="icon-wrapper"><i class="ph-bold ph-clock"></i></div>
                    <h3 style="flex: 1;">Rincian Jam (Fase Scan)</h3>
                    <span id="autoFillBadge" style="font-size: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 6px 12px; border-radius: 8px; font-weight: 900; display: none; border: 1px solid rgba(16, 185, 129, 0.3); align-items: center; gap: 4px;"><i class="ph-bold ph-magic-wand"></i> DATA DITEMUKAN</span>
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

                <label style="display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; color: var(--text-muted); margin: 5px 0 15px 0; text-transform: uppercase; letter-spacing: 0.5px; border-top: 1px dashed var(--border-subtle); padding-top: 20px;">
                    <i class="ph-fill ph-coffee" style="color: var(--brand); font-size: 16px;"></i> Waktu Istirahat (Opsional)
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
                    <button type="submit" class="btn-submit" onclick="this.innerHTML='<i class=\'ph-bold ph-spinner-gap ph-spin\' style=\'font-size:22px;\'></i> Memproses...';">
                        <i class="ph-bold ph-floppy-disk" style="font-size: 22px;"></i> Simpan & Kalkulasi Sistem
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    $(document).ready(function() {
        // Inisialisasi Select2 untuk Pencarian Pekerja
        $('#empSelect').select2({
            placeholder: "-- Ketik & Cari Nama Karyawan --",
            allowClear: true,
            width: '100%'
        });

        // Trigger pencarian data absen jika user mengganti karyawan di Select2
        $('#empSelect').on('change', function() {
            fetchExistingData();
        });
    });

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
                        // Kita pakai index of atau split untuk mencocokkan value murni
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