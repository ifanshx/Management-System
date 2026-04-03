<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.documentElement.classList.contains('dark');
        const bgColor = isDark ? '#18181b' : '#ffffff';
        const textColor = isDark ? '#f4f4f5' : '#09090b';

        <?php if(session()->getFlashdata('success')): ?>
            Swal.fire({ 
                icon: 'success', title: 'Berhasil', html: '<?= session()->getFlashdata('success') ?>', 
                confirmButtonColor: '#10b981', background: bgColor, color: textColor,
                customClass: { popup: 'swal2-custom-radius' }
            });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ 
                icon: 'error', title: 'Aksi Gagal', html: '<?= session()->getFlashdata('error') ?>', 
                confirmButtonColor: '#ef4444', background: bgColor, color: textColor,
                customClass: { popup: 'swal2-custom-radius' }
            });
        <?php endif; ?>
    });

    function confirmAction(url, title, text, color, btnText) {
        const isDark = document.documentElement.classList.contains('dark');
        Swal.fire({
            title: title, 
            html: text, 
            icon: 'warning', 
            showCancelButton: true,
            confirmButtonColor: color, 
            cancelButtonColor: isDark ? '#3f3f46' : '#cbd5e1', 
            confirmButtonText: btnText, 
            cancelButtonText: 'Batal',
            background: isDark ? '#18181b' : '#ffffff',
            color: isDark ? '#f4f4f5' : '#09090b',
            customClass: { popup: 'swal2-custom-radius' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Perintah...',
                    html: 'Sistem sedang menghubungi mesin IoT.<br><span style="font-size:12px; color:gray;">Mohon tunggu sesaat...</span>',
                    allowOutsideClick: false, showConfirmButton: false,
                    background: isDark ? '#18181b' : '#ffffff', color: isDark ? '#f4f4f5' : '#09090b',
                    didOpen: () => { Swal.showLoading(); }
                });
                window.location.href = url;
            }
        });
    }
</script>

