<?= $this->extend('layout/template') ?>

<?= $this->section('content') ?>

<style>
    .page-header { margin-bottom: 30px; }
    .page-title h1 { font-size: 26px; font-weight: 800; color: var(--text-main); letter-spacing: -0.5px; margin-bottom: 5px; }
    .page-title p { font-size: 13px; color: var(--text-muted); }

    .profile-layout {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 30px;
        align-items: start;
    }

    /* Left Sidebar: Profile Card & Menu */
    .sidebar-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow-card);
    }

    .profile-header {
        padding: 30px 20px 20px;
        text-align: center;
        border-bottom: 1px solid var(--border-subtle);
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 25px;
        background: var(--accent-main);
        color: #fff;
        font-size: 32px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        box-shadow: 0 10px 20px -5px var(--accent-light);
    }

    .profile-name { font-size: 18px; font-weight: 800; color: var(--text-main); line-height: 1.2; margin-bottom: 4px; }
    .profile-role { font-size: 13px; font-weight: 600; color: var(--accent-main); margin-bottom: 8px; }
    .profile-badge { display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-muted); font-family: monospace; }

    .nav-tabs { padding: 15px; display: flex; flex-direction: column; gap: 5px; }
    .tab-btn {
        display: flex; align-items: center; gap: 12px; padding: 12px 16px; width: 100%;
        background: transparent; border: none; border-radius: 12px;
        color: var(--text-muted); font-size: 14px; font-weight: 600; text-align: left;
        cursor: pointer; transition: all 0.3s; font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .tab-btn i { font-size: 20px; transition: 0.3s; }
    
    .tab-btn:hover { background: var(--bg-base); color: var(--text-main); }
    .tab-btn.active { background: rgba(37, 99, 235, 0.1); color: var(--accent-main); }
    html.dark .tab-btn.active { background: rgba(56, 189, 248, 0.1); }
    .tab-btn.active i { color: var(--accent-main); }

    /* Right Content Area */
    .content-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-subtle);
        border-radius: 20px;
        padding: 35px;
        box-shadow: var(--shadow-card);
        min-height: 400px;
    }

    .tab-pane { display: none; animation: fadeIn 0.4s ease; }
    .tab-pane.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    .pane-title { font-size: 18px; font-weight: 800; color: var(--text-main); margin-bottom: 25px; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 15px; }

    /* Data Display Styles */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .info-box { background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 16px 20px; border-radius: 16px; }
    .info-label { font-size: 12px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
    .info-value { font-size: 15px; font-weight: 600; color: var(--text-main); }

    /* Form Styles (Akun) */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
    .form-control { width: 100%; background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-main); padding: 12px 16px; border-radius: 12px; font-size: 14px; font-family: inherit; outline: none; transition: 0.3s; }
    .form-control:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }
    .btn-submit { background: var(--accent-main); color: #fff; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; margin-top: 10px; }

    /* FAQ Styles */
    .faq-item { border: 1px solid var(--border-subtle); border-radius: 12px; margin-bottom: 15px; overflow: hidden; }
    .faq-question { padding: 16px 20px; background: var(--bg-base); font-weight: 700; color: var(--text-main); cursor: pointer; display: flex; justify-content: space-between; align-items: center; }
    .faq-answer { padding: 0 20px; max-height: 0; overflow: hidden; transition: all 0.3s; color: var(--text-muted); font-size: 14px; line-height: 1.6; background: var(--bg-surface); }
    .faq-item.active .faq-answer { padding: 16px 20px; max-height: 200px; border-top: 1px solid var(--border-subtle); }
    .faq-item.active .faq-question i { transform: rotate(180deg); }

    @media (max-width: 992px) {
        .profile-layout { grid-template-columns: 1fr; }
        .nav-tabs { flex-direction: row; overflow-x: auto; }
        .tab-btn { justify-content: center; white-space: nowrap; }
    }
</style>

<div class="page-header">
    <div class="page-title">
        <h1>Pengaturan Akun</h1>
        <p>Kelola informasi profil, keamanan akses, dan preferensi sistem Anda.</p>
    </div>
</div>

<div class="profile-layout">
    
    <div class="sidebar-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <div class="profile-name"><?= esc($user['name']) ?></div>
            <div class="profile-role">
                <?= ($user['role'] === 'admin') ? 'Super Administrator' : esc($employee['position'] ?? 'Staf') ?>
            </div>
            <div class="profile-badge">NIK: <?= esc($user['employee_id']) ?></div>
        </div>
        
        <div class="nav-tabs">
            <button class="tab-btn active" onclick="switchTab('profil')">
                <i class="ph ph-user"></i> Data Pribadi
            </button>
            <button class="tab-btn" onclick="switchTab('keamanan')">
                <i class="ph ph-shield-check"></i> Keamanan Akun
            </button>
            <button class="tab-btn" onclick="switchTab('bantuan')">
                <i class="ph ph-lifebuoy"></i> Bantuan & Dukungan
            </button>
        </div>
    </div>

    <div class="content-card">
        
        <div id="profil" class="tab-pane active">
            <div class="pane-title"><i class="ph ph-identification-card"></i> Informasi Karyawan</div>
            
            <?php if($user['role'] === 'admin'): ?>
                <div style="padding: 20px; text-align: center; color: var(--text-muted); border: 1px dashed var(--border-subtle); border-radius: 16px;">
                    <i class="ph ph-crown" style="font-size: 40px; color: #f59e0b; margin-bottom: 10px;"></i>
                    <p>Anda login sebagai Super Admin.<br>Data administratif tidak terikat dengan tabel Karyawan.</p>
                </div>
            <?php else: ?>
                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-label">Departemen / Divisi</div>
                        <div class="info-value"><?= esc($employee['department']) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Posisi / Jabatan</div>
                        <div class="info-value"><?= esc($employee['position']) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Status Pegawai</div>
                        <div class="info-value"><?= esc($employee['status']) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Tanggal Bergabung</div>
                        <div class="info-value"><?= date('d F Y', strtotime($employee['join_date'])) ?></div>
                    </div>
                    <div class="info-box" style="grid-column: span 2;">
                        <div class="info-label">Alamat Terdaftar</div>
                        <div class="info-value"><?= esc($employee['address'] ?? 'Belum diisi') ?></div>
                    </div>
                </div>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 20px;">
                    <i class="ph ph-info"></i> Untuk mengubah data profil atau rekening bank, silakan hubungi bagian HRD.
                </p>
            <?php endif; ?>
        </div>

        <div id="keamanan" class="tab-pane">
            <div class="pane-title"><i class="ph ph-lock-key"></i> Ubah Kata Sandi</div>
            
            <form action="<?= base_url('/profile/update_password') ?>" method="post">
                <div class="form-group">
                    <label>Username Anda</label>
                    <input type="text" class="form-control" value="<?= esc($user['username']) ?>" readonly style="background: var(--bg-surface); color: var(--text-muted); cursor: not-allowed;">
                </div>
                <div class="form-group">
                    <label>Kata Sandi Saat Ini</label>
                    <input type="password" name="old_password" class="form-control" placeholder="Masukkan sandi lama..." required>
                </div>
                <div class="form-group">
                    <label>Kata Sandi Baru</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter..." required minlength="6">
                </div>
                <button type="submit" class="btn-submit">
                    <i class="ph ph-floppy-disk"></i> Simpan Kata Sandi
                </button>
            </form>
        </div>

        <div id="bantuan" class="tab-pane">
            <div class="pane-title"><i class="ph ph-headset"></i> Pusat Bantuan Noric</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
                <div class="info-box" style="display: flex; align-items: center; gap: 15px;">
                    <i class="ph ph-whatsapp-logo" style="font-size: 32px; color: #10b981;"></i>
                    <div>
                        <div class="info-label">Support IT (Sistem Error)</div>
                        <div class="info-value">0858-5468-5623</div>
                    </div>
                </div>
                <div class="info-box" style="display: flex; align-items: center; gap: 15px;">
                    <i class="ph ph-buildings" style="font-size: 32px; color: var(--accent-main);"></i>
                    <div>
                        <div class="info-label">HRD (Tanya Gaji/Absen)</div>
                        <div class="info-value">Ext. 104</div>
                    </div>
                </div>
            </div>

            <h4 style="font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 15px;">Pertanyaan yang Sering Diajukan (FAQ)</h4>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Bagaimana jika saya lupa absen di mesin fingerprint?
                    <i class="ph ph-caret-down"></i>
                </div>
                <div class="faq-answer">
                    Segera laporkan kepada atasan (Supervisor) Anda, lalu minta formulir "Koreksi Absensi Manual" ke divisi HRD maksimal 1x24 jam setelah kejadian agar tidak dihitung Alpa.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Kapan slip gaji bisa diunduh di portal?
                    <i class="ph ph-caret-down"></i>
                </div>
                <div class="faq-answer">
                    Slip gaji bulan berjalan akan tersedia dan bisa diunduh dalam format PDF maksimal pada tanggal 1 setiap bulannya.
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Tab Switcher Logic yang Diperbarui
    function switchTab(tabId) {
        // Hapus class active dari semua tombol dan panel
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        // Aktifkan panel konten yang dituju
        document.getElementById(tabId).classList.add('active');
        
        // Aktifkan tombol menu kiri yang sesuai
        const activeBtn = document.querySelector(`.tab-btn[onclick="switchTab('${tabId}')"]`);
        if(activeBtn) activeBtn.classList.add('active');

        // Opsional: Perbarui URL browser agar rapi tanpa me-refresh halaman
        window.history.replaceState(null, null, '#' + tabId);
    }

    // Deteksi Hash di URL saat halaman pertama kali dimuat
    document.addEventListener("DOMContentLoaded", function() {
        // Ambil teks setelah tanda '#' di URL (contoh: 'keamanan')
        const hash = window.location.hash.substring(1); 
        
        // Jika ada hash dan hash tersebut valid, langsung pindah ke tab itu
        if (hash === 'profil' || hash === 'keamanan' || hash === 'bantuan') {
            switchTab(hash);
        }
    });

    // FAQ Accordion Logic
    function toggleFaq(element) {
        const parent = element.parentElement;
        parent.classList.toggle('active');
    }

    // SweetAlert untuk Sukses/Error Ganti Password
    const isDark = document.documentElement.classList.contains('dark');
    const bgColor = isDark ? '#18181b' : '#ffffff';
    const textColor = isDark ? '#f4f4f5' : '#09090b';

    <?php if(session()->getFlashdata('success')): ?>
        Swal.fire({
            icon: 'success', title: 'Berhasil!', text: '<?= session()->getFlashdata('success') ?>',
            confirmButtonColor: '#38bdf8', background: bgColor, color: textColor,
        });
    <?php endif; ?>

    <?php if(session()->getFlashdata('error')): ?>
        Swal.fire({
            icon: 'error', title: 'Gagal!', text: '<?= session()->getFlashdata('error') ?>',
            confirmButtonColor: '#ef4444', background: bgColor, color: textColor,
        });
    <?php endif; ?>
</script>

<?= $this->endSection() ?>