<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const bgColor = isDark ? '#18181b' : '#ffffff';
        const textColor = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil', html: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#10b981', background: bgColor, color: textColor, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal', html: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444', background: bgColor, color: textColor, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
        <?php if(session()->getFlashdata('info')): ?>
            Swal.fire({ icon: 'info', title: 'Informasi', html: '<?= session()->getFlashdata('info') ?>', confirmButtonColor: '#3b82f6', background: bgColor, color: textColor, customClass: { popup: 'swal2-custom-radius' } });
        <?php endif; ?>
    });
</script>

<style>
    .swal2-custom-radius { border-radius: 20px !important; font-family: 'Plus Jakarta Sans', sans-serif; }

    /* =========================================================
       1. PAGE HEADER & FILTER BOX
       ========================================================= */
    .page-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;}
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 45px; height: 45px; border-radius: 12px; background: linear-gradient(135deg, rgba(56, 189, 248, 0.15), rgba(2, 132, 199, 0.05)); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 1px solid rgba(56, 189, 248, 0.2);}
    .page-title h1 { font-size: 22px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 12px; color: var(--text-muted); font-weight: 500; margin: 0;}

    .filter-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 15px 20px; border-radius: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; box-shadow: 0 5px 20px -10px rgba(0,0,0,0.05); }
    .filter-group { display: flex; gap: 10px; align-items: center; background: var(--bg-base); padding: 5px 10px; border-radius: 12px; border: 1px solid var(--border-subtle);}
    .filter-label { font-weight: 800; font-size: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;}
    .form-control-date { background: transparent; border: none; color: var(--text-main); font-family: 'Space Mono', monospace; font-size: 12px; font-weight: 800; outline: none; cursor: pointer; padding: 4px;}
    .form-control-date::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s;}
    
    .btn-filter { background: #38bdf8; color: #fff; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 800; font-size: 12px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(56, 189, 248, 0.2);}
    .btn-filter:hover { background: #0284c7; transform: translateY(-2px);}
    
    .toolbar-actions { display: flex; gap: 10px; }
    .btn-sync { background: var(--bg-base); color: var(--text-main); border: 1px solid var(--border-subtle); padding: 10px 16px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; gap: 6px; text-decoration: none;}
    .btn-manual { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2);}
    .btn-manual:hover { background: #f59e0b; color: #fff; transform: translateY(-2px);}
    .btn-pull { background: rgba(56, 189, 248, 0.1); color: #38bdf8; border-color: rgba(56, 189, 248, 0.2);}
    .btn-pull:hover { background: #38bdf8; color: #fff; transform: translateY(-2px);}
    .btn-time { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2);}
    .btn-time:hover { background: #10b981; color: #fff; transform: translateY(-2px);}

    /* =========================================================
       2. ANALYTICAL TABLE (COMPACT, GROUPED)
       ========================================================= */
    .table-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; box-shadow: 0 10px 25px -10px rgba(0,0,0,0.05); overflow: hidden; }
    
    table { width: 100%; border-collapse: collapse; white-space: nowrap; }
    th { text-align: center; padding: 12px 10px; font-size: 10px; font-weight: 900; text-transform: uppercase; color: var(--text-muted); background: var(--bg-base); border-bottom: 2px solid var(--border-subtle); border-right: 1px solid var(--border-subtle); letter-spacing: 0.5px;}
    td { text-align: center; padding: 10px; border-bottom: 1px dashed var(--border-subtle); border-right: 1px solid var(--border-subtle); color: var(--text-main); font-size: 12px; font-weight: 600; vertical-align: middle; transition: 0.2s;}
    
    th:first-child, td:first-child { text-align: left; padding-left: 20px;}
    th:last-child, td:last-child { border-right: none; padding-right: 20px;}
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: rgba(56, 189, 248, 0.02); }

    /* Group Header Styling */
    .group-header td { background: linear-gradient(to right, rgba(56, 189, 248, 0.08), transparent) !important; border-bottom: 2px solid rgba(56, 189, 248, 0.2) !important; border-right: none; padding: 12px 20px; }
    .group-header:hover td { background: linear-gradient(to right, rgba(56, 189, 248, 0.12), rgba(56, 189, 248, 0.02)) !important; }

    /* Colors */
    .th-scan { background: rgba(56, 189, 248, 0.05) !important; color: #0284c7 !important; border-bottom-color: rgba(56, 189, 248, 0.2) !important;}
    html.dark .th-scan { color: #38bdf8 !important; }
    .th-calc { background: rgba(168, 85, 247, 0.05) !important; color: #7e22ce !important; border-bottom-color: rgba(168, 85, 247, 0.2) !important;}
    html.dark .th-calc { color: #c084fc !important; }

    /* =========================================================
       3. BADGES & TIME CAPSULES (SUPER COMPACT)
       ========================================================= */
    .date-badge { display: inline-block; background: var(--bg-base); color: var(--text-muted); font-family: 'Space Mono', monospace; font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border-subtle);}

    .status-badge { padding: 4px 8px; border-radius: 6px; font-size: 9px; font-weight: 900; display: inline-flex; align-items: center; justify-content: center; gap: 4px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid transparent; min-width: 80px;}
    .status-badge.hadir { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.2); }
    .status-badge.terlambat { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }
    .status-badge.sakit, .status-badge.izin { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border-color: rgba(245, 158, 11, 0.2); }
    .status-badge.alpha { background: var(--bg-base); color: var(--text-muted); border-color: var(--border-subtle); }

    .time-capsule { font-family: 'Space Mono', monospace; font-size: 11px; font-weight: 900; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 4px 8px; border-radius: 6px; display: inline-block; color: var(--text-main); box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);}
    .time-capsule.success { border-color: rgba(16, 185, 129, 0.4); color: #10b981; background: rgba(16, 185, 129, 0.05);}
    .time-empty { font-family: 'Space Mono', monospace; font-size: 11px; color: var(--border-subtle); font-weight: 800;}
    
    .highlight-blue { font-family: 'Space Mono', monospace; color: #38bdf8; font-weight: 900; font-size: 11px; background: rgba(56, 189, 248, 0.1); padding: 4px 6px; border-radius: 4px; border: 1px dashed rgba(56, 189, 248, 0.3);}
    .highlight-purple { font-family: 'Space Mono', monospace; color: #a855f7; font-weight: 900; font-size: 11px; background: rgba(168, 85, 247, 0.1); padding: 4px 6px; border-radius: 4px; border: 1px dashed rgba(168, 85, 247, 0.3);}

    .btn-action-icon { font-size: 14px; transition: 0.3s; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid transparent; text-decoration: none;}
    .btn-action-icon:hover { transform: translateY(-2px); filter: brightness(1.1);}
    
    .btn-delete { color: #ef4444; background: rgba(239, 68, 68, 0.05); }
    .btn-delete:hover { background: #ef4444; color: #fff; }

    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 48px; color: var(--border-subtle); margin-bottom: 10px; display: block; }
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-fingerprint"></i></div>
        <div>
            <h1>Pantauan Kehadiran Pabrik</h1>
            <p>Sistem deteksi cerdas 4-Fase Scan, Kalkulasi Lembur & Bukti Foto IoT.</p>
        </div>
    </div>
</div>

<div class="filter-box">
    <form action="" method="get" class="filter-group">
        <div class="filter-label">Dari:</div>
        <input type="date" name="start_date" class="form-control-date" value="<?= esc($startDate) ?>">
        
        <div class="filter-label" style="margin-left: 5px;">Sampai:</div>
        <input type="date" name="end_date" class="form-control-date" value="<?= esc($endDate) ?>">
        
        <button type="submit" class="btn-filter" style="margin-left: 5px;"><i class="ph-bold ph-magnifying-glass"></i> Filter</button>
    </form>

    <div class="toolbar-actions">
        <a href="<?= base_url('/attendance/manual') ?>" class="btn-sync btn-manual">
            <i class="ph-bold ph-pencil-simple-line"></i> Koreksi Manual
        </a>
        <a href="#" onclick="confirmSyncTime(event, '<?= base_url('/attendance/syncMachineTime') ?>')" class="btn-sync btn-time" title="Sesuaikan waktu mesin absen dengan server">
            <i class="ph-bold ph-clock"></i> Sinkron Jam Mesin
        </a>
        <a href="#" onclick="confirmSyncData(event, '<?= base_url('/attendance/syncData?start_date=' . $startDate . '&end_date=' . $endDate) ?>')" class="btn-sync btn-pull" title="Tarik data absen sesuai rentang tanggal">
            <i class="ph-bold ph-cloud-arrow-down"></i> Tarik & Kalkulasi API IoT
        </a>
    </div>
</div>

<?php 
    $groupedAttendances = [];
    if(!empty($attendances)) {
        foreach($attendances as $row) {
            $empName = $row['name'] ?? 'Karyawan Dihapus / Tidak Diketahui';
            $groupedAttendances[$empName][] = $row;
        }
    }
?>

<div class="table-card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th rowspan="2">Tanggal Scan</th>
                    <th rowspan="2">Status Harian</th>
                    <th colspan="4" class="th-scan">Catatan Waktu Scan Mesin (IoT)</th>
                    <th colspan="2" class="th-calc">Kalkulasi Sistem</th>
                    <th rowspan="2" style="background: rgba(239,68,68,0.05); color: #ef4444; border-bottom-color: rgba(239,68,68,0.2);">Aksi Tambahan</th>
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
                <?php if(empty($groupedAttendances)): ?>
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="ph-fill ph-calendar-x"></i>
                                <h3 style="font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 4px;">Data Kehadiran Kosong</h3>
                                <p style="font-size: 12px; font-weight: 500; margin: 0;">Tidak ada aktivitas tap kartu/sidik jari pada rentang <b><?= date('d M Y', strtotime($startDate)) ?> - <?= date('d M Y', strtotime($endDate)) ?></b>.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                        function formatJamMnt($mnt) {
                            if ($mnt <= 0) return '-';
                            $j = floor($mnt / 60); $m = $mnt % 60;
                            return ($j > 0) ? "{$j}j {$m}m" : "{$m}m";
                        }
                    ?>

                    <?php foreach($groupedAttendances as $empName => $logs): ?>
                        <tr class="group-header">
                            <td colspan="9">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-size: 14px; font-weight: 900; color: #0284c7; letter-spacing: -0.5px;">
                                        <i class="ph-bold ph-user" style="margin-right: 6px;"></i> <?= esc($empName) ?>
                                        <?php if(($logs[0]['emp_status'] ?? '') === 'Borongan'): ?>
                                            <span style="font-size: 9px; background: #fff; color: #d97706; padding: 2px 6px; border-radius: 4px; border: 1px solid rgba(245, 158, 11, 0.3); margin-left: 6px;">BORONGAN</span>
                                        <?php endif; ?>
                                    </span>
                                    <span style="font-size: 10px; font-weight: 800; color: #38bdf8; background: #fff; padding: 4px 10px; border-radius: 8px; border: 1px solid rgba(56, 189, 248, 0.3);">
                                        <?= count($logs) ?> Hari Direkap
                                    </span>
                                </div>
                            </td>
                        </tr>

                        <?php foreach($logs as $row): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    
                                    <?php if(!empty($row['photo_url']) && $row['photo_url'] !== 'null'): ?>
                                        <a href="<?= esc($row['photo_url']) ?>" target="_blank" title="Lihat Bukti Foto Scan IoT" style="display: block; width: 36px; height: 36px; position: relative;">
                                            <img src="<?= esc($row['photo_url']) ?>" 
                                                 style="width: 100%; height: 100%; border-radius: 10px; object-fit: cover; border: 1px solid var(--border-subtle); box-shadow: 0 2px 8px rgba(0,0,0,0.1);" 
                                                 alt="Foto"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            
                                            <div style="display: none; width: 100%; height: 100%; border-radius: 10px; background: var(--bg-base); align-items: center; justify-content: center; color: var(--text-muted); border: 1px solid var(--border-subtle);" title="Foto Gagal Dimuat">
                                                <i class="ph-bold ph-image-broken"></i>
                                            </div>
                                        </a>
                                    <?php else: ?>
                                        <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--bg-base); display: flex; align-items: center; justify-content: center; color: var(--text-muted); border: 1px solid var(--border-subtle);" title="Absen Tanpa Foto">
                                            <i class="ph-bold ph-camera-slash"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <span class="date-badge"><?= date('d M Y', strtotime($row['date'])) ?></span>
                                        
                                        <?php 
                                            $v = (string)($row['verify_method'] ?? '');
                                            if($v==='1') echo '<span class="date-badge" title="Fingerprint" style="color:#3b82f6; background:rgba(59,130,246,0.1); border-color:rgba(59,130,246,0.2);"><i class="ph-bold ph-fingerprint"></i> Sidik Jari</span>';
                                            elseif($v==='2') echo '<span class="date-badge" title="Password/PIN" style="color:#f59e0b; background:rgba(245,158,11,0.1); border-color:rgba(245,158,11,0.2);"><i class="ph-bold ph-key"></i> Password</span>';
                                            elseif($v==='3') echo '<span class="date-badge" title="RFID Card" style="color:#10b981; background:rgba(16,185,129,0.1); border-color:rgba(16,185,129,0.2);"><i class="ph-bold ph-identification-card"></i> Kartu RFID</span>';
                                            elseif($v==='4') echo '<span class="date-badge" title="Face Scan" style="color:#8b5cf6; background:rgba(139,92,246,0.1); border-color:rgba(139,92,246,0.2);"><i class="ph-bold ph-user-focus"></i> Wajah</span>';
                                            elseif($v==='6') echo '<span class="date-badge" title="Vein Scan" style="color:#ec4899; background:rgba(236,72,153,0.1); border-color:rgba(236,72,153,0.2);"><i class="ph-bold ph-hand-palm"></i> Telapak (Vein)</span>';
                                            elseif($v==='7') echo '<span class="date-badge" title="QR Code" style="color:#0ea5e9; background:rgba(14,165,233,0.1); border-color:rgba(14,165,233,0.2);"><i class="ph-bold ph-qr-code"></i> QR Code</span>';
                                        ?>
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
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <span class="status-badge <?= $badgeClass ?>">
                                        <i class="ph-fill <?= $icon ?>" style="font-size: 10px;"></i> <?= esc($row['status']) ?>
                                    </span>
                                    <?php if($row['late_minutes'] > 0): ?>
                                        <div style="font-size: 9px; color: #ef4444; font-weight: 800; background: rgba(239, 68, 68, 0.05); padding: 2px 6px; border-radius: 4px; border: 1px dashed rgba(239, 68, 68, 0.3);">
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
                                    <span class="time-empty" style="font-size: 10px;"><?= in_array($row['status'], ['Sakit', 'Izin', 'Alpha']) ? '-' : 'Wait' ?></span>
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
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    <?php if(isset($row['emp_status']) && $row['emp_status'] === 'Borongan'): ?>
                                        <a href="#" onclick="openQuickKasbon(event, '<?= esc($row['employee_id']) ?>', '<?= esc($row['date']) ?>', '<?= esc($empName, 'js') ?>')" class="btn-action-icon" style="color: #f59e0b; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);" title="Kasbon Harian (Borongan)">
                                            <i class="ph-bold ph-hand-coins"></i>
                                        </a>
                                    <?php else: ?>
                                        <?php 
                                            $mealTaken = (isset($row['is_meal_taken']) && $row['is_meal_taken'] == 1);
                                            $mealTitle = $mealTaken ? 'Batalkan Uang Makan & Kembalikan Saldo Kas Laci?' : 'Tandai Uang Makan Diambil & Potong Saldo Kas Laci?';
                                            $mealClass = $mealTaken ? 'color: #fff; background: #10b981; border: 1px solid #10b981;' : 'color: #94a3b8; background: rgba(148, 163, 184, 0.1); border: 1px dashed #cbd5e1;';
                                        ?>
                                        <a href="#" onclick="confirmMealToggle(event, '<?= base_url('/attendance/toggle_meal/' . $row['id']) ?>', '<?= $mealTitle ?>')" class="btn-action-icon" style="<?= $mealClass ?>" title="<?= $mealTaken ? 'Uang Makan Telah Diambil (Klik untuk Batal)' : 'Tandai Uang Makan Diambil (Kasbon)' ?>">
                                            <i class="ph-bold ph-hamburger"></i>
                                        </a>
                                    <?php endif; ?>

                                    <a href="#" onclick="confirmDeleteLog(event, '<?= base_url('/attendance/delete/' . $row['id']) ?>')" class="btn-action-icon btn-delete" title="Hapus Log Kehadiran">
                                        <i class="ph-bold ph-trash"></i>
                                    </a>
                                </div>
                            </td>
                            
                        </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // --- SWEETALERT CONFIRMATIONS ---
    function confirmSyncData(e, url) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Tarik Data Mesin?',
            text: 'Sistem akan menyedot log absensi terbaru dari mesin IoT ke database ERP.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#38bdf8',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Tarik Sekarang',
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menyinkronkan...', allowOutsideClick: false, showConfirmButton: false, background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }, didOpen: () => { Swal.showLoading() }});
                window.location.href = url;
            }
        });
    }

    function confirmSyncTime(e, url) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Sinkronisasi Waktu?',
            text: 'Jam pada mesin absensi (IoT) akan disesuaikan dengan waktu Server Pusat (WIB).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Sinkronkan',
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Menyinkronkan Jam...', allowOutsideClick: false, showConfirmButton: false, background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }, didOpen: () => { Swal.showLoading() }});
                window.location.href = url;
            }
        });
    }

    function confirmMealToggle(e, url, titleMsg) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: titleMsg,
            text: 'Tindakan ini akan mengupdate Saldo Kas Laci & Jurnal Akuntansi secara otomatis (Sesuai jatah uang makan di Master Karyawan).',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Eksekusi',
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses Keuangan...', allowOutsideClick: false, showConfirmButton: false, background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }, didOpen: () => { Swal.showLoading() }});
                window.location.href = url;
            }
        });
    }

    function confirmDeleteLog(e, url) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: 'Hapus Log Absensi?',
            text: 'Data tap kartu/finger ini akan dihapus secara permanen. Lanjutkan?',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        });
    }

    // --- POPUP INPUT KASBON CEPAT (BORONGAN) ---
    function openQuickKasbon(e, empId, date, empName) {
        e.preventDefault();
        const isDark = document.documentElement.classList.contains('dark');
        
        let d = new Date(date);
        let dateStr = d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });

        Swal.fire({
            title: 'Kasbon Harian Borongan',
            html: `Masukkan nominal Kasbon untuk <b>${empName}</b><br><span style="font-size:11px; color:#64748b;">(Akan memotong Saldo Laci Kasir pada tanggal ${dateStr})</span>`,
            input: 'text',
            inputAttributes: {
                placeholder: 'Contoh: 50.000',
                style: 'font-family: "Space Mono", monospace; font-weight: 900; color: #f59e0b; text-align: center;'
            },
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1',
            confirmButtonText: '<i class="ph-bold ph-hand-coins"></i> Simpan & Potong Kas',
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff', 
            color: isDark ? '#f4f4f5' : '#09090b',
            customClass: { popup: 'swal2-custom-radius' },
            didOpen: () => {
                const input = Swal.getInput();
                input.addEventListener('keyup', () => {
                    let val = input.value.replace(/[^,\d]/g, '').toString(),
                        split = val.split(','), sisa = split[0].length % 3,
                        rupiah = split[0].substr(0, sisa), ribuan = split[0].substr(sisa).match(/\d{3}/gi);
                    if (ribuan) rupiah += (sisa ? '.' : '') + ribuan.join('.');
                    input.value = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
                });
            },
            preConfirm: (amount) => {
                let cleanAmt = amount.replace(/\./g, '');
                if (!cleanAmt || parseInt(cleanAmt) <= 0) {
                    Swal.showValidationMessage('Silakan masukkan nominal kasbon yang valid!');
                }
                return cleanAmt;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit Form Dinamis (Hidden POST)
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= base_url('/attendance/store_quick_kasbon') ?>';
                
                const csrf = document.createElement('input'); csrf.type = 'hidden'; csrf.name = '<?= csrf_token() ?>'; csrf.value = '<?= csrf_hash() ?>';
                const inputEmp = document.createElement('input'); inputEmp.type = 'hidden'; inputEmp.name = 'employee_id'; inputEmp.value = empId;
                const inputDate = document.createElement('input'); inputDate.type = 'hidden'; inputDate.name = 'date'; inputDate.value = date;
                const inputAmount = document.createElement('input'); inputAmount.type = 'hidden'; inputAmount.name = 'amount'; inputAmount.value = result.value;

                form.appendChild(csrf); form.appendChild(inputEmp); form.appendChild(inputDate); form.appendChild(inputAmount);
                document.body.appendChild(form);
                
                Swal.fire({ title: 'Mencatat Kasbon...', allowOutsideClick: false, showConfirmButton: false, background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b', customClass: { popup: 'swal2-custom-radius' }, didOpen: () => { Swal.showLoading() }});
                form.submit();
            }
        });
    }
</script>

<?= $this->endSection() ?>