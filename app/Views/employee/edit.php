<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const isDark = document.documentElement.classList.contains('dark');
    const bgColor = isDark ? '#18181b' : '#ffffff';
    const textColor = isDark ? '#f4f4f5' : '#09090b';

    <?php if(session()->getFlashdata('success')): ?>
        Swal.fire({ icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#10b981', background: bgColor, color: textColor, customClass: { popup: 'swal2-custom-radius' } });
    <?php endif; ?>

    <?php if(session()->getFlashdata('error')): ?>
        Swal.fire({ icon: 'error', title: 'Gagal!', html: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444', background: bgColor, color: textColor, customClass: { popup: 'swal2-custom-radius' } });
    <?php endif; ?>

    togglePayrollFields();
    toggleBankFields();
    toggleBpjsKs();
    toggleBpjsTk();
    toggleGradeLevel(); 

    document.getElementById('statusSelect').addEventListener('change', togglePayrollFields);
    document.getElementById('paymentMethodSelect').addEventListener('change', toggleBankFields);
    document.getElementById('departmentSelect').addEventListener('change', toggleGradeLevel); 
    
    // Inisialisasi UI Select2 dengan pencarian
    $('.select2-leader').select2({ width: '100%', placeholder: "-- Pilih Jika Anak Buah --", allowClear: true });
});

function togglePayrollFields() {
    const status = document.getElementById('statusSelect').value;
    const salaryFields = document.querySelectorAll('.salary-fixed-only');

    salaryFields.forEach(el => {
        if (status === 'Borongan') {
            el.style.display = 'none';
        } else {
            el.style.display = '';
        }
    });
}

function toggleBankFields() {
    const payment = document.getElementById('paymentMethodSelect').value;
    const bankFields = document.getElementById('bankFields');

    if (payment === 'Transfer') {
        bankFields.style.display = '';
    } else {
        bankFields.style.display = 'none';
    }
}

function toggleGradeLevel() {
    const deptSelect = document.getElementById('departmentSelect');
    const selectedOption = deptSelect.options[deptSelect.selectedIndex];
    
    const deptName = selectedOption ? selectedOption.getAttribute('data-name') : '';
    const wrapGrade = document.getElementById('wrapGradeLevel');
    const wrapSpecialty = document.getElementById('wrapSpecialty');
    
    if (deptName && deptName.toLowerCase().includes('produksi')) {
        wrapGrade.style.display = 'block';
        wrapSpecialty.style.display = 'block';
    } else {
        wrapGrade.style.display = 'none';
        wrapSpecialty.style.display = 'none';
        const gradeSelect = document.querySelector('select[name="grade_level"]');
        if(gradeSelect) gradeSelect.value = ''; 
        const specSelect = document.querySelector('select[name="specialty"]');
        if(specSelect) specSelect.value = ''; 
    }
}

function toggleBpjsKs() {
    const check = document.getElementById('checkBpjsKs');
    const wrap = document.getElementById('wrapBpjsKs');
    if(check && wrap) wrap.style.display = check.checked ? 'block' : 'none';
}

function toggleBpjsTk() {
    const check = document.getElementById('checkBpjsTk');
    const wrap = document.getElementById('wrapBpjsTk');
    if(check && wrap) wrap.style.display = check.checked ? 'block' : 'none';
}

function formatRupiah(angka) {
    if (!angka) return;
    let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
        split   = number_string.split(','),
        sisa    = split[0].length % 3,
        rupiah  = split[0].substr(0, sisa),
        ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
        
    if (ribuan) {
        let separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }
    
    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    angka.value = rupiah;
}
</script>

<style>
    .swal2-custom-radius { border-radius: 18px !important; font-family: 'Plus Jakarta Sans', sans-serif; }
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; }
    .page-title-wrap { display: flex; align-items: center; gap: 16px; }
    .page-icon { width: 50px; height: 50px; border-radius: 16px; background: linear-gradient(135deg, rgba(37,99,235,.1), rgba(59,130,246,.03)); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 22px; border: 1px solid rgba(37,99,235,.12); }
    .page-title h1 { font-size: 24px; font-weight: 900; color: var(--text-main); margin: 0 0 5px 0; letter-spacing: -.6px; }
    .page-title p { margin: 0; color: var(--text-muted); font-size: 12px; font-weight: 600; }
    .btn-back { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 16px; border-radius: 12px; font-weight: 800; text-decoration: none; font-size: 12px; transition: .25s ease; }
    .btn-back:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.05); }

    .main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
    @media (max-width: 1024px) { .main-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 22px; padding: 22px; box-shadow: 0 10px 30px -15px rgba(0,0,0,.1); }
    .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px dashed var(--border-subtle); }
    .icon-wrapper { width: 34px; height: 34px; border-radius: 12px; background: rgba(37,99,235,.08); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 17px; }
    .card-header h3 { font-size: 14px; font-weight: 900; color: var(--text-main); margin: 0; }

    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 10px; font-weight: 900; color: var(--text-muted); margin-bottom: 7px; text-transform: uppercase; letter-spacing: .6px; }
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; overflow: hidden; transition: .25s ease; }
    .input-wrapper:focus-within { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,.1); }
    .input-wrapper input, .input-wrapper select, .input-wrapper textarea { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 12px 14px; font-size: 12px; font-weight: 700; outline: none; font-family: inherit; width: 100%; }
    .input-wrapper textarea { resize: vertical; min-height: 80px; }
    
    /* Select2 Tweaks */
    .select2-container--default .select2-selection--single { background-color: var(--bg-base); border: none; height: 42px; border-radius: 14px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-main); line-height: 42px; padding-left: 14px; font-weight: 700; font-size: 12px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
    .select2-dropdown { background-color: var(--bg-surface); border-color: var(--border-subtle); border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #2563eb; }
    .select2-results__option { color: var(--text-main); font-weight: 600; font-size: 12px; }

    select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 34px !important; background-size: 10px; }

    select optgroup { font-weight: 900; color: var(--text-muted); background: var(--bg-base-alt); text-transform: uppercase; font-size: 10px; padding: 5px; }
    select option { font-weight: 600; color: var(--text-main); background: var(--bg-surface); font-size: 12px; padding: 5px; }

    .checkbox-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 15px; }
    .checkbox-label { position: relative; cursor: pointer; display: block; }
    .checkbox-label input { position: absolute; opacity: 0; }
    .checkbox-box { display: flex; align-items: center; gap: 8px; padding: 12px 14px; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; font-size: 11px; font-weight: 800; color: var(--text-muted); transition: .25s ease; }
    .checkbox-label input:checked + .checkbox-box { background: rgba(16,185,129,.1); border-color: #10b981; color: #10b981; box-shadow: 0 10px 20px -16px rgba(16,185,129,.5); }

    .btn-submit { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 13px 20px; border: none; border-radius: 14px; font-weight: 900; font-size: 13px; cursor: pointer; transition: .25s ease; box-shadow: 0 12px 24px -16px rgba(37,99,235,.65); display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 10px; }
    .btn-submit:hover { transform: translateY(-2px); filter: brightness(1.05); }
</style>

<form action="<?= base_url('/employee/update/' . $employee['id']) ?>" method="post">
    <?= csrf_field() ?>

    <div class="page-header">
        <div class="page-title-wrap">
            <div class="page-icon"><i class="ph-fill ph-user-gear"></i></div>
            <div class="page-title">
                <h1>Edit Karyawan</h1>
                <p>Perbarui data employee, payroll, akun login, dan hak akses.</p>
            </div>
        </div>
        <a href="<?= base_url('/employee') ?>" class="btn-back">&larr; Kembali</a>
    </div>

    <div class="main-grid">

        <div class="bento-card">
            <div class="card-header">
                <div class="icon-wrapper"><i class="ph-bold ph-identification-card"></i></div>
                <h3>Profil Karyawan</h3>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Induk (NIK ERP)</label>
                <div class="input-wrapper" style="background: rgba(0,0,0,0.02); border-color: transparent;">
                    <input type="text" value="<?= esc($employee['employee_id']) ?>" readonly style="cursor:not-allowed; color:var(--text-muted); font-family:'Space Mono', monospace; font-size:13px; font-weight:900;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-wrapper"><input type="text" name="name" required value="<?= old('name', $employee['name']) ?>" style="text-transform: uppercase;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">NIK KTP (Identitas)</label>
                    <div class="input-wrapper"><input type="number" name="nik_ktp" value="<?= old('nik_ktp', $employee['nik_ktp']) ?>" placeholder="16 Digit NIK KTP"></div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Nomor HP</label>
                    <div class="input-wrapper"><input type="tel" name="phone" value="<?= old('phone', $employee['phone']) ?>" placeholder="0812..."></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Pernikahan</label>
                    <div class="input-wrapper">
                        <select name="marital_status">
                            <option value="Lajang" <?= old('marital_status', $employee['marital_status']) == 'Lajang' ? 'selected' : '' ?>>Lajang</option>
                            <option value="Menikah" <?= old('marital_status', $employee['marital_status']) == 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                            <option value="TK/0" <?= old('marital_status', $employee['marital_status']) == 'TK/0' ? 'selected' : '' ?>>TK/0</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Domisili</label>
                <div class="input-wrapper"><textarea name="address" placeholder="Alamat lengkap..."><?= old('address', $employee['address']) ?></textarea></div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Kontak Darurat (Nama)</label>
                    <div class="input-wrapper"><input type="text" name="emergency_contact_name" value="<?= old('emergency_contact_name', $employee['emergency_contact_name']) ?>" placeholder="Nama Istri/Orang Tua"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Kontak Darurat (No HP)</label>
                    <div class="input-wrapper"><input type="tel" name="emergency_contact_phone" value="<?= old('emergency_contact_phone', $employee['emergency_contact_phone']) ?>" placeholder="0812..."></div>
                </div>
            </div>

            <hr style="border:0; border-top:1px dashed var(--border-subtle); margin:18px 0;">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Departemen</label>
                    <div class="input-wrapper">
                        <select name="department_id" required id="departmentSelect" onchange="toggleGradeLevel()">
                            <option value="">-- Pilih Departemen --</option>
                            <?php foreach($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>" data-name="<?= esc($dept['name']) ?>" <?= old('department_id', $employee['department_id'] ?? '') == $dept['id'] ? 'selected' : '' ?>>
                                    <?= esc($dept['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Jabatan (Posisi)</label>
                    <div class="input-wrapper">
                        <select name="position_id" required>
                            <option value="">-- Pilih Posisi --</option>
                            <?php foreach($positions as $pos): ?>
                                <option value="<?= $pos['id'] ?>" <?= old('position_id', $employee['position_id'] ?? '') == $pos['id'] ? 'selected' : '' ?>>
                                    <?= esc($pos['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group" id="wrapSpecialty" style="display:none;">
                    <label class="form-label" style="color: #8b5cf6;">Spesialisasi Produksi</label>
                    <div class="input-wrapper" style="border-color: rgba(139, 92, 246, 0.4);">
                        <select name="specialty">
                            <option value="">-- Spesialisasi --</option>
                            <?php foreach($specialties as $spec): ?>
                                <option value="<?= $spec ?>" <?= old('specialty', $employee['specialty'] ?? '') == $spec ? 'selected' : '' ?>><?= $spec ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" id="wrapGradeLevel" style="display:none;">
                    <label class="form-label" style="color: #f59e0b;">Level / Grade (Khusus Produksi)</label>
                    <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.4);">
                        <select name="grade_level">
                            <option value="">-- Pilih Grade --</option>
                            <option value="A" <?= old('grade_level', $employee['grade_level']) == 'A' ? 'selected' : '' ?>>Grade A</option>
                            <option value="B" <?= old('grade_level', $employee['grade_level']) == 'B' ? 'selected' : '' ?>>Grade B</option>
                            <option value="C" <?= old('grade_level', $employee['grade_level']) == 'C' ? 'selected' : '' ?>>Grade C</option>
                            <option value="Senior" <?= old('grade_level', $employee['grade_level']) == 'Senior' ? 'selected' : '' ?>>Senior</option>
                            <option value="Junior" <?= old('grade_level', $employee['grade_level']) == 'Junior' ? 'selected' : '' ?>>Junior</option>
                            <option value="Trainee" <?= old('grade_level', $employee['grade_level']) == 'Trainee' ? 'selected' : '' ?>>Trainee / Magang</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Tanggal Bergabung</label>
                    <div class="input-wrapper"><input type="date" name="join_date" value="<?= old('join_date', $employee['join_date']) ?>" required></div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" style="color: #ef4444;">Status Keaktifan</label>
                    <div class="input-wrapper" style="border-color: rgba(239, 68, 68, 0.4);">
                        <select name="is_active" required>
                            <option value="1" <?= old('is_active', $employee['is_active']) == 1 ? 'selected' : '' ?>>Aktif Bekerja & Login</option>
                            <option value="0" <?= old('is_active', $employee['is_active']) == 0 ? 'selected' : '' ?>>Non-Aktif / Resign</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" style="color: #ef4444;">Tanggal Resign (Bila Non-Aktif)</label>
                <div class="input-wrapper" style="border-color: rgba(239, 68, 68, 0.4);">
                    <input type="date" name="resign_date" value="<?= old('resign_date', $employee['resign_date']) ?>">
                </div>
            </div>

        </div>

        <div class="bento-card">
            <div class="card-header">
                <div class="icon-wrapper" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph-bold ph-wallet"></i></div>
                <h3>Payroll, Login & Hak Akses</h3>
            </div>

            <div style="background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); padding: 12px; border-radius: 12px; margin-bottom: 15px; font-size: 11px; color: var(--text-muted); font-weight: 600;">
                <i class="ph-fill ph-users-three" style="color: #f59e0b;"></i>
                <b>Sistem Mandor/Regu:</b> Jika karyawan ini punya Bos (Mandor), pilih namanya di bawah ini agar gajinya digabung. Kosongkan jika mandiri.
            </div>

            <div class="form-group">
                <label class="form-label" style="color: #f59e0b;">Mandor / Kepala Regu (Opsional)</label>
                <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.3); padding: 0;">
                    <select name="leader_id" class="select2-leader">
                        <option value=""></option>
                        <?php foreach($leaders as $ld): ?>
                            <option value="<?= $ld['employee_id'] ?>" <?= old('leader_id', $employee['leader_id'] ?? '') == $ld['employee_id'] ? 'selected' : '' ?>>
                                <?= esc($ld['name']) ?> (<?= esc($ld['employee_id']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Shift Kerja</label>
                    <div class="input-wrapper">
                        <select name="shift_id" required>
                            <?php foreach($shifts as $shift): ?>
                                <option value="<?= $shift['id'] ?>" <?= old('shift_id', $employee['shift_id']) == $shift['id'] ? 'selected' : '' ?>>
                                    <?= esc($shift['shift_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Pekerja</label>
                    <div class="input-wrapper">
                        <select name="status" required id="statusSelect">
                            <option value="Tetap" <?= old('status', $employee['status']) == 'Tetap' ? 'selected' : '' ?>>Tetap</option>
                            <option value="Borongan" <?= old('status', $employee['status']) == 'Borongan' ? 'selected' : '' ?>>Borongan</option>
                            <option value="Magang" <?= old('status', $employee['status']) == 'Magang' ? 'selected' : '' ?>>Magang / PKL</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Siklus Gaji</label>
                <div class="input-wrapper">
                    <select name="salary_type" required>
                        <option value="Mingguan" <?= old('salary_type', $employee['salary_type']) == 'Mingguan' ? 'selected' : '' ?>>Mingguan</option>
                        <option value="Harian" <?= old('salary_type', $employee['salary_type']) == 'Harian' ? 'selected' : '' ?>>Harian</option>
                        <option value="Bulanan" <?= old('salary_type', $employee['salary_type']) == 'Bulanan' ? 'selected' : '' ?>>Bulanan</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Metode Pembayaran</label>
                <div class="input-wrapper">
                    <select name="payment_method" required id="paymentMethodSelect">
                        <option value="Cash" <?= old('payment_method', $employee['payment_method']) == 'Cash' ? 'selected' : '' ?>>Cash (Tunai)</option>
                        <option value="Transfer" <?= old('payment_method', $employee['payment_method']) == 'Transfer' ? 'selected' : '' ?>>Transfer Bank</option>
                    </select>
                </div>
            </div>

            <div id="bankFields" style="display: none;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Nama Bank</label>
                        <div class="input-wrapper"><input type="text" name="bank_name" value="<?= old('bank_name', $employee['bank_name']) ?>" placeholder="Contoh: BCA / BRI"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No Rekening</label>
                        <div class="input-wrapper"><input type="text" name="bank_account" value="<?= old('bank_account', $employee['bank_account']) ?>" placeholder="Contoh: 1234567890"></div>
                    </div>
                </div>
            </div>

            <div class="salary-fixed-only">
                <div class="form-group">
                    <label class="form-label" style="color: #2563eb;">Gaji Pokok (Harian / Bulanan)</label>
                    <div class="input-wrapper" style="border-color: #2563eb; box-shadow: 0 0 0 2px rgba(37,99,235,.1);">
                        <div class="prefix" style="color: #2563eb; background: rgba(37,99,235,.1); padding: 12px 14px; font-weight: 900;">Rp</div>
                        <input type="text" name="basic_salary" value="<?= old('basic_salary') ? esc(old('basic_salary')) : number_format($employee['basic_salary'] ?? 0, 0, ',', '.') ?>" onkeyup="formatRupiah(this)" autocomplete="off" style="font-size: 14px; font-weight: 900; color: #2563eb; font-family: 'Space Mono', monospace;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Tunjangan Jabatan</label>
                        <div class="input-wrapper">
                            <div class="prefix" style="padding: 12px; background:var(--bg-base-alt); font-weight: 800;">Rp</div>
                            <input type="text" name="position_allowance" value="<?= old('position_allowance') ? esc(old('position_allowance')) : number_format($employee['position_allowance'] ?? 0, 0, ',', '.') ?>" onkeyup="formatRupiah(this)" autocomplete="off" style="font-family: 'Space Mono', monospace;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Uang Makan / Hari</label>
                        <div class="input-wrapper">
                            <div class="prefix" style="padding: 12px; background:var(--bg-base-alt); font-weight: 800;">Rp</div>
                            <input type="text" name="meal_allowance" value="<?= old('meal_allowance') ? esc(old('meal_allowance')) : number_format($employee['meal_allowance'] ?? 0, 0, ',', '.') ?>" onkeyup="formatRupiah(this)" autocomplete="off" style="font-family: 'Space Mono', monospace;">
                        </div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Uang Transport / Siklus</label>
                        <div class="input-wrapper">
                            <div class="prefix" style="padding: 12px; background:var(--bg-base-alt); font-weight: 800;">Rp</div>
                            <input type="text" name="transport_allowance" value="<?= old('transport_allowance') ? esc(old('transport_allowance')) : number_format($employee['transport_allowance'] ?? 0, 0, ',', '.') ?>" onkeyup="formatRupiah(this)" autocomplete="off" style="font-family: 'Space Mono', monospace;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tarif Lembur / Jam</label>
                        <div class="input-wrapper">
                            <div class="prefix" style="padding: 12px; background:var(--bg-base-alt); font-weight: 800;">Rp</div>
                            <input type="text" name="overtime_rate" value="<?= old('overtime_rate') ? esc(old('overtime_rate')) : number_format($employee['overtime_rate'] ?? 0, 0, ',', '.') ?>" onkeyup="formatRupiah(this)" autocomplete="off" style="font-family: 'Space Mono', monospace;">
                        </div>
                    </div>
                </div>
            </div>

            <hr style="border:0; border-top:1px dashed var(--border-subtle); margin:18px 0;">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Username Login Web</label>
                    <div class="input-wrapper"><input type="text" name="username" required value="<?= old('username', $user['username'] ?? '') ?>"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <div class="input-wrapper"><input type="text" name="password" value="<?= old('password') ?>" placeholder="Kosongkan jika tak diubah"></div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Role Aplikasi Web</label>
                    <div class="input-wrapper">
                        <select name="role" required>
                            <option value="karyawan" <?= old('role', $user['role'] ?? 'karyawan') == 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                            <option value="admin" <?= old('role', $user['role'] ?? '') == 'admin' ? 'selected' : '' ?>>Admin / HRD</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Privilege Mesin IoT</label>
                    <div class="input-wrapper">
                        <select name="machine_privilege">
                            <option value="0" <?= old('machine_privilege', $employee['machine_privilege']) == '0' ? 'selected' : '' ?>>User Biasa</option>
                            <option value="14" <?= old('machine_privilege', $employee['machine_privilege']) == '14' ? 'selected' : '' ?>>Admin Mesin</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="pin" value="<?= esc($employee['pin']) ?>">
            <input type="hidden" name="rfid" value="<?= esc($employee['rfid']) ?>">
            <input type="hidden" name="finger_count" value="<?= esc($employee['finger_count']) ?>">
            <input type="hidden" name="face_count" value="<?= esc($employee['face_count']) ?>">

            <hr style="border:0; border-top:1px dashed var(--border-subtle); margin:18px 0;">

            <div class="form-label">Potongan Wajib (Asuransi)</div>
            <div class="checkbox-grid" style="margin-bottom: 8px;">
                <label class="checkbox-label">
                    <input type="hidden" name="bpjs_kesehatan" value="0">
                    <input type="checkbox" name="bpjs_kesehatan" id="checkBpjsKs" value="1" <?= old('bpjs_kesehatan', $employee['bpjs_kesehatan']) ? 'checked' : '' ?> onchange="toggleBpjsKs()">
                    <div class="checkbox-box"><i class="ph-bold ph-heartbeat"></i> BPJS Kesehatan</div>
                </label>
                <label class="checkbox-label">
                    <input type="hidden" name="bpjs_ketenagakerjaan" value="0">
                    <input type="checkbox" name="bpjs_ketenagakerjaan" id="checkBpjsTk" value="1" <?= old('bpjs_ketenagakerjaan', $employee['bpjs_ketenagakerjaan']) ? 'checked' : '' ?> onchange="toggleBpjsTk()">
                    <div class="checkbox-box"><i class="ph-bold ph-hard-hat"></i> BPJS Ketenagakerjaan</div>
                </label>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group" id="wrapBpjsKs" style="display: <?= old('bpjs_kesehatan', $employee['bpjs_kesehatan']) ? 'block' : 'none' ?>; margin: 0;">
                    <label class="form-label">No. BPJS Kesehatan</label>
                    <div class="input-wrapper"><input type="number" name="bpjs_ks_number" value="<?= old('bpjs_ks_number', $employee['bpjs_ks_number']) ?>" placeholder="Nomor Kartu"></div>
                </div>
                <div class="form-group" id="wrapBpjsTk" style="display: <?= old('bpjs_ketenagakerjaan', $employee['bpjs_ketenagakerjaan']) ? 'block' : 'none' ?>; margin: 0;">
                    <label class="form-label">No. BPJS Ketenagakerjaan</label>
                    <div class="input-wrapper"><input type="number" name="bpjs_tk_number" value="<?= old('bpjs_tk_number', $employee['bpjs_tk_number']) ?>" placeholder="Nomor Kartu"></div>
                </div>
            </div>

            <button type="submit" class="btn-submit" style="margin-top: 25px;">
                <i class="ph-bold ph-floppy-disk"></i> Update Data Karyawan
            </button>
        </div>
    </div>
</form>

<?= $this->endSection() ?>