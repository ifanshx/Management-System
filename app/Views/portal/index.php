<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. WELCOME BANNER (PREMIUM GLASSMORPHISM)
       ========================================================= */
    .welcome-banner { 
        background: linear-gradient(135deg, #2563eb, #1e40af); 
        border-radius: 24px; 
        padding: 40px; 
        color: #fff; 
        margin-bottom: 30px; 
        box-shadow: 0 15px 40px -10px rgba(37,99,235,0.4); 
        position: relative; 
        overflow: hidden; 
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .welcome-banner::before { 
        content: ''; position: absolute; left: -20px; top: -50px; width: 150px; height: 150px; 
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); border-radius: 50%; 
    }
    .welcome-banner::after { 
        content: ''; position: absolute; right: -50px; bottom: -80px; width: 250px; height: 250px; 
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%; 
    }
    
    .profile-info { display: flex; align-items: center; gap: 20px; position: relative; z-index: 10;}
    
    .avatar-large { 
        width: 72px; height: 72px; border-radius: 20px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);
        display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 900; 
        border: 2px solid rgba(255,255,255,0.3); box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    
    .greeting h2 { margin:0 0 8px 0; font-size: 32px; font-weight: 900; letter-spacing: -0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);}
    .greeting-meta { display: inline-flex; align-items: center; gap: 15px; font-size: 13px; font-weight: 600; background: rgba(0,0,0,0.15); padding: 8px 16px; border-radius: 12px; backdrop-filter: blur(4px);}
    .greeting-meta span { display: flex; align-items: center; gap: 6px; }

    .quick-action-btn { background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); color: #fff; border: 1px solid rgba(255,255,255,0.3); padding: 14px 24px; border-radius: 16px; font-weight: 800; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; transition: 0.3s; position: relative; z-index: 10; box-shadow: 0 4px 15px rgba(0,0,0,0.1);}
    .quick-action-btn:hover { background: #fff; color: #2563eb; transform: translateY(-3px);}

    /* =========================================================
       2. STATS BENTO GRID
       ========================================================= */
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 30px; }
    
    .stat-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 25px; border-radius: 24px; display: flex; align-items: center; gap: 18px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); transition: 0.3s; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 15px 35px -10px rgba(0,0,0,0.08); border-color: rgba(37,99,235,0.2);}
    
    .stat-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; }
    .stat-label { font-size: 11px; color: var(--text-muted); font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;}
    .stat-val { font-size: 28px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; letter-spacing: -1px;}

    /* =========================================================
       3. RECENT ATTENDANCE (TIMELINE STYLE)
       ========================================================= */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; padding: 30px; box-shadow: 0 15px 40px -15px rgba(0,0,0,0.05);}
    .card-title { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px;}
    .card-title i { color: #3b82f6; background: rgba(59, 130, 246, 0.1); padding: 8px; border-radius: 10px; font-size: 20px;}

    .timeline-container { display: flex; flex-direction: column; gap: 15px; }
    
    .timeline-item { display: flex; align-items: center; justify-content: space-between; padding: 20px; background: var(--bg-base); border: 1px solid transparent; border-radius: 20px; transition: 0.3s; }
    .timeline-item:hover { background: var(--bg-surface); border-color: var(--border-subtle); box-shadow: 0 8px 20px rgba(0,0,0,0.03); transform: translateX(5px);}
    
    .tl-left { display: flex; align-items: center; gap: 20px; }
    .tl-date-box { background: var(--bg-surface); border: 1px solid var(--border-subtle); width: 60px; height: 60px; border-radius: 14px; display: flex; flex-direction: column; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
    .tl-day { font-size: 11px; font-weight: 900; color: #ef4444; text-transform: uppercase; margin-bottom: 2px;}
    .tl-date { font-size: 20px; font-weight: 900; color: var(--text-main); font-family: 'Space Mono', monospace; line-height: 1;}

    .tl-info h4 { margin: 0 0 6px 0; font-size: 15px; font-weight: 800; color: var(--text-main);}
    .tl-time-capsules { display: flex; gap: 10px;}
    .tl-time { background: var(--bg-surface); border: 1px dashed var(--border-subtle); padding: 4px 10px; border-radius: 8px; font-size: 12px; font-family: 'Space Mono', monospace; font-weight: 700; color: var(--text-muted); display: flex; align-items: center; gap: 6px;}

    .tl-right { text-align: right; }
    .status-badge { padding: 6px 14px; border-radius: 10px; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 6px;}
    .status-hadir { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);}
    .status-telat { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);}
    
    .empty-state { text-align: center; padding: 40px; color: var(--text-muted); }
    .empty-state i { font-size: 48px; color: var(--border-subtle); margin-bottom: 10px; display: block; }
</style>

<div class="welcome-banner">
    <div class="profile-info">
        <div class="avatar-large">
            <?= strtoupper(substr(session()->get('name'), 0, 1)) ?>
        </div>
        <div class="greeting">
            <h2>Halo, <?= esc(session()->get('name')) ?>!</h2>
            <div class="greeting-meta">
                <span><i class="ph-bold ph-identification-badge"></i> NIK: <?= esc(session()->get('employee_id')) ?></span>
                <span style="opacity: 0.5;">|</span>
                <span><i class="ph-bold ph-briefcase"></i> <?= esc($employee['department']) ?></span>
            </div>
        </div>
    </div>
    
    <a href="<?= base_url('/leave') ?>" class="quick-action-btn">
        <i class="ph-bold ph-calendar-plus" style="font-size: 20px;"></i> Ajukan Cuti / Izin
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2);"><i class="ph-fill ph-calendar-check"></i></div>
        <div>
            <div class="stat-label">Kehadiran Bulan Ini</div>
            <div class="stat-val"><?= $totalPresent ?> <span style="font-size: 14px; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif;">Hari</span></div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);"><i class="ph-fill ph-warning-circle"></i></div>
        <div>
            <div class="stat-label">Total Keterlambatan</div>
            <div class="stat-val"><?= $totalLateMinutes ?> <span style="font-size: 14px; color: var(--text-muted); font-family: 'Plus Jakarta Sans', sans-serif;">Menit</span></div>
        </div>
    </div>
    
    <div class="stat-card" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(168, 85, 247, 0.02)); border-color: rgba(168, 85, 247, 0.3);">
        <div class="stat-icon" style="background: #a855f7; color: #fff; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.4);"><i class="ph-fill ph-receipt"></i></div>
        <div>
            <div class="stat-label" style="color: #a855f7;">Slip Gaji Terakhir</div>
            <a href="<?= base_url('/portal/slip_gaji') ?>" style="font-size: 14px; font-weight: 900; color: #a855f7; text-decoration: none; display: flex; align-items: center; gap: 4px; margin-top: 4px;">
                Lihat Dokumen <i class="ph-bold ph-arrow-right"></i>
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
                <div style="font-weight: 800; color: var(--text-main); font-size: 16px; margin-bottom: 4px;">Belum Ada Riwayat</div>
                <div style="font-size: 13px;">Data absensi dari mesin belum masuk ke dalam sistem.</div>
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
                                <i class="ph-bold ph-sign-in" style="color: #10b981;"></i>
                                <?= $att['time_in'] ? date('H:i', strtotime($att['time_in'])) : '--:--' ?>
                            </div>
                            <div class="tl-time" title="Jam Pulang">
                                <i class="ph-bold ph-sign-out" style="color: #ef4444;"></i>
                                <?= $att['time_out'] ? date('H:i', strtotime($att['time_out'])) : '--:--' ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tl-right">
                    <span class="status-badge <?= ($att['status'] == 'Terlambat') ? 'status-telat' : 'status-hadir' ?>">
                        <i class="ph-fill <?= ($att['status'] == 'Terlambat') ? 'ph-warning-circle' : 'ph-check-circle' ?>"></i>
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

<?= $this->endSection() ?>