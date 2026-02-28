<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard') ?> | <?= esc($company['app_name'] ?? 'Sistem ERP') ?></title>
    
    <link rel="icon" type="image/png" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">
    <link rel="apple-touch-icon" href="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        :root {
            --bg-base: #f4f4f5; --bg-surface: #ffffff; --border-subtle: #e4e4e7;
            --text-main: #09090b; --text-muted: #71717a;
            --accent-main: #2563eb; --accent-light: rgba(37, 99, 235, 0.1); 
            --sidebar-width: 280px;
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        html.dark {
            --bg-base: #09090b; --bg-surface: #18181b; --border-subtle: rgba(255, 255, 255, 0.1);
            --text-main: #f4f4f5; --text-muted: #a1a1aa;
            --accent-main: #38bdf8; --accent-light: rgba(56, 189, 248, 0.15);
            --shadow-card: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; -webkit-font-smoothing: antialiased; }
        body { background-color: var(--bg-base); color: var(--text-main); display: flex; min-height: 100vh; overflow-x: hidden; transition: background-color 0.4s ease, color 0.4s ease; }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width); height: calc(100vh - 40px); margin: 20px 0 20px 20px;
            background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 24px;
            box-shadow: var(--shadow-card); display: flex; flex-direction: column; position: fixed; z-index: 100;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), background-color 0.4s ease, border-color 0.4s ease;
        }

        .brand { padding: 30px 25px 20px; display: flex; align-items: center; gap: 12px; }
        .brand img { width: 36px; height: 36px; border-radius: 8px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .brand-text { font-weight: 800; font-size: 18px; letter-spacing: -0.5px; color: var(--text-main); line-height: 1.2;}

        .nav-menu { padding: 0 15px 20px 15px; margin-top: 10px; flex-grow: 1; overflow-y: auto; overflow-x: hidden; scroll-behavior: smooth; }
        .nav-menu::-webkit-scrollbar { width: 5px; }
        .nav-menu::-webkit-scrollbar-track { background: transparent; }
        .nav-menu::-webkit-scrollbar-thumb { background: var(--border-subtle); border-radius: 100px; }
        .nav-menu::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

        .nav-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin: 20px 0 10px 15px; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 14px; border-radius: 12px; transition: all 0.2s ease; margin-bottom: 4px; cursor: pointer;}
        .nav-link i { font-size: 20px; }
        .nav-link:hover { color: var(--text-main); background: var(--bg-base); }
        .nav-link.active { color: var(--accent-main); background: var(--accent-light); font-weight: 700; }

        /* --- SUBMENU DROPDOWN STYLE (NEW) --- */
        .nav-dropdown { display: flex; flex-direction: column; margin-bottom: 4px;}
        .dropdown-toggle { display: flex; justify-content: space-between; align-items: center; width: 100%; transition: 0.2s;}
        .dropdown-toggle .caret { transition: transform 0.3s ease; font-size: 14px; }
        .dropdown-toggle.open .caret { transform: rotate(180deg); }
        .dropdown-toggle.open { color: var(--text-main); font-weight: 700; }
        
        .submenu { overflow: hidden; max-height: 0; transition: max-height 0.3s ease-out; background: rgba(0,0,0,0.01); border-radius: 0 0 12px 12px; margin-top: -4px;}
        html.dark .submenu { background: rgba(255,255,255,0.02); }
        
        .submenu-inner { padding: 8px 0 10px 15px; display: flex; flex-direction: column; }
        .sub-link { padding: 10px 15px 10px 25px; color: var(--text-muted); text-decoration: none; font-size: 13px; font-weight: 600; border-radius: 8px; transition: 0.2s; position: relative; display: flex; align-items: center;}
        
        .sub-link::before { content: ''; position: absolute; left: 10px; top: 0; bottom: 0; width: 1px; background: var(--border-subtle); transition: 0.2s;}
        .sub-link:hover::before, .sub-link.active::before { background: var(--accent-main); width: 2px;}
        
        .sub-link:hover { color: var(--text-main); background: var(--bg-base); }
        .sub-link.active { color: var(--accent-main); font-weight: 800; background: var(--accent-light); }
        
        .sub-divider { border-bottom: 1px dashed var(--border-subtle); margin: 5px 15px 5px 25px; }

        /* --- WORKSPACE --- */
        .workspace { margin-left: calc(var(--sidebar-width) + 20px); padding: 20px 40px; flex-grow: 1; display: flex; flex-direction: column; width: calc(100% - var(--sidebar-width) - 20px); min-height: 100vh;}
        .header { display: flex; justify-content: space-between; align-items: center; padding: 10px 0 30px; }
        .mobile-toggle { display: none; background: none; border: none; color: var(--text-main); font-size: 28px; cursor: pointer; }

        .header-search input { background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 12px 20px; border-radius: 100px; color: var(--text-main); width: 300px; font-size: 14px; outline: none; transition: all 0.3s; box-shadow: var(--shadow-card); }
        .header-search input:focus { border-color: var(--accent-main); box-shadow: 0 0 0 3px var(--accent-light); }

        .header-actions { display: flex; align-items: center; gap: 15px; }
        .theme-toggle { background: var(--bg-surface); border: 1px solid var(--border-subtle); color: var(--text-main); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer; transition: all 0.3s; box-shadow: var(--shadow-card); }
        .theme-toggle:hover { color: var(--accent-main); border-color: var(--accent-main); }

        /* PROFILE DROPDOWN */
        .profile-dropdown-wrapper { position: relative; }
        .user-pill { display: flex; align-items: center; gap: 10px; background: var(--bg-surface); border: 1px solid var(--border-subtle); padding: 6px 12px 6px 6px; border-radius: 100px; cursor: pointer; transition: all 0.3s; box-shadow: var(--shadow-card); user-select: none; }
        .user-pill:hover, .user-pill.active { border-color: var(--accent-main); background: var(--bg-base); }
        .profile-dropdown-menu { position: absolute; top: calc(100% + 15px); right: 0; width: 240px; background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); padding: 8px; opacity: 0; visibility: hidden; transform: translateY(-15px) scale(0.95); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); z-index: 1000; }
        html.dark .profile-dropdown-menu { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5); }
        .profile-dropdown-menu.show { opacity: 1; visibility: visible; transform: translateY(0) scale(1); }
        .dropdown-header { padding: 12px 12px 8px; margin-bottom: 4px; }
        .dropdown-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; color: var(--text-main); text-decoration: none; font-size: 13px; font-weight: 600; border-radius: 10px; transition: background 0.2s, color 0.2s; }
        .dropdown-item i { font-size: 18px; color: var(--text-muted); transition: color 0.2s; }
        .dropdown-item:hover { background: var(--bg-base); color: var(--accent-main); }
        .dropdown-item:hover i { color: var(--accent-main); }
        .dropdown-item.text-danger:hover { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .dropdown-item.text-danger:hover i { color: #ef4444; }
        .dropdown-divider { height: 1px; background: var(--border-subtle); margin: 6px 0; }
        .avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--accent-main); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-120%); margin: 0; height: 100vh; border-radius: 0 24px 24px 0; }
            .sidebar.active { transform: translateX(0); }
            .workspace { margin-left: 0; padding: 20px; width: 100%; }
            .mobile-toggle { display: block; } .header-search { display: none; }
            .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 90; display: none; opacity: 0; transition: opacity 0.3s; }
            .overlay.active { display: block; opacity: 1; }
        }

        .saas-footer { margin-top: auto; padding-top: 40px; padding-bottom: 20px; text-align: center; font-size: 12px; color: var(--text-muted); font-weight: 500; }
    </style>
