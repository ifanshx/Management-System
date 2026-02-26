<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const bgColor = isDark ? '#18181b' : '#ffffff';
        const textColor = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ icon: 'success', title: 'Berhasil', text: '<?= session()->getFlashdata('success') ?>', confirmButtonColor: '#38bdf8', background: bgColor, color: textColor });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ icon: 'error', title: 'Gagal', text: '<?= session()->getFlashdata('error') ?>', confirmButtonColor: '#ef4444', background: bgColor, color: textColor });
        <?php endif; ?>
    });

    function confirmAction(url, title, text, color, btnText) {
        Swal.fire({
            title: title, html: text, icon: 'warning', showCancelButton: true,
            confirmButtonColor: color, cancelButtonColor: '#71717a', confirmButtonText: btnText, cancelButtonText: 'Batal',
            background: document.documentElement.classList.contains('dark') ? '#18181b' : '#ffffff',
            color: document.documentElement.classList.contains('dark') ? '#f4f4f5' : '#09090b',
        }).then((result) => {
            if (result.isConfirmed) window.location.href = url;
        });
    }
</script>

<style>
    .module-header { margin-bottom: 30px; }
    .module-title h1 { font-size: 28px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; letter-spacing: -1px; }
    .module-title p { color: var(--text-muted); font-size: 14px; }

    .dashboard-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; align-items: start; }
    @media (max-width: 992px) { .dashboard-grid { grid-template-columns: 1fr; } }

    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 20px; padding: 30px; box-shadow: var(--shadow-card); }
    .bento-header { font-size: 16px; font-weight: 800; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px; }
    
    .status-box { text-align: center; padding: 30px; border-radius: 16px; margin-bottom: 20px; border: 2px dashed var(--border-subtle); }
    .status-box.online { background: rgba(16, 185, 129, 0.05); border-color: #10b981; }
    .status-box.offline { background: rgba(239, 68, 68, 0.05); border-color: #ef4444; }
    
    .icon-pulse { animation: pulse 2s infinite; }
    @keyframes pulse { 0% { transform: scale(0.95); opacity: 0.5; } 50% { transform: scale(1.05); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.5; } }

    .info-list { list-style: none; padding: 0; margin: 0; }
    .info-list li { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border-subtle); font-size: 14px; }
    .info-list li:last-child { border-bottom: none; }
    .info-label { color: var(--text-muted); font-weight: 600; }
    .info-val { color: var(--text-main); font-weight: 800; font-family: monospace; }

    .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
    .tool-btn { background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 25px 20px; border-radius: 16px; text-align: left; cursor: pointer; transition: 0.3s; text-decoration: none; display: flex; flex-direction: column; gap: 15px; }
    .tool-btn:hover { transform: translateY(-5px); box-shadow: var(--shadow-card); }
    .tool-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
    .tool-title { font-size: 15px; font-weight: 800; color: var(--text-main); margin-bottom: 5px; }
    .tool-desc { font-size: 12px; color: var(--text-muted); line-height: 1.4; }

    /* Specific Tool Colors */
    .btn-sync:hover { border-color: #38bdf8; }
    .btn-sync .tool-icon { background: rgba(56, 189, 248, 0.1); color: #38bdf8; }
    
    .btn-audit:hover { border-color: #a855f7; }
    .btn-audit .tool-icon { background: rgba(168, 85, 247, 0.1); color: #a855f7; }
    
    .btn-restart:hover { border-color: #ef4444; }
    .btn-restart .tool-icon { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
</style>

<div class="module-header">
    <div class="module-title">
        <h1>Control Panel Mesin (IoT)</h1>
        <p>Pusat pemeliharaan jarak jauh untuk mesin absensi keras Fingerspot.</p>
    </div>
</div>

<div class="dashboard-grid">
    <div class="bento-card">
        <div class="bento-header"><i class="ph ph-hard-drives"></i> Status Koneksi Mesin</div>
        
        <?php if($device): ?>
            <div class="status-box online">
                <i class="ph ph-wifi-high icon-pulse" style="font-size: 48px; color: #10b981;"></i>
                <h3 style="color: #10b981; font-weight: 800; margin: 10px 0 0 0;">ONLINE & TERHUBUNG</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">Mesin siap menerima perintah.</p>
            </div>
            <ul class="info-list">
                <li><span class="info-label">Cloud ID</span> <span class="info-val"><?= esc($cloudId ?? $cloud_id) ?></span></li>
                <li><span class="info-label">Firmware</span> <span class="info-val"><?= esc($device['firmware'] ?? 'N/A') ?></span></li>
                <li><span class="info-label">Kapasitas User</span> <span class="info-val"><?= esc($device['user_capacity'] ?? 'N/A') ?></span></li>
                <li><span class="info-label">Kapasitas Jari</span> <span class="info-val"><?= esc($device['finger_capacity'] ?? 'N/A') ?></span></li>
            </ul>
        <?php else: ?>
            <div class="status-box offline">
                <i class="ph ph-wifi-slash" style="font-size: 48px; color: #ef4444;"></i>
                <h3 style="color: #ef4444; font-weight: 800; margin: 10px 0 0 0;">OFFLINE / TERPUTUS</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 5px;">Mesin mati atau koneksi Wi-Fi pabrik terputus.</p>
            </div>
            <ul class="info-list">
                <li><span class="info-label">Cloud ID Target</span> <span class="info-val"><?= esc($cloud_id) ?></span></li>
            </ul>
        <?php endif; ?>
    </div>

    <div class="bento-card">
        <div class="bento-header"><i class="ph ph-wrench"></i> Hardware Tools (Tindakan Bahaya)</div>
        
        <div class="action-grid">
            <button type="button" class="tool-btn btn-sync" onclick="confirmAction('<?= base_url('/device/sync_time') ?>', 'Sinkronisasi Waktu?', 'Sistem akan menimpa jam mesin dengan waktu Server (WIB).', '#38bdf8', 'Ya, Samakan Jam')">
                <div class="tool-icon"><i class="ph ph-clock-counter-clockwise"></i></div>
                <div>
                    <div class="tool-title">Sync Jam Mesin</div>
                    <div class="tool-desc">Cegah manipulasi waktu. Sesuaikan jam mesin dengan zona Asia/Jakarta secara otomatis.</div>
                </div>
            </button>

            <button type="button" class="tool-btn btn-audit" onclick="confirmAction('<?= base_url('/device/audit_pins') ?>', 'Audit PIN Terdaftar?', 'Sistem akan menarik daftar semua PIN yang bersarang di memori mesin.', '#a855f7', 'Ya, Tarik Data')">
                <div class="tool-icon"><i class="ph ph-users-three"></i></div>
                <div>
                    <div class="tool-title">Audit PIN Mesin</div>
                    <div class="tool-desc">Menyedot semua daftar User ID (PIN) yang terdaftar di dalam mesin ke log server.</div>
                </div>
            </button>

            <button type="button" class="tool-btn btn-restart" onclick="confirmAction('<?= base_url('/device/restart') ?>', 'Restart Hardware?', '<span style=\'color:#ef4444;\'>Mesin akan mati sementara selama 1-2 menit. Pastikan tidak ada karyawan yang sedang antre absen!</span>', '#ef4444', 'Ya, Restart Sekarang')">
                <div class="tool-icon"><i class="ph ph-power"></i></div>
                <div>
                    <div class="tool-title">Restart Hardware</div>
                    <div class="tool-desc">Lakukan Soft-Reboot dari jarak jauh jika layar mesin mulai lambat atau hang.</div>
                </div>
            </button>
        </div>
    </div>
</div>

<?= $this->endSection() ?>