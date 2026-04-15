<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<?php
// --- FUNGSI PHP CERDAS UNTUK MENYINGKAT ANGKA ---
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
       1. WELCOME BANNER
       ========================================================= */
    .welcome-banner { 
        background: linear-gradient(135deg, #2563eb, #1e40af); 
        border-radius: 28px; padding: 40px; color: #fff; margin-bottom: 30px; 
        box-shadow: 0 15px 40px -10px rgba(37,99,235,0.4); position: relative; 
        overflow: hidden; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;
    }
    .welcome-banner::before { content: ''; position: absolute; left: -20px; top: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); border-radius: 50%; }
    .welcome-banner::after { content: ''; position: absolute; right: -50px; bottom: -80px; width: 250px; height: 250px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%; }
    
    .profile-info { display: flex; align-items: center; gap: 24px; position: relative; z-index: 10;}
    .avatar-large { width: 75px; height: 75px; border-radius: 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; border: 2px solid rgba(255,255,255,0.4); box-shadow: 0 8px 25px rgba(0,0,0,0.15);}
    .greeting h2 { margin:0 0 8px 0; font-size: 32px; font-weight: 900; letter-spacing: -0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);}
    .greeting-meta { display: inline-flex; align-items: center; gap: 15px; font-size: 13px; font-weight: 700; background: rgba(0,0,0,0.2); padding: 10px 18px; border-radius: 12px; backdrop-filter: blur(8px);}
    .greeting-meta span { display: flex; align-items: center; gap: 6px; }
    
    .quick-action-btn { background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: #fff; border: 1px solid rgba(255,255,255,0.3); padding: 16px 26px; border-radius: 18px; font-weight: 800; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; text-decoration: none; transition: 0.3s; position: relative; z-index: 10; box-shadow: 0 4px 20px rgba(0,0,0,0.1);}
    .quick-action-btn:hover { background: #fff; color: #2563eb; transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.15);}

    /* =========================================================
       2. STATS BENTO GRID & LIVE TIMER
       ========================================================= */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
    @media(max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media(max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }
    
    .stat-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 25px; border-radius: 24px; display: flex; flex-direction: column; justify-content: center; position: relative; overflow: hidden; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); transition: 0.3s; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px -10px rgba(0,0,0,0.08); border-color: rgba(37,99,235,0.2);}
    
    .stat-header { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; }
    .stat-icon { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .stat-label { font-size: 11px; color: var(--text-muted); font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;}
    .stat-val { font-size: 32px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; letter-spacing: -1px; line-height: 1;}
    .stat-sub { font-size: 12px; font-weight: 700; color: var(--text-muted); margin-top: 8px;}

    /* Live Timer */
    .live-indicator { display: inline-block; width: 10px; height: 10px; background-color: #10b981; border-radius: 50%; box-shadow: 0 0 0 3px rgba(16,185,129,0.2); animation: pulse 1.5s infinite; margin-right: 10px;}
    @keyframes pulse { 0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16,185,129, 0.4); } 70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16,185,129, 0); } 100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16,185,129, 0); } }
    .timer-text { font-family: 'Space Mono', monospace; font-size: 32px; font-weight: 900; color: #10b981; letter-spacing: 1px; display: flex; align-items: center;}
    .timer-completed { font-family: 'Space Mono', monospace; font-size: 32px; font-weight: 900; color: #8b5cf6; }
    .timer-paused { font-family: 'Space Mono', monospace; font-size: 32px; font-weight: 900; color: #f59e0b; display: flex; align-items: center;}
    .timer-empty { font-family: 'Space Mono', monospace; font-size: 20px; font-weight: 900; color: var(--text-muted); opacity: 0.5;}

    /* =========================================================
       3. RECENT ATTENDANCE & BENTO CARDS
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; padding: 35px; box-shadow: 0 15px 40px -15px rgba(0,0,0,0.05);}
    .card-title { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 12px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 18px;}
    .card-title i { color: #3b82f6; background: rgba(59, 130, 246, 0.1); padding: 10px; border-radius: 12px; font-size: 22px;}
    
    .timeline-container { display: flex; flex-direction: column; gap: 15px; }
    .timeline-item { display: flex; align-items: center; justify-content: space-between; padding: 20px; background: var(--bg-base); border: 1px solid transparent; border-radius: 20px; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.01); flex-wrap: wrap; gap: 15px;}
    .timeline-item:hover { background: var(--bg-surface); border-color: var(--border-subtle); box-shadow: 0 8px 25px rgba(0,0,0,0.04); transform: translateX(6px);}
    
    .tl-left { display: flex; align-items: center; gap: 20px; }
    .tl-date-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); width: 64px; height: 64px; border-radius: 16px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.02); flex-shrink: 0;}
    .tl-day { font-size: 11px; font-weight: 900; color: #ef4444; text-transform: uppercase; margin-bottom: 2px;}
    .tl-date { font-size: 22px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; line-height: 1;}
    .tl-info h4 { margin: 0 0 8px 0; font-size: 15px; font-weight: 800; color: var(--text-main);}
    .tl-time-capsules { display: flex; gap: 12px; flex-wrap: wrap;}
    .tl-time { background: var(--bg-surface); border: 1px dashed var(--border-subtle); padding: 6px 12px; border-radius: 10px; font-size: 13px; font-family: 'Space Mono', monospace; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; gap: 8px;}
    
    .tl-right { text-align: right; }
    .status-badge { padding: 8px 16px; border-radius: 12px; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px;}
    .status-hadir { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .status-telat { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);}
    
    .empty-state { text-align: center; padding: 50px; color: var(--text-muted); }
    .empty-state i { font-size: 56px; color: var(--border-subtle); margin-bottom: 15px; display: block; }

    /* =========================================================
       4. MOBILE FIXES
       ========================================================= */
    @media (max-width: 768px) {
        .welcome-banner { flex-direction: column; align-items: flex-start; padding: 25px; border-radius: 20px;}
        .profile-info { flex-direction: column; align-items: flex-start; gap: 15px;}
        .avatar-large { width: 60px; height: 60px; font-size: 24px; border-radius: 16px;}
        .greeting h2 { font-size: 24px; }
        .greeting-meta { flex-direction: column; align-items: flex-start; gap: 8px;}
        .greeting-meta span { display: block; }
        .greeting-meta span:nth-child(2) { display: none; /* hide the pipe separator */ }
        .quick-action-btn { width: 100%; justify-content: center; }

        .stat-card { padding: 20px; border-radius: 20px; }
        .stat-val { font-size: 24px; }
        
        .bento-card { padding: 20px; border-radius: 20px; }
        .timeline-item { flex-direction: column; align-items: flex-start; gap: 15px;}
        .tl-right { text-align: left; width: 100%; display: flex; justify-content: space-between; align-items: center;}
    }
</style>

<div class="welcome-banner">
    <div class="profile-info">
        <div class="avatar-large">
            <?= strtoupper(substr(session()->get('name') ?? 'U', 0, 1)) ?>
        </div>
        <div class="greeting">
            <h2><span id="dynamic-greeting">Halo</span>, <?= esc(session()->get('name')) ?>!</h2>
            <div class="greeting-meta">
                <span><i class="ph-bold ph-identification-badge"></i> NIK: <?= esc(session()->get('employee_id')) ?></span>
                <span style="opacity: 0.5;">|</span>
                <span><i class="ph-bold ph-briefcase"></i> <?= esc($employee['position_name'] ?? 'Posisi Belum Diatur') ?> - <?= esc($employee['department_name'] ?? 'Departemen Kosong') ?></span>
            </div>
        </div>
    </div>
    
    <a href="<?= base_url('/leave') ?>" class="quick-action-btn">
        <i class="ph-bold ph-calendar-plus" style="font-size: 20px;"></i> Form Cuti & Izin
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card" style="border-top: 4px solid #10b981;">
        <div class="stat-header">
            <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;"><i class="ph-fill ph-stopwatch"></i></div>
            <div class="stat-label">Status Hari Ini</div>
        </div>
        <div>
            <?php 
                // LOGIKA CERDAS: Ekstraksi Waktu untuk Javascript (Pencegah 00:00:00 bug)
                $hasIn = !empty($todayAttendance['time_in']) && $todayAttendance['time_in'] !== '00:00:00';
                $hasBreakOut = !empty($todayAttendance['break_out']) && $todayAttendance['break_out'] !== '00:00:00';
                $hasBreakIn = !empty($todayAttendance['break_in']) && $todayAttendance['break_in'] !== '00:00:00';
                $hasOut = !empty($todayAttendance['time_out']) && $todayAttendance['time_out'] !== '00:00:00';

                $dateStr = $todayAttendance['date'] ?? date('Y-m-d');
                
                // Gunakan format ISO 8601 (Y-m-dTH:i:s) agar valid dibaca Javascript Date()
                $timeInIso = $hasIn ? ($dateStr . 'T' . $todayAttendance['time_in']) : '';
                $breakOutIso = $hasBreakOut ? ($dateStr . 'T' . $todayAttendance['break_out']) : '';
                $breakInIso = $hasBreakIn ? ($dateStr . 'T' . $todayAttendance['break_in']) : '';
                $timeOutIso = $hasOut ? ($dateStr . 'T' . $todayAttendance['time_out']) : '';
            ?>

            <?php if(!$hasIn): ?>
                <div class="timer-empty">Belum Clock-In</div>
                <div class="stat-sub">Silakan scan di mesin absensi.</div>

            <?php elseif($hasIn && !$hasOut): ?>
                
                <?php if($hasBreakOut && !$hasBreakIn): // SEDANG ISTIRAHAT / PAUSED ?>
                    <?php
                        $inTs = strtotime($timeInIso);
                        $boutTs = strtotime($breakOutIso);
                        $workedSeconds = max(0, $boutTs - $inTs);
                        $h = str_pad(floor($workedSeconds / 3600), 2, '0', STR_PAD_LEFT);
                        $m = str_pad(floor(($workedSeconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
                        $s = str_pad($workedSeconds % 60, 2, '0', STR_PAD_LEFT);
                    ?>
                    <div class="timer-paused">
                        <i class="ph-fill ph-pause-circle" style="margin-right: 8px; font-size: 26px;"></i> <?= "$h:$m:$s" ?>
                    </div>
                    <div class="stat-sub" style="color: #f59e0b; font-weight: 800;"><i class="ph-bold ph-coffee"></i> Waktu Jeda Istirahat (Paused)</div>

                <?php else: // SEDANG BEKERJA (RUNNING LIVE) ?>
                    <div class="timer-text" id="realtime-duration" 
                         data-in="<?= $timeInIso ?>" 
                         data-bout="<?= $breakOutIso ?>" 
                         data-bin="<?= $breakInIso ?>">
                        <span class="live-indicator"></span> <span id="timer-display">00:00:00</span>
                    </div>
                    <div class="stat-sub" style="color: #10b981; font-weight: 800;"><i class="ph-bold ph-trend-up"></i> Sedang dalam jam kerja</div>
                <?php endif; ?>

            <?php elseif($hasOut): // SUDAH PULANG ?>
                <?php
                    $inTs = strtotime($timeInIso);
                    $outTs = strtotime($timeOutIso);
                    $workedSeconds = max(0, $outTs - $inTs);
                    
                    if ($hasBreakOut && $hasBreakIn) {
                        $boutTs = strtotime($breakOutIso);
                        $binTs = strtotime($breakInIso);
                        $workedSeconds = max(0, ($boutTs - $inTs) + ($outTs - $binTs));
                    } elseif ($hasBreakOut && !$hasBreakIn) {
                        $boutTs = strtotime($breakOutIso);
                        $workedSeconds = max(0, $boutTs - $inTs); 
                    }
                    
                    $h = str_pad(floor($workedSeconds / 3600), 2, '0', STR_PAD_LEFT);
                    $m = str_pad(floor(($workedSeconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
                    $s = str_pad($workedSeconds % 60, 2, '0', STR_PAD_LEFT);
                ?>
                <div class="timer-completed">
                    <i class="ph-fill ph-check-circle" style="margin-right: 8px; font-size: 26px;"></i> <?= "$h:$m:$s" ?>
                </div>
                <div class="stat-sub" style="color: #8b5cf6; font-weight: 800;"><i class="ph-bold ph-house-line"></i> Selesai (Sudah Clock-Out)</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;"><i class="ph-fill ph-calendar-check"></i></div>
            <div class="stat-label">Hadir Bulan Ini</div>
        </div>
        <div>
            <div class="stat-val"><?= $totalPresent ?? 0 ?> <span style="font-size: 16px; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif;">Hari</span></div>
            <div class="stat-sub">Total akumulasi kehadiran Anda.</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;"><i class="ph-fill ph-warning-circle"></i></div>
            <div class="stat-label">Total Telat (Bulan Ini)</div>
        </div>
        <div>
            <div class="stat-val"><?= $totalLateMinutes ?? 0 ?> <span style="font-size: 16px; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif;">Mnt</span></div>
            <div class="stat-sub">Berdampak pada potongan disiplin.</div>
        </div>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.05), transparent); border-color: rgba(168, 85, 247, 0.2);">
        <div class="stat-header">
            <div class="stat-icon" style="background: #a855f7; color: #fff; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.4);"><i class="ph-fill ph-receipt"></i></div>
            <div class="stat-label" style="color: #a855f7;">Slip Gaji</div>
        </div>
        <div>
            <div class="stat-val" style="font-size: 22px; font-family: 'Plus Jakarta Sans', sans-serif;">Tersedia</div>
            <a href="<?= base_url('/portal/slip_gaji') ?>" style="font-size: 13px; font-weight: 800; color: #a855f7; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-top: 8px; background: rgba(168,85,247,0.1); padding: 8px 14px; border-radius: 10px; transition: 0.2s;">
                Unduh / Lihat Dokumen <i class="ph-bold ph-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="bento-card">
    <div class="card-title">
        <i class="ph-fill ph-clock-counter-clockwise"></i> Log Kehadiran Terakhir (IoT Fingerspot)
    </div>
    
    <div class="timeline-container">
        <?php if(empty($recentAttendances)): ?>
            <div class="empty-state">
                <i class="ph-fill ph-calendar-x"></i>
                <div style="font-weight: 900; color: var(--text-main); font-size: 18px; margin-bottom: 6px;">Belum Ada Riwayat</div>
                <div style="font-size: 14px;">Data absensi Anda dari mesin belum masuk ke dalam sistem.</div>
            </div>
        <?php else: ?>
            <?php foreach($recentAttendances as $att): ?>
            <div class="timeline-item">
                <div class="tl-left">
                    <div class="tl-date-box">
                        <div class="tl-day"><?= date('D', strtotime($att['date'])) ?></div>
                        <div class="tl-date"><?= date('d', strtotime($att['date'])) ?></div>
                    </div>
                    <div class="tl-info">
                        <h4><?= date('F Y', strtotime($att['date'])) ?></h4>
                        <div class="tl-time-capsules">
                            <div class="tl-time" title="Jam Masuk">
                                <i class="ph-bold ph-sign-in" style="color: #10b981; font-size: 16px;"></i>
                                <?= !empty($att['time_in']) ? date('H:i', strtotime($att['time_in'])) : '--:--' ?>
                            </div>
                            <div class="tl-time" title="Jam Pulang">
                                <i class="ph-bold ph-sign-out" style="color: #ef4444; font-size: 16px;"></i>
                                <?= !empty($att['time_out']) && $att['time_out'] !== '00:00:00' ? date('H:i', strtotime($att['time_out'])) : '--:--' ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tl-right">
                    <span class="status-badge <?= ($att['status'] == 'Terlambat') ? 'status-telat' : 'status-hadir' ?>">
                        <i class="ph-fill <?= ($att['status'] == 'Terlambat') ? 'ph-warning-circle' : 'ph-check-circle' ?>" style="font-size: 16px;"></i>
                        <?= esc($att['status']) ?>
                    </span>
                    <?php if($att['late_minutes'] > 0): ?>
                        <div style="font-size: 11px; color: #ef4444; font-weight: 800; margin-top: 8px; display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                            <i class="ph-bold ph-clock-countdown"></i> Telat <?= $att['late_minutes'] ?> Menit
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // --- SMART GREETING ---
    const hour = new Date().getHours();
    let greeting = 'Selamat Malam';
    if (hour >= 4 && hour < 11) greeting = 'Selamat Pagi';
    else if (hour >= 11 && hour < 15) greeting = 'Selamat Siang';
    else if (hour >= 15 && hour < 18) greeting = 'Selamat Sore';
    document.getElementById('dynamic-greeting').innerText = greeting;

    // --- JS ENGINE UNTUK REAL-TIME TIMER DENGAN LOGIKA ISTIRAHAT ---
    const timerEl = document.getElementById('realtime-duration');
    
    if (timerEl) {
        // Ambil string ISO dari dataset yang digenerate oleh PHP
        const strIn = timerEl.getAttribute('data-in');
        const strBout = timerEl.getAttribute('data-bout');
        const strBin = timerEl.getAttribute('data-bin');
        const displaySpan = document.getElementById('timer-display');
        
        // Pastikan strIn ada dan valid
        if (strIn && strIn.trim() !== '') {
            const tIn = new Date(strIn).getTime();
            const tBout = (strBout && strBout.trim() !== '') ? new Date(strBout).getTime() : 0;
            const tBin = (strBin && strBin.trim() !== '') ? new Date(strBin).getTime() : 0;

            if (!isNaN(tIn)) {
                setInterval(() => {
                    const now = new Date().getTime();
                    let diffMs = 0;

                    // Logika: Jika sudah break_out dan sudah break_in (kembali kerja)
                    if (tBout > 0 && tBin > 0) {
                        diffMs = (tBout - tIn) + (now - tBin);
                    } 
                    // Jika belum pernah break sama sekali
                    else {
                        diffMs = now - tIn;
                    }
                    
                    let diffInSeconds = Math.floor(diffMs / 1000);
                    
                    // Pastikan timer tidak pernah menampilkan angka negatif
                    if (diffInSeconds >= 0 && displaySpan) {
                        const h = String(Math.floor(diffInSeconds / 3600)).padStart(2, '0');
                        const m = String(Math.floor((diffInSeconds % 3600) / 60)).padStart(2, '0');
                        const s = String(diffInSeconds % 60).padStart(2, '0');
                        
                        displaySpan.innerText = `${h}:${m}:${s}`;
                    }
                }, 1000); // Update setiap 1 detik
            } else {
                if(displaySpan) displaySpan.innerText = "--:--:--";
            }
        }
    }
});
</script>

<?= $this->endSection() ?>