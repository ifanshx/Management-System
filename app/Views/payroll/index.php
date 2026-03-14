<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php 
    // Kalkulasi Statistik Cepat dari Data yang ada
    $total_paid = 0;
    $draft_count = 0;
    $paid_count = 0;
    foreach($payrolls as $p) {
        if(strpos($p['status'], 'Paid') !== false) {
            $total_paid += $p['total_amount'];
            $paid_count++;
        } else {
            $draft_count++;
        }
    }
?>

<style>
    /* =========================================================
       1. PAGE HEADER
       ========================================================= */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px; flex-wrap: wrap; gap: 20px;}
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(37, 99, 235, 0.15), rgba(29, 78, 216, 0.05)); color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid rgba(37, 99, 235, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px; }
    .page-title p { color: var(--text-muted); font-size: 13px; font-weight: 500; margin: 0;}

    /* =========================================================
       2. STAT BOXES (FINANCIAL METRICS)
       ========================================================= */
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 30px; }
    
    .stat-box { padding: 30px; border-radius: 24px; color: #fff; position: relative; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: space-between; transition: 0.3s;}
    .stat-box:hover { transform: translateY(-5px); box-shadow: 0 15px 35px -5px rgba(0,0,0,0.15); }
    
    .stat-label { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; margin-bottom: 10px;}
    .stat-val { font-size: 36px; font-weight: 900; font-family: 'Space Mono', monospace; letter-spacing: -1px; line-height: 1;}
    
    .box-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .box-orange { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }

    /* =========================================================
       3. BENTO CARD & INLINE GENERATOR FORM
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 25px;}
    .card-header { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 10px;}
    
    .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 15px; align-items: end; background: var(--bg-base); padding: 15px; border-radius: 16px; border: 1px dashed var(--border-subtle);}
    @media (max-width: 1024px) { .form-grid { grid-template-columns: 1fr; } }

    .form-group label { display: block; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 14px 16px; border-radius: 12px; font-size: 13px; font-weight: 700; color: var(--text-main); outline: none; transition: 0.3s; cursor: pointer; font-family: inherit; appearance: none;}
    .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);}
    
    .date-input { font-family: 'Space Mono', monospace; font-size: 14px; font-weight: 900; color: #2563eb;}

    .btn-generate { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; padding: 16px 30px; border-radius: 14px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: 0.3s; font-size: 15px; box-shadow: 0 8px 20px -5px rgba(37, 99, 235, 0.5);}
    .btn-generate:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -5px rgba(37, 99, 235, 0.6);}

    /* =========================================================
       4. ANALYTICAL TABLE (PAYROLL HISTORY)
       ========================================================= */
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 18px 20px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { text-align: center; padding: 18px 20px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 14px; font-weight: 600; vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:nth-child(2), td:nth-child(2) { text-align: left; }
    th:last-child, td:last-child { border-right: none; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(37, 99, 235, 0.02); }
    html.dark tr:hover td { background: rgba(37, 99, 235, 0.05); }
    
    .status-badge { padding: 6px 14px; border-radius: 8px; font-size: 11px; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; letter-spacing: 0.5px; border: 1px solid transparent;}
    .status-draft { background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.3); }
    .status-paid { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.3); }

    .action-btns { display: flex; gap: 8px; justify-content: center; }
    .btn-icon { width: 36px; height: 36px; border-radius: 10px; border: none; background: var(--bg-base); color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: 0.3s; text-decoration: none;}
    
    .btn-detail { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
    .btn-detail:hover { background: #2563eb; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);}
    
    .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .btn-delete:hover { background: #ef4444; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);}
    
    .btn-lock { background: rgba(100, 116, 139, 0.1); color: #94a3b8; cursor: not-allowed; }
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-calculator"></i></div>
        <div>
            <h1>Mesin Kalkulator Penggajian</h1>
            <p>Proses kalkulasi otomatis gaji karyawan, tunjangan, potongan absensi, dan PPh.</p>
        </div>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-box box-green">
        <div>
            <div class="stat-label">Total Dana Dicairkan (Histori)</div>
            <div class="stat-val">Rp <?= number_format($total_paid, 0, ',', '.') ?></div>
        </div>
        <div style="font-size: 13px; font-weight: 800; opacity: 0.9; margin-top: 15px; display: flex; align-items: center; gap: 6px;">
            <i class="ph-fill ph-check-circle" style="font-size: 18px;"></i> 
            Dari <?= $paid_count ?> Dokumen Lunas
        </div>
        <i class="ph-fill ph-vault" style="position: absolute; right: -15px; bottom: -20px; font-size: 120px; opacity: 0.15; transform: rotate(-10deg);"></i>
    </div>
    
    <div class="stat-box box-orange">
        <div>
            <div class="stat-label">Dokumen Menggantung (Draft)</div>
            <div class="stat-val"><?= $draft_count ?> Dokumen</div>
        </div>
        <div style="font-size: 13px; font-weight: 800; opacity: 0.9; margin-top: 15px; display: flex; align-items: center; gap: 6px;">
            <i class="ph-fill ph-warning-circle" style="font-size: 18px;"></i> 
            Membutuhkan Pengecekan & Otorisasi Keuangan
        </div>
        <i class="ph-fill ph-files" style="position: absolute; right: -15px; bottom: -20px; font-size: 120px; opacity: 0.15; transform: rotate(10deg);"></i>
    </div>
</div>

<div class="bento-card" style="border-top: 6px solid #2563eb; padding: 25px 30px;">
    <div class="card-header" style="color: #2563eb;">
        <div style="background: rgba(37, 99, 235, 0.1); padding: 8px; border-radius: 10px;"><i class="ph-bold ph-magic-wand" style="font-size: 20px;"></i></div> 
        Buat Dokumen Penggajian Baru (Generate)
    </div>
    
    <form action="<?= base_url('/payroll/generate') ?>" method="post" class="form-grid">
        <?= csrf_field() ?>
        
        <div class="form-group" style="margin: 0;">
            <label>Tipe Siklus Penggajian</label>
            <select name="salary_type" class="form-control" required style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%232563eb%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px top 50%; background-size: 10px auto;">
                <option value="Harian">Gaji Harian (Siklus Pendek)</option>
                <option value="Mingguan">Gaji Mingguan (Tiap Sabtu)</option>
                <option value="Bulanan" selected>Gaji Bulanan (Akhir Bulan)</option>
            </select>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label>Periode Absen (Mulai)</label>
            <input type="date" name="period_start" class="form-control date-input" required>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <label>Periode Absen (Selesai)</label>
            <input type="date" name="period_end" class="form-control date-input" required>
        </div>
        
        <div class="form-group" style="margin: 0;">
            <button type="submit" class="btn-generate" onclick="return confirmCustom(event, this)">
                <i class="ph-bold ph-gear-six" style="font-size: 20px;"></i> <span>Kalkulasi Otomatis</span>
            </button>
        </div>
    </form>
</div>

<div class="bento-card" style="padding: 0; overflow: hidden;">
    <div style="padding: 25px 30px; border-bottom: 2px dashed var(--border-subtle); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.01);">
        <div style="font-size: 18px; font-weight: 900; color: var(--text-main); display: flex; align-items: center; gap: 10px;">
            <i class="ph-fill ph-folders" style="color: var(--text-muted); font-size: 22px;"></i> Riwayat Dokumen Buku Gaji (Ledger)
        </div>
    </div>
    
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Ref ID & Pembuatan</th>
                    <th>Tipe Gaji</th>
                    <th>Rentang Periode Absen (Cut-off)</th>
                    <th style="text-align: center;">Jml Pekerja</th>
                    <th style="text-align: right;">Total Dana (Rp)</th>
                    <th style="text-align: center;">Status Dokumen</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($payrolls)): ?>
                    <tr>
                        <td colspan="7">
                            <div style="text-align: center; padding: 80px 20px; color: var(--text-muted);">
                                <i class="ph-fill ph-folder-open" style="font-size: 56px; margin-bottom: 15px; display: block; color: var(--border-subtle);"></i>
                                <div style="font-weight: 800; font-size: 16px; color: var(--text-main); margin-bottom: 4px;">Belum Ada Riwayat</div>
                                <div style="font-size: 13px; font-weight: 500;">Gunakan form di atas untuk membuat dokumen penggajian pertama Anda.</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($payrolls as $pay): ?>
                    <tr>
                        <td>
                            <div style="font-family: 'Space Mono', monospace; color: #2563eb; font-weight: 900; font-size: 14px; background: rgba(37, 99, 235, 0.1); padding: 4px 8px; border-radius: 6px; display: inline-block; margin-bottom: 6px;">
                                <?= esc($pay['payroll_code']) ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; gap: 4px;">
                                <i class="ph-bold ph-clock"></i> <?= date('d M Y, H:i', strtotime($pay['created_at'])) ?>
                            </div>
                        </td>
                        <td>
                            <span style="background: var(--bg-base); padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 900; border: 1px dashed var(--border-subtle); letter-spacing: 0.5px;">
                                <?= strtoupper(esc($pay['salary_type'])) ?>
                            </span>
                        </td>
                        <td style="font-size: 12px; font-weight: 800; color: var(--text-muted);">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="ph-bold ph-calendar-blank"></i> 
                                <?= date('d M Y', strtotime($pay['period_start'])) ?> 
                                <i class="ph-bold ph-arrow-right"></i> 
                                <?= date('d M Y', strtotime($pay['period_end'])) ?>
                            </div>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: 900; font-size: 18px; font-family: 'Space Mono', monospace;"><?= $pay['total_employees'] ?></span> 
                            <span style="font-size: 11px; color: var(--text-muted); font-weight: 700;">Org</span>
                        </td>
                        <td style="text-align: right; color: #10b981; font-weight: 900; font-family: 'Space Mono', monospace; font-size: 16px;">
                            Rp <?= number_format($pay['total_amount'], 0, ',', '.') ?>
                        </td>
                        
                        <td style="text-align: center;">
                            <?php if($pay['status'] == 'Draft'): ?>
                                <span class="status-badge status-draft"><i class="ph-bold ph-warning-circle"></i> DRAFT</span>
                            <?php else: ?>
                                <span class="status-badge status-paid"><i class="ph-bold ph-check-circle"></i> PAID</span>
                            <?php endif; ?>
                        </td>

                        <td style="text-align: center;">
                            <div class="action-btns">
                                <a href="<?= base_url('/payroll/detail/' . $pay['id']) ?>" class="btn-icon btn-detail" title="Rincian Potongan & Slip Gaji">
                                    <i class="ph-bold ph-eye"></i>
                                </a>
                                <?php if($pay['status'] == 'Draft'): ?>
                                    <a href="#" onclick="confirmDeleteDoc(event, '<?= base_url('/payroll/delete/' . $pay['id']) ?>')" class="btn-icon btn-delete" title="Hapus Dokumen Draft">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <button disabled class="btn-icon btn-lock" title="Dokumen Lunas sudah terkunci di Buku Besar">
                                        <i class="ph-bold ph-lock-key"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // --- Custom Confirm Generate ---
    function confirmCustom(e, btn) {
        e.preventDefault();
        const form = btn.closest('form');
        const isDark = document.documentElement.classList.contains('dark');
        
        Swal.fire({
            title: 'Kalkulasi Gaji Sekarang?',
            html: 'Sistem akan menarik data absensi IoT dari mesin, menghitung keterlambatan, pajak, dan mencetak slip gaji untuk semua karyawan terkait pada rentang tanggal ini.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Mulai Proses Mesin',
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
        }).then((result) => {
            if (result.isConfirmed) {
                btn.innerHTML = `<i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 20px;"></i> <span>Memproses Data...</span>`;
                btn.disabled = true;
                form.submit();
            }
        });
        return false;
    }

    // --- Custom Confirm Delete Draft ---
    function confirmDeleteDoc(e, url) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        
        Swal.fire({
            title: 'Hapus Dokumen Draft?',
            text: 'Tindakan ini akan menghapus dokumen gaji dan semua slip gaji terkait. Data yang dihapus tidak bisa dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Hapus Dokumen',
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    }
</script>

<?= $this->endSection() ?>