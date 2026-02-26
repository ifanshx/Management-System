<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    .welcome-banner { background: linear-gradient(135deg, var(--accent-main), #1e3a8a); border-radius: 20px; padding: 30px; color: #fff; margin-bottom: 25px; box-shadow: 0 10px 25px -5px rgba(37,99,235,0.4); position: relative; overflow: hidden; }
    .welcome-banner::after { content: ''; position: absolute; right: -50px; top: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.1); border-radius: 50%; }
    
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 20px; border-radius: 16px; display: flex; align-items: center; gap: 15px; box-shadow: var(--shadow-card); }
    .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    
    .recent-list { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; overflow: hidden; box-shadow: var(--shadow-card); }
    .recent-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 1px dashed var(--border-subtle); }
    .recent-item:last-child { border-bottom: none; }
    
    .status-badge { padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; }
    .status-hadir { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .status-telat { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
</style>

<div class="welcome-banner">
    <div style="position: relative; z-index: 10;">
        <h2 style="margin:0 0 5px 0; font-size: 28px; font-weight: 800;">Halo, <?= esc(session()->get('name')) ?>!</h2>
        <p style="margin:0; font-size: 14px; opacity: 0.9;"><i class="ph ph-identification-badge"></i> NIK: <?= esc(session()->get('employee_id')) ?> | Divisi: <?= esc($employee['department']) ?></p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;"><i class="ph ph-calendar-check"></i></div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 700;">KEHADIRAN BULAN INI</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main);"><?= $totalPresent ?> Hari</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;"><i class="ph ph-warning-circle"></i></div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 700;">TOTAL KETERLAMBATAN</div>
            <div style="font-size: 22px; font-weight: 800; color: var(--text-main);"><?= $totalLateMinutes ?> Menit</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: rgba(168,85,247,0.1); color: #a855f7;"><i class="ph ph-receipt"></i></div>
        <div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 700;">SLIP GAJI TERAKHIR</div>
            <a href="<?= base_url('/portal/slip_gaji') ?>" style="font-size: 13px; font-weight: 700; color: var(--accent-main); text-decoration: none;">Lihat Dokumen &rarr;</a>
        </div>
    </div>
</div>

<h3 style="font-size: 16px; margin-bottom: 15px; color: var(--text-main);"><i class="ph ph-clock-counter-clockwise"></i> Log Kehadiran Terakhir (Fingerspot)</h3>
<div class="recent-list">
    <?php if(empty($recentAttendances)): ?>
        <div style="padding: 30px; text-align: center; color: var(--text-muted); font-size: 13px;">Belum ada data kehadiran.</div>
    <?php else: ?>
        <?php foreach($recentAttendances as $att): ?>
        <div class="recent-item">
            <div>
                <div style="font-weight: 700; color: var(--text-main); font-size: 14px;"><?= date('l, d M Y', strtotime($att['date'])) ?></div>
                <div style="font-size: 12px; color: var(--text-muted); font-family: monospace; margin-top: 4px;">
                    Masuk: <?= $att['time_in'] ? date('H:i', strtotime($att['time_in'])) : '--:--' ?> | 
                    Pulang: <?= $att['time_out'] ? date('H:i', strtotime($att['time_out'])) : '--:--' ?>
                </div>
            </div>
            <div style="text-align: right;">
                <span class="status-badge <?= ($att['status'] == 'Terlambat') ? 'status-telat' : 'status-hadir' ?>">
                    <?= esc($att['status']) ?>
                </span>
                <?php if($att['late_minutes'] > 0): ?>
                    <div style="font-size: 10px; color: #ef4444; font-weight: 700; margin-top: 4px;">Telat <?= $att['late_minutes'] ?> Mnt</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>