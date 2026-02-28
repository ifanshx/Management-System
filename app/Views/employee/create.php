<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* --- HEADER & LAYOUT --- */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;}
    .page-title h1 { font-size: 24px; font-weight: 800; color: var(--text-main); margin-bottom: 4px; letter-spacing: -0.5px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    .main-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; align-items: start; }
    @media (max-width: 1024px) { .main-grid { grid-template-columns: 1fr; } }

    /* --- BENTO CARDS --- */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 25px; box-shadow: var(--shadow-card); }
    .card-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed var(--border-subtle); }
    .icon-wrapper { width: 36px; height: 36px; border-radius: 10px; background: var(--accent-light); color: var(--accent-main); display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .card-header h3 { font-size: 15px; font-weight: 800; color: var(--text-main); margin: 0; }

    /* --- FORM ELEMENTS --- */
    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; overflow: hidden; transition: all 0.2s ease; }
    .input-wrapper:focus-within { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }
    .input-wrapper input, .input-wrapper select, .input-wrapper textarea { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 16px; font-size: 13px; font-weight: 600; outline: none; font-family: inherit; width: 100%;}
    .input-wrapper textarea { resize: vertical; min-height: 80px; }
    
    .prefix { background: rgba(0,0,0,0.02); color: var(--text-muted); font-size: 13px; font-weight: 800; padding: 0 16px; display: flex; align-items: center; border-right: 1px solid var(--border-subtle); }
    html.dark .prefix { background: rgba(255,255,255,0.02); }

    select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 40px !important; }

    /* --- CUSTOM TOGGLE CARD (BPJS) --- */
    .checkbox-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;}
    .checkbox-label { position: relative; cursor: pointer; display: block; }
    .checkbox-label input { position: absolute; opacity: 0; }
    .checkbox-box { display: flex; align-items: center; gap: 8px; padding: 14px 16px; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 12px; font-size: 12px; font-weight: 700; color: var(--text-muted); transition: 0.2s; }
    .checkbox-label input:checked + .checkbox-box { background: rgba(16, 185, 129, 0.1); border-color: #10b981; color: #10b981; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);}

    .btn-submit { background: var(--accent-main); color: #fff; padding: 14px 30px; border: none; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 12px var(--accent-light); display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin-top: 10px;}
    .btn-submit:hover { transform: translateY(-3px); filter: brightness(1.1); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Tambah Karyawan Baru</h1>
        <p>Daftarkan profil lengkap, atur penugasan, dan tentukan parameter penggajian.</p>
    </div>
    <a href="<?= base_url('/employee') ?>" style="background: var(--bg-surface); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 10px; font-weight: 700; text-decoration: none; font-size: 13px; transition: 0.2s;">
        &larr; Batal & Kembali
    </a>
</div>

<form action="<?= base_url('/employee/store') ?>" method="post">
    <?= csrf_field() ?>
    
    <div class="main-grid">
        
        <div class="bento-card">
            <div class="card-header">
                <div class="icon-wrapper"><i class="ph ph-user-plus"></i></div>
                <h3>Profil Dasar & Penugasan</h3>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Induk Karyawan (NIK) <span style="color:var(--accent-main); text-transform:none; font-weight:600;">(Otomatis)</span></label>
                <div class="input-wrapper" style="background: rgba(0,0,0,0.02); border-color: transparent;">
                    <input type="text" name="employee_id" value="<?= esc($autoNIK) ?>" readonly style="cursor: not-allowed; color: var(--text-muted); font-family: monospace; font-size: 14px; font-weight: 800;">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-wrapper">
                    <input type="text" name="name" placeholder="Nama sesuai KTP" required autocomplete="off">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Nomor HP</label>
                    <div class="input-wrapper">
                        <input type="tel" name="phone" placeholder="08..." autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Pernikahan</label>
                    <div class="input-wrapper">
                        <select name="marital_status">
                            <option value="Lajang">Lajang</option>
                            <option value="Menikah">Menikah</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat Domisili</label>
                <div class="input-wrapper">
                    <textarea name="address" placeholder="Alamat lengkap..."></textarea>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px dashed var(--border-subtle); margin: 20px 0;">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Departemen</label>
                    <div class="input-wrapper">
                        <select name="department" required>
                            <option value="" disabled selected>-- Pilih Dept --</option>
                            <option value="Manajemen & HRD">Manajemen & HRD</option>
                            <option value="Produksi & Manufaktur">Produksi & Manufaktur</option>
                            <option value="Quality Control & R&D">Quality Control & R&D</option>
                            <option value="Gudang & Logistik">Gudang & Logistik</option>
                            <option value="Sales & Marketing">Sales & Marketing</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Jabatan</label>
                    <div class="input-wrapper">
                        <input type="text" name="position" placeholder="Contoh: Staff" required autocomplete="off">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Tanggal Bergabung</label>
                    <div class="input-wrapper">
                        <input type="date" name="join_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Shift Kerja (Wajib)</label>
                    <div class="input-wrapper" style="border-color: var(--accent-main); box-shadow: 0 0 0 2px var(--accent-light);">
                        <select name="shift_id" required>
                            <option value="" disabled selected>-- Pilih Jadwal Shift --</option>
                            <?php foreach($shifts as $shift): ?>
                                <option value="<?= $shift['id'] ?>">
                                    <?= esc($shift['shift_name']) ?> (<?= date('H:i', strtotime($shift['time_in'])) ?> - <?= date('H:i', strtotime($shift['time_out'])) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Akses Mesin (IoT)</label>
                    <div class="input-wrapper">
                        <select name="machine_privilege">
                            <option value="0">0 - User Biasa</option>
                            <option value="14">14 - Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Pekerja</label>
                    <div class="input-wrapper">
                        <select name="status">
                            <option value="Tetap">Karyawan Tetap</option>
                            <option value="Kontrak">Kontrak</option>
                            <option value="Magang">Magang / Internship</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px dashed var(--border-subtle); margin: 20px 0;">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Username Portal</label>
                    <div class="input-wrapper">
                        <input type="text" name="username" placeholder="Saran: Gunakan NIK" required autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Portal</label>
                    <div class="input-wrapper">
                        <input type="password" name="password" placeholder="Min. 6 karakter" required autocomplete="off">
                    </div>
                </div>
            </div>
            
        </div>

        <div class="bento-card">
            <div class="card-header">
                <div class="icon-wrapper" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="ph ph-wallet"></i></div>
                <h3>Parameter Penggajian</h3>
            </div>

            <div class="form-group">
                <label class="form-label">Tipe Pembayaran Gaji</label>
                <div class="input-wrapper">
                    <select name="salary_type" required>
                        <option value="Bulanan" selected>Bulanan (Akhir Bulan)</option>
                        <option value="Mingguan">Mingguan (Per Sabtu)</option>
                        <option value="Harian">Harian</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" style="color: var(--accent-main);">Gaji Pokok Utama</label>
                <div class="input-wrapper" style="border-color: var(--accent-main); box-shadow: 0 2px 8px var(--accent-light);">
                    <div class="prefix" style="color: var(--accent-main); background: var(--accent-light);">Rp</div>
                    <input type="text" name="basic_salary" placeholder="0" onkeyup="formatRupiah(this)" style="font-size: 16px; font-weight: 800; color: var(--accent-main);" required autocomplete="off">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Tunjangan Jabatan</label>
                    <div class="input-wrapper">
                        <div class="prefix">Rp</div>
                        <input type="text" name="position_allowance" placeholder="0" onkeyup="formatRupiah(this)" autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Uang Makan /Hari</label>
                    <div class="input-wrapper">
                        <div class="prefix">Rp</div>
                        <input type="text" name="meal_allowance" placeholder="0" onkeyup="formatRupiah(this)" autocomplete="off">
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Tunj. Transport</label>
                    <div class="input-wrapper">
                        <div class="prefix">Rp</div>
                        <input type="text" name="transport_allowance" placeholder="0" onkeyup="formatRupiah(this)" autocomplete="off">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tarif Lembur /Jam</label>
                    <div class="input-wrapper">
                        <div class="prefix">Rp</div>
                        <input type="text" name="overtime_rate" placeholder="0" onkeyup="formatRupiah(this)" autocomplete="off">
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px dashed var(--border-subtle); margin: 20px 0;">

            <div class="form-label">Potongan Wajib (Asuransi)</div>
            <div class="checkbox-grid">
                <label class="checkbox-label">
                    <input type="hidden" name="bpjs_kesehatan" value="0">
                    <input type="checkbox" name="bpjs_kesehatan" value="1">
                    <div class="checkbox-box"><i class="ph ph-heartbeat" style="font-size: 18px;"></i> BPJS Kesehatan</div>
                </label>
                <label class="checkbox-label">
                    <input type="hidden" name="bpjs_ketenagakerjaan" value="0">
                    <input type="checkbox" name="bpjs_ketenagakerjaan" value="1">
                    <div class="checkbox-box"><i class="ph ph-hard-hat" style="font-size: 18px;"></i> BPJS TK (Kerja)</div>
                </label>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Nama Bank</label>
                    <div class="input-wrapper">
                        <input type="text" name="bank_name" placeholder="BCA / BRI" autocomplete="off">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Nomor Rekening</label>
                    <div class="input-wrapper">
                        <input type="text" name="bank_account" placeholder="123456789" style="font-family: monospace;" autocomplete="off">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="ph ph-check-circle"></i> Daftarkan Karyawan
            </button>
        </div>
    </div>
</form>

<?= $this->endSection() ?>