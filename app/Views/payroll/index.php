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
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px; }
    .page-title p { color: var(--text-muted); font-size: 13px; }

    /* --- STAT BOXES --- */
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px; }
    .stat-box { padding: 25px; border-radius: 20px; color: #fff; position: relative; overflow: hidden; box-shadow: var(--shadow-card); display: flex; flex-direction: column; justify-content: space-between;}
    .stat-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; margin-bottom: 8px;}
    .stat-val { font-size: 28px; font-weight: 800; font-family: monospace; letter-spacing: -1px;}
    
    .box-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .box-orange { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .box-blue { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }

    /* --- BENTO CARD & FORM --- */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); padding: 25px; margin-bottom: 25px;}
    .card-header { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; border-bottom: 1px dashed var(--border-subtle); padding-bottom: 15px;}
    
    .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end; }
    .form-group label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;}
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 12px 15px; border-radius: 12px; font-size: 13px; font-family: inherit; color: var(--text-main); outline: none; transition: 0.2s;}
    .form-control:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light);}
    
    .btn-generate { background: var(--text-main); color: var(--bg-surface); border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; width: 100%; font-size: 13px;}
    .btn-generate:hover { transform: translateY(-2px); background: var(--accent-main); color: #fff;}

    /* --- TABLE --- */
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: left; padding: 15px 20px; font-size: 11px; font-weight: 800; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { padding: 16px 20px; border-bottom: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 500;}
    tr:hover td { background: rgba(0,0,0,0.01); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }
    
    .status-badge { padding: 6px 12px; border-radius: 100px; font-size: 11px; font-weight: 800; display: inline-flex; align-items: center; gap: 5px; border: 1px solid transparent;}
    .status-draft { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2); }
    .status-paid { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }

    .action-btns { display: flex; gap: 8px; justify-content: center; }
    .btn-icon { width: 34px; height: 34px; border-radius: 10px; border: none; background: var(--bg-base); color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 18px; transition: 0.2s; text-decoration: none; border: 1px solid var(--border-subtle);}
    .btn-icon:hover { filter: brightness(0.95); transform: translateY(-2px); }
    html.dark .btn-icon:hover { filter: brightness(1.2); }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Mesin Kalkulator Penggajian</h1>
        <p>Kalkulasi otomatis gaji karyawan berdasarkan data mesin IoT dan buku kas terintegrasi.</p>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-box box-green">
        <div>
            <div class="stat-label">Total Dana Dicairkan (Histori)</div>
            <div class="stat-val">Rp <?= number_format($total_paid, 0, ',', '.') ?></div>
        </div>
        <div style="font-size: 12px; opacity: 0.8; margin-top: 15px;"><i class="ph ph-check-circle"></i> Dari <?= $paid_count ?> Dokumen Lunas</div>
        <i class="ph ph-bank" style="position: absolute; right: -10px; bottom: -10px; font-size: 100px; opacity: 0.15; transform: rotate(-10deg);"></i>
    </div>
    <div class="stat-box box-orange">
        <div>
            <div class="stat-label">Dokumen Menggantung (Draft)</div>
            <div class="stat-val"><?= $draft_count ?> Dokumen</div>
        </div>
        <div style="font-size: 12px; opacity: 0.8; margin-top: 15px;"><i class="ph ph-warning-circle"></i> Membutuhkan otorisasi HRD</div>
        <i class="ph ph-files" style="position: absolute; right: -10px; bottom: -10px; font-size: 100px; opacity: 0.15; transform: rotate(10deg);"></i>
    </div>
</div>

<div class="bento-card" style="border: 1px solid var(--accent-main); box-shadow: 0 4px 20px var(--accent-light);">
    <div class="card-header" style="color: var(--accent-main); border-bottom-color: var(--accent-light);">
        <i class="ph ph-magic-wand"></i> Buat Dokumen Penggajian Baru (Generate)
    </div>
    <form action="<?= base_url('/payroll/generate') ?>" method="post" class="form-grid">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Tipe Karyawan</label>
            <select name="salary_type" class="form-control" required style="cursor: pointer; appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2371717a%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px top 50%; background-size: 10px auto;">
                <option value="Harian">Harian (Siklus Pendek)</option>
                <option value="Mingguan">Mingguan (Tiap Sabtu)</option>
                <option value="Bulanan" selected>Bulanan (Akhir Bulan)</option>
            </select>
        </div>
        <div class="form-group">
            <label>Cut-off Mulai</label>
            <input type="date" name="period_start" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Cut-off Selesai</label>
            <input type="date" name="period_end" class="form-control" required>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-generate" onclick="return confirm('Sistem akan menarik data absensi IoT pada rentang tanggal tersebut dan mengunci perhitungan. Lanjutkan?')">
                <i class="ph ph-gear-six"></i> Proses Kalkulasi Data
            </button>
        </div>
    </form>
