<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER & TYPOGRAPHY
       ========================================================= */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap; gap: 20px;}
    
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(2, 132, 199, 0.05)); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(56, 189, 248, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 500; margin: 0;}

    /* =========================================================
       2. FILTER BOX & TOOLBAR (GLASSMORPHISM)
       ========================================================= */
    .filter-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 20px 25px; border-radius: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); }
    
    .filter-group { display: flex; gap: 15px; align-items: center; background: var(--bg-base); padding: 6px 12px; border-radius: 14px; border: 1px dashed var(--border-subtle);}
    .form-control-date { background: transparent; border: none; color: var(--text-main); font-family: 'Space Mono', monospace; font-size: 14px; font-weight: 800; outline: none; cursor: pointer;}
    .form-control-date::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s;}
    .form-control-date::-webkit-calendar-picker-indicator:hover { opacity: 1; }
    
    .btn-filter { background: #38bdf8; color: #fff; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 800; font-size: 13px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);}
    .btn-filter:hover { background: #0284c7; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(56, 189, 248, 0.4);}
    
    .toolbar-actions { display: flex; gap: 12px; }
    .btn-sync { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 12px 20px; border-radius: 12px; font-weight: 800; font-size: 13px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 8px; text-decoration: none;}
    
    .btn-manual { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2);}
    .btn-manual:hover { background: #f59e0b; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);}
    
    .btn-pull { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border-color: rgba(56, 189, 248, 0.2);}
    .btn-pull:hover { background: #38bdf8; color: #fff; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);}

    /* =========================================================
       3. ANALYTICAL TABLE (STRICT BORDERS)
       ========================================================= */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); overflow: hidden; }
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 14px 15px; font-size: 11px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 2px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); letter-spacing: 0.5px;}
    td { text-align: center; padding: 16px 15px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 13px; font-weight: 600; vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; }
    th:last-child, td:last-child { border-right: none; }
    tr:last-child td { border-bottom: none; }
    
    tr:hover td { background: rgba(56, 189, 248, 0.02); }
    html.dark tr:hover td { background: rgba(56, 189, 248, 0.05); }

    /* Grouped Header Colors */
    .th-scan { background: rgba(56, 189, 248, 0.05) !important; color: #0284c7 !important; border-bottom-color: rgba(56, 189, 248, 0.2) !important;}
    html.dark .th-scan { color: #38bdf8 !important; }
    
    .th-calc { background: rgba(168, 85, 247, 0.05) !important; color: #7e22ce !important; border-bottom-color: rgba(168, 85, 247, 0.2) !important;}
    html.dark .th-calc { color: #c084fc !important; }

    /* =========================================================
       4. BADGES & TIME CAPSULES
       ========================================================= */
    .emp-info { display: flex; flex-direction: column; gap: 4px; }
    .emp-name { font-weight: 900; color: var(--text-main); font-size: 14px; letter-spacing: -0.5px;}
    .emp-meta { font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 700; display: flex; align-items: center; gap: 6px;}

    .status-badge { padding: 6px 12px; border-radius: 8px; font-size: 10px; font-weight: 900; display: inline-flex; align-items: center; justify-content: center; gap: 6px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid transparent; min-width: 90px;}
    .status-badge.hadir { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }
    .status-badge.terlambat { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }
    .status-badge.sakit, .status-badge.izin { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2); }
    .status-badge.alpha { background: var(--bg-base); color: var(--text-muted); border-color: var(--border-subtle); }

    .time-capsule { font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 900; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 6px 10px; border-radius: 8px; display: inline-block; color: var(--text-main); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .time-capsule.success { border-color: rgba(16, 185, 129, 0.4); color: #10b981; background: rgba(16, 185, 129, 0.05);}
    .time-empty { font-family: 'Space Mono', monospace; font-size: 13px; color: var(--border-subtle); font-weight: 800;}
    
    .highlight-blue { font-family: 'Space Mono', monospace; color: #38bdf8; font-weight: 900; font-size: 14px; background: rgba(56, 189, 248, 0.1); padding: 4px 8px; border-radius: 6px; border: 1px dashed rgba(56, 189, 248, 0.3);}
    .highlight-purple { font-family: 'Space Mono', monospace; color: #a855f7; font-weight: 900; font-size: 14px; background: rgba(168, 85, 247, 0.1); padding: 4px 8px; border-radius: 6px; border: 1px dashed rgba(168, 85, 247, 0.3);}

    .btn-delete { color: #ef4444; background: rgba(239, 68, 68, 0.05); font-size: 18px; transition: 0.3s; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid transparent; text-decoration: none;}
    .btn-delete:hover { color: #fff; background: #ef4444; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transform: translateY(-2px);}

    /* Empty State */
    .empty-state { text-align: center; padding: 80px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 56px; color: var(--border-subtle); margin-bottom: 15px; display: block; }
    .empty-state h3 { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 5px; }
    .empty-state p { font-size: 13px; font-weight: 500; margin: 0; }
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-fingerprint"></i></div>
        <div>
            <h1>Pantauan Kehadiran Pabrik</h1>
            <p>Sistem deteksi cerdas 4-Fase Scan (Masuk - Ist. Keluar - Ist. Masuk - Pulang) & Kalkulasi Lembur.</p>
        </div>
    </div>
</div>

<div class="filter-box">
    <form action="" method="get" class="filter-group">
        <div style="font-weight: 800; font-size: 12px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; padding-left: 5px;">Pilih Tanggal:</div>
        <input type="date" name="date" class="form-control-date" value="<?= esc($dateFilter) ?>">
        <button type="submit" class="btn-filter"><i class="ph-bold ph-magnifying-glass"></i> Tampilkan</button>
    </form>

    <div class="toolbar-actions">
        <a href="<?= base_url('/attendance/manual') ?>" class="btn-sync btn-manual">
            <i class="ph-bold ph-pencil-simple-line" style="font-size: 18px;"></i> Koreksi Manual
        </a>

        <a href="<?= base_url('/attendance/sync?date=' . $dateFilter) ?>" class="btn-sync btn-pull" title="Tarik data absen dari Mesin Fisik secara paksa">
            <i class="ph-bold ph-cloud-arrow-down" style="font-size: 18px;"></i> Tarik API Mesin IoT
        </a>
    </div>
</div>

<div class="table-card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Identitas Pekerja</th>
                    <th rowspan="2">Status Harian</th>
                    <th colspan="4" class="th-scan">Catatan Waktu Scan Mesin (IoT)</th>
                    <th colspan="2" class="th-calc">Kalkulasi Sistem</th>
                    <th rowspan="2" style="background: rgba(239,68,68,0.05); color: #ef4444; border-bottom-color: rgba(239,68,68,0.2);">Aksi</th>
                </tr>
                <tr>
                    <th class="th-scan" style="border-top: 1px solid rgba(56, 189, 248, 0.2);">Clock In</th>
                    <th class="th-scan" style="border-top: 1px solid rgba(56, 189, 248, 0.2);">Break Out</th>
                    <th class="th-scan" style="border-top: 1px solid rgba(56, 189, 248, 0.2);">Break In</th>
                    <th class="th-scan" style="border-top: 1px solid rgba(56, 189, 248, 0.2);">Clock Out</th>
                    
                    <th class="th-calc" style="border-top: 1px solid rgba(168, 85, 247, 0.2);">Durasi Kerja</th>
                    <th class="th-calc" style="border-top: 1px solid rgba(168, 85, 247, 0.2);">Waktu Lembur</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($attendances)): ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="ph-fill ph-calendar-x"></i>
                                <h3>Data Kehadiran Kosong</h3>
                                <p>Tidak ada aktivitas tap kartu/sidik jari pada tanggal <b><?= date('d F Y', strtotime($dateFilter)) ?></b>.</p>
                            </div>
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
                            <div class="emp-info">
                                <div class="emp-name"><?= esc($row['name']) ?></div>
                                <div class="emp-meta">
                                    <span style="background: var(--bg-base); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border-subtle);"><i class="ph-bold ph-hash"></i> <?= esc($row['employee_id']) ?></span>
                                    <span><?= esc($row['department']) ?></span>
                                </div>
                            </div>
                        </td>
                        
                        <td>
                            <?php 
                                $badgeClass = strtolower($row['status']);
                                $icon = 'ph-check-circle';
                                if($row['status'] == 'Terlambat') $icon = 'ph-warning-circle';
                                if($row['status'] == 'Sakit' || $row['status'] == 'Izin') $icon = 'ph-envelope-simple';
                                if($row['status'] == 'Alpha') $icon = 'ph-x-circle';
                            ?>
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                                <span class="status-badge <?= $badgeClass ?>">
                                    <i class="ph-fill <?= $icon ?>" style="font-size: 14px;"></i> <?= esc($row['status']) ?>
                                </span>
                                <?php if($row['late_minutes'] > 0): ?>
                                    <div style="font-size: 10px; color: #ef4444; font-weight: 800; background: rgba(239, 68, 68, 0.05); padding: 2px 8px; border-radius: 4px; border: 1px dashed rgba(239, 68, 68, 0.3);">
                                        Telat <?= $row['late_minutes'] ?> Mnt
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td><span class="<?= $row['time_in'] ? 'time-capsule success' : 'time-empty' ?>"><?= $row['time_in'] ? date('H:i', strtotime($row['time_in'])) : '--:--' ?></span></td>
                        <td><span class="<?= $row['break_out'] ? 'time-capsule' : 'time-empty' ?>"><?= $row['break_out'] ? date('H:i', strtotime($row['break_out'])) : '--:--' ?></span></td>
                        <td><span class="<?= $row['break_in'] ? 'time-capsule' : 'time-empty' ?>"><?= $row['break_in'] ? date('H:i', strtotime($row['break_in'])) : '--:--' ?></span></td>
                        <td><span class="<?= $row['time_out'] ? 'time-capsule success' : 'time-empty' ?>"><?= $row['time_out'] ? date('H:i', strtotime($row['time_out'])) : '--:--' ?></span></td>

                        <td>
                            <?php if($row['work_duration_minutes'] > 0): ?>
                                <span class="highlight-blue"><?= formatJamMnt($row['work_duration_minutes']) ?></span>
                            <?php else: ?>
                                <span class="time-empty" style="font-size: 11px;"><?= in_array($row['status'], ['Sakit', 'Izin', 'Alpha']) ? '-' : 'Menunggu...' ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row['overtime_minutes'] > 0): ?>
                                <span class="highlight-purple"><?= formatJamMnt($row['overtime_minutes']) ?></span>
                            <?php else: ?>
                                <span class="time-empty">-</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <a href="#" onclick="confirmDeleteLog(event, '<?= base_url('/attendance/delete/' . $row['id']) ?>')" class="btn-delete" title="Hapus Log Kehadiran">
                                <i class="ph-bold ph-trash"></i>
                            </a>
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
    function confirmDeleteLog(e, url) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        
        Swal.fire({
            title: 'Hapus Log Kehadiran?',
            text: 'Data absensi karyawan ini di tanggal terpilih akan dihapus secara permanen. Lanjutkan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Hapus Data',
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