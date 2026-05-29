<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    :root {
        --bg-base: #f8fafc;
        --bg-surface: #ffffff;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --border-subtle: #e2e8f0;
    }
    html.dark {
        --bg-base: #0f172a;
        --bg-surface: #1e293b;
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
        --border-subtle: #334155;
    }

    /* =======================================================
       PREMIUM SWEETALERT CUSTOMIZATION 
       ======================================================= */
    .swal2-container.swal2-backdrop-show { background: rgba(15, 23, 42, 0.75) !important; backdrop-filter: blur(5px) !important; }
    .swal2-popup.swal2-custom-radius { border-radius: 24px !important; padding: 2.5em 2em 2em 2em !important; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25) !important; border: 1px solid var(--border-subtle) !important; background: var(--bg-surface) !important; color: var(--text-main) !important; }
    .swal2-title { font-family: 'Plus Jakarta Sans', sans-serif !important; font-weight: 900 !important; color: var(--text-main) !important; font-size: 24px !important; margin-bottom: 15px !important; }
    .swal2-html-container { font-family: 'Plus Jakarta Sans', sans-serif !important; font-weight: 600 !important; color: var(--text-muted) !important; font-size: 15px !important; line-height: 1.6 !important; margin-bottom: 25px !important; }
    .swal2-actions { gap: 12px !important; margin-top: 15px !important; }
    .swal2-confirm, .swal2-cancel { border-radius: 12px !important; font-weight: 800 !important; padding: 14px 28px !important; font-family: 'Plus Jakarta Sans', sans-serif !important; font-size: 15px !important; letter-spacing: 0.5px !important; transition: all 0.3s ease !important; }
    .swal2-confirm { box-shadow: 0 8px 20px -6px rgba(0,0,0,0.3) !important; }
    .swal2-confirm:hover { transform: translateY(-2px) !important; box-shadow: 0 12px 25px -6px rgba(0,0,0,0.4) !important; }
    .swal2-cancel { background: var(--bg-input) !important; color: var(--text-main) !important; border: 1px solid var(--border-subtle) !important; }
    .swal2-cancel:hover { background: var(--border-subtle) !important; transform: translateY(-2px) !important; }

    /* LAYOUT & FORM STYLES */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 18px; margin-bottom: 28px; }
    .page-title-wrap { display: flex; align-items: center; gap: 16px; }
    .page-icon { width: 56px; height: 56px; border-radius: 20px; background: linear-gradient(135deg, rgba(37,99,235,.15), rgba(59,130,246,.05)); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 25px; border: 1px solid rgba(37,99,235,.15); box-shadow: 0 14px 35px -18px rgba(37,99,235,.45); }
    .page-title h1 { font-size: 28px; font-weight: 900; margin: 0 0 5px 0; color: var(--text-main); letter-spacing: -0.7px; }
    .page-title p { margin: 0; font-size: 12px; color: var(--text-muted); font-weight: 600; }

    .btn-back { background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 16px; border-radius: 12px; font-weight: 800; text-decoration: none; font-size: 12px; transition: .25s ease; }
    .btn-back:hover { transform: translateY(-2px); }

    .main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: start; }
    @media (max-width: 1024px) { .main-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 22px; padding: 22px; box-shadow: 0 18px 40px -30px rgba(0,0,0,.22); }
    .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px dashed var(--border-subtle); }
    .icon-wrapper { width: 34px; height: 34px; border-radius: 12px; background: rgba(37,99,235,.08); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 17px; }
    .card-header h3 { font-size: 14px; font-weight: 900; color: var(--text-main); margin: 0; }

    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 10px; font-weight: 900; color: var(--text-muted); margin-bottom: 7px; text-transform: uppercase; letter-spacing: .6px; }
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; overflow: hidden; transition: .25s ease; }
    .input-wrapper:focus-within { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,.1); }
    .input-wrapper input, .input-wrapper select, .input-wrapper textarea { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 12px 14px; font-size: 12px; font-weight: 700; outline: none; font-family: inherit; width: 100%; }
    .input-wrapper textarea { resize: vertical; min-height: 80px; }
    select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 34px !important; background-size: 10px; }

    select optgroup { font-weight: 900; color: var(--text-muted); background: var(--bg-base); text-transform: uppercase; font-size: 10px; }
    select option { font-weight: 600; color: var(--text-main); background: var(--bg-surface); font-size: 12px; }

    .checkbox-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 15px; }
    .checkbox-label { position: relative; cursor: pointer; display: block; }
    .checkbox-label input { position: absolute; opacity: 0; }
    .checkbox-box { display: flex; align-items: center; gap: 8px; padding: 12px 14px; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; font-size: 11px; font-weight: 800; color: var(--text-muted); transition: .25s ease; }
    .checkbox-label input:checked + .checkbox-box { background: rgba(16,185,129,.1); border-color: #10b981; color: #10b981; box-shadow: 0 10px 20px -16px rgba(16,185,129,.5); }

    .btn-submit { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 13px 20px; border: none; border-radius: 14px; font-weight: 900; font-size: 13px; cursor: pointer; transition: .25s ease; box-shadow: 0 12px 24px -16px rgba(37,99,235,.65); display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 10px; }
    .btn-submit:hover { transform: translateY(-2px); filter: brightness(1.05); }

    /* Select2 Tweaks */
    .select2-container--default .select2-selection--single { background-color: var(--bg-base); border: 1px solid var(--border-subtle); height: 42px; border-radius: 14px; display: flex; align-items: center; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-main); font-weight: 700; font-size: 12px; padding-left: 14px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; right: 10px; }
    .select2-dropdown { background-color: var(--bg-surface); border-color: var(--border-subtle); border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; }
    .select2-search__field { border-radius: 8px !important; border: 1px solid var(--border-subtle) !important; background: var(--bg-base); color: var(--text-main); font-weight: 600; padding: 8px 12px !important; margin: 8px !important; width: calc(100% - 16px) !important; }
    .select2-container--default .select2-results__option--highlighted[aria-selected] { background-color: #2563eb; }
    .select2-results__option { color: var(--text-main); font-weight: 600; font-size: 12px; padding: 10px 14px; }
</style>

<div class="page-header">
    <div class="page-title-wrap">
        <div class="page-icon"><i class="ph-fill ph-user-plus"></i></div>
        <div class="page-title">
            <h1>Tambah Karyawan Baru</h1>
            <p>Input profil employee, payroll, akun login, dan integrasi mesin absensi.</p>
        </div>
    </div>
    <a href="<?= base_url('/employee') ?>" class="btn-back">&larr; Kembali</a>
</div>

<form action="<?= base_url('/employee/store') ?>" method="post" id="formEmployee">
    <?= csrf_field() ?>
    <div class="main-grid">

        <div class="bento-card">
            <div class="card-header">
                <div class="icon-wrapper"><i class="ph-bold ph-user-plus"></i></div>
                <h3>Profil Karyawan</h3>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Induk (NIK ERP)</label>
                <div class="input-wrapper" style="background: rgba(0,0,0,0.02); border-color: transparent;">
                    <input type="text" name="employee_id" value="<?= esc($autoNIK) ?>" readonly style="cursor:not-allowed; color:var(--text-muted); font-family:'Space Mono', monospace; font-size:13px; font-weight:900;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-wrapper"><input type="text" name="name" required value="<?= old('name') ?>" style="text-transform: uppercase;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">NIK KTP (Identitas)</label>
                    <div class="input-wrapper"><input type="number" name="nik_ktp" value="<?= old('nik_ktp') ?>" placeholder="16 Digit NIK KTP"></div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Nomor HP</label>
                    <div class="input-wrapper"><input type="tel" name="phone" value="<?= old('phone') ?>" placeholder="0812..."></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Pernikahan</label>
                    <div class="input-wrapper">
                        <select name="marital_status">
                            <option value="Lajang" <?= old('marital_status') == 'Lajang' ? 'selected' : '' ?>>Lajang</option>
                            <option value="Menikah" <?= old('marital_status') == 'Menikah' ? 'selected' : '' ?>>Menikah</option>
                            <option value="TK/0" <?= old('marital_status') == 'TK/0' ? 'selected' : '' ?>>TK/0</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Domisili</label>
                <div class="input-wrapper"><textarea name="address" placeholder="Alamat lengkap..."><?= old('address') ?></textarea></div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Kontak Darurat (Nama)</label>
                    <div class="input-wrapper"><input type="text" name="emergency_contact_name" value="<?= old('emergency_contact_name') ?>" placeholder="Nama Istri/Keluarga"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Kontak Darurat (No HP)</label>
                    <div class="input-wrapper"><input type="tel" name="emergency_contact_phone" value="<?= old('emergency_contact_phone') ?>" placeholder="0812..."></div>
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
                                <option value="<?= $dept['id'] ?>" data-name="<?= esc($dept['name']) ?>" <?= old('department_id') == $dept['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $pos['id'] ?>" <?= old('position_id') == $pos['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $spec ?>" <?= old('specialty') == $spec ? 'selected' : '' ?>><?= $spec ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" id="wrapGradeLevel" style="display:none;">
                    <label class="form-label" style="color: #f59e0b;">Level / Grade</label>
                    <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.4);">
                        <select name="grade_level">
                            <option value="">-- Pilih Grade --</option>
                            <option value="A" <?= old('grade_level') == 'A' ? 'selected' : '' ?>>Grade A</option>
                            <option value="B" <?= old('grade_level') == 'B' ? 'selected' : '' ?>>Grade B</option>
                            <option value="C" <?= old('grade_level') == 'C' ? 'selected' : '' ?>>Grade C</option>
                            <option value="Senior" <?= old('grade_level') == 'Senior' ? 'selected' : '' ?>>Senior</option>
                            <option value="Junior" <?= old('grade_level') == 'Junior' ? 'selected' : '' ?>>Junior</option>
                            <option value="Trainee" <?= old('grade_level') == 'Trainee' ? 'selected' : '' ?>>Trainee / Magang</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Bergabung</label>
                <div class="input-wrapper"><input type="date" name="join_date" value="<?= old('join_date') ?: date('Y-m-d') ?>" required></div>
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
                <div class="input-wrapper" style="border-color: rgba(245, 158, 11, 0.3); padding: 0; border: none; background: transparent;">
                    <select name="leader_id" class="select2-leader">
                        <option value=""></option>
                        <?php foreach($leaders as $ld): ?>
                            <option value="<?= $ld['employee_id'] ?>" <?= old('leader_id') == $ld['employee_id'] ? 'selected' : '' ?>>
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
                            <option value="" disabled <?= !old('shift_id') ? 'selected' : '' ?>>-- Pilih Shift --</option>
                            <?php foreach($shifts as $shift): ?>
                                <option value="<?= $shift['id'] ?>" <?= old('shift_id') == $shift['id'] ? 'selected' : '' ?>>
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
                            <option value="Tetap" <?= old('status') == 'Tetap' ? 'selected' : '' ?>>Tetap</option>
                            <option value="Borongan" <?= old('status') == 'Borongan' ? 'selected' : '' ?>>Borongan</option>
                            <option value="Magang" <?= old('status') == 'Magang' ? 'selected' : '' ?>>Magang / PKL</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Siklus Gaji</label>
                <div class="input-wrapper">
                    <select name="salary_type" required>
                        <option value="Mingguan" <?= old('salary_type', 'Mingguan') == 'Mingguan' ? 'selected' : '' ?>>Mingguan</option>
                        <option value="Harian" <?= old('salary_type') == 'Harian' ? 'selected' : '' ?>>Harian</option>
                        <option value="Bulanan" <?= old('salary_type') == 'Bulanan' ? 'selected' : '' ?>>Bulanan</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Metode Pembayaran</label>
                <div class="input-wrapper">
                    <select name="payment_method" required id="paymentMethodSelect">
                        <option value="Cash" <?= old('payment_method', 'Cash') == 'Cash' ? 'selected' : '' ?>>Cash (Tunai)</option>
                        <option value="Transfer" <?= old('payment_method') == 'Transfer' ? 'selected' : '' ?>>Transfer Bank</option>
                    </select>
                </div>
            </div>

            <div id="bankFields" style="display: none;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Nama Bank</label>
                        <div class="input-wrapper"><input type="text" name="bank_name" value="<?= old('bank_name') ?>" placeholder="Contoh: BCA / BRI"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">No Rekening</label>
                        <div class="input-wrapper"><input type="text" name="bank_account" value="<?= old('bank_account') ?>" placeholder="Contoh: 1234567890"></div>
                    </div>
                </div>
            </div>

            <div class="salary-fixed-only">
                <div class="form-group">
                    <label class="form-label" style="color: var(--primary);">Gaji Pokok (Harian / Bulanan)</label>
                    <div class="input-wrapper" style="border-color: var(--primary); box-shadow: 0 0 0 2px rgba(37,99,235,.1);">
                        <div class="prefix" style="color: var(--primary); background: rgba(37,99,235,.1); padding: 12px 14px; font-weight: 900;">Rp</div>
                        <input type="text" name="basic_salary" onkeyup="formatRupiah(this)" autocomplete="off" style="font-size: 14px; font-weight: 900; color: var(--primary); font-family: 'Space Mono', monospace;" value="<?= old('basic_salary') ?>">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Tunjangan Jabatan</label>
                        <div class="input-wrapper">
                            <div class="prefix" style="padding: 12px; background:var(--bg-base-alt); font-weight: 800;">Rp</div>
                            <input type="text" name="position_allowance" onkeyup="formatRupiah(this)" autocomplete="off" style="font-family: 'Space Mono', monospace;" value="<?= old('position_allowance') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Uang Makan / Hari</label>
                        <div class="input-wrapper">
                            <div class="prefix" style="padding: 12px; background:var(--bg-base-alt); font-weight: 800;">Rp</div>
                            <input type="text" name="meal_allowance" onkeyup="formatRupiah(this)" autocomplete="off" style="font-family: 'Space Mono', monospace;" value="<?= old('meal_allowance') ?>">
                        </div>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Uang Transport / Siklus</label>
                        <div class="input-wrapper">
                            <div class="prefix" style="padding: 12px; background:var(--bg-base-alt); font-weight: 800;">Rp</div>
                            <input type="text" name="transport_allowance" onkeyup="formatRupiah(this)" autocomplete="off" style="font-family: 'Space Mono', monospace;" value="<?= old('transport_allowance') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tarif Lembur / Jam</label>
                        <div class="input-wrapper">
                            <div class="prefix" style="padding: 12px; background:var(--bg-base-alt); font-weight: 800;">Rp</div>
                            <input type="text" name="overtime_rate" onkeyup="formatRupiah(this)" autocomplete="off" style="font-family: 'Space Mono', monospace;" value="<?= old('overtime_rate') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <hr style="border:0; border-top:1px dashed var(--border-subtle); margin:18px 0;">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Username Login Web</label>
                    <div class="input-wrapper"><input type="text" name="username" required value="<?= old('username') ?>"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Login Web</label>
                    <div class="input-wrapper"><input type="text" name="password" required value="<?= old('password') ?>"></div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label class="form-label">Role Aplikasi Web</label>
                    <div class="input-wrapper">
                        <select name="role" required>
                            <option value="karyawan" <?= old('role', 'karyawan') == 'karyawan' ? 'selected' : '' ?>>Karyawan</option>
                            <option value="admin" <?= old('role') == 'admin' ? 'selected' : '' ?>>Admin / HRD</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Privilege Mesin IoT</label>
                    <div class="input-wrapper">
                        <select name="machine_privilege">
                            <option value="0" <?= old('machine_privilege', '0') == '0' ? 'selected' : '' ?>>User Biasa</option>
                            <option value="14" <?= old('machine_privilege') == '14' ? 'selected' : '' ?>>Admin Mesin</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr style="border:0; border-top:1px dashed var(--border-subtle); margin:18px 0;">

            <div class="form-label">Potongan Wajib (Asuransi)</div>
            <div class="checkbox-grid" style="margin-bottom: 8px;">
                <label class="checkbox-label">
                    <input type="hidden" name="bpjs_kesehatan" value="0">
                    <input type="checkbox" name="bpjs_kesehatan" id="checkBpjsKs" value="1" <?= old('bpjs_kesehatan') ? 'checked' : '' ?> onchange="toggleBpjsKs()">
                    <div class="checkbox-box"><i class="ph-bold ph-heartbeat"></i> BPJS Kesehatan</div>
                </label>
                <label class="checkbox-label">
                    <input type="hidden" name="bpjs_ketenagakerjaan" value="0">
                    <input type="checkbox" name="bpjs_ketenagakerjaan" id="checkBpjsTk" value="1" <?= old('bpjs_ketenagakerjaan') ? 'checked' : '' ?> onchange="toggleBpjsTk()">
                    <div class="checkbox-box"><i class="ph-bold ph-hard-hat"></i> BPJS Ketenagakerjaan</div>
                </label>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group" id="wrapBpjsKs" style="display: none; margin: 0;">
                    <label class="form-label">No. BPJS Kesehatan</label>
                    <div class="input-wrapper"><input type="number" name="bpjs_ks_number" value="<?= old('bpjs_ks_number') ?>" placeholder="Nomor Kartu"></div>
                </div>
                <div class="form-group" id="wrapBpjsTk" style="display: none; margin: 0;">
                    <label class="form-label">No. BPJS Ketenagakerjaan</label>
                    <div class="input-wrapper"><input type="number" name="bpjs_tk_number" value="<?= old('bpjs_tk_number') ?>" placeholder="Nomor Kartu"></div>
                </div>
            </div>

            <button type="submit" class="btn-submit" style="margin-top: 25px;">
                <i class="ph-bold ph-floppy-disk"></i> Simpan Karyawan
            </button>
        </div>
    </div>
</form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const isDark = document.documentElement.classList.contains('dark');
    const bgColor = isDark ? '#1e293b' : '#ffffff';
    const textColor = isDark ? '#f8fafc' : '#0f172a';

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
    
    $('.select2-leader').select2({ width: '100%', placeholder: "-- Pilih Jika Punya Mandor --", allowClear: true });

    document.getElementById('formEmployee').addEventListener('submit', function() {
        Swal.fire({
            title: 'Menyimpan Data...',
            text: 'Mohon tunggu sebentar.',
            allowOutsideClick: false,
            showConfirmButton: false,
            background: bgColor,
            color: textColor,
            customClass: { popup: 'swal2-custom-radius' },
            didOpen: () => { Swal.showLoading(); }
        });
    });
});

function togglePayrollFields() {
    const status = document.getElementById('statusSelect').value;
    const salaryFields = document.querySelectorAll('.salary-fixed-only');
    salaryFields.forEach(el => {
        if (status === 'Borongan') {
            el.style.display = 'none';
            el.querySelectorAll('input').forEach(inp => inp.value = '0');
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
        document.querySelector('input[name="bank_name"]').value = '';
        document.querySelector('input[name="bank_account"]').value = '';
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
<?= $this->endSection() ?>