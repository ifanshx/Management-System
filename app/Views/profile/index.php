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
                icon: 'success', title: 'Berhasil!', html: '<?= session()->getFlashdata('success') ?>', 
                confirmButtonColor: '#10b981', background: bgColor, color: textColor 
            });
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            Swal.fire({ 
                icon: 'error', title: 'Pembaruan Gagal!', html: '<?= session()->getFlashdata('error') ?>', 
                confirmButtonColor: '#ef4444', background: bgColor, color: textColor 
            });
        <?php endif; ?>
    });
</script>

<style>
    /* =========================================================
       1. PAGE HEADER & TYPOGRAPHY
       ========================================================= */
    .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 20px;}
    .page-title { display: flex; align-items: center; gap: 18px;}
    .title-icon { width: 54px; height: 54px; border-radius: 16px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.05)); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 28px; border: 1px solid rgba(59, 130, 246, 0.2); box-shadow: inset 0 0 20px rgba(59,130,246,0.05);}
    .page-title h1 { font-size: 28px; font-weight: 900; color: var(--text-main); margin: 0 0 4px 0; letter-spacing: -0.8px;}
    .page-title p { font-size: 14px; color: var(--text-muted); font-weight: 500; margin: 0;}

    /* =========================================================
       2. LAYOUT & SIDEBAR (PREMIUM BENTO)
       ========================================================= */
    .profile-layout { display: grid; grid-template-columns: 340px 1fr; gap: 30px; align-items: start; }
    @media (max-width: 992px) { .profile-layout { grid-template-columns: 1fr; } }

    .sidebar-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; overflow: hidden; box-shadow: 0 15px 35px -10px rgba(0,0,0,0.05); position: sticky; top: 20px;}
    
    .profile-header { padding: 40px 25px 30px; text-align: center; border-bottom: 1px dashed var(--border-subtle); background: linear-gradient(to bottom, rgba(59,130,246,0.03), transparent); position: relative; overflow: hidden;}
    .profile-header::before { content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(59,130,246,0.05) 0%, transparent 60%); z-index: 0; pointer-events: none;}

    .profile-avatar { position: relative; z-index: 1; width: 96px; height: 96px; border-radius: 28px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; font-size: 40px; font-weight: 900; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; box-shadow: 0 12px 25px -5px rgba(37, 99, 235, 0.5), inset 0 2px 5px rgba(255,255,255,0.3); border: 3px solid var(--bg-surface); outline: 2px solid rgba(59,130,246,0.2); outline-offset: 4px;}
    .profile-name { position: relative; z-index: 1; font-size: 22px; font-weight: 900; color: var(--text-main); line-height: 1.2; margin-bottom: 6px; letter-spacing: -0.5px;}
    .profile-role { position: relative; z-index: 1; font-size: 14px; font-weight: 800; color: #3b82f6; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 0.5px;}
    .profile-badge { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 10px; font-size: 13px; font-weight: 800; background: var(--bg-base); border: 1px solid var(--border-subtle); color: var(--text-muted); font-family: 'Space Mono', monospace; box-shadow: 0 2px 5px rgba(0,0,0,0.02);}

    /* Navigation Tabs */
    .nav-tabs { padding: 20px; display: flex; flex-direction: column; gap: 10px; }
    .tab-btn { display: flex; align-items: center; gap: 14px; padding: 16px 20px; width: 100%; background: transparent; border: 1px solid transparent; border-radius: 16px; color: var(--text-muted); font-size: 14px; font-weight: 800; text-align: left; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden;}
    .tab-btn i { font-size: 22px; transition: transform 0.3s; z-index: 1;}
    .tab-btn span { z-index: 1; }
    
    .tab-btn::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 0; background: rgba(59, 130, 246, 0.05); transition: 0.3s; z-index: 0; border-radius: 16px;}
    .tab-btn:hover::before { width: 100%; }
    .tab-btn:hover { color: #3b82f6; padding-left: 25px; border-color: rgba(59, 130, 246, 0.1);}
    .tab-btn:hover i { transform: scale(1.15); }
    
    .tab-btn.active { background: rgba(59, 130, 246, 0.1); color: #2563eb; border-color: rgba(59, 130, 246, 0.2); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.15);}
    .tab-btn.active::before { width: 4px; background: #2563eb; border-radius: 4px 0 0 4px; left: -1px;}
    html.dark .tab-btn.active { color: #60a5fa; }

    /* =========================================================
       3. RIGHT CONTENT AREA
       ========================================================= */
    .content-card { background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 28px; padding: 45px; box-shadow: 0 15px 35px -10px rgba(0,0,0,0.05); min-height: 500px; }

    .tab-pane { display: none; animation: slideFadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
    .tab-pane.active { display: block; }
    @keyframes slideFadeUp { from { opacity: 0; transform: translateY(20px); filter: blur(2px); } to { opacity: 1; transform: translateY(0); filter: blur(0); } }

    .pane-title { font-size: 20px; font-weight: 900; color: var(--text-main); margin-bottom: 35px; display: flex; align-items: center; gap: 14px; border-bottom: 2px dashed var(--border-subtle); padding-bottom: 18px; }
    .pane-title i { color: #ffffff; background: linear-gradient(135deg, #3b82f6, #2563eb); padding: 10px; border-radius: 12px; font-size: 22px; box-shadow: 0 4px 10px rgba(59,130,246,0.3);}

    /* Info Grid (Data Pribadi) */
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .info-box { background: var(--bg-base); border: 1px solid var(--border-subtle); padding: 22px 25px; border-radius: 18px; transition: 0.3s; position: relative; overflow: hidden;}
    .info-box:hover { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 8px 20px rgba(59, 130, 246, 0.08); transform: translateY(-2px);}
    .info-label { font-size: 11px; color: var(--text-muted); font-weight: 900; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.8px;}
    .info-value { font-size: 16px; font-weight: 800; color: var(--text-main); }

    /* Form Styles (Akun & Keamanan) */
    .form-group { margin-bottom: 26px; }
    .form-group label { display: block; font-size: 12px; font-weight: 800; color: var(--text-muted); margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;}
    
    .input-wrapper { display: flex; align-items: stretch; background: var(--bg-base); border: 2px solid var(--border-subtle); border-radius: 16px; overflow: hidden; transition: 0.3s; }
    .input-wrapper:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15); background: var(--bg-surface);}
    .input-wrapper input { flex: 1; background: transparent; border: none; color: var(--text-main); padding: 18px 20px; font-size: 15px; font-weight: 700; outline: none; font-family: inherit;}
    .btn-toggle-pass { background: transparent; border: none; color: var(--text-muted); padding: 0 20px; cursor: pointer; font-size: 20px; transition: 0.2s; display: flex; align-items: center;}
    .btn-toggle-pass:hover { color: #3b82f6; background: rgba(59,130,246,0.05);}

    .btn-submit { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; border: none; padding: 18px 35px; border-radius: 16px; font-weight: 900; font-size: 16px; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5); margin-top: 10px;}
    .btn-submit:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -5px rgba(59, 130, 246, 0.6); filter: brightness(1.1);}
    .btn-submit:active { transform: translateY(0); box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4); }

    /* FAQ Styles */
    .faq-item { border: 1px solid var(--border-subtle); border-radius: 18px; margin-bottom: 15px; overflow: hidden; transition: 0.3s; background: var(--bg-base);}
    .faq-question { padding: 22px 25px; font-weight: 800; color: var(--text-main); font-size: 15px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: 0.3s;}
    .faq-question:hover { color: #3b82f6; background: rgba(59,130,246,0.02);}
    .faq-question i { color: var(--text-muted); transition: 0.4s cubic-bezier(0.16, 1, 0.3, 1); font-size: 20px;}
    
    .faq-answer { padding: 0 25px; max-height: 0; overflow: hidden; transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); color: var(--text-muted); font-size: 14px; line-height: 1.7; font-weight: 500;}
    .faq-item.active { border-color: #3b82f6; background: var(--bg-surface); box-shadow: 0 6px 20px rgba(59, 130, 246, 0.08);}
    .faq-item.active .faq-answer { padding: 0 25px 25px; max-height: 400px; }
    .faq-item.active .faq-question { color: #3b82f6; padding-bottom: 15px;}
    .faq-item.active .faq-question i { transform: rotate(180deg); color: #3b82f6; background: rgba(59,130,246,0.1); padding: 4px; border-radius: 8px;}

    @media (max-width: 992px) {
        .nav-tabs { flex-direction: row; overflow-x: auto; padding: 15px 20px; scrollbar-width: none;}
        .nav-tabs::-webkit-scrollbar { display: none; }
        .tab-btn { justify-content: center; white-space: nowrap; padding: 14px 20px;}
        .tab-btn:hover { padding-left: 20px; }
        .content-card { padding: 30px 25px; }
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
                <i class="ph-bold ph-user"></i> <span>Data Pribadi</span>
            </button>
            <button class="tab-btn" onclick="switchTab('keamanan')">
                <i class="ph-bold ph-shield-check"></i> <span>Keamanan Akun</span>
            </button>
            <button class="tab-btn" onclick="switchTab('bantuan')">
                <i class="ph-bold ph-lifebuoy"></i> <span>Bantuan & Dukungan</span>
            </button>
        </div>
    </div>

    <div class="content-card">
        
        <div id="profil" class="tab-pane active">
            <div class="pane-title"><i class="ph-fill ph-identification-card"></i> Informasi Karyawan</div>
            
            <?php if($user['role'] === 'admin'): ?>
                <div style="padding: 50px 30px; text-align: center; color: var(--text-muted); border: 2px dashed var(--border-subtle); border-radius: 24px; background: var(--bg-base); transition: 0.3s; cursor: default;" onmouseover="this.style.borderColor='#f59e0b'; this.style.backgroundColor='rgba(245,158,11,0.02)';" onmouseout="this.style.borderColor='var(--border-subtle)'; this.style.backgroundColor='var(--bg-base)';">
                    <i class="ph-fill ph-crown" style="font-size: 64px; color: #f59e0b; margin-bottom: 18px; display: block; filter: drop-shadow(0 4px 10px rgba(245,158,11,0.4));"></i>
                    <div style="font-weight: 900; font-size: 22px; color: var(--text-main); margin-bottom: 8px;">Akses Super Admin Aktif</div>
                    <p style="font-size: 15px; max-width: 400px; margin: 0 auto; line-height: 1.6;">Data administratif Anda memiliki hak akses penuh dan tidak terikat pada struktur tabel kepegawaian standar.</p>
                </div>
            <?php else: ?>
                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-label">Departemen / Divisi</div>
                        <div class="info-value"><?= esc($employee['department'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Posisi / Jabatan</div>
                        <div class="info-value"><?= esc($employee['position'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Status Pegawai</div>
                        <div class="info-value">
                            <span style="color: #10b981;"><i class="ph-fill ph-check-circle" style="margin-right:4px;"></i> <?= esc($employee['status'] ?? 'Aktif') ?></span>
                        </div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Tanggal Bergabung</div>
                        <div class="info-value"><i class="ph-bold ph-calendar-blank" style="margin-right: 6px; color: #3b82f6;"></i> <?= isset($employee['join_date']) ? date('d F Y', strtotime($employee['join_date'])) : 'N/A' ?></div>
                    </div>
                    <div class="info-box" style="grid-column: span 2;">
                        <div class="info-label">Alamat Terdaftar</div>
                        <div class="info-value" style="line-height: 1.6; font-size: 15px;"><?= esc($employee['address'] ?? 'Belum ada data alamat terdaftar.') ?></div>
                    </div>
                </div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 30px; display: flex; align-items: center; gap: 12px; background: rgba(59, 130, 246, 0.05); padding: 16px 20px; border-radius: 14px; border: 1px dashed rgba(59, 130, 246, 0.3);">
                    <i class="ph-fill ph-info" style="color: #3b82f6; font-size: 24px; flex-shrink: 0;"></i> 
                    <span>Untuk merubah data profil demografis, jabatan, atau nomor rekening bank, silakan hubungi bagian <b>Human Resources (HRD)</b>.</span>
                </div>
            <?php endif; ?>
        </div>

        <div id="keamanan" class="tab-pane">
            <div class="pane-title"><i class="ph-fill ph-shield-check"></i> Otentikasi & Keamanan</div>
            
            <form action="<?= base_url('/profile/update_password') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label>Username Akses (ID Login)</label>
                    <div class="input-wrapper" style="background: rgba(0,0,0,0.02); border-color: transparent; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                        <input type="text" value="<?= esc($user['username']) ?>" readonly style="color: var(--text-muted); cursor: not-allowed; font-family: 'Space Mono', monospace; font-size: 16px;">
                        <span class="btn-toggle-pass" style="cursor: default; color: #10b981;" title="Username tidak bisa diubah"><i class="ph-fill ph-lock-key"></i></span>
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
                <button type="submit" class="btn-submit" onclick="this.innerHTML='<i class=\'ph-bold ph-spinner-gap ph-spin\' style=\'font-size:22px;\'></i> Memverifikasi...';">
                    <i class="ph-bold ph-key"></i> Simpan Kata Sandi Baru
                </button>
            </form>
        </div>

        <div id="bantuan" class="tab-pane">
            <div class="pane-title"><i class="ph-fill ph-headset"></i> Pusat Bantuan Noric</div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px;">
                <div class="info-box" style="display: flex; align-items: center; gap: 18px; border-color: rgba(16, 185, 129, 0.3); background: rgba(16,185,129,0.02);">
                    <div style="width: 54px; height: 54px; border-radius: 16px; background: rgba(16, 185, 129, 0.15); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: inset 0 2px 4px rgba(255,255,255,0.5);"><i class="ph-fill ph-whatsapp-logo"></i></div>
                    <div>
                        <div class="info-label" style="color: #10b981;">Support IT (Sistem Error)</div>
                        <div class="info-value" style="font-family: 'Space Mono', monospace; font-size: 18px;">0858-5468-5623</div>
                    </div>
                </div>
                <div class="info-box" style="display: flex; align-items: center; gap: 18px; border-color: rgba(245, 158, 11, 0.3); background: rgba(245,158,11,0.02);">
                    <div style="width: 54px; height: 54px; border-radius: 16px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: inset 0 2px 4px rgba(255,255,255,0.5);"><i class="ph-fill ph-buildings"></i></div>
                    <div>
                        <div class="info-label" style="color: #f59e0b;">HRD (Tanya Gaji/Absen)</div>
                        <div class="info-value" style="font-family: 'Space Mono', monospace; font-size: 18px;">Ext. 104</div>
                    </div>
                </div>
            </div>

            <h4 style="font-size: 15px; font-weight: 900; color: var(--text-main); margin-bottom: 18px; text-transform: uppercase; letter-spacing: 0.8px;">Pertanyaan yang Sering Diajukan</h4>
            
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