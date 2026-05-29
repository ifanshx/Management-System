<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
if (!function_exists('format_compact')) {
    function format_compact($angka) {
        $isNegative = $angka < 0;
        $val = abs((float)$angka);
        
        if ($val >= 1000000000) {
            $res = number_format($val / 1000000000, 2, ',', '.') . ' M'; 
        } elseif ($val >= 1000000) {
            $res = number_format($val / 1000000, 2, ',', '.') . ' Jt'; 
        } else {
            $res = number_format($val, 0, ',', '.'); 
        }
        
        return $isNegative ? '-' . $res : $res;
    }
}
?>

<style>
    /* =========================================================
       1. AMBIENT GLOW & PAGE HEADER
       ========================================================= */
    .ambient-glow { position: absolute; top: -150px; left: 50%; transform: translateX(-50%); width: 800px; height: 500px; background: radial-gradient(circle, rgba(14, 165, 233, 0.08) 0%, transparent 70%); z-index: 0; pointer-events: none;}
    html.dark .ambient-glow { background: radial-gradient(circle, rgba(14, 165, 233, 0.15) 0%, transparent 70%); }

    .page-wrapper { position: relative; z-index: 1; }

    .page-header { margin-bottom: 35px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;}
    
    .page-title { display: flex; align-items: center; gap: 20px;}
    .title-icon { width: 64px; height: 64px; border-radius: 20px; background: linear-gradient(135deg, #0ea5e9, #0369a1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 12px 30px -8px rgba(14, 165, 233, 0.6); border: 1px solid rgba(255,255,255,0.15);}
    .title-text { display: flex; flex-direction: column; gap: 8px;}
    .title-text h1 { font-size: 32px; font-weight: 900; margin: 0; letter-spacing: -1px; line-height: 1; color: var(--text-main);}
    .title-text p { display: flex; align-items: center; gap: 10px; font-size: 14px; color: var(--text-muted); font-weight: 600; margin: 0;}
    
    /* =========================================================
       2. KPI CARDS
       ========================================================= */
    .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 25px; }
    .stat-box { padding: 25px; border-radius: 20px; color: #fff; position: relative; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); display: flex; flex-direction: column; justify-content: space-between; transition: 0.3s;}
    .stat-box:hover { transform: translateY(-4px); box-shadow: 0 15px 35px -5px rgba(0,0,0,0.15); }
    .stat-label { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; margin-bottom: 8px;}
    .stat-val { font-size: 32px; font-weight: 900; font-family: 'Space Mono', monospace; letter-spacing: -1px; line-height: 1;}
    .box-green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .box-orange { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }

    /* =========================================================
       3. BENTO CARDS & COMPONENTS
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; padding: 30px; box-shadow: 0 10px 40px -15px rgba(0,0,0,0.05); margin-bottom: 25px;}
    .card-title-main { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;}
    .card-title-main i { color: #2563eb; font-size: 20px;}

    .form-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; background: var(--bg-base); padding: 15px; border-radius: 16px; border: 1px dashed var(--border-subtle);}
    @media (max-width: 1024px) { .form-grid { grid-template-columns: 1fr; } }
    .form-group label { display: block; font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;}
    .form-control { width: 100%; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 12px 14px; border-radius: 12px; font-size: 13px; font-weight: 700; color: var(--text-main); outline: none; transition: 0.3s; cursor: pointer; font-family: inherit; appearance: none;}
    .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);}
    .date-input { font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 900; color: #2563eb;}
    
    .btn-generate { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; border: none; padding: 12px 20px; border-radius: 12px; font-weight: 900; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.3s; font-size: 13px; box-shadow: 0 8px 20px -5px rgba(37, 99, 235, 0.5);}
    .btn-generate:hover { transform: translateY(-2px); box-shadow: 0 12px 25px -5px rgba(37, 99, 235, 0.6);}

    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 14px 12px; font-size: 10px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); border-bottom: 2px solid var(--border-subtle); background: var(--bg-base); letter-spacing: 0.5px;}
    td { text-align: center; padding: 15px 12px; border-bottom: 1px dashed var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600; vertical-align: middle;}
    th:first-child, td:first-child { text-align: left; padding-left: 20px;}
    th:last-child, td:last-child { padding-right: 20px; }
    tr:hover td { background: rgba(37, 99, 235, 0.02); }
    
    .status-badge { padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 900; display: inline-flex; align-items: center; gap: 6px; letter-spacing: 0.5px; border: 1px solid transparent;}
    .status-draft { background: rgba(245, 158, 11, 0.1); color: #d97706; border-color: rgba(245, 158, 11, 0.3); }
    .status-paid { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.3); }
    .status-info { background: rgba(37, 99, 235, 0.1); color: #2563eb; border-color: rgba(37, 99, 235, 0.3); }

    .action-btns { display: flex; gap: 6px; justify-content: center; }
    .btn-icon { width: 34px; height: 34px; border-radius: 10px; border: none; background: var(--bg-base); color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; transition: 0.3s; text-decoration: none;}
    .btn-detail { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
    .btn-detail:hover { background: #2563eb; color: #fff; transform: translateY(-2px);}
    .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
    .btn-delete:hover { background: #ef4444; color: #fff; transform: translateY(-2px);}
    .btn-lock { background: rgba(100, 116, 139, 0.1); color: #94a3b8; cursor: not-allowed; }
</style>

<div class="ambient-glow"></div>

<div class="page-wrapper">

<?php 
    $total_paid = 0; $draft_count = 0; $paid_count = 0;
    foreach($payrolls as $p) {
        if(strpos($p['status'] ?? '', 'Paid') !== false) {
            $total_paid += (float) ($p['total_amount'] ?? 0); 
            $paid_count++;
        } else { 
            $draft_count++; 
        }
    }
?>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-calculator"></i></div>
        <div class="title-text">
            <h1>Mesin Kalkulator Penggajian</h1>
            <p>Proses kalkulasi otomatis, menghitung Upah Borongan & Gaji Pokok secara presisi.</p>
        </div>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-box box-green">
        <div>
            <div class="stat-label">Total Dana Dicairkan (Histori)</div>
            <div class="stat-val">Rp <?= format_compact($total_paid) ?></div>
        </div>
        <div style="font-size: 11px; font-weight: 800; opacity: 0.9; margin-top: 12px; display: flex; align-items: center; gap: 6px;">
            <i class="ph-fill ph-check-circle"></i> Dari <?= $paid_count ?> Dokumen Lunas
        </div>
        <i class="ph-fill ph-vault" style="position: absolute; right: -10px; bottom: -15px; font-size: 100px; opacity: 0.15; transform: rotate(-10deg);"></i>
    </div>
    
    <div class="stat-box box-orange">
        <div>
            <div class="stat-label">Dokumen Menggantung (Draft)</div>
            <div class="stat-val"><?= $draft_count ?> Dokumen</div>
        </div>
        <div style="font-size: 11px; font-weight: 800; opacity: 0.9; margin-top: 12px; display: flex; align-items: center; gap: 6px;">
            <i class="ph-fill ph-warning-circle"></i> Membutuhkan Otorisasi Pencairan
        </div>
        <i class="ph-fill ph-files" style="position: absolute; right: -10px; bottom: -15px; font-size: 100px; opacity: 0.15; transform: rotate(10deg);"></i>
    </div>
</div>

<div class="bento-card" style="border-top: 6px solid #2563eb;">
    <div class="card-title-main">
        <i class="ph-bold ph-magic-wand"></i> Buat Dokumen Penggajian Baru (Generate)
    </div>
    
    <form action="<?= base_url('/payroll/generate') ?>" method="post" class="form-grid">
        <?= csrf_field() ?>
        
        <div class="form-group" style="margin: 0;">
            <label>Status Pekerja</label>
            <select name="employee_status" class="form-control" required style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%232563eb%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px top 50%; background-size: 10px auto;">
                <option value="Tetap">Tetap (Tarik Semua Jenis Penghasilan)</option>
                <option value="Borongan">Borongan Murni (Tanpa Gaji Pokok)</option>
            </select>
        </div>

        <div class="form-group" style="margin: 0;">
            <label>Siklus Gaji</label>
            <select name="salary_type" class="form-control" required style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%232563eb%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 12px top 50%; background-size: 10px auto;">
                <option value="Harian">Harian</option>
                <option value="Mingguan" selected>Mingguan</option>
                <option value="Bulanan">Bulanan</option>
            </select>
        </div>

        <div class="form-group" style="margin: 0;">
            <label>Mulai (Cut-off)</label>
            <input type="date" name="period_start" class="form-control date-input" required>
        </div>
        <div class="form-group" style="margin: 0;">
            <label>Selesai (Cut-off)</label>
            <input type="date" name="period_end" class="form-control date-input" required>
        </div>
        <div class="form-group" style="margin: 0;">
            <button type="submit" class="btn-generate" onclick="return confirmCustom(event, this)">
                <i class="ph-bold ph-gear-six"></i> <span>Kalkulasi</span>
            </button>
        </div>
    </form>
</div>

<div class="bento-card" style="padding: 0; overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px dashed var(--border-subtle); display: flex; align-items: center; gap: 8px; font-weight: 900; font-size: 14px; color: var(--text-main); background: rgba(0,0,0,0.01);">
        <i class="ph-fill ph-folders" style="color: var(--text-muted); font-size: 20px;"></i> Riwayat Buku Gaji (Ledger)
    </div>
    
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Ref ID & Pembuatan</th>
                    <th style="text-align:center;">Filter Pekerja</th>
                    <th style="text-align:center;">Rentang Periode</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Total Dana (Rp)</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($payrolls)): ?>
                    <tr>
                        <td colspan="7">
                            <div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">
                                <i class="ph-fill ph-folder-open" style="font-size: 48px; margin-bottom: 10px; display: block; color: var(--border-subtle);"></i>
                                <div style="font-weight: 800; font-size: 14px; color: var(--text-main);">Belum Ada Riwayat</div>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($payrolls as $pay): ?>
                    <tr>
                        <td>
                            <div style="font-family: 'Space Mono', monospace; color: #2563eb; font-weight: 900; font-size: 13px; background: rgba(37, 99, 235, 0.1); padding: 4px 8px; border-radius: 6px; display: inline-block; margin-bottom: 4px;">
                                <?= esc($pay['payroll_code'] ?? '-') ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); font-weight: 700; display: flex; align-items: center; gap: 4px;">
                                <i class="ph-bold ph-clock"></i> <?= date('d M Y, H:i', strtotime($pay['created_at'] ?? 'now')) ?>
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <div style="font-size: 12px; font-weight: 800; color: var(--text-main); margin-bottom: 4px;"><?= esc($pay['employee_status'] ?? '-') ?></div>
                            <span class="status-badge status-info"><?= strtoupper(esc($pay['salary_type'] ?? '-')) ?></span>
                        </td>
                        <td style="font-size: 12px; font-weight: 800; color: var(--text-muted); text-align:center;">
                            <?= date('d M', strtotime($pay['period_start'] ?? 'now')) ?> - <?= date('d M Y', strtotime($pay['period_end'] ?? 'now')) ?>
                        </td>
                        <td style="text-align: center;">
                            <span style="font-weight: 900; font-size: 14px; font-family: 'Space Mono', monospace;"><?= esc($pay['total_employees'] ?? 0) ?></span> 
                            <span style="font-size: 10px; color: var(--text-muted); font-weight: 700;">Org</span>
                        </td>
                        <td style="text-align: right; color: #10b981; font-weight: 900; font-family: 'Space Mono', monospace; font-size: 15px;">
                            <?= number_format((float)($pay['total_amount'] ?? 0), 0, ',', '.') ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if(($pay['status'] ?? '') == 'Draft'): ?>
                                <span class="status-badge status-draft"><i class="ph-bold ph-warning-circle"></i> DRAFT</span>
                            <?php else: ?>
                                <span class="status-badge status-paid"><i class="ph-bold ph-check-circle"></i> PAID</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <div class="action-btns">
                                <a href="<?= base_url('/payroll/detail/' . ($pay['id'] ?? '')) ?>" class="btn-icon btn-detail" title="Rincian Slip Gaji">
                                    <i class="ph-bold ph-eye"></i>
                                </a>
                                <?php if(($pay['status'] ?? '') == 'Draft'): ?>
                                    <a href="#" onclick="confirmDeleteDoc(event, '<?= base_url('/payroll/delete/' . ($pay['id'] ?? '')) ?>')" class="btn-icon btn-delete" title="Hapus Dokumen">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <button disabled class="btn-icon btn-lock" title="Terkunci"><i class="ph-bold ph-lock-key"></i></button>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if(session()->getFlashdata('success')): ?>
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({ icon: 'success', title: 'Berhasil!', html: <?= json_encode(session()->getFlashdata('success')) ?>, confirmButtonColor: '#38bdf8', background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' } });
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        Swal.fire({ icon: 'error', title: 'Perhatian Sistem', html: <?= json_encode(session()->getFlashdata('error')) ?>, confirmButtonColor: '#ef4444', customClass: { popup: 'swal2-custom-radius' } });
    <?php endif; ?>

    function confirmCustom(e, btn) {
        e.preventDefault();
        const form = btn.closest('form');
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Kalkulasi Gaji?',
            html: '<span style="font-size:14px;">Sistem akan menarik data absensi, produksi borongan, kasbon, dan uang makan secara otomatis.</span>',
            icon: 'info', showCancelButton: true, confirmButtonColor: '#2563eb',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', confirmButtonText: 'Ya, Mulai', cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                btn.innerHTML = `<i class="ph-bold ph-spinner-gap ph-spin" style="font-size: 18px;"></i> <span>Proses...</span>`;
                btn.disabled = true; form.submit();
            }
        });
    }

    function confirmDeleteDoc(e, url) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Hapus Dokumen?',
            text: 'Dokumen gaji dibatalkan, dan Kasbon yang terpotong akan dihidupkan kembali.',
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        });
    }
</script>
<?= $this->endSection() ?>