<style>
    .swal2-custom-radius { border-radius: 24px !important; font-family: 'Plus Jakarta Sans', sans-serif; }

    /* --- HEADER --- */
    .module-header { margin-bottom: 35px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;}
    .module-title { display: flex; align-items: center; gap: 18px;}
    .title-icon { width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, rgba(168, 85, 247, 0.15), rgba(147, 51, 234, 0.05)); color: #a855f7; display: flex; align-items: center; justify-content: center; font-size: 28px; border: 1px solid rgba(168, 85, 247, 0.2); box-shadow: inset 0 0 20px rgba(168, 85, 247, 0.05);}
    .module-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.8px; }
    .module-title p { color: var(--text-muted); font-size: 14px; font-weight: 500; margin: 0;}

    /* --- LAYOUT GRID --- */
    .dashboard-grid { display: grid; grid-template-columns: 1fr 2.2fr; gap: 30px; align-items: start; }
    @media (max-width: 992px) { .dashboard-grid { grid-template-columns: 1fr; } }

    /* --- BENTO CARD --- */
    .bento-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; padding: 35px; box-shadow: 0 15px 35px -10px rgba(0,0,0,0.05); }
    .bento-header { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 12px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px; }
    .bento-header i { color: #ffffff; background: linear-gradient(135deg, #64748b, #475569); padding: 8px; border-radius: 10px; font-size: 20px; box-shadow: 0 4px 10px rgba(100,116,139,0.3);}
    
    /* --- STATUS BOX --- */
    .status-box { text-align: center; padding: 35px 20px; border-radius: 24px; margin-bottom: 25px; border: 2px dashed var(--border-subtle); position: relative; overflow: hidden;}
    .status-box.online { background: linear-gradient(to bottom, rgba(16, 185, 129, 0.05), transparent); border-color: rgba(16, 185, 129, 0.3); }
    .status-box.offline { background: linear-gradient(to bottom, rgba(239, 68, 68, 0.05), transparent); border-color: rgba(239, 68, 68, 0.3); }
    
    .icon-pulse { animation: pulse 2s infinite cubic-bezier(0.4, 0, 0.2, 1); filter: drop-shadow(0 0 15px rgba(16, 185, 129, 0.5));}
    @keyframes pulse { 0% { transform: scale(0.95); opacity: 0.8; } 50% { transform: scale(1.1); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.8; } }

    /* --- INFO LIST --- */
    .info-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;}
    .info-list li { display: flex; justify-content: space-between; align-items: center; padding: 14px 18px; border-radius: 14px; font-size: 14px; background: var(--bg-base); border: 1px solid var(--border-subtle);}
    .info-label { color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px;}
    .info-val { color: var(--text-main); font-weight: 900; font-family: 'Space Mono', monospace; }

    /* --- ACTION GRID (BUTTONS) --- */
    .action-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
    .tool-btn { background: var(--bg-base); border: 2px solid var(--border-subtle); padding: 25px; border-radius: 24px; text-align: left; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); text-decoration: none; display: flex; flex-direction: column; gap: 15px; position: relative; overflow: hidden;}
    .tool-btn::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent); opacity: 0; transition: 0.3s;}
    
    .tool-btn:hover { transform: translateY(-5px); box-shadow: 0 15px 30px -10px rgba(0,0,0,0.1); }
    .tool-btn:hover::before { opacity: 1; }
    
    .tool-icon { width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 28px; transition: 0.3s;}
    .tool-btn:hover .tool-icon { transform: scale(1.1) rotate(-5deg); }
    
    .tool-title { font-size: 16px; font-weight: 900; color: var(--text-main); margin-bottom: 6px; }
    .tool-desc { font-size: 13px; color: var(--text-muted); line-height: 1.5; font-weight: 500;}

    /* Specific Tool Colors */
    .btn-push:hover { border-color: #3b82f6; background: var(--bg-surface); }
    .btn-push .tool-icon { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #ffffff; box-shadow: 0 8px 15px rgba(59, 130, 246, 0.3);}

    .btn-audit:hover { border-color: #a855f7; background: var(--bg-surface); }
    .btn-audit .tool-icon { background: linear-gradient(135deg, #a855f7, #7e22ce); color: #ffffff; box-shadow: 0 8px 15px rgba(168, 85, 247, 0.3);}
    
    .btn-sync:hover { border-color: #10b981; background: var(--bg-surface); }
    .btn-sync .tool-icon { background: linear-gradient(135deg, #10b981, #047857); color: #ffffff; box-shadow: 0 8px 15px rgba(16, 185, 129, 0.3);}
    
    .btn-restart:hover { border-color: #ef4444; background: var(--bg-surface); }
    .btn-restart .tool-icon { background: linear-gradient(135deg, #ef4444, #b91c1c); color: #ffffff; box-shadow: 0 8px 15px rgba(239, 68, 68, 0.3);}
</style>

<div class="module-header">
    <div class="module-title">
        <div class="title-icon"><i class="ph-fill ph-cpu"></i></div>
        <div>
            <h1>Control Panel Mesin (IoT)</h1>
            <p>Pusat sinkronisasi dan pemeliharaan jarak jauh untuk mesin absensi fisik.</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="bento-card">
        <div class="bento-header"><i class="ph-bold ph-hard-drives"></i> Status Perangkat</div>
        
        <?php if($device): ?>
            <div class="status-box online">
                <i class="ph-fill ph-wifi-high icon-pulse" style="font-size: 56px; color: #10b981;"></i>
                <h3 style="color: #10b981; font-weight: 900; margin: 15px 0 0 0; letter-spacing: -0.5px;">ONLINE & TERHUBUNG</h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-top: 5px; font-weight: 500;">Mesin siap menerima perintah sinkronisasi.</p>
            </div>
            <ul class="info-list">
                <li><span class="info-label">Cloud ID</span> <span class="info-val" style="color: #3b82f6;"><?= esc($cloud_id) ?></span></li>
                <li><span class="info-label">Versi Firmware</span> <span class="info-val"><?= esc($device['firmware'] ?? 'N/A') ?></span></li>
                <li style="background: rgba(59,130,246,0.05); border-color: rgba(59,130,246,0.2);">
                    <span class="info-label" style="color:#3b82f6;"><i class="ph-fill ph-database"></i> Karyawan (Web)</span> 
                    <span class="info-val" style="color:#3b82f6; font-size: 16px;"><?= $totalDbUsers ?> User</span>
                </li>
                <li><span class="info-label">Kapasitas Mesin</span> <span class="info-val"><?= esc($device['user_capacity'] ?? 'N/A') ?> User</span></li>
                <li><span class="info-label">Kapasitas Jari</span> <span class="info-val"><?= esc($device['finger_capacity'] ?? 'N/A') ?></span></li>
            </ul>
        <?php else: ?>
            <div class="status-box offline">
                <i class="ph-fill ph-wifi-slash" style="font-size: 56px; color: #ef4444;"></i>
                <h3 style="color: #ef4444; font-weight: 900; margin: 15px 0 0 0; letter-spacing: -0.5px;">OFFLINE / TERPUTUS</h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-top: 5px; font-weight: 500;">Mesin mati atau koneksi Wi-Fi pabrik terputus.</p>
            </div>
            <ul class="info-list">
                <li><span class="info-label">Cloud ID Target</span> <span class="info-val"><?= esc($cloud_id) ?></span></li>
                <li style="background: rgba(59,130,246,0.05); border-color: rgba(59,130,246,0.2);">
                    <span class="info-label" style="color:#3b82f6;"><i class="ph-fill ph-database"></i> Karyawan (Web)</span> 
                    <span class="info-val" style="color:#3b82f6; font-size: 16px;"><?= $totalDbUsers ?> User</span>
                </li>
            </ul>
        <?php endif; ?>
    </div>

    <div class="bento-card">
        <div class="bento-header" style="border-bottom-color: transparent;"><i class="ph-bold ph-wrench"></i> Manajemen & Sinkronisasi IoT</div>
        
        <div class="action-grid">
            <button type="button" class="tool-btn btn-push" onclick="confirmAction('<?= base_url('/device/push_all_users') ?>', 'Upload Database ke Mesin?', 'Sistem akan mendaftarkan <b style=\'color:#3b82f6;\'>Semua Karyawan Aktif</b> di sistem Web ke dalam memori mesin fisik secara otomatis.', '#3b82f6', 'Ya, Kirim Semua Karyawan')">
                <div class="tool-icon"><i class="ph-bold ph-cloud-arrow-up"></i></div>
                <div>
                    <div class="tool-title">Upload (Push) Karyawan</div>
                    <div class="tool-desc">Kirim identitas seluruh karyawan aktif Web ke memori mesin fisik (Sinkronisasi Web &rarr; Mesin).</div>
                </div>
            </button>

            <button type="button" class="tool-btn btn-audit" onclick="confirmAction('<?= base_url('/device/audit_pins') ?>', 'Tarik Data Mesin?', 'Sistem akan menarik daftar semua PIN yang bersarang di memori mesin untuk dicocokkan dengan Database Web.', '#a855f7', 'Ya, Tarik Data (Pull)')">
                <div class="tool-icon"><i class="ph-bold ph-cloud-arrow-down"></i></div>
                <div>
                    <div class="tool-title">Tarik (Pull) Data Mesin</div>
                    <div class="tool-desc">Menyedot daftar user dan template biometrik dari mesin ke Server (Sinkronisasi Mesin &rarr; Web).</div>
                </div>
            </button>

            <button type="button" class="tool-btn btn-sync" onclick="confirmAction('<?= base_url('/device/sync_time') ?>', 'Sinkronisasi Waktu?', 'Sistem akan menimpa jam mesin dengan waktu Server (WIB).', '#10b981', 'Ya, Samakan Jam')">
                <div class="tool-icon"><i class="ph-bold ph-clock-counter-clockwise"></i></div>
                <div>
                    <div class="tool-title">Sync Jam Mesin</div>
                    <div class="tool-desc">Cegah manipulasi waktu absensi. Sesuaikan jam mesin dengan zona Asia/Jakarta secara instan.</div>
                </div>
            </button>

            <button type="button" class="tool-btn btn-restart" onclick="confirmAction('<?= base_url('/device/restart') ?>', 'Restart Hardware?', '<span style=\'color:#ef4444; font-weight:bold;\'>Mesin akan mati sementara selama 1-2 menit.</span><br>Pastikan tidak ada karyawan yang sedang antre absen!', '#ef4444', 'Ya, Restart Sekarang')">
                <div class="tool-icon"><i class="ph-bold ph-power"></i></div>
                <div>
                    <div class="tool-title">Restart Hardware</div>
                    <div class="tool-desc">Lakukan Soft-Reboot dari jarak jauh jika layar mesin mulai melambat atau mengalami hang.</div>
                </div>
            </button>
        </div>
        
        <div style="margin-top: 30px; padding: 15px 20px; background: rgba(245, 158, 11, 0.05); border: 1px dashed rgba(245, 158, 11, 0.3); border-radius: 16px; display: flex; align-items: center; gap: 15px;">
            <i class="ph-fill ph-warning-circle" style="color: #f59e0b; font-size: 28px;"></i>
            <span style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">
                Pastikan mesin dalam keadaan <b style="color: #10b981;">ONLINE</b> sebelum mengeksekusi perintah di atas. Perintah yang dikirim saat mesin offline akan dibatalkan oleh server secara otomatis.
            </span>
        </div>
    </div>
</div>

<?= $this->endSection() ?>