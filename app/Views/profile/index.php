<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================================
       1. PAGE HEADER & TYPOGRAPHY
       ========================================================= */
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;}
    .page-title { display: flex; align-items: center; gap: 15px;}
    .title-icon { width: 50px; height: 50px; border-radius: 14px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.05)); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 26px; border: 1px solid rgba(59, 130, 246, 0.2);}
    .page-title h1 { font-size: 26px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.5px;}
    .page-title p { font-size: 13px; color: var(--text-muted); font-weight: 500; margin: 0;}

    /* =========================================================
       2. LAYOUT & SIDEBAR (BENTO & STICKY)
       ========================================================= */
    .profile-layout { display: grid; grid-template-columns: 320px 1fr; gap: 30px; align-items: start; }
    @media (max-width: 992px) { .profile-layout { grid-template-columns: 1fr; } }

    .sidebar-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; overflow: hidden; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); position: sticky; top: 20px;}
    
    .profile-header { padding: 35px 25px 25px; text-align: center; border-bottom: 1px dashed var(--border-subtle); background: rgba(0,0,0,0.01);}
    html.dark .profile-header { background: rgba(255,255,255,0.01); }

    .profile-avatar { width: 88px; height: 88px; border-radius: 24px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; font-size: 36px; font-weight: 900; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5); border: 2px solid rgba(255,255,255,0.2);}
    .profile-name { font-size: 20px; font-weight: 900; color: var(--text-main); line-height: 1.2; margin-bottom: 6px; letter-spacing: -0.5px;}
    .profile-role { font-size: 13px; font-weight: 800; color: #3b82f6; margin-bottom: 12px; }
    .profile-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 800; background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-muted); font-family: 'Space Mono', monospace; }

    /* Navigation Tabs */
    .nav-tabs { padding: 15px; display: flex; flex-direction: column; gap: 8px; }
    .tab-btn { display: flex; align-items: center; gap: 12px; padding: 14px 20px; width: 100%; background: transparent; border: 1px solid transparent; border-radius: 14px; color: var(--text-muted); font-size: 13px; font-weight: 800; text-align: left; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);}
    .tab-btn i { font-size: 20px; transition: transform 0.3s; }
    
    .tab-btn:hover { background: rgba(59, 130, 246, 0.05); color: #3b82f6; padding-left: 25px; border-color: rgba(59, 130, 246, 0.1);}
    .tab-btn:hover i { transform: scale(1.1); }
    .tab-btn.active { background: rgba(59, 130, 246, 0.1); color: #2563eb; border-color: rgba(59, 130, 246, 0.2); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.1);}
    html.dark .tab-btn.active { color: #60a5fa; }

    /* =========================================================
       3. RIGHT CONTENT AREA
       ========================================================= */
    .content-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px; padding: 40px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); min-height: 500px; }

    .tab-pane { display: none; animation: slideFadeUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
    .tab-pane.active { display: block; }
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }

    .pane-title { font-size: 18px; font-weight: 900; color: var(--text-main); margin-bottom: 30px; display: flex; align-items: center; gap: 12px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 15px; }
    .pane-title i { color: #3b82f6; background: rgba(59, 130, 246, 0.1); padding: 8px; border-radius: 10px; font-size: 20px;}

    /* Info Grid (Data Pribadi) */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .info-box { background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 20px 25px; border-radius: 16px; transition: 0.3s;}
    .info-box:hover { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.05);}
    .info-label { font-size: 11px; color: var(--text-muted); font-weight: 900; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.5px;}
    .info-value { font-size: 15px; font-weight: 800; color: var(--text-main); }

    /* Form Styles (Akun) */
    .form-group { margin-bottom: 24px; }
    .form-group label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .input-wrapper { display: flex; align-items: center; background: var(--bg-base); border: 1px solid var(--border-subtle); border-radius: 14px; overflow: hidden; transition: 0.3s; }
    .input-wrapper:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); background: var(--bg-surface);}
    .input-wrapper input { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 16px 20px; font-size: 14px; font-weight: 700; outline: none; font-family: inherit;}
    .btn-toggle-pass { background: transparent; border: none; color: var(--text-muted); padding: 0 20px; cursor: pointer; font-size: 18px; transition: 0.2s;}
    .btn-toggle-pass:hover { color: #3b82f6; }

    .btn-submit { background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff; border: none; padding: 16px 30px; border-radius: 14px; font-weight: 900; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; box-shadow: 0 8px 20px -5px rgba(59, 130, 246, 0.5); margin-top: 10px;}
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 12px 25px -5px rgba(59, 130, 246, 0.6); }

    /* FAQ Styles */
    .faq-item { border: 1px solid var(--border-subtle); border-radius: 16px; margin-bottom: 15px; overflow: hidden; transition: 0.3s; background: var(--bg-base);}
    .faq-question { padding: 20px 25px; font-weight: 800; color: var(--text-main); font-size: 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: 0.3s;}
    .faq-question:hover { color: #3b82f6; }
    .faq-question i { color: var(--text-muted); transition: 0.3s; font-size: 18px;}
    
    .faq-answer { padding: 0 25px; max-height: 0; overflow: hidden; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); color: var(--text-muted); font-size: 13px; line-height: 1.6; font-weight: 500;}
    .faq-item.active { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.08);}
    .faq-item.active .faq-answer { padding: 0 25px 25px; max-height: 300px; }
    .faq-item.active .faq-question { color: #3b82f6; padding-bottom: 10px;}
    .faq-item.active .faq-question i { transform: rotate(180deg); color: #3b82f6; }

    @media (max-width: 992px) {
        .nav-tabs { flex-direction: row; overflow-x: auto; padding: 15px 20px;}
        .tab-btn { justify-content: center; white-space: nowrap; padding: 12px 20px;}
        .tab-btn:hover { padding-left: 20px; }
    }
</style>

<div class="page-header">
    <div class="page-title">
        <div class="title-icon"><i class="ph-fill ph-user-gear"></i></div>
        <div>
            <h1>Pengaturan Akun</h1>
            <p>Kelola informasi profil, keamanan akses, dan preferensi sistem Anda.</p>
        </div>
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
                <?= ($user['role'] === 'admin') ? 'Super Administrator' : esc($employee['position'] ?? 'Staf Karyawan') ?>
            </div>
            <div class="profile-badge"><i class="ph-bold ph-identification-card"></i> NIK: <?= esc($user['employee_id']) ?></div>
        </div>
        
        <div class="nav-tabs">
            <button class="tab-btn active" onclick="switchTab('profil')">
                <i class="ph-bold ph-user"></i> Data Pribadi
            </button>
            <button class="tab-btn" onclick="switchTab('keamanan')">
                <i class="ph-bold ph-shield-check"></i> Keamanan Akun
            </button>
            <button class="tab-btn" onclick="switchTab('bantuan')">
                <i class="ph-bold ph-lifebuoy"></i> Bantuan & Dukungan
            </button>
        </div>
    </div>

    <div class="content-card">
        
        <div id="profil" class="tab-pane active">
            <div class="pane-title"><i class="ph-fill ph-identification-card"></i> Informasi Karyawan</div>
            
            <?php if($user['role'] === 'admin'): ?>
                <div style="padding: 40px 20px; text-align: center; color: var(--text-muted); border: 2px dashed var(--border-subtle); border-radius: 20px; background: var(--bg-base);">
                    <i class="ph-fill ph-crown" style="font-size: 56px; color: #f59e0b; margin-bottom: 15px; display: block;"></i>
                    <div style="font-weight: 900; font-size: 18px; color: var(--text-main); margin-bottom: 5px;">Akses Super Admin Aktif</div>
                    <p style="font-size: 14px;">Data administratif Anda memiliki hak akses penuh dan tidak terikat pada struktur tabel kepegawaian standar.</p>
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
                        <div class="info-value"><i class="ph-bold ph-calendar-blank" style="margin-right: 4px; color: var(--text-muted);"></i> <?= date('d F Y', strtotime($employee['join_date'])) ?></div>
                    </div>
                    <div class="info-box" style="grid-column: span 2;">
                        <div class="info-label">Alamat Terdaftar</div>
                        <div class="info-value" style="line-height: 1.5; font-size: 14px;"><?= esc($employee['address'] ?? 'Belum ada data alamat terdaftar.') ?></div>
                    </div>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 25px; display: flex; align-items: center; gap: 8px; background: rgba(59, 130, 246, 0.05); padding: 12px 15px; border-radius: 10px; border: 1px dashed rgba(59, 130, 246, 0.2);">
                    <i class="ph-fill ph-info" style="color: #3b82f6; font-size: 18px;"></i> 
                    Untuk merubah data profil demografis atau nomor rekening bank, silakan hubungi bagian HRD.
                </div>
            <?php endif; ?>
        </div>

        <div id="keamanan" class="tab-pane">
            <div class="pane-title"><i class="ph-fill ph-lock-key"></i> Ubah Kata Sandi</div>
            
            <form action="<?= base_url('/profile/update_password') ?>" method="post">
                <div class="form-group">
                    <label>Username Akses (ID Login)</label>
                    <div class="input-wrapper" style="background: rgba(0,0,0,0.02);">
                        <input type="text" value="<?= esc($user['username']) ?>" readonly style="color: var(--text-muted); cursor: not-allowed; font-family: 'Space Mono', monospace;">
                        <span class="btn-toggle-pass" style="cursor: default;"><i class="ph-bold ph-lock"></i></span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kata Sandi Saat Ini</label>
                    <div class="input-wrapper">
                        <input type="password" name="old_password" id="oldPass" placeholder="Masukkan kata sandi lama Anda..." required>
                        <button type="button" class="btn-toggle-pass" onclick="togglePass('oldPass', this)"><i class="ph-bold ph-eye"></i></button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Kata Sandi Baru</label>
                    <div class="input-wrapper">
                        <input type="password" name="new_password" id="newPass" placeholder="Kombinasi huruf dan angka (Min. 6 karakter)..." required minlength="6">
                        <button type="button" class="btn-toggle-pass" onclick="togglePass('newPass', this)"><i class="ph-bold ph-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn-submit" onclick="this.innerHTML='<i class=\'ph-bold ph-spinner-gap ph-spin\' style=\'font-size:20px;\'></i> Menyimpan...';">
                    <i class="ph-bold ph-floppy-disk"></i> Simpan Perubahan Sandi
                </button>
            </form>
        </div>

        <div id="bantuan" class="tab-pane">
            <div class="pane-title"><i class="ph-fill ph-headset"></i> Pusat Bantuan Noric</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 35px;">
                <div class="info-box" style="display: flex; align-items: center; gap: 15px; border-color: rgba(16, 185, 129, 0.3);">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 24px;"><i class="ph-fill ph-whatsapp-logo"></i></div>
                    <div>
                        <div class="info-label">Support IT (Sistem Error)</div>
                        <div class="info-value" style="font-family: 'Space Mono', monospace;">0858-5468-5623</div>
                    </div>
                </div>
                <div class="info-box" style="display: flex; align-items: center; gap: 15px; border-color: rgba(245, 158, 11, 0.3);">
                    <div style="width: 48px; height: 48px; border-radius: 14px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 24px;"><i class="ph-fill ph-buildings"></i></div>
                    <div>
                        <div class="info-label">HRD (Tanya Gaji/Absen)</div>
                        <div class="info-value" style="font-family: 'Space Mono', monospace;">Ext. 104</div>
                    </div>
                </div>
            </div>

            <h4 style="font-size: 14px; font-weight: 800; color: var(--text-main); margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;">Pertanyaan yang Sering Diajukan (FAQ)</h4>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Bagaimana jika saya lupa absen di mesin fingerprint / wajah?</span>
                    <i class="ph-bold ph-caret-down"></i>
                </div>
                <div class="faq-answer">
                    Segera laporkan kepada atasan (Supervisor) Anda pada hari yang sama, lalu minta persetujuan untuk mengisi formulir "Koreksi Absensi Manual" ke divisi HRD maksimal 1x24 jam setelah kejadian agar hari tersebut tidak dihitung Alpa/Mangkir.
                </div>
            </div>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    <span>Kapan dokumen slip gaji bulanan bisa saya unduh?</span>
                    <i class="ph-bold ph-caret-down"></i>
                </div>
                <div class="faq-answer">
                    Slip gaji resmi bulan berjalan akan otomatis tersedia di menu <b>Slip Gaji</b> pada portal Anda segera setelah dokumen Penggajian (Payroll) disahkan secara sistem oleh tim Keuangan (Finance). Anda dapat mengunduhnya kapan saja dalam format PDF.
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // --- 1. Tab Switcher Logic ---
    function switchTab(tabId) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        document.getElementById(tabId).classList.add('active');
        
        const activeBtn = document.querySelector(`.tab-btn[onclick="switchTab('${tabId}')"]`);
        if(activeBtn) activeBtn.classList.add('active');

        // Update URL tanpa refresh halaman agar jika di-reload tetap di tab yang sama
        window.history.replaceState(null, null, '#' + tabId);
    }

    // Restore tab state from URL Hash
    document.addEventListener("DOMContentLoaded", function() {
        const hash = window.location.hash.substring(1); 
        if (hash === 'profil' || hash === 'keamanan' || hash === 'bantuan') {
            switchTab(hash);
        }
    });

    // --- 2. FAQ Accordion Logic ---
    function toggleFaq(element) {
        // Optional: Tutup semua FAQ lain saat satu dibuka
        document.querySelectorAll('.faq-item').forEach(item => {
            if(item !== element.parentElement) item.classList.remove('active');
        });
        element.parentElement.classList.toggle('active');
    }

    // --- 3. Toggle Password Visibility ---
    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        
        if(input.type === 'password') {
            input.type = 'text';
            icon.className = 'ph-bold ph-eye-slash';
            icon.style.color = '#3b82f6';
        } else {
            input.type = 'password';
            icon.className = 'ph-bold ph-eye';
            icon.style.color = 'var(--text-muted)';
        }
    }
</script>

<?= $this->endSection() ?>