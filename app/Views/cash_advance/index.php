<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --brand: #ef4444; --brand-dark: #dc2626; --brand-soft: rgba(239, 68, 68, 0.1);
        --bg-main: #f8fafc; --card-bg: #ffffff; --border-color: #e2e8f0;
        --text-main: #0f172a; --text-muted: #64748b;
        --shadow-md: 0 4px 20px -10px rgba(0,0,0,0.05);
        --shadow-hover: 0 10px 30px -10px rgba(0,0,0,0.08);
        --transition-smooth: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .swal2-custom-radius { border-radius: 20px !important; font-family: 'Plus Jakarta Sans', sans-serif; }
    
    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 800px; height: 500px; background: radial-gradient(circle, rgba(239, 68, 68, 0.05) 0%, transparent 70%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(circle, rgba(239, 68, 68, 0.1) 0%, transparent 70%); }

    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px; position: relative; z-index: 1;}
    .page-title { display: flex; align-items: center; gap: 16px;}
    .title-icon { width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.4);}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0; letter-spacing: -1px; line-height: 1.1;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 600; margin: 4px 0 0 0;}

    .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 2fr); gap: 24px; align-items: start; position: relative; z-index: 1; padding-bottom: 40px;}
    @media (max-width: 1024px) { .dashboard-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 24px; padding: 26px; box-shadow: var(--shadow-md); transition: var(--transition-smooth); }
    .bento-card:hover { box-shadow: var(--shadow-hover); }
    .card-header { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 22px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-color); padding-bottom: 16px;}

    .form-group { margin-bottom: 18px; }
    .form-label { display: block; font-size: 11px; font-weight: 900; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-main); border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden; transition: 0.3s; }
    .input-wrapper:focus-within { border-color: var(--brand); box-shadow: 0 0 0 4px var(--brand-soft); background: var(--card-bg);}
    .input-wrapper input, .input-wrapper select { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 14px 16px; font-size: 13px; font-weight: 700; outline: none; font-family: inherit; width: 100%;}
    
    .prefix { background: var(--brand-soft); color: var(--brand); font-size: 16px; font-weight: 900; padding: 0 16px; display: flex; align-items: center; border-right: 1px solid rgba(239, 68, 68, 0.2); }
    select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 256 256'%3E%3Cpath d='M213.66,101.66l-80,80a8,8,0,0,1-11.32,0l-80-80A8,8,0,0,1,53.66,90.34L128,164.69l74.34-74.35a8,8,0,0,1,11.32,11.32Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 40px !important; cursor: pointer;}

    .btn-submit { background: linear-gradient(135deg, var(--brand), var(--brand-dark)); color: #fff; padding: 18px; border: none; border-radius: 16px; font-weight: 900; font-size: 15px; cursor: pointer; transition: 0.3s; box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.5); width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 10px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 14px 30px -5px rgba(239, 68, 68, 0.6); }

    /* TABLE */
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 18px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-main); border-bottom: 2px solid var(--border-color); letter-spacing: 0.5px;}
    td { text-align: left; padding: 16px 18px; border-bottom: 1px dashed var(--border-color); color: var(--text-main); font-size: 13px; font-weight: 700; vertical-align: middle; transition: 0.2s;}
    tr:hover td { background: var(--brand-soft); }
    tr:last-child td { border-bottom: none; }
    
    .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px;}
    .status-lunas { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .status-belum { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2);}

    .btn-delete { color: var(--brand); background: var(--brand-soft); font-size: 18px; transition: 0.3s; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; text-decoration: none;}
    .btn-delete:hover { background: var(--brand); color: #fff; transform: translateY(-2px) scale(1.05);}
</style>

<div class="ambient-glow"></div>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-hand-coins"></i></div>
        <div>
            <h1>Kasbon & Pinjaman Karyawan</h1>
            <p>Catat pengeluaran kasbon (potong uang laci) dan atur skema auto-potong gaji.</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="bento-card" style="border-top: 6px solid var(--brand);">
        <div class="card-header" style="color: var(--brand);">
            <i class="ph-bold ph-plus-circle" style="font-size: 22px;"></i> Formulir Pencairan Kasbon
        </div>

        <form action="<?= base_url('/cash_advance/store') ?>" method="post" id="formKasbon">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Pilih Karyawan Peminjam</label>
                <div class="input-wrapper">
                    <select name="employee_id" required>
                        <option value="" disabled selected>-- Pilih Karyawan --</option>
                        <?php foreach($employees as $emp): ?>
                            <option value="<?= esc($emp['employee_id']) ?>"><?= esc($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Total Pinjaman Yang Diberikan</label>
                <div class="input-wrapper" style="border-color: var(--brand); box-shadow: 0 4px 15px var(--brand-soft);">
                    <div class="prefix">Rp</div>
                    <input type="text" name="amount" placeholder="0" onkeyup="formatRupiah(this)" required style="font-family: 'Space Mono', monospace; font-size: 20px; font-weight: 900; color: var(--brand);" autocomplete="off">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Dibagi Berapa Cicilan?</label>
                    <div class="input-wrapper">
                        <select name="tenure" required>
                            <option value="1">1x Potongan Gaji</option>
                            <option value="2">2x Potong Gaji (Cicil 2x)</option>
                            <option value="3">3x Potong Gaji (Cicil 3x)</option>
                            <option value="4">4x Potong Gaji (Cicil 4x)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tgl Potongan Pertama</label>
                    <div class="input-wrapper">
                        <input type="date" name="first_tempo_date" value="<?= date('Y-m-d', strtotime('next saturday')) ?>" required style="font-family: 'Space Mono', monospace; font-size: 13px;">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Keterangan / Keperluan</label>
                <div class="input-wrapper">
                    <input type="text" name="description" placeholder="Catatan singkat (Opsional)..." required autocomplete="off">
                </div>
            </div>

            <button type="submit" class="btn-submit" id="btnSubmit">
                <i class="ph-bold ph-paper-plane-tilt" style="font-size: 20px;"></i> <span>Cairkan Kasbon & Potong Kas</span>
            </button>
        </form>
    </div>

    <div class="bento-card">
        <div class="card-header">
            <i class="ph-bold ph-list-dashes" style="font-size: 22px;"></i> Daftar Tagihan / Cicilan Aktif
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th style="text-align: right;">Besaran (Rp)</th>
                        <th style="text-align: center;">Jatuh Tempo</th>
                        <th>Keterangan</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($kasbon)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                                <i class="ph-fill ph-check-circle" style="font-size: 56px; color: #10b981; margin-bottom: 12px; display: block; opacity: 0.4;"></i>
                                <b style="font-size: 16px; color: var(--text-main);">Tidak ada hutang aktif.</b><br>Semua karyawan bebas dari kasbon.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($kasbon as $kb): ?>
                        <tr style="<?= $kb['status'] == 'Lunas' ? 'opacity: 0.4;' : '' ?>">
                            <td>
                                <div style="font-weight: 800; color: var(--text-main); font-size: 14px; margin-bottom: 4px;"><?= esc($kb['name']) ?></div>
                                <div style="font-size: 10px; font-family: 'Space Mono', monospace; color: var(--text-muted); background: var(--bg-main); padding: 4px 8px; border-radius: 6px; display: inline-block; border: 1px solid var(--border-color);"><i class="ph-bold ph-identification-card"></i> <?= esc($kb['employee_id']) ?></div>
                            </td>
                            <td style="text-align: right; font-family: 'Space Mono', monospace; font-weight: 900; font-size: 15px; color: <?= $kb['status'] == 'Lunas' ? 'var(--text-muted)' : 'var(--brand)' ?>;">
                                <?= number_format($kb['amount'], 0, ',', '.') ?>
                            </td>
                            <td style="text-align: center;">
                                <span style="background: var(--bg-main); padding: 6px 10px; border-radius: 8px; font-family: 'Space Mono', monospace; font-size: 12px; border: 1px solid var(--border-color); font-weight: 800;">
                                    <?= date('d M Y', strtotime($kb['tempo_date'])) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--text-muted); max-width: 150px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;" title="<?= esc($kb['description']) ?>">
                                <?= esc($kb['description']) ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($kb['status'] == 'Lunas'): ?>
                                    <span class="status-badge status-lunas"><i class="ph-bold ph-check-circle"></i> LUNAS</span>
                                <?php else: ?>
                                    <span class="status-badge status-belum"><i class="ph-bold ph-hourglass-high"></i> PENDING</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($kb['status'] == 'Belum Lunas'): ?>
                                    <a href="#" onclick="confirmDelete(event, '<?= base_url('/cash_advance/delete/' . $kb['id']) ?>')" class="btn-delete" title="Batalkan Tagihan">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <i class="ph-bold ph-lock-key" style="color: var(--text-muted); font-size: 18px;" title="Terkunci (Masuk Payroll)"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // SweetAlert Interceptor
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const swalBg = isDark ? '#18181b' : '#ffffff';
        const swalText = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#10b981', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal!', html: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444', background: swalBg, color: swalText, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });

    // Form Auto-Rupiah
    function formatRupiah(angka) {
        let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
            split   = number_string.split(','),
            sisa    = split[0].length % 3,
            rupiah  = split[0].substr(0, sisa),
            ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
        if (ribuan) { let separator = sisa ? '.' : ''; rupiah += separator + ribuan.join('.'); }
        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        angka.value = rupiah;
    }

    // Clean Dots Before Submit
    document.getElementById('formKasbon').addEventListener('submit', function(e) {
        let amountInput = this.querySelector('input[name="amount"]');
        amountInput.value = amountInput.value.replace(/\./g, '');

        let btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 20px;"></i> <span>MENCATAT...</span>';
    });

    // Custom Delete Dialog
    function confirmDelete(e, url) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Hapus Cicilan Ini?',
            text: 'Catatan hutang ini akan dihapus. (PENTING: Jangan lupa VOID pengeluaran kasbon di menu Akuntansi/Keuangan jika uang batal dicairkan).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, showConfirmButton: false, background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }, didOpen: () => { Swal.showLoading() }});
                window.location.href = url;
            }
        });
    }
</script>

<?= $this->endSection() ?>