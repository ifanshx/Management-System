<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Noric Workspace' ?></title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        /* ==========================================
           1. TEMA TERANG (LIGHT MODE) - DEFAULT
           Base: Putih, Hitam, Biru
           ========================================== */
        :root {
            --bg-base: #f4f4f5;       /* Latar belakang utama (Abu-abu sangat muda) */
            --bg-surface: #ffffff;    /* Latar kotak/kartu (Putih bersih) */
            --border-subtle: #e4e4e7; /* Garis batas tipis */
            --text-main: #09090b;     /* Teks utama (Hitam pekat) */
            --text-muted: #71717a;    /* Teks pendukung (Abu-abu) */
            --accent-main: #2563eb;   /* Aksen Utama (Biru Profesional) */
            --accent-light: rgba(37, 99, 235, 0.1); 
            --sidebar-width: 280px;
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        /* ==========================================
           2. TEMA GELAP (DARK MODE)
           Base: Hitam, Putih, Biru Neon
           ========================================== */
        html.dark {
            --bg-base: #09090b;       /* Latar belakang utama (Hitam pekat Obsidian) */
            --bg-surface: #18181b;    /* Latar kotak/kartu (Abu-abu sangat gelap) */
            --border-subtle: rgba(255, 255, 255, 0.1);
            --text-main: #f4f4f5;     /* Teks utama (Putih) */
            --text-muted: #a1a1aa;    /* Teks pendukung (Abu-abu terang) */
            --accent-main: #38bdf8;   /* Aksen Utama (Biru Neon/Sky Blue) */
            --accent-light: rgba(56, 189, 248, 0.15);
            --shadow-card: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        }

        /* ==========================================
           3. GAYA UMUM & TRANSISI
           ========================================== */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* Transisi mulus saat ganti tema */
        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            transition: background-color 0.4s ease, color 0.4s ease;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            height: calc(100vh - 40px);
            margin: 20px 0 20px 20px;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 24px;
            box-shadow: var(--shadow-card);
            display: flex;
            flex-direction: column;
            position: fixed;
            z-index: 100;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.4s ease, border-color 0.4s ease;
        }

        .brand {
            padding: 30px 25px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-main);
            color: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 12px var(--accent-light);
            transition: background-color 0.4s ease;
        }

        .brand-text {
            font-weight: 800;
            font-size: 20px;
            letter-spacing: -0.5px;
            color: var(--text-main);
        }

        .nav-menu {
            padding: 0 15px 20px 15px; /* Tambahan padding bawah agar lega */
            margin-top: 10px;
            flex-grow: 1;
            overflow-y: auto; /* INI KUNCI AGAR BISA DI-SCROLL */
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        /* ==========================================
           CUSTOM SCROLLBAR UNTUK SIDEBAR
           (Elegan, tipis, bergaya macOS/SaaS)
           ========================================== */
        .nav-menu::-webkit-scrollbar {
            width: 5px; /* Lebar scrollbar sangat tipis */
        }

        .nav-menu::-webkit-scrollbar-track {
            background: transparent; /* Jalur scrollbar transparan */
        }

        .nav-menu::-webkit-scrollbar-thumb {
            background: var(--border-subtle); /* Warna scrollbar menyesuaikan tema */
            border-radius: 100px;
        }

        .nav-menu::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted); /* Warna sedikit gelap saat disorot mouse */
        }

        .nav-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 20px 0 10px 15px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border-radius: 12px;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }

        .nav-link i { font-size: 20px; }

        .nav-link:hover {
            color: var(--text-main);
            background: var(--bg-base);
        }

        .nav-link.active {
            color: var(--accent-main);
            background: var(--accent-light);
            font-weight: 700;
        }

        /* --- WORKSPACE & HEADER --- */
        .workspace {
            margin-left: calc(var(--sidebar-width) + 20px);
            padding: 20px 40px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            width: calc(100% - var(--sidebar-width) - 20px);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0 30px;
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-main);
            font-size: 28px;
            cursor: pointer;
        }

        .header-search input {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            padding: 12px 20px;
            border-radius: 100px;
            color: var(--text-main);
            width: 300px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
            box-shadow: var(--shadow-card);
        }

        .header-search input:focus {
            border-color: var(--accent-main);
            box-shadow: 0 0 0 3px var(--accent-light);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        /* Tombol Toggle Tema */
        .theme-toggle {
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            color: var(--text-main);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: var(--shadow-card);
        }

        .theme-toggle:hover {
            color: var(--accent-main);
            border-color: var(--accent-main);
        }

        /* ==========================================
           PROFILE DROPDOWN MENU (SaaS Grade)
           ========================================== */
        .profile-dropdown-wrapper {
            position: relative;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            padding: 6px 12px 6px 6px; /* Disesuaikan agar pas */
            border-radius: 100px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: var(--shadow-card);
            user-select: none; /* Mencegah teks terblok biru saat diklik cepat */
        }

        .user-pill:hover, .user-pill.active { 
            border-color: var(--accent-main); 
            background: var(--bg-base); 
        }

        .profile-dropdown-menu {
            position: absolute;
            top: calc(100% + 15px); /* Jarak dari tombol */
            right: 0;
            width: 240px;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            padding: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-15px) scale(0.95); /* Animasi muncul dari atas */
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 1000;
        }

        html.dark .profile-dropdown-menu {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }

        .profile-dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .dropdown-header {
            padding: 12px 12px 8px;
            margin-bottom: 4px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: var(--text-main);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            transition: background 0.2s, color 0.2s;
        }

        .dropdown-item i {
            font-size: 18px;
            color: var(--text-muted);
            transition: color 0.2s;
        }

        .dropdown-item:hover {
            background: var(--bg-base);
            color: var(--accent-main);
        }

        .dropdown-item:hover i {
            color: var(--accent-main);
        }

        .dropdown-item.text-danger:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .dropdown-item.text-danger:hover i {
            color: #ef4444;
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border-subtle);
            margin: 6px 0;
        }
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--accent-main);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        /* --- RESPONSIVE MOBILE --- */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-120%);
                margin: 0;
                height: 100vh;
                border-radius: 0 24px 24px 0;
            }
            .sidebar.active { transform: translateX(0); }
            .workspace { margin-left: 0; padding: 20px; width: 100%; }
            .mobile-toggle { display: block; }
            .header-search { display: none; }
            
            .overlay {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
                z-index: 90; display: none;
                opacity: 0; transition: opacity 0.3s;
            }
            .overlay.active { display: block; opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-icon"><i class="ph ph-wind"></i></div>
            <div class="brand-text">Noric V2</div>
        </div>

       <div class="nav-menu">
            <?php 
                // Deklarasikan variabel pembantu agar kode lebih bersih
                $role = session()->get('role');
                $dept = session()->get('department');
                $isAdmin = ($role === 'admin'); 
            ?>

            <?php if($isAdmin): ?>
                <div class="nav-label">Super Admin Panel</div>
                <a href="<?= base_url('/dashboard') ?>" class="nav-link <?= (url_is('dashboard')) ? 'active' : '' ?>">
                    <i class="ph ph-squares-four"></i> Dashboard Utama
                </a>
                <a href="<?= base_url('/setting/workshift_create') ?>" class="nav-link <?= (url_is('setting*')) ? 'active' : '' ?>">
                    <i class="ph ph-gear"></i> Pengaturan Sistem
                </a>
            <?php else: ?>
               <div class="nav-label">Employee Portal</div>
                <a href="<?= base_url('/portal') ?>" class="nav-link <?= (url_is('portal')) ? 'active' : '' ?>">
                    <i class="ph ph-identification-card"></i> Portal Saya
                </a>
                
                <a href="<?= base_url('/portal/slip_gaji') ?>" class="nav-link <?= (url_is('portal/slip_gaji')) ? 'active' : '' ?>">
                    <i class="ph ph-receipt"></i> Slip Gaji
                </a>
                
                <a href="<?= base_url('/leave') ?>" class="nav-link <?= (url_is('leave')) ? 'active' : '' ?>">
                    <i class="ph ph-calendar-plus"></i> Form Cuti / Izin
                </a>

                <div class="nav-label" style="color: var(--accent-main); margin-top: 25px; border-top: 1px dashed var(--border-subtle); padding-top: 15px;">
                    Area: <?= esc($dept ?? 'Umum') ?>
                </div>
            <?php endif; ?>

           <?php if($isAdmin || $dept === 'Manajemen & HRD'): ?>
                <?php if($isAdmin): ?><div class="nav-label" style="margin-top:20px;">Modul HRD & Payroll</div><?php endif; ?>
                
                <a href="<?= base_url('/employee') ?>" class="nav-link <?= (url_is('employee*')) ? 'active' : '' ?>">
                    <i class="ph ph-users-three"></i> Database Karyawan
                </a>
                <a href="<?= base_url('/attendance') ?>" class="nav-link <?= (url_is('attendance*')) ? 'active' : '' ?>">
                    <i class="ph ph-fingerprint"></i> Data Absensi (Fingerspot)
                </a>
                <a href="/payroll" class="nav-link">
                    <i class="ph ph-wallet"></i> Kelola Penggajian
                </a>
                <a href="<?= base_url('/leave/approval') ?>" class="nav-link <?= (url_is('leave/approval')) ? 'active' : '' ?>">
                    <i class="ph ph-files"></i> Approval Cuti & Izin
                </a>

                <?php if($isAdmin): ?><div class="nav-label" style="margin-top:20px;">Hardware Management</div><?php endif; ?>
                <a href="<?= base_url('/device') ?>" class="nav-link <?= (url_is('device*')) ? 'active' : '' ?>">
                    <i class="ph ph-cpu"></i> Control Panel IoT
                </a>

                <div class="nav-label" style="margin-top:20px;">Modul Finance & Kas</div>
                <a href="<?= base_url('/finance/cash_index') ?>" class="nav-link <?= (url_is('finance*')) ? 'active' : '' ?>">
                    <i class="ph ph-wallet"></i> Kas Operasional
                </a>

            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Produksi & Manufaktur'): ?>
                <?php if($isAdmin): ?><div class="nav-label" style="margin-top:20px;">Modul Produksi</div><?php endif; ?>
                
                <a href="#" class="nav-link"><i class="ph ph-clipboard-text"></i> Surat Perintah Kerja (SPK)</a>
                <a href="#" class="nav-link"><i class="ph ph-fire"></i> Input Output Harian</a>
                <a href="#" class="nav-link"><i class="ph ph-wrench"></i> Request Maintenance Mesin</a>
                <?php if(!$isAdmin): ?><a href="#" class="nav-link"><i class="ph ph-calendar-check"></i> Jadwal Shift Produksi</a><?php endif; ?>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Quality Control & R&D'): ?>
                <?php if($isAdmin): ?><div class="nav-label" style="margin-top:20px;">Modul QC & R&D</div><?php endif; ?>
                
                <a href="#" class="nav-link"><i class="ph ph-magnifying-glass-plus"></i> Form Inspeksi Mutu (QC)</a>
                <a href="#" class="nav-link"><i class="ph ph-warning-circle"></i> Input Defect / Retur</a>
                <a href="#" class="nav-link"><i class="ph ph-chart-line-up"></i> Laporan Uji Dyno Test</a>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Gudang & Logistik'): ?>
                <?php if($isAdmin): ?><div class="nav-label" style="margin-top:20px;">Modul Logistik</div><?php endif; ?>
                
                <a href="#" class="nav-link"><i class="ph ph-package"></i> Inbound (Material Masuk)</a>
                <a href="#" class="nav-link"><i class="ph ph-truck"></i> Outbound (Pengiriman)</a>
                <a href="#" class="nav-link"><i class="ph ph-stack"></i> Stok Silencer & Material</a>
                <a href="#" class="nav-link"><i class="ph ph-scan"></i> Stok Opname Bulanan</a>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Sales & Marketing'): ?>
                <?php if($isAdmin): ?><div class="nav-label" style="margin-top:20px;">Modul Penjualan</div><?php endif; ?>
                
                <a href="#" class="nav-link"><i class="ph ph-shopping-bag"></i> Input Order (PO)</a>
                <a href="#" class="nav-link"><i class="ph ph-address-book"></i> Database Distributor</a>
                <a href="#" class="nav-link"><i class="ph ph-chart-bar"></i> Laporan Penjualan</a>
            <?php endif; ?>

            <a href="<?= base_url('/logout') ?>" class="nav-link" style="color: #ef4444; margin-top: 25px; border-top: 1px solid var(--border-subtle); border-radius: 0; padding-top: 20px;">
                <i class="ph ph-sign-out"></i> Keluar Sistem
            </a>
        </div>
    </aside>

    <main class="workspace">
        <header class="header">
            <button class="mobile-toggle" id="mobileToggle"><i class="ph ph-list"></i></button>
            
            <div class="header-search">
                <input type="text" placeholder="Cari data, menu...">
            </div>

            <div class="header-actions">
                <button class="theme-toggle" id="themeToggle" title="Ganti Tema">
                    <i class="ph ph-moon" id="themeIcon"></i>
                </button>

                <div class="profile-dropdown-wrapper">
                    <div class="user-pill" id="profileDropdownBtn">
                        <div class="avatar"><?= strtoupper(substr(session()->get('name') ?? 'A', 0, 1)) ?></div>
                        <div style="display: flex; flex-direction: column; margin-left: 5px; margin-right: 5px;">
                            <span style="font-size: 13px; font-weight: 700; color: var(--text-main); line-height: 1.2;">
                                <?= esc(session()->get('name') ?? 'Administrator') ?>
                            </span>
                            <span style="font-size: 11px; font-weight: 500; color: var(--text-muted); line-height: 1.2;">
                                <?php 
                                    if(session()->get('role') === 'admin') echo 'System Admin';
                                    else echo esc(session()->get('position') ?? 'Karyawan');
                                ?>
                            </span>
                        </div>
                        <i class="ph ph-caret-down" style="color: var(--text-muted); font-size: 14px; margin-left: 5px;"></i>
                    </div>

                    <div class="profile-dropdown-menu" id="profileDropdownMenu">
                        <div class="dropdown-header">
                            <div style="font-weight: 800; font-size: 14px; color: var(--text-main); margin-bottom: 2px;">
                                <?= esc(session()->get('name') ?? 'Admin Noric') ?>
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); font-family: monospace;">
                             NIK: <?= esc(session()->get('employee_id')) ?>
                            </div>
                        </div>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a href="<?= base_url('/profile') ?>#profil" class="dropdown-item">
                            <i class="ph ph-user"></i> Profil Saya
                        </a>
                        <a href="<?= base_url('/profile') ?>#keamanan" class="dropdown-item">
                            <i class="ph ph-gear-six"></i> Pengaturan Akun
                        </a>
                        <a href="<?= base_url('/profile') ?>#bantuan" class="dropdown-item">
                            <i class="ph ph-headset"></i> Bantuan & Dukungan
                        </a>
                        
                        <div class="dropdown-divider"></div>
                        
                        <a href="<?= base_url('/logout') ?>" class="dropdown-item text-danger">
                            <i class="ph ph-sign-out"></i> Keluar Sistem
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <?= $this->renderSection('content') ?>
    </main>

    <script>
        // Logika Sidebar Mobile
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('mobileToggle');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        toggleBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);

        // Logika Dark/Light Mode
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement; // Tag <html>

        // Cek LocalStorage saat pertama kali load
        const currentTheme = localStorage.getItem('theme');
        
        // Jika ada tersimpan 'dark', maka pakai dark. Jika tidak, biarkan default (terang)
        if (currentTheme === 'dark') {
            htmlElement.classList.add('dark');
            themeIcon.classList.replace('ph-moon', 'ph-sun');
        }

        // Fungsi saat tombol diklik
        themeToggleBtn.addEventListener('click', () => {
            htmlElement.classList.toggle('dark');
            
            if (htmlElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('ph-moon', 'ph-sun'); // Ganti icon ke matahari
            } else {
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('ph-sun', 'ph-moon'); // Ganti icon ke bulan
            }
        });

        // ==========================================
        // LOGIKA PROFILE DROPDOWN MENU
        // ==========================================
        const profileBtn = document.getElementById('profileDropdownBtn');
        const profileMenu = document.getElementById('profileDropdownMenu');

        // Buka/Tutup menu saat pill diklik
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation(); // Mencegah event klik merambat ke document
            profileMenu.classList.toggle('show');
            profileBtn.classList.toggle('active');
        });

        // Tutup menu otomatis jika user mengklik area lain di luar dropdown
        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.remove('show');
                profileBtn.classList.remove('active');
            }
        });
    </script>
</body>
</html>