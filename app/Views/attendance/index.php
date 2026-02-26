<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>' });
        <?php endif; ?>
    });
</script>

<style>
    /* CSS Base Sebelumnya */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px;}
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px;}
    .page-title p { font-size: 13px; color: var(--text-muted); }

    .filter-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 15px 20px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; box-shadow: var(--shadow-card); }
    .filter-group { display: flex; gap: 15px; align-items: center; }
    .form-control { background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 10px 15px; border-radius: 10px; font-size: 13px; outline: none; }
    .btn-filter, .btn-sync { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.3s; display: flex; align-items: center; gap: 8px; text-decoration: none; font-size: 13px;}
    .btn-sync { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.2); }
    .btn-sync:hover { background: #38bdf8; color: #fff; }

    /* Pembaruan Tabel */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: var(--shadow-card); overflow: hidden; }
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 12px 15px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); }
    td { text-align: center; padding: 12px 15px; border-bottom: 1px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 500; }
    th:first-child, td:first-child { text-align: left; }
    th:last-child, td:last-child { border-right: none; }
    tr:hover td { background: rgba(0,0,0,0.01); }
    html.dark tr:hover td { background: rgba(255,255,255,0.02); }
    
    .time-badge { font-family: monospace; font-size: 13px; font-weight: 800; background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 4px 8px; border-radius: 6px; display: inline-block; }
    .time-empty { font-family: monospace; font-size: 13px; color: var(--border-subtle); }
    
    .status-badge { padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
    .status-badge.hadir { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .status-badge.terlambat { background: rgba(239, 68, 68, 0.1); color: #ef4444; }

    .highlight-blue { color: #38bdf8; font-weight: 800; }
    .highlight-purple { color: #a855f7; font-weight: 800; background: rgba(168,85,247,0.1); padding: 2px 6px; border-radius: 4px; }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Pantauan Kehadiran Pabrik</h1>
        <p>Sistem deteksi cerdas 4-Fase Scan (Masuk - Ist. Keluar - Ist. Masuk - Pulang) & Kalkulasi Lembur otomatis.</p>
    </div>
</div>

<div class="filter-box">
    <form action="" method="get" class="filter-group">
        <div style="font-weight: 700; font-size: 13px; color: var(--text-main);">Pilih Tanggal:</div>
        <input type="date" name="date" class="form-control" value="<?= esc($dateFilter) ?>">
        <button type="submit" class="btn-filter"><i class="ph ph-magnifying-glass"></i> Filter Data</button>
    </form>

    <a href="<?= base_url('/attendance/sync?date=' . $dateFilter) ?>" class="btn-sync" title="Tarik data jika internet pabrik sempat mati">
        <i class="ph ph-cloud-arrow-down" style="font-size: 18px;"></i> Tarik Manual IoT
    </a>
</div>

<div class="table-card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Data Karyawan</th>
                    <th rowspan="2">Status</th>
                    <th colspan="4" style="background: rgba(37,99,235,0.05); color: #2563eb;">Catatan Waktu Scan Mesin</th>
                    <th colspan="2" style="background: rgba(168,85,247,0.05); color: #a855f7;">Kalkulasi Sistem</th>
                </tr>
                <tr>
                    <th style="background: rgba(37,99,235,0.05);">Masuk</th>
                    <th style="background: rgba(37,99,235,0.05);">Ist. Keluar</th>
                    <th style="background: rgba(37,99,235,0.05);">Ist. Masuk</th>
                    <th style="background: rgba(37,99,235,0.05);">Pulang</th>
                    <th style="background: rgba(168,85,247,0.05);">Durasi Bersih</th>
                    <th style="background: rgba(168,85,247,0.05);">Waktu Lembur</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($attendances)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 60px 20px;">
                            <i class="ph ph-calendar-x" style="font-size: 48px; color: var(--border-subtle); margin-bottom: 10px; display: block;"></i>
                            <div style="color: var(--text-muted); font-weight: 600;">Tidak ada aktivitas mesin pada tanggal ini.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                        // Helper fungsi format menit
                        function formatJamMnt($mnt) {
                            if ($mnt <= 0) return '-';
                            $j = floor($mnt / 60); $m = $mnt % 60;
                            if ($j > 0) return "{$j}j {$m}m";
                            return "{$m}m";
                        }
                    ?>

                    <?php foreach($attendances as $row): ?>
                    <tr>
                        <td style="text-align: left;">
                            <div style="font-weight: 800;"><?= esc($row['name']) ?></div>
                            <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;"><?= esc($row['employee_id']) ?> | <?= esc($row['department']) ?></div>
                        </td>
                        
                        <td>
                            <?php 
                                $badgeClass = strtolower($row['status']);
                                $icon = ($row['status'] == 'Hadir') ? 'ph-check-circle' : 'ph-warning';
                            ?>
                            <span class="status-badge <?= $badgeClass ?>" style="display: block; text-align: center;">
                                <i class="ph <?= $icon ?>"></i> <?= esc($row['status']) ?>
                            </span>
                            <?php if($row['late_minutes'] > 0): ?>
                                <div style="font-size: 10px; color: #ef4444; font-weight: 700; margin-top: 4px;">Telat <?= $row['late_minutes'] ?> Mnt</div>
                            <?php endif; ?>
                        </td>

                        <td><span class="<?= $row['time_in'] ? 'time-badge' : 'time-empty' ?>"><?= $row['time_in'] ? date('H:i', strtotime($row['time_in'])) : '--:--' ?></span></td>
                        <td><span class="<?= $row['break_out'] ? 'time-badge' : 'time-empty' ?>"><?= $row['break_out'] ? date('H:i', strtotime($row['break_out'])) : '--:--' ?></span></td>
                        <td><span class="<?= $row['break_in'] ? 'time-badge' : 'time-empty' ?>"><?= $row['break_in'] ? date('H:i', strtotime($row['break_in'])) : '--:--' ?></span></td>
                        <td><span class="<?= $row['time_out'] ? 'time-badge' : 'time-empty' ?>" style="<?= !$row['time_out'] ? 'background: transparent; border: 1px dashed var(--border-subtle);' : 'border-color: #38bdf8; color: #38bdf8;' ?>"><?= $row['time_out'] ? date('H:i', strtotime($row['time_out'])) : '--:--' ?></span></td>

                        <td>
                            <?php if($row['work_duration_minutes'] > 0): ?>
                                <span class="highlight-blue"><?= formatJamMnt($row['work_duration_minutes']) ?></span>
                            <?php else: ?>
                                <span class="time-empty">Proses...</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['overtime_minutes'] > 0): ?>
                                <span class="highlight-purple"><?= formatJamMnt($row['overtime_minutes']) ?></span>
                            <?php else: ?>
                                <span class="time-empty">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>