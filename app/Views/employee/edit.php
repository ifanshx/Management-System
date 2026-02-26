<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>

<style>
    /* CSS Sama Persis dengan create.php agar Konsisten 100% */
    .header-action { display: flex; align-items: center; gap: 15px; margin-bottom: 30px; }
    .btn-back { width: 40px; height: 40px; border-radius: 12px; background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer; transition: all 0.2s; box-shadow: var(--shadow-card); text-decoration: none; }
    .btn-back:hover { background: var(--border-subtle); transform: translateX(-3px); }
    .page-title h1 { font-size: 24px; font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; margin-bottom: 4px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    .main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; align-items: start; }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 30px; box-shadow: var(--shadow-card); transition: border-color 0.3s ease, transform 0.3s ease; }
    .bento-card:hover { border-color: rgba(37, 99, 235, 0.3); transform: translateY(-2px); }
    .bento-header { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px; display: flex; align-items: center; gap: 8px; }
    .bento-header i { color: var(--accent-main); font-size: 22px; }

    .form-group { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { font-size: 13px; font-weight: 700; color: var(--text-main); }
    .form-control { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 12px 16px; border-radius: 12px; font-size: 14px; font-weight: 500; outline: none; transition: all 0.3s; width: 100%; font-family: 'Plus Jakarta Sans', sans-serif; }
    .form-control:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }
    
    select.form-control { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 16px center; padding-right: 40px; cursor: pointer; }

    .action-bar { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 20px 30px; margin-top: 25px; display: flex; justify-content: flex-end; gap: 15px; box-shadow: var(--shadow-card); }
    .btn-cancel { background: transparent; border: 1px solid var(--border-subtle); color: var(--text-main); padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background 0.2s; text-decoration: none; }
    .btn-cancel:hover { background: var(--bg-base); }
    .btn-submit { background: var(--accent-main); color: #fff; border: none; padding: 12px 30px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px var(--accent-light); display: flex; align-items: center; gap: 8px; }
    .btn-submit:hover { transform: translateY(-2px); filter: brightness(1.1); }

    @media (max-width: 992px) { .main-grid { grid-template-columns: 1fr; } }
</style>

<div class="header-action">
    <a href="<?= base_url('/employee') ?>" class="btn-back"><i class="ph ph-arrow-left"></i></a>
    <div class="page-title">
        <h1>Perbarui Data Karyawan</h1>
        <p>Edit profil, detail pekerjaan, atau data penggajian milik pekerja.</p>
    </div>
</div>

<form action="<?= base_url('/employee/update/' . $employee['id']) ?>" method="post">
    <?= csrf_field() ?> <div class="main-grid">

        <div style="display: flex; flex-direction: column; gap: 25px;">
            
            <div class="bento-card">
                <div class="bento-header">
                    <i class="ph ph-user-circle"></i> Profil & Data Pribadi
                </div>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label>Nomor Induk Karyawan (Web)</label>
                        <input type="text" class="form-control" value="<?= esc($employee['employee_id']) ?>" readonly style="background: var(--border-subtle); cursor: not-allowed; color: var(--text-main); font-weight: 800;">
                    </div>
                    
                    <div class="form-group">
                        <label>PIN Mesin IoT</label>
                        <input type="text" class="form-control" value="<?= esc($employee['pin'] ?? 'Kosong') ?>" readonly style="background: rgba(16, 185, 129, 0.1); border-color: #10b981; color: #10b981; font-weight: 800; text-align: center; cursor: not-allowed;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Nama Lengkap (Sesuai KTP)</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= esc($employee['name']) ?>" required autocomplete="off">
                </div>

                <div class="form-group">
                    <label for="phone">Nomor Telepon / WhatsApp</label>
                    <input type="text" id="phone" name="phone" class="form-control" value="<?= esc($employee['phone'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="marital_status">Status Tanggungan Pajak (PTKP)</label>
                    <select id="marital_status" name="marital_status" class="form-control" required>
                        <option value="TK/0" <?= ($employee['marital_status'] == 'TK/0') ? 'selected' : '' ?>>TK/0 - Lajang / Tidak Ada Tanggungan</option>
                        <option value="K/0" <?= ($employee['marital_status'] == 'K/0') ? 'selected' : '' ?>>K/0 - Menikah, Tanpa Anak</option>
                        <option value="K/1" <?= ($employee['marital_status'] == 'K/1') ? 'selected' : '' ?>>K/1 - Menikah, Anak 1</option>
                        <option value="K/2" <?= ($employee['marital_status'] == 'K/2') ? 'selected' : '' ?>>K/2 - Menikah, Anak 2</option>
                        <option value="K/3" <?= ($employee['marital_status'] == 'K/3') ? 'selected' : '' ?>>K/3 - Menikah, Anak 3 / Lebih</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="address">Alamat Domisili</label>
                    <textarea id="address" name="address" class="form-control" rows="3" style="resize: none;" required><?= esc($employee['address'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="bento-card">
                <div class="bento-header">
                    <i class="ph ph-shield-check"></i> Akses Portal Web & Mesin IoT
                </div>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 15px; line-height: 1.5;">
                    Ubah Hak Akses Mesin untuk memberikan wewenang operasional langsung pada mesin fisik (seperti menambah data offline).
                </p>
                
                <div class="form-group">
                    <label for="machine_privilege">Hak Akses Mesin Absensi Fisik</label>
                    <select id="machine_privilege" name="machine_privilege" class="form-control" required>
                        <option value="1" <?= ($employee['machine_privilege'] == 1) ? 'selected' : '' ?>>1 - User Biasa (Hanya bisa absen)</option>
                        <option value="2" <?= ($employee['machine_privilege'] == 2) ? 'selected' : '' ?>>2 - Admin Mesin (Bisa buka menu mesin & atur izin)</option>
                        <option value="3" <?= ($employee['machine_privilege'] == 3) ? 'selected' : '' ?>>3 - Sub-Admin Mesin</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label>Username Terdaftar (Web)</label>
                    <div style="background: var(--bg-base); border: 1px dashed var(--border-subtle); padding: 12px 16px; border-radius: 12px; font-family: monospace; color: var(--text-main); font-weight: 600;">
                        <i class="ph ph-lock-key" style="margin-right: 5px;"></i> Terhubung via NIK
                    </div>
                </div>
            </div>

        </div>

        <div style="display: flex; flex-direction: column; gap: 25px;">

            <div class="bento-card">
                <div class="bento-header">
                    <i class="ph ph-briefcase"></i> Detail Pekerjaan
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="department">Departemen / Divisi</label>
                        <select id="department" name="department" class="form-control" onchange="updatePositions()" required>
                            <option value="Produksi & Manufaktur" <?= ($employee['department'] == 'Produksi & Manufaktur') ? 'selected' : '' ?>>Produksi & Manufaktur</option>
                            <option value="Quality Control & R&D" <?= ($employee['department'] == 'Quality Control & R&D') ? 'selected' : '' ?>>Quality Control & R&D</option>
                            <option value="Gudang & Logistik" <?= ($employee['department'] == 'Gudang & Logistik') ? 'selected' : '' ?>>Gudang & Logistik</option>
                            <option value="Manajemen & HRD" <?= ($employee['department'] == 'Manajemen & HRD') ? 'selected' : '' ?>>Manajemen & HRD</option>
                            <option value="Sales & Marketing" <?= ($employee['department'] == 'Sales & Marketing') ? 'selected' : '' ?>>Sales & Marketing</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="position">Posisi / Jabatan</label>
                        <input type="hidden" id="old_position" value="<?= esc($employee['position']) ?>">
                        <select id="position" name="position" class="form-control" required>
                            </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="shift_id">Aturan Shift Kerja</label>
                    <select id="shift_id" name="shift_id" class="form-control" required>
                        <?php foreach($shifts as $shift): ?>
                            <option value="<?= $shift['id'] ?>" <?= ($employee['shift_id'] == $shift['id']) ? 'selected' : '' ?>>
                                <?= esc($shift['shift_name']) ?> (<?= esc($shift['shift_type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="status">Status Pegawai</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="Kontrak" <?= ($employee['status'] == 'Kontrak') ? 'selected' : '' ?>>Kontrak</option>
                            <option value="Tetap" <?= ($employee['status'] == 'Tetap') ? 'selected' : '' ?>>Tetap (PKWTT)</option>
                            <option value="Magang" <?= ($employee['status'] == 'Magang') ? 'selected' : '' ?>>Magang</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="join_date">Tanggal Bergabung</label>
                        <input type="date" id="join_date" name="join_date" class="form-control" value="<?= esc($employee['join_date']) ?>" required>
                    </div>
                </div>
            </div>

           <div class="bento-card" style="margin-top: 25px;">
                <div class="bento-header">
                    <i class="ph ph-wallet"></i> Komponen Penggajian & Tunjangan
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Gaji Pokok (Basic Salary)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-weight: 700; color: var(--text-muted);">Rp</span>
                            <input type="text" name="basic_salary" class="form-control" style="padding-left: 45px; font-weight: 800; color: var(--accent-main);" value="<?= isset($employee) ? number_format($employee['basic_salary'], 0, ',', '.') : '' ?>" onkeyup="formatRupiah(this)" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Siklus Pembayaran</label>
                        <select name="salary_type" class="form-control" required>
                            <option value="Harian" <?= (isset($employee) && $employee['salary_type'] == 'Harian') ? 'selected' : '' ?>>Harian</option>
                            <option value="Mingguan" <?= (isset($employee) && $employee['salary_type'] == 'Mingguan') ? 'selected' : '' ?>>Mingguan</option>
                            <option value="Bulanan" <?= (isset($employee) && $employee['salary_type'] == 'Bulanan') ? 'selected' : '' ?>>Bulanan</option>
                        </select>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed var(--border-subtle); margin: 20px 0;">

                <p style="font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase;">A. Tunjangan Karyawan</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>T. Jabatan (Tetap)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600; font-size:12px; color: var(--text-muted);">Rp</span>
                            <input type="text" name="position_allowance" class="form-control" style="padding-left: 35px; font-size:13px;" value="<?= isset($employee) ? number_format($employee['position_allowance'], 0, ',', '.') : '' ?>" onkeyup="formatRupiah(this)">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Uang Makan (Per Hari)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600; font-size:12px; color: var(--text-muted);">Rp</span>
                            <input type="text" name="meal_allowance" class="form-control" style="padding-left: 35px; font-size:13px;" value="<?= isset($employee) ? number_format($employee['meal_allowance'], 0, ',', '.') : '' ?>" onkeyup="formatRupiah(this)">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Transport (Per Hari)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600; font-size:12px; color: var(--text-muted);">Rp</span>
                            <input type="text" name="transport_allowance" class="form-control" style="padding-left: 35px; font-size:13px;" value="<?= isset($employee) ? number_format($employee['transport_allowance'], 0, ',', '.') : '' ?>" onkeyup="formatRupiah(this)">
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed var(--border-subtle); margin: 20px 0;">

                <p style="font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase;">B. Variabel Kehadiran</p>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Tarif Lembur (Per Jam)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600; font-size:12px; color: #10b981;">+ Rp</span>
                            <input type="text" name="overtime_rate" class="form-control" style="padding-left: 45px; border-color: rgba(16,185,129,0.3);" value="<?= isset($employee) ? number_format($employee['overtime_rate'], 0, ',', '.') : '' ?>" onkeyup="formatRupiah(this)">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Denda Telat (Per Menit)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 600; font-size:12px; color: #ef4444;">- Rp</span>
                            <input type="text" name="late_penalty_rate" class="form-control" style="padding-left: 45px; border-color: rgba(239,68,68,0.3);" value="<?= isset($employee) ? number_format($employee['late_penalty_rate'], 0, ',', '.') : '' ?>" onkeyup="formatRupiah(this)">
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px dashed var(--border-subtle); margin: 20px 0;">

                <p style="font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase;">C. Potongan Wajib & Rekening</p>
                
                <div style="background: var(--bg-base); padding: 15px; border-radius: 12px; border: 1px solid var(--border-subtle); margin-bottom: 15px;">
                    <div style="display: flex; gap: 25px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; font-weight: 600; color: var(--text-main);">
                            <input type="checkbox" name="bpjs_kesehatan" value="1" style="width: 18px; height: 18px; accent-color: var(--accent-main);" <?= (isset($employee) && $employee['bpjs_kesehatan'] == 1) ? 'checked' : '' ?>>
                            Potongan BPJS Kesehatan (1%)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; font-weight: 600; color: var(--text-main);">
                            <input type="checkbox" name="bpjs_ketenagakerjaan" value="1" style="width: 18px; height: 18px; accent-color: var(--accent-main);" <?= (isset($employee) && $employee['bpjs_ketenagakerjaan'] == 1) ? 'checked' : '' ?>>
                            Potongan BPJS Ketenagakerjaan (2%)
                        </label>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Bank Penyalur</label>
                        <select name="bank_name" class="form-control" required>
                            <option value="Tunai/Cash" <?= (isset($employee) && $employee['bank_name'] == 'Tunai/Cash') ? 'selected' : '' ?>>Tunai (Cash)</option>
                            <option value="BCA" <?= (isset($employee) && $employee['bank_name'] == 'BCA') ? 'selected' : '' ?>>BCA</option>
                            <option value="Mandiri" <?= (isset($employee) && $employee['bank_name'] == 'Mandiri') ? 'selected' : '' ?>>Mandiri</option>
                            <option value="BRI" <?= (isset($employee) && $employee['bank_name'] == 'BRI') ? 'selected' : '' ?>>BRI</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label>Nomor Rekening</label>
                        <input type="text" name="bank_account" class="form-control" placeholder="Isi '-' jika tunai" value="<?= esc($employee['bank_account'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="action-bar">
        <a href="<?= base_url('/employee') ?>" class="btn-cancel">Batal</a>
        <button type="submit" class="btn-submit">
            <i class="ph ph-check-circle"></i> Simpan Perubahan
        </button>
    </div>
</form>

<script>
    // Script Format Rupiah
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

    // Script Dropdown Posisi Dinamis
    const positionData = {
        "Produksi & Manufaktur": ["Plant Manager", "Supervisor Produksi", "Operator Bending & Roll", "Operator Welding (Las)", "Operator Polishing & Finishing", "Operator Assembly (Perakitan)", "Teknisi Mesin & Maintenance"],
        "Quality Control & R&D": ["Kepala QC", "QC Inspector (Inspektur Mutu)", "R&D Engineer (Pengembangan Produk)", "Dyno Tester (Penguji Performa)"],
        "Gudang & Logistik": ["Supervisor Logistik", "Admin Gudang", "Material Handler (Bahan Baku)", "Packaging & Shipping Staff"],
        "Manajemen & HRD": ["HR & GA Manager", "Staf Rekrutmen & Pelatihan", "Staf Payroll & Absensi", "Finance & Accounting"],
        "Sales & Marketing": ["Head of Sales", "Digital Marketing Specialist", "Customer Service / Admin Order", "Distributor Relation Officer"]
    };

    function updatePositions() {
        const departmentSelect = document.getElementById("department");
        const positionSelect = document.getElementById("position");
        const oldPosition = document.getElementById("old_position").value;
        const selectedDepartment = departmentSelect.value;

        positionSelect.innerHTML = '';

        if (selectedDepartment && positionData[selectedDepartment]) {
            positionSelect.disabled = false;
            
            positionData[selectedDepartment].forEach(function(position) {
                const option = document.createElement("option");
                option.value = position;
                option.text = position;
                // Otomatis memilih posisi lama jika sesuai
                if(position === oldPosition) {
                    option.selected = true;
                }
                positionSelect.appendChild(option);
            });
        }
    }

    // Jalankan fungsi saat halaman dimuat
    document.addEventListener("DOMContentLoaded", function() {
        updatePositions();
    });
</script>

<?= $this->endSection() ?>