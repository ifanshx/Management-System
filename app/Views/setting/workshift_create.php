<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- HEADER --- */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; }
    .page-title h1 { font-size: 24px; font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; margin-bottom: 4px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    /* --- LAYOUT GRID --- */
    .setting-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 992px) { .setting-grid { grid-template-columns: 1fr; } }

    /* --- CARD STYLE (SaaS Grade) --- */
    .bento-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.01), 0 1px 3px rgba(0,0,0,0.02);
        transition: border-color 0.3s ease;
    }
    .bento-card:hover { border-color: rgba(37, 99, 235, 0.2); }

    .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px dashed var(--border-subtle);
    }
    
    .icon-wrapper {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--accent-light);
        color: var(--accent-main);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    
    .card-header h3 { font-size: 15px; font-weight: 700; color: var(--text-main); margin: 0; }

    /* --- FORM ELEMENTS --- */
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 12px; font-weight: 700; color: var(--text-main); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-hint { font-size: 11px; color: var(--text-muted); margin-top: 6px; line-height: 1.4; }

    /* Custom Input Group (Seamless) */
    .input-wrapper {
        display: flex;
        align-items: stretch;
        background: var(--bg-base);
        border: 1px solid var(--border-subtle);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.2s ease;
    }
    .input-wrapper:focus-within {
        border-color: var(--accent-main);
        box-shadow: 0 0 0 3px var(--accent-light);
    }
    
    .input-wrapper input, .input-wrapper select {
        flex: 1;
        background: transparent;
        border: none;
        color: var(--text-main);
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        font-family: inherit;
        width: 100%;
    }

    .prefix, .suffix {
        background: rgba(0,0,0,0.02);
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 700;
        padding: 0 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    .prefix { border-right: 1px solid var(--border-subtle); }
    .suffix { border-left: 1px solid var(--border-subtle); }
    html.dark .prefix, html.dark .suffix { background: rgba(255,255,255,0.02); }

    /* Sub-grid for side-by-side inputs */
    .sub-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

    /* Specific States */
    .disabled-block { opacity: 0.4; pointer-events: none; filter: grayscale(100%); transition: all 0.3s; }
    
    select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        padding-right: 36px !important;
    }

    input[type="time"]::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s; }
    input[type="time"]::-webkit-calendar-picker-indicator:hover { opacity: 1; }

    /* --- STICKY FOOTER ACTION --- */
    .form-actions {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border-subtle);
        display: flex;
        justify-content: flex-end;
    }
    
    .btn-save {
        background: var(--accent-main);
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px var(--accent-light);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-save:hover { transform: translateY(-2px); filter: brightness(1.1); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Parameter Shift & Operasional</h1>
        <p>Atur logika mesin absensi, toleransi jam kerja, dan kebijakan denda pabrik secara terpusat.</p>
    </div>
</div>

<form action="<?= base_url('/setting/workshift_store') ?>" method="post">
    <?= csrf_field() ?>
    
    <div class="setting-grid">
        
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <div class="bento-card">
                <div class="card-header">
                    <div class="icon-wrapper"><i class="ph ph-sliders-horizontal"></i></div>
                    <h3>Identitas Shift</h3>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Nama Pengaturan Shift</label>
                    <div class="input-wrapper">
                        <input type="text" name="shift_name" placeholder="Contoh: Shift 1 Produksi, Shift Pagi Logistik" required autocomplete="off">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Mode Aturan Kerja</label>
                    <div class="input-wrapper">
                        <select name="shift_type" id="shiftType" onchange="toggleShiftMode()" required>
                            <option value="Reguler" selected>Reguler (Jadwal Terikat)</option>
                            <option value="Fleksibel">Fleksibel (Acuan Durasi Kerja)</option>
                        </select>
                    </div>
                    <div class="form-hint">Mode fleksibel akan mengabaikan denda telat jam masuk dan berfokus pada total durasi kerja harian.</div>
                </div>
            </div>

            <div class="bento-card" id="strictTimeBlock">
                <div class="card-header">
                    <div class="icon-wrapper"><i class="ph ph-clock"></i></div>
                    <h3>Jadwal & Validasi Mesin</h3>
                </div>
                
                <div class="sub-grid-2 form-group">
                    <div>
                        <label class="form-label">Jam Masuk</label>
                        <div class="input-wrapper"><input type="time" name="time_in" value="08:00"></div>
                    </div>
                    <div>
                        <label class="form-label">Jam Pulang</label>
                        <div class="input-wrapper"><input type="time" name="time_out" value="16:00"></div>
                    </div>
                </div>

                <div class="form-group" style="padding-top:10px; border-top:1px dashed var(--border-subtle);">
                    <label class="form-label">Validasi Mesin (Area Masuk)</label>
                    <div class="sub-grid-2">
                        <div class="input-wrapper">
                            <input type="number" name="scan_in_before" value="60">
                            <div class="suffix">Mnt Sblm</div>
                        </div>
                        <div class="input-wrapper">
                            <input type="number" name="scan_in_after" value="120">
                            <div class="suffix">Mnt Stlh</div>
                        </div>
                    </div>
                    <div class="form-hint">Rentang waktu mesin mengenali scan karyawan sebagai "Absen Masuk".</div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Validasi Mesin (Area Pulang)</label>
                    <div class="sub-grid-2">
                        <div class="input-wrapper">
                            <input type="number" name="scan_out_before" value="30">
                            <div class="suffix">Mnt Sblm</div>
                        </div>
                        <div class="input-wrapper">
                            <input type="number" name="scan_out_after" value="240">
                            <div class="suffix">Mnt Stlh</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bento-card">
                <div class="card-header">
                    <div class="icon-wrapper"><i class="ph ph-coffee"></i></div>
                    <h3>Jadwal Istirahat</h3>
                </div>
                <div class="sub-grid-2" style="margin-bottom:0;">
                    <div>
                        <label class="form-label">Istirahat (Keluar)</label>
                        <div class="input-wrapper"><input type="time" name="break_out" value="12:00"></div>
                    </div>
                    <div>
                        <label class="form-label">Selesai (Masuk)</label>
                        <div class="input-wrapper"><input type="time" name="break_in" value="13:00"></div>
                    </div>
                </div>
            </div>

        </div>

        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <div class="bento-card" id="toleranceBlock">
                <div class="card-header">
                    <div class="icon-wrapper" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;"><i class="ph ph-warning-circle"></i></div>
                    <h3>Toleransi & Kebijakan Denda</h3>
                </div>
                
                <div class="sub-grid-2 form-group">
                    <div>
                        <label class="form-label">Toleransi Telat</label>
                        <div class="input-wrapper">
                            <input type="number" name="late_tolerance" value="15">
                            <div class="suffix">Menit</div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Toleransi Pulang</label>
                        <div class="input-wrapper">
                            <input type="number" name="early_leave_tolerance" value="0">
                            <div class="suffix">Menit</div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0; padding-top:10px; border-top:1px dashed var(--border-subtle);">
                    <label class="form-label">Denda Keterlambatan</label>
                    <div class="input-wrapper" style="border-color: rgba(239, 68, 68, 0.2);">
                        <div class="prefix" style="color: #ef4444; background: rgba(239, 68, 68, 0.05);">- Rp</div>
                        <input type="text" name="late_penalty_rate" placeholder="Contoh: 1.000" onkeyup="formatRupiah(this)" required>
                        <div class="suffix" style="background: rgba(239, 68, 68, 0.05);">/ Menit</div>
                    </div>
                    <div class="form-hint">Denda dikalikan otomatis dengan total menit telat (di luar toleransi).</div>
                </div>
            </div>

            <div class="bento-card">
                <div class="card-header">
                    <div class="icon-wrapper"><i class="ph ph-hourglass-high"></i></div>
                    <h3>Kalkulasi Kehadiran</h3>
                </div>
                <div class="form-group">
                    <label class="form-label">Syarat Dihitung Penuh (1 Hari)</label>
                    <div class="input-wrapper">
                        <input type="number" name="full_day_duration" value="480">
                        <div class="suffix">Menit</div>
                    </div>
                    <div class="form-hint">Durasi kerja bersih agar tidak terpotong (Default: 480 mnt = 8 Jam).</div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Syarat Dihitung Setengah Hari</label>
                    <div class="input-wrapper">
                        <input type="number" name="half_day_duration" value="240">
                        <div class="suffix">Menit</div>
                    </div>
                    <div class="form-hint">Kurang dari ini, karyawan dianggap mangkir / alpa.</div>
                </div>
            </div>

            <div class="bento-card">
                <div class="card-header">
                    <div class="icon-wrapper" style="background: rgba(168, 85, 247, 0.1); color: #a855f7;"><i class="ph ph-moon-stars"></i></div>
                    <h3>Konfigurasi Lembur (Overtime)</h3>
                </div>
                
                <div class="sub-grid-2 form-group">
                    <div>
                        <label class="form-label">Minimal Klaim</label>
                        <div class="input-wrapper">
                            <input type="number" name="min_overtime" value="60">
                            <div class="suffix">Menit</div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Maksimal Klaim</label>
                        <div class="input-wrapper">
                            <input type="number" name="max_overtime" value="240">
                            <div class="suffix">Menit</div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Potongan Waktu Istirahat Lembur</label>
                    <div class="input-wrapper">
                        <input type="number" name="overtime_deduction" value="0">
                        <div class="suffix">Menit</div>
                    </div>
                    <div class="form-hint">Otomatis mengurangi durasi lembur karyawan untuk jam makan malam.</div>
                </div>
            </div>

        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-save">
            <i class="ph ph-floppy-disk"></i> Simpan Konfigurasi Shift
        </button>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function formatRupiah(angka) {
        var number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        angka.value = rupiah;
    }

    <?php if(session()->getFlashdata('success')): ?>
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            icon: 'success', title: 'Berhasil!', text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonColor: '#38bdf8', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b',
        });
    <?php endif; ?>

    function toggleShiftMode() {
        const type = document.getElementById('shiftType').value;
        const strictBlock = document.getElementById('strictTimeBlock');
        const tolBlock = document.getElementById('toleranceBlock');

        if (type === 'Fleksibel') {
            strictBlock.classList.add('disabled-block');
            tolBlock.classList.add('disabled-block');
        } else {
            strictBlock.classList.remove('disabled-block');
            tolBlock.classList.remove('disabled-block');
        }
    }
    
    document.addEventListener("DOMContentLoaded", toggleShiftMode);
</script>

<?= $this->endSection() ?>