</head>
<body>

    <div class="overlay" id="overlay"></div>

    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="<?= base_url('uploads/logo/' . esc($company['logo_path'] ?? 'default-logo.png')) ?>" alt="Logo">
            <div class="brand-text"><?= esc($company['app_name'] ?? 'ERP V2') ?></div>
        </div>

       <div class="nav-menu">
            <?php 
                $role = session()->get('role');
                $dept = session()->get('department');
                $isAdmin = ($role === 'admin'); 
            ?>

            <?php if($isAdmin): ?>
                <div class="nav-label">Command Center</div>
                <a href="<?= base_url('/omni-dashboard') ?>" class="nav-link <?= (url_is('omni-dashboard')) ? 'active' : '' ?>">
                    <i class="ph ph-presentation-chart"></i> Dasbor Eksekutif
                </a>

                <div class="nav-label">Super Admin Panel</div>
                <a href="<?= base_url('/dashboard') ?>" class="nav-link <?= (url_is('dashboard')) ? 'active' : '' ?>">
                    <i class="ph ph-squares-four"></i> HRD Dashboard
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
                    Area: <?= esc($dept ?? 'Umum') ?>
                </div>
            <?php endif; ?>

           <?php if($isAdmin || $dept === 'Admin Sales' || $dept === 'Logistik & Gudang'): ?>
                <div class="nav-label" style="margin-top:20px;">E-Commerce & Gudang</div>
                
                <a href="<?= base_url('/warehouse/local-inventory') ?>" class="nav-link <?= (url_is('warehouse/local-inventory')) ? 'active' : '' ?>">
                    <i class="ph ph-warehouse"></i> Master Gudang Lokal
                </a>

                <a href="<?= base_url('/shopee') ?>" class="nav-link <?= (url_is('shopee*')) ? 'active' : '' ?>">
                    <i class="ph ph-storefront"></i> Integrasi Shopee
                </a>
                
                <a href="<?= base_url('/warehouse/mass-fulfillment') ?>" class="nav-link <?= (url_is('warehouse/mass-fulfillment')) ? 'active' : '' ?>">
                    <i class="ph ph-stack"></i> Pusat Cetak Massal
                </a>
                <a href="<?= base_url('/warehouse/orders') ?>" class="nav-link <?= (url_is('warehouse/orders')) ? 'active' : '' ?>">
                    <i class="ph ph-package"></i> Antrean Packing
                </a>
            <?php endif; ?>

           <?php if($isAdmin || $dept === 'Manajemen & HRD'): ?>
                <?php if($isAdmin): ?><div class="nav-label" style="margin-top:20px;">Modul HRD & Payroll</div><?php endif; ?>
                <a href="<?= base_url('/employee') ?>" class="nav-link <?= (url_is('employee*')) ? 'active' : '' ?>">
                    <i class="ph ph-users-three"></i> Database Karyawan
                </a>
                <a href="<?= base_url('/attendance') ?>" class="nav-link <?= (url_is('attendance*')) ? 'active' : '' ?>">
                    <i class="ph ph-fingerprint"></i> Data Absensi (IoT)
                </a>
                <a href="<?= base_url('/payroll') ?>" class="nav-link <?= (url_is('payroll*')) ? 'active' : '' ?>">
                    <i class="ph ph-wallet"></i> Kelola Penggajian
                </a>
                <a href="<?= base_url('/leave/approval') ?>" class="nav-link <?= (url_is('leave/approval')) ? 'active' : '' ?>">
                    <i class="ph ph-files"></i> Approval Cuti & Izin
                </a>

                <?php if($isAdmin): ?><div class="nav-label" style="margin-top:20px;">Hardware Management</div><?php endif; ?>
                <a href="<?= base_url('/device') ?>" class="nav-link <?= (url_is('device*')) ? 'active' : '' ?>">
                    <i class="ph ph-cpu"></i> Control Panel Mesin
                </a>

                <div class="nav-label" style="margin-top:20px;">Modul Finance & Kas</div>
                <a href="<?= base_url('/sales/offline') ?>" class="nav-link <?= (url_is('sales/offline')) ? 'active' : '' ?>">
                    <i class="ph ph-monitor"></i> Kasir Penjualan (POS)
                </a>
                <a href="<?= base_url('/finance/cash_index') ?>" class="nav-link <?= (url_is('finance*')) ? 'active' : '' ?>">
                    <i class="ph ph-bank"></i> Kas Operasional
                </a>
            <?php endif; ?>

            <?php if($isAdmin || $dept === 'Produksi & Manufaktur'): ?>
                <?php if($isAdmin): ?><div class="nav-label" style="margin-top:20px;">Modul Produksi</div><?php endif; ?>
                <a href="<?= base_url('/production') ?>" class="nav-link <?= (url_is('production')) ? 'active' : '' ?>">
                    <i class="ph ph-factory"></i> Input Produksi Pabrik
                </a>
                <a href="#" class="nav-link"><i class="ph ph-clipboard-text"></i> Surat Perintah Kerja</a>
                <a href="#" class="nav-link"><i class="ph ph-wrench"></i> Request Maintenance</a>
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
                                <?= esc(session()->get('name') ?? 'Administrator') ?>
                            </div>
                            <div style="font-size: 12px; color: var(--text-muted); font-family: monospace;">
                             NIK: <?= esc(session()->get('employee_id')) ?>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('/profile') ?>" class="dropdown-item"><i class="ph ph-user"></i> Profil Saya</a>
                        <a href="<?= base_url('/profile') ?>" class="dropdown-item"><i class="ph ph-gear-six"></i> Pengaturan Akun</a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('/logout') ?>" class="dropdown-item text-danger"><i class="ph ph-sign-out"></i> Keluar Sistem</a>
                    </div>
                </div>
            </div>
        </header>

        <div style="padding: 0 40px;">
            <?php if (session()->getFlashdata('success')): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #059669; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 14px;">
                    <i class="ph ph-check-circle" style="font-size: 20px;"></i>
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #dc2626; padding: 15px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 14px;">
                    <i class="ph ph-warning-circle" style="font-size: 20px;"></i>
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>
        </div>

        <?= $this->renderSection('content') ?>

        <footer class="saas-footer">
            &copy; <?= date('Y') ?> <?= esc($company['company_name'] ?? 'Company Name') ?>. Powered by <?= esc($company['app_name'] ?? 'ERP System') ?>.
        </footer>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('mobileToggle');
        const overlay = document.getElementById('overlay');

        function toggleMenu() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
        toggleBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);

        // Dark Mode Logic
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

        // Dropdown Profile Logic
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

        // ===============================================
        // LOGIKA MENU DROPDOWN ACCORDION (SIDEBAR SHOPEE)
        // ===============================================
        function toggleSubmenu(menuId, el) {
            const submenu = document.getElementById(menuId);
            const isOpen = el.classList.contains('open');
            
            if (!isOpen) {
                el.classList.add('open');
                submenu.style.maxHeight = submenu.scrollHeight + "px";
            } else {
                el.classList.remove('open');
                submenu.style.maxHeight = null;
            }
        }

        // ===============================================
        // GLOBAL HELPER JAVASCRIPT
        // ===============================================
        function formatRupiah(angka) {
            if (!angka) return;
            var number_string = angka.value.replace(/[^,\d]/g, '').toString(),
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

        function startLiveClock() {
            const clockEl = document.getElementById('live-clock');
            if(clockEl) {
                setInterval(() => {
                    const now = new Date();
                    clockEl.textContent = now.toLocaleTimeString('id-ID', { hour12: false });
                }, 1000);
            }
        }
        document.addEventListener('DOMContentLoaded', startLiveClock);
    </script>
</body>
</html>