</div>

<div class="bento-card" style="padding: 0; overflow: hidden;">
    <div class="card-header" style="margin-bottom: 0; padding: 20px 25px;">
        <i class="ph ph-list-dashes"></i> Riwayat Dokumen Ledger (Buku Gaji)
    </div>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Ref & Tanggal</th>
                    <th>Tipe Gaji</th>
                    <th>Rentang Periode Absen</th>
                    <th style="text-align: center;">Pekerja</th>
                    <th style="text-align: right;">Total Dana</th>
                    <th style="text-align: center;">Status Dokumen</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($payrolls)): ?>
                    <tr><td colspan="7" style="text-align: center; padding: 60px 20px; color: var(--text-muted);"><i class="ph ph-folder-open" style="font-size: 48px; margin-bottom: 10px; display: block;"></i>Belum ada riwayat dokumen penggajian.</td></tr>
                <?php else: ?>
                    <?php foreach($payrolls as $pay): ?>
                    <tr>
                        <td>
                            <div style="font-family: monospace; color: var(--accent-main); font-weight: 800; font-size: 14px;"><?= esc($pay['payroll_code']) ?></div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px; font-weight: 600;">Dibuat: <?= date('d M Y, H:i', strtotime($pay['created_at'])) ?></div>
                        </td>
                        <td>
                            <span style="background: var(--bg-base); padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 800; border: 1px solid var(--border-subtle);"><?= strtoupper(esc($pay['salary_type'])) ?></span>
                        </td>
                        <td style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                            <i class="ph ph-calendar-blank"></i> <?= date('d M Y', strtotime($pay['period_start'])) ?> &rarr; <?= date('d M Y', strtotime($pay['period_end'])) ?>
                        </td>
                        <td style="text-align: center;"><span style="font-weight: 800;"><?= $pay['total_employees'] ?></span> <span style="font-size: 11px; color: var(--text-muted);">Org</span></td>
                        <td style="text-align: right; color: #10b981; font-weight: 800; font-family: monospace; font-size: 15px;">Rp <?= number_format($pay['total_amount'], 0, ',', '.') ?></td>
                        
                        <td style="text-align: center;">
                            <?php if($pay['status'] == 'Draft'): ?>
                                <span class="status-badge status-draft"><i class="ph ph-clock"></i> DRAFT</span>
                            <?php else: ?>
                                <span class="status-badge status-paid"><i class="ph ph-check-circle"></i> PAID</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="action-btns">
                                <a href="<?= base_url('/payroll/detail/' . $pay['id']) ?>" class="btn-icon" style="color: var(--accent-main); background: var(--accent-light); border-color: transparent;" title="Lihat Rincian">
                                    <i class="ph ph-eye"></i>
                                </a>
                                <?php if($pay['status'] == 'Draft'): ?>
                                    <a href="<?= base_url('/payroll/delete/' . $pay['id']) ?>" onclick="return confirm('Hapus dokumen draft ini? Data tidak dapat dikembalikan.')" class="btn-icon" style="color: #ef4444; background: rgba(239, 68, 68, 0.1); border-color: transparent;" title="Hapus Dokumen">
                                        <i class="ph ph-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <button disabled class="btn-icon" style="opacity: 0.3; cursor: not-allowed;" title="Dokumen Lunas tidak bisa dihapus">
                                        <i class="ph ph-lock-key"></i>
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
    <?php if(session()->getFlashdata('success')): ?>
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#38bdf8', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b' });
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        Swal.fire({ icon: 'error', title: 'Peringatan Sistem', text: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444' });
    <?php endif; ?>
</script>
<?= $this->endSection() ?>