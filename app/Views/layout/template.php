<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard') ?> | <?= esc($company['app_name'] ?? 'Noric ERP') ?></title>
    
    <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link rel="apple-touch-icon" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        /* ==========================================================================
           1. CSS VARIABLES (DESIGN SYSTEM)
           ========================================================================== */
        :root {
            --bg-base: #f4f4f5; 
            --bg-surface: #ffffff; 
            --border-subtle: #e4e4e7;
            --text-main: #09090b; 
            --text-muted: #71717a;
            --accent-main: #2563eb; 
            --accent-light: rgba(37, 99, 235, 0.1); 
            --sidebar-width: 280px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-dropdown: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html.dark {
            --bg-base: #09090b; 
            --bg-surface: #18181b; 
            --border-subtle: rgba(255, 255, 255, 0.1);
            --text-main: #f4f4f5; 
            --text-muted: #a1a1aa;
            --accent-main: #38bdf8; 
            --accent-light: rgba(56, 189, 248, 0.15);
            --shadow-card: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            --shadow-dropdown: 0 20px 25px -5px rgba(0, 0, 0, 0.8), 0 8px 10px -6px rgba(0, 0, 0, 0.6);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; -webkit-font-smoothing: antialiased; }
        body { background-color: var(--bg-base); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; transition: var(--transition-smooth); }

        /* ==========================================================================
           2. SIDEBAR NAVIGATION
           ========================================================================== */
        .sidebar {
            width: var(--sidebar-width); height: calc(100vh - 40px); margin: 20px 0 20px 20px;
            background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px;
            box-shadow: var(--shadow-card); display: flex; flex-direction: column; position: fixed; z-index: 100;
            transition: var(--transition-smooth);
        }

        .brand { padding: 30px 25px 20px; display: flex; align-items: center; gap: 12px; }
        .brand img { width: 38px; height: 38px; border-radius: 10px; object-fit: cover; box-shadow: var(--shadow-sm); }
        .brand-text { font-weight: 800; font-size: 18px; letter-spacing: -0.5px; color: var(--text-main); line-height: 1.2;}

        .nav-menu { padding: 0 15px 20px 15px; margin-top: 10px; flex-grow: 1; overflow-y: auto; overflow-x: hidden; scroll-behavior: smooth; }
        .nav-menu::-webkit-scrollbar { width: 4px; }
        .nav-menu::-webkit-scrollbar-track { background: transparent; }
        .nav-menu::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 100px; }
        .nav-menu::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        .nav-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin: 24px 0 10px 15px; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 12px; transition: all 0.2s ease; margin-bottom: 4px; cursor: pointer; border: 1px solid transparent;}
        .nav-link i { font-size: 20px; transition: transform 0.2s; }
        
        .nav-link:hover { color: var(--text-main); background: var(--bg-base); }
        .nav-link:hover i { transform: scale(1.1); }
        .nav-link.active { color: var(--accent-main); background: var(--accent-light); font-weight: 800; border-color: rgba(37, 99, 235, 0.05); }

        /* ==========================================================================
           3. WORKSPACE & HEADER
           ========================================================================== */
        .workspace { margin-left: calc(var(--sidebar-width) + 20px); padding: 20px 40px; flex-grow: 1; display: flex; flex-direction: column; width: calc(100% - var(--sidebar-width) - 20px); min-height: 100vh;}
        
        .header { display: flex; justify-content: space-between; align-items: center; padding: 10px 0 30px; }
        .mobile-toggle { display: none; background: none; border: none; color: var(--text-main); font-size: 28px; cursor: pointer; }

        .header-left { display: flex; align-items: center; gap: 20px;}
        .header-search input { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 12px 20px 12px 40px; border-radius: 100px; color: var(--text-main); width: 280px; font-size: 14px; outline: none; transition: var(--transition-smooth); box-shadow: var(--shadow-sm); background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2371717a' viewBox='0 0 256 256'%3E%3Cpath d='M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z'%3E%3C/path%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: 14px center;}
        .header-search input:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); width: 320px;}

        .header-clock { display: flex; align-items: center; gap: 8px; font-family: 'Space Mono', monospace; font-size: 13px; font-weight: 800; color: var(--text-muted); background: var(--bg-surface); padding: 12px 20px; border-radius: 100px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);}

        .header-actions { display: flex; align-items: center; gap: 15px; }
        
        .theme-toggle { background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer; transition: var(--transition-smooth); box-shadow: var(--shadow-sm); }
        .theme-toggle:hover { color: var(--accent-main); border-color: var(--accent-main); transform: rotate(15deg);}

        /* ==========================================================================
           4. PROFILE DROPDOWN (SAAS STYLE)
           ========================================================================== */
        .profile-dropdown-wrapper { position: relative; }
        .user-pill { display: flex; align-items: center; gap: 10px; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 6px 14px 6px 6px; border-radius: 100px; cursor: pointer; transition: var(--transition-smooth); box-shadow: var(--shadow-sm); user-select: none; }
        .user-pill:hover, .user-pill.active { border-color: var(--accent-main); background: var(--bg-base); }
        
        .avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--accent-main); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; }
        .user-info { display: flex; flex-direction: column; margin-left: 2px; }
        .user-name { font-size: 13px; font-weight: 800; color: var(--text-main); line-height: 1.2; }
        .user-role { font-size: 11px; font-weight: 600; color: var(--text-muted); line-height: 1.2; }

        .profile-dropdown-menu { position: absolute; top: calc(100% + 15px); right: 0; width: 260px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; box-shadow: var(--shadow-dropdown); padding: 8px; opacity: 0; visibility: hidden; transform: translateY(-15px) scale(0.95); transition: var(--transition-smooth); z-index: 1000; backdrop-filter: blur(10px); }
        .profile-dropdown-menu.show { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
        
        .dropdown-header { padding: 12px; margin-bottom: 4px; background: var(--bg-base); border-radius: 10px;}
        .dropdown-item { display: flex; align-items: center; gap: 12px; padding: 12px; color: var(--text-main); text-decoration: none; font-size: 13px; font-weight: 600; border-radius: 10px; transition: background 0.2s, color 0.2s; }
        .dropdown-item i { font-size: 18px; color: var(--text-muted); transition: color 0.2s; }
        .dropdown-item:hover { background: var(--bg-base); color: var(--accent-main); }
        .dropdown-item:hover i { color: var(--accent-main); }
        .dropdown-item.text-danger:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .dropdown-item.text-danger:hover i { color: #ef4444; }
        .dropdown-divider { height: 1px; background: var(--border-subtle); margin: 6px 0; }

        /* ==========================================================================
           5. ALERTS & FOOTER
           ========================================================================== */
        .alert-box { padding: 16px 20px; border-radius: 12px; margin-bottom: 25px; display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 14px; box-shadow: var(--shadow-sm); animation: slideDown 0.3s ease-out;}
        .alert-success { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #059669; }
        .alert-danger { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #dc2626; }
        
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .saas-footer { margin-top: auto; padding-top: 40px; padding-bottom: 20px; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: 600; }

        /* ==========================================================================
           6. RESPONSIVE MOBILE
           ========================================================================== */
        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-120%); margin: 0; height: 100vh; border-radius: 0 24px 24px 0; }
            .sidebar.active { transform: translateX(0); }
            .workspace { margin-left: 0; padding: 20px; width: 100%; }
            .mobile-toggle { display: block; } 
            .header-search, .header-clock { display: none; }
            .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 90; display: none; opacity: 0; transition: opacity 0.3s; }
            .overlay.active { display: block; opacity: 1; }
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>" alt="Logo">
            <div class="brand-text"><?= esc($company['app_name'] ?? 'ERP System') ?></div>
        </div>

       <div class="nav-menu">
            <?php 
                $role = session()->get('role');
                $dept = session()->get('department');
                $isAdmin = ($role === 'admin'); 
            ?>

           <?php if($isAdmin): ?>
                <div class="nav-label">Command Center</div>
                <a href="<?= base_url('/dashboard') ?>" class="nav-link <?= (url_is('dashboard')) ? 'active' : '' ?>">
                    <i class="ph ph-presentation-chart"></i> Dasbor Eksekutif
                </a>
                
                <a href="<?= base_url('/setting/company') ?>" class="nav-link <?= (url_is('setting/company')) ? 'active' : '' ?>">
                    <i class="ph ph-buildings"></i> Identitas Perusahaan
                </a>

                <a href="<?= base_url('/setting/workshift_create') ?>" class="nav-link <?= (url_is('setting/workshift*')) ? 'active' : '' ?>">
                    <i class="ph ph-clock"></i> Parameter Shift Kerja
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
                    Area Kerja: <?= esc($dept ?? 'Umum') ?>
                </div>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Produksi & Manufaktur'): ?>
                <div class="nav-label" style="margin-top:20px;">Pabrik & Manufaktur</div>
                <a href="<?= base_url('/production') ?>" class="nav-link <?= (url_is('production*')) ? 'active' : '' ?>">
                    <i class="ph ph-factory"></i> Produksi & BoM
                </a>
                <a href="<?= base_url('/asset') ?>" class="nav-link <?= (url_is('asset*')) ? 'active' : '' ?>">
                    <i class="ph ph-wrench"></i> Manajemen Aset Mesin
                </a>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Admin Sales' || $dept === 'Logistik & Gudang'): ?>
                <div class="nav-label" style="margin-top:20px;">Supply Chain & Gudang</div>
                
                <a href="<?= base_url('/procurement') ?>" class="nav-link <?= (url_is('procurement*')) ? 'active' : '' ?>">
                    <i class="ph ph-truck"></i> Procurement (PO)
                </a>
                <a href="<?= base_url('/warehouse/local-inventory') ?>" class="nav-link <?= (url_is('warehouse/local-inventory')) ? 'active' : '' ?>">
                    <i class="ph ph-warehouse"></i> Master Gudang Lokal
                </a>
                <a href="<?= base_url('/warehouse/mass-fulfillment') ?>" class="nav-link <?= (url_is('warehouse/mass-fulfillment')) ? 'active' : '' ?>">
                    <i class="ph ph-stack"></i> Pusat Cetak Massal
                </a>
                <a href="<?= base_url('/warehouse/orders') ?>" class="nav-link <?= (url_is('warehouse/orders')) ? 'active' : '' ?>">
                    <i class="ph ph-package"></i> Antrean Packing
                </a>
                <a href="<?= base_url('/warehouse/cancellation-hub') ?>" class="nav-link <?= (url_is('warehouse/cancellation-hub')) ? 'active' : '' ?>">
                    <i class="ph ph-warning-circle"></i> Resolusi Pembatalan
                </a>

                <div class="nav-label" style="margin-top:20px;">E-Commerce B2C</div>
                <a href="<?= base_url('/shopee') ?>" class="nav-link <?= (url_is('shopee*') || url_is('marketing*')) ? 'active' : '' ?>">
                    <i class="ph ph-storefront"></i> Shopee Omnichannel
                </a>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Manajemen & HRD'): ?>
                <div class="nav-label" style="margin-top:20px;">Keuangan & Penjualan</div>
                
                <a href="<?= base_url('/accounting') ?>" class="nav-link <?= (url_is('accounting*')) ? 'active' : '' ?>">
                    <i class="ph ph-books"></i> Buku Besar (GL)
                </a>
                <a href="<?= base_url('/wholesale') ?>" class="nav-link <?= (url_is('wholesale*')) ? 'active' : '' ?>">
                    <i class="ph ph-handshake"></i> B2B Grosir & Piutang
                </a>
                <a href="<?= base_url('/sales/offline') ?>" class="nav-link <?= (url_is('sales/offline')) ? 'active' : '' ?>">
                    <i class="ph ph-monitor"></i> POS Kasir Pabrik
                </a>
                <a href="<?= base_url('/finance/cash_index') ?>" class="nav-link <?= (url_is('finance*')) ? 'active' : '' ?>">
                    <i class="ph ph-bank"></i> Kas Operasional
                </a>

                <div class="nav-label" style="margin-top:20px;">HRD & IoT Absensi</div>
                <a href="<?= base_url('/employee') ?>" class="nav-link <?= (url_is('employee*')) ? 'active' : '' ?>">
                    <i class="ph ph-users-three"></i> Database Karyawan
                </a>
                <a href="<?= base_url('/attendance') ?>" class="nav-link <?= (url_is('attendance*')) ? 'active' : '' ?>">
                    <i class="ph ph-fingerprint"></i> Data Absensi Mesin
                </a>
                <a href="<?= base_url('/payroll') ?>" class="nav-link <?= (url_is('payroll*')) ? 'active' : '' ?>">
                    <i class="ph ph-wallet"></i> Kelola Penggajian
                </a>
                <a href="<?= base_url('/leave/approval') ?>" class="nav-link <?= (url_is('leave/approval')) ? 'active' : '' ?>">
                    <i class="ph ph-files"></i> Approval Cuti Karyawan
                </a>
                <?php if($isAdmin): ?>
                    <a href="<?= base_url('/device') ?>" class="nav-link <?= (url_is('device*')) ? 'active' : '' ?>">
                        <i class="ph ph-cpu"></i> Control Panel IoT
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <a href="<?= base_url('/logout') ?>" class="nav-link" style="color: #ef4444; margin-top: 30px; border-top: 1px dashed var(--border-subtle); border-radius: 0; padding-top: 20px;">
                <i class="ph ph-sign-out"></i> Keluar Sistem
            </a>
        </div>
    </aside>

    <main class="workspace">
        <header class="header">
            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle Menu">
                <i class="ph ph-list"></i>
            </button>
            
            <div class="header-left">
                <div class="header-search">
                    <input type="text" placeholder="Cari menu atau tekan Ctrl+K..." aria-label="Search">
                </div>
                <div class="header-clock" title="Waktu Server Real-Time">
                    <i class="ph ph-clock" style="color: var(--accent-main); font-size: 18px;"></i> 
                    <span id="live-clock">Memuat...</span>
                </div>
            </div>

            <div class="header-actions">
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle Dark Mode">
                    <i class="ph ph-moon" id="themeIcon"></i>
                </button>

                <div class="profile-dropdown-wrapper">
                    <div class="user-pill" id="profileDropdownBtn">
                        <div class="avatar"><?= strtoupper(substr(session()->get('name') ?? 'A', 0, 1)) ?></div>
                        <div class="user-info">
                            <span class="user-name"><?= esc(session()->get('name') ?? 'Administrator') ?></span>
                            <span class="user-role">
                                <?php 
                                    if(session()->get('role') === 'admin') echo 'System Admin';
                                    else echo esc(session()->get('position') ?? 'Karyawan');
                                ?>
                            </span>
                        </div>
                        <i class="ph ph-caret-down" style="color: var(--text-muted); font-size: 14px; margin-left: 8px;"></i>
                    </div>

                    <div class="profile-dropdown-menu" id="profileDropdownMenu">
                        <div class="dropdown-header">
                            <div style="font-weight: 900; font-size: 15px; color: var(--text-main); margin-bottom: 2px;">
                                <?= esc(session()->get('name') ?? 'Administrator') ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); font-family: 'Space Mono', monospace; font-weight: 700;">
                                NIK: <?= esc(session()->get('employee_id') ?? 'SYSTEM-ROOT') ?>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('/profile') ?>" class="dropdown-item"><i class="ph ph-user"></i> Profil Saya</a>
                        <a href="<?= base_url('/setting/company') ?>" class="dropdown-item"><i class="ph ph-gear-six"></i> Pengaturan Sistem</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('/logout') ?>" class="dropdown-item text-danger"><i class="ph ph-sign-out"></i> Keluar Sistem</a>
                    </div>
                </div>
            </div>
        </header>

        <div style="padding: 0 5px;">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert-box alert-success">
                    <i class="ph-fill ph-check-circle" style="font-size: 24px;"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert-box alert-danger">
                    <i class="ph-fill ph-warning-circle" style="font-size: 24px;"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
        </div>

        <?= $this->renderSection('content') ?>

        <footer class="saas-footer">
            &copy; <?= date('Y') ?> <b><?= esc($company['company_name'] ?? 'Noric Exhaust') ?></b>. Crafted specifically for Enterprise Manufacturing.
        </footer>
    </main>

    <script>
        // 1. Sidebar Toggle Logic
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('mobileToggle');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        toggleBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);

        // 2. Dark Mode Engine
        const themeToggleBtn = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        if (localStorage.getItem('theme') === 'dark') {
            htmlElement.classList.add('dark');
            themeIcon.classList.replace('ph-moon', 'ph-sun');
        }

        themeToggleBtn.addEventListener('click', () => {
            htmlElement.classList.toggle('dark');
            if (htmlElement.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                themeIcon.classList.replace('ph-moon', 'ph-sun');
            } else {
                localStorage.setItem('theme', 'light');
                themeIcon.classList.replace('ph-sun', 'ph-moon');
            }
        });

        // 3. Profile Dropdown Interaction
        const profileBtn = document.getElementById('profileDropdownBtn');
        const profileMenu = document.getElementById('profileDropdownMenu');

        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileMenu.classList.toggle('show');
            profileBtn.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.remove('show');
                profileBtn.classList.remove('active');
            }
        });

        // 4. Formatting Rupiah Helper
        function formatRupiah(angka) {
            if (!angka) return;
            let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
                split   = number_string.split(','),
                sisa    = split[0].length % 3,
                rupiah  = split[0].substr(0, sisa),
                ribuan  = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            angka.value = rupiah;
        }

        // 5. Live Clock System
        function startLiveClock() {
            const clockEl = document.getElementById('live-clock');
            if(clockEl) {
                setInterval(() => {
                    const now = new Date();
                    clockEl.textContent = now.toLocaleTimeString('id-ID', { hour12: false });
                }, 1000);
                // Eksekusi pertama kali tanpa harus menunggu 1 detik
                clockEl.textContent = new Date().toLocaleTimeString('id-ID', { hour12: false });
            }
        }
        document.addEventListener('DOMContentLoaded', startLiveClock);
    </script>
</body>
</html>